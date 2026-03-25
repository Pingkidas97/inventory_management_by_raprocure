<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
</head>
<body>
    <p>Dear {{ $vendorName }} from {{$vendorAddress}},</p>

    <p>Congratulations!<br>
    {{$buyername}} has confirmed the order on {{ $order['order_date'] }}.</p>

    <table style="border-collapse: collapse; width: 100%;" border="1" cellpadding="5">
        <tr>
            <th>Order Number</th>
            <td>{{ $order['order_number'] }}</td>
        </tr>
        <tr>
            <th>Order Date</th>
            <td>{{ $order['order_date'] }}</td>
        </tr>
    </table>

    <br>

    <table style="border-collapse: collapse; width: 100%;" border="1" cellpadding="5">
        <tr>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>UOM</th>
            <th>Amount</th>
        </tr>
        @foreach ($items as $item)
        <tr>
            <td>{{ $item['product_name'] }}</td>
            <td>{{ $item['quantity'] }}</td>
            <td>{{ $item['uom'] }}</td>
            <td>{{ $item['total']}}</td>
        </tr>
        @endforeach

        <tr>
            <th colspan="3" style="text-align: right;">Total</th>
            <th> {{ $order['total_amount'] }}</th>
        </tr>
    </table>

    <p>Please login at the portal <a href="https://a.eraprocure.co.in/">https://a.eraprocure.co.in/</a> to download the Purchase Order.</p>

    <p>Regards,<br>
    <strong>RaProcure</strong><br>
    <a href="https://www.raprocure.com">www.raprocure.com</a></p>
</body>
</html>
