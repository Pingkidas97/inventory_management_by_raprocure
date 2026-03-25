@extends('vendor.layouts.app_second',['title'=>'Change Password','sub_title'=>''])
@section('css')
<style>
.relative-input {
    position: relative;
}

.ids {
    position: absolute;
    top: 10px;
    right: 15px;
    cursor: pointer;
    color: black;
}
</style>
@endsection
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i> <a href="{{ route('vendor.dashboard') }}">Dashboard</a>
            <a href="{{ route('vendor.password.change') }}"> -> Change Password</a>
        </h5>
    </div>
</div>
@endsection

@section('content')
<div class="about_page_details">
    <div class="container-fluid">
        <div class="row justify-content-center pt-5">
            <div class="col-xl-6 col-lg-8 col-md-9 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <form id="changePasswordForm" action="{{ route('vendor.password.change') }}" method="POST">
                            <h3>Change Password</h3>
                            @csrf
                            <div class="row pt-3">
                                <div class="col-md-12  mb-3">
                                    <label for="password" class="form-label">Current Password <span
                                            class="text-danger">*</span></label>
                                    <div class="relative-input">
                                        <input oninput="this.value=this.value.replace(/[ ]/,'')" type="password"
                                            name="password" id="password" maxlength="25" placeholder="Current Password "
                                            class="form-control view_pass c_pass">
                                        <i class="fa fa-eye-slash ids" id="eye"
                                            onclick="eye_change(this,'password')"></i>
                                    </div>
                                    <span class="text-danger error-text password_error"></span>
                                </div>

                                <div class="col-md-12  mb-3">
                                    <label for="new_password" class="form-label">Change Password <span
                                            class="text-danger">*</span></label>
                                    <div class="relative-input">
                                        <input onblur="password_check_new()"
                                            oninput="this.value=this.value.replace(/[ ]/,'')" type="password"
                                            name="new_password" id="new_password" class="form-control view_pass"
                                            placeholder="Change Password" maxlength="25">
                                        <i class="fa fa-eye-slash ids" id="eye"
                                            onclick="eye_change(this,'new_password')"></i>
                                    </div>
                                    <span class="" id="password-msg">Password must be minimum 8 characters</span>
                                    <span class="text-danger error-text new_password_error"></span>

                                </div>
                                <div class="col-md-12  mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Passwsord<span
                                            class="text-danger">*</span></label>
                                    <div class="relative-input">
                                        <input onblur="password_check_confirm()"
                                            oninput="this.value=this.value.replace(/[ ]/,'')" type="password"
                                            name="confirm_password" id="confirm_password" class="form-control"
                                            placeholder="Confirm Passwsord" maxlength="25">
                                        <i class="fa fa-eye-slash ids" id="eye"
                                            onclick="eye_change(this,'confirm_password')"></i>
                                    </div>
                                    <span class="text-danger error-text confirm_password_error"></span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center">
                                <button type="submit" class="btn-rfq btn-rfq-primary m-1"><i
                                        class="bi bi-journal-bookmark"></i>Save</button>
                                <a href="{{ route('vendor.dashboard') }}"
                                    class="btn-rfq btn-rfq-danger ml-3 m-1">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
function eye_change(e, id) {
    // alert('sdfgjhdgfjdsf');
    var x = document.getElementById(id);
    // console.log("wc",x.type);
    if (x.type === "password") {
        x.type = "text";
        $(e).removeClass('fa-eye-slash');
        $(e).addClass('fa-eye');
        // console.log("with"x.type);
    } else {
        x.type = "password";
        $(e).addClass('fa-eye-slash');
        $(e).removeClass('fa-eye');
    }
}
$(document).ready(function() {
    // Clear error messages on input change
    $('#changePasswordForm input, #changePasswordForm select').on('keyup change', function() {
        const fieldName = $(this).attr('name');
        $(`span.error-text.${fieldName}_error`).text('');
    });

    $('#changePasswordForm').submit(function(e) {
        e.preventDefault();

        // Clear previous errors
        $('span.error-text').text('');
        // Get form values
        const password = $('#password').val().trim();
        const new_password = $('#new_password').val().trim();
        const confirm_password = $('#confirm_password').val().trim();

        let hasErrors = false;

        // Client-side validation
        if (!password) {
            $('span.error-text.password_error').text('Current Password Required!');
            hasErrors = true;
        }
        if (password.length < 6) {
            $('span.error-text.password_error').text(
                'Old password must be 6 digit or greater then 6 digit!');
            hasErrors = true;
        }
        if (!new_password) {
            $('span.error-text.new_password_error').text(
                'Change Password Required! Password must be minimum 8 characters');
            hasErrors = true;
            $('#password-msg').hide();
        }
        if (new_password.length < 6) {
            $('span.error-text.new_password_error').text('Password Must Be Greater Than 6 Digits.');
            hasErrors = true;
            $('#password-msg').hide();
        }

        if (!confirm_password) {
            $('span.error-text.confirm_password_error').text('Confirm Password Required!');
            hasErrors = true;
        }

        if (!confirm_password != new_password) {
            $('span.error-text.confirm_password_error').text(
                'Change Password And Confirm Password Must Be Match!');
            hasErrors = true;
        }
        // If there are errors, stop form submission
        if (hasErrors) {
            return false;
        }

        // Proceed with AJAX submission
        $.ajax({
            url: $(this).attr('action'),
            type: "POST",
            data: new FormData(this),
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    window.location.href = response.redirectUrl;
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        const errorField = key.replace('.', '_');
                        $(`span.error-text.${errorField}_error`).text(value[0]);
                    });
                } else {
                    toastr.error('An error occurred. Please try again.');
                }
            }
        });
    });
});
</script>
@endsection