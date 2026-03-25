<table class="product_listing_table">
    <thead>
        <tr>
            <th>RFQ No</th>
            <th>RFQ Date</th>
            <th>Buyer Name</th>
            <th>Products</th>
            <th>Vendor Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Quote Given</th>
            <th>Status</th>
            <th>Order Confirmed</th>
        </tr>
    </thead>
    <tbody>
        @forelse($summary as $row)
            <tr>
                <td>{{ $row['rfq_no'] }}</td>
                <td>{{ $row['rfq_date'] }}</td>
                <td>{{ $row['buyer_name'] }}</td>
                <td>
                    @php
                        $products = $row['products'];
                    @endphp

                    @if(strlen($products) > 50)
                        {{ Str::limit($products, 50) }}
                        <i class="fas fa-info-circle text-primary ms-2"
                           data-bs-toggle="tooltip"
                           data-bs-placement="top"
                           title="{{ $products }}"
                           style="cursor: pointer;"></i>
                    @else
                        {{ $products }}
                    @endif
                </td>

                <td>{{ $row['vendor_name'] }}</td>
                <td>{{ $row['email'] }}</td>
                <td>{{ $row['mobile'] }}</td>
                <td>{{ $row['quote_given'] }}</td>
                <td>{{ $row['status'] }}</td>
                <td>{{ $row['order_confirmed'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center">No data available in table</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- Use the paginator object for pagination --}}
<x-paginationwithlength :paginator="$summary" />
