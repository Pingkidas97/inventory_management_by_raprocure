@extends('vendor.layouts.app_second')
@section('title', 'Manage Products List - Raprocure')
@section('content')
<section class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-global py-2 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Manage Products</li>
        </ol>
    </nav>

    <section class="manage-product card">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h1 class="card-title font-size-18 mb-0">Manage Products</h1>
                <div class="row mt-3 justify-content-between">
                    <div class="col-12 col-sm-auto mb-3 col-lg-0">
                        <a href="{{ route('vendor.products.create') }}" class="ra-btn ra-btn-primary btn-lg btn-add-product py-3 mobile-only-w-100 font-size-12 gap-2">
                            <span class="bi bi-plus-square font-size-12"></span>
                            <span class="font-size-11">ADD PRODUCT TO YOUR PROFILE</span>
                        </a>
                    </div>
                    <div class="col-12 col-sm-auto">
                        <div class="row g-3">
                            <div class="col-12 col-sm-auto">
                                <a class="ra-btn ra-btn-sm ra-btn-outline-primary px-3 py-2 mobile-only-w-100 justify-content-center btn-add-product-sm" href="{{ route('vendor.products.fast_track_product') }}">Fast Track Product Addition</a>
                            </div>
                            <div class="col-12 col-sm-auto d-none d-md-block">
                                <a class="ra-btn ra-btn-sm ra-btn-outline-primary px-3 py-2 mobile-only-w-100 justify-content-center btn-add-product-sm" href="{{ route('vendor.products.add_multiple_product') }}">ADD MULTIPLE PRODUCT</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="ra-global-tabs manage-product-tab">
                    <ul class="nav nav-tabs" id="manageProductsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="approve-product-tab" data-bs-toggle="tab" data-bs-target="#approve-product" type="button" role="tab" aria-controls="approve-product" aria-selected="true">
                                Approved <span class="d-none d-sm-inline-block">Products</span> (<span>{{ $approvedCount }}</span>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pending-product-tab" data-bs-toggle="tab" data-bs-target="#pending-product" type="button" role="tab" aria-controls="pending-product" aria-selected="false">
                                Pending <span class="d-none d-sm-inline-block">Products</span> (<span>{{ $pendingCount }}</span>)
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content py-3" id="manageProductsTabContent">
                        <!-- Approved Tab -->
                        <div class="tab-pane fade show active" id="approve-product" role="tabpanel" aria-labelledby="approve-product-tab">
                            <form id="approvedSearchForm" class="mb-3">
                                <div class="row align-items-center flex-wrap gx-3 pt-3">
                                    <div class="col-12 col-sm-6 col-md-auto mb-4">
                                        <div class="input-group generate-rfq-input-group">
                                            <span class="input-group-text">
                                                <span class="bi bi-journal-text" aria-hidden="true"></span>
                                            </span>
                                            <div class="form-floating">
                                                <input type="text" name="product_name" class="form-control" id="approvedProductName" placeholder="Product Name">
                                                <label for="approvedProductName">Product Name</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-auto mb-4">
                                        <div class="input-group generate-rfq-input-group">
                                            <span class="input-group-text">
                                                <span class="bi bi-share" aria-hidden="true"></span>
                                            </span>
                                            <div class="form-floating">
                                                <select name="division" class="form-select cw-150" id="approvedSelectDivision" aria-label="Select Division">
                                                    <option value="">Select</option>
                                                    @foreach($divisions as $division)
                                                        <option value="{{ $division->id }}">{{ $division->division_name }}</option>
                                                    @endforeach
                                                </select>
                                                <label for="approvedSelectDivision">Division</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-auto mb-4">
                                        <div class="input-group generate-rfq-input-group">
                                            <span class="input-group-text">
                                                <span class="bi bi-signpost" aria-hidden="true"></span>
                                            </span>
                                            <div class="form-floating">
                                                <select name="category" class="form-select cw-200" id="approvedSelectCategory" aria-label="Select Category">
                                                    <option value="">Select</option>
                                                </select>
                                                <label for="approvedSelectCategory">Category</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-auto mb-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <button type="submit" class="ra-btn ra-btn-primary">
                                                <span class="bi bi-search font-size-12"></span>
                                                <span class="font-size-11">Search</span>
                                            </button>
                                            <button type="reset" class="ra-btn ra-btn-outline-danger" onclick="window.location.reload();">
                                                <span class="font-size-11">Reset</span>
                                            </button>

                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div id="approved-products-container" class="table-responsive"></div>
                        </div>

                        <!-- Pending Tab -->
                        <div class="tab-pane fade" id="pending-product" role="tabpanel" aria-labelledby="pending-product-tab">
                            <form id="pendingSearchForm" class="mb-3">
                                <div class="row align-items-center flex-wrap gx-3 pt-3">
                                    <div class="col-12 col-sm-6 col-md-auto mb-4">
                                        <div class="input-group generate-rfq-input-group">
                                            <span class="input-group-text">
                                                <span class="bi bi-journal-text" aria-hidden="true"></span>
                                            </span>
                                            <div class="form-floating">
                                                <input type="text" name="product_name" class="form-control" id="pendingProductName" placeholder="Product Name">
                                                <label for="pendingProductName">Product Name</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-auto mb-4">
                                        <div class="input-group generate-rfq-input-group">
                                            <span class="input-group-text">
                                                <span class="bi bi-share" aria-hidden="true"></span>
                                            </span>
                                            <div class="form-floating">
                                                <select name="division" class="form-select cw-150" id="pendingSelectDivision" aria-label="Select Division">
                                                    <option value="">Select</option>
                                                    @foreach($divisions as $division)
                                                        <option value="{{ $division->id }}">{{ $division->division_name }}</option>
                                                    @endforeach
                                                </select>
                                                <label for="pendingSelectDivision">Division</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-auto mb-4">
                                        <div class="input-group generate-rfq-input-group">
                                            <span class="input-group-text">
                                                <span class="bi bi-signpost" aria-hidden="true"></span>
                                            </span>
                                            <div class="form-floating">
                                                <select name="category" class="form-select cw-200" id="pendingSelectCategory" aria-label="Select Category">
                                                    <option value="">Select</option>
                                                </select>
                                                <label for="pendingSelectCategory">Category</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-auto mb-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <button type="submit" class="ra-btn ra-btn-primary">
                                                <span class="bi bi-search font-size-12"></span>
                                                <span class="font-size-11">Search</span>
                                            </button>
                                            <button type="reset" class="ra-btn ra-btn-outline-danger" onclick="window.location.reload();">
                                                <span class="font-size-11">Reset</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div id="pending-products-container" class="table-responsive"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    loadApprovedList();

    $('#approve-product-tab').on('click', function () {
        loadApprovedList();
    });

    $('#pending-product-tab').on('click', function () {
        loadPendingList();
    });

    $(document).on('submit', '#approvedSearchForm', function(e) {
        e.preventDefault();
        loadApprovedList(1, $(this).serialize());
    });

    $(document).on('submit', '#pendingSearchForm', function(e) {
        e.preventDefault();
        loadPendingList(1, $(this).serialize());
    });

    function loadApprovedList(page = 1, query = '') {
        $.ajax({
            url: "{{ route('vendor.manage-products.approved') }}?page=" + page + '&' + query,
            type: 'GET',
            beforeSend: function () {
                $('#approved-products-container').html('<div class="text-center py-4">Loading...</div>');
            },
            success: function (data) {
                $('#approved-products-container').html(data);
            }
        });
    }

    function loadPendingList(page = 1, query = '') {
        $.ajax({
            url: "{{ route('vendor.manage-products.pending') }}?page=" + page + '&' + query,
            type: 'GET',
            beforeSend: function () {
                $('#pending-products-container').html('<div class="text-center py-4">Loading...</div>');
            },
            success: function (data) {
                $('#pending-products-container').html(data);
            }
        });
    }

    $(document).on('click', '.pagination a', function (e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        const activeTab = $('.nav-tabs .active').attr('id');
        const query = activeTab === 'approve-product-tab' ? $('#approvedSearchForm').serialize() : $('#pendingSearchForm').serialize();

        if (activeTab === 'approve-product-tab') {
            loadApprovedList(page, query);
        } else {
            loadPendingList(page, query);
        }
    });

    $(document).on('change', '#approvedSelectDivision, #pendingSelectDivision', function () {
        const divisionId = $(this).val();
        const categorySelect = $(this).attr('id') === 'approvedSelectDivision' ? '#approvedSelectCategory' : '#pendingSelectCategory';

        if (divisionId !== '') {
            $.ajax({
                url: "{{ route('vendor.getCategoriesByDivision', '') }}/" + divisionId,
                type: 'GET',
                beforeSend: function () {
                    $(categorySelect).html('<option value="">Loading...</option>');
                },
                success: function (data) {
                    let options = '<option value="">Select</option>';
                    data.forEach(function (cat) {
                        options += `<option value="${cat.category_name}">${cat.category_name}</option>`;
                    });
                    $(categorySelect).html(options);
                }
            });
        } else {
            $(categorySelect).html('<option value="">Select</option>');
        }
    });
});
</script>
@endsection
