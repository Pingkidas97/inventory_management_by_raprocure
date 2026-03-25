@extends('vendor.layouts.app_second')
@section('title', 'Add Multiple Products')
@section('content')
<style>
    .bg-success { background: #d4edda !important; color: #155724 !important; padding: 5px; }
    .bg-danger { background: #f8d7da !important; color: #721c24 !important; padding: 5px; }
</style>
<main class="main main-inner-page flex-grow-1 py-2 px-md-3 px-1">
<section class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-global py-2 mb-0">
            <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
            <li class="breadcrumb-item">Manage Products</li>
            <li class="breadcrumb-item active" aria-current="page">Add Multiple Products</li>
        </ol>
    </nav>
    <section class="manage-product card">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h1 class="card-title font-size-18 mb-0">Add Multiple Products</h1>
                <a href="{{ route('vendor.products.index') }}" class="ra-btn ra-btn-primary font-size-12">
                    <span class="bi bi-arrow-left-square"></span> Back
                </a>
            </div>
            <div class="card-body">
                <div class="note-text fw-bold px-2">
                    <p style="margin-bottom: 0.3rem;">Note: 1. Type the name of your product. All products with same name will appear below. Select the products you deal in.</p>
                    <p style="margin-bottom: 0.3rem;">2. Add Dealer type, GST and HSN Code, then press 'Apply to all' button.</p>
                    <p style="margin-bottom: 0.3rem;">3. Click on Submit button at the bottom of the page.</p>
                </div>

                <div class="row my-3">
                    <div class="col-sm-3">
                        <label for="productSearch" class="col-form-label">Product Name <span class="text-danger">*</span></label>
                    </div>

                    <div class="col-md-4 position-relative">
                        <input oninput="this.value=this.value.replace(/[^a-z0-9 ]/gi,'')" onpaste="alert('Paste not allowed.'); return false;" 
                               type="text" id="productSearch" class="form-control" placeholder="Enter Product Name" autocomplete="off" autofocus="">
                        <div id="searchHint" class="position-absolute bg-white border p-2 mt-1" style="display: none; z-index: 1000; width: 100%;">
                           <font style="color:#6aa510;">Please enter more than 3 characters.</font>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <button type="button" id="resetSearch" class="ra-btn ra-btn-outline-danger"><i class="bi bi-arrow-clockwise"></i> Reset</button>
                    </div>
                </div>

                <div class="row mb-3" id="bulkSelectRow" style="display:none;">
                    <div class="col-md-3">
                        <select id="bulk_dealer_type" class="form-select">
                            <option value="">Select Dealer Type</option>
                            @foreach($dealertypes as $dealer)
                                <option value="{{ $dealer->id }}">{{ $dealer->dealer_type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="bulk_gst" class="form-select">
                            <option value="">Select GST Rate</option>
                            @foreach($taxes as $gst)
                                <option value="{{ $gst->id }}">{{ $gst->tax }}%</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" id="bulk_hsn" class="form-control" placeholder="Enter HSN Code" maxlength="8">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="ra-btn ra-btn-primary" id="applyBulk">Apply To All</button>
                    </div>
                </div>

                <form id="productForm" enctype="multipart/form-data">
                    <table class="table ra-table ra-table-stripped add-multi-product-table table-responsive" id="productTable">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 60px;">Sr. No</th>
                                <th class="text-center">Product Name <span class="text-danger">*</span></th>
                                <th class="text-center">Upload Picture <br><span class="text-danger">(JPEG/JPG/PNG)<i class="bi bi-paperclip" style="font-size: 20px;"></i></span></th>
                                <th class="text-center">Product Description <span class="text-danger">*</span></th>
                                <th class="text-center">Dealer Type <span class="text-danger">*</span></th>
                                <th class="text-center">GST/Sales Tax Rate <span class="text-danger">*</span></th>
                                <th class="text-center">HSN Code <span class="text-danger">*</span></th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr class="no-data">
                                <td colspan="8"><p> <b>Note : </b>  Start typing the name of your product in Search box, Product will start appearing in here, and then select product to upload in your product.</p></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="text-center" id="submitContainer" style="display: none;">
                        <button type="button" id="submitProducts" class="ra-btn ra-btn-primary">
                            <i class="bi bi-save"></i> Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</section>
</main>
@endsection

@section('scripts')
<script>
let rowCount = 0;

// Search products
$('#productSearch').on('input', function() {
    let term = $(this).val().trim();
    
    if (term.length < 3) {
        $('#searchHint').show();
        $('#bulkSelectRow').hide();
        $('#submitContainer').hide();
        return;
    } else {
        $('#searchHint').hide();
    }

    $.post("{{ route('vendor.addmultiple.products.autocomplete') }}", { term: term, _token: '{{ csrf_token() }}' }, function(res) {
        $('#searchHint').hide();
        if (res.length === 0) {
            $('#productTable tbody').html('<tr class="no-data"><td colspan="8"><b>No products found</b></td></tr>');
            $('#bulkSelectRow').hide();
            $('#submitContainer').hide();
            return;
        }

        $('#bulkSelectRow').show();
        $('#productTable tbody').empty();
        res.forEach((item, i) => {
            $('#productTable tbody').append(renderRow(item, i));
            verifyRow(i);
        });

        $('[data-bs-toggle="tooltip"]').tooltip();
        $('#submitContainer').show();
    }, 'json');
});

// Reset
$('#resetSearch').click(() => {
    $('#productSearch').val('');
    $('#productTable tbody').html('<tr class="no-data"><td colspan="8"><b>Start typing product name above...</b></td></tr>');
    $('#bulkSelectRow').hide();
    $('#submitContainer').hide();
});

// Render a row
function renderRow(product, index) {
    return `<tr data-index="${index}">
        <td><input type="checkbox" class="row-check"></td>
        <td>${product.value}<input type="hidden" name="products[${index}][id]" value="${product.id}"></td>
        <td>
            <div class="simple-file-upload">
                <input type="file" name="products[${rowCount}][product_image]" id="uploadFile_${rowCount}" class="real-file-input" style="display: none;" accept="image/jpeg,image/jpg,image/png,image/gif">
                <div class="file-display-box form-control text-start font-size-12 text-dark"
                     role="button"
                     data-bs-toggle="tooltip"
                     data-bs-placement="top"
                     title="Select an image file">
                    Upload file
                </div>
            </div>
        </td>
        <td><input type="text" name="products[${index}][desc]" class="form-control desc-input" value="${product.value}"></td>
        <td>
            <select name="products[${index}][dealer]" class="form-select dealer-input">
                <option value="">Select</option>
                @foreach($dealertypes as $dealer)
                <option value="{{ $dealer->id }}">{{ $dealer->dealer_type }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="products[${index}][gst]" class="form-select gst-input">
                <option value="">Select</option>
                @foreach($taxes as $gst)
                <option value="{{ $gst->id }}">{{ $gst->tax }}%</option>
                @endforeach
            </select>
        </td>
        <td><input type="text" name="products[${index}][hsn]" class="form-control hsn-input" maxlength="8"></td>
        <td class="status-msg"></td>
    </tr>`;
}

// Bulk apply
$('#applyBulk').click(() => {
    let dealer = $('#bulk_dealer_type').val();
    let gst = $('#bulk_gst').val();
    let hsn = $('#bulk_hsn').val();
    $('#productTable tbody tr').each(function(i) {
        if (dealer) $(this).find('.dealer-input').val(dealer);
        if (gst) $(this).find('.gst-input').val(gst);
        if (hsn) $(this).find('.hsn-input').val(hsn);
        verifyRow(i);
    });
});

// Verify row
$(document).on('change input', '.desc-input, .dealer-input, .gst-input, .hsn-input', function() {
    let index = $(this).closest('tr').data('index');
    verifyRow(index);
});

function verifyRow(index) {
    let row = $(`#productTable tbody tr[data-index="${index}"]`);
    let desc = row.find('.desc-input').val().trim();
    let dealer = row.find('.dealer-input').val();
    let gst = row.find('.gst-input').val();
    let hsn = row.find('.hsn-input').val();
    let status = row.find('.status-msg');

    let errors = [];

    if (!desc) errors.push('<div><i class="bi bi-x-circle-fill text-danger"></i> Enter Description</div>');
    if (!dealer) errors.push('<div><i class="bi bi-x-circle-fill text-danger"></i> Select Dealer Type</div>');
    if (!gst) errors.push('<div><i class="bi bi-x-circle-fill text-danger"></i> Select GST Rate</div>');
    if (!hsn || !/^\d{2,8}$/.test(hsn)) errors.push('<div><i class="bi bi-x-circle-fill text-danger"></i> Enter Valid HSN Code (2-8 digits)</div>');

    if (errors.length === 0) {
        status.html('<span class="bg-success">OK</span>');
        row.data('valid', 1);
    } else {
        status.html('<div class="bg-danger">' + errors.join('') + '</div>');
        row.data('valid', 0);
    }
}

// Submit
$('#submitProducts').click(() => {
    let validRows = $('#productTable tbody tr').filter(function() {
        return $(this).find('.row-check').is(':checked') && $(this).data('valid') == 1;
    });

    if (validRows.length === 0) {
        alert('Please select at least one valid product.');
        return;
    }

    let formData = new FormData();

    $('#productTable tbody tr').each(function() {
        let row = $(this);
        let index = row.data('index');

        let isChecked = row.find('.row-check').is(':checked') ? 1 : 0;

        formData.append(`products[${index}][checked]`, isChecked);
        formData.append(`products[${index}][product_id]`, row.find(`input[name="products[${index}][id]"]`).val());
        formData.append(`products[${index}][ps_desc]`, row.find('.desc-input').val());
        formData.append(`products[${index}][dealer_type]`, row.find('.dealer-input').val());
        formData.append(`products[${index}][tax_class]`, row.find('.gst-input').val());
        formData.append(`products[${index}][ean_code]`, row.find('.hsn-input').val());

        const fileInput = row.find('.real-file-input')[0];
        if (fileInput && fileInput.files.length > 0) {
            formData.append(`products[${index}][product_image]`, fileInput.files[0]);
        }
    });

    formData.append('_token', '{{ csrf_token() }}');

    const $btn = $('#submitProducts');
    $btn.addClass('disabled').html('<span class="spinner-border spinner-border-sm"></span> Processing...');

    $.ajax({
        url: "{{ route('vendor.addmultiple.products.store') }}",
        type: "POST",
        dataType: 'json',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response.status == 1) {
                toastr.success(response.message || 'Products uploaded successfully.');
                setTimeout(() => {
                    window.location.href = "{{ route('vendor.products.index') }}";
                }, 300);
            } else {
                alert(response.message || 'Error submitting products');
            }
        },
        error: function(xhr) {
            alert('Something went wrong. Please try again.');
            console.error(xhr.responseText);
        },
        complete: function() {
            $btn.removeClass('disabled').html('<i class="bi bi-save"></i> Submit');
        }
    });
});

// File handling
$(document).on('click', '.file-display-box', function() {
    $(this).closest('.simple-file-upload').find('.real-file-input').click();
});
$(document).on('change', '.real-file-input', function() {
    const fileName = this.files[0] ? this.files[0].name : 'Upload file';
    $(this).siblings('.file-display-box').text(fileName);
});
</script>
@endsection
