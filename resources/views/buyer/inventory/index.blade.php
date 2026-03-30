@php
    use Illuminate\Support\Str;
@endphp
@extends('buyer.layouts.appInventory', ['title'=> 'Inventory Dashboard'])
@push('styles')
    <link rel="stylesheet" href="{{ asset('public/css/addInventoryModal.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/suggestions.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/manualPO.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/inventorytable.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/bootstrap-icons/bootstrap-icons.css') }}">
    <style>
    .product-listing-table>tbody>tr td a { font-size: 11px !important;}
    .table-inventory td, #inventory-table_wrapper td { font-size: 11px !important;}
    .table-responsive {
        overflow: auto; /* Ensures scrolling works if content overflows */
    }

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
    .product-listing-table tbody td, .product-listing-table tbody th, .product-listing-table thead th, .product-listing-table tfoot th {
        font-size:11px !important;
        padding: 1px;

    }
    .form-control{
        font-size:10px !important;
        padding: 7px 5px;
    }
    .modal-grn {
        max-width: 1400px;
    }
    .getPassModal {
        max-width: 1400px !important;
    }
    .manualpo-label{
        font-size:12px !important;
    }
    #hiddenColumns span {
        background: #f1f3f5;
        border: 1px solid #ced4da;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        margin-right: 6px;
        cursor: pointer;
        display: inline-block;
    }

    #hiddenColumns span:hover {
        background: #dee2e6;
    }
    td.col-current-stock {
        background-color: #d1f7d6 !important;
        font-weight: bold;
        white-space: nowrap;
        color: #000;
    }

    td.col-indent-qty {
        background-color: #fff3cd !important;
        font-weight: bold;
        white-space: nowrap;
        color: #000;
    }

    td.col-rfq-qty {
        background-color: #cfe2ff !important;
        font-weight: bold;
        white-space: nowrap;
        color: #000;
    }

    td.col-order-qty {
        background-color: #fde2e2 !important;
        font-weight: bold;
        white-space: nowrap;
        color: #000;
    }

    td.col-grn-qty {
        background-color: #e2e3e5 !important;
        font-weight: bold;
        white-space: nowrap;
        color: #000;
    }

    </style>
@endpush

@section('content')
    
    <div class="card rounded">
        <div class="card-header bg-white">
            <div class="row align-items-center gx-3 gy-2 pt-0 pt-sm-4 pb-2">
                <div class="col-12 col-sm-auto">
                    <h1 class="font-size-18 mb-3 mb-sm-0">Inventory</h1>
                </div>

                <div class="col-12 col-sm-auto">
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1"><span class="bi bi-shop"></span></span>
                            <div class="form-floating">
                                <select name="branch_id" id="branch_id" class="form-select" style="min-width: 100px;">
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->branch_id }}" title="{{ $branch->name }}" {{ session('branch_id') == $branch->branch_id ? 'selected' : '' }}>
                                            {{ Str::limit($branch->name, 18, '...') }}
                                        </option>
                                    @endforeach
                                </select>
                                <label>Branch/Unit</label>
                            </div>
                    </div>
                </div>


                <div class="col-6 col-sm-auto">
                    <button type="button" class="ra-btn ra-btn-outline-primary font-size-11 w-100 justify-content-center" onclick="show_issue_modal()">
                        <span class="bi bi-plus-square font-size-11 d-none d-sm-inline-flex" aria-hidden="true"></span>
                        Issue
                    </button>
                </div>

                <div class="col-6 col-sm-auto">
                    <button type="button" class="ra-btn ra-btn-outline-warning text-black font-size-11 w-100 justify-content-center" onclick="show_indent_modal()">
                        <span class="bi bi-plus-square text-black font-size-11 d-none d-sm-inline-flex" aria-hidden="true"></span>
                        Add Indent
                    </button>
                </div>
                <div class="col-6 col-sm-auto">
                    <button type="button" class="ra-btn ra-btn-outline-warning text-black font-size-11 w-100 justify-content-center" onclick="show_bulk_indent_modal()">
                        <span class="bi bi-plus-square text-black font-size-11 d-none d-sm-inline-flex" aria-hidden="true"></span>
                        Bulk Indent
                    </button>
                </div>

                <div class="col-6 col-sm-auto">
                    <button type="button" class="ra-btn ra-btn-primary font-size-11 w-100 justify-content-center" onclick="show_rfq_modal()">
                        <span class="bi bi-plus-square font-size-11 d-none d-sm-inline-flex" aria-hidden="true"></span>
                        Add to RFQ
                    </button>
                </div>

                
                <div class="col-6 col-sm-auto">
                    <button type="button" class="ra-btn ra-btn-primary w-100 justify-content-center" id="showreportmodal">
                        All Reports
                    </button>
                </div>
                <div class="col-6 col-sm-auto">
                    <button type="button" class="ra-btn ra-btn-primary w-100 justify-content-center getPassBtn" id="getPassBtn">
                        GATE Pass Entry
                    </button>
                </div>
                <!-- Other Links -->
                <div class="col-6 col-sm-auto ms-xl-auto">
                    <div class="dropdown-container w-100">
                        <button type="button" id="dropdownToggleOtherLink" class="ra-btn ra-btn-outline-danger font-size-11 w-100 justify-content-center">
                            <span class="bi bi-plus-square font-size-11 d-none d-sm-inline-flex" aria-hidden="true"></span>
                            Other Links
                        </button>

                        <div class="dropdown-menu-custom" id="dropdownMenuOtherLink">
                            <ul>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100" id="addInventoryBtn">
                                        <span class="bi bi-plus-square" aria-hidden="true"></span> Add Inventory
                                    </button>
                                </li>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100" id="editInventoryBtn">
                                        <span class="bi bi-pencil-square" aria-hidden="true"></span> Edit
                                        Inventory
                                    </button>
                                </li>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100" onclick="window.location.href='{{ route('buyer.bulk.inventory.import') }}'">
                                        <span class="bi bi-box-arrow-in-down" aria-hidden="true"></span> Bulk
                                        Import
                                    </button>
                                </li>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100" onClick="show_stock_return_modal()">
                                        <span class="bi bi-plus-square" aria-hidden="true"></span>
                                        Stock Return
                                    </button>
                                </li>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100" onclick="show_issue_return_modal()">
                                        <span class="bi bi-plus-square" aria-hidden="true"></span>
                                        Issued Return
                                    </button>
                                </li>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100"  id="issuedtoBtn">
                                        <span class="bi bi-plus-square" aria-hidden="true"></span> Issue To
                                    </button>
                                </li>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100 deleteInventory">
                                        <span class="bi bi-trash3" aria-hidden="true"></span> Delete
                                    </button>
                                </li>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100 resetIndentRFQ">
                                        <span class="bi bi-arrow-clockwise" aria-hidden="true"></span> Reset
                                        Indent, RFQ
                                    </button>
                                </li>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100 manualPO">
                                        <span class="bi bi-plus-square" aria-hidden="true"></span> Manual PO
                                    </button>
                                </li>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100" onClick="product_life_cycle_modal()">
                                        <span class="bi bi-plus-square" aria-hidden="true"></span> Product Life Cycle
                                    </button>
                                </li>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100 workOrder">
                                        <span class="bi bi-plus-square" aria-hidden="true"></span> Work Order
                                    </button>
                                </li>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100 forceClosure">
                                        <span class="bi bi-plus-square" aria-hidden="true"></span> Force Closure
                                    </button>
                                </li>
                                <li class="dropdown-item-custom">
                                    <button type="button" class="ra-btn ra-btn-white font-size-13 w-100" id="addGRNTolerance" onclick="show_grn_tolerance_modal()">
                                        <span class="bi bi-plus-square" aria-hidden="true"></span> Add GRN Tolerance
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>





            </div>

        </div>

        <div class="card-body" style="/*height: calc(100vh - 167px);*/ overflow-y: hidden; overflow-x: auto;">
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
            <div class="row gx-3 gy-2 pt-3 mb-3">
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                        <div class="form-floating">
                            <input type="text" class="form-control specialCharacterAllowed" name="search_product_name" id="search_product_name" placeholder="" value="" />
                            <label for="search_product_name">Product Name</label>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-shop"></span></span>
                        <div class="form-floating">
                            <select class="form-select" name="category_id" id="search_category_id">
                                <option value="" selected>Select</option>
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
                            <label for="search_category_id">Category:</label>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-0 mb-sm-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-record2"></span></span>
                        <div class="form-floating">
                            <select class="form-select" name="ind_non_ind" id="ind_non_ind">
                                <option value="" selected>Select</option>
                                <option value="2">Indent</option>
                                <option value="3">Non Indent</option>
                                <option value="4">Pending Indent</option>
                                <option value="5">Unapproved Indent</option>
                            </select>
                            <label for="ind_non_ind">Indent Filter:</label>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-0 mb-sm-3">
                    <div class="input-group">
                        <span class="input-group-text"><span class="bi bi-shop"></span></span>
                        <div class="form-floating">
                            <select class="form-select" name="inventory_type_id" id="search_inventory_type_id" >
                                <option value="" selected>Select</option>
                                @foreach($inventoryTypes as $inventoryType)
                                    <option value="{{ $inventoryType->id }}" {{ request('inventory_type_id') == $inventoryType->id ? 'selected' : '' }}>
                                        {{ $inventoryType->name }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="search_inventory_type_id">Inventory Type :</label>
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
                <div class="col-6 col-sm-auto mb-0 mb-sm-3">
                    <button type="button" onclick="window.location.href='{{ route('buyer.inventory.index') }}'" class="ra-btn ra-btn-outline-danger w-100 justify-content-center">
                        <span class="bi bi-arrow-clockwise" aria-hidden="true"></span>
                        Reset
                    </button>
                </div>

                <div class="col-6 col-sm-auto mb-0 mb-sm-3">
                    <button type="button" class="ra-btn ra-btn-outline-primary w-100 justify-content-center" id="export">
                        <span class="bi bi-download" aria-hidden="true"></span>
                        Export
                    </button>
                </div>
            </div>
            <!-- Hidden column name show area -->
            <div id="hiddenColumns" class="mb-2"></div>

            <!-- Right click menu -->
            <ul id="columnContextMenu"
                class="dropdown-menu"
                style="display:none; position:absolute; z-index:9999;">
                <li>
                    <a href="#" class="dropdown-item" id="hideThisColumn">
                        Hide Column
                    </a>
                </li>
            </ul>
            <!-- end hide column -->
            <div>
                <table class="product-listing-table invengtory-all-table" id="inventory-table">
                    <thead>
                        <tr>
                            <th data-col-index="0" class="text-center border-bottom-dark text-wrap keep-word" style="min-width:30px">#</th>
                            <!--<th data-col-index="1" class="text-center border-bottom-dark text-wrap keep-word" style="min-width:100px">Item Code</th>-->
                            <th data-col-index="1" class="text-center border-bottom-dark text-wrap keep-word" style="min-width:150px">Product</th>
                            <th data-col-index="2" class="text-center border-bottom-dark text-wrap keep-word" style="min-width:100px">Category</th>
                            <th data-col-index="3" class="text-center border-bottom-dark text-wrap keep-word" style="min-width:100px">Our Product Name</th>
                            <th data-col-index="4" class="text-center border-bottom-dark text-wrap keep-word" style="min-width:100px">Specification / Size</th>
                            <th data-col-index="5" class="text-center border-bottom-dark text-wrap keep-word" style="min-width:75px">Brand</th>
                            <th data-col-index="6" class="text-center border-bottom-dark text-wrap keep-word" style="min-width:75px">Inventory Grouping</th>
                            <th data-col-index="7" class="text-center border-bottom-dark text-wrap keep-word col-current-stock" style="min-width:50px">Current Stock</th>
                            <th data-col-index="8" class="text-center border-bottom-dark text-wrap keep-word" style="min-width:50px">UOM</th>
                            <th data-col-index="9" class="text-center border-bottom-dark text-wrap keep-word col-indent-qty" style="min-width:50px">Indent Qty</th>
                            <th data-col-index="10" class="text-center border-bottom-dark text-wrap keep-word col-rfq-qty" style="min-width:50px">RFQ Qty</th>
                            <th data-col-index="11" class="text-center border-bottom-dark text-wrap keep-word col-order-qty" style="min-width:50px">Order Qty</th>
                            <th data-col-index="12" class="text-center border-bottom-dark text-wrap keep-word col-grn-qty" style="min-width:50px">GRN Qty</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <!--main-->
    @include('buyer.inventory.modal')
    @include('buyer.inventory.grnaddmodal')
    @include('buyer.indent.modal')
    @include('buyer.issue.modal')
    @include('buyer.issueReturn.modal')
    @include('buyer.stockReturn.modal')
    @include('buyer.report.modal')
    @include('buyer.inventory.issuedto')
    @include('buyer.inventory.addRFQ')
    @include('buyer.inventory.orderDetailsModal')
    @include('buyer.inventory.manualPOModal')
    @include('buyer.inventory.workOrderModal')
    @include('buyer.inventory.forceClosureModal')
    @include('buyer.inventory.addGRNToleranceModal')
    @include('buyer.inventory.getPassModal')
    @include('buyer.inventory.productLifeCycleModal')

    @push('exJs')
        @once

            <script src="{{ asset('public/js/inventory.js') }}"></script>
            <script src="{{ asset('public/js/productLifeCycle.js') }}"></script>
            {{-- <script src="{{ asset('public/js/xlsx.full.min.js') }}"></script> --}}
            <script src="{{ asset('public/js/deleteInventory.js') }}"></script>
            <script src="{{ asset('public/js/resetIndentRFQ.js') }}"></script>
            <script src="{{ asset('public/js/manualPO.js') }}"></script>
            <script src="{{ asset('public/js/selectAll.js') }}"></script>
            <script src="{{ asset('public/js/addgrn.js') }}"></script>
            <script src="{{ asset('public/js/addRfq.js') }}"></script>
            <script src="{{ asset('public/js/addIssue.js') }}"></script>
            <script src="{{ asset('public/js/showOrderDetails.js') }}"></script>
            <script src="{{ asset('public/js/addIssueReturn.js') }}"></script>
            <script src="{{ asset('public/js/addStockReturn.js') }}"></script>
            <script src="{{ asset('public/js/inventoryFileExport.js') }}"></script>
            <script src="{{ asset('public/js/getPass.js') }}"></script>
            <script src="{{ asset('public/js/workOrder.js') }}"></script>
            <script src="{{ asset('public/js/forceClosure.js') }}"></script>
            <script src="{{ asset('public/js/addGRNTolerance.js') }}"></script>
        @endonce

        <script>
            const getInventoryDetailsUrl = "{{ route('buyer.inventory.getDetailsByID') }}";
            const editInventoryDetailsUrl = "{{ route('buyer.inventory.edit', ['id' => '__ID__']) }}";
            const postindentlisturl = "{{ route('buyer.indent.fetchIndentData') }}";
            const postindentdataurl = "{{ route('buyer.indent.getIndentData') }}";
            const manualPOFetchURL = "{{ route('buyer.manualPO.fetchInventory') }}";
            const forceClosureFetchURL = "{{ route('buyer.forceClosure.fetchInventory') }}";
            const searchVendorByVendornameURL = "{{ route('buyer.manualPO.search.vendors') }}";
            const getVendorDetailsByNameURL = "{{ route('buyer.manualPO.get.vendordetails') }}";
            const genarateManualPOURL = "{{ route('buyer.manualPO.store') }}";
            const checkGrnEntry = "{{ route('buyer.grn.checkGrnEntry', ['inventoryId' => '__ID__']) }}";
            const getInventoryDetailsForIssueUrl = "{{ route('buyer.issue.fetchInventoryDetails') }}";
            const getissuedtoUrl = "{{ route('buyer.issued.getissuedto') }}";
            const saveissuedtoUrl = "{{ route('buyer.issued.save') }}";
            const deleteissuedtoUrl = "{{ route('buyer.issued.delete') }}";
            const getInventoryDetailsForIssueReturnUrl ="{{ route('buyer.issue_return.fetchInventoryDetails') }}";
            const getInventoryDetailsForStockReturnUrl ="{{ route('buyer.stock_return.fetchInventoryDetails') }}";
            const deleteInventoryUrl ="{{ route('buyer.inventory.delete') }}";
            const resetInventoryUrl ="{{ route('buyer.inventory.reset') }}";
            const fetchInventoryDetailsForAddRfqUrl ="{{ route('buyer.inventory.fetchInventoryDetailsForAddRfq') }}";
            const activeRfqUrl ="{{ route('buyer.inventory.activeRfq', ['inventoryId' => '__ID__']) }}";
            const activeRfqDetailsbyIdUrl = "{{ route('buyer.rfq.details', ['rfq_id' => '__RFQ_ID__']) }}";
            const orderDetailsbyIdUrl = "{{ route('buyer.rfq.details', ['rfq_id' => '__RFQ_ID__']) }}";
            const orderDetailsUrl ="{{ route('buyer.inventory.orderDetails', ['inventoryId' => '__ID__']) }}";
            const SearchaddIndentProductlist ="{{ route('buyer.indent.searchInventory') }}";
            const productLifeCycleUrl ="{{ route('buyer.productLifeCycle') }}";
            const genarateWorkOrderURL = "{{ route('buyer.workOrder.store') }}";
            const genarateforceClosureURL = "{{ route('buyer.forceClosure.store') }}";
            const workorderusercurrency = "{{ route('buyer.workOrder.userCurrency') }}";
            const gegrntoleranceUrl = "{{ route('buyer.grntolerance.get') }}";
            const savegrntoleranceUrl = "{{ route('buyer.grntolerance.save') }}";
        </script>

        <script>
            let inventoryTable = null;
            let inventoryAjaxRequest = null;
            $(document).ready(function() {
                $('input').val('');
                $('#ind_non_ind').val('');
                $('#search_category_id').val('');
                $('#search_inventory_type_id').val('');
                if (typeof $.fn.DataTable !== "function") {
                    toastr.error("DataTables is not loaded! Check script order!");
                    return;
                }
                inventory_list_data();
                $('#branch_id').on('change keyup', function() {
                    selectedIds = [];
                });
                $('#branch_id, #search_product_name, #search_category_id, #ind_non_ind, #search_inventory_type_id, #search_order_no').on('change keyup', function() {
                    // $('#inventory-table').DataTable().ajax.reload();
                    if (inventoryTable) {
                        inventoryTable.ajax.reload();
                    }
                });
            });
            
            function inventory_list_data() {
                if (!$.fn.DataTable.isDataTable('#inventory-table')) {
                    inventoryTable = $('#inventory-table').DataTable({
                        processing: true,
                        serverSide: true,
                        deferRender: true,
                        searching: false,
                        paging: true,
                        scrollY: '52vh',
                        scrollX: true,
                        pageLength: 25,
                        ajax: function (data, callback, settings) {
                            if (inventoryAjaxRequest) {
                                inventoryAjaxRequest.abort();
                            }

                            inventoryAjaxRequest = $.ajax({
                                url: "{{ route('buyer.inventory.data') }}",
                                method: "GET",
                                data: $.extend({}, data, {
                                    branch_id: $('#branch_id').val(),
                                    search_product_name: $('#search_product_name').val(),
                                    search_category_id: $('#search_category_id').val(),
                                    ind_non_ind: $('#ind_non_ind').val(),
                                    search_inventory_type_id: $('#search_inventory_type_id').val(),
                                    search_order_no: $('#search_order_no').val()
                                }),
                                success: function (response) {
                                    callback(response); // DataTables will use this
                                },
                                complete: function () {
                                    inventoryAjaxRequest = null;
                                }
                            });
                        },
                        columns: [
                            { data: 'select', orderable: false, searchable: false },
                            //{ data: 'item_code', name: 'item_code' },
                            { data: 'product', name: 'product' },
                            { data: 'category', name: 'category' },
                            { data: 'our_product_name', name: 'our_product_name' },
                            { data: 'specification', name: 'specification' },
                            { data: 'brand', name: 'brand' },
                            { data: 'inventory_grouping', name: 'inventory_grouping' },
                            { data: 'current_stock', name: 'current_stock', className: 'col-current-stock' },
                            { data: 'uom', name: 'uom' },
                            { data: 'indent_qty', name: 'indent_qty', className: 'col-indent-qty' },
                            { data: 'rfq_qty', name: 'rfq_qty', className: 'col-rfq-qty' },
                            { data: 'order_qty', name: 'order_qty', className: 'col-order-qty' },
                            { data: 'grn_qty', name: 'grn_qty', className: 'col-grn-qty' }
                        ],
                        order: [],
                        columnDefs: [
                            { orderable: false, targets: '_all' }
                        ],
                        language: {
                            processing: "<div class='spinner-border spinner-border-sm'></div> Loading..."
                        },
                        initComplete: function () {
                            setTimeout(function () {
                                inventoryTable.columns.adjust(); // Use the same instance
                            }, 200);
                        }
                    });
                } else {
                    inventoryTable.ajax.reload();
                }
            }

            $('#inventory-table').on('draw.dt', function() {
                $('#inventory-table tbody tr').each(function() {
                    var minusIcon = $(this).find('span[id^="minus_"]:visible');
                    if (minusIcon.length) {
                        var inventoryId = minusIcon.attr('id').split('_')[1];
                        if (inventoryId) {
                            checkPermissionAndExecute('INDENT', 'view', '1', () => {
                                fetchAndRenderIndentDetails(inventoryId);
                            });
                        }
                    }
                });
            });
            function fetchAndRenderIndentDetails(inventory) {
                $.ajax({
                    url: postindentlisturl, // Laravel route
                    type: "POST",
                    dataType: "json",
                    data: {
                        inventory: inventory,
                        _token: $('meta[name="csrf-token"]').attr("content")
                    },
                    beforeSend: function () {
                        // Optional: add a loader or UI blocker
                    },
                    success: function (response) {
                        var html = "";
                        $(".extra_tr_" + inventory).remove();
                        $("#header_" + inventory).remove();

                        if (response.status == 1) {
                            var responsedata = response.resp;
                            var approvalUserCount = response.numberOfIndentApprovalUser;
                            var isMultiApproval = approvalUserCount > 1;
                            $("#minus_" + inventory).css("display", "inline");
                            $("#plus_" + inventory).css("display", "none");
                            
                            html += '<tr id="header_' + inventory + '" class="append_tr"><td colspan="100%"><table>';
                            html += "<th></th>";
                            html += '<th colspan="2" class="text-center">Added Date</th>';
                            html += '<th class="text-center">Added By</th>';
                            if (isMultiApproval) {
                                html += '<th>Approved By 1</th>';
                                html += '<th>Approved By 2</th>';
                                html += '<th colspan="3" class="text-center">Remarks</th>';
                            } else {
                                html += '<th>Approved By</th>';
                                html += '<th colspan="4" class="text-center">Remarks</th>';
                            }
                            
                            html += '<th class="text-center">Indent Number</th>';
                            html += '<th class="text-center">Indent Quantity</th>';
                            html += '<th colspan="2" class="text-center">Status</th>';
                            html += "</tr>";

                            responsedata.forEach((p_data) => {
                                var formattedDate = new Date(p_data.created_at).toLocaleString("en-US", {
                                    year: "numeric",
                                    month: "long",
                                    day: "numeric",
                                    hour: "numeric",
                                    minute: "numeric",
                                    hour12: true,
                                });

                                var final_qty = p_data.indent_qty;
                                var remarks = p_data.remarks ? p_data.remarks : "";

                                html += '<tr class="extra_tr_' + inventory + ' append_tr">';
                                html += "<td colspan='3'>" + formattedDate + "</td>";
                                html += "<td>" + (p_data.created_by || "") + "</td>";
                                if (isMultiApproval) {
                                    html += "<td>" + (p_data.approved_by_1 || "") + "</td>";
                                    html += "<td>" + (p_data.approved_by_2 || "") + "</td>";
                                    html += remarks.length > 40
                                    ? `<td colspan="3">${remarks.substr(0, 40)}<i class="bi bi-info-circle-fill" title="${remarks}"></i></td>`
                                    : `<td colspan="3">${remarks}</td>`;
                                } else {
                                    html += "<td>" + (p_data.approved_by_1 || "") + "</td>";
                                    html += remarks.length > 40
                                    ? `<td colspan="4">${remarks.substr(0, 40)}<i class="bi bi-info-circle-fill" title="${remarks}"></i></td>`
                                    : `<td colspan="4">${remarks}</td>`;
                                }

                                

                                html += "<td>" + p_data.inventory_unique_id + "</td>";
                                var iseditindent = p_data.openEdit;
                                html += `<td><span style="cursor:pointer;color:blue;" class="indent_qty_quant ${iseditindent=='1' ? 'show_edit_indent_model' : 'unauthorized_edit_indent_model'}" data-indent="${p_data.id}" data-editpermission="${iseditindent}">${final_qty}</span></td>`;

                                if (p_data.is_active == 1) {
                                    html += `<td colspan="2" class="text-center">Approved</td>`;
                                } else {
                                    html += `<td colspan="2" class="text-center">Unapproved</td>`;
                                }

                                html += "</tr>";
                            });
                            html += '</table></td></tr>';   
                            $(".accordion_parent_" + inventory).parent().parent().after(html);
                        } else {
                            $("#minus_" + inventory).hide();
                            $("#plus_" + inventory).show();
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr) {
                        let errorMsg = "Something went wrong while fetching inventory details.";

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            try {
                                const res = JSON.parse(xhr.responseText);
                                if (res.message) {
                                    errorMsg = res.message;
                                }
                            } catch (e) {
                                // Ignore JSON parse errors
                            }
                        }

                        toastr.error(errorMsg);
                    },
                    // error: function () {
                    //     toastr.error("Something Went Wrong..");
                    // },
                });
            }
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
                        headers: ['Branch','Item Code', 'Product', 'Category', 'Our Product Name', 'Specification','Size', 'Brand', 'Inventory Grouping', 'Current Stock', 'UOM','Indent Qty', 'RFQ Qty', 'Order Qty', 'GRN Qty',],
                        totalUrl: "{{ route('buyer.inventory.exportTotal') }}",
                        batchUrl: "{{ route('buyer.inventory.exportBatch') }}",
                        token: "{{ csrf_token() }}",
                        exportName: "Inventory_Report_",
                        expButton: '#export',
                        exportProgress: '#export-progress',
                        progressText: '#progress-text',
                        progress: '#progress',
                        fillterReadOnly: '.fillter-form-control',
                        getParams: function() {
                            $('#search_product_name, #search_category_id,#ind_non_ind, #search_inventory_type_id, #search_order_no,#branch_id').prop('disabled', true);
                            $('button').prop('disabled', true);
                            return {
                                branch_id: $('#branch_id').val(),
                                search_product_name: $('#search_product_name').val(),
                                search_category_id: $('#search_category_id').val(),
                                ind_non_ind: $('#ind_non_ind').val(),
                                search_inventory_type_id: $('#search_inventory_type_id').val(),
                                search_order_no: $('#search_order_no').val()
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
                $('#search_product_name, #search_category_id,#ind_non_ind, #search_inventory_type_id, #search_order_no,#branch_id')
                    .prop('disabled', false);
                $('button').prop('disabled', false);
            }

        </script>
        <!-- Excel Export Script -->
         <!-- hide coloumn -->
        <script>
            let rightClickedColumnIndex = null;

            // Right click on column header
            $('#inventory-table thead').on('contextmenu', 'th', function (e) {
                e.preventDefault();

                rightClickedColumnIndex = $(this).data('col-index');

                $('#columnContextMenu')
                    .css({
                        top: e.pageY + 'px',
                        left: e.pageX + 'px'
                    })
                    .show();
            });

            // Hide column
            $('#hideThisColumn').on('click', function (e) {
                e.preventDefault();

                if (rightClickedColumnIndex === null) return;

                let column = inventoryTable.column(rightClickedColumnIndex);
                let colName = $(column.header()).text().trim();
                //alert(column);
                column.visible(false);

                // Show hidden column name on top
                if ($('#hiddenColumns span[data-index="' + rightClickedColumnIndex + '"]').length === 0) {
                    $('#hiddenColumns').append(
                        `<span data-index="${rightClickedColumnIndex}">
                            ${colName} ✕
                        </span>`
                    );
                }

                $('#columnContextMenu').hide();
            });

            // Click hidden column name → show again
            $('#hiddenColumns').on('click', 'span', function () {
                let index = $(this).data('index');
                inventoryTable.column(index).visible(true);
                $(this).remove();
            });

            // Click anywhere → hide menu
            $(document).on('click', function () {
                $('#columnContextMenu').hide();
            });


            
        </script>
        <!-- hide coloumn -->
    @endpush
@endsection
