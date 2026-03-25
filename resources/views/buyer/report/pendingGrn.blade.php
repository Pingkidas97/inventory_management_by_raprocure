@php
    use Illuminate\Support\Str;
@endphp
@extends('buyer.layouts.appInventory', ['title'=> 'Closed Indent Report List'])
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
    .product-listing-table tbody td, .product-listing-table tbody th, .product-listing-table thead th, .product-listing-table{
        font-size: 11px !important;
        white-space: normal;
    }
    table.dataTable thead th, table.dataTable thead td {
        padding: 5px;
    }
    .serial-no{
        position: relative;
        top: -1px;
        left: 5px;
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
                <h1 class="font-size-22 mb-0">Pending GRN Report</h1>
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
                <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                        <div class="form-floating">
                            <input type="text" class="form-control reportinputdiv"  name="search_order_no" id="search_order_no" value="" class="form-control globle-field-changes cart-action-qty-input filterinbox" placeholder="Product Name" />
                            <label for="search_order_no">Search By Order Number</label>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-auto">
                    <button type="button"
                        class="ra-btn ra-btn-outline-danger w-100 justify-content-center font-size-11" onClick="window.location.href='{{ route('buyer.report.pendingGrn') }}'">
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
                <div class="col-6 col-sm-auto">
                    <button type="button" class="ra-btn ra-btn-primary font-size-11 w-100 justify-content-center" onclick="show_add_grn_modal()">
                        <span class="bi bi-plus-square font-size-11 d-none d-sm-inline-flex" aria-hidden="true"></span>
                        Add Grn
                    </button>
                </div>

            </div>

            <!-- Start Datatable -->

            <div class="table-responsive-sm table-inventory">
                <table class="product-listing-table w-100 dataTables-example"  id="report-table">
                    <thead>
                        <tr>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:50px;">S. No.</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:66px;">Order <br> Number</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:60px;">Order <br> Date</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:100px;">Product <br> Name</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:65px;">Our <br> Product <br> Name</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:75px;">Vendor <br> Name</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:100px;">Specification</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:100px;">Size</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:70px;">Inventory <br> Grouping</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:55px;">Added <br> By</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:65px;">Added <br> Date</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:60px;">UOM</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:60px;">Order <br> Quantity</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:65px;">Total Grn  <br>  Quantity</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:70px;">Pending GRN  <br>  Quantity</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <!-- End Datatable -->
        </div>
    </div>
    <!--main-->
    @include('buyer.report.modal')
    @include('buyer.report.grnQtyDetailsModal')
    @include('buyer.report.pendinggrnaddmodal')

    @push('exJs')
        @once
            <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script> -->
            <script>
                const pendingGrnreportlistdataurl        =    "{{ route('buyer.report.pendingGrnReportlistdata') }}";
                const exportreportlistPendingGrnurl      =    "{{ route('buyer.report.exportPendingGrnReport') }}";
                const deleteExcelUrl                     =    "{{route('buyer.delete.export.file')}}";
                const updateGrnValueUrl                  =    "{{ route('buyer.grn.store') }}";
                const exportTotalPendingGrnReporturl     =    "{{ route('buyer.report.exportTotalPendingGrnReport') }}";
                const exportBatchPendingGrnReporturl     =    "{{ route('buyer.report.exportBatchPendingGrnReport') }}";
                const fetchOrderDetailsforPendingGrnurl  =    "{{ route('buyer.report.fetchOrderDetailsforPendingGrn') }}";
            </script>
            <!-- Excel Export Script -->
            <script src="{{ asset('public/assets/xlsx/xlsx.full.min.js') }}"></script>
            <script src="{{ asset('public/assets/xlsx/export.js') }}"></script>
            <!-- Excel Export Script -->
            <script src="{{ asset('public/js/pendingGrn.js') }}"></script>
            <script src="{{ asset('public/js/inventoryFileExport.js') }}"></script>
            <script src="{{ asset('public/js/selectAll.js') }}"></script>
        @endonce
    @endpush
@endsection
