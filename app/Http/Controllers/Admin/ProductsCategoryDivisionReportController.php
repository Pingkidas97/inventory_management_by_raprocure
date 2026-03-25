<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
class ProductsCategoryDivisionReportController extends Controller
{
    public function index(Request $request) {

        $query = $this->querires($request);
        $results = $query->orderBy('product_name')->paginate(25);
        if ($request->ajax()) {
            return view('admin.reports.partials.product-division-category-table', compact('results'))->render();
        }
        return view('admin.reports.product-division-category',compact('results'));
    }


    public function exportTotal(Request $request){
        $query = $this->querires($request);
        $total = $query->count();
        return response()->json(['total' => $total]);
    }

    public function exportBatch(Request $request){
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));
        $query = $this->querires($request);
        $results= $query->offset($offset)->limit($limit)->get();
        $result=[];
        foreach($results as $k=> $res){
            $result[]=[
                $res->product_name ?? '',
                $res->division->division_name?? '',
                $res->category->category_name?? '',
                optional($res->master_alias)->pluck('alias')->implode(', '),
                optional($res->vendor_alias)->pluck('alias')->implode(', '),
                $res->vendor_count,
                $res->created_at->format('d/m/Y'),
                $res->rfq_count,
                $res->order_count,
                $res->status == '1'?'Active':'Inactive'
            ];
        }
        return response()->json(['data'=>$result]);
    }

    private function querires(Request $request)
    {
        $query = Product::with(['division', 'category', 'master_alias', 'vendor_alias'])
                    ->withCount(['rfq_products as rfq_count' => function ($query) {
                                    $query->whereHas('rfq', function ($q) {
                                        //$q->where('status', 'completed'); // Filter by order status
                                    });
                                },'order_variants as order_count' => function ($query) {
                                    $query->whereHas('order', function ($q) {
                                        $q->where('order_status', 1); // Filter by order status
                                    });
                                },'product_vendors as vendor_count' => function ($q) {
                                    //$q->where('status', 'active'); // Example condition
                                }]);

        if ($request->filled('product_name')) {
            $query->where('product_name', 'like', '%' . $request->input('product_name') . '%');
        }
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [$request->input('from_date'), $request->input('to_date')]);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        } elseif ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } 
        return $query;
    }
}
