@extends('vendor.layouts.app_second')
@section('title', 'Add Product - Raprocure')
@section('content')
<style>
    .suggestions-container {
        position: absolute;
        background-color: white;
        border: 1px solid #ccc;
        width: 350px !important;
        max-height: 200px;
        overflow-y: auto;
        z-index: 999;
    }
    .suggestions-list {
        list-style-type: none;
        margin: 0;
        padding: 0;
    }
    .suggestion-item {
        padding: 8px;
        cursor: pointer;
    }
    .suggestion-item:hover {
        background-color: #f1f1f1;
    }
    .result-message {
        padding: 5px;
        font-size: 14px;
        background: #f1f2f2;
        color: #000;
    }
    .bg-success {
        background: #d4edda !important;
        color: #155724 !important;
        padding: 5px;
    }
    .bg-danger {
        background: #f8d7da !important;
        color: #721c24 !important;
        padding: 5px;
    }
    .error-message {
        padding: 8px;
        font-size: 14px;
        background: #f8d7da;
        color: #721c24;
        border-radius: 4px;
        margin-top: 4px;
    }
    .error-message .bi {
        font-size: 16px;
        min-width: 20px;
    }
    .error-message div {
        line-height: 1.4;
    }
</style>
<main class="main main-inner-page flex-grow-1 py-2 px-md-3 px-1">
<section class="container-fluid">
    <!-- Start Breadcrumb Here -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-global py-2 mb-0">
            <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
            <li class="breadcrumb-item">Manage Products</li>
            <li class="breadcrumb-item active" aria-current="page">Add Multiple Productst</li>
        </ol>
    </nav>
    <!-- Start Product Content Here -->
    <section class="manage-product card">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between bg-white py-3">
                <h1 class="card-title font-size-18 mb-0">Add Multiple Products</h1>
                <a href="javascript:void(0)"
                    class="ra-btn ra-btn-primary d-flex align-items-center font-size-12">
                    <span class="bi bi-arrow-left-square font-size-11"></span>
                    <span class="font-size-11">Back</span>
                </a>
            </div>
            <div class="card-body add-product-section">
                <div class="note-text fw-bold px-2">
                    <p>Note: 1. Type the name of your product in the box. All products with same name, will
                        appear on the screen below. And then specifically select the products you deal in.
                    </p>
                    <p>2. Next add Dealer type, GST and HSN Code, then press 'Apply to all' button.</p>
                    <p>3. Click on Submit button, which is placed on bottom of the page.</p>
                </div>
                <div class="row align-items-center my-4">
                    <div class="col-sm-3">
                        <label for="productSearch" class="col-form-label">Product Name <span
                                class="text-danger">*</span></label>
                    </div>
                    <div class="col-sm-4 position-relative">
                        <!-- Search Input -->
                        <input class="form-control" id="productSearch" name="product" autocomplete="off" placeholder="Enter Product Name">
                        <!-- Search Results Dropdown -->
                        <div id="searchResults" class="position-absolute w-100 bg-white border mt-1 d-none" style="z-index: 1000; max-height: 300px; overflow-y: auto;">
                            <ul class="list-group list-group-flush" id="resultsList"></ul>
                        </div>
                    </div>
                    <div class="col-sm-4 text-sm-end mt-3 mt-sm-0">
                        <button type="button" class="ra-btn ra-btn-outline-danger" id="resetSearch"><span class="bi bi-arrow-clockwise" aria-hidden="true"></span><span class="font-size-11">RESET</span> </button>
                    </div>
                </div>
                
                <!-- Bulk Selection -->
                <div class="row gx-3 bulk-selection">
                    <!-- ... (keep your existing bulk selection HTML) ... -->
                </div>

                <div class="table-responsive">
                    <table class="table ra-table ra-table-stripped add-multi-product-table">
                        <thead>
                            <!-- ... (keep your existing table header) ... -->
                        </thead>
                        <tbody>
                            <!-- Table rows will be added dynamically -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Below note will display while no Product  -->
                <div class="note-text-2 px-2 py-3"><b> Note : </b> Start typing the name of your product in Search
                    box, Product will start appearing in here, and then select product to upload in your
                    product.
                </div>

               
                <div class="text-center py-3">
                    <button type="button" class="ra-btn ra-btn-primary font-size-12" id="submitProducts">
                        <span class="bi bi-save font-size-12"></span>
                        <span class="font-size-11">Submit</span>
                    </button>
                </div>
            </div>
        </div>
    </section>
</section>
</main>
@section('scripts')
<script>
$(document).ready(function() {
    let currentSearchTerm = '';

    // Search products on input
    $('#productSearch').on('input', function() {
        currentSearchTerm = $(this).val().trim();
        if (currentSearchTerm.length >= 2) {
            searchProducts(currentSearchTerm);
        } else {
            $('#searchResults').addClass('d-none');
            $('.add-multi-product-table tbody').empty(); // Clear table if search term is too short
            $('.note-text-2').show(); // Show note when table is empty
        }
    });

    // Reset search
    $('#resetSearch').click(function() {
        $('#productSearch').val('');
        $('#searchResults').addClass('d-none');
        $('.add-multi-product-table tbody').empty(); // Clear table on reset
        $('.note-text-2').show(); // Show note when table is cleared
    });

    // Search products function
    function searchProducts(term) {
        $.ajax({
            url: "{{ route('vendor.addmultiple.products.autocomplete') }}",
            dataType: "json",
            data: {
                term: term
            },
            success: function(data) {
                $('#searchResults').addClass('d-none'); // Hide dropdown as it's no longer needed
                updateTable(data); // Directly update table with search results
            }
        });
    }

    // Update table with search results
    function updateTable(results = []) {
        const tableBody = $('.add-multi-product-table tbody');
        tableBody.empty(); // Clear existing rows

        if (results.length === 0) {
            $('.note-text-2').show(); // Show note if no results
            return;
        }

        $('.note-text-2').hide(); // Hide note if results are found

        // Add each product to the table
        results.forEach((product, index) => {
            addProductToTable({ id: product.id, value: product.label }, index);
        });

        // Re-initialize file upload and validation events for new rows
        initFileUpload();
        initValidationEvents();
    }

    // Function to add product to table
    function addProductToTable(product, index) {
        var newRow = `
            <tr data-product-id="${product.id}">
                <td class="align-bottom" style="width: 60px;">
                    <input type="checkbox" name="selected_rows[]" class="select-row-checkbox">
                    ${index + 1}
                    <input type="hidden" name="products[${index}][product_id]" id="product_id_${index}" value="${product.id}">
                    <input type="hidden" name="products[${index}][status]" id="status_${index}" value="3">
                </td>
                <td class="align-bottom">
                    <input type="text" name="products[${index}][product_name]" id="product_name_${index}" class="form-control" value="${product.value}" readonly>
                </td>
                <td class="align-bottom">
                    <div class="simple-file-upload">
                        <input type="file" name="products[${index}][image]" class="real-file-input" style="display: none;">
                        <div class="file-display-box form-control text-start font-size-12 text-dark" role="button">
                            Upload file
                        </div>
                    </div>
                </td>
                <td class="align-bottom">
                    <input type="text" name="products[${index}][description]" id="ps_desc_${index}" class="form-control" maxlength="500">
                </td>
                <td class="align-bottom">
                    <select class="form-select" name="products[${index}][dealer_type_id]" id="dealer_type_${index}" required>
                        <option value="">Select Dealer Type</option>
                        <option value="1">Manufacturer</option>
                        <option value="2">Trader</option>
                    </select>
                </td>
                <td class="align-bottom">
                    <select class="form-select" name="products[${index}][gst_id]" id="tax_class_${index}" required>
                        <option value="">Select</option>
                        <option value="1">0%</option>
                        <option value="2">3%</option>
                        <option value="3">5%</option>
                        <option value="4">12%</option>
                        <option value="5">18%</option>
                        <option value="6">28%</option>
                    </select>
                </td>
                <td class="align-bottom">
                    <input type="text" name="products[${index}][hsn_code]" id="ean_code_${index}" class="form-control" maxlength="8" required>
                </td>
                <td class="message">
                    <div class="font-size-13" id="product_message_${index}" style="padding:5px">
                        Enter Dealer Type, GST Rate, HSN Code
                    </div>
                </td>
            </tr>
        `;
        
        // Append the new row
        $('.add-multi-product-table tbody').append(newRow);

        // Run initial validation for the new row
        verifyproductlst(index);
    }

    // Initialize file upload functionality
    function initFileUpload() {
        $('.simple-file-upload').off('click').on('click', function() {
            var fileInput = $(this).find('.real-file-input');
            fileInput.click();
        });

        $('.real-file-input').off('change').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).siblings('.file-display-box').text(fileName || 'Upload file');
        });
    }

    // Initialize validation events
    function initValidationEvents() {
        // Add event listeners for Dealer Type, GST Rate, HSN Code, and Description
        $('.add-multi-product-table').on('change input', 'select[name*="[dealer_type_id]"], select[name*="[gst_id]"], input[name*="[hsn_code]"], input[name*="[description]"]', function() {
            var row = $(this).closest('tr');
            var index = row.index(); // Get row index
            verifyproductlst(index); // Run validation
        });
    }

    // Apply bulk selection to all rows
    $('.btn-bulk-apply').click(function() {
        var dealerType = $('#dealerType').val();
        var gstSales = $('#gstSales').val();
        var hsnCode = $('#hsnCode').val();

        if (!dealerType || !gstSales || !hsnCode) {
            alert('Please fill all bulk selection fields');
            return;
        }

        $('.add-multi-product-table tbody tr').each(function(index) {
            $(this).find('select[name*="[dealer_type_id]"]').val(dealerType);
            $(this).find('select[name*="[gst_id]"]').val(gstSales);
            $(this).find('input[name*="[hsn_code]"]').val(hsnCode);
            verifyproductlst(index); // Re-validate each row after bulk update
        });
    });

    // Submit products
    $('#submitProducts').click(function() {
        var allValid = true;
        $('.add-multi-product-table tbody tr').each(function(index) {
            verifyproductlst(index); // Validate each row
            var status = $(this).find(`#status_${index}`).val();
            if (status !== '1' && status !== '2') {
                allValid = false;
            }
        });

        if (!allValid) {
            alert('Please ensure all products are valid before submitting.');
            return;
        }

        // Add your form submission logic here
        alert('Products submitted!');
    });

    // Validation function
    function verifyproductlst(tab_index) {
        var product_name = $(`#product_name_${tab_index}`).val();
        var product_id = $(`#product_id_${tab_index}`).val();
        var ps_desc = $(`#ps_desc_${tab_index}`).val().replace(/\s+/g, '');
        var dealer_type = $(`#dealer_type_${tab_index}`).val();
        var tax_class = $(`#tax_class_${tab_index}`).val();
        var ean_code = $(`#ean_code_${tab_index}`).val();

        // For existing products (selected from dropdown)
        if (product_id && product_name !== '' && ps_desc !== '' && dealer_type !== '' && tax_class !== '' && ean_code !== '' && $.isNumeric(ean_code) && ean_code.length > 1 && ean_code.length < 9 && parseInt(ean_code) > 9 && parseInt(ean_code) <= 99999999) {
            $(`#product_message_${tab_index}`).removeClass('bg-danger').addClass('bg-success');
            $(`#product_message_${tab_index}`).html('OK'); // Show "OK" instead of "Product Verified"
            $(`#status_${tab_index}`).val('1');
        } 
        // For new products (not selected from dropdown)
        else if (!product_id && product_name !== '' && ps_desc !== '' && dealer_type !== '' && tax_class !== '' && ean_code !== '' && $.isNumeric(ean_code) && ean_code.length > 1 && ean_code.length < 9 && parseInt(ean_code) > 9 && parseInt(ean_code) <= 99999999) {
            $(`#product_message_${tab_index}`).removeClass('bg-danger').addClass('bg-success');
            $(`#product_message_${tab_index}`).html('OK'); // Show "OK" instead of "New Product"
            $(`#status_${tab_index}`).val('2');
        }
        else {
            var error_msg = '<div class="text-start">';
            var error_count = 0;

            if (product_name === '') {
                error_msg += '<div class="d-flex align-items-center mb-1">';
                error_msg += '<span class="bi bi-x-circle-fill text-danger me-2"></span>';
                error_msg += '<span>Enter Product Name</span>';
                error_msg += '</div>';
                error_count++;
            }
            if (ps_desc === '') {
                error_msg += '<div class="d-flex align-items-center mb-1">';
                error_msg += '<span class="bi bi-x-circle-fill text-danger me-2"></span>';
                error_msg += '<span>Enter Description</span>';
                error_msg += '</div>';
                error_count++;
            }
            if (dealer_type === '') {
                error_msg += '<div class="d-flex align-items-center mb-1">';
                error_msg += '<span class="bi bi-x-circle-fill text-danger me-2"></span>';
                error_msg += '<span>Select Dealer Type</span>';
                error_msg += '</div>';
                error_count++;
            }
            if (tax_class === '') {
                error_msg += '<div class="d-flex align-items-center mb-1">';
                error_msg += '<span class="bi bi-x-circle-fill text-danger me-2"></span>';
                error_msg += '<span>Select GST/Sales Tax Rate</span>';
                error_msg += '</div>';
                error_count++;
            }
            if (ean_code === '' || !$.isNumeric(ean_code)) {
                error_msg += '<div class="d-flex align-items-center mb-1">';
                error_msg += '<span class="bi bi-x-circle-fill text-danger me-2"></span>';
                error_msg += '<span>Enter Valid HSN Code</span>';
                error_msg += '</div>';
                error_count++;
            } else if (ean_code.length <= 1 || ean_code.length >= 9 || parseInt(ean_code) <= 9 || parseInt(ean_code) > 99999999) {
                error_msg += '<div class="d-flex align-items-center mb-1">';
                error_msg += '<span class="bi bi-x-circle-fill text-danger me-2"></span>';
                error_msg += '<span>HSN Code must be 2-8 digits</span>';
                error_msg += '</div>';
                error_count++;
            }

            error_msg += '</div>';

            $(`#status_${tab_index}`).val('3');
            $(`#product_message_${tab_index}`).addClass('bg-danger').removeClass('bg-success');

            if (error_count > 0) {
                $(`#product_message_${tab_index}`).html('<div class="fw-bold mb-1">Invalid Product</div>' + error_msg);
            } else {
                $(`#product_message_${tab_index}`).html('');
            }
        }
    }

    // Initialize file upload for existing rows
    initFileUpload();
});
</script>
@endsection
@endsection