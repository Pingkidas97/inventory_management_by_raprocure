@php
    use Illuminate\Support\Str;
@endphp
@extends('buyer.layouts.appInventory', ['title'=> 'Manual PO Report List'])
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
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h1 class="font-size-22 mb-0">Dead Stock Report</h1>
                <div class="input-group w-200 mt-4">
                    <span class="input-group-text"><span class="bi bi-shop"></span></span>
                    <div class="form-floating">
                        <select class="form-select globle-field-changes form-select branch_unit" name="branch_id" id="branch_id">
                            @foreach($branches as $branch)
                                <option value="{{ $branch->branch_id }}" {{ session('branch_id') == $branch->branch_id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        <label>Branch/Unit:</label>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-body">
            <!-- Export Progress Section -->
            <div class="col-md-12">
                <div id="export-progress" style="display: none;">
                    <p>Export Progress: <span id="progress-text">0%</span></p>
                    <div id="progress-bar" style="width: 100%; background: #f3f3f3;">
                        <div id="progress" style="height: 20px; width: 0%; background: green;"></div>
                    </div>
                    <br>
                </div>
            </div>
            <!-- Export Progress Section -->
            <!-- Inventry Filter Section -->
            <div class="row g-3 pt-3 mb-3">

                <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                        <div class="form-floating">
                            <input type="text" class="form-control reportinputdiv"  name="search_product_name" id="search_product_name" value="" class="form-control globle-field-changes cart-action-qty-input filterinbox" placeholder="Product Name" />
                            <label for="search_product_name">Product Name</label>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-shop"></span></span>
                        <div class="form-floating">
                            <select class="form-select"  name="category_id" id="search_category_id" >
                                <option value="">Select</option>
                            @foreach ($categories as $id => $categoryName)
                                @php
                                    $isLong = strlen($categoryName) > 14;
                                @endphp
                                <option
                                    value="{{ $categoryName }}"
                                    {{ request('category_id') == $categoryName ? 'selected' : '' }}
                                    title="{{ $isLong ? $categoryName : '' }}"
                                >
                                    {{ $isLong ? Str::limit($categoryName, 14) : $categoryName }}
                                    @if ($isLong)
                                        <i class="bi bi-info-circle" title="{{ $categoryName }}"></i>
                                    @endif
                                </option>
                            @endforeach
                            </select>
                            <label>Category:</label>
                        </div>
                    </div>
                </div>

                <!-- <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-shop"></span></span>
                        <div class="form-floating">
                            <select class="form-select"  name="stock_qty" id="stock_qty" >
                                <option value="">Select</option>
                                <option value="0">Zero</option>
                                <option value="1">Non Zero</option>
                            </select>
                            <label>Stock Quantity:</label>
                        </div>
                    </div>
                </div> -->

                <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                        <div class="form-floating">
                            <input type="text" class="form-control dateTimePickerStart" name="from_date" id="from_date"
                                placeholder="From Date">
                            <label for="from_date">From Date</label>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                        <div class="form-floating">
                            <input type="text" class="form-control dateTimePickerEnd" name="to_date" id="to_date"
                                placeholder="To Date">
                            <label for="to_date">To Date</label>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-auto">
                    <button type="button"
                        class="ra-btn ra-btn-primary w-100 justify-content-center font-size-11" id="searchBtn">
                        <span class="bi bi-search" aria-hidden="true"></span>
                        Search
                    </button>
                </div>

                <div class="col-6 col-sm-auto">
                    <button type="button"
                        class="ra-btn ra-btn-outline-danger w-100 justify-content-center font-size-11" onClick="window.location.href='{{ route('buyer.report.deadStock') }}'">
                        <span class="bi bi-arrow-clockwise" aria-hidden="true"></span>
                        Reset
                    </button>
                </div>

                <div class="col-6 col-sm-auto">
                    <button type="button"
                        class="ra-btn ra-btn-primary w-100 justify-content-center font-size-11 px-2 px-sm-3" onClick="window.location.href='{{ route('buyer.inventory.index') }}'">
                        Back to Inventory
                    </button>
                </div>

                <div class="col-6 col-sm-auto">
                    <button type="button"
                        class="ra-btn ra-btn-primary w-100 justify-content-center font-size-11" id="showreportmodal">
                        All Reports
                    </button>
                </div>

                <div class="col-6 col-sm-auto">
                    <button type="button"
                        class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-11" id="export">
                        <span class="bi bi-download" aria-hidden="true"></span>
                        Export
                    </button>
                </div>

            </div>

            <div class="table-responsive table-inventory">
                <table class="product-listing-table  w-100 dataTables-example1"  id="report-table">
                    <thead>
                        <tr>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Sr. No.</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom w-120">Product Name</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Our Product Name</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Specification</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Size</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Inventory Grouping</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">UOM</th>
                            <!-- <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Current Stock Quantity</th> -->
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <!--main-->
    @include('buyer.report.modal')
    @push('exJs')
        @once
            <!-- <script src="{{ asset('public/js/xlsx.full.min.js') }}"></script> -->
            <script src="{{ asset('public/js/datepicker.js') }}"></script>
            <script src="{{ asset('public/js/inventoryFileExport.js') }}"></script>
            <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script> -->

            <script>
                $(document).ready(function() {
                    $('input').val('');

                    if (typeof $.fn.DataTable !== "function") {
                        toastr.error("DataTables is not loaded! Check script order!");
                        return;
                    }
                    report_list_data();
                    $('#branch_id, #search_product_name, #search_category_id,#stock_qty').on('change keyup', function() {
                    $('#report-table').DataTable().ajax.reload();
                });
                });

                function report_list_data() {
                    $('#report-table').DataTable({
                        processing: true,
                        serverSide: true,
                        searching: false,
                        paging: true,
                        scrollY: 300,
                        pageLength: 25,
                        destroy: true,
                        ajax: {
                            url: "{{ route('buyer.report.deadStock.listdata') }}",
                            data: function (d) {
                                d.from_date = $('#from_date').val();
                                d.to_date = $('#to_date').val();
                                d.branch_id           = $('#branch_id').val();
                                d.search_product_name = $('#search_product_name').val();
                                d.search_category_id  = $('#search_category_id').val();
                                /*d.stock_qty        = $('#stock_qty').val();*/
                            },
                        },
                        columns: [
                                {
                                    data: null,
                                    render: function (data, type, row, meta) {
                                        return meta.row + meta.settings._iDisplayStart + 1;
                                    },
                                    orderable: false,
                                },
                                { data: 'product_name', name: 'product_name' },
                                { data: 'our_product_name', name: 'our_product_name' },
                                { data: 'specification', name: 'specification' },
                                { data: 'size', name: 'size' },
                                { data: 'inventory_grouping', name: 'inventory_grouping' },
                                { data: 'uom', name: 'uom' },
                                /*{ data: 'current_stock_quantity', name: 'current_stock_quantity' },*/
                            ],

                            columnDefs: [
                                { "orderable": false, "targets": "_all" }
                            ],
                            order: [],
                            language: {
                                processing: "<div class='spinner-border spinner-border-sm'></div> Loading..."
                            }
                    });

                }
                // $(document).on('click', '#export', function () {
                //     let btn = $(this);
                //     let url= "{{ route('buyer.report.deadStock.export') }}";
                //     let data= {
                //             _token: $('meta[name="csrf-token"]').attr('content'),
                //             branch_id: $('#branch_id').val(),
                //             search_product_name: $('#search_product_name').val(),
                //             search_category_id: $('#search_category_id').val(),
                //             /*stock_qty: $('#stock_qty').val(),*/
                //             from_date: $('#from_date').val(),
                //             to_date: $('#to_date').val(),
                //         };
                //     let deleteExcelUrl="{{route('buyer.delete.export.file')}}";
                //     inventoryFileExport(btn,url,data,deleteExcelUrl);
                // });

                // Open Report Modal
                $('#showreportmodal').click(function (e) {
                    e.preventDefault();
                    $('#reportModal').modal('show');
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
            <!-- Excel Export Script -->
            <script src="{{ asset('public/assets/xlsx/xlsx.full.min.js') }}"></script>
            <script src="{{ asset('public/assets/xlsx/export.js') }}"></script>
            <script>
                $(document).ready(function() {
                    $('#export').on('click', function() {
                        const exporter = new Exporter({
                            chunkSize: 100,
                            rowLimitPerSheet: 200000,
                            headers: ['Serial No','Branch', 'Product Name', 'Our Product Name', 'Specification','Size','Inventory Grouping','UOM',],
                            totalUrl: "{{ route('buyer.report.deadStock.exportTotal') }}",
                            batchUrl: "{{ route('buyer.report.deadStock.exportBatch') }}",
                            token: "{{ csrf_token() }}",
                            exportName: "Dead_Stock_Report_",
                            expButton: '#export',
                            exportProgress: '#export-progress',
                            progressText: '#progress-text',
                            progress: '#progress',
                            fillterReadOnly: '.fillter-form-control',
                            getParams: function() {
                                $('#search_product_name, #search_category_id, #from_date, #to_date,#branch_id').prop('disabled', true);
                                $('button').prop('disabled', true);
                                return {
                                    branch_id: $('#branch_id').val(),
                                    search_product_name: $('#search_product_name').val(),
                                    search_category_id: $('#search_category_id').val(),
                                    from_date: $('#from_date').val(),
                                    to_date: $('#to_date').val()
                                };
                            },
                        });

                        exporter.start();
                        const watcher = setInterval(function () {
                            if ($('#export-progress').is(':hidden')) {
                                enableFilters();
                                clearInterval(watcher);
                            }
                        }, 100);
                    });

                    $('#export-progress').hide();

                });
                function enableFilters() {
                    $('#export-progress').hide();
                    $('#search_product_name, #search_category_id, #from_date, #to_date, #branch_id')
                        .prop('disabled', false);
                    $('button').prop('disabled', false);
                }

            </script>
            <!-- Excel Export Script -->
        @endonce
    @endpush
@endsection
