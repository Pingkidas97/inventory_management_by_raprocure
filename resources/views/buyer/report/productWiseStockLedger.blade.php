@php
    use Illuminate\Support\Str;
    $decodedSpecification = $inventory->specification
        ? cleanInvisibleCharacters($inventory->specification)
        : '-';
    $decodedSize = $inventory->size
        ? cleanInvisibleCharacters($inventory->size)
        : '-';

@endphp
@extends('buyer.layouts.appInventory', ['title'=> 'Product Wise Stock Ledger Report List'])
@push('styles')
    <link rel="stylesheet" href="{{ asset('public/css/report.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/inventoryAssets/css/jquery-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/bootstrap-icons/bootstrap-icons.css') }}">
    <style>
    .dataTables_scrollBody::-webkit-scrollbar {
        height: 14px;   /* horizontal scrollbar thickness */
        width: 14px;    /* vertical scrollbar thickness */
    }

    .dataTables_scrollBody::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background-color: #043e6c;
        border-radius: 8px;
        border: 3px solid #f1f1f1;
    }

    .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
        background-color: #043e6c;
    }
    </style>
@endpush
@push('headJs')
    @once
        <script src="{{ asset('public/assets/inventoryAssets/js/jquery-ui.min.js') }}"></script>
    @endonce
@endpush
@section('content')
    <div class="card rounded">

        <div class="card-header bg-white">
            <div class="row align-items-center justify-content-between mb-3">
                <div class="col-12 col-sm-auto">
                    <h1 class="font-size-22 mb-0">Product Wise Stock Ledger Report</h1>
                </div>
                <div class="col-12 col-sm-auto">
                    <label class="font-size-11 text-primary-blue ps-5">Branch/Unit:</label>
                    <div class="input-group w-100 w-200">
                        <span class="input-group-text"><span class="bi bi-shop"></span></span>
                        <div class="form-floating">
                            <select class="form-select globle-field-changes form-select branch_unit" name="branch_id"
                                id="branch_id" disabled>
                                <option value="{{ $inventory->branch->branch_id }}">{{ $inventory->branch->name }}</option>
                            </select>

                        </div>
                    </div>

                </div>

            </div>
        </div>

        <div class="card-body">
            <!-- Product Info Card -->
            <div class="product-stock-details text-center">
                <h2 class="font-size-22 fw-bold mb-3">{{ $inventory->product->product_name ?? $inventory->buyer_product_name }}</h2>
                <h3 class="font-size-18 mb-3"><strong class="font-size-18">Specification:</strong>
                    {{ $decodedSpecification }}
                </h3>
                <h3 class="font-size-18 mb-3"><strong class="font-size-18">Size:</strong>
                    {{ $decodedSize }}
                </h3>
                <h3 class="font-size-18 mb-3">Stock Ledger</h3>

            </div>

            <!-- Inventry Filter Section -->
            <form method="GET" class="row g-3 align-items-end mb-4">
                <div class="row g-3 pt-3 mb-3">
                    <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                        <div class="input-group">
                            <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                            <div class="form-floating">
                                <input type="text" class="form-control date-picker" id="from_date" name="from_date"
                                    placeholder="From Date">
                                <label for="from_date">From Date</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                        <div class="input-group">
                            <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                            <div class="form-floating">
                                <input type="text" class="form-control date-picker" id="to_date" name="to_date"
                                    placeholder="To Date">
                                <label for="to_date">To Date</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-sm-auto">
                        <button type="button" class="ra-btn ra-btn-primary w-100 justify-content-center font-size-11"
                            id="searchBtn">
                            <span class="bi bi-search" aria-hidden="true"></span>
                            Search
                        </button>
                    </div>

                    <div class="col-6 col-sm-auto">
                        <button type="button"
                            class="ra-btn ra-btn-outline-danger w-100 justify-content-center font-size-11"
                            onclick="window.location.href='{{ route('buyer.report.productWiseStockLedger.index', ['id' => $inventory->id]) }}'">
                            <span class="bi bi-arrow-clockwise" aria-hidden="true"></span>
                            Reset
                        </button>
                    </div>

                    <div class="col-6 col-sm-auto">
                        <button type="button"
                            class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-11" id="export">
                            <span class="bi bi-download" aria-hidden="true"></span>
                            Export
                        </button>
                    </div>

                    <div class="col-6 col-sm-auto">
                        <button type="button" class="ra-btn ra-btn-primary w-100 justify-content-center font-size-11"
                            onclick="window.location.href='{{ route('buyer.report.stockLedger') }}'">
                            Stock Ledger
                        </button>
                    </div>

                    <div class="col-6 col-sm-auto">
                        <button type="button"
                            class="ra-btn ra-btn-primary w-100 justify-content-center font-size-11 px-2 px-sm-3"
                            onclick="window.location.href='{{ route('buyer.inventory.index') }}'">
                            Back to Inventory
                        </button>
                    </div>



                </div>
            </form>

            <!-- Start Datatable -->

            <div class="table-responsive" id="inventory-table_wrapper">
                <table class="product-listing-table table-inventory dataTables-example1" id="report-table">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center align-bottom text-wrap keep-word border-bottom-dark">Date</th>
                            <th rowspan="2" class="text-center align-bottom text-wrap keep-word border-bottom-dark">Particulars / Description</th>
                            <th rowspan="2" class="text-center align-bottom text-wrap keep-word border-bottom-dark">No.</th>
                            <th rowspan="2" class="text-center align-bottom text-wrap keep-word border-bottom-dark">Reference Number</th>
                            <th colspan="2" class="text-center align-bottom text-wrap keep-word">Inwards</th>
                            <th colspan="2" class="text-center align-bottom text-wrap keep-word">Outwards</th>
                            <th colspan="2" class="text-center align-bottom text-wrap keep-word">Closing</th>
                        </tr>
                        <tr>
                            <th class="text-center align-bottom text-wrap keep-word border-bottom-dark">Quantity ({{ $inventory->uom->uom_name }})</th>
                            <th class="text-center align-bottom text-wrap keep-word border-bottom-dark">Total Amount ({{ session('user_currency.symbol', '₹') }})
                            </th>
                            <th class="text-center align-bottom text-wrap keep-word border-bottom-dark">Quantity ({{ $inventory->uom->uom_name }})</th>
                            <th class="text-center align-bottom text-wrap keep-word border-bottom-dark">Total Amount ({{ session('user_currency.symbol', '₹') }})
                            </th>
                            <th class="text-center align-bottom text-wrap keep-word border-bottom-dark">Quantity ({{ $inventory->uom->uom_name }})</th>
                            <th class="text-center align-bottom text-wrap keep-word border-bottom-dark">Total Amount ({{ session('user_currency.symbol', '₹') }})
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dynamic table data -->
                    </tbody>
                </table>
            </div>
            <!-- End Datatable -->
        </div>
    </div>

    <!--main-->
    @include('buyer.report.modal')
    @push('exJs')
        @once
            {{-- <script src="{{ asset('public/js/xlsx.full.min.js') }}"></script> --}}
            <script src="{{ asset('public/js/datepicker.js') }}"></script>
            <script src="{{ asset('public/js/inventoryFileExport.js') }}"></script>
            {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script> --}}
            <script>
                $(document).ready(function() {
                    $('input').val('');

                    if (typeof $.fn.DataTable !== "function") {
                        toastr.error("DataTables is not loaded! Check script order!");
                        return;
                    }
                    report_list_data();
                });

                function report_list_data() {
                    var inventory_id = "{{ $inventory->id ?? '' }}";
                    $('#report-table').DataTable({
                        processing  : true,
                        serverSide  : true,
                        searching   : false,
                        paging      : true,
                        "scrollY"   : 300,
                        pageLength  : 25,
                        ajax: {
                            url: "{{ route('buyer.report.productWiseStockLedger.listdata') }}",
                            data: function (d) {
                                d.inventory_id = inventory_id;
                                d.from_date = $('#from_date').val();
                                d.to_date = $('#to_date').val();
                            },
                        },
                        columns: [
                            { data: 'date', name: '' },
                            { data: 'description', name: '' },
                            { data: 'no', name: '' },
                            { data: 'reference_number', name: '' },
                            { data: 'inward_quantity', name: '' },
                            { data: 'inward_total_amount', name: '' },
                            { data: 'outward_quantity', name: '' },
                            { data: 'outward_total_amount', name: '' },
                            { data: 'closing_quantity', name: '' },
                            { data: 'closing_total_amount', name: '' },
                        ],
                        columnDefs: [
                            { "orderable": false, "targets": "_all" }
                        ],
                        order: [],
                        language: {
                                    processing: "<div class='spinner-border spinner-border-sm'></div> Loading..."
                                }

                    });
                    $('#report-table').on('draw.dt', function () {
                        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(function (tooltipTriggerEl) {
                            return new bootstrap.Tooltip(tooltipTriggerEl);
                        });
                    });

                }

                $(document).on('click', '#export', function() {
                    var inventory_id = "{{ $inventory->id ?? '' }}";
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('buyer.report.productWiseStockLedger.export') }}",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr("content"),
                            inventory_id: inventory_id,
                            from_date: $('#from_date').val(),
                            to_date: $('#to_date').val()
                        },
                        dataType: 'json',
                        success: function(result) {
                            if (result.count != 0) {
                                var createXLSLFormatObj = [];
                                var xlsHeader = [
                                    "Date",
                                    "Particulars / Description",
                                    "No.",
                                    "Reference Number",
                                    "Inward Quantity ({{ $inventory->uom->uom_name }})",
                                    "Inward Total Amount ({{ session('user_currency.symbol', '₹') }})",
                                    "Outward Quantity ({{ $inventory->uom->uom_name }})",
                                    "Outward Total Amount ({{ session('user_currency.symbol', '₹') }})",
                                    "Closing Quantity ({{ $inventory->uom->uom_name }})",
                                    "Closing Total Amount ({{ session('user_currency.symbol', '₹') }})"
                                ];
                                var xlsRows = result.data;
                                var s_no = 1;
                                createXLSLFormatObj.push(xlsHeader);

                                $.each(xlsRows, function(index, value) {
                                    var innerRowData = [];
                                    $.each(value, function(ind, val) {
                                        innerRowData.push(val);
                                    });
                                    createXLSLFormatObj.push(innerRowData);
                                });

                                createXLSLFormatObj.unshift([
                                    "","Size : " + @json($decodedSize), "", "", "", "","", "", "", ""
                                ]);

                                createXLSLFormatObj.unshift([
                                   "", "Specification : "+ @json($decodedSpecification), "", "", "", "","", "", "",  ""
                                ]);
                                createXLSLFormatObj.unshift([
                                    "","Branch Name : "+@json($inventory->branch->name), "", "", "", "", "", "", "",  ""
                                ]);
                                createXLSLFormatObj.unshift([
                                    "","Product Name : "+@json($inventory->product->product_name ?? $inventory->buyer_product_name), "", "", "", "", "", "", "",  ""
                                ]);
                                var date = new Date();
                                var dd = date.getDate();
                                var mm = date.getMonth() + 1;
                                var yy = date.getFullYear();
                                var today = ('0' + dd).slice(-2) + '-' + ('0' + mm).slice(-2) + '-' + yy;

                                var filename = "{{ $inventory->product->product_name }}" + " Stock Ledger Report " + today + ".xlsx";

                                var ws_name = "Reports";
                                var wb = XLSX.utils.book_new(),
                                    ws = XLSX.utils.aoa_to_sheet(createXLSLFormatObj);

                                ws['!merges'] = [
                                    { s: { r: 0, c: 1 }, e: { r: 0, c: 9 } },
                                    { s: { r: 1, c: 1 }, e: { r: 1, c: 9 } },
                                    { s: { r: 2, c: 1 }, e: { r: 2, c: 9 } },
                                    { s: { r: 3, c: 1 }, e: { r: 3, c: 9 } },
                                ];
                                XLSX.utils.book_append_sheet(wb, ws, ws_name);
                                XLSX.writeFile(wb, filename);
                            } else {
                                toastr.error('No record found, Try another search!');
                            }
                        }
                    });
                });

                //from date
                $('#from_date').datepicker({
                    dateFormat: 'dd/mm/yy',
                    maxDate: 0, // today
                    onSelect: function (selectedDate) {
                        // Set min and max date for To Date
                        $('#to_date').datepicker('option', 'minDate', selectedDate);
                        $('#to_date').datepicker('option', 'maxDate', 0); // today
                        $('#to_date').datepicker('enable');
                    }
                });
                // Search button click
                $('#searchBtn').on('click', function (e) {
                    e.preventDefault();
                    const fromDate = $('#from_date').val();
                    const toDate = $('#to_date').val();

                    if (fromDate && toDate) {
                        $('#report-table').DataTable().ajax.reload();
                    } else {
                        toastr.error("Please select both From Date and To Date before searching.");
                    }
                });
                // Initialize To Date (disabled until From Date is selected)
                $('#to_date').datepicker({
                    dateFormat: 'dd/mm/yy'
                }).datepicker('disable');
            </script>
        @endonce
    @endpush
@endsection












