<div class="table-responsive">
    <table class="table table-striped" width="100%">
        <thead>
            <tr>
                <th>Buyer/Vendor Name</th>
                <th>Received On</th>
                <th>Payment Received On</th>
                <th>Validity Period</th>
                <th>Image</th>
                <th>Ad Position</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($advertisements as $ad)
                <tr>
                    <td>{{ $ad->types == 1 ? 'Buyer' : 'Vendor' }}: {{ $ad->buyer_vendor_name }}</td>
                    <td>{{ \Carbon\Carbon::parse($ad->received_on)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($ad->payment_received_on)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($ad->validity_period_from)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($ad->validity_period_to)->format('d/m/Y') }}</td>
                    <td>
                        @if($ad->images)
                            {{ $ad->images }} 
                        @else
                            No Image
                        @endif
                    </td>
                    <td>{{ $ad->ad_position == 1 ? 'Buyer Ads only on the Vendor Side' : 'Vendor Ads only on the Buyer Side' }}</td>
                    <td>{{ \App\Models\Advertisement::getStatus()[$ad->status] ?? $ad->status }}</td>
                    <td>
                        <a href="{{ route('admin.advertisement.edit', $ad->id) }}" class="btn-rfq btn-rfq-secondary btn-sm">Edit</a>
                      
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">No data found</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-paginationwithlength :paginator="$advertisements" />
