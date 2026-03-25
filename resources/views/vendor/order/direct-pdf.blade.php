<?php 
    $rupees = '&#8377';
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Direct Order for {{ $order->manual_po_number}}</title>
    <style>
    * {
        font-family: sans-serif;
        font-size: 0.94em;
    }

    .table3 {
        float: left;
        width: 33.33%;
    }

    #datatable_wrapper {
        margin-top: 15px;
    }

    .table-border {
        border: 1px solid #000;
    }

    table {
        border-collapse: collapse;
    }

    thead tr th {
        color: #1b1a1a !important;
        text-align: center;
    }

    .fc {
        color: black;
        line-height: 0.9;
    }

    .fc1 {
        color: black;
        line-height: 0.7;
        font-size: 1.1em !important;
    }

    .form-control {
        height: 30px;
    }

    .fcolor1 {
        background-color: rgb(54, 96, 146, 0.4);
    }

    table.product_table {
        border-bottom: 1px solid #000;
    }

    table.product_table tr th {
        border: 1px solid #000;
        font-size: 12px !important;
    }

    table.product_table tr th:first-child {
        border-left: 0px;
    }

    table.product_table tr th:lasr-child {
        border-right: 0px;
    }

    table.product_table tr td {
        border: 1px solid #000;
        font-size: 12px !important;
    }

    table.product_table tr td:first-child {
        border-left: 0px;
    }

    table.product_table tr td:last-child {
        border-right: 0px;
    }
    </style>
</head>

<body onload="window.print();">
    <div class="table-border">
        <table style="table-layout: fixed; width: 100%; border-bottom: 1px solid #000;">
            <tr>
                <td style="">
                    <div class="row" style="margin-bottom:-33px;margin-top:10px;">
                        <img height="30" width="150" src="{{asset('assets/images/logo/rfq-logo.png')}}" />
                        <div class="col-md-12" style="position: relative;top: -38px;">
                            <h4 style="text-align: right;padding-right: 12px;">DIRECT ORDER /ORDER CONFIRMATION</h4>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <table style="table-layout: fixed; width: 100%; border-bottom: 1px solid #000;">
            <tbody>
                <tr>
                    <td style="padding-left: 5px;width: 50%; border-right: 1px solid #000;" colspan="5">
                        <p class="fc"><b>Vendor Name : </b>{{$vendor->legal_name}} </p>
                        <p class="fc"><b>Vendor Address :</b> {{$vendor->registered_address}}</p>
                        <table style="table-layout: fixed; width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="width: 50%;">
                                        <?php
                                        $branch_state_code = substr($vendor->gstin, 0, 2); ?>
                                        <p class="fc1"><b>City : </b><?php echo $vendor->vendor_city->city_name; ?></p>
                                    </td>
                                    <td style="width: 50%;">
                                        <p class="fc1"><b>Pincode : </b><?php echo $vendor->pincode; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 50%;">
                                        <p class="fc1"><b>State : </b><?php echo $vendor->vendor_state->name; ?></p>
                                    </td>
                                    <td style="width: 50%;">
                                        <p class="fc1"><b>State Code : </b><?php echo $branch_state_code; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%;">
                                        <p class="fc1"><b>GSTIN : </b>
                                            <?php echo $vendor->gstin; ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%;">
                                        <p class="fc1"><b>PAN :
                                            </b><?php echo $vendor->msme; ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%;">
                                        <p class="fc1"><b>Email : </b><?php echo $vendor->user->email; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%;">
                                        <p class="fc1"><b>Phone NO : </b><?php echo $vendor->user->mobile; ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td style="padding-left: 5px;width: 50%;" colspan="8" class="fcolor1">
                        <table style="table-layout: fixed; width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="width: 55%;">
                                        <p class="fc1"><b>Order No : </b><?php echo $order->manual_po_number; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 45%;">
                                        <p class="fc1"><b>Order Date :
                                            </b><?php echo date('d/m/Y', strtotime($order->created_at)); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 50%;">
                                        <?php 
                                        $delivery_date = '';
                                        if($order->order_delivery_period != ''){
                                            $delivery_date = $order->order_delivery_period.' Days';
                                        } 
                                    ?>
                                        <p class="fc1"><b>Delivery Period : </b><?php echo $delivery_date; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%;">
                                        <p class="fc1"><b>Payment Term :
                                            </b>&nbsp;<?php echo $order->order_payment_term; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%;">
                                        <p class="fc1"><b>Price Basis :
                                            </b>&nbsp;<?php echo $order->order_price_basis; ?></p>
                                    </td>

                                </tr>

                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
        <table style="table-layout: fixed; width: 100%; border-bottom: 1px solid #000;">
            <tbody>
                <tr>
                    <td style="padding-left:5px;width: 50%; border-right: 1px solid #000;" colspan="5">
                        <?php 
                            $starting_state_code_b = substr($order->buyer->gstin, 0, 2);
                        ?>
                        <p class="fc"><b>Buyer Name : </b><?php echo $order->buyer->legal_name; ?> </p>
                        <p class="fc"><b>Buyer Address :</b>
                            <?php echo $order->buyer->registered_address;  ?></p>
                        <table style="table-layout: fixed; width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="width: 60%;">
                                        <p class="fc1"><b>City : </b><?php echo $order->buyer->buyer_city->city_name; ?>
                                        </p>
                                    </td>
                                    <td style="width: 40%;">
                                        <p class="fc1"><b>Pincode : </b><?php echo$order->buyer->pincode; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 60%;">
                                        <p class="fc1"><b>State : </b><?php echo $order->buyer->buyer_state->name; ?>
                                        </p>
                                    </td>
                                    <td style="width: 40%;">
                                        <p class="fc1"><b>State Code : </b><?php echo $starting_state_code_b; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%;">
                                        <p class="fc1"><b>GSTIN : </b><?php echo $order->buyer->gstin; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%;">
                                        <div><span class="fc1 fcolor"><b>Contact Person :
                                                </b></span><span
                                                class="fc1"><?php echo $order->buyer->users->name; ?></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%;">
                                        <p class="fc1"><b>Email : </b><?php echo $order->buyer->users->email; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%;">
                                        <p class="fc1"><b>Phone NO :
                                            </b><?php echo '+'.$order->buyer->users->country_code.' '.$order->buyer->users->mobile; ?>
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td style="padding-left: 5px;width: 50%;" colspan="5">
                        <p class="fc"><b>Buyer Name : </b><?php echo $order->buyer->legal_name; ?> </p>
                        <p class="fc"><b>Delivery Address
                                :</b><?php echo $order->order_products[0]->inventory->branch->address;  ?></p>
                        <table style="table-layout: fixed; width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="width: 50%;">
                                        <p class="fc1"><b>City :
                                            </b><?php echo $order->order_products[0]->inventory->branch->branch_city->city_name; ?>
                                        </p>
                                    </td>
                                    <td style="width: 50%;">
                                        <p class="fc1"><b>Pincode :
                                            </b><?php echo  $order->order_products[0]->inventory->branch->pincode; ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 60%;">
                                        <?php 
                                            $starting_state_code_c = substr($order->order_products[0]->inventory->branch->gstin, 0, 2);
                                        ?>
                                        <p class="fc1"><b>State :
                                            </b><?php echo $order->order_products[0]->inventory->branch->branch_state->name; ?>
                                        </p>
                                    </td>
                                    <td style="width: 40%;" colspan="5">
                                        <p class="fc1"><b>State Code : </b><?php echo $starting_state_code_c; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%;">
                                        <p class="fc1"><b>GSTIN :
                                            </b><?php echo $order->order_products[0]->inventory->branch->gstin; ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%;">
                                        <p class="fc1"><b>Branch Name :
                                            </b><?php echo $order->order_products[0]->inventory->branch->name; ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%;">
                                        <p class="fc1"><b>Phone NO :
                                            </b><?php echo $order->order_products[0]->inventory->branch->mobile; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 100%;">
                                        <p class="fc1"><b>Email :
                                            </b><?php echo $order->order_products[0]->inventory->branch->email; ?>
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
        <table style="table-layout: fixed; width: 100%; ">
            <tr>
                <td style="">
                    <div class="row">
                        <div class="col-md-12">
                            <h4 style="text-align: left; margin-left:5px;">
                                <?php echo $order->order_products[0]->product->division->division_name.' > '.$order->order_products[0]->product->category->category_name; ?>
                            </h4>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="product_table" style=" width: 100%; border-bottom: 1px solid #000;">
            <thead>
                <tr class='fcolor1'>
                    <th style="text-align:center;width:5%;">S.No</th>
                    <th style="text-align:center;width:47%;">Product Description</th>
                    <th style="text-align:center;width:5%;">Quantity</th>
                    <th style="text-align:center;width:6%;">UOM</th>
                    <th style="text-align:center;width:8%;">Rate</th>
                    <th style="text-align:center;width:9%;">HSN Code</th>
                    <th style="text-align:center;width:6%;">GST %</th>
                    <th style="text-align:center;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @php  $total=0;$gtotal=0; @endphp
                @foreach ($order->order_products as $key => $value)

                <tr>
                    <td style="text-align:center;"><?php echo ++$key ?></td>
                    <td>
                        {{$value->product->product_name}}
                        {{$value->inventory->specification}}
                        {{$value->inventory->size}}
                    </td>
                    <td style="text-align:center;">{{$value->product_quantity}}</td>
                    <td style="text-align:center;">{{$value->product->uom}}</td>
                    <td style="text-align:center;">
                        <span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>
                        {{$value->product_price}}
                    </td>
                    <td style="text-align:center;">{{$value->product->prod_hsn_code}}</td>
                    <td style="text-align:center;">{{$value->product_gst}}%</td>
                    <td style="text-align:right; padding-right:5px;">
                        <span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>
                        @php
                        $total=$value->product_total_amount;
                        $gtotal+=$total;
                        @endphp
                         {{IND_money_format($total)}}
                    </td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="7" style="background-color:rgb(242,220,219); padding-left:5px ;">
                        Total
                    </td>
                    <td style="padding:5px; background-color:rgb(242,220,219);text-align:right;">
                        <span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span>
                         {{IND_money_format($gtotal)}}
                    </td>
                </tr>
            </tbody>
        </table>

        <table style="table-layout: fixed; width: 100%;  border-bottom: 1px solid #000;">
            <tbody>
                <!-- <tr>
                    <td style="" colspan="6">
                    </td>
                    <td style="padding-right: 10px;text-align: right;">
                        <b>ORDER GENERATED THROUGH<br> RaProcure </b>
                    </td>
                </tr> -->
                <tr>
                    <td style=" padding:5px; font-size: 12px;"><b>Amount In Words :</b>
                        <?php              
                            echo amounts_number_to_words($gtotal);
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <table style="table-layout: fixed; width: 100%; border-bottom: 1px solid #000;">
            <tr>
                <td style="">
                    <div class="row">
                        <div class="col-md-12">
                            <h3 style="text-align: right; padding-right: 5px;">ORDER GENERATED THROUGH<br> RaProcure
                            </h3>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <table style="border: 1px solid #0a0909 !important;table-layout: fixed; width: 100%;">
            <tr>
                <td colspan="2">
                    <p class="fc" style="padding-left:5px!important;"><b>Remarks : </b>{{$order->order_remarks}}</p>
                    <p class="fc" style="padding-left:5px!important;"><b>Additional Remarks :
                        </b>{{$order->order_add_remarks}}</p>
                </td>
            </tr>
            <tr>
                <td width="70%">
                    <p class="fc" style="padding-left:5px!important;"><b>Prepared By :
                        </b><?php echo $order->buyer->users->name; ?></p>
                </td>
                <td style="text-align:right;padding-right:5px!important;" width="30%"><b>For
                        <?php echo $order->buyer->legal_name; ?> </b></td>
            </tr>
        </table>
    </div>
</body>

</html>