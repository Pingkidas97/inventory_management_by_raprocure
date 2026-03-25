@extends('admin.layouts.app_second', [
'title' => 'Add Product To Vendor Profile',
'sub_title' => 'Add Product'
])
@section('css')
<link href="https://cdn.jsdelivr.net/bootstrap.tagsinput/0.8.0/bootstrap-tagsinput.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/bootstrap.tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>
<style>
.bootstrap-tagsinput {
    width: 100%;
    min-height: 38px;
    padding: 0.375rem 0.75rem;
    line-height: 1.5;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    background-color: #fff;
}

.bootstrap-tagsinput .tag {
    margin-right: 2px;
    color: white;
    background-color: #0d6efd;
    padding: 0.25em 0.5em;
    border-radius: 0.25rem;
}

.capital {
    text-transform: uppercase;
}

.ck-editor__editable {
    min-height: 200px;
}

.text-warning {
    color: #ffc107 !important;
}

.error-class {
    color: #ff0000 !important;
}

ul {
    list-style: outside none none;
    margin: 0;
    padding: 0;
}

.note-taxt {
    background: #eae2e2;
    border-radius: 5px;
    font-size: 15px;
    padding: 15px;
}

.note-taxt p {
    margin-bottom: 1rem;
}
</style>
@endsection
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-box-seam"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.new-products.index') }}"> -> Vendor Module </a>
            <span> -> Add Product </span>
        </h5>
    </div>
</div>
@endsection

@section('content')

<div class="page-start-section from-start-section">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between"
                        style="background-color: transparent;padding: 15px;border: none !important;">
                        <ul class="nav nav-tabs card-header-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#general_information_menu"
                                    style="color: #015294;background-color: #fff;border-color: #fff;border-bottom: 1px solid #015294 !important;">
                                    <i class="bi bi-info-circle me-2"></i>
                                    General Information
                                </a>
                            </li>
                        </ul>
                        <div class="ms-auto">
                            <a class="btn-rfq btn-rfq-primary" onclick="raise_query()" href="javascript:void(0)">
                                Raise Query
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="addVendorProductForm" enctype="multipart/form-data"
                            style="padding-right: 20px;padding-left: 20px;">
                            @csrf
                            @method('POST')
                            <input type="hidden" name="id" value="{{ $product->id }}">

                            <div class="pt-4">
                                <div class="basic-form">

                                    {{-- Product Name --}}
                                    <div class="mb-4 row align-items-center">
                                        <label for="product_name" class="col-md-3 col-form-label">Product Name <span
                                                class="text-danger">*</span></label>
                                        <div class="col-md-4">
                                            <div class="position-relative">
                                                <input type="text" class="form-control" name="product_name"
                                                    id="product_name" value="{{ $product->product_name }}"
                                                    autocomplete="off">
                                                <input type="hidden" name="product_id" id="product_id">
                                                <input type="hidden" name="vendor_id" id="vendor_id" value="{{ $id }}">
                                                <div id="product_suggestions" class="dropdown-menu w-100 shadow"
                                                    style="display: none; max-height: 300px; overflow-y: auto; position: absolute; z-index: 1000;">
                                                </div>
                                            </div>
                                            <span class="error-class text-danger" id="product-name-error"></span>
                                        </div>
                                        <div class="col-md-1"></div>
                                        <div class="col-md-4 note-taxt">
                                            <b>
                                                <p>Note: 1. Start typing the name of your product, options will start
                                                    appearing in Dropdown, and then select anyone.</p>
                                                <p>2. Just first few fields - marked <span class="text-danger"
                                                        style="font-size: 21px;">*</span> - are mandatory.</p>
                                            </b>
                                        </div>
                                    </div>

                                    {{-- Upload Picture --}}
                                    <div class="mb-4 row align-items-center">
                                        <label for="product_image" class="col-sm-3 col-form-label">Upload
                                            Picture</label>
                                        <div class="col-sm-4">
                                            <input type="file" name="product_image" id="product_image"
                                                class="form-control" accept=".jpeg,.jpg,.png,.gif"
                                                onchange="validateProductFile(this, 'JPEG/JPG/PNG/GIF')">
                                            <span class="help-block text-danger" id="error-msg-product-image"></span>
                                            @if($product->image)
                                            <div class="mt-2">
                                                <img src="{{ asset('public/uploads/product/thumbnails/100/'.$product->image) }}"
                                                    alt="Product Image" style="max-width: 200px; max-height: 200px;">
                                                <a href="{{ asset('public/uploads/product/thumbnails/100/'.$product->image) }}"
                                                    target="_blank" class="ms-2"><b>View Full Image</b></a>
                                                <input type="hidden" name="existing_product_image"
                                                    value="{{ $product->image }}">
                                            </div>
                                            @endif
                                        </div>
                                        <div class="col-sm-4">
                                            <span class="text-danger">(JPEG/JPG/PNG/GIF)</span>
                                        </div>
                                    </div>

                                    {{-- Product Description --}}
                                    <div class="mb-4 row align-items-center">
                                        <label for="product_description" class="col-sm-3 col-form-label">Product
                                            Description <span class="text-danger">*</span></label>
                                        <div class="col-sm-9">
                                            <span class="pro-char-count text-muted prod-des-count">Characters:
                                                {{ strlen($product->description) }}/500</span>
                                            <textarea class="form-control" id="product_description"
                                                name="product_description"></textarea>
                                            <span class="error-class" id="product-description-error"></span>
                                        </div>
                                    </div>

                                    {{-- Dealer Type --}}
                                    <div class="mb-4 row align-items-center">
                                        <label for="product_dealer_type" class="col-sm-3 col-form-label">Dealer Type
                                            <span class="text-danger">*</span></label>
                                        <div class="col-sm-4">
                                            <select class="form-select" id="product_dealer_type"
                                                name="product_dealer_type">
                                                <option value="">Select Dealer Type</option>
                                                <option value="1">Manufacturer</option>
                                                <option value="2">Trader</option>
                                            </select>
                                            <span class="error-class text-danger" id="product-dealer-type-error"></span>
                                        </div>
                                    </div>

                                    {{-- GST/Sales Tax Rate --}}
                                    <div class="mb-4 row align-items-center">
                                        <label for="product_gst" class="col-sm-3 col-form-label">GST/Sales Tax Rate
                                            <span class="text-danger">*</span></label>
                                        <div class="col-sm-4">
                                            <select class="form-select" id="product_gst" name="product_gst">
                                                <option value="">Select GST Class</option>
                                                <option value="1">0%</option>
                                                <option value="2">5%</option>
                                                <option value="3">12%</option>
                                                <option value="4">18%</option>
                                                <option value="5">28%</option>
                                            </select>
                                            <span class="error-class text-danger" id="product-gst-error"></span>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="d-flex gap-1">
                                        <button type="submit" class="btn-rfq btn-rfq-primary">Add Product</button>
                                        <a href="{{ route('admin.new-products.index') }}"
                                            class="btn-rfq btn-rfq-danger">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>

<script>
$(document).ready(function() {
    const editorConfig = {
        toolbar: [
            "heading", "bold", "italic", "bulletedList", "numberedList",
            "blockQuote", "undo", "redo"
        ]
    };

    const MAX_CHARS = 500;

    function initEditor(selector, counterSelector, errorSelector = null, validateForm = false) {
        ClassicEditor
            .create(document.querySelector(selector), editorConfig)
            .then(editor => {
                const counter = document.querySelector(counterSelector);
                let isProcessing = false;

                const updateCount = () => {
                    if (isProcessing) return;
                    isProcessing = true;

                    const content = editor.getData();
                    const text = content.replace(/<[^>]*>/g, '');
                    const count = text.length;

                    counter.textContent = `Characters: ${count}/${MAX_CHARS}`;

                    counter.classList.remove('text-muted', 'text-warning', 'text-danger');
                    if (count > MAX_CHARS) {
                        counter.classList.add('text-danger');
                        const trimmed = text.substring(0, MAX_CHARS);
                        editor.setData(trimmed);
                    } else if (count > 450) {
                        counter.classList.add('text-warning');
                    } else {
                        counter.classList.add('text-muted');
                    }

                    isProcessing = false;
                };

                updateCount();
                editor.model.document.on('change:data', updateCount);

                if (validateForm && errorSelector) {
                    const form = document.querySelector('#general_information_form');
                    if (form) {
                        form.addEventListener('submit', function(e) {
                            const content = editor.getData();
                            const text = content.replace(/<[^>]*>/g, '');
                            if (text.length > MAX_CHARS) {
                                e.preventDefault();
                                const errorElem = document.querySelector(errorSelector);
                                if (errorElem) {
                                    errorElem.textContent =
                                        'Description must be 500 characters or less';
                                }
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error(`Editor initialization error for ${selector}:`, error);
            });
    }

    // Initialize the editor
    initEditor('#product_description', '.pro-char-count', '#p_desc_err', true);
});
</script>

<script>
$(document).ready(function() {
    $('#addVendorProductForm input').on('keyup change', function() {
        const fieldName = $(this).attr('name');
        $(`span.error-text.${fieldName}-error`).text('');
    });

    $('#addVendorProductForm').submit(function(e) {
        e.preventDefault();

        $('span.error-text').text('');

        const product_name = $('#product_name').val();
        const productId = $('#product_id').val().trim();
        const product_gst = $('#product_gst').val();
        const product_dealer_type = $('#product_dealer_type').val();


        let hasErrors = false;

        // Product name
        if (!product_name) {
            $('#product-name-error').text('Please enter the product name.');
            hasErrors = true;
        } else if (product_name.length < 2) {
            $('#product-name-error').text('Product name must be at least 2 characters.');
            hasErrors = true;
        }

        // GST
        if (!product_gst) {
            $('#product-gst-error').text('Please enter the GST percentage.');
            hasErrors = true;
        } else if (!/^\d{1,2}(\.\d{1,2})?$/.test(product_gst) || parseFloat(product_gst) > 100) {
            $('#product-gst-error').text('Enter a valid GST percentage (0-100).');
            hasErrors = true;
        }

        // Dealer Type
        if (!product_dealer_type) {
            $('#product-dealer-type-error').text('Please select a dealer type.');
            hasErrors = true;
        }


        if (hasErrors) {
            toastr.error('Please fill all the Mandatory fields marked with *');
            return;
        };

        $.ajax({
            url: "{{ route('admin.vendor.products.store') }}",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                if (response.status == 1) {
                    toastr.success(response.message);
                    setTimeout(function() {
                        window.location.reload();
                    }, 300);
                } else {
                    // Check if alias_error_msg exists in the response
                    if (response.alias_error_message && Array.isArray(response
                            .alias_error_message)) {
                        let alias_error_html = '';

                        // Iterate over the error messages and create HTML to display them
                        response.alias_error_message.forEach(function(alias_error) {
                            alias_error_html +=
                                '<li><span class="text-danger">*</span> ' +
                                alias_error + '</li>';
                        });

                        // Display the error messages with HTML content
                        $(".product-alias-error-msg").html('<ul>' + alias_error_html +
                            '</ul>');
                    }
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
            }
        });
    });

    // Optional: Load categories dynamically when division changes
    $('#division_id').on('change', function() {
        var divisionId = $(this).val();
        $('#category_id').html('<option value="">Loading...</option>');
        $.get('{{ route("admin.getCategoriesByDivision") }}', {
            division_id: divisionId
        }, function(data) {
            let options = '<option value="">Select</option>';
            $.each(data.categories, function(index, cat) {
                options += `<option value="${cat.id}">${cat.category_name}</option>`;
            });
            $('#category_id').html(options);
        });
    });
});

$(document).ready(function() {
    const $input = $('#product_name');
    const $hiddenInput = $('#product_id');
    const $suggestionBox = $('#product_suggestions');
    let debounceTimer;

    $input.on('input', function() {
        const query = $(this).val().trim();
        clearTimeout(debounceTimer);
        $hiddenInput.val(''); // reset hidden input

        // Show dropdowns again if user is typing
        $('#division_id').closest('.row').show();
        $('#category_id').closest('.row').show();

        if (query.length < 2) {
            $suggestionBox.hide();
            return;
        }

        debounceTimer = setTimeout(function() {
            $.get('{{ route("admin.product.autocomplete") }}', {
                term: query
            }, function(data) {
                $suggestionBox.empty();

                if (data.length === 0) {
                    $suggestionBox.append(
                        '<div class="dropdown-item disabled">No products found</div>'
                        );
                    alert(
                        'Cannot find the Product. Kindly add the product from product directory');
                    $input.val(''); // Clear input field
                    return; // exit after alert
                } else {
                    $suggestionBox.append(
                        `<div class="dropdown-item disabled text-muted">Showing result for "<strong>${query}</strong>" — ${data.length} found</div>`
                    );
                    $.each(data, function(i, item) {
                        const $option = $(
                            '<div class="dropdown-item" style="cursor: pointer;"></div>'
                            ).text(item.label);
                        $option.on('click', function() {
                            $input.val(item.label);
                            $hiddenInput.val(item.id);
                            $suggestionBox.hide();

                            // Hide Division and Category dropdowns
                            $('#division_id').closest('.row').hide();
                            $('#category_id').closest('.row').hide();

                        });
                        $suggestionBox.append($option);
                    });
                }

                $suggestionBox.show();
            });
        }, 300);
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#product_name, #product_suggestions').length) {
            $suggestionBox.hide();
        }
    });
});
</script>
@endsection