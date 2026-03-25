<table class="table ra-table ra-table-stripped">
    <thead>
        <tr>
            <th style="width: 50px;">#</th>
            <th class="text-start">Product</th>
            {{-- Changed to text-start for better alignment --}}
            <th class="text-center">Gallery</th>
            <th class="text-center">Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $index => $product)
        <tr>
            <td>{{ $products->firstItem() + $index }}</td>
            <td>
                <div class="align-items-center" style="display: flex;">
                    <!-- Check if the product has an image and the image file exists in the directory -->
                    @php $imagePath = public_path('uploads/product/thumbnails/100/' . $product->image); @endphp @if ($product->image && file_exists($imagePath))
                    <img src="{{ asset('public/uploads/product/thumbnails/100/' . $product->image) }}" alt="{{ $product->image }}" width="50" height="50" style="max-width: 50px; max-height: 50px; border-radius: 0.35rem;" />
                    @else
                    <img src="{{ asset('public/uploads/product/small-product-placeholder.png') }}" alt="Default Image" width="50" height="50" style="max-width: 50px; max-height: 50px; border-radius: 0.35rem;" />
                    @endif

                    <div style="display: block; padding: 5px;">
                        <!-- Display division and category -->
                        {{ $product->product->division->division_name ?? 'N/A' }} > {{ $product->product->category->category_name ?? 'N/A' }} <br />

                        <!-- Display product name -->
                        {{ $product->product->product_name ?? 'N/A' }}
                    </div>
                </div>
            </td>

            <td class="text-center align-middle p-0">
                <button type="button" class="ra-btn ra-btn-link" aria-label="Upload">
                    <span class="bi bi-upload font-size-20" aria-hidden="true"></span>
                </button>
            </td>
            <td class="text-center align-middle">
                <label class="ra-switch-checkbox">
                    <input type="checkbox" name="status" {{ $product->status ? 'checked' : '' }} />
                    <span class="slider round"></span>
                </label>
            </td>
            <td>
                <a href="{{ route('vendor.products.edit', $product->id) }}" class="ra-btn ra-btn-primary height-inherit btn-sm">
                    Edit
                </a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">NO DATA FOUND</td>
            {{-- Colspan should match the number of columns --}}
        </tr>
        @endforelse
    </tbody>
</table>
<x-paginationwithlength :paginator="$products" />
