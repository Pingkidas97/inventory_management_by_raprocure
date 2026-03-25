@extends('buyer.layouts.app', ['title'=>'Unapproved Order Confirmation'])

@section('css')
    {{-- <link rel="stylesheet" href="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.css') }}" /> --}}
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar-menu')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1">
        <div class="container-fluid">
            <div class="bg-white unapproved-order-page">
                <h3 class="card-head-line">Unapproved Order Confirmation</h3>
                <div class="list-for-rfq-wrap">
                    <ul class="list-for-rfq">
                        <li>RFQ No: {{$rfq_id}}</li>
                        <li>PRN Number: </li>
                        <li>Branch/Unit : Test Buyer Branch 1</li>
                        <li>Buyer Name : AMIT BUYER TEST</li>
                    </ul>
                    <div>
                        <button type="button"
                            class="ra-btn btn-outline-primary ra-btn-outline-primary small-btn text-uppercase text-nowrap">
                        <span class="bi bi-download" aria-hidden="true"></span> Download
                        </button>
                        <a href="{{ route('buyer.rfq.cis-sheet', ['rfq_id' => $rfq_id]) }}" class="ra-btn small-btn ra-btn-primary small-btn">
                            <span class="bi bi-arrow-left-square" aria-hidden="true"></span>
                            Back
                        </a>
                    </div>
                </div>
                <div class="table-info px-15 pb-15">
                    <h2 class="accordion-header" id="companyInfoOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInfoOne"
                            aria-expanded="false" aria-controls="collapseInfoOne">
                        1. COMPANY
                        </button>
                    </h2>
                    <div id="collapseInfoOne" class="accordion-collapse collapse show" aria-labelledby="companyInfoOne">
                        <div class="accordion-body">
                            <div class="table-responsive">
                                <table class="product-listing-table w-100">
                                    <thead>
                                        <tr>
                                            <th class="text-center">S.No</th>
                                            <th class="text-center">Products</th>
                                            <th class="text-center w-300">Specification</th>
                                            <th class="text-center w-120">Size</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-center w-120">UOM</th>
                                            <th class="text-center">MRP (₹)</th>
                                            <th class="text-center">Disc.(%)</th>
                                            <th class="text-center">Rate (₹)</th>
                                            <th class="text-center">GST</th>
                                            <th class="text-center">Amount (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td class="text-center"><b class="font-size-12">SAMSUNG S23</b></td>
                                            <td class="text-center">1</td>
                                            <td></td>
                                            <td><input type="text" value="12.00"
                                                class=" form-control text-center bg-white product-quantity-field mx-auto"></td>
                                            <td class="text-center">Pieces</td>
                                            <td><input type="text" value="0.00"
                                                class=" form-control text-center bg-white product-mrp-field mx-auto"></td>
                                            <td><input type="text" value="0.00"
                                                class=" form-control text-center bg-white product-discount-field mx-auto"></td>
                                            <td><input type="text" value="10.00"
                                                class=" form-control text-center bg-white product-rate-field mx-auto"></td>
                                            <td class="text-center">0%</td>
                                            <td class="text-end">₹120.00</td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td class="text-center"><b class="font-size-12">SAMSUNG S23</b></td>
                                            <td class="text-center">2</td>
                                            <td></td>
                                            <td><input type="text" value="12.00"
                                                class=" form-control text-center bg-white product-quantity-field mx-auto"></td>
                                            <td class="text-center">Pieces</td>
                                            <td><input type="text" value="0.00"
                                                class=" form-control text-center bg-white product-mrp-field mx-auto"></td>
                                            <td><input type="text" value="0.00"
                                                class=" form-control text-center bg-white product-discount-field mx-auto"></td>
                                            <td><input type="text" value="10.00"
                                                class=" form-control text-center bg-white product-rate-field mx-auto"></td>
                                            <td class="text-center">0%</td>
                                            <td class="text-end">₹252.00</td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td class="text-center"><b class="font-size-12">SAMSUNG S23</b></td>
                                            <td class="text-center">3</td>
                                            <td></td>
                                            <td><input type="text" value="12.00"
                                                class=" form-control text-center bg-white product-quantity-field mx-auto"></td>
                                            <td class="text-center">Sets</td>
                                            <td><input type="text" value="0.00"
                                                class=" form-control text-center bg-white product-mrp-field mx-auto"></td>
                                            <td><input type="text" value="0.00"
                                                class=" form-control text-center bg-white product-discount-field mx-auto"></td>
                                            <td><input type="text" value="10.00"
                                                class=" form-control text-center bg-white product-rate-field mx-auto"></td>
                                            <td class="text-center">0%</td>
                                            <td class="text-end">₹1,845.00</td>
                                        </tr>
                                        <tr>
                                            <td>4</td>
                                            <td class="text-center"><b class="font-size-12">SAMSUNG S23</b></td>
                                            <td class="text-center">4</td>
                                            <td></td>
                                            <td><input type="text" value="12.00"
                                                class=" form-control text-center bg-white product-quantity-field mx-auto"></td>
                                            <td class="text-center">Sets</td>
                                            <td><input type="text" value="0.00"
                                                class=" form-control text-center bg-white product-mrp-field mx-auto"></td>
                                            <td><input type="text" value="0.00"
                                                class=" form-control text-center bg-white product-discount-field mx-auto"></td>
                                            <td><input type="text" value="10.00"
                                                class=" form-control text-center bg-white product-rate-field mx-auto"></td>
                                            <td class="text-center">0%</td>
                                            <td class="text-end">₹182.00</td>
                                        </tr>
                                        <tr>
                                            <td>5</td>
                                            <td class="text-center"><b class="font-size-12">SAMSUNG S23</b></td>
                                            <td class="text-center">5</td>
                                            <td></td>
                                            <td><input type="text" value="12.00"
                                                class=" form-control text-center bg-white product-quantity-field mx-auto"></td>
                                            <td class="text-center">Sets</td>
                                            <td><input type="text" value="0.00"
                                                class=" form-control text-center bg-white product-mrp-field mx-auto"></td>
                                            <td><input type="text" value="0.00"
                                                class=" form-control text-center bg-white product-discount-field mx-auto"></td>
                                            <td><input type="text" value="10.00"
                                                class=" form-control text-center bg-white product-rate-field mx-auto"></td>
                                            <td class="text-center">0%</td>
                                            <td class="text-end">₹238.00</td>
                                        </tr>
                                        <tr>
                                            <td>6</td>
                                            <td class="text-center"><b class="font-size-12">SAMSUNG S23</b></td>
                                            <td class="text-center">6</td>
                                            <td></td>
                                            <td><input type="text" value="12.00"
                                                class=" form-control text-center bg-white product-quantity-field mx-auto"></td>
                                            <td class="text-center">Sets</td>
                                            <td><input type="text" value="0.00"
                                                class=" form-control text-center bg-white product-mrp-field mx-auto"></td>
                                            <td><input type="text" value="0.00"
                                                class=" form-control text-center bg-white product-discount-field mx-auto"></td>
                                            <td><input type="text" value="10.00"
                                                class=" form-control text-center bg-white product-rate-field mx-auto"></td>
                                            <td class="text-center">0%</td>
                                            <td class="text-end">₹285.00</td>
                                        </tr>
                                        <tr>
                                            <td><b>Total</b></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td class="text-end">₹2,922.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="pt-15">
                                <div class="border-bottom  pb-15">
                                    <form class="blue-light-bg p-15 rounded">
                                        <div class="row justify-content-between">
                                            <div class="col-md-4 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-geo-alt"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="priceBasis" placeholder="Price Basis"
                                                            value="1">
                                                        <label for="priceBasis">Price Basis <span class="text-danger">*</span></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-currency-rupee"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="paymentTerm" placeholder="Payment Term"
                                                            value="1">
                                                        <label for="paymentTerm">Payment Term <span class="text-danger">*</span></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-calendar2-date"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="deliveryPeriod"
                                                            placeholder="Delivery Period (In Days)" value="12">
                                                        <label for="deliveryPeriod">Delivery Period (In Days) <span
                                                            class="text-danger">*</span></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-patch-check"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="guranteeWarranty"
                                                            placeholder="Gurantee/Warranty">
                                                        <label for="guranteeWarranty">Gurantee/Warranty</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-pencil"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="guranteeWarranty"
                                                            placeholder="Gurantee/Warranty">
                                                        <label for="guranteeWarranty">Remarks</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-pencil"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="guranteeWarranty"
                                                            placeholder="Gurantee/Warranty">
                                                        <label for="guranteeWarranty">Additional Remarks</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-file-earmark-text"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="guranteeWarranty"
                                                            placeholder="Gurantee/Warranty">
                                                        <label for="guranteeWarranty">Buyer Order Number</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-center mt-4">
                                                <button type="button"
                                                    class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap gap-1">GENERATE
                                                Unapproved
                                                PO
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h2 class="accordion-header" id="companyInfoTwo">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInfoTwo"
                            aria-expanded="false" aria-controls="collapseInfoTwo">
                        2. TEST AMIT VENDOR
                        </button>
                    </h2>
                    <div id="collapseInfoTwo" class="accordion-collapse collapse show" aria-labelledby="companyInfoTwo">
                        <div class="accordion-body">
                            <div class="table-responsive">
                                <table class="product-listing-table w-100">
                                    <thead>
                                        <tr>
                                            <th class="text-center">S.No</th>
                                            <th class="text-center">Products</th>
                                            <th class="text-center w-300">Specification</th>
                                            <th class="text-center w-120">Size</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-center w-120">UOM</th>
                                            <th class="text-center">MRP (₹)</th>
                                            <th class="text-center">Disc.(%)</th>
                                            <th class="text-center">Rate (₹)</th>
                                            <th class="text-center">GST</th>
                                            <th class="text-center">Amount (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td class="text-center"><b class="font-size-12">SAMSUNG S23</b></td>
                                            <td class="text-center">1</td>
                                            <td></td>
                                            <td><input type="text" value="12.00"
                                                class=" form-control text-center bg-white product-quantity-field mx-auto"></td>
                                            <td class="text-center">Pieces</td>
                                            <td><input type="text" value="0.00"
                                                class=" form-control text-center bg-white product-mrp-field mx-auto"></td>
                                            <td><input type="text" value="0.00"
                                                class=" form-control text-center bg-white product-discount-field mx-auto"></td>
                                            <td><input type="text" value="10.00"
                                                class=" form-control text-center bg-white product-rate-field mx-auto"></td>
                                            <td class="text-center">0%</td>
                                            <td class="text-end">₹120.00</td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td class="text-center"><b class="font-size-12">SAMSUNG S23</b></td>
                                            <td class="text-center">2</td>
                                            <td></td>
                                            <td><input type="text" value="12.00"
                                                class=" form-control text-center bg-white product-quantity-field mx-auto"></td>
                                            <td class="text-center">Pieces</td>
                                            <td><input type="text" value="0.00"
                                                class=" form-control text-center bg-white product-mrp-field mx-auto"></td>
                                            <td><input type="text" value="0.00"
                                                class=" form-control text-center bg-white product-discount-field mx-auto"></td>
                                            <td><input type="text" value="10.00"
                                                class=" form-control text-center bg-white product-rate-field mx-auto"></td>
                                            <td class="text-center">0%</td>
                                            <td class="text-end">₹252.00</td>
                                        </tr>
                                        <tr>
                                            <td><b>Total</b></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td class="text-end">₹2,922.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="pt-15">
                                <div class="border-bottom  pb-15">
                                    <form class="blue-light-bg p-15 rounded">
                                        <div class="row justify-content-between">
                                            <div class="col-md-4 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-geo-alt"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="priceBasis" placeholder="Price Basis"
                                                            value="1">
                                                        <label for="priceBasis">Price Basis <span class="text-danger">*</span></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-currency-rupee"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="paymentTerm" placeholder="Payment Term"
                                                            value="1">
                                                        <label for="paymentTerm">Payment Term <span class="text-danger">*</span></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-calendar2-date"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="deliveryPeriod"
                                                            placeholder="Delivery Period (In Days)" value="12">
                                                        <label for="deliveryPeriod">Delivery Period (In Days) <span
                                                            class="text-danger">*</span></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-patch-check"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="guranteeWarranty"
                                                            placeholder="Gurantee/Warranty">
                                                        <label for="guranteeWarranty">Gurantee/Warranty</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-pencil"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="guranteeWarranty"
                                                            placeholder="Gurantee/Warranty">
                                                        <label for="guranteeWarranty">Remarks</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-pencil"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="guranteeWarranty"
                                                            placeholder="Gurantee/Warranty">
                                                        <label for="guranteeWarranty">Additional Remarks</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-file-earmark-text"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="guranteeWarranty"
                                                            placeholder="Gurantee/Warranty">
                                                        <label for="guranteeWarranty">Buyer Order Number</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-center mt-4">
                                                <button type="button"
                                                    class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap gap-1">GENERATE
                                                Unapproved
                                                PO
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h2 class="accordion-header" id="companyInfoThree">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInfoThree"
                            aria-expanded="false" aria-controls="collapseInfoThree">
                        3. A KUMAR
                        </button>
                    </h2>
                    <div id="collapseInfoThree" class="accordion-collapse collapse show" aria-labelledby="companyInfoThree">
                        <div class="accordion-body">
                            <div class="table-responsive">
                                <table class="product-listing-table w-100">
                                    <thead>
                                        <tr>
                                            <th class="text-center">S.No</th>
                                            <th class="text-center">Products</th>
                                            <th class="text-center w-300">Specification</th>
                                            <th class="text-center w-120">Size</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-center w-120">UOM</th>
                                            <th class="text-center">MRP (₹)</th>
                                            <th class="text-center">Disc.(%)</th>
                                            <th class="text-center">Rate (₹)</th>
                                            <th class="text-center">GST</th>
                                            <th class="text-center">Amount (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td class="text-center"><b class="font-size-12">SAMSUNG S23</b></td>
                                            <td class="text-center">1</td>
                                            <td></td>
                                            <td><input type="text" value="12.00"
                                                class=" form-control text-center bg-white product-quantity-field mx-auto"></td>
                                            <td class="text-center">Pieces</td>
                                            <td><input type="text" value="0.00"
                                                class=" form-control text-center bg-white product-mrp-field mx-auto"></td>
                                            <td><input type="text" value="0.00"
                                                class=" form-control text-center bg-white product-discount-field mx-auto"></td>
                                            <td><input type="text" value="10.00"
                                                class=" form-control text-center bg-white product-rate-field mx-auto"></td>
                                            <td class="text-center">0%</td>
                                            <td class="text-end">₹2,24,000.00</td>
                                        </tr>
                                        <tr>
                                            <td><b>Total</b></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td class="text-end">₹2,24,000.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="pt-15">
                                <div class="border-bottom  pb-15">
                                    <form class="blue-light-bg p-15 rounded">
                                        <div class="row justify-content-between">
                                            <div class="col-md-4 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-geo-alt"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="priceBasis" placeholder="Price Basis"
                                                            value="1">
                                                        <label for="priceBasis">Price Basis <span class="text-danger">*</span></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-currency-rupee"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="paymentTerm" placeholder="Payment Term"
                                                            value="1">
                                                        <label for="paymentTerm">Payment Term <span class="text-danger">*</span></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-calendar2-date"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="deliveryPeriod"
                                                            placeholder="Delivery Period (In Days)" value="12">
                                                        <label for="deliveryPeriod">Delivery Period (In Days) <span
                                                            class="text-danger">*</span></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-patch-check"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="guranteeWarranty"
                                                            placeholder="Gurantee/Warranty">
                                                        <label for="guranteeWarranty">Gurantee/Warranty</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-pencil"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="guranteeWarranty"
                                                            placeholder="Gurantee/Warranty">
                                                        <label for="guranteeWarranty">Remarks</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-pencil"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="guranteeWarranty"
                                                            placeholder="Gurantee/Warranty">
                                                        <label for="guranteeWarranty">Additional Remarks</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 col-12 mt-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">
                                                    <span class="bi bi-file-earmark-text"></span>
                                                    </span>
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="guranteeWarranty"
                                                            placeholder="Gurantee/Warranty">
                                                        <label for="guranteeWarranty">Buyer Order Number</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-center mt-4">
                                                <button type="button"
                                                    class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap gap-1">GENERATE
                                                Unapproved
                                                PO
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')

@endsection