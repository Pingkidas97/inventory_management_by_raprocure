<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rfq;
use Illuminate\Support\Facades\DB;

class RfqReceivedController extends Controller
{
    public function index(Request $request)
    {
        $query = Rfq::join('rfq_vendors', 'rfqs.rfq_id', '=', 'rfq_vendors.rfq_id')
            ->where('rfq_vendors.vendor_user_id', getParentUserId())
            ->with([
                'rfqVendors',
                'rfqProducts',
                'rfqProducts.masterProduct',
                'buyer'
            ]);
        if ($request->filled('buyer_name')) {
            $legal_name = $request->buyer_name;
            $query->whereHas('buyer', function ($q) use ($legal_name) {
                $q->where('legal_name', 'like', "%$legal_name%");
            });
        }
        if ($request->filled('frq_no')) {
            $query->where('rfqs.rfq_id', $request->frq_no);
        }

        if ($request->filled('status')) {
            $query->where('rfq_vendors.vendor_status', $request->status);
        }
        $order = $request->order;
        if (!empty($order)) {
            $query->orderBy($column[$order['0']['column']], $order['0']['dir']);
        } else {
            $query->orderBy('rfqs.id', 'desc');
        }
        $perPage = $request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());

        if ($request->ajax()) {
            return view('vendor.rfq-received.partials.table', compact('results'))->render();
        }
        return view('vendor.rfq-received.index', compact('results'));
    }
    public function showRfqReplyForm($rfq_id)
    {   
        $vendor_id    = getParentUserId();
        $vendorUserId = getParentUserId();

        // Get RFQ with buyer and buyer_user joined manually
        $rfq = DB::table('rfqs as r')
            ->leftJoin('buyers as b', 'r.buyer_id', '=', 'b.user_id')
            ->leftJoin('users as u', 'r.buyer_user_id', '=', 'u.id')
            ->select(
                'r.*',
                'b.legal_name as buyer_legal_name',
                'u.name as buyer_user_name'
            )
            ->where('r.rfq_id', $rfq_id)
            ->first();

        if (!$rfq) {
            abort(404, 'RFQ not found');
        }


        // Get RFQ Products
        $products = DB::table('rfq_products as rp')
        ->join('products as p', 'rp.product_id', '=', 'p.id')
        ->leftJoin('divisions as d', 'p.division_id', '=', 'd.id')
        ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
        ->leftJoin('vendor_products as vp', function ($join) use ($vendor_id) {
            $join->on('vp.product_id', '=', 'rp.product_id')
                 ->where('vp.vendor_id', '=', $vendor_id);
        })
        ->where('rp.rfq_id', $rfq_id)
        ->select(
            'rp.id as rfq_product_id',
            'rp.product_id',
            'rp.brand',
            'rp.remarks',
            'p.product_name',
            'd.division_name',
            'c.category_name',
            'vp.id as vendor_product_id',
            'vp.image as vendor_product_image',
            'vp.model_no',
            'vp.catalogue',
            'vp.approval_status'
        )
        ->get();


        $variants = DB::table('rfq_product_variants')
            ->where('rfq_id', $rfq_id)
            ->get()
            ->groupBy('product_id');




        // Get vendor's dispatch branches
        $branches = DB::table('branch_details')
            ->where('user_type', 2) // Vendor
            ->where('user_id', $vendorUserId)
            ->where('status', 1)
            ->get();

        $vendor_currency = DB::table('currencies')->where('status', 1)->get();


        return view('vendor.rfq-received.rfq_details', compact('rfq', 'products', 'variants', 'branches', 'vendor_currency', 'vendorUserId'));
    }
}
