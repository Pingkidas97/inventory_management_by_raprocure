<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VendorProduct;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
class VendorProductController extends Controller {
    
    public function create($id) {
        $product = VendorProduct::with(['vendor', 'product', 'receivedfrom'])->findOrFail($id);
        return view('admin.vendor-products.create', compact('product','id'));
    }

    public function bulk_create($id)
    {
        $product = VendorProduct::with(['vendor', 'product', 'receivedfrom'])->findOrFail($id);
        
        // Fetch categories using the adapted get_AllCategoryId method
        $categories = $this->get_AllCategoryId();

        // echo "<pre>";
        // print_r($product);  die();
        
        return view('admin.vendor-products.bulk-create', compact('product', 'id', 'categories'));
    }

    /**
     * Fetch all category IDs grouped by category name
     * @return \Illuminate\Support\Collection|bool
     */
    private function get_AllCategoryId()
    {
        $categories = DB::table('categories')
            ->select(DB::raw('GROUP_CONCAT(id ORDER BY id ASC) as cat_ids, category_name'))
            ->where('status', '1')
            ->groupBy('category_name')
            ->orderBy('category_name')
            ->get();

        return $categories->isNotEmpty() ? $categories : false;
    }

    public function get_products_by_category(Request $request)
    {
        $category_ids = $request->input('category_ids');
        $vendor_id = $request->input('vendor_id');
        $search_key = trim($request->input('prod_name')); // Remove extra spaces

        // Validate category name
        if (empty($category_ids)) {
            return response()->json([
                'status' => 0,
                'message' => 'Category is Required'
            ], 200, [], JSON_PRETTY_PRINT);
        }

        // Convert comma-separated category names to array
        $category_ids = explode(',', $category_ids);

        // Build query
        $query = DB::table('products')
            ->select('products.*')
            ->leftJoin('vendor_products', function ($join) use ($vendor_id) {
                $join->on('products.id', '=', 'vendor_products.product_id')
                     ->where('vendor_products.vendor_id', '=', $vendor_id);
            })
            ->whereIn('products.category_id', function ($subQuery) use ($category_ids) {
                $subQuery->select('id')
                         ->from('categories')
                         ->whereIn('id', $category_ids)
                         ->where('status', '1');
            })
            ->where('products.status', '1')
            ->whereNull('vendor_products.product_id')
            ->orderBy('products.product_name', 'asc');

        // Apply search key filtering
        if (!empty($search_key)) {
            $search_key_arr = explode(' ', $search_key);
            foreach ($search_key_arr as $key) {
                $query->where('products.product_name', 'like', '%' . $key . '%');
            }
        }

        // Execute query
        $products = $query->get();

        // Prepare response
        if ($products->isEmpty()) {
            return response()->json([
                'status' => 0,
                'message' => 'No Product found for the selected category that can be added to the vendor profile'
            ], 200, [], JSON_PRETTY_PRINT);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Products Found',
            'all_products' => $products
        ], 200, [], JSON_PRETTY_PRINT);
    }


    
    
    public function store(Request $request) {
        $validator = Validator::make($request->all(), ['product_name' => 'required', 'product_description' => 'required', 'product_dealer_type' => 'required', 'product_gst' => 'required', ]);
        // Handle validation failure
        if ($request->ajax()) {
            if ($validator->fails()) {
                return response()->json(['status' => 0, 'message' => 'Please fill all the mandatory fields marked with *.', 'errors' => $validator->errors() ], 422);
            }
        } else {
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }
        try {
            DB::beginTransaction();
            $product = new VendorProduct();
            $product->vendor_id = $request->vendor_id;
            $product->product_id = $request->product_id;
            $product->description = strip_tags($request->product_description);
            $product->dealer_type_id = $request->product_dealer_type;
            $product->gst_id = $request->product_gst;
            $product->edit_status = 0;
            $product->approval_status  = 1;
            $product->added_by_user_id  = Auth::id(); // Returns the authenticated user's ID;
            $product->created_at = now();
            $product->updated_at = now();
            if ($request->hasFile('product_image')) {
                $existingImage = DB::table('vendor_products')->where('id', $request->id)->value('image');
                if (!empty($existingImage)) {
                    $this->deleteProductThumbnails($existingImage);
                }
                $file = $request->file('product_image');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $productDir = public_path('uploads/product');
                File::ensureDirectoryExists($productDir);
                $file->move($productDir, $filename);
                $productData->image = $filename;
                $this->createProductThumbnails($filename);
            }
            $product->save();
            DB::commit();
            // Response for AJAX
            if ($request->ajax()) {
                return response()->json(['status' => 1, 'message' => 'Product saved successfully.', 'data' => $product]);
            }
            // Response for normal request
            return redirect()->route('admin.vendor-products.index')->with('success', 'Product saved successfully.');
        }
        catch(\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['status' => 0, 'message' => 'Error saving product: ' . $e->getMessage() ], 500);
            }
            return redirect()->back()->with('error', 'Error saving product: ' . $e->getMessage());
        }
    }
   
    private function createProductThumbnails(string $filename) {
        $manager = new ImageManager(new GdDriver());
        $originalPath = public_path('uploads/product/' . $filename);
        $sizes = ['100' => 100, '250' => 250, '500' => 500, ];
        foreach ($sizes as $folder => $size) {
            $thumbDir = public_path("uploads/product/thumbnails/{$folder}");
            if (!File::exists($thumbDir)) {
                File::makeDirectory($thumbDir, 0755, true);
            }
            $thumb = $manager->read($originalPath)->scale($size, $size);
            $thumb->save($thumbDir . '/' . $filename);
        }
    }
    
    private function deleteProductThumbnails(string $filename) {
        $originalPath = public_path('uploads/product/' . $filename);
        if (File::exists($originalPath)) {
            File::delete($originalPath);
        }
        $thumbSizes = ['100', '250', '500'];
        foreach ($thumbSizes as $size) {
            $thumbPath = public_path("uploads/product/thumbnails/{$size}/{$filename}");
            if (File::exists($thumbPath)) {
                File::delete($thumbPath);
            }
        }
    }


    public function bulkstore(Request $request)
    {  
 

        $request->validate([
            'vendor_id' => 'required|integer|exists:users,id',
            'dealer_type' => 'required|integer',
            'tax_class' => 'required|integer',
            'master_product_id' => 'required|array',
        ]);

        $vendor_id = $request->vendor_id;

        // Check if vendor is active
        $vendorCheck = DB::table('vendors as v')
            ->join('users as u', 'v.user_id', '=', 'u.id')
            ->where('u.status', 1)
            ->where('u.id', $vendor_id)
            ->first();

        if (!$vendorCheck) {
            return response()->json([
                'status' => 0,
                'message' => 'Vendor is inactive or not found!'
            ]);
        }

        // Retrieve products from tbl_product_master
        $products = DB::table('products')
            ->whereIn('id', $request->master_product_id)
            ->get();

        if ($products->isEmpty()) {
            return response()->json([
                'status' => 0,
                'message' => 'No products found with the given master_product_id'
            ]);
        }

        $userId = auth()->id() ?? 1; // fallback to 1 if no auth, adjust as needed

        $dataToInsert = [];

        foreach ($products as $product) {
            $dataToInsert[] = [
                'vendor_id' => $vendor_id,
                'product_id' => $product->id,
                'dealer_type_id' => $request->dealer_type,
                'gst_id' => $request->tax_class,
                'vendor_status' => 1, // active
                'edit_status' => 0,
                'approval_status' => 1,
                'added_by_user_id' => $userId,
                'verified_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $inserted = DB::table('vendor_products')->insert($dataToInsert);

        if ($inserted) {
            return response()->json([
                'status' => 1,
                'message' => 'Products successfully added to the vendor profile'
            ]);
        } else {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to add products to the vendor profile'
            ], 500);
        }
    }

}
