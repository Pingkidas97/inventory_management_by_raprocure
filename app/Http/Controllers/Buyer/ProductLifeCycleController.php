<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventories;
use App\Models\Grn;
use App\Models\Issued;
use App\Models\Consume;
use App\Helpers\NumberFormatterHelper;

class ProductLifeCycleController extends Controller
{
    public function productLifeCycle(Request $request)
    {
        $ids = $request->input('inventory_ids');

        if (!$ids || !is_array($ids)) {
            return response()->json([
                'status'  => false,
                'message' => 'No Data Found'
            ]);
        }

        $inventories = Inventories::with(['product', 'indentsForPLC', 'manualOrderProductForPLC','RfqProductVariantForPLC'])
            ->whereIn('id', $ids)
            ->select('id', 'product_id', 'specification', 'size', 'buyer_product_name')
            ->get();

        if ($inventories->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No Data Found'
            ]);
        }
        $consumes = Consume::with('issued')
            ->whereHas('issued', function ($query) use ($ids) {
                $query->whereIn('inventory_id', $ids)
                    ->where('issued_return_for', '<>', 0);
            })
            ->orderBy('updated_at', 'desc')
            ->orderBy('consume_no', 'desc')
            ->get();

        $consumeData = [];

        if (!$consumes->isEmpty()) {
            foreach ($consumes as $consume) {

                $issued = $consume->issued;

                if (!$issued) continue;

                $item = [
                    'consume_no' => $consume->consume_no ?? '-',
                    'qty' => NumberFormatterHelper::formatQty(
                                $consume->qty,
                                session('user_currency')['symbol'] ?? '₹'
                            ),
                    'issued_no' => $issued->issued_no ?? '-',
                    'issue_qty' => NumberFormatterHelper::formatQty(
                                $issued->qty,
                                session('user_currency')['symbol'] ?? '₹'
                            ),
                    'reference' => $issued->reference_number ?? '-',
                    'rate' => NumberFormatterHelper::formatCurrency(
                                $issued->rate ?? 0,
                                session('user_currency')['symbol'] ?? '₹'
                            ),
                    'added_date' => $consume->updated_at
                        ? \Carbon\Carbon::parse($consume->updated_at)->format('d/m/Y')
                        : '-'
                ];

                // grouping key
                $issuedId   = $consume->issued_id ?? 0;
                $inventoryId = $issued->inventory_id ?? 0;
                $grn=$issued->grn;
                // push
                $consumeData[$grn->grn_type][$grn->order_id][$inventoryId][] = $item;
            }
        }
        $issues = Issued::with('grn')
            ->whereIn('inventory_id', $ids)
            ->where('issued_return_for', '<>', 0)
            ->orderBy('updated_at', 'desc')
            ->orderBy('issued_no', 'desc')
            ->get();

        $issueData = [];

        if (!$issues->isEmpty()) {
            foreach ($issues as $issue) {
                $grn=$issue->grn;
                $item = [
                    'issued_no'   => $issue->issued_no ?? '-',
                    'qty'         => NumberFormatterHelper::formatQty(
                                        $issue->qty,
                                        session('user_currency')['symbol'] ?? '₹'
                                    ),
                    'reference'   => $issue->reference_number ?? '-',
                    'rate'        => NumberFormatterHelper::formatCurrency(
                                        $issue->rate,
                                        session('user_currency')['symbol'] ?? '₹'
                                    ),
                    'added_date'  => $issue->updated_at
                                        ? \Carbon\Carbon::parse($issue->updated_at)->format('d/m/Y')
                                        : '-'
                    
                ];

                // grouping keys
                $returnFor   = $issue->issued_return_for ?? 0;
                $inventoryId = $issue->inventory_id ?? 0;

                // push into array
                $issueData[$grn->grn_type][$grn->order_id][$inventoryId][] = $item;
            }
        }
        $grns = Grn::with(['inventory', 'inventory.product'])
                ->whereIn('inventory_id', $ids)
                ->where(function($query) {
                    $query->where('grn_type', '!=', 3)
                        ->orWhere('order_id', '<>', 0)
                        ->orWhere('stock_return_for', '<>', 0);
                })
                ->orderBy('updated_at', 'desc')
                ->orderBy('grn_no', 'desc')
                ->get();
        $grnData = [];

        if (!$grns->isEmpty()) {
            foreach ($grns as $grn) {
                if($grn->order_id == 0 && $grn->stock_return_for == 0){
                    $grn->reference_number = 'Stock Return No ' . optional($grn->stock)->stock_no;
                    $grn->rate = $grn->order_rate;

                } elseif ($grn->order_id == 0 && $grn->stock_return_for != 0) {
                    $grn->reference_number = 'Stock Return No ' . optional($grn->stock)->stock_no;

                    $originalGrnId = optional($grn->stock)->stock_return_for;
                    $originalGrn = Grn::with(['manualOrderProduct', 'order.order_variants', 'inventory'])->find($originalGrnId);

                    $grn->rate = $originalGrn ? $originalGrn->getOrderRateAttribute() : null;

                } else {
                    $grn->reference_number = $grn->po_number;
                    $grn->rate = $grn->order_rate;
                }

                $item = [
                    'grn_no' => $grn->grn_no ?? '-',
                    'grn_qty' => NumberFormatterHelper::formatQty($grn->grn_qty, session('user_currency')['symbol'] ?? '₹'),
                    'grn_reference' => $grn->reference_number,
                    'rate' => NumberFormatterHelper::formatCurrency($grn->rate, session('user_currency')['symbol'] ?? '₹'),
                    'added_date' => $grn->updated_at?->format('d/m/Y') ?? '-',
                    
                ];

                // keys
                $type = $grn->grn_type ?? 0;
                $orderId = $grn->order_id ?? 0;
                $inventoryId = $grn->inventory_id ?? 0;

                // push into nested array
                $grnData[$type][$orderId][$inventoryId][] = $item;
            }
        }
           
        $finalData = $inventories->map(function ($inventory) use($grnData,$issueData,$consumeData) {

            return [
                'inventory_id'  => $inventory->id,
                'product_name'  => $inventory->product->product_name ?? $inventory->buyer_product_name,
                'specification' => $inventory->specification,
                'size'          => $inventory->size,

                'indent' => $inventory->indentsForPLC->map(function ($indent)use ($inventory,$grnData,$issueData,$consumeData) {                    
                    $status = $indent->is_deleted == 1 ? 'Deleted' :
                            ($indent->is_active == 2 ? 'Unapproved' :
                            ($indent->closed_indent == 1 ? 'Closed' : 'Approved'));
                    return [
                        'indent_number' => $indent->inventory_unique_id ?? '-',
                        'indent_qty'    => NumberFormatterHelper::formatQty($indent->indent_qty, session('user_currency')['symbol'] ?? '₹'),
                        'status'        => $status,
                        'added_date'    => $indent->created_at?->format('d/m/Y') ?? '-',                        
                    ];
                }),
                'rfq' => $inventory->RfqProductVariantForPLC->map(function ($RfqProductVariant)use ($inventory,$grnData,$issueData,$consumeData) { 
                    
                    
                        $rfq = $RfqProductVariant->rfq;

                        if (!$rfq) return null;

                        $totalQty = $rfq->rfqProductVariants
                                        ->where('inventory_id', $RfqProductVariant->inventory_id)
                                        ->where('inventory_status', 1)
                                        ->sum('quantity');

                        
                        // Inside the map function for RfqProductVariant -> orders
                        $orders = [];
                        $variantId = $rfq->rfqProductVariants->pluck('id')->first(); // first variant

                        foreach ($rfq->orders as $order) {
                            foreach ($order->order_variants as $ov) {
                                if ($ov->inventory_id == $RfqProductVariant->inventory_id || $ov->rfq_product_variant_id == $variantId) { 
                                    if($order->order_status == '1'){
                                        $currency = $order->vendor_currency ?? '₹';
                                        $orders[] = [
                                            'order_id'     => $order->id,
                                            'order_no'     => $order->po_number,
                                            'order_date'   => $order->created_at?->format('d/m/Y'),
                                            'order_qty'    => NumberFormatterHelper::formatQty($ov->order_quantity, session('user_currency')['symbol'] ?? '₹'),
                                            'rate'         => NumberFormatterHelper::formatCurrency($ov->order_price, $currency),
                                            'vendor_name'  => $order->vendor->legal_name ?? 'N/A',
                                            'basePoUrl'    => route('buyer.rfq.order-confirmed.view', ['id' => $order->id]),
                                            'order_status' => $order->order_status == '1' ? 'Confirm' : 'Cancel',
                                            'type'         => 'rfq',
                                            'grn'          => $grnData[1][$order->id][$RfqProductVariant->inventory_id] ?? [],
                                            'issue'        => $issueData[1][$order->id][$RfqProductVariant->inventory_id] ?? [],
                                            'consume'      => $consumeData[1][$order->id][$RfqProductVariant->inventory_id]??[]
                                        ];
                                    }                                  

                                    
                                }
                            }
                        }

                        return [
                            'rfq_no'          => $rfq->rfq_id,
                            'rfq_date'        => $rfq->updated_at?->format('d/m/Y') ?? '-',
                            'rfq_closed'      => in_array($rfq->buyer_rfq_status, [8, 10]) ? 'Closed' : '',
                            'rfq_qty'         => NumberFormatterHelper::formatQty($totalQty, session('user_currency')['symbol'] ?? '₹'),
                            'rfq_id'          => $rfq->rfq_id,
                            'orders'          => $orders, // attach orders here
                        ];
                    
                }),
                'manualPo' => $inventory->manualOrderProductForPLC
                        ->filter(function ($product) {
                            $order = $product->manualOrder;
                            return $order->order_status == '1' && $order->is_approve == '1';
                        })->map(function ($product)use($grnData,$issueData,$consumeData) {
                                $order = $product->manualOrder;
                                $currency = $order->currencyDetails?->currency_symbol ?? '₹';
                        
                                return [
                                    'order_id'     => $order->id,
                                    'inventory_id'     => $product->inventory_id,
                                    'order_no'     => $order->manual_po_number ?? 'N/A',
                                    'order_date'   => $order->created_at?->format('d/m/Y') ?? '-',
                                    'order_qty'    => NumberFormatterHelper::formatQty(
                                                        $product->product_quantity,
                                                        session('user_currency')['symbol'] ?? '₹'
                                                    ),
                                    'rate'         => NumberFormatterHelper::formatCurrency(
                                                        $product->product_price,
                                                        $currency
                                                    ),
                                    'vendor_name'  => $order->vendor->legal_name ?? 'N/A',
                                    'basePoUrl'    => route('buyer.report.manualPO.orderDetails', ['id' => $order->id]),
                                    'type'         => 'manual',
                                    'order_status' => $order->order_status == '1' ? 'Confirm' : 'Cancel',
                                    'grn'          => $grnData[4][$order->id][$product->inventory_id] ?? [],                                    
                                    'issue'        => $issueData[4][$order->id][$product->inventory_id] ?? [],
                                    'consume'      => $consumeData[4][$order->id][$product->inventory_id]??[]
                                ];
                            }),
            ];
        });

        // $json = '{
        //     "status": true,
        //     "data": [
        //         {
        //             "inventory_id": 47274,
        //             "product_name": "SHAPING MACHINE",
        //             "specification": "3003",
        //             "size": "",
        //             "indent": [
        //                 {
        //                     "indent_number": 195,
        //                     "indent_qty": "100",
        //                     "status": "Approved",
        //                     "added_date": "30/03/2026"
        //                 },
        //                 {
        //                     "indent_number": 196,
        //                     "indent_qty": "200",
        //                     "status": "Approved",
        //                     "added_date": "30/03/2026"
        //                 }
        //             ],
        //             "rfq": [
        //                 {
        //                     "rfq_no": "OPPO-26-00105",
        //                     "rfq_date": "30/03/2026",
        //                     "rfq_closed": "Closed",
        //                     "rfq_qty": "30",
        //                     "rfq_id": "OPPO-26-00105",
        //                     "orders": [
        //                         {
        //                             "order_id": 2559,
        //                             "order_no": "O-OPPO-26-00105/02",
        //                             "order_date": "30/03/2026",
        //                             "order_qty": "3",
        //                             "rate": "₹ 10.00",
        //                             "vendor_name": "PINGKI",
        //                             "basePoUrl": "https://v82.guruworkwithit.online/buyer/rfq/order-confirmed/view/2559",
        //                             "order_status": "Confirm",
        //                             "type": "rfq",
        //                             "grn": [],
        //                             "issue": [],
        //                             "consume": []
        //                         },
        //                         {
        //                             "order_id": 2557,
        //                             "order_no": "O-OPPO-26-00105/01",
        //                             "order_date": "30/03/2026",
        //                             "order_qty": "3",
        //                             "rate": "₹ 10.00",
        //                             "vendor_name": "PINGKI",
        //                             "basePoUrl": "https://v82.guruworkwithit.online/buyer/rfq/order-confirmed/view/2557",
        //                             "order_status": "Cancel",
        //                             "type": "rfq",
        //                             "grn": [],
        //                             "issue": [],
        //                             "consume": []
        //                         }
        //                     ]
        //                 },
        //                 {
        //                     "rfq_no": "OPPO-26-00106",
        //                     "rfq_date": "30/03/2026",
        //                     "rfq_closed": "",
        //                     "rfq_qty": "70",
        //                     "rfq_id": "OPPO-26-00106",
        //                     "orders": [
        //                         {
        //                             "order_id": 2554,
        //                             "order_no": "O-OPPO-26-00106/01",
        //                             "order_date": "30/03/2026",
        //                             "order_qty": "3.366",
        //                             "rate": "₹ 15.00",
        //                             "vendor_name": "PINGKI",
        //                             "basePoUrl": "https://v82.guruworkwithit.online/buyer/rfq/order-confirmed/view/2554",
        //                             "order_status": "Confirm",
        //                             "type": "rfq",
        //                             "grn": [],
        //                             "issue": [],
        //                             "consume": []
        //                         },
        //                         {
        //                             "order_id": 2555,
        //                             "order_no": "O-OPPO-26-00106/02",
        //                             "order_date": "30/03/2026",
        //                             "order_qty": "7",
        //                             "rate": "₹ 15.00",
        //                             "vendor_name": "PINGKI",
        //                             "basePoUrl": "https://v82.guruworkwithit.online/buyer/rfq/order-confirmed/view/2555",
        //                             "order_status": "Confirm",
        //                             "type": "rfq",
        //                             "grn": [],
        //                             "issue": [],
        //                             "consume": []
        //                         }
        //                     ]
        //                 }
        //             ],
        //             "manualPo": [
        //                 {
        //                     "order_id": 461,
        //                     "inventory_id": 47274,
        //                     "order_no": "MO-OPPO-26-027",
        //                     "order_date": "30/03/2026",
        //                     "order_qty": "580",
        //                     "rate": "₹ 15.00",
        //                     "vendor_name": "PINGKI VENDOR",
        //                     "basePoUrl": "https://v82.guruworkwithit.online/buyer/inventory/reports/manualpo/orderDetails/461",
        //                     "type": "manual",
        //                     "order_status": "Confirm",
        //                     "grn": [],
        //                     "issue": [],
        //                     "consume": []
        //                 }
        //             ]
        //         }
        //     ]
        // }';

        // return response()->json(json_decode($json, true));


        return response()->json([
            'status' => true,
            'data'   => $finalData
        ]);

        
    }
}