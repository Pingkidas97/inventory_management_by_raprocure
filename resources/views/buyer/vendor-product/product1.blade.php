@extends('buyer.layouts.app', ['title'=> $product->product_name.' ', 'sub_title'=>'Create'])

@section('css')
<style>
    .category-product {
        padding: 6px;
    }
    .product {
        padding: 6px;
        border: 2px solid blue;
        border-radius: 8px;
        background-color:#b9deea;
    }
    a.product-title {
        color: black;
    }
    #vendor-locations input{
        margin-right: 5px;
    }
    .vendor-locations-div {
        max-height: 200px;
        overflow-x: hidden;
        overflow-y: auto;
    }
</style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3>Vendor Product</h3>
        </div>
        <div class="card-body">
            <div class="row filter-section" style="margin-bottom: 20px;">
                <div class="col-md-4">
                    Showing result for: {{ $product->product_name }}
                    <br>
                    Number of Vendors (<span id="vendor-count"></span>) 
                </div>
                <div class="col-md-1">
                    <input type="text" name="vendor_name" id="vendor-name" class="form-control product-filter-input" value="" placeholder="Find by vendor name" style="min-width: 170px;">
                </div>
                <div class="col-md-2">
                    <input type="text" name="brand_name" id="brand-name" class="form-control product-filter-input" value="" placeholder="Find by brand" style="min-width: 170px;margin-left: 30px;">
                </div>
                <div class="col-md-1"></div>
                <div class="col-md-2">
                    <button class="btn-rfq btn-rfq-white mr-10" id="add-selected-vendor-product"> Add all to RFQ</button>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-primary mr-10" data-bs-toggle="modal" data-bs-target="#product-filter"> <i class="bi bi-funnel"></i> Filter</button>
                </div>
                <div class="col-md-1">
                    <select class="form-control" id="vendor-sort">
                        <option value="">Sort by :</option>
                        <option value="1">Name (A - Z)</option>
                        <option value="2">Name (Z - A)</option>
                    </select>
                </div>
            </div>
            <div class="row product-section">
                
            </div>
            <div class="row">
                <p class="text-end">If you can't find your Vendor, <a href="javascript:void(0)" style="text-decoration-line: underline;">Click here</a></p>
            </div>
        </div>
    </div>
    <div class="bd-example-snippet bd-code-snippet"> 
        <div class="bd-example m-0 border-0"> 
            <div class="modal fade" id="product-filter" tabindex="-1" aria-labelledby="product-filter-label" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="product-filter-label">Filter</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h2 class="fs-5">Dealer</h2>
                            <div class="mb-30">
                                <ul class="check-listing" id="vendor-dealer-type">
                                    @foreach($dealer_types as $k=>$v)
                                    <li class="">
                                        <label>
                                            <input type="checkbox" name="dealer_type" class="input-dealer-type" value="<?php echo $k ?>">
                                            <?php echo $v ?>
                                        </label>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            <hr>
                            <h2 class="fs-5">Location</h2>
                            <div class="mb-30 vendor-locations-div">
                                <ul class="check-listing" id="vendor-locations">
                                    {{-- <li class="">
                                        <label>
                                            <input type="checkbox" name="dealer_type" class="input-location" value="16">
                                            UP
                                        </label>
                                    </li>
                                    <li class="">
                                        <label>
                                            <input type="checkbox" name="dealer_type" class="input-location" value="14">
                                            MP
                                        </label>
                                    </li> --}}
                                </ul>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary reset-vendor-filter" data-bs-dismiss="modal">Reset</button>
                            <button type="button" class="btn btn-primary product-filter">Apply</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
@endsection

@section('scripts')
    <script>
        var selected_vendors = new Array();
        $(document).ready(function () {
            loadVendorProduct();
        });
        $(document).on("input", ".product-filter-input", function () {
            if($(this).val().length>=3 || $(this).val().length==0){
                loadVendorProduct();
            }
        });
        $(document).on("blur", ".product-filter", function () {
            if($(this).val().length<3){
                $(this).val('');
            }
        });
        $(document).on("click", ".product-filter", function () {
            loadVendorProduct();
            $("#product-filter").modal("hide");
        });
        $(document).on("change", "#vendor-sort", function () {
            loadVendorProduct();
        });
        function loadVendorProduct(){
            let vendor_name = $("#vendor-name").val();
            let brand_name = $("#brand-name").val();
            let sort_type = $("#vendor-sort").val();

            let vendor_location = new Array();
            $('.input-location:checked').each(function () {
                vendor_location.push($(this).val());
            });

            let int_vendor_location = new Array();
            $('.input-int-location:checked').each(function () {
                int_vendor_location.push($(this).val());
            });
            
            let dealer_type = new Array();
            $('.input-dealer-type:checked').each(function () {
                dealer_type.push($(this).val());
            });
            
            $.ajax({
                async: false,
                type: "POST",
                url: '{{ route('buyer.vendor.get-product') }}',
                dataType: 'json',
                data: {
                    page_name: "vendor",
                    product_id: '{{ $product->id }}',
                    vendor_name: vendor_name,
                    brand_name: brand_name,
                    sort_type: sort_type,
                    vendor_location: vendor_location,
                    int_vendor_location: int_vendor_location,
                    dealer_type: dealer_type,
                    selected_vendors: selected_vendors
                },
                beforeSend: function() {},
                success: function(responce) {
                    if (responce.status == false) {
                        // toastr.error(responce.message);
                        $("#vendor-count").html(0);
                        $(".product-section").html('<h5 class="text-center">'+responce.message+'</h5>');
                    } else {
                        $(".product-section").html(responce.products);
                        $("#vendor-count").html(responce.vendor_count);
                        printVendorLocation(responce, vendor_location, int_vendor_location);
                    }
                },
                error: function() {
                    // toastr.error('Something Went Wrong..');
                },
                complete: function() {}
            });
        }
        function printVendorLocation(responce, vendor_location, int_vendor_location){
            let all_vendor_state = responce.all_states;
            let all_international_vendor_country = responce.all_country;
            
            // console.log(vendor_location, int_vendor_location);
            
            let vendor_location_html = '';
            if (all_vendor_state.length > 0) {
                for (var i = 0; i < all_vendor_state.length; i++) {
                    let state = all_vendor_state[i];
                    let checked = '';
                    if (vendor_location.map(Number).includes(state.id)) {
                        checked = 'checked';
                    }
                    vendor_location_html += '<li class="side-category-list-wrapper vendor-checkbox-div">';
                    vendor_location_html += '<label class="container-checkbox1">';
                    vendor_location_html += '<input type="checkbox" name="vendor_location" class="input-location domestic-vendor" value="' + state.id + '" ' + checked + '>';
                    vendor_location_html += state.name;
                    vendor_location_html += '<span class="checkmark"></span></label>';
                    vendor_location_html += '</li>';
                }
            }
            if (all_international_vendor_country.length > 0) {
                for (var i = 0; i < all_international_vendor_country.length; i++) {
                    let country = all_international_vendor_country[i];
                    let checked = '';
                    if (int_vendor_location.map(Number).includes(country.id)) {
                        checked = 'checked';
                    }
                    vendor_location_html += '<li class="side-category-list-wrapper vendor-checkbox-div">';
                    vendor_location_html += '<label class="container-checkbox1 international-vendor-country">';
                    vendor_location_html += '<input type="checkbox" name="vendor_location" class="input-int-location international-vendor" value="' + country.id + '" ' + checked + '>';
                    vendor_location_html += country.name;
                    vendor_location_html += '<span class="checkmark"></span></label>';
                    vendor_location_html += '</li>';
                }
            }
            $("#vendor-locations").html(vendor_location_html);
        }
        $(document).on("click", ".reset-vendor-filter", function () {
            window.location.reload();
        });

        
        $(document).on("change", ".vendor-product-checkbox", function () {
            if($(".vendor-product-checkbox:checked").length>0){
                $(".add-this-vendor-product").addClass("disabled");
            }else{
                $(".add-this-vendor-product").removeClass("disabled");
            }

            if($(this).prop("checked")){
                selected_vendors.push($(this).val());
            }else{
                selected_vendors = selected_vendors.filter(item => item !== $(this).val());
            }
            updateAddDraftBtn();
        });
        $(document).on("click", ".add-this-vendor-product", function () {
            let vendors_id = new Array();
            vendors_id.push($(this).parents(".vendor-product-card").find(".vendor-product-checkbox").val());
            addToDraft(vendors_id);
        });
        $(document).on("click", "#add-selected-vendor-product", function () {
            let vendors_id = new Array();
            if($(".vendor-product-checkbox:checked").length<=0){
                $(".vendor-product-checkbox").prop("checked", true);
            }
            $(".vendor-product-checkbox:checked").each(function(){
                vendors_id.push($(this).val());
            });
            if(vendors_id.length==0){
                alert("Please Select at least one Vendor.");
                return false;
            }            
            addToDraft(vendors_id);
        });
        
        function addToDraft(vendors_id) {
            $(".add-this-vendor-product").addClass("disabled");

            // console.log("addToDraft", vendors_id, selected_vendors);

            $.ajax({
                async: false,
                type: "POST",
                url: '{{ route('buyer.rfq.add-to-draft') }}',
                dataType: 'json',
                data: {
                    product_id: '{{ $product->id }}',
                    vendors_id: vendors_id,
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function() {},
                success: function(responce) {
                    if (responce.status == false) {
                        toastr.error(responce.message);
                    } else {
                        window.location.href = responce.redirectUrl;
                    }
                },
                error: function() {
                    // toastr.error('Something Went Wrong..');
                },
                complete: function() {}
            });
            
        }
        function updateAddDraftBtn() {
            if(selected_vendors.length>0){
                $("#add-selected-vendor-product").html("Add selected to RFQ");
            }else{
                $("#add-selected-vendor-product").html("Add all to RFQ");
            }
        }
        
    </script>
@endsection