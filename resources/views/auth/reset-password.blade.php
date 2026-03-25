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
    <title>Reset password - Raprocure</title>
    <!---css-->
    <link href="{{ asset('public/assets/login/css/style.css') }}" rel="stylesheet">

    <!---bootsrap-->
    <link href="{{ asset('public/assets/login/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Add this in your HTML head section -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="{{ asset('public/assets/login/js/jquery-3.7.1.min.js') }}"></script>
    <style>
    label.error,
    .error-message {
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
    <div class="project_header" id="project_header">
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
                            <img alt="logo" class="header_img_final" src="{{ asset('public/assets/images/rfq-logo.png') }}">
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
                <div class="col-md-12 col-12 d-flex login-account-page">
                    <div class="login">
                        <h3>Reset Password</h3>
                        <!-- Login Form -->
                        <form class="makeForm my-4" action="{{ route('reset-password.submit') }}" method="POST"
                            id="loginForm">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
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
                            <button type="submit" class="btn-rfq btn-rfq-primary login-buyer-vendor">Submit</button>
                        </form>
                        <h5>Please Click here to <a class="theme-color" href="">Login</a> </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!---local-js-->
    <script src="{{ asset('public/assets/login/js/common.js') }}"></script>

    <!----bootsrap-->
    <script src="{{ asset('public/assets/login/bootstrap/js/bootstrap.min.js') }}"></script>

    <script src="{{ asset('public/assets/login/crypto-js/crypto.js') }}"></script>
    <!---fontawesome-->

    <link href="{{ asset('public/assets/library/toastr/css/toastr.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('public/assets/library/toastr/js/toastr.min.js') }}"></script>

    <script>
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        // Clear previous errors
        $('.error').remove();
        $.ajax({
            url: $(this).attr("action"),
            method: "POST",
            data: new FormData(this), // Use FormData for consistency with native JS
            processData: false, // Don't process the data
            contentType: false, // Don't set content type (FormData does it)
            success: function(response) {
                if (response.status) {
                    toastr.success(response.message);
                    window.location.reload();
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

    $(document).on("click", ".show-hide-password", function() {
        if ($(this).parent().find(".password-field").attr("type") == "text") {
            $(this).removeClass("bi-eye").addClass("bi-eye-slash").parent().find(".password-field").attr("type",
                "password");
        } else {
            $(this).removeClass("bi-eye-slash").addClass("bi-eye").parent().find(".password-field").attr("type",
                "text");
        }
    }); 
    @if(session() - > has('success'))
    toastr.success("{{ session('success') }}");
    @endif

    @if(session() - > has('error'))
    toastr.error("{{ session('error') }}");
    @endif
    </script>
</body>

</html>