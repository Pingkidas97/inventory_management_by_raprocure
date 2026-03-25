<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Models\User;
use App\Models\Vendor;
use App\Models\BranchDetail;
use App\Helpers\EmailHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class VendorProfileController extends Controller
{
    public $vendor_profile_dir = 'vendor-profile';

    public function index()
    {
        $company_id = getParentUserId();
        $vendor_data = User::with(["vendor", "branchDetails"])->where("id", $company_id)->first();
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
        
        $india_states = DB::table("states")
                    ->select("id", "name")
                    ->where("country_id", 101)
                    ->orderBy("name", "ASC")
                    ->pluck("name", "id")->toArray();

        return view('vendor.setting.profile', compact('vendor_data', 'countries', 'india_states', 'nature_of_organization', 'nature_of_business'));
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

        $company_id = getParentUserId();

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

    public function saveVendorProfile(Request $request)
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

            'branch_name' => array_map('trim', $request->input('branch_name', [])),
            'branch_gstin' => array_map('trim', $request->input('branch_gstin', [])),
            'branch_address' => array_map('trim', $request->input('branch_address', [])),
            'branch_country' => array_map('trim', $request->input('branch_country', [])),
            'branch_state' => array_map('trim', $request->input('branch_state', [])),
            // 'branch_city' => array_map('trim', $request->input('branch_city', [])),
            'branch_pincode' => array_map('trim', $request->input('branch_pincode', [])),
            'branch_authorized_designation' => array_map('trim', $request->input('branch_authorized_designation', [])),
            'branch_mobile' => array_map('trim', $request->input('branch_mobile', [])),
            'branch_email' => array_map('trim', $request->input('branch_email', [])),
            'branch_status' => array_map('trim', $request->input('branch_status', [])),

            'msme' => trim($request->msme),
            'msme_certificate_old' => trim($request->msme_certificate_old),
            'iso_registration' => trim($request->iso_registration),
            'iso_regi_certificate_old' => trim($request->iso_regi_certificate_old),
            'description' => trim($request->description),
            't_n_c' => trim($request->t_n_c)
        ]);
        
        $company_id = getParentUserId();

        $validator = $this->validateVendorProfile($request);

        if ($validator->fails()) {
            // Return or print errors
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 200);
        }

        $current_user_id = Auth::user()->id;
        $file_prefix = 'V' . $current_user_id. '-';

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

            $vendor->msme = $request->msme;
            if ($request->hasFile('msme_certificate')) {
                $res = uploadFile($request, 'msme_certificate', $this->vendor_profile_dir, $file_prefix . 'MSME');
                if($res['status']){
                    if(!empty($vendor->msme_certificate)){
                        removeFile(public_path('uploads/'.$this->vendor_profile_dir.'/'.$vendor->msme_certificate));
                    }
                    $vendor->msme_certificate = $res['file_name'];
                }else{
                    throw new \Exception($res['file_name']);
                }
            }else if(!empty($request->msme_certificate_old)){
                $vendor->msme_certificate = $request->msme_certificate_old;
            }
            $vendor->iso_registration = $request->iso_registration;
            if ($request->hasFile('iso_regi_certificate')) {
                $res = uploadFile($request, 'iso_regi_certificate', $this->vendor_profile_dir, $file_prefix . 'ISO');
                if($res['status']){
                    if(!empty($vendor->iso_regi_certificate)){
                        removeFile(public_path('uploads/'.$this->vendor_profile_dir.'/'.$vendor->iso_regi_certificate));
                    }
                    $vendor->iso_regi_certificate = $res['file_name'];
                }else{
                    throw new \Exception($res['file_name']);
                }
            }else if(!empty($request->iso_regi_certificate_old)){
                $vendor->iso_regi_certificate = $request->iso_regi_certificate_old;
            }

            $vendor->description = $request->description;
            $vendor->t_n_c = $request->t_n_c;
            
            $vendor->updated_by = $current_user_id;
            $vendor->save();

            if(isset($request->branch_name) && !empty($request->branch_name)){
                foreach ($request->branch_name as $key => $value) {
                    $is_new_branch = true;
                    if($request->edit_id_branch[$key]!=0){
                        $isExists = BranchDetail::where('branch_id', $request->edit_id_branch[$key])
                                        ->where('user_type', 2)
                                        ->where('record_type', 1)
                                        ->where('user_id', $company_id)
                                        ->first();
                        if(!empty($isExists)){
                            $is_new_branch = false;
                        }
                    }

                    if($is_new_branch==true){
                        $new_branch = new BranchDetail();
                        $new_branch->name = remove_extra_spaces($request->branch_name[$key]);
                        $new_branch->gstin = $request->branch_gstin[$key];
                        $new_branch->address = $request->branch_address[$key];
                        $new_branch->country = $request->branch_country[$key];
                        $new_branch->state = !empty($request->branch_state[$key]) ? $request->branch_state[$key] : null;
                        // $new_branch->city = !empty($request->branch_city[$key]) ? $request->branch_city[$key] : null;
                        $new_branch->pincode = $request->branch_pincode[$key];
                        $new_branch->authorized_designation = $request->branch_authorized_designation[$key];
                        $new_branch->mobile = $request->branch_mobile[$key];
                        $new_branch->email = $request->branch_email[$key];
                        $new_branch->status = $request->branch_status[$key];
                        $new_branch->user_type = 2;
                        $new_branch->user_id = $company_id;
                        $new_branch->record_type = 1;
                        $new_branch->branch_id = 0;
                        $new_branch->updated_by = Auth::user()->id;
                        $new_branch->save();
                        $new_branch->branch_id = $new_branch->id;
                        $new_branch->save();
                    }else{
                        BranchDetail::where('branch_id', $request->edit_id_branch[$key])
                                ->where('user_type', 2)
                                ->where('record_type', 1)
                                ->where('user_id', $company_id)
                                ->update(
                                    array(
                                        'name'=> remove_extra_spaces($request->branch_name[$key]),
                                        'gstin'=> $request->branch_gstin[$key],
                                        'address'=> $request->branch_address[$key],
                                        'country'=> $request->branch_country[$key],
                                        'state'=> !empty($request->branch_state[$key]) ? $request->branch_state[$key] : null,
                                        // 'city'=> !empty($request->branch_city[$key]) ? $request->branch_city[$key] : null,
                                        'pincode'=> $request->branch_pincode[$key],
                                        'authorized_designation'=> $request->branch_authorized_designation[$key],
                                        'mobile'=> $request->branch_mobile[$key],
                                        'email'=> $request->branch_email[$key],
                                        'status'=> $request->branch_status[$key],
                                        'updated_by'=> Auth::user()->id,
                                    )
                                );
                    }
                }
            }

            $parentUser = getParentDetails();
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
                $regd_branch->authorized_designation = $parentUser->name;
                $regd_branch->mobile = $parentUser->mobile;
                $regd_branch->email = $parentUser->email;
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
                                'authorized_designation'=> $parentUser->name,
                                'mobile'=> $parentUser->mobile,
                                'email'=> $parentUser->email,
                                'updated_by'=> Auth::user()->id
                            )
                        );
            }

            $redirectUrl = '';
            if(Auth::user()->is_verified==1){
                $redirectUrl = route('vendor.dashboard');
            }else{
                $redirectUrl = route('vendor.profile-complete');
            }

            DB::commit();

            Session::put('legal_name', $vendor->legal_name);

            return response()->json([
                'status' => true,
                'message' => 'Vendor Profile completed',
                'redirectUrl' => $redirectUrl
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

    private function validateVendorProfile($request)
    {
        $company_id = getParentUserId();
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
            'other_contact_details' => ['required', 'string', 'regex:/^[0-9,\-\/ ]+$/', 'max:255'],
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
            'gstin_document' => ['required_without:gstin_document_old', 'file', 'mimes:jpg,jpeg,pdf', 'max:2048'],
            'website' => ['nullable', 'url'],
            'company_name1' => ['required', 'string', 'max:255'],
            'company_name2' => ['required', 'string', 'max:255'],
            'registered_product_name' => ['required', 'string', 'max:350'],

            'branch_name.*' => ['sometimes', 'required', 'string', 'regex:/^([a-zA-Z0-9.\&\(\)\+,\- ])+$/', 'max:255'],
            'branch_gstin.*' => ['sometimes', function ($attribute, $value, $fail) use ($request, $company_id) {
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
            'branch_address.*' => ['sometimes', 'required', 'max:1700'],
            'branch_country.*' => ['sometimes', 'required', 'integer', 'same:country'],
            'branch_state.*' => ['sometimes', 
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
            // 'branch_city.*' => ['sometimes', 
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
            'branch_pincode.*' => ['sometimes', 
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
            'branch_authorized_designation.*' => ['sometimes', 'required', 'max:255', 'string'],
            'branch_mobile.*' => [
                'sometimes', 
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
            'branch_email.*' => ['sometimes', 'required', 'max:255', 'email'],
            'branch_status.*' => ['sometimes', 'required', Rule::in(array(1, 2))],

            'msme' => ['nullable', 'max:255', 'string'],
            'msme_certificate' => ['nullable', 'file', 'mimes:jpg,jpeg,pdf', 'max:2048'],
            'iso_registration' => ['sometimes', 'nullable', 'max:255', 'string'],
            'iso_regi_certificate' => ['sometimes', 'nullable', 'file', 'mimes:jpg,jpeg,pdf', 'max:2048'],

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

    public function profileComplete()
    {
        if(Auth::user()->is_profile_verified==1){
            return redirect()->to(route('vendor.dashboard'));
        }
        return view('vendor.setting.profile-success');
    }

    public function changePassword(request $request)
    {
        return view('vendor.setting.change-password');
    }
    public function updatePassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required|string|min:8|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->toArray()
            ]);
        }

        try {
            $user = Auth::user();

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ]);
            }   

            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully',
                'redirect' => route('vendor.dashboard')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating password: ' . $e->getMessage()
            ], 500);
        }
    }
}
