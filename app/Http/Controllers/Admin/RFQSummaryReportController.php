<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rfq;
use App\Models\User;
use App\Models\RfqProduct;
use App\Models\RfqProductVariant;
use App\Models\RfqVendorQuotation;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class RFQSummaryReportController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 25);

        // 1. Build the base query with necessary joins
        $query = Rfq::query()
            ->where('record_type', 2)
            ->join('rfq_vendors', 'rfq_vendors.rfq_id', '=', 'rfqs.rfq_id')
            ->join('users as vendors', 'vendors.id', '=', 'rfq_vendors.vendor_user_id')
            ->join('users as buyer', 'buyer.id', '=', 'rfqs.buyer_user_id')
            ->select([
                'rfqs.rfq_id',
                'rfqs.created_at',
                'rfqs.buyer_rfq_status',
                'buyer.name as buyer_name',
                'vendors.id as vendor_id',
                'vendors.name as vendor_name',
                'vendors.email',
                'vendors.mobile'
            ]);

        // 2. Add filters if present in the request
        if ($request->filled('rfq_id')) {
            $query->where('rfqs.rfq_id', $request->rfq_id);
        }
        if ($request->filled('buyer_name')) {
            $query->where('buyer.name', 'like', '%' . $request->buyer_name . '%');
        }
        if ($request->filled('vendor_name')) {
            $query->where('vendors.name', 'like', '%' . $request->vendor_name . '%');
        }
        if ($request->filled('from_date')) {
            $query->whereDate('rfqs.created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('rfqs.created_at', '<=', $request->to_date);
        }
        if ($request->filled('status')) {
            $query->where('rfqs.buyer_rfq_status', $request->status);
        }
        // Product name filter requires extra joins
        if ($request->filled('product_name')) {
            $query->join('rfq_products', 'rfq_products.rfq_id', '=', 'rfqs.rfq_id')
                ->join('products', 'products.id', '=', 'rfq_products.product_id')
                ->where('products.product_name', 'like', '%' . $request->product_name . '%');
        }

        // 3. Paginate and preserve filters in pagination links
        $paginated = $query->orderBy('rfqs.created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString(); // <-- This is important!

        // 4. If no results, return early
        if ($paginated->isEmpty()) {
            return $this->renderResponse($request, $paginated);
        }

        // 5. Collect IDs for batch queries
        $rfqIds = $paginated->pluck('rfq_id')->unique()->toArray();
        $vendorIds = $paginated->pluck('vendor_id')->unique()->toArray();

        // 6. Fetch related product names
        $productNamesByRfq = RfqProduct::whereIn('rfq_id', $rfqIds)
            ->join('products', 'products.id', '=', 'rfq_products.product_id')
            ->select('rfq_products.rfq_id', \DB::raw('GROUP_CONCAT(products.product_name) as product_names'))
            ->groupBy('rfq_products.rfq_id')
            ->pluck('product_names', 'rfq_id')
            ->toArray();

        // 7. Fetch variant IDs
        $variantIdsByRfq = RfqProductVariant::whereIn('rfq_id', $rfqIds)
            ->select('rfq_id', 'id')
            ->get()
            ->groupBy('rfq_id')
            ->map(fn ($group) => $group->pluck('id')->toArray())
            ->toArray();
        $variantIds = collect($variantIdsByRfq)->flatten()->unique()->toArray();

        // 8. Fetch quoted RFQs
        $quotedRfqs = RfqVendorQuotation::query()
            ->join('rfq_product_variants', 'rfq_product_variants.id', '=', 'rfq_vendor_quotations.rfq_product_variant_id')
            ->whereIn('rfq_vendor_quotations.vendor_id', $vendorIds)
            ->whereIn('rfq_vendor_quotations.rfq_product_variant_id', $variantIds)
            ->select('rfq_vendor_quotations.vendor_id', 'rfq_product_variants.rfq_id')
            ->distinct()
            ->get()
            ->keyBy(fn ($item) => $item->vendor_id . '_' . $item->rfq_id);

        // 9. Fetch confirmed orders
        $confirmedOrders =Order::whereIn('rfq_id', $rfqIds)
            ->whereIn('vendor_id', $vendorIds)
            ->where('order_status', 1)
            ->select('vendor_id', 'rfq_id')
            ->get()
            ->keyBy(fn ($item) => $item->vendor_id . '_' . $item->rfq_id);

        // 10. Prepare summary for each row
        $summary = $paginated->getCollection()->map(function ($item) use (
            $productNamesByRfq,
            $quotedRfqs,
            $confirmedOrders
        ) {
            $key = $item->vendor_id . '_' . $item->rfq_id;
            return [
                'rfq_no' => $item->rfq_id,
                'rfq_date' => $item->created_at ? $item->created_at->format('d/m/Y') : null,
                'buyer_name' => $item->buyer_name,
                'products' => $productNamesByRfq[$item->rfq_id] ?? '',
                'vendor_name' => $item->vendor_name,
                'email' => $item->email,
                'mobile' => $item->mobile,
                'quote_given' => isset($quotedRfqs[$key]) ? 'Yes' : '-',
                'status' => in_array($item->buyer_rfq_status, [8, 10]) ? 'Closed' : 'Active',
                'order_confirmed' => isset($confirmedOrders[$key]) ? 'Yes' : '-',
            ];
        });

        $paginated->setCollection($summary);

        // 11. Return the response (Blade or JSON)
        return $this->renderResponse($request, $paginated);
    }


    /**
     * Render response based on request type
     *
     * @param Request $request
     * @param $summary
     * @return \Illuminate\Http\Response
     */
    private function renderResponse(Request $request, $summary)
    {
        if ($request->ajax()) {
            return view('admin.rfq-summary-report.partials.table', compact('summary'))->render();
        }

        return view('admin.rfq-summary-report.index', compact('summary'));
    }

    public function exportTotal(Request $request)
    {
        $query = \App\Models\Rfq::query()
            ->where('record_type', 2)
            ->join('rfq_vendors', 'rfq_vendors.rfq_id', '=', 'rfqs.rfq_id')
            ->join('users as vendors', 'vendors.id', '=', 'rfq_vendors.vendor_user_id')
            ->join('users as buyer', 'buyer.id', '=', 'rfqs.buyer_user_id');

        // Apply filters
        if ($request->filled('rfq_id')) {
            $query->where('rfqs.rfq_id', $request->rfq_id);
        }
        if ($request->filled('buyer_name')) {
            $query->where('buyer.name', 'like', '%' . $request->buyer_name . '%');
        }
        if ($request->filled('vendor_name')) {
            $query->where('vendors.name', 'like', '%' . $request->vendor_name . '%');
        }
        if ($request->filled('from_date')) {
            $query->whereDate('rfqs.created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('rfqs.created_at', '<=', $request->to_date);
        }
        if ($request->filled('status')) {
            $query->where('rfqs.buyer_rfq_status', $request->status);
        }
        if ($request->filled('product_name')) {
            $query->join('rfq_products', 'rfq_products.rfq_id', '=', 'rfqs.rfq_id')
                ->join('products', 'products.id', '=', 'rfq_products.product_id')
                ->where('products.product_name', 'like', '%' . $request->product_name . '%');
        }

        $total = $query->distinct('rfqs.rfq_id', 'vendors.id')->count();
        return response()->json(['total' => $total]);
    }

    public function exportBatch(Request $request)
    {
        $offset = intval($request->input('start', 0));
        $limit = intval($request->input('limit', 1000));

        $query = \App\Models\Rfq::query()
            ->where('record_type', 2)
            ->join('rfq_vendors', 'rfq_vendors.rfq_id', '=', 'rfqs.rfq_id')
            ->join('users as vendors', 'vendors.id', '=', 'rfq_vendors.vendor_user_id')
            ->join('users as buyer', 'buyer.id', '=', 'rfqs.buyer_user_id')
            ->select([
                'rfqs.rfq_id',
                'rfqs.created_at',
                'rfqs.buyer_rfq_status',
                'buyer.name as buyer_name',
                'vendors.id as vendor_id',
                'vendors.name as vendor_name',
                'vendors.email',
                'vendors.mobile'
            ]);

        // Apply filters
        if ($request->filled('rfq_id')) {
            $query->where('rfqs.rfq_id', $request->rfq_id);
        }
        if ($request->filled('buyer_name')) {
            $query->where('buyer.name', 'like', '%' . $request->buyer_name . '%');
        }
        if ($request->filled('vendor_name')) {
            $query->where('vendors.name', 'like', '%' . $request->vendor_name . '%');
        }
        if ($request->filled('from_date')) {
            $query->whereDate('rfqs.created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('rfqs.created_at', '<=', $request->to_date);
        }
        if ($request->filled('status')) {
            $query->where('rfqs.buyer_rfq_status', $request->status);
        }
        if ($request->filled('product_name')) {
            $query->join('rfq_products', 'rfq_products.rfq_id', '=', 'rfqs.rfq_id')
                ->join('products', 'products.id', '=', 'rfq_products.product_id')
                ->where('products.product_name', 'like', '%' . $request->product_name . '%');
        }

        $rows = $query->orderBy('rfqs.created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();

        // Gather all RFQ and Vendor IDs for batch queries
        $rfqIds = $rows->pluck('rfq_id')->unique()->toArray();
        $vendorIds = $rows->pluck('vendor_id')->unique()->toArray();

        // Get product names for each RFQ
        $productNamesByRfq = \App\Models\RfqProduct::whereIn('rfq_id', $rfqIds)
            ->join('products', 'products.id', '=', 'rfq_products.product_id')
            ->select('rfq_products.rfq_id', \DB::raw('GROUP_CONCAT(products.product_name) as product_names'))
            ->groupBy('rfq_products.rfq_id')
            ->pluck('product_names', 'rfq_id')
            ->toArray();

        // Get variant IDs for each RFQ
        $variantIdsByRfq = \App\Models\RfqProductVariant::whereIn('rfq_id', $rfqIds)
            ->select('rfq_id', 'id')
            ->get()
            ->groupBy('rfq_id')
            ->map(fn ($group) => $group->pluck('id')->toArray())
            ->toArray();
        $variantIds = collect($variantIdsByRfq)->flatten()->unique()->toArray();

        // Get quoted RFQs
        $quotedRfqs = \App\Models\RfqVendorQuotation::query()
            ->join('rfq_product_variants', 'rfq_product_variants.id', '=', 'rfq_vendor_quotations.rfq_product_variant_id')
            ->whereIn('rfq_vendor_quotations.vendor_id', $vendorIds)
            ->whereIn('rfq_vendor_quotations.rfq_product_variant_id', $variantIds)
            ->select('rfq_vendor_quotations.vendor_id', 'rfq_product_variants.rfq_id')
            ->distinct()
            ->get()
            ->keyBy(fn ($item) => $item->vendor_id . '_' . $item->rfq_id);

        // Get confirmed orders
        $confirmedOrders = \App\Models\Order::whereIn('rfq_id', $rfqIds)
            ->whereIn('vendor_id', $vendorIds)
            ->where('order_status', 1)
            ->select('vendor_id', 'rfq_id')
            ->get()
            ->keyBy(fn ($item) => $item->vendor_id . '_' . $item->rfq_id);

        // Prepare export data
        $result = [];
        foreach ($rows as $item) {
            $key = $item->vendor_id . '_' . $item->rfq_id;
            $result[] = [
                $item->rfq_id,
                $item->created_at ? $item->created_at->format('d/m/Y') : '',
                $item->buyer_name,
                $productNamesByRfq[$item->rfq_id] ?? '',
                $item->vendor_name,
                $item->email,
                $item->mobile,
                isset($quotedRfqs[$key]) ? 'Yes' : '-',
                in_array($item->buyer_rfq_status, [8, 10]) ? 'Closed' : 'Active',
                isset($confirmedOrders[$key]) ? 'Yes' : '-',
            ];
        }

        return response()->json(['data' => $result]);
    }




}
