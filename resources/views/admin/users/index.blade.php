@extends('admin.layouts.app_second',['title' => 'User Management','sub_title' => 'List'])
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-people"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.users.index') }}"> -> User Management </a>
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
                    <h3 class="">All Users</h3>
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 col-md-12 mb-3">
                            
                            <form id="searchForm" action="{{ route('admin.users.index') }}" method="GET">
                                <ul class="rfq-filter-button">
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <div class="form-floating">
                                                <input type="text" id="search_user" name="search"
                                                    class="form-control fillter-form-control"
                                                    value="{{ request('search') }}"
                                                    placeholder="Search by name/email/contact" style="width: 350px;">
                                                <label for="search_user">Search By Name/Email/Contact</label>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-record2"></i></span>
                                            <div class="form-floating">
                                                <select name="status" id="user_status"
                                                    class="form-select fillter-form-select">
                                                    <option value="">Select</option>
                                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>
                                                        Active</option>
                                                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>
                                                        Inactive</option>
                                                </select>
                                                <label for="user_status">Status</label>
                                            </div>
                                        </div>
                                    </li>

                                    

                                    <li class="notShow_on_mobile">
                                        <button type="submit" class="btn-style btn-style-primary">
                                            <i class="bi bi-search"></i> Search
                                        </button>
                                    </li>

                                    <li class="notShow_on_mobile">
                                        <a href="{{ route('admin.users.index') }}"
                                            class="btn-style btn-style-danger">RESET</a>
                                    </li>

                                 

                                    <li class="notShow_on_mobile">
                                        <a href="{{ route('admin.users.create') }}" class="btn-rfq btn-rfq-white ">
                                            <i class="bi bi-plus"></i> ADD 
                                        </a>
                                    </li>
                                </ul>
                            </form>

                        </div>

                    </div>

                    <div class="table-responsive" id="table-container">
                        @include('admin.users.partials.table')
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
    $(document).on('submit', '#searchForm', function(e) {
        e.preventDefault();
        loadTable($(this).attr('action') + '?' + $(this).serialize());
    });

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        loadTable($(this).attr('href'));
    });

    $(document).on('change', '#perPage', function() {
        const form = $('#searchForm');
        const formData = form.serialize();
        const perPage = $(this).val();
        const url = form.attr('action') + '?' + formData + '&per_page=' + perPage;
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

    

    // Delete user
    $('.delete-user').click(function() {
        var userId = $(this).data('id');

        if (confirm('Are you sure you want to delete this user?')) {
            $.ajax({
                url: "{{ url('super-admin/users') }}/" + userId,
                type: "DELETE",
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        location.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('An error occurred. Please try again.');
                }
            });
        }
    });

    // Status toggle
    $(document).on('change', '.status-toggle', function() {
        const userId = $(this).data('id');
        const isActive = $(this).is(':checked');
        
        $.ajax({
            url: "{{ route('admin.users.updateStatus', ['id' => ':id']) }}".replace(':id', userId),
            type: "PUT",
            data: {
                _token: "{{ csrf_token() }}",
                is_active: isActive ? 1 : 0
            },
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                    $(this).prop('checked', !isActive);
                }
            },
            error: function(xhr) {
                toastr.error('An error occurred. Please try again.');
                $(this).prop('checked', !isActive);
            }
        });
    });
    
})
</script>
@endsection