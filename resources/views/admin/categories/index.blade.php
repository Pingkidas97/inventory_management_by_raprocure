@extends('admin.layouts.app_second', ['title' => 'Category','sub_title' => 'List'])

@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.divisions.index') }}"> -> {{ $divisionName }} </a>
            <span> -> Categories List</span>
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
                    <form id="searchForm" method="GET" action="{{ route('admin.categories.index', ['id' => $id]) }}">
                        <h3 class="">Categories</h3>
                        <div class="row">
                            <div class="col-xl-8 col-lg-8 col-md-12">
                                <ul class="rfq-filter-button">
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="text" class="form-control fillter-form-control" name="category_name" placeholder="Search by name" value="{{ request('category_name') }}">
                                                <label>Category Name</label>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-record2"></i></span>
                                            <div class="form-floating">
                                                <select name="status" class="form-select fillter-form-select">
                                                    <option value="">All Status</option>
                                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                                <label>Status</label>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <button type="submit" class="btn-style btn-style-primary">
                                            <i class="bi bi-search"></i> Search
                                        </button>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.categories.index', ['id' => $id]) }}" class="btn-style btn-style-danger">RESET</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.categories.create', ['id' => $id]) }}" class="btn-style btn-style-white">
                                            <i class="bi bi-plus-square"></i> CATEGORY
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </form>

                    <!-- Table Section -->
                    <div class="table-responsive product_listing_table_wrap" id="table-container">
                        @include('admin.categories.partials.table', ['categories' => $categories])
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
    // Toggle Category Status
    $(document).on('change', '.category-status-toggle', function() {
        const categoryId = $(this).data('id');
        const status = $(this).is(':checked') ? '1' : '2';
        const $checkbox = $(this);

        $.ajax({
            url: "{{ route('admin.categories.updateStatus', ['id' => ':id']) }}".replace(':id', categoryId),
            type: "PUT",
            data: {
                _token: "{{ csrf_token() }}",
                status: status
            },
            success: function(response) {
                response.success ? toastr.success(response.message) : toastr.error(response.message);
                if (!response.success) $checkbox.prop('checked', !$checkbox.prop('checked'));
            },
            error: function() {
                toastr.error('An error occurred. Please try again.');
                $checkbox.prop('checked', !$checkbox.prop('checked'));
            }
        });
    });

    // Search & Pagination AJAX
    $(document).on('submit', '#searchForm', function(e) {
        e.preventDefault();
        loadTable($(this).attr('action') + '?' + $(this).serialize());
    });

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        loadTable(url);
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
            error: function() {
                $('.loading-spinner').hide();
                toastr.error('Failed to load data.');
            }
        });
    }
});
</script>
@endsection
