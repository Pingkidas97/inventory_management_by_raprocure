<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ??'' }} || Admin Panel</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Custom Admin CSS -->
    <link href="{{ asset('public/assets/css/admin.css') }}" rel="stylesheet">

    <link href='https://fonts.googleapis.com/css?family=DM Sans' rel='stylesheet'>
    <!-- jQuery -->
    <script src="{{ asset('public/assets/jQuery/jquery-3.6.0.min.js') }}"></script>
    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <style>
        body {
            font-family: 'DM Sans';
        }
    </style>
    @yield('css')
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        @include('admin.layouts.sidebar')
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Top Navigation -->
            @include('admin.layouts.navigation')
            <!-- Main Content -->
            <div class="container-fluid px-4">
                @yield('content')
            </div>
        </div>
    </div>
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- Custom Admin JS -->
    <script src="{{ asset('public/assets/js/admin.js') }}"></script>
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