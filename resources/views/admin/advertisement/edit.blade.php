@extends('admin.layouts.app_second', [
    'title' => 'Manage Advertisement',
    'sub_title' => 'Edit Advertisement',
])

@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.advertisement.index') }}"> -> Advertisement List</a>
            <span> -> Edit Advertisement</span>
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
                        <div class="basic-form pro_edit">
                            <form id="editAdvertisementForm" action="{{ route('admin.advertisement.update', $data->id) }}" method="post">
                                @csrf
                                @method('PUT')
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="types" class="form-label"><strong>Type</strong> <span class="text-danger">*</span></label>
                                        <select class="form-control" id="types" name="types">
                                            <option value="">Select Type</option>
                                            <option {{ $data->types == '1' ? 'selected' : '' }} value="1">Buyer</option>
                                            <option {{ $data->types == '2' ? 'selected' : '' }} value="2">Vendor</option>
                                        </select>
                                        <span class="text-danger error-text types_error"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="buyer_vendor_name" class="form-label"><strong>Buyer Name / Vendor Name</strong> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="buyer_vendor_name" name="buyer_vendor_name" value="{{ $data->buyer_vendor_name }}" oninput="limitCharacters(this,255)">
                                        <span class="text-danger error-text buyer_vendor_name_error"></span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="received_on" class="form-label"><strong>Received On</strong> <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="received_on" name="received_on" value="{{ $data->received_on }}">
                                        <span class="text-danger error-text received_on_error"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="payment_received_on" class="form-label"><strong>Payment Received On</strong> <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="payment_received_on" name="payment_received_on" value="{{ $data->payment_received_on }}">
                                        <span class="text-danger error-text payment_received_on_error"></span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label"><strong>Validity Period</strong> <span class="text-danger">*</span></label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input type="date" class="form-control" id="validity_period_from" name="validity_period_from" value="{{ $data->validity_period_from }}" onblur="set_to_date();">
                                                <span class="text-danger error-text validity_period_from_error"></span>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="date" class="form-control" id="validity_period_to" name="validity_period_to" value="{{ $data->validity_period_to }}">
                                                <span class="text-danger error-text validity_period_to_error"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="images" class="form-label"><strong>Image Upload</strong></label>
                                        <input type="file" class="form-control" id="images" name="images">
                                        <input type="hidden" name="old_image" id="old_image" value="{{ $data->images }}">
                                        @if($data->images)
                                            <img src="{{ asset('public/uploads/advertisment/' . $data->images) }}" width="100" height="100" class="mt-2" style="object-fit: contain;">
                                        @endif
                                        <span class="text-danger error-text images_error"></span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="ad_position" class="form-label"><strong>Ad Position</strong> <span class="text-danger">*</span></label>
                                        <select class="form-control" id="ad_position" name="ad_position">
                                            <option value="">Select Ad Position</option>
                                            <option {{ $data->ad_position == '1' ? 'selected' : '' }} value="1">Buyer Ads only on the Vendor Side</option>
                                            <option {{ $data->ad_position == '2' ? 'selected' : '' }} value="2">Vendor Ads only on the Buyer Side</option>
                                        </select>
                                        <span class="text-danger error-text ad_position_error"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="ads_url" class="form-label"><strong>URL</strong> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="ads_url" name="ads_url" value="{{ $data->ads_url }}" oninput="limitCharacters(this,255)">
                                        <span class="text-danger error-text ads_url_error"></span>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label d-block"><strong>Status</strong> <span class="text-danger">*</span></label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="active" value="1" {{ $data->status == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="active">Active</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="inactive" value="2" {{ $data->status == '2' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="inactive">Inactive</label>
                                        </div>
                                        <span class="text-danger error-text status_error"></span>
                                    </div>
                                </div>
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="submit" class="btn-rfq btn-rfq-primary">Save</button>
                                    <a href="{{ route('admin.advertisement.index') }}" class="btn-rfq btn-rfq-danger">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    // Clear error messages on input change
    $('#editAdvertisementForm input, #editAdvertisementForm select').on('keyup change', function() {
        const fieldName = $(this).attr('name');
        $(`span.error-text.${fieldName}_error`).text('');
    });

    // Form submission
    $('#editAdvertisementForm').submit(function (e) {
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
        const images = $('#images').val();
        const old_image = $('#old_image').val().trim();
        const ad_position = $('#ad_position').val().trim();
        const ads_url = $('#ads_url').val().trim();
        const status = $('input[name="status"]:checked').val();

        let hasErrors = false;

        // Client-side validation
        if (!types) {
            $('span.error-text.types_error').text('Please select a type.');
            hasErrors = true;
        }
        if (!buyer_vendor_name) {
            $('span.error-text.buyer_vendor_name_error').text('Please enter the buyer or vendor name.');
            hasErrors = true;
        } else if (buyer_vendor_name.length < 2) {
            $('span.error-text.buyer_vendor_name_error').text('Name must be at least 2 characters.');
            hasErrors = true;
        }
        if (!received_on) {
            $('span.error-text.received_on_error').text('Please select the received on date.');
            hasErrors = true;
        }
        if (!payment_received_on) {
            $('span.error-text.payment_received_on_error').text('Please select the payment received on date.');
            hasErrors = true;
        }
        if (!validity_period_from) {
            $('span.error-text.validity_period_from_error').text('Please select the validity period from date.');
            hasErrors = true;
        }
        if (!validity_period_to) {
            $('span.error-text.validity_period_to_error').text('Please select the validity period to date.');
            hasErrors = true;
        }
        if (!images && !old_image) {
            $('span.error-text.images_error').text('Please upload an image.');
            hasErrors = true;
        }
        if (!ad_position) {
            $('span.error-text.ad_position_error').text('Please select an ad position.');
            hasErrors = true;
        }
        if (!ads_url) {
            $('span.error-text.ads_url_error').text('Please enter the ad URL.');
            hasErrors = true;
        }
        if (!status) {
            $('span.error-text.status_error').text('Please select a status.');
            hasErrors = true;
        }

        if (hasErrors) return;

        // AJAX submission
        $.ajax({
            url: $(this).attr('action'),
            type: "POST",
            data: new FormData(this),
            contentType: false,
            processData: false,
            dataType: "json",
            beforeSend: function() {
                $('#editAdvertisementForm').find('button[type="submit"]').prop('disabled', true);
            },
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => {
                        window.location.href = "{{ route('admin.advertisement.index') }}";
                    }, 300);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function (key, value) {
                        const errorField = key.replace('.', '_');
                        $(`span.error-text.${errorField}_error`).text(value[0]);
                    });
                } else {
                    toastr.error('An error occurred. Please try again.');
                }
            },
            complete: function() {
                $('#editAdvertisementForm').find('button[type="submit"]').prop('disabled', false);
            }
        });
    });

    // Limit characters function
    window.limitCharacters = function(element, maxChars) {
        if (element.value.length > maxChars) {
            element.value = element.value.substr(0, maxChars);
        }
    };

    // Set minimum date for validity_period_to
    window.set_to_date = function() {
        const fromDate = $('#validity_period_from').val();
        if (fromDate) {
            $('#validity_period_to').attr('min', fromDate);
        }
    };
});
</script>
@endsection
