@extends('buyer.layouts.app', ['title'=> $category->category_name.' All Product', 'sub_title'=>'Create'])

@section('css')
<style>
    .category-product {
        padding: 6px;
    }
    .product {
        padding: 6px;
        border: 2px solid blue;
        border-radius: 8px;
        background-color:#b9deea;
    }
    a.product-title {
        color: black;
    }
</style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3>Category Product</h3>
        </div>
        <div class="card-body">
            <div class="row product-section">
                
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            loadCategoryProduct();
        });
        function loadCategoryProduct(){
            $.ajax({
                async: false,
                type: "POST",
                url: '{{ route('buyer.category.get-product') }}',
                dataType: 'json',
                data: {
                    category_id: '{{ $category->id }}',
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {},
                success: function(responce) {
                    if (responce.status == false) {
                        toastr.error(responce.message);
                    } else {
                        $(".product-section").html(responce.products);
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