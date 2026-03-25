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
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vendor.products.index') }}">Manage Products</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add Fast Product</li>
            </ol>
        </nav>

        <!-- Start Product Content Here -->
        <section class="manage-product card">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between bg-white py-3">
                    <h1 class="card-title font-size-18 mb-0">Add Fast Product</h1>
                    <a href="{{ route('vendor.products.index') }}" class="ra-btn ra-btn-primary d-flex align-items-center font-size-12">
                        <span class="bi bi-arrow-left-square font-size-11"></span>
                        <span class="font-size-11">Back</span>
                    </a>
                </div>
                <div class="card-body add-product-section">
                    <p class="fw-bold">Start typing the name of your product, options will start appearing in the dropdown, and then select one.</p>

                    <div class="table-responsive">
                        <table class="table ra-table ra-table-stripped add-fast-product-table" id="productTable">
                            <thead>
                                <th scope="col" class="text-center text-uppercase text-nowrap">Sr. No</th>
                                <th scope="col" class="text-center text-uppercase text-nowrap">Product Name <span class="text-danger">*</span></th>
                                <th scope="col" class="text-center text-uppercase text-nowrap">Upload Picture</th>
                                <th scope="col" class="text-center text-uppercase text-nowrap">Product Description <span class="text-danger">*</span></th>
                                <th scope="col" class="text-center text-uppercase text-nowrap">Dealer Type <span class="text-danger">*</span></th>
                                <th scope="col" class="text-center text-uppercase text-nowrap">GST/Sales Tax Rate <span class="text-danger">*</span></th>
                                <th scope="col" class="text-center text-uppercase text-nowrap">HSN Code <span class="text-danger">*</span></th>
                                <th scope="col" class="text-center text-uppercase text-nowrap" style="width:200PX;">Status</th>
                                <th scope="col" class="text-center text-uppercase text-nowrap">Action</th>
                            </thead>
                            <tbody>
                                <tr data-row-index="1">
                                    <td>1</td>
                                    <td>
                                        <!-- Product search input field -->
                                        <input type="text" name="pro-name" id="product_name_1" class="form-control bulk-product-field" value="" autocomplete="off" maxlength="255" placeholder="Search Product..." data-row-index="1" />
                                        <input type="hidden" name="product_id" id="product_id_1" value="">

                                        <!-- Suggestions dropdown -->
                                        <div class="suggestions-container" id="suggestions-container-1" style="display: none;">
                                            <!-- Suggestions list -->
                                            <ul class="suggestions-list" id="suggestions-list-1">
                                                <!-- Suggestions will be appended here -->
                                            </ul>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="simple-file-upload">
                                            <input type="file" id="uploadFile-1" class="real-file-input" style="display: none;" />
                                            <div class="file-display-box form-control text-start font-size-12 text-dark" role="button" data-bs-toggle="tooltip" data-bs-placement="top">
                                                Upload file
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="pro-desc1" id="ps_desc_1" class="form-control" value="" maxlength="500" />
                                    </td>
                                    <td>
                                        <select class="form-select" name="select-dealer1" id="dealer_type_1">
                                            <option value="">Select Dealer Type</option>
                                            @foreach ($dealertypes as $dealerType)
                                            <option value="{{ $dealerType->id }}">{{ $dealerType->dealer_type }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select" name="select-tax1" id="tax_class_1">
                                            <option value="">Select</option>
                                            @foreach ($taxes as $tax)
                                            <option value="{{ $tax->id }}">
                                                {{ $tax->tax_name }} - {{ $tax->tax }}%
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="hsn-code1" id="ean_code_1" class="form-control" maxlength="8" value="" />
                                    </td>
                                    <td>
                                        <div id="product_message_1"></div>
                                        <input type="hidden" name="status1" id="status_1" value="">
                                    </td>
                                    <td class="text-center">
                                        <button class="ra-btn ra-btn-link p-0 mh-inherit remove-row" aria-label="Remove">
                                            <span class="bi bi-trash3 text-danger" aria-hidden="true"></span>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end py-3">
                        <button type="button" class="ra-btn ra-btn-primary font-size-12" id="addProductBtn">
                            <span class="bi bi-plus-square font-size-12"></span>
                            <span class="font-size-11">Add Product</span>
                        </button>
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
    // Add new product row
    document.getElementById("addProductBtn").addEventListener("click", function () {
        const tableBody = document.querySelector("#productTable tbody");
        const rowCount = tableBody.rows.length + 1;

        const newRow = document.createElement("tr");
        newRow.setAttribute("data-row-index", rowCount);
        newRow.innerHTML = `
            <td>${rowCount}</td>
            <td>
                <input type="text" name="pro-name" id="product_name_${rowCount}" class="form-control bulk-product-field" value="" autocomplete="off" maxlength="255" placeholder="Search Product..." data-row-index="${rowCount}">
                <input type="hidden" name="product_id" id="product_id_${rowCount}" value="">
                <div class="suggestions-container" id="suggestions-container-${rowCount}" style="display: none;">
                    <ul class="suggestions-list" id="suggestions-list-${rowCount}"></ul>
                </div>
            </td>
            <td>
                <div class="simple-file-upload">
                    <input type="file" id="uploadFile-${rowCount}" class="real-file-input" style="display: none;">
                    <div class="file-display-box form-control text-start font-size-12 text-dark" role="button" data-bs-toggle="tooltip" data-bs-placement="top">Upload file</div>
                </div>
            </td>
            <td>
                <input type="text" name="pro-desc${rowCount}" id="ps_desc_${rowCount}" class="form-control" value="" maxlength="500">
            </td>
            <td>
                <select class="form-select" name="select-dealer${rowCount}" id="dealer_type_${rowCount}">
                    <option value="">Select Dealer Type</option>
                    @foreach ($dealertypes as $dealerType)
                        <option value="{{ $dealerType->id }}">{{ $dealerType->dealer_type }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select class="form-select" name="select-tax${rowCount}" id="tax_class_${rowCount}">
                    <option value="">Select</option>
                    @foreach ($taxes as $tax)
                        <option value="{{ $tax->id }}">
                            {{ $tax->tax_name }} - {{ $tax->tax }}%
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="hsn-code${rowCount}" id="ean_code_${rowCount}" class="form-control" maxlength="8" value="">
            </td>
            <td>
                <div id="product_message_${rowCount}"></div>
                <input type="hidden" name="status${rowCount}" id="status_${rowCount}" value="">
            </td>
            <td class="text-center">
                <button class="ra-btn ra-btn-link p-0 mh-inherit remove-row" aria-label="Remove">
                    <span class="bi bi-trash3 text-danger" aria-hidden="true"></span>
                </button>
            </td>
        `;

        tableBody.appendChild(newRow);
    });

    // Event delegation to handle dynamically added rows
    document.querySelector("#productTable").addEventListener("click", function (event) {
        if (event.target && event.target.closest(".remove-row")) {
            const tableBody = document.querySelector("#productTable tbody");
            const row = event.target.closest("tr");

            // Check if there's only one row left
            if (tableBody.rows.length === 1) {
                alert("You cannot remove the last row");
            } else {
                row.remove();
                // Update row numbers after removal
                updateRowNumbers();
            }
        }
    });

    // Function to update row numbers after removal
    function updateRowNumbers() {
        const tableBody = document.querySelector("#productTable tbody");
        const rows = tableBody.querySelectorAll("tr");
        rows.forEach((row, index) => {
            const rowIndex = index + 1;
            row.setAttribute("data-row-index", rowIndex);
            row.querySelector("td").textContent = rowIndex; // Update Sr. No.
            // Update input and container IDs
            const input = row.querySelector(".bulk-product-field");
            input.setAttribute("data-row-index", rowIndex);
            input.id = `product_name_${rowIndex}`;
            const suggestionContainer = row.querySelector(".suggestions-container");
            suggestionContainer.id = `suggestions-container-${rowIndex}`;
            const suggestionList = row.querySelector(".suggestions-list");
            suggestionList.id = `suggestions-list-${rowIndex}`;
            
            // Update other IDs
            row.querySelector('[id^="ps_desc_"]').id = `ps_desc_${rowIndex}`;
            row.querySelector('[id^="dealer_type_"]').id = `dealer_type_${rowIndex}`;
            row.querySelector('[id^="tax_class_"]').id = `tax_class_${rowIndex}`;
            row.querySelector('[id^="ean_code_"]').id = `ean_code_${rowIndex}`;
            row.querySelector('[id^="product_message_"]').id = `product_message_${rowIndex}`;
            row.querySelector('[id^="status_"]').id = `status_${rowIndex}`;
            row.querySelector('[id^="product_id_"]').id = `product_id_${rowIndex}`;
            row.querySelector('[id^="product_id_"]').name = `product_id${rowIndex}`;
        });
    }

    // Handle product search and suggestions for dynamically added fields
    $(document).on("input", ".bulk-product-field", function () {
        const query = $(this).val();
        const rowIndex = $(this).attr("data-row-index");
        const suggestionsList = $(`#suggestions-list-${rowIndex}`);
        const suggestionsContainer = $(`#suggestions-container-${rowIndex}`);

        // Clear any existing timeout for this row
        if (window.suggestionTimeouts && window.suggestionTimeouts[rowIndex]) {
            clearTimeout(window.suggestionTimeouts[rowIndex]);
            delete window.suggestionTimeouts[rowIndex];
        }

        // Clear previous suggestions and hide suggestions container initially
        suggestionsList.empty();
        suggestionsContainer.hide();

        // If input is empty, hide suggestions and return
        if (query.length === 0) {
            $(`#product_id_${rowIndex}`).val('');
            $('#product_message_' + rowIndex).removeClass('bg-danger bg-success');
            $('#product_message_' + rowIndex).html('');
            $('#status_' + rowIndex).val('');
            verifyproductlst(rowIndex); // Call verification immediately
            return;
        }

        // Call verification immediately on input
        verifyproductlst(rowIndex);

        // If the input length is less than 3 characters, show a message
        if (query.length < 3) {
            suggestionsContainer.show();
            suggestionsList.append('<li class="result-message">Please enter more than 3 characters.</li>');
            return;
        }

        // Make AJAX request
        $.ajax({
            url: "{{ route('vendor.fasttrack.products.autocomplete') }}",
            type: "GET",
            data: { term: query },
            dataType: "json",
            success: function (response) {
                // Clear previous suggestions
                suggestionsList.empty();

                // Show suggestions container if there are results
                if (response.length > 0) {
                    suggestionsContainer.show();
                    suggestionsList.append('<li class="result-message">Showing result for <b>"' + ucwords(query) + '"</b> ' + response.length + ' records found</li>');

                    // Loop through the response and append each product to the list
                    response.forEach(function (item) {
                        suggestionsList.append('<li data-id="' + item.id + '" class="suggestion-item">' + item.label + '</li>');
                    });
                } else {
                    suggestionsContainer.show();
                    suggestionsList.append('<li class="result-message">No results found for <b>"' + ucwords(query) + '"</b></li>');
                    
                    // Store timeout ID for this row
                    if (!window.suggestionTimeouts) window.suggestionTimeouts = {};
                    window.suggestionTimeouts[rowIndex] = setTimeout(function() {
                        suggestionsContainer.hide();
                    }, 1000);
                    
                    // If no results found and user typed more than 3 characters, set as new product
                    if (query.length >= 3) {
                        $('#status_' + rowIndex).val('2');
                        $(`#product_id_${rowIndex}`).val('');
                        verifyproductlst(rowIndex); // Call verification after setting status
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    });


    // Handle selecting a suggestion
    $(document).on("click", ".suggestion-item", function () {
        const selectedProduct = $(this).text();
        const productId = $(this).data("id");
        const rowIndex = $(this).closest("tr").find(".bulk-product-field").attr("data-row-index");
        const suggestionsList = $(`#suggestions-list-${rowIndex}`);
        const suggestionsContainer = $(`#suggestions-container-${rowIndex}`);

        // Check if the product is already selected in another row
        const allProductInputs = $(".bulk-product-field").not(`[data-row-index="${rowIndex}"]`);
        let isDuplicate = false;
        allProductInputs.each(function () {
            if ($(this).val().toLowerCase() === selectedProduct.toLowerCase()) {
                isDuplicate = true;
                return false; // Exit the loop
            }
        });

        if (isDuplicate) {
            // Clear the input field
            $(`.bulk-product-field[data-row-index="${rowIndex}"]`).val('');
            $(`#product_id_${rowIndex}`).val('');
            // Show error message in the suggestions list
            suggestionsList.empty();
            suggestionsContainer.show();
            alert('Sorry, this product already exists in your list.')
            return;
        }

        // If no duplicate, set the input field value and store the product ID
        $(`.bulk-product-field[data-row-index="${rowIndex}"]`).val(selectedProduct);
        $(`#product_id_${rowIndex}`).val(productId);  // Store the product ID

        // Hide the suggestions container and clear the list
        suggestionsContainer.hide();
        suggestionsList.empty();
        
        // Verify the product
        verifyproductlst(rowIndex);
    });
  
    // Modify the verifyproductlst function to handle new products
    // Modify the verifyproductlst function to handle validation messages
function verifyproductlst(tab_index) {
    if (tab_index) {
        var product_name = $('#product_name_' + tab_index).val();
        var product_id = $('#product_id_' + tab_index).val();
        var ps_desc = $('#ps_desc_' + tab_index).val().replace(/\s+/g, '');
        var dealer_type = $('#dealer_type_' + tab_index).val();
        var tax_class = $('#tax_class_' + tab_index).val();
        var ean_code = $('#ean_code_' + tab_index).val();
        
        // For existing products (selected from dropdown)
        if (product_id && product_name != '' && ps_desc != '' && dealer_type != '' && tax_class != '' && ean_code != '' && $.isNumeric(ean_code) && parseInt(ean_code.length) > 1 && parseInt(ean_code.length) < 9 && parseInt(ean_code) > 9 && parseInt(ean_code) <= 99999999) {
            $('#product_message_' + tab_index).removeClass('bg-danger');
            $('#product_message_' + tab_index).addClass('bg-success');
            $('#product_message_' + tab_index).html('Product Verified');
            $('#status_' + tab_index).val('1');
        } 
        // For new products (not selected from dropdown)
        else if (!product_id && product_name != '' && ps_desc != '' && dealer_type != '' && tax_class != '' && ean_code != '' && $.isNumeric(ean_code) && parseInt(ean_code.length) > 1 && parseInt(ean_code.length) < 9 && parseInt(ean_code) > 9 && parseInt(ean_code) <= 99999999) {
            $('#product_message_' + tab_index).removeClass('bg-danger');
            $('#product_message_' + tab_index).addClass('bg-success');
            $('#product_message_' + tab_index).html('New Product');
            $('#status_' + tab_index).val('2');
        }
        else {
            var error_msg = '<div class="text-start">';
            var error_count = 0;
            
            if (product_name == '') {
                error_msg += '<div class="d-flex align-items-center mb-1">';
                error_msg += '<span class="bi bi-x-circle-fill text-danger me-2"></span>';
                error_msg += '<span>Enter Product Name</span>';
                error_msg += '</div>';
                error_count++;
            }
            if (ps_desc == "") {
                error_msg += '<div class="d-flex align-items-center mb-1">';
                error_msg += '<span class="bi bi-x-circle-fill text-danger me-2"></span>';
                error_msg += '<span>Enter Description</span>';
                error_msg += '</div>';
                error_count++;
            }
            if (dealer_type == "") {
                error_msg += '<div class="d-flex align-items-center mb-1">';
                error_msg += '<span class="bi bi-x-circle-fill text-danger me-2"></span>';
                error_msg += '<span>Select Dealer Type</span>';
                error_msg += '</div>';
                error_count++;
            }
            if (tax_class == "") {
                error_msg += '<div class="d-flex align-items-center mb-1">';
                error_msg += '<span class="bi bi-x-circle-fill text-danger me-2"></span>';
                error_msg += '<span>Select GST/Sales Tax Rate</span>';
                error_msg += '</div>';
                error_count++;
            }
            if (ean_code == '' || !$.isNumeric(ean_code)) {
                error_msg += '<div class="d-flex align-items-center mb-1">';
                error_msg += '<span class="bi bi-x-circle-fill text-danger me-2"></span>';
                error_msg += '<span>Enter Valid HSN Code</span>';
                error_msg += '</div>';
                error_count++;
            } else if (parseInt(ean_code.length) <= 1 || parseInt(ean_code.length) >= 9 || parseInt(ean_code) <= 9 || parseInt(ean_code) > 99999999) {
                error_msg += '<div class="d-flex align-items-center mb-1">';
                error_msg += '<span class="bi bi-x-circle-fill text-danger me-2"></span>';
                error_msg += '<span>HSN Code must be 2-8 digits</span>';
                error_msg += '</div>';
                error_count++;
            }

            error_msg += '</div>';

            $('#status_' + tab_index).val('3');
            $('#product_message_' + tab_index).addClass('bg-danger');
            $('#product_message_' + tab_index).removeClass('bg-success');
            
            if (error_count > 0) {
                $('#product_message_' + tab_index).html(error_msg);
            } else {
                $('#product_message_' + tab_index).html('');
            }
        }
    }
}       
       // Modify the verifyproductlst function to handle validation messages
        function verifyproductlst(tab_index) {
            if (tab_index) {
                var product_name = $('#product_name_' + tab_index).val();
                var product_id = $('#product_id_' + tab_index).val();
                var ps_desc = $('#ps_desc_' + tab_index).val().replace(/\s+/g, '');
                var dealer_type = $('#dealer_type_' + tab_index).val();
                var tax_class = $('#tax_class_' + tab_index).val();
                var ean_code = $('#ean_code_' + tab_index).val();
                
                // For existing products (selected from dropdown)
                if (product_id && product_name != '' && ps_desc != '' && dealer_type != '' && tax_class != '' && ean_code != '' && $.isNumeric(ean_code) && parseInt(ean_code.length) > 1 && parseInt(ean_code.length) < 9 && parseInt(ean_code) > 9 && parseInt(ean_code) <= 99999999) {
                    $('#product_message_' + tab_index).removeClass('bg-danger');
                    $('#product_message_' + tab_index).addClass('bg-success');
                    $('#product_message_' + tab_index).html('Product Verified');
                    $('#status_' + tab_index).val('1');
                } 
                // For new products (not selected from dropdown)
                else if (!product_id && product_name != '' && ps_desc != '' && dealer_type != '' && tax_class != '' && ean_code != '' && $.isNumeric(ean_code) && parseInt(ean_code.length) > 1 && parseInt(ean_code.length) < 9 && parseInt(ean_code) > 9 && parseInt(ean_code) <= 99999999) {
                    $('#product_message_' + tab_index).removeClass('bg-danger');
                    $('#product_message_' + tab_index).addClass('bg-success');
                    $('#product_message_' + tab_index).html('New Product');
                    $('#status_' + tab_index).val('2');
                }
                else {
                    var error_msg = '<div class="text-start">';
                    var error_count = 0;
                    
                    if (product_name == '') {
                        error_msg += '<div class="d-flex align-items-center mb-1">';
                        error_msg += '<span class="bi bi-x-circle-fill text-danger me-2"></span>';
                        error_msg += '<span>Enter Product Name</span>';
                        error_msg += '</div>';
                        error_count++;
                    }
                    if (ps_desc == "") {
                        error_msg += '<div class="d-flex align-items-center mb-1">';
                        error_msg += '<span class="bi bi-x-circle-fill text-danger me-2"></span>';
                        error_msg += '<span>Enter Description</span>';
                        error_msg += '</div>';
                        error_count++;
                    }
                    if (dealer_type == "") {
                        error_msg += '<div class="d-flex align-items-center mb-1">';
                        error_msg += '<span class="bi bi-x-circle-fill text-danger me-2"></span>';
                        error_msg += '<span>Select Dealer Type</span>';
                        error_msg += '</div>';
                        error_count++;
                    }
                    if (tax_class == "") {
                        error_msg += '<div class="d-flex align-items-center mb-1">';
                        error_msg += '<span class="bi bi-x-circle-fill text-danger me-2"></span>';
                        error_msg += '<span>Select GST/Sales Tax Rate</span>';
                        error_msg += '</div>';
                        error_count++;
                    }
                    if (ean_code == '' || !$.isNumeric(ean_code)) {
                        error_msg += '<div class="d-flex align-items-center mb-1">';
                        error_msg += '<span class="bi bi-x-circle-fill text-danger me-2"></span>';
                        error_msg += '<span>Enter Valid HSN Code</span>';
                        error_msg += '</div>';
                        error_count++;
                    } else if (parseInt(ean_code.length) <= 1 || parseInt(ean_code.length) >= 9 || parseInt(ean_code) <= 9 || parseInt(ean_code) > 99999999) {
                        error_msg += '<div class="d-flex align-items-center mb-1">';
                        error_msg += '<span class="bi bi-x-circle-fill text-danger me-2"></span>';
                        error_msg += '<span>HSN Code must be 2-8 digits</span>';
                        error_msg += '</div>';
                        error_count++;
                    }

                    error_msg += '</div>';

                    $('#status_' + tab_index).val('3');
                    $('#product_message_' + tab_index).addClass('bg-danger');
                    $('#product_message_' + tab_index).removeClass('bg-success');
                    
                    if (error_count > 0) {
                        $('#product_message_' + tab_index).html(error_msg);
                    } else {
                        $('#product_message_' + tab_index).html('');
                    }
                }
            }
        }

    // Add change event listeners for form fields to trigger verification
    $(document).on('change', '[id^="dealer_type_"], [id^="tax_class_"], [id^="ean_code_"]', function() {
        const rowIndex = $(this).closest('tr').data('row-index');
        verifyproductlst(rowIndex);
    });

    $(document).on('blur', '[id^="ps_desc_"]', function() {
        const rowIndex = $(this).closest('tr').data('row-index');
        verifyproductlst(rowIndex);
    });

    // Function to capitalize the first letter of each word
    function ucwords(str) {
        return str.replace(/\b(\w)/g, function (s) {
            return s.toUpperCase();
        });
    }

    // Handle file upload display
    $(document).on("click", ".file-display-box", function () {
        const fileInput = $(this).siblings(".real-file-input");
        fileInput.trigger("click");
    });

    $(document).on("change", ".real-file-input", function () {
        const fileName = this.files[0]?.name || "Upload file";
        $(this).siblings(".file-display-box").text(fileName);
    });



    // Handle form submission for bulk products
    $(document).on('click', '#submitProducts', function() {
        let hasValidProducts = false;
        const tableBody = document.querySelector("#productTable tbody");
        const rows = tableBody.querySelectorAll("tr");
        const formData = new FormData();
        
        // Verify all rows and collect data
        rows.forEach((row) => {
            const rowIndex = row.getAttribute('data-row-index');
            verifyproductlst(rowIndex);
            
            const status = $('#status_' + rowIndex).val();
            const message = $('#product_message_' + rowIndex).html();
            
            // Check if product is valid (status 1 or 2 with appropriate message)
            if((status == '1' || status == '2') && 
               (message == 'New Product' || message == 'New product' || 
                message == 'Product Verified' || message == 'Product verified')) {
                hasValidProducts = true;
                
                // Add product data to formData
                formData.append('products['+rowIndex+'][product_id]', $('#product_id_' + rowIndex).val());
                formData.append('products['+rowIndex+'][product_name]', $('#product_name_' + rowIndex).val());
                formData.append('products['+rowIndex+'][ps_desc]', $('#ps_desc_' + rowIndex).val());
                formData.append('products['+rowIndex+'][dealer_type]', $('#dealer_type_' + rowIndex).val());
                formData.append('products['+rowIndex+'][tax_class]', $('#tax_class_' + rowIndex).val());
                formData.append('products['+rowIndex+'][ean_code]', $('#ean_code_' + rowIndex).val());
                formData.append('products['+rowIndex+'][status]', status);
                
                // Handle file upload if exists
                const fileInput = $('#uploadFile-' + rowIndex)[0];
                if (fileInput.files.length > 0) {
                    formData.append('products['+rowIndex+'][product_image]', fileInput.files[0]);
                }
            }
        });
        
        if (!hasValidProducts) {
            alert('At least one product should be verified or marked as new product');
            return;
        }
        
        // Add CSRF token
        formData.append('_token', '{{ csrf_token() }}');
        
        // Disable submit button during processing
        $("#submitProducts").addClass("disabled");
        
        // Show loading indicator
        $("#submitProducts").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
        
        // Submit via AJAX
        $.ajax({
            url: "{{ route('vendor.fasttrack.products.store') }}", // Update with your actual route
            type: "POST",
            dataType: 'json',
            data: formData,
            contentType: false,
            cache: false,
            processData: false,
            success: function(response) {
                if (response.status == 1) {
                    toastr.success(response.message);
                    setTimeout(function () {
                        window.location.href = "{{ route('vendor.products.index') }}";
                    }, 300);
                } else {
                    // Show error message
                    alert(response.message || 'Error submitting products');
                }
            },
            error: function(xhr, status, error) {
                alert('Something went wrong. Please try again.');
                console.error(error);
            },
            complete: function() {
                // Re-enable button
                $("#submitProducts").removeClass("disabled");
                $("#submitProducts").html('<span class="bi bi-save font-size-12"></span><span class="font-size-11">Submit</span>');
            }
        });
    });
</script>
@endsection
@endsection