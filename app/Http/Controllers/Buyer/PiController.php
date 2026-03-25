<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrdersPi;
class PiController extends Controller
{
    public function index(Request $request)
    {   
        $query=OrdersPi::with('vendorUser')->where('buyer_id',getParentUserId());
        if ($request->filled('order_no')) {
            $query->where('order_number', 'like', '%' . $request->input('order_no') . '%');
        }
        if ($request->filled('vendor_name')) {
            $query->whereHas('vendorUser', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('vendor_name') . '%');
            });
        }
        if ($request->filled('form_date')) {
            $query->where('order_date', '>=', $request->input('form_date'));
        }
        if ($request->filled('to_date')) {
            $query->where('order_date', '<=', $request->input('to_date'));
        }
        $perPage = $request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());
        
        if ($request->ajax()) {
            return view('buyer.rfq.pi.partials.table', compact('results'))->render();
        }
        return view('buyer.rfq.pi.index', compact('results'));
    }
}
