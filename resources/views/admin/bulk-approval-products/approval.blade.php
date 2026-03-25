@extends('admin.layouts.app_second', [
'title' => 'Bulk Products for Approval',
'sub_title' => 'Products For Approval Bulk',
])
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-box"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.bulk-products.index') }}"> -> Bulk Products for Approval </a>
            <span> -> Products For Approval Bulk </span>
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
                        <div class="basic-form">
                            <form method="post" enctype="multipart/form-data">
                                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                                    <h4 class="card-title mb-0"> Products For Approval Bulk</h4>
                                    <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                                        <button type="button" data-btn-type="2"
                                            class="save-form-btn btn-rfq btn-rfq-primary" id="upload_bulk_product">
                                            <i class="bi bi-save"></i> Submit
                                        </button>

                                        <button type="button" class="btn-rfq btn-rfq-danger"
                                            id="delete_selected_products">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>

                                        <a href="{{ route('admin.bulk-products.index') }}"
                                            class="btn-rfq btn-rfq-primary go-back-to-page">
                                            <i class="bi bi-arrow-left-square"></i> Back
                                        </a>
                                    </div>
                                </div>
                                <table class="table table-striped table-responsive">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="select_all_checkbox" /></th>
                                            <th>SR. NO</th>
                                            <th>PRODUCT NAME <span class="text-danger">*</span></th>
                                            <th>UPLOAD PICTURE</th>
                                            <th>PRODUCT DESCRIPTION <span class="text-danger">*</span></th>
                                            <th>DEALER TYPE <span class="text-danger">*</span></th>
                                            @if(is_national($products[0]->vendor_id) == 1)
                                            <th>GST/SALES TAX RATE <span class="text-danger">*</span></th>
                                            @endif
                                            <th>HSN CODE <span class="text-danger">*</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <!-- Example product row -->
                                        @foreach($products as $index => $product)
                                        <tr class="product-row">
                                            <td>
                                                <input type="checkbox" name="selected[]" value="{{ $product->id }}" />
                                            </td>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <input type="text" class="form-control" name="product_name[]"
                                                    value="{{ $product->product->product_name }}" required readonly>
                                            </td>
                                            <td>
                                                <input type="file" class="form-control" name="product_image[]" />
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="product_description[]"
                                                    value="{{ strip_tags($product->description) }}" required />
                                                <div class="text-danger error-message" data-field="product_description"
                                                    data-id="{{ $product->id }}"></div>

                                            </td>
                                            <td>
                                                <select name="dealer_type[]" class="form-control" required>


                                                    @foreach($dealertypes as $type)
                                                    <option value="{{ $type->id }}"
                                                        {{ $product->dealer_type_id == $type->id ? 'selected' : '' }}>
                                                        {{ $type->dealer_type }}
                                                    </option>
                                                    @endforeach

                                                </select>
                                            </td>

                                            @if(is_national($product->vendor_id ))
                                            <td>
                                                <select class="form-control tax_class import_drop_down_sel"
                                                    name="tax_class[{{ $product->id }}]"
                                                    id="tax_class_{{ $product->id }}" tabindex="{{ $index + 1 }}"
                                                    data-astric="true">
                                                    <option value="">Select</option>
                                                    @foreach($taxes as $tax)
                                                    <option value="{{ $tax->id }}"
                                                        {{ $product->gst_id == $tax->id ? 'selected' : '' }}>
                                                        {{ $tax->tax_name }} - {{ $tax->tax }}%
                                                    </option>
                                                    @endforeach
                                                </select>
                                                <div class="text-danger error-message" data-field="tax_class"
                                                    data-id="{{ $product->id }}"></div>
                                            </td>
                                            @else
                                            <input type="hidden" name="tax_class[{{ $product->id }}]"
                                                id="tax_class_{{ $product->id }}" value="1">
                                            @endif

                                            <td>
                                                <input class="form-control" type="text" name="hsn[]"
                                                    value="{{ $product->hsn_code }}" required />
                                                <div class="text-danger error-message" data-field="hsn"
                                                    data-id="{{ $product->id }}"></div>
                                            </td>
                                        </tr>
                                        @endforeach
                                        <!-- Add more rows here -->
                                    </tbody>
                                </table>
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
$(document).ready(function() {
    // Select All checkbox functionality
    $('#select_all_checkbox').change(function() {
        $('input[name="selected[]"]').prop('checked', $(this).prop('checked'));
    });

    // Delete selected products
    $('#delete_selected_products').click(function() {
        let selectedIds = [];
        $('input[name="selected[]"]:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            alert('Please select at least one product to delete.');
            return;
        }

        if (confirm('Are you sure you want to delete the selected products?')) {
            $.ajax({
                url: '{{ route("admin.bulk-products.delete-multiple")}}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ids: selectedIds
                },
                success: function(response) {
                    if (response.success) {
                        // Remove deleted rows from the table
                        $('input[name="selected[]"]:checked').each(function() {
                            $(this).closest('tr').remove();
                        });

                        // Show success message
                        toastr.success('Selected products deleted successfully.');

                        // Reload the page if no products left
                        if ($('.product-row').length === 0) {
                            location.reload();
                        }
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('An error occurred while deleting products.');
                }
            });
        }
    });

    $('#upload_bulk_product').click(function() {
        $('.error-message').text('');

        let valid = true;
        let formData = new FormData();
        let selectedCount = 0;

        $('input[name="selected[]"]:checked').each(function(index) {
            let row = $(this).closest('tr');
            let id = $(this).val();

            let productDescription = row.find('input[name="product_description[]"]').val()
            .trim();
            let dealerType = row.find('select[name="dealer_type[]"]').val();
            let taxClass = row.find('select[name="tax_class[' + id + ']"]').val();
            let hsn = row.find('input[name="hsn[]"]').val().trim();
            let imageFile = row.find('input[type="file"][name="product_image[]"]')[0]?.files[0];

            // Validate fields
            if (productDescription === '') {
                row.find('[data-field="product_description"]').text('Description is required.');
                valid = false;
            }
            if (dealerType === '') {
                row.find('[data-field="dealer_type"]').text('Dealer type is required.');
                valid = false;
            }
            if (taxClass === '') {
                row.find('[data-field="tax_class"]').text('GST rate is required.');
                valid = false;
            }
            if (hsn === '') {
                row.find('[data-field="hsn"]').text('HSN code is required.');
                valid = false;
            }

            // Append product data to FormData
            formData.append(`products[${index}][id]`, id);
            formData.append(`products[${index}][product_description]`, productDescription);
            formData.append(`products[${index}][dealer_type]`, dealerType);
            formData.append(`products[${index}][tax_class]`, taxClass);
            formData.append(`products[${index}][hsn]`, hsn);
            if (imageFile) {
                formData.append(`products[${index}][product_image]`, imageFile);
            }

            selectedCount++;
        });

        if (selectedCount === 0) {
            alert("Please select at least one product.");
            return;
        }

        if (!valid) {
            return;
        }

        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: '{{ route("admin.bulk-products.update-multiple") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    toastr.success('Products updated successfully.');
                    location.reload();
                } else {
                    toastr.error('Something went wrong.');
                }
            },
            error: function() {
                toastr.error('Server error. Try again later.');
            }
        });
    });

});
</script>
@endsection