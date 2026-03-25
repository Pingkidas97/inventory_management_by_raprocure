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

    <title>{{ $title??'' }} - Raprocure</title>

    <!---css-->
    <link href="{{ asset('public/assets/buyer/css/style.css') }}" rel="stylesheet">

    <!---bootsrap-->
    <link href="{{ asset('public/assets/buyer/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    <script src="{{ asset('public/assets/login/js/jquery-3.7.1.min.js') }}"></script>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield('css')

</head>

<body>
    <div class="project_header" id="project_header">
        <header class="P_header">
            <div class="container-fluid">
                <div class="cust_container">
                    <div class="top_head row align-items-center">
                        <div class="col-lg-4  col-md-6 col-4 d-block">
                            <h5>{{session('legal_name')}}</h5>
                        </div>
                        <div class="col-12 col-md-4 d-lg-block d-none">
                            <h4 class="text-center">Welcome to Raprocure!</h4>
                        </div>
                        <div class="col-lg-4  col-md-6 col-8 top-section-right">
                            <p class="text-white show_bothNo">Helpline No.: 9088880077 / 9088844477</p>
                            <div class="user">
                                <a href="#" class="d-flex align-items-center gap-2" onclick="setLogout(event)">
                                    <i class="fa-solid fa-user"></i>
                                    <span>Jhon Doe
                                        <i class="fa-solid fa-angle-down ms-1"></i></span>
                                </a>
                                <div class="user_logout" id="user_logout" style="display: none;"><a href="#">Logout</a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <div class="header" id="myHeader">
            <div class="container-fluid">
                <div class="cust_container">
                    <div class="row btm_heada">

                        <div class="col-lg-2 col-md-3 col-sm-4 col-6 navbar-header  header-bottom-left">
                            <a class="navbar-brand p-0" href="#">
                                <img alt=" " class="header_img_final" src="{{ asset('public/assets/images/rfq-logo.png') }}">
                            </a>
                        </div>
                        <div class="col-lg-8 col-md-7 col-sm-6 col-12 header-bottom-right "></div>
                        <div class="col-lg-2 col-md-2 col-sm-2 col-6 globle-header-icons "></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="main" id="main">
        <div class="container-fluid">
            
            @yield('content')

        </div>
    </div>

    <!---local-js-->
    <script src="{{ asset('public/assets/vendor/js/common.js') }}"></script>

    <!----bootsrap-->
    <script src="{{ asset('public/assets/buyer/bootstrap/js/bootstrap.min.js') }}"></script>

    {{-- toastr --}}
    <link href="{{ asset('public/assets/library/toastr/css/toastr.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('public/assets/library/toastr/js/toastr.min.js') }}"></script>

    @yield('scripts')

</body>

</html>