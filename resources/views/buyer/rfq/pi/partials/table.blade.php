<table class="product-listing-table w-100">
    <thead>
        <tr>
            <th>ORDER NO</th>
            <th>ORDER DATE </th>
            <th>VENDOR</th>
            <th>PI Attachment</th>
            <th>PI Date</th>
        </tr>
    </thead>
    <tbody>
        @php
            $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp
        @forelse ($results as $result)
            <tr class="table-tr">
                <td>
                    {{ $result->order_number ?? ''}}
                </td>
                <td>{{ date("d/m/Y", strtotime($result->order_date))}}</td>
                <td>
                    {{ $result->vendorUser->name ?? ''}}
                </td>
                <td><a href="{{ asset('public/uploads/pi-order/'.$result->pi_attachment) }}">Download</a></td>
                <td>
                    {{ date("d/m/Y", strtotime($result->pi_date))}}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="12">No Draft RFQ found.</td>
            </tr>
        @endforelse

    </tbody>
</table>
<x-paginationwithlength :paginator="$results" />

    