<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Rfq;
use App\Models\RfqVendor;
use App\Models\RfqVendorQuotation;
use App\Models\Order;
use App\Models\VendorProduct;
use Illuminate\Support\Facades\DB;

class VendorActivityReportController extends Controller
{
    /**
     * Display the vendor activity report with filters and statistics.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 25); // Pagination size

        /**
         * STEP 1: Prepare base Vendor query with eager loading for user, city, and state
         */
        $vendorsQuery = Vendor::with([
                'user:id,name,email,mobile,last_login',
                'vendor_city:id,city_name',
                'vendor_state:id,name',
            ])
            ->select([
                'id',
                'user_id',
                'legal_name',
                'created_at',
                'gstin as gst_no',
                'registered_address as address',
                'city',
                'state',
            ])->where('t_n_c',1)
            ->orderByDesc('created_at');

        /**
         * STEP 2: Apply filters
         */
        if ($request->filled('vendor_name')) {
            $vendorsQuery->where('legal_name', 'like', '%' . $request->vendor_name . '%');
        }

        if ($request->filled('registered_address')) {
            $vendorsQuery->where('registered_address', 'like', '%' . $request->registered_address . '%');
        }

        if ($request->filled('from_date')) {
            $vendorsQuery->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $vendorsQuery->whereDate('created_at', '<=', $request->to_date);
        }

        /**
         * STEP 3: Paginate the result
         */
        $vendors = $vendorsQuery->paginate($perPage)->withQueryString();

        // Extract vendor and user IDs for batch queries
        $vendorIds = $vendors->pluck('id')->all();
        $userIds   = $vendors->pluck('user_id')->all();

        /**
         * STEP 4: Batch fetch related statistics to reduce N+1 queries
         */

        // Total RFQs received per vendor (from rfq_vendors table)
        $rfqReceived = RfqVendor::whereIn('vendor_user_id', $userIds)
            ->selectRaw('vendor_user_id, COUNT(*) as count')
            ->groupBy('vendor_user_id')
            ->pluck('count', 'vendor_user_id')
            ->toArray();

        // Total quotations submitted (from rfq_vendor_quotations + rfq_vendors + rfq_product_variants)
        $quotationGiven = DB::table('rfq_vendor_quotations as q')
            ->join('rfq_vendors as v', 'q.vendor_id', '=', 'v.vendor_user_id')
            ->join('rfq_product_variants as pv', function ($join) {
                $join->on('q.rfq_product_variant_id', '=', 'pv.id')
                     ->on('v.rfq_id', '=', 'pv.rfq_id');
            })
            ->whereIn('q.vendor_id', $userIds)
            ->select('q.vendor_id', DB::raw('COUNT(q.id) as count'))
            ->groupBy('q.vendor_id')
            ->pluck('count', 'q.vendor_id')
            ->toArray();

        // Confirmed orders count and total value per vendor
        $confirmedOrders = Order::whereIn('vendor_id', $userIds)
            ->where('order_status', 1)
            ->select(DB::raw('vendor_id, COUNT(*) as count, SUM(order_total_amount) as total_value'))
            ->groupBy('vendor_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->vendor_id => [
                        'count'       => $item->count,
                        'total_value' => $item->total_value,
                    ]
                ];
            })
            ->toArray();

        // Verified products count per vendor
        $verifiedProducts = VendorProduct::whereIn('vendor_id', $userIds)
            ->where('vendor_status', 1)
            ->select(DB::raw('vendor_id, COUNT(*) as count'))
            ->groupBy('vendor_id')
            ->pluck('count', 'vendor_id')
            ->toArray();

        // Last login date for each user
        $lastLogins = DB::table('user_session')
            ->whereIn('user_id', $userIds)
            ->select('user_id', DB::raw('MAX(updated_date) as last_login'))
            ->groupBy('user_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->user_id => (!empty($item->last_login) && $item->last_login !== '0000-00-00 00:00:00')
                        ? date('d/m/Y', strtotime($item->last_login))
                        : '-'
                ];
            })
            ->toArray();

        /**
         * STEP 5: Combine all summary data into a clean collection
         */
        $summary = $vendors->getCollection()->map(function ($vendor) use (
            $rfqReceived, $quotationGiven, $confirmedOrders, $verifiedProducts, $lastLogins
        ) {
            $user    = $vendor->user;
            $userId  = $vendor->user_id;
            $orders  = $confirmedOrders[$userId] ?? ['count' => 0, 'total_value' => 0];

            return [
                'user_id'                   => $userId,
                'vendor_name'               => $vendor->legal_name,
                'primary_contact'           => $user?->name ?? '-',
                'phone_no'                  => $user?->mobile ?? '-',
                'email'                     => $user?->email ?? '-',
                'gst_no'                    => $vendor->gst_no,
                'registered_address'        => $vendor->address,
                'state'                     => $vendor->vendor_state?->name ?? '',
                'city'                      => $vendor->vendor_city?->city_name ?? '',
                'created'                   => optional($vendor->created_at)->format('d/m/Y') ?? '-',
                'total_rfq_received'        => $rfqReceived[$userId] ?? 0,
                'total_quotation'           => $quotationGiven[$userId] ?? 0,
                'total_confirmed_orders'    => $orders['count'],
                'value_of_confirmed_orders' => $orders['total_value'],
                'no_of_verified_product'    => $verifiedProducts[$userId] ?? 0,
                'last_login_date'           => $lastLogins[$userId] ?? '-',
            ];
        });

        // Replace the original vendor collection with the summarized data
        $vendors->setCollection($summary);

        /**
         * STEP 6: Return the view (supports AJAX partial)
         */
        if ($request->ajax()) {
            return view('admin.vendor-activity-report.partials.table', ['vendors' => $vendors])->render();
        }

        return view('admin.vendor-activity-report.index', ['vendors' => $vendors]);
    }
}
