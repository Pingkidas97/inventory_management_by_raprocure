@extends('buyer.layouts.app', ['title'=>'Buyer Profile', 'sub_title'=>'Create'])

@section('css') 
    
    <link href="{{ asset('public/assets/library/sumoselect-v3.4.9-2/css/sumoselect.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/library/sumoselect-v3.4.9-2/css/sumo-select-style.css') }}" rel="stylesheet">
<link href="{{ asset('public/assets/buyer/css/additional-custom-style.css') }}" rel="stylesheet">
    <style>
        span.tmd-serial-no, span.branch-serial-no {
            font-size: 16px;
        }
        #submit-buyer-profile .spinner-border {
            height: 14px;
            width: 14px;
        }
    </style>
@endsection

@section('content')
    @php
        $buyer = $buyer_data->buyer;
        $branchDetails = $buyer_data->branchDetails;
        $topManagamantDetails = $buyer_data->topManagamantDetails;
    @endphp
    <div class="bg-white">
        <!---Sidebar-->
        @if(Auth::user()->is_profile_verified==1)
            @include('buyer.layouts.sidebar-menu')
        @endif
    </div>
    
    <main class="main flex-grow-1">
        <div class="my_profile card bg-white">
            <div class="card-header d-flex align-items-center w-100 border-0 bg-transparent justify-content-between">
                <h4 class="card-title">My Profile </h4>
                @if(Auth::user()->is_profile_verified==1)
                <a href="javascript:void(0);" id="make-editable-profile" class="btn-rfq btn-rfq-secondary">Edit</a>
                @endif
            </div>
            <div class="card-body">
                <div class="tab-content-inner">
                    <form id="buyer-profile-form" action="{{ route('buyer.save-buyer-profile') }}" method="POST">
                        @csrf
                        <div class="row">
                            <h2 class="col-md-12 text-center profile-type-title">Profile Form - for Steel Manufacturing Plants</h2>
                        </div>
                        <div class="row mt-4">
                            <h2 class="mb-4 mt-10 col-md-12">Organization Details</h2>
                        </div>
                        <div class="basic-form">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Company Name / Legal Name<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control required text-upper-case" value="{{ $buyer->legal_name }}"
                                        placeholder="Enter Company Name / Legal Name" name="legal_name" id="legal_name"
                                        oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')" maxlength="255">
                                </div>

                                <div class="form-group col-md-6">                                
                                    <label>Date of Incorporation (DD/MM/YYYY)<span
                                            class="text-danger">*</span></label>
                                    <input type="text" placeholder="Date format is DD/MM/YYYY" onblur="validateDateFormat(this, true);"
                                        class="form-control required date-masking" id="incorporation_date" name="incorporation_date"
                                        value="{{ !empty($buyer->incorporation_date) ? date("d/m/Y", strtotime($buyer->incorporation_date)) : '' }}" maxlength="10">
                                </div>
                                <div class="form-group col-md-12">
                                    <input type="hidden" name="logo_old" value="{{ $buyer->logo }}" >
                                    <label class="text-dark">Logo (File Type: JPG,JPEG,PNG)</label>
                                    <div class="file-browse">
                                        <span class="button button-browse">
                                            Select <input type="file" class="logo" name="logo" onchange="validateFile(this, 'JPG/JPEG/PNG')">
                                        </span>
                                        <input type="text" class="form-control" placeholder="Upload Logo" readonly="" >
                                    </div>
                                    <span>
                                        @if (is_file(public_path('uploads/buyer-profile/'.$buyer->logo)))
                                            <a class="file-links" href="{{ url('public/uploads/buyer-profile/'.$buyer->logo) }}" target="_blank" download="{{ $buyer->logo }}">
                                                <span>{!! strlen($buyer->logo)>30 ? substr($buyer->logo, 0, 25).'<i class="bi bi-info-circle-fill" title="'.$buyer->logo.'" ></i>' : $buyer->logo !!} </span>
                                            </a>
                                        @endif
                                    </span>
                                </div>
                                <div class="form-group col-12">
                                    <label>Registered Address<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control registered_address required"
                                        id="registered_address" name="registered_address" value="{{ $buyer->registered_address }}" maxlength="1700" placeholder="Enter Registered Address">
                                </div>

                                <div class="form-group col-md-3">
                                    <label>Country<span class="text-danger">*</span></label>
                                    <select class="form-select required organization-country {{ Auth::user()->is_profile_verified==1 ? 'disabled' : '' }}" 
                                    onchange="getState('organization-country', 'organisation-state', 'organisation-city')" 
                                    name="country">
                                        @php
                                            if(empty($buyer->country)){
                                                $buyer->country = 101;
                                            }
                                        @endphp
                                        @if(!empty($countries))
                                            @foreach ($countries as $country_id => $country_name)
                                            <option value="{{ $country_id }}" {{ $buyer->country == $country_id ? 'selected' : '' }} >{{ $country_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label>State<span class="text-danger">*</span></label>
                                    <select class="form-select required organisation-state"
                                        {{-- onchange="getCity('organisation-state', 'organisation-city')"  --}}
                                        name="state">
                                        <option value="">Select State</option>
                                        {!! getStateByCountryId($buyer->country, $buyer->state??0) !!}
                                    </select>
                                </div>

                                {{-- <div class="form-group col-md-6">
                                    <label>City<span class="text-danger">*</span></label>
                                    <select class="form-select required organisation-city" name="city">
                                        <option value="">Select City</option>
                                        {!! !empty($buyer->state) ? getCityByStateId($buyer->state, $buyer->city??0) : '' !!}
                                    </select>
                                </div> --}}

                                <div class="form-group col-md-6">
                                    <label>Pincode<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control organisation-pincode required" name="pincode" value="{{ $buyer->pincode }}" 
                                    onkeypress="return validatePinCode(event, this)"
                                    onpaste="return false;"  placeholder="Enter Pin Code"
                                    onblur="validatePinCodeWithCountry(this)" >
                                </div>

                                <div class="form-group col-xl-6 col-md-12">
                                    <label>
                                        <span class="gst-field-label-name"></span>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control gstin-vat required {{ Auth::user()->is_profile_verified==1 ? 'disabled' : '' }}" name="gstin" value="{{ $buyer->gstin }}" 
                                    maxlength="15" onblur="validateGSTVatWithCountry(this)" placeholder="{{ $buyer->country == 101 ? "Enter GSTIN/VAT" : "Enter your Tax Identification Number" }}">
                                </div>

                                <div class="form-group col-xl-6 -lg-6 col-md-12">
                                    <div class="row">
                                        <div class="form-group col-sm-5 buyer-pan-card-input-field {{ $buyer->country != 101 ? 'd-none' : '' }}">
                                            <label>PAN/TIN<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control organisation-pan-number required" name="pan" placeholder="Enter PAN/TIN"
                                                maxlength="10" value="{{ $buyer->pan }}" onblur="validatePanCardWithCountry(this)">
                                        </div>
                                        <div class="form-group col-sm-7 buyer-pan-card-upload-field">
                                            <input type="hidden" name="pan_file_old" value="{{ $buyer->pan_file }}" >
                                            <label class="text-dark">(File Type: JPG, JPEG, PDF)
                                                <span class="text-danger">* </span>
                                            </label>
                                            <div class="file-browse">
                                                <span class="button button-browse">
                                                    Select <input onchange="validateFile(this, 'JPG/JPEG/PDF')" type="file" class="{{ $buyer->pan_file=='' || $buyer->pan_file==null ? 'required-file' : '' }}" name="pan_file">
                                                </span>
                                                <input type="text" class="form-control" placeholder="Upload PAN/TIN Card" readonly="">
                                            </div>
                                            <span>
                                                @if (is_file(public_path('uploads/buyer-profile/'.$buyer->pan_file)))
                                                    <a class="file-links" href="{{ url('public/uploads/buyer-profile/'.$buyer->pan_file) }}" target="_blank" download="{{ $buyer->pan_file }}">
                                                        <span>{!! strlen($buyer->pan_file)>30 ? substr($buyer->pan_file, 0, 25).'<i class="bi bi-info-circle-fill" title="'.$buyer->pan_file.'" ></i>' : $buyer->pan_file !!} </span>
                                                    </a>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Website</label>
                                    <input type="text" class="form-control website-url" name="website" value="{{ $buyer->website }}" maxlength="255" placeholder="Enter Website">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Output / Product Details<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control product-details required" maxlength="1700" name="product_details" value="{{ $buyer->product_details }}" placeholder="Enter Output / Product Details">
                                </div>
                            </div>

                            <div class="hr_line"></div>

                            <div class="row mt-4 justify-content-between">
                                <h2 class="col-md-6 col-sm-6 col-12">Top Management Details</h2>
                                <div class="col-md-6 col-sm-6 col-12">
                                    <button href="javascript:void(0)" type="button"
                                        class="btn-rfq btn-rfq-secondary ms-auto d-table my-2 my-sm-0"
                                        onclick="addMoreTopManagementDetails()"> +Add More Top Management Details</button>
                                </div>
                            </div>

                            <div id="load-container" class="load-container">
                                @if(!empty($topManagamantDetails) && count($topManagamantDetails)>0)
                                    @foreach ($topManagamantDetails as $k => $tmd)
                                        <div class="row tmd-row">
                                            <div class="add_remove">
                                                <h4 class="frm_head"> <span class="tmd-serial-no">{{ $k+1 }}</span>. Top Management Details Information</h4>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Name<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control required text-upper-case" placeholder="Enter Name"
                                                    name="tdm_name[]" value="{{ $tmd->name }}" maxlength="255" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')">
                                                <input type="hidden" name="edit_id_tmd[]" value="{{ $tmd->branch_id }}">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Designation<span class="text-danger">*</span></label>
                                                <select class="form-select required" name="tdm_top_management_designation[]">
                                                    @if(!empty($director_designations))
                                                        @foreach ($director_designations as $designation_id => $designation_name)
                                                        <option value="{{ $designation_id }}" {{ $tmd->top_management_designation == $designation_id ? 'selected' : '' }} >{{ $designation_name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Mobile<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control validate-max-length my-mobile-number required" name="tdm_mobile[]" value="{{ $tmd->mobile }}"
                                                data-maxlength="{{ $buyer->country==101 ? "10" : "25" }}" data-minlength="{{ $buyer->country==101 ? "10" : "1" }}" placeholder="Enter Mobile">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Email<span class="text-danger">*</span></label>
                                                <input type="email" class="form-control valid-email required" name="tdm_email[]" value="{{ $tmd->email }}" placeholder="Enter Email">
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="row tmd-row">
                                        <div class="add_remove">
                                            <h4 class="frm_head"><span class="tmd-serial-no">1</span>. Top Management Details Information</h4>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Name<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control required text-upper-case" placeholder="Enter Name"
                                                name="tdm_name[]" value="" maxlength="255" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')">
                                            <input type="hidden" name="edit_id_tmd[]" value="0">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Designation<span class="text-danger">*</span></label>
                                            <select class="form-select required" name="tdm_top_management_designation[]">
                                                @if(!empty($director_designations))
                                                    @foreach ($director_designations as $designation_id => $designation_name)
                                                    <option value="{{ $designation_id }}">{{ $designation_name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Mobile<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control validate-max-length my-mobile-number required" name="tdm_mobile[]" value="" placeholder="Enter Mobile"
                                            data-maxlength="{{ $buyer->country==101 ? "10" : "25" }}" data-minlength="{{ $buyer->country==101 ? "10" : "1" }}">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Email<span class="text-danger">*</span></label>
                                            <input type="email" class="form-control valid-email required" name="tdm_email[]" value="" placeholder="Enter Email">
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="hr_line"></div>
                            <div class="row mt-4 justify-content-between">
                                <h2 class="col-md-6 col-sm-6 col-12">Branch/Unit Name</h2>
                                <div class="col-md-6 col-sm-6 col-12">
                                    <button href="javascript:void(0)" type="button"
                                        class="btn-rfq btn-rfq-secondary ms-auto d-table addmoreBtn my-2 my-sm-0"
                                        onclick="addMoreBranchFields()"> +Add More Branch/Unit Name</button>
                                </div>
                            </div>
                            <div id="branch_container">
                                @if(!empty($branchDetails) && count($branchDetails)>0)
                                    @foreach ($branchDetails as $k => $branch)
                                        <div class="row branch-row" data-row-id="{{ $k }}">
                                            <div class="add_remove">
                                                <h4 class="frm_head">BRANCH/UNIT <span class="branch-serial-no">{{ $k+1 }}</span></h4>
                                            </div>
                                            
                                            <div class="form-group col-md-12">
                                                <label>Branch/Unit Name<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control required text-upper-case" name="branch_name[]" value="{{ $branch->name }}" placeholder="Enter Branch/Unit Name" maxlength="255">
                                                <input type="hidden" name="edit_id_branch[]" value="{{ $branch->branch_id }}">
                                            </div>
                                            <div class="form-group col-md-12">
                                                <label>Address<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control required" maxlength="1700"
                                                    name="branch_address[]" value="{{ $branch->address }}" placeholder="Enter Address">
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Country<span class="text-danger">*</span></label>
                                                <select class="form-select branch-country disabled branch-country-{{ $branch->branch_id }} required" name="branch_country[]"
                                                    onchange="getState('branch-country-{{ $branch->branch_id }}', 'branch-state-{{ $branch->branch_id }}', 'branch-city-{{ $branch->branch_id }}')"
                                                >
                                                    @if(!empty($countries))
                                                        @foreach ($countries as $country_id => $country_name)
                                                        <option value="{{ $country_id }}" {{ $branch->country == $country_id ? 'selected' : '' }} >{{ $country_name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>

                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>State<span class="text-danger">*</span></label>
                                                <select class="form-select branch-state branch-state-{{ $branch->branch_id }} required"
                                                    {{-- onchange="getCity('branch-state-{{ $branch->branch_id }}', 'branch-city-{{ $branch->branch_id }}')" --}}
                                                    name="branch_state[]">
                                                    <option value="">Select State</option>
                                                    @php
                                                        $b_country = !empty($branch->country) ? $branch->country : 101;
                                                    @endphp
                                                    {!! getStateByCountryId($b_country, $branch->state??0) !!}
                                                </select>
                                            </div>

                                            {{-- <div class="form-group col-md-6">
                                                <label>City<span class="text-danger">*</span></label>
                                                <select class="form-select branch-city branch-city-{{ $branch->branch_id }} required" name="branch_city[]">
                                                    <option value="">Select City</option>
                                                    {!! !empty($branch->state) ? getCityByStateId($branch->state, $branch->city??0) : '' !!}
                                                </select>
                                            </div> --}}

                                            <div class="form-group col-md-6">
                                                <label>Pincode<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control branch-pincode required" name="branch_pincode[]" value="{{ $branch->pincode }}"
                                                onkeypress="return validatePinCode(event, this)" placeholder="Enter Pin Code"
                                                onblur="validatePinCodeWithCountry(this, true)">
                                            </div>

                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="form-group col-xl-3 col-md-6 col-12">
                                                        <label class="branch-gst-lable">
                                                            GSTIN/VAT
                                                            {!! $buyer->country != 101 ? '<i title="Please enter your Tax Identification Number" class="bi bi-info-circle-fill" aria-hidden="true"></i>' : "" !!}
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" class="form-control branch-gstin-vat required" name="branch_gstin[]" value="{{ $branch->gstin }}" 
                                                        maxlength="15" onblur="validateGSTVatWithCountry(this, true)" placeholder="{!! $buyer->country != 101 ? 'Enter your Tax Identification Number' : "Enter GSTIN/VAT" !!}">
                                                    </div>
                                                    <div class="form-group col-xl-3 col-md-6 col-12 file-browser">
                                                        
                                                    <input type="hidden" name="branch_gstin_file_old[]" value="{{ $branch->gstin_file }}" >
                                                        <label class="text-dark">(File Type: JPG, JPEG, PDF) <span class="text-danger">*</span></label>
                                                        <div class="file-browse">
                                                            <span class="button button-browse">
                                                            Select <input type="file" class="{{ $branch->gstin_file=='' || $branch->gstin_file==null ? 'required-file' : '' }}" name="branch_gstin_file[]" onchange="validateFile(this, 'JPG/JPEG/PDF')">
                                                            </span>
                                                            <input type="text" class="form-control" placeholder="Upload GSTIN/VAT" readonly="" >
                                                        </div>                                                   
                                                        <span>
                                                            @if (is_file(public_path('uploads/buyer-profile/'.$branch->gstin_file)))
                                                                <a class="file-links" href="{{ url('public/uploads/buyer-profile/'.$branch->gstin_file) }}" target="_blank" download="{{ $branch->gstin_file }}">
                                                                    <span>{!! strlen($branch->gstin_file)>30 ? substr($branch->gstin_file, 0, 25).'<i class="bi bi-info-circle-fill" title="'.$branch->gstin_file.'" ></i>' : $branch->gstin_file !!} </span>
                                                                </a>
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <div class="form-group col-xl-6 col-md-12 col-12">
                                                        <label>Name of Authorized Person<span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control required" name="branch_authorized_name[]" value="{{ $branch->authorized_name }}" placeholder="Enter Name of Authorized Person">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group col-md-6">
                                                <label>Designation of Authorized Person<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control required" name="branch_authorized_designation[]" value="{{ $branch->authorized_designation }}" placeholder="Enter Designation of Authorized Person">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Mobile<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control validate-max-length my-mobile-number required" name="branch_mobile[]" value="{{ $branch->mobile }}"
                                                data-maxlength="{{ $buyer->country == 101 ? 10 : 25 }}" data-minlength="{{ $buyer->country == 101 ? 10 : 1 }}" placeholder="Enter Mobile">
                                            </div>

                                            <div class="form-group col-md-6">
                                                <label>Email<span class="text-danger">*</span></label>
                                                <input type="email" class="form-control valid-email required" name="branch_email[]" value="{{ $branch->email }}" placeholder="Enter Email">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <label>Products Output/Products Manufactured</label>
                                                        <input type="text" class="form-control" name="branch_output_details[]" value="{{ $branch->output_details }}" maxlength="1700" placeholder="Enter Products Output/Products Manufactured">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Annual Capacity in Tonnage</label>
                                                <input type="text" placeholder="Enter Annual Capacity in Tonnage"
                                                    class="form-control" name="branch_installed_capacity[]" value="{{ $branch->installed_capacity }}" onkeypress='return event.charCode >= 48 && event.charCode <= 57'>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Division<span class="text-danger">*</span></label>
                                                <select class="form-control division-sumo-select required" name="branch_categories[{{ $k }}][]" multiple>
                                                    @php
                                                        $branch_category = !empty($branch->categories) ? explode(",", $branch->categories) : array();
                                                    @endphp
                                                    @if(!empty($divisions))
                                                        @foreach ($divisions as $id => $division_name)
                                                        <option value="{{ $id }}" {{ in_array($id, $branch_category) ? 'selected' : '' }} >{{ $division_name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label>Status<span class="text-danger">*</span></label>
                                                <div class="custom-file branch-toggle-div">
                                                    <label class="radio-inline mr-3">
                                                        <label class="switch">
                                                            <input onchange="branchStatus(this)" class="branch-status required" value="1" type="checkbox" {{ $branch->status == 1 ? "checked" : "" }} >
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </label>
                                                    <input class="branch-status-hidden" value="{{ $branch->status }}" type="hidden" name="branch_status[]">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="row branch-row">
                                        <div class="add_remove">
                                            <h4 class="frm_head">BRANCH/UNIT <span class="branch-serial-no">1</span></h4>
                                        </div>
                                        <div class="form-group col-md-12">
                                            <label>Branch/Unit Name<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control required text-upper-case" name="branch_name[]" value="" placeholder="Enter Branch/Unit Name" maxlength="255">
                                            <input type="hidden" name="edit_id_branch[]" value="0">
                                        </div>
                                        <div class="form-group col-md-12">
                                            <label>Address<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control required" maxlength="1700"
                                                name="branch_address[]" value="" placeholder="Enter Address">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Country<span class="text-danger">*</span></label>
                                            <select class="form-select branch-country disabled branch-country-n1 required" name="branch_country[]"
                                                onchange="getState('branch-country-n1', 'branch-state-n1', 'branch-city-n1')"
                                            >
                                                @if(!empty($countries))
                                                    @foreach ($countries as $country_id => $country_name)
                                                    <option value="{{ $country_id }}" {{ $country_id == 101 ? 'selected' : '' }}>{{ $country_name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>State<span class="text-danger">*</span></label>
                                            <select class="form-select branch-state branch-state-n1 required"
                                                {{-- onchange="getCity('branch-state-n1', 'branch-city-n1')"  --}}
                                                name="branch_state[]">
                                                <option value="">Select State</option>
                                                @if(!empty($india_states))
                                                    @foreach ($india_states as $id => $name)
                                                    <option value="{{ $id }}">{{ $name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>

                                        {{-- <div class="form-group col-md-6">
                                            <label>City<span class="text-danger">*</span></label>
                                            <select class="form-select branch-city branch-city-n1 required" name="branch_city[]">
                                                <option value="">Select City</option>
                                            </select>
                                        </div> --}}

                                        <div class="form-group col-md-6">
                                            <label>Pincode<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control branch-pincode required" name="branch_pincode[]" value=""
                                            onkeypress="return validatePinCode(event, this)" placeholder="Enter Pin Code"
                                            onblur="validatePinCodeWithCountry(this, true)">
                                        </div>

                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="form-group col-xl-3 col-md-6 col-12">
                                                    <label class="branch-gst-lable">
                                                        GSTIN/VAT
                                                        {!! $buyer->country != 101 ? '<i title="Please enter your Tax Identification Number" class="bi bi-info-circle-fill" aria-hidden="true"></i>' : "" !!}
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control branch-gstin-vat required" name="branch_gstin[]" value="" 
                                                    maxlength="15" onblur="validateGSTVatWithCountry(this, true)" placeholder="{!! $buyer->country != 101 ? 'Please enter your Tax Identification Number' : "Enter GSTIN/VAT" !!}">
                                                </div>
                                                <div class="form-group col-xl-3 col-md-6 col-12 file-browser">
                                                    <label class="text-dark">(File Type: JPG, JPEG, PDF) <span class="text-danger">*</span></label>
                                                    <div class="file-browse">
                                                        <span class="button button-browse">
                                                        Select <input type="file" class="required-file" name="branch_gstin_file[]" value="" onchange="validateFile(this, 'JPG/JPEG/PDF')">
                                                        </span>
                                                        <input type="text" class="form-control" placeholder="Upload GSTIN/VAT" readonly="" >
                                                    </div>    
                                                </div>
                                                <div class="form-group col-xl-6 col-md-12 col-12">
                                                    <label>Name of Authorized Person<span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control required" name="branch_authorized_name[]" value="" placeholder="Enter Name of Authorized Person">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group col-md-6">
                                            <label>Designation of Authorized Person<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control required" name="branch_authorized_designation[]" value="" placeholder="Enter Designation of Authorized Person">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Mobile<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control validate-max-length my-mobile-number required" name="branch_mobile[]" value="" placeholder="Enter Mobile"
                                            data-maxlength="{{ $buyer->country == 101 ? 10 : 25 }}" data-minlength="{{ $buyer->country == 101 ? 10 : 1 }}">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Email<span class="text-danger">*</span></label>
                                            <input type="email" class="form-control valid-email required" name="branch_email[]" value="" placeholder="Enter Email">
                                        </div>
                                        <div class="form-group col-md-6">                                        
                                            <label>Products Output/Products Manufactured</label>
                                            <input type="text" class="form-control" name="branch_output_details[]" value="" maxlength="1700" placeholder="Enter Products Output/Products Manufactured">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Annual Capacity in Tonnage</label>
                                            <input type="text" placeholder="Enter Annual Capacity in Tonnage"
                                                class="form-control" name="branch_installed_capacity[]" value="" onkeypress='return event.charCode >= 48 && event.charCode <= 57'> 
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Division<span class="text-danger">*</span></label>
                                            <select class="form-control division-sumo-select required" name="branch_categories[0][]" multiple>
                                                @if(!empty($divisions))
                                                    @foreach ($divisions as $id => $division_name)
                                                    <option value="{{ $id }}">{{ $division_name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Status<span class="text-danger">*</span></label>
                                            <div class="custom-file branch-toggle-div">
                                                <label class="radio-inline mr-3">
                                                    <label class="switch">
                                                        <input onchange="branchStatus(this)" class="branch-status required" value="1" type="checkbox" checked>
                                                        <span class="slider round"></span>
                                                    </label>
                                                </label>
                                                <input class="branch-status-hidden" value="1" type="hidden" name="branch_status[]">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="hr_line"></div>

                            <div class="mt-4 mb-3">
                                <h2 class="col-md-6">Other Information</h2>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label for="organisation-description" class="form-label">Organization Description<span class="text-danger">(Maximum 500 Words)*</span></label>
                                    <textarea class="form-control required" name="organisation_description" id="organisation-description" placeholder="Enter Organization Description"
                                        rows="3">{{ $buyer->organisation_description }}</textarea>
                                </div>
                                <div class="form-group col-md-6">
                                    <div class="mt-30">
                                        <label class="radio-inline mr-3">
                                            <input type="checkbox" class="subscribe-news-letter" {{ $subscribed == true ? 'checked' : '' }} > Subscribe for our offer news 
                                            <input type="hidden" name="subscribe_news_letter" class="subscribe-news-letter-hidden" value="{{ $subscribed == true ? '1' : '2' }}"> 
                                        </label>
                                    </div>
                                </div>
                                <div class="form-group col-md-12">
                                    <label>Short Code
                                        <span class="text-danger"> (Will be used as a prefix to your RFQ Number. It should be of 4 alphabets only)*</span>
                                    </label>
                                    <input type="text" class="form-control organisation-short-code {{ Auth::user()->is_profile_verified==1 ? 'disabled' : '' }} text-upper-case required" name="organisation_short_code" maxlength="4" minlength="4"
                                        value="{{ $buyer->organisation_short_code }}" placeholder="Enter Short Code" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')" >
                                </div>

                                <div class="form-group col-md-12 note-txt">
                                    <span>
                                        <strong>*Note: <br>
                                            1.</strong> Once your profile is verified by raProcure, you will be eligible to use all the services of the portal.
                                    </span> <br>
                                    <span>
                                        <strong>2.</strong> Plus GST on the Subscription charges mentioned above.
                                    </span>
                                </div>
                                <div class="form-group col-md-12 ">
                                    <label class="">
                                        <input type="checkbox" name="buyer_accept_tnc" value="1" {{ $buyer->buyer_accept_tnc == 1 ? "checked" : "checked" }} 
                                            class="required" required="" >
                                            By creating an account, you agree to the <a href="{{ url("public/assets/raProcure/faqs/raPROCURES-TERMS-AND-CONDITIONS.pdf") }}" target="_blank">Terms of Service</a>. 
                                            For more information about RaProcure's privacy practices, see the <a href="{{ url("public/assets/raProcure/faqs/raPROCURES-PRIVACY-POLICY.pdf") }}" target="_blank">RaProcure Privacy Statement</a>.
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn-rfq btn-rfq-primary" id="submit-buyer-profile">SUBMIT</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <script>
        let tmd_designation = "", branch_country = "", branch_state = "", branch_division = "";
        @if(!empty($director_designations))
            @foreach ($director_designations as $designation_id => $designation_name)
            tmd_designation += '<option value="{{ $designation_id }}">{{ $designation_name }}</option>';
            @endforeach
        @endif

        @if(!empty($countries))
            @foreach ($countries as $country_id => $country_name)
            branch_country += '<option value="{{ $country_id }}" {{ $country_id == 101 ? "selected" : "" }}>{{ $country_name }}</option>';
            @endforeach
        @endif

        @if(!empty($india_states))
            @foreach ($india_states as $id => $name)
            branch_state += '<option value="{{ $id }}">{{ $name }}</option>';
            @endforeach
        @endif
        
        @if(!empty($divisions))
            @foreach ($divisions as $id => $division_name)
            branch_division += '<option value="{{ $id }}">{{ $division_name }}</option>';
            @endforeach
        @endif

        let checkUniqueGstNumber = function(_this){
            let buyer_gst_number = $(_this).val();
            $("#submit-buyer-profile").attr("disabled", "disabled");
            $.ajax({
                url: '{{ route('buyer.validate-buyer-gstin-vat') }}',
                type: "POST",
                dataType: "json",
                data: {
                    buyer_gst_number: buyer_gst_number,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status==false) {
                        toastr.error(response.message);
                        $(_this).val('');
                        setTimeout(function(){
                            $("#submit-buyer-profile").removeAttr("disabled");
                        }, 1000);
                    }else{
                        $("#submit-buyer-profile").removeAttr("disabled");
                    }
                }
            });
        }
        let validateShortCode = function() {
            let _this = $('.organisation-short-code');
            let short_code = _this.val();
            let is_valid_short_code = false;

            if(short_code==''){
                return false;
            }
            if(short_code.length!=4){
                appendError('.organisation-short-code', "Short Code should have 4 Character only.");
                return false;
            }

            appendError('.organisation-short-code');
            
            $.ajax({
                async: false,
                type: "POST",
                url: '{{ route('buyer.validate-buyer-short-code') }}',
                dataType: 'json',
                data: {
                    short_code: short_code,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {},
                success: function(responce) {
                    if (responce.status == false) {
                        toastr.error(responce.message);
                        appendError('.organisation-short-code', responce.message);
                        is_valid_short_code = false;
                    } else {    
                        is_valid_short_code = true;               
                    }
                },
                error: function() {
                    // toastr.error('Something Went Wrong..');
                },
                complete: function() {}
            });
            return is_valid_short_code;
        }
    </script>
    
    <script src="{{ asset('public/assets/js/profile-validation.js') }}"></script>
    <script src="{{ asset('public/assets/buyer/js/buyer-profile-script.js') }}"></script>
    <script src="{{ asset('public/assets/library/sumoselect-v3.4.9-2/js/jquery.sumoselect.min.js') }}"></script>

    <script>

        $('.division-sumo-select').SumoSelect({selectAll: true,nativeOnDevice: [],maxHeight:100,  csvDispCount: 7, placeholder: 'Select Division' });

        $('#buyer-profile-form').on('submit', function(e) {
            e.preventDefault();
            $("#submit-buyer-profile").attr("disabled", "disabled");
            if(!validateBuyerProfile()){
                toastr.error("Please fill all the manadatory fields");
                $("#submit-buyer-profile").removeAttr("disabled");
                return false;
            }

            let formData = new FormData(document.getElementById("buyer-profile-form"));

            $.ajax({
                url: $(this).attr("action"),
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    $("#submit-buyer-profile").html('<i class="bi spinner-border"></i> Submitting...').attr("disabled", "disabled");
                },
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        window.location.href = response.redirectUrl;
                    } else {
                        if (response.errors) {
                            let errorMessage = '';
                            for (let field in response.errors) {
                                if (response.errors.hasOwnProperty(field)) {
                                    errorMessage += `${response.errors[field].join(', ')}\n`;
                                }
                            }
                            if(errorMessage!=''){
                                toastr.error(errorMessage);
                            }
                        }else{
                            toastr.error(response.message);
                            console.log(response.complete_message);
                        }
                    }
                    $("#submit-buyer-profile").html('Submit').removeAttr("disabled");
                },
                error: function(xhr) {
                    // Handle network errors or server errors
                    toastr.error("Something went wrong...");
                    setTimeout(function(){
                        $("#submit-buyer-profile").html('Submit').removeAttr("disabled");
                    }, 3000);
                    console.log("Error: ", e);
                    console.log(xhr.responseJSON?.message || 'An error occurred. Please try again.');
                    
                    // alert(xhr.responseJSON?.message || 'An error occurred. Please try again.');
                }
            });
        });

        function getState(country, state, city='') {
            let country_id = $("." + country).val();
            $.ajax({
                method: "POST",
                dataType: "json",
                url: "{{ route('buyer.get-state-by-country-id') }}",
                data: {
                    country_id: country_id,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if(response.status){
                        $("." + state).html('');
                        $("." + state).html('<option value="">Select State</option>'+response.state_list);
                        if(city!=''){
                            $("."+ city).html('<option value="">Select City</option>');
                        }
                    }else{
                        $("." + country).val('');
                        toastr.error(response.message);
                    }
                }
            });
        }

        // function getCity(state, city) {
        //     let state_id = $("." + state).val();    
        //     $.ajax({
        //         method: "POST",
        //         dataType: "json",
        //         url: "{{ route('buyer.get-city-by-state-id') }}",
        //         data: {
        //             state_id: state_id,
        //             _token: $('meta[name="csrf-token"]').attr('content')
        //         },
        //         success: function(response) {
        //             if(response.status){
        //                 $("." + city).html(response.city_list);
        //             }else{
        //                 $("." + state).val('');
        //                 toastr.error(response.message);
        //             }
        //         }
        //     });
        // }

        
        $(document).ready(function () {
            @if(Auth::user()->is_profile_verified==1)            
            function makeDisableProfileFormOnLoad() {
                $('select').prop('disabled', true);
                $('button').prop('disabled', true);
                $("input").prop("disabled", true);
                $('textarea').prop('disabled', true);
                $(".division-sumo-select").css('height', '0');
            }
            function makeDisableProfileForm() {
                let edit_btn = $("#make-editable-profile");
                if (edit_btn.text() == 'Edit') {
                    $('select').prop('disabled', false);
                    $('button').prop('disabled', false);
                    $("input").prop("disabled", false);
                    $('textarea').prop('disabled', false);
                    edit_btn.text('Cancel');
                    edit_btn.removeClass("btn-rfq-secondary");
                    edit_btn.addClass("btn-rfq-danger");
                    $(".division-sumo-select").css('height', 'auto');
                } else if (edit_btn.text() == 'Cancel') {
                    $('select').prop('disabled', true);
                    $('button').prop('disabled', true);
                    $("input").prop("disabled", true);
                    $('textarea').prop('disabled', true);
                    edit_btn.text('Edit');
                    edit_btn.addClass("btn-rfq-secondary");
                    edit_btn.removeClass("btn-rfq-danger");
                    $(".division-sumo-select").css('height', '0');
                }
                // $('#GlobalSearchInput').prop("disabled", false);
                $('.non-disabled').prop('disabled', false);
            }

            makeDisableProfileFormOnLoad();
            $(document).on("click", "#make-editable-profile", function(){
                makeDisableProfileForm();
            });
            @endif
        });
    </script>

@endsection