@extends('buyer.layouts.app', ['title'=>'Generate RFQ'])

@section('css')
    <link rel="stylesheet" href="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.css') }}" />
    <link href="{{ asset('public/assets/library/sumoselect-v3.4.9-2/css/sumoselect.min.css') }}" rel="stylesheet">
    <link href="{{ asset('public/assets/library/sumoselect-v3.4.9-2/css/sumo-select-style.css') }}" rel="stylesheet">
    <style>
        #rfq-product-search-list {
            top: 60px;
            width: 98%;
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
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar-menu')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1">
        <div class="container-fluid">
            <!---RFQ Filter section-->
            <section class="mt-2 mb-2 mx-0 mx-md-2 pt-2">
                <div class="row align-items-center">
                    <div class="col-md-2 mb-md-4">
                        <h1 class="font-size-14 py-2">Generate RFQ</h1>
                    </div>
                    <div class="col-md-10">
                        <div class="row align-items-center flex-wrap flex-wrap gx-3">
                            <div class="col-auto col-md-auto mb-4">
                                <button type="button"
                                    class="ra-btn btn-outline-primary ra-btn-outline-primary text-uppercase text-nowrap font-size-11" onclick="openOffcanvasFilter()">
                                    <span class="bi bi-funnel font-size-11" aria-hidden="true"></span> Filter
                                </button>
                            </div>
                            <div class="col-6 col-md-auto mb-4">
                                <button type="button" class="ra-btn btn-outline-primary ra-btn-outline-primary text-uppercase text-nowrap font-size-11" id="select-all-vendor">
                                    <span class="bi bi-card-checklist font-size-11" aria-hidden="true"></span>
                                    Select All Vendor
                                </button>
                            </div>
                            <div class="col-6 col-md-auto mb-4">
                                <div class="input-group generate-rfq-input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-file-earmark-text"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control sync-draft-rfq-changes" value="{{ $draft_rfq->prn_no }}" id="prn-no" placeholder="PRN Number">
                                        <label for="prn-no">PRN Number</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-auto mb-4">
                                <div class="input-group generate-rfq-input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-shop"></span>
                                    </span>
                                    <div class="form-floating">
                                        <select class="form-select sync-draft-rfq-changes" id="buyer-branch" aria-label="Select Branch">
                                            <option value="">Select</option>
                                            @foreach ($buyer_branch as $branch)
                                                <option value="{{$branch->branch_id}}" {{$branch->branch_id==$draft_rfq->buyer_branch ? "selected" : ""}} >{{$branch->name}}</option>                            
                                            @endforeach
                                        </select>
                                        <label for="buyer-branch">Branch/Unit: <sup class="text-danger">*</sup></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-8 col-md-auto mb-4">
                                <div class="input-group generate-rfq-input-group" id="datepicker">
                                    <span class="input-group-text">
                                        <span class="bi bi-calendar2-date"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control sync-draft-rfq-changes" value="{{ !empty($draft_rfq->last_response_date) ? date('d/m/Y', strtotime($draft_rfq->last_response_date)) : '' }}" id="last-response-date" placeholder="Last Response Date">
                                        <label for="last-response-date">Last Response Date</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4 col-md-auto mb-4">
                                <div class="generate-rfq-action">
                                    <button class="ra-btn btn-sm ra-btn-outline-danger font-size-11 px-2 px-md-3 w-100" id="delete-draft-rfq">
                                        <span class="bi bi-trash3 d-none d-md-block font-size-10"></span>
                                        Delete RFQ
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!---RFQ Vendor list section-->
            <section class="mx-0 mx-md-2 py-2 rfq-product-section"></section>

            <!-- Add more product -->
            <section class="d-flex align-items-center justify-content-center mb-4">
                <button type="button" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap gap-1" data-bs-toggle="modal" data-bs-target="#addProducts">
                    <span class="bi bi-plus-square font-size-12"></span> Add Product
                </button>
            </section>

            <!-- Fill more details -->
            <section>
                <div class="row justify-content-between fill-more-details">
                    <div class="col-12 col-md-4 mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-geo-alt"></span>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control sync-draft-rfq-changes" id="buyer-price-basis" placeholder="Price Basis" value="{{ $draft_rfq->buyer_price_basis }}">
                                <label for="buyer-price-basis">Price Basis</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-currency-rupee"></span>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control sync-draft-rfq-changes" id="buyer-pay-term" placeholder="Payment Term" value="{{ $draft_rfq->buyer_pay_term }}">
                                <label for="buyer-pay-term">Payment Term</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-2 mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-calendar2-date"></span>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control sync-draft-rfq-changes" id="buyer-delivery-period" placeholder="Delivery Period (In Days)" value="{{ $draft_rfq->buyer_delivery_period }}">
                                <label for="buyer-delivery-period">Delivery Period (In Days)</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-patch-check"></span>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control sync-draft-rfq-changes" id="buyer-gurantee-warranty" placeholder="Gurantee/Warranty" value="{{ $draft_rfq->warranty_gurarantee }}">
                                <label for="buyer-gurantee-warranty">Gurantee/Warranty</label>
                            </div>
                        </div>
                    </div>
                </div>

            </section>

            <!-- Floating product options-->
            <section class="floting-product-options">
                <div class="d-flex flex-wrap flex-md-nowrap align-items-center justify-content-center gap-3">
                    <button type="button" class="ra-btn btn-outline-primary ra-btn-outline-primary-light text-uppercase text-nowrap font-size-10" id="schedule-rfq">
                        <span class="bi bi-calendar-plus font-size-12"></span> Schedule RFQ
                    </button>
                    <button type="button" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10" id="compose-draft-rfq">
                        <span class="bi bi-lightning-charge font-size-12"></span> Generate RFQ
                    </button>
                    <button type="button" class="ra-btn ra-btn-outline-danger text-uppercase text-nowrap font-size-10">
                        <span class="bi bi-arrow-left font-size-14"></span> Back
                    </button>
                </div>

            </section>
        </div>
    </main>

    <!-- Off canvas Filter -->
    <section class="offcanvas-filter" id="filterPanel">
        <button onclick="closeOffcanvasFilter()" class="ra-btn ra-btn-primary border-0 offcanvas-filter-close-btn">
            <span class="visually-hidden-focusable">Close Filter</span>
            <span class="bi bi-x-lg" aria-hidden="true"></span>
        </button>
        <div class="px-3">
            <h3 class="font-size-18">Location </h3>
        </div>
        <div class="filter-list offcanvas-filter-scroll-list py-2" id="location-list-div">
            {{-- <div class="mt-1">
                <label class="ra-custom-checkbox mb-0">
                    <input type="checkbox">
                    <span class="font-size-11">Assam</span>
                    <span class="checkmark "></span>
                </label>
            </div>
            <div class="mt-1">
                <label class="ra-custom-checkbox mb-0">
                    <input type="checkbox">
                    <span class="font-size-11">Delhi</span>
                    <span class="checkmark "></span>
                </label>
            </div>
            <div class="mt-1">
                <label class="ra-custom-checkbox mb-0">
                    <input type="checkbox">
                    <span class="font-size-11">Goa</span>
                    <span class="checkmark "></span>
                </label>
            </div>
            <div class="mt-1">
                <label class="ra-custom-checkbox mb-0">
                    <input type="checkbox">
                    <span class="font-size-11">Haryana</span>
                    <span class="checkmark "></span>
                </label>
            </div>
            <div class="mt-1">
                <label class="ra-custom-checkbox mb-0">
                    <input type="checkbox">
                    <span class="font-size-11">Jharkhand</span>
                    <span class="checkmark "></span>
                </label>
            </div>
            <div class="mt-1">
                <label class="ra-custom-checkbox mb-0">
                    <input type="checkbox">
                    <span class="font-size-11">Karnataka</span>
                    <span class="checkmark "></span>
                </label>
            </div>
            <div class="mt-1">
                <label class="ra-custom-checkbox mb-0">
                    <input type="checkbox">
                    <span class="font-size-11">Kerala</span>
                    <span class="checkmark "></span>
                </label>
            </div> --}}
        </div>
    </section>

    <!-- Modal Add Product -->
    <div class="modal add-products-modal fade" id="addProducts" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addProductsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-graident text-white justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-default btn-sm mr-2 rfq-search-back-button d-none">
                            <span class="visually-hidden-focusable">Back</span>
                            <span class="bi bi-arrow-left font-size-20 text-white" aria-hidden="true"></span>
                        </button>
                        <h4 class="modal-title font-size-16" id="addProductsLabel">Add Product</h4>
                    </div>
                    <button type="button" class="btn btn-default btn-sm close-product-search-popup" data-bs-dismiss="modal"
                        aria-label="Close"><span class="bi bi-x-lg font-size-18 text-white"></span></button>
                </div>
                <div class="modal-body p-0">
                    <div class="sticky-top search-product-modal-spacing bg-white px-4 pt-3 pb-2">
                        <!---Search Product Filter-->
                        <div class="search-product-modal position-relative">
                            <input type="text" id="rfq-product-search" maxlength="70" name="keyword" placeholder="Search Product" class="form-control input-lg form-control-search" autocomplete="off">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default btn-lg radius-0 btn-search">
                                    <span class="visually-hidden-focusable">Search</span>
                                    <span class="bi bi-search font-size-18" aria-hidden="true"></span>
                                </button>
                            </span>
                            <input type="hidden" id="rfq-searched-product-id" value="">
                        </div>
                        <ul class="search_text_box" id="rfq-product-search-list" style="display: none;"> </ul>
                        <!---Search Product Filter-->
                        <div class="search-product-filter d-none">
                            <div class="row align-items-center justify-content-start justify-content-md-end">
                                <div class="col-12 col-md-auto py-2">
                                    <select class="form-select location-sumo-select" id="location-type" multiple style="width: 200px;">
                                        <option value="4" class="domestic-vendor">Assam</option>
                                        <option value="7" class="domestic-vendor">Chhattisgarh</option>
                                        <option value="10" class="domestic-vendor">Delhi</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-auto py-2">
                                    <select class="form-select form-select-dealer-type" id="dealer-type" aria-label="Select Dealer Type">
                                        <option value=""> Select Dealer Type </option>
                                        @foreach ($dealer_types as $id => $dealer_type)
                                            <option value="{{$id}}">{{$dealer_type}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto col-md-auto py-2">
                                    <div class="select-all-vendors">
                                        <div>
                                            <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox" class="select-all-searched-product-vendor">
                                                <span class="font-size-11">Select All Vendor</span>
                                                <span class="checkmark "></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto col-md-auto py-2">
                                    <button class="ra-btn btn-primary ra-btn-primary text-uppercase font-size-11 text-nowrap" id="add-selected-vendor-product-to-draft">
                                        <span class="bi bi-plus"></span> Add to RFQ
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!---Product Listing-->
                    <section class="product-listing px-4 py-2 d-none">
                        <div class="container-fluid">
                            <div class="row searched-product-card"></div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

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
    <!-- Modal Filter -->
    <div class="modal fade" id="submitSpecification" tabindex="-1" aria-labelledby="submitSpecificationLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-graident text-white">
                    <h1 class="modal-title font-size-12" id="submitSpecificationLabel"><span class="bi bi-card-text" aria-hidden="true"></span> Specification</h1>
                    <button type="button" class="btn-close font-size-10 text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="specifications-textarea" class="form-label">Add product specifications to highlight features, materials, and technical details.</label>
                        <textarea class="form-control specifications-textarea" id="specifications-textarea" rows="3" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ra-btn ra-btn-outline-danger text-uppercase text-nowrap font-size-11" id="reset-specification" data-bs-dismiss="modal">Reset</button>
                    <button type="button" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11" id="submit-specification" data-bs-dismiss="modal">Submit</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')

    <!-- jQuery UI -->
    <script src="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.full.min.js') }}"></script>
    
    <script src="{{ asset('public/assets/library/sumoselect-v3.4.9-2/js/jquery.sumoselect.min.js') }}"></script>

    <script src="{{ asset('public/assets/buyer/js/rfq-scripts.js') }}"></script>

    <script>
        $(".category_division, .product-serach-box").addClass("d-none");
        $(document).ready(function () {
            loadDraftProduct();
            $('.location-sumo-select').SumoSelect({selectAll: true, csvDispCount: 2, placeholder: 'Select Location' });
            setTimeout(markSelectedSelectAllCheckbox, 500);

            let currentDate = new Date();
            currentDate.setDate(currentDate.getDate() + 1);

            // $('#rfq_schedule_date').datetimepicker({
            //     lang: 'en',
            //     timepicker: false,
            //     minDate: currentDate,
            //     format: 'd/m/Y',
            //     formatDate: 'd/m/Y',
            // });
            // $('#rfq_schedule_date').disableKeyboard();

            let last_date_to_response = new Date();
            last_date_to_response.setDate(last_date_to_response.getDate() + 1);
            $('#last-response-date').datetimepicker({
                lang: 'en',
                timepicker: false,
                minDate: last_date_to_response,
                format: 'd/m/Y',
                formatDate: 'd/m/Y',
            });
            $('#last-response-date').disableKeyboard();
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
                        matchAllScrollHeights();
                        syncFileInput();
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
            let product_id = $(this).parents(".rfq-product-row").find(".master-product-id").val();
            // let product_name = $(this).parents(".rfq-product-row").find(".rfq-product-name").val();
            // let reg_num = "this.value=this.value.replace(/[^0-9.]/,'')";
            html = `
                <tr class="table-tr">
                    <td class="text-center row-count-number">${row_count}</td>
                    <td>
                        <input type="text" title="" class="form-control form-control-sm specification sync-field-changes" autocomplete="off" readonly
                            maxlength="500" data-bs-toggle="modal" data-bs-target="#submitSpecification" name="specification[]" value="">
                        <input type="hidden" name="variant_order[]" value="" class="form-control variant-order">
                        <input type="hidden" name="edit_id[]" value="" class="variant-edit-id">
                        <input type="hidden" name="variant_grp_id[]" value="${Math.floor(Date.now() / 1000).toString() + Math.floor(Math.random() * (99999 - 10000 + 1) + 10000).toString()}" class="variant-grp-id">
                    </td>
                    <td><input type="text" class="form-control form-control-sm size sync-field-changes" oninput="" maxlength="255"  name="size[]" value=""></td>
                    <td><input type="text" class="form-control form-control-sm quantity sync-field-changes" maxlength="10" name="quantity[]" value=""></td>
                    <td>
                        <select class="form-select form-select-sm uom sync-field-changes" name="uom[]">
                            <option value="">Select</option>
                            @foreach($uoms as $id => $uom_name)
                            <option value="{{$id}}">{{$uom_name}}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <div class="file-upload-block">
                            <div class="file-upload-wrapper" style="display: block;">
                                <input type="file" class="file-upload sync-field-changes" name="attachment[]" style="display: none;" onchange="validateRFQFile(this)">
                                <input type="hidden" name="old_attachment[]" value="" class="form-control old-attachment">
                                <input type="hidden" name="delete_attachment[]" value="" class="form-control delete-attachment">
                                <button type="button" class="custom-file-trigger form-control text-start text-dark font-size-11">Attach file</button>
                            </div>
                            <div class="file-info" style="display: none;"></div>
                        </div>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-icon p-0 remove-btn" aria-label="Delete" onclick="removeVariant(this)" type="button">
                            <span class="bi bi-trash3 font-size-16 text-danger" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>
            `;
            
            $(this).parents(".rfq-product-row").find(".variants-record").append(html);
            row_count++;
            rewiesSerialNumber(this);
            matchAllScrollHeights();
            syncFileInput();
            
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
                    $(".search-product-filter, .product-listing, .rfq-search-back-button").removeClass('d-none');
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
                    $(".rfq-search-back-button").click();
                    $("#addProducts").modal('hide');
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
                            // let file_html = '';
                            let file_html = `
                                <div class="d-flex align-item-center gap-1">
                                    <a class="file-links" href="${responce.file_url}/${responce.is_file_uploaded}" target="_blank" download="${responce.is_file_uploaded}">
                                        <span class="display-file font-size-12">${responce.is_file_uploaded}</span>
                                    </a>
                                    <i class="bi bi-trash3 text-danger font-size-12 ml-3 remove-file11 remove-product-variant-file" style="cursor:pointer;"></i>
                                </div>
                            `;
                            $(_this).parents(".table-tr").find(".file-upload-wrapper").css('display', 'none');
                            $(_this).parents(".table-tr").find(".file-info").css('display', 'block').html(file_html);
                            $(_this).parents(".table-tr").find('.old-attachment').val(responce.is_file_uploaded);
                            // file_html += '<a class="file-links" href="' + responce.file_url +'/'+ responce.is_file_uploaded + '" target="_blank" download="'+ responce.is_file_uploaded + '">' + responce.is_file_uploaded + '</a>';
                            // file_html +='<span class="remove-product-variant-file btn-rfq btn-rfq-sm"><i class="bi bi-trash3 text-danger"></i></span>';
                            
                            // $(_this).parents(".table-tr").find(".attachment-link").html(file_html);
                            // $(_this).parents(".table-tr").find('.old-attachment').val(responce.is_file_uploaded);
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
            formData.append('warranty_gurarantee', $("#buyer-gurantee-warranty").val());

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
                        // toastr.success(responce.message);
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
                        matchAllScrollHeights();
                        // toastr.success(responce.message);
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

        // searching
        let currentPage_rfq = 1;
        let isLoading_rfq = false;
        let hasMoreResults_rfq = true;
        let lastSearch_rfq = "";
        let is_suggesation_rfq = "no";
        let loader_html_rfq = ` <li style="text-align: center;" class="search-loader-image">
                                <p><img src="{{ asset('public/assets/images/loader.gif') }}" style="width: 35px;"></p>
                            </li>`;
        var product_search_request_rfq;

        $('#rfq-product-search').debounceInput(function () {
            const keyword = $(this).val().trim();
            currentPage_rfq = 1;
            hasMoreResults_rfq = true;
            is_suggesation_rfq = "no";
            $('#rfq-product-search-list').empty();

            if (keyword.length >= 3) {
                lastSearch_rfq = keyword;
                loadMoreResults(keyword, currentPage_rfq);
            } else {
                $('#rfq-product-search-list').hide();
                $(".search-product-filter, .product-listing, .rfq-search-back-button").addClass('d-none');
                if(keyword.length > 0){
                    $('#rfq-product-search-list').show().html(`<li style="text-align: center;" class="search-loader-image">
                                <p><font style="color:#6aa510;">Please enter more than 3 characters.</font></p>
                            </li>`);
                }
            }
        }, 300);

        // Infinite scroll inside dropdown
        $('#rfq-product-search-list').on('scroll', function () {
            const $this = $(this);
            if (
                hasMoreResults_rfq &&
                !isLoading_rfq &&
                $this.scrollTop() + $this.innerHeight() >= this.scrollHeight - 10
            ) {
                currentPage_rfq++;
                loadMoreResults(lastSearch_rfq, currentPage_rfq);
            }
        });
        function loadMoreResults(keyword, page) {
            isLoading_rfq = true;
            $('#rfq-product-search-list').show();
            
            if(page == 1){
                $('#rfq-product-search-list').html(loader_html_rfq);
            }else{
                $('#rfq-product-search-list').append(loader_html_rfq);
            }
            if(product_search_request_rfq && page==1){
                product_search_request_rfq.abort();
            }

            product_search_request_rfq = $.ajax({
                url: '{{ route("buyer.search.vendor-product") }}',
                method: 'POST',
                data: {
                    rfq_id: '{{ $draft_rfq->rfq_id }}',
                    product_name: keyword,
                    page: page,
                    source: 'rfq',
                    is_suggesation: is_suggesation_rfq
                },
                dataType: 'json',
                success: function (responce) { 
                    $(".search-loader-image").remove();                   
                    let products = responce.product_html;
                    let is_products = responce.is_products;
                    is_suggesation_rfq = responce.is_suggesation;
                    if (is_products) {
                        $('#rfq-product-search-list').append(products);
                        hasMoreResults_rfq = true;
                    } else {
                        if(responce.is_suggesation == "no" && page === 1){
                            is_suggesation_rfq = "yes";
                            loadMoreResults(keyword, 1);
                        }
                        if (responce.is_suggesation == "yes" && page === 1) {
                            $('#rfq-product-search-list').append('<li><p>No Product found for <b>"'+keyword+'"</b></p></li>');
                        }
                        hasMoreResults_rfq = false;
                    }
                    isLoading_rfq = false;
                },
                error: function () {
                    console.error('Search error.');
                    isLoading_rfq = false;
                    hasMoreResults_rfq = false;
                }
            });
        }
    </script>
@endsection