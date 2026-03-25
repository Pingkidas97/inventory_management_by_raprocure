

@foreach($products->product_vendors as $k => $v)
    @php
        $color = $colors->next();
    @endphp
    <div class="col-xl-3 col-lg-3 col-md-4 col-sm-12 vendor-product-card">
        <div class="product-listing-items bg-white px-2 pt-1 position-relative">
            <div class="pt-2 pb-3">
                <label class="ra-custom-checkbox">
                    <input type="checkbox" class="vendor-product-checkbox" value="{{ $v->vendor_profile->user_id }}">
                    <span class="checkmark "></span>
                </label>
            </div>
            <div class="product-listing-items-thumb product-listing-items-thumb-img position-relative {{$color}}">
                @php
                    $image_url = '';
                @endphp
                @if (!empty($v->image) && is_file(public_path('uploads/product/thumbnails/250/'.$v->image)))
                    @php
                        $image_url = url('public/uploads/product/thumbnails/250/'.$v->image);
                    @endphp
                @endif
                {{-- <div class="ra-badge ra-badge-prime font-size-12 font-inherit">
                    <span class="font-size-12 font-inherit">PRIME</span>
                </div> --}}
                {{-- <div class="ra-badge ra-badge-popular font-size-12 font-inherit">
                    <span class="font-size-12 font-inherit">POPULAR</span>
                </div> --}}
                <figure>
                    @if($image_url=='')
                        <figcaption class="text-uppercase font-size-20 fw-bold">{{ $v->vendor_profile->legal_name }}</figcaption>
                        {{-- <div class="bg-opacity-25 d-flex align-items-center justify-content-center mb-2 rounded me-3" style="width: 120px; height: 150px; object-fit: cover;background-color:#b9deea;">
                            <strong class="text-dark">{{ $v->vendor_profile->legal_name }} </strong>
                        </div> --}}
                    @else
                        <img src="{{ $image_url }}" alt="{{ $v->vendor_profile->legal_name }}">
                        {{-- <div class="bg-opacity-25 d-flex align-items-center justify-content-center mb-2" style="height: 150px;">
                            <img src="{{ $image_url }}" alt="{{ $v->vendor_profile->legal_name }}" class="rounded me-3" style="width: 120px; height: 150px; object-fit: cover;">
                        </div> --}}
                    @endif
                </figure>
            </div>
            <div class="product-listing-items-detail text-center">
                <h5 class="product-title pt-2 fw-bold ra-text-primary truncate-2-lines">
                    <a href="javascript:void(0);" target="_blank">{{ $v->vendor_profile->legal_name }}</a>
                </h5>
                <div class="product-short-desc text-center truncate-3-lines">
                    <h6 class="font-size-11 font-inherit">
                        {!! htmlEntityDecodeWithLimit($v->description, 120) !!}
                    </h6>
                </div>
                <h4 class="product-category">
                    <a class="text-dark fw-bold font-size-12 text-inherit" href="javascript:void(0);" data-bs-toggle="tooltip" title="{{ $products->product_name }}">{{ $products->product_name }}</a>
                </h4>
            </div>
            <div class="product-listing-items-action">
                <div class="contact-vendor text-center py-2">
                    <span class="visually-hidden-focusable"> Call at</span>
                    <span class="bi bi-phone" aria-hidden="true"></span> +{{ $v->vendor_profile->vendor_country->phonecode }} {{ $v->vendor_profile->user->mobile }}
                </div>
                <div class="d-flex justify-content-center action-rfq gap-2">
                    <button class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1 add-this-vendor-product-to-draft">
                        <span class="bi bi-plus-square font-size-11" aria-hidden="true"></span> Add Rfq
                    </button>
                </div>
            </div>
        </div>
    </div>
@endforeach