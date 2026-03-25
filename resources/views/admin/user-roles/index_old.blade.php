@extends('admin.layouts.app', ['title'=> 'Role List'])
@section('css')

@endsection
@section('content')
<style>
.pagination .page-item .page-link {
    border-radius: 8px;
    margin: 0 3px;
    font-size: 16px;
    color: #3b82f6;
    /* Tailwind Blue-500 */
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
<div class="container-fluid px-4">
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">User Roles</h4>
            <a href="{{ route('admin.user-roles.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Add New Role
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">SR.NO.</th>
                            <th>Role Name</th>
                            <th>MANAGE PERMISSION</th>
                            <th>STATUS</th>
                            <th>MODIFIED</th>
                            <th width="15%">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $index => $role)
                        <tr>
                            <td>{{ ($roles->currentPage() - 1) * $roles->perPage() + $loop->iteration }}</td>
                            <td>{{ $role->role_name }}</td>
                            <td>
                                <a type="button" class="btn-sm btn btn-secondary"
                                    href="{{ route('admin.user-roles.edit', $role->id) }}"><i class="fa fa-lock"></i></a>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input status-toggle" type="checkbox"
                                        data-id="{{ $role->id }}" id="status-{{ $role->id }}"
                                        {{ $role->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status-{{ $role->id }}">

                                    </label>
                                </div>
                            </td>
                            <td>{{ $role->updated_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="d-flex" style="gap: 5px;">
                                    <button class="btn btn-danger btn-sm delete-btn" data-id="{{ $role->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @php
                $paginator = $roles;
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
                            @for ($page = 1; $page <= $paginator->lastPage(); $page++)
                                @if ($page == $paginator->currentPage())
                                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                @else
                                <li class="page-item"><a class="page-link"
                                        href="{{ $paginator->url($page) }}">{{ $page }}</a></li>
                                @endif
                                @endfor

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
    // Status toggle handler
    $('.status-toggle').change(function() {
        const roleId = $(this).data('id');
        const isActive = $(this).is(':checked');

        $.ajax({
            url: "{{ route('admin.user-roles.status', ['id' => ':id']) }}".replace(':id', roleId),
            type: 'PUT',
            data: {
                _token: "{{ csrf_token() }}",
                is_active: isActive ? 1 : 0
            },
            success: function(response) {
                // Update the label text
                const label = $(`label[for="status-${roleId}"]`);
                toastr.success('Status updated successfully');
        
            },
            error: function(xhr) {
                // Revert the toggle if there's an error
                $(this).prop('checked', !isActive);
                alert('Error updating status: ' + (xhr.responseJSON?.message ||
                    'Server error'));
            }
        });
    });
});
$(document).ready(function() {
    // Delete button click handler
    // Delete button click handler
    $(document).on('click', '.delete-btn', function() {
        const roleId = $(this).data('id');
        const deleteUrl = "{{ route('admin.user-roles.destroy', ':id') }}".replace(':id', roleId);

        if (confirm('Are you sure you want to delete this role?\nThis action cannot be undone.')) {
            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        alert('User role deleted successfully!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to delete role'));
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message ||
                        'Server error occurred'));
                }
            });
        }
    });
});
</script>
@endsection