<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Division;
use App\Models\Product;
use App\Models\VendorProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class BulkApprovalProductsController extends Controller
{
   
    public function index(Request $request)
    {
        $subQuery = VendorProduct::select(DB::raw('MIN(id) as id'))
            ->where('edit_status',  '!=' ,2)
            ->where('approval_status', '!=', 1)
            ->whereNotNull('group_id');

        if ($request->filled('product_name')) {
            $subQuery->whereHas('product', function ($q) use ($request) {
                $q->where('product_name', 'like', '%' . $request->input('product_name') . '%');
            });
        }

        if ($request->filled('vendor_name')) {
            $subQuery->whereHas('vendor', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('vendor_name') . '%');
            });
        }

        if ($request->filled('status')) {
            $subQuery->where('status', $request->input('status'));
        }

        $groupedIds = $subQuery->groupBy('group_id')->pluck('id');

        $query = VendorProduct::with(['vendor','receivedfrom'])
            ->whereIn('id', $groupedIds);

        $perPage = $request->input('per_page', 25);
        $products = $query->paginate($perPage)->appends($request->all());

        if ($request->ajax()) {
            return view('admin.bulk-approval-products.partials.table', compact('products'))->render();
        }

        return view('admin.bulk-approval-products.index', compact('products'));
    }

    public function approval($id, $group_id)
    {
        $products = VendorProduct::with(['vendor', 'product'])
            ->where('edit_status', '!=', 2)
            ->where('approval_status', '!=', 1)
            ->where('group_id', $group_id)
            ->get();

        $divisions = Division::where('status', 1)->orderBy('division_name')->get();
        
        $dealertypes = DB::table('dealer_types')
            ->where('status', '1')
            ->get();

        $uoms = DB::table('uoms')
            ->where('status', '1')
            ->get();

        $taxes = DB::table('taxes')
            ->where('status', '1')
            ->get();

        return view('admin.bulk-approval-products.approval', compact(
            'products',
            'divisions',
            'dealertypes',
            'uoms',
            'taxes'
        ));
    }


    public function updateMultiple(Request $request)
    {
        $errors = [];

        foreach ($request->products as $index => $productData) {
            $productId = $productData['id'] ?? null;
            $description = trim($productData['product_description'] ?? '');
            $dealerType = trim($productData['dealer_type'] ?? '');
            $taxClass = $productData['tax_class'] ?? '';
            $hsn = trim($productData['hsn'] ?? '');

            // Manual validation
            $rowErrors = [];

            if (empty($productId) || !VendorProduct::find($productId)) {
                $rowErrors['id'] = 'Invalid product ID.';
            }
            if ($description === '') {
                $rowErrors['product_description'] = 'Description is required.';
            }
            if ($dealerType === '') {
                $rowErrors['dealer_type'] = 'Dealer type is required.';
            }
            if (!is_numeric($taxClass)) {
                $rowErrors['tax_class'] = 'GST rate must be numeric.';
            }
            if ($hsn === '') {
                $rowErrors['hsn'] = 'HSN code is required.';
            }

            if (!empty($rowErrors)) {
                $errors[$index] = $rowErrors;
                continue; // Skip this product
            }

            // Passed all checks, proceed with update
            $product = VendorProduct::find($productId);
            $product->description = $description;
            $product->dealer_type_id = $dealerType;
            $product->gst_id = $taxClass;
            $product->hsn_code = $hsn;

            // Image handling
            if ($request->hasFile("products.$index.product_image")) {
                $existingImage = $product->image;
                if (!empty($existingImage)) {
                    $this->deleteProductThumbnails($existingImage);
                }

                $file = $request->file("products.$index.product_image");
                $filename = time() . '_' . $file->getClientOriginalExtension();
                $productDir = public_path('uploads/product');
                \Illuminate\Support\Facades\File::ensureDirectoryExists($productDir);
                $file->move($productDir, $filename);

                $product->image = $filename;
                $this->createProductThumbnails($filename);
            }

            $product->edit_status = 0;
            $product->approval_status = 1;
            $product->verified_by = auth()->id();
            $product->save();
        }

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'errors' => $errors,
                'message' => 'Some products failed to update.',
            ], 422);
        }

        return response()->json(['success' => true]);
    }






    private function createProductThumbnails(string $filename)
    {
        $manager = new ImageManager(new Driver());
        $originalPath = public_path('uploads/product/' . $filename);

        $sizes = ['100' => 100, '250' => 250, '500' => 500];

        foreach ($sizes as $folder => $size) {
            $thumbDir = public_path("uploads/product/thumbnails/{$folder}");
            File::ensureDirectoryExists($thumbDir);

            $thumb = $manager->read($originalPath)->scale($size, $size);
            $thumb->save($thumbDir . '/' . $filename);
        }
    }

    private function deleteProductThumbnails(string $filename)
    {
        $originalPath = public_path('uploads/product/' . $filename);
        if (File::exists($originalPath)) {
            File::delete($originalPath);
        }

        foreach (['100', '250', '500'] as $size) {
            $thumbPath = public_path("uploads/product/thumbnails/{$size}/{$filename}");
            if (File::exists($thumbPath)) {
                File::delete($thumbPath);
            }
        }
    }

    public function deleteMultiple(Request $request)
    {
        try {
            $ids = $request->input('ids');
            
            // Delete the products (adjust this based on your model)
            VendorProduct::whereIn('id', $ids)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Products deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

  
}
