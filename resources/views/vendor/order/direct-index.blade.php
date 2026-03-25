@extends('vendor.layouts.app_second',['title'=>'Orders Confirmed','sub_title'=>'Direct Orders Confirmed'])
@section('css')

@endsection
 

@section('content')
<section class="container-fluid">
    <!-- Start Product Content Here -->
    <section class="manage-product card">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h1 class="card-title font-size-18 mb-0">Direct Orders Confirmed</h1>
                <!-- Search Section -->
                <form id="searchForm" action="{{ route('vendor.direct_order.index') }}" method="GET">
                    <div class="row align-items-center flex-wrap flex-wrap gx-3 gy-4 pt-3">
                        <div class="col-12 col-sm-6 col-md-auto">
                            <div class="input-group generate-rfq-input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-person" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="buyer_name" name="buyer_name" placeholder="Buyer Name" value="{{ request('buyer_name') }}">
                                    <label for="buyer_name">Buyer Name</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-md-auto">
                            <div class="input-group generate-rfq-input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-journal-text" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="order_no" name="order_no" placeholder="Order No"  value="{{ request('order_no') }}">
                                    <label for="order_no">Order No</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 col-sm-6 col-md-auto">
                            <div class="input-group generate-rfq-input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-record2" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <select name="status" id="status" class="form-select cw-200">
                                        <option value="">Select</option>
                                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Confirmed</option>
                                        <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    <label for="status">Status</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-auto">
                            <div class="d-flex align-item-center gap-3">
                                <button type="submit" class="ra-btn ra-btn-primary">
                                    <span class="bi bi-search font-size-12"></span>
                                    <span class="font-size-11">Search</span>
                                </button>
                                <a href="{{ route('vendor.direct_order.index') }}" class="ra-btn ra-btn-outline-danger">
                                    <span class="font-size-11">Reset</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body add-product-section">
                <div class="table-responsive" id="table-container">
                     @include('vendor.order.partials.direct-table', ['results' => $results])
                </div>
            </div>
        </div>
    </section>
</section>
 
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $(document).on('submit', '#searchForm', function(e) {
        e.preventDefault();
        loadTable($(this).attr('action') + '?' + $(this).serialize());
    });

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        loadTable($(this).attr('href'));
    });

    $(document).on('change', '#perPage', function () {
        const form = $('#searchForm');
        const formData = form.serialize();
        const perPage = $(this).val();
        const url = form.attr('action') + '?' + formData + '&per_page=' + perPage;
        loadTable(url);
    });

    function loadTable(url) {
        $.ajax({
            url: url,
            type: 'GET',
            beforeSend: function () {
                $('#table-container').html('<div class="text-center py-4">Loading...</div>');
            },
            success: function(response) {
                $('#table-container').html(response);
                if (history.pushState) {
                    history.pushState(null, null, url);
                }
            }
        });
    }
});
function validateFileSize(event) {
    var fileInput = event.target;
    var maxSize = 2 * 1024 * 1024; // 2MB in bytes
    var files = fileInput.files;

    if (files.length > 0) {
        for (var i = 0; i < files.length; i++) {
            var fileSize = files[i].size;
            if (fileSize > maxSize) {
                alert('File "' + files[i].name + '" size exceeds 2MB limit.');
                // Clear the value of the file input element
                fileInput.value = '';

                let file_name_section = $(fileInput).closest(".custom-file").find(".product-file-name-div");

                if (file_name_section.length > 0) {
                    file_name_section.find('.product-file-name').html('').addClass('d-none');
                    file_name_section.find('.remove-product-file').addClass('d-none');
                } else {
                    // Find the rfq-file-name span elements
                    const fileNameSpans = document.querySelectorAll('.rfq-file-name-div, .delete-rfq-file, .product-file-name-div');
                    // Add the class 'd-none' to each fileNameSpan element
                    fileNameSpans.forEach(function(element) {
                        element.classList.add('d-none');
                    });
                }
            } else {
                // File is valid, show file name and remove 'd-none' class
                let file_name_section = $(fileInput).closest(".custom-file").find(".product-file-name-div");
                if (file_name_section.length > 0) {
                    file_name_section.find('.product-file-name').html(files[i].name).removeClass('d-none');
                    file_name_section.find('.remove-product-file').removeClass('d-none');
                }

                const fileNameSpans = document.querySelectorAll(
                    '.rfq-file-name-div, .delete-rfq-file, .product-file-name-div');
                fileNameSpans.forEach(function(element) {
                    element.classList.remove('d-none');
                });
            }
        }
    }
}

$(document).on("change", 'input[type="file"]', function(event) {
    validateFileSize(event);
});
//================= Validate PI file ===============//
function validatePIFile(obj, ext = 'JPEG/JPG/PNG/DOC/DOCX/PDF') {
    var file = obj.files[0]; // Get the selected file
    if (!file) return;

    // Check file size (max 1MB = 1048576 bytes)
    if (file.size > 1048576) {
        $(obj).val('');
        appendFileError(obj, "File size must be less than 1MB.");
        return;
    }

    var avatar = $(obj).val();
    var extension = avatar.split('.').pop().toUpperCase();
    var checkExtension = ext.split('/');
    if (checkExtension.indexOf(extension) < 0) {
        $(obj).val('');
        $(obj).attr('src', '');
        appendFileError(obj, "Invalid file extension.");
    } else {
        let order_number = $(obj).data('order-number');
        if (!confirm("Are you sure want to upload PI on Order No: " + order_number + " ?")) {
            $(obj).val('');
            $(obj).attr('src', '');
            return false;
        }
        appendFileError(obj);
        uploadPIFile(obj);
    }
}

function appendFileError(obj, msg = '') {
    $(obj).parents('.file-browse').parent().find('.error-message').remove();
    if (msg) {
        $(obj).parents('.file-browse').parent().append('<span class="help-block text-danger error-message">' + msg +
            '</span>');
    }
}

function uploadPIFile(obj) {
    let formData = new FormData();
    let fileInput = $(obj)[0];
    formData.append("_token", "{{ csrf_token() }}");
    if (fileInput && fileInput.files.length > 0) {
        formData.append("pi_attachment", fileInput.files[0]);
    } else {
        console.log("Please select file to upload");
        return;
    }

    let order_number = $(obj).data('order-number');
    formData.append("order_number", order_number);
    formData.append("order_type", "direct_order");

    $.ajax({
        type: "POST",
        url: '{{ route("vendor.upload.pi.attachment") }}',
        dataType: 'json',
        data: formData,
        contentType: false,
        cache: false,
        processData: false,
        success: function(response) {
            if (response.status == 0) {
                toastr.error(response.message);
            } else if (response.status == 1) {
                toastr.success(response.message);
                $(obj).val('');
                $(obj).attr('src', '');
                let filename = response.file_name;
                if (filename.length > 11) {
                    filename = filename.substring(0, 11) + '...';
                }
                let btn_html = '<a href="' + response.file_url + '/' + response.file_name +
                    '" target="_blank" download="PI Invoice for ' + order_number + '" >' + filename +
                    '</a>';
                $(obj).closest('.custom-file').children('.pi-file-name-div').find(".pi-file-name")
                    .removeClass('d-none').html(btn_html).attr('title', response.file_name);
                $(obj).closest('.custom-file').children('.pi-file-name-div').append(
                    '<span class="remove-pi-file btn-rfq btn-rfq-sm"><i class="bi bi-trash text-danger"></i></span>'
                    );
            }
        },
        error: function() {
            toastr.error('Something Went Wrong...');
        }
    });
}
$(document).on("click", ".remove-pi-file", function() {
    let order_number = $(this).parents(".custom-file").find(".pi-attachment-field").data("order-number");
    if (confirm("Are you sure want to delete uploaded PI Attachment on Order No: " + order_number + "?")) {

        let _this = this;
        let formData = new FormData();
        formData.append("order_number", order_number);
        formData.append("order_type", "rfq_order");

        $.ajax({
            type: "POST",
            url: 'https://tech.guruworkwithit.online/sysadmin/orders/deleteVendorOrderPI',
            dataType: 'json',
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function(response) {
                if (response.status == 0) {
                    toastr.error(response.message);
                } else if (response.status == 1) {
                    toastr.success(response.message);
                    $(_this).closest('.custom-file').children('.pi-file-name-div').html(
                        '<span class="pi-file-name d-none" title=""></span>');
                }
            },
            error: function() {
                toastr.error('Something Went Wrong...');
            }
        });
    }
});
</script>
@endsection