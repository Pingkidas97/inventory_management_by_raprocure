<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Plan;
use App\Models\InvoiceNumber;
use App\Models\UserSession;
use App\Models\LoginAttempt;
use App\Models\Notification;
use App\Models\UserPlan;
use App\Models\BranchDetail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Helpers\EmailHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{
    public $vendor_profile_dir = 'vendor-profile';

    public function __construct()
    {
        if (auth()->check() && auth()->user()->user_type != 3) {
            abort(403, 'Unauthorized access.');
        }
    }
    public function index(Request $request){
        
        $query = Vendor::with(['user'])->withCount(['vendor_products as vendor_products_count' => function ($query) {
            $query->where('approval_status', 1)
                  ->where('edit_status', 0);
        }]);
        if ($request->filled('user')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('user') . '%');
                $q->orWhere('email', 'like', '%' . trim($request->input('user')) . '%');
                $q->orWhere('mobile', 'like', '%' . $request->input('user') . '%');
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('status',$request->input('status'));
            });
        }
        $query->orderBy('vendors.updated_at', 'desc');
        if ($request->filled('profile_status')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('is_verified',$request->input('profile_status'));
            });
        }
        $perPage = $request->input('per_page', 25); // default to 25 if not present
        $results = $query->paginate($perPage)->appends($request->all());
        
        if ($request->ajax()) {
            return view('admin.vendor.partials.table', compact('results'))->render();
        }
        return view('admin.vendor.index', compact('results'));
    }

    public function registration(){
        
        // $countries = \DB::table('countries')->get(); // Fetch all countries from database
        $countries = DB::table("countries")
                            ->select("phonecode", "name")
                            ->orderBy("name", "ASC")
                            ->pluck("name", "phonecode")->toArray();

        return view('admin.vendor.registration', [
            'countries' => $countries
        ]);
    }

    public function saVendorRegistration(Request $request){

        $clean = xssCleanInput($request->all());
        $request->merge($clean);

        $request->merge([
            'company_name' => trim($request->company_name),
            'name' => trim($request->name),
            'email' => trim($request->email),
            'mobile' => trim($request->mobile),
            'country_code' => trim($request->country_code),
            'referred_by' => trim($request->referred_by),
        ]);

        $validator = Validator::make($request->all(), [
            'company_name' => ['required', 'string', 'max:255', 'regex:/^([a-zA-Z0-9.\&\(\)\+,\- ])+$/'],
            'name' => ['required', 'string', 'max:255', 'regex:/^([a-zA-Z ]+)$/'],
            'email' => 'required|email|max:255|unique:users,email',
            'country_code' => [
                'required', 
                'max:5', 
                'regex:/^[0-9]+$/',
                Rule::in(DB::table("countries")->select("phonecode")->pluck("phonecode")->toArray())
            ],
            'mobile' => [
                'required',
                'regex:/^[0-9]+$/', // only digits
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->country_code === '91' && strlen($value) !== 10) {
                        $fail('Mobile number must be exactly 10 digits for country code 91.');
                    } elseif ($request->country_code !== '91' && strlen($value) > 25) {
                        $fail('Mobile number must not exceed 25 digits.');
                    }
                },
                Rule::unique('users')->where(function ($query) use ($request) {
                    return $query->where('country_code', $request->country_code);
                }),
            ],
            'referred_by'  => ['required', 'string', 'max:255', 'regex:/^([a-zA-Z0-9.\&\(\)\+,\- ])+$/'],
        ], [
            'company_name.required' => 'Company Name is required.',
            'company_name.regex'    => 'Company Name field only support alphanumeric.',
            'name.required'         => 'Person Name is required.',
            'name.regex'            => 'Person Name field only support alphabetic.',
            'email.required'        => 'Please enter Email',
            'email.email'           => 'Please enter valid email',
            'email.unique'          => 'This email already exists.',
            'country_code.required' => 'Country code is required.',
            'country_code.in'       => 'Country code is invalid.',
            'mobile.required'       => 'Mobile number is required.',
            'mobile.regex'          => 'Mobile number must contain digits only.',
            'mobile.unique'         => 'This mobile already exists.',
            'referred_by.required'  => 'Referred by is required.'
        ]);
        if ($validator->fails()) {
            // Return or print errors
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 200);
        }

        DB::beginTransaction();

        try {
            $user = new User();
            $user->name = strtoupper($request->name);
            $user->email = $request->email;
            $user->country_code = $request->country_code;
            $user->mobile = $request->mobile;
            $user->password = Hash::make($request->mobile);
            $user->status = 1;
            $user->user_type = 2;
            $user->user_created_by = auth()->user()->id;
            $user->save();

            $user_id = $user->id;

            $vendor = new Vendor();
            $vendor->user_id = $user_id;
            $vendor->legal_name = strtoupper($request->company_name);
            $vendor->referred_by = $request->referred_by;
            $vendor->save();

            session()->flash('success', "Vendor registered successfully");
            
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Vendor registered successfully',
                'redirect_url' => route('admin.vendor.sa-vendor-profile', $user_id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            // throw $e;
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage()
            ]);
        }
    }

    public function vendorProfileBySA(Request $request, $company_id)
    {
        $vendor_data = User::with(["vendor", "branchDetails"])->where("id", $company_id)->first();
        $nature_of_organization = DB::table("nature_of_organization")
                    ->select("id", "organization_name")
                    ->orderBy("id", "DESC")
                    ->pluck("organization_name", "id")->toArray();

        // $nature_of_business = DB::table("nature_of_business")
        //             ->select("id", "business_name")
        //             ->orderBy("id", "DESC")
        //             ->pluck("business_name", "id")->toArray();

        $countries = DB::table("countries")
                    ->select("id", "name")
                    ->orderBy("name", "ASC")
                    ->pluck("name", "id")->toArray();
        
        $india_states = DB::table("states")
                    ->select("id", "name")
                    ->where("country_id", 101)
                    ->orderBy("name", "ASC")
                    ->pluck("name", "id")->toArray();

        $vendor_plan = Plan::where('type', 2)->where('status', 1)->orderBy('no_of_user', 'asc')->get();

        return view('admin.vendor.vendor-profile-by-sa', compact('vendor_data', 'countries', 'india_states', 'nature_of_organization', 'vendor_plan'));
    }

    public function validateVendorGSTINVat(Request $request)
    {
        $vendor_gst_number = $request->vendor_gst_number;
        if(empty($vendor_gst_number)){
            return response()->json([
                'status' => false,
                'message' => 'GSTIN/Vat is required'
            ]);
        }

        $company_id = $request->company_id;
        if(empty($company_id)){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }

        $is_exists = Vendor::where("gstin", $vendor_gst_number)
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
                'message' => $vendor_gst_number . " GSTIN/VAT is already exists",
            );
        }
        return response()->json($response);
    }

    public function saveSAVendorProfile(Request $request)
    {
        $clean = xssCleanInput($request->all());
        $request->merge($clean);

        $request->merge([
            'legal_name' => trim($request->legal_name),
            'profile_img_old' => trim($request->profile_img_old),
            'date_of_incorporation' => trim($request->date_of_incorporation),
            'nature_of_organization' => trim($request->nature_of_organization),
            // 'nature_of_business' => trim($request->nature_of_business),
            'other_contact_details' => trim($request->other_contact_details),
            'registered_address' => trim($request->registered_address),
            'country' => trim($request->country),
            'state' => trim($request->state),
            // 'city' => trim($request->city),
            'pincode' => trim($request->pincode),
            'gstin' => trim($request->gstin),
            'gstin_document_old' => trim($request->gstin_document_old),
            'website' => trim($request->website),
            'company_name1' => trim($request->company_name1),
            'company_name2' => trim($request->company_name2),
            'registered_product_name' => trim($request->registered_product_name),
            'description' => trim($request->description),
            'vendor_plan' => trim($request->vendor_plan),
            't_n_c' => trim($request->t_n_c)
        ]);
        
        $company_id = $request->company_id;

        $validator = $this->validateVendorProfile($request);

        if ($validator->fails()) {
            // Return or print errors
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 200);
        }

        // $current_user_id = Auth::user()->id;
        $file_prefix = 'V' . $company_id. '-';

        DB::beginTransaction();

        try {
            $is_vendor_exists = Vendor::where("user_id", $company_id)->first();
            if(!empty($is_vendor_exists)){
                $vendor = Vendor::find($is_vendor_exists->id);
            }else{
                $vendor = new Vendor();
                $vendor->user_id = $company_id;
            }

            $vendor->legal_name = remove_extra_spaces($request->legal_name);
            if ($request->hasFile('profile_img')) {
                $res = uploadFile($request, 'profile_img', $this->vendor_profile_dir, $file_prefix . 'Company-Logo');
                if($res['status']){
                    if(!empty($vendor->profile_img)){
                        removeFile(public_path('uploads/'.$this->vendor_profile_dir.'/'.$vendor->profile_img));
                    }
                    $vendor->profile_img = $res['file_name'];
                }else{
                    throw new \Exception($res['file_name']);
                }
            }else if(!empty($request->profile_img_old)){
                $vendor->profile_img = $request->profile_img_old;
            }
                        
            $vendor->date_of_incorporation = date("Y-m-d", strtotime(str_replace("/", "-", $request->date_of_incorporation)));
            $vendor->nature_of_organization = $request->nature_of_organization;
            // $vendor->nature_of_business = $request->nature_of_business;
            $vendor->other_contact_details = $request->other_contact_details;
            $vendor->registered_address = $request->registered_address;
            $vendor->country = $request->country;
            $vendor->state = !empty($request->state) ? $request->state : null;
            // $vendor->city = !empty($request->city) ? $request->city : null;
            $vendor->pincode = $request->pincode;
            $vendor->gstin = $request->gstin;
            if ($request->hasFile('gstin_document')) {
                $res = uploadFile($request, 'gstin_document', $this->vendor_profile_dir, $file_prefix . 'G');
                if($res['status']){
                    if(!empty($vendor->gstin_document)){
                        removeFile(public_path('uploads/'.$this->vendor_profile_dir.'/'.$vendor->gstin_document));
                    }
                    $vendor->gstin_document = $res['file_name'];
                }else{
                    throw new \Exception($res['file_name']);
                }
            }else if(!empty($request->gstin_document_old)){
                $vendor->gstin_document = $request->gstin_document_old;
            }
            $vendor->website = $request->website;
            $vendor->company_name1 = $request->company_name1;
            $vendor->company_name2 = $request->company_name2;
            $vendor->registered_product_name = $request->registered_product_name;

            $vendor->description = $request->description;
            $vendor->t_n_c = $request->t_n_c;
            
            $vendor->updated_by = Auth::user()->id;
            $vendor->save();
            
            $vendor_data = User::find($company_id);
            $vendor_data->status = 1;
            $vendor_data->is_verified = 1;
            $vendor_data->is_profile_verified = 1;
            $vendor_data->verified_by = Auth::user()->id;
            $vendor_data->user_updated_by = Auth::user()->id;
            $vendor_data->save();

            $isRegdBranch = BranchDetail::where('user_type', 2)
                            ->where('record_type', 1)
                            ->where('is_regd_address', 1)
                            ->where('user_id', $company_id)
                            ->first();

            if(empty($isRegdBranch)){
                $regd_branch = new BranchDetail();
                $regd_branch->name = "Regd. Address";
                $regd_branch->gstin = $request->gstin;
                $regd_branch->address = $request->registered_address;
                $regd_branch->country = $request->country;
                $regd_branch->state = !empty($request->state) ? $request->state : null;
                // $regd_branch->city = !empty($request->city) ? $request->city : null;
                $regd_branch->pincode = $request->pincode;
                $regd_branch->authorized_designation = $vendor_data->name;
                $regd_branch->mobile = $vendor_data->mobile;
                $regd_branch->email = $vendor_data->email;
                $regd_branch->status = 1;
                $regd_branch->user_type = 2;
                $regd_branch->user_id = $company_id;
                $regd_branch->record_type = 1;
                $regd_branch->is_regd_address = 1;
                $regd_branch->branch_id = 0;
                $regd_branch->updated_by = Auth::user()->id;
                $regd_branch->save();
                $regd_branch->branch_id = $regd_branch->id;
                $regd_branch->save();
            }else{
                BranchDetail::where('user_type', 2)
                        ->where('record_type', 1)
                        ->where('is_regd_address', 1)
                        ->where('user_id', $company_id)
                        ->update(
                            array(
                                'gstin'=> $request->gstin,
                                'address'=> $request->registered_address,
                                'country'=> $request->country,
                                'state'=> !empty($request->state) ? $request->state : null,
                                // 'city'=> !empty($request->city) ? $request->city : null,
                                'pincode'=> $request->pincode,
                                'authorized_designation'=> $vendor_data->name,
                                'mobile'=> $vendor_data->mobile,
                                'email'=> $vendor_data->email,
                                'updated_by'=> Auth::user()->id
                            )
                        );
            }

            if(empty($vendor->vendor_code)){
                $plan = Plan::find($request->vendor_plan);
    
                // update account details
                UserPlan::updateOrCreate(
                    ['user_id' => $company_id], // Unique identifying field
                    [
                        'user_type' => 2,
                        'plan_id' => 12,
                        'no_of_users' => $plan->no_of_user,
                        'price' => 0,
                        'gst' => 18,
                        'final_amount' => 0,
                        'payment_salt' => '',
                        'start_date' => now()->format('Y-m-d'),
                        'subscription_period' => "3 Years",
                        'next_renewal_date' => now()->addYears(3)->format('Y-m-d'),
                        'activated_by' => auth()->user()->id
                    ]
                );
    
                // update vendor code in vendor table
                $state_id = $vendor->state ? $vendor->state : 0;
                $vendor_code = generateVendorCode($state_id);
                
                $vendor->vendor_code = $vendor_code;
                $vendor->plan_id = $plan->id;
                $vendor->save();

                $mail_data = vendorEmailTemplet('new-vendor-registration-by-superadmin');
                $mail_msg = $mail_data->mail_message;
                $mail_subject = $mail_data->subject;

                $bold_html_password = '<span style="font-weight: 600" >'.$vendor_data->mobile.'<span>';
                $mail_msg = str_replace('$vendor_name', $vendor->legal_name, $mail_msg);
                $mail_msg = str_replace('$referred_by_buyer', $vendor->referred_by, $mail_msg);
                $mail_msg = str_replace('$vendor_email', $vendor_data->email, $mail_msg);
                $mail_msg = str_replace('$vendor_password', $bold_html_password, $mail_msg);

                EmailHelper::sendMail($vendor_data->email, $mail_subject, $mail_msg);
            }

            // echo "<pre>";
            // print_r($vendor_data);
            // die;

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Vendor Profile updated successfully.',
                'redirectUrl' => route('admin.vendor.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to update Vendor Profile. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }
    }

    private function validateVendorProfile($request)
    {
        $company_id = $request->company_id;
        return Validator::make($request->all(), [
            'legal_name' => ['required', 'string', 'max:255', 'regex:/^([a-zA-Z0-9.\&\(\)\+,\- ])+$/'],
            'profile_img' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
            'date_of_incorporation' => [
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
            'nature_of_organization' => ['required', 'integer', Rule::in(DB::table("nature_of_organization")->select("id")->pluck("id")->toArray())],
            // 'nature_of_business' => ['required', 'integer', Rule::in(DB::table("nature_of_business")->select("id")->pluck("id")->toArray())],
            'registered_address' => 'required|max:1700',
            'other_contact_details' => ['string', 'regex:/^[0-9,\-\/ ]+$/', 'max:255'],
            'country' => ['required', 'integer', Rule::in(DB::table("countries")->select("id")->pluck("id")->toArray())],
            'state' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('country') == 101) {
                        if (empty($value) || !preg_match('/^[0-9]+$/', $value)) {
                            $fail('Company State is required');
                        }
                    }else{
                        if (!empty($value) && !preg_match('/^[0-9]+$/', $value)) {
                            $fail('Company State is invalid');
                        }
                    }
                    if(!empty($value) && !in_array($value, DB::table("states")->select("id")->where("country_id", $request->input('country'))->pluck("id")->toArray())){
                        $fail('Company State is invalid');
                    }
                }
            ],
            // 'city' => [
            //     function ($attribute, $value, $fail) use ($request) {
            //         if ($request->input('country') == 101) {
            //             if (empty($value) || !preg_match('/^[0-9]+$/', $value)) {
            //                 $fail('Company City is required');
            //             }
            //         }else{
            //             if (!empty($value) && !preg_match('/^[0-9]+$/', $value)) {
            //                 $fail('Company City is invalid');
            //             }
            //         }
            //         if (!empty($value) && !empty($request->input('state'))) {
            //             if(!empty($value) && !in_array($value, DB::table("cities")->select("id")->where("state_id", $request->input('state'))->pluck("id")->toArray())){
            //                 $fail('Company City is invalid');
            //             }
            //         }
            //     }
            // ],
            'pincode' => [
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('country') == 101) {
                        if (empty($value)) {
                            $fail('Company Pincode is required');
                        }else if(!preg_match('/^[0-9]+$/', $value) || strlen($value) != 6){
                            $fail('Company Pincode is invalid');
                        }
                    }else{
                        if(!empty($value) && !preg_match('/^[0-9]+$/', $value)){
                            $fail('Company Pincode is invalid');
                        }
                    }
                }
            ],
            'gstin' => [
                function ($attribute, $value, $fail) use ($request, $company_id) {
                    if ($request->input('country') == 101) {
                        if (empty($value)) {
                            $fail('Company GSTIN/VAT is required');
                        }else if(strlen($value) != 15){
                            $fail('Company GSTIN/VAT should be 15 characters');
                        }else if(!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $value)){
                            $fail('Company GSTIN/VAT is invalid');
                        }else if(!empty(Vendor::where("gstin", $value)->select("user_id", "gstin")->where("user_id", "!=", $company_id)->first())){
                            $fail($value." GSTIN/VAT is already exists");
                        }
                    }else{
                        if (empty($value)) {
                            $fail('Company Tax Identification Number is required');
                        }else if (strlen($value) > 150) {
                            $fail('Company Tax Identification Number should be less than 150 characters');
                        }else if(!preg_match('/^[A-Z0-9]+$/', $value)){
                            $fail('Company Tax Identification Number is invalid');
                        }
                    }
                }
            ],
            'gstin_document' => ['file', 'mimes:jpg,jpeg,pdf', 'max:2048'],
            'website' => ['nullable', 'url'],
            'company_name1' => ['required', 'string', 'max:255'],
            'company_name2' => ['string', 'max:255'],
            'registered_product_name' => ['required', 'string', 'max:350'],
            // 'vendor_plan' => ['required', 'integer', Rule::in(DB::table("plans")->select("id")->where('type', 2)->where('status', 1)->pluck("id")->toArray())],
            'vendor_plan' => [
                function ($attribute, $value, $fail) use ($request, $company_id) {
                    $vendor_data = DB::table("vendors")->select("vendor_code")->where("user_id", $company_id)->first();
                    if(empty($vendor_data->vendor_code) && empty($request->input('vendor_plan'))){
                        $fail('Please Select a Plan');                        
                    }
                    // apply rules for Rule::in(DB::table("plans")->select("id")->where('type', 2)->where('status', 1)->pluck("id")->toArray())
                    if(!in_array($value, DB::table("plans")->select("id")->where('type', 2)->where('status', 1)->pluck("id")->toArray())) {
                        $fail('Please Select a valid Plan');
                    }
                }
            ],
            'description' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $wordCount = str_word_count(strip_tags($value));
                    if ($wordCount > 300) {
                        $fail('Description must not contain more than 300 words.');
                    }
                }
            ],
            't_n_c' => ['required', 'accepted']
        ]);
    }



    public function profileStatus(Request $request)
    {
        $user_id = $request->user_id;
        
        $vendor_data = User::with(["vendor"])->where("id", $user_id)->first();
        if(empty($vendor_data)){
            return response()->json(['status' => false, 'message' => 'Vendor not found.']);
        }
        $vendor = $vendor_data->vendor;

        // Check if vendor profile is complete (T&C accepted)
        if ($vendor->t_n_c != 1) {
            return response()->json([
                'status' => 0,
                'message' => 'Vendor Profile Not Completed'
            ]);
        }

        // Update is_verified field
        $vendor_data->is_verified = $request->status;

        DB::beginTransaction();

        try {
            $vendor_code = '';
            // Update is_profile_verified only if it is currently 0/null and new status is 1
            if ($vendor_data->is_profile_verified == 2) {
                // Get the lowest user active Vendor plan (type = 2)
                $plan = Plan::where('type', 2)
                ->where('status', 1)
                ->orderBy('no_of_user', 'asc')
                ->first();
                
                if (!empty($plan)) {

                    $vendor_data->is_profile_verified = 1;
                    $vendor_data->status = 1;
                    $vendor_data->verified_by = auth()->user()->id;
                    
                    // Add entry in user_plans table
                    UserPlan::create([
                        'user_type'            => 2,
                        'user_id'              => $vendor_data->id,
                        'plan_id'              => 12,
                        'plan_name'            => '',
                        'plan_amount'          => 0,
                        // 'trial_period'         => '',
                        'no_of_users'          => $plan->no_of_user,
                        'discount'             => 0,
                        'gst'                  => 18,
                        'final_amount'         => 0,
                        'start_date'           => now()->format('Y-m-d'),
                        'subscription_period'  => '3 Years',
                        'next_renewal_date'    => now()->addYears(3)->format('Y-m-d'),
                        'is_expired'           => '2',
                        'created_at'           => now(),
                        'activated_by'         => auth()->user()->id
                    ]);

                    // update vendor code in vendor table
                    $state_id = $vendor_data->vendor->state ? $vendor_data->vendor->state : 0;
                    $vendor_code = generateVendorCode($state_id);
                    
                    $vendor->vendor_code = $vendor_code;
                    $vendor->plan_id = $plan->id;
                    $vendor->save();
                    
                    // logout company session
                    UserSession::where('user_id', $vendor_data->id)->update(['data' => null]);
    
                    // send verification mail to vendor
                    $this->sendVendorVerificationMail($vendor_data);
                }
            }

            $vendor_data->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'is_reload' => !empty($vendor_code) ? true : false,
                'message' => 'Vendor Profile Status updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to update Vendor Profile Status. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }
    }

    public function status(Request $request)
    {
        $id=$request->user_id;
        $vendor = Vendor::find($id);
        $vendor->user->status = $request->status;
        $vendor->user->save();
        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
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
        
        $user = User::with(["vendor", "branchDetails", "vendorRegisteredBranch"])->where("id", $request->user_id)->first();

        if(empty($user)){
            return response()->json([
                'status' => false,
                'message' => 'Vendor Profile not found.'
            ]);
        }

        if(!empty($user->vendor->vendor_code)){
            return response()->json([
                'status' => false,
                'message' => 'Vendor profile is already verified and cannot be deleted, please refresh the page.'
            ]);
        }

        if($user->status == 1){
            return response()->json([
                'status' => false,
                'message' => 'Vendor is active and cannot be deleted, please make inactive the vendor status.'
            ]);
        }

        DB::beginTransaction();

        try {
            // Delete vendor profile
            if ($user->vendor) {
                if ($user->vendor->profile_img) {
                    removeFile(public_path('uploads/vendor-profile/'.$user->vendor->profile_img));
                }
                if ($user->vendor->gstin_document) {
                    removeFile(public_path('uploads/vendor-profile/'.$user->vendor->gstin_document));
                }
                if ($user->vendor->msme_certificate) {
                    removeFile(public_path('uploads/vendor-profile/'.$user->vendor->msme_certificate));
                }
                $user->vendor->delete();
            }

            $email = $user->email;
            
            // Delete branchDetails and their files
            foreach ($user->branchDetails as $branch) {
                $branch->delete();
            }

            // Delete all vendor Registered Branch details
            $user->vendorRegisteredBranch->delete();
            // foreach ($user->vendorRegisteredBranch as $regd_branch) {
            // }

            // Finally, delete the user
            $user->delete();

            // logout company session
            UserSession::where('user_id', $request->user_id)->delete();

            // delete login attempt
            LoginAttempt::where('user_id', $email)->delete();

            // delete notification
            Notification::where('sender_id', $request->user_id)->delete();;
            
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Vendor successfully deleted.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete Vendor. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }
    }

    public function plan(Request $request,$id)
    {
        $vendor = Vendor::find($id);
        $plans = Plan::where('type', 2)->where('status',1)->orderBy('no_of_user','asc')->get();
        $user_plans = UserPlan::where('user_id', $vendor->user->id)->orderBy('id','desc')->first();
        
        return view('admin.vendor.plan', compact('vendor','plans','user_plans'));
    }

    public function planUpdate(Request $request,$id)
    {   
        $validator = Validator::make($request->all(), [
            'plan_id'=>'required|exists:plans,id',
            'plan_duration'=>['required', 'integer', Rule::in([3, 6, 12])],
            // 'no_of_user'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
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

        $vendor = Vendor::find($id);
        $no_of_user = $plan->no_of_user;
        $plan_amount = $plan->price;
        
        $plan_duration = (int) $request->plan_duration;
        switch ($plan_duration) {
            case 6:
                $price = number_format($plan_amount / 2, 2, '.', '');
                break;
            case 3:
                $price = number_format($plan_amount / 4, 2, '.', '');
                break;
            default:
                $price = number_format($plan_amount, 2, '.', '');
                break;
        }

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

            $invoice_no = InvoiceNumber::generateInvoiceNumber($vendor->user_id);
            
            UserPlan::where('user_id', $vendor->user_id)->where('is_expired', 2)->update(['is_expired' => 1]);

            $userPlan = new UserPlan;
            $userPlan->user_type = 1;
            $userPlan->user_id = $vendor->user_id;
            $userPlan->plan_id = $plan_id;
            $userPlan->plan_name = $plan->plan_name;
            $userPlan->plan_amount = $plan_amount;
            $userPlan->no_of_users = $no_of_user;
            $userPlan->discount = $discount;
            $userPlan->gst = $gst;
            $userPlan->final_amount = $total;
            $userPlan->start_date = now()->format('Y-m-d');
            $userPlan->subscription_period = $plan_duration." Months";
            $userPlan->next_renewal_date = now()->addMonths($plan_duration)->format('Y-m-d');
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
                'message' => 'Failed to activate Vendor Plan. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }
    }

    public function profile(Request $request,$id)
    {
        $company_id = $id;
        
        $vendor_data = User::with(["vendor", "branchDetails"])->where("id", $id)->first();
        if(empty($vendor_data)){
            return redirect()->route('admin.vendor.index')->with('error','Vendor not found');
        }

        $nature_of_organization = DB::table("nature_of_organization")
                    ->select("id", "organization_name")
                    ->orderBy("id", "DESC")
                    ->pluck("organization_name", "id")->toArray();

        $nature_of_business = DB::table("nature_of_business")
                    ->select("id", "business_name")
                    ->orderBy("id", "DESC")
                    ->pluck("business_name", "id")->toArray();

        $countries = DB::table("countries")
                    ->select("id", "name")
                    ->orderBy("name", "ASC")
                    ->pluck("name", "id")->toArray();
        
        $india_states = array();

        $vendor_plan = Plan::where('type', 2)->where('status', 1)->orderBy('no_of_user', 'asc')->get();

        return view('admin.vendor.profile', compact('vendor_data', 'countries', 'india_states', 'nature_of_organization', 'nature_of_business', 'vendor_plan'));
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

        $vendor_data = User::with(["vendor"])->where("id", $user_id)->first();
        if(empty($vendor_data)){
            return response()->json(['status' => false, 'message' => 'Vendor not found.']);
        }
        $vendor = $vendor_data->vendor;

        if(empty($vendor->t_n_c)){
            return response()->json(['status' => false, 'message' => 'Vendor Profile Not Completed']);
        }
        if(!empty($vendor->vendor_code)){
            return response()->json(['status' => false, 'message' => 'Vendor Profile already Verified']);
        }

        $plan = Plan::find($plan_id);
        if(empty($plan)){
            return response()->json(['status' => false, 'message' => 'Plan not found.']);
        }

        $free_plan = Plan::find(12);

        DB::beginTransaction();

        try {

            // update status in user table
            $vendor_data->is_profile_verified = 1;
            $vendor_data->is_verified = 1;
            $vendor_data->status = 1;
            $vendor_data->verified_by = auth()->user()->id;
            $vendor_data->save();

            // update in user_plan update or insert table
            UserPlan::updateOrCreate(
                ['user_id' => $vendor_data->id], // Unique identifying field
                [
                    'user_type' => 2,
                    'plan_id' => $free_plan->id,
                    'no_of_users' => $plan->no_of_user,
                    'price' => $free_plan->price,
                    'gst' => 18,
                    'final_amount' => 0,
                    'payment_salt' => '',
                    'start_date' => now()->format('Y-m-d'),
                    'subscription_period' => "3 Years",
                    'next_renewal_date' => now()->addYears(3)->format('Y-m-d'),
                    'activated_by' => auth()->user()->id
                ]
            );

            // update vendor code in vendor table
            $state_id = $vendor_data->vendor->state ? $vendor_data->vendor->state : 0;
            $vendor_code = generateVendorCode($state_id);
            
            $vendor->vendor_code = $vendor_code;
            $vendor->plan_id = $plan->id;
            $vendor->save();
            
            // logout company session
            UserSession::where('user_id', $vendor_data->id)->update(['data' => null]);

            // send verification mail to vendor
            $this->sendVendorVerificationMail($vendor_data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Vendor Profile verification Successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to complete Vendor Profile. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }

    }
    private function sendVendorVerificationMail($vendor_data)
    {
        $mail_data = vendorEmailTemplet('Vendor-Verification-Email-trail');
        $mail_msg = $mail_data->mail_message;
        $mail_subject = $mail_data->subject;
        $mail_msg = str_replace('$name', $vendor_data->vendor->legal_name, $mail_msg);
        $mail_msg = str_replace('$link', route('login'), $mail_msg);

        EmailHelper::sendMail($vendor_data->email, $mail_subject, $mail_msg);
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
            return view('admin.vendor.partials.user-table', compact('results','id'))->render();
        }
        return view('admin.vendor.user', compact('results','id'));
    }

    public function exportTotal(Request $request){
        $query = Vendor::with(['user'])->withCount(['vendor_products as vendor_products_count' => function ($query) {
            $query->where('approval_status', 1)
                  ->where('edit_status', 0);
        }]);
        if ($request->filled('user')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('user') . '%');
                $q->orWhere('email', 'like', '%' . $request->input('user') . '%');
                $q->orWhere('mobile', 'like', '%' . $request->input('user') . '%');
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('status',$request->input('status'));
            });
        }
        if ($request->filled('profile_status')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('is_verified',$request->input('profile_status'));
            });
        }
        $total = $query->count();
        return response()->json(['total' => $total]);
    }

    public function exportBatch(Request $request){
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));
        $query = Vendor::with(['user'])->withCount(['vendor_products as vendor_products_count' => function ($query) {
            $query->where('approval_status', 1)
                  ->where('edit_status', 0);
        }]);
        if ($request->filled('user')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('user') . '%');
                $q->orWhere('email', 'like', '%' . $request->input('user') . '%');
                $q->orWhere('mobile', 'like', '%' . $request->input('user') . '%');
            });
        }
        if ($request->filled('status')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('status',$request->input('status'));
            });
        }
        if ($request->filled('profile_status')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('is_verified',$request->input('profile_status'));
            });
        }
        $query->orderBy('vendors.updated_at', 'desc');
        $results= $query->offset($offset)->limit($limit)->get();
        $result=[];
        foreach($results as $res){
            $result[]=[
                $res->vendor_code,
                $res->legal_name,
                $res->user->name,
                $res->user->email,
                optional($res->vendorVerifiedAt()->first())->start_date ? date("d/m/Y", strtotime($res->vendorVerifiedAt()->first()->start_date)) : '',
                $res->vendor_products_count,
                (!empty($res->user->country_code)?'+'.$res->user->country_code:'').' '.$res->user->mobile,
                $res->user->status==1?'Active':'Inactive',
                $res->user->is_verified==1?'Verified':'Not Verified',
                $res->user->user_created_by,
               
            ];
        }
        return response()->json(['data'=>$result]);
    }   
}
