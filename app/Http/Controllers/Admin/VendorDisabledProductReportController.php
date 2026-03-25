<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\VendorProduct;
use App\Models\ProductAlias;
use App\Models\RfqProduct; // Model for rfq_products

class VendorDisabledProductReportController extends Controller
{
    /**
     * Display a listing of the disabled products report.
     */
    public function index(Request $request)
    {
        $query = VendorProduct::where('vendor_status', 2); //  2 = disabled

        // Optional filters
        if ($request->filled('product_name')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('product_name', 'like', '%' . $request->input('product_name') . '%');
            });
        }

        if ($request->filled('vendor_name')) {
            $query->whereHas('vendor', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('vendor_name') . '%');
            });
        }

        
        // Set dynamic per page value
        $perPage = $request->input('per_page', 25); // Default to 25 if not specified
  
       // Eager load relationships
       $products = $query->with(['vendor', 'product.division', 'product.category'])
                      ->orderBy('id', 'desc')
                      ->paginate($perPage)
                      ->appends($request->all()); // Preserve query params in pagination

        // For AJAX requests (pagination/search)
        if ($request->ajax()) {
            return view('admin.vendor-disabled-product-report.partials.table', compact('products'))->render();
        }

        return view('admin.vendor-disabled-product-report.index', compact('products'));
    }

    // In ProductController.php
    public function bulkDelete(Request $request)
    {
        $product_ids = $request->input('product_ids', []);
        $vendor_ids = $request->input('vendor_ids', []);

        if (empty($product_ids) || empty($vendor_ids) || count($product_ids) !== count($vendor_ids)) {
            return response()->json([
                'status' => 0,
                'message' => "Invalid product or vendor IDs."
            ]);
        }

        $deleted_count = 0;
        $not_deleted_count = 0;

        foreach ($product_ids as $index => $product_id) {
            $vendor_id = $vendor_ids[$index];

            // Find the vendor product
            $vendorProduct = \App\Models\VendorProduct::where('id', $product_id)->first();

            if (!$vendorProduct) {
                $not_deleted_count++;
                continue;
            }

            // Check if this product is used in any RFQ (rfq_products table)
            $rfqProductExists = RfqProduct::where('product_id', $vendorProduct->prod_id)->exists();
            if ($rfqProductExists) {
                $not_deleted_count++;
                continue;
            }



            // Remove product images
            if (!empty($vendorProduct->image)) {
                $imagePaths = [
                    public_path('uploads/product/' . $vendorProduct->image),
                    public_path('uploads/product/compress/' . $vendorProduct->image),
                    public_path('uploads/product/compress/thumbnails/100/' . $vendorProduct->image),
                    public_path('uploads/product/compress/thumbnails/250/' . $vendorProduct->image),
                    public_path('uploads/product/compress/thumbnails/500/' . $vendorProduct->image),
                ];
                foreach ($imagePaths as $imgPath) {
                    if (File::exists($imgPath)) {
                        @File::delete($imgPath);
                    }
                }
            }

            // Delete product from vendor's list
            $vendorProduct->delete();

            // Delete product aliases
            \App\Models\ProductAlias::where('product_id', $vendorProduct->prod_id)
                ->where('vendor_id', $vendor_id)
                ->delete();

            $deleted_count++;
        }

        $message1 = $deleted_count > 0 ? "$deleted_count products successfully deleted." : "No products were deleted.";
        $message2 = $not_deleted_count > 0 ? "$not_deleted_count products could not be deleted." : "No Product Found for Not Deletion.";

        return response()->json([
            'status' => 1,
            'message1' => $message1,
            'message2' => $message2
        ]);
    }

    public function exportTotal(Request $request)
    {
        $query = VendorProduct::with(['vendor', 'product'])
            ->where('approval_status', 0) // 0 for disabled, adjust as needed
            ->orderBy('updated_at', 'desc');

        if ($request->filled('product_name')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('product_name', 'like', '%' . $request->input('product_name') . '%');
            });
        }
        if ($request->filled('vendor_name')) {
            $query->whereHas('vendor', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('vendor_name') . '%');
            });
        }

        $total = $query->count();
        return response()->json(['total' => $total]);
    }

    public function exportBatch(Request $request)
    {
        $offset = intval($request->input('start', 0));
        $limit = intval($request->input('limit', 1000));

        $query = VendorProduct::where('vendor_status', 2); // 2 = disabled

        // Optional filters
        if ($request->filled('product_name')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('product_name', 'like', '%' . $request->input('product_name') . '%');
            });
        }

        if ($request->filled('vendor_name')) {
            $query->whereHas('vendor', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('vendor_name') . '%');
            });
        }

        // Eager load relationships
        $products = $query->with(['vendor', 'product.division', 'product.category'])
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();

        $result = [];
        foreach ($products as $product) {
            $result[] = [
                $product->vendor->name ?? '',
                $product->product->product_name ?? '',
                // Concatenate Division and Category with " > "
                ($product->product->division->division_name ?? '') . ' > ' . ($product->product->category->category_name ?? ''),
                $product->created_at ? $product->created_at->format('d/m/Y') : '',
            ];
        }

        return response()->json(['data' => $result]);
    }




}
