

@foreach($products->product_vendors as $k => $v)
    <div class="card shadow-sm p-2 mb-3 vendor-product-card" style="max-width: 350px;margin-right: 10px;">
        <div class="d-flex align-items-start">
            <input type="checkbox" class="form-check-input me-2 mt-1 vendor-product-checkbox" value="{{ $v->vendor_profile->user_id }}" />

            @php
                $image_url = '';
            @endphp
            @if (!empty($v->image) && is_file(public_path('uploads/product/thumbnails/250/'.$v->image)))
                @php
                    $image_url = url('public/uploads/product/thumbnails/250/'.$v->image);
                @endphp
            @endif
            
            @if($image_url=='')
                <div class="bg-opacity-25 d-flex align-items-center justify-content-center mb-2 rounded me-3" style="width: 120px; height: 150px; object-fit: cover;background-color:#b9deea;">
                    <strong class="text-dark">{{ $v->vendor_profile->legal_name }} </strong>
                </div>
            @else
                <div class="bg-opacity-25 d-flex align-items-center justify-content-center mb-2" style="height: 150px;">
                    <img src="{{ $image_url }}" alt="{{ $v->vendor_profile->legal_name }}" class="rounded me-3" style="width: 120px; height: 150px; object-fit: cover;">
                </div>
            @endif
            
            <div class="flex-grow-1">
                <h6 class="text-primary fw-bold mb-1" style="font-size: 0.95rem;">{{ $v->vendor_profile->legal_name }}</h6>
                <p class="mb-1" style="font-size: 0.85rem; min-height: 30px;">{!! htmlEntityDecodeWithLimit($v->description, 20) !!}</p>
                <p class="fw-semibold mb-1" style="font-size: 0.9rem; min-height: 25px;">{{ $products->product_name }}</p>
                <p class="mb-2 text-muted" style="font-size: 0.85rem;">
                    <i class="bi bi-phone me-1"></i> +{{ $v->vendor_profile->vendor_country->phonecode }} {{ $v->vendor_profile->user->mobile }}
                </p>
                <button class="btn btn-primary btn-sm add-this-vendor-product-to-draft" type="button">
                    <i class="bi bi-plus-lg me-1"></i>ADD RFQ
                </button>
            </div>
        </div>
    </div>
@endforeach