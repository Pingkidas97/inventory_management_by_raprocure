<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="keywords" content="">
        <meta name="author" content="">
        <meta property="og:image:size" content="300" />
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', session('page_title', 'Raprocure'))</title>
        <!---favicon-->
        <link rel="shortcut icon" href="{{ asset('public/assets/images/favicon/raprocure-fevicon.ico') }}"
            type="image/x-icon">
         <!---bootsrap-->
        <link href="{{ asset('public/assets/buyer/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
        <!---bootsrap-icon-->
        <link rel="stylesheet" href="{{ asset('public/assets/buyer/bootstrap-icons/bootstrap-icons.min.css') }}">
        {{-- toastr --}}
        <link href="{{ asset('public/assets/library/toastr/css/toastr.min.css') }}" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.css') }}" />
        <!-- Custom css -->
        <link href="{{ asset('public/assets/inventoryAssets/css/style.css') }}" rel="stylesheet">
        <link href="{{ asset('public/assets/inventoryAssets/fontawesome/css/all.css') }}" rel="stylesheet">
        <!-- Custom css -->
        <link href="{{ asset('public/assets/inventoryAssets/css/layout.css') }}" rel="stylesheet" />

        <!-- jQuery (Must be first) -->
        <script src="{{ asset('public/assets/jQuery/jquery-3.6.0.min.js') }}"></script>
        
        <!-- DataTables CSS -->
         <link rel="stylesheet"  href="{{ asset('public/assets/dataTables/jquery.dataTables.min.css') }}" />

        
        <!-- jQuery Validation Plugin -->        
        <script src="{{ asset('public/assets/jQuery/jquery.validate.min.js') }}"></script>
        <style>
            .row.btm_heada {
                display: none;
            }
            #global-loader {
                position: fixed;
                width: 100%;
                height: 100%;
                background: #ffffff;
                top: 0;
                left: 0;
                z-index: 999999;
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 1;
                transition: opacity .2s ease-in-out, visibility .2s ease-in-out;
                visibility: visible;
            }

            /* Hidden State */
            #global-loader.hide {
                opacity: 0;
                visibility: hidden;
            }

       
        .spinner {
            width: 60px;
            height: 60px;
            border: 6px solid #ddd;
            border-top: 6px solid #0d71bb;
            border-radius: 50%;
            animation: spin 1.8s linear infinite;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }
        </style>

        @stack('styles')
        @stack('headJs')
    </head>

    <body>
        <!---Default Loader-->
        <div id="global-loader">
            <div class="spinner"></div>
        </div>
        <!---Header part-->

        <div class="project_header sticky-top">
            @include('buyer.layouts.navigation')
        </div>

        <div class="d-flex">
            <!-- Section Sidebar -->
            <div class="bg-white">
                @include('buyer.layouts.sidebar-menu')
            </div>

            <!---Section Main-->
            <main class="main flex-grow-1">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </main>



        </div>

        <!-- Back to top button -->
        <button onclick="scrollToTop()" id="backToTopBtn" class="ra-btn ra-btn-primary px-2 py-1 font-size-20">
            <span>
                <span class="bi bi-arrow-up-short font-size-20" aria-hidden="true"></span>
            </span>
        </button>
         <!---bootsrap-->
        <script src="{{ asset('public/assets/buyer/bootstrap/js/bootstrap.bundle.js') }}"></script>
        <script src="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.full.min.js') }}"></script>
        <script src="{{ asset('public/assets/library/toastr/js/toastr.min.js') }}"></script>

        
        <!-- DataTables JS -->
        <script src="{{ asset('public/assets/dataTables/jquery.dataTables.min.js') }}"></script>

        <!-- FontAwesome JS -->
        <script src="{{ asset('public/assets/inventoryAssets/fontawesome/js/all.js') }}"></script>

       
        <!-- XLSX excel JS -->
        <script src="{{ asset('public/assets/xlsx/xlsx.full.min.js') }}"></script>


        <!-- Custom JS -->
        <script src="{{ asset('public/assets/inventoryAssets/js/script.js') }}"></script>
        <script src="{{ asset('public/js/manuallyAcceptPasteLogic.js') }}"></script>
        <script src="{{ asset('public/assets/inventoryAssets/js/common.js') }}"></script>

        <script>
            $(document).ready(function() {
            @if(session('success'))
                toastr.success("{{ session('success') }}");
            @elseif(session('error'))
                toastr.error("{{ session('error') }}");
            @elseif(session('warning'))
                toastr.warning("{{ session('warning') }}");
            @elseif(session('info'))
                toastr.info("{{ session('info') }}");
            @endif
        });
        </script>
        <!-- for loader -->
        <script>
            window.addEventListener("load", function () {
                setTimeout(function () {
                    if($("#global-loader").length > 0){
                        document.getElementById("global-loader").classList.add("hide");
                    }
                }, 200); // delay before fade starts
            });
        </script>
        <!-- for loader -->
        <script>
            function openNav() {
        const sidebar = document.getElementById("mySidebar");
        sidebar.style.transform = "translateX(0)";
        sidebar.classList.add("onClickMenuSidebar"); // Add 'open' class
      }

      function closeNav() {
        const sidebar = document.getElementById("mySidebar");
        sidebar.style.transform = "translateX(-115%)";
        sidebar.classList.remove("onClickMenuSidebar"); // Remove 'open' class

        let wasMobileView = window.innerWidth <= 768;
        window.addEventListener('resize', function () {
          const isMobileView = window.innerWidth <= 768;
          if (wasMobileView && !isMobileView) {
            closeNav();
          }
          wasMobileView = isMobileView;
        });
      }

        window.routes = {
            checkPermission: "{{ route('buyer.inventory.checkPermission') }}"
        };
        window.addEventListener("load", function () {
                setTimeout(function () {
                    document.getElementById("global-loader").classList.add("hide");
                }, 200); // delay before fade starts
            });
        </script>
        @yield('scripts')
        @stack('exJs')
    </body>

</html>
