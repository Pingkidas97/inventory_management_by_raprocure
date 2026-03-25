@php
    use Illuminate\Support\Str;
@endphp
@extends('buyer.layouts.appInventory', ['title'=> 'Issued Report List'])
@push('styles')
    <link rel="stylesheet" href="{{ asset('public/css/report.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/inventoryAssets/css/jquery-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/bootstrap-icons/bootstrap-icons.css') }}">
    <style>
    table.dataTable tbody th, table.dataTable tbody td {
        padding: 5px 20px !important; 
    }
    /* DataTables scroll body */
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
                <h1 class="font-size-22 mb-0">Issued Report</h1>
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
                        <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                        <div class="form-floating">
                            <select class="form-select"  name="issueto_id" id="search_issueto_id" >
                                <option value="">Select</option>
                                @foreach ($IssueTo as $item)
                                    @php
                                        $isLong = strlen($item->name) > 14;
                                    @endphp
                                    <option
                                        value="{{ $item->id }}"
                                        {{ request('issueto_id') == $item->id ? 'selected' : '' }}
                                        title="{{ $isLong ? $item->name : '' }}"
                                    >
                                        {{ $isLong ? Str::limit($item->name, 14) : $item->name }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="search_issueto_id">Issued To:</label>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-shop"></span></span>
                        <div class="form-floating">
                            <select class="form-select"  name="updated_by" id="search_buyer_id">
                                <option value="">Select</option>
                                @foreach ($addUsers as $user)
                                    @php
                                        $isLong = strlen($user->name) > 22;
                                    @endphp
                                    <option
                                        value="{{ $user->id }}"
                                        {{ request('updated_by') == $user->id ? 'selected' : '' }}
                                        title="{{ $isLong ? $user->name : '' }}"
                                    >
                                        {{ $isLong ? Str::limit($user->name, 22) : $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="search_buyer_id">Added by User:</label>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-shop"></span></span>
                        <div class="form-floating">
                            <select class="form-select" name="search_consume" id="search_consume">
                                <option value="">Select</option>
                                <option value="1" {{ request('search_consume') == 1 ? 'selected' : '' }}>Consume</option>
                            </select>

                            <label for="search_consume">Consume Filter:</label>
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
                        class="ra-btn ra-btn-outline-danger w-100 justify-content-center font-size-11" onClick="window.location.href='{{ route('buyer.report.issued') }}'">
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
                    <button type="button"
                        class="ra-btn ra-btn-outline-primary-light w-100 justify-content-center font-size-11" id="consume">
                        <span class="bi bi-archive-fill" aria-hidden="true"></span>
                        Consume
                    </button>
                </div>


            </div>

            <!-- Start Datatable -->

            <div class="table-responsive table-inventory">
                <table class="product-listing-table w-100 dataTables-example1"  id="report-table">
                    <thead>
                        <tr>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:52px">Select</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:54px">Issued Number</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:135px">Product</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:80px">Our Product Name</th>
                            <!--<th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:80px">Division</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:100px">Category</th>-->
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:100px">Specification</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:100px">Size</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:60px">Inventory Grouping</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:30px">Issued Quantity</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:30px">UOM</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:30px">Amount ({{ session('user_currency.symbol', '₹')}})</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:80px">Added BY</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:60px">Added Date</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:60px">Remarks</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom" style="min-width:60px">Issued To</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <input type="hidden" id='user_currency' value="{{ session('user_currency.symbol', '₹')}}">
            <!-- End Datatable -->
        </div>
    </div>
    <!--main-->
    @include('buyer.report.modal')
    @include('buyer.report.consume_modal')
    @push('exJs')
        @once
            <script>
                const getissuedlistdataUrl              =   "{{ route('buyer.report.issuedlistdata') }}";
                const getIssuedExportUrl              =   "{{ route('buyer.report.issuedExport') }}";
                const deleteExcelUrl="{{route('buyer.delete.export.file')}}";
                const exportTotalIssuedUrl            =   "{{ route('buyer.report.exportTotalIssued') }}";
                const exportBatchIssuedUrl            =   "{{ route('buyer.report.exportBatchIssued') }}";
                const currency = "{{ session('user_currency.symbol', '₹') }}";
            </script>
            <script src="{{ asset('public/js/datepicker.js') }}"></script>
            <script src="{{ asset('public/js/inventoryFileExport.js') }}"></script>
            <!-- Excel Export Script -->
            <script src="{{ asset('public/assets/xlsx/xlsx.full.min.js') }}"></script>
            <script src="{{ asset('public/assets/xlsx/export.js') }}"></script>
            <!-- Excel Export Script -->
            <script src="{{ asset('public/js/issued.js') }}"></script>
        @endonce
    @endpush
@endsection
