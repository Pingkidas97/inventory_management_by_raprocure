<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <meta property="og:image:size" content="300" />
    <link rel="shortcut icon" href="{{ asset('public/assets/images/favicon/raprocure-fevicon.ico') }}" type="image/x-icon">
    <title>Login/SignUp - Raprocure</title>
    <!---css-->
    <link href="{{ asset('public/assets/login/css/style.css') }}" rel="stylesheet">

    <!---bootsrap-->
    <link href="{{ asset('public/assets/login/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    <!---fontawesome-icon-->
    {{-- <link href="{{ asset('public/assets/login/fontawesome/css/all.css') }}"> --}}
    <!-- Add this in your HTML head section -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="{{ asset('public/assets/login/js/jquery-3.7.1.min.js') }}"></script>
    <style>
        label.error, .error-message {
            float: left;
            color: #E28647;
            font-size: 80%;
            padding-top: 0;
            margin-bottom: 15px;
        }
        #captcha-refresh-btn .spinner-border {
            height: unset;
            border: none;
        }
        .form-submit-btn .spinner-border {
            height: 14px;
            width: 14px;
        }
        p.signup-captcha-error {
            font-weight: 600;
        }
    </style>
</head>

<body>
    <!-- <div class="project_header" id="project_header"> -->
    <div class="project_header sticky" id="project_header">
        <header class="P_header">
            <div class="container-fluid">
                <div class="cust_container">
                    <div class="top_head row align-items-center py-1">
                        <div class="col-lg-4  col-md-6 col-12 d-md-block d-none">
                        </div>
                        <div class="col-12 col-md-4 d-lg-block d-none">
                        </div>
                        <div class="col-lg-4  col-md-6 col-12 top-section-right">
                            <p class="text-white">Helpline No.: 9088880077 / 9088844477</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <div class="header" id="myHeader">
            <div class="container-fluid">
                <div class="row btm_heada">
                    <div class="col-lg-2 col-md-3 col-sm-4 col-6 navbar-header  header-bottom-left">
                        <a class="navbar-brand" href="#">
                            <img alt=" " class="header_img_final" src="{{ asset('public/assets/images/rfq-logo.png') }}">
                        </a>
                    </div>
                    <div class="col-lg-8 col-md-7 col-sm-6 col-12 header-bottom-right ">
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-2 col-6 globle-header-icons ">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="main" id="main">
        <div class="container-fluid">      

            <div class="row bg-white">

                <div class="col-md-6 col-12 d-flex login-account-page">
                    <div class="login">

                        <h3>Login to Your Account</h3>
                        <!-- Login Form -->
                        <form class="makeForm my-4" action="{{ route('login.submit') }}" method="POST" id="loginForm">
                            @csrf
                            <div class="mb-3">
                                <input name="email" type="email" class="form-control" id="login-email" aria-describedby="emailHelp" 
                                placeholder="Enter Email">
                            </div>
                            <div class="mb-3 password-container">
                                <input type="password" class="form-control password-field"  id="login-password" placeholder="Password" maxlength="25">
                                <i class="bi bi-eye-slash show-hide-password"></i>
                                <span class="focus-input100"></span>
                                <input type="hidden" id="hiddenkey" name="password">
                            </div>
                            <div class="form-check d-flex justify-content-center">
                                <input type="checkbox" class="form-check-input" id="remember-me">
                                <label class="form-check-label" for="remember-me">Remember me</label>
                            </div>
                            <button type="submit" class="btn-rfq btn-rfq-primary login-buyer-vendor">Login</button>
                        </form>

                        <h5>Forgot Password ? Please Click here to <a class="theme-color" href="{{route('forgot-password')}}">Reset Password</a> </h5>

                    </div>

                </div>

                <div class="col-md-6 col-12 d-flex align-items-center justify-content-around">
                    <form class="my-4 makeForm" id="register-form" action="{{ route('register.submit') }}" method="POST">
                        <div class="register">
                            <h3>New User Register Here</h3>
                            <div class="custom-tab-1">
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link steel-plant" id="steel-plant-tab" data-bs-toggle="tab"
                                            href="#steel-plant" role="tab" aria-controls="steel-plant"
                                            aria-selected="false" onclick="set_login_type('1')">
                                            <i class="la la-cubes me-2"></i>Steel Plant
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active vendor-form" id="vendor-tab" data-bs-toggle="tab"
                                            href="#vendor" role="tab" aria-controls="vendor" aria-selected="true" onclick="set_login_type('2')">
                                            <i class="la la-list me-2"></i>Vendor
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <input type="hidden" name="user_type" id="user_type" value="2">
                                <input type="hidden" id="msg-confirm-type" value="no">
                                <input type="text" class="form-control text-upper-case company-name" name="company_name" value="" 
                                placeholder="Company Name *" maxlength="255" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')">
                                {{-- <div id="lblError" class="text-danger small"></div> --}}
                            </div>

                            <div class="mb-3">
                                <input type="text" value="" class="form-control text-upper-case person-name" name="name"
                                    placeholder="Person Name (of person to be Main Admin) *" maxlength="255" oninput="this.value = this.value.replace(/[^a-zA-Z ]/g, '')">
                                {{-- <div id="lblError" class="text-danger small"></div> --}}
                            </div>
                            <div class="mb-3">
                                <div class="input-group mobile-number-input-group">
                                    <!-- Dropdown for Country Code -->
                                    <select class="form-control country-code" name="country_code">
                                        @if(!empty($country_code))
                                            @foreach($country_code as $k => $v)
                                                <option value="{{ $k }}" {{ $k == 91 ? 'selected' : '' }}>{{ $v }} (+{{ $k }})</option>
                                            @endforeach
                                        @endif
                                        {{-- <option value="355">Albania (+355)</option>
                                        <option value="213">Algeria (+213)</option>
                                        <option value="242">Democratic Republic Of The Congo (+242)</option> --}}
                                        <!-- Add more options as necessary -->
                                    </select>

                                    <!-- Mobile Number Input -->
                                    <input type="text" value="" class="form-control mobile-number" name="mobile" maxlength="20" aria-describedby="user_mobile"
                                        placeholder="Mobile Number *">
                                </div>
                            </div>
                            <div class="mb-3">
                                <input type="email" class="form-control user-email" name="email" placeholder="Email ID *">
                                {{-- <div class="invalid-feedback user-signup-email-error"></div> --}}
                            </div>

                            <div class="mb-3 password-container">
                                <input type="password" class="form-control password-field user-password" name="password" placeholder="Create Password *" maxlength="25">
                                <i class="bi bi-eye-slash show-hide-password"></i>
                                <span class="focus-input100"></span>
                                <p class="help-block error-message">
                                    Password must be minimum 8 characters
                                </p>
                            </div>
                            <div class="mb-3 password-container">
                                <input type="password" class="form-control password-field user-confirm-password" name="password_confirmation" placeholder="Confirm Password *" maxlength="25">
                                <i class="bi bi-eye-slash show-hide-password"></i>
                                <span class="focus-input100"></span>
                            </div>
                            <div class="d-flex gap-2 mb-3 captcha_div">
                                <div class="captcha-img">
                                    <div class=" C_img">
                                        <img src="{{ captcha_src('flat') }}" class="captchaImg" alt="CAPTCHA Image" id="captcha-image">
                                    </div>
                                    <button type="button" class="btn-rfq btn-rfq-primary" id="captcha-refresh-btn">
                                        <i class="bi bi-arrow-repeat" style="font-size: 16px"></i>
                                    </button>
                                </div>
                                <input type="text" name="captcha" class="form-control signup-captcha" placeholder="Enter CAPTCHA" maxlength="4">
                            </div>
                            <div class="form-group" style="display: flex;gap: 10px;">
                                <p class="help-block error-message signup-captcha-error"></p>
                            </div>
                            <div class="row ">
                                <div class="col-sm-12 mt-3">
                                    <button type="submit" class="btn-rfq btn-rfq-primary form-submit-btn">
                                        SUBMIT
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Buyer_submit_popup -->

    <div class="modal fade Buyer_submit_popup" id="user-registration-modal" tabindex="-1" aria-labelledby="exampleModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Registration Alert</h5>
                    <button type="button" data-mdb-button-init data-mdb-ripple-init class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="registeration-confirm-message">Please click proceed only if you are a <strong>steel Manufacturing
                        Unit</strong>. Select Vendor option to register if you are a Vendor to Steel Plants.</div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-primary msg-confirm-btn" href="javascript:void(0)">PROCEED</a>
                    <button type="button" data-mdb-button-init data-mdb-ripple-init class="btn btn-secondary"
                        data-bs-dismiss="modal">CANCEL</button>

                </div>
            </div>
        </div>
    </div>

    <!---local-js-->
    <!-- <script src="{{ asset('public/assets/login/js/common.js') }}"></script> -->
    
    <!----bootsrap-->
    <script src="{{ asset('public/assets/login/bootstrap/js/bootstrap.min.js') }}"></script>

    <script src="{{ asset('public/assets/login/crypto-js/crypto.js') }}"></script>
    <!---fontawesome-->
    {{-- <script src="{{ asset('public/assets/login/fontawesome/js/all.js') }}"></script> --}}

    <!-- Toastr CSS -->
    {{-- <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" /> --}}
    <!-- Toastr JS -->
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script> --}}
    
    <link href="{{ asset('public/assets/library/toastr/css/toastr.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('public/assets/library/toastr/js/toastr.min.js') }}"></script>

    <script>

        $('#loginForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.error').remove();
            // $('#alert').removeClass('alert-danger alert-success').addClass('d-none').text('');

            let mailformat = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            let email = $('#login-email').val();
            let password = $('#login-password').val();
            let remember = $('#remember-me').is(':checked') ? 1 : 2;
            
            let hasErrors = false;

            // Client-side validation
            if (!email) {
                appendError("#login-email", "Please enter Email");
                // $("#login-email").parent().append('<label id="login-email-error" class="error" for="login-email">Please enter Email</label>');
                hasErrors = true;
            } else if (!mailformat.test(email)) {
                appendError("#login-email", "Please enter valid email");
                // $("#login-email").parent().append('<label id="login-email-error" class="error" for="login-email">Please enter valid email</label>');
                hasErrors = true;
            }

            if (!password) {
                appendError("#login-password", "Please enter Password");
                // $("#login-password").parent().append('<label id="login-password-error" class="error" for="login-password">Please enter Password</label>');
                hasErrors = true;
            } else if (password.length < 8) {
                appendError("#login-password", "Password must be at least 8 characters");
                // $("#login-password").parent().append('<label id="login-password-error" class="error" for="login-password">Password must be at least 6 characters</label>');
                hasErrors = true;
            }

            if (hasErrors) return;

            onLoginSubmit();
            let user_password = $('#hiddenkey').val();
            
            $.ajax({
                url: $(this).attr("action"),
                method: "POST",
                data: {
                    email: email,
                    password: user_password,
                    remember: remember,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status) {                        
                        toastr.success(response.message);
                        window.location.href = response.redirect_url;                      
                    } else {
                        toastr.error(response.message);                        
                    }
                },
                error: function(xhr) {
                    // Handle network errors or server errors
                    alert(xhr.responseJSON?.message || 'An error occurred. Please try again.');
                }
            });
        });

        function onLoginSubmit() {
            $("#login-password").parent().find('#login-password-error').remove();
            var pass=$("#login-password").val();
            if(pass.trim().length==0){
               $("#login-password").parent().append('<label id="login-password-error" class="error" for="login-password">Please enter Password</label>');
               return false;
            }
            var CryptoJSAesJson = {
               stringify: function (cipherParams) {
                     var j = {ct: cipherParams.ciphertext.toString(CryptoJS.enc.Base64)};
                     if (cipherParams.iv) j.iv = cipherParams.iv.toString();
                     if (cipherParams.salt) j.s = cipherParams.salt.toString();
                     return JSON.stringify(j);
               },
               parse: function (jsonStr) {
                     var j = JSON.parse(jsonStr);
                     var cipherParams = CryptoJS.lib.CipherParams.create({ciphertext: CryptoJS.enc.Base64.parse(j.ct)});
                     if (j.iv) cipherParams.iv = CryptoJS.enc.Hex.parse(j.iv)
                     if (j.s) cipherParams.salt = CryptoJS.enc.Hex.parse(j.s)
                     return cipherParams;
               }
            }
            var key = '{{ env("AUTH_ENCRYPTION_KEY", "C7zjDVG0fnjVVwjd") }}';
            var encrypted = CryptoJS.AES.encrypt(JSON.stringify(pass), key, {format: CryptoJSAesJson}).toString();
            
            $('#hiddenkey').val(encrypted);
        }

        $('#register-form').on('submit', function(e) {
            e.preventDefault();

            let mailformat = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            let company_name = $('.company-name').val();
            let person_name = $('.person-name').val();
            let country_code = $('.country-code').val();
            let mobile_number = $('.mobile-number').val();
            let email = $('.user-email').val();
            let user_password = $('.user-password').val();
            let user_confirm_password = $('.user-confirm-password').val();
            let signup_captcha = $('.signup-captcha').val();
            let user_type = $("#user_type").val();
            
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

            if (!user_password) {
                $(".signup-password-error").addClass("text-danger");
                hasErrors = true;
            } else if (user_password.length < 8) {
                $(".signup-password-error").addClass("text-danger");
                hasErrors = true;
            }else{
                $(".signup-password-error").removeClass("text-danger");
            }
            if (!user_confirm_password) {
                appendError(".user-confirm-password", "Please enter Confirm Password");
                hasErrors = true;
            } else if (user_password != user_confirm_password) {
                appendError(".user-confirm-password", "Password And Confirm Password Are Not Same!");
                hasErrors = true;
            }else{
                appendError(".user-confirm-password");
            }
            if (!signup_captcha || signup_captcha.length!=4) {
                $(".signup-captcha-error").html('Incorrect CAPTCHA');
                hasErrors = true;
            }else{
                $(".signup-captcha-error").html('');
            }
            
            if (hasErrors) return;
            
            if($("#msg-confirm-type").val()=="no"){
                let confirm_message = "";
                if($("#user_type").val()==1){
                    confirm_message = "Please click proceed only if you are a <b>Steel Manufacturing Unit</b>. Select Vendor option to register if you are a Vendor to Steel Plants.";
                }else{
                    confirm_message = "Please click proceed only if you are a <b>Vendor to Steel Plants</b>. Select the Steel Plant option to register if you are a Steel Manufacturing Unit.";
                }
                $("#registeration-confirm-message").html(confirm_message);
                $("#user-registration-modal").modal('show');
                return false;
            }else{
                $(".form-submit-btn").addClass("disabled").html('<i class="bi spinner-border"></i> Submit');

                $.ajax({
                    url: $(this).attr("action"),
                    method: "POST",
                    data: {
                        user_type: user_type,
                        company_name: company_name,
                        name: person_name,
                        country_code: country_code,
                        mobile: mobile_number,
                        email: email,
                        password: user_password,
                        password_confirmation: user_confirm_password,
                        captcha: signup_captcha,
                        _token: $('meta[name="csrf-token"]').attr('content')
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
                                if (response.errors.password) {
                                    error_messages += ' '+response.errors.password[0];
                                    $(".signup-password-error").addClass("text-danger");
                                }else{
                                    $(".signup-password-error").removeClass("text-danger");
                                }
                                if (response.errors.password_confirmation) {
                                    error_messages += ' '+response.errors.password_confirmation[0];
                                    appendError(".user-confirm-password", response.errors.password_confirmation[0]);
                                }else{
                                    appendError(".user-confirm-password");
                                }
                                if (response.errors.captcha) {
                                    error_messages += ' '+response.errors.captcha[0];
                                    $(".signup-captcha-error").html('Incorrect CAPTCHA');
                                }else{
                                    $(".signup-captcha-error").html('');
                                }
                                toastr.error(error_messages);
                            } else {
                                toastr.error(response.message);
                                if(response.redirect_url){
                                    window.location.href = response.redirect_url;
                                }
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
            }
        });

        $(".msg-confirm-btn").click(function(){
            $("#msg-confirm-type").val('yes');
            $("#user-registration-modal").modal('hide');
            $("#register-form").submit();
        });

        $(document).on("click", ".show-hide-password", function(){
            if($(this).parent().find(".password-field").attr("type")=="text"){
                $(this).removeClass("bi-eye").addClass("bi-eye-slash").parent().find(".password-field").attr("type", "password");
            }else{
                $(this).removeClass("bi-eye-slash").addClass("bi-eye").parent().find(".password-field").attr("type", "text");
            }
        });

        function set_login_type(type) {
            $("#user_type").val(type);
        }

        // CAPTCHA Refresh Functionality
        document.getElementById('captcha-refresh-btn').addEventListener('click', function() {
            // Get the base URL from Laravel (injected in your blade template)
            const baseUrl = '{{ url("/") }}';
            $(this).find("i").toggleClass("spinner-border");

            fetch(`${baseUrl}/captcha/flat?${new Date().getTime()}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to refresh CAPTCHA');
                    }
                    return response.blob();
                })
                .then(blob => {
                    document.getElementById('captcha-image').src = URL.createObjectURL(blob);
                    $(this).find("i").toggleClass("spinner-border");
                })
                .catch(error => {
                    console.error('Error refreshing CAPTCHA:', error);
                    $(this).find("i").toggleClass("spinner-border");
                    // Optionally show error to user
                    alert('Failed to refresh CAPTCHA. Please try again.');
                });
        });

        function refreshCsrfToken() {
            $.ajax({
               url: '{{ route("web.csrf") }}',
               type: 'GET',
               dataType: 'JSON',
               success: function(response) {
                  const newToken = response.csrf;

                  // Update <meta> tag
                  const meta = document.querySelector('meta[name="csrf-token"]');
                  if (meta) {
                     meta.setAttribute('content', newToken);
                  }

                  console.log("CSRF token refreshed:", newToken);
               },
               error: function(xhr, status, error) {
                  alert('An error occurred: ' + xhr.responseText);
               }
            });
        }

        function appendError(obj, msg = '') {
            $(obj).parent().find('.error-message').remove();
            if (msg) {
                $(obj).parent().append('<span class="help-block error-message">' + msg + '</span>');
            }
        }

        // Call it manually whenever needed, called in 2 hour
        setInterval(refreshCsrfToken, 2*60*60*1000);

        // Configure toastr
        // toastr.options = {
        //     closeButton: true,       // Show close button
        //     progressBar: true,       // Show progress bar
        //     positionClass: "toast-top-right",
        //     preventDuplicates: true, // Prevent duplicate toasts
        //     showDuration: "300",     // Animation show duration
        //     hideDuration: "1000",    // Animation hide duration
        //     timeOut: "5000",         // Auto-close after 5 seconds (set to 0 to disable)
        //     extendedTimeOut: "1000", // Additional time if mouse hovers
        //     showEasing: "swing",     // Animation easing
        //     hideEasing: "linear",
        //     showMethod: "fadeIn",    // Animation style
        //     hideMethod: "fadeOut"
        // };

        @if (session()->has('success'))
        toastr.success("{{ session('success') }}");
        @endif
        
        @if (session()->has('error'))
        toastr.error("{{ session('error') }}");
        @endif
    </script>
</body>

</html>
