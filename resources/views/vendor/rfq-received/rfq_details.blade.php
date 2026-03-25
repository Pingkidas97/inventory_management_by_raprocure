@extends('vendor.layouts.app_second',['title'=>'RFQ','sub_title'=>'RFQ Details'])
@section('content')
<style>
.form-control.form-control-price-basis {
    width: 100% !important;
}

.form-control.form-control-payment-terms {
    width: 100% !important;
}

.form-control.form-control-delivery-period {
    width: 100% !important;
}

.form-control.form-control-price-validity {
    width: 100% !important;
}

.form-control.form-control-dispatch-branch {
    width: 100% !important;
}

.form-select.form-select-currency {
    width: 100% !important;
}
</style>
@php
  $is_international_vendor = is_national();
  $is_international_buyer_check = is_national_buyer($rfq->buyer_id);
@endphp
<main class="main main-inner-page flex-grow-1 py-2 px-md-3 px-1">
    <section class="container-fluid">
        <div class="d-flex align-items-center flex-wrap justify-content-between mr-auto flex py-2">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-global py-2 mb-0">
                    <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                    <li class="breadcrumb-item">RFQ Received</li>
                    <li class="breadcrumb-item active" aria-current="page">RFQ Details</li>
                </ol>
            </nav>
            <div>
                <h2 class="font-size-15 fw-bold">Please Quote Rate without GST</h2>
            </div>
        </div>
        @php
        $branch = getbuyerBranchById($rfq->buyer_branch);
        @endphp


        <!-- RFQ Details Card -->
        <section class="rfq-vendor-listing">
            <div class="card shadow-none mb-3">
                <div class="card-body">
                    <ul>
                        <li><span class="fw-bold">RFQ No:</span> <span>{{ $rfq->rfq_id }}</span></li>

                        <li><span class="fw-bold">RFQ Date:</span>
                            <span>{{ \Carbon\Carbon::parse($rfq->created_at)->format('d/m/Y') }}</span>
                        </li>

                        <li><span class="fw-bold">PRN Number:</span> <span>{{ $rfq->prn_no ?? '-' }}</span></li>

                        <li><span class="fw-bold">Buyer Name:</span>
                            <span>{{ $rfq->buyer_legal_name ?? '-' }}</span>
                        </li>

                        <li><span class="fw-bold">User Name:</span>
                            <span>{{ $rfq->buyer_user_name ?? '-' }}</span>
                        </li>

                        <li><span class="fw-bold">Branch Name:</span> <span>{!!  $branch->name ?? '-' !!}</span></li>

                        <li>
                            <span class="fw-bold">Branch Address:</span>
                            <span>
                                {{ Str::limit($branch->address ?? '-', 30) }}
                                @if(!empty($branch->address))
                                <button type="button" class="ra-btn ra-btn-link height-inherit text-black font-size-14"
                                    data-bs-toggle="tooltip" data-bs-original-title="{!! $branch->address !!}">
                                    <span class="bi bi-info-circle-fill font-size-14"></span>
                                </button>
                                @endif
                            </span>
                        </li>

                        <li><span class="fw-bold">Last Date to Response:</span>
                            <span>{{ $rfq->last_response_date ? \Carbon\Carbon::parse($rfq->last_response_date)->format('d/m/Y') : '-' }}</span>
                        </li>

                        <li><span class="fw-bold">Last Edited Date:</span>
                            <span>{{ $rfq->updated_at ? \Carbon\Carbon::parse($rfq->updated_at)->format('d/m/Y') : '-' }}</span>
                        </li>

                        <li><span class="fw-bold"><b class="text-primary">RFQ Terms -</b></span></li>

                        <li><span class="fw-bold">Price Basis:</span>
                            <span>{{ $rfq->buyer_price_basis ?? '-' }}</span>
                        </li>

                        <li><span class="fw-bold">Payment Terms:</span>
                            <span>{{ $rfq->buyer_pay_term ?? '-' }}</span>
                        </li>

                        <li><span class="fw-bold">Delivery Period:</span>
                            <span>{{ $rfq->buyer_delivery_period ?? '-' }} Days</span>
                        </li>
                    </ul>

                </div>
            </div>
        </section>

        <!-- Product Table -->
        <section class="rfq-vendor-listing-product-form">
            <div class="card shadow-none mb-3">
                <div class="card-body card-vendor-list-right-panel toggle-table-wrapper">
                    @foreach($products as $index => $product)
                    <div class="d-flex justify-content-between mb-30">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-vendor">
                                <li class="breadcrumb-item"><a href="#">{{ $product->division_name }}</a></li>
                                <li class="breadcrumb-item"><a href="#">{{ $product->category_name }}</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $product->product_name }}</li>
                            </ol>
                        </nav>
                    </div>

                    <div class="table-responsive table-product toggle-table-content">
                        <table class="table table-product-list table-d-block-mobile">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Specification</th>
                                    <th>Size</th>
                                    <th>Quantity/UOM</th>

                                    <th>Price (<span class="currency-symbol"></span>)</th>
                                    <th>MRP (<span class="currency-symbol"></span>)</th>
                                    <th>Disc.(%)</th>
                                    <th>Total (<span class="currency-symbol"></span>)</th>

                                    <th>Specs</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $productVariants = $variants[$product->product_id] ?? []; @endphp
                                @foreach($productVariants as $vIndex => $variant)
                                <tr>
                                    <td>{{ $vIndex + 1 }}</td>
                                    <td>{{ $variant->specification }}</td>
                                    <td class="text-center">
                                        @php $sizeStr = strip_tags($variant->size); @endphp
                                        @if(strlen($sizeStr) > 5)
                                            {!!  mb_substr($sizeStr, 0, length: 5) !!}
                                            <button type="button" class="btn btn-link p-0 m-0 align-baseline" data-bs-toggle="tooltip" data-bs-placement="top" title="{!!  $sizeStr !!}">
                                                <i class="bi bi-info-circle"></i>
                                            </button>
                                        @else
                                            {!! $variant->size !!} 

                                        @endif
                                    </td>
                                    <td class="text-center">{{ $variant->quantity }} {{ getUOMName($variant->uom) }} </td>
                                    <td><input type="number" name="price[{{ $variant->id }}]"
                                            class="form-control form-control-sm" value=""></td>
                                    <td><input type="number" name="mrp[{{ $variant->id }}]"
                                            class="form-control form-control-sm" value=""></td>
                                    <td><input type="number" name="disc[{{ $variant->id }}]"
                                            class="form-control form-control-sm" value=""></td>
                                    <td><input type="number" name="total[{{ $variant->id }}]"
                                            class="form-control form-control-sm" value=""></td>
                                    <td>
                                        <input type="text" name="vendor_spec[{{ $variant->id }}]"
                                            class="form-control form-control-sm" value="" placeholder="Enter Specs"
                                            data-bs-toggle="modal" data-bs-target="#submitSpecification">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Search by Brand and Remarks -->
                    <div class="row mt-4">
                        <div class="col-md-4 mb-4">
                            <div class="input-group disabled">
                                <span class="input-group-text">
                                    <span class="bi bi-pencil" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="remarks" placeholder="Remarks" disabled>
                                    <label for="remarks">Remarks</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group disabled">
                                <span class="input-group-text">
                                    <span class="bi bi-tag-fill" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="brand" placeholder="Brand" disabled>
                                    <label for="brand">Brand</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-paperclip" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <div class="form-floating-tooltip">
                                        <button type="button"
                                            class="ra-btn ra-btn-link height-inherit text-danger font-size-18"
                                            data-bs-toggle="tooltip" data-placement="top"
                                            data-bs-original-title="(Maximum allowed file size 1MB, PDF, DOC, Excel, Image)">
                                            <span class="bi bi-question-circle font-size-18"></span>
                                        </button>
                                    </div>
                                    <span class="form-floating-label" for="uploadFile">Upload File</span>
                                    <div class="simple-file-upload">
                                        <input type="file" id="uploadFile" class="real-file-input"
                                            style="display: none;">

                                        <div class="file-display-box form-control text-start font-size-12 text-dark"
                                            role="button" data-bs-toggle="tooltip" data-bs-placement="top">
                                            Attach file
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-tag-fill" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="sellerBrand" placeholder="Seller Brand">
                                    <label for="sellerBrand">Seller Brand</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>
            </div>
        </section>

        <!-- Remarks Section -->
        <section>
            <div class="row">
                <div class="col-12">
                    <label for="sellerRemarks">Remarks:</label>
                    <textarea name="seller-remarks" id="sellerRemarks" rows="4" class="form-control"
                        placeholder="If there is any change in quantity, please specify here."></textarea>
                </div>
                <div class="col-12 my-3">
                    <label for="sellerAdditionalRemarks">Additional Remarks:</label>
                    <textarea name="Seller-Additional-Remarks" id="sellerAdditionalRemarks" rows="4"
                        class="form-control"
                        placeholder="Any details about warranty/gauarantee, please specify here."></textarea>
                </div>
            </div>
        </section>

        <!-- Bottom Control Section -->
        <section class="product-option-filter">
            <div class="card">
                <div class="card-body">
                    <div class="row gx-3 gy-4 pt-3 justify-content-center align-items-center">
                        <div class="col-12 col-sm-auto col-xxl-2">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-geo-alt" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control form-control-price-basis" id="priceBasis"
                                        placeholder="Price Basis" value="12">
                                    <label for="priceBasis">Price Basis <span class="text-danger">*</span></label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-auto col-xxl-2">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-currency-rupee" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control form-control-payment-terms" id="paymentTerms"
                                        placeholder="Payment Terms" value="Online">
                                    <label for="paymentTerms">Payment Terms <span class="text-danger">*</span></label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-auto col-xxl-2 delivery-period-width">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-calendar-date" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control form-control-delivery-period"
                                        id="deliveryPeriodInDays" placeholder="Delivery Period (In Days)" value="15">
                                    <label for="deliveryPeriodInDays">Delivery Period (In Days) <span
                                            class="text-danger">*</span></label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-auto col-xxl-2">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-calendar-date" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control form-control-price-validity"
                                        id="priceValidityInDays" placeholder="Price Validity (In Days)">
                                    <label for="priceValidityInDays">Price Validity (In Days)</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-auto col-xxl-2">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-geo-alt" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <select class="form-select form-control-dispatch-branch" id="vendorDispatchBranch"
                                        name="vendor-dispatch-branch">
                                        <option value="">Select</option>
                                        <option value="">Regd. Address</option>
                                        <option value="">Branch one kolkata</option>
                                    </select>
                                    <label for="vendorDispatchBranch">Dispatch Branch <span
                                            class="text-danger">*</span></label>
                                </div>
                            </div>
                        </div>
                        @php
                            $is_disabled = ($is_international_vendor == '1' && $is_international_buyer_check == '1');
                        @endphp

                        <div class="col-12 col-sm-auto flex-xxl-grow-1">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-currency-exchange" aria-hidden="true"></i>
                                </span>
                                <div class="form-floating">
                                    <select class="form-select form-select-currency globle-field-changes" 
                                            id="updateCurrency" 
                                            name="vendor_currency"
                                            {{ $is_disabled ? 'disabled' : '' }}
                                            aria-label="Select">
                                        
                                        @if (!$is_disabled)
                                            <option value="">Select</option>
                                        @endif

                                        @foreach($vendor_currency ?? [] as $val)
                                            @php
                                                if ($val->currency_name == '') continue;
                                                $currency_val = ($val->currency_symbol == 'रु') ? 'NPR' : $val->currency_symbol;
                                                $currency_symbol = ($val->currency_symbol == 'रु') ? 'NPR' : $val->currency_symbol;
                                                $selected = ($currency_val == ($normal_product_data['vend_currency'] ?? '')) ? 'selected' : '';
                                            @endphp

                                            <option value="{{ $currency_val }}" 
                                                    data-symbol="{{ $currency_symbol }}" 
                                                    {{ $selected }}>
                                                {{ $val->currency_name }} ({{ $val->currency_symbol }})
                                            </option>
                                        @endforeach
                                    </select>

                                    @if ($is_disabled)
                                        <input type="hidden" name="vendor_currency" value="₹">
                                    @endif

                                    <label for="updateCurrency">Currency <span class="text-danger">*</span></label>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="row pt-3 gx-3 gy-3 justify-content-center align-items-center">
                        <div class="col-auto">
                            <button type="button" data-bs-toggle="modal" data-bs-target="#sendMessage"
                                class="ra-btn ra-btn-sm px-3 ra-btn-outline-primary">
                                <i class="bi bi-send" aria-hidden="true"></i>
                                Send Message
                            </button>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="ra-btn ra-btn-outline-primary-light">
                                <i class="bi bi-journal-bookmark" aria-hidden="true"></i>
                                Save
                            </button>
                        </div>
                        <div class="col-12 col-sm-auto text-center">
                            <button type="button" class="ra-btn ra-btn-sm px-3 ra-btn-primary send-quote-btn">
                                <i class="bi bi-check-lg" aria-hidden="true"></i>
                                Send Quote
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </section>
</main>

<!-- Include Modal HTML if needed here -->
<!-- Modal: Specification -->
<div class="modal fade" id="submitSpecification" tabindex="-1" aria-labelledby="submitSpecificationLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header justify-content-between bg-graident text-white px-4">
                <h2 class="modal-title font-size-13" id="submitSpecificationLabel">
                    <span class="bi bi-pencil" aria-hidden="true"></span> View/Update Specs
                </h2>
                <button type="button" class="btn btn-link p-0 font-size-14 text-white" data-bs-dismiss="modal"
                    aria-label="Close">
                    <span class="bi bi-x-lg" aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <textarea class="form-control specifications-textarea" id="specificationsTextarea"
                        rows="8"></textarea>
                </div>
                <div class="text-center">
                    <button type="button"
                        class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11">Update</button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal: Send Message -->
<div class="modal fade" id="sendMessage" tabindex="-1" aria-labelledby="sendMessageLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header justify-content-between bg-graident text-white px-4">
                <h2 class="modal-title font-size-13" id="sendMessageLabel">
                    <span class="bi bi-pencil" aria-hidden="true"></span> Send Message
                </h2>
                <button type="button" class="btn btn-link p-0 font-size-14 text-white" data-bs-dismiss="modal"
                    aria-label="Close">
                    <span class="bi bi-x-lg" aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <input type="text" value="RONI-25-00043" name="subject" class="form-control" readonly
                        placeholder="Subject">
                </div>
                <div class="mb-3">
                    <textarea name="send-msg" class="form-control specifications-textarea" rows="8"
                        placeholder="Write your message here..."></textarea>
                </div>
                <div class="mb-3">
                    <div class="simple-file-upload">
                        <input type="file" class="real-file-input" style="display: none;">
                        <div class="file-display-box form-control text-start font-size-12 text-dark" role="button"
                            data-bs-toggle="tooltip" data-bs-placement="top">
                            Upload file
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="text-end">
                    <button type="button"
                        class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11">Send</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Laravel Blade RFQ price input logic (jQuery version)
$(document).on("blur", ".price-change", function () {
    var row = $(this).closest('tr');
    var price = parseFloat(row.find(".variant-price").val().replace(/[^0-9.-]/g, '') || 0);
    var mrp = parseFloat(row.find(".variant-mrp").val().replace(/[^0-9.-]/g, '') || 0);
    var discount = parseFloat(row.find(".variant-discount").val().replace(/[^0-9.-]/g, '') || 0);
    var totalQty = parseFloat(row.find(".totalQty").text() || 0);

    // Format price fields
    if (price > 0) row.find(".variant-price").val(price.toFixed(2));
    else row.find(".variant-price").val('');

    if (mrp > 0) row.find(".variant-mrp").val(mrp.toFixed(2));
    else row.find(".variant-mrp").val('');

    if (discount > 0 && discount <= 99) {
        row.find(".variant-discount").val(discount.toFixed(2));
    } else if (discount > 99) {
        alert("Discount can not be greater than 100%");
        discount = 99;
        row.find(".variant-discount").val(discount);
    } else {
        row.find(".variant-discount").val('');
        discount = 0;
    }

    // Recalculate price based on MRP and discount
    if (mrp > 0 && discount > 0) {
        let discountedPrice = mrp - (mrp * discount / 100);
        if (discountedPrice.toFixed(2) == 0) {
            alert("Price can not be 0");
            row.find(".variant-price").val('');
        } else {
            row.find(".variant-price").val(discountedPrice.toFixed(2));
        }
    } else if (mrp > 0 && !discount) {
        row.find(".variant-price").val(mrp.toFixed(2));
    }

    // Recalculate total
    let finalPrice = parseFloat(row.find(".variant-price").val() || 0);
    let total = finalPrice * totalQty;
    row.find(".totalAmounts").text(total > 0 ? IND_amount_format(total.toFixed(2)) : '');
});

document.addEventListener('DOMContentLoaded', function () {
    const currencyDropdown = document.getElementById('updateCurrency');
    const currencyTargets = document.querySelectorAll('.currency-symbol');

    if (currencyDropdown) {
        currencyDropdown.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const symbol = selectedOption.dataset.symbol || '₹';

            // Update all elements that show currency symbol
            currencyTargets.forEach(function (el) {
                el.textContent = symbol;
            });
        });
    }
});
</script>
@endsection