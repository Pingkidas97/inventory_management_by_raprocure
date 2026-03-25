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

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 25);

        // Base query for vendors, eager load related user (primary contact)
        $vendors = Vendor::query()
            ->with(['user' => function($q) {
                $q->select('id', 'name', 'email', 'mobile', 'last_login');
            },'vendor_city:id,city_name',
              'vendor_state:id,name',])
            ->select(
                'id',
                'user_id',
                'legal_name',
                'created_at',
                'gstin as gst_no',
                'registered_address as address',
                'city',
                'state'
            )
            ->orderBy('created_at', 'desc');

        // Optional filter by vendor name
        if ($request->filled('vendor_name')) {
            $vendors->where('legal_name', 'like', '%' . $request->vendor_name . '%');
        }

        // Paginate and preserve query string
        $vendors = $vendors->paginate($perPage)->withQueryString();

        // Collect vendor and user IDs for batch queries
        $vendorIds = $vendors->pluck('id')->toArray();
        $userIds = $vendors->pluck('user_id')->toArray();


        $rfqReceived = RfqVendor::whereIn('vendor_user_id', $userIds)
            ->selectRaw('vendor_user_id, COUNT(*) as count')
            ->groupBy('vendor_user_id')
            ->pluck('count', 'vendor_user_id');


        $quotationGiven = DB::table('rfq_vendor_quotations as q')
            ->join('rfq_vendors as v', 'q.vendor_id', '=', 'v.vendor_user_id')
            ->join('rfq_product_variants as pv', function($join) {
                $join->on('q.rfq_product_variant_id', '=', 'pv.id')
                     ->on('v.rfq_id', '=', 'pv.rfq_id');
            })
            ->whereIn('q.vendor_id', $userIds)
            ->select('q.vendor_id', DB::raw('COUNT(q.id) as count'))
            ->groupBy('q.vendor_id')
            ->pluck('count', 'q.vendor_id');

        // $quotationGiven = RfqVendor::whereIn('vendor_user_id', $userIds)
        //                 ->whereIn('vendor_status', [4, 5, 6, 7, 9, 10]) // Only these statuses
        //                 ->selectRaw('vendor_user_id, COUNT(*) as count')
        //                 ->groupBy('vendor_user_id')
        //                 ->pluck('count', 'vendor_user_id');

        $confirmedOrders = Order::whereIn('vendor_id', $userIds)
            ->where('order_status', 1)
            ->select(DB::raw('vendor_id, COUNT(*) as count, SUM(order_total_amount) as total_value'))
            ->groupBy('vendor_id')
            ->get()
            ->keyBy('vendor_id');

       

        $verifiedProducts = VendorProduct::whereIn('vendor_id', $userIds)
            ->where('vendor_status', 1) // 1 = Active/Verified
            ->select(DB::raw('vendor_id, COUNT(*) as count'))
            ->groupBy('vendor_id')
            ->pluck('count', 'vendor_id');



        // Prepare summary data
        $summary = $vendors->getCollection()->map(function($vendor) use (
             $rfqReceived, $quotationGiven, $confirmedOrders, $verifiedProducts
        ) {
            $user = $vendor->user;

           

            $confirmedOrder = $confirmedOrders->get($vendor->user_id);

            return [
                'user_id'             => $vendor->user_id,
                'vendor_name'         => $vendor->legal_name,
                'primary_contact'     => $user?->name,
                'phone_no'            => $user?->mobile,
                'email'               => $user?->email,
                'gst_no'              => $vendor->gst_no,
                'registered_address'  => $vendor->address,
                'state'               => $vendor->vendor_state?->name,   
                'city'                => $vendor->vendor_city?->city_name, 
                'created'             => $vendor->created_at ? $vendor->created_at->format('d/m/Y') : '',
                'total_rfq_received'  => $rfqReceived[$vendor->user_id] ?? 0,
                'total_quotation'     => $quotationGiven[$vendor->id] ?? 0,
                'total_confirmed_orders' => $confirmedOrder->count ?? 0,
                'value_of_confirmed_orders' => $confirmedOrder->total_value ?? 0,
                'no_of_verified_product' => $verifiedProducts[$vendor->user_id] ?? 0,
                'last_login_date'     => $this->getLastLoginDate($vendor->user_id),
            ];
        });

        $vendors->setCollection($summary);

        // Render view (Blade or AJAX partial)
        if ($request->ajax()) {
            return view('admin.vendor-activity-report.partials.table', ['vendors' => $vendors])->render();
        }

        return view('admin.vendor-activity-report.index', ['vendors' => $vendors]);
    }

    public function getLastLoginDate($userId)
    {
        $lastLogin = DB::table('user_session')
            ->where('user_id', $userId)
            ->max('updated_date');

        if (!empty($lastLogin) && $lastLogin !== '0000-00-00 00:00:00') {
            return date('d/m/Y', strtotime($lastLogin));
        }

        return '-';
    }

    public function getVendorRfqQuotationCount($userId)
    {
        return Rfq::join('rfq_vendors', 'rfqs.id', '=', 'rfq_vendors.rfq_id')
            ->whereIn('rfqs.id', function ($query) use ($userId) {
                $query->select('rfq_id')
                      ->from('rfq_product_variants')
                      ->whereIn('id', function ($subQuery) use ($userId) {
                          $subQuery->select('rfq_product_variant_id')
                                   ->from('rfq_vendor_quotations')
                                   ->where('vendor_id', $userId);
                      });
            })
            ->where('rfq_vendors.vendor_user_id', $userId) // Use correct column name
            ->distinct()
            ->count('rfqs.id');
    }


}
