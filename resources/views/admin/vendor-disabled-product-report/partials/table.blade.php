<table  class="product_listing_table">
    <thead>
        <tr>
            <th><input type="checkbox" id="select-all-products"></th>
            <th>#</th>
            <th>Vendor Name</th>
            <th>Product Name</th>
            <th>Created At</th>
            <th>Action</th> <!-- New column -->
        </tr>
    </thead>
    <tbody>
        @forelse($products as $product)
        <tr>
            <td style="width: 15px;">
                <input type="checkbox" class="row-checkbox" data-product-id="{{ $product->id }}" data-vendor-id="{{ $product->vendor_id }}">
            </td style="width: 15px;">
            <td>{{ $loop->iteration + ($products->currentPage() - 1) * $products->perPage() }}</td>
            <td>{{ $product->vendor->name ?? '-' }}</td>
            <td>{{ $product->product->division->division_name ?? '-' }} -> {{ $product->product->category->category_name ?? '-' }} <br> {{ $product->product->product_name }}</td>
            <td>{{ $product->created_at->format('d/m/Y') }}</td>
            <td>
               
                <!-- Delete Button -->
                <button type="button"
                        class="btn-rfq btn-sm btn-rfq-danger btn-delete-product"
                        data-id="{{ $product->id }}"
                        title="Delete" id="delete-selected"> 
                     DELETE
                </button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center">No disabled products found.</td>
        </tr>
        @endforelse
    </tbody>
</table>
<x-paginationwithlength :paginator="$products" />
