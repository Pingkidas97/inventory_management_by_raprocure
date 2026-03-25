@extends('vendor.layouts.app_second',['title'=>'Help and Support','sub_title'=>'Edit'])

@section('css')

@endsection
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i> <a href="{{ route('vendor.dashboard') }}">Dashboard</a>
            <a href="{{ route('vendor.help_support.index') }}"> ->Support</a>
            <a href="javascript:void(0);"> -> Write to Us</a>
        </h5>
    </div>
</div>
@endsection

@section('content')
<section class="container-fluid">
    <!-- Start Product Content Here -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-global py-2 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Help and Support</li>
        </ol>
    </nav>
    <section class="manage-product">
        <div class="row justify-content-center pt-5">
            <div class="col-xl-8 col-lg-8 col-md-9 col-sm-12">
                <div class="card border-0">
                    <div class="card-body">
                        <h5 class="border-bottom">Write to Us</h5>
                        <form action="{{route('vendor.help_support.store')}}" method="post">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-6 p-2">
                                    <div class="form-group">
                                        <label for="issue_type" class="form-label">Issue Type: <span class="text-danger">*</span></label>
                                        <select class="form-select" id="issue_type" name="issue_type">
                                            <option value="">Select Issue</option>
                                            <option value="CIS Sheet Issue">CIS Sheet Issue</option>
                                            <option value="RFQ Received">Compose RFQ Issue</option>
                                            <option value="RFQ Received">Bulk RFQ Issue</option>
                                            <option value="RFQ Received">RFQ Received</option>
                                            <option value="Confirm Order">Confirm Order</option>
                                            <option value="Product Issue">Product Issue</option>
                                        </select>
                                        <span class="text-danger error-text issue_type_error"></span>
                                        @error('issue_type')
                                        <span class="text-danger error-text">{{ $message }}</span>
                                        @enderror
                                    </div>

                                </div>
                                <div class="col-md-6 p-2">
                                    <div class="form-group pt-4">
                                        <div class="my-rfq-input">
                                            <div class="file-browse">
                                                <span class="button button-browse">
                                                    Select
                                                    <input onchange="validateImageFile(this, 'PDF/PNG/JPG/JPEG/DOCX/XLSX')" id="image" type="file" name="image" value="" class="form-control product-add-input" oninput="">
                                                </span>
                                                <input type="text" class="form-control" placeholder="Upload File" readonly>
                                                <span title="" class="tooltip-img vendor-file-custom-tooltip"  data-tooltip="(Maximum allowed file size 1MB, PDF, DOC, Excel, Word, PNG, JPG, JPEG, CDR, DWG, Image)"><i class="bi bi-question-circle"></i>
                                                </span>
                                            </div>
                                            <div class="rfq-file-name-div">
                                                <span class="rfq-file-name d-none"></span>
                                                <span class="remove-rfq-file_buy btn-rfq btn-rfq-sm d-none"><i class="bi bi-trash text-danger"></i></span>
                                            </div>
                                        </div>
                                        <span class="help-block" id="error_msg_image"></span>
                                        @error('image')
                                        <span class="text-danger error-text">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12 p-2">
                                    <div class="form-group">
                                        <label for="description" class="form-label">Description<span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="description" name="description"></textarea>
                                        <span class="text-danger error-text description_error"></span>
                                        @error('description')
                                        <span class="text-danger error-text">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="ra-btn ra-btn-primary m-1"><i class="bi bi-send"></i> Save</button>
                                <a href="{{ route('vendor.help_support.index') }}"  class="ra-btn ra-btn-outline-danger m-1">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>

@endsection

@section('scripts')
<script>
$(document).on("click", ".remove-rfq-file", function() {
    $(".bulk-rfq-input").val('');
    $(".bulk-rfq-input").attr('src', '');
    $(".rfq-file-name").addClass('d-none').html('');
    $(".remove-rfq-file").addClass('d-none');
    $(".invalid-product-row").html('');
    $(".invalid-product-section").addClass('d-none');
});

function validateImageFile(obj, ext) {
    var avatarok = 1;
    var avatar = $(obj).val();
    var extension = avatar.split('.').pop().toUpperCase();
    checkExtension = ext.split('/');
    if (checkExtension.indexOf(extension) < 0) { // || extension != "XLS"//XLSB
        console.log("file extension: ", extension, avatar);
        $(obj).val('');
        $(obj).attr('src', '');
        $(".rfq-file-name").addClass('d-none').html('');
        $(".remove-rfq-file").addClass('d-none');
        appendFileError(obj, "Invalid file extension.");
    } else {
        $(".rfq-file-name").removeClass('d-none').html(avatar.split('\\').pop());
        $(".remove-rfq-file").removeClass('d-none');
        appendFileError(obj);
    }
}

function validateAttachment(obj, ext) {
    var avatarok = 1;
    var avatar = $(obj).val();
    var extension = avatar.split('.').pop().toUpperCase();
    checkExtension = ext.split('/');
    if (checkExtension.indexOf(extension) < 0) { // || extension != "XLS"//XLSB
        console.log("file extension: ", extension, avatar);
        $(obj).val('');
        $(obj).attr('src', '');
        $(".rfq-file-name").addClass('d-none').html('');
        $(".remove-rfq-file").addClass('d-none');
        appendFileError(obj, "Invalid file extension.");
    } else {
        $(".rfq-file-name").removeClass('d-none').html(avatar.split('\\').pop());
        $(".remove-rfq-file").removeClass('d-none');
        appendFileError(obj);
    }
}

function appendFileError(obj, msg = '') {
    $(obj).parents('.file-browse').parent().find('.error-message').remove();
    if (msg) {
        $(obj).parents('.file-browse').parent().append('<span class="help-block text-danger error-message">' + msg +
            '</span>');
    }
}
</script>
@endsection