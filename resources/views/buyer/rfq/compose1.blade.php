@extends('buyer.layouts.app', ['title'=>'Generate RFQ', 'sub_title'=>'Create'])

@section('css')
    
    <link href="{{ asset('public/assets/library/sumoselect-v3.4.9-2/css/sumoselect.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/library/sumoselect-v3.4.9-2/css/sumo-select-style.css') }}" rel="stylesheet">

    <style>
        .vendor-list {
            background-color: #fff2f0;
            border-radius: 8px;
            padding: 1rem;
            /* height: 100%; */
        }
        .variant-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .form-control[readonly] {
            background-color: #e0f7ff;
        }
        .remove-btn {
            color: red;
            cursor: pointer;
        }
        .remove-product-variant-file {
            cursor: pointer;
        }
        .breadcrumb-item+.breadcrumb-item::before {
            content: var(--bs-breadcrumb-divider, ">");
        }
        .breadcrumb-item, .product-order {
            font-size: 20px;
        }
        .vendor-list-div {
            max-height: 155px;
            overflow-y: auto;
        }
        ol.breadcrumb li:last-child {
            color: #015294 !important;
        }
        .row-count-number{
            font-weight: 600;
        }
        .searched-product-card {
            max-height: 500px;
            overflow-y: auto;
        }
        .btn-rfq-sm {
            font-size: 27px;
        }
        /* span.attachment-link a {
            margin-left: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        } */
        .attachment-link {
            display: flex;
            align-items: center;
            max-width: 140px; /* or adjust based on your layout */
        }

        .file-links {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
            max-width: 180px; /* Adjust based on desired character length */
            color: green;
            text-decoration: none;
        }
        .location-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100%;
            width: 250px;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .location-sidebar.active {
            transform: translateX(0);
        }

        .sidebar-header {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            font-weight: bold;
            border-bottom: 1px solid #ccc;
        }
        .spin {
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        @keyframes spin {
            100% {
            transform: rotate(360deg);
            }
        }
  </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3>Generate RFQ</h3>
        </div>
        <div class="card-body">
            <div class="row filter-section" style="margin-bottom: 20px;">
                
                <div class="col-md-1">
                    <button type="button" class="btn btn-primary" id="openLocationFilter"> <i class="bi bi-funnel"></i> Filter</button>
                </div>
                <div class="col-md-1">
                    <button class="btn-rfq btn-rfq-white" id="select-all-vendor"> Select All Vendor</button>
                </div>
                <div class="col-md-2">
                    <input type="text" id="prn-no" class="form-control sync-draft-rfq-changes" value="{{ $draft_rfq->prn_no }}" placeholder="PRN Number" style="min-width: 170px;">
                </div>
                <div class="col-md-2">
                    <select class="form-control sync-draft-rfq-changes" id="buyer-branch">
                        <option value="">Select</option>
                        @foreach ($buyer_branch as $branch)
                            <option value="{{$branch->branch_id}}" {{$branch->branch_id==$draft_rfq->buyer_branch ? "selected" : ""}} >{{$branch->name}}</option>                            
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" id="last-response-date" class="form-control sync-draft-rfq-changes" value="{{ $draft_rfq->last_response_date }}" placeholder="Last Response Date" style="min-width: 170px;">
                </div>
                <div class="col-md-1">
                    <button class="btn-rfq btn-rfq-danger" id="delete-draft-rfq"> Delete RFQ</button>
                </div>
            </div>
        </div>
    </div>
    <div class="rfq-product-section"></div>
    <div class="card">
        <div class="card-body">
            <div class="row filter-section" style="margin-bottom: 20px;">
                <div class="col-md-12 text-center">
                    <button class="btn-rfq btn-rfq-white" id="add-selected-vendor-product" type="button" data-bs-toggle="modal" data-bs-target="#add-product-to-rfq">Add Product</button>
                </div>
            </div>
            <div class="row filter-section" style="margin-bottom: 20px;">
                <div class="col-md-4">
                    <input type="text" id="buyer-price-basis" class="form-control sync-draft-rfq-changes" value="{{ $draft_rfq->buyer_price_basis }}" placeholder="Price Basis" style="min-width: 170px;">
                </div>
                <div class="col-md-4">
                    <input type="text" id="buyer-pay-term" class="form-control sync-draft-rfq-changes" value="{{ $draft_rfq->buyer_pay_term }}" placeholder="Payment Terms" style="min-width: 170px;">
                </div>
                <div class="col-md-4">
                    <input type="text" id="buyer-delivery-period" class="form-control sync-draft-rfq-changes" value="{{ $draft_rfq->buyer_delivery_period }}" placeholder="Delivery Period (In Days)" style="min-width: 170px;">
                </div>
            </div>
            <div class="row filter-section" style="margin-bottom: 20px;">
                <div class="col-md-12 text-center">
                    <button class="btn-rfq btn-rfq-white" id="schedule-rfq"> Schedule RFQ</button>
                    <button class="btn-rfq btn-rfq-white" id="compose-draft-rfq"> Generate RFQ</button>
                    <a class="btn-rfq btn-rfq-white" href=""> Back</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="bd-example-snippet bd-code-snippet"> 
        <div class="bd-example m-0 border-0"> 
            <div class="modal fade" id="add-product-to-rfq" tabindex="-1" aria-labelledby="add-product-to-rfq-label" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="add-product-to-rfq-label">
                                <i class="bi bi-arrow-left back-to-vendor-list cursor-pointer mr-2"></i>
                                Add Product
                            </h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="clearAddProductModal()"></button>
                        </div>
                        <div class="modal-body">
                            
                            <div class="row">
                                <div class="col-md-8 dropdown ">
                                    <input type="text" id="rfq-search-product-name" class="form-control" value="" placeholder="Search Product" data-bs-toggle="dropdown" aria-expanded="false">
                                    {{-- <ul class="dropdown-menu w-100" id="searchResults"  style="display: none; max-height: 300px; overflow-y: auto;">
                                        <!-- Example results -->
                                        <li><a class="dropdown-item" href="#">FEEDER NUT > MECHANICAL > DRI</a></li>
                                        <li><a class="dropdown-item" href="#">BEARING PIN WITH NUT > BEARING > GENERAL</a></li>
                                        <li><a class="dropdown-item" href="#">FERRULE NUT > FASTENER > GENERAL</a></li>
                                        <li><a class="dropdown-item" href="#">HEX FLARE NUT > FASTENER > GENERAL</a></li>
                                        <li><a class="dropdown-item" href="#">NYLOC NUT > FASTENER > GENERAL</a></li>
                                        <li><a class="dropdown-item" href="#">DUMMY LOCK NUT > FASTENER > GENERAL</a></li>
                                    </ul> --}}
                                </div>
                                <div class="col-md-2">
                                    <input type="text" id="rfq-searched-product-id" class="form-control" value="" placeholder="Product id">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn-rfq btn-rfq-white" id="search-product-for-rfq"> Search</button>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="form-group col-md-3">
                                    <select class="form-select location-sumo-select" id="location-type" multiple>
                                        <option value="4" class="domestic-vendor">Assam</option>
                                        <option value="7" class="domestic-vendor">Chhattisgarh</option>
                                        <option value="10" class="domestic-vendor">Delhi</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <select class="form-select" id="dealer-type">
                                        <option value=""> Select Dealer Type </option>
                                        @foreach ($dealer_types as $id => $dealer_type)
                                            <option value="{{$id}}">{{$dealer_type}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>
                                        <input type="checkbox" class="select-all-searched-product-vendor" value=""> Select All Vendor
                                    </label>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn-rfq btn-rfq-white" id="add-selected-vendor-product-to-draft"> Add TO RFQ</button>
                                </div>
                            </div>

                            <hr/>

                            <div class="row p-3 searched-product-card">
                                
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="bd-example-snippet bd-code-snippet"> 
        <div class="bd-example m-0 border-0"> 
            <div class="modal fade" id="add-product-to-rfq-loader" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="add-product-to-rfq-loader-label" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-12 text-center">
                                    <img src="{{ asset('public/assets/images/raprocure-fevicon.png') }}" style="width: 7%;" class="img img-fluid">
                                    <h4 class="rfq-generate-status">Updating RFQ Products, Please Wait...</h4>
                                    <i class="bi bi-arrow-repeat spin" style="color: #00406b;font-size: 35px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="bd-example-snippet bd-code-snippet"> 
        <div class="bd-example m-0 border-0"> 
            <div class="modal fade" id="compose-rfq-loader" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="compose-rfq-loader-label" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-12 text-center">
                                    <img src="{{ asset('public/assets/images/raprocure-fevicon.png') }}" style="width: 7%;" class="img img-fluid">
                                    <h4 class="rfq-generate-status">Processing RFQ, Please Wait...</h4>
                                    <i class="bi bi-arrow-repeat spin" style="color: #00406b;font-size: 35px;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Location Sidebar -->
    <div id="locationSidebar" class="location-sidebar">
        <div class="sidebar-header">
            <span style="font-size: 20px">Location</span>
            <button id="closeLocationSidebar">✖</button>
        </div>
        <div class="sidebar-body">
            <div id="location-list-div">
                {{-- <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input location-input-checkbox" type="checkbox" value="">
                        Assam
                    </label>
                </div> --}}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    
    <script src="{{ asset('public/assets/library/sumoselect-v3.4.9-2/js/jquery.sumoselect.min.js') }}"></script>

    <script src="{{ asset('public/assets/buyer/js/rfq-scripts.js') }}"></script>

    <script>
        $(document).ready(function () {
            loadDraftProduct();
            $('.location-sumo-select').SumoSelect({selectAll: true, csvDispCount: 2, placeholder: 'Select Location' });
            setTimeout(markSelectedSelectAllCheckbox, 500);

            $('#rfq-search-product-name').on('keyup', function () {
                let value = $(this).val().toLowerCase();
                // $("#searchResults li").filter(function () {
                //     $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                // });
                if (value.trim() === "") {
                    $('#searchResults').hide();
                } else {
                    // $('#searchResults').show();
                    // $('#searchResults li').each(function () {
                    //     var text = $(this).text().toLowerCase();
                    //     $(this).toggle(text.includes(value));
                    // });

                    // // Optional: hide dropdown if no matching items
                    // if ($('#searchResults li:visible').length === 0) {
                    //     $('#searchResults').hide();
                    // }
                }
            });
        });
        
        function loadDraftProduct(){                        
            $.ajax({
                async: false,
                type: "POST",
                url: '{{ route('buyer.rfq.get-draft-product') }}',
                dataType: 'json',
                data: {
                    draft_id: '{{ $draft_rfq->rfq_id }}',
                    // _token: "{{ csrf_token() }}"
                },
                beforeSend: function() {},
                success: function(responce) {
                    if (responce.status == false) {
                        toastr.error(responce.message);
                    } else {
                        $(".rfq-product-section").html(responce.products);
                        showVendorLocation(responce);
                        setTimeout(updateCheckedUncheckedForAllVendors, 300);
                    }
                },
                error: function() {
                    // toastr.error('Something Went Wrong..');
                },
                complete: function() {}
            });
        }
        
        var row_count = 2;
        $(document).on("click", ".add-variant", function() {
            let html = '';
            let product_name = $(this).parents(".rfq-product-row").find(".rfq-product-name").val();
            let product_id = $(this).parents(".rfq-product-row").find(".master-product-id").val();
            // let reg_num = "this.value=this.value.replace(/[^0-9.]/,'')";
            html = `
                <tr class="table-tr">
                    <td class="text-center row-count-number">${row_count}</td>
                    <td>
                        <input type="text" class="form-control" value="${product_name}" readonly>
                        <input type="hidden" name="variant_order[]" value="" class="form-control variant-order">
                        <input type="hidden" name="edit_id[]" value="" class="variant-edit-id">
                        <input type="hidden" name="variant_grp_id[]" value="${Math.floor(Date.now() / 1000).toString() + Math.floor(Math.random() * (99999 - 10000 + 1) + 10000).toString()}" class="variant-grp-id">
                    </td>
                    <td><input type="text" class="form-control specification sync-field-changes" placeholder="Specification" name="specification[]"></td>
                    <td><input type="text" class="form-control size sync-field-changes" placeholder="Size" name="size[]"></td>
                    <td><input type="number" class="form-control quantity sync-field-changes" placeholder="Quantity *" name="quantity[]"></td>
                    <td>
                        <select class="form-select uom sync-field-changes" name="uom[]">
                            <option value="">Select</option>
                            @foreach($uoms as $id => $uom_name)
                            <option value="{{$id}}">{{$uom_name}}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="file" class="form-control form-control-sm sync-field-changes" name="attachment[]" onchange="validateRFQFile(this)">
                        <input type="hidden" name="old_attachment[]" value="" class="form-control old-attachment">
                        <input type="hidden" name="delete_attachment[]" value="" class="form-control delete-attachment">
                        <span class="attachment-link"></span>
                    </td>
                    <td class="text-center">
                        <span class="remove-btn text-danger" style="cursor:pointer;" onclick="removeVariant(this)">
                            <i class="bi bi-trash"></i>
                        </span>
                    </td>
                </tr>
            `;
            
            $(this).parents(".rfq-product-row").find(".variants-record").append(html);
            row_count++;
            rewiesSerialNumber(this);
            
            $(this).parents(".rfq-product-row").find('input[name="brand"]').trigger('change');
        });

        let vendor_product_search_request;
        function showSelectedProduct(){

            let dealer_type = new Array();
            if($("#dealer-type").val()){
                dealer_type.push($("#dealer-type").val());
            }
            // let vendor_location = $("#location_type").val();
            let domestic_vendors = new Array(), international_vendors = new Array();
            $('#location-type option.domestic-vendor:selected').each(function() {
                domestic_vendors.push($(this).val());
            });

            $('#location-type option.international-vendor:selected').each(function() {
                international_vendors.push($(this).val());
            });
            
            let pid = $("#rfq-searched-product-id").val();
            if(vendor_product_search_request){
                vendor_product_search_request.abort();
            }
            
            vendor_product_search_request = $.ajax({
                async: false,
                type: "POST",
                url: '{{ route('buyer.vendor.get-product') }}',
                dataType: 'json',
                data: {
                    page_name: "draft-rfq",
                    product_id: pid,
                    draft_id: "{{ $draft_rfq->rfq_id }}",
                    vendor_location: domestic_vendors,
                    int_vendor_location: international_vendors,
                    dealer_type: dealer_type
                },
                beforeSend: function() {},
                success: function(responce) {
                    if (responce.status == false) {
                        $(".searched-product-card").html('<h5 class="text-center">'+responce.message+'</h5>');
                    } else {
                        $(".searched-product-card").html(responce.products);
                        printVendorLocation(responce, domestic_vendors, international_vendors);
                    }
                },
                error: function() {
                    // toastr.error('Something Went Wrong..');
                },
                complete: function() {}
            });
        }
        
        function addToDraftRFQ(vendors_id) {
            $(".add-this-vendor-product-to-draft").addClass("disabled");
            let product_id = $("#rfq-searched-product-id").val();
            
            $.ajax({
                async: false,
                type: "POST",
                url: '{{ route('buyer.rfq.add-to-draft-rfq') }}',
                dataType: 'json',
                data: {
                    product_id: product_id,
                    vendors_id: vendors_id,
                    draft_id: "{{ $draft_rfq->rfq_id }}",
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function() {
                    clearAddProductModal();
                    $("#add-product-to-rfq-loader").modal('show');
                },
                success: function(responce) {
                    if (responce.status == false) {
                        toastr.error(responce.message);
                    } else {
                        loadDraftProduct();
                        setTimeout(function(){
                            $("#add-product-to-rfq-loader").modal('hide');
                        }, 500);
                    }
                },
                error: function() {
                    toastr.error('Something Went Wrong...');
                    setTimeout(function(){
                        $("#add-product-to-rfq-loader").modal('hide');
                    }, 500);
                },
                complete: function() {
                    setTimeout(function(){
                        $("#add-product-to-rfq-loader").modal('hide');
                    }, 500);
                }
            });
        }
        function updateRFQProduct(_this) {

            let form_id = $(_this).parents(".product-form-section").attr("id");
            $('#' + form_id + ' .quantity').each(function() {
                $(this).sanitizeNumberField();
            });
            let formData = new FormData(document.getElementById(form_id));
            formData.append('rfq_draft_id', "{{ $draft_rfq->rfq_id }}");

            let selectedVendors = [];
            $('.vendor-input-checkbox:checked').each(function() {
                selectedVendors.push($(this).val());
            });
            selectedVendors = [...new Set(selectedVendors)]; // remove duplicates

            // First, remove any existing values (if needed)
            formData.delete('vendor_id[]');

            // Add each vendor ID as a separate entry
            selectedVendors.forEach(function(vendorId) {
                formData.append('vendor_id[]', vendorId);
            });

            $.ajax({
                contentType: false,
                cache: false,
                processData: false,
                type: "POST",
                url: '{{ route('buyer.rfq.update-product') }}',
                dataType: 'json',
                data: formData,
                beforeSend: function() {},
                success: function(responce) {
                    if (responce.status == false) {
                        toastr.error(responce.message);
                        if(responce.type && responce.type == "DraftNotFound"){
                            setTimeout(function(){
                                window.location.href = "{{ route('buyer.dashboard') }}";
                            }, 3000);
                        }
                    } else {
                        if(responce.is_file_uploaded && responce.is_file_uploaded!=''){
                            let file_html = '';
                            file_html += '<a class="file-links" href="' + responce.file_url +'/'+ responce.is_file_uploaded + '" target="_blank" download="'+ responce.is_file_uploaded + '">' + responce.is_file_uploaded + '</a>';
                            file_html +='<span class="remove-product-variant-file btn-rfq btn-rfq-sm"><i class="bi bi-trash3 text-danger"></i></span>';
                            
                            $(_this).parents(".table-tr").find(".attachment-link").html(file_html);
                            $(_this).parents(".table-tr").find('.old-attachment').val(responce.is_file_uploaded);
                            $(_this).val('');
                        }
                        if(responce.is_file_deleted && responce.is_file_deleted!=''){
                            $(_this).parents(".rfq-product-row").find('.delete-attachment[data-file="' + responce.is_file_deleted + '"]').val('');
                        }
                    }
                },
                error: function() {
                    // toastr.error('Something Went Wrong..');
                },
                complete: function() {}
            });
        }
        function updateDraftRFQ() {

            $('#buyer-delivery-period').sanitizeNumberField();
            
            let formData = new FormData();
            formData.append('rfq_draft_id', "{{ $draft_rfq->rfq_id }}");
            formData.append('prn_no', $("#prn-no").val());
            formData.append('buyer_branch', $("#buyer-branch").val());
            formData.append('last_response_date', $("#last-response-date").val());
            formData.append('buyer_price_basis', $("#buyer-price-basis").val());
            formData.append('buyer_pay_term', $("#buyer-pay-term").val());
            formData.append('buyer_delivery_period', $("#buyer-delivery-period").val());

            $.ajax({
                contentType: false,
                cache: false,
                processData: false,
                type: "POST",
                url: '{{ route('buyer.rfq.update-draft') }}',
                dataType: 'json',
                data: formData,
                beforeSend: function() {},
                success: function(responce) {
                    if (responce.status == false) {
                        toastr.error(responce.message);
                        if(responce.type && responce.type == "DraftNotFound"){
                            setTimeout(function(){
                                window.location.href = "{{ route('buyer.dashboard') }}";
                            }, 3000);
                        }
                    }
                },
                error: function() {
                    // toastr.error('Something Went Wrong..');
                },
                complete: function() {}
            });
            return true;
        }
        
        $(document).on("click", ".remove-product-btn", function() {
            if (confirm("Are you sure want to remove this product from RFQ?")) {
                let master_product_id = $(this).parents(".product-form-section").find(".master-product-id").val();
                deleteRFQProduct(master_product_id);
                // $(this).parents(".product-form-section").remove();
                $(this).parents("div.card").remove();

                if ($(".remove-product-btn").length < 1) {
                    window.location.href = "{{ route('buyer.dashboard') }}";
                }
            }
        });

        function deleteRFQProduct(master_product_id){
            $.ajax({
                type: "POST",
                url: '{{ route('buyer.rfq.delete-product') }}',
                dataType: 'json',
                data: {
                    master_product_id: master_product_id,
                    rfq_draft_id: "{{ $draft_rfq->rfq_id }}"
                },
                beforeSend: function() {},
                success: function(responce) {
                    if (responce.status == false) {
                        toastr.error(responce.message);
                        if(responce.type && responce.type == "DraftNotFound"){
                            setTimeout(function(){
                                window.location.href = "{{ route('buyer.dashboard') }}";
                            }, 3000);
                        }
                    } else {
                        rewiseProductSerialNumber();
                        toastr.success(responce.message);
                    }
                },
                error: function() {
                    // toastr.error('Something Went Wrong..');
                },
                complete: function() {}
            });
        }
        function deleteRFQProductVariant(_this){
            let variant_grp_id = $(_this).parents("tr").find(".variant-grp-id").val();
            let master_product_id = $(_this).parents(".product-form-section").find(".master-product-id").val();
            let brand_field = $(_this).parents(".product-form-section").find('input[name="brand"]');

            $.ajax({
                type: "POST",
                url: '{{ route('buyer.rfq.delete-product-variant') }}',
                dataType: 'json',
                data: {
                    master_product_id: master_product_id,
                    variant_grp_id: variant_grp_id,
                    rfq_draft_id: "{{ $draft_rfq->rfq_id }}"
                },
                beforeSend: function() {},
                success: function(responce) {
                    if (responce.status == false) {
                        toastr.error(responce.message);
                        if(responce.type && responce.type == "DraftNotFound"){
                            setTimeout(function(){
                                window.location.href = "{{ route('buyer.dashboard') }}";
                            }, 3000);
                        }
                    } else {
                        // $(_this).parents("tr").removeClass("table-tr");
                        $(_this).parents("tr").remove();
                        rewiesSerialNumber(brand_field);
                        
                        toastr.success(responce.message);
                    }
                },
                error: function() {
                    // toastr.error('Something Went Wrong..');
                },
                complete: function() {}
            });
        }
        $(document).on("click", "#delete-draft-rfq", function() {
            if (!confirm("Are you sure want to delete this RFQ?")) {
                return false;
            }
            $.ajax({
                type: "POST",
                url: '{{ route('buyer.rfq.delete-draft') }}',
                dataType: 'json',
                data: {
                    rfq_draft_id: "{{ $draft_rfq->rfq_id }}"
                },
                beforeSend: function() {},
                success: function(responce) {
                    if (responce.status == false) {
                        toastr.error(responce.message);
                        if(responce.type && responce.type == "DraftNotFound"){
                            setTimeout(function(){
                                window.location.href = "{{ route('buyer.dashboard') }}";
                            }, 3000);
                        }
                    } else {
                        setTimeout(function(){
                            window.location.href = "{{ route('buyer.dashboard') }}";
                        }, 1500);
                    }
                },
                error: function() {
                    // toastr.error('Something Went Wrong..');
                },
                complete: function() {}
            });
        });
        
        function finalUpdateRFQ(){
            $('.quantity').each(function() {
                $(this).sanitizeNumberField();
            });

            let selectedVendors = [];
            $('.vendor-input-checkbox:checked').each(function() {
                selectedVendors.push($(this).val());
            });
            selectedVendors = [...new Set(selectedVendors)];

            $(".product-form-section").each(function() {
                
                let form_id = $(this).attr("id");
                // console.log("form_id", form_id);
                let formData = new FormData(document.getElementById(form_id));
                formData.append('rfq_draft_id', "{{ $draft_rfq->rfq_id }}");

                // First, remove any existing values (if needed)
                formData.delete('vendor_id[]');

                // Add each vendor ID as a separate entry
                selectedVendors.forEach(function(vendorId) {
                    formData.append('vendor_id[]', vendorId);
                });
                
                $.ajax({
                    type: "POST",
                    url: '{{ route('buyer.rfq.update-product') }}',
                    data: formData,
                    dataType: 'json',
                    contentType: false,
                    cache: false,
                    processData: false,
                    async:false,
                    beforeSend: function() {
                    },
                    success: function(responce) {
                        if (responce.status == false) {
                            toastr.error(responce.message);
                            if(responce.type && responce.type == "DraftNotFound"){
                                setTimeout(function(){
                                    window.location.href = "{{ route('buyer.dashboard') }}";
                                }, 3000);
                            }
                        }
                    }
                });
            });
            return true;
        }

        $(document).on("click", "#compose-draft-rfq", function() {
            $(this).attr("disabled", "disabled");
            $(this).prop("disabled", true);
            if (validateRFQForm() == true) {
                //Generate rfq
                generateRFQ();
            }else{
                $(this).prop("disabled", false);
                $(this).removeAttr("disabled");
            }            
        });
        function generateRFQ() {
            //start loader
            $("#compose-rfq-loader").modal('show').addClass('show');
            
            if ($('#compose-rfq-loader').hasClass('show')) {
                setTimeout(function() {
                    if (finalUpdateRFQ() && updateDraftRFQ()) {
                        // console.log("Hello");
                        
                        $.ajax({
                            type: "POST",
                            url: "{{ route('buyer.rfq.compose') }}",
                            data: {
                                rfq_draft_id: "{{ $draft_rfq->rfq_id }}"
                            },
                            dataType: 'json',
                            async: false,
                            success: function(response) {
                                if (response.status == true) {
                                    // RFQ generated
                                    window.location.href = response.redirect_url;
                                } else {
                                    //hide loader
                                    $("#compose-rfq-loader").modal('hide');
                                    toastr.error(response.message);
                                    // RFQ processed
                                    if(response.type && response.type == "DraftNotFound"){
                                        setTimeout(function(){
                                            window.location.href = "{{ route('buyer.dashboard') }}";
                                        }, 3000);
                                    }
                                    
                                    if(response.type && response.type == "UpdateBranchAndReload"){
                                        $("#buyer-branch").trigger("change");
                                        setTimeout(function() {
                                            window.location.reload();
                                        }, 3000);
                                    }
                                    if(response.type && response.type == "RedirectToURL"){
                                        setTimeout(function() {
                                            window.location.href = response.redirectURL;
                                        }, 3000);
                                    }
                                }
                            }
                        });
                    }
                }, 700);
            } else {
                return false;
            }
        }
    </script>
@endsection