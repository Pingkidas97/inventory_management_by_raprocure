@extends('admin.layouts.app')
@section('title', 'Edit User')
@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4 mt-3">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary">Edit User</h5>
        </div>
        <div class="card-body">
            <form id="updateUserForm">
                @csrf
                @method('PUT')
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="{{ old('name', $user->name) }}">
                        <span class="text-danger error-text name_error"></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="designation" class="form-label">Designation <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="designation" name="designation"
                            value="{{ old('designation', $user->designation) }}">
                        <span class="text-danger error-text designation_error"></span>
                    </div>
                    <div class="col-md-6">
                        <label for="mobile" class="form-label">Mobile No. <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select class="form-control" id="country_code" name="country_code"
                                style="max-width: 120px;">
                                @foreach($countries as $country)
                                <option value="+{{ $country->phonecode }}"
                                    {{ old('country_code', $user->country_code) == "+{$country->phonecode}" ? 'selected' : '' }}>
                                    {{ $country->name }} (+{{ $country->phonecode }})
                                </option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control" id="mobile" name="mobile"
                                value="{{ old('mobile', $user->mobile) }}">
                        </div>
                        <span class="text-danger error-text mobile_error"></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="{{ old('email', $user->email) }}">
                        <span class="text-danger error-text email_error"></span>
                    </div>
                    <div class="col-md-6">
                        <label for="role_id" class="form-label">User Role <span class="text-danger">*</span></label>
                        <select class="form-select" id="role_id" name="role_id">
                            <option value="">Select User Role</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}"
                                {{ old('role_id', $user->role_mapping->user_role_id ?? '') == $role->id ? 'selected' : '' }}>
                                {{ $role->role_name }}
                            </option>
                            @endforeach
                        </select>
                        <span class="text-danger error-text role_id_error"></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label d-block">Status</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="active" value="1"
                                {{ old('status', $user->status) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">Active</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="inactive" value="2"
                                {{ old('status', $user->status) == 2 ? 'checked' : '' }}>
                            <label class="form-check-label" for="inactive">Inactive</label>
                        </div>
                        <span class="text-danger error-text status_error"></span>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
$(document).ready(function() {
    // Clear error messages on input change
    $('#updateUserForm input, #updateUserForm select').on('keyup change', function() {
        const fieldName = $(this).attr('name');
        $(`span.error-text.${fieldName}_error`).text('');
    });

    $('#updateUserForm').submit(function(e) {
        e.preventDefault();

        // Clear previous errors
        $('span.error-text').text('');

        // Get form values
        const name = $('#name').val().trim();
        const designation = $('#designation').val().trim();
        const mobile = $('#mobile').val().trim();
        const email = $('#email').val().trim();
        const role_id = $('#role_id').val();
        const status = $('input[name="status"]:checked').val();

        let hasErrors = false;

        // Client-side validation
        if (!name) {
            $('span.error-text.name_error').text('Please enter the name.');
            hasErrors = true;
        } else if (name.length < 2) {
            $('span.error-text.name_error').text('Name must be at least 2 characters.');
            hasErrors = true;
        }

        if (!designation) {
            $('span.error-text.designation_error').text('Please enter the designation.');
            hasErrors = true;
        }

        if (!mobile) {
            $('span.error-text.mobile_error').text('Please enter the mobile number.');
            hasErrors = true;
        } else if (!/^\d{10}$/.test(mobile)) {
            $('span.error-text.mobile_error').text('Mobile number must be 10 digits.');
            hasErrors = true;
        }

        if (!email) {
            $('span.error-text.email_error').text('Please enter the email address.');
            hasErrors = true;
        } else if (!/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
            $('span.error-text.email_error').text('Please enter a valid email address.');
            hasErrors = true;
        }

        if (!role_id) {
            $('span.error-text.role_id_error').text('Please select a user role.');
            hasErrors = true;
        }

        if (!status) {
            $('span.error-text.status_error').text('Please select a status.');
            hasErrors = true;
        }

        // If there are errors, stop form submission
        if (hasErrors) {
            return;
        }

        // Proceed with AJAX submission
        $.ajax({
            url: "{{ route('admin.users.update', $user->id) }}",
            type: "POST",
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(function() {
                        window.location.href = "{{ route('admin.users.index') }}";
                    }, 300); // Redirect after 300 milliseconds

                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    toastr.error(xhr.responseJSON.message);
                } else {
                    toastr.error('An error occurred. Please try again.');
                }
            }
        });
    });
});
</script>
@endsection