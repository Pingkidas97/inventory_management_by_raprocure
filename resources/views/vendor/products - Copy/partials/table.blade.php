{{-- resources/views/admin/manage-products/partials/table.blade.php --}}
<table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Product</th>
            <th>Vendor</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    @forelse ($products as $index => $product)
        <tr>
            <td>{{ $products->firstItem() + $index }}</td>
            <td>{{ $product->product->product_name ?? '-' }}</td>
            <td>{{ $product->vendor->name ?? '-' }}</td>
            <td>
                <input type="checkbox" class="form-check-input product-status-toggle"
                       data-id="{{ $product->id }}" {{ $product->status ? 'checked' : '' }}>
            </td>
            <td>
                <a href="#" class="btn btn-sm btn-primary">Edit</a>
                <button class="btn btn-sm btn-danger btn-delete-product" data-id="{{ $product->id }}">Delete</button>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center">No products found.</td>
        </tr>
    @endforelse
    </tbody>
</table>
<x-paginationwithlength :paginator="$products" />
