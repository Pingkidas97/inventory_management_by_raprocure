@extends('buyer.layouts.app', ['title'=>'Add User'])

@section('css')
    <link href="{{ asset('public/assets/library/sumoselect-v3.4.9-2/css/sumoselect.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/library/sumoselect-v3.4.9-2/css/sumo-select-style.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar-menu')
    </div>
    <!---Section Main-->
    <main class="main flex-grow-1">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-8">
                    <div class="card">
                        <div class="card-header bg-white py-3 py-md-4 px-md-4 px-lg-5 rounded-top-4">
                            <h1 class="font-size-18 mb-0">Add User</h1>
                        </div>
                        <form id="createUserForm" method="POST">
                            @csrf
                        <div class="card-body px-md-4 px-lg-5 py-4">
                            <div class="row">
                                <div class="mb-3 col-12 col-sm-6">
                                    <label for="name" class="form-label">
                                        Name<sup class="text-danger">*</sup>
                                    </label>
                                    <input type="text" name="name" id="name" class="form-control" placeholder="" aria-describedby="nameId" value="" required />
                                    <small id="name_error" class="text-danger name_error font-size-13">
                                    </small>
                                </div>
                                <div class="mb-3 col-12 col-sm-6">
                                    <label for="branchId" class="form-label">
                                        Branch/Unit<sup class="text-danger">*</sup>
                                    </label>
                                    <select name="branchId[]" id="branchId" class="form-select branch-sumo-select" multiple>
                                        @foreach ($branches as $branche)
                                            <option value="{{$branche->branch_id}}">{{$branche->name}}</option>
                                        @endforeach
                                    </select>
                                    <small id="branchId_error" class="text-danger branchId_error font-size-13">
                                    </small>
                                </div>
                                <div class="mb-3 col-12 col-sm-6">
                                    <label for="designation" class="form-label">
                                        Designation<sup class="text-danger">*</sup>
                                    </label>
                                    <input type="text" name="designation" id="designation" class="form-control"
                                        placeholder="" aria-describedby="designation_error" value="" required />
                                    <small id="designation_error" class="text-danger designation_error font-size-13">
                                    </small>
                                </div>
                                <div class="mb-3 col-12 col-sm-6">
                                    <label for="role_id" class="form-label">User Role<sup class="text-danger">*</sup></label>
                                    <select class="form-select" id="role_id" name="role_id"  aria-describedby="userRoleId" required>
                                        <option value="">Select User Role</option>
                                        @foreach ($roles as $role)
                                            <option value="{{$role->id}}">{{$role->role_name}}</option>
                                        @endforeach
                                    </select>
                                    <small id="role_id_error" class="text-danger role_id_error font-size-13">
                                    </small>
                                </div>
                                <div class="mb-3 col-12 col-sm-6">
                                    <label for="mobile_no" class="form-label">
                                        Mobile Number<sup class="text-danger">*</sup>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <select name="country" id="country" class="form-control border-right-0 rounded-end-0">
                                                @foreach ($countries as $country)
                                                    <option value="{{$country->phonecode}}">{{$country->name}} (+{{$country->phonecode}})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <input type="text" name="mobile_no" id="mobile_no" class="form-control" placeholder="" aria-describedby="mobileNoId" value="" required />
                                    </div>
                                    <small id="mobile_no_error" class="text-danger mobile_error font-size-13">
                                    </small>
                                </div>
                                <div class="mb-3 col-12 col-sm-6">
                                    <label for="email" class="form-label">
                                        Email<sup class="text-danger">*</sup>
                                    </label>
                                    <input type="text" name="email" id="email" class="form-control" placeholder="" aria-describedby="email_error" value="" required />
                                    <small id="email_error" class="text-danger email_error font-size-13">
                                    </small>
                                </div>
                                <div class="mb-3 col-12">
                                    <div>Status<sup class="text-danger">*</sup></div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="status" id="activeRadio" value="1" checked>
                                        <label class="form-check-label" for="activeRadio">Active</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="status"
                                            id="inactiveRadio" value="2">
                                        <label class="form-check-label" for="inactiveRadio">Inactive</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="card-footer bg-white d-flex justify-content-end py-3 px-5 gap-3 rounded-bottom-4">
                            <a type="button" class="ra-btn ra-btn-outline-danger" href="{{ route('buyer.user-management.users') }}">Cancel</a>
                            <button type="submit" class="ra-btn ra-btn-primary">Save</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
<script src="{{ asset('public/assets/library/sumoselect-v3.4.9-2/js/jquery.sumoselect.min.js') }}"></script>
<script>
$(document).ready(function () {
    $('.branch-sumo-select').SumoSelect({selectAll: true, nativeOnDevice: [], maxHeight:100, csvDispCount: 7, placeholder: 'Select Branch/Unit'});

    // Clear error messages on input change
    $('#createUserForm input, #createUserForm select').on('keyup change', function() {
        const fieldName = $(this).attr('name');
        $(`small.text-danger.${fieldName}_error`).text('');
    });

    // Form submission
    $('#createUserForm').submit(function (e) {
        e.preventDefault();

        // Clear previous errors
        $('small.text-danger').text('');

        // Get form values
        const name = $('#name').val().trim();
        const designation = $('#designation').val().trim();
        const mobile = $('#mobile_no').val().trim();
        const email = $('#email').val().trim();
        const role_id = $('#role_id').val();
        const profile_image = $('#profile_image').val();
        const status = $('input[name="status"]:checked').val();

        let hasErrors = false;

        // Client-side validation
        if (!name) {
            $('small.text-danger.name_error').text('Please enter the name.');
            hasErrors = true;
        } else if (name.length < 2) {
            $('small.text-danger.name_error').text('Name must be at least 2 characters.');
            hasErrors = true;
        }

        if (!designation) {
            $('small.text-danger.designation_error').text('Please enter the designation.');
            hasErrors = true;
        } else if (designation.length < 2) {
            $('small.text-danger.designation_error').text('Designation must be at least 2 characters.');
            hasErrors = true;
        }

        if (!mobile) {
            $('small.text-danger.mobile_error').text('Please enter the mobile number.');
            hasErrors = true;
        } else if (!/^\d{7,15}$/.test(mobile)) {
            $('small.text-danger.mobile_error').text('Mobile number must be between 7 and 15 digits.');
            hasErrors = true;
        }

        if (!email) {
            $('small.text-danger.email_error').text('Please enter the email address.');
            hasErrors = true;
        } else if (!/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
            $('small.text-danger.email_error').text('Please enter a valid email address.');
            hasErrors = true;
        }

        if (!role_id) {
            $('small.text-danger.role_id_error').text('Please select a user role.');
            hasErrors = true;
        }

        if (!status) {
            $('small.text-danger.status_error').text('Please select a status.');
            hasErrors = true;
        }

        if (hasErrors) return;

        // AJAX submission
        $.ajax({
            url: "{{ route('buyer.user-management.store-user') }}",
            type: "POST",
            data: new FormData(this),
            contentType: false,
            processData: false,
            dataType: "json",
            beforeSend: function() {
                $('#createUserForm').find('button[type="submit"]').prop('disabled', true);
            },
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => {
                        window.location.href = "{{ route('buyer.user-management.users') }}";
                    }, 300);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function (key, value) {
                        const errorField = key.replace('.', '_');
                        $(`small.text-danger.${errorField}_error`).text(value[0]);
                    });
                } else {
                    toastr.error('An error occurred. Please try again.');
                }
            },
            complete: function() {
                $('#createUserForm').find('button[type="submit"]').prop('disabled', false);
            }
        });
    });

    // Limit characters function
    window.limitCharacters = function(element, maxChars) {
        if (element.value.length > maxChars) {
            element.value = element.value.substr(0, maxChars);
        }
    };
});
</script>
@endsection
