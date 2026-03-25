@extends('admin.layouts.app_second', [
'title' => 'Add Product To Vendor Profile',
'sub_title' => 'Add Product'
])
@section('css')
<style type="text/css">
.table>tbody>tr:nth-child(odd) {
    background-color: #fff4ef !important;
}
</style>
@endsection
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.new-products.index') }}"> -> Vendor Module </a>
            <span> -> Add Multiple Product </span>
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
                            <a class="btn-rfq btn-rfq-primary" href="{{ route('admin.vendor.index') }}">
                                <i class="bi bi-arrow-left-square"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-4 row align-items-center">
                            <label class="col-sm-2 col-form-label">Category <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <div class="form-group">
                                    <select id="category-name" class="form-select" name="product_category">
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $category)
                                        <option value="{{ $category->cat_ids }}">{{ $category->category_name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="error-class text-danger" id="category-error"></span>
                                </div>
                            </div>
                        </div>

                        <form id="addVendorProductForm" enctype="multipart/form-data"
                            class="vendor-product-form d-none">
                            @csrf
                            @method('POST')

                            <input type="hidden" name="vendor_id" value="{{ $id }}">

                            <div class="pt-4">
                                <div class="basic-form">
                                    <div class="row align-items-center"
                                        style="border-bottom: 1px solid #d6d6d6;padding-bottom: 10px;">
                                        <label class="col-sm-2 col-form-label">Product Name</label>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <input
                                                    oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')"
                                                    type="text" class="form-control" id="prod_name" value=""
                                                    autocomplete="off" placeholder="Search by product name">
                                                <input type="hidden" id="product_id" name="product_id">
                                            </div>
                                        </div>
                                        <div class="col-sm-1"></div>
                                        <div class="col-sm-2">
                                            <div class="form-group">
                                                <select class="form-control dealer_type select-custom-210"
                                                    name="dealer_type">
                                                    @php
                                                    $dealerTypes = get_active_dealer_types();
                                                    @endphp
                                                    <option value="">Select Dealer Type</option>
                                                    @foreach($dealerTypes as $type)
                                                    <option value="{{ $type->id }}">
                                                        {{ $type->dealer_type }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        @if (is_international_vendor_check($product->vendor->id) === 'Yes')
                                        <div class="col-sm-2">
                                            <div class="form-group">
                                                <select class="form-control tax_class" name="tax_class">
                                                    <option value="">Select GST/Sales Tax Rate</option>
                                                    @foreach (get_active_tax() as $tax)
                                                    <option value="{{ $tax->id }}">{{ $tax->tax_name }}
                                                        ({{ $tax->tax }}%)</option>
                                                    @endforeach

                                                </select>
                                            </div>
                                        </div>
                                        @else
                                        <input type="hidden" id="tax_class" name="tax_class" value="1">
                                        @endif
                                        <div class="col-sm-1">
                                            <div class="text-center">
                                                <button type="submit" class="save-form-btn btn-rfq btn-rfq-primary"
                                                    id="upload_bulk_product"><i class="bi bi-save"></i> Submit</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive py-table-new">
                                        <table class="table table-striped table-responsive1">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="text-center text-nowrap">
                                                        <input type="checkbox" class="prod-checkbox-global">
                                                    </th>
                                                    <th scope="col"><span class="product-name-heading">Product
                                                            Name</span></th>
                                                </tr>
                                            </thead>
                                            <tbody class="product-section">
                                                <!-- Products will be populated via AJAX -->
                                            </tbody>
                                        </table>
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
    let search_request;
    let debounceTimer;

    // Category change handler
    $('#category-name').on('change', function() {
        $('#prod_name').val('');
        $('#product_id').val('');
        getProducts();
    });

    // Product name input handler with debounce
    $('#prod_name').on('input', function() {
        const query = $(this).val().trim();
        $('#product_id').val('');
        clearTimeout(debounceTimer);

        if (query.length < 2 && query.length !== 0) {
            return;
        }

        debounceTimer = setTimeout(function() {
            getProducts();
        }, 300);
    });

    // Fetch products via AJAX
    function getProducts() {
        let category_ids = $('#category-name').val();
        let prod_name = $('#prod_name').val();
        let vendor_id = $('input[name="vendor_id"]').val();

        if (!category_ids) {

            $('.vendor-product-form').addClass('d-none');
            $('.product-section').html('');
            $('#category-error').text('Please select a category.');
            return;
        }

        $('#category-error').text('');

        if (search_request) {
            search_request.abort();
        }

        search_request = $.ajax({
            type: 'POST',
            url: "{{ route('admin.vendor.products.get_products_by_category') }}",
            data: {
                category_ids: category_ids,
                prod_name: prod_name,
                vendor_id: vendor_id,
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(result) {
                if (result.status == 1) {
                    let html = '',
                        products = result.all_products;
                    for (let i = 0; i < products.length; i++) {
                        html += '<tr class="product-row">';
                        html += '<td class="text-center">';
                        html +=
                            '<input type="checkbox" class="prod-checkbox" name="master_product_id[]" value="' +
                            products[i].id + '">';
                        html += '</td>';
                        html += '<td>';
                        html += '<div><span class="cat-prod-name">' + products[i].product_name +
                            '</span></div>';
                        html += '</td>';
                        html += '</tr>';
                    }
                    $('.product-section').html(html);
                    $('.vendor-product-form').removeClass('d-none');
                } else {
                    $('.product-section').html(
                        '<tr class="product-row"><td class="text-center" colspan="2">' + result
                        .message + '</td></tr>');
                    $('.vendor-product-form').removeClass('d-none');
                }
            },
            error: function() {
                $('.product-section').html(
                    '<tr class="product-row"><td class="text-center" colspan="2">Error fetching products</td></tr>'
                    );
                toastr.error('An error occurred while fetching products.');
            }
        });
    }

    // Toggle checkbox on product name click
    $(document).on('click', '.cat-prod-name', function() {
        let prod_row = $(this).parents('.product-row');
        prod_row.find('.prod-checkbox').prop('checked', !prod_row.find('.prod-checkbox').prop(
            'checked'));
    });

    // Toggle all checkboxes on header click
    $(document).on('click', '.product-name-heading', function() {
        let isChecked = $('.prod-checkbox-global').prop('checked');
        $('.prod-checkbox-global').prop('checked', !isChecked);
        $('.prod-checkbox').prop('checked', !isChecked);
    });

    // Toggle all checkboxes on global checkbox change
    $(document).on('change', '.prod-checkbox-global', function() {
        $('.prod-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Form submission
    $('#addVendorProductForm').submit(function(e) {
        e.preventDefault();

        let dealer_type = $('.dealer_type').val();
        let tax_class = $('.tax_class').val() || $('input[name="tax_class"]').val();
        let checkedProducts = $('.prod-checkbox:checked').length;
        let category_ids = $('#category-name').val();

        let hasErrors = false;

        if (!category_ids) {
            $('#category-error').text('Please select a category.');
            hasErrors = true;
        }

        if (checkedProducts === 0) {
            toastr.error('Please select at least one product');
            hasErrors = true;
        }

        if (!dealer_type) {
            toastr.error('Dealer Type is required');
            hasErrors = true;
        }

        if (!tax_class) {
            toastr.error('GST is required');
            hasErrors = true;
        }

        if (hasErrors) {
            return;
        }

        let form_data = new FormData(this);

        $.ajax({
            url: "{{ route('admin.vendor.products.bulkstore') }}",
            type: 'POST',
            data: form_data,
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
                $('#upload_bulk_product').addClass('disabled');
            },
            success: function(response) {
                if (response.status == 1) {
                    toastr.success(response.message);
                    setTimeout(function() {
                        window.location.reload();
                    }, 300);
                } else {

                    toastr.error(response.message);
                    $('#upload_bulk_product').removeClass('disabled');
                }
            },
            error: function(xhr) {
                $('#upload_bulk_product').removeClass('disabled');
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
});
</script>
@endsection