<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gate Pass - {{ $orders->first()->get_pass_no ?? '' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        h1, h2, h3 {
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header .title {
            text-align: left;
        }
        .header img {
            max-height: 50px;
        }
        .info {
            margin-bottom: 20px;
        }
        .info div {
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background-color: #f0f0f0;
        }
        .note {
            margin-top: 20px;
            font-style: italic;
        }
        .order-generated {
            margin-top: 10px;
            display: flex;
            align-items: center;
        }
        .order-generated p {
            margin: 0;
            font-weight: bold;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    @php $order = $orders->first(); @endphp

    <div class="header">
        <div class="title">
            <strong><h2>Gate Pass</h2></strong>
            <p>Gate Pass No: {{ $order->get_pass_id ?? '' }}</p>
        </div>

      
    </div>

    <div class="info">
        <div><strong>Product Name:</strong> {{ $order->inventory->product->product_name ?? $order->inventory->buyer_product_name ?? '-' }}</div>
        <div><strong>Specification:</strong> {!! $order->inventory->specification ? cleanInvisibleCharacters($order->inventory->specification) : '-' !!}</div>
        <div><strong>Size:</strong> {!! $order->inventory->size ? cleanInvisibleCharacters($order->inventory->size) : '-' !!}</div>
        <div><strong>Invoice Number:</strong> {{ $order->vendor_invoice_number ?? '-' }}</div>
        <div><strong>Transporter Name:</strong> {!! $order->transporter_name ?? '-' !!}</div>
        <div><strong>Vehicle No / LR No:</strong> {!! $order->vehicle_no_lr_no ?? '-' !!}</div>
        <div><strong>Gross Wt (kgs):</strong> {!! $order->gross_wt ?? '-' !!}</div>
        <div><strong>Vendor Name:</strong> {!! $order->vendor_name ?? '-' !!}</div>
        <div><strong>Branch Name:</strong> {{ $order->inventory->branch->name ?? '-' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>PO Number</th>
                <th>Gate Entry Quantity</th>
                <th>Gate Entry Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $index => $order)
                <tr>
                    <td>{{ $index + 1 }}</td>                
                    <td>{{ $order->po_number ?? '-' }}</td>
                    <td>{{ number_format($order->grn_qty, 3) }}</td>
                    <td>{{ optional($order->updated_at)->format('d/m/Y') ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @php
        $path = public_path('assets/images/rfq-logo.png');
        if(file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        } else {
            $base64 = ''; // file not found হলে blank
        }
    @endphp
    <div class="order-generated">
        <p>GATE ENTRY GENERATED THROUGH</p>
        <img src="{{ $base64 }}" alt="Company Logo" style="max-width: 20%;">
    </div>

    <div class="note">
        <p><strong>Note:</strong> This is a system-generated Gate Pass.</p>
    </div>
</body>
</html>