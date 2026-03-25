@extends('admin.layouts.app_second', ['title' => 'Division','sub_title' => 'List'])
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.divisions.index') }}"> -> Division List</a>
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
                    <form id="searchForm" method="GET" action="{{ route('admin.divisions.index') }}">
                        <h3>Division</h3>
                        <div class="row">
                            <div class="col-xl-8 col-lg-8 col-md-12">
                                <ul class="rfq-filter-button">
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="text" class="form-control fillter-form-control" name="division_name" value="{{ request('division_name') }}" placeholder="Division Name">
                                                <label for="division_name">Division Name</label>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-record2"></i></span>
                                            <div class="form-floating">
                                                <select name="status" class="form-select fillter-form-select">
                                                    <option value="">Select</option>
                                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                                <label for="status">Status</label>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="notShow_on_mobile">
                                        <button type="submit" class="btn-style btn-style-primary"><i class="bi bi-search"></i> Search</button>
                                    </li>
                                    <li class="notShow_on_mobile">
                                        <a href="{{ route('admin.divisions.index') }}" class="btn-style btn-style-danger">RESET</a>
                                    </li>
                                    <li class="notShow_on_mobile">
                                        <a href="{{ route('admin.divisions.create') }}" class="btn-style btn-style-white"><i class="bi bi-plus-square"></i> ADD DIVISION</a>
                                    </li>
                                </ul>
                                <ul class="rapo_btn-grp">
                                    <li>
                                        <button type="submit" class="btn-style btn-style-primary"><i class="bi bi-search"></i> Search</button>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.divisions.index') }}" class="btn-style btn-style-danger">RESET</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.divisions.create') }}" class="btn-style btn-style-white"><i class="bi bi-plus-square"></i> ADD DIVISION</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive product_listing_table_wrap" id="table-container">
                        @include('admin.divisions.partials.table', ['divisions' => $divisions])
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
        // Division status toggle
        $(document).on('change', '.division-status-toggle', function() {
            const divisionId = $(this).data('id');
            const isActive = $(this).is(':checked') ? '1' : '2';
            
            $.ajax({
                url: "{{ route('admin.divisions.updateStatus', ['id' => ':id']) }}".replace(':id', divisionId),
                type: "PUT",
                data: {
                    _token: "{{ csrf_token() }}",
                    status: isActive
                },
                success: function(response) {
                    if(response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                        // Revert the toggle if there was an error
                        $(this).prop('checked', !$(this).prop('checked'));
                    }
                },
                error: function(xhr) {
                    toastr.error('An error occurred. Please try again.');
                    // Revert the toggle if there was an error
                    $(this).prop('checked', !$(this).prop('checked'));
                }
            });
        });
        
        // AJAX pagination and search
        $(document).on('submit', '#searchForm', function(e) {
            e.preventDefault();
            loadTable($(this).attr('action') + '?' + $(this).serialize());
        });
        
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();

            const url = new URL($(this).attr('href'), window.location.origin);
            const page = url.searchParams.get("page");

            // Get the current search form parameters
            const searchParams = $('#searchForm').serialize();

            // Construct the new URL with filters + page
            const fullUrl = "{{ route('admin.divisions.index') }}" + '?' + searchParams + '&page=' + page;

            loadTable(fullUrl);
        });

        
        function loadTable(url) {
            $('.loading-spinner').show();
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    $('#table-container').html(response);
                    $('.loading-spinner').hide();
                    if (history.pushState) {
                        history.pushState(null, null, url);
                    }
                },
                error: function(xhr) {
                    toastr.error('An error occurred while loading data.');
                    $('.loading-spinner').hide();
                }
            });
        }
    });
</script>
@endsection
