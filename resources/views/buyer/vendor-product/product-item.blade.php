
@foreach($products->product_vendors as $k => $v)
    @php
        $color = $colors->next();
    @endphp
    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-12 ">
        <div class="product-listing-items bg-white px-2 pt-1 position-relative">
            <div class="pt-2 pb-3">
                <label class="ra-custom-checkbox">
                    <input type="checkbox" class="vendor-product-checkbox" {{ in_array($v->vendor_profile->user_id, $selected_vendors) ? "checked" : "" }}  value="{{ $v->vendor_profile->user_id }}">
                    <span class="checkmark "></span>
                </label>
            </div>
            <div class="wishlist">
                <button type="button"
                    class="btn btn-link align-items-center btn-wishlist px-2 pt-2"
                    onclick="toggleWishlist(this)">
                    <span class="visually-hidden-focusable">Make as wishlist</span>
                    <span class="bi bi-heart font-size-18" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-link px-2 py-1 text-dark">
                    <span class="visually-hidden-focusable">Dislike product</span>
                    <span class="bi bi-ban font-size-14" aria-hidden="true"></span>
                </button>
            </div>
            <div class="product-listing-items-thumb product-listing-items-thumb-img position-relative {{$color}}">
                <!-- Image -->
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
                    @else
                        <img src="{{ $image_url }}" alt="{{ $v->vendor_profile->legal_name }}">
                    @endif
                </figure>
            </div>
            <div class="product-listing-items-detail text-center">
                <h5 class="product-title pt-2 fw-bold ra-text-primary ">
                    <a href="" target="_blank" class="truncate-2-lines">{{ $v->vendor_profile->legal_name }}</a>
                    </h5>
                <div class="product-short-desc text-center">
                    <h6 class="font-size-11 font-inherit truncate-3-lines">
                        {!! htmlEntityDecodeWithLimit($v->description, 150) !!}
                    </h6>
                </div>

                <h4 class="product-category">
                    <a class="text-dark fw-bold font-size-12 text-inherit text-truncate w-100" href="" title="{{ $products->product_name }}">{{ $products->product_name }}</a>
                </h4>
            </div>
            <div class="product-listing-items-action">
                <div class="contact-vendor text-center py-2">
                    <span class="visually-hidden-focusable"> Call at</span>
                    <span class="bi bi-phone" aria-hidden="true"></span> +{{ $v->vendor_profile->vendor_country->phonecode }} {{ $v->vendor_profile->user->mobile }}
                </div>
                <div class="d-flex justify-content-center action-rfq gap-2">
                    <button class="ra-btn btn-outline-primary ra-btn-outline-primary-light text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1"
                        data-bs-toggle="modal" data-bs-target="#messageModal">
                        <span class="bi bi-send font-size-11" aria-hidden="true"></span> Message
                    </button>
                    <button class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1 add-this-vendor-product">
                        <span class="bi bi-plus-square font-size-11" aria-hidden="true"></span>
                        Add Rfq
                    </button>
                </div>
            </div>
        </div>
    </div>
@endforeach