<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- jQuery -->
    <script src="{{ asset('public/assets/jQuery/jquery-3.6.0.min.js') }}"></script>
    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- Add to your <head> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href='https://fonts.googleapis.com/css?family=DM Sans' rel='stylesheet'>
    <script src="{{ asset('public/assets/js/lib/customCrypto.js')}}"></script>
    <style>
        body {
            font-family: 'DM Sans';
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title text-center mb-4">Login</h4>

                    <div id="alert" class="alert d-none"></div>

                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email ID</label>
                            <input type="email" class="form-control" id="email" name="email" oninput="limitCharacters(this,255)">
                            <span id="email-error" class="error-message"></span>
                        </div>

                        <div class="mb-3 position-relative">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" 
                                    class="form-control" 
                                    id="password" 
                                    name="password" 
                                    oninput="limitCharacters(this,255)">
                                <button class="btn btn-outline-secondary toggle-password" 
                                        type="button"
                                        aria-label="Show password">
                                    <i class="bi bi-eye-slash"></i> <!-- Bootstrap Icons -->
                                </button>
                            </div>
                            <span id="password-error" class="error-message"></span>
                        </div>

                        <style>
                            .toggle-password {
                                border-top-left-radius: 0;
                                border-bottom-left-radius: 0;
                                border-left: none;
                            }
                            .toggle-password:hover {
                                background-color: #f8f9fa;
                            }
                        </style>

                        <script>
                            document.querySelectorAll('.toggle-password').forEach(button => {
                                button.addEventListener('click', function() {
                                    const passwordInput = this.previousElementSibling;
                                    const icon = this.querySelector('i');
                                    
                                    if (passwordInput.type === 'password') {
                                        passwordInput.type = 'text';
                                        icon.classList.replace('bi-eye-slash', 'bi-eye');
                                        this.setAttribute('aria-label', 'Hide password');
                                    } else {
                                        passwordInput.type = 'password';
                                        icon.classList.replace('bi-eye', 'bi-eye-slash');
                                        this.setAttribute('aria-label', 'Show password');
                                    }
                                });
                            });
                        </script>

                        <div class="mb-3">
                            <label class="form-label">CAPTCHA Verification</label>
                            <div class="row align-items-center g-2 mb-2">
                                <div class="col-md-6">
                                    <img src="{{ captcha_src('flat') }}" id="captcha-image" alt="captcha"
                                        class="img-fluid rounded border">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="captcha" oninput="limitCharacters(this,10)">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" id="refresh-captcha" class="btn btn-outline-secondary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd"
                                                d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z" />
                                            <path
                                                d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <span id="captcha-error" class="error-message"></span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <a href="/forgot-password">Forgot your password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.error-message').text('');
            $('#alert').removeClass('alert-danger alert-success').addClass('d-none').text('');

            const email = $('#email').val();
            const password = $('#password').val();
            const remember = $('#remember').is(':checked') ? 1 : 0;
            const captcha = $('input[name="captcha"]').val();

            let hasErrors = false;

            // Client-side validation
            if (!email) {
                $('#email-error').text('Please enter your email address.');
                hasErrors = true;
            } else if (!/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
                $('#email-error').text('Please enter a valid email address.');
                hasErrors = true;
            }

            if (!password) {
                $('#password-error').text('Please enter your password.');
                hasErrors = true;
            } else if (password.length < 6) {
                $('#password-error').text('Password must be at least 6 characters.');
                hasErrors = true;
            }

            if (!captcha) {
                $('#captcha-error').text('Please enter the CAPTCHA text.');
                hasErrors = true;
            }

            if (hasErrors) return;
            const encryptpassword = CustomCrypto.encrypt(password);
            
            $.ajax({
                url: "{{ route('admin.login.submit') }}",
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
                        $('#alert').removeClass('d-none').addClass('alert alert-success')
                                    .text(response.message);
                        setTimeout(() => {
                            window.location.href = response.redirect_url;
                        }, 1000);
                    } else {
                        // Handle server-side validation errors
                        if (response.errors) {
                            if (response.errors.email) {
                                $('#email-error').text(response.errors.email[0]);
                            }
                            if (response.errors.password) {
                                $('#password-error').text(response.errors.password[0]);
                            }
                            if (response.errors.captcha) {
                                $('#captcha-error').text(response.errors.captcha[0]);
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
        
        // Clear error when user starts typing
        $('#email, #password').on('input', function() {
            const id = $(this).attr('id');
            $(`#${id}-error`).text('');
        });

        // CAPTCHA Refresh Functionality
        document.getElementById('refresh-captcha').addEventListener('click', function() {
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
            closeButton: true,       // Show close button
            progressBar: true,       // Show progress bar
            positionClass: "toast-top-right",
            preventDuplicates: true, // Prevent duplicate toasts
            showDuration: "300",     // Animation show duration
            hideDuration: "1000",    // Animation hide duration
            timeOut: "5000",         // Auto-close after 5 seconds (set to 0 to disable)
            extendedTimeOut: "1000", // Additional time if mouse hovers
            showEasing: "swing",     // Animation easing
            hideEasing: "linear",
            showMethod: "fadeIn",    // Animation style
            hideMethod: "fadeOut"
        };          
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>