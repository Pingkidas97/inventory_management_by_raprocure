<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <meta property="og:image:size" content="300" />
    <title>Raprocure</title>
    <!-- Favicon Configuration for All Devices -->
    <link rel="shortcut icon" href="https://tech.guruworkwithit.online/assets/images/logo/raprocure-fevicon.png"
        type="image/x-icon">
    <!-- Standard Favicon (16x16) -->
    <link rel="icon" type="image/png"
        href="https://tech.guruworkwithit.online/assets/images/logo/raprocure-fevicon-16x16.png" sizes="16x16">
    <!-- Standard Favicon (32x32) -->
    <link rel="icon" type="image/png"
        href="https://tech.guruworkwithit.online/assets/images/logo/raprocure-fevicon-32x32.png" sizes="32x32">

    <!-- Apple Touch Icon (iOS) -->
    <link rel="apple-touch-icon" sizes="180x180"
        href="https://tech.guruworkwithit.online/assets/images/logo/raprocure-fevicon-180x180.png">

    <!-- Android Chrome -->
    <link rel="icon" sizes="192x192"
        href="https://tech.guruworkwithit.online/assets/images/logo/raprocure-fevicon-192x192.png">
    <link rel="icon" sizes="128x128"
        href="https://tech.guruworkwithit.online/assets/images/logo/raprocure-fevicon-128x128.png">

    <!-- Microsoft Tiles -->
    <meta name="msapplication-TileImage"
        content="https://tech.guruworkwithit.online/assets/images/logo/raprocure-fevicon-144x144.png">
    <meta name="msapplication-TileColor" content="#ffffff">

    <!-- Safari Pinned Tab Icon -->
    <link rel="mask-icon" href="https://tech.guruworkwithit.online/assets/images/logo/raprocure-fevicon.svg"
        color="#5bbad5">


    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <!---bootsrap-->
    <link href="{{ asset('public/assets/superadmin/login/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <!---css-->
    <link href="{{ asset('public/assets/superadmin/login/css/style.css')}}" rel="stylesheet">
    <!-- Add this in your HTML head section -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="{{ asset('public/assets/js/lib/customCrypto.js')}}"></script>
</head>

<body>
    <div class="login-12">
        <div class="container-fluid">
            <div class="row">
               <div class="col-md-4 form-info">
                  <div class="form-section">
                     <div class="logo clearfix">
                        <span class="login100-form-logo">
                        <img class="comp_logo_img" src="{{ asset('public/assets/superadmin/login/images/rfq-logo.png')}}" alt="logo">
                        </span>
                     </div>
                     <h3>Sign Into Your Account</h3>
                     <div id="loginForm" class="login-inner-form">
                        <div id="alert" class="alert d-none"></div>
                        <form id="loginform" class="login100-form validate-form login-form " name="authentication"
                           accept-charset="utf-8" method="post">
                           <div class="form-group form-box">
                              <input id="email_address" name="email_address" type="text" class="form-control" placeholder="Email Address" value="">
                              <span class="focus-input100"></span>
                              <i class="bi bi-envelope"></i>
                           </div>
                          <div class="form-group form-box clearfix">
                            <input id="password" type="password" class="form-control" placeholder="Password" aria-label="Password">
                            <input type="hidden" id="hiddenkey" name="password">
                            <span class="focus-input100"></span>
                            <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
                          </div>
                           <div class="checkbox form-group clearfix" id="user_msges">
                              <a href="{{route('admin.forgot-password')}}" class="link-light float-end forgot-password" id="forgot_msg">Forgot your
                              password?</a>
                           </div>
                           <div class="form-group captcha_div">
                              <div class=" captcha-img">
                                 <div class="C_img"><img src="{{ captcha_src('flat') }}" id="captcha-image" alt="captcha"
                                        class="img-fluid rounded border"></div>
                                 <button type="button" class="btn-rfq btn-rfq-primary captcha-refresh-btn border-0"  id="captcha-refresh-btn"><i class="bi bi-arrow-clockwise"></i></button>
                              </div>
                              <!-- bi bi-eye-slash -->
                              <input type="text" name="captcha" class="form-control" placeholder="Enter CAPTCHA" maxlength="4">
                           </div>
                           <div class="form-group">
                              <button type="submit" id="login_submit" name="Submit" class="btn-rfq btn-rfq-primary btn-lg btn-theme"
                                 style="border-radius: 0px;">Login</button>
                           </div>
                        </form>
                     </div>
                  </div>
               </div>
               <div class="col-md-8 rgtBigImg">
                  <img src="{{ asset('public/assets/superadmin/login/images/login-page.png')}}" class="img-fluid">
               </div>
            </div>
         </div>
      </div>
      <!---bootsrap-->
      <script src="{{ asset('public/assets/superadmin/login/bootstrap/js/bootstrap.min.js')}}"></script>
      <!----jquery-->
      <script src="{{ asset('public/assets/superadmin/login/js/jquery.min.js')}}"></script>
      <!-- Toastr CSS -->
      <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
      <!-- Toastr JS -->
      <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

      <script src="{{ asset('public/assets/login/crypto-js/crypto.js') }}"></script>

      <script>
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.error-message').text('');
            $('#alert').removeClass('alert-danger alert-success').addClass('d-none').text('');

            const email = $('#email_address').val();
            const password = $('#password').val();
            const remember = $('#remember').is(':checked') ? 1 : 0;
            const captcha = $('input[name="captcha"]').val();

            let hasErrors = false;

            // Client-side validation
            if (!email) {
                toastr.error('Please enter your email address.');
                hasErrors = true;
            } else if (!/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
                toastr.error('Please enter a valid email address.');
                hasErrors = true;
            }

            if (!password) {
                toastr.error('Please enter your password.');
                hasErrors = true;
            } else if (password.length < 8) {
                toastr.error('Password must be at least 68 characters.');
                hasErrors = true;
            }

            if (!captcha) {
                toastr.error('Please enter the CAPTCHA text.');
                hasErrors = true;
            }

            if (hasErrors) return;

            onLoginSubmit();
            let encryptpassword = $('#hiddenkey').val();
            
            $.ajax({
                url: "{{ route('admin.login.submit') }}",
                async: true, 
                method: "POST",
                data: {
                    email: email,
                    password: encryptpassword,
                    remember: remember,
                    captcha: captcha,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        
                        toastr.success(response.message)
                        window.location.href = response.redirect_url;
                        
                    } else {
                        // Handle server-side validation errors
                        if (response.errors) {
                            if (response.errors.email) {
                                toastr.error(response.errors.email[0]);
                            }
                            if (response.errors.password) {
                                toastr.error(response.errors.password[0]);
                            }
                            if (response.errors.captcha) {
                                toastr.error(response.errors.captcha[0]);
                                refreshCaptcha();
                            }
                        } else {
                            $('#alert').removeClass('d-none').addClass('alert alert-danger')
                                        .text(response.message);
                        }
                    }
                },
                error: function(xhr) {
                    // Handle network errors or server errors
                    $('#alert').removeClass('d-none').addClass('alert alert-danger')
                                .text(xhr.responseJSON?.message || 'An error occurred. Please try again.');
                }
            });
        });

        function onLoginSubmit() {
            $("#password").parent().find('#login-password-error').remove();
            var pass=$("#password").val();
            if(pass.trim().length==0){
               $("#password").parent().append('<label id="login-password-error" class="error" for="password">Please enter Password</label>');
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

        // CAPTCHA Refresh Functionality
        document.getElementById('captcha-refresh-btn').addEventListener('click', function() {
            // Get the base URL from Laravel (injected in your blade template)
            const baseUrl = '{{ url("/") }}';

            fetch(`${baseUrl}/captcha/flat?${new Date().getTime()}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to refresh CAPTCHA');
                    }
                    return response.blob();
                })
                .then(blob => {
                    document.getElementById('captcha-image').src = URL.createObjectURL(blob);
                })
                .catch(error => {
                    console.error('Error refreshing CAPTCHA:', error);
                    // Optionally show error to user
                    alert('Failed to refresh CAPTCHA. Please try again.');
                });
        });

        function limitCharacters(inputField, maxLength) {
            if (inputField.value.length > maxLength) {
                toastr.error(`Character limit exceeded! Maximum ${maxLength} characters allowed.`);
                inputField.value = inputField.value.substring(0, maxLength);
            }
        }

        // CAPTCHA Refresh Functionality
        document.getElementById('captcha-refresh-btn').addEventListener('click', function() {
            // Get the base URL from Laravel (injected in your blade template)
            const baseUrl = '{{ url("/") }}';

            fetch(`${baseUrl}/captcha/flat?${new Date().getTime()}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to refresh CAPTCHA');
                    }
                    return response.blob();
                })
                .then(blob => {
                    document.getElementById('captcha-image').src = URL.createObjectURL(blob);
                })
                .catch(error => {
                    console.error('Error refreshing CAPTCHA:', error);
                    // Optionally show error to user
                    alert('Failed to refresh CAPTCHA. Please try again.');
                });
        });

        function limitCharacters(inputField, maxLength) {
            if (inputField.value.length > maxLength) {
                toastr.error(`Character limit exceeded! Maximum ${maxLength} characters allowed.`);
                inputField.value = inputField.value.substring(0, maxLength);
            }
        }

        // Configure toastr
        toastr.options = {
            closeButton: true, // Show close button
            progressBar: true, // Show progress bar
            positionClass: "toast-top-right",
            preventDuplicates: true, // Prevent duplicate toasts
            showDuration: "300", // Animation show duration
            hideDuration: "1000", // Animation hide duration
            timeOut: "5000", // Auto-close after 5 seconds (set to 0 to disable)
            extendedTimeOut: "1000", // Additional time if mouse hovers
            showEasing: "swing", // Animation easing
            hideEasing: "linear",
            showMethod: "fadeIn", // Animation style
            hideMethod: "fadeOut"
        };

        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');

            if (togglePassword) {
                togglePassword.addEventListener('click', function() {
                    const passwordInput = document.getElementById('password');
                    const isPassword = passwordInput.type === 'password';

                    // Toggle the input type
                    passwordInput.type = isPassword ? 'text' : 'password';

                    // Toggle the eye icon
                    this.classList.toggle('bi-eye');
                    this.classList.toggle('bi-eye-slash');

                    // Optional: Change aria-label for accessibility
                    const label = isPassword ? 'Hide password' : 'Show password';
                    this.setAttribute('aria-label', label);
                });
            }
        });
    </script>
</body>
</html>