<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RfqVendorAuction;
class LiveAuctionRfqController extends Controller
{
    public function index(Request $request)
    {   
        
        $query = RfqVendorAuction::where('vendor_id', getParentUserId())
        ->with([
            'rfq_auction',
            'rfq_auction.rfq',
            'rfq_auction.rfq_auction_variant',
            'rfq_auction.rfq_auction_variant.product',
            'rfq_auction.buyer',
            'rfq_auction.buyer.users'
        ]);
        if ($request->filled('buyer_name'))
        {
            $legal_name=$request->buyer_name;
            $query->whereHas('rfq_auction.buyer', function ($q) use ($legal_name) {
                $q->where('legal_name', 'like', "%$legal_name%");
            });
        }
        if ($request->filled('frq_no')){
            $query->where('rfq_no', $request->frq_no);
        }
        
        if ($request->filled('auction_date')){
            $query->whereHas('rfq_auction', function ($q) use ($request) {
                $q->where('auction_date', $request->auction_date);
            });
        }
        $order=$request->order;
        if (!empty($order)) {
            $query->orderBy($column[$order['0']['column']], $order['0']['dir']);
        } else {
            $query->orderBy('id', 'desc');
        }
        $perPage = $request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());
        // echo '<pre>';
        // print_r($results);die;
        if ($request->ajax()) {
            return view('vendor.live-auction.partials.table', compact('results'))->render();
        }
        return view('vendor.live-auction.index', compact('results'));
    }
}
