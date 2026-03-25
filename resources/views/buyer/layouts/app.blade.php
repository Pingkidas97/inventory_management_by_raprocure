<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="description" content="" />
        <meta name="keywords" content="" />
        <meta name="author" content="" />
        <meta property="og:image:size" content="300" />
        <title>{{ $title??'' }} - Raprocure</title>
        <!---favicon-->
		<link rel="shortcut icon" href="{{ asset('public/assets/images/favicon/raprocure-fevicon.ico') }}" type="image/x-icon">
        <!---bootsrap-->
        <link href="{{ asset('public/assets/buyer/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
        <!---bootsrap-icon-->
        <link rel="stylesheet" href="{{ asset('public/assets/buyer/bootstrap-icons/bootstrap-icons.min.css') }}">
        <!---css-->
        <link href="{{ asset('public/assets/buyer/css/layout.css') }}" rel="stylesheet"/>
        <link href="{{ asset('public/assets/buyer/css/style.css') }}" rel="stylesheet">

		<script src="{{ asset('public/assets/login/js/jquery-3.7.1.min.js') }}"></script>

		<meta name="csrf-token" content="{{ csrf_token() }}">
        <style>
            li.suggesation-line a {
                color: #0d71bb;
            }
        </style>
		@yield('css')

    </head>
    <body>
        <!---Header part-->
        <div class="project_header sticky-top">
            @include('buyer.layouts.navigation')
        </div>
        <div class="d-flex">
			@yield('content')            
        </div>
        <!-- Back to top button -->
        <button onclick="scrollToTop()" id="backToTopBtn" class="ra-btn ra-btn-primary px-2 py-1 font-size-20">
            <span>
                <span class="bi bi-arrow-up-short font-size-20" aria-hidden="true"></span>
            </span>
        </button>
        <!---bootsrap-->
        <script src="{{ asset('public/assets/buyer/bootstrap/js/bootstrap.bundle.js') }}"></script>
        <!---local-js-->
        <script src="{{ asset('public/assets/buyer/js/common.js') }}"></script>
        
        {{-- toastr --}}
        <link href="{{ asset('public/assets/library/toastr/css/toastr.min.css') }}" rel="stylesheet" />
        <script src="{{ asset('public/assets/library/toastr/js/toastr.min.js') }}"></script>

        @include('buyer.layouts.app-js')

		@yield('scripts')

    </body>
</html>