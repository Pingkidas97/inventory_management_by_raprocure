@extends('admin.layouts.app',['title'=>'Manage Advertisement','sub_title'=>'Edit'])

@section('css')

@endsection
@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4 mt-3">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary">Edit Advertisement</h5>
        </div>
        <div class="card-body">
            <form id="create-form" action="{{route('admin.advertisement.update',$data->id)}}" method="post">
                @csrf
                @method('PUT')
                <div class="row mb-3">
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="types" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="types" name="types">
                                <option value="">Select Type</option>
                                <option {{ $data->types == '1' ? 'selected' : ''}} value="1">Buyer</option>
                                <option {{ $data->types == '2' ? 'selected' : ''}} value="2">Vendor</option>
                            </select>
                            <span class="text-danger error-text types_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="buyer_vendor_name" class="form-label">Buyer Name/ Vendor Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="buyer_vendor_name" name="buyer_vendor_name"
                                placeholder="Buyer Name/ Vendor Name" value="{{$data->buyer_vendor_name}}">
                            <span class="text-danger error-text buyer_vendor_name_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="received_on" class="form-label">Received On <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="received_on" name="received_on" value="{{$data->received_on}}">
                            <span class="text-danger error-text received_on_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="payment_received_on" class="form-label">Payment Received On<span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="payment_received_on" name="payment_received_on" value="{{$data->payment_received_on}}">
                            <span class="text-danger error-text payment_received_on_error"></span>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6 p-2">
                        <label class="form-label">Validity Period <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-6">
                                <input autocomplete="off" onblur="set_to_date();" type="date" class="form-control" placeholder=" Form Validity Period" value="{{$data->validity_period_from}}" id="validity_period_from" name="validity_period_from" maxlength="10" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                <span class="text-danger error-text validity_period_from_error"></span>
                            </div>
                            <div class="col-md-6">
                                <input autocomplete="off" type="date" class="form-control" placeholder="To Validity Period" value="{{$data->validity_period_to}}" id="validity_period_to" name="validity_period_to" maxlength="10" onkeypress="return event.charCode >= 48 && event.charCode <= 57">   
                                <span class="text-danger error-text validity_period_to_error"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="images" class="form-label">Image Upload<span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="images" name="images" >
                            <input type="hidden" name="old_image" id="old_image" value="{{$data->images}}">
                            @if($data->images)
                            <img src="{{public_path('uploads/advertisment/'.$data->images)}}" width="100" height="100">
                            @endif
                            <span class="text-danger error-text images_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="ad_position" class="form-label">Ad Position<span class="text-danger">*</span></label>
                            <select class="form-control required" id="ad_position" name="ad_position">
                                <option value="">Select Ad Position</option>
                                <option {{ $data->ad_position == '1' ? 'selected' : ''}} value="1"> Buyer Ads only on the Vendor Side</option>
                                <option {{ $data->ad_position == '2' ? 'selected' : ''}} value="2">Vendor Ads only on the Buyer Side</option>
                            </select>
                            <span class="text-danger error-text ad_position_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="ads_url" class="form-label">Url<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ads_url" name="ads_url" value="{{$data->ads_url}}">
                            <span class="text-danger error-text ads_url_error"></span>
                        </div>
                    </div>
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
                    <a href="{{ route('admin.advertisement.index') }}" class="btn btn-secondary  me-2">Cancel</a>
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
        const types = $('#types').val().trim();
        const buyer_vendor_name = $('#buyer_vendor_name').val().trim();
        const received_on = $('#received_on').val().trim();
        const payment_received_on = $('#payment_received_on').val().trim();
        const validity_period_from = $('#validity_period_from').val().trim();
        const validity_period_to = $('#validity_period_to').val().trim();
        const images = $('#images').val().trim();
        const old_image = $('#old_image').val().trim();
        const ad_position = $('#ad_position').val().trim();
        const ads_url = $('#ads_url').val().trim();
        const status = $('input[name="status"]:checked').val();

        let hasErrors = false;

        // Client-side validation
        if (!types) {
            $('span.error-text.types_error').text('Please select type.');
            hasErrors = true;
        } 
        if (buyer_vendor_name.length < 2) {
            $('span.error-text.buyer_vendor_name_error').text('buyer vendor name must be at least 2 characters.');
            hasErrors = true;
        }
        if (!received_on) {
            $('span.error-text.received_on_error').text('Please enter the received on.');
            hasErrors = true;
        }
        if (!payment_received_on) {
            $('span.error-text.payment_received_on_error').text('Please enter payment received on.');
            hasErrors = true;
        } 
        if (!validity_period_from) {
            $('span.error-text.validity_period_from_error').text('Please enter validity period from.');
            hasErrors = true;
        }
        if (!validity_period_to) {
            $('span.error-text.validity_period_to_error').text('Please enter validity period to.');
            hasErrors = true;
        }
        if (!images&&!old_image) {
            $('span.error-text.images_error').text('Please select images.');
            hasErrors = true;
        }
        if (!ad_position) {
            $('span.error-text.ad_position_error').text('Please select ad position.');
            hasErrors = true;
        }
        if (!ads_url) {
            $('span.error-text.ads_url_error').text('Please enter ads url.');
            hasErrors = true;
        }
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
                    window.location.href = "{{ route('admin.advertisement.index') }}";
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