@extends('vendor.layouts.app_second',['title'=>'Help and Support','sub_title'=>''])
@section('css')
<style>
ul.add-FQR-list {
    display: flex;
    gap: 17px;
    flex-wrap: wrap;
}

ul.add-FQR-list li {
    font-size: 16px;
    border: 1px solid #f3f3f3;
    padding: 5px 12px;
    color: #015294;
}
</style>
@endsection
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i> <a href="{{ route('vendor.dashboard') }}">Dashboard</a>
            <a href="{{ route('vendor.direct_order.index') }}"> ->Orders Confirmed</a>
            <a href="{{ route('vendor.direct_order.index') }}"> ->View Order</a>
        </h5>
    </div>
</div>
@endsection

@section('content')
<div class="about_page_details">
    <div class="container-fluid">
        <div class="card border-0">
            <div class="card-header bg-transparent">
                <div class="col-md-12 d-flex justify-content-between">
                    <h3 class="">Order Details</h3>
                    <div class="rfq-filter-button">
                        <a href="javascript:void(0);" onclick="window.open(`{{ route('vendor.direct_order.print', $order->id) }}`, '_blank', 'width=800,height=600,resizable=yes,scrollbars=yes'); return false;"
                            class="btn-rfq btn-rfq-white float-right mr-3   py-2 px-2"><i
                                class="bi bi-download"></i>Download </a>
                        <a href="{{ route('vendor.direct_order.index') }}"
                            class="btn-rfq btn-rfq-primary btn-rfq-primary float-right"><i
                                class="bi bi-arrow-left-square"></i>Back</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <ul class="add-FQR-list">
                    <li>Order No: {{ $order->manual_po_number }}</li>
                    <li>Order Date: {{ date('d/m/Y', strtotime($order->created_at)) }}</li>
                    <li>Buyer Name: {{ $order->buyer->legal_name ?? '-' }}</li>
                    <li>Branch/Unit : {{$order->order_products[0]->inventory->branch->name}}</li>
                </ul>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col" width="2">S.No.</th>
                            <th scope="col" class="text-center" width="100">PRODUCTS</th>
                            <th scope="col" class="text-center" width="450">SPECIFICATION</th>
                            <th scope="col" class="text-center" width="80">SIZE</th>
                            <th scope="col" class="text-center" width="60">QUANTITY</th>
                            <th scope="col" class="text-center" width="60">UOM</th>
                            <th scope="col" class="text-center" width="60">Rate(₹)</th>
                            <th scope="col" class="text-center" width="60">GST</th>
                            <th scope="col" class="text-right" width="60">Amount(₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $total=0;$gtotal=0; @endphp
                        @foreach ($order->order_products as $key => $value)
                        <tr>
                            <td><b>{{++$key}}</b></td>
                            <td class="text-center"><b>{{$value->product->product_name}}</b></td>
                            <td class="text-center">{{$value->inventory->specification}}</td>
                            <td class="text-center">{{$value->inventory->size}}</td>
                            <td class="text-center">{{$value->product_quantity}}</td>
                            <td class="text-center">{{$value->product->uom}}</td>
                            <td class="text-center">{{$value->product_price}}</td>
                            <td class="text-center">{{$value->product_gst}}%</td>
                            <td class="text-right">
                                @php
                                $total=$value->product_total_amount;
                                $gtotal+=$total;
                                @endphp
                                ₹{{IND_money_format($total)}}
                            </td>
                        </tr>
                        @endforeach
                        <tr>
                            <td><b>Total</b></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-right grand-total-price">
                                ₹{{IND_money_format($gtotal)}}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="bg-light p-3 rounded">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group down_side_sec mt-3">
                                <span class="input-group-text" id="basic-addon1"><iclass="bi bi-journal-medical"></i></span>
                                <div class="form-floating">
                                    <input readonly="" type="text" value="{{$order->order_payment_term}}" class="form-control field-changes w-100" placeholder="Payment Terms">
                                    <label for="p_remarks">Payment Terms </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group down_side_sec mt-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-date"></i></span>
                                <div class="form-floating">
                                    <input readonly="" type="text" value="{{$order->order_delivery_period}}" class="form-control field-changes w-100" placeholder="Delivery Period">
                                    <label for="p_remarks">Delivery Period </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group down_side_sec  mt-3">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-currency-rupee"></i></span>
                                <div class="form-floating">
                                    <input readonly="" type="text" value="{{$order->order_price_basis}}" class="form-control field-changes w-100" placeholder="Price Basis">
                                    <label for="p_remarks">Price Basis</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class=" col-md-6">
                            <div class="input-group down_side_sec mt-4">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-pencil"></i></span>
                                <div class="form-floating">
                                    <input disabled="" type="text" value="{{$order->order_remarks}}" class="form-control field-changes w-100" placeholder="Remarks" >
                                    <label for="p_remarks">Remarks</label>
                                </div>
                            </div>
                        </div>
                         <div class=" col-md-6">
                            <div class="input-group down_side_sec mt-4">
                                <span class="input-group-text" id="basic-addon1"><i class="bi bi-pencil"></i></span>
                                <div class="form-floating">
                                    <input disabled="" type="text"  value="{{$order->order_add_remarks}}" class="form-control field-changes w-100" placeholder="Additional Remarks" >
                                    <label for="p_remarks">Additional Remarks</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')


@endsection