
@foreach($products->product_vendors as $k => $v)
	<div class="card text-center border rounded shadow-sm p-2 vendor-product-card" style="width: 16rem; position: relative;">

		<!-- Checkbox -->
		<input type="checkbox" class="form-check-input position-absolute vendor-product-checkbox" {{ in_array($v->vendor_profile->user_id, $selected_vendors) ? "checked" : "" }} style="top: 10px; left: 10px;" value="{{ $v->vendor_profile->user_id }}">
		
		<!-- Wishlist & Block Icons -->
		<div class="position-absolute" style="top: 10px; right: 10px;">
			<i class="bi bi-heart-fill text-primary me-1"></i>
			<i class="bi bi-ban"></i>
		</div>
		
		<!-- Image -->
		@php
			$image_url = '';
		@endphp
		@if (!empty($v->image) && is_file(public_path('uploads/product/thumbnails/250/'.$v->image)))
			@php
				$image_url = url('public/uploads/product/thumbnails/250/'.$v->image);
			@endphp
		@endif

		@if($image_url=='')
			<div class="bg-opacity-25 d-flex align-items-center justify-content-center mb-2" style="height: 150px;background-color:#b9deea;">
				<strong class="text-dark">{{ $v->vendor_profile->legal_name }} </strong>
			</div>
		@else
			<div class="bg-opacity-25 d-flex align-items-center justify-content-center mb-2" style="height: 150px;">
				<img src="{{ $image_url }}" alt="{{ $v->vendor_profile->legal_name }}" style="width: 200px;">
			</div>
		@endif
		
		<!-- Vendor Info -->
		<h6 class="text-primary mb-0">{{ $v->vendor_profile->legal_name }} </h6>
		<small class="text-muted">{!! htmlEntityDecodeWithLimit($v->description, 20) !!}</small>
		
		<!-- Product Info -->
		<p class="fw-bold mt-2 mb-1">{{ $products->product_name }}</p>
		<p class="mb-2">
			<i class="bi bi-telephone"></i> +{{ $v->vendor_profile->vendor_country->phonecode }} {{ $v->vendor_profile->user->mobile }}
			{{-- <i class="bi bi-telephone"></i> {{ $v->vendor_profile->vendor_country->id }} --}}
		</p>
		
		<!-- Buttons -->
		<div class="d-flex justify-content-center gap-2 mb-2">
			<button class="btn btn-outline-primary btn-sm">
			<i class="bi bi-send"></i> MESSAGE
			</button>
			<button class="btn btn-primary btn-sm add-this-vendor-product">
			<i class="bi bi-cart-plus"></i> ADD RFQ
			</button>
		</div>

	</div>
@endforeach