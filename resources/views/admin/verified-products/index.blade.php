@extends('admin.layouts.app_second',['title' => 'Verified Products','sub_title' => 'List'])
@section('css')

@endsection
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i> <a href="{{ route('admin.dashboard') }}">Dashboard </a>
            <a href="{{ route('admin.verified-products.index') }}"> -> All Verified Products </a>
        </h5>
    </div>
</div>
@endsection
@section('content')
<div class="about_page_details">
    <div class="container-fluid">
        <div class="card border-0">
            <div class="card-body">
                <div class="col-md-12 botom-border">
                    <h3 class="">All Verified Products</h3>
                    <div class="row">
                        <div class="col-md-12">
                            <div id="export-progress">
                                <p>Export Progress: <span id="progress-text">0%</span></p>
                                <div id="progress-bar" style="width: 100%; background: #f3f3f3;">
                                    <div id="progress" style="height: 20px; width: 0%; background: green;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-8 col-lg-8 col-md-12">
                            <form id="searchForm" action="{{ route('admin.verified-products.index') }}" method="GET">
                                <ul class="rfq-filter-button">
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="text" name="product_name"
                                                    class="form-control fillter-form-control"
                                                    value="{{ request('product_name') }}" placeholder="Product Name">
                                                <label for="">Product Name</label>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="text" name="vendor_name"
                                                    class="form-control fillter-form-control"
                                                    value="{{ request('product_name') }}" placeholder="Vendor Name">
                                                <label for="">Vendor Name</label>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="notShow_on_mobile">
                                        <button type="submit" class="btn-style btn-style-primary">
                                            <i class="bi bi-search"></i>
                                            Search
                                        </button>
                                    </li>
                                    <li class="notShow_on_mobile">
                                        <a href="{{ route('admin.verified-products.index') }}"
                                            class="btn-style btn-style-danger">RESET</a>
                                    </li>
                                    <li class="notShow_on_mobile">
                                        <button class="btn-style btn-style-white" id="export-btn"><i
                                                class="bi bi-download"></i>
                                            EXPORT
                                        </button>
                                    </li>
                                </ul>
                            </form>
                            <ul class="rapo_btn-grp">
                                <li>
                                    <button type="submit" name="search" class="btn-style btn-style-primary">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                        Search
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="btn-style btn-style-danger">
                                        Reset
                                    </button>
                                </li>
                                <li>
                                    <a class="btn-style btn-style-white" href="javascript:void(0);" id="export-btn">
                                        <i class="fa-regular fa-square-plus"></i>
                                        EXPORT
                                    </a>
                                </li>
                            </ul>
                        </div>




                        <div class="col-xl-4 col-lg-4 col-md-12">
                            <ul class="rfq-filter-button">
                                <li>
                                    <select class="form-select" id="tag-type">
                                        <option value="PRIME">Prime</option>
                                        <option value="POPULAR">Popular</option>
                                        <option value="NOTHING" selected>Nothing</option>
                                    </select>
                                </li>

                                <li>
                                    <select class="form-select" id="time-period">
                                        <option value="">Month</option>
                                        <option value="1">1 Month</option>
                                        <option value="3">3 Months</option>
                                        <option value="6">6 Months</option>
                                        <option value="9">9 Months</option>
                                        <option value="12">12 Months</option>
                                    </select>
                                </li>
                                <li class="move_to_end">
                                    <button type="button" class="btn-style btn-style-secondary" id="apply-filter">
                                        Apply
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="table-responsive product_listing_table_wrap" id="table-container">
                        @include('admin.verified-products.partials.table', ['products' => $products])
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
    $(document).on('submit', '#searchForm', function(e) {
        e.preventDefault();
        loadTable($(this).attr('action') + '?' + $(this).serialize());
    });

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        loadTable($(this).attr('href'));
    });

    // Handle perPage dropdown change
    $(document).on('change', '#perPage', function() {
        const form = $('#searchForm');
        const formData = form.serialize(); // Get current search filters
        const perPage = $(this).val();
        const url = form.attr('action') + '?' + formData + '&per_page=' + perPage;

        loadTable(url);
    });

    function loadTable(url) {
        $.ajax({
            url: url,
            type: 'GET',
            beforeSend: function() {
                $('#table-container').html('<div class="text-center py-4">Loading...</div>');
            },
            success: function(response) {
                $('#table-container').html(response);
                if (history.pushState) {
                    history.pushState(null, null, url);
                }
            }
        });
    }

    $(document).on('change', '.product-status-toggle', function() {
        const id = $(this).data('id');
        const status = $(this).is(':checked') ? 1 : 0;
        const checkbox = $(this);

        $.ajax({
            url: "{{ url('admin/verified-products') }}/" + id + "/status",
            type: "PUT",
            data: {
                _token: "{{ csrf_token() }}",
                status: status
            },
            success: function(res) {
                toastr.success(res.message);
            },
            error: function() {
                toastr.error('Something went wrong.');
                checkbox.prop('checked', !checkbox.prop('checked'));
            }
        });
    });

    $(document).on('click', '.btn-delete-product', function() {
        if (!confirm('Are you sure?')) return;
        const id = $(this).data('id');

        $.ajax({
            url: "{{ url('admin/verified-products') }}/" + id,
            type: "DELETE",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                toastr.success(res.message);
                location.reload();
            }
        });
    });

    $(document).on('click', '#apply-filter', function() {
        let selectedProducts = [];

        $(".product-checkbox:checked").each(function() {
            selectedProducts.push($(this).val());
        });

        let prodTag = $("#tag-type").val(); // Make sure your select dropdown has this ID
        let validMonths = $("#time-period").val();

        if (selectedProducts.length === 0) {
            alert("Please select at least one product.");
            return;
        }

        if (!prodTag) {
            alert("Please select a badge type.");
            return;
        }

        if (prodTag !== "NOTHING" && !validMonths) {
            alert("Please select a valid time period.");
            return;
        }

        $.ajax({
            url: "{{ route('admin.verified-products.update-tags') }}", // Create this route
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                product_ids: selectedProducts,
                prod_tag: prodTag,
                valid_months: validMonths
            },
            dataType: "json",
            success: function(response) {
                toastr.success(response.message);
                if (response.status === "success") {
                    location.reload();
                }
            },
            error: function() {
                toastr.error("Something went wrong. Please try again.");
            }
        });
    });
});
</script>

<script src="{{ asset('public/assets/xlsx/xlsx.full.min.js') }}"></script>
<script src="{{ asset('public/assets/xlsx/export.js') }}"></script>
<script>
$(document).ready(function() {
    const exporter = new Exporter({
        chunkSize: 1000,
        rowLimitPerSheet: 200000,
        headers: ["Vendor Name", "Division", "Category", "Product Name", "Product Alias",
            "Added By Vendor", "Verified By"
        ],
        totalUrl: "{{ route('admin.verified-products.exportTotal') }}",
        batchUrl: "{{ route('admin.verified-products.exportBatch') }}",
        token: "{{ csrf_token() }}",
        exportName: "All-Verified-Products",
        expButton: '#export-btn',
        exportProgress: '#export-progress',
        progressText: '#progress-text',
        progress: '#progress',
        fillterReadOnly: '.fillter-form-control',
        getParams: function() {
            return {
                name: $('#name').val(),
                vendor_name: $('#vendor_name').val()
            };
        }
    });

    $('#export-btn').on('click', function() {
        exporter.start();
    });

    $('#export-progress').hide();
});
</script>
@endsection