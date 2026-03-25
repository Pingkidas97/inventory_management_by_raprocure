<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order for <?php echo $order->po_number;?></title>

    <style>
        * {
            font-family: sans-serif;
            font-size: 0.96em;
        }

        .table3 {
            float: left;
            width: 33.33%;
        }

        #datatable_wrapper {
            margin-top: 15px;
        }

        .table-border{border:1px solid #000;}

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
            font-size: 1.2em !important;
            margin-top: 7px;
            margin-bottom: 7px;
        }

        .form-control {
            height: 30px;
        }

        .fcolor1 {
            background-color: rgb(54, 96, 146, 0.4);
        }
        table.product_table{border-bottom:1px solid #000;}
        table.product_table tr th{border:1px solid #000;font-size: 12px !important;}
        table.product_table tr th:first-child{border-left:0px;}
        table.product_table tr th:lasr-child{border-right:0px;}
        table.product_table tr td{border:1px solid #000;font-size: 12px !important;}
        table.product_table tr td:first-child{border-left:0px;}
        table.product_table tr td:last-child{border-right:0px;}
    </style>
</head>

<body>
    <div class="table-border">
        <table style="table-layout: fixed; width: 100%; border-bottom: 1px solid #000;">
            <tr>
                <td style="">
                    <div class="row" style="margin-bottom: -22px;">
                        <img height="30" width="150"
                            src="<?php echo asset('assets/images/logo/rfq-logo.png') ?>" style="margin-top: 8px;" />
                        <div class="col-md-12" style="position: relative;top: -30px;">

                            <h4 style="text-align: right;padding-right: 12px;">PURCHASE ORDER /ORDER CONFIRMATION</h4>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <table style="table-layout: fixed; width: 100%; border-bottom: 1px solid #000;">
            <tbody>
                <tr>
                    <?php  
                        $starting_state_code_a = substr($order->vendor->gstin, 0, 2);
                    ?>
                    <td style="padding-left: 5px;width: 50%; border-right: 1px solid #000;" colspan="5">
                        <p class="fc"><b>Vendor Name : </b><?php echo $order->vendor->legal_name;?> </p>
                        <p class="fc"><b>Vendor Address :</b><?php echo $order->vendor->registered_address; ?></p>
                        <table style="table-layout: fixed; width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="width: 70%;">
                                        <?php  $branch_state_code = substr($order->vendor->gstin, 0, 2); ?>
                                        <p class="fc1"><b>City : </b><?php echo $order->vendor->vendor_city->city_name; ?></p>
                                    </td>
                                    <td style="width: 30%;">
                                        <p class="fc1"><b>Pincode : </b><?php echo  $order->vendor->pincode; ?></p>
                                    </td>
                                </tr>
                                <?php                                    
                                    $is_international  =  $order->vendor->country;
                                    $gst_title  = 'GSTIN/VAT';
                                    $pan_title  = 'PAN/TIN';
                                ?>
                                <tr>
                                    <td style="width: 70%;">                                       
                                        <p class="fc1"><b>State : </b><?php echo $order->vendor->vendor_state->name; ?></p>
                                    </td>
                                    <?php 
                                       if($is_international == 101){
                                    ?>
                                    <td style="width: 30%;">
                                        <p class="fc1"><b>State Code : </b><?php echo $branch_state_code; ?></p>
                                    </td>
                                    <?php 
                                     }else{
                                    ?>
                                    <td style="width: 30%;">
                                        <p class="fc1"></p>
                                    </td>
                                    <?php } ?>
                                </tr>
                                <tr>
                                    <td style="width: 50%;">
                                        <p class="fc1"><b>Country : </b><?php echo  $order->vendor->vendor_country->name; ?></p>
                                    </td>
                                    <td style="width: 50%;">
                                        <p class="fc1"></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 70%;">
                                        <p class="fc1"><b><?php echo $gst_title ?> : </b><br>
                                            <br><?php echo $order->vendor->gstin; ?>
                                        </p>
                                    </td>
                                    <td style="width: 30%;">
                                        <p class="fc1"><b><?php echo $pan_title ?> :
                                            </b><br><br><?php echo $order->vendor->msme; ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <p class="fc1"><b>Phone NO : </b> <?php echo '+'.$order->vendor->user->country_code.' '.$order->vendor->user->mobile; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <p class="fc1"><b>Email : </b> <?php echo $order->vendor->user->email; ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td style="padding-left: 5px;width: 50%;" colspan="8" class="fcolor1">
                        <table style="table-layout: fixed; width: 100%;">
                            <tbody>
                                <tr>
                                    <td colspan="2">
                                        <p class="fc1"><b>Buyer Order Number : </b><?php echo $order->buyer_order_number; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 65%;">
                                        <p class="fc1"><b>Order No : </b><?php echo $order->po_number; ?></p>
                                    </td>
                                    
                                    <td style="width: 35%;">
                                        <p class="fc1"><b>Order Date : </b><?php echo date('d/m/Y', strtotime($order->created_at)); ?></p>
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
                                    <td style="width: 50%;">
                                        <p class="fc1"><b>PRN : </b><?php //echo $order->prn_no; ?></p>
                                    </td>                                    
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <p class="fc1"><b>Price Basis : </b><span style="font-size: 12px !important;line-height: 1.1; word-spacing: 0.2px;"><?php echo $order->order_price_basis; ?></span></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <p class="fc1"><b>Payment Term : </b><span style="font-size: 12px !important;line-height: 1.1; word-spacing: 0.2px;"><?php echo $order->order_payment_term; ?></span></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 50%;">
                                        <p class="fc1"><b>RFQ NO: </b><?php echo $order->rfq_id; ?></p>
                                    </td>
                                    <td style="width: 50%;">
                                        <p class="fc1"><b>RFQ Date : </b><?php echo date('d/m/Y' ,strtotime($order->rfq->created_at)); ?></p>
                                    </td>
                                </tr>
                                <tr></tr>
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
                        <p class="fc"><b>Buyer Address :</b><?php echo $order->buyer->registered_address;  ?></p>
                        <table style="table-layout: fixed; width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="width: 60%;">
                                        <p class="fc1"><b>City : </b><?php echo $order->buyer->buyer_city->city_name; ?></p>
                                    </td>
                                    <td style="width: 40%;">
                                        <p class="fc1"><b>Pincode : </b><?php echo $order->buyer->pincode; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 60%;">
                                         <p class="fc1"><b>State : </b><?php echo $order->buyer->buyer_state->name; ?></p>
                                    </td>
                                    <?php 
                                     if($order->buyer->country == '101'){
                                    ?>
                                    <td style="width: 40%;">
                                        <p class="fc1"><b>State Code : </b><?php echo $starting_state_code_b; ?></p>
                                    </td>
                                   <?php }else{ ?>
                                    <td style="width: 40%;">
                                        <p class="fc1"></p>
                                    </td>
                                   <?php } ?>
                                </tr>
                                <tr>
                                    <td style="width: 60%;">
                                       <p class="fc1"><b>Country : </b><?php echo $order->buyer->buyer_country->name; ?></p>
                                    </td>
                                    <td style="width: 40%;">
                                        <p class="fc1"></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 60%;">
                                        <p class="fc1"><b>GSTIN/VAT : </b><br><br><?php echo $order->buyer->gstin; ?></p>
                                    </td>
                                    <td style="width: 40%;">
                                       <div><span class="fc1 fcolor"><b>Contact Person :</b><br><br></span><span class="fc1"><?php echo $order->buyer->users->name; ?></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <p class="fc1"><b>Phone NO : </b> <?php echo '+'.$order->buyer->users->country_code.' '.$order->buyer->users->mobile; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <p class="fc1"><b>Email : </b> <?php echo $order->buyer->users->email; ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td style="padding-left: 5px;width: 50%;" colspan="5">
                        <p class="fc"><b>Buyer Name : </b><?php echo  $order->buyer->legal_name;; ?> </p>
                        <p class="fc"><b>Delivery Address :</b><?php echo $order->rfq->buyer_branchs->address;  ?></p>
                        <table style="table-layout: fixed; width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="width: 60%;">
                                        <p class="fc1"><b>City : </b><?php echo $order->rfq->buyer_branchs->branch_city->city_name; ?></p>
                                    </td>
                                    <td style="width: 40%;">
                                        <p class="fc1"><b>Pincode : </b><?php echo $order->rfq->buyer_branchs->pincode; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 60%;">
                                        <?php 
                                            $starting_state_code_c = substr($order->rfq->buyer_branchs->gstin, 0, 2);
                                        ?>
                                        <p class="fc1"><b>State : </b><?php echo $order->rfq->buyer_branchs->branch_state->name; ?></p>
                                    </td>
                                    <?php if($order->rfq->buyer_branchs->country == '101'){ ?>
                                     <td style="width: 40%;" colspan="5">
                                        <p class="fc1"><b>State Code : </b><?php echo $starting_state_code_c; ?></p>
                                     </td>
                                    <?php }else{ ?>
                                     <td style="width: 40%;" colspan="5">
                                        <p class="fc1"></p>
                                     </td>
                                    <?php } ?>
                                </tr>
                                <tr>
                                    <td style="width: 60%;">
                                        <p class="fc1"><b>Country : </b><?php echo $order->rfq->buyer_branchs->branch_country->name; ?></p>
                                    </td>
                                    <td style="width: 40%;">
                                        <p class="fc1"></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 65%;">
                                        <p class="fc1"><b>GSTIN/VAT : </b><br><br><?php echo $order->rfq->buyer_branchs->gstin; ?></p>
                                    </td>
                                    <td style="width: 35%;">
                                        <p class="fc1"><b>Branch Name :</b><br><br><?php echo  $order->rfq->buyer_branchs->name; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <p class="fc1"><b>Phone NO : </b> <?php echo $order->rfq->buyer_branchs->mobile; ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <p class="fc1"><b>Email : </b> <?php echo $order->rfq->buyer_branchs->email; ?></p>
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
                                <?php echo $order->order_variants[0]->product->division->division_name.' > '.$order->order_variants[0]->product->category->category_name; ?>
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
                    <th style="text-align:center;width: 30%;">Product Description</th>
                    <th style="text-align:center;width:7%;">Quantity</th>
                    <th style="text-align:center;width:9%;">UOM</th>
                    <th style="text-align:center;width:8%;">MRP
                        (<span style="font-family: DejaVu Sans; sans-serif;"><?php
                            // Check if 'vend_currency' is set and not empty
                            if (isset($order->vendor_currency) && !empty($order->vendor_currency)) {
                                // Display the value from the 'vend_currency' field
                                echo $order->vendor_currency; 
                            } else {
                                // Display the Indian Rupee symbol
                                echo '&#8377;'; // HTML entity for the Rupee symbol
                            }
                        ?></span>)
                    </th>
                    <th style="text-align:center;width:6%;">DISC. %</th>
                    <th style="text-align:center;width:8%;">Rate
                        (<span style="font-family: DejaVu Sans; sans-serif;"><?php
                            // Check if 'vend_currency' is set and not empty
                            if (isset($order->vendor_currency) && !empty($order->vendor_currency)) {
                                // Display the value from the 'vend_currency' field
                                echo $order->vendor_currency; 
                            } else {
                                // Display the Indian Rupee symbol
                                echo '&#8377;'; // HTML entity for the Rupee symbol
                            }
                        ?></span>)
                    </th>
                    <th style="text-align:center;width:9%;">HSN Code</th>
                    <?php
                        if($order->int_buyer_vendor==2){
                    ?> 
                    <th style="text-align:center;width:6%;">GST %</th>
                    <?php
                        }
                    ?> 
                    <th style="text-align:center;">Total Amount
                        (<span style="font-family: DejaVu Sans; sans-serif;"><?php
                             // Check if 'vend_currency' is set and not empty
                            if (isset($order->vendor_currency) && !empty($order->vendor_currency)) {
                                // Display the value from the 'vend_currency' field
                                echo $order->vendor_currency; 
                            } else {
                                // Display the Indian Rupee symbol
                                echo '&#8377;'; // HTML entity for the Rupee symbol
                            }
                        ?></span>)
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                 $grand_total=0;
                foreach ($order->order_variants as $key => $val) {
                    $gst_class = '';
                    $tax_class = $val->product_gst;
                    $amount = ($val->order_price * $val->order_quantity);
                    $gst = ($amount * $tax_class) / 100;
                    $amount = $amount + $gst;
                    $grand_total += $amount;
                     
                    $grand_total=  number_format((float)$grand_total, 2, '.', '');
                    $amount =  number_format((float)$amount, 2, '.', '');
                ?>
                <tr>
                    <td style="text-align:center;"><?php echo $i++ ?></td>
                    <td>
                        <?php echo '&nbsp;'.$val->product->product_name; ?>
                        <span style="font-size: 10px !important; ">
                        <?php echo $val->frq_variant->specification; ?>
                        <?php echo $val->frq_variant->size; ?>
                        </span>
                    </td>
                    <td style="text-align:center;"><?php echo $val->order_quantity; ?> </td>
                    <td style="text-align:center;"><?php echo $val->frq_variant->uom; ?> </td>
                    <td style="text-align:center;">
                        <?php 
                            $order_mrp = number_format((float)$val->order_mrp, 2, '.', '');
                            echo !empty($val->order_mrp) ? IND_money_format($order_mrp) : '';
                        ?>
                    </td>
                    <td style="text-align:center;"><?php echo !empty($val->order_discount) ? $val->order_discount : ''; ?></td>
                    <td style="text-align:center;">
                        <span style="font-family: DejaVu Sans; sans-serif;"><?php
                    ?></span>
                        <?php 
                            $rate_amount = number_format((float)$val->order_price, 2, '.', '');
                            echo IND_money_format($rate_amount); 
                        ?>
                    </td>
                    <td style="text-align:center;"><?php echo !empty($val->vend_product_hsn_code) ? $val->vend_product_hsn_code : '' ?>
                    </td>
                    <?php
                    if($order->int_buyer_vendor==2){
                    
                        if($tax_class != ''){
                            $gst_class = $tax_class.'%';
                        }
                    ?>
                    <td style="text-align:center;"><?php echo $gst_class ?> </td>
                    <?php
                        }
                    ?>
                    <td style="text-align:right; padding-right:5px;">
                        <span style="font-family: DejaVu Sans; sans-serif;">
                    <?php
                    ?></span><?php echo IND_money_format($amount); ?>
                        
                    </td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td colspan="<?php echo $order->int_buyer_vendor==2 ? '9' : '8' ?>"
                        style="background-color:rgb(242,220,219); padding-left:5px ;">
                        Total 
                    </td>
                    <td
                        style="background-color:rgb(242,220,219);text-align:right;">
                        <span style="font-family: DejaVu Sans; sans-serif;">

                        <?php
                            // Check if 'vend_currency' is set and not empty
                            if (isset($order->vendor_currency) && !empty($order->vendor_currency)) {
                                // Display the value from the 'vend_currency' field
                                echo $order->vendor_currency; 
                            } else {
                                // Display the Indian Rupee symbol
                                echo '&#8377;'; // HTML entity for the Rupee symbol
                            }
                        ?>  
                    </span><?php echo IND_money_format($grand_total); ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <table style="table-layout: fixed; width: 100%;  border-bottom: 1px solid #000;">
            <tbody>
                 <tr>
                    <td style="padding:3px; font-size: 12px;"><b>Amount In Words :</b>
                        <?php              
                            echo amounts_number_to_words_with_currency($grand_total, $order->vend_currency);
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
                            <h3 style="text-align: right; padding-right: 5px;">ORDER GENERATED THROUGH<br> RaProcure</h3>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <table style="table-layout: fixed; width: 100%; padding: 0px;">
            <tbody>
                <tr>
                    <td style="padding: 5px;" colspan="13">
                        <p style="margin-top: 3px; margin-bottom: 1px;" class="fc"><b>Remarks : </b><span style="font-size:9px;letter-spacing: 0px; text-align: left; line-height: normal;"><?php echo $order->order_remarks ?> </span> </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px;" colspan="13">
                        <p style="margin-top: 3px; margin-bottom: 1px;" class="fc"><b>Additional Remarks : </b><span style="font-size:9px;letter-spacing: 0px; text-align: left; line-height: normal;"><?php echo $order->order_add_remarks ?> </span> </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px;width: 50%;" colspan="5">
                        <!-- <p class="fc"><b>Checked By : </b><?php //echo 'Checked'; ?></p> -->
                        <p class="fc"><b>RFQ Generated By : </b><?php echo $order->rfq->rfq_generated_by?->name; ?></p>
                        <p class="fc"><b>PO Generated By : </b><?php echo $order->po_generated_by?->name; ?></p>
                        <p style="margin-bottom: 3px;" class="fc"><b>Order Confirmed By : </b><?php echo $order->order_confirmed_by?->name; ?></p>
                    </td>
                    <td style="padding: 5px;width: 50%;text-align:right;"
                        colspan="8"> <b>For <?php echo $order->buyer->legal_name; ?> </b>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>