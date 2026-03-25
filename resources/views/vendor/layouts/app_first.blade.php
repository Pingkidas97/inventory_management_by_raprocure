<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>{{ $title??'' }} {{ $sub_title??'' }} - Raprocure</title>
  <!---favicon-->
  <link rel="shortcut icon" href="{{ asset('public/assets/vendor/favicon/raprocure-fevicon.ico') }}" type="image/x-icon" />
  <!---Bootsrap CSS and Icons-->
  <link href="{{ asset('public/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('public/assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}" />
  <!---Custom CSS-->
  <link href="{{ asset('public/assets/vendor/css/layout.css') }}" rel="stylesheet" />
  <link href="{{ asset('public/assets/vendor/css/style.css') }}" rel="stylesheet" />
  <link href="{{ asset('public/assets/vendor/css/responsive.css') }}" rel="stylesheet" />
  <!-- jQuery -->
  <script src="{{ asset('public/assets/jQuery/jquery-3.6.0.min.js') }}"></script>
  @stack('styles')
</head>

<body>
  @include('vendor.layouts.navigation')
  
  <!-- Body Section -->
  <div class="d-flex">
    @include('vendor.layouts.sidebar')
    
    <!---Main Content-->
    <main class="main main-dashboard-page flex-grow-1 py-2">
      @yield('content')
    </main>
  </div>

  <!-- Back to top button -->
  <button onclick="scrollToTop()" id="back-to-top-btn" class="ra-btn ra-btn-primary px-2 py-1 font-size-20">
    <span>
      <span class="bi bi-arrow-up-short font-size-20" aria-hidden="true"></span>
    </span>
  </button>
  
  <!---bootsrap-->
  <script src="{{ asset('public/assets/vendor/bootstrap/js/bootstrap.bundle.js') }}"></script>
  <!---local-js-->
  <script src="{{ asset('public/assets/vendor/js/common.js') }}"></script>
  <script>
    function openNav() {
      const sidebar = document.getElementById("mySidebar");
      sidebar.style.transform = "translateX(0)";
      sidebar.classList.add("onClickMenuSidebar");
      window.addEventListener('resize', function () {
        const isMobileView = window.innerWidth <= 768;

        if (!isMobileView) {
          sidebar.classList.remove("onClickMenuSidebar");
          sidebar.removeAttribute("style");
        }
      });
    }

    function closeNav() {
      const sidebar = document.getElementById("mySidebar");
      sidebar.style.transform = "translateX(-115%)";
      sidebar.classList.remove("onClickMenuSidebar");

      let wasMobileView = window.innerWidth <= 768;
      window.addEventListener('resize', function () {
        const isMobileView = window.innerWidth <= 768;

        if (wasMobileView && !isMobileView) {
          openNav();
          sidebar.classList.remove("onClickMenuSidebar");
          sidebar.removeAttribute("style");
        }
        wasMobileView = isMobileView;
      });
    }
  </script>
  @stack('scripts')
</body>
</html>