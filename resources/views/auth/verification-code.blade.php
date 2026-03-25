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
    <title>Verify the Verification Code - Raprocure</title>
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
        .spinner-border{
            height: 14px;
            width: 14px;
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
                        <div class="col-lg-4 col-md-6 col-12 top-section-right">
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
    <div class="main " id="main">
        <div class="container-fluid">
            <div class="py-4 verify_pad">
                <div class="verify_page bg-white my-4">
                    <div class="mx-auto d-table">
                        <form action="{{ route('register.verify-verification-code') }}" method="POST" id="verificationForm">
                            @csrf
                            <h3 class="">Verify the Verification Code </h3>
                            <p class="text-left">Check your Email for Verification Code</p>

                            <div class="input-group mb-3 enter_code_input">
                                <input type="text" class="form-control" placeholder="Enter Verification Code" name="verification_code" id="verification-code"
                                    maxlength="6" minlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                <a href="javascript:void(0);" class="input-group-text resend" id="resend_otp">
                                    Resend 
                                    <span class="ms-2" id="timer"></span>
                                </a>
                            </div>

                            <div class="row">
                                <div class="col-sm-12 verify_btn">
                                    <button type="submit" class="btn-rfq btn-rfq-primary mt-10 mx-auto d-table form-submit-btn">
                                       Submit
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!---local-js-->
    <script src="{{ asset('public/assets/login/js/common.js') }}"></script>
    
    <!----bootsrap-->
    <script src="{{ asset('public/assets/login/bootstrap/js/bootstrap.min.js') }}"></script>

    <!---fontawesome-->
    {{-- <script src="{{ asset('public/assets/login/fontawesome/js/all.js') }}"></script> --}}

    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>

        $('#verificationForm').on('submit', function(e) {
            e.preventDefault();

            let verification_code = $('#verification-code').val();

            // Client-side validation
            if (!verification_code) {
                toastr.error("Please enter Verification Code");
                return false;
            }else if (verification_code.length!=6) {
                toastr.error("Incorrect Verification Code");
                return false;
            }
            $(".form-submit-btn").addClass("disabled").html('<i class="bi spinner-border"></i> Submit');
            $.ajax({
                url: $(this).attr("action"),
                method: "POST",
                data: {
                    verification_code: verification_code,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status) {                        
                        // toastr.success(response.message);
                        window.location.href = response.redirect_url;                      
                    } else {
                        if(response.message){
                            toastr.error(response.message);
                        }
                        if (response.errors && response.errors.verification_code) {
                            toastr.error(response.errors.verification_code[0]);
                        }
                        if(response.redirect_url){
                            window.location.href = response.redirect_url;
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
        

        const countdownTime = 3 * 60; // 3 minutes in seconds
        const cookieKey = 'otp_timer_start';

        // Helper: Set a cookie
        function setCookie(name, value, seconds) {
            const expires = new Date(Date.now() + seconds * 1000).toUTCString();
            document.cookie = `${name}=${value}; expires=${expires}; path=/`;
        }

        // Helper: Get a cookie
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
        }

        document.addEventListener("DOMContentLoaded", function () {
            const timerElement = document.getElementById("timer");
            const resendButton = document.getElementById("resend_otp");

            // Get or set start time
            let startTime = getCookie(cookieKey);
            if (!startTime) {
                startTime = Math.floor(Date.now() / 1000); // current time in seconds
                setCookie(cookieKey, startTime, countdownTime); // save start time
            }

            const now = Math.floor(Date.now() / 1000);
            let timeRemaining = countdownTime - (now - startTime);

            if (timeRemaining <= 0) {
                timerElement.textContent = "";
                $('#resend_otp').removeClass('disabled');
                return;
            }

            // Format time MM:SS
            function formatTime(seconds) {
                const minutes = Math.floor(seconds / 60);
                const secs = seconds % 60;
                return `${minutes}:${secs < 10 ? "0" : ""}${secs}`;
            }

            // Start countdown
            const timerInterval = setInterval(() => {
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    timerElement.textContent = "";
                    $('#resend_otp').removeClass('disabled');
                    document.cookie = `${cookieKey}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`; // delete cookie
                    return;
                }

                timerElement.textContent = `(In: ${formatTime(timeRemaining)})`;
                $('#resend_otp').addClass('disabled');
                timeRemaining--;
            }, 1000);
        });

        $('#resend_otp').on('click', function(e) {
            
            let startTime = getCookie(cookieKey);

            const now = Math.floor(Date.now() / 1000);
            let timeRemaining = countdownTime - (now - startTime);

            if (timeRemaining > 0) {
                console.log("time is remaining");
                return;
            }
            
            $(".form-submit-btn").addClass("disabled");
            $(this).addClass("disabled");

            $.ajax({
                url: '{{ route("register.resend-verification-code") }}',
                method: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status) {                        
                        toastr.success(response.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);                      
                    } else {
                        if(response.message){
                            toastr.error(response.message);
                        }
                        if(response.redirect_url){
                            window.location.href = response.redirect_url;
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

        @if (session()->has('success'))
        toastr.success("{{ session('success') }}");
        @endif
        
        @if (session()->has('error'))
        toastr.error("{{ session('error') }}");
        @endif
    </script>
</body>

</html>
