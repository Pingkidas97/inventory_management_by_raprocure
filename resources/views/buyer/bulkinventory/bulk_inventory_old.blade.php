@extends('buyer.layouts.appInventory', ['title'=> 'Inventory Dashboard'])
@section('css')
    @push('styles')
        <link rel="stylesheet" href="{{asset('public/css/bulkInventory.css')}}">
        <!-- DataTables Core -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <!-- Scroller Extension -->
        <link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.2.0/css/scroller.dataTables.min.css">
    @endpush
@endsection
@section('content')

    <div class="container-fluid">
        <div class="main" id="main">
            <div class="myaccount-box-right1">
                <div class="mt-15 Inventory">
                    <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 notification ticket_list mb-0">
                        <!--top_part-->
                        <div class="top_part">
                            <div class="col-md-12 mb-3">
                                <div class="row align-items-center text-center">

                                    <!-- Left: Heading -->
                                    <div class="col-md-4 text-md-start text-center mb-2 mb-md-0">
                                        <div class="createbox">
                                            <h2 class="mb-0">Bulk Inventory</h2>
                                        </div>
                                    </div>

                                    <!-- Center: Branch Dropdown -->
                                    <div class="col-md-4">
                                        <ul class="rfq-filter-right non-empty-rfq-page-section mb-0 d-flex justify-content-center">
                                            <li class="w-100">
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-shop"></i></span>
                                                    <div class="form-floating flex-grow-1">
                                                        <select class="globle-field-changes form-select select-custom-170 w-100" id="branch_id" name="branch_id">
                                                            @foreach($branch_data as $branch)
                                                                <option value="{{ $branch->branch_id }}" {{ session('branch_id') == $branch->branch_id ? 'selected' : '' }}>
                                                                {{ $branch->name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                        <label for="branch_id">Branch/Unit:</label>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Right: Back Button -->
                                    <div class="col-md-4 text-md-end text-center mt-2 mt-md-0">
                                        <a class="btn-rfq btn-rfq-primary" href="{{ route('buyer.inventory.index') }}">
                                            <i class="bi bi-arrow-left"></i> Back To Inventory
                                        </a>
                                    </div>

                                </div>

                                <div class="border_hr mt-3"></div>
                            </div>
                            <!--col-md-12 mb-3-->
                        </div>
                        <!--top_part-->
                        <!-- Instruction + Download Button -->
                        <div class="row mt-4">
                            <!-- Instructions Section -->
                            <div class="col-md-7">
                                <div class="p-3 border rounded shadow-sm bg-light">
                                    <h5 class="mb-3 text-primary">Steps to Upload Inventory</h5>
                                    <ul class="mb-4 numbered-list">
                                        <li>Download the CSV file from the <strong>Download Sample</strong> button.</li>
                                        <li><strong>UOM</strong> fields must match predefined options.</li>
                                        <li>
                                            <strong>Inventory Type</strong> fields allowed: Scrapable,Consumable,Returnable
                                        </li>
                                        <li>Add your products into the downloaded CSV file.</li>
                                        <li>Upload your CSV file below and then hit the <strong>Import Product</strong> button.</li>
                                        <li>Ensure product names match the Raprocure master list.</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Download Button Section -->
                            <div class="col-md-5 d-flex align-items-start justify-content-center mt-4 mt-md-0">
                                <a class="btn btn-success" href="{{ asset('web/sample-csv/sample_Inventory_import.csv') }}" download>
                                    <i class="bi bi-box-arrow-in-down"></i> Download Sample
                                </a>
                            </div>
                        </div>
                        <!-- Instruction + Download Button -->
                        <!-- Upload CSV File Form -->
                        <div class="row mt-5">
                            <div class="col-12">
                                <div class="p-3 border rounded shadow-sm bg-white">
                                    <!-- Important: Add an ID to target this form via jQuery -->
                                    <form id="form" method="POST" enctype="multipart/form-data" action="{{ route('buyer.bulk.inventory.uploadCSV') }}">
                                        @csrf

                                        <div class="mb-3">
                                            <label for="import_product" class="form-label">Upload CSV File</label>
                                            <input type="file" name="import_product" id="import_product" accept=".csv" class="form-control" required style="width: 58%;">
                                        </div>

                                        <input type="hidden" id="upload_process_smt" value="1">

                                        <div class="choose" style="display:none;">
                                            <button type="buttton" class="btn-rfq btn-rfq-primary" id="import_product_btn_smt" name="import_product_btn">
                                                <i class="fa fa-folder-open"></i> Import Product
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Upload CSV File Form -->
                        <!--datatable-->
                        <div id="AjaxLoader" style="position: fixed; top: 0; left: 0; height: 100%; width: 100%; background-color: #FFFAFA; opacity: 0.7;justify-content: center; align-items: center;display:none;">
                            <img src="{{ asset('assets/images/loaders/circleTickbox.gif') }}" style="align-self: center;">
                        </div>
                        <div class="col-md-12 invalid-product-section mt-3 d-none">
                            <h3>Uploaded Inventory Details</h3>
                            <h5>Please click on cell to fix any errors.</h5>
                            <form id="upload_data_form" method="post">
                                @csrf
                                <div class="table-container table-responsive" style="width: 100%; overflow-x: auto;">

                                    <div style="margin:20px;">
                                        {{-- start pingki --}}
                                        <template id="product-row-template">
                                            <tr>
                                                <td class="sr-no"></td>
                                                <td class="message-cell"></td>
                                                <td class="action-cell"></td>
                                                <td class="product-name-cell"></td>
                                                <td class="spec-cell"></td>
                                                <td class="size-cell"></td>
                                                <td class="stock-cell"></td>
                                                <td class="uom-cell"></td>
                                                <td class="price-cell"></td>
                                                <td class="brand-cell"></td>
                                                <td class="our-product-name-cell"></td>
                                                <td class="inventory-group-cell"></td>
                                                <td class="inventory-type-cell"></td>
                                                <td class="min-qty-cell"></td>
                                            </tr>
                                        </template>


                                        {{-- end pingki --}}
                                        <table class="table table-striped" id="example" style="width: 90%;">
                                            <thead>
                                                {{-- start pingki --}}
                                                <tr>
                                                    <th>Sr. No</th>
                                                    <th>Message</th>
                                                    <th>Action</th>
                                                    <th>Product Name</th>
                                                    <th>Product Specification</th>
                                                    <th>Product Size</th>
                                                    <th>Opening Stock</th>
                                                    <th>Product UOM</th>
                                                    <th>Stock Price</th>
                                                    <th>Brand</th>
                                                    <th>Our Product Name</th>
                                                    <th>Inventory Grouping</th>
                                                    <th>Inventory Type</th>
                                                    <th>Set Min Qty for Indent</th>
                                                </tr>
                                                {{-- end pingki --}}
                                            </thead>
                                            <tbody class="invalid-product-row">
                                            </tbody>
                                        </table>

                                    </div>

                                    <div id="table-info" style="margin-top:10px;"></div>

                                    <div class="col-sm-12">
                                        <div class="text-center">
                                            <button type="button" data-btn-type="2" class="save-form-btn btn-rfq btn-rfq-primary" id="upload_bulk_product"><i class="bi bi-save"></i> Upload Inventory</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!--datatable-->
                    </div>
                </div>
                <!--mt-15 Inventory-->
            </div>
            <!--myaccount-box-right1-->
        </div>
        <!--main--->
    </div>
<!--container-fluid--->
    @push('exJs')
        <script src="{{ asset('public/js/bulkinventory.js') }}"></script>
        <script>
            var csrf_token_js                   =   '{{ csrf_token() }}';
            var bulkinventoryuploadCSVurl       =   "{{ route('buyer.inventory.bulk.inventory.uploadCSV') }}";
            var bulkInventorydeleteRowurl       =   "{{ route('buyer.inventory.bulk.inventory.deleteRow') }}";
            var bulkinventoryupdateRowurl       =   "{{ route('buyer.inventory.bulk.inventory.updateRow') }}";
            var searchallproducturl             =   "{{ route('buyer.search.allproduct') }}";
            var bulkinventorycheckurl           =   "{{ route('buyer.inventory.bulk.inventory.check') }}";
            var bulkinventoryupdateProductsurl  =   "{{ route('buyer.inventory.bulk.inventory.updateProducts') }}";
            var inventoryindexsurl              =   "{{ route('buyer.inventory.index') }}";

        </script>
        <!-- DataTables Core -->
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

        <!-- Scroller Extension -->
        <script src="https://cdn.datatables.net/scroller/2.2.0/js/dataTables.scroller.min.js"></script>
    @endpush

@endsection
