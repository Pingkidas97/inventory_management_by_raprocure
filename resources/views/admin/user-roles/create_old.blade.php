@extends('admin.layouts.app', ['title'=> 'Add Role'])
@section('css')

@endsection
@section('content')
<div class="card" style="margin: 25px;">
    <div class="card-header">
        <h5>Create Role with Permissions</h5>
    </div>
    <form id="create-role-form">
        @csrf
        @method('POST')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <input type="text" name="role_name" class="form-control" placeholder="Enter Role Name" required>
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('admin.user-roles.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>

            <h5 class="mt-4">Permissions</h5>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all-modules" class="select-all">
                                <label for="select-all-modules">Module/Submodule</label>
                            </th>
                            <th>
                                <label for="select-all-add">Add</label>
                                <input type="checkbox" id="select-all-add" class="select-all">
                            </th>
                            <th>
                                <label for="select-all-edit">Edit</label>
                                <input type="checkbox" id="select-all-edit" class="select-all">
                            </th>
                            <th>
                                <label for="select-all-delete">Delete</label>
                                <input type="checkbox" id="select-all-delete" class="select-all">
                            </th>
                            <th>
                                <label for="select-all-view">View</label>
                                <input type="checkbox" id="select-all-view" class="select-all">
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($modules as $module)
                        <tr>
                            <td>
                                <input type="checkbox" class="module-check" data-id="{{ $module->id }}">
                                <strong>{{ strtoupper($module->module_name) }}</strong>
                            </td>

                            <td>
                                @if($module->available_permissions['add'])
                                <input type="checkbox" name="permissions[{{ $module->id }}][add]" value="1"
                                    class="add-checkbox">
                                @else
                                <span>x</span>
                                @endif
                            </td>

                            <td>
                                @if($module->available_permissions['edit'])
                                <input type="checkbox" name="permissions[{{ $module->id }}][edit]" value="1"
                                    class="edit-checkbox">
                                @else
                                <span>x</span>
                                @endif
                            </td>

                            <td>
                                @if($module->available_permissions['delete'])
                                <input type="checkbox" name="permissions[{{ $module->id }}][delete]" value="1"
                                    class="delete-checkbox">
                                @else
                                <span>x</span>
                                @endif
                            </td>

                            <td>
                                @if($module->available_permissions['view'])
                                <input type="checkbox" name="permissions[{{ $module->id }}][view]" value="1"
                                    class="view-checkbox">
                                @else
                                <span>x</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select All for each permission type
    document.getElementById('select-all-add').addEventListener('change', function() {
        document.querySelectorAll('.add-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    document.getElementById('select-all-edit').addEventListener('change', function() {
        document.querySelectorAll('.edit-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    document.getElementById('select-all-delete').addEventListener('change', function() {
        document.querySelectorAll('.delete-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    document.getElementById('select-all-view').addEventListener('change', function() {
        document.querySelectorAll('.view-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Select All Modules (checks all checkboxes in all rows)
    document.getElementById('select-all-modules').addEventListener('change', function() {
        document.querySelectorAll('tbody tr').forEach(row => {
            const checkboxes = row.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Also update other select-all checkboxes
        document.querySelectorAll('thead .select-all').forEach(checkbox => {
            if (checkbox.id !== 'select-all-modules') {
                checkbox.checked = this.checked;
            }
        });
    });

    // Row-wise Module checkbox — check/uncheck the entire row
    const moduleCheckboxes = document.querySelectorAll('.module-check');

    moduleCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            const inputs = row.querySelectorAll('input[type="checkbox"]:not(.module-check)');
            inputs.forEach(function(input) {
                input.checked = checkbox.checked;
            });
        });
    });
});
</script>
<script>
$(document).ready(function() {
    $('#create-role-form').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: "{{ route('admin.user-roles.store') }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                alert('Success: ' + response.message);
                window.location.href = "{{ route('admin.user-roles.index') }}";
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Validation errors
                    var errors = xhr.responseJSON.errors;
                    var errorMessages = [];

                    for (var key in errors) {
                        errorMessages.push(errors[key][0]);
                    }

                    alert('Validation Error:\n' + errorMessages.join('\n'));
                } else {
                    // Other errors
                    alert('Error: ' + (xhr.responseJSON.message || 'An error occurred'));
                }
            }
        });
    });
});
</script>
@endsection