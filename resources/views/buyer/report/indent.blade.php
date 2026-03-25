@php
    use Illuminate\Support\Str;
@endphp
@extends('buyer.layouts.appInventory', ['title'=> 'Indent Report List'])
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
    .serial-no{
        position: relative;
        top: -1px;
        left: 5px;
    }
    </style>
    </style>
@endpush
@push('headJs')
    @once
        <script src="{{ asset('public/assets/inventoryAssets/js/jquery-ui.min.js') }}"></script>
    @endonce
@endpush
@section('content')
    <!---indent Modal-->
    <div id="indentModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-graident text-white">
                    <h2 class="modal-title font-size-13" id="addeditindentModalLabel">Add Indent</h2>
                    <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-floating w-25 mt-2" id="search_bulk_indent_product" style="display:none;">
                        <input type="text" class="form-control specialCharacterAllowed" name="search_product_name" id="search_indent_product_name" placeholder="" value="" />
                        <label for="search_product_name">Search for Product Name / Specification</label>
                    </div>
                    <ul id="product_search_list" class="list-group position-absolute w-25" style="z-index:999; display:none;"></ul>
                    <form id="addIndentForm">
                        @csrf
                        <input type="hidden" id="indent_inventory_id" name="inventory_id">
                        <input type="hidden" id="indent_id" name="indent_id">
                        <div class="table-responsive">
                            <table class="product-listing-table w-100 text-center">
                                <thead>
                                    <tr>
                                        <th scope="col">Product Name</th>
                                        <th scope="col">Product Specification</th>
                                        <th scope="col">Product Size</th>
                                        <th scope="col">Product UOM</th>
                                        <th scope="col">Remarks</th>
                                        <th scope="col">QTY <span class="text-danger">*</span></th>
                                    </tr>
                                </thead>
                                <tbody id ="indent_tbody">
                                    
                                </tbody>
                            </table>
                        </div>
                        <!-- Save Button -->
                        <div class="d-flex justify-content-center mt-3">
                            <button type="submit" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 save_indent_button"><span class="bi bi-save font-size-11" aria-hidden="true"></span> Save Indent</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--indent Modal--->
    <div class="card rounded">

        <div class="card-header bg-white">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h1 class="font-size-22 mb-0">Indent Report</h1>
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
                        <span class="input-group-text"><span class="bi bi-shop"></span></span>
                        <div class="form-floating">
                            <select class="form-select" name="is_active" id="search_is_active" >
                                <option value="">Select</option>
                                <option value="1">Approved</option>
                                <option value="2">Unapproved</option>
                            </select>
                            <label for="search_is_active">Status:</label>
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
                        class="ra-btn ra-btn-outline-danger w-100 justify-content-center font-size-11" onClick="window.location.href='{{ route('buyer.report.indent') }}'">
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
                
                <div class="col-3 col-sm-auto ms-auto text-end d-flex gap-2">
                    <button type="button" class="ra-btn ra-btn-primary font-size-11 justify-content-center" onclick="getMultiIndentData()">
                        <span class="bi bi-plus-square font-size-11 d-none d-sm-inline-flex" aria-hidden="true"></span>
                        Aprrove Indent
                    </button>      
                    <button type="button" class="ra-btn ra-btn-primary font-size-11 justify-content-center" onclick="show_rfq_modal()">
                        <span class="bi bi-plus-square font-size-11 d-none d-sm-inline-flex" aria-hidden="true"></span>
                        Add to RFQ
                    </button>                   
                </div>

            </div>

            <!-- Start Datatable -->

            <div class="table-responsive table-inventory">
                <table class="product-listing-table w-100 dataTables-example1"  id="report-table">
                    <thead>
                        <tr>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Indent Number</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Product</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Our Product Name</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Specification</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Size</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Inventory Grouping</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">User</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Indent Qty</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">RFQ Qty</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">UOM</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Remarks</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Status</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Added Date</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <!-- End Datatable -->
        </div>
    </div>
    <!--main-->
    @include('buyer.report.modal')
    @include('buyer.inventory.addRFQ')
    @push('exJs')
        @once
            <script src="{{ asset('public/js/datepicker.js') }}"></script>
            <script>
                const indentreportlistdataurl        =   "{{ route('buyer.report.indentlistdata') }}";
                const exportreportlistindenturl      =    "{{ route('buyer.report.exportIndentReport') }}";
                const deleteExcelUrl="{{route('buyer.delete.export.file')}}";
                const activeRfqUrl ="{{ route('buyer.report.activeIndentRfq', ['indentId' => '__ID__']) }}";
                const activeRfqDetailsbyIdUrl = "{{ route('buyer.rfq.details', ['rfq_id' => '__RFQ_ID__']) }}";
                const exportTotalIndentreportDataurl =    "{{ route('buyer.report.exportTotalIndentreportData') }}";
                const exportBatchIndentreportDataUrl      =    "{{ route('buyer.report.exportBatchIndentreportData') }}";
                const fetchInventoryDetailsForAddRfqUrl ="{{ route('buyer.inventory.fetchInventoryDetailsForAddRfq') }}";
                // const postmultiindentdataurl = "{{ route('buyer.indent.getMultiIndentData', ['indentId' => '__ID__']) }}";
                const postmultiindentdataurl = "{{ route('buyer.indent.getMultiIndentData') }}";
                let inventoryTable = null;
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
                    $('#to_date').datepicker({
                        dateFormat: 'dd/mm/yy'
                    }).datepicker('disable');

                    $('#branch_id').on('change keyup', function() {
                        selectedIds = [];
                    });

                    //===save indent data==
                    let isSaveIndentSubmitting = false;
                    $("#addIndentForm").off('submit').on('submit', function (e) {
                        e.preventDefault();

                        if (isSaveIndentSubmitting) return;

                        const $form = $(this);
                        const $saveButton = $('.save_indent_button');
                        let hasError = false;

                        const branchId = $('#branch_id').val();


                        if (!branchId) {
                            toastr.error("Branch Name is required");
                            hasError = true;
                        }

                        // const inventoryIds = $form.find('input[name="inventory_id[]"]').map(function () {
                        //     return $(this).val();
                        // }).get();

                        // const indentQtyArr = $form.find('input[name="indent_qty[]"]').map(function () {
                        //     return $(this).val();
                        // }).get();
                        const inventoryIds = [];
                        const indentQtyArr = [];
                        $('#indent_tbody tr').each(function() {
                            inventoryIds.push($(this).find('input[name="inventory_id[]"]').val());
                            indentQtyArr.push($(this).find('input[name="indent_qty[]"]').val());
                        });
                        let minIndentQtyArr = [];

                        const indentQtyField = $form.find('input[name="indent_qty"]');
                        const minIndentQtyField = $form.find('input[name="min_indent_qty"]');

                        if (indentQtyField.length > 0 && minIndentQtyField.length > 0) {
                            const indentQty = parseFloat(parseFloat(indentQtyField.val()).toFixed(3));
                            const minQty = parseFloat(parseFloat(minIndentQtyField.val()).toFixed(3));

                            if (!isNaN(indentQty) && !isNaN(minQty) && indentQty < minQty) {
                                toastr.error(`Indent quantity cannot be less than existing RFQ quantity (${minQty.toFixed(3)})`);
                                hasError = true;
                            }
                        }

                        for (let i = 0; i < inventoryIds.length; i++) {
                            if (!inventoryIds[i]) {
                                toastr.error(`Row ${i + 1}: Valid Inventory is required`);
                                hasError = true;
                            }

                            const qty = parseFloat(parseFloat(indentQtyArr[i]).toFixed(3));

                            if (!indentQtyArr[i]) {
                                toastr.error(`Row ${i + 1}: QTY is required`);
                                hasError = true;
                            } else if (isNaN(qty) || qty < 0.001) {
                                toastr.error(`Row ${i + 1}: QTY must be a number and at least 0.001`);
                                hasError = true;
                            }
                        }

                        $form.find('[maxlength]').each(function () {
                            const max = parseInt($(this).attr('maxlength'));
                            const val = $(this).val() || '';
                            if (val.length > max) {
                                const fieldName = $(this).attr('name') || 'Field';
                                toastr.error(fieldName + ` must not exceed ${max} characters.`);
                                hasError = true;
                                return false;
                            }
                        });

                        if (hasError) {
                            isSaveIndentSubmitting = false;
                            return;
                        }

                        isSaveIndentSubmitting = true;
                        $saveButton.prop('disabled', true);

                        let formData = $form.serialize();
                        formData += '&buyer_branch_id=' + encodeURIComponent(branchId);
                    
                        $.ajax({
                            url: "{{ route('buyer.indent.store') }}",
                            type: "POST",
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            data: formData,
                            success: function (response) {
                                isSaveIndentSubmitting = false;
                                $saveButton.prop('disabled', false);
                                if (response.status) {
                                    $form[0].reset();
                                    $form.find('input[type="hidden"]').val('');
                                    $('#indentModal').modal('hide');
                                    toastr.success(response.message);
                                    if ($.fn.DataTable.isDataTable('#report-table')) {
                                        $('#report-table').DataTable().ajax.reload();
                                    }
                                } else {
                                    toastr.error(response.message || "Failed to add indent!");
                                }
                            },
                            error: function (xhr) {
                                isSaveIndentSubmitting = false;
                                $saveButton.prop('disabled', false);
                                const res = xhr.responseJSON || {};
                                if (xhr.status === 422 && res.errors) {
                                    let first = true;
                                    $.each(res.errors, function (key, messages) {
                                        toastr.error(messages[0]);
                                        if (first) {
                                            $('[name="' + key + '"]').focus();
                                            first = false;
                                        }
                                    });
                                } else {
                                    toastr.error(res.message || "Something went wrong!");
                                }
                            }
                        });
                    });
                    //===Save Indent Data==

                    $(document).on('click', '.delete_indent_button', function () {
                        checkPermissionAndExecute('INDENT', 'delete', '1', function () {
                            const $form = $('.delete_indent_button').closest('form');
                            const indentId = $('#indent_id').val();
                            const inventoryId = $('#indent_inventory_id').val();
                            const inventoryIds = $form.find('input[name="inventory_id[]"]').map(function () {
                                return $(this).val();
                            }).get();
                            const indent_qty = $('#indent_qty').val();
                            const indentQtys  = $form.find('input[name="indent_qty[]"]').map(function () {
                                return $(this).val();
                            }).get();
                            const indentIds  = $form.find('input[name="indent_id[]"]').map(function () {
                                return $(this).val();
                            }).get();
                            if (!indentId || !inventoryId) {
                                toastr.error("Invalid indent or inventory ID.");
                                return;
                            }

                            if (confirm("Are you sure you want to delete this indent?")) {
                                $('.delete_indent_button').attr('disabled', 'disabled');
                                $.ajax({
                                    url: '{{ route("buyer.indent.delete", ":id") }}'.replace(':id', indentId),
                                    type: 'DELETE',
                                    data: {
                                        _method: 'DELETE', // spoof method
                                        _token: $('meta[name="csrf-token"]').attr('content'),
                                        indent_inventory_id: inventoryIds,
                                        indent_qty: indentQtys,
                                        indent_id: indentIds
                                    },
                                    success: function (response) {
                                        if (response.status=='1') {
                                            $('#addIndentForm')[0].reset();
                                            $('#addIndentForm').find('input[type="hidden"]').val('');
                                            $('#indentModal').modal('hide');

                                            toastr.success(response.message || 'Indent deleted successfully.');
                                            $('.delete_indent_button').removeAttr('disabled');
                                            if ($.fn.DataTable.isDataTable('#report-table')) {
                                                $('#report-table').DataTable().ajax.reload();
                                            }
                                        } else {
                                            $('.delete_indent_button').removeAttr('disabled');
                                            toastr.error(response.message || 'Failed to delete indent.');
                                        }
                                    },
                                    error: function () {
                                        $('.delete_indent_button').removeAttr('disabled');
                                        toastr.error('Server error. Please try again.');
                                    }
                                });

                            }
                        });
                    });
                    $(document).on('click', '.approve_indent_button', function () {
                        checkPermissionAndExecute('INDENT_APPROVE', 'add', '1', function () {
                            const $form = $('.delete_indent_button').closest('form');
                            const indentId = $('#indent_id').val();
                            const inventoryId = $('#indent_inventory_id').val();
                            const indent_qty = $('#indent_qty').val();
                            if (!indentId || !inventoryId) {
                                toastr.error("Invalid indent or inventory ID.");
                                return;
                            }
                            // const inventoryIds = $form.find('input[name="inventory_id[]"]').map(function () {
                            //     return $(this).val();
                            // }).get();
                            // const indentQtys  = $form.find('input[name="indent_qty[]"]').map(function () {
                            //     return $(this).val();
                            // }).get();
                            // const indentIds  = $form.find('input[name="indent_id[]"]').map(function () {
                            //     return $(this).val();
                            // }).get(); 
                            const inventoryIds = [];
                            const indentIds = [];   
                            const indentQtys = [];  
                            $('#indent_tbody tr').each(function() {
                                inventoryIds.push($(this).find('input[name="inventory_id[]"]').val());
                                indentIds.push($(this).find('input[name="indent_id[]"]').val());
                                indentQtys.push($(this).find('input[name="indent_qty[]"]').val());
                            });
                            
                            $('.approve_indent_button').attr('disabled', 'disabled');
                            $.ajax({
                                url: '{{ route("buyer.indent.bulkApprove") }}',
                                type: 'POST',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                    indent_inventory_id: inventoryIds,
                                    indent_qty: indentQtys,
                                    indent_id: indentIds
                                },
                                success: function (response) {
                                    if (response.status=='1') {
                                        $('#addIndentForm')[0].reset();
                                        $('#addIndentForm').find('input[type="hidden"]').val('');
                                        $('#indentModal').modal('hide');

                                        toastr.success(response.message || 'Indent approved successfully.');
                                        $('.approve_indent_button').removeAttr('disabled');
                                        if ($.fn.DataTable.isDataTable('#report-table')) {
                                            $('#report-table').DataTable().ajax.reload();
                                        }
                                    } else {
                                        $('.approve_indent_button').removeAttr('disabled');
                                        toastr.error(response.message || 'Failed to approve indent.');
                                    }
                                },
                                error: function () {
                                    $('.delete_indent_button').removeAttr('disabled');
                                    toastr.error('Server error. Please try again.');
                                }
                            });
                        });
                    });

            </script>
            <script src="{{ asset('public/js/indent.js') }}"></script>
            <script src="{{ asset('public/js/inventoryFileExport.js') }}"></script>
            <!-- Excel Export Script -->
            <script src="{{ asset('public/assets/xlsx/xlsx.full.min.js') }}"></script>
            <script src="{{ asset('public/assets/xlsx/export.js') }}"></script>
            <script src="{{ asset('public/js/selectAll.js') }}"></script>
            <script src="{{ asset('public/js/addRfq.js') }}"></script>
            <!-- Excel Export Script -->
        @endonce
    @endpush
@endsection
