@extends('admin.layouts.app')
@section('title', 'Admin User List')
@section('content')
<style>
    .pagination .page-item .page-link {
        border-radius: 8px;
        margin: 0 3px;
        font-size: 16px;
        color: #3b82f6; /* Tailwind Blue-500 */
    }

    .pagination .page-item.active .page-link {
        background-color: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }

    .pagination .page-item.disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>
<div class="container-fluid">
    <div class="card shadow mb-4 mt-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">User Management</h6>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Add New User</a>
        </div>
        <div class="card-body">
            <!-- Search Filter Section -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <form id="searchForm" method="GET" action="{{ route('admin.users.index') }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Search Users</label>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="By Username, Contact No. or Email"
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                 
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">SEARCH</button>
                                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary ml-2">RESET</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="usersTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Designation</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td> {{ $user->country_code ? '+' . $user->country_code : '' }} {{ $user->mobile }}</td>
                            <td>{{ $user->designation }}</td>
                            <td>{{ $user->role->role_name ?? 'N/A' }}</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input status-toggle" 
                                           type="checkbox" 
                                           data-id="{{ $user->id }}"
                                           id="status-{{ $user->id }}" 
                                           {{ $user->status == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status-{{ $user->id }}"></label>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                <button class="btn btn-sm btn-danger delete-user" data-id="{{ $user->id }}">Delete</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <!-- Pagination Links -->
                @php
                    $paginator = $users;
                    $onEachSide = 2; // Number of pages to show on each side of current page
                    $window = $onEachSide * 2;
                @endphp

                @if ($paginator->total() > 0)
                    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                        <div class="mb-2">
                            <small>
                                Showing
                                {{ $paginator->firstItem() ?? 0 }}
                                to
                                {{ $paginator->lastItem() ?? 0 }}
                                of
                                {{ $paginator->total() }}
                                entries
                            </small>
                        </div>
                        <div>
                            <ul class="pagination mb-0">
                                {{-- Previous Page Link --}}
                                @if ($paginator->onFirstPage())
                                    <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a>
                                    </li>
                                @endif

                                {{-- Page Number Links --}}
                                @php
                                    $currentPage = $paginator->currentPage();
                                    $lastPage = $paginator->lastPage();
                                    
                                    // Calculate the range of pages to show
                                    $startPage = max($currentPage - $onEachSide, 1);
                                    $endPage = min($currentPage + $onEachSide, $lastPage);
                                    
                                    // Adjust if we're near the start or end
                                    if ($currentPage <= $onEachSide) {
                                        $endPage = min($window + 1, $lastPage);
                                    }
                                    if ($currentPage >= $lastPage - $onEachSide) {
                                        $startPage = max($lastPage - $window, 1);
                                    }
                                @endphp

                                {{-- Always show first page --}}
                                @if ($startPage > 1)
                                    <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">1</a></li>
                                    @if ($startPage > 2)
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif
                                @endif

                                {{-- Show calculated page range --}}
                                @for ($page = $startPage; $page <= $endPage; $page++)
                                    @if ($page == $currentPage)
                                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                    @else
                                        <li class="page-item"><a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a></li>
                                    @endif
                                @endfor

                                {{-- Always show last page --}}
                                @if ($endPage < $lastPage)
                                    @if ($endPage < $lastPage - 1)
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif
                                    <li class="page-item"><a class="page-link" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a></li>
                                @endif

                                {{-- Next Page Link --}}
                                @if ($paginator->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a>
                                    </li>
                                @else
                                    <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
$(document).ready(function() {

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
});
</script>
@endsection