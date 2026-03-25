@extends('admin.layouts.app_second', [
    'title' => 'Product',
    'sub_title' => 'Add Product',
])

@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-box"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.products.index', ['id' => $id]) }}"> -> Product List</a>
            <span> -> Add Product</span>
        </h5>
    </div>
</div>
@endsection

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
<div class="page-start-section">
<div class="container-fluid">
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="basic-form pro_edit">
                  <form id="createProductForm" class="form-horizontal form-material" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf

                    <!-- Division -->
                    <div class="row mb-3">
                        <div class="col-md-3 d-flex align-items-center">
                            <label for="product_division" class="form-label mb-0"><strong>Division</strong> <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-9">
                            <select id="product_division" name="division_id" class="form-select">
                                <option value="">Select</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->division_name }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-text division_id_error"></span>
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="row mb-3">
                        <div class="col-md-3 d-flex align-items-center">
                            <label for="product_category" class="form-label mb-0"><strong>Category</strong> <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-9">
                            <select id="product_category" name="category_id" class="form-select">
                                <option value="">Select</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-text category_id_error"></span>
                        </div>
                    </div>

                    <!-- Product Name -->
                    <div class="row mb-3">
                        <div class="col-md-3 d-flex align-items-center">
                            <label for="product_name" class="form-label mb-0"><strong>Product Name</strong> <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="product_name" name="product_name" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '').substring(0, 255);">
                            <span class="text-danger error-text product_name_error"></span>
                        </div>
                    </div>

                    <!-- Tags / Aliases -->
                    <div class="row mb-3">
                        <div class="col-md-3 d-flex align-items-center">
                            <label for="tags-input" class="form-label mb-0"><strong>Aliases & Tags</strong></label>
                        </div>
                        <div class="col-md-9">
                            <input type="text" data-role="tagsinput" class="form-control" name="tags" id="tags-input">
                            <span class="text-danger error-text tags_error"></span>
                        </div>
                    </div>

                    <div class="d-flex gap-1">
                        <button type="submit" class="btn-rfq btn-rfq-primary">Add Product</button>
                        <a href="{{ route('admin.products.index', ['id' => $id]) }}" class="btn-rfq btn-rfq-danger">Cancel</a>
                    </div>
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
$(document).ready(function () {
    $('#createProductForm input, #createProductForm select').on('keyup change', function () {
        const fieldName = $(this).attr('name');
        $(`span.error-text.${fieldName}_error`).text('');
    });

    $('#createProductForm').submit(function (e) {
        e.preventDefault();
        $('span.error-text').text('');
        let hasErrors = false;

        const division = $('#product_division').val().trim();
        const category = $('#product_category').val().trim();
        const productName = $('#product_name').val().trim();

        if (!division) {
            $('span.error-text.division_id_error').text('Please select a division.');
            hasErrors = true;
        }
        if (!category) {
            $('span.error-text.category_id_error').text('Please select a category.');
            hasErrors = true;
        }
        if (!productName) {
            $('span.error-text.product_name_error').text('Please enter product name.');
            hasErrors = true;
        } else if (productName.length < 2) {
            $('span.error-text.product_name_error').text('Product name must be at least 2 characters.');
            hasErrors = true;
        }

        if (hasErrors) return;

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
                        window.location.href = "{{ route('admin.products.index', ['id' => $id]) }}";
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
