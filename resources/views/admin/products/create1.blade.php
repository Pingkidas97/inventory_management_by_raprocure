@extends('admin.layouts.app')
@section('title', 'Add New Product')
@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>
<style>
.bootstrap-tagsinput {
    width: 100%;
    min-height: 38px;
    padding: 0.375rem 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}
.bootstrap-tagsinput .tag {
    margin-right: 2px;
    color: white;
    background-color: #0d6efd;
    padding: 2px 5px;
    border-radius: 3px;
}
</style>
<div class="container-fluid">
    <div class="card shadow mb-4 mt-3">
        <div class="card-header py-3">
            <h5 class="mb-0 text-primary">Add New Product</h5>
        </div>
        <div class="card-body">
            <form class="general_information_form" method="POST" id="general_information_form" enctype="multipart/form-data" novalidate>
                @csrf
                <!-- Division Dropdown -->
                <div class="mb-3">
                    <label class="form-label">Division <span class="text-danger">*</span></label>
                    <select id="product_division" name="division_id" class="form-select">
                        <option value="">Select</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->id }}">{{ $division->division_name }}</option>
                        @endforeach
                    </select>
                    <span class="text-danger error-text div_id_error"></span>
                </div>

                <!-- Category Dropdown -->
                <div class="mb-3">
                    <label class="form-label">Category <span class="text-danger">*</span></label>
                    <select id="product_category" name="category_id" class="form-select">
                        <option value="">Select</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                    <span class="text-danger error-text cat_id_error"></span>
                </div>

                <!-- Product Name -->
                <div class="mb-3">
                    <label class="form-label">Product Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" placeholder="Product Name" name="product_name" id="prod_name" maxlength="255"
                           oninput="this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '').substring(0, 255);">
                    <span class="text-danger error-text prod_name_error"></span>
                </div>

                <!-- Aliases & Tags -->
                <div class="mb-3">
                    <label class="form-label">Aliases & Tags</label>
                    <input type="text" data-role="tagsinput" class="form-control" name="tags" id="tags-input" value="">
                    <span class="text-danger error-text tag_error"></span>
                </div>


                <!-- Submit Buttons -->
                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.products.index',['id' => $id ]) }}" class="btn btn-secondary me-2" onclick="window.history.back(); return false;">Cancel</a>
                    <button type="submit" id="general_form_submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(document).ready(function () {

        // Clear error on input change or keyup
        $('#general_information_form input, #general_information_form select').on('keyup change', function () {
            const fieldName = $(this).attr('name');
            $(`span.error-text.${fieldName}_error`).text('');
        });

        $('#general_information_form').submit(function (e) {
            e.preventDefault();
            $('span.error-text').text('');
            let hasErrors = false;

            const division = $('#product_division').val().trim();
            const category = $('#product_category').val().trim();
            const productName = $('#prod_name').val().trim();

            if (!division) {
                $('span.error-text.div_id_error').text('Please select a division.');
                hasErrors = true;
            }

            if (!category) {
                $('span.error-text.cat_id_error').text('Please select a category.');
                hasErrors = true;
            }

            if (!productName) {
                $('span.error-text.prod_name_error').text('Please enter product name.');
                hasErrors = true;
            } else if (productName.length < 2) {
                $('span.error-text.prod_name_error').text('Product name must be at least 2 characters.');
                hasErrors = true;
            }

            if (hasErrors) return;

            // AJAX Submit if validation passes
            let formData = new FormData(this);

            $.ajax({
                url: "{{ route('admin.products.store') }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(() => {
                            window.location.href = "{{ route('admin.products.index',['id' => $id]) }}";
                        }, 500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function (key, value) {
                            $('span.error-text.' + key + '_error').text(value[0]);
                        });
                    } else {
                        toastr.error('Something went wrong!');
                    }
                }
            });
        });

        // Optional: Load categories dynamically when division changes
        $('#product_division').on('change', function () {
            var divisionId = $(this).val();
            $('#product_category').html('<option value="">Loading...</option>');
            $.get('{{ route("admin.getCategoriesByDivision") }}', { division_id: divisionId }, function (data) {
                let options = '<option value="">Select</option>';
                $.each(data.categories, function (index, cat) {
                    options += `<option value="${cat.id}">${cat.category_name}</option>`;
                });
                $('#product_category').html(options);
            });
        });

    });
</script>
@endsection
