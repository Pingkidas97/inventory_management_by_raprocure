<?php 
    $order_detail = $plan;
    $rupees = '&#8377';
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>PROFORMA INVOICE for {{$plan->invoice_no}}</title>
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
        .form-control {
            height: 30px;
        }
        table.product_table tr th{border-bottom:1px solid #000;font-size: 12px !important;}
        table.product_table tr td{border-bottom:1px solid #000;font-size: 12px !important;}
    </style>
</head>
<body>
    <div class="table-border" style="border: 1px solid #000;">
        <table style="table-layout: fixed; width: 100%;   border-bottom: 1px solid #000;">
            <tr>
                <td style="">
                    <div class="row" style="margin-bottom: -22px;">
                        <img height="30" width="150" src="{{asset('public/assets/images/rfq-logo.png')}}" />
                        <div class="col-md-12" style="position: relative;top: -30px;">
                        <h4 style="text-align: right;padding-right: 12px;">Raprocure Technologies Private Limited </h4>
                        <span></span>
                        <p style="text-align: center;">8D, Bengal Eco Intelligent Park, EM Block, Sector V, Kolkata 700091</p>
                        <p style="text-align: center;">State: West Bengal. I State Code: 19</p>
                        <p style="text-align: center;">Mobile: 9088880077 I Email: apex@raprocure.com I www.raprocure.com</p>
                        <p style="text-align: center;">GSTIN: 19AAMCR2585E1Z0 I CIN: U72900WB2022PTC257903</p>
                        <p style="text-align: center;"> Bank Name: ICICI BANK LTD.    ACCOUNT NO: 000605037715.    IFSC: ICIC0000006.    Branch: RN Mukherjee Road</p>
                    </div>
                    </div>
                </td>
            </tr>
        </table>
        <table style="table-layout: fixed; width: 100%;  border-bottom: 1px solid #000;">
            <tr>
                <td style="">
                    <div class="row">
                        <div class="col-md-12">
                            <h4 style="text-align: center;">PROFORMA INVOICE</h4>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <?php 
            $totalval=0;
            $starting_state_code = substr($vendor->gstin,0,2);
        ?>
        <table style="table-layout: fixed; width: 100%; ">
            <tbody>
                <tr>
                    <td style="padding: 15px;width: 50%;  border-right: 1px solid #000; border-bottom: 1px solid #000;" colspan="5">
                        <p class="fc"><b>Proforma Invoice No: {{$plan->invoice_no}}</b></p>
                        <p class="fc"><b>Invoice Date : <?php echo date('d/m/Y',strtotime($plan->created_at))?> </b></p>
                        <p class="fc"><b>Customer Name :{{$vendor->legal_name}}</b></p>
                        <p class="fc"><b>Customer Code : </b>{{$vendor->vendor_code}}</p>
                        <p class="fc"><b>GSTIN : </b>{{$vendor->gstin}}</p>
                        <p class="fc"><b>PAN : </b>{{$vendor->pan}}</p>
                    </td>
                    <td style="padding: 15px;width: 50%;  border-bottom: 1px solid #000;" colspan="8">
                        <p class="fc"><b>Address : </b> {{$vendor->registered_address}}</p>   
                        <p class="fc"><b>State : </b>{{getCityStateCountry($vendor->state,'state')}}</p>
                        <p class="fc"><b>State Code : </b> {{$starting_state_code}}</p>
                        <p class="fc"><b>Place of Supply : </b> {{getCityStateCountry($vendor->city,'city')}},{{getCityStateCountry($vendor->state,'state')}}</p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <table class="product_table" style="table-layout: fixed; width: 100%;" class="display dataTable">
            <thead>
                <tr>
                    <th style="text-align:center;border-right:1px solid #000;">S.No</th>
                    <th colspan="6" style=" padding-right:43.5px !important;text-align:center; border-right:1px solid #000;">Description</th>
                    <th colspan="2" style="text-align:center;border-right:1px solid #000;">HSN/SAC</th>
                    <th style="text-align:center;border-right:1px solid #000;">Rate 
                        (<span style="font-family:DejaVu Sans; sans-serif;">&#8377;</span>)
                    </th>
                    <th style="text-align:center;border-right:1px solid #000;">Discount (%)</th>
                    <th style="text-align:center;">Amount 
                        (<span style="font-family:DejaVu Sans; sans-serif;">&#8377;</span>)
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align:center;border-right: 1px solid #000; ">1</td>
                    <td colspan="6" style="text-align:center; border-right: 1px solid #000;">
                        Subscription plan valid for the period 
                        @php
                            if($plan->start_date != '' && $plan->start_date != null) {
                                $subscriptionstart = date("d/m/Y", strtotime($plan->start_date));
                            } else {
                                $subscriptionstart = '';
                            }
                            if($plan->next_renewal_date != '' && $plan->next_renewal_date != null) {
                                $subscriptionend = date("d/m/Y", strtotime($plan->next_renewal_date));
                            } else {
                                $subscriptionend = '';
                            }
                            echo $subscriptionstart.' - '.$subscriptionend;
                        @endphp
                    </td>
                    <td colspan="2" style="text-align:center; border-right: 1px solid #000;">998319</td>
                    <td style="text-align:center; border-right: 1px solid #000;">
                        @php $plan_price = $plan->plan_amount; @endphp   
                    </td>
                    <td style="text-align:center; border-right: 1px solid #000;">
                        @php
                            if($plan->discount != '') {
                                echo $plan->discount .'%';
                            }
                        @endphp
                    </td>
                    <td style="text-align:center;">
                        @php
                            $plansprice = $plan->plan_amount; 
                            // echo $plansprice;
                            $totaldisc = ($plan_price *  $plan->discount/100);
                            // echo $totaldisc;
                            $total = $plan_price - $totaldisc;
                            // echo $total;
                            echo IND_money_format($total);
                            $totalval += $total;
                        @endphp        
                    </td>
                </tr>
            </tbody>
        </table>
        <table style="table-layout: fixed; width: 100%; border-bottom: 1px solid #000;border-top: 1px solid #000;  ">
            <tbody>
                <tr >
                    <td align="right" style="padding: 5px; width: 50%; color: #1b1a1a !important;border-right: 1px solid #000;  ">
                        Total Value 
                    </td>
                    <td align="right" style="padding: 5px; width: 50%;color: #1b1a1a !important;padding-left:37px !important;">
                        {{IND_money_format($totalval)}}
                    </td>
                </tr>
            </tbody>
        </table>
        <table style="table-layout: fixed; width: 100%; border-bottom: 1px solid #000;  ">
            <tbody>
                <tr>
                    <td align="right" style="padding: 5px; width: 50%;color: #1b1a1a !important; border-right: 1px solid #000; ">
                    CGST @ 9% 
                    </td>
                    <td align="right"  style="padding: 5px; width: 50%;color: #1b1a1a !important;">
                    @php 
                        if($vendor->state== 41) {
                            $CGST = ($totalval * 9/100);
                            $CGST=  number_format((float)$CGST, 2, '.', '');
                            $CGST=  IND_money_format($CGST);  
                        } else {
                            $CGST = 0;
                        }
                    @endphp
                    {{$CGST}}
                    </td>
                </tr>
            </tbody>
        </table>
        <table style="table-layout: fixed; width: 100%; border-bottom: 1px solid #000;  ">
            <tbody>
                <tr>
                    <td align="right" style="padding: 5px; width: 50%;color: #1b1a1a !important; border-right: 1px solid #000; ">
                    SGST @ 9%
                    </td>
                    <td align="right"  style="padding: 5px; width: 50%;color: #1b1a1a !important;">
                        @php
                            if($vendor->state == 41) {
                                $SGST = ($totalval * 9/100);
                                $SGST=  number_format((float)$SGST, 2, '.', '');
                                $SGST=  IND_money_format($SGST);     
                            } else {
                                $SGST = 0;
                            }
                        @endphp
                        {{$SGST}}
                    </td>
                </tr>
            </tbody>
        </table>
        <table style="table-layout: fixed; width: 100%; border-bottom: 1px solid #000;  ">
            <tbody>
                <tr>
                    <td align="right" style="padding: 5px; width: 50%;color: #1b1a1a !important; border-right: 1px solid #000; ">
                    IGST @ 18 % 
                    </td>
                    <td align="right"  style="padding: 5px; width: 50%;color: #1b1a1a !important;"> 
                        @php 
                            $gst='';
                            if($vendor->state!= 41) {
                                $IGST = ($totalval * 18/100);
                                $IGST=  number_format((float)$IGST, 2, '.', '');
                                $gst=  IND_money_format($IGST);     
                            } else {
                                $IGST = 0;
                                $gst= $IGST;
                            }
                        @endphp 
                        {{$gst}}  
                    </td>
                </tr>
            </tbody>
        </table>
        <table style="table-layout: fixed; width: 100%; border-bottom: 1px solid #000;  ">
            <tbody>
                <tr>
                    <td align="right" style="padding: 5px; width: 50%;color: #1b1a1a !important; border-right: 1px solid #000; ">
                    Total Amount
                    </td>
                    <td align="right"  style="padding: 5px; width: 50%;color: #1b1a1a !important;"> 
                        @php  
                            $totalgst = $IGST + $SGST + $CGST;
                            $totalGrand = $totalval + $totalgst;
                            $totalGrand = number_format((float)$totalGrand, 2, '.', '');
                        @endphp 
                        {{IND_money_format($totalGrand)}}  
                    </td>
                </tr>
            </tbody>
        </table>
        <table style="table-layout: fixed; width: 100%;border-bottom: 1px solid #000; border-collapse: collapse;">
            <tbody>
                <tr style="">
                    <!-- <td colspan="2" style=" padding-top:10px;padding-bottom:10px;border-left: 1px solid #0a0909 !important;border-top: 1px solid #0a0909 !important;border-bottom: 1px solid #0a0909 !important;">Amount In Words  : </td> -->
                    <td colspan="12" style=" padding:5px;border-top: 0px !important;">Amount In Words :
                        {{             
                         amounts_number_to_words($totalGrand);
                        }}
                    </td>
                </tr>
            </tbody>
        </table>
        <table style="table-layout: fixed; width: 100%; border-collapse: collapse;">
            <tbody>
                <tr>
                    <td style="border-bottom: 1px solid #000; padding:5px;">The above particulars are true & correct.<br>
                    Subject to Kolkata jurisdiction <br>
                    E & O.E
                    </td>
                    <td style="padding-right: 10px;text-align: right; border-bottom: 1px solid #000;"><b>For Raprocure Technologies<br> Private Limited</b></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center;">This is a computer generated invoice</td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- <div>
        <div style="padding-top:1cm; padding-bottom: 1cm;">
            <p style="font-size: 10pt; line-height: 14pt;">
                <strong>Note : </strong> Product Order Details .
            </p>
        </div>
    </div> -->
    </body>
</html>