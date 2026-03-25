<?php

namespace App\Http\Controllers\Buyer;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Validator};
use Carbon\Carbon;

use App\Models\{Tax, User, Vendor, WorkOrder, Inventories, WorkOrderProduct, Grn, Currency};
use App\Helpers\{NumberFormatterHelper, TruncateWithTooltipHelper};
use App\Http\Controllers\Buyer\InventoryController;
use Illuminate\Support\Facades\Mail;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Rules\NoSpecialCharacters;
use App\Traits\TrimFields;
use Illuminate\Support\Facades\DB;
use App\Helpers\EmailHelper;



class WorkOrderController extends Controller
{
    use TrimFields;
    public static function userCurrency() 
    {
        $userId = Auth::user()->parent_id ?: Auth::id();
        $currencyId = User::find($userId)->currency ==0? 1: User::find($userId)->currency;
        $currency = Currency::find($currencyId);
        $taxes = Tax::where('status', '1')->get(['id', 'tax']);
        session(['user_currency' => [
            'id' => $currency->id,
            'symbol' => $currency->currency_symbol
        ]]);
        $currency_symbol=session('user_currency')['symbol'] ?? '₹';
        $currency_id = User::find($userId)->currency ==0? 1: User::find($userId)->currency;
        $currencies = Currency::select('id', 'currency_symbol','currency_name')->get();
        return response()->json([
            'status' => 'success',
            'data' => [               
                'taxes' => $taxes,
                'currency_symbol' => $currency_symbol,
                'currencies' => $currencies
            ]
        ]);
    }



    public function store(Request $request)
    {
        $request = $this->trimAndReturnRequest($request);
        
        $request->validate([
            'vendor_user_id'     => ['required', 'exists:users,id'],
            'wo_created_date'    => ['required'],
            'currency_id'        => ['required', 'exists:currencies,id'],
            //'inventory_id'       => ['required', 'array'],
            //'inventory_id.*'     => ['required', 'exists:inventories,id'],

            //'qty'                => ['required', 'array'],
            //'qty.*'              => ['required', 'numeric', 'min:0', new NoSpecialCharacters(false)],

            'rate'               => ['required', 'array'],
            'rate.*'             => ['required', 'numeric', 'min:0.01', new NoSpecialCharacters(false)],

            'mrp'               => ['array'],
            'mrp.*'             => ['sometimes', 'nullable','numeric', new NoSpecialCharacters(false)],

            'disc'               => ['array'],
            'disc.*'             => ['sometimes', 'nullable','numeric','max:100', new NoSpecialCharacters(false)],

            'gst'                => ['required', 'array'],
            'gst.*'              => ['required', 'exists:taxes,id'],

            'paymentTerms'       => ['required', 'string', 'max:2000', new NoSpecialCharacters(false)],
            //'priceBasis'         => ['required', 'string', 'max:2000', new NoSpecialCharacters(false)],
            //'deliveryPeriod'     => ['required', 'numeric', 'min:1', 'max:999', new NoSpecialCharacters(false)],

            'remarks'            => ['nullable', 'string', 'max:3000', new NoSpecialCharacters(true)],
            'additionalRemarks'  => ['nullable', 'string', 'max:3000', new NoSpecialCharacters(true)],
        ]);

        $request->merge([
            'rate' => array_map(function ($r) {
                return preg_match('/^\.\d+$/', $r) ? ('0'.$r) : $r;
            }, $request->rate)
            
        ]);
        
        try {
            DB::beginTransaction();
            $userId = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;
            $user = User::with('buyer')->find($userId);
            $orgShortCode = $user->buyer->organisation_short_code ?? 'ORG';
            $year = date('y');
            $lastPoNumber = WorkOrder::where('buyer_id', $userId)
                            ->orderBy('id', 'desc')
                            ->value('work_order_number');
            $parts = explode('-', $lastPoNumber);
            $lastNumber = 0;
            if (!empty($parts) && is_numeric(end($parts))) {
                $lastNumber = (int) end($parts);
            }
            $workOrderNumber = 'WO-' . $orgShortCode . '-' . $year . '-' . sprintf('%03d', $lastNumber+1);
            foreach ($request->rate as $index => $rate) {

                // save cleaned rate back
                $rate=$request->rate[$index];

                // Validate DB double(10,2)
                if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $rate)) {
                    return response()->json([
                        'status' => '2',
                        'message' => 'Rate on row ' . ($index + 1) . ' is out of range. Max allowed: 99,999,999.99',
                    ], 422);
                }
            }
            $wo_created_date =  $request->wo_created_date;
            $wo_created_date = Carbon::createFromFormat('d/m/Y', $wo_created_date)->format('Y-m-d');
            
            $WorkOrder = WorkOrder::create([
                'work_order_number'       => $workOrderNumber,
                'vendor_id'              => $request->vendor_user_id,
                'buyer_id'               => $userId,
                'currency_id'            => $request->currency_id,
                'buyer_user_id'          => Auth::user()->id,
                'branch_id'              => $request->branch_id,
                'order_status'           => '1',
                //'order_price_basis'      => $request->priceBasis,
                'order_payment_term'     => $request->paymentTerms,
                //'order_delivery_period'  => $request->deliveryPeriod,
                'order_remarks'          => $request->remarks,
                'order_add_remarks'      => $request->additionalRemarks,
                'prepared_by'            => Auth::user()->id,
                'approved_by'            => Auth::user()->id,
                'created_at'             => $wo_created_date,
            ]);

            // Store each product
            foreach ($request->prod_dec as $index => $prod_dec) {
                $gstRate = Tax::find($request->gst[$index])?->tax ?? 0;
                $qty=1;
                
                $totalAmount = $qty * $request->rate[$index] * (1 + ($gstRate / 100));
                
                WorkOrderProduct::create([
                    'work_order_id'         => $WorkOrder->id,
                    'product_description'   => $prod_dec,
                    //'inventory_id'          => $request->inventory_id[$index],
                    //'product_quantity'      => $quantity,
                    'product_price'         => $request->rate[$index],
                    'product_mrp'           => ($request->mrp[$index]  ?? '') === '' ? null : $request->mrp[$index],
                    'product_disc'          => ($request->disc[$index] ?? '') === '' ? null : $request->disc[$index],
                    'product_total_amount'  => $totalAmount,
                    'product_gst'           => $request->gst[$index],
                ]);
            }

            // other_terms_conditions
            $other_term_check = $request->input('other_term_check');
            if ($other_term_check && $other_term_check == "1") {
                $other_terms = $request->input('other_terms_textarea');
                $other_terms = substr(cleanGlobalSpecialChar($other_terms), 0, 15600);
                DB::table("other_terms_conditions")->insert([
                    "buyer_id" => getParentUserId(),
                    "buyer_user_id" => Auth::user()->id,
                    "po_number" => $workOrderNumber,
                    "po_type" => 2,
                    "other_terms" => $other_terms,
                    "created_at" => date('Y-m-d H:i:s'),
                    "updated_at" => date('Y-m-d H:i:s')
                ]);
            }

            //start notification
            $notification_data = array();
            $notification_data['po_number'] = $workOrderNumber;
            $notification_data['message_type'] = 'Work Order Confirmed';
            $notification_data['notification_link'] = route('vendor.work_order.index');
            $notification_data['to_user_id'] = $request->vendor_user_id;
            sendNotifications($notification_data);
            //end notification

            DB::commit();
            //start send mail
            $this->sendEmail($workOrderNumber, $request, $wo_created_date);//pingki
            //end send mail

            $fcm_tokens = DB::table('users')->select('fcm_token')->where('id', $request->vendor_user_id)->where('fcm_token', '!=', null)
                            ->get()->pluck('fcm_token')->toArray();

            if(!empty($fcm_tokens)){
                unset($notification_data['to_user_id']);
                $notification_data['fcm_tokens'] = $fcm_tokens;
                sendFirebaseNotifications($notification_data);
            }
            
            return response()->json([
                'status' => '1',
                'message' => 'Work Order generated successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => '2',
                'message' => 'Failed to generate Work Order. ' . $e->getMessage(),
            ]);
        }
    }   

    private function sendEmail($workOrderNumber,  $request, $wo_created_date)
    {
        $order = WorkOrder::where('work_order_number', $workOrderNumber)
                ->where('buyer_user_id', Auth::user()->id)
                ->first();

        if (!$order) {
            return;
        }
        $wo_created_date = Carbon::createFromFormat('Y-m-d', $wo_created_date)->format('d/m/Y');
        $vendor_data = Vendor::where('user_id', $request->vendor_user_id)
            ->with(['user', 'vendor_country', 'vendor_state', 'vendor_city'])
            ->first();

        $subject = "Work Order Confirmed (Order No. " . $order->work_order_number . " )";

        $mail_data = vendorEmailTemplet('order-confirmation-email');
        $admin_msg = $mail_data->mail_message;

        $product_data = $this->getPOVariantHTMLForMail($request,$order->work_order_number, get_currency_str(session('user_currency')['symbol'] ?? '₹'));

        $admin_msg = str_replace('$rfq_date_formate', $wo_created_date, $admin_msg);
        $admin_msg = str_replace('$rfq_number', '', $admin_msg);
        $admin_msg = str_replace('$rfq_no', '', $admin_msg);
        $admin_msg = str_replace('$buyer_name', session('legal_name'), $admin_msg);
        $admin_msg = str_replace('$vendor_name', $vendor_data['legal_name'], $admin_msg);
        $admin_msg = str_replace('$product_details', $product_data, $admin_msg);
        $admin_msg = str_replace('$dispatch_address', '', $admin_msg);
        $admin_msg = str_replace('$delivery_address', '', $admin_msg);
        $admin_msg = str_replace('$order_id', $order->work_order_number, $admin_msg);
        $admin_msg = str_replace('$order_date', $wo_created_date, $admin_msg);
        $admin_msg = str_replace('$website_url', route("login"), $admin_msg);

        EmailHelper::sendMail($vendor_data['user']['email'], $subject, $admin_msg);
    }

    private function getPOVariantHTMLForMail($request,$po_number, $currency_symbol){

        $mail_html = '';
        $total_price = 0;
        // if(!empty($po_variants)){
        if(!empty($request->qty)){
            // foreach ($po_variants as $key => $value) {
            foreach ($request->qty as $index => $quantity) {
                $rate = $request->rate[$index];
                $inventory = Inventories::with(['product', 'uom'])->findOrFail($request->inventory_id[$index]);

                $productName = $inventory->product->product_name;
                $uomName = $inventory->uom->uom_name;
                $sub_total_price = $quantity * $rate ;

                $total_price += $sub_total_price;
                $sub_total_price = number_format((float)$sub_total_price, 2, '.', '');

                $mail_html.= '<tr class="td_class">
                                <td class="td_class">
                                  ' . $productName . '
                                </td>
                                <td class="td_class" style="text-align: center;">
                                  ' . NumberFormatterHelper::formatQty($quantity,session('user_currency')['symbol'] ?? '₹') . '
                                </td>
                                <td class="td_class" style="text-align: center;">
                                  '. $uomName .'
                                </td>
                                <td class="td_class" style="text-align: center;">
                                ' . $currency_symbol .' '. NumberFormatterHelper::formatQty($sub_total_price,session('user_currency')['symbol'] ?? '₹')/*IND_money_format($sub_total_price)*/ . '
                                </td>
                            </tr>';
            }
            // $po_total_amout = number_format((float)$total_price, 2, '.', '');
            $mail_html.='<tr>
                            <td colspan="3" class="td_class">Total</td>
                            <td class="td_class" style="text-align: center;">
                            ' . $currency_symbol .' '.  NumberFormatterHelper::formatQty($sub_total_price,session('user_currency')['symbol'] ?? '₹') /*IND_money_format($po_total_amout)*/ . '
                            </td>
                        </tr>';
        }
        return $mail_html;
    }
    //-------------- --------------Work Order REPORT---------- ---------------------
    public function listdata(Request $request)
    {
        if (!$request->ajax()) return;

        $query = $this->getFilteredQuery($request);

        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);
        $workOrders = $paginated->items();

        $data = [];

        foreach ($workOrders as $row) {

            // Get product descriptions from work_order_products
            $productDescriptions = $row->products
                ->pluck('product_description')
                ->implode(', ');

            // Get total amount from work_order_products
            $totalAmount = $row->products
                ->sum('product_total_amount');

            $currency_symbol = $row->currencyDetails?->currency_symbol 
                ?? (session('user_currency')['symbol'] ?? '₹');

            $data[] = [
                'work_order_number' => $row->work_order_number,

                'order_date' => $row->created_at
                    ? Carbon::parse($row->created_at)->format('d/m/Y')
                    : '',

                // now correct
                'product_names' => TruncateWithTooltipHelper::wrapText($productDescriptions),

                'vendor_name' => optional($row->vendor)->legal_name ?? '',
                'prepared_by' => optional($row->preparedBy)->name ?? '',

                // correct total
                'total_amount' => NumberFormatterHelper::formatCurrency($totalAmount, $currency_symbol),

                'status' => '
                    <a href="javascript:void(0)" 
                    data-url="' . route('buyer.report.workOrder.download', $row->id) . '"
                    class="ra-btn ra-btn-primary font-size-11 export-btn"
                    style="background-color: #043e6c; color: #ffffff !important; border: none; text-align:center;">
                        <span class="bi bi-download d-none d-sm-inline-block"></span>
                        DOWNLOAD
                    </a>
                ',
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $paginated->total(),
            'recordsFiltered' => $paginated->total(),
            'data' => $data,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);
    }

    public function exportTotal(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $total = $query->count();
        return response()->json(['total' => $total]);
    }
    public function exportBatch(Request $request)
    {
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));

        $query = $this->getFilteredQuery($request);

        $results = $query->offset($offset)->limit($limit)->get();
        $result = [];
        $totalAmount = 0;
        foreach ($results as $index => $row) {
            $product = $row->products->first();
            $currency_symbol = $row->currencyDetails?->currency_symbol ?? (session('user_currency')['symbol'] ?? '₹');
            $totalAmount+= $row->products->sum('product_total_amount');
            $result[] = [
                    $index + 1 + $offset,
                    optional($product->inventory->branch)->name ?? '',
                    $row->work_order_number,
                    $row->created_at ? Carbon::parse($row->created_at)->format('d/m/Y') : '',
                    $this->formatProductName($row, $this->filters['search_product_name'] ?? '', $this->filters['search_category_id'] ?? ''),
                    optional($row->vendor)->legal_name ?? '',
                    optional($row->preparedBy)->name ?? '',
                    NumberFormatterHelper::formatCurrency($row->products->sum('product_total_amount'), $currency_symbol),
                    $row->order_status == 1 ? 'Confirmed' : 'Cancelled',
                ];
        }
        $result[] = ['','','','','','','','Total Amount('.$currency_symbol.'):',
            NumberFormatterHelper::formatCurrency($totalAmount, $currency_symbol),
            '',
        ];
        return response()->json(['data' => $result]);
    }

    public function getFilteredQuery(Request $request)
    {
        // Set session branch
        if (session('branch_id') != $request->branch_id) {
            session(['branch_id' => $request->branch_id]);
        }

        $userId = Auth::user()->parent_id ?: Auth::id();

        $query = WorkOrder::with(['vendor', 'preparedBy', 'branch']);

        // Buyer filter (always applied)
        $query->where('buyer_id', $userId);
        $query->where('branch_id', $request->branch_id);

        // Date filter
        if ($request->filled(['from_date', 'to_date'])) {
            $from = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay();
            $to   = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay();

            $query->whereBetween('created_at', [$from, $to]);
        }

        // Order number search
        if ($request->filled('search_order_no')) {
            $query->where('work_order_number', 'like', '%' . $request->search_order_no . '%');
        }

        // Vendor name search
        if ($request->filled('search_vendor_name')) {
            $query->whereHas('vendor', function ($q) use ($request) {
                $q->where('legal_name', 'like', '%' . $request->search_vendor_name . '%');
            });
        }

        // Order status
        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        // Sorting
        $query->orderBy('id', 'desc')->orderBy('created_at', 'desc');

        return $query;
    }


    public function formatProductName($order, $searchProductName = null, $searchCategoryName = null)
    {
        $productNames = $order->products
            ->filter(function ($product) use ($searchProductName, $searchCategoryName) {
                $matchesName = true;
                $matchesCategory = true;

                if ($searchProductName) {
                    $matchesName = stripos($product->product->product_name ?? '', $searchProductName) !== false;
                }

                if ($searchCategoryName) {
                    $searchCategoryId=InventoryController::getIdsByCategoryName($searchCategoryName);
                    $matchesCategory = in_array($product->product->category_id ?? null, $searchCategoryId);
                }

                return $matchesName && $matchesCategory;
            })
            ->pluck('product.product_name')
            ->filter()
            ->unique()
            ->sort()
            ->implode(', ');
        return e($productNames);
    }
   

    //---------------------------------Download work  Order----------------------------------
    public function download($id)
    {
        $order = WorkOrder::with([
            'products.tax',   // 
            'vendor'      // 
        ])->findOrFail($id);
        $branch = \App\Models\BranchDetail::with([
            'branch_city',
            'branch_state',
            'branch_country'
        ])->where('branch_id', $order->branch_id)->first();
        $order->setRelation('branch', $branch);
        // Other terms
        $other_terms = DB::table('other_terms_conditions')
            ->where('po_number', $order->work_order_number)
            ->first();
        $order->branch = $branch;    
        $order->order_other_terms = $other_terms->other_terms ?? '';
        //dd($order);
        // Load PDF
        $pdf = Pdf::loadView('buyer.inventory.downloadWorkOrder', compact('order'))
            ->setPaper('A4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, "Work_Order.pdf", [
            'Content-Type' => 'application/pdf',
        ]);
    }

}
