<table class="product_listing_table">
    <thead>
        <tr>
            <th>Name Of Buyer</th>
            <th>Primary Contact	</th>
            <th>Phone No</th>
            <th>Email</th>
            <th>No. of Accounts Created</th>
            <th>Total RFQ Generated</th>
            <th>Total Bulk RFQ Generated</th>
            <th>Total Offers Received</th>
            <th>Total Orders Confirmed</th>
            <th>Value (Of Confirmed Orders)</th>
            <th>Last Login Date</th>
        </tr>
    </thead>
    <tbody>
        @php
            $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp
        @forelse ($results as $result)
            <tr>
                <td>{{ $result->legal_name??''}}</td>
                <td>{{ $result->users->name ?? ''}}</td>
                <td>{{ $result->users->country_code ?? ''}} {{ $result->users->mobile ?? ''}}</td>
                <td>{{ $result->users->email ?? ''}}</td>
                <td>{{($result->buyerUser?$result->buyerUser->count()+1:'').'/'.($result->users->latestPlan?($result->users->latestPlan->plan_id==11?$result->users->latestPlan->no_of_users:$result->users->latestPlan->plan->no_of_user):0)}}</td>
                <td>{{$result->rfqs?$result->rfqs->count():0}}</td>
                <td>{{$result->rfqs?$result->rfqs->where('is_bulk_rfq',1)->count():0}}</td>
                <td></td>
                <td>{{$result->orders?$result->orders->where('order_status',3)->count():0}}</td>
                <td>{{ $result->orders ? '₹ '.$result->orders->where('order_status', 3)->sum('order_total_amount'):''}}</td>
                <td>{{ $result->getLastLoginDate($result->user_id) ?? ''}}</td>
            </tr>
        @empty
            <tr>
                <td colspan="12" class="text-center">No data available in table</td>
            </tr>
        @endforelse
    </tbody>
</table>

<x-paginationwithlength :paginator="$results" />