<table class="product_listing_table">
    <thead>
        <tr>
            <th style="width: 2%;"><input type="checkbox" id="select-all-products"></th>
            <th style="width: 2%;">#</th>
            <th>Vendor Name</th>
            <th>Product Name</th>
            <th>Delete</th>
            <th>Status</th>
            <th>Added By Vendor</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @php
        $i = ($products->currentPage() - 1) * $products->perPage() + 1;
        @endphp

        @forelse ($products as $product)
        <tr>
            <td>
                <input type="checkbox" class="product-checkbox" value="{{ $product->id }}" />
            </td>
            <td>{{ $i++ }}</td> {{-- S.No. --}}
            <td>{{ $product->vendor->name ?? '' }}</td>
            <td>{{ $product->product->product_name ?? '' }}</td>
            <td>
                <button class="btn-style btn-style-danger btn-delete-product"
                    data-id="{{ $product->id }}">Delete</button>
            </td>
            <td>
                <span>
                    <label class="switch">
                        <input type="checkbox" class="product-status-toggle" data-id="{{ $product->id }}"
                            {{ $product->vendor_status == '1' ? 'checked' : '' }} />
                        <span class="slider round"></span>
                    </label>
                </span>
            </td>

            <td>{{ $product->created_by_vendor ? 'Yes' : 'No' }}</td>
            <td>
                <a href="{{ route('admin.verified-products.view', $product->id) }}"
                    class="btn-style btn-style-secondary">View</a>
                <a href="{{ route('admin.verified-products.edit', $product->id) }}"
                    class="btn-style btn-style-secondary">Edit</a>
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
<script>
const selectAll = document.getElementById('select-all-products');
const checkboxes = document.querySelectorAll('.product-checkbox');

// When 'Select All' is clicked
selectAll.addEventListener('change', function() {
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// When any individual checkbox is changed
checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        // If any checkbox is unchecked, uncheck 'Select All'
        if (!this.checked) {
            selectAll.checked = false;
        } else {
            // If all checkboxes are checked, check 'Select All'
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            selectAll.checked = allChecked;
        }
    });
});
</script>