@extends('admin.layouts.app_second', ['title' => 'Auction RFQs Summary', 'sub_title' => 'Report List'])
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i> <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.reports.auction-rfqs-summary') }}"> ->Auction RFQs Summary Reports</a>
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
                    <h3 class="">Auction RFQs Summary Reports</h3>
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
                            <form id="searchForm" action="{{ route('admin.reports.auction-rfqs-summary') }}" method="GET">
                                <ul class="rfq-filter-button">
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="text" name="buyer_name" id="buyer_name" class="form-control fillter-form-control" value="{{ request('buyer_name') }}" placeholder="Buyer Name">
                                                <label>Buyer Name</label>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="date" name="from_date" name="from_date" class="form-control fillter-form-control" value="{{ request('from_date') }}" placeholder="From Date">
                                                <label>From Date</label>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="date" name="to_date" id="to_date" class="form-control fillter-form-control" value="{{ request('to_date') }}" placeholder="To Date">
                                                <label>To Date</label>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="notShow_on_mobile">
                                        <button type="submit" class="btn-style btn-style-primary"><i class="bi bi-search"></i> Search</button>
                                    </li>
                                    <li class="notShow_on_mobile">
                                        <a href="{{ route('admin.reports.auction-rfqs-summary') }}" class="btn-style btn-style-danger">RESET</a>
                                    </li>
                                    <li class="notShow_on_mobile">
                                        <button class="btn-style btn-style-white" id="export-btn"><i class="bi bi-download"></i>
                                            EXPORT
                                        </button>
                                    </li>
                                </ul>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive product_listing_table_wrap" id="table-container">
                        @include('admin.reports.partials.auction-rfq-summary-report-table', ['results' => $results])
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

    $(document).on('change', '#perPage', function () {
        const form = $('#searchForm');
        const formData = form.serialize();
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
</script>

<script src="{{ asset('public/assets/xlsx/xlsx.full.min.js') }}"></script>
<script src="{{ asset('public/assets/xlsx/export.js') }}"></script>
<script>
    $(document).ready(function () {
        const exporter = new Exporter({
            chunkSize: 100,
            rowLimitPerSheet: 200000,
            headers: ["RFQ No", "Auction Date", "Auction Time", "Buyer Name", "Products", "Vendor Name", "Email","Mobile","Status","Is Participated","Order Confirmed"],
            totalUrl: "{{ route('admin.auction-rfqs-summary.exportTotal') }}",
            batchUrl: "{{ route('admin.auction-rfqs-summary.exportBatch') }}",
            token: "{{ csrf_token() }}",
            exportName: "rfqs-rummary-report",
            expButton: '#export-btn',
            exportProgress: '#export-progress',
            progressText: '#progress-text',
            progress: '#progress',
            fillterReadOnly: '.fillter-form-control',
            getParams: function () {
                return {
                    buyer_name: $('#buyer_name').val(),
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val()
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