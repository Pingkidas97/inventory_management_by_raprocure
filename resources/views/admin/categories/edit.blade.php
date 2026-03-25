@extends('admin.layouts.app_second', [
    'title' => 'Category',
    'sub_title' => 'Edit Category',
])

@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.categories.index', ['id' => $category->division_id]) }}"> -> Category List</a>
            <span> -> Edit Category</span>
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
                <div class="basic-form pro_edit">
                    <form id="editCategoryForm" class="form-horizontal form-material">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="category_name" class="form-label"><strong>Category Name</strong> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control capital" id="category_name" name="category_name"
                                       value="{{ old('category_name', $category->category_name) }}" oninput="limitCharacters(this,255)">
                                <input type="hidden" name="division_id" value="{{ $category->division_id }}">
                                <span class="text-danger error-text category_name_error"></span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label d-block"><strong>Status</strong> <span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" id="active"
                                           value="1" {{ $category->status == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="active">Active</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" id="inactive"
                                           value="2" {{ $category->status == '2' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="inactive">Inactive</label>
                                </div>
                                <span class="text-danger error-text status_error"></span>
                            </div>
                        </div>

                        <div class="d-flex gap-1 justify-content-center">
                            <button type="submit" class="btn-rfq btn-rfq-primary">Update</button>
                            <a href="{{ route('admin.categories.index', ['id' => $category->division_id]) }}" class="btn-rfq btn-rfq-danger">Cancel</a>
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
    $('#editCategoryForm input').on('keyup change', function() {
        const fieldName = $(this).attr('name');
        $(`span.error-text.${fieldName}_error`).text('');
    });

    $('#editCategoryForm').submit(function (e) {
        e.preventDefault();

        $('span.error-text').text('');
        const category_name = $('#category_name').val().trim();
        const status = $('input[name="status"]:checked').val();
        let hasErrors = false;

        if (!category_name) {
            $('span.error-text.category_name_error').text('Please enter the category name.');
            hasErrors = true;
        } else if (category_name.length < 2) {
            $('span.error-text.category_name_error').text('Category name must be at least 2 characters.');
            hasErrors = true;
        }

        if (!status) {
            $('span.error-text.status_error').text('Please select a status.');
            hasErrors = true;
        }

        if (hasErrors) return;

        $.ajax({
            url: "{{ route('admin.categories.update', $category->id) }}",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => {
                        window.location.href = "{{ route('admin.categories.index', ['id' => $category->division_id]) }}";
                    }, 300);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function (key, value) {
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
