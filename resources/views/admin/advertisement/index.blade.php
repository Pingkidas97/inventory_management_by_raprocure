@extends('admin.layouts.app_second', ['title' => 'Manage Advertisement', 'sub_title' => 'List'])
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.advertisement.index') }}"> -> Advertisement Module </a>
        </h5>
    </div>
</div>
@endsection
@section('content')
<div class="about_page_details">
    <div class="container-fluid">
        <div class="card border-0">
            <div class="card-body">
                <div class="col-md-12 botom-border">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="">Manage Advertisements</h3>
                        <a href="{{ route('admin.advertisement.create') }}" class="btn-rfq btn-rfq-white">
                            <i class="bi bi-plus"></i> Add 
                        </a>
                    </div>
           
                    <div id="table-container">
                        @include('admin.advertisement.partials.table', ['advertisements' => $advertisements])
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
$(document).ready(function() {
  

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        loadTable($(this).attr('href'));
    });

    // Handle perPage change
    $(document).on('change', '#perPage', function() {
        let perPage = $(this).val();

        // Build URL directly using the route
        let baseUrl = "{{ route('admin.advertisement.index') }}";
        let url = baseUrl + '?per_page=' + perPage;

        loadTable(url);
    });

    function loadTable(url) {
        $.ajax({
            url: url,
            type: 'GET',
            beforeSend: function() {
                $('#table-container').html('<div class="text-center py-4">Loading...</div>');
            },
            success: function(response) {
                $('#table-container').html(response);
                if (history.pushState) {
                    history.pushState(null, null, url);
                }
            }
        });
    }
 
});
</script>
@endsection


