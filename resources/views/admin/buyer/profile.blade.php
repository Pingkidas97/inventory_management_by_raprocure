@extends('admin.layouts.app_second')
@section('breadcrumb')
<style type="text/css">
    /* Form group styling */
    .form-group.col-md-6.mb-3 {
        margin-bottom: 1rem !important;
    }
    /* Text label styling */
    .form-group .text-dark {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #212529;
    }
    /* File browse container */
    .file-browse {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    /* Browse button styling */
    .button.button-browse {
        position: relative;
        display: inline-block;
        padding: 0.375rem 0.75rem;
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        color: #495057;
        font-size: 1rem;
        line-height: 1.5;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
        margin-right: 5px;
    }
    .button.button-browse:hover {
        background-color: #e2e6ea;
        border-color: #dae0e5;
    }
    /* Hide the actual file input */
    .button.button-browse input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    /* Readonly input field */
    .file-browse .form-control {
        flex: 1;
        background-color: #fff;
    }
    /* File link styling */
    .file-links {
        display: inline-flex;
        align-items: center;
        color: #0d6efd;
        text-decoration: none;
        margin-top: 0.25rem;
    }
    .file-links:hover {
        text-decoration: underline;
    }
    /* Info icon styling */
    .bi.bi-info-circle-fill {
        margin-left: 0.25rem;
        color: #6c757d;
        font-size: 0.875rem;
    }
    /* Responsive adjustments */
    @media (max-width: 767.98px) {
        .form-group.col-md-6.mb-3 {
            width: 100%;
        }
    }
    #verify-buyer-profile .spinner-border {
        height: 14px;
        width: 14px;
    }
</style>
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.buyer.index') }}"> -> Buyer Module </a>
        </h5>
    </div>
</div>
@endsection

@section('content')
<link href="{{ asset('public/assets/library/sumoselect-v3.4.9-2/css/sumoselect.min.css') }}" rel="stylesheet">
<link href="{{ asset('public/assets/library/sumoselect-v3.4.9-2/css/sumo-select-style.css') }}" rel="stylesheet">
@php
    $buyer = $buyer_data->buyer;
    $branchDetails = $buyer_data->branchDetails;
    $topManagamantDetails = $buyer_data->topManagamantDetails;
    $latestPlan = $buyer_data->latestPlan;
    $current_plan_id = 0;
    if(!empty($latestPlan)){
        $current_plan_id = $latestPlan->plan_id;
    }
@endphp
<div class="container-fluid py-4">
  <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0" style="margin-top: 20px;">
                <div class="card-header bg-white border-0 py-3">
                    <h4 class="card-title mb-0">My Profile</h4>
                </div>
                <div class="card-body">
                    <form id="buyer-profile-form" action="{{ route('buyer.save-buyer-profile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Profile Title -->
                        <div class="row mb-4">
                            <div class="col-12 text-center">
                                <h2 class="profile-type-title">PROFILE FORM - FOR STEEL MANUFACTURING PLANTS</h2>
                            </div>
                        </div>
                        
                        <!-- Organization Details Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h3 class="mb-3">Organization Details</h3>
                            </div>
                            
                            <!-- Company Name -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold required">Company Name / Legal Name</label>
                                <input type="text" class="form-control text-upper-case" 
                                    value="{{ $buyer->legal_name }}"
                                    placeholder="Enter Company Name / Legal Name" 
                                    name="legal_name" id="legal_name"
                                    oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')" 
                                    maxlength="255" disabled>
                            </div>
                            
                            <!-- Date of Incorporation -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold required">Date of Incorporation (DD/MM/YYYY)</label>
                                <input type="text" placeholder="Date format is DD/MM/YYYY" 
                                    class="form-control date-masking" 
                                    id="incorporation_date" name="incorporation_date"
                                    value="{{ !empty($buyer->incorporation_date) ? date('d/m/Y', strtotime($buyer->incorporation_date)) : '' }}" 
                                    maxlength="10" disabled>
                            </div>
                            
                            <!-- Logo Upload -->
                            <div class="col-md-12 mb-3">
                                <input type="hidden" name="logo_old" value="{{ $buyer->logo }}">
                                <label class="form-label">Logo (File Type: JPG,JPEG,PNG)</label>
                                <div class="input-group">
                                    <input type="file" class="form-control" name="logo" 
                                        onchange="validateFile(this, 'JPG/JPEG/PNG')" disabled>
                                    {{-- @if (is_file(public_path('uploads/buyer-profile/'.$buyer->logo)))
                                        <a class="input-group-text file-links" 
                                            href="{{ url('public/uploads/buyer-profile/'.$buyer->logo) }}" 
                                            target="_blank" download="{{ $buyer->logo }}">
                                            View Uploaded File
                                        </a>
                                    @endif --}}
                                </div>
                                <span>
                                    @if (is_file(public_path('uploads/buyer-profile/'.$buyer->logo)))
                                        <a class="file-links" href="{{ url('public/uploads/buyer-profile/'.$buyer->logo) }}" target="_blank" download="{{ $buyer->logo }}">
                                            <span>{!! strlen($buyer->logo)>30 ? substr($buyer->logo, 0, 25).'<i class="bi bi-info-circle-fill" title="'.$buyer->logo.'" ></i>' : $buyer->logo !!} </span>
                                        </a>
                                    @endif
                                </span>
                            </div>
                            
                            <!-- Registered Address -->
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold required">Registered Address</label>
                                <input type="text" class="form-control" 
                                    id="registered_address" name="registered_address" 
                                    value="{{ $buyer->registered_address }}" 
                                    maxlength="1700" placeholder="Enter Registered Address" disabled>
                            </div>
                            
                            <!-- Country, State, City -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold required">Country</label>
                                <select class="form-select organization-country" 
                                    name="country" disabled>
                                    @php
                                        if(empty($buyer->country)){
                                            $buyer->country = 101;
                                        }
                                    @endphp
                                    @if(!empty($countries))
                                        @foreach ($countries as $country_id => $country_name)
                                        <option value="{{ $country_id }}" 
                                            {{ $buyer->country == $country_id ? 'selected' : '' }}>
                                            {{ $country_name }}
                                        </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold required">State</label>
                                <select class="form-select organisation-state" name="state" disabled>
                                    <option value="">Select State</option>
                                    {!! getStateByCountryId($buyer->country, $buyer->state??0) !!}
                                </select>
                            </div>
                            
                            {{-- <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold required">City</label>
                                <select class="form-select organisation-city" name="city" disabled>
                                    <option value="">Select City</option>
                                    {!! !empty($buyer->state) ? getCityByStateId($buyer->state, $buyer->city??0) : '' !!}
                                </select>
                            </div> --}}
                            
                            <!-- Pincode -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold required">Pincode</label>
                                <input type="text" class="form-control organisation-pincode" 
                                    name="pincode" value="{{ $buyer->pincode }}" 
                                    placeholder="Enter Pin Code" disabled>
                            </div>
                            
                            {{-- <div class="col-md-6 mb-3">
                            </div> --}}
                            <!-- GST/VAT -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold required">
                                    @if($buyer->country == 101)
                                        GSTIN/VAT
                                    @else
                                        Tax Identification Number
                                    @endif
                                </label>
                                <input type="text" class="form-control gstin-vat" 
                                    name="gstin" value="{{ $buyer->gstin }}" 
                                    maxlength="15" 
                                    placeholder="{{ $buyer->country == 101 ? 'Enter GSTIN/VAT' : 'Enter your Tax Identification Number' }}" 
                                    disabled>
                            </div>
                            
                            
                            <!-- PAN/TIN -->
                            <div class="col-md-6 mb-3">
                                <div class="row">
                                    <div class="col-md-6 {{ $buyer->country != 101 ? 'd-none' : '' }}">
                                        <label class="form-label fw-bold required">PAN/TIN</label>
                                        <input type="text" class="form-control organisation-pan-number" 
                                            name="pan" placeholder="Enter PAN/TIN"
                                            maxlength="10" value="{{ $buyer->pan }}" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">PAN/TIN File (JPG, JPEG, PDF)</label>
                                        <div class="input-group">
                                            <input type="file" class="form-control" name="pan_file" disabled>
                                            {{-- @if (is_file(public_path('uploads/buyer-profile/'.$buyer->pan_file)))
                                                <a class="input-group-text file-links" 
                                                    href="{{ url('public/uploads/buyer-profile/'.$buyer->pan_file) }}" 
                                                    target="_blank" download="{{ $buyer->pan_file }}">
                                                    View Uploaded File
                                                </a>
                                            @endif --}}
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
                            
                            <!-- Website -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Website</label>
                                <input type="text" class="form-control website-url" 
                                    name="website" value="{{ $buyer->website }}" 
                                    maxlength="255" placeholder="Enter Website" disabled>
                            </div>
                            
                            <!-- Product Details -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold required">Output / Product Details</label>
                                <input type="text" class="form-control product-details" 
                                    maxlength="1700" name="product_details" 
                                    value="{{ $buyer->product_details }}" 
                                    placeholder="Enter Output / Product Details" disabled>
                            </div>
                        </div>
                        
                        <div class="hr_line"></div>
                        
                        <!-- Top Management Details Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h3 class="mb-3">Top Management Details</h3>
                            </div>
                            
                            <div id="load-container" class="load-container">
                                @if(!empty($topManagamantDetails) && count($topManagamantDetails)>0)
                                    @foreach ($topManagamantDetails as $k => $tmd)
                                        <div class="row tmd-row mb-4">
                                            <div class="col-12">
                                                <h4 class="frm_head">
                                                    <span class="tmd-serial-no">{{ $k+1 }}</span>. 
                                                    Top Management Details Information
                                                </h4>
                                            </div>
                                            
                                            <!-- Name -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold required">Name</label>
                                                <input type="text" class="form-control text-upper-case" 
                                                    placeholder="Enter Name"
                                                    name="tdm_name[]" value="{{ $tmd->name }}" 
                                                    maxlength="255" 
                                                    oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')" 
                                                    disabled>
                                                <input type="hidden" name="edit_id_tmd[]" value="{{ $tmd->branch_id }}">
                                            </div>
                                            
                                            <!-- Designation -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold required">Designation</label>
                                                <select class="form-select" name="tdm_top_management_designation[]" disabled>
                                                    @if(!empty($director_designations))
                                                        @foreach ($director_designations as $designation_id => $designation_name)
                                                        <option value="{{ $designation_id }}" 
                                                            {{ $tmd->top_management_designation == $designation_id ? 'selected' : '' }}>
                                                            {{ $designation_name }}
                                                        </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            
                                            <!-- Mobile -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold required">Mobile</label>
                                                <input type="text" class="form-control my-mobile-number" 
                                                    name="tdm_mobile[]" value="{{ $tmd->mobile }}"
                                                    placeholder="Enter Mobile" disabled>
                                            </div>
                                            
                                            <!-- Email -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold required">Email</label>
                                                <input type="email" class="form-control valid-email" 
                                                    name="tdm_email[]" value="{{ $tmd->email }}" 
                                                    placeholder="Enter Email" disabled>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="row tmd-row mb-4">
                                        <div class="col-12">
                                            <h4 class="frm_head">
                                                <span class="tmd-serial-no">1</span>. 
                                                Top Management Details Information
                                            </h4>
                                        </div>
                                        
                                        <!-- Name -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">Name</label>
                                            <input type="text" class="form-control text-upper-case" 
                                                placeholder="Enter Name"
                                                name="tdm_name[]" value="" 
                                                maxlength="255" disabled>
                                            <input type="hidden" name="edit_id_tmd[]" value="0">
                                        </div>
                                        
                                        <!-- Designation -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">Designation</label>
                                            <select class="form-select" name="tdm_top_management_designation[]" disabled>
                                                @if(!empty($director_designations))
                                                    @foreach ($director_designations as $designation_id => $designation_name)
                                                    <option value="{{ $designation_id }}">
                                                        {{ $designation_name }}
                                                    </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        
                                        <!-- Mobile -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">Mobile</label>
                                            <input type="text" class="form-control my-mobile-number" 
                                                name="tdm_mobile[]" value="" 
                                                placeholder="Enter Mobile" disabled>
                                        </div>
                                        
                                        <!-- Email -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">Email</label>
                                            <input type="email" class="form-control valid-email" 
                                                name="tdm_email[]" value="" 
                                                placeholder="Enter Email" disabled>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="hr_line"></div>
                        
                        <!-- Branch/Unit Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h3 class="mb-3">Branch/Unit Name</h3>
                            </div>
                            
                            <div id="branch_container">
                                @if(!empty($branchDetails) && count($branchDetails)>0)
                                    @foreach ($branchDetails as $k => $branch)
                                        <div class="row branch-row mb-4" data-row-id="{{ $k }}">
                                            <div class="col-12">
                                                <h4 class="frm_head">BRANCH/UNIT <span class="branch-serial-no">{{ $k+1 }}</span></h4>
                                            </div>
                                            
                                            <!-- Branch Name -->
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label fw-bold required">Branch/Unit Name</label>
                                                <input type="text" class="form-control text-upper-case" 
                                                    name="branch_name[]" value="{{ $branch->name }}" 
                                                    placeholder="Enter Branch/Unit Name" maxlength="255" disabled>
                                                <input type="hidden" name="edit_id_branch[]" value="{{ $branch->branch_id }}">
                                            </div>
                                            
                                            <!-- Address -->
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label fw-bold required">Address</label>
                                                <input type="text" class="form-control" maxlength="1700"
                                                    name="branch_address[]" value="{{ $branch->address }}" 
                                                    placeholder="Enter Address" disabled>
                                            </div>
                                            
                                            <!-- Country, State, City -->
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label fw-bold required">Country</label>
                                                <select class="form-select branch-country" 
                                                    name="branch_country[]" disabled>
                                                    @if(!empty($countries))
                                                        @foreach ($countries as $country_id => $country_name)
                                                        <option value="{{ $country_id }}" 
                                                            {{ $branch->country == $country_id ? 'selected' : '' }}>
                                                            {{ $country_name }}
                                                        </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label fw-bold required">State</label>
                                                <select class="form-select branch-state" 
                                                    name="branch_state[]" disabled>
                                                    <option value="">Select State</option>
                                                    @php
                                                        $b_country = !empty($branch->country) ? $branch->country : 101;
                                                    @endphp
                                                    {!! getStateByCountryId($b_country, $branch->state??0) !!}
                                                </select>
                                            </div>
                                            
                                            {{-- <div class="col-md-4 mb-3">
                                                <label class="form-label fw-bold required">City</label>
                                                <select class="form-select branch-city" 
                                                    name="branch_city[]" disabled>
                                                    <option value="">Select City</option>
                                                    {!! !empty($branch->state) ? getCityByStateId($branch->state, $branch->city??0) : '' !!}
                                                </select>
                                            </div> --}}
                                            
                                            <!-- Pincode -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold required">Pincode</label>
                                                <input type="text" class="form-control branch-pincode" 
                                                    name="branch_pincode[]" value="{{ $branch->pincode }}"
                                                    placeholder="Enter Pin Code" disabled>
                                            </div>
                                            
                                            {{-- <div class="col-md-6 mb-3">
                                            </div> --}}
                                            
                                            <!-- GST/VAT -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold required">
                                                    @if($branch->country == 101)
                                                        GSTIN/VAT
                                                    @else
                                                        Tax Identification Number
                                                    @endif
                                                </label>
                                                <input type="text" class="form-control branch-gstin-vat" 
                                                    name="branch_gstin[]" value="{{ $branch->gstin }}" 
                                                    maxlength="15" 
                                                    placeholder="{{ $branch->country == 101 ? 'Enter GSTIN/VAT' : 'Enter your Tax Identification Number' }}" 
                                                    disabled>
                                            </div>
                                            
                                            <!-- GST/VAT File -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">GSTIN/VAT File (JPG, JPEG, PDF)</label>
                                                <div class="input-group">
                                                    <input type="file" class="form-control" name="branch_gstin_file[]" disabled>
                                                    {{-- @if (is_file(public_path('uploads/buyer-profile/'.$branch->gstin_file)))
                                                        <a class="input-group-text file-links" 
                                                            href="{{ url('public/uploads/buyer-profile/'.$branch->gstin_file) }}" 
                                                            target="_blank" download="{{ $branch->gstin_file }}">
                                                            View Uploaded File
                                                        </a>
                                                    @endif --}}
                                                </div>
                                                <span>
                                                        @if (is_file(public_path('uploads/buyer-profile/'.$branch->gstin_file)))
                                                            <a class="file-links" href="{{ url('public/uploads/buyer-profile/'.$branch->gstin_file) }}" target="_blank" download="{{ $branch->gstin_file }}">
                                                                <span>{!! strlen($branch->gstin_file)>30 ? substr($branch->gstin_file, 0, 25).'<i class="bi bi-info-circle-fill" title="'.$branch->gstin_file.'" ></i>' : $branch->gstin_file !!} </span>
                                                            </a>
                                                        @endif
                                                    </span>
                                            </div>
                                            
                                            <!-- Authorized Person -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold required">Name of Authorized Person</label>
                                                <input type="text" class="form-control" 
                                                    name="branch_authorized_name[]" value="{{ $branch->authorized_name }}" 
                                                    placeholder="Enter Name of Authorized Person" disabled>
                                            </div>
                                            
                                            <!-- Authorized Designation -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold required">Designation of Authorized Person</label>
                                                <input type="text" class="form-control" 
                                                    name="branch_authorized_designation[]" value="{{ $branch->authorized_designation }}" 
                                                    placeholder="Enter Designation of Authorized Person" disabled>
                                            </div>
                                            
                                            <!-- Mobile -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold required">Mobile</label>
                                                <input type="text" class="form-control my-mobile-number" 
                                                    name="branch_mobile[]" value="{{ $branch->mobile }}"
                                                    placeholder="Enter Mobile" disabled>
                                            </div>
                                            
                                            <!-- Email -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold required">Email</label>
                                                <input type="email" class="form-control valid-email" 
                                                    name="branch_email[]" value="{{ $branch->email }}" 
                                                    placeholder="Enter Email" disabled>
                                            </div>
                                            
                                            <!-- Products Output -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Products Output/Products Manufactured</label>
                                                <input type="text" class="form-control" 
                                                    name="branch_output_details[]" value="{{ $branch->output_details }}" 
                                                    maxlength="1700" 
                                                    placeholder="Enter Products Output/Products Manufactured" disabled>
                                            </div>
                                            
                                            <!-- Annual Capacity -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Annual Capacity in Tonnage</label>
                                                <input type="text" class="form-control" 
                                                    name="branch_installed_capacity[]" value="{{ $branch->installed_capacity }}" 
                                                    placeholder="Enter Annual Capacity in Tonnage" disabled>
                                            </div>
                                            
                                            <!-- Division -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold required">Division</label>
                                                <select class="form-select division-sumo-select" 
                                                    name="branch_categories[{{ $k }}][]" multiple disabled>
                                                    @php
                                                        $branch_category = !empty($branch->categories) ? explode(",", $branch->categories) : array();
                                                    @endphp
                                                    @if(!empty($divisions))
                                                        @foreach ($divisions as $id => $division_name)
                                                        <option value="{{ $id }}" 
                                                            {{ in_array($id, $branch_category) ? 'selected' : '' }}>
                                                            {{ $division_name }}
                                                        </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            
                                            <!-- Status -->
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold required">Status</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input branch-status" 
                                                        type="checkbox" role="switch" 
                                                        value="1" {{ $branch->status == 1 ? "checked" : "" }} 
                                                        disabled>
                                                    <input type="hidden" name="branch_status[]" 
                                                        value="{{ $branch->status }}" disabled>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="row branch-row mb-4">
                                        <div class="col-12">
                                            <h4 class="frm_head">BRANCH/UNIT <span class="branch-serial-no">1</span></h4>
                                        </div>
                                        
                                        <!-- Branch Name -->
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label fw-bold required">Branch/Unit Name</label>
                                            <input type="text" class="form-control text-upper-case" 
                                                name="branch_name[]" value="" 
                                                placeholder="Enter Branch/Unit Name" maxlength="255" disabled>
                                            <input type="hidden" name="edit_id_branch[]" value="0">
                                        </div>
                                        
                                        <!-- Address -->
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label fw-bold required">Address</label>
                                            <input type="text" class="form-control" maxlength="1700"
                                                name="branch_address[]" value="" 
                                                placeholder="Enter Address" disabled>
                                        </div>
                                        
                                        <!-- Country, State, City -->
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-bold required">Country</label>
                                            <select class="form-select branch-country" 
                                                name="branch_country[]" disabled>
                                                @if(!empty($countries))
                                                    @foreach ($countries as $country_id => $country_name)
                                                    <option value="{{ $country_id }}" 
                                                        {{ $country_id == 101 ? 'selected' : '' }}>
                                                        {{ $country_name }}
                                                    </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-bold required">State</label>
                                            <select class="form-select branch-state" 
                                                name="branch_state[]" disabled>
                                                <option value="">Select State</option>
                                                @if(!empty($india_states))
                                                    @foreach ($india_states as $id => $name)
                                                    <option value="{{ $id }}">{{ $name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        
                                        {{-- <div class="col-md-4 mb-3">
                                            <label class="form-label fw-bold required">City</label>
                                            <select class="form-select branch-city" 
                                                name="branch_city[]" disabled>
                                                <option value="">Select City</option>
                                            </select>
                                        </div> --}}
                                        
                                        <!-- Pincode -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">Pincode</label>
                                            <input type="text" class="form-control branch-pincode" 
                                                name="branch_pincode[]" value=""
                                                placeholder="Enter Pin Code" disabled>
                                        </div>
                                        
                                        <!-- GST/VAT -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">
                                                @if($buyer->country == 101)
                                                    GSTIN/VAT
                                                @else
                                                    Tax Identification Number
                                                @endif
                                            </label>
                                            <input type="text" class="form-control branch-gstin-vat" 
                                                name="branch_gstin[]" value="" 
                                                maxlength="15" 
                                                placeholder="{{ $buyer->country == 101 ? 'Enter GSTIN/VAT' : 'Enter your Tax Identification Number' }}" 
                                                disabled>
                                        </div>
                                        
                                        <!-- GST/VAT File -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">GSTIN/VAT File (JPG, JPEG, PDF)</label>
                                            <input type="file" class="form-control" 
                                                name="branch_gstin_file[]" disabled>
                                        </div>
                                        
                                        <!-- Authorized Person -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">Name of Authorized Person</label>
                                            <input type="text" class="form-control" 
                                                name="branch_authorized_name[]" value="" 
                                                placeholder="Enter Name of Authorized Person" disabled>
                                        </div>
                                        
                                        <!-- Authorized Designation -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">Designation of Authorized Person</label>
                                            <input type="text" class="form-control" 
                                                name="branch_authorized_designation[]" value="" 
                                                placeholder="Enter Designation of Authorized Person" disabled>
                                        </div>
                                        
                                        <!-- Mobile -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">Mobile</label>
                                            <input type="text" class="form-control my-mobile-number" 
                                                name="branch_mobile[]" value="" 
                                                placeholder="Enter Mobile" disabled>
                                        </div>
                                        
                                        <!-- Email -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">Email</label>
                                            <input type="email" class="form-control valid-email" 
                                                name="branch_email[]" value="" 
                                                placeholder="Enter Email" disabled>
                                        </div>
                                        
                                        <!-- Products Output -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Products Output/Products Manufactured</label>
                                            <input type="text" class="form-control" 
                                                name="branch_output_details[]" value="" 
                                                maxlength="1700" 
                                                placeholder="Enter Products Output/Products Manufactured" disabled>
                                        </div>
                                        
                                        <!-- Annual Capacity -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Annual Capacity in Tonnage</label>
                                            <input type="text" class="form-control" 
                                                name="branch_installed_capacity[]" value="" 
                                                placeholder="Enter Annual Capacity in Tonnage" disabled>
                                        </div>
                                        
                                        <!-- Division -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">Division</label>
                                            <select class="form-select division-sumo-select" 
                                                name="branch_categories[0][]" multiple disabled>
                                                @if(!empty($divisions))
                                                    @foreach ($divisions as $id => $division_name)
                                                    <option value="{{ $id }}">{{ $division_name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        
                                        <!-- Status -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold required">Status</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input branch-status" 
                                                    type="checkbox" role="switch" 
                                                    value="1" checked disabled>
                                                <input type="hidden" name="branch_status[]" 
                                                    value="1" disabled>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="hr_line"></div>
                        
                        <!-- Other Information Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h3 class="mb-3">Other Information</h3>
                            </div>
                            
                            <!-- Organization Description -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold required">Organization Description (Maximum 500 Words)</label>
                                <textarea class="form-control" name="organisation_description" 
                                    id="organisation-description" 
                                    placeholder="Enter Organization Description"
                                    rows="3" disabled>{{ $buyer->organisation_description }}</textarea>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold required">Select Plan for Subscription: (1 month Free period) <span class="text-danger">*</span></label>
                                <select class="form-select" id="buyer_plan" {{ !empty($buyer->buyer_code) ? "disabled" : "" }}>
                                    @if(!empty($buyer_plan))
                                        @foreach ($buyer_plan as $key => $value)
                                        <option value="{{ $value->id }}" {{ $current_plan_id == $value->id ? 'selected' : '' }}>
                                            {{ $value->plan_name.": Rs. ".$value->price." per year for ".$value->no_of_user." users" }}
                                        </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <!-- Newsletter Subscription -->
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input subscribe-news-letter" 
                                        type="checkbox" disabled
                                        {{ $subscribed == true ? 'checked' : '' }}>
                                    <label class="form-check-label">
                                        Subscribe for our offer news
                                    </label>
                                    <input type="hidden" name="subscribe_news_letter" 
                                        class="subscribe-news-letter-hidden" 
                                        value="{{ $subscribed == true ? '1' : '2' }}">
                                </div>
                            </div>
                            
                            <!-- Short Code -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold required">
                                    Short Code (Will be used as a prefix to your RFQ Number. It should be of 4 alphabets only)
                                </label>
                                <input type="text" class="form-control organisation-short-code text-upper-case" 
                                    name="organisation_short_code" maxlength="4" minlength="4"
                                    value="{{ $buyer->organisation_short_code }}" 
                                    placeholder="Enter Short Code" 
                                    oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')" 
                                    disabled>
                            </div>
                            
                            <!-- Notes -->
                            <div class="col-md-12 mb-3 note-txt">
                                <p><strong>*Note:</strong></p>
                                <ol>
                                    <li>Once your profile is verified by raProcure, you will be eligible to use all the services of the portal.</li>
                                    <li>Plus GST on the Subscription charges mentioned above.</li>
                                </ol>
                            </div>
                            
                            <!-- Terms and Conditions -->
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                        name="buyer_accept_tnc" value="1" disabled
                                        {{ $buyer->buyer_accept_tnc == 1 ? "checked" : "checked" }} 
                                        required>
                                    <label class="form-check-label">
                                        By creating an account, you agree to the 
                                        <a href="{{ url('public/assets/raProcure/faqs/raPROCURES-TERMS-AND-CONDITIONS.pdf') }}" target="_blank">Terms of Service</a>. 
                                        For more information about RaProcure's privacy practices, see the 
                                        <a href="{{ url('public/assets/raProcure/faqs/raPROCURES-PRIVACY-POLICY.pdf') }}" target="_blank">RaProcure Privacy Statement</a>.
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-rfq-primary" id="verify-buyer-profile">
                                    {{ !empty($buyer->buyer_code) ? "Verified" : "Verify" }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
  </div>
</div>
@endsection
@section('scripts')
    <script src="{{ asset('public/assets/library/sumoselect-v3.4.9-2/js/jquery.sumoselect.min.js') }}"></script>
    <script>
        $('.division-sumo-select').SumoSelect({selectAll: true, csvDispCount: 7, placeholder: 'Select Division' });
 
        $(document).on('click', '#verify-buyer-profile', function(){
            verifyBuyerProfile();
        });
        function verifyBuyerProfile(){
            $("#verify-buyer-profile").prop('disabled', true).html('<i class="bi spinner-border"></i> Verifying...');
            $.ajax({
                type: "POST",
                url: '{{ route('admin.buyer.profile.update') }}',
                dataType: 'json',
                data: {
                    plan_id: $("#buyer_plan").val(),
                    user_id: "{{ $buyer->user_id }}",
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function() {},
                success: function(responce) {
                    if (responce.status == false) {
                        toastr.error(responce.message);
                        $("#verify-buyer-profile").prop('disabled', false).html('Verify');
                    } else {
                        toastr.success(responce.message);
                        $("#verify-buyer-profile").prop('disabled', false).html('Verified');
                        setTimeout(() => {
                            window.location.href = "{{ route('admin.buyer.index') }}";
                        }, 1500);
                    }
                },
                error: function() {
                    // toastr.error('Something Went Wrong..');
                },
                complete: function() {}
            });
        }
    </script>
@endsection