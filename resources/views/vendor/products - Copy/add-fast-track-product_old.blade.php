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

    .error-message {
        padding: 5px;
        font-size: 14px;
        background: #f8d7da;
        color: #721c24;
    }
</style>

<main class="main main-inner-page flex-grow-1 py-2 px-md-3 px-1">
    <section class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                <li class="breadcrumb-item">Manage Products</li>
                <li class="breadcrumb-item active" aria-current="page">Add Fast Product</li>
            </ol>
        </nav>

        <!-- Start Product Content Here -->
        <section class="manage-product card">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between bg-white py-3">
                    <h1 class="card-title font-size-18 mb-0">Add Fast Product</h1>
                    <a href="javascript:void(0)" class="ra-btn ra-btn-primary d-flex align-items-center font-size-12">
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
                                <th scope="col" class="text-center text-uppercase text-nowrap">Status</th>
                                <th scope="col" class="text-center text-uppercase text-nowrap">Action</th>
                            </thead>
                            <tbody>
                                <tr data-row-index="1">
                                    <td>1</td>
                                    <td>
                                        <!-- Product search input field -->
                                        <input type="text" name="pro-name" class="form-control bulk-product-field" value="" autocomplete="off" maxlength="255" placeholder="Search Product..." data-row-index="1" />

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
                                        <input type="text" name="pro-desc1" class="form-control" value="" maxlength="500" />
                                    </td>
                                    <td>
                                        <select class="form-select" name="select-dealer1">
                                            <option value="">Select Dealer Type</option>
                                            @foreach ($dealertypes as $dealerType)
                                            <option value="{{ $dealerType->id }}">{{ $dealerType->dealer_type }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select" name="select-tax1">
                                            <option value="">Select</option>
                                            @foreach ($taxes as $tax)
                                            <option value="{{ $tax->id }}">
                                                {{ $tax->tax_name }} - {{ $tax->tax }}%
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="hsn-code1" class="form-control" maxlength="8" value="" />
                                    </td>
                                    <td>
                                        <!-- Optionally, you can add a status select here -->
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
                        <button type="button" class="ra-btn ra-btn-primary font-size-12">
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
                <input type="text" name="pro-name" class="form-control bulk-product-field" value="" autocomplete="off" maxlength="255" placeholder="Search Product..." data-row-index="${rowCount}">
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
                <input type="text" name="pro-desc${rowCount}" class="form-control" value="" maxlength="500">
            </td>
            <td>
                <select class="form-select" name="select-dealer${rowCount}">
                    <option value="">Select Dealer Type</option>
                    @foreach ($dealertypes as $dealerType)
                        <option value="{{ $dealerType->id }}">{{ $dealerType->dealer_type }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select class="form-select" name="select-tax${rowCount}">
                    <option value="">Select</option>
                    @foreach ($taxes as $tax)
                        <option value="{{ $tax->id }}">
                            {{ $tax->tax_name }} - {{ $tax->tax }}%
                        </option>
                    @endforeach
                </select>
            </td>
           

 <td>
                <input type="text" name="hsn-code${rowCount}" class="form-control" maxlength="8" value="">
            </td>
            <td></td>
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
            const suggestionContainer = row.querySelector(".suggestions-container");
            suggestionContainer.id = `suggestions-container-${rowIndex}`;
            const suggestionList = row.querySelector(".suggestions-list");
            suggestionList.id = `suggestions-list-${rowIndex}`;
        });
    }

    // Handle product search and suggestions for dynamically added fields
    $(document).on("input", ".bulk-product-field", function () {
        const query = $(this).val();
        const rowIndex = $(this).attr("data-row-index");

        // Clear previous suggestions and hide suggestions container initially
        const suggestionsList = $(`#suggestions-list-${rowIndex}`);
        const suggestionsContainer = $(`#suggestions-container-${rowIndex}`);
        suggestionsList.empty();
        suggestionsContainer.hide();

        // If input is empty, hide suggestions and return
        if (query.length === 0) {
            return;
        }

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
                    // Add result message as the first list item
                    suggestionsList.append('<li class="result-message">Showing result for <b>"' + ucwords(query) + '"</b> ' + response.length + ' records found</li>');

                    // Loop through the response and append each product to the list
                    response.forEach(function (item) {
                        suggestionsList.append('<li data-id="' + item.id + '" class="suggestion-item">' + item.label + '</li>');
                    });
                } else {
                    suggestionsContainer.show();
                    suggestionsList.append('<li class="result-message">No results found for <b>"' + ucwords(query) + '"</b></li>');
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
            // Show error message in the suggestions list
            suggestionsList.empty();
            suggestionsContainer.show();
            alert('Sorry, this product already exists in your list.')
            return;
        }

        // If no duplicate, set the input field value
        $(`.bulk-product-field[data-row-index="${rowIndex}"]`).val(selectedProduct);

        // Hide the suggestions container and clear the list
        suggestionsContainer.hide();
        suggestionsList.empty();
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
</script>
@endsection
@endsection