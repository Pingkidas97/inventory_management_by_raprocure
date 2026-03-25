<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Buyer;
use App\Models\Currency;
use App\Models\Plan;
use App\Models\UserPlan;
use App\Models\InvoiceNumber;
use App\Models\User;
use App\Models\UserSession;
use App\Models\Division;
use App\Models\Subscription;
use App\Models\LoginAttempt;
use App\Models\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Exports\BuyerExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Helpers\EmailHelper;

class BuyerController extends Controller
{
    public function __construct()
    {
        if (auth()->check() && auth()->user()->user_type != 3) {
            abort(403, 'Unauthorized access.');
        }
    } 

    public function index(Request $request)
    {   
        $currencies= Currency::all();
        $query = Buyer::with(['users']);
        if ($request->filled('user')) {
            $query->whereHas('users', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('user') . '%');
                $q->orWhere('email', 'like', '%' . trim($request->input('user')) . '%');
                $q->orWhere('mobile', 'like', '%' . $request->input('user') . '%');
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('users', function ($q) use ($request) {
                $q->where('status', $request->input('status'));
            });
        }
        $query->orderBy('buyers.updated_at', 'desc');
        $perPage = $request->input('per_page', 25); // default to 25 if not present
        $results = $query->paginate($perPage)->appends($request->all());
        if ($request->ajax()) {
            return view('admin.buyer.partials.table', compact('results','currencies'))->render();
        }

        // echo "<pre>";
        // print_r($results); die();

        return view('admin.buyer.index', compact('results','currencies'));
    }
    public function profileStatus(Request $request)
    {
        $id=$request->user_id;
        $buyer = Buyer::find($id);
        $buyer->users->is_profile_verified = $request->status;
        $buyer->users->save();
        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }

    public function status(Request $request)
    {
        $id=$request->user_id;
        $buyer = Buyer::find($id);
        $buyer->users->status = $request->status;
        $buyer->users->save();

        UserSession::where('user_id', $buyer->user_id)->update(['data' => null]);

        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }

    public function inventoryStatus(Request $request)
    {
        $id=$request->user_id;
        $buyer = Buyer::find($id);
        $buyer->users->is_inventory_enable = $request->status;
        $buyer->users->save();
        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }

    public function apiStatus(Request $request)
    {
        $id=$request->user_id;
        $buyer = Buyer::find($id);
        $buyer->users->is_api_enable = $request->status;
        $buyer->users->save();
        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }

    public function currency(Request $request)
    {
        $id=$request->user_id;
        $buyer = Buyer::find($id);
        $buyer->users->currency = $request->currency;
        $buyer->users->save();
        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }

    public function profile(Request $request, $id)
    {
        $buyer_data = User::with(["buyer", "branchDetails", "topManagamantDetails", "latestPlan"])->where("id", $id)->first();
        if(empty($buyer_data)){
            return redirect()->route('admin.buyer.index')->with('error','Buyer not found');
        }

        $divisions = Division::where("status", 1)
                                ->select("id", "division_name")
                                ->orderBy("division_name", "ASC")
                                ->pluck("division_name", "id")->toArray();
        
        $countries = DB::table("countries")
                            ->select("id", "name")
                            ->orderBy("name", "ASC")
                            ->pluck("name", "id")->toArray();
        
        $director_designations = DB::table("director_designations")
                            ->select("id", "designation_name")
                            ->orderBy("id", "DESC")
                            ->pluck("designation_name", "id")->toArray();

        $isSubscribed = Subscription::where('email', $buyer_data->email)->first();
        $subscribed = false;
        if(!empty($isSubscribed)){
            $subscribed = true;
        }

        $buyer_plan = Plan::where('type', 1)->where('status', 1)->orderBy('no_of_user', 'asc')->get();
        $india_states = array();
        
        return view('admin.buyer.profile', compact('countries', 'buyer_data', 'director_designations', 'divisions', 'india_states', 'subscribed', 'buyer_plan'));
    }
     
    public function updateProfile(Request $request)
    {
        $user_id = $request->user_id;
        if(empty($user_id)){
            return response()->json(['status' => false, 'message' => 'Something Went Wrong..']);
        }

        $plan_id = $request->plan_id;
        if(empty($plan_id)){
            return response()->json(['status' => false, 'message' => 'Please Select a Plan.']);
        }

        $buyer_data = User::with(["buyer"])->where("id", $user_id)->first();
        if(empty($buyer_data)){
            return response()->json(['status' => false, 'message' => 'Buyer not found.']);
        }
        $buyer = $buyer_data->buyer;

        if(empty($buyer->organisation_short_code)){
            return response()->json(['status' => false, 'message' => 'Buyer Profile Not Completed']);
        }
        if(!empty($buyer->buyer_code)){
            return response()->json(['status' => false, 'message' => 'Buyer Profile already Verified']);
        }

        $plan = Plan::find($plan_id);
        if(empty($plan)){
            return response()->json(['status' => false, 'message' => 'Plan not found.']);
        }

        $free_plan = Plan::find(11);

        DB::beginTransaction();

        try {

            // update status in user table
            $buyer_data->is_profile_verified = 1;
            $buyer_data->is_verified = 1;
            $buyer_data->status = 1;
            $buyer_data->verified_by = auth()->user()->id;
            $buyer_data->save();

            // update in user_plan update or insert table
            UserPlan::updateOrCreate(
                ['user_id' => $buyer_data->id], // Unique identifying field
                [
                    'user_type' => 1,
                    'plan_id' => $free_plan->id,
                    'no_of_users' => $plan->no_of_user,
                    'price' => $free_plan->price,
                    'gst' => 18,
                    'final_amount' => 0,
                    'payment_salt' => '',
                    'start_date' => now()->format('Y-m-d'),
                    'subscription_period' => "1 Month",
                    'next_renewal_date' => now()->addDays(30)->format('Y-m-d'),
                    'activated_by' => auth()->user()->id
                ]
            );

            // update buyer code in buyer table
            $state_id = $buyer_data->buyer->state ? $buyer_data->buyer->state : 0;
            $buyer_code = generateBuyerCode($state_id);
            
            $buyer->buyer_code = $buyer_code;
            $buyer->plan_id = $plan->id;
            $buyer->save();
            
            // logout company session
            UserSession::where('user_id', $buyer_data->id)->update(['data' => null]);

            // send verification mail to buyer
            $this->sendBuyerVerificationMail($buyer_data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Buyer Profile verification Successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to complete Buyer Profile. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }

    }

    private function sendBuyerVerificationMail($buyer_data)
    {
        $mail_data = buyerEmailTemplet('Buyer-Verification-Email-trail');
        $mail_msg = $mail_data->mail_message;
        $mail_subject = $mail_data->subject;
        $mail_msg = str_replace('$name', $buyer_data->buyer->legal_name, $mail_msg);
        $mail_msg = str_replace('$link', route('login'), $mail_msg);

        EmailHelper::sendMail($buyer_data->email, $mail_subject, $mail_msg);
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'=>'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => $validator->errors()->first()
            ]);
        }
        
        $user = User::with(["buyer", "branchDetails", "topManagamantDetails"])->where("id", $request->user_id)->first();

        if(empty($user)){
            return response()->json([
                'status' => false,
                'message' => 'Buyer Profile not found.'
            ]);
        }

        if(!empty($user->buyer->buyer_code)){
            return response()->json([
                'status' => false,
                'message' => 'Buyer profile is already verified and cannot be deleted, please refresh the page.'
            ]);
        }

        if($user->status == 1){
            return response()->json([
                'status' => false,
                'message' => 'Buyer is active and cannot be deleted, please make inactive the buyer status.'
            ]);
        }

        DB::beginTransaction();

        try {
            // Delete buyer profile
            if ($user->buyer) {
                if ($user->buyer->logo) {
                    removeFile(public_path('uploads/buyer-profile/'.$user->buyer->logo));
                }
                if ($user->buyer->pan_file) {
                    removeFile(public_path('uploads/buyer-profile/'.$user->buyer->pan_file));
                }
                $user->buyer->delete();
            }

            $email = $user->email;
            
            // Delete branchDetails and their files
            foreach ($user->branchDetails as $branch) {
                if ($branch->gstin_file) {
                    removeFile(public_path('uploads/buyer-profile/'.$branch->gstin_file));
                }
                $branch->delete();
            }

            // Delete all top management details
            foreach ($user->topManagamantDetails as $manager) {
                $manager->delete();
            }

            // Finally, delete the user
            $user->delete();

            // logout company session
            UserSession::where('user_id', $request->user_id)->delete();

            // delete login attempt
            LoginAttempt::where('user_id', $email)->delete();

            // delete notification
            Notification::where('sender_id', $request->user_id)->delete();

            // delete subscription
            Subscription::where('email', $email)->delete();
            
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Buyer successfully deleted.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete Buyer. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }
    }


    public function plan(Request $request,$id)
    {
        $buyer = Buyer::find($id);
        $plans = Plan::where('type', 1)->where('status', 1)->orderBy('no_of_user', 'asc')->get();
        $user_plans = UserPlan::where('user_id', $buyer->users->id)->orderBy('id', 'desc')->first();
        
        return view('admin.buyer.plan', compact('buyer','plans','user_plans'));
    }

    public function planUpdate(Request $request, $id)
    {   
        $validator = Validator::make($request->all(), [
            'plan_id'=>'required|exists:plans,id',
            // 'no_of_user'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first()
            ]);
        }
        $plan_id = $request->plan_id;
        $plan = Plan::find($plan_id);
        if(empty($plan)){
            return response()->json([
                'status' => false,
                'message' => 'Selected Plan not found.'
            ]);
        }

        $buyer = Buyer::find($id);
        $no_of_user = $plan->no_of_user;
        $price = $plan->price;
        $gst = 18;
        $discount = $request->discount;
        $discounted_amount = $price;
        if($discount > 0){
            $discounted_amount = $price - ($price * $discount / 100);
        }else if($discount > 99){
            return response()->json([
                'status' => false,
                'message' => 'Discount can not be greater than 99%'
            ]);
        }
        $total = $discounted_amount + ($discounted_amount * $gst / 100);
        
        DB::beginTransaction();

        try {

            $invoice_no = InvoiceNumber::generateInvoiceNumber($buyer->user_id);
            
            UserPlan::where('user_id', $buyer->user_id)->where('is_expired', 2)->update(['is_expired' => 1]);

            $userPlan = new UserPlan;
            $userPlan->user_type = 1;
            $userPlan->user_id = $buyer->user_id;
            $userPlan->plan_id = $plan_id;
            $userPlan->plan_name = $plan->plan_name;
            $userPlan->plan_amount = $price;
            $userPlan->no_of_users = $no_of_user;
            $userPlan->discount = $discount;
            $userPlan->gst = $gst;
            $userPlan->final_amount = $total;
            $userPlan->start_date = now()->format('Y-m-d');
            $userPlan->subscription_period = "1 Year";
            $userPlan->next_renewal_date = now()->addYear()->format('Y-m-d');
            $userPlan->invoice_no = $invoice_no;
            $userPlan->transaction_no = $invoice_no;
            $userPlan->activated_by = auth()->user()->id;
            $userPlan->save();
            
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Plan Details Activated Successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to activate Buyer Plan. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }
    }

    public function users(Request $request,$id)
    {
        $query = User::where('parent_id',$id);
        if ($request->filled('name')) {
            $query->where('name', $request->input('name'));
        }
        if ($request->filled('email')) {
            $query->where('email', $request->input('email'));
        }
        if ($request->filled('mobile')) {
            $query->where('mobile', $request->input('mobile'));
        }
        $perPage = $request->input('per_page', 25); // default to 25 if not present
        $results = $query->paginate($perPage)->appends($request->all());
        if ($request->ajax()) {
            return view('admin.buyer.partials.user-table', compact('results','id'))->render();
        }
        return view('admin.buyer.user', compact('results','id'));
    }

    public function exportTotalBuyer(Request $request)
    { 
        $query = Buyer::with(['users']);
        if ($request->filled('user')) {
            $query->whereHas('users', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('user') . '%');
                $q->orWhere('email', 'like', '%' . $request->input('user') . '%');
                $q->orWhere('mobile', 'like', '%' . $request->input('user') . '%');
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('users', function ($q) use ($request) {
                $q->where('status',$request->input('status'));
            });
        }
        $total = $query->count();
        return response()->json(['total' => $total]);
    }
    
    public function exportBatchBuyer(Request $request)
    { 
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));
        $query = Buyer::with(['users']);
        if ($request->filled('user')) {
            $query->whereHas('users', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('user') . '%');
                $q->orWhere('email', 'like', '%' . $request->input('user') . '%');
                $q->orWhere('mobile', 'like', '%' . $request->input('user') . '%');
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('users', function ($q) use ($request) {
                $q->where('status',$request->input('status'));
            });
        }
        $query->orderBy('buyers.updated_at', 'desc');
        $results= $query->offset($offset)->limit($limit)->get();
        $result=[];
        foreach($results as $res){
            if(!empty($res->users))
            {
            $result[]=[
                $res->buyer_code,
                $res->legal_name,
                $res->users->name??'',
                $res->users->email??'',
                optional($res->buyerVerifiedAt()->first())->start_date ? date("d/m/Y", strtotime($res->buyerVerifiedAt()->first()->start_date)) : '',
                (!empty($res->users->country_code)?'+'.$res->users->country_code:'').' '.$res->users->mobile,
                // $res->users->is_verified==1?'Verified':'Not Verified',
                $res->users->status==1?'Active':'Inactive',
            ];
        }
        }
        return response()->json(['data'=>$result]);
    }



    public function exportTotalUser(Request $request)
    { 
        $id = $request->input('parent_id');
        $query = User::where('parent_id',$id);
        if ($request->filled('name')) {
            $query->where('name', $request->input('name'));
        }
        if ($request->filled('email')) {
            $query->where('email', $request->input('email'));
        }
        if ($request->filled('mobile')) {
            $query->where('mobile', $request->input('mobile'));
        }
        $total = $query->count();
        return response()->json(['total' => $total]);
    }
    
    public function exportBatchUser(Request $request)
    { 
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));
        $id=$request->input('parent_id');
        $query = User::where('parent_id',$id);
        if ($request->filled('name')) {
            $query->where('name', $request->input('name'));
        }
        if ($request->filled('email')) {
            $query->where('email', $request->input('email'));
        }
        if ($request->filled('mobile')) {
            $query->where('mobile', $request->input('mobile'));
        }
        $results= $query->offset($offset)->limit($limit)->get();
        $result=[];
        foreach($results as $k=> $res){
            $result[]=[
                $k++,
                $res->name,
                $res->email,
                (!empty($res->country_code)?'+'.$res->country_code:'').' '.$res->mobile,
                $res->created_at??'',
            ];
        }
        return response()->json(['data'=>$result]);
    }

}
