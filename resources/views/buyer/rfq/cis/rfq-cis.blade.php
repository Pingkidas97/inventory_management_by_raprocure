@extends('buyer.layouts.app', ['title'=>'CIS Sheet'])

@section('css')
    <link rel="stylesheet" href="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.css') }}" />
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar-menu')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1">
        <div class="container-fluid">
            <!---CIS Statemant section-->
            <section class="card rounded">
                <div class="card-header bg-white">
                    <div class="row gy-3 justify-content-between align-items-center py-3 px-0 px-md-3 mb-30">
                        <div class="col-12 col-sm-auto order-2 order-sm-1">
                            <h1 class="text-primary-blue font-size-27">Comparative Information Statement</h1>
                        </div>
                        <div class="col-12 col-sm-auto order-1 order-sm-2">
                            <div
                                class="row gx-3 gy-2 align-items-center justify-content-center justify-content-sm-end">
                                <div class="col-auto">
                                    <button type="button" class="ra-btn ra-btn-outline-primary px-2 font-size-11"
                                        data-bs-toggle="modal" data-bs-target="#checkLastCisPoModal">
                                    Check Last CIS/PO
                                    </button>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="ra-btn ra-btn-outline-primary px-2 font-size-11">
                                    <span class="bi bi-download font-size-12" aria-hidden="true"></span>
                                    Download CIS
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="cis-info pb-2 px-0 px-md-3  d-none d-sm-flex">
                        <div class="cis-info-left">
                            <ul>
                                <li>RFQ No. : {{$rfq_id}}</li>
                                <li>PRN Number:</li>
                                <li>Branch/Unit Details : Mumbai /Pune</li>
                                <li>Last Date to Response:</li>
                                <li>Last Edited Date: 17/06/2025</li>
                            </ul>
                        </div>
                        <div class="cis-info-right">
                            RFQ Date: 18/06/2025
                        </div>
                    </div>
                    <div class="cis-filter gx-3 gy-2 py-2 px-2 mb-4 mx-0 mx-md-3 d-none d-sm-flex">
                        <div class="cis-filter-item flex-lg-fill">
                            <select class="form-select" aria-label="Default select example " name="sort-price"
                                id="sortPrice">
                                <option value="">Sort By Price</option>
                                <option value="">Lowest Price</option>
                                <option value="">Highest Price</option>
                                <option value="">Delivery Period</option>
                            </select>
                        </div>
                        <div class="cis-filter-item flex-lg-fill">
                            <input type="text" class="form-control dateTimePickerStart" id="fromDate"
                                placeholder="From Date">
                            <label for="fromDate" class="visually-hidden-focusable">From Date</label>
                        </div>
                        <div class="cis-filter-item flex-lg-fill">
                            <input type="text" class="form-control dateTimePickerEnd" id="toDate"
                                placeholder="To Date">
                            <label for="toDate" class="visually-hidden-focusable">From End</label>
                        </div>
                        <div class="cis-filter-item flex-lg-fill">
                            <div class="dropdown">
                                <button id="dropdownButtonLocation"
                                    class="btn btn-outline-default custom-multiselect-dropdown-btn dropdown-toggle justify-content-between"
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Select Location
                                </button>
                                <div class="dropdown-menu custom-multiselect-dropdown-menu">
                                    <div class="sticky-top-option">
                                        <div class="mt-1">
                                            <label class="ra-custom-checkbox mb-0">
                                            <input type="checkbox">
                                            <span class="font-size-11">Select All</span>
                                            <span class="checkmark "></span>
                                            </label>
                                        </div>
                                    </div>
                                    <ul class="filter-list scroll-list p-0">
                                        <li>
                                            <div class="mt-1">
                                                <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11">Assam</span>
                                                <span class="checkmark "></span>
                                                </label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="mt-1">
                                                <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11">Delhi</span>
                                                <span class="checkmark "></span>
                                                </label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="mt-1">
                                                <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11">Goa</span>
                                                <span class="checkmark "></span>
                                                </label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="mt-1">
                                                <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11">Haryana</span>
                                                <span class="checkmark "></span>
                                                </label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="mt-1">
                                                <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11">Jharkhand</span>
                                                <span class="checkmark "></span>
                                                </label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="mt-1">
                                                <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11">Karnataka</span>
                                                <span class="checkmark "></span>
                                                </label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="mt-1">
                                                <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11">Kerala</span>
                                                <span class="checkmark "></span>
                                                </label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="cis-filter-item flex-lg-fill">
                            <div class="dropdown">
                                <button id="dropdownButtonFavorite"
                                    class="btn btn-outline-default custom-multiselect-dropdown-btn dropdown-toggle justify-content-between"
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Select Favorite
                                </button>
                                <div class="dropdown-menu custom-multiselect-dropdown-menu">
                                    <div class="sticky-top-option">
                                        <div class="mt-1">
                                            <label class="ra-custom-checkbox mb-0">
                                            <input type="checkbox">
                                            <span class="font-size-11 ra-custom-checkbox-label">Select
                                            All</span>
                                            <span class="checkmark "></span>
                                            </label>
                                        </div>
                                    </div>
                                    <ul class="filter-list scroll-list p-0">
                                        <li>
                                            <div class="mt-1">
                                                <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11 ra-custom-checkbox-label">RONIT VENDOR
                                                PROFILE COMPANY QWE</span>
                                                <span class="checkmark "></span>
                                                </label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="mt-1">
                                                <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11 ra-custom-checkbox-label">GURU VENDOR
                                                PVT AND LTD</span>
                                                <span class="checkmark "></span>
                                                </label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="mt-1">
                                                <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11 ra-custom-checkbox-label">MY RAPROCURE
                                                VENDOR PVT LTD</span>
                                                <span class="checkmark "></span>
                                                </label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="mt-1">
                                                <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11 ra-custom-checkbox-label">ABC GURU
                                                TEST VENDOR PVT LTD</span>
                                                <span class="checkmark "></span>
                                                </label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="cis-filter-item flex-lg-fill">
                            <div class="dropdown">
                                <button id="dropdownButtonLastVendor"
                                    class="btn btn-outline-default custom-multiselect-dropdown-btn dropdown-toggle justify-content-between"
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Select Last Vendor
                                </button>
                                <div class="dropdown-menu custom-multiselect-dropdown-menu">
                                    <div class="sticky-top-option">
                                        <div class="mt-1">
                                            <label class="ra-custom-checkbox mb-0">
                                            <input type="checkbox">
                                            <span class="font-size-11 ra-custom-checkbox-label">Select
                                            All</span>
                                            <span class="checkmark "></span>
                                            </label>
                                        </div>
                                    </div>
                                    <ul class="filter-list scroll-list p-0">
                                        <li>
                                            <div class="mt-1">
                                                <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11 ra-custom-checkbox-label">RONIT VENDOR
                                                PROFILE COMPANY QWE</span>
                                                <span class="checkmark "></span>
                                                </label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="mt-1">
                                                <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11 ra-custom-checkbox-label">GURU VENDOR
                                                PVT AND LTD</span>
                                                <span class="checkmark "></span>
                                                </label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="mt-1">
                                                <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11 ra-custom-checkbox-label">MY RAPROCURE
                                                VENDOR PVT LTD</span>
                                                <span class="checkmark "></span>
                                                </label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="mt-1">
                                                <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11 ra-custom-checkbox-label">ABC GURU
                                                TEST VENDOR PVT LTD</span>
                                                <span class="checkmark "></span>
                                                </label>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="cis-filter-item">
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="ra-btn ra-btn-sm px-3 ra-btn-primary">
                                <span class="bi bi-search font-size-14" aria-hidden="true"></span>
                                Search
                                </button>
                                <button type="button" class="ra-btn ra-btn-sm px-3 ra-btn-outline-danger">
                                Reset
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="cis-details py-3 px-0 px-md-3 d-none d-sm-block">
                        <div class="row g-0 gy-5">
                            <div class="col-6 col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-bordered border-dark cis-table-left">
                                        <thead>
                                            <tr>
                                                <th colspan="3" class="border-right-0 p-0 align-bottom">
                                                    <div class="p-3">
                                                        <div class="mb-4">
                                                            <h2 class="cis-table-left-heading text-primary-blue ">
                                                                Your
                                                                Exclusive Automated CIS
                                                            </h2>
                                                            <h3
                                                                class="font-size-10 fw-bold text-danger-red p-0 m-0">
                                                                NOTE: These are updated Rates post AUCTION that was
                                                                held on 18/06/2025
                                                            </h3>
                                                        </div>
                                                        <nav aria-label="breadcrumb">
                                                            <ol class="breadcrumb breadcrumb-cis">
                                                                <li class="breadcrumb-item"><a href="#">CCM</a></li>
                                                                <li class="breadcrumb-item active"
                                                                    aria-current="page">CONSUMABLE
                                                                </li>
                                                            </ol>
                                                        </nav>
                                                    </div>
                                                </th>
                                                <th colspan="2" class="border-left-0 p-0 align-bottom">
                                                    <div class="cis-table-headings text-end text-nowrap px-2">
                                                        <p>Vendor's Name</p>
                                                        <p>Vendor's Contact</p>
                                                        <p>No. of Product Quoted</p>
                                                        <p>Latest Quote Received on</p>
                                                        <p class="quote-heading">Quotation Page</p>
                                                    </div>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th scope="col" class="p-2 cis-table-left-w-200">Product</th>
                                                <th scope="col" class="p-2 text-nowrap">Specifications</th>
                                                <th scope="col" class="p-2 text-nowrap">Size</th>
                                                <th scope="col" class="p-2 text-nowrap">Quantity/UOM</th>
                                                <th scope="col" class="p-2 text-nowrap">Counter Offer</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="align-middle p-1 text-nowrap position-relative" scope="row">
                                                    <span class="name-tooltip">
                                                    PNEUMATIC CYLINDER
                                                    </span>
                                                    <span role="button" type="button" class="p-0 infoIcon"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="PNEUMATIC CYLINDER OPERATED PULP KNIFE GATE VALVE">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-14" aria-hidden="true"></span>
                                                    </span>
                                                </td>
                                                <td class="align-middle p-1">
                                                    <span class="name-tooltip">
                                                    we Formats a
                                                    </span>
                                                    <span role="button" type="button" class="p-0 infoIcon"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="weFormats a HTML string/file with your desired indentation level. The formatting rules are not configurable but are already optimized for the best possible output. Note that the formatter will keep spaces and tabs between content tags such as div and span as it's considered to be valid content.">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-14" aria-hidden="true"></span>
                                                    </span>                                                    
                                                </td>
                                                <td class="align-middle p-1">
                                                    <span class="name-tooltip">
                                                    we Formats
                                                    </span>
                                                    <span role="button" type="button" class="p-0 infoIcon"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="weFormats a HTML string/file with your desired indentation level. The formatting rules are not configurable but are already optimized for the best possible output. Note that the formatter will keep spaces and tabs between content tags such as div and span as it's considered to be valid content.">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-14" aria-hidden="true"></span>
                                                    </span>
                                                </td>
                                                <td class="align-middle p-1">12 Metre</td>
                                                <td class="align-middle p-1"></td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1 bg-pink text-uppercase fw-bold"
                                                    scope="row">
                                                    Total
                                                </td>
                                                <td class="align-middle p-1" colspan="3"></td>
                                                <td class="align-middle p-1"></td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1" scope="row">Price Basis</td>
                                                <td class="align-middle p-1" colspan="3"></td>
                                                <td class="align-middle p-1"></td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1" scope="row">Payment Terms</td>
                                                <td class="align-middle p-1" colspan="3"></td>
                                                <td class="align-middle p-1"></td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1" scope="row">Delivery Period</td>
                                                <td class="align-middle p-1" colspan="3"></td>
                                                <td class="align-middle p-1"></td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1" scope="row">Seller Brand</td>
                                                <td class="align-middle p-1" colspan="3"></td>
                                                <td class="align-middle p-1"></td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1" scope="row">Remarks</td>
                                                <td class="align-middle p-1" colspan="3"></td>
                                                <td class="align-middle p-1"></td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1" scope="row">Additional Remarks </td>
                                                <td class="align-middle p-1" colspan="3"></td>
                                                <td class="align-middle p-1"></td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1" scope="row">Technical Approval</td>
                                                <td class="align-middle p-1" colspan="3"></td>
                                                <td class="align-middle p-1"></td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1 text-nowrap" scope="row">Technical Approval Remarks
                                                </td>
                                                <td class="align-middle p-1" colspan="3"></td>
                                                <td class="align-middle p-1"></td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle p-1 bg-pink fw-bold" scope="row"
                                                    colspan="5">
                                                    Company Information
                                                </td>
                                            </tr>
                                            <tr class="toggle-row">
                                                <td class="align-middle p-1" scope="row">Vintage</td>
                                                <td class="align-middle p-1" colspan="3"></td>
                                                <td class="align-middle p-1"></td>
                                            </tr>
                                            <tr class="toggle-row">
                                                <td class="align-middle p-1" scope="row">Business Type</td>
                                                <td class="align-middle p-1" colspan="3"></td>
                                                <td class="align-middle p-1"></td>
                                            </tr>
                                            <tr class="toggle-row">
                                                <td class="align-middle p-1" scope="row">Main Products</td>
                                                <td class="align-middle p-1" colspan="3"></td>
                                                <td class="align-middle p-1"></td>
                                            </tr>
                                            <tr class="toggle-row">
                                                <td class="align-middle p-1" scope="row">Client</td>
                                                <td class="align-middle p-1" colspan="3"></td>
                                                <td class="align-middle p-1"></td>
                                            </tr>
                                            <tr class="toggle-row">
                                                <td class="align-middle p-1 text-nowrap" scope="row">
                                                    Certifications-MSME/ISO
                                                </td>
                                                <td class="align-middle p-1" colspan="3"></td>
                                                <td class="align-middle p-1"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-6 col-md-6 position-relative">
                                <div class="d-flex justify-content-between prev-next-table-space d-none d-sm-flex">
                                    <button type="button" id="scrollLeft"
                                        class="btn btn-link min-width-inherit height-inherit p-0">
                                    <span class="visually-hidden-focusable">scroll left</span>
                                    <span class="bi bi-arrow-left font-size-20 text-primary-blue"
                                        aria-hidden="true"> </span>
                                    </button>
                                    <button type="button" id="scrollRight"
                                        class="btn btn-link min-width-inherit height-inherit p-0">
                                    <span class="visually-hidden-focusable">scroll right</span>
                                    <span class="bi bi-arrow-right font-size-20 text-primary-blue"
                                        aria-hidden="true"> </span>
                                    </button>
                                </div>
                                <div class="table-responsive" id="tableScrollContainer">
                                    <table
                                        class="table table-bordered border-dark cis-vendor-table cis-table-mobile">
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    <div class="cis-vendor-table-heading text-center">
                                                        <p>
                                                            <a href="javascript:void(0)" target="_blank"
                                                                class="font-size-13 cursor-pointer"
                                                                title="VENDOR RON">
                                                            VENDOR RON
                                                            </a>
                                                            <span class="font-size-13">
                                                            <input type="checkbox">
                                                            </span>
                                                        </p>
                                                        <p>+977 7894561235</p>
                                                        <p>100.00% (1/1)</p>
                                                        <p>18/06/2025</p>
                                                    </div>
                                                </th>
                                                <th scope="col">
                                                    <div class="cis-vendor-table-heading text-center">
                                                        <p>
                                                            <a href="javascript:void(0)" target="_blank"
                                                                class="font-size-13 cursor-pointer"
                                                                title="RONIT VENDOR">
                                                            RONIT VENDOR
                                                            </a>
                                                            <span class="font-size-13">
                                                            <input type="checkbox">
                                                            </span>
                                                        </p>
                                                        <p>+91 8949849849</p>
                                                        <p>100.00% (1/1)</p>
                                                        <p>18/06/2025</p>
                                                    </div>
                                                </th>
                                                <th scope="col">
                                                    <div class="position-relative">
                                                        <div class="cis-vendor-notification">
                                                            <a href="javascript:void(0)"
                                                                class="send-reminder-notification"
                                                                title="Remind the Vendor"><span
                                                                class=" ml-10 bi bi-bell"></span></a>
                                                        </div>
                                                    </div>
                                                    <div class="cis-vendor-table-heading text-center">
                                                        <p>
                                                            <a href="javascript:void(0)" target="_blank"
                                                                class="font-size-13 cursor-pointer"
                                                                title="WIPRO STEEL">
                                                            WIPRO STEEL
                                                            </a>
                                                            <span class="font-size-13">
                                                            <input type="checkbox">
                                                            </span>
                                                        </p>
                                                        <p>+91 8974561234</p>
                                                        <p>100.00% (1/1)</p>
                                                    </div>
                                                </th>
                                                <th scope="col">
                                                    <div class="cis-vendor-table-heading text-center">
                                                        <p>
                                                            <a href="javascript:void(0)" target="_blank"
                                                                class="font-size-13 cursor-pointer"
                                                                title="AMUL PVT LTD">
                                                            AMUL PVT LTD
                                                            </a>
                                                            <span class="font-size-13">
                                                            <input type="checkbox">
                                                            </span>
                                                        </p>
                                                        <p>+91 7894561235</p>
                                                        <p>100.00% (1/1)</p>
                                                        <p>18/06/2025</p>
                                                    </div>
                                                </th>
                                                <th scope="col">
                                                    <div class="cis-vendor-table-heading text-center">
                                                        <p>
                                                            <a href="javascript:void(0)" target="_blank"
                                                                class="font-size-13 cursor-pointer"
                                                                title="MY RAPROCURE">
                                                            MY RAPROCURE
                                                            </a>
                                                            <span class="font-size-13">
                                                            <input type="checkbox">
                                                            </span>
                                                        </p>
                                                        <p>+91 9645312587</p>
                                                        <p>100.00% (1/1)</p>
                                                    </div>
                                                </th>
                                                <th scope="col">
                                                    <div class="cis-vendor-table-heading text-center">
                                                        <p>
                                                            <a href="javascript:void(0)" target="_blank"
                                                                class="font-size-13 cursor-pointer"
                                                                title="VISHWAKARMA">
                                                            VISHWAKARMA
                                                            </a>
                                                            <span class="font-size-13">
                                                            <input type="checkbox">
                                                            </span>
                                                        </p>
                                                        <p>+91 7894561235</p>
                                                        <p>100.00% (1/1)</p>
                                                    </div>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th scope="col" class="text-center p-2 bg-white">
                                                    <a target="_blank" href="{{ route('buyer.rfq.quotation-received', ['rfq_id' => $rfq_id, 'vendor_id' => 1]) }}"
                                                        class="text-decoration-underline text-primary-blue"> View Quotation </a>
                                                </th>
                                                <th scope="col" class="text-center p-2 bg-white">
                                                    <a target="_blank" href="{{ route('buyer.rfq.quotation-received', ['rfq_id' => $rfq_id, 'vendor_id' => 1]) }}"
                                                        class="text-decoration-underline text-primary-blue"> View Quotation </a>
                                                </th>
                                                <th scope="col" class="text-center p-2 bg-white">
                                                    <a target="_blank" href="{{ route('buyer.rfq.quotation-received', ['rfq_id' => $rfq_id, 'vendor_id' => 1]) }}"
                                                        class="text-decoration-underline text-primary-blue"> View Quotation </a>
                                                </th>
                                                <th scope="col" class="text-center p-2 bg-white">
                                                    <a target="_blank" href="{{ route('buyer.rfq.quotation-received', ['rfq_id' => $rfq_id, 'vendor_id' => 1]) }}"
                                                        class="text-decoration-underline text-primary-blue"> View Quotation </a>
                                                </th>
                                                <th scope="col" class="text-center p-2 bg-white">
                                                    <a target="_blank" href="{{ route('buyer.rfq.quotation-received', ['rfq_id' => $rfq_id, 'vendor_id' => 1]) }}"
                                                        class="text-decoration-underline text-primary-blue"> View Quotation </a>
                                                </th>
                                                <th scope="col" class="text-center p-2 bg-white">
                                                    <a target="_blank" href="{{ route('buyer.rfq.quotation-received', ['rfq_id' => $rfq_id, 'vendor_id' => 1]) }}"
                                                        class="text-decoration-underline text-primary-blue"> View Quotation </a>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th scope="col" class="text-center p-2 bg-white">Rate (₹)</th>
                                                <th scope="col" class="text-center p-2 bg-white">Rate (₹)</th>
                                                <th scope="col" class="text-center p-2 bg-white">Rate (₹)</th>
                                                <th scope="col" class="text-center p-2 bg-white">Rate (₹)</th>
                                                <th scope="col" class="text-center p-2 bg-white">Rate (₹)</th>
                                                <th scope="col" class="text-center p-2 bg-white">Rate (₹)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="product-price p-1 align-middle bg-gold">
                                                    <div
                                                        class="d-flex justify-content-center align-items-center gap-4">
                                                        <div>
                                                            <span role="button" type="button" class="p-0"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="60 (18-Jun)">
                                                            <span
                                                                class="bi bi-info-circle-fill text-dark font-size-11"
                                                                aria-hidden="true"></span>
                                                            </span>
                                                        </div>
                                                        <div>
                                                            60
                                                            <!-- This Checkbox will show when Buyer click Proceed to order Button -->
                                                            <span class="font-size-13 d-none">
                                                            <input type="checkbox">
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="product-price p-1 align-middle">
                                                    <div
                                                        class="d-flex justify-content-center align-items-center gap-4">
                                                        <div>
                                                            <span role="button" type="button" class="p-0"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                title="75 (18-Jun)">
                                                            <span
                                                                class="bi bi-info-circle-fill text-dark font-size-11"
                                                                aria-hidden="true"></span>
                                                            </span>
                                                        </div>
                                                        <div>75</div>
                                                    </div>
                                                </td>
                                                <td class="product-price p-1 align-middle">
                                                </td>
                                                <td class="product-price p-1 align-middle">
                                                </td>
                                                <td class="product-price p-1 align-middle">
                                                </td>
                                                <td class="product-price p-1 align-middle">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="product-price p-1 align-middle text-center bg-gold">
                                                    ₹ 720
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    ₹ 900
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    ₹ 0
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    ₹ 0
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    ₹ 0
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    ₹ 0
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="product-price p-1 align-middle text-center">
                                                    5000
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    123
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="Online Payment">Online pay</span> <span
                                                        role="button" type="button" class="p-0"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Online Payment">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    </span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="123">123</span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="">50 Days</span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="">123 Days</span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="testing br">testing br</span> <span role="button"
                                                        type="button" class="p-0" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="Formats a HTML string/file with your desired indentation level. The formatting rules are not configurable but are already optimized for the best possible output. Note that the formatter will keep spaces and tabs between content tags such as div and span as it's considered to be valid content. Formats a HTML string/file with your desired indentation level. The formatting rules are not configurable but are already optimized for the best possible output. Note that the formatter will keep spaces and tabs between content tags such as div and span as it's considered to be valid content. Formats a HTML string/file with your desired indentation level. The formatting rules are not configurable but are already optimized for the best possible output. Note that the formatter will keep spaces and tabs between content tags such as div and span as it's considered to be valid content.">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    </span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="testing br">testing br</span> <span role="button"
                                                        type="button" class="p-0" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="Formats a HTML string/file with your desired indentation level. The formatting rules are not configurable but are already optimized for the best possible output. Note that the formatter will keep spaces and tabs between content tags such as div and span as it's considered to be valid content. Formats a HTML string/file with your desired indentation level. The formatting rules are not configurable but are already optimized for the best possible output. Note that the formatter will keep spaces and tabs between content tags such as div and span as it's considered to be valid content. Formats a HTML string/file with your desired indentation level. The formatting rules are not configurable but are already optimized for the best possible output. Note that the formatter will keep spaces and tabs between content tags such as div and span as it's considered to be valid content.">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    </span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="remakrs de">remakrs de</span> <span role="botton"
                                                        type="button" class="p-0" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="remarks de">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    <span class="visually-hidden-focusable">info</span>
                                                    </span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="testing re">testing re</span> <span role="button"
                                                        type="button" class="p-0" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="testing re">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    </span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="remakrs de">remakrs de</span> <span role="button"
                                                        type="button" class="p-0" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="remarks de">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    </span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="testing ad">testing ad</span> <span role="button"
                                                        type="button" class="p-0" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="testing ad">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    </span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span role="button" type="button" class="p-0"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewTechApprovalModal"
                                                        title="Technical Approval">
                                                    <span class="bi bi-eye-fill text-dark font-size-14"
                                                        aria-hidden="true"></span>
                                                    </span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" class="bg-pink ps-2 align-middle">
                                                    <span role="button" type="button" class="toggle-row-button p-0">
                                                    <span class="bi bi-chevron-up text-dark"></span>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr class="toggle-row">
                                                <td class="product-price p-1 align-middle text-center">
                                                    28 Year
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    37 Year
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    0 Year
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    0 Year
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    125 Year
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    124 Year
                                                </td>
                                            </tr>
                                            <tr class="toggle-row">
                                                <td class="product-price p-1 align-middle text-center">
                                                    Manufacturer
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    Trader
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    Manufacturer
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    Trader
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    Manufacturer
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    Trader
                                                </td>
                                            </tr>
                                            <tr class="toggle-row">
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="SHOULDER BOL">SHOULDER BOL</span>
                                                    <span role="button" type="button" class="p-0"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Shoulder Bol">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    </span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="RESIN CEMENT">RESIN CEMENT</span>
                                                    <span role="button" type="button" class="p-0"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Resin Cement">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    </span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="TYRES,EARTHM">TYRES,EARTHM</span>
                                                    <span role="button" type="button" class="p-0"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Tyres, Earth">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    </span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="HYDRAULIC CY">HYDRAULIC CY</span>
                                                    <span role="button" type="button" class="p-0"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Hydraulic CY">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    </span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="COMPENSATING">COMPENSATING</span>
                                                    <span role="button" type="button" class="p-0"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Compensating">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    </span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="TYRES,EARTHM">TYRES,EARTHM</span>
                                                    <span role="button" type="button" class="p-0"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Tyres,Earthm">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr class="toggle-row">
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="Ankit,Saurba">Ankit,Saurba </span><span
                                                        role="button" type="button" class="p-0"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Ankit,Saurba">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    </span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="12,12">12,12</span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="amit,s">amit,s</span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="amit,s">amit,s</span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="a,b">a,b</span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="Jeevaka indu">Jeevaka indu</span>
                                                </td>
                                            </tr>
                                            <tr class="toggle-row">
                                                <td class="product-price p-1 align-middle text-center">
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title="894279879872">894279879872</span> <span
                                                        role="button" type="button" class="p-0"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="894279879872">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    </span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                                <td class="product-price p-1 align-middle text-center">
                                                    <span title=""></span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Start of CIS details Mobile only -->
                    <div class="cis-details cis-details-mobile d-sm-none">
                        <div class="cis-details-mobile-wrapper">
                            <div
                                class="card-header border-0 bg-white d-flex align-items-center justify-content-between py-3">
                                <button class="cis-mobile-toggle-button ra-btn btn-show-hide bg-white w-100 justify-content-start">
                                    <h2 class="font-size-14 fw-bold text-primary-blue">COPPER MOULD TUBE</h2>
                                    <span id="toggleIcon" class="toggle-icon bi bi-chevron-up"></span>
                                </button>
                            </div>
                            <div class="cis-details-mobile-wrapper-content">
                                <div>
                                    <h3 class="font-size-14 fw-bold">Specifications</h3>
                                    <div class="mb-3">Lorem Ipsum is simply dummy text of the printing and
                                        typesetting industry
                                    </div>
                                    <p><strong>Size:</strong> 12 &nbsp; &nbsp; | &nbsp; &nbsp;
                                        <strong>Quantity/UOM:</strong> 100 Pieces
                                    </p>
                                    <div class="list-of-vendors mt-4">
                                        <ul
                                            class="list-of-vendors-heading border-bottom-0 rounded-0 rounded-top rounded-right bg-light py-2">
                                            <li class="d-flex">
                                                <span class="vendor-name fw-bold">Vendor Name</span>
                                                <span class="vendor-price fw-bold">Rate (₹)</span>
                                            </li>
                                        </ul>
                                        <ul class="rounded-0 rounded-bottom rounded-left">
                                            <li class="d-flex">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="RONIT VENDOR">
                                                RONIT VENDOR
                                                </a>
                                                </span>
                                                <span class="vendor-price">600</span>
                                            </li>
                                            <li class="d-flex">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="NARSHINGH ENTERPRISE">
                                                NARSHINGH EN
                                                </a>
                                                </span>
                                                <span class="vendor-price">550</span>
                                            </li>
                                            <li class="d-flex">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="Wipro Steel">
                                                WIPRO STEEL
                                                </a>
                                                </span>
                                                <span class="vendor-price">720</span>
                                            </li>
                                            <li class="toggle-vendor-list">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="CISCO">
                                                CISCO
                                                </a>
                                                </span>
                                                <span class="vendor-price">520</span>
                                            </li>
                                            <li class="toggle-vendor-list">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="New India BE">
                                                New India BE
                                                </a>
                                                </span>
                                                <span class="vendor-price">640</span>
                                            </li>
                                            <li class="toggle-vendor-list">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="Guru RaProcure">
                                                Guru RaProcure
                                                </a>
                                                </span>
                                                <span class="vendor-price">480</span>
                                            </li>
                                        </ul>
                                        <div
                                            class="show-more d-flex align-items-center justify-content-center py-2">
                                            <span role="button" type="button"
                                                class="toggle-show-more-button d-flex align-items-center fw-bold text-primary-blue height-inherit  p-0">
                                            Show More <span
                                                class="bi bi-chevron-down text-dark ms-1 font-size-13"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="cis-details-mobile-wrapper">
                            <div
                                class="card-header border-0 bg-white d-flex align-items-center justify-content-between py-3">
                                <button class="cis-mobile-toggle-button ra-btn btn-show-hide bg-white w-100 justify-content-start">
                                    <h2 class="font-size-14 fw-bold text-primary-blue">ADAPTER SOCKET</h2>
                                    <span id="toggleIcon" class="toggle-icon bi bi-chevron-up"></span>
                                </button>
                            </div>
                            <div class="cis-details-mobile-wrapper-content">
                                <div>
                                    <h3 class="font-size-14 fw-bold">Specifications</h3>
                                    <div class="mb-3">Lorem Ipsum is simply dummy text of the printing and
                                        typesetting industry
                                    </div>
                                    <p><strong>Size:</strong> 12 &nbsp; &nbsp; | &nbsp; &nbsp;
                                        <strong>Quantity/UOM:</strong> 100 Pieces
                                    </p>
                                    <div class="list-of-vendors mt-4">
                                        <ul
                                            class="list-of-vendors-heading border-bottom-0 rounded-0 rounded-top rounded-right bg-light py-2">
                                            <li class="d-flex">
                                                <span class="vendor-name fw-bold">Vendor Name</span>
                                                <span class="vendor-price fw-bold">Rate (₹)</span>
                                            </li>
                                        </ul>
                                        <ul class="rounded-0 rounded-bottom rounded-left">
                                            <li class="d-flex">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="RONIT VENDOR">
                                                RONIT VENDOR
                                                </a>
                                                </span>
                                                <span class="vendor-price">600</span>
                                            </li>
                                            <li class="d-flex">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="NARSHINGH ENTERPRISE">
                                                NARSHINGH EN
                                                </a>
                                                </span>
                                                <span class="vendor-price">550</span>
                                            </li>
                                            <li class="d-flex">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="Wipro Steel">
                                                WIPRO STEEL
                                                </a>
                                                </span>
                                                <span class="vendor-price">720</span>
                                            </li>
                                            <li class="toggle-vendor-list">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="CISCO">
                                                CISCO
                                                </a>
                                                </span>
                                                <span class="vendor-price">520</span>
                                            </li>
                                            <li class="toggle-vendor-list">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="New India BE">
                                                New India BE
                                                </a>
                                                </span>
                                                <span class="vendor-price">640</span>
                                            </li>
                                            <li class="toggle-vendor-list">
                                                <span class="vendor-name">
                                                <a href="javascript:void(0)" target="_blank"
                                                    class="font-size-13" title="Guru RaProcure">
                                                Guru RaProcure
                                                </a>
                                                </span>
                                                <span class="vendor-price">480</span>
                                            </li>
                                        </ul>
                                        <div
                                            class="show-more d-flex align-items-center justify-content-center py-2">
                                            <span role="button" type="button"
                                                class="toggle-show-more-button d-flex align-items-center fw-bold text-primary-blue height-inherit  p-0">
                                            Show More <span
                                                class="bi bi-chevron-down text-dark ms-1 font-size-13"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End of CIS details Mobile only -->
                </div>
            </section>
            <!---Gap Creation-->
            <div class="fill-more-details d-none d-sm-block"></div>
            <!-- Floating CIS options-->
            <section class="floting-product-options cis-floating-button d-none d-sm-block">
                <div class="d-flex flex-wrap flex-md-nowrap align-items-center justify-content-center gap-3">
                    <button type="button"
                        class="ra-btn btn-outline-primary ra-btn-outline-primary text-uppercase text-nowrap font-size-10"
                        data-bs-toggle="modal" data-bs-target="#createAuctionModal"><span
                        class="bi bi-calendar-date font-size-12" aria-hidden="true"></span> Creat Auction
                    </button>
                    <button type="button"
                        class="ra-btn btn-outline-primary ra-btn-outline-primary text-uppercase text-nowrap font-size-10"
                        data-bs-toggle="modal" data-bs-target="#messageModal"><span class="bi bi-send font-size-12"
                        aria-hidden="true"></span> Send Message
                    </button>
                    <a type="button" href="{{ route('buyer.rfq.counter-offer', ['rfq_id' => $rfq_id, 'vendor_id' => 1]) }}"
                        class="ra-btn btn-outline-primary ra-btn-outline-primary-light text-uppercase text-nowrap font-size-10"><span
                        class="bi bi-repeat font-size-12" aria-hidden="true"></span> Counter Offer
                    </a>
                    <a type="button" href="{{ route('buyer.unapproved-orders.create', ['rfq_id' => $rfq_id]) }}"
                        class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-10"><span
                        class="bi bi-check2-square font-size-12" aria-hidden="true"></span> Proceed to Order
                    </a>
                </div>
            </section>
        </div>
    </main>






    <!-- Modal Check Last CIS/PO -->
    <div class="modal fade" id="checkLastCisPoModal" tabindex="-1" aria-labelledby="checkLastCisPoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md create-auction-modal">
            <div class="modal-content">
                <div class="modal-header bg-graident text-white">
                    <h2 class="modal-title font-size-13" id="checkLastCisPoModalLabel">
                        <span class="bi bi-eye" aria-hidden="true"></span>
                        Check Last CIS/PO
                    </h2>
                    <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="pt-3">
                        <div class="form-group">
                            <input type="radio" name="check-cis-po" id="checkLastCis" value="" checked>
                            <label for="checkLastCis" class="me-4">Check Last CIS</label>

                            <input type="radio" name="check-cis-po" id="checkLastPo" value="">
                            <label for="checkLastPo">Check Last PO</label>
                        </div>
                    </div>
                    <div class="pt-3">
                        <label for="checkCisProduct" class="font-size-13">Products</label>
                        <select name="checkCisProduct" id="checkCisProduct" class="form-select">
                            <option value="">Select Product</option>
                            <option value="">COPPER MOULD TUBE</option>
                        </select>
                    </div>

                    <div class="pt-3 check-result-table">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sr. No.</th>
                                        <th>RFQ No.</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>RONI-25-00040</td>
                                        <td>06/06/2025</td>
                                        <td><a target="_blank" href="javascript:void(0)">Click
                                                to View</a></td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>RONI-25-00029</td>
                                        <td>20/05/2025</td>
                                        <td><a target="_blank" href="javascript:void(0)">Click
                                                to View</a></td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>RONI-25-00026</td>
                                        <td>20/05/2025</td>
                                        <td><a target="_blank" href="javascript:void(0)">Click
                                                to View</a></td>
                                    </tr>

                                    <!-- Below Row will appear while no results -->
                                    <tr>
                                        <td colspan="4">Last PO not found for selected product</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>




                </div>
            </div>
        </div>
    </div>

    <!-- Modal Create Auction -->
    <div class="modal fade" id="createAuctionModal" tabindex="-1" aria-labelledby="createAuctionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md create-auction-modal">
            <div class="modal-content">
                <div class="modal-header bg-graident text-white">
                    <h2 class="modal-title font-size-16" id="createAuctionModalLabel">Create Auction</h2>
                    <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="pt-3">
                        <label for="dropdownButtonLastVendor" class="font-size-13">Vendor <span
                                class="text-danger">*</span></label>
                        <div class="dropdown">
                            <button id="dropdownButtonLastVendor"
                                class="btn btn-outline-default custom-multiselect-dropdown-btn dropdown-toggle justify-content-between"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Select Vendor
                            </button>
                            <div class="dropdown-menu custom-multiselect-dropdown-menu">
                                <div class="sticky-top-option">
                                    <div class="mt-1">
                                        <label class="ra-custom-checkbox mb-0">
                                            <input type="checkbox">
                                            <span class="font-size-13 ra-custom-checkbox-label">Select
                                                All</span>
                                            <span class="checkmark "></span>
                                        </label>
                                    </div>
                                </div>
                                <ul class="filter-list scroll-list p-0">
                                    <li>
                                        <div class="mt-1">
                                            <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11 ra-custom-checkbox-label">RONIT VENDOR
                                                    PROFILE COMPANY QWE</span>
                                                <span class="checkmark "></span>
                                            </label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="mt-1">
                                            <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11 ra-custom-checkbox-label">GURU VENDOR
                                                    PVT AND LTD</span>
                                                <span class="checkmark "></span>
                                            </label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="mt-1">
                                            <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11 ra-custom-checkbox-label">MY RAPROCURE
                                                    VENDOR PVT LTD</span>
                                                <span class="checkmark "></span>
                                            </label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="mt-1">
                                            <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-11 ra-custom-checkbox-label">ABC GURU
                                                    TEST VENDOR PVT LTD</span>
                                                <span class="checkmark "></span>
                                            </label>
                                        </div>
                                    </li>
                                </ul>

                            </div>

                        </div>
                    </div>
                    <div class="pt-3">
                        <label for="date" class="font-size-13">Date <span class="text-danger">*</span></label>
                        <input type="text" id="date" value="RONI-25-00056" class="form-control" readonly>
                    </div>
                    <div class="pt-3">
                        <label for="time" class="font-size-13">Time <span class="text-danger">*</span></label>
                        <input type="time" id="time" value="" class="form-control">
                    </div>

                    <div class="pt-4">
                        <div class="table-responsive">
                            <table class="table table-bordered border-dark table-modal-auction">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center font-size-13 text-nowrap">Product</th>
                                        <th scope="col" class="text-center font-size-13 text-nowrap">Specifications</th>
                                        <th scope="col" class="text-center font-size-13 text-nowrap">Size</th>
                                        <th scope="col" class="text-center font-size-13 text-nowrap">Start Price (₹)
                                            <sup class="text-danger">*</sup>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <span class="text-truncate product-name">
                                                    COPPER MOULD TUB COPPER MOULD TUBCOPPER MOULD TUBCOPPER MOULD TUB
                                                </span>
                                                <button type="button" class="btn btn-link p-0" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="COPPER MOULD TUB">
                                                    <span class="bi bi-info-circle-fill text-dark font-size-11"
                                                        aria-hidden="true"></span>
                                                    <span class="visually-hidden-focusable">info</span>
                                                </button>
                                            </div>

                                        </td>
                                        <td class="align-middle">we</td>
                                        <td class="align-middle text-center">12</td>
                                        <td class="align-middle">
                                            <input type="text" class="form-control" placeholder="Enter Start Price">
                                        </td>
                                    </tr>
                                </tbody>

                            </table>
                        </div>
                    </div>
                    <div class="pt-2">
                        <div class="row gy-2">
                            <div class="col-sm-3">
                                <label for="currency" class="font-size-13">Currency <sup
                                        class="text-danger">*</sup></label>
                                <select name="currency" id="currency" class="form-select" required>
                                    <option value="₹" data-symbol="₹">INR (₹)</option>
                                    <option value="$" data-symbol="$">USD ($)</option>
                                    <option value="NPR" data-symbol="NPR">NPR (रु)</option>
                                </select>
                            </div>
                            <div class="col-sm-9">
                                <label for="minBidDecrement" class="font-size-13">Min Bid Decrement (%) <sup
                                        class="text-danger">*</sup></label>
                                <input type="text" name="min-bid-decrement" id="minBidDecrement" class="form-control"
                                    placeholder="Enter minimum bid decrement" required>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button"
                        class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Message -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header bg-graident text-white">
                    <h2 class="modal-title font-size-12" id="messageModalLabel"><span class="bi bi-pencil"
                            aria-hidden="true"></span> New Message</h2>
                    <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="message pt-3">
                        <div class="dropdown">
                            <button id="dropdownButtonLastVendor"
                                class="btn btn-outline-default custom-multiselect-dropdown-btn dropdown-toggle justify-content-between"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Select Vendor
                            </button>
                            <div class="dropdown-menu custom-multiselect-dropdown-menu">
                                <div class="sticky-top-option">
                                    <div class="mt-1">
                                        <label class="ra-custom-checkbox mb-0">
                                            <input type="checkbox">
                                            <span class="font-size-13 ra-custom-checkbox-label">Select
                                                All</span>
                                            <span class="checkmark "></span>
                                        </label>
                                    </div>
                                </div>
                                <ul class="filter-list scroll-list p-0">
                                    <li>
                                        <div class="mt-1">
                                            <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-13 ra-custom-checkbox-label">RONIT VENDOR
                                                    PROFILE COMPANY QWE</span>
                                                <span class="checkmark "></span>
                                            </label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="mt-1">
                                            <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-13 ra-custom-checkbox-label">GURU VENDOR
                                                    PVT AND LTD</span>
                                                <span class="checkmark "></span>
                                            </label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="mt-1">
                                            <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-13 ra-custom-checkbox-label">MY RAPROCURE
                                                    VENDOR PVT LTD</span>
                                                <span class="checkmark "></span>
                                            </label>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="mt-1">
                                            <label class="ra-custom-checkbox mb-0">
                                                <input type="checkbox">
                                                <span class="font-size-13 ra-custom-checkbox-label">ABC GURU
                                                    TEST VENDOR PVT LTD</span>
                                                <span class="checkmark "></span>
                                            </label>
                                        </div>
                                    </li>
                                </ul>

                            </div>

                        </div>
                    </div>
                    <div class="message pt-2">
                        <input type="text" value="RONI-25-00056" class="form-control" readonly>
                    </div>

                    <section class="ck-editor-section py-2">
                        <textarea name="" id="" rows="5" class="form-control height-inherit"
                            placeholder="This is the placeholder of the editor."></textarea>
                    </section>
                    <section class="upload-file py-2">
                        <div class="file-upload-block justify-content-start">
                            <div class="file-upload-wrapper">
                                <input type="file" class="file-upload" style="display: none;">
                                <button type="button"
                                    class="custom-file-trigger form-control text-start text-dark font-size-13">Upload
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

    <!-- Modal Technical Approval -->
    <div class="modal fade" id="viewTechApprovalModal" tabindex="-1" aria-labelledby="viewTechApprovalModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header bg-graident text-white">
                    <h2 class="modal-title font-size-16" id="viewTechApprovalModalLabel">Technical Approval</h2>
                    <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="message pt-2">
                        <input type="text" value="RONIT VENDOR PROFILE COMPANY QWE" class="form-control" readonly>
                    </div>
                    <div class="message pt-2">
                        <input type="text" value="RONI-25-00056" class="form-control" readonly>
                    </div>

                    <div class="ck-editor-section py-2">
                        <label for="technicalApprovalDesc">Technical Approval Description</label>
                        <textarea name="" id="technicalApprovalDesc" rows="5"
                            class="form-control height-inherit"></textarea>
                    </div>
                    <div class="pt-2 form-group">
                        <label class="font-size-13">Technical Approval</label>
                        <div class="mt-2">
                            <label class="radio-inline font-size-13 me-3">
                                <input type="radio" name="technicalApproval" value="Yes"> Yes
                            </label>
                            <label class="radio-inline font-size-13">
                                <input type="radio" name="technicalApproval" value="No"> No
                            </label>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button"
                        class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11">Submit</button>
                    <button type="button"
                        class="ra-btn btn-outline-primary ra-btn-outline-danger text-uppercase text-nowrap font-size-11"
                        data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.full.min.js') }}"></script>

    <script>
        jQuery(function () {
            jQuery('.dateTimePickerStart').datetimepicker({
                format: 'd/m/Y',
                onShow: function (ct) {
                    this.setOptions({
                        maxDate: jQuery('.dateTimePickerEnd').val() ? jQuery('.dateTimePickerEnd').val() : false
                    })
                },
                timepicker: false
            });
            jQuery('.dateTimePickerEnd').datetimepicker({
                format: 'd/m/Y',
                onShow: function (ct) {
                    this.setOptions({
                        minDate: jQuery('.dateTimePickerStart').val() ? jQuery('.dateTimePickerStart').val() : false
                    })
                },
                timepicker: false
            });
        });
    </script>

@endsection