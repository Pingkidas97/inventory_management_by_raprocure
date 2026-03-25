<table class="table ra-table ra-table-stripped ">
    <thead>
        <tr>
            <th scope="col" class="text-nowrap">RFQ No</th>
            <th scope="col" class="text-nowrap">RFQ Date</th>
            <th scope="col" class="text-nowrap">Product</th>
            <th scope="col" class="text-nowrap">Buyer Name</th>
            <th scope="col" class="text-nowrap">Username</th>
            <th scope="col" class="text-nowrap">Auction Date</th>
            <th scope="col" class="text-nowrap">Auction Time</th>
            <th scope="col" class="text-nowrap">Auction Status</th>
            <th scope="col" class="text-nowrap">Action</th>
        </tr>
    </thead>
    <tbody>
        @php
        $order_status=['1'=>'RFQ Received','4'=>'Counter Offer Received','5'=>'Order Confirmed','6'=>'Counter Offer Sent','7'=>'Quotation Sent','8'=>'Closed'];
        $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp

        @forelse ($results as $result)
        <tr> 
            <td class="align-middle">{{ $result->rfq_no }}</td>
            <td class="align-middle">{{ date('d/m/Y', strtotime($result->rfq_auction->rfq->created_at)) }}</td>
            <td>
                @php
                    $variant = $result->rfq_auction->rfq_auction_variant->first();
                    $product = $variant?->product;
                @endphp
                @if($product)
                    {{ $product->division->division_name ?? '-' }} >
                    {{ $product->category->category_name ?? '-' }}<br>
                    {{ $product->product_name }} 
                @else
                    -
                @endif
            </td>
            <td class="align-middle">{{ $result->rfq_auction->buyer->legal_name ?? '-' }}</td>
            <td class="align-middle">{{ $result->rfq_auction->buyer->users->name ?? '-' }}</td>
            <td class="align-middle">{{ date('d/m/Y', strtotime($result->rfq_auction->auction_date)) }}</td>
            <td class="align-middle">{{ date('h:i A', strtotime($result->rfq_auction->auction_start_time)) }} To {{ date('h:i A', strtotime($result->rfq_auction->auction_end_time)) }}</td>
            <td class="align-middle"></td>
            <td class="align-middle">
                <a class="btn-sm btn-rfq-secondary" href=""><span><i class="bi bi-eye"></i></span></a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center">No Data Available in Table</td>
        </tr>
        @endforelse
    </tbody>
</table>

<x-paginationwithlength :paginator="$results" />