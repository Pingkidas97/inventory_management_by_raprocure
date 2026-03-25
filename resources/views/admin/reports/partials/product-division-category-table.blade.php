<table class="product_listing_table">
    <thead>
        <tr>
            <th>Name of Product</th>
            <th>Division</th>
            <th>Category</th>
            <th>Master Alias</th>
            <th>Vendor Alias</th>
            <th>No Of Vendor Allocated</th>
            <th>Edit</th>
            <th>Added Since	</th>
            <th>Total RFQ Generated</th>
            <th>Total Order Confirmed</th>
            <th>Product Status</th>
        </tr>
    </thead>
    <tbody>
        @php
            $i = ($results->currentPage() - 1) * $results->perPage() + 1;
        @endphp
        @forelse ($results as $result)
            <tr>
                <td>{{ $result->product_name ?? ''}}</td>
                <td>{{ $result->division->division_name ?? ''}}</td>
                <td>{{ $result->category->category_name ?? ''}}</td>
                <td>{{ optional($result->master_alias)->pluck('alias')->implode(', ')}}</td>
                <td>{{ optional($result->vendor_alias)->pluck('alias')->implode(', ')}}</td>
                <td>{{$result->vendor_count}}</td>
                <td>
                    <a href="{{ route('admin.products.edit', $result->id) }}" class="btn-rfq btn-rfq-secondary btn-sm">Edit</a>
                </td>
                <td>{{$result->created_at->format('d/m/Y')}}</td>
                <td>{{$result->rfq_count}}</td>
                <td>{{$result->order_count}}</td>
                <td> 
                    <span>
                        <label class="switch">
                            <input type="checkbox" 
                                   id="checkbox_{{ $result->id }}" 
                                   class="product-status-toggle" 
                                   data-id="{{ $result->id }}"
                                   {{ $result->status == '1' ? 'checked' : '' }}>
                            <span class="slider round"></span>
                        </label>
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="12" class="text-center">No data available in table</td>
            </tr>
        @endforelse
    </tbody>
</table>

<x-paginationwithlength :paginator="$results" />