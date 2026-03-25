
@if ($is_suggesation=="yes" && $page==1)
    <li class="suggesation-line">
        <a href="javascript:void(0)" class="show-searched-product" >
            <p>
                <span class="">
                    Showing result for "{{ $product_name }}" 0 record found
                    <span class="float-right need-help-from-searching">Need Help?</span>
                </span>
            </p>
        </a>
    </li>
    <li class="suggesation-line">
        <a href="javascript:void(0)" class="show-searched-product">
            <p>
                <span class="">
                    Suggestions are:
                </span>
            </p>
        </a>
    </li>
@endif

@foreach($products as $k => $v)
    @php
        if($source == 'search'){
            $url = route('buyer.vendor.product', ['id'=>$v->product_id]);
        }else{
            $url = 'javascript:void(0);';
        }
    @endphp
    <li>
        <a href="{{ $url }}" class="show-searched-product" data-id="{{$v->product_id}}" data-name="{{$v->product_name}}">
            <p>
                <span class="">
                    {{ $v->product_name }}
                </span> 
                &gt; 
                {{ $v->category_name }}
                &gt;
                {{ $v->division_name }}
            </p>
        </a>
    </li>
@endforeach