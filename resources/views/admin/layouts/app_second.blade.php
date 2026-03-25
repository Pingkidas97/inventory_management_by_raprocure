<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="author" content="" />
    <meta property="og:image:size" content="300" />
    <title>{{ $title??'' }} {{ $sub_title??'' }}</title>
    
    <!---favicon-->
    <link rel="shortcut icon" href="{{ asset('public/assets/superadmin/favicon/raprocure-fevicon.ico') }}" type="image/x-icon" />
    <!---bootsrap-->
    <link href="{{ asset('public/assets/superadmin/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
    <!---css-->
    <link href="{{ asset('public/assets/superadmin/css/style.css') }}" rel="stylesheet" />
    <link href="{{ asset('public/assets/superadmin/css/layout.css') }}" rel="stylesheet" />
    <link href="{{ asset('public/assets/superadmin/css/custom.css') }}" rel="stylesheet" />

     <!-- jQuery -->
    <script src="{{ asset('public/assets/jQuery/jquery-3.6.0.min.js') }}"></script>
    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    @yield('css')


</head>

<body>
    @include('admin.layouts.navigation_second')

    <div>
        <a href="javascript:void(0)" class="menubtn" onclick="openNav()">MENU</a>
    </div>
    
    @include('admin.layouts.sidebar_second')

    <div class="main inner-main">
        @yield('breadcrumb')
        @yield('content')
    </div>

    <!---bootsrap-->
    <script src="{{ asset('public/assets/superadmin/bootstrap/js/bootstrap.bundle.js') }}"></script>
    <!---local-js-->
    <script src="{{ asset('public/assets/superadmin/js/common.js') }}"></script>
    <script>
      function limitCharacters(inputField, maxLength) {
          if (inputField.value.length > maxLength) {
              toastr.error(`Character limit exceeded! Maximum ${maxLength} characters allowed.`);
              inputField.value = inputField.value.substring(0, maxLength);
          }
      }
    </script>
    @yield('scripts')
</body>
</html>