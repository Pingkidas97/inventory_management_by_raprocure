
@if(count($products) > 0)
    @foreach($products as $k => $v)
        @php
            // $color = $colors[$k % count($colors)];
            $color = $colors->next();
        @endphp
        <div class="col-lg-3 col-md-4 d-flex align-items-stretch">
            <div class="category-box {{$color}}">
                <h4><a href="{{ route('buyer.vendor.product', ['id'=>$v->product_id]) }}">{{ $v->product_name }}</a></h4>
            </div>
        </div>
    @endforeach
@else
    <div class="col-lg-12 col-md-12 d-flex align-items-stretch text-center">
        <h4>No Product Found</h4>
    </div>
@endif