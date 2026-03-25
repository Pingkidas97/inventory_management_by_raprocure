<?php

namespace App\Http\Controllers\Buyer;

use App\Exports\{GrnReportExport,PendingGrnReportExport,PendingGrnForStockReturnReportExport};
use App\Http\Controllers\Controller;
use App\Http\Controllers\Buyer\ManualPOController;
use App\Http\Controllers\Buyer\InventoryController;
use App\Models\{
    ManualOrder,Grn,ManualOrderProduct,ReturnStock,User,Rfq,Inventories,OrderVariant,Order,Indent,RfqProductVariant,Issued,Tax,GetPass
};
use App\Helpers\{
    NumberFormatterHelper,TruncateWithTooltipHelper,PendingGrnUpdateBYrHelper
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Rules\NoSpecialCharacters;
use App\Traits\TrimFields;
use App\Traits\HasModulePermission;
use Pdf;

class GrnController extends Controller
{
    use TrimFields;
    use HasModulePermission;
    public function checkGrnEntry($inventoryId)
    {
        try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('GRN', 'add', '1');
            }
            ManualPOController::userCurrency();
            $buyerId = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;

            $pendingManualOrder = $this->getPendingOrderDetails($buyerId, $inventoryId, 'manual_order',null,null);
            $pendingRfqOrder = $this->getPendingOrderDetails($buyerId, $inventoryId, 'rfq_order',null,null);

            $pendingManualOrderArray = $pendingManualOrder instanceof \Illuminate\Support\Collection ? $pendingManualOrder->toArray() : (is_array($pendingManualOrder) ? $pendingManualOrder : []);
            $pendingRfqOrderArray = $pendingRfqOrder instanceof \Illuminate\Support\Collection ? $pendingRfqOrder->toArray() : (is_array($pendingRfqOrder) ? $pendingRfqOrder : []);

            $pendingOrderArray = array_merge($pendingManualOrderArray, $pendingRfqOrderArray);

            $stockReturnOrder = $this->getPendingOrderDetails($buyerId, $inventoryId, 'stock_return',null,null);
            $stockReturnOrderArray = $stockReturnOrder instanceof \Illuminate\Support\Collection ? $stockReturnOrder->toArray() : (is_array($stockReturnOrder) ? $stockReturnOrder : []);

            return response()->json([
                'has_pending_order' => !empty($pendingOrderArray) || !empty($stockReturnOrderArray),
                'order_details' => $pendingOrderArray,
                'stock_return_details' => $stockReturnOrderArray,
                'inventoryId' => $inventoryId,
                'order' => !empty($pendingOrderArray),
                'stockReturn' => !empty($stockReturnOrderArray),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }
    public function getPendingOrderDetails($buyerId, $inventoryId, $orderType,$orderId=null,$poNumber=null)
    {
        if($orderType=='stock_return'){
            $pendingOrders = ReturnStock::where('buyer_id', $buyerId)
                ->where('inventory_id', $inventoryId)
                ->where('stock_return_type', '1')
                ->get();

            if ($pendingOrders->isEmpty()) {
                return [];
            }
            return $pendingOrders->flatMap(function ($stockReturn) use ($inventoryId, $buyerId,$orderType) {
                $maxGrnQty = Grn::where('inventory_id', $inventoryId)
                    ->where('company_id', $buyerId)
                    ->where('stock_id', $stockReturn->id)
                    ->sum('grn_qty');

                $stock_return_for=$stockReturn->stock_return_for;
                $grnData = Grn::where('id', $stock_return_for)->first();
                $gst_percentage = $grnData?->order_gst ?? 0;
                $rate = $grnData?->order_rate ?? 0;





                if ((float)($maxGrnQty ?? 0) < (float)$stockReturn->qty) {

                     return [[
                        'stock_return_id' => $stockReturn->id,
                        'stock_return_for' => $stockReturn->stock_return_for,
                        'order_type' => $orderType,
                        'stock_no' => $stockReturn->stock_no,
                        'remarks' => $stockReturn->remarks,
                        'updated_at' => Carbon::parse($stockReturn->updated_at)->format('d/m/Y'),
                        'qty' => $stockReturn->qty,
                        'show_qty' => NumberFormatterHelper::formatQty($stockReturn->qty, session('user_currency')['symbol'] ?? '₹'),
                        'gst_percentage' => $gst_percentage,
                        'rate' => $rate,
                        'buyer_rate' => $rate ?? 0,
                        'stock_vendor_name' => $stockReturn->stock_vendor_name ?? '-',
                        'grn_entered' =>  NumberFormatterHelper::formatQty($maxGrnQty,session('user_currency')['symbol'] ?? '₹' ),
                    ]];
                }
                return [];
            });

        }else if($orderType=='rfq_order'){
            $pendingOrders = Rfq::with([
                    'rfqProductVariants' => function ($query) use ($inventoryId) {
                        $query->where('inventory_id', $inventoryId)->where('inventory_status', 1);
                    },
                    'orders' => function ($query)use ($orderId,$poNumber) {
                        $query->where('order_status', 1);
                        if(!is_null($orderId)){
                            $query->where('id',$orderId);
                        }
                        if(!is_null($poNumber)){
                            $query->where('po_number', 'LIKE', '%' . $poNumber . '%');
                        }
                    },
                    'orders.order_variants',
                    'orders.order_variants.frq_variant',
                    'orders.vendor'
                ])
                ->where('record_type', 2)
                ->whereHas('rfqProductVariants', function ($query) use ($inventoryId) {
                    $query->where('inventory_id', $inventoryId)->where('inventory_status', 1);
                })
                ->get();

            if ($pendingOrders->isEmpty()) {
                return [];
            }

            $result = [];

            foreach ($pendingOrders as $rfq) {
                foreach ($rfq->orders as $order) {
                    foreach ($order->order_variants as $variant) {
                        if($variant->rfq_product_variant_id== $variant->frq_variant->id &&  $variant->frq_variant->inventory_id==$inventoryId){
                            $maxGrnQty = Grn::where('inventory_id', $inventoryId)
                                ->where('company_id', $buyerId)
                                ->where('order_id', $order->id)
                                ->sum('grn_qty');
                            $maxGetPassQty = GetPass::where('inventory_id', $inventoryId)
                                ->where('company_id', $buyerId)
                                ->where('order_id', $order->id)
                                ->sum('grn_qty');
                            $grn_buyer_rate=Grn::where('inventory_id', $inventoryId)
                                ->where('company_id', $buyerId)
                                ->where('order_id', $order->id)
                                ->MAX('grn_buyer_rate');
                            $GetPass_grn_buyer_rate=GetPass::where('inventory_id', $inventoryId)
                                        ->where('company_id', $buyerId)
                                        ->where('order_id', $order->id)
                                        ->MAX('grn_buyer_rate');
                            $latest_getpass_row = GetPass::where('inventory_id', $inventoryId)
                                        ->where('company_id', $buyerId)
                                        ->where('order_id', $order->id)
                                        ->orderBy('id', 'desc')
                                        ->first();
                            $billDate = $latest_getpass_row?->bill_date ? \Carbon\Carbon::parse($latest_getpass_row->bill_date)->format('d/m/Y') : '';

                            if ((float)($maxGrnQty ?? 0) < (float) $variant->order_quantity) {
                                $currency = $order->vendor_currency?? '₹';
                                if ($currency === 'NPR') {
                                    $currency = 'रु';
                                }


                                $result[] = [
                                    'product_name' => $variant->product->product_name ?? '',
                                    'specification' => TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($variant->frq_variant->inventory->specification ?? '')),
                                    'size' => TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($variant->frq_variant->inventory->size ?? '')),
                                    'inventory_id' => $inventoryId,

                                    'id' => $order->id,
                                    'order_type' => $orderType,
                                    'order_number' => $order->po_number,
                                    'rfq_number' => $rfq->rfq_id,
                                    'order_date' => $order->created_at?->format('d/m/Y'),
                                    'order_quantity' => $variant->order_quantity,
                                    'show_order_quantity' => NumberFormatterHelper::formatQty($variant->order_quantity, session('user_currency')['symbol'] ?? '₹'),
                                    'vendor_name' => $order->vendor->legal_name ?? 'N/A',
                                    'grn_entered' => NumberFormatterHelper::formatQty($maxGrnQty, session('user_currency')['symbol'] ?? '₹'),
                                    'ratewithcurrency' => NumberFormatterHelper::formatCurrency($variant->order_price, $currency),
                                    'rate_in_local_currency' => $this->RequiredLocalCurrencyOrNot($currency, $orderType),
                                    'currency_symbol' => $currency,
                                    'grn_buyer_rate' => ($grn_buyer_rate > 0)? NumberFormatterHelper::formatQty($grn_buyer_rate, session('user_currency')['symbol'] ?? '₹'): 0,
                                    'rate' => rtrim(rtrim(number_format($variant->order_price, 2, '.', ''), '0'), '.'),
                                    'buyer_rate' => rtrim(rtrim(number_format($grn_buyer_rate, 2, '.', ''), '0'), '.'),
                                    'grn_quantity' => $variant->grn_quantity ?? 0,
                                    'invoice_number' => $latest_getpass_row?->vendor_invoice_number ?? '',
                                    'vehicle_lr_number' => $latest_getpass_row?->vehicle_no_lr_no?? '',
                                    'gross_weight' => $latest_getpass_row?->gross_wt ?? '',
                                    'transporter_name' => $latest_getpass_row?->transporter_name ?? '',
                                    'bill_date' => $billDate,
                                    'gst' => $variant->product_gst ?? 0,
                                    'gst_percentage' => $variant->product_gst ?? 0,
                                    'freight_charges' => $latest_getpass_row?->frieght_other_charges ?? 0,
                                    'approved_by' => $latest_getpass_row?->approved_by ?? '',
                                    'baseManualPoUrl' => route('buyer.rfq.order-confirmed.view', ['id' => '__ID__']),
                                    'grn_qty' => $maxGetPassQty
                                ];
                            }
                        }
                    }
                }
            }

            return $result;

        }else{
            $query = ManualOrder::where('buyer_id', $buyerId)
                        ->where('order_status', 1)
                        ->whereHas('products', function ($query) use ($inventoryId) {
                            $query->where('inventory_id', $inventoryId);
                        })
                        ->with([
                            'products' => function ($query) use ($inventoryId) {
                                $query->where('inventory_id', $inventoryId);
                            },
                            'vendor'
                        ]);
            if(!is_null($orderId)){
                $query->where('id',$orderId);
            }
            if(!is_null($poNumber)){
                $query->where('manual_po_number', 'LIKE', '%' . $poNumber . '%');
            }
            $pendingOrders = $query->get();
            if ($pendingOrders->isEmpty()) {
                return [];
            }
            return $pendingOrders->flatMap(function ($order) use ($inventoryId, $orderType, $buyerId,$orderId,$poNumber) {
                return $order->products->map(function ($product) use ($order, $inventoryId, $orderType, $buyerId,$orderId,$poNumber) {
                    $maxGrnQty = Grn::where('inventory_id', $inventoryId)
                        ->where('company_id', $buyerId)
                        ->where('order_id', $product->manual_order_id)
                        ->sum('grn_qty');
                    $maxGetPassQty = GetPass::where('inventory_id', $inventoryId)
                                ->where('company_id', $buyerId)
                                ->where('order_id', $order->id)
                                ->sum('grn_qty');
                    $grn_buyer_rate=Grn::where('inventory_id', $inventoryId)
                                ->where('company_id', $buyerId)
                                ->where('order_id', $product->manual_order_id)
                                ->MAX('grn_buyer_rate');
                    $GetPass_grn_buyer_rate=GetPass::where('inventory_id', $inventoryId)
                                        ->where('company_id', $buyerId)
                                        ->where('order_id', $order->id)
                                        ->MAX('grn_buyer_rate');
                    $latest_getpass_row = GetPass::where('inventory_id', $inventoryId)
                                        ->where('company_id', $buyerId)
                                        ->where('order_id', $order->id)
                                        ->orderBy('id', 'desc')
                                        ->first();

                    $billDate = $latest_getpass_row?->bill_date ? \Carbon\Carbon::parse($latest_getpass_row->bill_date)->format('d/m/Y') : '';

                    $grn_buyer_rate=($grn_buyer_rate > $GetPass_grn_buyer_rate)?$grn_buyer_rate:$GetPass_grn_buyer_rate;
                    if ((float)($maxGrnQty ?? 0) < (float)$product->product_quantity) {
                        $currency = $order->currencyDetails?->currency_symbol ?? '₹';

                        return [
                            'product_name' => $product->inventory->product->product_name ?? $product->inventory->buyer_product_name,
                            'specification' => TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($product->inventory->specification ?? '')),
                            'size' => TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($product->inventory->size ?? '')),
                            'inventory_id' => $inventoryId,

                            'id' => $product->manual_order_id,
                            'order_type' => $orderType,
                            'order_number' => $order->manual_po_number,
                            'rfq_number' => $order->rfq_number,
                            'order_date' => $order->created_at->format('d/m/Y'),
                            'order_quantity' => $product->product_quantity,
                            'show_order_quantity' => NumberFormatterHelper::formatQty($product->product_quantity, session('user_currency')['symbol'] ?? '₹'),
                            'vendor_name' => $order->vendor->legal_name ?? 'N/A',
                            'grn_entered' => NumberFormatterHelper::formatQty($maxGrnQty,session('user_currency')['symbol'] ?? '₹' ),
                            'ratewithcurrency' => NumberFormatterHelper::formatCurrency($product->product_price, $currency),
                            'rate' =>rtrim(rtrim(number_format($product->product_price, 2, '.', ''), '0'), '.'),
                            'buyer_rate' =>rtrim(rtrim(number_format($grn_buyer_rate, 2, '.', ''), '0'), '.'),
                            'rate_in_local_currency' => $this->RequiredLocalCurrencyOrNot($currency, $orderType),
                            'currency_symbol' => $currency,
                            'grn_buyer_rate' => ($grn_buyer_rate > 0)? NumberFormatterHelper::formatQty($grn_buyer_rate, session('user_currency')['symbol'] ?? '₹'): 0,
                            'grn_quantity' => $product->grn_quantity ?? 0,
                            'invoice_number' => $latest_getpass_row?->vendor_invoice_number ?? '',
                            'vehicle_lr_number' => $latest_getpass_row?->vehicle_no_lr_no?? '',
                            'gross_weight' => $latest_getpass_row?->gross_wt ?? '',
                            'transporter_name' => $latest_getpass_row?->transporter_name ?? '',
                            'bill_date' => $billDate,
                            'gst' =>$product->product_gst ?? 0,
                            'gst_percentage' => Tax::find($product->product_gst ?? 0)->tax ?? 0,
                            'freight_charges' => $latest_getpass_row?->frieght_other_charges ?? 0,
                            'approved_by' => $latest_getpass_row?->approved_by ?? '',
                            'baseManualPoUrl' => route('buyer.report.manualPO.orderDetails', ['id' => '__ID__']),
                            'grn_qty' => $maxGetPassQty
                        ];
                    }
                    return null;
                })->filter();
            });
        }
    }
    private function getVendorCurrency($vendorId)
    {
        $user = User::with('currencyDetails')->find($vendorId);

        if ($user && $user->currencyDetails) {
            $symbol = $user->currencyDetails->currency_symbol;
        } else {
            $symbol = '₹';
        }
        return $symbol;
    }

    private function RequiredLocalCurrencyOrNot($vendorCurrency, $orderType)
    {
        $sessionCurrency = session('user_currency')['symbol'] ?? '₹';

        $currencyMap = [
            // Nepal
            'NPR' => 'रु',
            'रु'  => 'रु',
            '₹'   => '₹',
            '$'   => '$'
        ];

        $vendorSymbol  = $currencyMap[$vendorCurrency] ?? $vendorCurrency;
        $sessionSymbol = $currencyMap[$sessionCurrency] ?? $sessionCurrency;

        if ($vendorSymbol === $sessionSymbol) {
            return '0';
        } else {
            return '1';
        }
    }

    //---------------------------------Store GRN---------------------------------------------------------------

    public function store(Request $request)
    {
        if (Auth::user()->parent_id != 0) {
            $this->ensurePermission('GATE_PASS_ENTRY', 'add', '1');
        }
        $request = $this->trimAndReturnRequest($request);
        $this->validateRequest($request);

        $companyId = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;
        $userId = Auth::user()->id;

        $inventoryId = $request->inventory_id;

        $grnQtys = collect($request->grn_qty)->filter(fn($qty) => is_numeric($qty));
        $grnStockReturnQtys = collect($request->grn_stock_return_qty)->filter(fn($qty) => is_numeric($qty));

        $allQtys = $grnQtys->concat($grnStockReturnQtys)->values();

        $this->checkEmptyGrnOrZeroValueGrn($allQtys);

        $orderIds = collect($request->order_id)->filter(fn($id) => is_numeric($id));
        $stockIds = collect($request->stock_return_id)->filter(fn($id) => is_numeric($id));
        $grnTypes = collect($request->grn_type)->filter(fn($grn_type) => is_numeric($grn_type));
        $stockReturnGrnTypes = collect($request->stock_return_grn_type)->filter(fn($grn_type) => is_numeric($grn_type));

        //order grn insert
        foreach ($grnQtys as $index => $qty) {
            if($grnTypes[$index]=='4'){
                $this->checkMaxQtyForManualOrderGrn(floatval($qty), $inventoryId, $orderIds[$index] ?? null);
                $nextGrnNumber = Grn::getNextGrnNumber($companyId);
                $validData = $this->buildValidDataForOrderRow($request, collect([$index => $qty]), $inventoryId, $companyId, $userId, $nextGrnNumber);

                if (empty($validData)) {
                    return $this->errorResponse('grn_qty', 'No valid GRN quantities provided.');
                }
                Grn::insert($validData);
            }
            if($grnTypes[$index]=='1'){
                $this->checkMaxQtyForRfqGrn(floatval($qty), $inventoryId, $orderIds[$index] ?? null);
                $nextGrnNumber = Grn::getNextGrnNumber($companyId);
                $validData = $this->buildValidDataForOrderRow($request, collect([$index => $qty]), $inventoryId, $companyId, $userId, $nextGrnNumber);

                if (empty($validData)) {
                    return $this->errorResponse('grn_qty', 'No valid GRN quantities provided.');
                }

                Grn::insert($validData);
               }
        }

        //stock return grn insert
        foreach ($grnStockReturnQtys as $index => $qty) {
            if($stockReturnGrnTypes[$index]=='3'){
                $this->checkMaxQtyForStockReturnGrn(floatval($qty), $inventoryId, $stockIds[$index] ?? null);
                $nextGrnNumber = Grn::getNextGrnNumber($companyId);
                $validData = $this->buildValidDataForStockReturn($request, collect([$index => $qty]), $inventoryId, $companyId, $userId, $nextGrnNumber);

                if (empty($validData)) {
                    return $this->errorResponse('grn_qty', 'No valid GRN quantities provided.');
                }

                Grn::insert($validData);
            }
        }

        $this->closeIndent($inventoryId);
        return response()->json([
            'status' => true,
            'message' => 'GRN quantity updated successfully.',
        ]);
    }
    public function closeIndent($inventoryId)
    {
        $indentQty = Indent::where('inventory_id', $inventoryId)
            ->where('is_deleted', 2)
            ->where('closed_indent', 2)
            ->where('is_active', 1)
            ->sum('indent_qty');

        $inventoryController = app(InventoryController::class);

        $totalGrnQty = Grn::where('inventory_id', $inventoryId)
                ->where('grn_type', 1)
                ->where('inv_status', 1)
                ->SUM('grn_qty');

        $inventoryController->preloadRfqData([$inventoryId]);
        $rfqQty = $inventoryController->getRfqData($inventoryId)['rfq_qty'][$inventoryId] ?? 0;

        $inventoryController->preloadOrderData([$inventoryId]);
        $orderQty = $inventoryController->getOrderData($inventoryId)['order_qty'][$inventoryId] ?? 0;
        $inventoryController->clearAllCacheSilent([$inventoryId]);
        $maxOrderQty = $orderQty * 1.02;

        if (
            round($indentQty, 3) == round($totalGrnQty, 3) &&
            round($indentQty, 3) == round($rfqQty, 3) &&
            round($indentQty, 3) == round($orderQty, 3)
        ) {
            $this->closeInventoryIndent($inventoryId);
        }elseif (
            round($indentQty, 3) == round($rfqQty, 3) &&
            round($indentQty, 3) == round($orderQty, 3) &&
            round($orderQty, 3) < round($totalGrnQty, 3) &&
            round($totalGrnQty, 3) <= round($maxOrderQty, 3)
        ) {
            $rfqProductVariants = DB::table('rfq_product_variants')
            ->join('order_variants', function ($join) {
                $join->on('rfq_product_variants.id', '=', 'order_variants.rfq_product_variant_id')
                    ->on('rfq_product_variants.product_id', '=', 'order_variants.product_id');
            })
            ->join('orders', function ($join) {
                $join->on('orders.id', '=', 'order_variants.id')
                    ->on('orders.po_number', '=', 'order_variants.po_number');
            })
            ->where('rfq_product_variants.inventory_id', $inventoryId)
            ->where('rfq_product_variants.inventory_status', 1)
            ->select(
                'order_variants.order_quantity',
                'orders.id as order_id',
                DB::raw('(order_variants.order_quantity * 1.02) as max_quantity')
            )
            ->get();


            $exceeded = false;
            foreach ($rfqProductVariants as $variant) {
                $orderId = $variant->order_id;
                $maxQty = round($variant->max_quantity,3);
                $order_quantity = $variant->order_quantity;

                $totalGrnQty = DB::table('grns')
                    ->where('order_id', $orderId)
                    ->where('inventory_id', $inventoryId)
                    ->where('grn_type', 1)
                    ->where('inv_status', 1)
                    ->sum('grn_qty');
                if ($totalGrnQty < $order_quantity || $totalGrnQty > $maxQty) {

                    $exceeded = true;
                    break;
                }
            }

            if (!$exceeded) {
                $this->closeInventoryIndent($inventoryId);
            }
        }

    }
    protected function closeInventoryIndent(int $inventoryId)
    {
        Indent::where('inventory_id', $inventoryId)->where('is_deleted', 2)->where('is_active', 1)->update([
            'inv_status' => 2,
            'closed_indent' => 1
        ]);

        RfqProductVariant::where('inventory_id', $inventoryId)->update([
            'inventory_status' => 2
        ]);

        Grn::where('inventory_id', $inventoryId)->update([
            'inv_status' => 2
        ]);

        Issued::where('inventory_id', $inventoryId)->update([
            'inv_status' => 2
        ]);

        Inventories::where('id', $inventoryId)->update([
            'is_indent' => 2
        ]);

        $inventoryController = app(InventoryController::class);
        $inventoryController->clearAllCacheSilent([$inventoryId]);
    }
    public function errorResponse($field, $message)
    {
        return response()->json([
            'status' => false,
            'errors' => [$field => [$message]],
        ], 422);
    }
    public function validateRequest(Request $request)
    {
        $request->validate([
            'inventory_id' => 'required|exists:inventories,id',

            // GRN set
            'grn_qty'     => 'sometimes|array',
            'grn_qty.*'   => ['nullable', 'string', 'max:20', 'min:0.001', new NoSpecialCharacters(false)],

            'order_qty'   => 'sometimes|array',
            'grn_entered' => 'sometimes|array',

            // Common optional fields
            'invoice_number.*'    => ['nullable', 'string', 'max:50', new NoSpecialCharacters(true)],
            'vehicle_lr_number.*' => ['nullable', 'string', 'max:20', new NoSpecialCharacters(true)],
            'gross_weight.*'      => ['nullable', 'string', 'max:20', new NoSpecialCharacters(false)],
            'gst.*'               => ['nullable', 'string', 'max:20', new NoSpecialCharacters(false)],
            'freight_charges.*'   => ['nullable', 'string', 'max:20', new NoSpecialCharacters(false)],
            'approved_by.*'       => ['nullable', 'string', 'max:255', new NoSpecialCharacters(false)],

            // Stock Return set
            'grn_stock_return_qty'     => 'sometimes|array',
            'grn_stock_return_qty.*'   => ['nullable', 'string', 'max:20', 'min:0.001', new NoSpecialCharacters(false)],

            'stock_return_qty'         => 'sometimes|array',
            'stock_return_grn_entered' => 'sometimes|array',

            // 'rate_in_local_currency.*' => 'nullable|numeric',
        ]);

        $rules=[];
        $messages=[];
        foreach($request->currency_symbol??[] as $i=>$c)
            {
                $q=$request->grn_qty[$i]??null;
                if(!empty($q)&&$this->RequiredLocalCurrencyOrNot($c,$request->grn_type[$i]??null))
                    {
                        $rules["rate_in_local_currency.$i"]=['required','regex:/^[\d,]+(\.\d+)?$/'];$r=$i+1;$messages["rate_in_local_currency.$i.required"]="Rate in local currency required at row $r";$messages["rate_in_local_currency.$i.regex"]="Invalid number format at row $r";
                    }
            }
        if(!empty($rules))
            {
                $request->validate($rules,$messages);
            }
        if(!empty($request->rate_in_local_currency))
            {
                foreach($request->rate_in_local_currency as $i=>$v)
                    {
                        $request->merge(["rate_in_local_currency.$i"=>str_replace(',','',$v)]);
                    }
            }


        // Manual validation: At least one group must be present
        $hasGrnSet = $request->filled('grn_qty') && $request->filled('order_qty') && $request->filled('grn_entered');
        $hasStockReturnSet = $request->filled('grn_stock_return_qty') && $request->filled('stock_return_qty') && $request->filled('stock_return_grn_entered');

        if (!($hasGrnSet || $hasStockReturnSet)) {
            return back()->withErrors([
                'error' => 'At least one of GRN or Stock Return data must be provided.'
            ])->withInput();
        }
    }
    public function checkEmptyGrnOrZeroValueGrn($Qtys)
    {
        $Qtys = collect($Qtys);

        if ($Qtys->isEmpty()) {
            abort(response()->json([
                'status' => false,
                'errors' => ['grn_qty' => ['At least one GRN Quantity is required.']],
            ], 422));
        }

        if ($Qtys->contains(fn($qty) => floatval($qty) <= 0)) {
            abort(response()->json([
                'status' => false,
                'errors' => ['grn_qty' => ['GRN Quantity must be greater than 0.']],
            ], 422));
        }

        return true;
    }
    protected function checkMaxQtyForRfqGrn($grnQtys, $inventoryId, $orderId)
    {
        $grnQtys = collect($grnQtys);
        $grns = Grn::where('inventory_id', $inventoryId)
            ->where('order_id', $orderId)
            ->where('grn_type', 1)
            ->get();
        if ($grns->isEmpty()) {
            $totalGrnEntered = 0;
        } else {
            $totalGrnEntered = $grns->sum('grn_qty');
        }
        $productId = Inventories::where('id', $inventoryId)->value('product_id');
        $rfqProductVariant = RfqProductVariant::join('orders', 'rfq_product_variants.rfq_id', '=', 'orders.rfq_id')
            ->where('rfq_product_variants.inventory_id', $inventoryId)
            ->where('orders.id', $orderId)
            ->select('rfq_product_variants.id', 'orders.po_number', 'orders.rfq_id')
            ->first();

        $RfqProductVariantId = $rfqProductVariant->id ?? null;
        $poNumber = $rfqProductVariant->po_number ?? null;
        $rfqId = $rfqProductVariant->rfq_id ?? null;

        $orderQty = OrderVariant::where('product_id', $productId)
            ->where('po_number', $poNumber)
            ->where('rfq_product_variant_id', $RfqProductVariantId)
            ->value('order_quantity') ?? 0;

        $maxOrderQty = $orderQty * 1.02;
        $maxGrnQty =  round($maxOrderQty - $totalGrnEntered, 3);
        $exceedsMaxQty = $grnQtys->filter(function($qty) use ($maxGrnQty) {
            return floatval($qty) > floatval($maxGrnQty);
        })->isNotEmpty();
        if ($exceedsMaxQty) {
            abort(response()->json([
                'status' => false,
                'errors' => ['grn_qty' => ['GRN Quantity exceeds allowed 2% over Order Qty. Max GRN Qty: ' . $maxGrnQty]],
            ], 422));
        }
    }
    protected function checkMaxQtyForManualOrderGrn($grnQtys, $inventoryId, $orderId)
    {
        $grnQtys = collect($grnQtys);
        $grns = Grn::where('inventory_id', $inventoryId)
        ->where('order_id', $orderId)
        ->where('grn_type', 4)
        ->get();

        if ($grns->isEmpty()) {
            $totalGrnEntered = 0;
        } else {
            $totalGrnEntered = $grns->sum('grn_qty');
        }
        $manualOrderProduct = ManualOrderProduct::where('inventory_id', $inventoryId)
        ->where('manual_order_id', $orderId)
        ->first();

        $orderQty = $manualOrderProduct?->product_quantity ?? 0;
        $maxOrderQty = $orderQty * 1.02 ;
        $maxGrnQty =  round($maxOrderQty - $totalGrnEntered, 3);
        $exceedsMaxQty = $grnQtys->filter(function($qty) use ($maxGrnQty) {
            return floatval($qty) > floatval($maxGrnQty);
        })->isNotEmpty();
        if ($exceedsMaxQty) {
            abort(response()->json([
                'status' => false,
                'errors' => ['grn_qty' => ['GRN Quantity exceeds allowed 2% over Order Qty. Max GRN Qty: ' . $maxGrnQty]],
            ], 422));
        }
    }
    protected function checkMaxQtyForStockReturnGrn($grnQtys, $inventoryId, $stockId)
    {
        $grnQtys = collect($grnQtys);
        $grns = Grn::where('inventory_id', $inventoryId)
            ->where('stock_id', $stockId)
            ->where('grn_type', 3)
            ->get();

        $totalGrnEntered = $grns->isEmpty() ? 0 : $grns->sum('grn_qty');
        $orderQty = ReturnStock::where('inventory_id', $inventoryId)->where('id', $stockId)->value('qty')?? 0;
        $maxOrderQty = $orderQty * 1.02;
        $maxGrnQty =  round($maxOrderQty - $totalGrnEntered, 3);
        $exceedsMaxQty = $grnQtys->filter(function($qty) use ($maxGrnQty) {
            return floatval($qty) > floatval($maxGrnQty);
        })->isNotEmpty();
        if ($exceedsMaxQty) {
            abort(response()->json([
                'status' => false,
                'errors' => ['grn_qty' => ['GRN Quantity exceeds allowed 2% over Order Qty. Max GRN Qty: ' . $maxGrnQty]],
            ], 422));
        }
    }
    protected function buildValidDataForOrderRow($request, $grnQtys, $inventoryId, $companyId, $userId, $nextGrnNumber)
    {
        $validData = [];

        $fields = ['invoice_number', 'bill_date', 'transporter_name', 'vehicle_lr_number', 'gross_weight', 'freight_charges', 'approved_by'];

        $length = count($request->grn_qty);

        foreach ($fields as $field) {
            $fieldName = str_replace('[]', '', $field);
            $value = $request->{$fieldName} ?? null;

            if (!is_array($value)) {
                $request->{$fieldName} = array_fill(0, $length, $value);
            } else {
                $arrLength = count($value);
                if ($arrLength < $length) {
                    $fillValue = $value[0] ?? null;
                    $request->{$fieldName} = array_pad($value, $length, $fillValue);
                }
            }

            $request->{$fieldName} = array_map(function($v) {
                if (is_array($v)) return reset($v);
                return $v;
            }, $request->{$fieldName});
        }

        foreach ($grnQtys as $index => $qty) {
            $grnQty = number_format(floatval($qty), 3, '.', '');
            $orderQty = number_format(floatval($request->order_qty[$index] ?? 0), 3, '.', '');
            $enteredQty = number_format(floatval($request->grn_entered[$index] ?? 0), 3, '.', '');
            $remainingQty = number_format(($orderQty * 1.02) - $enteredQty, 3, '.', '');
            $orderId = $request->order_id[$index];
            $grnType = $request->grn_type[$index];

            if ($grnType == 1) {
                $orderStatus = Order::where('id', $orderId)->value('order_status');
            } elseif ($grnType == 4) {
                $orderStatus = ManualOrder::where('id', $orderId)->value('order_status');
            } else {
                abort(response()->json([
                    'status' => false,
                    'message' => 'Invalid GRN type.',
                ], 422));
            }

            if ($orderStatus == 2) {
                abort(response()->json([
                    'status' => false,
                    'message' => 'Order is already Cancelled. Please refresh the GRN details form.',
                ], 422));
            }

            if ($this->bccomp_fallback($grnQty, '0', 3) === 1 && $this->bccomp_fallback($grnQty, $remainingQty, 3) <= 0) {
                $rate = $request->rate[$index] ?? null;

                if (!is_numeric($rate)) {
                    abort(response()->json([
                        'status' => false,
                        'message' => 'Rate on row ' . ($index + 1) . ' must be a numeric value.',
                    ], 422));
                }

                if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $rate)) {
                    abort(response()->json([
                        'status' => false,
                        'message' => 'Rate on row ' . ($index + 1) . ' is out of range (must be max 99,999,999.99).',
                    ], 422));
                }

                $validData[] = [
                    'inventory_id'     => $inventoryId,
                    'grn_qty'          => $grnQty,
                    'order_id'         => $orderId,
                    'po_number'        => $request->po_number[$index],
                    'company_id'       => $companyId,
                    'updated_by'       => $userId,
                    'grn_no'           => $nextGrnNumber++,
                    'order_qty'        => $orderQty,
                    'rate'             => $request->rate[$index],
                    'grn_buyer_rate'   => (float) str_replace(',', '', $request->rate_in_local_currency[$index] ?? 0),
                    'grn_type'         => $request->grn_type[$index],
                    'vendor_name'      => $request->vendor_name[$index],

                    'vendor_invoice_number'   => $request->invoice_number[$index] ?? null,
                    'bill_date' => isset($request->bill_date[$index]) && $request->bill_date[$index]
                                ? \Carbon\Carbon::createFromFormat('d/m/Y', $request->bill_date[$index])->format('Y/m/d')
                                : null,
                    'transporter_name'   => $request->transporter_name[$index] ?? null,
                    'vehicle_no_lr_no'        => $request->vehicle_lr_number[$index] ?? null,
                    'gross_wt'                => $request->gross_weight[$index] ?? null,
                    'frieght_other_charges'   => $request->freight_charges[$index] ?? null,
                    'approved_by'             => $request->approved_by[$index] ?? null,

                    'gst_no'                  => $request->gst[$index] ?? null,
                    'updated_at'              => isset($request->grn_date[$index]) && $request->grn_date[$index]
                                ? \Carbon\Carbon::createFromFormat('d/m/Y', $request->grn_date[$index])->format('Y-m-d H:i:s')
                                : date('Y-m-d H:i:s'),
                ];
            }
        }
        return $validData;
    }
    protected function buildValidDataForStockReturn($request, $grnStockReturnQtys, $inventoryId, $companyId, $userId, $nextGrnNumber)
    {
        $validData = [];

        foreach ($grnStockReturnQtys as $index => $qty) {
            $grnQty = number_format(floatval($qty), 3, '.', '');
            $orderQty = number_format(floatval($request->stock_return_qty[$index] ?? 0), 3, '.', '');
            $enteredQty = number_format(floatval($request->stock_return_grn_entered[$index] ?? 0), 3, '.', '');

            $remainingQty = number_format(($orderQty * 1.02) - $enteredQty, 3, '.', '');
            $stock_id = $request->stock_return_id[$index];
            $stock_return_for = $request->stock_return_for[$index];

            if ($this->bccomp_fallback($grnQty, '0', 3) === 1 && $this->bccomp_fallback($grnQty, $remainingQty, 3) <= 0) {

                $validData[] = [
                    'inventory_id'     => $inventoryId,
                    'grn_qty'          => $grnQty,
                    'order_id'         => '0',
                    'po_number'        => '',
                    'company_id'       => $companyId,
                    'stock_id'         => $stock_id,
                    'stock_return_for' => $stock_return_for,
                    'updated_by'       => $userId,
                    'grn_no'           => $nextGrnNumber,
                    'grn_type'         => $request->stock_return_grn_type[$index],
                    'vendor_name'      => $request->stock_vendor_name[$index],
                    'vendor_invoice_number'   => $request->stock_invoice_number[$index] ?? null,
                    'bill_date' => isset($request->stock_bill_date[$index]) && $request->stock_bill_date[$index]
                                ? \Carbon\Carbon::createFromFormat('d/m/Y', $request->stock_bill_date[$index])->format('Y/m/d')
                                : null,
                    'transporter_name'   => $request->stock_transporter_name[$index] ?? null,
                    'vehicle_no_lr_no'        => $request->stock_vehicle_lr_number[$index] ?? null,
                    'gross_wt'                => $request->stock_gross_weight[$index] ?? null,
                    'gst_no'                  => $request->stock_gst[$index] ?? null,
                    'frieght_other_charges'   => $request->stock_freight_charges[$index] ?? null,
                    'approved_by'             => $request->stock_approved_by[$index] ?? null,
                    'updated_at'              => isset($request->stock_grn_date[$index]) && $request->stock_grn_date[$index]
                                ? \Carbon\Carbon::createFromFormat('d/m/Y', $request->stock_grn_date[$index])->format('Y-m-d H:i:s')
                                : date('Y-m-d H:i:s'),
                ];
            }
        }

        return $validData;
    }
    public function bccomp_fallback($left, $right, $scale = 2) {
        $left = round((float) $left, $scale);
        $right = round((float) $right, $scale);

        if ($left < $right) return -1;
        if ($left > $right) return 1;
        return 0;
    }

    //--------------------------GRN REPORT TABLE & EXCEL------------------------------------------------------
    public function grnReportlistdata(Request $request)
    {
        if (!$request->ajax()) return;
        $filteredQuery = $this->getFilteredQuery($request);
        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginated = $filteredQuery->paginate($perPage, ['*'], 'page', $page);

        $grns = collect($paginated->items());

        $data = $grns->map(function ($grn) {
            $currencySymbol = session('user_currency')['symbol'] ?? '₹';
            return [
                'grn_no' => $grn->grn_no,
                'product' => optional($grn->inventory->product)->product_name ?? optional($grn->inventory)->buyer_product_name,
                'buyer_product_name' => optional($grn->inventory)->buyer_product_name ?? '',
                'specification' => TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($grn->inventory->specification)),
                'size' => TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($grn->inventory->size)),
                'inventory_grouping' => TruncateWithTooltipHelper::wrapText($grn->inventory->inventory_grouping),
                'vendor_name' => TruncateWithTooltipHelper::wrapText($grn->vendor_name??''),
                'vendor_invoice_no' => TruncateWithTooltipHelper::wrapText($grn->vendor_invoice_number),
                'vehicle_no_lr_no' => TruncateWithTooltipHelper::wrapText($grn->vehicle_no_lr_no),
                'gross_wt' => TruncateWithTooltipHelper::wrapText($grn->gross_wt),
                'gst_no' => TruncateWithTooltipHelper::wrapText($grn->gst_no),
                'frieght_other_charges' => TruncateWithTooltipHelper::wrapText($grn->frieght_other_charges),
                'added_by' => TruncateWithTooltipHelper::wrapText(optional($grn->updatedBy)->name),
                'added_date' => $grn->updated_at ? Carbon::parse($grn->updated_at)->format('d/m/Y') : '',
                'grn_qty' =>'<span class="grn-entry-details" style="cursor:pointer;color:blue;" data-id="'.$grn->id.'" >'.NumberFormatterHelper::formatQty($grn->grn_qty,$currencySymbol) .'</span>',
                'uom' => $grn->inventory->uom->uom_name ?? '',
                'amount' => NumberFormatterHelper::formatCurrency($grn->order_rate*$grn->grn_qty,$currencySymbol),
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $paginated->total(),
            'recordsFiltered' => $paginated->total(),
            'data' => $data,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);
    }
   
    public function exportTotalGrnReport(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $total = $query->count();
        return response()->json(['total' => $total]);
    }

    public function exportBatchGrnReport(Request $request)
    {
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));
        $query = $this->getFilteredQuery($request);
        $results = $query->offset($offset)->limit($limit)->get();
        $result = [];
        $totalAmount = 0;
        $currency=session('user_currency')['symbol'] ?? '₹';
        foreach ($results as $index => $grn) {
                try {
                    $billDate = $grn->bill_date ? \Carbon\Carbon::parse($grn->bill_date)->format('d/m/Y') : '';
                } catch (\Exception $e) {
                    $billDate = '';
                }
                $amount = $grn->order_rate * $grn->grn_qty;
                $totalAmount += round($amount, 2);
                $result[] = [
                    $grn->grn_no,
                    $grn->po_number . ' ' . (strtotime($grn->created_at) ? \Carbon\Carbon::parse($grn->created_at)->format('d/m/Y') : ''),
                    optional($grn->inventory->branch)->name ?? '',
                    optional($grn->inventory->product)->product_name ?? $grn->inventory->buyer_product_name,
                    $grn->inventory->buyer_product_name ?? '',
                    cleanInvisibleCharacters($grn->inventory->specification ?? ''),
                    cleanInvisibleCharacters($grn->inventory->size ?? ''),
                    $grn->inventory->inventory_grouping ?? '',
                    $grn->vendor_name,
                    $grn->vendor_invoice_number,
                    $billDate,
                    $grn->transporter_name,
                    $grn->vehicle_no_lr_no,
                    " ".$grn->gross_wt,
                    " ".$grn->gst_no,
                    " ".$grn->frieght_other_charges,
                    $grn->updated_at ? $grn->updated_at->format('d/m/Y') : '',
                    optional($grn->updatedBy)->name?? '',
                    $grn->updated_at ? $grn->updated_at->format('d/m/Y') : '',
                    " ".NumberFormatterHelper::formatQty($grn->grn_qty, $currency),
                    optional($grn->inventory->uom)->uom_name ?? '',
                    " ".NumberFormatterHelper::formatCurrency($grn->order_rate,$currency),
                    " ".NumberFormatterHelper::formatCurrency($grn->order_rate*$grn->grn_qty,$currency),
                ];

        }
        $result[] = [
                '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '','','',
            ];
        $result[] = [
                '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
                'Total Amount('.$currency.'):',
                " ".NumberFormatterHelper::formatCurrency($totalAmount,$currency),
            ];
        return response()->json(['data' => $result]);
    }
    public function getFilteredQuery(Request $request)
    {
        if (session('branch_id') != $request->branch_id) {
            session(['branch_id' => $request->branch_id]);
        }

        $query = Grn::with(['inventory', 'company', 'inventory.product','updatedBy','manualOrder.vendor','manualOrderProduct']);

        $query->when(
            $request->company_id == (Auth::user()->parent_id ?? Auth::user()->id),
            fn($q) => $q->where('company_id', Auth::user()->parent_id ?? Auth::user()->id)
        );

        $query->when($request->filled(['from_date', 'to_date']), function ($q) use ($request) {
            $from = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay();
            $to = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay();
            $q->whereBetween('updated_at', [$from, $to]);
        });
        $query->when($request->search_product_name, function ($q, $val) {
            $q->where(function ($subQuery) use ($val) {
                $subQuery->whereHas('inventory', function ($q1) use ($val) {
                    $q1->where('buyer_product_name', 'like', "%$val%");
                })
                ->orWhereHas('inventory.product', function ($q2) use ($val) {
                    $q2->where('product_name', 'like', "%$val%");
                });
            });
        });


        $query->when($request->search_category_id, function ($q, $val) {
            $cat_ids = InventoryController::getIdsByCategoryName($val);
            if ($cat_ids) {
                $q->whereHas('inventory.product', fn($p) => $p->whereIn('category_id', $cat_ids));
            }
        });

        $query->when($request->branch_id, function ($q, $val) {
            $q->whereHas('inventory.branch', fn($b) => $b->where('branch_id', $val));
        });

        return $query->orderByDesc('id')->orderByDesc('updated_at');
    }

    //------------------------------IN GRN REPORT FETCH DETAILS AND EDIT DETAILS---------------------------------------
    public function fetchGrnRowdata(Request $request)
    {
        $grn = Grn::with(['inventory', 'manualOrder', 'manualOrderProduct', 'stock', 'order'])->find($request->id);

        if (!$grn) {
            return response()->json(['html' => '<p class="text-danger">GRN not found.</p>']);
        }

        // Base data
        $data = [
            'id' => $grn->id,
            'grn_qty' => NumberFormatterHelper::formatQty($grn->grn_qty, session('user_currency')['symbol'] ?? '₹') ?? '',
            'vendor_invoice_no' => $grn->vendor_invoice_number ?? '',
            'bill_date' => $grn->bill_date ? Carbon::parse($grn->bill_date)->format('d/m/Y') : '',
            'transporter_name' => $grn->transporter_name ?? '',
            'gross_wt' => $grn->gross_wt ?? '',
            'vehicle_no_lr_no' => $grn->vehicle_no_lr_no ?? '',
            'gst_no' => $grn->gst_no ?? '',
            'frieght_other_charges' => $grn->frieght_other_charges ?? '',
            'approved_by' => $grn->approved_by ?? '',
        ];

        // Type-specific data
        if ($grn->grn_type == 3 && $grn->stock) {
            $data += [
                'stock_return_no' => $grn->stock->stock_no ?? '',
                'stock_return_date' => $grn->stock->updated_at ? Carbon::parse($grn->stock->updated_at)->format('d/m/Y') : '',
                'stock_return_qty' => $grn->stock->qty ?? '',
                'vendor_name' => $grn->stock->vendor_name ?? '',
            ];
        } else {
            $data += [
                'order_no' => $grn->po_number ?? '',
                'rfq_no' => $grn->rfq_id ?? '-',
                'order_date' => $grn->created_at ? Carbon::parse($grn->created_at)->format('d/m/Y') : '',
                'order_qty' => $grn->order_qty ?? '',
                'vendor_name' => $grn->vendor_name ?? '',
                'grn_no' => $grn->grn_no ?? '',
                'grn_date' => $grn->updated_at ? Carbon::parse($grn->updated_at)->format('d/m/Y') : '',
            ];
        }

        return response()->json($data);

    }
    public function editGrnRowdata(Request $request)
    {
         try {
            if (Auth::user()->parent_id != 0) {
                $this->ensurePermission('GRN', 'edit', '1');
            }
            $request = $this->trimAndReturnRequest($request);
            $request->validate([
                'id' => 'required|exists:grns,id',
                'invoice_number'     => ['nullable', 'string', 'max:50', new NoSpecialCharacters(true)],
                'bill_date'          => ['nullable', 'string', 'max:50', new NoSpecialCharacters(true)],
                'transporter_name'    => ['nullable', 'string', 'max:255', new NoSpecialCharacters(false)],
                'vehicle_lr_number'  => ['nullable', 'string', 'max:20', new NoSpecialCharacters(true)],
                'gross_weight'       => ['nullable', 'string', 'max:20', new NoSpecialCharacters(false)],
                'gst'                => ['nullable', 'string', 'max:20', new NoSpecialCharacters(false)],
                'freight_charges'    => ['nullable', 'string', 'max:20', new NoSpecialCharacters(false)],
                'approved_by'        => ['nullable', 'string', 'max:255', new NoSpecialCharacters(false)],
            ]);

            try {
                $grn = Grn::findOrFail($request->id);

                $grn->vendor_invoice_number = $request->invoice_number;
                if (!empty($request->bill_date)) {
                    $billDate = str_replace('-', '/', $request->bill_date);

                    try {
                        $grn->bill_date = Carbon::createFromFormat('d/m/Y', $billDate)->format('Y/m/d');
                    } catch (\Exception $e) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Invalid bill date format. Correct format: d/m/Y'
                        ]);
                    }
                }
                $grn->transporter_name = $request->transporter_name;
                $grn->vehicle_no_lr_no = $request->vehicle_lr_number;
                $grn->gross_wt = $request->gross_weight;
                $grn->gst_no = $request->gst;
                $grn->frieght_other_charges = $request->freight_charges;
                $grn->approved_by = $request->approved_by;
                $grn->save();

                return response()->json(['status' => 'success', 'message' => 'GRN updated successfully.']);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Failed to update GRN.']);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->getMessage()
            ], 403);
        }
    }
    public function downloadGrnRowdata_old($id)
    {
        $buyer = User::with(['buyer.buyer_city', 'buyer.buyer_state', 'buyer.buyer_country'])
                ->find(Auth::user()->parent_id ?? Auth::id());
        $grn_no=Grn::where('id',$id)->value('grn_no');
        $grnModel = Grn::with(['inventory.product', 'manualOrder', 'manualOrderProduct', 'stock', 'order'])->where('grn_no', $grn_no)->where('company_id', Auth::user()->parent_id ?? Auth::id())->get();

        if (!$grnModel->count()) {
            abort(404, 'GRN not found');
        }
        $total_amount = $grnModel->sum(function ($grn) {
                return (float)$grn->order_rate * (float)$grn->grn_qty;
            });

        $total_gst = $grnModel->sum(function ($grn) {
                return (float)$grn->gst_no;
            });
        $grn = [
            //common fields
            'buyer_name' => $buyer?->buyer->legal_name ?? 'N/A',
            'registered_address' => $buyer?->buyer->registered_address ?? '',

            'city' => $buyer?->buyer?->buyer_city?->city_name ?? '',
            'state' => $buyer?->buyer?->buyer_state?->name ?? '',
            'country' => $buyer?->buyer?->buyer_country?->name ?? '',
            'pincode' => $buyer?->buyer->pincode ?? '',
            'grn_no' => $grnModel[0]->grn_no ?? '',
            'grn_date' => $grnModel[0]->updated_at ? Carbon::parse($grnModel[0]->updated_at)->format('d/m/Y') :'',
            'bill_date' => (!empty($grnModel[0]->bill_date) && strtotime($grnModel[0]->bill_date))
                ? Carbon::parse($grnModel[0]->bill_date)->format('d/m/Y')
                : '',
            'vendor_invoice_no' => $grnModel[0]->vendor_invoice_number ?? '',
            'transporter_name' => $grnModel[0]->transporter_name ?? '',
            'gross_wt' => $grnModel[0]->gross_wt ?? '',
            'vehicle_no_lr_no' => $grnModel[0]->vehicle_no_lr_no ?? '',
            'frieght_other_charges' => $grnModel[0]->frieght_other_charges ?? '',
            'approved_by' => $grnModel[0]->approved_by ?? '',


            //order specific fields
            'order_details' => collect($grnModel)->map(function ($grn) {
                return [
                    'order_no' => $grn->po_number,
                    'order_date' => (!empty($grn->created_at) && strtotime($grn->created_at))
                        ? Carbon::parse($grn->created_at)->format('d/m/Y')
                        : '',
                    'vendor' => $grn->vendor_name ?? '',
                'product' => $grn->inventory->product->product_name ?? $grn->inventory->buyer_product_name,
                'size' => e(cleanInvisibleCharacters($grn->inventory->size ?? '')),
                'specification' => e(cleanInvisibleCharacters($grn->inventory->specification ?? '')),
                'grn_qty' => NumberFormatterHelper::formatQty($grn->grn_qty, session('user_currency')['symbol'] ?? '₹') ?? '',
                'product_order_qty' => NumberFormatterHelper::formatQty(
                    ($grn->grn_type == 3 && $grn->stock)
                        ? $grn->stock->qty
                        : ($grn->order_qty ?? ''),
                    session('user_currency')['symbol'] ?? '₹'
                ) ?? '',
                'rate' => NumberFormatterHelper::formatCurrency($grn->order_rate, session('user_currency')['symbol'] ?? '₹') ?? '',
                'amount' => NumberFormatterHelper::formatCurrency($grn->order_rate * $grn->grn_qty, session('user_currency')['symbol'] ?? '₹') ?? '',
                'gst_no' => NumberFormatterHelper::formatCurrency($grn->gst_no ?? '', session('user_currency')['symbol'] ?? '₹'),
                ];
            })->toArray(),
            'total_amount' => NumberFormatterHelper::formatCurrency($total_amount, session('user_currency')['symbol'] ?? '₹') ?? '',
            'total_gst' => NumberFormatterHelper::formatCurrency($total_gst, session('user_currency')['symbol'] ?? '₹') ?? '',
        ];
        $pdf = Pdf::loadView('buyer.report.downloadGrnPdf', compact('grn'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont'     => 'DejaVu Sans',
                'isRemoteEnabled' => true,
            ]);

        return $pdf->download("GRN No-{$grn_no} details.pdf");
    }

    public function downloadGrnRowdata($id)
    {
        $grn_no=Grn::where('id',$id)->value('grn_no');
        $grnModel = Grn::with(['inventory.product', 'inventory.uom', 'manualOrder', 'manualOrderProduct', 'stock', 'order'])->where('grn_no', $grn_no)->where('company_id', Auth::user()->parent_id ?? Auth::id())->get();

        if (!$grnModel->count()) {
            abort(404, 'GRN not found');
        }
        $total_amount = $grnModel->sum(function ($grn) {
                return (float)$grn->order_rate * (float)$grn->grn_qty;
            });

        $total_gst = $grnModel->sum(function ($grn) {
                return (float)$grn->gst_no;
            });
        $buyerId  = $grnModel[0]->inventory->buyer_parent_id ?? null;
        $branchId = $grnModel[0]->inventory->buyer_branch_id ?? null;

        $buyer = User::with([
            'buyer.buyer_city',
            'buyer.buyer_state',
            'buyer.buyer_country',
            'buyer.branchDetails.city',
            'buyer.branchDetails.state',
            'buyer.branchDetails.country'
        ])
        ->find($buyerId);

        $branch = $buyer?->branchDetails?->where('branch_id', $branchId)->first();
        $grn = [
            //common fields
            'buyer_name' => $buyer?->buyer->legal_name ?? 'N/A',
            'registered_address' => $branch?->address
                ?? $buyer?->buyer?->registered_address
                ?? '',

            'city' => $branch?->cityRelation?->city_name
                ?? $buyer?->buyer?->buyer_city?->city_name
                ?? '',

            'state' => $branch?->stateRelation?->name
                ?? $buyer?->buyer?->buyer_state?->name
                ?? '',

            'country' => $branch?->countryRelation?->name
                ?? $buyer?->buyer?->buyer_country?->name
                ?? '',

            'pincode' => $branch?->pincode
                ?? $buyer?->buyer?->pincode
                ?? '',
            'grn_no' => $grnModel[0]->grn_no ?? '',
            'grn_date' => $grnModel[0]->updated_at ? Carbon::parse($grnModel[0]->updated_at)->format('d/m/Y') :'',
            'bill_date' => (!empty($grnModel[0]->bill_date) && strtotime($grnModel[0]->bill_date))
                ? Carbon::parse($grnModel[0]->bill_date)->format('d/m/Y')
                : '',
            'vendor_invoice_no' => $grnModel[0]->vendor_invoice_number ?? '',
            'transporter_name' => $grnModel[0]->transporter_name ?? '',
            'gross_wt' => $grnModel[0]->gross_wt ?? '',
            'vehicle_no_lr_no' => $grnModel[0]->vehicle_no_lr_no ?? '',
            'frieght_other_charges' => $grnModel[0]->frieght_other_charges ?? '',
            'approved_by' => $grnModel[0]->approved_by ?? '',


            //order specific fields
            'order_details' => collect($grnModel)->map(function ($grn) {
                return [
                    'order_no' => $grn->po_number,
                    'order_date' => (!empty($grn->created_at) && strtotime($grn->created_at))
                        ? Carbon::parse($grn->created_at)->format('d/m/Y')
                        : '',
                    'vendor' => $grn->vendor_name ?? '',
                'product' => $grn->inventory->product->product_name ?? '',
                'size' => e(cleanInvisibleCharacters($grn->inventory->size ?? '')),
                'specification' => e(cleanInvisibleCharacters($grn->inventory->specification ?? '')),
                'uom' =>  $grn->inventory?->uom?->uom_name ?? '',
                'grn_qty' => NumberFormatterHelper::formatQty($grn->grn_qty, session('user_currency')['symbol'] ?? '₹') ?? '',
                'product_order_qty' => NumberFormatterHelper::formatQty(
                    ($grn->grn_type == 3 && $grn->stock)
                        ? $grn->stock->qty
                        : ($grn->order_qty ?? ''),
                    session('user_currency')['symbol'] ?? '₹'
                ) ?? '',
                'rate' => NumberFormatterHelper::formatCurrency($grn->order_rate, session('user_currency')['symbol'] ?? '₹') ?? '',
                'amount' => NumberFormatterHelper::formatCurrency($grn->order_rate * $grn->grn_qty, session('user_currency')['symbol'] ?? '₹') ?? '',
                'gst_no' => NumberFormatterHelper::formatCurrency($grn->gst_no ?? '', session('user_currency')['symbol'] ?? '₹'),
                ];
            })->toArray(),
            'total_amount' => NumberFormatterHelper::formatCurrency($total_amount, session('user_currency')['symbol'] ?? '₹') ?? '',
            'total_gst' => NumberFormatterHelper::formatCurrency($total_gst, session('user_currency')['symbol'] ?? '₹') ?? '',
        ];
        $pdf = Pdf::loadView('buyer.report.downloadGrnPdf', compact('grn'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont'     => 'DejaVu Sans',
                'isRemoteEnabled' => true,
            ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "GRN No-{$grn_no} details.pdf", [
            'Content-Type' => 'application/pdf',
        ]);
    }
    //--------------------------------------PENDING GRN REPORT---------------------------------------------------------
    public function pendingGrnReportlistdata(Request $request)
    {
        if (!$request->ajax()) return;
        $filteredQuery = $this->getFilteredPendingGrnData($request);

        $allGrns = collect();
        $filteredQuery->chunk(100, function ($chunk) use (&$allGrns) {
            $allGrns = $allGrns->merge($chunk);
        });

        $data = $this->getFormatPendingGrnData($allGrns);

        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginatedData = array_slice($data->all(), ($page - 1) * $perPage, $perPage);
        $paginated = new LengthAwarePaginator(
            $paginatedData,
            count($data),
            $perPage,
            $page
        );
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $paginated->total(),
            'recordsFiltered' => $paginated->total(),
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);

    }
    
    public function exportTotalPendingGrnReport(Request $request)
    {
        $query = $this->getFilteredPendingGrnData($request);
        $total = $query->count();
        return response()->json(['total' => $total]);
    }
    public function exportBatchPendingGrnReport(Request $request)
    {
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));
        $query = $this->getFilteredPendingGrnData($request);
        $results = $query->offset($offset)->limit($limit)->get();
        $result = [];
        foreach ($results as $index => $item) {
                $orderQty = round($item->order_quantity, 3);
                $totalGrnQty = round($item->total_grn_quantity, 3);
                $pendingGrnQty = round($item->pending_quantity, 3);

                $result[] = [
                    $offset + $index + 1,
                    $item->branch_name ?? '',
                    $item->order_number ?? '',
                    $item->order_date ? Carbon::parse($item->order_date)->format('d/m/Y') : '',
                    $item->product_name ?? $item->buyer_product_name,
                    $item->buyer_product_name ?? '',
                    $item->vendor_name ?? '',
                    cleanInvisibleCharacters($item->specification ?? ''),
                    cleanInvisibleCharacters($item->size ?? ''),
                    $item->inventory_grouping ?? '',
                    $item->added_by ?? '',
                    $item->added_date ? $item->added_date : null,
                    $item->uom_name ?? '',
                    " " . NumberFormatterHelper::formatQty($orderQty, session('user_currency')['symbol'] ?? '₹'),
                    " " . NumberFormatterHelper::formatQty($totalGrnQty, session('user_currency')['symbol'] ?? '₹'),
                    " " . NumberFormatterHelper::formatQty($pendingGrnQty, session('user_currency')['symbol'] ?? '₹'),
                ];

        }
        return response()->json(['data' => $result]);
    }
    public function getFilteredPendingGrnData_pingki(Request $request)
    {
        $companyId = Auth::user()->parent_id ?? Auth::user()->id;
        $buyerParentId = $companyId;
        $buyerBranchId = $request->input('branch_id');
        $productName = $request->input('search_product_name', '');
        $categoryName = $request->input('search_category_id', '');
        $orderName = $request->input('search_order_no', '');

        if (session('branch_id') !== $request->branch_id) {
            session(['branch_id' => $request->branch_id]);
        }

        $categoryIds = [];
        if (!empty($categoryName)) {
            $categoryIds = DB::table('categories')
                ->where('category_name', $categoryName)
                ->pluck('id')
                ->toArray();
        }

        // --- GRN Subquery ---
        $grnSubQuery = DB::table('grns')
            ->selectRaw('inventory_id, order_id, po_number, SUM(grn_qty) as total_grn_qty, MAX(updated_by) as added_by_id, MAX(updated_at) as added_at')
            ->groupBy('inventory_id', 'order_id', 'po_number');

        // ---- Manual Orders ----
        $manualQuery = DB::table('inventories as inv')
            ->join('branch_details as branch', function ($join) {
                $join->on('inv.buyer_branch_id', '=', 'branch.branch_id')
                    ->where('branch.user_type', '1');
            })
            ->join('products as p', 'inv.product_id', '=', 'p.id')
            ->join('uoms as u', 'inv.uom_id', '=', 'u.id')
            ->join('manual_order_products as mop', 'inv.id', '=', 'mop.inventory_id')
            ->join('manual_orders as mo', function ($join) {
                $join->on('mop.manual_order_id', '=', 'mo.id')
                    ->where('mo.order_status', '1');
            })
            ->join('vendors as v', 'mo.vendor_id', '=', 'v.user_id')
            ->leftJoinSub($grnSubQuery, 'g', function ($join) {
                $join->on('inv.id', '=', 'g.inventory_id')
                    ->on('mo.manual_po_number', '=', 'g.po_number')
                    ->on('mo.id', '=', 'g.order_id');
            })
            ->leftJoin('buyers as b', 'g.added_by_id', '=', 'b.user_id')
            ->leftJoin('users as u1', 'g.added_by_id', '=', 'u1.id')
            ->selectRaw("
                branch.name as branch_name,
                mo.manual_po_number as order_number,
                mo.id as order_id,
                '4' as grn_type,
                mo.created_at as order_date,
                'Manual' as order_type,
                p.product_name,
                inv.buyer_product_name,
                v.legal_name as vendor_name,
                inv.id as inventory_id,
                inv.specification,
                inv.size,
                inv.inventory_grouping,
                u.uom_name,
                mop.product_quantity as order_quantity,
                mop.product_price as rate,
                COALESCE(g.total_grn_qty, 0) as total_grn_quantity,
                GREATEST(mop.product_quantity - COALESCE(g.total_grn_qty, 0), 0) as pending_quantity,
                CASE
                    WHEN g.total_grn_qty > 0 AND b.legal_name IS NOT NULL THEN b.legal_name
                    WHEN g.total_grn_qty > 0 AND u1.name IS NOT NULL THEN u1.name
                    ELSE NULL
                END as added_by,
                CASE WHEN g.total_grn_qty > 0 THEN DATE_FORMAT(g.added_at, '%d/%m/%Y') ELSE NULL END as added_date
            ")
            ->where('inv.buyer_parent_id', $buyerParentId)
            ->where('inv.buyer_branch_id', $buyerBranchId)
            ->when(!empty($productName), function ($query) use ($productName) {
                $query->where(function ($q) use ($productName) {
                    $q->where('p.product_name', 'like', "%{$productName}%")
                    ->orWhere('inv.buyer_product_name', 'like', "%{$productName}%");
                });
            })
            ->when(!empty($orderName), function ($query) use ($orderName) {
                $query->where('mo.manual_po_number', 'like', "%{$orderName}%");
            })
            ->when(!empty($categoryIds), function ($query) use ($categoryIds) {
                $query->whereIn('p.category_id', $categoryIds);
            })
            ->whereRaw('(mop.product_quantity - COALESCE(g.total_grn_qty, 0)) > 0');

        // ---- RFQ Orders ----
        $rfqQuery = DB::table('inventories as inv')
            ->join('branch_details as branch', function ($join) {
                $join->on('inv.buyer_branch_id', '=', 'branch.branch_id')
                    ->where('branch.user_type', '1');
            })
            ->join('products as p', 'inv.product_id', '=', 'p.id')
            ->join('uoms as u', 'inv.uom_id', '=', 'u.id')
            ->join('rfq_product_variants as rpv', function ($join) {
                $join->on('inv.id', '=', 'rpv.inventory_id')
                    ->where('rpv.inventory_status', '1');
            })
            ->join('rfqs as r', function ($join) {
                $join->on('rpv.rfq_id', '=', 'r.rfq_id')
                    ->whereIn('r.buyer_rfq_status', ['5','8','9','10']);
            })
            ->join('order_variants as ov', function ($join) {
                $join->on('r.rfq_id', '=', 'ov.rfq_id')
                    ->on('rpv.id', '=', 'ov.rfq_product_variant_id');
            })
            ->join('orders as o', function ($join) {
                $join->on('ov.rfq_id', '=', 'o.rfq_id')
                    ->whereColumn('o.po_number', 'ov.po_number')
                    ->where('o.order_status', '1');
            })
            ->join('vendors as v', 'o.vendor_id', '=', 'v.user_id')
            ->leftJoinSub($grnSubQuery, 'g', function ($join) {
                $join->on('inv.id', '=', 'g.inventory_id')
                    ->on('o.po_number', '=', 'g.po_number')
                    ->on('o.id', '=', 'g.order_id');
            })
            ->leftJoin('buyers as b', 'g.added_by_id', '=', 'b.user_id')
            ->leftJoin('users as u1', 'g.added_by_id', '=', 'u1.id')
            ->selectRaw("
                branch.name as branch_name,
                ov.po_number as order_number,
                o.id as order_id,
                '1' as grn_type,
                ov.created_at as order_date,
                'RFQ' as order_type,
                p.product_name,
                inv.buyer_product_name,
                v.legal_name as vendor_name,
                inv.id as inventory_id,
                inv.specification,
                inv.size,
                inv.inventory_grouping,
                u.uom_name,
                ov.order_quantity as order_quantity,
                ov.order_price as rate,
                COALESCE(g.total_grn_qty, 0) as total_grn_quantity,
                GREATEST(ov.order_quantity - COALESCE(g.total_grn_qty, 0), 0) as pending_quantity,
                CASE
                    WHEN g.total_grn_qty > 0 AND b.legal_name IS NOT NULL THEN b.legal_name
                    WHEN g.total_grn_qty > 0 AND u1.name IS NOT NULL THEN u1.name
                    ELSE NULL
                END as added_by,
                CASE WHEN g.total_grn_qty > 0 THEN DATE_FORMAT(g.added_at, '%d/%m/%Y') ELSE NULL END as added_date
            ")
            ->where('inv.buyer_parent_id', $buyerParentId)
            ->where('inv.buyer_branch_id', $buyerBranchId)
            ->when(!empty($productName), function ($query) use ($productName) {
                $query->where(function ($q) use ($productName) {
                    $q->where('p.product_name', 'like', "%{$productName}%")
                    ->orWhere('inv.buyer_product_name', 'like', "%{$productName}%");
                });
            })
            ->when(!empty($categoryIds), function ($query) use ($categoryIds) {
                $query->whereIn('p.category_id', $categoryIds);
            })
            ->when(!empty($orderName), function ($query) use ($orderName) {
                $query->where('ov.po_number', 'like', "%{$orderName}%");
            })
            ->whereRaw('(ov.order_quantity - COALESCE(g.total_grn_qty, 0)) > 0');
        $manualQuery->distinct();
        $rfqQuery->distinct();

        // Combine and order
        $results = DB::query()
            ->fromSub($manualQuery->unionAll($rfqQuery), 'combined')
            ->orderBy('order_date', 'desc');

        return $results;
    }
    public function getFilteredPendingGrnData(Request $request)
    {
        $user = Auth::user();
        $buyerParentId = $user->parent_id ?? $user->id;
        $buyerBranchId = $request->branch_id;

        $productName  = trim($request->input('search_product_name'));
        $categoryName = trim($request->input('search_category_id'));
        $orderName    = trim($request->input('search_order_no'));

        if (session('branch_id') !== $buyerBranchId) {
            session(['branch_id' => $buyerBranchId]);
        }

        /* ------------------------------------------------
        | CATEGORY FILTER
        ------------------------------------------------*/
        $categoryIds = [];
        if (!empty($categoryName)) {
            $categoryIds = DB::table('categories')
                ->where('category_name', $categoryName)
                ->pluck('id')
                ->toArray();
        }

        /* ------------------------------------------------
        | GRN AGGREGATION
        ------------------------------------------------*/
        $grnSubQuery = DB::table('grns')
            ->selectRaw('
                inventory_id,
                order_id,
                po_number,
                SUM(grn_qty) as total_grn_qty,
                MAX(updated_by) as added_by_id,
                MAX(updated_at) as added_at
            ')
            ->groupBy('inventory_id', 'order_id', 'po_number');

        /* =================================================
        | MANUAL ORDERS
        =================================================*/
        $manualQuery = DB::table('inventories as inv')
            ->join('branch_details as branch', function ($join) {
                $join->on('inv.buyer_branch_id', '=', 'branch.branch_id')
                    ->where('branch.user_type', '1');
            })
            ->leftjoin('products as p', 'inv.product_id', '=', 'p.id')
            ->join('uoms as u', 'inv.uom_id', '=', 'u.id')
            ->join('manual_order_products as mop', 'inv.id', '=', 'mop.inventory_id')
            ->join('manual_orders as mo', function ($join) {
                $join->on('mop.manual_order_id', '=', 'mo.id')
                    ->where('mo.order_status', '1');
            })
            ->join('vendors as v', 'mo.vendor_id', '=', 'v.user_id')
            ->leftJoinSub($grnSubQuery, 'g', function ($join) {
                $join->on('inv.id', '=', 'g.inventory_id')
                    ->on('mo.id', '=', 'g.order_id')
                    ->on('mo.manual_po_number', '=', 'g.po_number');
            })
            ->leftJoin('buyers as b', 'g.added_by_id', '=', 'b.user_id')
            ->leftJoin('users as u1', 'g.added_by_id', '=', 'u1.id')
            ->where('inv.buyer_parent_id', $buyerParentId)
            ->where('inv.buyer_branch_id', $buyerBranchId)
            ->when($productName, function ($q) use ($productName) {
                $q->where(function ($x) use ($productName) {
                    $x->where('p.product_name', 'like', "%{$productName}%")
                    ->orWhere('inv.buyer_product_name', 'like', "%{$productName}%");
                });
            })
            ->when($orderName, function ($q) use ($orderName) {
                $q->where('mo.manual_po_number', 'like', "%{$orderName}%");
            })
            ->when(!empty($categoryIds), function ($q) use ($categoryIds) {
                $q->whereIn('p.category_id', $categoryIds);
            })
            ->selectRaw("
                branch.name as branch_name,
                mo.manual_po_number as order_number,
                mo.id as order_id,
                '4' as grn_type,
                mo.created_at as order_date,
                'Manual' as order_type,
                p.product_name,
                inv.buyer_product_name,
                v.legal_name as vendor_name,
                inv.id as inventory_id,
                inv.specification,
                inv.size,
                inv.inventory_grouping,
                u.uom_name,
                mop.product_quantity as order_quantity,
                mop.product_price as rate,
                COALESCE(g.total_grn_qty,0) as total_grn_quantity,
                (mop.product_quantity - COALESCE(g.total_grn_qty,0)) as pending_quantity,
                CASE
                    WHEN g.total_grn_qty > 0 AND b.legal_name IS NOT NULL THEN b.legal_name
                    WHEN g.total_grn_qty > 0 AND u1.name IS NOT NULL THEN u1.name
                    ELSE NULL
                END as added_by,
                CASE
                    WHEN g.total_grn_qty > 0 THEN DATE_FORMAT(g.added_at,'%d/%m/%Y')
                    ELSE NULL
                END as added_date
            ");

        /* =================================================
        | RFQ ORDERS
        =================================================*/
        $rfqQuery = DB::table('inventories as inv')
            ->join('branch_details as branch', function ($join) {
                $join->on('inv.buyer_branch_id', '=', 'branch.branch_id')
                    ->where('branch.user_type', '1');
            })
            ->leftjoin('products as p', 'inv.product_id', '=', 'p.id')
            ->join('uoms as u', 'inv.uom_id', '=', 'u.id')
            ->join('rfq_product_variants as rpv', function ($join) {
                $join->on('inv.id', '=', 'rpv.inventory_id')
                    ->where('rpv.inventory_status', '1');
            })
            ->join('rfqs as r', function ($join) {
                $join->on('rpv.rfq_id', '=', 'r.rfq_id')
                    ->whereIn('r.buyer_rfq_status', ['5','8','9','10']);
            })
            ->join('order_variants as ov', function ($join) {
                $join->on('r.rfq_id', '=', 'ov.rfq_id')
                    ->on('rpv.id', '=', 'ov.rfq_product_variant_id');
            })
            ->join('orders as o', function ($join) {
                $join->on('ov.rfq_id', '=', 'o.rfq_id')
                    ->whereColumn('o.po_number', 'ov.po_number')
                    ->where('o.order_status', '1');
            })
            ->join('vendors as v', 'o.vendor_id', '=', 'v.user_id')
            ->leftJoinSub($grnSubQuery, 'g', function ($join) {
                $join->on('inv.id', '=', 'g.inventory_id')
                    ->on('o.id', '=', 'g.order_id')
                    ->on('o.po_number', '=', 'g.po_number');
            })
            ->leftJoin('buyers as b', 'g.added_by_id', '=', 'b.user_id')
            ->leftJoin('users as u1', 'g.added_by_id', '=', 'u1.id')
            ->where('inv.buyer_parent_id', $buyerParentId)
            ->where('inv.buyer_branch_id', $buyerBranchId)
            ->when($productName, function ($q) use ($productName) {
                $q->where(function ($x) use ($productName) {
                    $x->where('p.product_name', 'like', "%{$productName}%")
                    ->orWhere('inv.buyer_product_name', 'like', "%{$productName}%");
                });
            })
            ->when($orderName, function ($q) use ($orderName) {
                $q->where('ov.po_number', 'like', "%{$orderName}%");
            })
            ->when(!empty($categoryIds), function ($q) use ($categoryIds) {
                $q->whereIn('p.category_id', $categoryIds);
            })
            ->selectRaw("
                branch.name as branch_name,
                ov.po_number as order_number,
                o.id as order_id,
                '1' as grn_type,
                ov.created_at as order_date,
                'RFQ' as order_type,
                p.product_name,
                inv.buyer_product_name,
                v.legal_name as vendor_name,
                inv.id as inventory_id,
                inv.specification,
                inv.size,
                inv.inventory_grouping,
                u.uom_name,
                ov.order_quantity as order_quantity,
                ov.order_price as rate,
                COALESCE(g.total_grn_qty,0) as total_grn_quantity,
                (ov.order_quantity - COALESCE(g.total_grn_qty,0)) as pending_quantity,
                CASE
                    WHEN g.total_grn_qty > 0 AND b.legal_name IS NOT NULL THEN b.legal_name
                    WHEN g.total_grn_qty > 0 AND u1.name IS NOT NULL THEN u1.name
                    ELSE NULL
                END as added_by,
                CASE
                    WHEN g.total_grn_qty > 0 THEN DATE_FORMAT(g.added_at,'%d/%m/%Y')
                    ELSE NULL
                END as added_date
            ");

        /* =================================================
        | FINAL UNION + FILTER (IMPORTANT)
        =================================================*/
        return DB::query()
            ->fromSub($manualQuery->unionAll($rfqQuery), 'pending_grns')
            ->where('pending_quantity', '>', 0)
            ->orderBy('order_date', 'desc');
    }

    public function getFormatPendingGrnData($grns)
    {
        return $grns->map(function ($item, $index) {
            $orderQty = round($item->order_quantity, 3);
            $totalGrnQty = round($item->total_grn_quantity, 3);
            $pendingGrnQty = round($item->pending_quantity, 3);

            return [
                'serial_number' => '<input type="checkbox" name="grn_checkbox[]" class="inventory_chkd" data-inventory-id="' . $item->inventory_id . '" data-order-id="' . $item->order_id . '" data-order-type="' . $item->order_type . '" data-value="' . $totalGrnQty . '" data-po-number="' . $item->order_number . '" data-vendor-name="' . $item->vendor_name . '" data-grn-type="' . $item->grn_type . '"> ' . "<span class='serial-no'>".($index + 1)."</span>",
                'branch_name' => $item->branch_name ?? '',
                'order_number' => $item->order_number ?? '',
                'order_date' => Carbon::parse($item->order_date)->format('d/m/Y'),
                'order_type' => $item->order_type ?? '',
                'product_name' => $item->product_name ?? $item->buyer_product_name,
                'buyer_product_name' => $item->buyer_product_name ?? '',
                'vendor_name' => $item->vendor_name ?? '',
                'specification' => TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($item->specification ?? '')),
                'size' => TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($item->size ?? '')),
                'inventory_grouping' => TruncateWithTooltipHelper::wrapText($item->inventory_grouping ?? ''),
                'added_by' => $item->added_by ?? '',
                'added_date' => $item->added_date? $item->added_date: null,
                'uom' => $item->uom_name ?? '',
                'order_quantity' => NumberFormatterHelper::formatQty($orderQty, session('user_currency')['symbol'] ?? '₹'),
                'total_grn_quantity' => "<span class='editable-grn' data-id='{$item->inventory_id}' data-value='{$totalGrnQty}' data-order-id='{$item->order_id}' data-po-number='{$item->order_number}' data-vendor-name='{$item->vendor_name}'  data-grn-type='{$item->grn_type}' data-order-qty='{$orderQty}' data-rate='{$item->rate}'>".NumberFormatterHelper::formatQty($totalGrnQty, session('user_currency')['symbol'] ?? '₹')."</span>",
                'pending_grn_quantity' => NumberFormatterHelper::formatQty($pendingGrnQty, session('user_currency')['symbol'] ?? '₹'),
            ];
        });
    }

    public function fetchOrderDetailsforPendingGrn(Request $request)
    {
        $inventoryIds = $request->input('inventory_ids', []);
        $orderIds = $request->input('order_ids', []);
        $orderTypes = $request->input('order_types', []);
        $grnTypes = $request->input('grn_types', []);

        if (Auth::user()->parent_id != 0) {
            $this->ensurePermission('GRN', 'add', '1');
        }

        ManualPOController::userCurrency();
        $buyerId = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;

        $pendingOrderArray = [];

        foreach ($inventoryIds as $index => $inventoryId) {
            $orderId = $orderIds[$index] ?? null;
            $orderType = $orderTypes[$index] ?? null;
            $grnType = $grnTypes[$index] ?? null;

            if ($grnType == 4) {
                // Manual Order
                $pendingOrders = $this->getPendingOrderDetails($buyerId, $inventoryId, 'manual_order', $orderId,null);
            } elseif ($grnType == 1) {
                // RFQ Order
                $pendingOrders = $this->getPendingOrderDetails($buyerId, $inventoryId, 'rfq_order', $orderId,null);
            } else {
                continue;
            }

            if ($pendingOrders instanceof \Illuminate\Support\Collection) {
                $pendingOrders = $pendingOrders->toArray();
            } elseif (!is_array($pendingOrders)) {
                $pendingOrders = [];
            }

            $pendingOrderArray = array_merge($pendingOrderArray, $pendingOrders);
        }

        return response()->json($pendingOrderArray);
    }

    public function storeFromPendingGRN(Request $request)
    {
        $request = $this->trimAndReturnRequest($request);
        $this->validateRequest($request);

        $companyId = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;
        $userId = Auth::user()->id;
        $inventoryId = $request->inventory_id;

        $allQtys = collect($request->grn_qty)->filter(fn($qty) => is_numeric($qty));

        $this->checkEmptyGrnOrZeroValueGrn($allQtys);

        $orderIds = collect($request->order_id)->filter(fn($id) => is_numeric($id));
        $inventoryIds = collect($request->inventory_id)->filter(fn($inventory_id) => is_numeric($inventory_id));
        $grnTypes = collect($request->grn_type)->filter(fn($grn_type) => is_numeric($grn_type));

        $nextGrnNumber = Grn::getNextGrnNumber($companyId);
        //order grn insert
        foreach ($allQtys as $index => $qty) {
            if($grnTypes[$index]=='4'){
                $this->checkMaxQtyForManualOrderGrn(floatval($qty), $inventoryIds[$index], $orderIds[$index] ?? null);

                $validData = $this->buildValidDataForOrderRow($request, collect([$index => $qty]), $inventoryIds[$index], $companyId, $userId, $nextGrnNumber);

                if (empty($validData)) {
                    return $this->errorResponse('grn_qty', 'No valid GRN quantities provided.');
                }
                Grn::insert($validData);
                $this->closeIndent($inventoryIds[$index]);
            }
            if($grnTypes[$index]=='1'){
                $this->checkMaxQtyForRfqGrn(floatval($qty), $inventoryIds[$index], $orderIds[$index] ?? null);
                $validData = $this->buildValidDataForOrderRow($request, collect([$index => $qty]), $inventoryIds[$index], $companyId, $userId, $nextGrnNumber);

                if (empty($validData)) {
                    return $this->errorResponse('grn_qty', 'No valid GRN quantities provided.');
                }

                Grn::insert($validData);
                $this->closeIndent($inventoryIds[$index]);
               }
        }

        return response()->json([
            'status' => true,
            'message' => 'GRN quantity updated successfully.',
        ]);
    }


    //---------------------------------Pending GRN For STock Return-------------------------------------------------

    public function pendingGrnStockReturnReportlistdata(Request $request)
    {
        if (!$request->ajax()) return;
        $filteredQuery = $this->getFilteredPendingGrnStockReturnData($request);

        $allGrns = collect();
        $filteredQuery->chunk(100, function ($chunk) use (&$allGrns) {
            $allGrns = $allGrns->merge($chunk);
        });

        $data = $this->getFormatPendingGrnStockReturnData($allGrns);

        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginatedData = array_slice($data->all(), ($page - 1) * $perPage, $perPage);
        $paginated = new LengthAwarePaginator(
            $paginatedData,
            count($data),
            $perPage,
            $page
        );
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $paginated->total(),
            'recordsFiltered' => $paginated->total(),
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);

    }

    public function exportTotalPendingGrnStockReturnReport(Request $request)
    {
        $query = $this->getFilteredPendingGrnStockReturnData($request);
        $total = $query->count();
        return response()->json(['total' => $total]);
    }
    public function exportBatchPendingGrnStockReturnReport(Request $request)
    {
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));
        $query = $this->getFilteredPendingGrnStockReturnData($request);
        $results = $query->offset($offset)->limit($limit)->get();
        $result = [];
        $currency=session('user_currency')['symbol'] ?? '₹';
        foreach ($results as $index => $item) {
                $totalGrnQty = round($item->totalGrnQty, 3);
                $orderQty = round($item->stock_qty, 3);
                $pendingGrnQty = round($item->pending_qty, 3);

                $key = $item->inventory_id . '-' . $item->stock_id . '-' . $item->grn_type . '-' . $item->last_updated_at;
                $updatedById = $item->updated_by ?? null;
                $addedByName = $updatedById ? optional(User::find($updatedById))->name : '';

                $result[] = [
                    " ".$item->grn_no,
                    optional($item->inventory->branch)->name ?? '',
                    $item->stock_no ?? '',
                    optional($item->inventory->product)->product_name ?? $item->inventory->buyer_product_name,
                    $item->inventory->buyer_product_name ?? '',
                    cleanInvisibleCharacters($item->inventory->specification ?? ''),
                    cleanInvisibleCharacters($item->inventory->size ?? ''),
                    $item->stock_vendor_name ?? '',
                    $addedByName,
                    Carbon::parse($item->last_updated_at)->format('d/m/Y'),
                    optional($item->inventory->uom)->uom_name ?? '',
                    " ".NumberFormatterHelper::formatQty($orderQty, $currency),
                    " ".NumberFormatterHelper::formatQty($totalGrnQty, $currency),
                    " ".NumberFormatterHelper::formatQty($pendingGrnQty, $currency),
                ];

        }
        return response()->json(['data' => $result]);
    }
    public function getFilteredPendingGrnStockReturnData(Request $request)
    {
        $buyerId = Auth::user()->parent_id ?? Auth::user()->id;
        $query = ReturnStock::with([
            'inventory',
            'grn',
            'updater',
            'inventory.branch',
            'inventory.product'
        ])
        ->leftJoin('grns', function ($join) {
            $join->on('grns.stock_id', '=', 'return_stocks.id')
                ->where('grns.grn_type', 3);
        })
        ->where('return_stocks.buyer_id', $buyerId)
        ->where('return_stocks.stock_return_type', 1)
        ->select(
            'return_stocks.inventory_id',
            'return_stocks.stock_no',
            'return_stocks.qty as stock_qty',
            DB::raw('MAX(return_stocks.id) as stock_id'),
            DB::raw('GROUP_CONCAT(DISTINCT grns.grn_no ORDER BY grns.id ASC SEPARATOR ", ") as grn_no'),
            DB::raw('MAX(return_stocks.updated_at) as last_updated_at'),
            DB::raw('MAX(return_stocks.updated_by) as updated_by'),
            DB::raw('MAX(return_stocks.stock_vendor_name) as stock_vendor_name'),
            DB::raw('COALESCE(SUM(grns.grn_qty), 0) as totalGrnQty'),
            DB::raw('ROUND((return_stocks.qty - COALESCE(SUM(grns.grn_qty), 0)), 3) as pending_qty')
        )
        ->groupBy(
            'return_stocks.inventory_id',
            'return_stocks.stock_no',
            'return_stocks.qty'
        )
        ->havingRaw('(return_stocks.qty - COALESCE(SUM(grns.grn_qty), 0)) > 0');

        // Filter by product name
        $query->when($request->search_product_name, function ($q, $searchProductName) {
            $q->where(function ($subQuery) use ($searchProductName) {
                $subQuery->whereHas('inventory', function ($q1) use ($searchProductName) {
                    $q1->where('buyer_product_name', 'like', "%$searchProductName%");
                })
                ->orWhereHas('inventory.product', function ($q2) use ($searchProductName) {
                    $q2->where('product_name', 'like', "%$searchProductName%");
                });
            });
        });

        // Filter by category ID (name)
        $query->when($request->search_category_id, function ($q, $searchCategoryId) {
            $catIds = InventoryController::getIdsByCategoryName($searchCategoryId);
            if (!empty($catIds)) {
                $q->whereHas('inventory.product', function ($p) use ($catIds) {
                    $p->whereIn('category_id', $catIds);
                });
            }
        });

        // Update session branch_id
        if (session('branch_id') !== $request->branch_id) {
            session(['branch_id' => $request->branch_id]);
        }

        // Filter by branch
        $query->when($request->branch_id, function ($q, $branchId) {
            $q->whereHas('inventory.branch', function ($b) use ($branchId) {
                $b->where('branch_id', $branchId);
            });
        });

        $query->when($request->filled(['from_date', 'to_date']), function ($q) use ($request) {
            $from = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay();
            $to = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay();
            $q->whereBetween('return_stocks.updated_at', [$from, $to]);
        });

        return $query->orderByDesc('stock_no')
                    ->orderByDesc('last_updated_at')
                    ;
    }
    private function getFormatPendingGrnStockReturnData($grns)
    {
        return $grns
            ->unique(fn($item) => $item->inventory_id . '-' . $item->stock_id)
            ->map(function ($item){
                $totalGrnQty = round($item->totalGrnQty, 3);
                $orderQty = round($item->stock_qty, 3);
                $pendingGrnQty = round($item->pending_qty, 3);
                $key = $item->inventory_id . '-' . $item->stock_id . '-' . $item->grn_type . '-' . $item->last_updated_at;
                $updatedById = $item->updated_by;
                $addedByName = $updatedById ? User::find($updatedById)->name : '';
                return [
                    'grn_no' =>$item->grn_no ?: '-',
                    'stock_no' => $item->stock_no,
                    'product_name' => $item->inventory->product->product_name ?? $item->inventory->buyer_product_name,
                    'buyer_product_name' => $item->inventory->buyer_product_name ?? '',
                    'specification' => TruncateWithTooltipHelper::wrapText(cleanInvisibleCharacters($item->inventory->specification)),
                    'size' => TruncateWithTooltipHelper::wrapText(cleanInvisibleCharacters($item->inventory->size)),
                    'stock_vendor_name' => $item->stock_vendor_name ?? '',
                    'added_by' => $addedByName,
                    'added_date' => Carbon::parse($item->last_updated_at)->format('d/m/Y'),
                    'uom' => $item->inventory->uom->uom_name ?? '',
                    'order_quantity' => NumberFormatterHelper::formatQty($orderQty, session('user_currency')['symbol'] ?? '₹'),
                    'total_grn_quantity' => NumberFormatterHelper::formatQty($totalGrnQty, session('user_currency')['symbol'] ?? '₹'),
                    'pending_grn_quantity' => NumberFormatterHelper::formatQty($pendingGrnQty, session('user_currency')['symbol'] ?? '₹'),
                ];
            })
            ->values()
            ->map(function ($item) {
                return $item;
            });
    }
}






