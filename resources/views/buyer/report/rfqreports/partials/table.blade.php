<div class="d-none" data-role="rfq-report-meta" data-has-results="{{ $results->count() > 0 ? 'true' : 'false' }}"></div>
<table class="product-listing-table w-100 text-nowrap">
    <thead>
        <tr>
            <th>RFQ Date</th>
            <th>
                RFQ No
                @if(!empty($totals['total_results']))
                    ({{ number_format($totals['total_results']) }})
                @else
                    ({{ number_format($results->total()) }})
                @endif
            </th>
            <th>Division &gt; Category</th>
            <th>Product</th>
            <th>Branch / Unit</th>
            <th>Username</th>
            <th>RFQ Status</th>
            <th>Total Products</th>
            <th>Vendors RFQ Sent</th>
            <th>
                Counter Offer Given
                ({{ number_format($totals['counter_offer_yes'] ?? 0) }})
            </th>
            <th>
                Total Responses Received
                ({{ number_format($totals['total_responses'] ?? 0) }})
            </th>
            <th>Is Auction</th>
            <th>L1 Value</th>
            <th>H1 Value</th>
            <th>
                Order Given
                ({{ number_format($totals['orders_count'] ?? 0) }})
            </th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($results as $row)
            <tr>
                <td>{{ $row['rfq_date'] ?: '-' }}</td>
                <td><a href="{{ $row['rfq_detail_url'] }}">{{ $row['rfq_no'] }}</a></td>
                <td>
                    @php($divisionCategory = $row['division_category'] ?: '-')
                    <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $divisionCategory }}">{{ $divisionCategory }}</span>
                </td>
                <td>
                    @php($products = $row['products'] ?: '-')
                    <span class="text-truncate1 d-inline-block" style="max-width: 200px;">
                        {{ strlen($products) > 20
                            ? substr($products, 0, 20)
                            : $products  }}
                        @if (strlen($products) > 20)
                            <button class="btn btn-link text-black border-0 p-0 font-size-12 bi bi-info-circle-fill ms-1"
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                title="{{ $products }}"></button>
                        @endif
                    </span>
                </td>
                <td>{{ $row['branch'] ?: '-' }}</td>
                <td>{{ $row['username'] ?: '-' }}</td>
                <td>{{ $row['rfq_status'] }}</td>
                <td>{{ $row['total_products'] }}</td>
                <td>{{ $row['vendors_sent'] }}</td>
                <td>{{ $row['counter_offer'] }}</td>
                <td>{{ $row['responses'] }}</td>
                <td>{{ $row['is_auction'] }}</td>
                <td>
                    @if ($row['l1_value'] !== null)
                        {{ $row['l1_currency'] ? $row['l1_currency'] . ' ' : '' }}{{ number_format($row['l1_value'], 2) }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if ($row['h1_value'] !== null)
                        {{ $row['h1_currency'] ? $row['h1_currency'] . ' ' : '' }}{{ number_format($row['h1_value'], 2) }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ $row['order_count'] > 0 ? 'Yes (' . $row['order_count'] . ')' : 'No' }}</td>
                <td>{{ $row['status'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="16">No RFQ report data found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
<x-paginationwithlength :paginator="$results" />
