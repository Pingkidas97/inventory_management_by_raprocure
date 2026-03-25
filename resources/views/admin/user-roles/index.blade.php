@extends('admin.layouts.app_second', ['title' => 'Manage Role','sub_title' => 'List'])

@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span> -> Roles List</span>
        </h5>
    </div>
</div>
@endsection


@section('content')
<div class="page-start-section">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">User Roles</h5>
                            <a href="{{ route('admin.user-roles.create') }}" class="btn-rfq btn-rfq-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> Add New Role
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th width="5%">SR.NO.</th>
                                        <th>Role Name</th>
                                        <th>Manage Permission</th>
                                        <th>Status</th>
                                        <th>Modified</th>
                                        <!-- <th width="15%">Actions</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roles as $index => $role)
                                        <tr>
                                            <td>{{ ($roles->currentPage() - 1) * $roles->perPage() + $loop->iteration }}</td>
                                            <td>{{ $role->role_name }}</td>
                                            <td>
                                                <a href="{{ route('admin.user-roles.edit', $role->id) }}" class="btn-sm btn-rfq btn-rfq-white">
                                                    <i class="bi bi-lock"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch form-check-switch">
                                                    <input class="form-check-input status-toggle" type="checkbox" data-id="{{ $role->id }}" id="status-{{ $role->id }}" {{ $role->is_active ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="status-{{ $role->id }}"></label>
                                                </div>
                                            </td>
                                            <td>{{ $role->updated_at->format('d/m/Y') }}</td>
                                           <!--  <td>
                                                <div class="action-buttons">
                                                    <button class="btn-rfq btn-rfq-danger btn-sm delete-btn" data-id="{{ $role->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td> -->
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No roles found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($roles->total() > 0)
                            <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">
                                <div>
                                    <small>
                                        Showing {{ $roles->firstItem() ?? 0 }} to {{ $roles->lastItem() ?? 0 }} of {{ $roles->total() }} entries
                                    </small>
                                </div>
                                <div>
                                    <ul class="pagination mb-0">
                                        {{-- Previous Page Link --}}
                                        <li class="page-item {{ $roles->onFirstPage() ? 'disabled' : '' }}">
                                            <a class="page-link" href="{{ $roles->previousPageUrl() }}" rel="prev">«</a>
                                        </li>
                                        {{-- Page Number Links --}}
                                        @for ($page = 1; $page <= $roles->lastPage(); $page++)
                                            <li class="page-item {{ $page == $roles->currentPage() ? 'active' : '' }}">
                                                <a class="page-link" href="{{ $roles->url($page) }}">{{ $page }}</a>
                                            </li>
                                        @endfor
                                        {{-- Next Page Link --}}
                                        <li class="page-item {{ $roles->hasMorePages() ? '' : 'disabled' }}">
                                            <a class="page-link" href="{{ $roles->nextPageUrl() }}" rel="next">»</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        @endif
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
    // Status toggle handler
    $('.status-toggle').on('change', function() {
        const roleId = $(this).data('id');
        const isActive = $(this).is(':checked');

        $.ajax({
            url: "{{ route('admin.user-roles.status', ['id' => ':id']) }}".replace(':id', roleId),
            type: 'PUT',
            data: {
                _token: "{{ csrf_token() }}",
                is_active: isActive ? 1 : 0
            },
            beforeSend: function() {
                $(`#status-${roleId}`).prop('disabled', true);
            },
            success: function(response) {
                toastr.success(response.message || 'Status updated successfully');
            },
            error: function(xhr) {
                // Revert the toggle on error
                $(`#status-${roleId}`).prop('checked', !isActive);
                toastr.error(xhr.responseJSON?.message || 'Error updating status');
            },
            complete: function() {
                $(`#status-${roleId}`).prop('disabled', false);
            }
        });
    });

    // Delete button handler
    $(document).on('click', '.delete-btn', function() {
        const roleId = $(this).data('id');
        const deleteUrl = "{{ route('admin.user-roles.destroy', ':id') }}".replace(':id', roleId);

        if (confirm('Are you sure you want to delete this role? This action cannot be undone.')) {
            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function() {
                    $(`button[data-id="${roleId}"]`).prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'Role deleted successfully');
                        setTimeout(() => {
                            window.location.reload();
                        }, 300);
                    } else {
                        toastr.error(response.message || 'Failed to delete role');
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error deleting role');
                },
                complete: function() {
                    $(`button[data-id="${roleId}"]`).prop('disabled', false);
                }
            });
        }
    });
});
</script>
@endsection