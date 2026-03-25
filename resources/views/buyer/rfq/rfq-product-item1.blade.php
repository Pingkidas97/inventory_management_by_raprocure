

@foreach($draft_rfq->rfqProducts as $k => $row)
<div class="card">
    <div class="card-body">
        <form action="" class="product-form-section" id="product-form-id-{{$row['masterProduct']->id}}" enctype="multipart/form-data">
            <div class="row rfq-product-row">
                <input type="hidden" name="master_product_id" class="master-product-id" value="{{$row['masterProduct']->id}}">
                <input type="hidden" class="rfq-product-name" value="{{$row['masterProduct']->product_name}}">

                <!-- Vendor List -->
                <div class="col-md-2">
                    <h4>Vendor List</h4>
                    <div class="vendor-list">
                        <input type="text" class="form-control mb-2 search-product-vendor" placeholder="Search & Select more Vendors">
                        <div class="vendor-list-div">
                            <div class="form-check">                            
                                <label class="form-check-label">Select All <input class="form-check-input select-product-all-vendor" type="checkbox"></label>
                            </div>
                            @foreach ($row['productVendors'] as $vendor)
                                @php
                                    $location_type_class = $vendor->vendor_profile->vendor_country->id==101 ? "domestic-vendor" : "international-vendor";
                                    $location_id = $vendor->vendor_profile->vendor_country->id==101 ? $vendor->vendor_profile->vendor_state->id : $vendor->vendor_profile->vendor_country->id;
                                @endphp
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input class="form-check-input vendor-input-checkbox vendor-has-{{ $vendor->vendor_id }}-id vendor-location-has-{{ $location_id }}-id {{$location_type_class}}" type="checkbox" data-vendor-name="{{ $vendor->vendor_profile['legal_name'] }}"
                                        {{ in_array($vendor->vendor_id, $rfq_vendors) ? "checked" : "" }} value="{{ $vendor->vendor_id }}" data-product-id="{{$row['masterProduct']->id}}" name="vendor_id[]">
                                        {{ $vendor->vendor_profile['legal_name'] }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Product Form -->
                <div class="col-md-10">
                    <div class="mb-3">
                        <nav aria-label="breadcrumb" class="mb-4 d-flex">
                            <input type="hidden" name="product_order" class="product-order-hidden" value="{{ $row['product_order'] }}">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><span class="product-order">{{$k+1}}.</span> {{$row['masterProduct']['division']->division_name}}</li>
                                <li class="breadcrumb-item">{{$row['masterProduct']['category']->category_name}}</li>
                                <li class="breadcrumb-item active text-primary rfq-product-name" aria-current="page">{{$row['masterProduct']->product_name}}</li>
                            </ol>
                            <span class="remove-product-btn btn-rfq btn-rfq-danger btn-rfq-sm ms-auto" style="cursor: pointer;">
                                <i class="bi bi-trash"></i>
                            </span>
                        </nav>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control sync-field-changes" placeholder="Brand" name="brand" value="{{ $row['brand'] }}">
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control sync-field-changes" placeholder="Remarks" name="remarks" value="{{ $row['remarks'] }}">
                            </div>
                        </div>
                    </div>

                    <!-- Product Variants -->
                    <div id="variantContainer">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light text-center">
                                <tr>
                                    <th style="width: 4%;">Sr. No.</th>
                                    <th style="width: 15%;">Product</th>
                                    <th style="width: 20%;">Specification</th>
                                    <th style="width: 20%;">Size</th>
                                    <th style="width: 10%;">Quantity <span class="text-danger">*</span></th>
                                    <th style="width: 8%;">UOM <span class="text-danger">*</span></th>
                                    <th style="width: 10%;">Attachment</th>
                                    <th style="width: 6%;" class="text-end">
                                        <button type="button" class="btn-rfq btn-rfq-white btn-sm add-variant">+ VARIANT</button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="variants-record">
                                @foreach ($row['productVariants'] as $variant_k => $variant)
                                    <tr class="table-tr">
                                        <td class="text-center row-count-number">{{$variant_k+1}}</td>
                                        <td>
                                            <input type="text" class="form-control sync-field-changes" value="{{$row['masterProduct']->product_name}}" readonly>
                                            <input type="hidden" name="variant_order[]" value="{{ $variant->variant_order }}" class="variant-order">
                                            <input type="hidden" name="edit_id[]" value="{{ $variant->id }}" class="variant-edit-id">
                                            <input type="hidden" name="variant_grp_id[]" value="{{ $variant->variant_grp_id }}" class="variant-grp-id">
                                        </td>
                                        <td><input type="text" class="form-control specification sync-field-changes" placeholder="Specification" name="specification[]" value="{{ $variant->specification }}"></td>
                                        <td><input type="text" class="form-control size sync-field-changes" placeholder="Size" name="size[]" value="{{ $variant->size }}"></td>
                                        <td><input type="number" class="form-control quantity sync-field-changes" placeholder="Quantity *" name="quantity[]" value="{{ $variant->quantity }}"></td>
                                        <td>
                                            <select class="form-select uom sync-field-changes" name="uom[]">
                                                <option value="">Select</option>
                                                @foreach($uoms as $id => $uom_name)
                                                <option value="{{$id}}" {{ $variant->uom==$id ? "selected" : "" }} >{{$uom_name}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="file" class="form-control form-control-sm sync-field-changes" name="attachment[]" onchange="validateRFQFile(this)">
                                            <input type="hidden" name="old_attachment[]" value="{{$variant->attachment}}" class="form-control old-attachment">
                                            <input type="hidden" name="delete_attachment[]" value="" class="form-control delete-attachment">
                                            <span class="attachment-link">
                                                @if (is_file(public_path('uploads/rfq-attachment/'.$variant->attachment)))
                                                    <a class="file-links" href="{{ url('public/uploads/rfq-attachment/'.$variant->attachment) }}" target="_blank" download="{{ $variant->attachment }}">
                                                        {{$variant->attachment}}
                                                    </a>
                                                    <span class="remove-product-variant-file btn-rfq btn-rfq-sm"><i class="bi bi-trash3 text-danger"></i></span>
                                                @endif
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="remove-btn text-danger" style="cursor:pointer;" onclick="removeVariant(this)">
                                                <i class="bi bi-trash" style="font-size: 16px;"></i>
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach