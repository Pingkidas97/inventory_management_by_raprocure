<?php

namespace App\Http\Controllers\Buyer;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Buyer\InventoryController;
use App\Http\Controllers\Buyer\GrnController;
use Illuminate\Http\Request;
use App\Models\Inventories;
use App\Models\Rfq;
use App\Models\Grn;
use App\Models\IndentRfq;
use App\Models\Indent;
use App\Models\Order;
use App\Models\RfqProductVariant;
use App\Models\ForceClosure;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NumberFormatterHelper;
use App\Traits\TrimFields;
use App\Traits\HasModulePermission;

use DB;

class ForceClosureController extends Controller
{
    use TrimFields;
    use HasModulePermission;
    public function fetchInventoryDetails(Request $request)
    {
        if (Auth::user()->parent_id != 0) {
            $this->ensurePermission('FORCE_CLOSURE', 'add', '1');
        }

        $inventory = Inventories::with('product')->find($request->inventory_id);

        if (!$inventory) {
            return response()->json([
                'status' => 'error',
                'message' => 'Inventory not found.'
            ]);
        }

        // Fetch RFQs
        $rfqs = Rfq::where('record_type', 2)
            ->whereIn('buyer_rfq_status', [1, 5, 9, 10])
            ->whereHas('rfqProductVariants', function ($q) use ($request) {
                $q->where('inventory_id', $request->inventory_id)
                    ->where('inventory_status', 1);
            })
            ->with(['rfqProductVariants' => function ($q) use ($request) {
                $q->where('inventory_id', $request->inventory_id)
                    ->where('inventory_status', 1)
                    ->with(['orderVariantsActiveOrder']);
            }])
            ->get();

        if ($rfqs->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active RFQ exists for this inventory.'
            ]);
        }

        $filteredRfqs = [];

        foreach ($rfqs as $rfq) {

            $variant = $rfq->rfqProductVariants->first();
            if (!$variant) continue;

            // Use common function
            $data = $this->calculateForceClosureData(
                $request->inventory_id,
                $rfq->rfq_id,
                $variant
            );

            if (isset($data['error'])) continue;

            if (!$data['isEligible']) continue;

            $filteredRfqs[] = [
                'rfq_id' => $rfq->id,
                'rfq_number' => $rfq->rfq_id,
                'rfqQty' => NumberFormatterHelper::formatQty(
                    $data['rfqQty'],
                    session('user_currency')['symbol'] ?? '₹'
                ),
                'totalOrderQty' => NumberFormatterHelper::formatQty(
                    $data['totalOrder'],
                    session('user_currency')['symbol'] ?? '₹'
                ),
                'totalGrnQty' => NumberFormatterHelper::formatQty(
                    $data['totalGrn'],
                    session('user_currency')['symbol'] ?? '₹'
                ),
                'force_closure_status' => $data['forceClosure'] ? 1 : 0,
                'details' => collect($data['details'])->map(function ($d) {
                    return [
                        'order_number' => $d['order_number'],
                        'order_quantity' => NumberFormatterHelper::formatQty($d['order_quantity'],session('user_currency')['symbol'] ?? '₹'),
                        'grn_qty' => NumberFormatterHelper::formatQty($d['grn_qty'],session('user_currency')['symbol'] ?? '₹'),
                    ];
                })->values()
            ];
        }

        if (empty($filteredRfqs)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No RFQ matches the GRN condition for force closure.'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'inventory' => [
                'item_code' => $inventory->item_code ?? '',
                'product_name' => $inventory->product->product_name ?? '',
                'specification' => $inventory->specification ?? '',
                'size' => $inventory->size ?? ''
            ],
            'rfqs' => array_values($filteredRfqs)
        ]);
    }  

    private function calculateForceClosureData($inventoryId, $rfqNumber, $variant = null)
    {
        if (!$variant) {
            $variant = RfqProductVariant::where('rfq_id', $rfqNumber)
                ->where('inventory_id', $inventoryId)
                ->where('inventory_status', 1)
                ->with(['orderVariantsActiveOrder'])
                ->first();
        }

        if (!$variant) {
            return ['error' => 'RFQ Variant not found'];
        }

        // Collect PO numbers
        $poNumbers = $variant->orderVariantsActiveOrder->pluck('po_number')->unique();

        // GRN Data
        $buyerId = (auth()->user()->parent_id != 0)
            ? auth()->user()->parent_id
            : auth()->id();

        $grnData = Grn::where('inventory_id', $inventoryId)
            ->where('company_id', $buyerId)
            ->whereIn('po_number', $poNumbers)
            ->selectRaw('po_number, SUM(grn_qty) as total_grn')
            ->groupBy('po_number')
            ->pluck('total_grn', 'po_number');

        $rfqQty = (float)$variant->quantity;
        $totalOrder = 0;
        $totalGrn = 0;
        $valid = true;
        $details = [];

        foreach ($variant->orderVariantsActiveOrder as $order) {
            $orderQty = (float)$order->order_quantity;
            $grnQty = (float)($grnData[$order->po_number] ?? 0);

            $totalOrder += $orderQty;
            $totalGrn += $grnQty;

            if ($grnQty < $orderQty) {
                $valid = false;
                break;
            }

            $details[] = [
                'order_number' => $order->po_number,
                'order_quantity' => $orderQty,
                'grn_qty' => $grnQty,
            ];
        }

        $isEligible = false;
        $forceClosure = false;

        if ($valid && $totalOrder < $rfqQty && $totalOrder > 0 && $totalGrn > 0) {
            $maxRange = $totalOrder * 1.02;
            $forceClosure = ($totalGrn >= $totalOrder && $totalGrn <= $maxRange);
            $isEligible = true;
        }

        return [
            'variant' => $variant,
            'rfqQty' => $rfqQty,
            'totalOrder' => $totalOrder,
            'totalGrn' => $totalGrn,
            'details' => $details,
            'valid' => $valid,
            'isEligible' => $isEligible,
            'forceClosure' => $forceClosure
        ];
    }

    public function store(Request $request)
    {
        if (Auth::user()->parent_id != 0) {
            $this->ensurePermission('FORCE_CLOSURE', 'add', '1');
        }

        $request->validate([
            'rfq_id' => 'required|exists:rfqs,id',
            'rfq_number' => 'required|exists:rfqs,rfq_id',
            'inventory_id' => 'required|exists:inventories,id',
        ]);

        try {

            $buyerId = auth()->id();
            $buyerParentId = auth()->user()->parent_id != 0
                ? auth()->user()->parent_id
                : $buyerId;

            //  Variant check
            $variant = RfqProductVariant::where('rfq_id', $request->rfq_number)
                ->where('inventory_id', $request->inventory_id)
                ->where('inventory_status', 1)
                ->with(['orderVariantsActiveOrder'])
                ->first();

            if (!$variant) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'RFQ Variant not found'
                ], 404);
            }

            //  Inventory check
            $inventory = Inventories::find($request->inventory_id);
            if (!$inventory) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Inventory not found'
                ], 404);
            }

            //  Unapproved order check (optimized)
            $unApproveOrder = Order::where('rfq_id', $request->rfq_number)
                ->where('order_status', 3)
                ->whereHas('order_variants', function ($q) use ($variant) {
                    $q->where('rfq_product_variant_id', $variant->id);
                })
                ->exists();

            if ($unApproveOrder) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unapproved order exists. Please cancel or confirm the order before proceeding with force closure.'
                ], 400);
            }

            //  Common calculation
            $data = $this->calculateForceClosureData(
                $request->inventory_id,
                $request->rfq_number,
                $variant
            );

            if (isset($data['error'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => $data['error']
                ], 404);
            }

            if (!$data['isEligible']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'RFQ does not meet the force closure condition.'
                ], 400);
            }

            if (!$data['forceClosure']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'GRN quantity does not meet the force closure condition.'
                ], 400);
            }

            // Transaction start
            DB::beginTransaction();

            // Create force closure
            ForceClosure::create([
                'inventory_id' => $request->inventory_id,
                'rfq_id' => $request->rfq_id,
                'rfq_number' => $variant->rfq_id,
                'rfq_product_variant_id' => $variant->id,
                'original_rfq_quantity' => $data['rfqQty'],
                'updated_rfq_quantity' => $data['totalOrder'],
                'total_order_quantity' => $data['totalOrder'],
                'total_grn_quantity' => $data['totalGrn'],
                'buyer_parent_id' => $buyerParentId,
                'buyer_id' => $buyerId,
            ]);

            //  Update variant quantity
            $variant->quantity = $data['totalOrder'];
            $variant->save();

            //  Inventory mapping
            (new InventoryController())->handleIndentRfqMapping(
                $variant->product_id,
                $variant->rfq_id,
                $inventory
            );

            //  Update indent
            $this->updateIndent($inventory, $variant->rfq_id);

            //  Close indent
            (new GrnController())->closeIndent($request->inventory_id);

            DB::commit();

            //  RFQ status update
            $evaluated = $this->reEvaluateRFQVendorsStatus($request->rfq_id);

            $buyer_rfq_status = ($evaluated['is_rfq_qty_left'] == "yes") ? 9 : 5;

            DB::table('rfqs')
                ->where('rfq_id', $request->rfq_id)
                ->update(['buyer_rfq_status' => $buyer_rfq_status]);

            if (!empty($evaluated['update_vendor_rfq_status_wise'])) {
                foreach ($evaluated['update_vendor_rfq_status_wise'] as $status => $vendors) {
                    DB::table("rfq_vendors")
                        ->where('rfq_id', $request->rfq_id)
                        ->whereIn('vendor_user_id', array_values($vendors))
                        ->update(['vendor_status' => $status]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Force closure completed successfully.'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function reEvaluateRFQVendorsStatus($rfq_id)
    {

        $latestIds = DB::table('rfq_vendor_quotations')
            ->select(DB::raw('MAX(id) as id'))
            ->where('rfq_id', $rfq_id)
            ->where('status', 1)
            ->groupBy('vendor_id')
            ->pluck('id')->toArray();
        //
        $rfq_vendors = DB::table('rfq_vendors')
            ->select('vendor_user_id', 'product_id', 'vendor_status')
            ->where('rfq_id', $rfq_id)
            ->get()->toArray();
        //

        $rfq = Rfq::where('rfq_id', $rfq_id)
            ->select('id', 'rfq_id', 'buyer_id', 'buyer_rfq_status', 'created_at', 'updated_at')
            ->with([
                'rfqVendorQuotations' => function ($q) use ($latestIds) {
                    $q->select('id', 'rfq_id', 'vendor_id', 'rfq_product_variant_id', 'price', 'buyer_price', 'created_at', 'updated_at')
                        ->whereIn('id', $latestIds);
                },
                'rfqProducts' => function ($q) {
                    $q->select('id', 'rfq_id', 'product_id');
                },
                'rfqProducts.productVariants' => function ($q) use ($rfq_id) {
                    $q->where('rfq_id', $rfq_id);
                },
                'rfqOrders' => function ($q) {
                    $q->select('id', 'rfq_id', 'vendor_id', 'po_number')->where('order_status', 1);
                },
                'rfqOrders.order_variants' => function ($q) {
                    $q->select('id', 'po_number', 'rfq_product_variant_id', 'order_quantity');
                }
            ])
            ->first();
        //
        unset($latestIds);

        if (!$rfq) {
            return ['update_vendor_rfq_status_wise' => [], 'is_rfq_qty_left' => 'no'];
        }
        $rfq_data = $rfq->toArray();
        unset($rfq);

        $db_vendor_rfq_status = [];
        $rfq_product_vendor = [];
        foreach ($rfq_vendors as $key => $value) {
            $db_vendor_rfq_status[$value->vendor_user_id] = $value->vendor_status;
            $rfq_product_vendor[$value->product_id][] = $value->vendor_user_id;
        }

        $is_vendor_quote_the_price = [];
        foreach ($rfq_data['rfq_vendor_quotations'] as $key => $value) {
            if (!empty($value['price'])) {
                $is_vendor_quote_the_price[$value['vendor_id']] = (!empty($value['buyer_price']) && $value['buyer_price'] > 0) ? 'counter-offer' : 'quote';
            }
        }

        foreach ($db_vendor_rfq_status as $vendor_id => $vendor_status) {
            if (!isset($is_vendor_quote_the_price[$vendor_id])) {
                $is_vendor_quote_the_price[$vendor_id] = "no";
            }
        }

        $variant_qty = [];
        $vendor_wise_order_qty = [];
        $order_variant_qty = [];
        foreach ($rfq_data['rfq_products'] as $key => $value) {
            foreach ($value['product_variants'] as $k => $variant) {
                $variant_qty[$variant['id']] = $variant['quantity'];
                $order_variant_qty[$variant['id']] = 0;

                if (isset($rfq_product_vendor[$value['product_id']])) {
                    $vendors = $rfq_product_vendor[$value['product_id']];
                    foreach ($vendors as $vendorId) {
                        if (!isset($vendor_wise_order_qty[$vendorId])) {
                            $vendor_wise_order_qty[$vendorId] = [];
                        }
                        $vendor_wise_order_qty[$vendorId][$variant['id']] = 0;
                    }
                }
            }
        }

        foreach ($rfq_data['rfq_orders'] as $key => $order) {
            $vendor_id = $order['vendor_id'];
            foreach ($order['order_variants'] as $k => $variant) {
                $vid = $variant['rfq_product_variant_id'];
                $qty = $variant['order_quantity'];
                if (!isset($order_variant_qty[$vid])) {
                    $order_variant_qty[$vid] = 0;
                }
                $order_variant_qty[$vid] += $qty;

                if (!isset($vendor_wise_order_qty[$vendor_id][$vid])) {
                    $vendor_wise_order_qty[$vendor_id][$vid] = 0;
                }
                $vendor_wise_order_qty[$vendor_id][$vid] += $qty;
            }
        }

        $is_vendor_have_order = [];
        $is_vendor_order_qty_completed = [];
        $buyer_rfq_status = $rfq_data['buyer_rfq_status'];

        foreach ($vendor_wise_order_qty as $vendor_id => $variants_order_qty) {
            $is_vendor_have_order[$vendor_id] = array_sum(array_values($variants_order_qty)) > 0 ? "yes" : "no";

            $is_qty_left = false;
            foreach ($variants_order_qty as $variant_grp_id => $order_qty) {
                if (isset($order_variant_qty[$variant_grp_id]) && ($variant_qty[$variant_grp_id] - $order_variant_qty[$variant_grp_id]) > 0) {
                    $is_qty_left = true;
                    break;
                }
            }
            if ($is_qty_left == true) { //still vendor have some product for send counter offer
                $is_vendor_order_qty_completed[$vendor_id] = "no";
            } else {
                $is_vendor_order_qty_completed[$vendor_id] = "yes";
            }
        }

        $is_rfq_qty_left = "no";
        foreach ($vendor_wise_order_qty as $vendor_id => $variants_order_qty) {
            foreach ($variants_order_qty as $variant_grp_id => $order_qty) {
                if (isset($order_variant_qty[$variant_grp_id]) && ($variant_qty[$variant_grp_id] - $order_variant_qty[$variant_grp_id]) > 0) {
                    $is_rfq_qty_left = "yes";
                    break 2;
                }
            }
        }

        unset($rfq_data);
        unset($order_variant_qty);
        unset($variant_qty);
        unset($rfq_product_vendor);
        unset($rfq_vendors);

        $update_vendor_rfq_status = array();
        foreach ($vendor_wise_order_qty as $vendor_id => $value) {
            $vendor_rfq_status = 1;
            if ($is_vendor_have_order[$vendor_id] == "yes") {
                if ($is_vendor_order_qty_completed[$vendor_id] == "yes") {
                    $vendor_rfq_status = 5; // order confirm with full qty
                } else {
                    if ($buyer_rfq_status == 10) { //due to rfq manually closed, vendor will also partially closed
                        $vendor_rfq_status = 10;
                    } else {
                        $vendor_rfq_status = 9;  //rfq not closed, partial order
                    }
                }
            } else {
                if ($buyer_rfq_status == 10) { //due to rfq manually closed, vendor will also closed
                    $vendor_rfq_status = 8;
                } else {
                    if ($is_vendor_order_qty_completed[$vendor_id] == "yes") {
                        $vendor_rfq_status = 8; // order confirm with full qty to another vendor
                    } else if ($is_vendor_quote_the_price[$vendor_id] == "quote") {
                        $vendor_rfq_status = 7;
                    } else if ($is_vendor_quote_the_price[$vendor_id] == "counter-offer") {
                        $vendor_rfq_status = 4;
                    } else {
                        $vendor_rfq_status = 1;
                    }
                    if (in_array($vendor_rfq_status, array(4, 7)) && $db_vendor_rfq_status[$vendor_id] == 6) {
                        $vendor_rfq_status = 6;
                    }
                }
            }
            if ($db_vendor_rfq_status[$vendor_id] != $vendor_rfq_status) {
                $update_vendor_rfq_status[$vendor_id] = $vendor_rfq_status;
            }
        }

        unset($is_vendor_quote_the_price);
        unset($is_vendor_have_order);
        unset($vendor_wise_order_qty);
        unset($is_vendor_order_qty_completed);
        unset($db_vendor_rfq_status);
        unset($buyer_rfq_status);

        $update_vendor_rfq_status_wise = array();
        foreach ($update_vendor_rfq_status as $vendor_id => $vendor_rfq_status) {
            $update_vendor_rfq_status_wise[$vendor_rfq_status][] = $vendor_id;
        }
        unset($update_vendor_rfq_status);

        return array('update_vendor_rfq_status_wise' => $update_vendor_rfq_status_wise, 'is_rfq_qty_left' => $is_rfq_qty_left);
    }

    public function updateIndent($inventory, $rfqId)
    {
        $indentIDsByRfqInventory = IndentRfq::where('rfq_id', $rfqId)
            ->where('inventory_id', $inventory->id)
            ->pluck('indent_id')
            ->unique()
            ->toArray();

        $indentUsage = IndentRfq::whereIn('indent_id', $indentIDsByRfqInventory)
            ->where('inventory_id', $inventory->id)
            ->selectRaw('indent_id, SUM(used_indent_qty) as total_used_qty')
            ->groupBy(['indent_id', 'inventory_id'])
            ->get();
        $indents = Indent::whereIn('id', $indentIDsByRfqInventory)
                ->where('inventory_id', $inventory->id)
                ->get()
                ->keyBy('id');


        foreach ($indentUsage as $usage) {

            $indent = $indents->get($usage->indent_id);
            if (!$indent) continue;

            $newQty = $indent->indent_qty - $usage->total_used_qty;

            if ($newQty > 0) {
                $indent->indent_qty = $usage->total_used_qty;
                $indent->save();
            } else {
                $indent->delete();
            }
        }
    }


}