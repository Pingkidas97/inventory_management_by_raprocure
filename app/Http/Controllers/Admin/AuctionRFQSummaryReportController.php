<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RfqAuction;
class AuctionRFQSummaryReportController extends Controller
{
    public function index(Request $request)
    {   
        $query=RfqAuction::with('rfq_vendor_auction.vendor','rfq_auction_variant','rfq_auction_variant.product','buyer');
        
        if ($request->filled('buyer_name'))
        {
            $legal_name=$request->buyer_name;
            $query->whereHas('rfq_auction.buyer', function ($q) use ($legal_name) {
                $q->where('legal_name', 'like', "%$legal_name%");
            });
        }
        // Filter by date range (auction_date)
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('auction_date', [$request->from_date, $request->to_date]);
        } elseif ($request->filled('from_date')) {
            $query->where('auction_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->where('auction_date', '<=', $request->to_date);
        }
        $perPage = $request->input('per_page', 25);
        $results = $query->orderBy('auction_date', 'desc')->paginate($perPage)->appends($request->all());
        //$results = $query->orderBy('id')->paginate(25);
         
        if ($request->ajax()) {
            return view('admin.reports.partials.auction-rfq-summary-report-table', compact('results'))->render();
        }
        return view('admin.reports.auction-rfq-summary-report', compact('results'));
    }

    public function exportTotal(Request $request)
    {
        $query=RfqAuction::with('rfq_vendor_auction.vendor','rfq_auction_variant','rfq_auction_variant.product','buyer');
        if ($request->filled('buyer_name'))
        {
            $legal_name=$request->buyer_name;
            $query->whereHas('rfq_auction.buyer', function ($q) use ($legal_name) {
                $q->where('legal_name', 'like', "%$legal_name%");
            });
        }
        // Filter by date range (auction_date)
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('auction_date', [$request->from_date, $request->to_date]);
        } elseif ($request->filled('from_date')) {
            $query->where('auction_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->where('auction_date', '<=', $request->to_date);
        }
        $results = $query->orderBy('auction_date')->get();
        return response()->json(['total' => $results->count()]);
    }

    public function exportBatch(Request $request)
    {
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));
        $query=RfqAuction::with('rfq_vendor_auction.vendor','rfq_auction_variant','rfq_auction_variant.product','buyer');
        if ($request->filled('buyer_name'))
        {
            $legal_name=$request->buyer_name;
            $query->whereHas('rfq_auction.buyer', function ($q) use ($legal_name) {
                $q->where('legal_name', 'like', "%$legal_name%");
            });
        }
        // Filter by date range (auction_date)
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('auction_date', [$request->from_date, $request->to_date]);
        } elseif ($request->filled('from_date')) {
            $query->where('auction_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->where('auction_date', '<=', $request->to_date);
        }
        $data_list = $query->orderBy('auction_date')->skip($offset)->take($limit)->get();
        $result = [];
        foreach ($data_list as $value) {
            $result[]=[
                ($value->rfq_no??''),
                ( date('d/m/Y', strtotime($value->auction_date)) ?? ''),
                ( date('h:i A', strtotime($value->auction_start_time)) ).' To '.( date('h:i A', strtotime($value->auction_end_time)) ),
                ($value->buyer?->legal_name??''),
                ($value->rfq_auction_variant->product->product_name ?? ''),
                ($value->rfq_vendor_auction->vendor?->legal_name),
                ($value->rfq_vendor_auction->vendor?->user->email),
                ($value->rfq_vendor_auction->vendor?->user->mobile),
                ($value->rfq_vendor_auction->vendor?->user->status==1?'Active':'Inactive'),
                '',
               ''];
        }
        return response()->json(['data' => $result]);
    }

}
