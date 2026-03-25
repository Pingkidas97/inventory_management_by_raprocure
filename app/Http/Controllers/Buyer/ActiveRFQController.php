<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Division;
use App\Models\LiveVendorProduct;
use App\Models\Rfq;
use App\Models\RfqProduct;
use Carbon\Carbon;
use DB;

class ActiveRFQController extends Controller
{
    public function index(Request $request)
    {
        $user_branch_id_only = getBuyerUserBranchIdOnly();

        // DB::enableQueryLog();
        $query = Rfq::select('rfqs.*')
                    ->where('rfqs.buyer_id', getParentUserId())
                    ->whereNotIn('rfqs.buyer_rfq_status', [2, 5, 8, 10])
                    // Apply filter only if any of the inputs are present
                    ->when(
                        $request->filled('division') || $request->filled('category') || $request->filled('product_name'),
                        function ($query1) use ($request) {
                            $query1->whereHas('rfqProducts.masterProduct', function ($q) use ($request) {
                                if ($request->filled('division') && !empty($request->division)) {
                                    $q->where('division_id', $request->division);
                                }
                                if ($request->filled('category') && !empty($request->category)) {
                                    $categories = explode(",", $request->category);
                                    $q->whereIn('category_id', $categories);
                                }
                                if ($request->filled('product_name')) {
                                    $q->where('product_name', 'like', '%' . $request->product_name . '%');
                                }
                            });
                        }
                    )
                    ->with([
                        'rfqVendorQuotations' => function ($q) {
                            $q->where('status', 1);
                        },
                        'rfqProducts.masterProduct',
                        'buyerUser',
                        'buyerBranch' => function ($q) {
                            $q->where('user_type', 1);
                        },
                        'rfq_auction'=> function ($query) {
                            $query->select('rfq_no', 'auction_date', 'auction_start_time', 'auction_end_time');
                        }
                    ])
                    ->addSelect([
                        'rfq_response_received' => function ($q) {
                            $q->selectRaw('COUNT(DISTINCT vendor_id)')
                                ->from('rfq_vendor_quotations')
                                ->whereColumn('rfq_id', 'rfqs.rfq_id')
                                ->where('status', 1);
                        }
                    ]);

        if (!empty($user_branch_id_only)) {
            $query->whereIn('buyer_branch', $user_branch_id_only);
        }
        if ($request->filled('rfq_no')){
            $query->where('rfq_id', 'like', '%' .$request->rfq_no . '%');
        }
        if ($request->filled('rfq_status') && !empty($request->rfq_status)){
            $query->where('buyer_rfq_status', $request->rfq_status);
        }
        if ($request->filled('prn_number')){
            $query->where('prn_no', "like", '%'.$request->prn_number.'%');
        }
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from_date = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay()->format('Y-m-d H:i:s');
            $to_date = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay()->format('Y-m-d H:i:s');
            
            $query->whereBetween('created_at', [$from_date, $to_date]);
        } else {
            if ($request->filled('from_date')) {
                $from_date = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay()->format('Y-m-d H:i:s');
                $query->where('created_at', '>=', $from_date);
            }

            if ($request->filled('to_date')) {
                $to_date = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay()->format('Y-m-d H:i:s');
                $query->where('created_at', '<=', $to_date);
            }
        }
        
        $query->orderBy('updated_at', 'DESC');
        $query->where('record_type', 2);
        
        $perPage = $request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());

        // dd(DB::getQueryLog());

        if ($request->ajax()) {
            return view('buyer.rfq.active-rfq.partials.table', compact('results'))->render();
        }

        $divisions = Division::where("status", 1)->orderBy('division_name', 'asc')->get();
        $categories = Category::where("status", 1)->get();

        $unique_category = [];
        foreach ($categories as $category) {
            $name = $category->category_name;
            $id = $category->id;
            if (!isset($unique_category[$name])) {
                $unique_category[$name] = [];
            }
            $unique_category[$name][] = $id;
        }
        ksort($unique_category);

        return view('buyer.rfq.active-rfq.index', compact('divisions', 'unique_category', 'results'));
    }
    public function rfq_details(Request $request, $rfq_id)
    {
        return view('buyer.rfq.rfq-details', compact('rfq_id'));
    }
    public function sent_rfq(Request $request)
    {
        return view('buyer.rfq.sent-rfq.index');
    }
}
