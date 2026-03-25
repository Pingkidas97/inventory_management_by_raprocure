@php
    use App\Helpers\NumberFormatterHelper;
    use App\Helpers\CurrencyConvertHelper;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Work Order PDF</title>
    <style>
       @font-face {
            font-family: 'Noto Sans Devanagari';
            src: url('{{ public_path("assets/font/NotoSansDevanagari-Regular.ttf") }}') format("truetype");
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'Noto Sans Devanagari', DejaVu Sans, Arial, sans-serif;
            font-size: 7pt;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
        }

        .main-wrapper {
            margin: 20px;
            padding: 0;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }
        table th,
        table td {
            padding: 3pt;
        }

        .top-bar-table {
            border: 1px solid #000;
            border-bottom-width: 0;
            font-weight: bold;
        }

        .top-bar-table td {
            vertical-align: middle;
        }

        .top-bar-table .left {
            text-align: left;
        }

        .top-bar-table .right {
            text-align: right;
        }

        .layout-table td {
            width: 50%;
            border: 1px solid #000;
            vertical-align: top;
        }

        .info-table,
        .info-table td  {
            border: none;
        }
        .info-table td {
            padding-left: 0pt;
        }

        .section-title {
            margin: 5px 0 5px 5px;
            text-align: left;
            font-weight: bold;
        }


        .product-table th,
        .product-table td {
            border: 1px solid #000;
            border-top: none;
            text-align: center;

            white-space: nowrap;
        }
        .product-table thead th {
            background: #afbfd3;
            padding-top: 1pt;
            padding-bottom: 1pt;
        }
        .product-table tfoot td {
            background: #f1dcdb;
            vertical-align: top;
        }

        .product-table td.description {
            text-align: left;
            white-space: normal;
        }

        .product-table .total-label {
            text-align: left;
            font-weight: bold;
        }

        .product-table .total-value {
            font-weight: bold;
            text-align: center;
        }

        .amount-words {
            padding: 4pt 3pt;
            border: 1px solid #000;
            border-top: none;
            font-weight: bold;
        }

        .footer {
            border: 1px solid #000;
            border-top: none;
            padding: 4pt 3pt;
        }
        .keep-word {
            word-break: normal;
        }
        .text-wrap {
             white-space: normal!important;
        }
        .order-generated {
            text-align: right;
            /* margin-top: 10px; */
            font-size: 8pt;
            border: 1px solid #000;
            border-top: none;
            padding: 4pt 3pt;
        }
        .order-generated img {
            max-width: 20%;
            margin-left: 5px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
@php

 $currency = !empty($order->currency_id) ? get_currency_symbol($order->currency_id) : '₹';
@endphp
<div class="main-wrapper">

    <table class="top-bar-table">
        <tr>
            <td class="left">
                @php
                    $buyer_id=$order->buyer_id;
                    $buyer_user_id=$order->buyer_user_id;
                    $companyId=$buyer_id;
                    $companyId = (auth()->user()->parent_id != 0) ? auth()->user()->parent_id : auth()->user()->id;
                    $company = \App\Models\User::where('id', $companyId)->with('buyer')->first();
                    $logo = ($company && isset($company->logo) && !empty($company->logo)) ? $company->logo : null;
                    $base64 = null;
                    if ($logo) {
                        $path = public_path('uploads/buyer-profile/' . $logo);
                        if (is_file($path) && file_exists($path)) {
                            $type = pathinfo($path, PATHINFO_EXTENSION);
                            $data = file_get_contents($path);
                            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        }
                    }
                @endphp

                @if ($base64)
                    <img src="{{ $base64 }}" alt="Company Logo" style="max-width: 20%; vertical-align: middle;">
                @else
                    {{ strtoupper(optional($company->buyer)->legal_name ?? 'UNKNOWN COMPANY')}}
                @endif

        </td>
            <td class="right">PURCHASE ORDER</td>
        </tr>
    </table>


    <table class="layout-table">
        <!-- VENDOR + ORDER SECTION -->
        <tr>
            <td>
                <table class="info-table">
                    <tr>
                        <td colspan="2">
                            <strong>Vendor Name:</strong> {{ strtoupper(optional($order->vendor)->legal_name ?? 'UNKNOWN VENDOR') }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Vendor Address:</strong>
                            {{ optional(optional($order->vendorUser)->vendor)->registered_address ?? '' }}
                            @php
                                $city =optional(optional(optional($order->vendorUser)->vendor)->vendor_city)->city_name;
                                if (!empty($city)) {
                                    echo ', ' . $city;
                                }
                            @endphp

                        </td>
                    </tr>
                    <tr>
                        <!-- <td><strong>City:</strong> </td> -->
                        <td colspan="2"><strong>Pincode:</strong> {{ optional(optional($order->vendorUser)->vendor)->pincode ?? ' ' }}</td>
                    </tr>
                    <tr>
                        <td><strong>State:</strong> {{ optional(optional(optional($order->vendorUser)->vendor)->vendor_state)->name ?? ' ' }}</td>
                        <!-- <td><strong>State Code:</strong> {{ optional(optional(optional($order->vendorUser)->vendor)->vendor_state)->state_code ?? '-' }}</td> -->
                        <td><strong>State Code:</strong> {{ optional(optional(optional($order->vendorUser)->vendor)->vendor_country)->id == 101 ? (substr(optional(optional($order->vendorUser)->vendor)->gstin ?? '', 0, 2) ?: '')   : ''    }} </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>GSTIN:</strong> {{ optional(optional($order->vendorUser)->vendor)->gstin ?? ' ' }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Email:</strong> {{ optional($order->vendorUser)->email ?? ' ' }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <!-- <strong>Phone NO:</strong> +{{ optional($order->vendorUser)->country_code ?? ' ' }} {{ optional($order->vendorUser)->mobile ?? ' ' }} -->
                            <strong>Phone NO:</strong> +{{ optional(optional(optional($order->vendorUser)->vendor)->vendor_country)->phonecode ?? '' }}
 {{ optional($order->vendorUser)->mobile ?? ' ' }}
                        </td>
                    </tr>
                </table>
            </td>

            <td style="background: #afbfd3;">
                <table class="info-table">
                    <tr>
                        <td>
                            <strong>Order No:</strong> {{ $order->work_order_number ?? ' ' }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Order Date:</strong> {{ optional($order->created_at)->format('d/m/Y') ?? ' ' }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Delivery Period:</strong> {{ $order->order_delivery_period ?? ' ' }} Days
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Payment Term:</strong> {{ $order->order_payment_term ?? ' ' }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>Price Basis:</strong> {{ $order->order_price_basis ?? ' ' }}
                        </td>
                    </tr>
                </table>
            </td>

        </tr>
        <!-- BUYER + DELIVERY SECTION -->
        <tr>
            <td>
                <table class="info-table">
                    <tr>
                        <td colspan="2">
                            <strong>Buyer Name:</strong> {{ strtoupper(optional($company->buyer)->legal_name ?? 'UNKNOWN BUYER') }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Buyer Address:</strong> {{ optional($company->buyer)->registered_address ?? '' }}
                            @php
                                $city = optional(optional($company->buyer)->buyer_city)->city_name;
                                if (!empty($city)) {
                                        echo ', ' . $city;
                                    }
                            @endphp
                        </td>
                    </tr>
                    <tr>
                        <!-- <td>
                            <strong>City:</strong>
                        </td> -->
                        <td colspan="2">
                            <strong>Pincode:</strong> {{ optional($company->buyer)->pincode ?? ' ' }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>State:</strong> {{ optional(optional($company->buyer)->buyer_state)->name ?? ' ' }}
                        </td>
                        <td>
                            <!-- <strong>State Code:</strong> {{ optional(optional($company->buyer)->buyer_state)->state_code ?? ' ' }} -->
                            <strong>State Code:</strong> {{ optional(optional($company->buyer)->buyer_country)->id == 101
        ? (substr(optional($company->buyer)->gstin ?? '', 0, 2) ?: '')   : ''    }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Country:</strong> {{ optional(optional($company->buyer)->buyer_country)->name ?? ' ' }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>GSTIN:</strong> {{ optional($company->buyer)->gstin ?? ' ' }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Contact Person:</strong> {{ strtoupper($order->buyerUser->name ?? ' ') }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Email:</strong> {{ $order->buyerUser->email ?? ' ' }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <!-- <strong>Phone NO:</strong> +{{ $order->buyerUser->country_code ?? ' ' }} {{ $order->buyerUser->mobile ?? ' ' }} -->
                            <strong>Phone NO:</strong> +{{ optional(optional($company->buyer)->buyer_country)->phonecode ?? ' ' }} {{ $order->buyerUser->mobile ?? ' ' }}
                        </td>
                    </tr>
                </table>
            </td>

            <td>
                <table class="info-table">
                    <tr>
                        <td colspan="2">
                            <strong>Buyer Name:</strong> {{ optional($company->buyer)->legal_name ?? 'UNKNOWN BUYER' }}
                            @php
                                $firstProduct = optional($order->products)->first();
                                $orderBranch = optional($firstProduct->inventory)->branch;
                            @endphp
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Delivery Address:</strong> {{ optional($orderBranch)->address ?? ' ' }}
                            @php
                                $city =optional(optional($orderBranch)->branch_city)->city_name;
                                if (!empty($city)) {
                                    echo ', ' . $city;
                                }
                            @endphp
                        </td>
                    </tr>
                    <tr>
                        <!-- <td><strong>City:</strong> </td> -->
                        <td colspan="2"><strong>Pincode:</strong> {{ optional($orderBranch)->pincode ?? ' ' }}</td>
                    </tr>
                    <tr>
                        <td><strong>State:</strong> {{ optional(optional($orderBranch)->branch_state)->name ?? ' ' }}</td>
                        <!-- <td><strong>State Code:</strong> {{ optional(optional($orderBranch)->branch_state)->state_code ?? ' ' }}</td> -->
                        <td><strong>State Code:</strong> {{ optional(optional($orderBranch)->branch_country)->id == 101
        ? (substr($orderBranch->gstin ?? '', 0, 2) ?: '')   : ''    }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Country:</strong> {{ optional(optional($orderBranch)->branch_country)->name ?? ' ' }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>GSTIN:</strong> {{ optional($orderBranch)->gstin ?? ' ' }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Branch Name:</strong> {{ optional($orderBranch)->name ?? ' ' }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Phone NO:</strong> +{{ optional(optional($orderBranch)->branch_country)->phonecode ?? ' ' }} {{ optional($orderBranch)->mobile ?? ' ' }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <strong>Email:</strong> {{ optional($orderBranch)->email ?? ' ' }}
                        </td>
                    </tr>
                </table>
            </td>

        </tr>
        <tr>
            <td colspan="2">
                <h4 class="section-title">
                    @php
                        $division = $firstProduct->product->division?->division_name;
                        $category = $firstProduct->product->category?->category_name;
                    @endphp

                    @if($division && $category)
                        {{ $division }} &gt; {{ $category }}
                    @elseif($division)
                        {{ $division }}
                    @elseif($category)
                        {{ $category }}
                    @endif
                </h4>
            </td>
        </tr>
    </table>

    <table class="product-table">
        <thead>
            <tr>
                <th class="text-wrap keep-word" style="width: 5%;">S.No</th>
                <th class="text-wrap keep-word" style="width: 18%;">Product Description</th>
                <th class="text-wrap keep-word">Quantity</th>
                <th class="text-wrap keep-word">UOM</th>
                <th class="text-wrap keep-word">Price ({{$currency == 'रु' ? 'NPR' : $currency}})</th>
                <th class="text-wrap keep-word">MRP ({{$currency == 'रु' ? 'NPR' : $currency}})</th>
                <th class="text-wrap keep-word">DISC. (%)</th>
                <th class="text-wrap keep-word">HSN Code</th>
                <th class="text-wrap keep-word">GST %</th>
                <th class="text-wrap keep-word" style="width: 15%;">Total Amount ({{$currency == 'रु' ? 'NPR' : $currency}})</th>
            </tr>
        </thead>
        <tbody>
             @php  $totalAmount=0; @endphp
                    @foreach($order->products as $key => $product)
                            @php
                                $formatted_rate=NumberFormatterHelper::formatCurrencyPDF($product->product_price,$currency);
                                $formatted_rate = str_contains($formatted_rate, '.') ? $formatted_rate : $formatted_rate . '.00';
                                $formatted_mrp = $product->product_mrp === null ? '' : (str_contains(NumberFormatterHelper::formatCurrencyPDF($product->product_mrp, $currency), '.') ? NumberFormatterHelper::formatCurrencyPDF($product->product_mrp, $currency) : NumberFormatterHelper::formatCurrencyPDF($product->product_mrp, $currency) . '.00');
                                $hsnCode = DB::table('vendor_products')->where('product_id', $product->product_id)->where('vendor_id', $order->vendor_id)->value('hsn_code');
                            @endphp
                        <tr class="highlight" >
                            <td>{{ $key + 1 }}</td>
                            <td style="word-wrap: break-word; white-space: normal; text-align: left;">
                                {{ $product->product->product_name }}
                                {!! $product->inventory->specification ? ' - ' . cleanInvisibleCharacters($product->inventory->specification) : '' !!}
                                {!! $product->inventory->size ? ' - ' . cleanInvisibleCharacters($product->inventory->size) : '' !!}
                            </td>
                            <td style="white-space: nowrap;">{{NumberFormatterHelper::formatQty($product->product_quantity,$currency)}}</td>
                            <td>{{ $product->inventory->uom->uom_name }}</td>
                            <td style="white-space: nowrap;">{{$currency == 'रु' ? 'NPR ' : $currency}}{{$formatted_rate}}</td>
                            <td style="white-space: nowrap;">{{ $formatted_mrp !== '' ? ($currency == 'रु' ? 'NPR ' : $currency) . $formatted_mrp : '' }}</td>
                            <td style="white-space: nowrap;">{{$product->product_disc !== null ? NumberFormatterHelper::formatCurrencyPDF($product->product_disc, $currency).' %' : ''}}</td>
                            <td>{{ $hsnCode ?? '' }}</td>
                            <td style="white-space: nowrap;">{{ $product->tax->tax ?? '0' }} %</td>
                            <td style="white-space: nowrap;">
                                @php
                                    $price = $product->product_price;
                                    $qty = $product->product_quantity;
                                    $taxPercent = floatval($product->tax->tax ?? 0);
                                    $qty = $qty == 0 ? 1 : $qty;
                                    $subtotal = $price * $qty;
                                    $gstAmount = $subtotal * ($taxPercent / 100);
                                    $totalWithGst = $subtotal + $gstAmount;
                                    $totalAmount+= $totalWithGst;

                                    $formatted_totalWithGst = NumberFormatterHelper::formatCurrencyPDF($totalWithGst,$currency);
                                    $formatted_totalWithGst = str_contains($formatted_totalWithGst, '.') ? $formatted_totalWithGst : $formatted_totalWithGst . '.00';
                                @endphp
                                {{$currency == 'रु' ? 'NPR ' : $currency}}{{$formatted_totalWithGst}}</td>
                        </tr>
                    @endforeach

        </tbody>
        <tfoot>
            <tr>
                <td colspan="9" class="total-label">Total:</td>
                @php
                $formatted_totalAmount=NumberFormatterHelper::formatCurrencyPDF($totalAmount,$currency);
                $formatted = str_contains($formatted_totalAmount, '.') ? $formatted_totalAmount : $formatted_totalAmount . '.00';
                @endphp

                <td class="total-value">{{$currency == 'रु' ? 'NPR ' : $currency}}
                    {{$formatted}}</td>
            </tr>
        </tfoot>
    </table>

    <div class="amount-words">
        Amount In Words: {{ CurrencyConvertHelper::numberToWordsWithCurrency($totalAmount, $currency) }}
    </div>
    <div class="order-generated" >
        <p><strong>ORDER GENERATED THROUGH</strong></p>
        @php
            $path = public_path('assets/images/rfq-logo.png');
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        @endphp
        <img src="{{ $base64 }}" alt="raProcure" style="max-width: 20%; margin-left: 5px; vertical-align: middle;">

    </div>


    <div class="footer">
        <p><strong>Remarks:</strong> {{ $order->order_remarks ?? ' ' }}</p>
        <p><strong>Additional Remarks:</strong> {{ $order->order_add_remarks ?? ' ' }}</p>
        <p>
            <strong>Prepared By:</strong>
            {{ strtoupper(optional($order->preparedBy)->name ?? 'UNKNOWN') }}
            {{-- For --}}
            {{-- {{ strtoupper(optional($company->buyer)->legal_name ?? 'UNKNOWN BUYER') }} --}}
        </p>
    </div>
    @if(!empty($order->order_other_terms))
    <div class="footer">
        <p><strong>Other Terms and Conditions:</strong></p>
        <p>{!! nl2br($order->order_other_terms) !!}</p>
    </div>
    @endif


</div>

</body>
</html>
