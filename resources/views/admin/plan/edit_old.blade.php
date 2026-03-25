@extends('admin.layouts.app',['title'=>'Manage Plan','sub_title'=>'Edit'])

@section('css')

@endsection
@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4 mt-3">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary">Edit Plan</h5>
        </div>
        <div class="card-body">
            <form id="create-form" action="{{route('admin.plan.update',$data->id)}}" method="post">
                @csrf
                @method('PUT')
                <div class="row mb-3">
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="plan_name" class="form-label">Plan Name<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="plan_name" name="plan_name"
                                placeholder="Plan Name" value="{{$data->plan_name}}">
                            <span class="text-danger error-text plan_name_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="type" class="form-label">Customer Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type">
                                <option value="">Select Type</option>
                                <option {{ $data->type == '1' ? 'selected' : ''}} value="1">Buyer</option>
                                <option {{ $data->type == '2' ? 'selected' : ''}} value="2">Vendor</option>
                            </select>
                            <span class="text-danger error-text type_error"></span>
                        </div>
                    </div>
                    
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="no_of_user" class="form-label">No. of Logins<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="no_of_user" name="no_of_user" value="{{$data->no_of_user}}" oninput="this.value=this.value.replace(/[^0-9]/g, '').slice(0, 5)" maxlength="5">
                            <span class="text-danger error-text no_of_user_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="price" class="form-label">Amount<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="price" name="price" value="{{$data->price}}" oninput="this.value=this.value.replace(/[^0-9]/g, '').slice(0, 20)">
                            <span class="text-danger error-text price_error"></span>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <label class="radio-inline mr-3"><input type="radio" name="status" value="1" {{ $data->status == '1' ? 'checked' : ''}}> Active</label>
                                <label class="radio-inline mr-3"><input type="radio" name="status" value="2" {{ $data->status == '2' ? 'checked' : ''}}> Inactive</label>
                                <span class="help-block error-text status_error"></span> 
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.plan.index') }}" class="btn btn-secondary  me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save</button>
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
    $('#create-form input, #create-form select').on('keyup change', function() {
        const fieldName = $(this).attr('name');
        $(`span.error-text.${fieldName}_error`).text('');
    });

    $('#create-form').submit(function(e) {
        e.preventDefault();

        // Clear previous errors
        $('span.error-text').text('');

        // Get form values
        const plan_name = $('#plan_name').val().trim();
        const type = $('#type').val().trim();
        const no_of_user = $('#no_of_user').val().trim();
        const price = $('#price').val().trim();
        // const trial_period = $('#trial_period').val().trim();
        const status = $('input[name="status"]:checked').val();

        let hasErrors = false;

        // Client-side validation
        if (!plan_name) {
            $('span.error-text.plan_name_error').text('Please enter plan name.');
            hasErrors = true;
        } 
        if (!type) {
            $('span.error-text.type_error').text('Please select type');
            hasErrors = true;
        }
        if (!no_of_user) {
            $('span.error-text.no_of_user_error').text('Please enter no of user.');
            hasErrors = true;
        }
        if (!price) {
            $('span.error-text.price_error').text('Please enter price.');
            hasErrors = true;
        } 
        // if (!trial_period) {
        //     $('span.error-text.trial_period_error').text('Please enter trial period.');
        //     hasErrors = true;
        // }
        if (!status) {
            $('span.error-text.status_error').text('Please select status.');
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
            contentType: false,
            processData: false,
            sendBeforeSend: function() {
                $('#create-form').find('button[type="submit"]').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    window.location.href = "{{ route('admin.plan.index') }}";
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
            },
            complete: function() {
                $('#create-form').find('button[type="submit"]').prop('disabled', false);
            }
        });
    });
});
</script>
@endsection