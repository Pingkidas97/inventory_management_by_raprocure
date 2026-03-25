<table class="product_listing_table">
    <thead>
        <tr>
            <th>RFQ No</th>
            <th>Auction Date</th>
            <th>Auction Time</th>
            <th>Buyer Name</th>
            <th>Products</th>
            <th>Vendor Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Status</th>
            <th>Is Participated</th>
            <th>Order Confirmed</th>
        </tr>
    </thead>
    <tbody>
        @php
            $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp
        @forelse ($results as $result)
            <tr>
                <td>{{ $result->rfq_no??''}}</td>
                <td>{{ date('d/m/Y', strtotime($result->auction_date)) ?? ''}}</td>
                <td>{{ date('h:i A', strtotime($result->auction_start_time)) }} To {{ date('h:i A', strtotime($result->auction_end_time)) }}</td>
                <td>{{$result->buyer?->legal_name??''}}</td>
                <td>{{$result->rfq_auction_variant->product->product_name ?? ''}}</td>
                <td>{{$result->rfq_vendor_auction->vendor?->legal_name}}</td>
                <td>{{$result->rfq_vendor_auction->vendor?->user->email}}</td>
                <td>{{$result->rfq_vendor_auction->vendor?->user->mobile}}</td>
                <td>{{$result->rfq_vendor_auction->vendor?->user->status==1?'Active':'Inactive'}}</td>
                <td></td>
                <td></td>
            </tr>
        @empty
            <tr>
                <td colspan="12" class="text-center">No data available in table</td>
            </tr>
        @endforelse
    </tbody>
</table>

<x-paginationwithlength :paginator="$results" />