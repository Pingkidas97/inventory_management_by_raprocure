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
                        <form class="my-4" autocomplete="off" action="{{ route('admin.vendor.registration-vendor') }}" method="POST" enctype="multipart/form-data" id="reg-form">
                            @csrf {{-- CSRF token for security --}}

                            <div class="register">
                                {{-- Hidden field for user type, assuming 0 means vendor --}}
                                <input type="hidden" name="user_type" value="0">

                                {{-- Company Name Field --}}
                                <div class="form-group mb-3 mt-10">
                                    <input type="text" value="{{ old('company_name') }}" autocomplete="new-password"
                                           class="form-control convert-to-upper-case" {{-- Removed @error class here, handled by JS --}}
                                           name="company_name" id="company_name"
                                           placeholder="Company Name *" maxlength="255"
                                           oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&amp;\(\)\+,\- ]/g,'')">
                                    {{-- Custom error span for company name --}}
                                    <span class="text-danger small user-signup-companyname-error"></span>
                                </div>

                                {{-- Person Name Field (Main Admin) --}}
                                <div class="form-group mb-3 mt-10">
                                    <input type="text" value="{{ old('first_name') }}" autocomplete="new-password"
                                           class="form-control convert-to-upper-case" {{-- Removed @error class here --}}
                                           name="first_name" id="first_name"
                                           placeholder="Person Name (of person to be Main Admin) *"
                                           maxlength="255">
                                    {{-- Custom error span for first name --}}
                                    <span class="text-danger small user-signup-firstname-error"></span>
                                </div>

                                {{-- Email ID Field --}}
                                <div class="form-group mb-3">
                                    <input type="text" value="{{ old('user_email') }}" autocomplete="new-password"
                                           class="form-control" {{-- Removed @error class here --}}
                                           name="user_email" id="user_email"
                                           placeholder="Email ID *">
                                    {{-- Custom error span for email --}}
                                    <span class="text-danger small user-signup-email-error"></span>
                                </div>

                                {{-- Mobile Number Field with Country Code and INLINE error span --}}
                                <div class="form-group mb-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <select class="form-control" name="country_code" id="country_code" style="max-width: 120px;">
                                                @foreach ($countries as $phonecode => $country_name)
                                                    <option value="{{ $phonecode }}" {{ $phonecode==91 ? 'selected' : '' }} >{{ $country_name }} (+{{ $phonecode }})</option>
                                                @endforeach
                                                {{-- @foreach ($countries as $country)
                                                    <option value="{{ $country->phonecode }}" {{ old('country_code', '91') == $country->phonecode ? 'selected' : '' }}>
                                                        {{ $country->name }} (+{{ $country->phonecode }})
                                                    </option>
                                                @endforeach --}}
                                            </select>
                                        </div>
                                        <input type="text" value="{{ old('user_mobile') }}"
                                               class="form-control my-mobile-number" {{-- Removed @error class here --}}
                                               name="user_mobile" id="user_mobile"
                                               maxlength="20"
                                               placeholder="Mobile Number *">
                                        {{-- This span will be dynamically filled with client-side/AJAX error and icon --}}
                                       
                                    </div>
                                    <span class="mobile-error-feedback"></span> {{-- Removed d-none here, JS will control visibility --}}
                                    {{-- Custom error span for mobile number (alternative/fallback if you prefer it below) --}}
                                    <span class="text-danger small user-signup-mobile-error"></span>
                                </div>

                                {{-- Referred by Field --}}
                                <div class="form-group mb-3">
                                    <input type="text" value="{{ old('referred_by') }}" autocomplete="new-password"
                                           class="form-control" {{-- Removed @error class here --}}
                                           name="referred_by" id="referred_by"
                                           placeholder="Referred by (Buyer)"
                                           maxlength="255">
                                    {{-- Custom error span for referred by --}}
                                    <span class="text-danger small user-referredby-error"></span> {{-- Renamed to user-referredby-error for consistency --}}
                                </div>

                                {{-- Submit Button --}}
                                <div class="row" id="signin_div">
                                    <div class="col-sm-12">
                                        <button type="submit" id="submit-btn" class="btn-rfq btn-rfq-primary">Submit</button>
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
{{-- Ensure jQuery is loaded before this script. If not, uncomment the line below --}}
{{-- <script src="{{ asset('public/assets/jQuery/jquery-3.6.0.min.js') }}"></script> --}}

<script>
    // JavaScript for converting input to uppercase
    document.querySelectorAll('.convert-to-upper-case').forEach(element => {
        element.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const regForm = document.getElementById('reg-form');
        const submitBtn = document.getElementById('submit-btn');

        regForm.addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent normal form submission

            let isValid = true;

            // Get form field values
            const companyName = document.getElementById('company_name').value.trim();
            const firstName = document.getElementById('first_name').value.trim();
            const userEmail = document.getElementById('user_email').value.trim();
            const userMobile = document.getElementById('user_mobile').value.trim();
            const referredBy = document.getElementById('referred_by').value.trim();

            // Reset error messages and invalid classes
            document.querySelectorAll('.text-danger.small').forEach(el => el.innerText = '');
            document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));
            // Hide the inline mobile error by default
            document.querySelector('.mobile-error-feedback').innerText = '';
            document.querySelector('.mobile-error-feedback').style.display = 'none';


            // Validation logic for Company Name
            if (companyName === '') {
                document.getElementById('company_name').classList.add('is-invalid');
                document.querySelector('.user-signup-companyname-error').innerText = 'Company Name is required.';
                isValid = false;
            } else if (!/^[a-zA-Z0-9.\&\(\)\+,\- ]+$/.test(companyName)) {
                document.getElementById('company_name').classList.add('is-invalid');
                document.querySelector('.user-signup-companyname-error').innerText = 'Company Name can only contain alphanumeric characters, dots, &, (), +, -, and spaces.';
                isValid = false;
            }

            // Validation logic for Person Name (First Name)
            if (firstName === '') {
                document.getElementById('first_name').classList.add('is-invalid');
                document.querySelector('.user-signup-firstname-error').innerText = 'Person Name is required.';
                isValid = false;
            } else if (!/^[a-zA-Z ]+$/.test(firstName)) {
                document.getElementById('first_name').classList.add('is-invalid');
                document.querySelector('.user-signup-firstname-error').innerText = 'Person Name can only contain alphabetic characters and spaces.';
                isValid = false;
            }

            // Validation logic for Email ID
            if (userEmail === '') {
                document.getElementById('user_email').classList.add('is-invalid');
                document.querySelector('.user-signup-email-error').innerText = 'Email ID is required.';
                isValid = false;
            } else if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(userEmail)) {
                document.getElementById('user_email').classList.add('is-invalid');
                document.querySelector('.user-signup-email-error').innerText = 'Please enter a valid email address.';
                isValid = false;
            }

            // Validation logic for Mobile Number
            if (userMobile === '') {
                document.getElementById('user_mobile').classList.add('is-invalid');
                document.querySelector('.mobile-error-feedback').innerText = 'Mobile Number is required.';
                document.querySelector('.mobile-error-feedback').style.display = 'inline-flex'; // Show inline error
                isValid = false;
            } else if (!/^\d{1,20}$/.test(userMobile)) {
                document.getElementById('user_mobile').classList.add('is-invalid');
                document.querySelector('.mobile-error-feedback').innerText = 'Mobile Number must be between 1 to 20 digits.';
                document.querySelector('.mobile-error-feedback').style.display = 'inline-flex'; // Show inline error
                isValid = false;
            }

            // Validation logic for Referred By (optional, but checking length if filled)
            if (referredBy.length > 0 && referredBy.length > 255) {
                document.getElementById('referred_by').classList.add('is-invalid');
                document.querySelector('.user-referredby-error').innerText = 'Maximum 255 characters allowed for Referred By.';
                isValid = false;
            } else if (referredBy === '') { // If you want to make it required, uncomment this
                // document.getElementById('referred_by').classList.add('is-invalid');
                // document.querySelector('.user-referredby-error').innerText = 'Referred by Buyer is required.';
                // isValid = false;
            }


            // If valid, submit via AJAX
            if (isValid) {
                // Using jQuery for AJAX as your initial code used it
                $.ajax({
                    url: regForm.getAttribute('action'),
                    type: 'POST',
                    data: new FormData(regForm), // Use FormData for consistency with native JS
                    processData: false, // Don't process the data
                    contentType: false, // Don't set content type (FormData does it)
                    beforeSend: function () {
                        submitBtn.disabled = true;
                        submitBtn.innerText = 'Submitting...';
                    },
                    success: function (response) {
                        if (response.success) {
                            alert(response.message || 'Form submitted successfully!');
                            regForm.reset(); // Reset form
                        } else {
                            // Server-side errors (if Laravel sends specific error messages)
                            if (response.errors) {
                                for (let field in response.errors) {
                                    let errorMessages = response.errors[field];
                                    const inputElement = document.getElementById(field);
                                    if (inputElement) {
                                        inputElement.classList.add('is-invalid');
                                        if (field === 'user_mobile') {
                                            document.querySelector('.mobile-error-feedback').innerText = errorMessages[0];
                                            document.querySelector('.mobile-error-feedback').style.display = 'inline-flex';
                                        } else if (field === 'company_name') {
                                            document.querySelector('.user-signup-companyname-error').innerText = errorMessages[0];
                                        } else if (field === 'first_name') {
                                            document.querySelector('.user-signup-firstname-error').innerText = errorMessages[0];
                                        } else if (field === 'user_email') {
                                            document.querySelector('.user-signup-email-error').innerText = errorMessages[0];
                                        } else if (field === 'referred_by') {
                                            document.querySelector('.user-referredby-error').innerText = errorMessages[0];
                                        }
                                        // You might need more specific handling here if your server returns errors
                                        // for fields that don't have direct mapping or specific error spans.
                                    }
                                }
                            } else {
                                alert(response.message || 'Something went wrong. Please try again.');
                            }
                        }
                    },
                    error: function (xhr) {
                        // Handle server-side validation errors or failures
                        if (xhr.status === 422) { // Laravel validation error status
                            const errors = xhr.responseJSON.errors;
                            for (const field in errors) {
                                const errorMessages = errors[field];
                                const inputElement = document.getElementById(field);
                                if (inputElement) {
                                    inputElement.classList.add('is-invalid');
                                    // Set error message based on the field
                                    if (field === 'company_name') {
                                        document.querySelector('.user-signup-companyname-error').innerText = errorMessages[0];
                                    } else if (field === 'first_name') {
                                        document.querySelector('.user-signup-firstname-error').innerText = errorMessages[0];
                                    } else if (field === 'user_email') {
                                        document.querySelector('.user-signup-email-error').innerText = errorMessages[0];
                                    } else if (field === 'user_mobile') {
                                        document.querySelector('.mobile-error-feedback').innerText = errorMessages[0];
                                    } else if (field === 'referred_by') {
                                        document.querySelector('.user-referredby-error').innerText = errorMessages[0];
                                    }
                                }
                            }
                        } else {
                            alert('An error occurred during submission. Please try again.');
                        }
                    },
                    complete: function () {
                        submitBtn.disabled = false;
                        submitBtn.innerText = 'Submit';
                    }
                });
            }
        });
    });
</script>
@endsection