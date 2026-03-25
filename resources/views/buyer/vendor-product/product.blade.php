@extends('buyer.layouts.app', ['title'=>$product->product_name.' '])

@section('css')
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar-menu')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1">
        <div class="container-fluid">
            <!---Search listing top section-->
            <section class="search-listing-header px-3">
                <div class="row justify-content-between align-items-center mt-4 search-listing-header">
                    <div class="col-12 col-md-auto mb-4">
                        <h5 class="font-size-14 text-dark">
                            Showing result for: <strong>{{ $product->product_name }}</strong>
                        </h5>
                        <h3 class="font-size-20 text-dark">
                            Number of Vendors (<span class="font-size-20" id="vendor-count"></span>)
                        </h3>
                    </div>
                    <div class="col-12 col-md-auto mb-4">
                        <div class="row search-by-vendor gap-3">
                            <div class="col-12 col-md-auto">
                                <label for="vendor_name" class="col-form-label visually-hidden-focusable">Find by vendor name</label>
                                <input type="text" name="vendor_name" id="vendor-name" class="form-control bg-white product-filter-input" value="" placeholder="Find by vendor name">
                            </div>
                            <div class="col-12 col-md-auto">
                                <label for="brand_name" class="col-form-label visually-hidden-focusable">Find by brand</label>
                                <input type="text" name="brand_name" id="brand-name" class="form-control bg-white product-filter-input" value="" placeholder="Find by brand">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto mb-4">
                        <div class="d-flex align-items-center justify-content-start justify-content-md-end gap-2">
                            <button class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap" id="add-selected-vendor-product">Add all to RFQ</button>
                            <button type="button"
                                class="ra-btn btn-outline-primary ra-btn-outline-primary text-uppercase text-nowrap"
                                data-bs-toggle="modal" data-bs-target="#searchFiterModal">
                                <span class="bi bi-funnel d-none d-sm-block" aria-hidden="true"></span> Filter
                            </button>
                            <div class="d-flex align-items-center position-relative">
                                <label class="text-uppercase text-primary sortby_label" for="sortBy"><span class="small">Sort by:</span></label>
                                <div class="input-group border-1">
                                    <div class="input-group-text d-none d-sm-block">
                                        <span class="bi bi-filter" aria-hidden="true"></span>
                                    </div>

                                    <select class="sortby form-select" id="vendor-sort">
                                        <option value="">Sort by:</option>
                                        <option value="1">Name (A - Z)</option>
                                        <option value="2">Name (Z - A)</option>
                                    </select>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </section>

            <!---Product Listing-->
            <section class="product-listing">
                <div class="container-fluid">
                    <div class="row product-section">
                        {{-- <div class="col-xl-2 col-lg-3 col-md-4 col-sm-12 ">
                            <div class="product-listing-items bg-white px-2 pt-1 position-relative">
                                <div class="pt-2 pb-3">
                                    <label class="ra-custom-checkbox">
                                        <input type="checkbox">
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
                                <div
                                    class="product-listing-items-thumb product-listing-items-thumb-img position-relative light-blue">
                                    <div class="ra-badge ra-badge-prime font-size-12 font-inherit">
                                        <span class="font-size-12 font-inherit">PRIME</span>
                                    </div>
                                    <figure>
                                        <!-- <img src="" alt=""> class d-none need to be added while no image-->
                                        <figcaption class="text-uppercase font-size-20 fw-bold">SINGHM</figcaption>
                                    </figure>
                                </div>
                                <div class="product-listing-items-detail text-center">
                                    <h5 class="product-title pt-2 fw-bold ra-text-primary "><a href=""
                                            target="_blank" class="truncate-2-lines">SINGHM</a></h5>
                                    <div class="product-short-desc text-center">
                                        <h6 class="font-size-11 font-inherit truncate-3-lines">
                                            Organization Description
                                        </h6>
                                    </div>

                                    <h4 class="product-category">
                                        <a class="text-dark fw-bold font-size-12 text-inherit text-truncate w-100" href=""
                                            title="DP TEST MATERIALS">DP TEST
                                            MATERIALS</a>
                                    </h4>
                                </div>
                                <div class="product-listing-items-action">
                                    <div class="contact-vendor text-center py-2">
                                        <span class="visually-hidden-focusable"> Call at</span>
                                        <span class="bi bi-phone" aria-hidden="true"></span> +91 8787965896
                                    </div>
                                    <div class="d-flex justify-content-center action-rfq gap-2">
                                        <button
                                            class="ra-btn btn-outline-primary ra-btn-outline-primary-light text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1"
                                            data-bs-toggle="modal" data-bs-target="#messageModal">
                                            <span class="bi bi-send font-size-11" aria-hidden="true"></span> Message
                                        </button>
                                        <button
                                            class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1">
                                            <span class="bi bi-plus-square font-size-11" aria-hidden="true"></span>
                                            Add Rfq
                                        </button>

                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-12 ">
                            <div class="product-listing-items bg-white px-2 pt-1 position-relative">
                                <div class="pt-2 pb-3">
                                    <label class="ra-custom-checkbox">
                                        <input type="checkbox">
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
                                    <button type="button" class="btn btn-link px-2 py-1 text-danger">
                                        <span class="visually-hidden-focusable">Dislike product</span>
                                        <span class="bi bi-ban font-size-14" aria-hidden="true"></span>

                                    </button>
                                </div>
                                <div
                                    class="product-listing-items-thumb product-listing-items-thumb-img position-relative light-pink">
                                    <div class="ra-badge ra-badge-popular font-size-12 font-inherit">
                                        <span class="font-size-12 font-inherit">POPULAR</span>
                                    </div>
                                    <figure>
                                        <figcaption class="text-uppercase font-size-20 fw-bold">SINGHM</figcaption>
                                    </figure>
                                </div>
                                <div class="product-listing-items-detail text-center">
                                    <h5 class="product-title pt-2 fw-bold ra-text-primary "><a href=""
                                            target="_blank" class="truncate-2-lines">SINGHM</a></h5>
                                    <div class="product-short-desc text-center">
                                        <h6 class="font-size-11 font-inherit truncate-3-lines">
                                            Organization Description
                                        </h6>
                                    </div>

                                    <h4 class="product-category">
                                        <a class="text-dark fw-bold font-size-12 text-inherit text-truncate w-100" href=""
                                            title="DP TEST MATERIALS">DP TEST
                                            MATERIALS</a>
                                    </h4>
                                </div>
                                <div class="product-listing-items-action">
                                    <div class="contact-vendor text-center py-2">
                                        <span class="visually-hidden-focusable"> Call at</span>
                                        <span class="bi bi-phone" aria-hidden="true"></span> +91 8787965896
                                    </div>
                                    <div class="d-flex justify-content-center action-rfq gap-2">
                                        <button
                                            class="ra-btn btn-outline-primary ra-btn-outline-primary-light text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1">
                                            <span class="bi bi-send font-size-11" aria-hidden="true"></span> Message
                                        </button>
                                        <button
                                            class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1">
                                            <span class="bi bi-plus-square font-size-11" aria-hidden="true"></span>
                                            Add Rfq
                                        </button>

                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-12 ">
                            <div class="product-listing-items bg-white px-2 pt-1 position-relative">
                                <div class="pt-2 pb-3">
                                    <label class="ra-custom-checkbox">
                                        <input type="checkbox">
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
                                <div
                                    class="product-listing-items-thumb product-listing-items-thumb-img position-relative light-blue">
                                    <figure>
                                        <img class="object-fit-fill"
                                            src="https://a.eraprocure.co.in/assets/uploads/product/thumbnails/250/230176108.jpg"
                                            alt="">
                                </div>
                                <div class="product-listing-items-detail text-center">
                                    <h5 class="product-title pt-2 fw-bold ra-text-primary "><a href=""
                                            target="_blank" class="truncate-2-lines">SINGHM</a></h5>
                                    <div class="product-short-desc text-center">
                                        <h6 class="font-size-11 font-inherit truncate-3-lines">
                                            Organization Description
                                        </h6>
                                    </div>

                                    <h4 class="product-category">
                                        <a class="text-dark fw-bold font-size-12 text-inherit text-truncate w-100" href=""
                                            title="DP TEST MATERIALS">DP TEST
                                            MATERIALS</a>
                                    </h4>
                                </div>
                                <div class="product-listing-items-action">
                                    <div class="contact-vendor text-center py-2">
                                        <span class="visually-hidden-focusable"> Call at</span>
                                        <span class="bi bi-phone" aria-hidden="true"></span> +91 8787965896
                                    </div>
                                    <div class="d-flex justify-content-center action-rfq gap-2">
                                        <button
                                            class="ra-btn btn-outline-primary ra-btn-outline-primary-light text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1">
                                            <span class="bi bi-send font-size-11" aria-hidden="true"></span> Message
                                        </button>
                                        <button
                                            class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1">
                                            <span class="bi bi-plus-square font-size-11" aria-hidden="true"></span>
                                            Add Rfq
                                        </button>

                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-12 ">
                            <div class="product-listing-items bg-white px-2 pt-1 position-relative">
                                <div class="pt-2 pb-3">
                                    <label class="ra-custom-checkbox">
                                        <input type="checkbox">
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
                                <div
                                    class="product-listing-items-thumb product-listing-items-thumb-img position-relative light-green">
                                    <figure>
                                        <figcaption class="text-uppercase font-size-20 fw-bold">SINGHM</figcaption>
                                    </figure>
                                </div>
                                <div class="product-listing-items-detail text-center">
                                    <h5 class="product-title pt-2 fw-bold ra-text-primary "><a href=""
                                            target="_blank" class="truncate-2-lines">SINGHM</a></h5>
                                    <div class="product-short-desc text-center">
                                        <h6 class="font-size-11 font-inherit truncate-3-lines">
                                            Organization Description
                                        </h6>
                                    </div>

                                    <h4 class="product-category">
                                        <a class="text-dark fw-bold font-size-12 text-inherit text-truncate w-100" href=""
                                            title="DP TEST MATERIALS">DP TEST
                                            MATERIALS</a>
                                    </h4>
                                </div>
                                <div class="product-listing-items-action">
                                    <div class="contact-vendor text-center py-2">
                                        <span class="visually-hidden-focusable"> Call at</span>
                                        <span class="bi bi-phone" aria-hidden="true"></span> +91 8787965896
                                    </div>
                                    <div class="d-flex justify-content-center action-rfq gap-2">
                                        <button
                                            class="ra-btn btn-outline-primary ra-btn-outline-primary-light text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1">
                                            <span class="bi bi-send font-size-11" aria-hidden="true"></span> Message
                                        </button>
                                        <button
                                            class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1">
                                            <span class="bi bi-plus-square font-size-11" aria-hidden="true"></span>
                                            Add Rfq
                                        </button>

                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-12 ">
                            <div class="product-listing-items bg-white px-2 pt-1 position-relative">
                                <div class="pt-2 pb-3">
                                    <label class="ra-custom-checkbox">
                                        <input type="checkbox">
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
                                <div
                                    class="product-listing-items-thumb product-listing-items-thumb-img position-relative light-orange">
                                    <figure>
                                        <figcaption class="text-uppercase font-size-20 fw-bold">SINGHM</figcaption>
                                    </figure>
                                </div>
                                <div class="product-listing-items-detail text-center">
                                    <h5 class="product-title pt-2 fw-bold ra-text-primary "><a href=""
                                            target="_blank" class="truncate-2-lines">SINGHM</a></h5>
                                    <div class="product-short-desc text-center">
                                        <h6 class="font-size-11 font-inherit truncate-3-lines">
                                            Organization Description
                                        </h6>
                                    </div>

                                    <h4 class="product-category">
                                        <a class="text-dark fw-bold font-size-12 text-inherit text-truncate w-100" href=""
                                            title="DP TEST MATERIALS">DP TEST
                                            MATERIALS</a>
                                    </h4>
                                </div>
                                <div class="product-listing-items-action">
                                    <div class="contact-vendor text-center py-2">
                                        <span class="visually-hidden-focusable"> Call at</span>
                                        <span class="bi bi-phone" aria-hidden="true"></span> +91 8787965896
                                    </div>
                                    <div class="d-flex justify-content-center action-rfq gap-2">
                                        <button
                                            class="ra-btn btn-outline-primary ra-btn-outline-primary-light text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1">
                                            <span class="bi bi-send font-size-11" aria-hidden="true"></span> Message
                                        </button>
                                        <button
                                            class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1">
                                            <span class="bi bi-plus-square font-size-11" aria-hidden="true"></span>
                                            Add Rfq
                                        </button>

                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-12 ">
                            <div class="product-listing-items bg-white px-2 pt-1 position-relative">
                                <div class="pt-2 pb-3">
                                    <label class="ra-custom-checkbox">
                                        <input type="checkbox">
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
                                <div
                                    class="product-listing-items-thumb product-listing-items-thumb-img position-relative light-blue">
                                    <figure>
                                        <figcaption class="text-uppercase font-size-20 fw-bold">SINGHM</figcaption>
                                    </figure>
                                </div>
                                <div class="product-listing-items-detail text-center">
                                    <h5 class="product-title pt-2 fw-bold ra-text-primary "><a href=""
                                            target="_blank" class="truncate-2-lines">SINGHM</a></h5>
                                    <div class="product-short-desc text-center">
                                        <h6 class="font-size-11 font-inherit truncate-3-lines">
                                            Organization Description
                                        </h6>
                                    </div>

                                    <h4 class="product-category">
                                        <a class="text-dark fw-bold font-size-12 text-inherit text-truncate w-100" href=""
                                            title="DP TEST MATERIALS">DP TEST
                                            MATERIALS</a>
                                    </h4>
                                </div>
                                <div class="product-listing-items-action">
                                    <div class="contact-vendor text-center py-2">
                                        <span class="visually-hidden-focusable"> Call at</span>
                                        <span class="bi bi-phone" aria-hidden="true"></span> +91 8787965896
                                    </div>
                                    <div class="d-flex justify-content-center action-rfq gap-2">
                                        <button
                                            class="ra-btn btn-outline-primary ra-btn-outline-primary-light text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1">
                                            <span class="bi bi-send font-size-11" aria-hidden="true"></span> Message
                                        </button>
                                        <button
                                            class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1">
                                            <span class="bi bi-plus-square font-size-11" aria-hidden="true"></span>
                                            Add Rfq
                                        </button>

                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-12 ">
                            <div class="product-listing-items bg-white px-2 pt-1 position-relative">
                                <div class="pt-2 pb-3">
                                    <label class="ra-custom-checkbox">
                                        <input type="checkbox">
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
                                <div
                                    class="product-listing-items-thumb product-listing-items-thumb-img position-relative light-blue">
                                    <figure>
                                        <figcaption class="text-uppercase font-size-20 fw-bold">SINGHM</figcaption>
                                    </figure>
                                </div>
                                <div class="product-listing-items-detail text-center">
                                    <h5 class="product-title pt-2 fw-bold ra-text-primary "><a href=""
                                            target="_blank" class="truncate-2-lines">SINGHM</a></h5>
                                    <div class="product-short-desc text-center">
                                        <h6 class="font-size-11 font-inherit truncate-3-lines">
                                            Organization Description
                                        </h6>
                                    </div>

                                    <h4 class="product-category">
                                        <a class="text-dark fw-bold font-size-12 text-inherit text-truncate w-100" href=""
                                            title="DP TEST MATERIALS">DP TEST
                                            MATERIALS</a>
                                    </h4>
                                </div>
                                <div class="product-listing-items-action">
                                    <div class="contact-vendor text-center py-2">
                                        <span class="visually-hidden-focusable"> Call at</span>
                                        <span class="bi bi-phone" aria-hidden="true"></span> +91 8787965896
                                    </div>
                                    <div class="d-flex justify-content-center action-rfq gap-2">
                                        <button
                                            class="ra-btn btn-outline-primary ra-btn-outline-primary-light text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1">
                                            <span class="bi bi-send font-size-11" aria-hidden="true"></span> Message
                                        </button>
                                        <button
                                            class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1">
                                            <span class="bi bi-plus-square font-size-11" aria-hidden="true"></span>
                                            Add Rfq
                                        </button>

                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-12 ">
                            <div class="product-listing-items bg-white px-2 pt-1 position-relative">
                                <div class="pt-2 pb-3">
                                    <label class="ra-custom-checkbox">
                                        <input type="checkbox">
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
                                <div
                                    class="product-listing-items-thumb product-listing-items-thumb-img position-relative light-blue">
                                    <figure>
                                        <figcaption class="text-uppercase font-size-20 fw-bold">SINGHM</figcaption>
                                    </figure>
                                </div>
                                <div class="product-listing-items-detail text-center">
                                    <h5 class="product-title pt-2 fw-bold ra-text-primary "><a href=""
                                            target="_blank" class="truncate-2-lines">SINGHM</a></h5>
                                    <div class="product-short-desc text-center">
                                        <h6 class="font-size-11 font-inherit truncate-3-lines">
                                            Organization Description
                                        </h6>
                                    </div>

                                    <h4 class="product-category">
                                        <a class="text-dark fw-bold font-size-12 text-inherit text-truncate w-100" href=""
                                            title="DP TEST MATERIALS">DP TEST
                                            MATERIALS</a>
                                    </h4>
                                </div>
                                <div class="product-listing-items-action">
                                    <div class="contact-vendor text-center py-2">
                                        <span class="visually-hidden-focusable"> Call at</span>
                                        <span class="bi bi-phone" aria-hidden="true"></span> +91 8787965896
                                    </div>
                                    <div class="d-flex justify-content-center action-rfq gap-2">
                                        <button
                                            class="ra-btn btn-outline-primary ra-btn-outline-primary-light text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1">
                                            <span class="bi bi-send font-size-11" aria-hidden="true"></span> Message
                                        </button>
                                        <button
                                            class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1">
                                            <span class="bi bi-plus-square font-size-11" aria-hidden="true"></span>
                                            Add Rfq
                                        </button>

                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-12 ">
                            <div class="product-listing-items bg-white px-2 pt-1 position-relative">
                                <div class="pt-2 pb-3">
                                    <label class="ra-custom-checkbox">
                                        <input type="checkbox">
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
                                <div
                                    class="product-listing-items-thumb product-listing-items-thumb-img position-relative light-blue">

                                    <figure>
                                        <!-- <img src="" alt=""> -->
                                        <figcaption class="text-uppercase font-size-20 fw-bold">SINGHM</figcaption>
                                    </figure>
                                </div>
                                <div class="product-listing-items-detail text-center">
                                    <h5 class="product-title pt-2 fw-bold ra-text-primary "><a href=""
                                            target="_blank" class="truncate-2-lines">SINGHM</a></h5>
                                    <div class="product-short-desc text-center">
                                        <h6 class="font-size-11 font-inherit truncate-3-lines">
                                            Organization Description
                                        </h6>
                                    </div>

                                    <h4 class="product-category">
                                        <a class="text-dark fw-bold font-size-12 text-inherit text-truncate w-100" href=""
                                            title="DP TEST MATERIALS">DP TEST
                                            MATERIALS</a>
                                    </h4>
                                </div>
                                <div class="product-listing-items-action">
                                    <div class="contact-vendor text-center py-2">
                                        <span class="visually-hidden-focusable"> Call at</span>
                                        <span class="bi bi-phone" aria-hidden="true"></span> +91 8787965896
                                    </div>
                                    <div class="d-flex justify-content-center action-rfq gap-2">
                                        <button
                                            class="ra-btn btn-outline-primary ra-btn-outline-primary-light text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1">
                                            <span class="bi bi-send font-size-11" aria-hidden="true"></span> Message
                                        </button>
                                        <button
                                            class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10 text-inherit px-2 py-1">
                                            <span class="bi bi-plus-square font-size-11" aria-hidden="true"></span>
                                            Add Rfq
                                        </button>

                                    </div>
                                </div>

                            </div>
                        </div> --}}

                    </div>
            </section>

            <div class="d-flex justify-content-center justify-content-md-end pt-2 pb-4 mb-4">
                <div class="disclaimer">
                    <strong>If you can't find your Vendor, <a href="javascript:void(0)" class="text-underline">Click here</a></strong> 
                </div>

            </div>
        </div>
    </main>

    <!-- Modal Filter -->
    <div class="modal fade" id="searchFiterModal" tabindex="-1" aria-labelledby="searchFiterModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-graident text-white">
                    <h1 class="modal-title font-size-12" id="searchFiterModalLabel"><span class="bi bi-funnel"
                            aria-hidden="true"></span> Filter</h1>
                    <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <section class="dealer mb-3">
                        <h3 class="font-size-18">Dealer </h3>
                        <div class="filter-list scroll-list">
                            @foreach($dealer_types as $k=>$v)
                                <div class="mt-1">
                                    <label class="ra-custom-checkbox">
                                        <input type="checkbox" name="dealer_type" class="input-dealer-type" value="{{$k}}">
                                        <span class="font-size-11">{{$v}}</span>
                                        <span class="checkmark "></span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="location mb-3">
                        <h3 class="font-size-18">Location </h3>
                        <div class="filter-list scroll-list" id="vendor-locations">
                        </div>
                    </section>
                </div>
                <div class="modal-footer">
                    <button type="button" class="ra-btn ra-btn-outline-danger text-uppercase text-nowrap font-size-11 reset-vendor-filter" data-bs-dismiss="modal">Reset</button>
                    <button type="button" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 product-filter">Apply</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Message -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-graident text-white">
                    <h1 class="modal-title font-size-12" id="messageModalLabel"><span class="bi bi-pencil"
                            aria-hidden="true"></span> New Message</h1>
                    <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="message pt-3">
                        <input type="text" value="DVC PVT LTD" readonly="" class="form-control">
                    </div>
                    <div class="message pt-2">
                        <input type="text" value="SHOULDER BOLT" readonly="" class="form-control">
                    </div>

                    <section class="ck-editor-section py-2">
                        This is the placeholder of the editor.
                    </section>
                    <section class="upload-file py-2">
                        <div class="file-upload-block justify-content-start">
                            <div class="file-upload-wrapper">
                                <input type="file" class="file-upload" style="display: none;">
                                <button type="button"
                                    class="custom-file-trigger form-control text-start text-dark font-size-11">Upload
                                    file</button>
                            </div>
                            <div class="file-info" style="display: none;"></div>
                        </div>
                    </section>

                </div>
                <div class="modal-footer">
                    <button type="button"
                        class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11">Send</button>
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

            // Debounced input on filters
            $(".product-filter-input").debounceInput(function () {
                var val = $(this).val();
                if (val.length >= 3 || val.length === 0) {
                    loadVendorProduct();
                }
            }, 200); // 200ms debounce
        });
        $(document).on("blur", ".product-filter", function () {
            if($(this).val().length<3){
                $(this).val('');
            }
        });
        $(document).on("click", ".product-filter", function () {
            loadVendorProduct();
            $("#searchFiterModal").modal("hide");
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
                    vendor_location_html += `
                        <div class="mt-1 vendor-checkbox-div">
                            <label class="ra-custom-checkbox mb-0">
                                <input type="checkbox" class="input-location domestic-vendor" name="vendor_location" value="${state.id}" ${checked}>
                                <span class="font-size-11">${state.name}</span>
                                <span class="checkmark"></span>
                            </label>
                        </div>
                    `;
                }
            }
            if (all_international_vendor_country.length > 0) {
                for (var i = 0; i < all_international_vendor_country.length; i++) {
                    let country = all_international_vendor_country[i];
                    let checked = '';
                    if (int_vendor_location.map(Number).includes(country.id)) {
                        checked = 'checked';
                    }
                    vendor_location_html += `
                        <div class="mt-1 vendor-checkbox-div">
                            <label class="ra-custom-checkbox mb-0">
                                <input type="checkbox" class="input-int-location international-vendor" name="vendor_location" value="${country.id}" ${checked}>
                                <span class="font-size-11">${country.name}</span>
                                <span class="checkmark"></span>
                            </label>
                        </div>
                    `;
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
            vendors_id.push($(this).parents(".product-listing-items").find(".vendor-product-checkbox").val());
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