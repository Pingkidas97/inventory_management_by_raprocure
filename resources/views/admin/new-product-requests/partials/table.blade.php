@php
$status = [0 => 'Product for Approval', 1 => 'Approved', 2 => 'Query Raised', 3 => 'Re-submitted', 4 => 'Product for
Approval'];
@endphp
<table class="product_listing_table">
    <thead>
        <tr>
            <th>#</th>
            <th style="width:12%;">Vendor Name</th>
            <th style="width:12%;">Received From</th>
            <th>Product Name</th>
            <th>Delete</th>
            <th>Request Received Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @php
        $i = ($products->currentPage() - 1) * $products->perPage() + 1;
        @endphp

        @forelse ($products as $product)
        <tr>
            <td>{{ $i++ }}</td>
            <td>{{ strtoupper($product->vendor->name ?? '-') }}</td>
            <td>{{ strtoupper($product->receivedfrom->name ?? '-') }}</td>
            <td>{{ strtoupper($product->product_name ?? '-') }}</td>
            <td><button class="btn-rfq btn-sm btn-rfq-danger btn-delete-product"
                    data-id="{{ $product->id }}">Delete</button></td>
            <td>{{ \Carbon\Carbon::parse($product->created_at)->format('d/m/Y') }}</td>
            <td>{{ $status[$product->approval_status] ?? '-' }}</td>
            <td>
                <a href="{{ route('admin.new-products.approval', $product->id) }}"
                    class="btn-rfq btn-rfq-secondary btn-sm">Approval</a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">No data available in table</td>
        </tr>
        @endforelse
    </tbody>
</table>

<x-paginationwithlength :paginator="$products" />