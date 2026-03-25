@extends('admin.layouts.app',['title' => 'Buyer','sub_title' => 'All User'])
@section('css')
<style>
    .pagination .page-link { border-radius: 8px; margin: 0 3px; font-size: 16px; color: #3b82f6; }
    .pagination .active .page-link { background-color: #3b82f6; color: white; border-color: #3b82f6; }
</style>
@endsection
@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4 mt-4">
        <div class="card-header py-3 d-flex justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">All User</h6>
        </div>
        <div class="card-body">
            <form id="searchForm" action="{{ route('admin.vendor.user',['id'=>$id]) }}" method="GET">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <input type="text" name="name" class="form-control" value="{{ request('name') }}" placeholder="Search By Name">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="email" class="form-control" value="{{ request('email') }}" placeholder="Search By Email">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="mobile" class="form-control" value="{{ request('mobile') }}" placeholder="Search By Contact">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                      <div class="form-group">
                        <button type="submit" class="btn btn-sm btn-primary">SEARCH</button>
                        <a href="{{ route('admin.vendor.user', ['id'=>$id]) }}" class="btn btn-sm btn-secondary ml-2">RESET</a>
                        <a href="{{ route('admin.vendor.index') }}" class="btn btn-sm btn-secondary ml-2">BACK</a>
                      </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive" id="table-container">
                @include('admin.vendor.partials.user-table', ['results'=>$results])
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $(document).on('submit', '#searchForm', function(e) {
        e.preventDefault();
        loadTable($(this).attr('action') + '?' + $(this).serialize());
    });

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        loadTable($(this).attr('href'));
    });

    // Handle perPage dropdown change
    $(document).on('change', '#perPage', function () {
        const form = $('#searchForm');
        const formData = form.serialize(); // Get current search filters
        const perPage = $(this).val();
        const url = form.attr('action') + '?' + formData + '&per_page=' + perPage;

        loadTable(url);
    });

    function loadTable(url) {
        $.ajax({
            url: url,
            type: 'GET',
            beforeSend: function () {
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
