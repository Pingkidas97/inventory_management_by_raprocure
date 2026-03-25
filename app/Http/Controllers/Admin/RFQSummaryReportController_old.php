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
    public function index1(Request $request)
    {
        // Build base query for RFQs
        $query = Rfq::where('record_type', 2);

        // Optional filters
        if ($request->filled('rfq_id')) {
            $query->where('rfq_id', $request->rfq_id);
        }
        if ($request->filled('buyer_name')) {
            $query->whereHas('buyerUser', function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->buyer_name.'%');
            });
        }

        $perPage = $request->input('per_page', 25);
        $rfqs = $query->orderBy('created_at', 'desc')->paginate($perPage)->appends($request->all());

        // Prepare summary data for each RFQ
        $summary = [];
        foreach ($rfqs as $rfq) {
            // Buyer
            $buyer = $rfq->buyerUser ?? $rfq->buyer;

            // Products: Get all product names for this RFQ
            $productNames = RfqProduct::where('rfq_id', $rfq->rfq_id)
                ->join('products', 'products.id', '=', 'rfq_products.product_id')
                ->pluck('products.product_name')
                ->toArray();


            // Vendors (from quotations)
            $vendorQuotations =RfqVendorQuotation::where('rfq_product_variant_id', $rfq->rfq_id)->get();
            $vendorIds = $vendorQuotations->pluck('vendor_id')->unique()->toArray();
           
            $vendors = User::whereIn('id', $vendorIds)->get();

        
            $vendorNames = $vendors->pluck('name')->toArray();


            $vendorEmails = $vendors->pluck('email')->toArray();
            $vendorMobiles = $vendors->pluck('mobile')->toArray();

            // Quote Given
            $quoteGiven = $vendorQuotations->count() > 0 ? 'Yes' : 'No';

            // Order Confirmed
            $orderConfirmed = Order::where('rfq_id', $rfq->rfq_id)
                ->where('order_status', '1')
                ->exists() ? 'Yes' : 'No';

            $summary[] = [
                'rfq_no'         => $rfq->rfq_id,
                'rfq_date'       => $rfq->created_at ? $rfq->created_at->format('d/m/Y') : '',
                'buyer_name'     => $buyer->name ?? '',
                'products'       => implode(',', $productNames),
                'vendor_name'    => implode(',', $vendorNames),
                'email'          => implode(',', $vendorEmails),
                'mobile'         => implode(',', $vendorMobiles),
                'quote_given'    => $quoteGiven,
                'status'         => $this->getRfqStatus($rfq->buyer_rfq_status),
                'order_confirmed'=> $orderConfirmed,
            ];
        }

        // AJAX support: return only the table partial if AJAX
        if ($request->ajax()) {
            return view('admin.rfq-summary-report.partials.table', compact('rfqs', 'summary'))->render();
        }

        // Normal page load
        return view('admin.rfq-summary-report.index', compact('rfqs', 'summary'));
    }

    public function index2(Request $request)
    {
        // Only show real RFQs (not drafts/edits)
        $query = \App\Models\Rfq::where('record_type', 2);

        // Optional filters
        if ($request->filled('rfq_id')) {
            $query->where('rfq_id', $request->rfq_id);
        }
        if ($request->filled('buyer_name')) {
            $query->whereHas('buyerUser', function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->buyer_name.'%');
            });
        }

        $perPage = $request->input('per_page', 25);
        $rfqs = $query->orderBy('created_at', 'desc')->paginate($perPage)->appends($request->all());

        // Prepare summary data for each RFQ
        $summary = [];
        foreach ($rfqs as $rfq) {
            // Buyer
            $buyer = $rfq->buyerUser ?? $rfq->buyer;

            // Products: Get all product names for this RFQ
            $productNames = \App\Models\RfqProduct::where('rfq_id', $rfq->rfq_id)
                ->join('products', 'products.id', '=', 'rfq_products.product_id')
                ->pluck('products.product_name')
                ->toArray();

            // // Vendors (from quotations) - collect all vendor_ids for this RFQ (from all product variants)
            $vendorIds = \App\Models\RfqVendorQuotation::whereIn(
                    'rfq_product_variant_id',
                    \App\Models\RfqProductVariant::where('rfq_id', $rfq->rfq_id)->pluck('id')
                )
                ->pluck('vendor_id')
                ->unique()
                ->toArray();

            // Get all product IDs for this RFQ
            $productIds = \App\Models\RfqVendor::where('rfq_id', $rfq->rfq_id)->pluck('vendor_user_id');

            // // Now get all vendor_ids from quotations for these products
            // $vendorIds = \App\Models\RfqVendorQuotation::whereIn('rfq_product_variant_id', $productIds)
            //     ->pluck('vendor_id')
            //     ->unique()
            //     ->toArray();


            // echo "<pre>";
            // print_r($rfq->rfq_id ); die();

            // Fetch vendor details from users table
            $vendors = \App\Models\User::whereIn('id', $vendorIds)->get();

            $vendorNames = $vendors->pluck('name')->unique()->toArray();
            $vendorEmails = $vendors->pluck('email')->unique()->toArray();
            $vendorMobiles = $vendors->pluck('mobile')->unique()->toArray();

            // Quote Given: If any vendor quotation exists for this RFQ
            $quoteGiven = count($vendorIds) > 0 ? 'Yes' : 'No';

            // Order Confirmed: If any order exists for this RFQ
            $orderConfirmed = \App\Models\Order::where('rfq_id', $rfq->rfq_id)
                ->where('order_status', '1')
                ->exists() ? 'Yes' : 'No';

            $summary[] = [
                'rfq_no'         => $rfq->rfq_id,
                'rfq_date'       => $rfq->created_at ? $rfq->created_at->format('d/m/Y') : '',
                'buyer_name'     => $buyer->name ?? '',
                'products'       => implode(',', $productNames),
                'vendor_name'    => implode(',', $vendorNames),
                'email'          => implode(',', $vendorEmails),
                'mobile'         => implode(',', $vendorMobiles),
                'quote_given'    => $quoteGiven,
                'status'         => $this->getRfqStatus($rfq->buyer_rfq_status),
                'order_confirmed'=> $orderConfirmed,
            ];
        }

        // AJAX support: return only the table partial if AJAX
        if ($request->ajax()) {
            return view('admin.rfq-summary-report.partials.table', compact('rfqs', 'summary'))->render();
        }

        // Normal page load
        return view('admin.rfq-summary-report.index', compact('rfqs', 'summary'));
    }

    public function index3(Request $request)
    {
        $query = \App\Models\Rfq::where('record_type', 2);

        // Optional filters
        if ($request->filled('rfq_id')) {
            $query->where('rfq_id', $request->rfq_id);
        }
        if ($request->filled('buyer_name')) {
            $query->whereHas('buyerUser', function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->buyer_name.'%');
            });
        }

        $perPage = $request->input('per_page', 25);
        $rfqs = $query->orderBy('created_at', 'desc')->paginate($perPage)->appends($request->all());

        $summary = [];
        foreach ($rfqs as $rfq) {
            $buyer = $rfq->buyerUser ?? $rfq->buyer;

            // Get all product names for this RFQ
            $productNames = \App\Models\RfqProduct::where('rfq_id', $rfq->rfq_id)
                ->join('products', 'products.id', '=', 'rfq_products.product_id')
                ->pluck('products.product_name')
                ->toArray();

            // Get all vendor_user_ids for this RFQ from rfq_vendors
            $vendorUserIds = \App\Models\RfqVendor::where('rfq_id', $rfq->rfq_id)
                ->pluck('vendor_user_id')
                ->unique()
                ->toArray();

            // For each vendor, create a row
            foreach ($vendorUserIds as $vendorUserId) {
                $vendor = \App\Models\User::find($vendorUserId);

                // Quote Given: check if this vendor has given a quotation for this RFQ
                $hasQuotation = \App\Models\RfqVendorQuotation::where('vendor_id', $vendorUserId)
                    ->whereIn('rfq_product_variant_id',
                        \App\Models\RfqProductVariant::where('rfq_id', $rfq->rfq_id)->pluck('id')
                    )
                    ->exists();

                $quoteGiven = $hasQuotation ? 'Yes' : 'No';

                // Order Confirmed: check if an order exists for this RFQ and vendor
                $orderConfirmed = \App\Models\Order::where('rfq_id', $rfq->rfq_id)
                    ->where('vendor_id', $vendorUserId)
                    ->where('order_status', '1')
                    ->exists() ? 'Yes' : 'No';

                $summary[] = [
                    'rfq_no'         => $rfq->rfq_id,
                    'rfq_date'       => $rfq->created_at ? $rfq->created_at->format('d/m/Y') : '',
                    'buyer_name'     => $buyer->name ?? '',
                    'products'       => implode(',', $productNames),
                    'vendor_name'    => $vendor->name ?? '',
                    'email'          => $vendor->email ?? '',
                    'mobile'         => $vendor->mobile ?? '',
                    'quote_given'    => $quoteGiven,
                    'status'         => $this->getRfqStatus($rfq->buyer_rfq_status),
                    'order_confirmed'=> $orderConfirmed,
                ];
            }
        }

        if ($request->ajax()) {
             return view('admin.rfq-summary-report.partials.table', compact('rfqs', 'summary'))->render();
        }

        return view('admin.rfq-summary-report.index', compact('rfqs', 'summary'));
    }
    

    public function index4(Request $request)
    {
        $query = DB::table('rfqs')
            ->select([
                'rfqs.rfq_id',
                'rfqs.created_at',
                'rfqs.buyer_rfq_status',
                'buyer.name as buyer_name',
                'products.product_name',
                'vendors.name as vendor_name',
                'vendors.email',
                'vendors.mobile',
                DB::raw('MAX(CASE WHEN quotations.id IS NOT NULL THEN 1 ELSE 0 END) as has_quotation'),
                DB::raw('MAX(CASE WHEN orders.id IS NOT NULL AND orders.order_status = 1 THEN 1 ELSE 0 END) as order_confirmed')
            ])
            ->join('users as buyer', 'buyer.id', '=', 'rfqs.buyer_user_id')
            ->join('rfq_vendors', 'rfq_vendors.rfq_id', '=', 'rfqs.rfq_id')
            ->join('users as vendors', 'vendors.id', '=', 'rfq_vendors.vendor_user_id')
            ->leftJoin('rfq_products', 'rfq_products.rfq_id', '=', 'rfqs.rfq_id')
            ->leftJoin('products', 'products.id', '=', 'rfq_products.product_id')
            ->leftJoin('rfq_product_variants', function($join) {
                $join->on('rfq_product_variants.rfq_id', '=', 'rfqs.rfq_id')
                     ->on('rfq_product_variants.product_id', '=', 'rfq_products.product_id');
            })
            ->leftJoin('rfq_vendor_quotations as quotations', function($join) {
                $join->on('quotations.vendor_id', '=', 'rfq_vendors.vendor_user_id')
                     ->on('quotations.rfq_product_variant_id', '=', 'rfq_product_variants.id');
            })
            ->leftJoin('orders', function($join) {
                $join->on('orders.rfq_id', '=', 'rfqs.rfq_id')
                     ->on('orders.vendor_id', '=', 'rfq_vendors.vendor_user_id');
            })
            ->where('rfqs.record_type', 2)
            ->groupBy([
                'rfqs.rfq_id',
                'rfqs.created_at',
                'rfqs.buyer_rfq_status',
                'buyer.name',
                'products.product_name',
                'vendors.name',
                'vendors.email',
                'vendors.mobile'
            ]);

        // Apply filters
        if ($request->filled('rfq_id')) {
            $query->where('rfqs.rfq_id', $request->rfq_id);
        }
        
        if ($request->filled('buyer_name')) {
            $query->where('buyer.name', 'like', '%'.$request->buyer_name.'%');
        }

        $perPage = $request->input('per_page', 25);
        $summary = $query->orderBy('rfqs.created_at', 'desc')
                        ->paginate($perPage);

        // Transform the results
        $summary->getCollection()->transform(function ($item) {
            return [
                'rfq_no' => $item->rfq_id,
                'rfq_date' => $item->created_at ? Carbon::parse($item->created_at)->format('d/m/Y') : '',
                'buyer_name' => $item->buyer_name ?? '',
                'products' => $item->product_name ?? '',
                'vendor_name' => $item->vendor_name ?? '',
                'email' => $item->email ?? '',
                'mobile' => $item->mobile ?? '',
                'quote_given' => $item->has_quotation ? 'Yes' : 'No',
                'status' => $this->getRfqStatus($item->buyer_rfq_status),
                'order_confirmed' => $item->order_confirmed ? 'Yes' : 'No',
            ];
        });

        if ($request->ajax()) {
            return view('admin.rfq-summary-report.partials.table', [
                'summary' => $summary
            ])->render();
        }

        return view('admin.rfq-summary-report.index', [
            'summary' => $summary
        ]);
    }

    public function index0(Request $request)
    {
        $perPage = $request->input('per_page', 25);

        // Build base query for RFQs with vendor join
        $baseQuery = \App\Models\Rfq::query()
            ->where('record_type', 2)
            ->join('rfq_vendors', 'rfq_vendors.rfq_id', '=', 'rfqs.rfq_id')
            ->join('users as vendors', 'vendors.id', '=', 'rfq_vendors.vendor_user_id')
            ->join('users as buyer', 'buyer.id', '=', 'rfqs.buyer_user_id');

        // Filters
        if ($request->filled('rfq_id')) {
            $baseQuery->where('rfqs.rfq_id', $request->rfq_id);
        }
        if ($request->filled('buyer_name')) {
            $baseQuery->where('buyer.name', 'like', '%'.$request->buyer_name.'%');
        }

        // Select only needed columns
        $baseQuery->select([
            'rfqs.rfq_id',
            'rfqs.created_at',
            'rfqs.buyer_rfq_status',
            'buyer.name as buyer_name',
            'vendors.id as vendor_id',
            'vendors.name as vendor_name',
            'vendors.email',
            'vendors.mobile'
        ]);

        // Paginate at the RFQ-vendor level
        $rfqVendorPage = $baseQuery->orderBy('rfqs.created_at', 'desc')->paginate($perPage);

        // Now, for these RFQ-vendor pairs, fetch products and status
        $summary = $rfqVendorPage->getCollection()->transform(function ($item) {
            // Get all product names for this RFQ
            $productNames = \App\Models\RfqProduct::where('rfq_id', $item->rfq_id)
                ->join('products', 'products.id', '=', 'rfq_products.product_id')
                ->pluck('products.product_name')
                ->toArray();

            // Check if any quotation exists for this vendor and this RFQ
            $variantIds = \App\Models\RfqProductVariant::where('rfq_id', $item->rfq_id)->pluck('id');
            $hasQuotation = \App\Models\RfqVendorQuotation::where('vendor_id', $item->vendor_id)
                ->whereIn('rfq_product_variant_id', $variantIds)
                ->exists();

            // Check if order is confirmed for this vendor and RFQ
            $orderConfirmed = \App\Models\Order::where('rfq_id', $item->rfq_id)
                ->where('vendor_id', $item->vendor_id)
                ->where('order_status', '1')
                ->exists();

            return [
                'rfq_no'         => $item->rfq_id,
                'rfq_date'       => $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') : '',
                'buyer_name'     => $item->buyer_name ?? '',
                'products'       => implode(',', $productNames),
                'vendor_name'    => $item->vendor_name ?? '',
                'email'          => $item->email ?? '',
                'mobile'         => $item->mobile ?? '',
                'quote_given'    => $hasQuotation ? 'Yes' : 'No',
                'status'         => $this->getRfqStatus($item->buyer_rfq_status),
                'order_confirmed'=> $orderConfirmed ? 'Yes' : 'No',
            ];
        });

        // Replace collection with transformed summary
        $rfqVendorPage->setCollection($summary);

        if ($request->ajax()) {
            return view('admin.rfq-summary-report.partials.table', [
                'summary' => $rfqVendorPage
            ])->render();
        }

        return view('admin.rfq-summary-report.index', [
            'summary' => $rfqVendorPage
        ]);
    }

    public function index_0101(Request $request)
    {
        $perPage = $request->input('per_page', 25);

        // Base RFQ query with joins and filters
        $baseQuery = \App\Models\Rfq::query()
            ->where('record_type', 2)
            ->join('rfq_vendors', 'rfq_vendors.rfq_id', '=', 'rfqs.rfq_id')
            ->join('users as vendors', 'vendors.id', '=', 'rfq_vendors.vendor_user_id')
            ->join('users as buyer', 'buyer.id', '=', 'rfqs.buyer_user_id');

        if ($request->filled('rfq_id')) {
            $baseQuery->where('rfqs.rfq_id', $request->rfq_id);
        }

        if ($request->filled('buyer_name')) {
            $baseQuery->where('buyer.name', 'like', '%'.$request->buyer_name.'%');
        }

        // Select necessary fields only
        $baseQuery->select([
            'rfqs.rfq_id',
            'rfqs.created_at',
            'rfqs.buyer_rfq_status',
            'buyer.name as buyer_name',
            'vendors.id as vendor_id',
            'vendors.name as vendor_name',
            'vendors.email',
            'vendors.mobile'
        ]);

        // Paginate
        $paginated = $baseQuery->orderBy('rfqs.created_at', 'desc')->paginate($perPage);

        // Collect RFQ & Vendor IDs in batch
        $rfqIds = $paginated->pluck('rfq_id')->unique()->toArray();
        $vendorIds = $paginated->pluck('vendor_id')->unique()->toArray();

        // Pre-fetch related data in bulk
        $productNamesByRfq = \App\Models\RfqProduct::whereIn('rfq_id', $rfqIds)
            ->join('products', 'products.id', '=', 'rfq_products.product_id')
            ->get()
            ->groupBy('rfq_id')
            ->map(function ($group) {
                return $group->pluck('product_name')->implode(', ');
            });

        $variantIdsByRfq = \App\Models\RfqProductVariant::whereIn('rfq_id', $rfqIds)
            ->get()
            ->groupBy('rfq_id')
            ->map(function ($group) {
                return $group->pluck('id')->toArray();
            });

        $quotedRfqs = \App\Models\RfqVendorQuotation::whereIn('vendor_id', $vendorIds)
            ->whereIn('rfq_product_variant_id', $variantIdsByRfq->flatten()->unique())
            ->get()
            ->groupBy(fn ($item) => $item->vendor_id . '_' . $item->rfq_id)
            ->keys()
            ->flip(); // For fast lookup

        $confirmedOrders = \App\Models\Order::whereIn('rfq_id', $rfqIds)
            ->whereIn('vendor_id', $vendorIds)
            ->where('order_status', 1)
            ->get()
            ->map(fn ($order) => $order->vendor_id . '_' . $order->rfq_id)
            ->flip(); // For fast lookup

        // Transform each item efficiently
        $summary = $paginated->getCollection()->map(function ($item) use (
            $productNamesByRfq,
            $variantIdsByRfq,
            $quotedRfqs,
            $confirmedOrders
        ) {
            $key = $item->vendor_id . '_' . $item->rfq_id;

            return [
                'rfq_no'         => $item->rfq_id,
                'rfq_date'       => optional($item->created_at)->format('d/m/Y'),
                'buyer_name'     => $item->buyer_name,
                'products'       => $productNamesByRfq[$item->rfq_id] ?? '',
                'vendor_name'    => $item->vendor_name,
                'email'          => $item->email,
                'mobile'         => $item->mobile,
                'quote_given'    => $quotedRfqs->has($key) ? 'Yes' : 'No',
                'status'         => $this->getRfqStatus($item->buyer_rfq_status),
                'order_confirmed'=> $confirmedOrders->has($key) ? 'Yes' : 'No',
            ];
        });

        $paginated->setCollection($summary);

        if ($request->ajax()) {
            return view('admin.rfq-summary-report.partials.table', [
                'summary' => $paginated
            ])->render();
        }

        return view('admin.rfq-summary-report.index', [
            'summary' => $paginated
        ]);
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 25);

        // Base RFQ query with joins and filters
        $baseQuery = \App\Models\Rfq::query()
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

        // Apply filters only if provided
        if ($request->filled('rfq_id')) {
            $baseQuery->where('rfqs.rfq_id', $request->rfq_id);
        }

        if ($request->filled('buyer_name')) {
            $baseQuery->where('buyer.name', 'like', '%' . $request->buyer_name . '%');
        }

        if ($request->filled('vendor_name')) {
            $baseQuery->where('vendors.name', 'like', '%' . $request->vendor_name . '%');
        }

        if ($request->filled('from_date')) {
            $baseQuery->whereDate('rfqs.created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $baseQuery->whereDate('rfqs.created_at', '<=', $request->to_date);
        }

        if ($request->filled('status')) {
            $baseQuery->where('rfqs.buyer_rfq_status', $request->status);
        }

        // Join with rfq_products for product_name filter
        if ($request->filled('product_name')) {
            $baseQuery->join('rfq_products', 'rfq_products.rfq_id', '=', 'rfqs.rfq_id')
                ->join('products', 'products.id', '=', 'rfq_products.product_id')
                ->where('products.product_name', 'like', '%' . $request->product_name . '%');
        }

        // Paginate with minimal data
        $paginated = $baseQuery->orderBy('rfqs.created_at', 'desc')->paginate($perPage);

        // Early return for empty results
        if ($paginated->isEmpty()) {
            return $this->renderResponse($request, $paginated);
        }

        // Collect RFQ & Vendor IDs in batch
        $rfqIds = $paginated->pluck('rfq_id')->unique()->toArray();
        $vendorIds = $paginated->pluck('vendor_id')->unique()->toArray();

        // Pre-fetch related data with optimized queries
        $productNamesByRfq = \App\Models\RfqProduct::whereIn('rfq_id', $rfqIds)
            ->join('products', 'products.id', '=', 'rfq_products.product_id')
            ->select('rfq_products.rfq_id', \DB::raw('GROUP_CONCAT(products.product_name) as product_names'))
            ->groupBy('rfq_products.rfq_id')
            ->pluck('product_names', 'rfq_id')
            ->toArray();

        $variantIdsByRfq = \App\Models\RfqProductVariant::whereIn('rfq_id', $rfqIds)
            ->select('rfq_id', 'id')
            ->get()
            ->groupBy('rfq_id')
            ->map(fn ($group) => $group->pluck('id')->toArray())
            ->toArray();

        $variantIds = collect($variantIdsByRfq)->flatten()->unique()->toArray();

        // Fetch quoted RFQs with join to get rfq_id from rfq_product_variants
        $quotedRfqs = \App\Models\RfqVendorQuotation::query()
            ->join('rfq_product_variants', 'rfq_product_variants.id', '=', 'rfq_vendor_quotations.rfq_product_variant_id')
            ->whereIn('rfq_vendor_quotations.vendor_id', $vendorIds)
            ->whereIn('rfq_vendor_quotations.rfq_product_variant_id', $variantIds)
            ->select('rfq_vendor_quotations.vendor_id', 'rfq_product_variants.rfq_id')
            ->distinct()
            ->get()
            ->keyBy(fn ($item) => $item->vendor_id . '_' . $item->rfq_id);

        $confirmedOrders = \App\Models\Order::whereIn('rfq_id', $rfqIds)
            ->whereIn('vendor_id', $vendorIds)
            ->where('order_status', 1)
            ->select('vendor_id', 'rfq_id')
            ->get()
            ->keyBy(fn ($item) => $item->vendor_id . '_' . $item->rfq_id);

        // Transform each item efficiently
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



    // Helper to convert status code to text
    protected function getRfqStatus($status)
    {
        $statuses = [
            1 => 'RFQ Generated',
            2 => 'Scheduled RFQ',
            3 => 'Active RFQ',
            4 => 'Counter Offer Sent',
            5 => 'Order Confirmed',
            6 => 'Counter Offer Received',
            7 => 'Quotation Received',
            8 => 'Closed RFQ',
            9 => 'Partial Order',
            10 => 'Closed with Partial Order',
        ];
        return $statuses[$status] ?? 'Unknown';
    }

}
