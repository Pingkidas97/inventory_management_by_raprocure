@extends('admin.layouts.app_second')

@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.vendor.index') }}"> -> Vendor Module </a>
            <span> -> Update <b>{{ $vendor_data->vendor->legal_name }}</b> Vendor Profile </span>
        </h5>
    </div>
</div>
@endsection

{{-- Custom CSS for form styling --}}
<style type="text/css">
    label.error, .error-message {
        float: left;
        color: #E28647;
        font-size: 80%;
        padding-top: 0;
        margin-bottom: 15px;
    }
    .form-submit-btn .spinner-border {
        height: 14px;
        width: 14px;
    }

    .file-browse {
        position: relative;
        display: flex;
        align-items: center;
    }
    .file-browse .button {
        background-color: #ffffff;
        float: left;
        font-weight: 400;
        line-height: 22px;
        border: 1px solid #ccd4da;
        border-right: 0;
        border-radius: 0.25rem 0 0 0.25rem;
        opacity: 0;
        z-index: 2;
        padding: .3rem .75rem;
    }
    .button-browse input[type=file] {
        position: absolute;
        top: 0;
        right: 0;
        min-width: 100%;
        min-height: 100%;
        /* font-size: 100px; */
        text-align: right;
        filter: alpha(opacity=0);
        opacity: 0;
        outline: none;
        background: white;
        cursor: inherit;
        display: block;
    }
    form input {
        font-size: 13px !important;
    }
    .file-browse .form-control[readonly] {
        background-color: #ffffff !important;
        opacity: 1;
        float: left;
        border-radius: 0.25rem;
        width: 100%;
        position: absolute;
    }
    .container-fluid .form-group {
        margin-bottom: 1rem;
    }
    .card {
        margin-bottom: 3px;
    }

</style>

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

<div class="container-fluid py-4 card bg-white border-0">
    <div class="row justify-content-center mt-5">
        <div class="card-header d-flex align-items-center w-100 border-0 bg-transparent row">
            <h4 class="col-md-3 card-title"><b style="font-size: 20px">{{ $vendor_data->vendor->legal_name }}</b> Vendor Profile </h4>
            <h2 class="col-md-9 text-center profile-type-title">For Vendors who are supplying to Steel Plants</h2>
        </div>
        <div class="card-body">
            <div class=" tab-content-inner">
                <form id="vendor-profile-form" action="{{ route('admin.vendor.save-sa-vendor-profile') }}" method="POST">
                    @csrf
                    <div class="basic-form">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Company Name / Legal Name<span class="text-danger">*</span></label>
                                <input type="text" class="form-control required text-upper-case" value="{{ $vendor->legal_name }}"
                                    placeholder="Enter Company Name / Legal Name" name="legal_name" id="legal_name"
                                    oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')" maxlength="255">
                                    <input type="hidden" name="company_id" value="{{ $vendor->user_id }}">
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
                                <label>Other Contact Details</label>
                                <input type="text" placeholder="Enter Other Contact Details" oninput="this.value = this.value.replace(/[^0-9,\-\/ ]/g, '')"
                                    class="form-control" id="other_contact_details" name="other_contact_details"
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
                                            {{-- <span class="text-danger">*</span> --}}
                                        </span>
                                        <div class="file-browse">
                                            <span class="button button-browse">
                                                Select <input onchange="validateFile(this, 'JPG/JPEG/PDF')" type="file" class="" name="gstin_document">
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
                                <label>Customer Name 2</label>
                                <input type="text" class="form-control" maxlength="255" name="company_name2" value="{{ $vendor->company_name2 }}" placeholder="Enter Customer Name 2" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')">
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

                        <div class="form-group row">
                            <div class="form-group col-md-12">
                                <label for="other-description" class="form-label">Organization Description<span class="text-danger">(Maximum 300 Words)*</span></label>
                                <textarea class="form-control required" name="description" id="other-description" placeholder="Please enter a short description about your organization for the Buyer to view."
                                    rows="5">{{ $vendor->description }}</textarea>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Number of Logins Required <span class="text-danger">*</span></label>
                                <select class="form-select required" id="vendor-plan" {{ !empty($vendor->vendor_code) ? "disabled" : "" }} name="vendor_plan">
                                    @if(!empty($vendor_plan))
                                        @foreach ($vendor_plan as $key => $value)
                                        <option value="{{ $value->id }}" {{ $current_plan_id == $value->id ? 'selected' : '' }}>
                                            {{ $value->plan_name.": Rs. ".$value->price." per year for ".$value->no_of_user." users" }}
                                        </option>
                                        @endforeach
                                    @endif
                                </select>
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
                url: '{{ route('admin.vendor.validate-vendor-gstin-vat') }}',
                type: "POST",
                dataType: "json",
                data: {
                    vendor_gst_number: vendor_gst_number,
                    company_id: "{{ $vendor->user_id }}",
                    _token: '{{ csrf_token() }}'
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
                url: "{{ route('admin.get-state-by-country-id') }}",
                data: {
                    country_id: country_id,
                    _token: '{{ csrf_token() }}'
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
        //         url: "{{ route('admin.get-city-by-state-id') }}",
        //         data: {
        //             state_id: state_id,
        //             _token: '{{ csrf_token() }}'
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

        //for select file: start
        $(document).on('change', '.button-browse :file', function () {
            let input = $(this),
                numFiles = input.get(0).files ? input.get(0).files.length : 1,
                label = input.val().replace(/\\/g, '/').replace(/.*\//, '');

            input.trigger('fileselect', [numFiles, label, input]);
        });

        $('.button-browse :file').on('fileselect', function (event, numFiles, label, input) {
            let val = numFiles > 1 ? numFiles + ' files selected' : label;
            input.parent('.button-browse').next(':text').val(val);
        });
        //for select file: end

        
        $(document).on("input", ".text-upper-case", function () {
            $(this).val(($(this).val()).toUpperCase());
        });
    </script>
@endsection