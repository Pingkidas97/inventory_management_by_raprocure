@extends('buyer.layouts.appInventory', ['title' => 'Inventory Dashboard'])
@section('css')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('public/css/bulkInventory.css') }}">
        <!-- DataTables Core -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <!-- Scroller Extension -->
        <link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.2.0/css/scroller.dataTables.min.css">
    @endpush
@endsection
@section('content')
    <div class="card rounded">
        <div class="card-header bg-white">
            <div class="row align-items-center justify-content-between mb-3">
                <div class="col-12 col-sm-auto">
                    <h1 class="font-size-22 mb-0">Bulk Inventory</h1>
                </div>
                <div class="col-12 col-sm-auto">
                    <div class="input-group w-200 w-mobile-100 mt-4">
                        <span class="input-group-text"><span class="bi bi-shop"></span></span>
                        <div class="form-floating">
                            <select class="form-select" id="branch_id" name="branch_id">
                                @foreach ($branch_data as $branch)
                                    <option value="{{ $branch->branch_id }}"
                                        {{ session('branch_id') == $branch->branch_id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="branch_id">Branch/Unit</label>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-auto pt-3">
                    <button type="button" class="ra-btn ra-btn-primary font-size-11"
                        onClick="window.location.href='{{ route('buyer.inventory.index') }}'">
                        <span class="bi bi-box-arrow-in-down font-size-11" aria-hidden="true"></span>
                        Back to Inventory
                    </button>
                </div>
            </div>

        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-7 mt-md-7 mb-7">
                    <h5 class="mb-3 text-primary-blue mb-3">Steps to Upload Inventory</h5>
                    <ul class="mb-4 numbered-list">
                        <li>Download the CSV file from the <strong>Download Sample</strong> button.</li>
                        <li><strong>UOM</strong> fields must match predefined options.</li>
                        <li>
                            <strong>Inventory Type</strong> fields allowed: Scrapable,Consumable,Returnable
                        </li>
                        <li>Add your products into the downloaded CSV file.</li>
                        <li>Upload your CSV file below and then hit the <strong>Import Product</strong>
                            button.</li>
                        <li>Ensure product names match the Raprocure master list.</li>
                    </ul>
                </div>
                <div class="col-md-5 mt-md-5 mb-5">
                    <button type="button" class="ra-btn ra-btn-primary font-size-11"
                        onClick="window.location.href='{{ asset('public/web/sample-csv/sample_Inventory_import.csv') }}'">
                        <span class="bi bi-box-arrow-in-down font-size-11" aria-hidden="true"></span>
                        Download Sample
                    </button>
                </div>
            </div>

            <div class="row">
                <!-- Upload CSV File Form -->
                <div class="col-12 col-sm-7">
                    <!-- Important: Add an ID to target this form via jQuery -->
                    <form id="form" method="POST" enctype="multipart/form-data"
                        action="{{ route('buyer.bulk.inventory.uploadCSV') }}">
                        @csrf

                        {{-- <div class="mb-3">
                                <label for="import_product" class="form-label font-size-18 text-primary-blue">Upload
                                    CSV File</label>
                                <input type="file" name="import_product" id="import_product" accept=".csv"
                                    class="form-control" required>
                            </div> --}}
                        <label for="import_product" class="form-label font-size-18 text-primary-blue">Upload
                            CSV File</label>
                        <div class="custom-file-wrapper">
                            <span class="custom-file-display" id="fileName">Choose file</span>
                            <span class="custom-file-button">Browse</span>
                            <input type="file" id="import_product" name="import_product" accept=".csv"
                                class="custom-file-input" required>
                        </div>

                        <input type="hidden" id="upload_process_smt" value="1">

                        <div class="choose" style="display:none;">
                            <button type="buttton" class="btn-rfq btn-rfq-primary" id="import_product_btn_smt"
                                name="import_product_btn">
                                <i class="fa fa-folder-open"></i> Import Product
                            </button>
                        </div>
                    </form>
                </div>
                <!-- Upload CSV File Form -->
                <div class="col-12">
                    <!--datatable-->
                    <div id="AjaxLoader"
                        style="position: fixed; top: 0; left: 0; height: 100%; width: 100%; background-color: #FFFAFA; opacity: 0.7;justify-content: center; align-items: center;display:none;">
                        <img src="{{ asset('public/assets/inventoryAssets/images/loaders/circleTickbox.gif') }}" style="align-self: center;">
                    </div>
                    <div class="invalid-product-section mt-3 d-none">
                        <div class="mt-4 mb-3">
                            <h3 class="font-size-18 mb-2">Uploaded Inventory Details</h3>
                            <h5 class="font-size-13">Please click on cell to fix any errors.</h5>
                        </div>
                        <form id="upload_data_form" method="post">
                            @csrf
                            <div class="mt-3">

                                <div class="table-responsive">
                                    {{-- start pingki --}}
                                    <template id="product-row-template">
                                        <tr>
                                            <td class="text-wrap keep-word text-end sr-no"></td>
                                            <td class="text-wrap keep-word message-cell"></td>
                                            <td class="text-wrap keep-word text-center action-cell"></td>
                                            <td class="text-wrap keep-word product-name-cell"></td>
                                            <td class="text-wrap keep-word spec-cell"></td>
                                            <td class="text-wrap keep-word size-cell"></td>
                                            <td class="text-wrap keep-word stock-cell"></td>
                                            <td class="text-wrap keep-word uom-cell"></td>
                                            <td class="text-wrap keep-word price-cell"></td>
                                            <td class="text-wrap keep-word brand-cell"></td>
                                            <td class="text-wrap keep-word our-product-name-cell"></td>
                                            <td class="text-wrap keep-word inventory-group-cell"></td>
                                            <td class="text-wrap keep-word cost-center-cell"></td>
                                            <td class="text-wrap keep-word inventory-type-cell"></td>
                                            <td class="text-wrap keep-word min-qty-cell"></td>
                                        </tr>
                                    </template>


                                    {{-- end pingki --}}
                                    <table class="product-listing-table w-100 text-wrap keep-word" id="example" >
                                        <thead>
                                            {{-- start pingki --}}
                                            <tr>
                                                <th class="text-wrap keep-word align-bottom">Sr. No</th>
                                                <th data-orderable="false" class="text-wrap keep-word align-bottom">Message</th>
                                                <th data-orderable="false" class="text-wrap keep-word align-bottom text-center action-heading">Action</th>
                                                <th data-orderable="false" class="text-wrap keep-word align-bottom">Product Name</th>
                                                <th data-orderable="false" class="text-wrap keep-word align-bottom">Product Specification</th>
                                                <th data-orderable="false" class="text-wrap keep-word align-bottom">Product Size</th>
                                                <th data-orderable="false" class="text-wrap keep-word align-bottom">Opening Stock</th>
                                                <th data-orderable="false" class="text-wrap keep-word align-bottom">Product UOM</th>
                                                <th data-orderable="false" class="text-wrap keep-word align-bottom">Stock Price</th>
                                                <th data-orderable="false" class="text-wrap keep-word align-bottom">Brand</th>
                                                <th data-orderable="false" class="text-wrap keep-word align-bottom">Our Product Name</th>
                                                <th data-orderable="false" class="text-wrap keep-word align-bottom">Inventory Grouping</th>
                                                <th data-orderable="false" class="text-wrap keep-word align-bottom">Cost Center</th>
                                                <th data-orderable="false" class="text-wrap keep-word align-bottom">Inventory Type</th>
                                                <th data-orderable="false" class="text-wrap keep-word align-bottom">Set Min Qty for Indent</th>
                                            </tr>
                                            {{-- end pingki --}}
                                        </thead>
                                        <tbody class="invalid-product-row">
                                        </tbody>
                                    </table>

                                </div>

                                <div id="table-info" style="margin-top:10px;"></div>

                                <div class="col-sm-12">
                                    <div class="d-flex justify-content-center">
                                        <button type="button" data-btn-type="2"
                                            class="ra-btn ra-btn-primary font-size-11" id="upload_bulk_product"><i
                                                class="bi bi-save"></i> Upload Inventory</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!--datatable-->
                </div>

            </div>
        </div>


        <!--container-fluid--->
        @push('exJs')
            <script src="{{ asset('public/js/bulkinventory.js') }}"></script>
            <script>
                var csrf_token_js = '{{ csrf_token() }}';
                var bulkinventoryuploadCSVurl = "{{ route('buyer.bulk.inventory.uploadCSV') }}";
                var bulkInventorydeleteRowurl = "{{ route('buyer.bulk.inventory.deleteRow') }}";
                var bulkinventoryupdateRowurl = "{{ route('buyer.bulk.inventory.updateRow') }}";
                var searchallproducturl = "{{ route('buyer.search.allproduct') }}";
                var bulkinventorycheckurl = "{{ route('buyer.bulk.inventory.check') }}";
                var bulkinventoryupdateProductsurl = "{{ route('buyer.bulk.inventory.updateProducts') }}";
                var inventoryindexsurl = "{{ route('buyer.inventory.index') }}";
            </script>
            <!-- DataTables Core -->
            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

            <!-- Scroller Extension -->
            <script src="https://cdn.datatables.net/scroller/2.2.0/js/dataTables.scroller.min.js"></script>

            
        @endpush
    @endsection
