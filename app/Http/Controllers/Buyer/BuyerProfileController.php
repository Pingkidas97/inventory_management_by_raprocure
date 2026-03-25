<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Buyer;
use App\Models\BranchDetail;
use App\Models\User;
use App\Models\Division;
use App\Models\Subscription;
use DB;
use App\Helpers\EmailHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;

class BuyerProfileController extends Controller
{
    public $buyer_profile_dir = 'buyer-profile';
    public function index()
    {
        $company_id = getParentUserId();
        
        $buyer_data = User::with(["buyer", "branchDetails", "topManagamantDetails"])->where("id", $company_id)->first();
        
        $divisions = Division::where("status", 1)
                                ->select("id", "division_name")
                                ->orderBy("division_name", "ASC")
                                ->pluck("division_name", "id")->toArray();
        
        $countries = DB::table("countries")
                            ->select("id", "name")
                            ->orderBy("name", "ASC")
                            ->pluck("name", "id")->toArray();
        
        $india_states = DB::table("states")
                            ->select("id", "name")
                            ->where("country_id", 101)
                            ->orderBy("name", "ASC")
                            ->pluck("name", "id")->toArray();

        $director_designations = DB::table("director_designations")
                            ->select("id", "designation_name")
                            ->orderBy("id", "DESC")
                            ->pluck("designation_name", "id")->toArray();

        $current_user_email = getParentEmailId();

        $isSubscribed = Subscription::where('email', $current_user_email)->first();
        $subscribed = false;
        if(!empty($isSubscribed)){
            $subscribed = true;
        }
        return view('buyer.setting.profile', compact('countries', 'buyer_data', 'director_designations', 'divisions', 'india_states', 'subscribed'));
    }

    public function validateBuyerGSTINVat(Request $request)
    {
        $buyer_gst_number = $request->buyer_gst_number;
        if(empty($buyer_gst_number)){
            return response()->json([
                'status' => false,
                'message' => 'GSTIN/Vat is required'
            ]);
        }

        $company_id = getParentUserId();

        $is_exists = Buyer::where("gstin", $buyer_gst_number)
                            ->select("user_id", "gstin")
                            ->where("user_id", "!=", $company_id)
                            ->first();
        if(empty($is_exists)){
            $response = array(
                'status' => true,
                'message' => "GSTIN/VAT Validated successfully",
            );
        }else{
            $response = array(
                'status' => false,
                'message' => $buyer_gst_number . " GSTIN/VAT is already exists",
            );
        }
        return response()->json($response);
    }
    
    public function validateBuyerShortCode(Request $request)
    {
        $short_code = $request->short_code;
        if(empty($short_code)){
            return response()->json([
                'status' => false,
                'message' => 'Please Enter Short Code'
            ]);
        }

        $company_id = getParentUserId();
        // DB::enableQueryLog();
        $is_exists = Buyer::where("organisation_short_code", $short_code)
                            ->select("user_id", "organisation_short_code")
                            ->where("user_id", "!=", $company_id)
                            ->first();
        if(empty($is_exists)){
            $response = array(
                'status' => true,
                'message' => "Short Code is Verified successfully",
            );
        }else{
            $response = array(
                'status' => false,
                'message' => "This Short Code already exists. Please choose another Short Code.",
            );
        }
        // $response['qry'] = DB::getQueryLog();
        return response()->json($response);
    }

    public function saveBuyerProfile(Request $request)
    {
        $clean = xssCleanInput($request->all());
        $request->merge($clean);

        $request->merge([
            'legal_name' => trim($request->legal_name),
            'incorporation_date' => trim($request->incorporation_date),
            'registered_address' => trim($request->registered_address),
            'country' => trim($request->country),
            'state' => trim($request->state),
            // 'city' => trim($request->city),
            'pincode' => trim($request->pincode),
            'gstin' => trim($request->gstin),
            'pan' => trim($request->pan),
            'website' => trim($request->website),
            'product_details' => trim($request->product_details),

            'tdm_name' => array_map('trim', $request->input('tdm_name', [])),
            'tdm_top_management_designation' => array_map('trim', $request->input('tdm_top_management_designation', [])),
            'tdm_mobile' => array_map('trim', $request->input('tdm_mobile', [])),
            'tdm_email' => array_map('trim', $request->input('tdm_email', [])),

            'branch_name' => array_map('trim', $request->input('branch_name', [])),
            'branch_address' => array_map('trim', $request->input('branch_address', [])),
            'branch_country' => array_map('trim', $request->input('branch_country', [])),
            'branch_state' => array_map('trim', $request->input('branch_state', [])),
            // 'branch_city' => array_map('trim', $request->input('branch_city', [])),
            'branch_pincode' => array_map('trim', $request->input('branch_pincode', [])),
            'branch_gstin' => array_map('trim', $request->input('branch_gstin', [])),
            'branch_authorized_name' => array_map('trim', $request->input('branch_authorized_name', [])),
            'branch_authorized_designation' => array_map('trim', $request->input('branch_authorized_designation', [])),
            'branch_mobile' => array_map('trim', $request->input('branch_mobile', [])),
            'branch_email' => array_map('trim', $request->input('branch_email', [])),
            'branch_output_details' => array_map('trim', $request->input('branch_output_details', [])),
            'branch_installed_capacity' => array_map('trim', $request->input('branch_installed_capacity', [])),
            'branch_status' => array_map('trim', $request->input('branch_status', [])),

            'organisation_description' => trim($request->organisation_description),
            'subscribe_news_letter' => trim($request->subscribe_news_letter),
            'organisation_short_code' => trim($request->organisation_short_code),
            'buyer_accept_tnc' => trim($request->buyer_accept_tnc),
        ]);
        
        $company_id = getParentUserId();

        $validator = $this->validateBuyerProfile($request);
        
        if ($validator->fails()) {
            // Return or print errors
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 200);
        }

        $current_user_id = Auth::user()->id;
        $file_prefix = 'B' . $current_user_id. '-';

        DB::beginTransaction();

        try {
            $is_buyer_exists = Buyer::where("user_id", $company_id)->first();
            if(!empty($is_buyer_exists)){
                $buyer = Buyer::find($is_buyer_exists->id);
            }else{
                $buyer = new Buyer();
                $buyer->user_id = $company_id;
            }

            $buyer->legal_name = remove_extra_spaces($request->legal_name);
            $buyer->incorporation_date = date("Y-m-d", strtotime(str_replace("/", "-", $request->incorporation_date)));
            if ($request->hasFile('logo')) {
                $res = uploadFile($request, 'logo', $this->buyer_profile_dir, $file_prefix . 'Buyer-logo');
                if($res['status']){
                    if(!empty($buyer->logo)){
                        removeFile(public_path('uploads/'.$this->buyer_profile_dir.'/'.$buyer->logo));
                    }
                    $buyer->logo = $res['file_name'];
                }else{
                    throw new \Exception($res['file_name']);
                }
            }else if(!empty($request->logo_old)){
                $buyer->logo = $request->logo_old;
            }

            $is_first_mail_sent = true;
            if(empty($buyer->organisation_short_code) && empty($buyer->buyer_code)){
                $is_first_mail_sent = false;
            }

            $buyer->registered_address = $request->registered_address;
            $buyer->country = $request->country;
            $buyer->state = !empty($request->state) ? $request->state : null;
            // $buyer->city = !empty($request->city) ? $request->city : null;
            $buyer->pincode = $request->pincode;
            $buyer->gstin = $request->gstin;
            $buyer->pan = $request->pan;
            if ($request->hasFile('pan_file')) {
                $res = uploadFile($request, 'pan_file', $this->buyer_profile_dir, $file_prefix . 'Buyer-pan');
                if($res['status']){
                    if(!empty($buyer->pan_file)){
                        removeFile(public_path('uploads/'.$this->buyer_profile_dir.'/'.$buyer->pan_file));
                    }
                    $buyer->pan_file = $res['file_name'];
                }else{
                    throw new \Exception($res['file_name']);
                }
            }else if(!empty($request->pan_file_old)){
                $buyer->pan_file = $request->pan_file_old;
            }
            $buyer->website = $request->website;
            $buyer->product_details = $request->product_details;
            $buyer->organisation_description = $request->organisation_description;
            $buyer->organisation_short_code = $request->organisation_short_code;
            $buyer->buyer_accept_tnc = $request->buyer_accept_tnc;
            $buyer->tab1_status = 1;
            $buyer->tab2_status = 1;
            $buyer->tab3_status = 1;
            $buyer->tab4_status = 1;
            $buyer->updated_by = $current_user_id;
            $buyer->save();

            if(!empty($request->tdm_name)){
                foreach ($request->tdm_name as $key => $value) {
                    $is_new_tmd = true;
                    if($request->edit_id_tmd[$key]!=0){
                        $isExists = BranchDetail::where('branch_id', $request->edit_id_tmd[$key])
                                        ->where('user_type', 1)
                                        ->where('record_type', 2)
                                        ->where('user_id', $company_id)
                                        ->first();
                        if(!empty($isExists)){
                            $is_new_tmd = false;
                        }
                    }

                    if($is_new_tmd==true){
                        $new_tmd = new BranchDetail();
                        $new_tmd->name = remove_extra_spaces($request->tdm_name[$key]);
                        $new_tmd->top_management_designation = $request->tdm_top_management_designation[$key];
                        $new_tmd->mobile = $request->tdm_mobile[$key];
                        $new_tmd->email = $request->tdm_email[$key];
                        $new_tmd->user_type = 1;
                        $new_tmd->user_id = $company_id;
                        $new_tmd->record_type = 2;
                        $new_tmd->status = 1;
                        $new_tmd->branch_id = 0;
                        $new_tmd->updated_by = Auth::user()->id;
                        $new_tmd->save();
                        $new_tmd->branch_id = $new_tmd->id;
                        $new_tmd->save();
                    }else{
                        BranchDetail::where('branch_id', $request->edit_id_tmd[$key])
                                ->where('user_type', 1)
                                ->where('record_type', 2)
                                ->where('user_id', $company_id)
                                ->update(
                                    array(
                                        'name'=> remove_extra_spaces($request->tdm_name[$key]),
                                        'top_management_designation'=> $request->tdm_top_management_designation[$key],
                                        'mobile'=> $request->tdm_mobile[$key],
                                        'email'=> $request->tdm_email[$key],
                                        'updated_by'=> Auth::user()->id,
                                    )
                                );
                    }
                }
            }

            if(!empty($request->branch_name)){
                $branch_gstin_files = $request->file('branch_gstin_file');

                foreach ($request->branch_name as $key => $value) {
                    $is_new_branch = true;
                    if($request->edit_id_branch[$key]!=0){
                        $isExists = BranchDetail::where('branch_id', $request->edit_id_branch[$key])
                                        ->where('user_type', 1)
                                        ->where('record_type', 1)
                                        ->where('user_id', $company_id)
                                        ->first();
                        if(!empty($isExists)){
                            $is_new_branch = false;
                        }
                    }

                    $gst_file_name = '';
                    if ($request->hasFile('branch_gstin_file') && isset($branch_gstin_files[$key])) {
                        $res = uploadMultipleFile($request, 'branch_gstin_file', $this->buyer_profile_dir, $key, $file_prefix . 'Branch-unit');
                        if($res['status']){
                            if($is_new_branch==false && !empty($isExists->gstin_file)){
                                removeFile(public_path('uploads/'.$this->buyer_profile_dir.'/'.$isExists->gstin_file));
                            }
                            $gst_file_name = $res['file_name'];
                        }else{
                            throw new \Exception($res['file_name']);
                        }
                    }else if(!empty($request->branch_gstin_file_old[$key])){
                        $gst_file_name = $request->branch_gstin_file_old[$key];
                    }

                    if($is_new_branch==true){
                        $new_branch = new BranchDetail();
                        $new_branch->name = remove_extra_spaces($request->branch_name[$key]);
                        $new_branch->address = $request->branch_address[$key];
                        $new_branch->country = $request->branch_country[$key];
                        $new_branch->state = !empty($request->branch_state[$key]) ? $request->branch_state[$key] : null;
                        // $new_branch->city = !empty($request->branch_city[$key]) ? $request->branch_city[$key] : null;
                        $new_branch->pincode = $request->branch_pincode[$key];
                        $new_branch->gstin = $request->branch_gstin[$key];
                        $new_branch->gstin_file = $gst_file_name;
                        $new_branch->authorized_name = $request->branch_authorized_name[$key];
                        $new_branch->authorized_designation = $request->branch_authorized_designation[$key];
                        $new_branch->mobile = $request->branch_mobile[$key];
                        $new_branch->email = $request->branch_email[$key];
                        $new_branch->output_details = $request->branch_output_details[$key];
                        $new_branch->installed_capacity = $request->branch_installed_capacity[$key];
                        $new_branch->categories = implode(",", $request->branch_categories[$key]);
                        $new_branch->status = $request->branch_status[$key];
                        $new_branch->user_type = 1;
                        $new_branch->user_id = $company_id;
                        $new_branch->record_type = 1;
                        $new_branch->branch_id = 0;
                        $new_branch->updated_by = Auth::user()->id;
                        $new_branch->save();
                        $new_branch->branch_id = $new_branch->id;
                        $new_branch->save();
                    }else{
                        BranchDetail::where('branch_id', $request->edit_id_branch[$key])
                                ->where('user_type', 1)
                                ->where('record_type', 1)
                                ->where('user_id', $company_id)
                                ->update(
                                    array(
                                        'name'=> remove_extra_spaces($request->branch_name[$key]),
                                        'address'=> $request->branch_address[$key],
                                        'country'=> $request->branch_country[$key],
                                        'state'=> !empty($request->branch_state[$key]) ? $request->branch_state[$key] : null,
                                        // 'city'=> !empty($request->branch_city[$key]) ? $request->branch_city[$key] : null,
                                        'pincode'=> $request->branch_pincode[$key],
                                        'gstin'=> $request->branch_gstin[$key],
                                        'gstin_file'=> $gst_file_name,
                                        'authorized_name'=> $request->branch_authorized_name[$key],
                                        'authorized_designation'=> $request->branch_authorized_designation[$key],
                                        'mobile'=> $request->branch_mobile[$key],
                                        'email'=> $request->branch_email[$key],
                                        'output_details'=> $request->branch_output_details[$key],
                                        'installed_capacity'=> $request->branch_installed_capacity[$key],
                                        'categories'=> implode(",", $request->branch_categories[$key]),
                                        'status'=> $request->branch_status[$key],
                                        'updated_by'=> Auth::user()->id,
                                    )
                                );
                    }
                }
            }

            $current_user_email = getParentEmailId();

            $isSubscribed = Subscription::where('email', $current_user_email)->first();
            if(!empty($isSubscribed)){
                if($request->subscribe_news_letter==2){
                    Subscription::where('email', $current_user_email)->delete();
                }else{
                    Subscription::where('email', $current_user_email)->update(['updated_at'=> date("Y-m-d H:i:s")]);
                }
            }else{
                if($request->subscribe_news_letter==1){
                    $subscribe = new Subscription();
                    $subscribe->email = $current_user_email;
                    $subscribe->is_submited = 1;
                    $subscribe->save();
                }
            }

            $admin_detail = getMainSuperadminDetails();
            
            $notification = array();
            $notification_data['to_user_id'] = $admin_detail->id;
            $notification_data['notification_link'] = route('admin.buyer.profile', ['id'=>$buyer->id]);
            if($is_first_mail_sent==false){
                $this->sendMailForProfileCompletion($request);
                // send notification to SA for verify new buyer profile
                $notification_data['message_type'] = "Buyer Account Creation";
            }else{
                // send notification to SA for edited buyer profile
                $notification_data['message_type'] = "Buyer Profile Update";
            }
            if(Auth::user()->is_profile_verified==1 || $is_first_mail_sent==false){
                sendNotifications($notification_data);
            }

            $redirectUrl = '';
            if(Auth::user()->is_verified==1){
                $redirectUrl = route('buyer.dashboard');
            }else{
                $redirectUrl = route('buyer.profile-complete');
            }

            DB::commit();

            Session::put('legal_name', $buyer->legal_name);

            return response()->json([
                'status' => true,
                'message' => 'Buyer Profile completed',
                'redirectUrl' => $redirectUrl
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
    
    private function validateBuyerProfile($request)
    {
        $company_id = getParentUserId();
        return Validator::make($request->all(), [
            'legal_name' => ['required', 'string', 'max:255', 'regex:/^([a-zA-Z0-9.\&\(\)\+,\- ])+$/'],
            'incorporation_date' => [
                'required',
                'date_format:d/m/Y',
                function ($attribute, $value, $fail) {
                    try {
                        $date = Carbon::createFromFormat('d/m/Y', $value);
                        if ($date->gt(now())) {
                            $fail('Date of Incorporation must not be a future date.');
                        }
                    } catch (\Exception $e) {
                        $fail('Date of Incorporation is not a valid date.');
                    }
                }

            ],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'registered_address' => 'required|max:1700',
            'country' => ['required', 'integer', Rule::in(DB::table("countries")->select("id")->pluck("id")->toArray())],
            'state' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('country') == 101) {
                        if (empty($value) || !preg_match('/^[0-9]+$/', $value)) {
                            $fail('Organization State is required');
                        }
                    }else{
                        if (!empty($value) && !preg_match('/^[0-9]+$/', $value)) {
                            $fail('Organization State is invalid');
                        }
                    }
                    if(!empty($value) && !in_array($value, DB::table("states")->select("id")->where("country_id", $request->input('country'))->pluck("id")->toArray())){
                        $fail('Organization State is invalid');
                    }
                }
            ],
            // 'city' => [
            //     function ($attribute, $value, $fail) use ($request) {
            //         if ($request->input('country') == 101) {
            //             if (empty($value) || !preg_match('/^[0-9]+$/', $value)) {
            //                 $fail('Organization City is required');
            //             }
            //         }else{
            //             if (!empty($value) && !preg_match('/^[0-9]+$/', $value)) {
            //                 $fail('Organization City is invalid');
            //             }
            //         }
            //         if (!empty($value) && !empty($request->input('state'))) {
            //             if(!empty($value) && !in_array($value, DB::table("cities")->select("id")->where("state_id", $request->input('state'))->pluck("id")->toArray())){
            //                 $fail('Organization City is invalid');
            //             }
            //         }
            //     }
            // ],
            'pincode' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('country') == 101) {
                        if (empty($value)) {
                            $fail('Organization Pincode is required');
                        }else if(!preg_match('/^[0-9]+$/', $value) || strlen($value) != 6){
                            $fail('Organization Pincode is invalid');
                        }
                    }else{
                        if(!empty($value) && !preg_match('/^[0-9]+$/', $value)){
                            $fail('Organization Pincode is invalid');
                        }
                    }
                }
            ],
            'gstin' => [
                function ($attribute, $value, $fail) use ($request, $company_id) {
                    if ($request->input('country') == 101) {
                        if (empty($value)) {
                            $fail('Organization GSTIN/VAT is required');
                        }else if(strlen($value) != 15){
                            $fail('Organization GSTIN/VAT should be 15 characters');
                        }else if(!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $value)){
                            $fail('Organization GSTIN/VAT is invalid');
                        }else if(!empty(Buyer::where("gstin", $value)->select("user_id", "gstin")->where("user_id", "!=", $company_id)->first())){
                            $fail($value." GSTIN/VAT is already exists");
                        }
                    }else{
                        if (empty($value)) {
                            $fail('Organization Tax Identification Number is required');
                        }else if (strlen($value) > 150) {
                            $fail('Organization Tax Identification Number should be less than 150 characters');
                        }else if(!preg_match('/^[A-Z0-9]+$/', $value)){
                            $fail('Organization Tax Identification Number is invalid');
                        }else if(!empty(Buyer::where("gstin", $value)->select("user_id", "gstin")->where("user_id", "!=", $company_id)->first())){
                            $fail($value." Tax Identification Number is already exists");
                        }
                    }
                }
            ],
            'pan' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('country') == 101) {
                        if (empty($value)) {
                            $fail('Organization PAN/TIN is required');
                        }else if (strlen($value) != 10){
                            $fail('Organization PAN/TIN should be 10 digit');
                        }else if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $value)){
                            $fail('Organization PAN/TIN is invalid');
                        }
                    }
                }
            ],
            'pan_file' => ['required_without:pan_file_old', 'file', 'mimes:jpg,jpeg,pdf', 'max:2048'],
            'pan_file_old' => ['nullable', 'string'],
            'website' => ['nullable', 'url'],
            'product_details' => ['nullable', 'string', 'max:1700'],

            'tdm_name.*' => ['required', 'string', 'max:255', 'regex:/^([a-zA-Z0-9.\&\(\)\+,\- ])+$/'],
            'tdm_top_management_designation.*' => ['required', 'integer', Rule::in(DB::table("director_designations")->select("id")->pluck("id")->toArray())],
            'tdm_mobile.*' => [
                function ($attribute, $value, $fail) use ($request) {
                    if (empty($value)) {
                        $fail('Top Management mobile number is required');
                    }else if (!preg_match('/^[0-9]+$/', $value)) {
                        $fail('Top Management mobile number is Invalid');
                    }
                    if ($request->input('country') == 101) {
                        if (strlen($value) != 10) {
                            $fail('Top Management mobile number should be 10 digits');
                        }
                    }else{
                        if (strlen($value) > 25) {
                            $fail('Top Management mobile number should be less than 25 digits');
                        }
                    }
                }
            ],
            'tdm_email.*' => ['required', 'max:255', 'email'],

            'branch_name.*' => ['required', 'string', 'max:255'],
            'branch_address.*' => ['required', 'max:1700'],
            'branch_country.*' => ['required', 'integer', 'same:country'],
            'branch_state.*' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('country') == 101) {
                        if (empty($value) || !preg_match('/^[0-9]+$/', $value)) {
                            $fail('Branch State is required');
                        }
                    }else{
                        if (!empty($value) && !preg_match('/^[0-9]+$/', $value)) {
                            $fail('Branch State is invalid');
                        }
                    }
                    if(!empty($value) && !in_array($value, DB::table("states")->select("id")->where("country_id", $request->input('country'))->pluck("id")->toArray())){
                        $fail('Branch State is invalid');
                    }
                }
            ],
            // 'branch_city.*' => [
            //     function ($attribute, $value, $fail) use ($request) {
            //         if (preg_match('/\d+/', $attribute, $matches)) {
            //             $index = $matches[0];
            //             $stateId = $request->input("branch_state.$index");
            //             if ($request->input('country') == 101) {
            //                 if (empty($value) || !preg_match('/^[0-9]+$/', $value)) {
            //                     $fail('Branch City is required');
            //                 }
            //             }else{
            //                 if (!empty($value) && !preg_match('/^[0-9]+$/', $value)) {
            //                     $fail('Branch City is invalid');
            //                 }
            //             }
            //             if (!empty($value) && !empty($stateId)) {
            //                 if(!empty($value) && !in_array($value, DB::table("cities")->select("id")->where("state_id", $stateId)->pluck("id")->toArray())){
            //                     $fail('Branch City is invalid');
            //                 }
            //             }
            //         }
            //     }
            // ],
            'branch_pincode.*' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('country') == 101) {
                        if (empty($value)) {
                            $fail('Branch Pincode is required');
                        }else if(!preg_match('/^[0-9]+$/', $value) || strlen($value) != 6){
                            $fail('Branch Pincode is invalid');
                        }
                    }else{
                        if(!empty($value) && !preg_match('/^[0-9]+$/', $value)){
                            $fail('Branch Pincode is invalid');
                        }
                    }
                }
            ],
            'branch_gstin.*' => [function ($attribute, $value, $fail) use ($request, $company_id) {
                if ($request->input('country') == 101) {
                    if (empty($value)) {
                        $fail('Branch GSTIN/VAT is required');
                    }else if(strlen($value) != 15){
                        $fail('Branch GSTIN/VAT should be 15 characters');
                    }else if(!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $value)){
                        $fail('Branch GSTIN/VAT is invalid');
                    }
                }else{
                    if (empty($value)) {
                        $fail('Branch Tax Identification Number is required');
                    }else if (strlen($value) > 150) {
                        $fail('Branch Tax Identification Number should be less than 150 characters');
                    }else if(!preg_match('/^[A-Z0-9]+$/', $value)){
                        $fail('Branch Tax Identification Number is invalid');
                    }
                }
            }],
            'branch_gstin_file' => ['required_without:branch_gstin_file_old', 'array'],
            'branch_gstin_file.*' => ['file', 'mimes:jpg,jpeg,pdf', 'max:2048'],
            'branch_gstin_file_old' => ['nullable', 'array'],
            'branch_authorized_name.*' => ['required', 'max:255', 'string'],
            'branch_authorized_designation.*' => ['required', 'max:255', 'string'],
            'branch_mobile.*' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('country') == 101) {
                        if (!preg_match('/^[0-9]+$/', $value)) {
                            $fail('Branch mobile number is invalid');
                        }else if (strlen($value) != 10) {
                            $fail('Branch mobile number should be 10 digits');
                        }
                    }else{
                        if (!preg_match('/^[0-9]+$/', $value)) {
                            $fail('Branch mobile number is invalid');
                        }else if (strlen($value) > 25) {
                            $fail('Branch mobile number should be less than 25 digits');
                        }
                    }
                }
            ],
            'branch_email.*' => ['required', 'max:255', 'email'],
            'branch_output_details.*' => ['nullable', 'max:1700', 'string'],
            'branch_installed_capacity.*' => ['nullable', 'regex:/^\d{1,254}$/'], // 'max:255', 'integer'
            'branch_categories.*' => [
                'required',
                'array'
            ],
            'branch_categories.*.*' => [
                'required',
                Rule::in(Division::where("status", 1)->select("id")->pluck("id")->toArray())
            ],
            'branch_status.*' => [
                'required', 
                Rule::in(array(1, 2)),
                function ($attribute, $value, $fail) use ($request) {
                    if (!in_array(1, $request->input('branch_status', []))) {
                        $fail("All the branches can't be made inactive. One branch must stay");
                    }
                }
            ],
            
            'organisation_description' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $wordCount = str_word_count(strip_tags($value));
                    if ($wordCount > 500) {
                        $fail('Organization Description must not contain more than 500 words.');
                    }
                }
            ],
            'subscribe_news_letter' => ['nullable', Rule::in(array(1, 2))],//'boolean'
            'organisation_short_code' => [
                'required', 'max:4', 'string', 'regex:/^[A-Z]+$/',
                function ($attribute, $value, $fail) use ($request, $company_id)  {
                    if(Auth::user()->is_profile_verified==1){
                        $buyer_data = Buyer::where("user_id", $company_id)->select('organisation_short_code')->first();
                        if ($value !== $buyer_data->organisation_short_code) {
                            $fail("Short Code should not be change, please refresh the page");
                        }
                    }
                },
            ],
            'buyer_accept_tnc' => ['required', 'accepted']
        ]);
    }

    private function sendMailForProfileCompletion(object $request): void
    {
        $buyer_name = remove_extra_spaces($request->legal_name);
        $buyer_mail_data = buyerEmailTemplet('buyer-register-email');
        $buyer_mail_msg = $buyer_mail_data->mail_message;
        $buyer_mail_subject = $buyer_mail_data->subject;
        $buyer_mail_msg = str_replace('$name', $buyer_name, $buyer_mail_msg);
        $buyer_email = Auth::user()->email;
        EmailHelper::sendMail($buyer_email, $buyer_mail_subject, $buyer_mail_msg);

        $mail_data = buyerEmailTemplet('buyer-registration-completed-mail');
        $date = now()->format('d/m/Y');
        $before_date = dateAfterDays(7);
        $admin_detail = getMainSuperadminDetails();
        $emailto = $admin_detail->email;
        $mail_msg = $mail_data->mail_message;
        $mail_subject = $mail_data->subject;
        $mail_msg = str_replace('$name', $admin_detail->name, $mail_msg);
        $mail_msg = str_replace('$buyer_name', $buyer_name, $mail_msg);
        $mail_msg = str_replace('$date', $date, $mail_msg);
        $mail_msg = str_replace('$before_date', $before_date, $mail_msg);
        EmailHelper::sendMail($emailto, $mail_subject, $mail_msg);
    }

    public function profileComplete()
    {
        if(Auth::user()->is_profile_verified==1){
            return redirect()->to(route('buyer.dashboard'));
        }
        return view('buyer.setting.profile-success');
    }
}
