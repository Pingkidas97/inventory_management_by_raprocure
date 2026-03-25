@extends('buyer.layouts.app', ['title'=>'RFQ Details'])

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
            <!---RFQ Filter section-->
            <section class="rfq-details-top-filter mt-2 mb-2 mx-0 mx-md-2 pt-2">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="row align-items-center justify-content-xl-center flex-wrap gx-3">
                            <div class="col-6 col-md-auto mb-4">
                                <div class="input-group generate-rfq-input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-file-earmark-text"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="rfqNumber"
                                            placeholder="RFQ Number" value="RONI-25-00046" readonly disabled>
                                        <label for="rfqNumber" class="font-size-13">RFQ Number</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-auto mb-4">
                                <div class="input-group generate-rfq-input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-file-earmark-text"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="prnNumber"
                                            placeholder="PRN Number" value=" " readonly disabled>
                                        <label for="prnNumber" class="font-size-13">PRN Number</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-auto mb-4">
                                <div class="input-group generate-rfq-input-group">
                                    <span class="input-group-text">
                                        <span class="bi bi-shop"></span>
                                    </span>
                                    <div class="form-floating">
                                        <select class="form-select" id="selectBranch" aria-label="Select Branch"
                                            readonly disabled>
                                            <option value="">Select</option>
                                            <option value="1" selected>Kolkata/Newtown</option>
                                        </select>
                                        <label for="selectBranch" class="font-size-13">Branch/Unit</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-auto mb-4">
                                <div class="input-group generate-rfq-input-group" id="datepicker">
                                    <span class="input-group-text">
                                        <span class="bi bi-calendar2-date"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control dateTimepicker" id="rfqDate"
                                            placeholder="RFQ Date" value="16/06/2025" readonly disabled>
                                        <label for="rfqDate" class="font-size-13">RFQ Date</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 col-md-auto mb-4">
                                <div class="input-group generate-rfq-input-group" id="datepicker">
                                    <span class="input-group-text">
                                        <span class="bi bi-calendar2-date"></span>
                                    </span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control dateTimepicker" id="lastResponseDate"
                                            placeholder="Last Response Date" readonly disabled>
                                        <label for="lastResponseDate" class="font-size-13">Last Response
                                            Date</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-auto mb-4">
                                <a class="ra-btn btn-sm ra-btn-primary font-size-11 px-2 px-md-3" href="{{ route("buyer.rfq.active-rfq") }}">
                                    <span class="bi bi-arrow-left-square font-size-10"></span>
                                    Back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!---RFQ Vendor list section-->
            <section class="mx-0 mx-md-2 py-2">
                <div class="card card-vendor-list">
                    <div class="card-vendor-list-wrapper gap-30">
                        <!-- Vendor Left column -->
                        <div class="card-vendor-list-left-panel">
                            <h3 class="font-size-18 mb-3">Vendor List</h3>
                            <div class="card-vendor-list-search-panel mb-3">
                                <div class="card-vendor-list-search-list mt-2 scrollSection">
                                    <div class="filter-list scroll-list">
                                        <div class="filter-list-item">
                                            <p class="item-name">NEW INDIA BEARING LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>
                        <!-- Vendor right column -->
                        <div class="card-vendor-list-right-panel">
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
                                                    Quantity
                                                </div>
                                            </th>
                                            <th scope="col" class="text-nowrap">
                                                <div class="table-product-list-uom">
                                                    UOM
                                                </div>
                                            </th>
                                            <th scope="col" class="text-nowrap">
                                                <div class="table-product-list-attachment">
                                                    Attachment
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>
                                                <input type="text" name="Specification" title="" value="12"
                                                    class="form-control form-control-sm" maxlength="500"
                                                    data-bs-toggle="modal" data-bs-target="#submitSpecification"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="Size" value=""
                                                    class="form-control form-control-sm" oninput="" maxlength="255"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="quantity" value="12"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="uom" value="MT"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <div class="position-relative">
                                                    <div class="file-info" style="display: block;">
                                                        <div class="d-flex align-item-center gap-1">
                                                            <a href="javascript:void(0)">
                                                                <span
                                                                    class="display-file text-light-gery  font-size-13">Screenshot
                                                                    (5).png</span>
                                                            </a>
                                                            <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="Screenshot (5).png">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>

                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>2</td>
                                            <td>
                                                <input type="text" name="Specification" title="" value="12"
                                                    class="form-control form-control-sm" maxlength="500"
                                                    data-bs-toggle="modal" data-bs-target="#submitSpecification"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="Size" value=""
                                                    class="form-control form-control-sm" oninput="" maxlength="255"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="quantity" value="12"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="uom" value="MT"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <div class="position-relative">
                                                    <div class="file-info" style="display: block;">
                                                        <div class="d-flex align-item-center gap-1">
                                                            <a href="javascript:void(0)">
                                                                <span
                                                                    class="display-file text-light-gery  font-size-13">Screenshot
                                                                    (5).png</span>
                                                            </a>
                                                            <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="Screenshot (5).png">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>

                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>

                            </div>
                                </div>
                                </div>
                            

                            <!-- Search by Brand and Remarks -->
                            <div class="row mt-4">
                                <div class="col-md-6 mb-4">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="bi bi-tags"></span>
                                        </span>
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="brand" placeholder="Brand" readonly disabled>
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
                                            <input type="text" class="form-control" id="remarks"
                                                placeholder="Remarks" readonly disabled>
                                            <label for="remarks">Remarks</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </section>
            <section class="mx-0 mx-md-2 py-2">
                <div class="card card-vendor-list">
                    <div class="card-vendor-list-wrapper gap-30">
                        <!-- Vendor Left column -->
                        <div class="card-vendor-list-left-panel">
                            <h3 class="font-size-18 mb-3">Vendor List</h3>
                            <div class="card-vendor-list-search-panel mb-3">
                                <div class="card-vendor-list-search-list mt-2 scrollSection">
                                    <div class="filter-list scroll-list">
                                        <div class="filter-list-item">
                                            <p class="item-name">NEW INDIA BEARING LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>
                        <!-- Vendor right column -->
                        <div class="card-vendor-list-right-panel">
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
                                                    Quantity
                                                </div>
                                            </th>
                                            <th scope="col" class="text-nowrap">
                                                <div class="table-product-list-uom">
                                                    UOM
                                                </div>
                                            </th>
                                            <th scope="col" class="text-nowrap">
                                                <div class="table-product-list-attachment">
                                                    Attachment
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>
                                                <input type="text" name="Specification" title="" value="12"
                                                    class="form-control form-control-sm" maxlength="500"
                                                    data-bs-toggle="modal" data-bs-target="#submitSpecification"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="Size" value=""
                                                    class="form-control form-control-sm" oninput="" maxlength="255"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="quantity" value="12"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="uom" value="MT"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <div class="position-relative">
                                                    <div class="file-info" style="display: block;">
                                                        <div class="d-flex align-item-center gap-1">
                                                            <a href="javascript:void(0)">
                                                                <span
                                                                    class="display-file text-light-gery  font-size-13">Screenshot
                                                                    (5).png</span>
                                                            </a>
                                                            <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="Screenshot (5).png">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>

                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>2</td>
                                            <td>
                                                <input type="text" name="Specification" title="" value="12"
                                                    class="form-control form-control-sm" maxlength="500"
                                                    data-bs-toggle="modal" data-bs-target="#submitSpecification"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="Size" value=""
                                                    class="form-control form-control-sm" oninput="" maxlength="255"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="quantity" value="12"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="uom" value="MT"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <div class="position-relative">
                                                    <div class="file-info" style="display: block;">
                                                        <div class="d-flex align-item-center gap-1">
                                                            <a href="javascript:void(0)">
                                                                <span
                                                                    class="display-file text-light-gery  font-size-13">Screenshot
                                                                    (5).png</span>
                                                            </a>
                                                            <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="Screenshot (5).png">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>

                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>

                            </div>
                                </div>
                                </div>
                            

                            <!-- Search by Brand and Remarks -->
                            <div class="row mt-4">
                                <div class="col-md-6 mb-4">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="bi bi-tags"></span>
                                        </span>
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="brand" placeholder="Brand" readonly disabled>
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
                                            <input type="text" class="form-control" id="remarks"
                                                placeholder="Remarks" readonly disabled>
                                            <label for="remarks">Remarks</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </section>
            <section class="mx-0 mx-md-2 py-2">
                <div class="card card-vendor-list">
                    <div class="card-vendor-list-wrapper gap-30">
                        <!-- Vendor Left column -->
                        <div class="card-vendor-list-left-panel">
                            <h3 class="font-size-18 mb-3">Vendor List</h3>
                            <div class="card-vendor-list-search-panel mb-3">
                                <div class="card-vendor-list-search-list mt-2 scrollSection">
                                    <div class="filter-list scroll-list">
                                        <div class="filter-list-item">
                                            <p class="item-name">NEW INDIA BEARING LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>
                        <!-- Vendor right column -->
                        <div class="card-vendor-list-right-panel">
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
                                                    Quantity
                                                </div>
                                            </th>
                                            <th scope="col" class="text-nowrap">
                                                <div class="table-product-list-uom">
                                                    UOM
                                                </div>
                                            </th>
                                            <th scope="col" class="text-nowrap">
                                                <div class="table-product-list-attachment">
                                                    Attachment
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>
                                                <input type="text" name="Specification" title="" value="12"
                                                    class="form-control form-control-sm" maxlength="500"
                                                    data-bs-toggle="modal" data-bs-target="#submitSpecification"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="Size" value=""
                                                    class="form-control form-control-sm" oninput="" maxlength="255"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="quantity" value="12"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="uom" value="MT"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <div class="position-relative">
                                                    <div class="file-info" style="display: block;">
                                                        <div class="d-flex align-item-center gap-1">
                                                            <a href="javascript:void(0)">
                                                                <span
                                                                    class="display-file text-light-gery  font-size-13">Screenshot
                                                                    (5).png</span>
                                                            </a>
                                                            <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="Screenshot (5).png">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>

                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>2</td>
                                            <td>
                                                <input type="text" name="Specification" title="" value="12"
                                                    class="form-control form-control-sm" maxlength="500"
                                                    data-bs-toggle="modal" data-bs-target="#submitSpecification"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="Size" value=""
                                                    class="form-control form-control-sm" oninput="" maxlength="255"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="quantity" value="12"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="uom" value="MT"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <div class="position-relative">
                                                    <div class="file-info" style="display: block;">
                                                        <div class="d-flex align-item-center gap-1">
                                                            <a href="javascript:void(0)">
                                                                <span
                                                                    class="display-file text-light-gery  font-size-13">Screenshot
                                                                    (5).png</span>
                                                            </a>
                                                            <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="Screenshot (5).png">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>

                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>

                            </div>
                                </div>
                                </div>
                            

                            <!-- Search by Brand and Remarks -->
                            <div class="row mt-4">
                                <div class="col-md-6 mb-4">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="bi bi-tags"></span>
                                        </span>
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="brand" placeholder="Brand" readonly disabled>
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
                                            <input type="text" class="form-control" id="remarks"
                                                placeholder="Remarks" readonly disabled>
                                            <label for="remarks">Remarks</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </section>
            <section class="mx-0 mx-md-2 py-2">
                <div class="card card-vendor-list">
                    <div class="card-vendor-list-wrapper gap-30">
                        <!-- Vendor Left column -->
                        <div class="card-vendor-list-left-panel">
                            <h3 class="font-size-18 mb-3">Vendor List</h3>
                            <div class="card-vendor-list-search-panel mb-3">
                                <div class="card-vendor-list-search-list mt-2 scrollSection">
                                    <div class="filter-list scroll-list">
                                        <div class="filter-list-item">
                                            <p class="item-name">NEW INDIA BEARING LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>
                        <!-- Vendor right column -->
                        <div class="card-vendor-list-right-panel">
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
                                                    Quantity
                                                </div>
                                            </th>
                                            <th scope="col" class="text-nowrap">
                                                <div class="table-product-list-uom">
                                                    UOM
                                                </div>
                                            </th>
                                            <th scope="col" class="text-nowrap">
                                                <div class="table-product-list-attachment">
                                                    Attachment
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>
                                                <input type="text" name="Specification" title="" value="12"
                                                    class="form-control form-control-sm" maxlength="500"
                                                    data-bs-toggle="modal" data-bs-target="#submitSpecification"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="Size" value=""
                                                    class="form-control form-control-sm" oninput="" maxlength="255"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="quantity" value="12"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="uom" value="MT"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <div class="position-relative">
                                                    <div class="file-info" style="display: block;">
                                                        <div class="d-flex align-item-center gap-1">
                                                            <a href="javascript:void(0)">
                                                                <span
                                                                    class="display-file text-light-gery  font-size-13">Screenshot
                                                                    (5).png</span>
                                                            </a>
                                                            <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="Screenshot (5).png">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>

                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>2</td>
                                            <td>
                                                <input type="text" name="Specification" title="" value="12"
                                                    class="form-control form-control-sm" maxlength="500"
                                                    data-bs-toggle="modal" data-bs-target="#submitSpecification"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="Size" value=""
                                                    class="form-control form-control-sm" oninput="" maxlength="255"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="quantity" value="12"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="uom" value="MT"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <div class="position-relative">
                                                    <div class="file-info" style="display: block;">
                                                        <div class="d-flex align-item-center gap-1">
                                                            <a href="javascript:void(0)">
                                                                <span
                                                                    class="display-file text-light-gery  font-size-13">Screenshot
                                                                    (5).png</span>
                                                            </a>
                                                            <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="Screenshot (5).png">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>

                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>

                            </div>
                                </div>
                                </div>
                            

                            <!-- Search by Brand and Remarks -->
                            <div class="row mt-4">
                                <div class="col-md-6 mb-4">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="bi bi-tags"></span>
                                        </span>
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="brand" placeholder="Brand" readonly disabled>
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
                                            <input type="text" class="form-control" id="remarks"
                                                placeholder="Remarks" readonly disabled>
                                            <label for="remarks">Remarks</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </section>
            <section class="mx-0 mx-md-2 py-2">
                <div class="card card-vendor-list">
                    <div class="card-vendor-list-wrapper gap-30">
                        <!-- Vendor Left column -->
                        <div class="card-vendor-list-left-panel">
                            <h3 class="font-size-18 mb-3">Vendor List</h3>
                            <div class="card-vendor-list-search-panel mb-3">
                                <div class="card-vendor-list-search-list mt-2 scrollSection">
                                    <div class="filter-list scroll-list">
                                        <div class="filter-list-item">
                                            <p class="item-name">NEW INDIA BEARING LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">GURU RAPROCURE PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">AMIT RAP</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">WIPRO STEEL LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">BK PVT LTD</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Maa Steel</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Zoho</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">TVT PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">SEO PVT. LTD.</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                        <div class="filter-list-item">
                                            <p class="item-name">Amit Enterprises</p>
                                            <p class="item-contact">9836453456</p>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>
                        <!-- Vendor right column -->
                        <div class="card-vendor-list-right-panel">
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
                                                    Quantity
                                                </div>
                                            </th>
                                            <th scope="col" class="text-nowrap">
                                                <div class="table-product-list-uom">
                                                    UOM
                                                </div>
                                            </th>
                                            <th scope="col" class="text-nowrap">
                                                <div class="table-product-list-attachment">
                                                    Attachment
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>
                                                <input type="text" name="Specification" title="" value="12"
                                                    class="form-control form-control-sm" maxlength="500"
                                                    data-bs-toggle="modal" data-bs-target="#submitSpecification"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="Size" value=""
                                                    class="form-control form-control-sm" oninput="" maxlength="255"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="quantity" value="12"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="uom" value="MT"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <div class="position-relative">
                                                    <div class="file-info" style="display: block;">
                                                        <div class="d-flex align-item-center gap-1">
                                                            <a href="javascript:void(0)">
                                                                <span
                                                                    class="display-file text-light-gery  font-size-13">Screenshot
                                                                    (5).png</span>
                                                            </a>
                                                            <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="Screenshot (5).png">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>

                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>2</td>
                                            <td>
                                                <input type="text" name="Specification" title="" value="12"
                                                    class="form-control form-control-sm" maxlength="500"
                                                    data-bs-toggle="modal" data-bs-target="#submitSpecification"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="Size" value=""
                                                    class="form-control form-control-sm" oninput="" maxlength="255"
                                                    readonly disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="quantity" value="12"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <input type="text" name="uom" value="MT"
                                                    class="form-control form-control-sm" maxlength="10" readonly
                                                    disabled>
                                            </td>
                                            <td>
                                                <div class="position-relative">
                                                    <div class="file-info" style="display: block;">
                                                        <div class="d-flex align-item-center gap-1">
                                                            <a href="javascript:void(0)">
                                                                <span
                                                                    class="display-file text-light-gery  font-size-13">Screenshot
                                                                    (5).png</span>
                                                            </a>
                                                            <button type="button"
                                                                class="btn btn-link p-0 text-light-gery"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="Screenshot (5).png">
                                                                <span
                                                                    class="bi bi-info-circle-fill font-size-13 "></span>
                                                            </button>

                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>

                            </div>
                                </div>
                                </div>
                            

                            <!-- Search by Brand and Remarks -->
                            <div class="row mt-4">
                                <div class="col-md-6 mb-4">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <span class="bi bi-tags"></span>
                                        </span>
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="brand" placeholder="Brand" readonly disabled>
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
                                            <input type="text" class="form-control" id="remarks"
                                                placeholder="Remarks" readonly disabled>
                                            <label for="remarks">Remarks</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                                <input type="text" class="form-control" id="priceBasis" placeholder="Price Basis"
                                    readonly disabled>
                                <label for="priceBasis">Price Basis</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-currency-rupee"></span>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="paymentTerm" placeholder="Payment Term"
                                    readonly disabled>
                                <label for="paymentTerm">Payment Term</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-2 mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-calendar2-date"></span>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="deliveryPeriod"
                                    placeholder="Delivery Period (In Days)" readonly disabled>
                                <label for="deliveryPeriod">Delivery Period (In Days)</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <span class="bi bi-patch-check"></span>
                            </span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="guranteeWarranty"
                                    placeholder="Gurantee/Warranty" readonly disabled>
                                <label for="guranteeWarranty">Gurantee/Warranty</label>
                            </div>
                        </div>
                    </div>
                </div>

            </section>
        </div>
    </main>

@endsection

@section('scripts')
@endsection