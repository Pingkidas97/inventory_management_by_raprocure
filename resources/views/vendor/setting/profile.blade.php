@extends('vendor.layouts.app_second', ['title'=>'Vendor Profile', 'sub_title'=>'Create'])

@section('css')
    <style>
        span.tmd-serial-no, span.branch-serial-no {
            font-size: 16px;
        }
        #submit-vendor-profile .spinner-border {
            height: 14px;
            width: 14px;
        }        
    </style>
@endsection

@section('content')
    @php
        $vendor = $vendor_data->vendor;
        $branchDetails = $vendor_data->branchDetails;
        
        $is_profile_verified = false;
        if($vendor_data->is_profile_verified==1){
            $is_profile_verified = true;
        }
    @endphp
    <div class="my_profile form-head card bg-white border-0">
        <div class="card-header d-flex align-items-center w-100 border-0 bg-transparent">
            <h4 class="card-title">My Profile </h4>
            {{-- <a href="" class="btn-rfq btn-rfq-secondary">Edit</a> --}}
        </div>
        <div class="card-body">
            <div class=" tab-content-inner">
                <form id="vendor-profile-form" action="{{ route('vendor.save-vendor-profile') }}" method="POST">
                    @csrf
                    <div class="row">
                        <h2 class="col-md-12 text-center profile-type-title">For Vendors who are supplying to Steel Plants</h2>
                    </div>
                    <div class="row mt-4">
                        <h2 class="mb-4 mt-10 col-md-12">1. Company Details</h2>
                    </div>
                    <div class="basic-form">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Company Name / Legal Name<span class="text-danger">*</span></label>
                                <input type="text" class="form-control required text-upper-case" value="{{ $vendor->legal_name }}"
                                    placeholder="Enter Company Name / Legal Name" name="legal_name" id="legal_name"
                                    oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')" maxlength="255">
                            </div>

                            <div class="form-group col-md-6">
                                <input type="hidden" name="profile_img_old" value="{{ $vendor->profile_img }}" >
                                <span class="text-dark">Company Logo (File Type: JPG,JPEG,PNG)</span>
                                <div class="file-browse">
                                    <span class="button button-browse">
                                        Select <input type="file" class="profile_img" name="profile_img" onchange="validateFile(this, 'JPG/JPEG/PNG')">
                                    </span>
                                    <input type="text" class="form-control" placeholder="Upload Company Logo" readonly="" >
                                </div>
                                <span>
                                    @if (is_file(public_path('uploads/vendor-profile/'.$vendor->profile_img)))
                                        <a class="file-links" href="{{ url('public/uploads/vendor-profile/'.$vendor->profile_img) }}" target="_blank" download="{{ $vendor->profile_img }}">
                                            <span>{!! strlen($vendor->profile_img)>30 ? substr($vendor->profile_img, 0, 25).'<i class="bi bi-info-circle-fill" title="'.$vendor->profile_img.'" ></i>' : $vendor->profile_img !!} </span>
                                        </a>
                                    @endif
                                </span>
                            </div>
                            <div class="form-group col-md-3">
                                <div class="row">
                                    <div class="col-12">
                                        <label>Date of Incorporation (DD/MM/YYYY)<span
                                                class="text-danger">*</span></label>
                                        <input type="text" placeholder="Date format is DD/MM/YYYY" onblur="validateDateFormat(this, true);"
                                            class="form-control required date-masking" id="date_of_incorporation" name="date_of_incorporation"
                                            value="{{ !empty($vendor->date_of_incorporation) ? date("d/m/Y", strtotime($vendor->date_of_incorporation)) : '' }}" maxlength="10">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group col-md-3">
                                <label>Nature of Organization<span class="text-danger">*</span></label>
                                <select class="form-select required" name="nature_of_organization">
                                    @if(!empty($nature_of_organization))
                                        @foreach ($nature_of_organization as $id => $name)
                                        <option value="{{ $id }}" {{ $vendor->nature_of_organization == $id ? 'selected' : '' }} >{{ $name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label>Other Contact Details<span class="text-danger">*</span></label>
                                <input type="text" placeholder="Enter Other Contact Details" oninput="this.value = this.value.replace(/[^0-9,\-\/ ]/g, '')"
                                    class="form-control required" id="other_contact_details" name="other_contact_details"
                                    value="{{ $vendor->other_contact_details }}" maxlength="255">
                                    
                            </div>
                            {{-- <div class="form-group col-md-6 d-none">
                                <label>Nature of Business<span class="text-danger">*</span></label>
                                <select class="form-select required" name="nature_of_business">
                                    @if(!empty($nature_of_business))
                                        @foreach ($nature_of_business as $id => $name)
                                        <option value="{{ $id }}" {{ $vendor->nature_of_business == $id ? 'selected' : '' }} >{{ $name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div> --}}

                            <div class="form-group col-12">
                                <label>Registered Address<span class="text-danger">*</span></label>
                                <textarea class="form-control registered_address required" placeholder="Enter Registered Address"
                                id="registered_address" maxlength="1700" name="registered_address">{{ $vendor->registered_address }}</textarea>
                            </div>

                            <div class="form-group col-md-3">
                                <label>Country<span class="text-danger">*</span></label>
                                <select class="form-select required organization-country" 
                                onchange="getState('organization-country', 'organisation-state', 'organisation-city')" 
                                name="country">
                                    @php
                                        if(empty($vendor->country)){
                                            $vendor->country = 101;
                                        }
                                    @endphp
                                    @if(!empty($countries))
                                        @foreach ($countries as $country_id => $country_name)
                                        <option value="{{ $country_id }}" {{ $vendor->country == $country_id ? 'selected' : '' }} >{{ $country_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label>State<span class="text-danger">*</span></label>
                                <select class="form-select required organisation-state"
                                    {{-- onchange="getCity('organisation-state', 'organisation-city')" --}}
                                    name="state">
                                    <option value="">Select State</option>
                                    {!! getStateByCountryId($vendor->country, $vendor->state??0) !!}
                                </select>
                            </div>

                            {{-- <div class="form-group col-md-6">
                                <label>City<span class="text-danger">*</span></label>
                                <select class="form-select required organisation-city"
                                    name="city">
                                    <option value="">Select City</option>
                                    {!! !empty($vendor->state) ? getCityByStateId($vendor->state, $vendor->city??0) : '' !!}
                                </select>
                            </div> --}}

                            <div class="form-group col-md-6">
                                <label>Pincode<span class="text-danger">*</span></label>
                                <input type="text" class="form-control organisation-pincode required" name="pincode" value="{{ $vendor->pincode }}" 
                                minlength="6" maxlength="6" oninput="this.value=this.value.replace(/[^0-9.\&\(\)\+,\- ]/,'')" placeholder="Enter Pin Code">
                            </div>

                            <div class="form-group col-xl-6 col-lg-6 col-md-12">
                                <div class="row">
                                    <div class="form-group col-sm-5">
                                        <label>
                                            <span class="gst-field-label-name">{{ $vendor->country == 101 ? "GSTIN/VAT" : "Please enter your Tax Identification Number" }}</span>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control gstin-vat required" name="gstin" value="{{ $vendor->gstin }}" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')"
                                        maxlength="15" placeholder="{{ $vendor->country == 101 ? "Enter GSTIN/VAT" : "Enter your Tax Identification Number" }}">
                                    </div>
                                    <div class="col-sm-7">
                                        <input type="hidden" name="gstin_document_old" value="{{ $vendor->gstin_document }}" >
                                        <span class="text-dark">GSTIN/VAT Document (File Type: JPG, JPEG, PDF)
                                            <span class="text-danger">*</span>
                                        </span>
                                        <div class="file-browse">
                                            <span class="button button-browse">
                                                Select <input onchange="validateFile(this, 'JPG/JPEG/PDF')" type="file" class="{{ $vendor->gstin_document=='' || $vendor->gstin_document==null ? 'required-file' : '' }}" name="gstin_document">
                                            </span>
                                            <input type="text" class="form-control" placeholder="Upload GSTIN/VAT Document" readonly="">
                                        </div>
                                        <span>
                                            @if (is_file(public_path('uploads/vendor-profile/'.$vendor->gstin_document)))
                                                <a class="file-links" href="{{ url('public/uploads/vendor-profile/'.$vendor->gstin_document) }}" target="_blank" download="{{ $vendor->gstin_document }}">
                                                    <span>{!! strlen($vendor->gstin_document)>30 ? substr($vendor->gstin_document, 0, 25).'<i class="bi bi-info-circle-fill" title="'.$vendor->gstin_document.'" ></i>' : $vendor->gstin_document !!} </span>
                                                </a>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label>Website</label>
                                <input type="text" class="form-control website-url" name="website" value="{{ $vendor->website }}" maxlength="255" placeholder="Enter Website URL">
                            </div>
                            
                            <div class="form-group col-md-12">
                                <span>- Mention the name of 2 of your customers (must be Steel Plants)</span>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Customer Name 1<span class="text-danger">*</span></label>
                                <input type="text" class="form-control required" maxlength="255" name="company_name1" value="{{ $vendor->company_name1 }}" placeholder="Enter Customer Name 1" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Customer Name 2<span class="text-danger">*</span></label>
                                <input type="text" class="form-control required" maxlength="255" name="company_name2" value="{{ $vendor->company_name2 }}" placeholder="Enter Customer Name 2" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')">
                            </div>

                            <div class="form-group col-md-12">
                                <span>- Mention the name of your TOP 3 PRODUCTS (Please Note: After your profile is verified, you will be able to add ALL your products with details).</span>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Product Name<span class="text-danger">*</span></label>
                                @php
                                    $organization_product_name = explode('@#', $vendor->registered_product_name);
                                @endphp
                                <input type="text" class="form-control required" maxlength="350" name="registered_product_name" oninput="removeCharacters(this, '#')" onblur="removeCharacters(this, '#')" value="{{ implode(", ", $organization_product_name)}}" placeholder="Enter Product Name">
                            </div>
                        </div>

                        @if($is_profile_verified)
                        <div class="hr_line"></div>
                        <div class="row mt-4 justify-content-between">
                            <h2 class="col-md-6 col-sm-6 col-12">2. Branch Details </h2>
                            <h2 class="col-md-6 col-sm-6 col-12">Note: Click on “ADD BRANCH” only if you have any additional branch.</h2>
                            <div class="col-md-6 col-sm-6 col-12">
                                <a href="javascript:void(0)"
                                    class="btn-rfq btn-rfq-secondary ms-auto d-table addmoreBtn"
                                    onclick="addMoreBranchFields()"> + Add Branch</a>
                            </div>
                        </div>
                        <div id="branch_container">
                            @if(!empty($branchDetails) && count($branchDetails)>0)
                                @foreach ($branchDetails as $k => $branch)
                                    <div class="row branch-row" data-row-id="{{ $k }}">
                                        <h4 class="frm_head">Branch Information <span class="branch-serial-no">{{ $k+1 }}</span></h4>
                                        <div class="form-group col-md-6">
                                            <label>Branch Name<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control required text-upper-case" name="branch_name[]" value="{{ $branch->name }}" placeholder="Enter Branch Name" maxlength="255" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')">
                                            <input type="hidden" name="edit_id_branch[]" value="{{ $branch->branch_id }}">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>
                                                <span class="gst-field-label-name">GSTIN/VAT</span>
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control required branch-gstin-vat" name="branch_gstin[]" value="{{ $branch->gstin }}" placeholder="Enter GSTIN/VAT" maxlength="15" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&amp;\(\)\+,\- ]/,'')">
                                        </div>
                                        <div class="form-group col-md-12">
                                            <label>Registered Address<span class="text-danger">*</span></label>
                                            <textarea class="form-control required" name="branch_address[]" placeholder="Enter Registered Address" maxlength="1700">{{ $branch->address }}</textarea>
                                        </div>
                                        <div class="form-group col-md-6">
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
                                        <div class="form-group col-md-6">
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
                                            onkeypress="return validatePinCode(event, this)" placeholder="Enter Pin Code" minlength="6" maxlength="6">
                                        </div>
                                        
                                        <div class="form-group col-md-6">
                                            <label>Name of Authorized Person & Designation<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control required" name="branch_authorized_designation[]" value="{{ $branch->authorized_designation }}" placeholder="Enter Name of Authorized Person & Designation" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Mobile<span class="text-danger">*</span></label>
                                            <input type="text" class="form-control validate-max-length my-mobile-number required" name="branch_mobile[]" value="{{ $branch->mobile }}"
                                            data-maxlength="{{ $vendor->country == 101 ? 10 : 25 }}" data-minlength="{{ $vendor->country == 101 ? 10 : 1 }}" placeholder="Enter Mobile">
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Email<span class="text-danger">*</span></label>
                                            <input type="email" class="form-control valid-email required" name="branch_email[]" value="{{ $branch->email }}" placeholder="Enter Email" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+\@,\- ]/,'')">
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
                                @if(0)
                                <div class="row branch-row">
                                    <h4 class="frm_head">Branch Information <span class="branch-serial-no">1</span></h4>
                                    <div class="form-group col-md-6">
                                        <label>Branch Name<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control required text-upper-case" name="branch_name[]" value="" placeholder="Enter Branch Name" maxlength="255" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')">
                                        <input type="hidden" name="edit_id_branch[]" value="0">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>
                                            <span class="gst-field-label-name">GSTIN/VAT</span>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control required branch-gstin-vat" name="branch_gstin[]" value="" placeholder="Enter GSTIN/VAT" maxlength="15" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&amp;\(\)\+,\- ]/,'')">
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label>Registered Address<span class="text-danger">*</span></label>
                                        <textarea class="form-control required" name="branch_address[]" placeholder="Enter Registered Address" maxlength="1700"></textarea>
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
                                            {{-- onchange="getCity('branch-state-n1', 'branch-city-n1')" --}}
                                            name="branch_state[]">
                                            <option value="">Select State</option>
                                            @if(!empty($india_states))
                                                @foreach ($india_states as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>City<span class="text-danger">*</span></label>
                                        <select class="form-select branch-city branch-city-n1 required" name="branch_city[]">
                                            <option value="">Select City</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Pincode<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control branch-pincode required" name="branch_pincode[]" value=""
                                        onkeypress="return validatePinCode(event, this)" placeholder="Enter Pin Code" minlength="6" maxlength="6">
                                    </div>
                                    
                                    <div class="form-group col-md-6">
                                        <label>Name of Authorized Person & Designation<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control required" name="branch_authorized_designation[]" value="" placeholder="Enter Name of Authorized Person & Designation" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Mobile<span class="text-danger">*</span></label>
                                        <input type="text" class="form-control validate-max-length my-mobile-number required" name="branch_mobile[]" value=""
                                        data-maxlength="{{ $vendor->country == 101 ? 10 : 25 }}" data-minlength="{{ $vendor->country == 101 ? 10 : 1 }}" placeholder="Enter Mobile">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Email<span class="text-danger">*</span></label>
                                        <input type="email" class="form-control valid-email required" name="branch_email[]" value="" placeholder="Enter Email" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+\@,\- ]/,'')">
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
                            @endif
                        </div>
                        @endif

                        <div class="hr_line"></div>

                        <div class="row mt-4 form-group justify-content-between">
                            <h2 class="col-md-6">{{ $is_profile_verified ? 3 : 2 }}. Registrations</h2>
                        </div>

                        <div class="form-group row">
                            <div class="form-group col-md-6">
                                <label>1. MSME</label>
                                <input type="text" class="form-control registration-msme" name="msme" value="{{ $vendor->msme }}" placeholder="Enter MSME" maxlength="255" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')">
                            </div>
                            <div class="form-group col-md-6">
                                <input type="hidden" name="msme_certificate_old" value="{{ $vendor->msme_certificate }}" >
                                <span class="text-dark">MSME Certificate (File Type: PDF, DOC, DOCX, JPG, JPEG, PNG)</span>
                                <div class="file-browse">
                                    <span class="button button-browse">
                                        Select <input type="file" class="msme_certificate" id="registration-msme-file" name="msme_certificate" onchange="validateFile(this, 'PDF/DOC/DOCX/JPEG/JPG/PNG');reValidateRegistrationDoc(this);">
                                    </span>
                                    <input type="text" class="form-control" placeholder="Upload MSME Certificate Document" readonly="">
                                </div>
                                <span>
                                    @if (is_file(public_path('uploads/vendor-profile/'.$vendor->msme_certificate)))
                                        <a class="file-links" href="{{ url('public/uploads/vendor-profile/'.$vendor->msme_certificate) }}" target="_blank" download="{{ $vendor->msme_certificate }}">
                                            <span>{!! strlen($vendor->msme_certificate)>30 ? substr($vendor->msme_certificate, 0, 25).'<i class="bi bi-info-circle-fill" title="'.$vendor->msme_certificate.'" ></i>' : $vendor->msme_certificate !!} </span>
                                        </a>
                                    @endif
                                </span>
                            </div>
                            @if($is_profile_verified)
                            <div class="form-group col-md-6">
                                <label>2. ISO Registration</label>
                                <input type="text" class="form-control registration-iso" name="iso_registration" value="{{ $vendor->iso_registration }}" placeholder="Enter ISO Registration" maxlength="255" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')">
                            </div>
                            <div class="form-group col-md-6">
                                <input type="hidden" name="iso_regi_certificate_old" value="{{ $vendor->iso_regi_certificate }}" >
                                <span class="text-dark">ISO Certificate (File Type: PDF, DOC, DOCX, JPG, JPEG, PNG)</span>
                                <div class="file-browse">
                                    <span class="button button-browse">
                                        Select <input type="file" id="registration-iso-file" class="iso_regi_certificate" name="iso_regi_certificate" onchange="validateFile(this, 'PDF/DOC/DOCX/JPEG/JPG/PNG');reValidateRegistrationDoc(this);">
                                    </span>
                                    <input type="text" class="form-control" placeholder="Upload ISO Certificate Document" readonly="" >
                                </div>
                                <span>
                                    @if (is_file(public_path('uploads/vendor-profile/'.$vendor->iso_regi_certificate)))
                                        <a class="file-links" href="{{ url('public/uploads/vendor-profile/'.$vendor->iso_regi_certificate) }}" target="_blank" download="{{ $vendor->iso_regi_certificate }}">
                                            <span>{!! strlen($vendor->iso_regi_certificate)>30 ? substr($vendor->iso_regi_certificate, 0, 25).'<i class="bi bi-info-circle-fill" title="'.$vendor->iso_regi_certificate.'" ></i>' : $vendor->iso_regi_certificate !!} </span>
                                        </a>
                                    @endif
                                </span>
                            </div>
                            @endif
                        </div>

                        <div class="hr_line"></div>

                        <div class="row mt-4 form-group justify-content-between">
                            <h2 class="col-md-6">{{ $is_profile_verified ? 4 : 3 }}. Other Details</h2>
                        </div>
                        <div class="form-group row">
                            <div class="form-group col-md-12">
                                <label for="other-description" class="form-label">Organization
                                    Description<span class="text-danger">(Maximum 300 Words)*</span></label>
                                <textarea class="form-control required" name="description" id="other-description" placeholder="Please enter a short description about your organization for the Buyer to view."
                                    rows="5">{{ $vendor->description }}</textarea>
                            </div>

                            <div class="form-group col-md-12 note-txt">
                                <span>
                                    <strong>*Note: <br>
                                        1.</strong> Once your profile is verified by raProcure, you will be eligible to upload your products, catalog, create mini web page. You will be asked to pay the Subscription amount only after the trial period ends.
                                </span>
                            </div>
                            <div class="form-group col-md-12">
                                <label class="radio-inline mr-3">
                                    <input type="checkbox" name="t_n_c" value="1" {{ $vendor->t_n_c == 1 ? "checked" : "checked" }} class="required" required="" >
                                        By creating an account, you agree to the <a href="{{ url("public/assets/raProcure/faqs/raPROCURES-TERMS-AND-CONDITIONS.pdf") }}" target="_blank">Terms of Service</a>. 
                                        For more information about RaProcure's privacy practices, see the <a href="{{ url("public/assets/raProcure/faqs/raPROCURES-PRIVACY-POLICY.pdf") }}" target="_blank">RaProcure Privacy Statement</a>.
                                </label>
                            </div>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn-rfq btn-rfq-primary" id="submit-vendor-profile">SUBMIT</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        let branch_country = "", branch_state = "";

        @if($is_profile_verified)
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
        @endif

        let checkUniqueGstNumber = function(_this){
            let vendor_gst_number = $(_this).val();
            $("#submit-vendor-profile").attr("disabled", "disabled");
            $.ajax({
                url: '{{ route('vendor.validate-vendor-gstin-vat') }}',
                type: "POST",
                dataType: "json",
                data: {
                    vendor_gst_number: vendor_gst_number,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status==false) {
                        toastr.error(response.message);
                        $(_this).val('');
                        setTimeout(function(){
                            $("#submit-vendor-profile").removeAttr("disabled");
                        }, 1000);
                    }else{
                        $("#submit-vendor-profile").removeAttr("disabled");
                    }
                }
            });
        }
    </script>
    
    <script src="{{ asset('public/assets/js/profile-validation.js') }}"></script>
    <script src="{{ asset('public/assets/vendor/js/vendor-profile-script.js') }}"></script>

    <script>
        $('#vendor-profile-form').on('submit', function(e) {
            e.preventDefault();
            $("#submit-vendor-profile").attr("disabled", "disabled");
            if(!validateVendorProfile()){
                toastr.error("Please fill all the manadatory fields");
                $("#submit-vendor-profile").removeAttr("disabled");
                return false;
            }

            let formData = new FormData(document.getElementById("vendor-profile-form"));

            $.ajax({
                url: $(this).attr("action"),
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend: function() {
                    $("#submit-vendor-profile").html('<i class="bi spinner-border"></i> Submitting...').attr("disabled", "disabled");
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
                    $("#submit-vendor-profile").html('Submit').removeAttr("disabled");
                },
                error: function(xhr) {
                    // Handle network errors or server errors
                    toastr.error("Something went wrong...");
                    setTimeout(function(){
                        $("#submit-vendor-profile").html('Submit').removeAttr("disabled");
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
                url: "{{ route('vendor.get-state-by-country-id') }}",
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
        //         url: "{{ route('vendor.get-city-by-state-id') }}",
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
    </script>

@endsection