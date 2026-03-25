<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <meta property="og:image:size" content="300" />
    <link rel="shortcut icon" href="{{ asset('public/assets/superadmin/favicon/raprocure-fevicon.ico')}}"
        type="image/x-icon">
    <title>Reset password-Raprocure</title>

    <!---css-->
    <style>
        /****forget-password***/
        .forgetPassword_page .form-forget {
            max-width: 395px;
            margin: 0 auto;
            text-align: center;
            width: 100%;
        }

        .forgetPassword_page .form-forget h3 {
            font-size: 21px;
            margin-bottom: 40px;
            color: #040404;
            font-weight: 400;
        }

        .forgetPassword_page .form-detail {
            min-height: 100vh;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px 15px;
        }

        .form-logo {
            height: 65px;
            width: 260px;
            margin: 0 auto 20px auto;
        }

        .forgetPassword_page .form-logo-img {
            width: 100%;
            height: 100%;

        }

        .forgetPassword_page .inner-form {
            color: #cccccc;
            position: relative;
            width: 100%;
        }

        .forgetPassword_page .inner-form .form-field {
            width: 100%;
            position: relative;
            margin-bottom: 25px;
        }

        .forgetPassword_page .inner-form .form-control {
            border-radius: 0px !important;
            font-size: 16px;
            color: #535353;
            font-weight: 500;
            border: 1px solid #dcdcdc;
            background: #fff;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.2);
            height: 45px;
            padding: 12px 45px 12px 20px;
        }

        .forgetPassword_page .inner-form .form-field svg {
            position: absolute;
            top: 12px;
            right: 20px;
            font-size: 18px;
            color: #535353;
        }

        .primary_btn {
            background: #015294;
            line-height: 50px;
            width: 100%;
            text-transform: uppercase;
            font-size: 15px;
            font-weight: 500;
            color: #fff;
            text-align: center;
            border: none;
        }

        .count_first {
            width: 10%;
            border: none;
        }

        .count_snd {
            width: 10%;
            border: none;
            padding-left: 14px;
        }

        .count_output {
            width: 20%;
            margin-left: 15px;
        }

        .form-field span {
            color: black;
            font-weight: 400;
        }

        .forgetPassword_page p {
            margin: 25px 0 0;
            font-size: 16px;
            color: #535353;
        }

        .loginLink {
            text-decoration: none;
            color: #535353;
        }

        .rgtBigImg {
            padding: 0px !important;
        }

        .rgtBigImg img {
            background-size: cover;
            width: 100% !important;
            opacity: 0.9;
            height: 100%;
        }

        /***responsive***/

        @media (max-width: 991px) {
            .rgtBigImg {
                display: none;
            }
        }
    </style>

    <!---bootsrap-->
    <link href="{{ asset('public/assets/superadmin/login/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">

    <!---fontawesome-icon-->
    <link href="{{ asset('public/assets/superadmin/fontawesome/css/all.css')}}">
    <!-- AlertifyJS CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>

    <!-- AlertifyJS Default theme (optional) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>

    <!-- AlertifyJS JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

</head>

<body>

    <div class="forgetPassword_page">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-4 col-md-12 col-12 form-detail">
                    <div class="form-forget">
                        <div class="form-logo">
                            <img class="form-logo-img img-fluid" src="{{ asset('public/assets/superadmin/images/rfq-logo.png')}}" alt="logo">
                        </div>
                        <h3>Reset Your Password</h3>
                        <div class="inner-form">
                            <form action="{{ route('admin.reset-password.submit') }}" method="post" name="form-forget" id="form-forget">
                                @csrf
                                <input type="hidden" name="token" value="{{$token}}">
                                <div class="form-field">
                                    <input name="password" type="password" class="form-control" placeholder="Please enter new password">
                                    <i class="fa-regular fa-envelope"></i>
                                </div>
                                 <div class="form-field">
                                    <input name="confirm_password" type="password" class="form-control" placeholder="Please enter confirm password">
                                    <i class="fa-regular fa-envelope"></i>
                                </div>
                                <div class="form-field">
                                    <button type="submit" class="primary_btn">Forgot</button>
                                </div>
                            </form>
                        </div>
                        <p>Already a member? <a href="{{route('admin.login')}}" class="loginLink"> Login here</a></p>
                    </div>
                </div>
                <div class="col-lg-8 col-md-12 col-12 rgtBigImg">
                    <img src="{{ asset('public/assets/superadmin/images/login-page.png')}}" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <!---bootsrap-->
    <script src="{{ asset('public/assets/superadmin/login/bootstrap/js/bootstrap.min.js')}}"></script>
    <!----fontawesome-->
    <script src="{{ asset('public/assets/superadmin/fontawesome/js/all.js')}}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        alertify.set('notifier','position', 'bottom-left');
        $('#form-forget').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: $(this).attr('action'),
                method: "POST",
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                success: function(response) {
                    if (response.status) {
                        alertify.success(response.message);
                         window.location.reload();
                    } else {
                        alertify.error(response.message);
                    }
                }
            });
        });
    </script>   
</body>

</html>