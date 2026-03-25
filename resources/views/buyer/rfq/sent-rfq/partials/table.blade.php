<table class="product-listing-table w-100">
    <thead>
        <tr>
            <th>RFQ No.</th>
            <th>RFQ Date</th>
            <th>Product Name</th>
            <th>PRN Number</th>
            <th>Username</th>
            <th>Branch/Unit</th>
            <th>Responses</th>
            <th>RFQ Status</th>
            <th>RFQ Action</th>
        </tr>
    </thead>
    <tbody>
        @php
            $i = ($results->currentPage() - 1) * $results->perPage() + 1;

            $rfq_status = array(
                1=> "<span class='rfq-status rfq-generate'>RFQ Generated</span>",
                2=> "<span class='rfq-status rfq-generate'>RFQ Generated</span>",
                3=> "<span class='rfq-status rfq-generate'>Active RFQ</span>",
                4=> "<span class='rfq-status counter-offer-sent'>Counter Offer Sent</span>",
                5=> "<span class='rfq-status Partial-order'>Partial Order</span>",
                6=> "<span class='rfq-status counter-offer-received'>Counter Offer Received</span>",
                7=> "<span class='rfq-status rfq-received'>Quotation Received</span>",
                9=> "<span class='rfq-status Partial-order'>Partial Order</span>",
            );
            $auction_scheduled = "<span class='rfq-status counter-offer-sent'>Auction Scheduled</span>";
            $auction_completed = "<span class='rfq-status Auction-Completed'>Auction Completed</span>";
        @endphp

        @forelse ($results as $result)
            <tr class="{{ $result->buyer_rfq_read_status ==1 ? 'list-unread' : '' }} ">
                <td class="clickable-td"><a href="{{ route('buyer.rfq.details', $result->rfq_id) }}?page=active-rfq">{{ $result->rfq_id }}</a></td>
                <td class="clickable-td"><a href="{{ route('buyer.rfq.details', $result->rfq_id) }}?page=active-rfq">{{ date("d/m/Y", strtotime($result->created_at))}}</a></td>
                <td>
                    @php
                        $products = array();
                        foreach ($result->rfqProducts as $variant) {
                            $products[] = $variant->masterProduct?->product_name;
                        }
                        $products_name = implode(', ', $products);
                    @endphp
                    <div class="d-flex">
                        <span class="rfq-product-name text-truncate">
                            {{ $products_name }}
                        </span>
                        @if (strlen($products_name) > 50)
                            <button class="btn btn-link text-black border-0 p-0 font-size-12 bi bi-info-circle-fill ms-1"
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                title="{{ $products_name }}"></button>
                        @endif
                    </div>
                </td>
                <td>{{ $result->prn_no}}</td>
                <td>{{ $result->buyerUser->name}}</td>
                <td>{{ $result->buyerBranch->name}}</td>
                <td>{{ $result->rfq_response_received}}</td>
                <td>
                    {!! $rfq_status[$result->buyer_rfq_status] !!}
                </td>
                <td>
                    <div class="rfq-table-btn-group">
                        <a class="ra-btn small-btn ra-btn-primary" href="{{ route("buyer.rfq.cis-sheet", ['rfq_id'=>$result->rfq_id]) }}">CIS</a>
                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Edit</button>
                        <button class="ra-btn small-btn ra-btn-outline-danger">Close</button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="12">No RFQ found.</td>
            </tr>
        @endforelse

    </tbody>
</table>
<x-paginationwithlength :paginator="$results" />

    