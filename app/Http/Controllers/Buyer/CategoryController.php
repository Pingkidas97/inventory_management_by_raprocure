<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\LiveVendorProduct;
use App\Helpers\CircularArray;


class CategoryController extends Controller
{
    public function index(Request $request, $category_id)
    {
        $category = Category::with('division')->find($category_id);
        if(empty($category)){
            session()->flash('error', "Category not found");
            return redirect()->to(route('buyer.dashboard'));
        }
        // echo "<pre>";
        // print_r($category);
        // die;

        return view('buyer.category.product', compact('category'));
    }
    public function getCategoryProduct(Request $request)
    {
        $category_id = $request->category_id;
        $category = Category::find($category_id);

        if(empty($category)){
            return response()->json([
                'status' => false,
                'message' => 'Category not found'
            ]);
        }

        $products = LiveVendorProduct::getCategoryProduct($request);
        $colors_list = ['light-blue', 'light-pink', 'light-orange', 'light-green'];
        $colors = new CircularArray($colors_list);

        $product_html = view('buyer.category.product-item', compact('products', 'colors'))->render();

        return response()->json([
            'status' => true,
            'message' => 'Category Product found',
            'products' => $product_html
        ]);
    }
}
