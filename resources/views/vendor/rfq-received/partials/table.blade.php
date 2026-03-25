<table class="table ra-table ra-table-stripped ">
    <thead>
        <th scope="col" class="text-nowrap">
            RFQ No
        </th>
        <th scope="col" class="text-nowrap">
            RFQ Date
        </th>
        <th scope="col" class="text-nowrap">
            Product
        </th>
        <th scope="col" class="text-nowrap">
            Buyer Name
        </th>
        <th scope="col" class="text-nowrap">
            Username
        </th>
        <th scope="col" class="text-nowrap">
            RFQ Status
        </th>
        <th scope="col" class="text-nowrap">
            Action
        </th>
    </thead>
    <tbody>
        @php
        $order_status=[
                        '1'=>'<span class="badge badge-success text-start">RFQ Received</span>',
                        '4'=>'Counter Offer Received',
                        '5'=>'<span class="badge badge-primary text-start">Order Confirmed</span>',
                        '6'=>'<span class="badge badge-pink text-start">Counter Offer Sent</span>',
                        '7'=>'<span class="badge badge-pink text-start">Quotation Received</span>',
                        '8'=>'<span class="badge badge-danger text-start">Closed</span>'];
        $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp

        @forelse ($results as $result)
        <tr>
            @php
                $productNames = $result->rfqProducts
                    ->pluck('masterProduct.product_name')
                    ->filter()
                    ->values();
                $fullText = $productNames->join(', ');
                $words = str_word_count($fullText, 1);
                $shortText = implode(' ', array_slice($words, 0, 10));
                $isTruncated = count($words) > 10;
            @endphp 
            @php
                $product = $result->rfqProducts->first()?->masterProduct;
            @endphp

            <td class="align-middle">{{ $result->rfq_id }}</td>
            <td class="align-middle">{{ date('d/m/Y', strtotime($result->created_at)) }}</td>
            <td>
                @if($product)
                    {{ $product->division->division_name ?? '-' }} >
                    {{ $product->category->category_name ?? '-' }}<br>
                    {{ $product->product_name }} 
                @else
                    -
                @endif
            </td>
            <td class="align-middle">{{ $result->buyer->legal_name ?? '-' }}</td>
            <td class="align-middle">{{ $result->buyer->users->name ?? '-' }}</td>
            <td class="align-middle">{!! $order_status[$result->vendor_status] ?? '-' !!}</td>
            <td>
               <a class="ra-btn ra-btn-outline-primary-light py-2 height-inherit" href="{{ route('vendor.rfq.reply', ['rfq_id' => $result->rfq_id]) }}">Edit</a>

            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">No Data Available in Table</td>
        </tr>
        @endforelse
    </tbody>
</table>

<x-paginationwithlength :paginator="$results" />