<?php

namespace App\Http\Controllers\Buyer;

use App\Exports\{GrnReportExport,PendingGrnReportExport,PendingGrnForStockReturnReportExport};
use App\Http\Controllers\Controller;
use App\Http\Controllers\Buyer\ManualPOController;
use App\Http\Controllers\Buyer\InventoryController;
use App\Models\{
    ManualOrder,GetPass,Grn,ManualOrderProduct,ReturnStock,User,Rfq,Inventories,OrderVariant,Order,Indent,RfqProductVariant,Issued,Tax
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
use App\Http\Controllers\Buyer\GrnController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

class GetPassController extends Controller
{
    use TrimFields, HasModulePermission;

    public function getPendingOrderDetails($buyerId, $inventoryId, $orderType,$orderId=null,$poNumber=null)
    {
        if($orderType=='rfq_order'){
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
                            
                            $maxGrnQty = GetPass::where('inventory_id', $inventoryId)
                                ->where('company_id', $buyerId)
                                ->where('order_id', $order->id)
                                ->sum('grn_qty');                           
                            $grn_buyer_rate=GetPass::where('inventory_id', $inventoryId)
                                        ->where('company_id', $buyerId)
                                        ->where('order_id', $order->id)
                                        ->MAX('grn_buyer_rate');
                            
                            if ((float)($maxGetPassQty ?? 0) < (float) $variant->order_quantity) { 
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
                                    'invoice_number' => $order->invoice_number ?? 'N/A',
                                    'vehicle_lr_number' => $variant->vehicle_lr_number ?? 'N/A',
                                    'gross_weight' => $variant->gross_weight ?? 'N/A',
                                    'gst' => $variant->product_gst ?? 0,
                                    'gst_percentage' => $variant->product_gst ?? 0,
                                    'freight_charges' => $variant->freight_charges ?? 0,
                                    'approved_by' => $order->approved_by ?? 'N/A',
                                    'baseManualPoUrl' => route('buyer.rfq.order-confirmed.view', ['id' => '__ID__']),
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
                    $maxGrnQty =  GetPass::where('inventory_id', $inventoryId)
                                ->where('company_id', $buyerId)
                                ->where('order_id', $order->id)
                                ->sum('grn_qty');
                    $grn_buyer_rate=GetPass::where('inventory_id', $inventoryId)
                                ->where('company_id', $buyerId)
                                ->where('order_id', $product->manual_order_id)
                                ->MAX('grn_buyer_rate');
                    
                    if ((float)($maxGrnQty ?? 0) < (float)$product->product_quantity) {
                        $currency = $order->currencyDetails?->currency_symbol ?? '₹';

                        return [
                            'product_name' => $product->inventory->product->product_name ?? '',
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
                            'invoice_number' => $order->invoice_number ?? 'N/A',
                            'vehicle_lr_number' => $product->vehicle_lr_number ?? 'N/A',
                            'gross_weight' => $product->gross_weight ?? 'N/A',
                            'gst' =>$product->product_gst ?? 0,
                            'gst_percentage' => Tax::find($product->product_gst ?? 0)->tax ?? 0,
                            'freight_charges' => $product->freight_charges ?? 0,
                            'approved_by' => $order->approved_by ?? 'N/A',
                            'baseManualPoUrl' => route('buyer.report.manualPO.orderDetails', ['id' => '__ID__']),
                        ];
                    }
                    return null;
                })->filter();
            });
        }
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
    public function store(Request $request)
    {

        $grnController = app(GrnController::class);
        $request = $grnController->trimAndReturnRequest($request);
        $grnController->validateRequest($request);        

        $companyId = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;
        $userId = Auth::user()->id;
        

        $allQtys = collect($request->grn_qty)->filter(fn($qty) => is_numeric($qty));

        $this->checkEmptyGrnOrZeroValueGrn($allQtys);

        $orderIds = collect($request->order_id)->filter(fn($id) => is_numeric($id));
        $inventoryIds = collect($request->inventory_id)->filter(fn($inventory_id) => is_numeric($inventory_id));
        $grnTypes = collect($request->grn_type)->filter(fn($grn_type) => is_numeric($grn_type));
      
        $nextGrnNumber = GetPass::getNextGetPassNumber($companyId);
        $getPassId='GATE-Entry-'.$companyId.'-'.$nextGrnNumber;
        //order grn insert
        foreach ($allQtys as $index => $qty) {
            if($grnTypes[$index]=='4'){
                $this->checkMaxQtyForManualOrderGrn(floatval($qty), $inventoryIds[$index], $orderIds[$index] ?? null);
                
                $validData = $this->buildValidDataForOrderRow($request, collect([$index => $qty]), $inventoryIds[$index], $companyId, $userId, $nextGrnNumber,$getPassId);

                if (empty($validData)) {
                    return $grnController->errorResponse('grn_qty', 'No valid Gate Entry Quantities provided.');
                }
                GetPass::insert($validData);
            }
            if($grnTypes[$index]=='1'){
                $this->checkMaxQtyForRfqGrn(floatval($qty), $inventoryIds[$index], $orderIds[$index] ?? null);
                $validData = $this->buildValidDataForOrderRow($request, collect([$index => $qty]), $inventoryIds[$index], $companyId, $userId, $nextGrnNumber,$getPassId);

                if (empty($validData)) {
                    return $grnController->errorResponse('grn_qty', 'No valid Gate Entry Quantities provided.');
                }
                
                GetPass::insert($validData);            
               }
        }       

        return response()->json([
            'status' => true,
            'message' => 'Gate Entiry Quantity updated successfully.',
            'getPassId' => $getPassId
        ]);      
        
    }
    // public function downloadPdf($getPassId)
    // {
    //     $orders = GetPass::where('get_pass_id', $getPassId)->get();

    //     $pdf = Pdf::loadView('buyer.inventory.downloadGatePass', compact('orders'))
    //         ->setPaper('A4', 'portrait');

    //     // Return PDF as response (AJAX will receive blob)
    //     return response($pdf->output(), 200)
    //         ->header('Content-Type', 'application/pdf')
    //         ->header('Content-Disposition', "attachment; filename=GatePass_$getPassId.pdf");
    // }


    public function downloadPdf($getPassId)
    {
        $folderPath = storage_path("app/public/gatepass");
        
        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0777, true, true);
        }

        $filePath = $folderPath . "/GatePass_$getPassId.pdf";

        if (file_exists($filePath)) {
            $orders = GetPass::with(['inventory.branch'])->where('get_pass_id', $getPassId)->get();
             
            $pdf = Pdf::loadView('buyer.inventory.downloadGatePass', compact('orders'))
                 ->setPaper('A4', 'portrait');

            file_put_contents($filePath, $pdf->output());
        }

        return response()->download($filePath);
    }

    public function checkEmptyGrnOrZeroValueGrn($Qtys)
    {
        $Qtys = collect($Qtys);

        if ($Qtys->isEmpty()) {
            abort(response()->json([
                'status' => false,
                'errors' => ['grn_qty' => ['At least one Gate Entry Quantity is required.']],
            ], 422));
        }

        if ($Qtys->contains(fn($qty) => floatval($qty) <= 0)) {
            abort(response()->json([
                'status' => false,
                'errors' => ['grn_qty' => ['Gate Entry Quantity must be greater than 0.']],
            ], 422));
        }

        return true;
    }
    protected function buildValidDataForOrderRow($request, $grnQtys, $inventoryId, $companyId, $userId, $nextGrnNumber,$getPassId)
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
            $grnController = app(GrnController::class);
            if ($grnController->bccomp_fallback($grnQty, '0', 3) === 1 && $grnController->bccomp_fallback($grnQty, $remainingQty, 3) <= 0) {
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
                    'get_pass_id'      => $getPassId,
                    'company_id'       => $companyId,
                    'updated_by'       => $userId,
                    'get_pass_no'           => $nextGrnNumber++,
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
                                : date('Y-m-d H:i:s'),//3rd feb pingki
                    // 'updated_at' => date('Y-m-d H:i:s'),//3rd feb pingki
                ];
            }
        }
        return $validData;
    }
    protected function checkMaxQtyForRfqGrn($grnQtys, $inventoryId, $orderId)
    {
        $grnQtys = collect($grnQtys);
        $grns = GetPass::where('inventory_id', $inventoryId)
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
    public function checkMaxQtyForManualOrderGrn($grnQtys, $inventoryId, $orderId)
    {
        $grnQtys = collect($grnQtys);
        $grns = GetPass::where('inventory_id', $inventoryId)
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
    //--------------------------Gate Pass REPORT TABLE & EXCEL------------------------------------------------------
    public function gatePassReportlistdata(Request $request)
    {
        if (!$request->ajax()) return;
        $filteredQuery = $this->getFilteredQuery($request);
        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginated = $filteredQuery->paginate($perPage, ['*'], 'page', $page);

        $getPasses = collect($paginated->items());

        $data = $getPasses->map(function ($getPass) {
            $currencySymbol = session('user_currency')['symbol'] ?? '₹';
            return [
                'get_pass_no' => $getPass->get_pass_no,
                'get_pass_id' => '<a href="'.route('buyer.getpass.download', $getPass->get_pass_id).'" 
                    class="text-primary" target="_blank" style="color:#000 !important;">
                    '.$getPass->get_pass_id.'
                  </a>',
                'product' => optional($getPass->inventory->product)->product_name ?? optional($getPass->inventory)->buyer_product_name,
                'buyer_product_name' => optional($getPass->inventory)->buyer_product_name ?? '',
                'specification' => TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($getPass->inventory->specification)),
                'size' => TruncateWithTooltipHelper::wrapTextSS(cleanInvisibleCharacters($getPass->inventory->size)),
                'inventory_grouping' => TruncateWithTooltipHelper::wrapText($getPass->inventory->inventory_grouping),
                'vendor_name' => TruncateWithTooltipHelper::wrapText($getPass->vendor_name??''),
                'vendor_invoice_no' => TruncateWithTooltipHelper::wrapText($getPass->vendor_invoice_number),
                'vehicle_no_lr_no' => TruncateWithTooltipHelper::wrapText($getPass->vehicle_no_lr_no),
                'gross_wt' => TruncateWithTooltipHelper::wrapText($getPass->gross_wt),
                'added_date' => $getPass->updated_at ? Carbon::parse($getPass->updated_at)->format('d/m/Y') : '',
                'grn_qty' =>'<span class="grn-entry-details" style="cursor:pointer;color:blue;" data-id="'.$getPass->id.'" >'.NumberFormatterHelper::formatQty($getPass->grn_qty,$currencySymbol) .'</span>',
                'uom' => $getPass->inventory->uom->uom_name ?? '',
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
   
    public function exportTotalGetPassReport(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $total = $query->count();
        return response()->json(['total' => $total]);
    }

    public function exportBatchGetPassReport(Request $request)
    {
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));
        $query = $this->getFilteredQuery($request);
        $results = $query->offset($offset)->limit($limit)->get();
        $result = [];
        $totalAmount = 0;
        $currency=session('user_currency')['symbol'] ?? '₹';
        foreach ($results as $index => $getPass) {  
                $result[] = [
                    $getPass->get_pass_no,
                    $getPass->get_pass_id,
                    $getPass->po_number,
                    optional($getPass->inventory->branch)->name ?? '',
                    optional($getPass->inventory->product)->product_name ?? $getPass->inventory->buyer_product_name,
                    $getPass->inventory->buyer_product_name ?? '',
                    cleanInvisibleCharacters($getPass->inventory->specification ?? ''),
                    cleanInvisibleCharacters($getPass->inventory->size ?? ''),
                    $getPass->inventory->inventory_grouping ?? '',
                    $getPass->vendor_name,
                    $getPass->vendor_invoice_number,
                    $getPass->transporter_name,
                    $getPass->vehicle_no_lr_no,
                    " ".$getPass->gross_wt,
                    $getPass->updated_at ? $getPass->updated_at->format('d/m/Y') : '',
                    " ".NumberFormatterHelper::formatQty($getPass->grn_qty, $currency),
                    optional($getPass->inventory->uom)->uom_name ?? '',
                ];

        }
        
        return response()->json(['data' => $result]);
    }
    public function getFilteredQuery(Request $request)
    {
        if (session('branch_id') != $request->branch_id) {
            session(['branch_id' => $request->branch_id]);
        }

        $query = GetPass::with(['inventory', 'company', 'inventory.product','updatedBy','manualOrder.vendor','manualOrderProduct']);

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

        return $query->orderByDesc('updated_at');
    }

}