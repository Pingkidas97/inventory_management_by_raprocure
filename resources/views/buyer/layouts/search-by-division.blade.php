<div class="row">
    @foreach($divisionCategoryData as $key => $division)
    <div class="col-md-3 category_subDivision">
        <h3> {{ $division['division_name'] }} </h3>
        <ul class="Catgorydrop-link">
            @foreach ($division['categories'] as $item)
                <li class="item">
                    <a class="text-effect" href="{{ route('buyer.category.product', ['id' => $item['category_id']]) }}"> {{ $item['category_name'] }} </a>
                </li>
            @endforeach
        </ul>
    </div>
    @endforeach
</div>