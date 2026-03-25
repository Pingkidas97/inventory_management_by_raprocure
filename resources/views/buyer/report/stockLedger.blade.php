@php
    use Illuminate\Support\Str;
@endphp
@extends('buyer.layouts.appInventory', ['title'=> 'Stock Ledger Report List'])
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
                <h1 class="font-size-22 mb-0">Stock Ledger Report</h1>
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

                <div class="col-6 col-sm-auto">
                    <button type="button"
                        class="ra-btn ra-btn-outline-danger w-100 justify-content-center font-size-11" onClick="window.location.href='{{ route('buyer.report.stockLedger') }}'">
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
                {{-- <div class="col-6 col-sm-auto">
                    <button type="button"
                        class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-11" id="export">
                        <span class="bi bi-download" aria-hidden="true"></span>
                        Export
                    </button>
                </div> --}}


            </div>

            <!-- Start Datatable -->

            <div class="table-responsive table-inventory">
                <table class="product-listing-table w-100 dataTables-example1"  id="report-table">
                    <thead>
                        <tr>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Serial Number</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Product Name</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Category</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Our Product Name</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Specification</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Size</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Brand</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Invetory Grouping</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">Current Stock</th>
                            <th class="text-center border-bottom-dark text-wrap keep-word align-bottom">UOM</th>
                        </tr>
                    </thead>
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

                    $('#branch_id, #search_product_name, #search_category_id').on('change keyup', function() {
                        $('#report-table').DataTable().ajax.reload();
                    });
                });

                function report_list_data() {
                    $('#report-table').DataTable({
                        processing  : true,
                        serverSide  : true,
                        searching   : false,
                        paging      : true,
                        scrollY   : 300,
                        pageLength  : 25,
                        destroy: true,
                        createdRow: function(row) {
                                // current Qty (col 8)
                                $('td', row).eq(8).css({
                                    "background-color": "#d1f7d6",
                                    "color": "#000",
                                    "font-weight": "bold",
                                    "white-space": "nowrap"
                                });
                        },
                        ajax: {
                            url: "{{ route('buyer.report.stockLedger.listdata') }}",
                            data: function (d) {
                                d.branch_id           = $('#branch_id').val();
                                d.search_product_name = $('#search_product_name').val();
                                d.search_category_id  = $('#search_category_id').val();
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
                                { data:'product', name: 'product' },
                                { data:'category', name:'category' },
                                { data: 'our_product_name', name:'our_product_name'},
                                { data:'specification', name:'specification' },
                                { data:'size', name:'size' },
                                { data:'brand', name:'brand' },
                                { data:'inventory_grouping', name:'inventory_grouping' },
                                { data:'current_stock', name:'current_stock' },
                                { data:'uom', name:'uom' },
                            ],
                            columnDefs: [
                                { "orderable": false, "targets": "_all" }
                            ],
                            order: [],
                            columnDefs: [
                                    { orderable: false, targets: '_all' }
                                ],
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

                $(document).on('click', '#export', function () {
                    let btn = $(this);
                    let url= "{{route('buyer.report.stockLedger.export')}}";
                    let data= {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            branch_id: $('#branch_id').val(),
                            search_product_name: $('#search_product_name').val(),
                            search_category_id: $('#search_category_id').val(),
                        };
                    let deleteExcelUrl="{{route('buyer.delete.export.file')}}";
                    inventoryFileExport(btn,url,data,deleteExcelUrl);
                });

                // Open Report Modal
                $('#showreportmodal').click(function (e) {
                    e.preventDefault();
                    $('#reportModal').modal('show');
                });
            </script>
        @endonce
    @endpush
@endsection
