@extends('admin.layouts.app_second')

@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.vendor.index') }}"> -> Vendor Module </a>
            <span> -> Add New Vendor </span>
        </h5>
    </div>
</div>
@endsection

{{-- Custom CSS for form styling --}}
<style type="text/css">
    /* Style for the form group container */
    .form-group.mb-3 {
        margin-bottom: 1rem;
    }

    /* Style for the input group container to align country code and mobile number */
    .input-group {
        display: flex; /* Use flexbox for alignment */
        width: 100%; /* Take full width of parent */
        align-items: center; /* Vertically align items in the input group */
        position: relative; /* Needed if you want to absolutely position error icon/message */
    }

    /* Style for the country code dropdown */
    #country_code {
        border-top-right-radius: 0; /* Remove right border radius */
        border-bottom-right-radius: 0; /* Remove bottom right border radius */
        border-right: 0; /* Remove right border to blend with next input */
        cursor: pointer; /* Indicate it's clickable */
        padding: 0.375rem 0.75rem; /* Standard padding */
        background-color: #f8f9fa; /* Light background */
        border-color: #ced4da; /* Standard border color */
        /* Remove default dropdown arrow and add custom one */
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1rem;
        padding-right: 2.5rem; /* Space for the custom arrow */
    }

    /* Style for the mobile number input */
    #user_mobile {
        border-top-left-radius: 0; /* Remove left border radius */
        border-bottom-left-radius: 0; /* Remove bottom left border radius */
        padding: 0.375rem 0.75rem; /* Standard padding */
        flex-grow: 1; /* Allow it to take remaining space */
    }

    /* Focus styles for better user experience */
    #country_code:focus,
    #user_mobile:focus {
        border-color: #80bdff; /* Blue border on focus */
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); /* Blue shadow on focus */
        outline: 0; /* Remove default outline */
    }

    /* Style for error state (when input is invalid) */
    /* Keep this for visual feedback on the input border */
    .is-invalid {
        border-color: #dc3545; /* Red border for invalid input */
    }

    .is-invalid:focus {
        border-color: #dc3545; /* Maintain red border on focus for invalid input */
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25); /* Red shadow on focus for invalid input */
    }

    /* Style for error message text */
    .text-danger.small {
        font-size: 0.875rem; /* Smaller font size for error messages */
        margin-top: 0.25rem; /* Space above the error message */
        display: block; /* Ensure it takes its own line by default */
    }

    /* NEW: Style for the inline mobile error feedback */
    /* This will now be handled by a specific span within the input-group */
    .mobile-error-feedback {
        display: inline-flex; /* Use inline-flex to align icon and text */
        align-items: center;
        margin-left: 0.5rem; /* Space between input and error */
        white-space: nowrap; /* Prevent text from wrapping */
        color: #dc3545; /* Red color */
        font-size: 0.875rem; /* Small font size */
        flex-shrink: 0; /* Prevent it from shrinking */
    }


    /* Responsive adjustments for smaller screens */
    @media (max-width: 576px) {
        #country_code {
            max-width: 120px; /* Limit width on small screens */
            font-size: 0.875rem; /* Smaller font size */
            padding-right: 1.5rem; /* Adjust padding for smaller arrow */
        }
    }
    
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
</style>

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-8 col-md-9 col-sm-12">
            <div class="card" style="margin-top: 20px;">
                <div class="card-body">
                    <div class="col-md-12 border-botom-10">
                        <h3>Add New Vendor</h3>
                    </div>

                    <div class="basic-form mt-20">
                        {{-- Form for adding a new vendor --}}
                        <form class="my-4" autocomplete="off" action="{{ route('admin.vendor.sa-vendor-registration') }}" method="POST" enctype="multipart/form-data" id="register-vendor">
                            @csrf {{-- CSRF token for security --}}

                            <div class="register">

                                {{-- Company Name Field --}}
                                <div class="form-group mb-3 mt-10">
                                    <input type="text" value=""
                                        class="form-control convert-to-upper-case company-name" {{-- Removed @error class here, handled by JS --}}
                                        name="company_name"
                                        placeholder="Company Name *" maxlength="255"
                                        oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')" >
                                    {{-- Custom error span for company name --}}
                                    <span class="text-danger small user-signup-companyname-error"></span>
                                </div>

                                {{-- Person Name Field (Main Admin) --}}
                                <div class="form-group mb-3 mt-10">
                                    <input type="text" value=""
                                           class="form-control convert-to-upper-case person-name" {{-- Removed @error class here --}}
                                           name="name"
                                           placeholder="Person Name (of person to be Main Admin) *"
                                           maxlength="255"
                                           oninput="this.value = this.value.replace(/[^a-zA-Z ]/g, '')" >
                                    {{-- Custom error span for first name --}}
                                    <span class="text-danger small user-signup-firstname-error"></span>
                                </div>

                                {{-- Email ID Field --}}
                                <div class="form-group mb-3">
                                    <input type="text" value=""
                                           class="form-control user-email" {{-- Removed @error class here --}}
                                           name="email"
                                           placeholder="Email ID *">
                                    {{-- Custom error span for email --}}
                                    <span class="text-danger small user-signup-email-error"></span>
                                </div>

                                {{-- Mobile Number Field with Country Code and INLINE error span --}}
                                <div class="form-group mb-3">
                                    <div class="input-group mobile-number-input-group">
                                        <div class="input-group-prepend">
                                            <select class="form-control country-code" name="country_code" style="max-width: 120px;">
                                                @foreach ($countries as $phonecode => $country_name)
                                                    <option value="{{ $phonecode }}" {{ $phonecode==91 ? 'selected' : '' }} >{{ $country_name }} (+{{ $phonecode }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <input type="text" value=""
                                               class="form-control mobile-number" {{-- Removed @error class here --}}
                                               name="mobile"
                                               maxlength="10"
                                               placeholder="Mobile Number *"
                                               oninput="this.value = this.value.replace(/[^0-9.]/g, '');" >
                                        {{-- This span will be dynamically filled with client-side/AJAX error and icon --}}
                                       
                                    </div>
                                    {{-- Custom error span for mobile number (alternative/fallback if you prefer it below) --}}
                                    <span class="text-danger small user-signup-mobile-error"></span>
                                </div>

                                {{-- Referred by Field --}}
                                <div class="form-group mb-3">
                                    <input type="text" value="" autocomplete="new-password"
                                           class="form-control referred-by" {{-- Removed @error class here --}}
                                           name="referred_by"
                                           placeholder="Referred by (Buyer)"
                                           maxlength="255">
                                    {{-- Custom error span for referred by --}}
                                    <span class="text-danger small user-referredby-error"></span> {{-- Renamed to user-referredby-error for consistency --}}
                                </div>

                                {{-- Submit Button --}}
                                <div class="row">
                                    <div class="col-sm-12 text-center">
                                        <button type="submit" id="submit-btn" class="btn-rfq btn-rfq-primary form-submit-btn">Submit</button>
                                    </div>
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
    function appendError(obj, msg = '') {
        $(obj).parent().find('.error-message').remove();
        if (msg) {
            $(obj).parent().append('<span class="help-block error-message">' + msg + '</span>');
        }
    }

    $('#register-vendor').on('submit', function(e) {
        e.preventDefault();

        let mailformat = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        let company_name = $('.company-name').val();
        let person_name = $('.person-name').val();
        let country_code = $('.country-code').val();
        let mobile_number = $('.mobile-number').val();
        let email = $('.user-email').val();
        let referred_by = $('.referred-by').val();
        
        let hasErrors = false;

        // Client-side validation
        if (!company_name) {
            appendError(".company-name", "Please enter Company Name");
            hasErrors = true;
        }else{
            appendError(".company-name");
        }
        if (!person_name) {
            appendError(".person-name", "Please enter Full Name");
            hasErrors = true;
        }else{
            appendError(".person-name");
        }
        if (!country_code) {
            appendError(".country-code", "Please Select Country Code");
            hasErrors = true;
        }else{
            appendError(".country-code");
        }
        if (!mobile_number) {
            appendError(".mobile-number-input-group", "Please enter Mobile number");
            hasErrors = true;
        }else if (country_code == '91' && mobile_number.length!=10) {
            appendError(".mobile-number-input-group", "Please enter 10 digits Mobile number");
            hasErrors = true;
        }else{
            appendError(".mobile-number-input-group");
        }
        
        if (!email) {
            appendError(".user-email", "Please enter Email");
            hasErrors = true;
        } else if (!mailformat.test(email)) {
            appendError(".user-email", "Please enter valid email");
            hasErrors = true;
        }else{
            appendError(".user-email");
        }
        if (!referred_by) {
            appendError(".referred-by", "Please enter Referred by");
            hasErrors = true;
        }else{
            appendError(".referred-by");
        }
        
        if (hasErrors) return;
        
        $(".form-submit-btn").addClass("disabled").html('<i class="bi spinner-border"></i> Submit');

        $.ajax({
            url: $(this).attr("action"),
            method: "POST",
            data: {
                company_name: company_name,
                name: person_name,
                country_code: country_code,
                mobile: mobile_number,
                email: email,
                referred_by: referred_by,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.status) {
                    toastr.success(response.message);
                    window.location.href = response.redirect_url;
                } else {
                    if (response.errors) {
                        let error_messages = '';
                        if (response.errors.company_name) {
                            error_messages += ' '+response.errors.company_name[0];
                            appendError(".company-name", response.errors.company_name[0]);
                        }else{
                            appendError(".company-name");
                        }
                        if (response.errors.name) {
                            error_messages += ' '+response.errors.name[0];
                            appendError(".person-name", response.errors.name[0]);
                        }else{
                            appendError(".person-name");
                        }
                        if (response.errors.country_code) {
                            error_messages += ' '+response.errors.country_code[0];
                            appendError(".country-code", response.errors.country_code[0]);
                        }else{
                            appendError(".country-code");
                        }
                        if (response.errors.mobile) {
                            error_messages += ' '+response.errors.mobile[0];
                            appendError(".mobile-number-input-group", response.errors.mobile[0]);
                        }else{
                            appendError(".mobile-number-input-group");
                        }
                        if (response.errors.email) {
                            error_messages += ' '+response.errors.email[0];
                            appendError(".user-email", response.errors.email[0]);
                        }else{
                            appendError(".user-email");
                        }
                        if (response.errors.referred_by) {
                            error_messages += ' '+response.errors.referred_by[0];
                            appendError(".referred-by", response.errors.referred_by[0]);
                        }else{
                            appendError(".referred-by");
                        }
                        toastr.error(error_messages);
                    }
                }
                $(".form-submit-btn").removeClass("disabled").html('Submit');
            },
            error: function(xhr) {
                // Handle network errors or server errors
                $(".form-submit-btn").removeClass("disabled").html('Submit');
                alert(xhr.responseJSON?.message || 'An error occurred. Please try again.');
            }
        });
    
    });

    $(document).on("change", ".country-code", function(e) {
        let country_code = $(this).val();
        if(country_code == '91'){
            $(".mobile-number").attr("maxlength", "10");
        }else{
            $(".mobile-number").attr("maxlength", "20");
        }
    });
    $(document).on("input", ".convert-to-upper-case", function () {
        $(this).val(($(this).val()).toUpperCase());
    });
</script>
@endsection