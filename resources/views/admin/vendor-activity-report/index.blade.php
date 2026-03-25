@extends('admin.layouts.app_second', ['title' => 'Vendor Activity','sub_title' => 'Report List'])
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i> <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.vendor-activity-report.index') }}"> -> Vendor Activity Report</a>
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
                    <h3>Vendor Activity Report</h3>
                    <div class="row">
                        <div class="col-md-12">
                            <div id="export-progress">
                                <p>Export Progress: <span id="progress-text">0%</span></p>
                                <div id="progress-bar" style="width: 100%; background: #f3f3f3;">
                                    <div id="progress" style="height: 20px; width: 0%; background: green;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12 col-lg-12 col-md-12">
                            <form id="searchForm" action="{{ route('admin.vendor-activity-report.index') }}" method="GET">
                                <ul class="rfq-filter-button">
                                    
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <div class="form-floating">
                                                <input type="text" name="vendor_name" class="form-control fillter-form-control" value="{{ request('vendor_name') }}" placeholder="Vendor Name">
                                                <label>Vendor Name</label>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="text" name="product_name" class="form-control fillter-form-control" value="{{ request('register_address') }}" placeholder="Register Address">
                                                <label>Register Address</label>
                                            </div>
                                        </div>
                                    </li>

                                    <!-- From Date -->
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                            <div class="form-floating">
                                                <input type="date" name="from_date" class="form-control fillter-form-control" value="{{ request('from_date') }}" placeholder="From Date" />
                                                <label>From Date</label>
                                            </div>
                                        </div>
                                    </li>

                                    <!-- To Date -->
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                            <div class="form-floating">
                                                <input type="date" name="to_date" class="form-control fillter-form-control" value="{{ request('to_date') }}" placeholder="To Date" />
                                                <label>To Date</label>
                                            </div>
                                        </div>
                                    </li>


                                    <li>
                                        <button type="submit" class="btn-style btn-style-primary"><i class="bi bi-search"></i> Search</button>
                                    </li>
                                    <li class="notShow_on_mobile">
                                        <a href="{{ route('admin.vendor-activity-report.index') }}" class="btn-style btn-style-danger">RESET</a>
                                    </li>
                                    <!-- Export Button -->
                                    <li>
                                        <button type="button" id="export-btn" class="btn-rfq btn-rfq-white">
                                            <i class="bi bi-download"></i> Export
                                        </button>
                                    </li>
                                    
                                </ul>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive product_listing_table_wrap" id="table-container">
                        @include('admin.vendor-activity-report.partials.table', ['vendors' => $vendors])
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
            beforeSend: function () {
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
});
$(document).ready(function() {
    // When "Select All" is clicked
    $('#select-all-products').on('change', function() {
        $('.product-checkbox').prop('checked', this.checked);
    });

    // When any individual checkbox is clicked
    $('.product-checkbox').on('change', function() {
        // If any checkbox is unchecked, uncheck "Select All"
        if (!$(this).prop('checked')) {
            $('#select-all-products').prop('checked', false);
        } else {
            // If all checkboxes are checked, check "Select All"
            if ($('.product-checkbox:checked').length === $('.product-checkbox').length) {
                $('#select-all-products').prop('checked', true);
            }
        }
    });
});
$(document).on('click', '#delete-selected', function () {
    let productIds = [];
    let vendorIds = [];

    $('.row-checkbox:checked').each(function () {
        productIds.push($(this).data('product-id'));
        vendorIds.push($(this).data('vendor-id'));
    });

    if (productIds.length === 0) {
        alert("Please select at least one product to delete.");
        return;
    }

    if (confirm("Are you sure you want to delete the selected products?")) {
        $.ajax({
            url: "{{ route('admin.vendor-disabled-product-report.bulkDelete') }}", // Laravel route
            type: 'POST',
            data: {
                product_ids: productIds,
                vendor_ids: vendorIds,
                _token: '{{ csrf_token() }}' // CSRF token for Laravel
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 1) {
                    toastr.success(response.message1);
                    toastr.error(response.message2);
                    // location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert("An error occurred while deleting products. Please try again.");
            }
        });
    }
});
</script>
<script src="{{ asset('public/assets/xlsx/xlsx.full.min.js') }}"></script>
<script src="{{ asset('public/assets/xlsx/export.js') }}"></script>
<script>
$(document).ready(function () {
    const exporter = new Exporter({
        chunkSize: 1000,
        rowLimitPerSheet: 200000,
        headers: [
            "Vendor Name",
            "Product Name",
            "Division > Category",
            "Date"
        ],
        totalUrl: "{{ route('admin.vendor-disabled-product-report.exportTotal') }}",
        batchUrl: "{{ route('admin.vendor-disabled-product-report.exportBatch') }}",
        token: "{{ csrf_token() }}",
        exportName: "Disabled-Products-Report",
        expButton: '#export-btn',
        exportProgress: '#export-progress',
        progressText: '#progress-text',
        progress: '#progress',
        fillterReadOnly: '.fillter-form-control',
        getParams: function () {
            return {
                product_name: $('[name="product_name"]').val(),
                vendor_name: $('[name="vendor_name"]').val()
            };
        }
    });

    $('#export-btn').on('click', function () {
        exporter.start();
    });

    $('#export-progress').hide();
});
</script>
@endsection
