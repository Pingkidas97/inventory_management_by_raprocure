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
    
    #verify-vendor-profile .spinner-border {
        height: 14px;
        width: 14px;
    }
</style>
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.vendor.index') }}"> -> Vendor Module </a>
            <a href="javascript:void(0);"> -> <b>{{ $vendor_data->vendor->legal_name }}</b> Vendor Profile </a>
        </h5>
    </div>
</div>
@endsection
@section('content')
@php
    $vendor = $vendor_data->vendor;
    $branchDetails = $vendor_data->branchDetails;
    
    $is_profile_verified = false;
    if($vendor_data->is_profile_verified==1){
        $is_profile_verified = true;
    }

    $latestPlan = $vendor_data->latestPlan;
    $current_plan_id = 0;
    if(!empty($latestPlan)){
        $current_plan_id = $latestPlan->plan_id;
    }
@endphp
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card my_profile form-head border-0" style="margin-top: 20px;">
                <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                    <h4 class="card-title mb-0"> <b style="font-size: 20px">{{ $vendor_data->vendor->legal_name }}</b> Vendor Profile</h4>
                </div>
                <div class="card-body">
                    <div class="tab-content-inner">
                        <form id="vendor-profile-form" action="{{ route('vendor.save-vendor-profile') }}" method="POST">
                            @csrf
                            <!-- Profile Type Section -->
                            {{-- <div class="row mb-4">
                                <h2 class="col-12 text-center profile-type-title">For Vendors who are supplying to Steel Plants</h2>
                            </div> --}}
                            
                            <!-- 1. Company Details Section -->
                            <div class="row">
                                <h3 class="mb-4 col-12">1. Company Details</h3>
                            </div>
                            
                            <div class="row g-3">
                                <!-- Company Name -->
                                <div class="col-md-6">
                                    <label class="fw-bold form-label">Company Name / Legal Name<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control required text-upper-case" value="{{ $vendor->legal_name }}"
                                        placeholder="Enter Company Name / Legal Name" name="legal_name" id="legal_name"
                                        oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')" maxlength="255" disabled>
                                </div>
                                
                                <!-- Company Logo -->
                                <div class="col-md-6">
                                    <input type="hidden" name="profile_img_old" value="{{ $vendor->profile_img }}">
                                    <label class="fw-bold form-label">Company Logo<span class="text-danger">*</span></label>
                                    <div class="file-browse">
                                        <span class="button button-browse">
                                            Select <input type="file" class="profile_img" name="profile_img" onchange="validateFile(this, 'JPG/JPEG/PNG')">
                                        </span>
                                        <input type="text" class="form-control" placeholder="Upload Company Logo" readonly="" >
                                    </div>
                                    @if (is_file(public_path('uploads/vendor-profile/'.$vendor->profile_img)))
                                        <div class="mt-2">
                                            <a class="file-links" href="{{ url('public/uploads/vendor-profile/'.$vendor->profile_img) }}" target="_blank" download="{{ $vendor->profile_img }}">
                                                <span>
                                                    {!! strlen($vendor->profile_img)>30 ? substr($vendor->profile_img, 0, 25).'<i class="bi bi-info-circle-fill" title="'.$vendor->profile_img.'"></i>' : $vendor->profile_img !!}
                                                </span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Date of Incorporation -->
                                <div class="col-md-3">
                                    <label class="fw-bold form-label">Date of Incorporation (DD/MM/YYYY)<span class="text-danger">*</span></label>
                                    <input type="text" placeholder="Date format is DD/MM/YYYY" onblur="validateDateFormat(this, true);"
                                        class="form-control required date-masking" id="date_of_incorporation" name="date_of_incorporation"
                                        value="{{ !empty($vendor->date_of_incorporation) ? date("d/m/Y", strtotime($vendor->date_of_incorporation)) : '' }}" maxlength="10" disabled>
                                </div>
                                
                                <!-- Nature of Organization -->
                                <div class="col-md-3">
                                    <label class="fw-bold form-label">Nature of Organization<span class="text-danger">*</span></label>
                                    <select class="form-select required" name="nature_of_organization" disabled>
                                        @if(!empty($nature_of_organization))
                                            @foreach ($nature_of_organization as $id => $name)
                                                <option value="{{ $id }}" {{ $vendor->nature_of_organization == $id ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="fw-bold form-label">Other Contact Details<span class="text-danger">*</span></label>
                                    <input type="text" placeholder="Enter Other Contact Details" oninput="this.value = this.value.replace(/[^0-9,\-\/ ]/g, '')"
                                        class="form-control required" id="other_contact_details" name="other_contact_details" disabled
                                        value="{{ $vendor->other_contact_details }}" maxlength="255">
                                        
                                </div>
                                
                                <!-- Nature of Business -->
                                {{-- <div class="col-md-6 d-none">
                                    <label class="fw-bold form-label">Nature of Business<span class="text-danger">*</span></label>
                                    <select class="form-select required" name="nature_of_business" disabled>
                                        @if(!empty($nature_of_business))
                                            @foreach ($nature_of_business as $id => $name)
                                                <option value="{{ $id }}" {{ $vendor->nature_of_business == $id ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div> --}}
                                
                                <!-- Registered Address -->
                                <div class="col-12">
                                    <label class="fw-bold form-label">Registered Address<span class="text-danger">*</span></label>
                                    <textarea class="form-control registered_address required" placeholder="Enter Registered Address"
                                        id="registered_address" maxlength="1700" name="registered_address" rows="3" disabled>{{ $vendor->registered_address }}</textarea>
                                </div>
                                
                                <!-- Country, State, City -->
                                <div class="col-md-3">
                                    <label class="fw-bold form-label">Country<span class="text-danger">*</span></label>
                                    <select class="form-select required organization-country" 
                                        onchange="getState('organization-country', 'organisation-state', 'organisation-city')" 
                                        name="country" disabled>
                                        @php
                                            if(empty($vendor->country)){
                                                $vendor->country = 101;
                                            }
                                        @endphp
                                        @if(!empty($countries))
                                            @foreach ($countries as $country_id => $country_name)
                                                <option value="{{ $country_id }}" {{ $vendor->country == $country_id ? 'selected' : '' }}>{{ $country_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="fw-bold form-label">State<span class="text-danger">*</span></label>
                                    <select class="form-select required organisation-state"
                                        onchange="getCity('organisation-state', 'organisation-city')"
                                        name="state" disabled>
                                        <option value="">Select State</option>
                                        {!! getStateByCountryId($vendor->country, $vendor->state??0) !!}
                                    </select>
                                </div>
                                
                                {{-- <div class="col-md-6">
                                    <label class="fw-bold form-label">City<span class="text-danger">*</span></label>
                                    <select class="form-select required organisation-city"
                                        name="city" disabled>
                                        <option value="">Select City</option>
                                        {!! !empty($vendor->state) ? getCityByStateId($vendor->state, $vendor->city??0) : '' !!}
                                    </select>
                                </div> --}}
                                
                                <!-- Pincode -->
                                <div class="col-md-6">
                                    <label class="fw-bold form-label">Pincode<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control organisation-pincode required" name="pincode" value="{{ $vendor->pincode }}" 
                                        minlength="6" maxlength="6" oninput="this.value=this.value.replace(/[^0-9.\&\(\)\+,\- ]/,'')" placeholder="Enter Pin Code" disabled>
                                </div>
                                
                                <!-- GSTIN/VAT -->
                                <div class="col-xl-6">
                                    <div class="row g-3">
                                        <div class="col-sm-5">
                                            <label class="fw-bold form-label">
                                                <span class="gst-field-label-name">{{ $vendor->country == 101 ? "GSTIN/VAT" : "Please enter your Tax Identification Number" }}</span>
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control gstin-vat required" name="gstin" value="{{ $vendor->gstin }}" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')"
                                                maxlength="15" placeholder="{{ $vendor->country == 101 ? "Enter GSTIN/VAT" : "Enter your Tax Identification Number" }}" disabled>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="hidden" name="gstin_document_old" value="{{ $vendor->gstin_document }}">
                                            <label class="fw-bold form-label">GSTIN/VAT Document<span class="text-danger">*</span></label>
                                            <div class="file-browse">
                                                <span class="button button-browse">
                                                    Select <input onchange="validateFile(this, 'JPG/JPEG/PDF')" type="file" class="{{ $vendor->gstin_document=='' || $vendor->gstin_document==null ? 'required-file' : '' }}" name="gstin_document" disabled>
                                                </span>
                                                <input type="text" class="form-control" placeholder="Upload GSTIN/VAT Document" readonly>
                                            </div>
                                            @if (is_file(public_path('uploads/vendor-profile/'.$vendor->gstin_document)))
                                                <div class="mt-2">
                                                    <a class="file-links" href="{{ url('public/uploads/vendor-profile/'.$vendor->gstin_document) }}" target="_blank" download="{{ $vendor->gstin_document }}">
                                                        <span>{!! strlen($vendor->gstin_document)>30 ? substr($vendor->gstin_document, 0, 25).'<i class="bi bi-info-circle-fill" title="'.$vendor->gstin_document.'"></i>' : $vendor->gstin_document !!}</span>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Website -->
                                <div class="col-md-6">
                                    <label class="fw-bold form-label">Website</label>
                                    <input type="text" class="form-control website-url" name="website" value="{{ $vendor->website }}" maxlength="255" placeholder="Enter Website URL" disabled>
                                </div>
                                
                                <!-- Customer References -->
                                <div class="col-12">
                                    <p class="mb-3">- Mention the name of 2 of your customers (must be Steel Plants)</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold form-label">Customer Name 1<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control required" maxlength="255" name="company_name1" value="{{ $vendor->company_name1 }}" placeholder="Enter Customer Name 1" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold form-label">Customer Name 2<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control required" maxlength="255" name="company_name2" value="{{ $vendor->company_name2 }}" placeholder="Enter Customer Name 2" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')" disabled>
                                </div>
                                
                                <!-- Top Products -->
                                <div class="col-12">
                                    <p class="mb-3">- Mention the name of your TOP 3 PRODUCTS (Please Note: After your profile is verified, you will be able to add ALL your products with details).</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="fw-bold form-label">Product Name<span class="text-danger">*</span></label>
                                    @php
                                        $organization_product_name = explode('@#', $vendor->registered_product_name);
                                    @endphp
                                    <input type="text" class="form-control required" maxlength="350" name="registered_product_name" oninput="removeCharacters(this, '#')" onblur="removeCharacters(this, '#')" value="{{ implode(", ", $organization_product_name)}}" placeholder="Enter Product Name" disabled>
                                </div>
                            </div>
                            
                            @if($is_profile_verified)
                            <!-- 2. Branch Details Section -->
                            <div class="hr_line"></div>
                            <div class="row mt-4 justify-content-between">
                                <h3 class="col-md-6">2. Branch Details</h3>
                            </div>
                            
                            <div id="branch_container">
                                @if(!empty($branchDetails) && count($branchDetails)>0)
                                    @foreach ($branchDetails as $k => $branch)
                                        <div class="row branch-row g-3 mb-4" data-row-id="{{ $k }}">
                                            <h4 class="frm_head">Branch Information <span class="branch-serial-no">{{ $k+1 }}</span></h4>
                                            
                                            <!-- Branch Name -->
                                            <div class="col-md-6">
                                                <label class="fw-bold form-label">Branch Name<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control required text-upper-case" name="branch_name[]" value="{{ $branch->name }}" placeholder="Enter Branch Name" maxlength="255" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')" disabled>
                                                <input type="hidden" name="edit_id_branch[]" value="{{ $branch->branch_id }}">
                                            </div>
                                            
                                            <!-- Branch GSTIN -->
                                            <div class="col-md-6">
                                                <label class="fw-bold form-label">
                                                    <span class="gst-field-label-name">GSTIN/VAT</span>
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control required branch-gstin-vat" name="branch_gstin[]" value="{{ $branch->gstin }}" placeholder="Enter GSTIN/VAT" maxlength="15" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&amp;\(\)\+,\- ]/,'')" disabled>
                                            </div>
                                            
                                            <!-- Branch Address -->
                                            <div class="col-12">
                                                <label class="fw-bold form-label">Registered Address<span class="text-danger">*</span></label>
                                                <textarea class="form-control required" name="branch_address[]" placeholder="Enter Registered Address" maxlength="1700" rows="3" disabled>{{ $branch->address }}</textarea>
                                            </div>
                                            
                                            <!-- Branch Country, State, City -->
                                            <div class="col-md-6">
                                                <label class="fw-bold form-label">Country<span class="text-danger">*</span></label>
                                                <select class="form-select branch-country disabled branch-country-{{ $branch->branch_id }} required" name="branch_country[]"
                                                    onchange="getState('branch-country-{{ $branch->branch_id }}', 'branch-state-{{ $branch->branch_id }}', 'branch-city-{{ $branch->branch_id }}')" disabled>
                                                    @if(!empty($countries))
                                                        @foreach ($countries as $country_id => $country_name)
                                                            <option value="{{ $country_id }}" {{ $branch->country == $country_id ? 'selected' : '' }}>{{ $country_name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label class="fw-bold form-label">State<span class="text-danger">*</span></label>
                                                <select class="form-select branch-state branch-state-{{ $branch->branch_id }} required"
                                                    onchange="getCity('branch-state-{{ $branch->branch_id }}', 'branch-city-{{ $branch->branch_id }}')"
                                                    name="branch_state[]" disabled>
                                                    <option value="">Select State</option>
                                                    @php
                                                        $b_country = !empty($branch->country) ? $branch->country : 101;
                                                    @endphp
                                                    {!! getStateByCountryId($b_country, $branch->state??0) !!}
                                                </select>
                                            </div>
                                            
                                            {{-- <div class="col-md-6">
                                                <label class="fw-bold form-label">City<span class="text-danger">*</span></label>
                                                <select class="form-select branch-city branch-city-{{ $branch->branch_id }} required" name="branch_city[]" disabled>
                                                    <option value="">Select City</option>
                                                    {!! !empty($branch->state) ? getCityByStateId($branch->state, $branch->city??0) : '' !!}
                                                </select>
                                            </div> --}}
                                            
                                            <!-- Branch Pincode -->
                                            <div class="col-md-6">
                                                <label class="fw-bold form-label">Pincode<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control branch-pincode required" name="branch_pincode[]" value="{{ $branch->pincode }}"
                                                    onkeypress="return validatePinCode(event, this)" placeholder="Enter Pin Code" minlength="6" maxlength="6" disabled>
                                            </div>
                                            
                                            <!-- Authorized Person -->
                                            <div class="col-md-6">
                                                <label class="fw-bold form-label">Name of Authorized Person & Designation<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control required" name="branch_authorized_designation[]" value="{{ $branch->authorized_designation }}" placeholder="Enter Name of Authorized Person & Designation" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')" disabled>
                                            </div>
                                            
                                            <!-- Contact Information -->
                                            <div class="col-md-6">
                                                <label class="fw-bold form-label">Mobile<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control validate-max-length my-mobile-number required" name="branch_mobile[]" value="{{ $branch->mobile }}"
                                                    data-maxlength="{{ $vendor->country == 101 ? 10 : 25 }}" data-minlength="{{ $vendor->country == 101 ? 10 : 1 }}" placeholder="Enter Mobile" disabled>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label class="fw-bold form-label">Email<span class="text-danger">*</span></label>
                                                <input type="email" class="form-control valid-email required" name="branch_email[]" value="{{ $branch->email }}" placeholder="Enter Email" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+\@,\- ]/,'')" disabled>
                                            </div>
                                            
                                            <!-- Status Toggle -->
                                            <div class="col-md-6">
                                                <label class="fw-bold form-label">Status<span class="text-danger">*</span></label>
                                                <div class="custom-file branch-toggle-div">
                                                    <label class="radio-inline me-3">
                                                        <label class="switch">
                                                            <input onchange="branchStatus(this)" class="branch-status required" value="1" type="checkbox" {{ $branch->status == 1 ? "checked" : "" }} disabled>
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </label>
                                                    <input class="branch-status-hidden" value="{{ $branch->status }}" type="hidden" name="branch_status[]">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    {{-- <div class="alert alert-info">No Branch Information Available</div> --}}
                                @endif
                            </div>
                            @endif
                            
                            <!-- 3. Registrations Section -->
                            <div class="hr_line"></div>
                            <div class="row mt-4 justify-content-between">
                                <h3 class="col-md-6">{{ $is_profile_verified ? 3 : 2 }}. Registrations</h3>
                            </div>
                            
                            <div class="row g-3">
                                <!-- MSME Registration -->
                                <div class="col-md-6">
                                    <label class="fw-bold form-label">1. MSME</label>
                                    <input type="text" class="form-control registration-msme" name="msme" value="{{ $vendor->msme }}" placeholder="Enter MSME" maxlength="255" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')" disabled>
                                </div>
                                <div class="col-md-6">
                                    <input type="hidden" name="msme_certificate_old" value="{{ $vendor->msme_certificate }}">
                                    <label class="fw-bold form-label">MSME Certificate</label>
                                    <div class="file-browse">
                                        <span class="button button-browse">
                                            Select <input type="file" class="msme_certificate" id="registration-msme-file" name="msme_certificate" onchange="validateFile(this, 'PDF/DOC/DOCX/JPEG/JPG/PNG');reValidateRegistrationDoc(this);" disabled>
                                        </span>
                                        <input type="text" class="form-control" placeholder="Upload MSME Certificate Document" readonly>
                                    </div>
                                    @if (is_file(public_path('uploads/vendor-profile/'.$vendor->msme_certificate)))
                                        <div class="mt-2">
                                            <a class="file-links" href="{{ url('public/uploads/vendor-profile/'.$vendor->msme_certificate) }}" target="_blank" download="{{ $vendor->msme_certificate }}">
                                                <span>{!! strlen($vendor->msme_certificate)>30 ? substr($vendor->msme_certificate, 0, 25).'<i class="bi bi-info-circle-fill" title="'.$vendor->msme_certificate.'"></i>' : $vendor->msme_certificate !!}</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                                
                                @if($is_profile_verified)
                                <!-- ISO Registration -->
                                <div class="col-md-6">
                                    <label class="fw-bold form-label">2. ISO Registration</label>
                                    <input type="text" class="form-control registration-iso" name="iso_registration" value="{{ $vendor->iso_registration }}" placeholder="Enter ISO Registration" maxlength="255" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')" disabled>
                                </div>
                                <div class="col-md-6">
                                    <input type="hidden" name="iso_regi_certificate_old" value="{{ $vendor->iso_regi_certificate }}">
                                    <label class="fw-bold form-label">ISO Certificate</label>
                                    <div class="file-browse">
                                        <span class="button button-browse">
                                            Select <input type="file" id="registration-iso-file" class="iso_regi_certificate" name="iso_regi_certificate" onchange="validateFile(this, 'PDF/DOC/DOCX/JPEG/JPG/PNG');reValidateRegistrationDoc(this);" disabled>
                                        </span>
                                        <input type="text" class="form-control" placeholder="Upload ISO Certificate Document" readonly>
                                    </div>
                                    @if (is_file(public_path('uploads/vendor-profile/'.$vendor->iso_regi_certificate)))
                                        <div class="mt-2">
                                            <a class="file-links" href="{{ url('public/uploads/vendor-profile/'.$vendor->iso_regi_certificate) }}" target="_blank" download="{{ $vendor->iso_regi_certificate }}">
                                                <span>{!! strlen($vendor->iso_regi_certificate)>30 ? substr($vendor->iso_regi_certificate, 0, 25).'<i class="bi bi-info-circle-fill" title="'.$vendor->iso_regi_certificate.'"></i>' : $vendor->iso_regi_certificate !!}</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                            
                            <!-- 4. Other Details Section -->
                            <div class="hr_line"></div>
                            <div class="row mt-4 justify-content-between">
                                <h3 class="col-md-6">{{ $is_profile_verified ? 4 : 3 }}. Other Details</h3>
                            </div>
                            
                            <div class="row g-3">
                                <!-- Organization Description -->
                                <div class="col-12">
                                    <label for="other-description" class="fw-bold form-label">Organization Description<span class="text-danger"> (Maximum 300 Words)*</span></label>
                                    <textarea class="form-control required" name="description" id="other-description" placeholder="Please enter a short description about your organization for the Buyer to view."
                                        rows="5" disabled>{{ $vendor->description }}</textarea>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold required">Number of Logins Required <span class="text-danger">*</span></label>
                                    <select class="form-select" id="vendor-plan" {{ !empty($vendor->vendor_code) ? "disabled" : "" }}>
                                        @if(!empty($vendor_plan))
                                            @foreach ($vendor_plan as $key => $value)
                                            <option value="{{ $value->id }}" {{ $current_plan_id == $value->id ? 'selected' : '' }}>
                                                {{ $value->plan_name.": Rs. ".$value->price." per year for ".$value->no_of_user." users" }}
                                            </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                
                                <!-- Note -->
                                <div class="col-12 note-txt">
                                    <p><strong>*Note:</strong><br>
                                        1. Once your profile is verified by raProcure, you will be eligible to upload your products, catalog, create mini web page. You will be asked to pay the Subscription amount only after the trial period ends.
                                    </p>
                                </div>
                                
                                <!-- Terms and Conditions -->
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input required" type="checkbox" name="t_n_c" value="1" id="t_n_c" {{ $vendor->t_n_c == 1 ? "checked" : "checked" }} required>
                                        <label class="form-check-label" for="t_n_c">
                                            By creating an account, you agree to the <a href="{{ url("public/assets/raProcure/faqs/raPROCURES-TERMS-AND-CONDITIONS.pdf") }}" target="_blank">Terms of Service</a>. 
                                            For more information about RaProcure's privacy practices, see the <a href="{{ url("public/assets/raProcure/faqs/raPROCURES-PRIVACY-POLICY.pdf") }}" target="_blank">RaProcure Privacy Statement</a>.
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="row mt-4">
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn-rfq btn-rfq-primary" id="verify-vendor-profile">
                                        {{ !empty($vendor->vendor_code) ? "Verified" : "Verify" }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        $(document).on('click', '#verify-vendor-profile', function(){
            verifyBuyerProfile();
        });
        function verifyBuyerProfile(){
            $("#verify-vendor-profile").prop('disabled', true).html('<i class="bi spinner-border"></i> Verifying...');
            $.ajax({
                type: "POST",
                url: '{{ route('admin.vendor.profile.update') }}',
                dataType: 'json',
                data: {
                    plan_id: $("#vendor-plan").val(),
                    user_id: "{{ $vendor->user_id }}",
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function() {},
                success: function(responce) {
                    if (responce.status == false) {
                        toastr.error(responce.message);
                        $("#verify-vendor-profile").prop('disabled', false).html('Verify');
                    } else {
                        toastr.success(responce.message);
                        $("#verify-vendor-profile").prop('disabled', false).html('Verified');
                        setTimeout(() => {
                            window.location.href = "{{ route('admin.vendor.index') }}";
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
