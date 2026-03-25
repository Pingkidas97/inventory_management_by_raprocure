
@foreach($draft_rfq->rfqProducts as $k => $row)
<div class="card card-vendor-list">
    <form class="card-vendor-list-wrapper gap-30 rfq-product-row product-form-section" action="" 
        id="product-form-id-{{$row['masterProduct']->id}}" enctype="multipart/form-data">

        <input type="hidden" name="master_product_id" class="master-product-id" value="{{$row['masterProduct']->id}}">
        <input type="hidden" class="rfq-product-name" value="{{$row['masterProduct']->product_name}}">

        <!-- Vendor Left column -->
        <div class="card-vendor-list-left-panel">
            <h3 class="font-size-18 mb-3">Vendor List</h3>
            <div class="card-vendor-list-search-panel mb-3">
                <div class="card-vendor-list-search">
                    <label for="searchVendor" class="visually-hidden-focusable">Search & Select more Vendors</label>
                    <input type="text" name="keyword" placeholder="Search &amp; Select more Vendors" class="form-control bg-white search-product-vendor">
                </div>
                <div class="card-vendor-list-search-list mt-2 scrollSection">
                    <div class="filter-list scroll-list">
                        <div>
                            <label class="ra-custom-checkbox">
                                <input type="checkbox" class="select-product-all-vendor" id="select-all-vendor{{$row['masterProduct']->id}}">
                                <span class="font-size-11">Select All</span>
                                <span class="checkmark "></span>
                            </label>
                        </div>
                        @foreach ($row['productVendors'] as $vendor)
                            @php
                                $location_type_class = $vendor->vendor_profile->vendor_country->id==101 ? "domestic-vendor" : "international-vendor";
                                $location_id = $vendor->vendor_profile->vendor_country->id==101 ? $vendor->vendor_profile->vendor_state->id : $vendor->vendor_profile->vendor_country->id;
                            @endphp
                            <div class="vendor-checkbox">
                                <label class="ra-custom-checkbox">
                                    <input type="checkbox" class="vendor-input-checkbox vendor-has-{{ $vendor->vendor_id }}-id vendor-location-has-{{ $location_id }}-id {{$location_type_class}}" data-vendor-name="{{ $vendor->vendor_profile['legal_name'] }}"
                                    {{ in_array($vendor->vendor_id, $rfq_vendors) ? "checked" : "" }} value="{{ $vendor->vendor_id }}" data-product-id="{{$row['masterProduct']->id}}" name="vendor_id[]">
                                    <span class="font-size-11">{{ $vendor->vendor_profile['legal_name'] }}</span>
                                    <span class="checkmark "></span>
                                </label>
                            </div>
                        @endforeach
                    </div>

                </div>

            </div>
        </div>
        <!-- Vendor right column -->
        <div class="card-vendor-list-right-panel">
            <input type="hidden" name="product_order" class="product-order-hidden" value="{{ $row['product_order'] }}">
            <!-- Top breadcrumb -->
            <div class="d-flex justify-content-between mb-30">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-vendor">
                            <li class="breadcrumb-item"><span class="product-order">{{$k+1}}.</span> {{$row['masterProduct']['division']->division_name}}</li>
                            <li class="breadcrumb-item">{{$row['masterProduct']['category']->category_name}}</li>
                            <li class="breadcrumb-item active" aria-current="page">{{$row['masterProduct']->product_name}}</li>
                        </ol>
                    </nav>
                </div>

                <div>
                    <button class="btn btn-sm ra-btn-outline-danger remove-product-btn">
                        <span class="visually-hidden-focusable">Remove Product</span>
                        <span class="bi bi-trash3"></span>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="button" class="ra-btn-green ra-btn-green-sm mb-1 text-nowrap add-variant"><span class="bi bi-plus"></span> Variant</button>
            </div>
            <!-- Product List Table -->
            <div class="table-responsive table-product">
                <table class="table table-product-list">
                    <thead>
                        <tr>
                            <th scope="col" class="text-nowrap">
                                <div class="table-product-list-sl"></div>Sr. No.
                            </th>
                            <th scope="col" class="text-nowrap">
                                <div class="table-product-list-specification">Specification</div>
                            </th>
                            <th scope="col" class="text-nowrap">
                                <div class="table-product-list-size">Size</div>
                            </th>
                            <th scope="col" class="text-nowrap">
                                <div class="table-product-list-qty">
                                    Quantity <span class="text-danger">*</span>
                                </div>
                            </th>
                            <th scope="col" class="text-nowrap">
                                <div class="table-product-list-uom">
                                    UOM <span class="text-danger">*</span>
                                </div>
                            </th>
                            <th scope="col" class="text-nowrap">
                                <div class="table-product-list-attachment">
                                    Attachment
                                    <span title="" class="custom-tooltip text-danger"
                                        data-tooltip="Max file: size 1MB,File type: ( PDF, DOC, Excel, Image, CDR, DWG)">
                                        <i class="bi bi-question-circle"></i>
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="text-center">

                            </th>
                        </tr>
                    </thead>

                    <tbody class="variants-record">
                        @foreach ($row['productVariants'] as $variant_k => $variant)
                            <tr class="table-tr">
                                <td class="text-center row-count-number">{{$variant_k+1}}</td>
                                <td>
                                    <input type="text" title="" class="form-control form-control-sm specification sync-field-changes" autocomplete="off" readonly
                                    maxlength="500" data-bs-toggle="modal" data-bs-target="#submitSpecification" name="specification[]" value="{{ $variant->specification }}">
                                    <input type="hidden" name="variant_order[]" value="{{ $variant->variant_order }}" class="variant-order">
                                    <input type="hidden" name="edit_id[]" value="{{ $variant->id }}" class="variant-edit-id">
                                    <input type="hidden" name="variant_grp_id[]" value="{{ $variant->variant_grp_id }}" class="variant-grp-id">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm size sync-field-changes" oninput="" maxlength="255"  name="size[]" value="{{ $variant->size }}">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm quantity sync-field-changes" maxlength="10" name="quantity[]" value="{{ $variant->quantity }}">
                                </td>
                                <td>
                                    <select class="form-select form-select-sm uom sync-field-changes" name="uom[]">
                                        <option value="">Select</option>
                                        @foreach($uoms as $id => $uom_name)
                                            <option value="{{$id}}" {{ $variant->uom==$id ? "selected" : "" }} >{{$uom_name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    @php
                                        $attachment = '';
                                        if (is_file(public_path('uploads/rfq-attachment/'.$variant->attachment))) {
                                            $attachment = $variant->attachment;
                                        }
                                    @endphp
                                    <div class="file-upload-block">                                    
                                        <div class="file-upload-wrapper" style="display: {{ !empty($attachment) ? 'none' : 'block' }};">
                                            <input type="file" class="file-upload sync-field-changes" name="attachment[]" style="display: none;" onchange="validateRFQFile(this)">
                                            <input type="hidden" name="old_attachment[]" value="{{$variant->attachment}}" class="form-control old-attachment">
                                            <input type="hidden" name="delete_attachment[]" value="" class="form-control delete-attachment">
                                            <button type="button" class="custom-file-trigger form-control text-start text-dark font-size-11">Attach file</button>
                                        </div>
                                        <div class="file-info" style="display: {{ !empty($attachment) ? 'block' : 'none' }};">
                                            @if(!empty($attachment))
                                                <div class="d-flex align-item-center gap-1">
                                                    <a class="file-links" href="{{ url('public/uploads/rfq-attachment/'.$variant->attachment) }}" target="_blank" download="{{ $variant->attachment }}">
                                                        <span class="display-file font-size-12">{{$attachment}}</span>
                                                    </a>
                                                    <i class="bi bi-trash3 text-danger font-size-12 ml-3 remove-file11 remove-product-variant-file" style="cursor:pointer;"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-icon p-0 remove-btn" type="button" aria-label="Delete" onclick="removeVariant(this)">
                                        <span class="bi bi-trash3 font-size-16 text-danger" aria-hidden="true"></span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>

            <!-- Search by Brand and Remarks -->
            <div class="row mt-4">
                <div class="col-md-6 mb-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <span class="bi bi-tags"></span>
                        </span>
                        <div class="form-floating">
                            <input type="text" class="form-control sync-field-changes" id="brand" placeholder="Brand" name="brand" value="{{ $row['brand'] }}">
                            <label for="brand">Brand</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <span class="bi bi-pencil"></span>
                        </span>
                        <div class="form-floating">
                            <input type="text" class="form-control sync-field-changes" id="remarks" placeholder="Remarks" name="remarks" value="{{ $row['remarks'] }}">
                            <label for="remarks">Remarks</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endforeach