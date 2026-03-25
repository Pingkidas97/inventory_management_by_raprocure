@extends('buyer.layouts.app', ['title'=>$category->category_name.' All Product'])

@section('css')
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar-menu')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1">
        <div class="container-fluid">
            <div class="about-page-details p-2">
                <div class="Top-sec-page">
                    <ul class="breadcrumb-ist">
                        <li class="breadcrumb-menu"><a href="{{ route("buyer.dashboard") }}">Home</a></li>
                        <li class="breadcrumb-menu">{{$category->division->division_name}}</li>
                        <li class="breadcrumb-menu">{{$category->category_name}}</li>
                    </ul>
                    <div class="cat-filter-icon">
                        <span data-sort="2" class="bi bi-sort-up-alt sorting-btn cursor-pointer"></span>
                        <span data-sort="1" class="bi bi-sort-down-alt sorting-btn cursor-pointer active"></span>
                    </div>
                </div>
                <div class="product-serach-box w-100">
                    <form class="d-flex searchBar mb-3">
                        <div class="d-flex w-100"> <i class="bi bi-search me-2"></i>
                            <input type="search" class="search-product" placeholder="Search Your Product">
                        </div>
                        <button class="btn p-0" type="submit" data-bs-toggle="tooltip" data-bs-placement="bottom"
                            title="Start Voice Search">
                        <i class="bi bi-mic-fill font-size-18"></i>
                        </button>
                    </form>
                </div>
                <div class="row cateory-box-container">
                    {{-- <div class="col-lg-3 col-md-4 d-flex align-items-stretch">
                        <div class="category-box light-blue">
                            <h4><a href="#">ADAMITE ROLLS </a></h4>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 d-flex align-items-stretch">
                        <div class="category-box light-pink">
                            <h4><a href="#">ALLOY STEEL ROLLS </a></h4>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 d-flex align-items-stretch">
                        <div class="category-box light-orange">
                            <h4><a href="#">CHILLED ROLLS </a></h4>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 d-flex align-items-stretch">
                        <div class="category-box light-green">
                            <h4><a href="#">COMPOSITE ROLLS </a></h4>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 d-flex align-items-stretch">
                        <div class="category-box light-blue">
                            <h4><a href="#">DPIC ROLLS </a></h4>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 d-flex align-items-stretch">
                        <div class="category-box light-pink">
                            <h4><a href="#">FERRITIC SPHEROIDAL ROLL </a></h4>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 d-flex align-items-stretch">
                        <div class="category-box light-orange">
                            <h4><a href="#">FORGED ROLLS </a></h4>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 d-flex align-items-stretch">
                        <div class="category-box light-green">
                            <h4><a href="#">HARD CHROME PLATED ROLLER </a></h4>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 d-flex align-items-stretch">
                        <div class="category-box light-blue">
                            <h4><a href="#">HSS ROLLS </a></h4>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 d-flex align-items-stretch">
                        <div class="category-box light-pink">
                            <h4><a href="#">SG IRON ROLLS </a></h4>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 d-flex align-items-stretch">
                        <div class="category-box light-orange">
                            <h4><a href="#">TUNGSTEN CARBIDE ROLLS </a></h4>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            loadCategoryProduct();

            // Debounced input event
            $('.search-product').debounceInput(function () {
                let product_name = $(this).val();
                if (product_name.length >= 3 || product_name.length === 0) {
                    loadCategoryProduct();
                }
            }, 200);

            // Sort button click
            $(document).on("click", ".sorting-btn", function () {
                $(".sorting-btn").removeClass("active");
                $(this).addClass("active");
                loadCategoryProduct();
            });
        });
        function loadCategoryProduct(){
            let product_name = '';
            let searchVal = $(".search-product").val();
            if (searchVal.length >= 3 || searchVal.length === 0) {
                product_name = searchVal;
            }
            let sort_by = $(".sorting-btn.active").attr("data-sort");

            $.ajax({
                type: "POST",
                url: '{{ route('buyer.category.get-product') }}',
                dataType: 'json',
                data: {
                    category_id: '{{ $category->id }}',
                    product_name: product_name,
                    sort_by: sort_by,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {},
                success: function(responce) {
                    if (responce.status == false) {
                        toastr.error(responce.message);
                    } else {
                        $(".cateory-box-container").html(responce.products);
                    }
                },
                error: function() {
                    // toastr.error('Something Went Wrong..');
                },
                complete: function() {}
            });
            
        }
    </script>
@endsection