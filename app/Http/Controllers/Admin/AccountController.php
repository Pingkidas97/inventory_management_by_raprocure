<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Buyer;
use App\Models\Vendor;
use App\Models\Plan;
use App\Models\UserPlan;
use App\Models\User;
use Carbon\Carbon;
use PDF;
class AccountController extends Controller
{
    public function __construct()
    {
        if (auth()->check() && auth()->user()->user_type != 3) {
            abort(403, 'Unauthorized access.');
        }
    }
    public function vendor(Request $request)
    {
        $query = Vendor::with(['user','latestPlan']);

        if ($request->filled('vendor_name')) {
            $query->where('legal_name', 'like', '%' . $request->input('vendor_name') . '%');
        }
        if ($request->filled('from_date') || $request->filled('to_date')) {
            $query->whereHas('latestPlan', function ($q) use ($request) {
                if ($request->filled('from_date')) {
                    $q->where('next_renewal_date', '>=', $request->input('from_date'));
                }
                if ($request->filled('to_date')) {
                    $q->where('next_renewal_date', '<=', $request->input('to_date'));
                }
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('latestPlan', function ($q) use ($request) {
                $q->where('is_expired',$request->input('status'));
            });
        }
        
        $perPage = $request->input('per_page', 25); // default to 25 if not present
        $results = $query->paginate($perPage)->appends($request->all());
        
        $managers=User::where('parent_id',2)->get();
        if ($request->ajax()) {
            return view('admin.account.partials.vendor-table', compact('results','managers'))->render();
        }
        return view('admin.account.vendor', compact('results','managers'));
    }

    public function vendorManager(Request $request)
    {
        $id=$request->user_id;
        $manager_id=$request->manager_id;
        $vendor = Vendor::find($id);
        $vendor->assigned_manager = $manager_id;
        $vendor->save();
        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }
    public function vendorPlanExtend(Request $request)
    {
        $extend_month = $request->extend_month;
        $user_id = $request->user_id;
        $user_plan=$request->user_plan;
        $buyer = Buyer::find($user_id);
        if(!empty($extend_month))
        {
            if(!empty($buyer))
            {
                $userPlan=UserPlan::find($user_plan);
                if(!empty($userPlan))
                {
                    $plan=Plan::find($userPlan->plan_id);
                    if(!empty($plan))
                    {
                        if($plan->price>0)
                        {
                            return response()->json(['status' => false, 'message' => 'You can not extend the plan. Extend only free plan']);
                        }else{
                            $currentDate=date('Y-m-d');
                            // Set base date and extension
                            $currentDate = Carbon::parse($currentDate);
                            $extendedDate = $currentDate->copy()->addMonths($extend_month);
                            // Format dates
                            $newDate = $extendedDate->format('Y-m-d');
                            $newReDate = $extendedDate->format('d/m/Y');
                            // Prepare update data
                            $updata = [
                                'next_renewal_date' => $newDate,
                                'is_expired' => 2,
                                'extend_month' => !empty($result->extend_month)
                                    ? $result->extend_month . '@#' . $extend_month
                                    : $extend_month,
                                'old_renewal_date' => !empty($result->old_renewal_date)
                                    ? $result->old_renewal_date . '@#' . $result->next_renewal_date
                                    : $result->next_renewal_date,
                                'subscription_period' => match ($extend_month) {
                                    1 => '1 Month',
                                    12 => '1 Year',
                                    default => $extend_month . ' Months',
                                },
                            ];
                            $userPlan->update($updata);
                            return response()->json(['status' => true, 'message' => 'Plan extended successfully.']);
                        }
                    }else{
                        return response()->json(['status' => false, 'message' => 'Plan not found.']);
                    }
                }else{
                    return response()->json(['status' => false, 'message' => 'User plan not found.']);
                }
            }else{
                return response()->json(['status' => false, 'message' => 'Buyer not found.']);
            }
        }else{
            return response()->json(['status' => false, 'message' => 'Please enter extend month.']);
        }
    }

    public function vendorPlanView(Request $request)
    {
        $id=$request->id;
        $vendorPlan=UserPlan::find($id);
        $vendor=Vendor::where('user_id',$vendorPlan->user_id)->first();
        $user=User::find($vendorPlan->user_id);
        return view('admin.account.vendor-plan-view',compact('vendorPlan','vendor','user'));
    }

    public function updateFreePlanForAllVendors(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['status' => 0, 'message' => 'Invalid request.'], 400);
        }
        $request->validate([
            'fromDateInput' => 'required|date',
            'toDateInput' => 'required|date|after_or_equal:fromDateInput',
            'selectedYears' => 'required|integer|min:1',
        ]);
        $fromDate = Carbon::parse($request->input('fromDateInput'))->startOfDay();
        $toDate = Carbon::parse($request->input('toDateInput'))->endOfDay();
        $extendYears = (int) $request->input('selectedYears');
        $extendMonths = $extendYears * 12;
        // Fetch vendor plans within date range
        $vendorPlans = UserPlan::where('user_type', 2)
            ->where('is_expired', 2)
            ->where('plan_id', 12)
            ->whereBetween('next_renewal_date', [$fromDate, $toDate])
            ->get();
        if ($vendorPlans->isEmpty()) {
            return response()->json(['status' => 0, 'message' => 'No vendor plans found in the date range.']);
        }
        $now = Carbon::now();
        foreach ($vendorPlans as $plan) {
            $newRenewalDate = $now->copy()->addMonths($extendMonths);
            $plan->update([
                'next_renewal_date' => $newRenewalDate,
                'is_expired' => 2,
                'extend_month' => $plan->extend_month
                    ? $plan->extend_month . '@#' . $extendMonths
                    : $extendMonths,
                'old_renewal_date' => $plan->old_renewal_date
                    ? $plan->old_renewal_date . '@#' . $plan->next_renewal_date
                    : $plan->next_renewal_date,
                'subscription_period' => $this->getSubscriptionPeriodText($extendYears),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['status' => true, 'message' => $vendorPlans->count() . ' vendor plan(s) updated successfully.']);
    }

    public function vendorPlanInvoice($id)
    {
        $plan = UserPlan::with('user')->findOrFail($id);
        $vendor = Vendor::where('user_id', $plan->user_id)->first();
        // Load a Blade view and pass data
        PDF::setOptions([
            'isRemoteEnabled' => true,
            'defaultMediaType' => 'all',
            'defaultFont' => 'sans-serif',
            'dpi' => 150,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true
        ]);
        $pdf = PDF::loadView('admin.account.vendor-plan-invoice', ['plan' => $plan,'vendor'=>$vendor]);
        $fileName = 'Invoice_' . $plan->id . '.pdf';
        return $pdf->download($fileName);
    }


    public function exportVendorTotal(Request $request)
    {
        $query = Vendor::with(['user','latestPlan']);
        if ($request->filled('vendor_name')) {
            $query->where('legal_name', 'like', '%' . $request->input('vendor_name') . '%');
        }
        if ($request->filled('from_date') || $request->filled('to_date')) {
            $query->whereHas('latestPlan', function ($q) use ($request) {
                if ($request->filled('from_date')) {
                    $q->where('next_renewal_date', '>=', $request->input('from_date'));
                }
                if ($request->filled('to_date')) {
                    $q->where('next_renewal_date', '<=', $request->input('to_date'));
                }
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('latestPlan', function ($q) use ($request) {
                $q->where('is_expired',$request->input('status'));
            });
        }
        $total = $query->count();
        return response()->json(['total' => $total]);
    }

    public function exportVendorBatch(Request $request)
    {
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));
        $query = Vendor::with(['user','latestPlan']);
        if ($request->filled('vendor_name')) {
            $query->where('legal_name', 'like', '%' . $request->input('vendor_name') . '%');
        }
        if ($request->filled('from_date') || $request->filled('to_date')) {
            $query->whereHas('latestPlan', function ($q) use ($request) {
                if ($request->filled('from_date')) {
                    $q->where('next_renewal_date', '>=', $request->input('from_date'));
                }
                if ($request->filled('to_date')) {
                    $q->where('next_renewal_date', '<=', $request->input('to_date'));
                }
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('latestPlan', function ($q) use ($request) {
                $q->where('is_expired',$request->input('status'));
            });
        }
        $results= $query->offset($offset)->limit($limit)->get();
        $result=[];
        foreach($results as $k=> $res){
            $result[]=[
                $res->legal_name ?? '',
                $res->latestPlan->plan_name?? '',
                $res->latestPlan->invoice_no ?? '',
                $res->latestPlan->no_of_users??'' ,
                $res->vendor_code ?? '',
                (!empty($res->user->country_code)?'+'.$res->user->country_code:'').' '.$res->user->mobile,
                $res->user->email ?? '',
                $res->latestPlan->start_date ?? '',
                $res->latestPlan->subscription_period ?? '',
                $res->latestPlan->next_renewal_date ?? '' ,
                !empty($res->latestPlan)?'₹ '.$res->latestPlan->final_amount : '' ,
                $res->assigned_manager
            ];
        }
        return response()->json(['data'=>$result]);
    }

    
    private function getSubscriptionPeriodText($years)
    {
        return $years === 1 ? '1 Year' : "$years Years";
    }
    
    public function buyer(Request $request)
    {
        $query = Buyer::with(['users','latestPlan']);
        if ($request->filled('buyer_name')) {
            $query->where('legal_name', 'like', '%' . $request->input('buyer_name') . '%');
        }
        if ($request->filled('from_date') || $request->filled('to_date')) {
            $query->whereHas('latestPlan', function ($q) use ($request) {
                if ($request->filled('from_date')) {
                    $q->where('next_renewal_date', '>=', $request->input('from_date'));
                }
                if ($request->filled('to_date')) {
                    $q->where('next_renewal_date', '<=', $request->input('to_date'));
                }
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('latestPlan', function ($q) use ($request) {
                $q->where('is_expired',$request->input('status'));
            });
        }
        $perPage = $request->input('per_page', 25); // default to 25 if not present
        $results = $query->paginate($perPage)->appends($request->all());
        $managers=User::where('parent_id',2)->get();
        if ($request->ajax()) {
            return view('admin.account.partials.buyer-table', compact('results','managers'))->render();
        }
        return view('admin.account.buyer', compact('results','managers'));
    }

    public function buyerManager(Request $request)
    {
        $id=$request->user_id;
        $manage_id=$request->manage_id;
        $buyer = Buyer::find($id);
        $buyer->assigned_manager = $manage_id;
        $buyer->save();
        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }

    public function buyerPlanExtend(Request $request)
    {
        $extend_month = $request->extend_month;
        $user_id = $request->user_id;
        $user_plan=$request->user_plan;
        $buyer = Buyer::find($user_id);
        if(!empty($extend_month))
        {
            if(!empty($buyer))
            {
                $userPlan=UserPlan::find($user_plan);
                if(!empty($userPlan))
                {
                    $plan=Plan::find($userPlan->plan_id);
                    if(!empty($plan))
                    {
                        if($plan->price>0)
                        {
                            return response()->json(['status' => false, 'message' => 'You can not extend the plan. Extend only free plan']);
                        }else{
                            $currentDate=date('Y-m-d');
                            // Set base date and extension
                            $currentDate = Carbon::parse($currentDate);
                            $extendedDate = $currentDate->copy()->addMonths($extend_month);
                            // Format dates
                            $newDate = $extendedDate->format('Y-m-d');
                            $newReDate = $extendedDate->format('d/m/Y');
                            // Prepare update data
                            $updata = [
                                'next_renewal_date' => $newDate,
                                'is_expired' => 2,
                                'extend_month' => !empty($result->extend_month)
                                    ? $result->extend_month . '@#' . $extend_month
                                    : $extend_month,
                                'old_renewal_date' => !empty($result->old_renewal_date)
                                    ? $result->old_renewal_date . '@#' . $result->next_renewal_date
                                    : $result->next_renewal_date,
                                'subscription_period' => match ($extend_month) {
                                    1 => '1 Month',
                                    12 => '1 Year',
                                    default => $extend_month . ' Months',
                                },
                            ];
                            $userPlan->update($updata);
                            return response()->json(['status' => true, 'message' => 'Plan extended successfully.']);
                        }
                    }else{
                        return response()->json(['status' => false, 'message' => 'Plan not found.']);
                    }
                }else{
                    return response()->json(['status' => false, 'message' => 'User plan not found.']);
                }
            }else{
                return response()->json(['status' => false, 'message' => 'Buyer not found.']);
            }
        }else{
            return response()->json(['status' => false, 'message' => 'Please enter extend month.']);
        }
    }

    public function buyerPlanView(Request $request)
    {
        $id=$request->id;
        $buyerPlan=UserPlan::find($id);
        $buyer=Buyer::where('user_id',$buyerPlan->user_id)->first();
        $user=User::find($buyerPlan->user_id);
        return view('admin.account.buyer-plan-view',compact('buyerPlan','buyer','user'));
    }

    public function buyerPlanInvoice($id)
    {
        $plan = UserPlan::with('user')->findOrFail($id);
        $buyer=Buyer::where('user_id',$plan->user_id)->first();
        // Load a Blade view and pass data
        //return view('admin.account.buyer-plan-invoice', ['plan' => $plan,'buyer'=>$buyer]);
        PDF::setOptions([
            'isRemoteEnabled' => true,
            'defaultMediaType' => 'all',
            'defaultFont' => 'sans-serif',
            'dpi' => 150,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true
        ]);
        
        $pdf = PDF::loadView('admin.account.buyer-plan-invoice', ['plan' => $plan,'buyer'=>$buyer]);
        
        $fileName = 'Invoice_' . $plan->id . '.pdf';
        return $pdf->download($fileName);
    }

    public function exportBuyerTotal(Request $request)
    {
        $query = Buyer::with(['users','latestPlan']);
        if ($request->filled('buyer_name')) {
            $query->where('legal_name', 'like', '%' . $request->input('buyer_name') . '%');
        }
        if ($request->filled('from_date') || $request->filled('to_date')) {
            $query->whereHas('latestPlan', function ($q) use ($request) {
                if ($request->filled('from_date')) {
                    $q->where('next_renewal_date', '>=', $request->input('from_date'));
                }
                if ($request->filled('to_date')) {
                    $q->where('next_renewal_date', '<=', $request->input('to_date'));
                }
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('latestPlan', function ($q) use ($request) {
                $q->where('is_expired',$request->input('status'));
            });
        }
        $total = $query->count();
        return response()->json(['total' => $total]);
    }

    public function exportBuyerBatch(Request $request)
    {
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));
        $query = Buyer::with(['users','latestPlan']);
        if ($request->filled('buyer_name')) {
            $query->where('legal_name', 'like', '%' . $request->input('buyer_name') . '%');
        }
        if ($request->filled('from_date') || $request->filled('to_date')) {
            $query->whereHas('latestPlan', function ($q) use ($request) {
                if ($request->filled('from_date')) {
                    $q->where('next_renewal_date', '>=', $request->input('from_date'));
                }
                if ($request->filled('to_date')) {
                    $q->where('next_renewal_date', '<=', $request->input('to_date'));
                }
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('latestPlan', function ($q) use ($request) {
                $q->where('is_expired',$request->input('status'));
            });
        }
        $results= $query->offset($offset)->limit($limit)->get();
        $result=[];
        foreach($results as $k=> $res){
            $result[]=[
                $res->legal_name ?? '',
                $res->latestPlan->plan_name?? '',
                $res->latestPlan->invoice_no ?? '',
                $res->latestPlan->no_of_users??'' ,
                $res->vendor_code ?? '',
                !empty($res->users)?((!empty($res->users->country_code)?'+'.$res->users->country_code:'').' '.$res->users->mobile):'',
                $res->users->email ?? '',
                $res->latestPlan->start_date ?? '',
                $res->latestPlan->subscription_period ?? '',
                $res->latestPlan->next_renewal_date ?? '' ,
                !empty($res->latestPlan)?'₹ '.$res->latestPlan->final_amount :'' ,
                $res->assigned_manager
            ];
        }
        return response()->json(['data'=>$result]);
    }
}