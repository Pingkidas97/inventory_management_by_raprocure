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
    <link rel="stylesheet" href="{{ asset('public/assets/css/bootstrap-icons/font/bootstrap-icons.min.css') }}">
    
    <script src="{{ asset('public/assets/login/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('public/assets/js/popper.min.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        .dropdown-menu .dropdown-item, .dropdown-item:hover {
            padding: 2px 10px;
            color: var(--bodycolor) !important;
            background-color: var(--bodycolor);
        }
    </style>
    @yield('css')
</head>

<body>
    <div class="project_header" id="project_header">
        <header class="project_top_header">
            <div class="container-fluid">
                <div class="cust_container">
                    <div class="top_head row align-items-center">
                        <div class="col-lg-4  col-md-6 col-4 d-block">
                            <h5 title="{{session('legal_name')}}">{{ (strlen(session('legal_name')) > 50) ? substr(session('legal_name'), 0, 50) . '...' : session('legal_name') }}</h5>
                        </div>
                        <div class="col-12 col-md-4 d-lg-block d-none">
                            <h4 class="text-center">Welcome to Raprocure!</h4>
                        </div>
                        <div class="col-lg-4 col-md-6 col-8 top-section-right">
                            <p class="text-white show_bothNo">Helpline No.: 9088880077 / 9088844477</p>
                            <div class="user">
                                <div class="dropdown">
                                    <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-toggle" id="user-dropdown" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-person"></i>
                                        <span title="{{Auth::user()->name}}">
                                            {{ (strlen(Auth::user()->name) > 10) ? substr(Auth::user()->name, 0, 10) . '...' : Auth::user()->name }}
                                        </span>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="user-dropdown">
                                        <li><a class="dropdown-item" href="{{ route('user.logout') }}">Logout</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <div class="project_bottom_header">
            <div class="container-fluid">
                <div class="cust_container">
                    <div class="row btm_heada">
                        <div class="col-lg-2 col-md-3 col-sm-4 col-6 navbar-header header-bottom-left">
                            <a class="navbar-brand p-0" href="{{ route("buyer.dashboard") }}">
                                <img alt="raProcure" class="header_img_final" src="{{ asset('public/assets/images/rfq-logo.png') }}">
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
    </div>
    <div class="main">
        <div class="container-fluid">

            @yield('content')
            
        </div>
    </div>

    <!---local-js-->
    <script src="{{ asset('public/assets/buyer/js/common.js') }}"></script>
    <!----bootsrap-->
    <script src="{{ asset('public/assets/buyer/bootstrap/js/bootstrap.min.js') }}"></script>
    
    {{-- toastr --}}
    <link href="{{ asset('public/assets/library/toastr/css/toastr.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('public/assets/library/toastr/js/toastr.min.js') }}"></script>

    @yield('scripts')
    
</body>

</html>