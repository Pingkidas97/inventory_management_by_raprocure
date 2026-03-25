<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Buyer;

class BuyerActivityReportController extends Controller
{
    public function index(Request $request) {

        $query = Buyer::with(['users','latestPlan','latestPlan.plan','buyerUser','rfqs','orders']);
        $query->whereHas('users', function ($q) use ($request) {
                $q->where('is_profile_verified',1);
            });
        if ($request->filled('buyer_name')) {
            $query->where('legal_name', 'like', '%' . $request->input('buyer_name'));
            // $query->whereHas('users', function ($q) use ($request) {
            //     $q->where('name', 'like', '%' . $request->input('buyer_name') . '%');
            //     $q->orWhere('email', 'like', '%' . $request->input('buyer_name') . '%');
            //     $q->orWhere('mobile', 'like', '%' . $request->input('buyer_name') . '%');
            // });
        }
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [$request->input('from_date'), $request->input('to_date')]);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        } elseif ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }
        $perPage = $request->input('per_page', 25); // default to 25 if not present
        $results = $query->paginate($perPage)->appends($request->all());
       
        if ($request->ajax()) {
            return view('admin.reports.partials.buyer-activity-table', compact('results'))->render();
        }
        return view('admin.reports.buyer-activity', compact('results'));
    }

     public function exportTotal(Request $request){
        $query = Buyer::with(['users','latestPlan','latestPlan.plan','buyerUser','rfqs','orders']);
        $query->whereHas('users', function ($q) use ($request) {
                $q->where('is_profile_verified',1);
            });
        if ($request->filled('buyer_name')) {
            $query->where('legal_name', 'like', '%' . $request->input('buyer_name'));
        }
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [$request->input('from_date'), $request->input('to_date')]);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        } elseif ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }
        $total = $query->count();
        return response()->json(['total' => $total]);
    }

    public function exportBatch(Request $request){
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));
        $query = Buyer::with(['users','latestPlan','latestPlan.plan','buyerUser','rfqs','orders']);
        $query->whereHas('users', function ($q) use ($request) {
                $q->where('is_profile_verified',1);
            });
        if ($request->filled('buyer_name')) {
            $query->where('legal_name', 'like', '%' . $request->input('buyer_name'));
        }
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [$request->input('from_date'), $request->input('to_date')]);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        } elseif ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }
        $results= $query->offset($offset)->limit($limit)->get();
        $result=[];
        foreach($results as $k=> $res){
            $result[]=[
                ($res->legal_name ?? ''),
                ($res->users->name?? ''),
                ($res->users->country_code ?? '').''.($res->users->mobile ?? ''),
                ($res->users->email ?? ''),
                ($res->buyerUser?$res->buyerUser->count()+1:'').'/'.($res->users->latestPlan?($res->users->latestPlan->plan_id==11?$res->users->latestPlan->no_of_users:$res->users->latestPlan->plan->no_of_user):0),
                ($res->rfqs?$res->rfqs->count():0),
                ($res->rfqs?$res->rfqs->where('is_bulk_rfq',1)->count():0),
                '',
                ($res->orders?$res->orders->where('order_status',3)->count():0),
                ($res->orders ? '₹ '.$res->orders->where('order_status', 3)->sum('order_total_amount'):''),
                ($res->getLastLoginDate($res->user_id) ?? '')
            ];
        }
        return response()->json(['data'=>$result]);
    }

}
