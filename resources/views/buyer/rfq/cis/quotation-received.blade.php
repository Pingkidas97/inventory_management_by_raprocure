@extends('buyer.layouts.app', ['title'=>'Quotation Received'])

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
            <!-- Start of Breadcrumb -->
            <nav class="breadcrumb mb-2 font-size-13">
                <a class="breadcrumb-item text-muted" href="#">Dashboard</a>
                <a class="breadcrumb-item text-muted" href="#">CIS</a>
                <span class="breadcrumb-item active text-dark" aria-current="page">Quotation Received</span>
            </nav>

            <!-- RFQ Listing -->

            <section class="rfq-vendor-listing card rounded shadow-none mb-4">
                <div class="card-body">
                    <ul>
                        <li>
                            <span>RFQ No:</span>
                            <span class="fw-bold">RONI-25-00043</span>
                        </li>
                        <li>
                            <span>RFQ Date:</span>
                            <span class="fw-bold">06/06/2025</span>
                        </li>
                        <li>
                            <span>PRN Number:</span>
                            <span class="fw-bold"></span>
                        </li>
                        <li>
                            <span>Vendor Name:</span>
                            <span class="fw-bold">RONIT VENDOR PROFILE COMPANY QWE</span>
                        </li>
                        <li>
                            <span>Branch Name:</span>
                            <span class="fw-bold">Kolkata/Newtown</span>
                        </li>
                        <li>
                            <span>Branch Address:</span>
                            <span class="fw-bold">
                                newtown,24 Parg
                                <span role="button" type="button"
                                    class="btn btn-link p-0 height-inherit text-black font-size-14"
                                    data-bs-toggle="tooltip" data-placement="top"
                                    data-bs-original-title="newtown,24 Parganas (n),West Bengal,India">
                                    <span class="bi bi-info-circle-fill font-size-14"></span>
                                </span>
                            </span>
                        </li>
                        <li>
                            <span>Last Date to Response:</span>
                            <span class="fw-bold"></span>
                        </li>
                        <li>
                            <span class="fw-bold"><b class="text-primary-blue">RFQ Terms</b> - Price Basis:</span>
                            <span class="fw-bold"></span>
                        </li>
                        <li>
                            <span class="fw-bold"> Price Basis:</span>
                            <span class="fw-bold"> </span>
                        </li>
                        <li>
                            <span>Payment Terms:</span>
                            <span class="fw-bold"></span>
                        </li>
                        <li>
                            <span>Delivery Period:</span>
                            <span class="fw-bold"></span>
                        </li>
                        <li>
                            <button type="button" class="ra-btn ra-btn-sm px-3 ra-btn-outline-primary send-quote-btn font-size-11">
                                Download Quotation PDF
                            </button>
                        </li>
                    </ul>
                </div>
            </section>

            <!---RFQ Vendor list section-->
            <section class="mx-0 mx-md-2 py-2">
                <div class="card card-vendor-list">
                    <!-- Top breadcrumb -->
                    <div class="d-flex justify-content-between mb-30">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-vendor">
                                    <li class="breadcrumb-item"><a href="#">1. GENERAL</a></li>
                                    <li class="breadcrumb-item"><a href="#">CONSUMABLE</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">DP TEST MATERIALS
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <!-- Product List Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive table-product px-2">
                                <table class="table table-product-list min-width-1100">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-nowrap text-dark">
                                                #
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark text-start long-spec">
                                                Specification
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Size
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Quantity/UOM
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                    Price(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                MRP(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Disc.(%)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Total(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Counter <br>Offer
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Historical <br>Price
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark text-start short-spec">
                                                Specs
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>


                    <!-- Search by Brand and Remarks -->
                    <div class="row mt-4 gy-2 rfq-details-top-filter">
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-pencil"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="remarks" placeholder="Remarks"
                                        readonly disabled>
                                    <label for="remarks" class="font-size-13">Remarks</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-tags"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="brand" placeholder="Brand" readonly
                                        disabled>
                                    <label for="brand" class="font-size-13">Brand</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <span class="form-control" readonly disabled>Attachment File...</span>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-tags"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="sellerBrand" placeholder="Seller Brand" value="121212" readonly disabled>
                                    <label for="sellerBrand" class="font-size-13">Seller Brand</label>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>

            </section>
            <section class="mx-0 mx-md-2 py-2">
                <div class="card card-vendor-list">
                    <!-- Top breadcrumb -->
                    <div class="d-flex justify-content-between mb-30">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-vendor">
                                    <li class="breadcrumb-item"><a href="#">1. GENERAL</a></li>
                                    <li class="breadcrumb-item"><a href="#">CONSUMABLE</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">DP TEST MATERIALS
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <!-- Product List Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive table-product px-2">
                                <table class="table table-product-list min-width-1100">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-nowrap text-dark">
                                                #
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark text-start long-spec">
                                                Specification
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Size
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Quantity/UOM
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                    Price(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                MRP(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Disc.(%)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Total(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Counter <br>Offer
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Historical <br>Price
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark text-start short-spec">
                                                Specs
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>


                    <!-- Search by Brand and Remarks -->
                    <div class="row mt-4 gy-2 rfq-details-top-filter">
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-pencil"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="remarks" placeholder="Remarks"
                                        readonly disabled>
                                    <label for="remarks" class="font-size-13">Remarks</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-tags"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="brand" placeholder="Brand" readonly
                                        disabled>
                                    <label for="brand" class="font-size-13">Brand</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <span class="form-control" readonly disabled>Attachment File...</span>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-tags"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="sellerBrand" placeholder="Seller Brand" value="121212" readonly disabled>
                                    <label for="sellerBrand" class="font-size-13">Seller Brand</label>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>

            </section>
            <section class="mx-0 mx-md-2 py-2">
                <div class="card card-vendor-list">
                    <!-- Top breadcrumb -->
                    <div class="d-flex justify-content-between mb-30">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-vendor">
                                    <li class="breadcrumb-item"><a href="#">1. GENERAL</a></li>
                                    <li class="breadcrumb-item"><a href="#">CONSUMABLE</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">DP TEST MATERIALS
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <!-- Product List Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive table-product px-2">
                                <table class="table table-product-list min-width-1100">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-nowrap text-dark">
                                                #
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark text-start long-spec">
                                                Specification
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Size
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Quantity/UOM
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                    Price(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                MRP(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Disc.(%)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Total(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Counter <br>Offer
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Historical <br>Price
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark text-start short-spec">
                                                Specs
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>


                    <!-- Search by Brand and Remarks -->
                    <div class="row mt-4 gy-2 rfq-details-top-filter">
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-pencil"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="remarks" placeholder="Remarks"
                                        readonly disabled>
                                    <label for="remarks" class="font-size-13">Remarks</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-tags"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="brand" placeholder="Brand" readonly
                                        disabled>
                                    <label for="brand" class="font-size-13">Brand</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <span class="form-control" readonly disabled>Attachment File...</span>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-tags"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="sellerBrand" placeholder="Seller Brand" value="121212" readonly disabled>
                                    <label for="sellerBrand" class="font-size-13">Seller Brand</label>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>

            </section>
            <section class="mx-0 mx-md-2 py-2">
                <div class="card card-vendor-list">
                    <!-- Top breadcrumb -->
                    <div class="d-flex justify-content-between mb-30">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-vendor">
                                    <li class="breadcrumb-item"><a href="#">1. GENERAL</a></li>
                                    <li class="breadcrumb-item"><a href="#">CONSUMABLE</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">DP TEST MATERIALS
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <!-- Product List Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive table-product px-2">
                                <table class="table table-product-list min-width-1100">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-nowrap text-dark">
                                                #
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark text-start long-spec">
                                                Specification
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Size
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Quantity/UOM
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                    Price(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                MRP(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Disc.(%)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Total(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Counter <br>Offer
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Historical <br>Price
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark text-start short-spec">
                                                Specs
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>


                    <!-- Search by Brand and Remarks -->
                    <div class="row mt-4 gy-2 rfq-details-top-filter">
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-pencil"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="remarks" placeholder="Remarks"
                                        readonly disabled>
                                    <label for="remarks" class="font-size-13">Remarks</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-tags"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="brand" placeholder="Brand" readonly
                                        disabled>
                                    <label for="brand" class="font-size-13">Brand</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <span class="form-control" readonly disabled>Attachment File...</span>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-tags"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="sellerBrand" placeholder="Seller Brand" value="121212" readonly disabled>
                                    <label for="sellerBrand" class="font-size-13">Seller Brand</label>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>

            </section>
            <section class="mx-0 mx-md-2 py-2">
                <div class="card card-vendor-list">
                    <!-- Top breadcrumb -->
                    <div class="d-flex justify-content-between mb-30">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-vendor">
                                    <li class="breadcrumb-item"><a href="#">1. GENERAL</a></li>
                                    <li class="breadcrumb-item"><a href="#">CONSUMABLE</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">DP TEST MATERIALS
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <!-- Product List Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive table-product px-2">
                                <table class="table table-product-list min-width-1100">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-nowrap text-dark">
                                                #
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark text-start long-spec">
                                                Specification
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Size
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Quantity/UOM
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                    Price(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                MRP(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Disc.(%)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Total(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Counter <br>Offer
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Historical <br>Price
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark text-start short-spec">
                                                Specs
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>


                    <!-- Search by Brand and Remarks -->
                    <div class="row mt-4 gy-2 rfq-details-top-filter">
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-pencil"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="remarks" placeholder="Remarks"
                                        readonly disabled>
                                    <label for="remarks" class="font-size-13">Remarks</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-tags"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="brand" placeholder="Brand" readonly
                                        disabled>
                                    <label for="brand" class="font-size-13">Brand</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <span class="form-control" readonly disabled>Attachment File...</span>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-tags"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="sellerBrand" placeholder="Seller Brand" value="121212" readonly disabled>
                                    <label for="sellerBrand" class="font-size-13">Seller Brand</label>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>

            </section>
            <section class="mx-0 mx-md-2 py-2">
                <div class="card card-vendor-list">
                    <!-- Top breadcrumb -->
                    <div class="d-flex justify-content-between mb-30">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-vendor">
                                    <li class="breadcrumb-item"><a href="#">1. GENERAL</a></li>
                                    <li class="breadcrumb-item"><a href="#">CONSUMABLE</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">DP TEST MATERIALS
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <!-- Product List Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive table-product px-2">
                                <table class="table table-product-list min-width-1100">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-nowrap text-dark">
                                                #
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark text-start long-spec">
                                                Specification
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Size
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Quantity/UOM
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                    Price(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                MRP(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Disc.(%)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Total(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Counter <br>Offer
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Historical <br>Price
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark text-start short-spec">
                                                Specs
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>


                    <!-- Search by Brand and Remarks -->
                    <div class="row mt-4 gy-2 rfq-details-top-filter">
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-pencil"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="remarks" placeholder="Remarks"
                                        readonly disabled>
                                    <label for="remarks" class="font-size-13">Remarks</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-tags"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="brand" placeholder="Brand" readonly
                                        disabled>
                                    <label for="brand" class="font-size-13">Brand</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <span class="form-control" readonly disabled>Attachment File...</span>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-tags"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="sellerBrand" placeholder="Seller Brand" value="121212" readonly disabled>
                                    <label for="sellerBrand" class="font-size-13">Seller Brand</label>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>

            </section>
            <section class="mx-0 mx-md-2 py-2">
                <div class="card card-vendor-list">
                    <!-- Top breadcrumb -->
                    <div class="d-flex justify-content-between mb-30">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-vendor">
                                    <li class="breadcrumb-item"><a href="#">1. GENERAL</a></li>
                                    <li class="breadcrumb-item"><a href="#">CONSUMABLE</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">DP TEST MATERIALS
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <!-- Product List Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive table-product px-2">
                                <table class="table table-product-list min-width-1100">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-nowrap text-dark">
                                                #
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark text-start long-spec">
                                                Specification
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Size
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Quantity/UOM
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                    Price(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                MRP(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Disc.(%)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Total(₹)
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Counter <br>Offer
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark">
                                                Historical <br>Price
                                            </th>
                                            <th scope="col" class="text-nowrap text-dark text-start short-spec">
                                                Specs
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>1.</td>
                                            <td>
                                                <span class="text-muted">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">Lorem ipsum dolor sit amet consectetur adipisicing elit. </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">12 Pieces</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">95</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">100</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">5</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">₹ 1,140</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted text-nowrap">80</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">
                                                    <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="95 (01-Jul)">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>
                                                    95
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-block position-relative">
                                                    <span class="text-muted text-truncate view-spec"  title="this is specs from the vendor side" data-bs-toggle="modal" data-bs-target="#viewSpec">
                                                        this is specs from the vendor side ... 
                                                </span>
                                                </span>
                                                
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>


                    <!-- Search by Brand and Remarks -->
                    <div class="row mt-4 gy-2 rfq-details-top-filter">
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-pencil"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="remarks" placeholder="Remarks"
                                        readonly disabled>
                                    <label for="remarks" class="font-size-13">Remarks</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-tags"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="brand" placeholder="Brand" readonly
                                        disabled>
                                    <label for="brand" class="font-size-13">Brand</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <span class="form-control" readonly disabled>Attachment File...</span>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-tags"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="sellerBrand" placeholder="Seller Brand" value="121212" readonly disabled>
                                    <label for="sellerBrand" class="font-size-13">Seller Brand</label>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>

            </section>

            <!-- Section Remarks -->
            <section>
                <div class="mb-3">
                    <p class="fw-bold">Remarks:</p>
                    <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Nihil adipisci tempora possimus animi id aut sapiente deserunt, deleniti at voluptatum rem, perspiciatis hic asperiores molestias, nisi placeat blanditiis odio fugit.</p>
                </div>
                <div class="mb-3">
                    <p class="fw-bold">Additional Remarks:</p>
                    <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Nihil adipisci tempora possimus animi id aut sapiente deserunt, deleniti at voluptatum rem, perspiciatis hic asperiores molestias, nisi placeat blanditiis odio fugit.</p>
                </div>
            </section>

            <!-- Product more details -->
            <section class="product-option-filter">
                <div class="card shadow-none">
                    <div class="card-body">
                        <div class="row gx-3 gy-4 pt-3 justify-content-center align-items-center">
                            <div class="col-12 col-sm-auto col-xxl-2">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-geo-alt" aria-hidden="true"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control form-control-price-basis"
                                            id="priceBasis" placeholder="Price Basis" value="12" readonly disabled>
                                        <label for="priceBasis">Price Basis</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-auto col-xxl-2">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-currency-rupee" aria-hidden="true"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control form-control-payment-terms"
                                            id="paymentTerms" placeholder="Payment Terms" value="12" readonly disabled>
                                        <label for="paymentTerms">Payment Terms</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-auto col-xxl-2">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-calendar-date" aria-hidden="true"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control form-control-delivery-period"
                                            id="deliveryPeriodInDays" placeholder="Delivery Period (In Days)"
                                            value="21" readonly disabled>
                                        <label for="deliveryPeriodInDays">Delivery Period (In Days)</label>
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
                                            id="priceValidityInDays" placeholder="Price Validity (In Days)"
                                            value="21" readonly disabled>
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
                                        <input type="text" class="form-control form-control-dispatch-branch"
                                            id="dispatchBranch" placeholder="Dispatch Branch" value="Regd. Address" readonly disabled>
                                        <label for="dispatchBranch">Dispatch Branch</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-auto col-xxl-2">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-currency-exchange" aria-hidden="true"></span>
                                    </span>
                                    <div class="form-floating">
                                        <select class="form-select form-select-currency" id="updateCurrency"
                                            aria-label="Select" readonly disabled>
                                            <option value="₹" data-symbol="₹" selected="">INR (₹)</option>
                                            <option value="$" data-symbol="$">USD ($)</option>
                                            <option value="NPR" data-symbol="NPR">NPR (रु)</option>
                                        </select>
                                        <label for="updateCurrency">Currency</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row pt-3 gx-3 gy-3 justify-content-center align-items-center">
                            <div class="col-12 col-sm-auto text-center">
                                <a href="{{ route("buyer.rfq.cis-sheet", ['rfq_id'=>$rfq_id]) }}" class="ra-btn ra-btn-sm px-3 ra-btn-primary send-quote-btn">
                                    <span class="bi bi-arrow-left-short font-size-14" aria-hidden="true"></span>
                                    Back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Spec Modal -->

    <div class="modal fade" id="viewSpec" tabindex="-1" aria-labelledby="viewSpecLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-graident text-white">
                    <h2 class="modal-title font-size-12" id="viewSpecLabel">View Specs</h2>
                    <button type="button" class="btn-close font-size-10 text-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        this is specs from the vendor side 
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection