<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\VendorProduct;
use App\Models\User;
use App\Models\Division;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use App\Exports\VerifiedProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class VendorProductController extends Controller
{
    public function index()
    {
        $vendorId = getParentUserId();

        $approvedCount = VendorProduct::where('approval_status', 1)
            ->where('vendor_id', $vendorId)
            ->count();

        $pendingCount = VendorProduct::where('approval_status', '!=', 1)
            ->where('vendor_id', $vendorId)
            ->count();

        $divisions = Division::where('status', '1')
            ->orderBy('division_name')
            ->get();

        return view('vendor.products.index', compact('approvedCount', 'pendingCount', 'divisions'));
    }

    public function approvedList(Request $request)
    {
        $vendorId = getParentUserId(); // Your vendor scoping logic

        $query = VendorProduct::with(['product.division', 'product.category'])
            ->where('approval_status', 1)
            ->where('vendor_id', $vendorId);

        if ($request->filled('product_name')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('product_name', 'like', '%' . $request->product_name . '%');
            });
        }

        if ($request->filled('division')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('division_id', $request->division);
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        // Order by updated_at
        $products = $query
            ->orderBy('updated_at', 'desc') // This will order by updated_at in descending order
            ->paginate(25)
            ->appends($request->query());

        return view('vendor.products.partials.approved', compact('products'))->render();
    }

    public function pendingList(Request $request)
    {
        $vendorId = getParentUserId();

        $query = VendorProduct::with(['product.division', 'product.category'])
            ->where('approval_status', '!=', 1)
            ->where('vendor_id', $vendorId);

        if ($request->filled('product_name')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('product_name', 'like', '%' . $request->product_name . '%');
            });
        }

        if ($request->filled('division')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('division_id', $request->division);
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }
        // Order by updated_at
        $products = $query
            ->orderBy('updated_at', 'desc') // This will order by updated_at in descending order
            ->paginate(25)
            ->appends($request->query());

        return view('vendor.products.partials.pending', compact('products'))->render();
    }

    public function getCategoriesByDivision($division_id)
    {
        $categories = Category::where('division_id', $division_id)
            ->where('status', '1')
            ->orderBy('category_name')
            ->get();

        return response()->json($categories);
    }

    public function create($id = null)
    {
        $divisions = Division::where('status', 1)
            ->orderBy('division_name')
            ->get();
        $categories = Category::where('status', 1)
            ->orderBy('category_name')
            ->get();
        $dealertypes = DB::table('dealer_types')
            ->where('status', '1')
            ->get();
        $uoms = DB::table('uoms')
            ->where('status', '1')
            ->get();
        $taxes = DB::table('taxes')
            ->where('status', '1')
            ->get();
        return view('vendor.products.create', compact('divisions', 'categories', 'dealertypes','uoms','taxes', 'id'));
    }

    public function autocomplete(Request $request)
    {
        $search = $request->get('term');
        $vendor_id = getParentUserId();
        $words = preg_split('/\s+/', trim($search)); // Split input by spaces

        $query = DB::table('products')
            ->leftJoin('product_alias', 'product_alias.product_id', '=', 'products.id')
            ->leftJoin('divisions', 'divisions.id', '=', 'products.division_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->select(
                'products.id',
                'products.product_name',
                'divisions.division_name',
                'categories.category_name'
            )
            ->distinct()
            ->limit(100);

        if ($vendor_id) {
            $query->whereNotExists(function ($subQuery) use ($vendor_id) {
                $subQuery
                    ->select(DB::raw(1))
                    ->from('vendor_products')
                    ->whereColumn('vendor_products.product_id', 'products.id')
                    ->where('vendor_products.vendor_id', $vendor_id);
            });
        }

        $query->where(function ($q) use ($words) {
            foreach ($words as $word) {
                $q->where(function ($subQ) use ($word) {
                    $subQ->where('products.product_name', 'like', "%$word%")
                         ->orWhere('product_alias.alias', 'like', "%$word%")
                         ->orWhere('divisions.division_name', 'like', "%$word%")
                         ->orWhere('categories.category_name', 'like', "%$word%");
                });
            }
        });

        $results = $query->get();

        $response = [];
        foreach ($results as $item) {
            $division = $item->division_name ?? 'N/A';
            $category = $item->category_name ?? 'N/A';
            $product = $item->product_name ?? 'N/A';

            $response[] = [
                'label' => "<strong>{$division}</strong> > {$category} > {$product}",
                'value' => $product,
                'id'    => $item->id,
                'division' => $division,
                'category' => $category,
            ];
        }

        return response()->json($response);
    }

    public function store(Request $request)
    {   
        
        $validator = Validator::make($request->all(), [
            'product_name' => 'required',
            'description' => 'required',
            'hsn_code' => 'required|digits_between:2,8',
            'gst' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
            ]);
        }

        // Clean and validate product description
        $psDesc = strip_tags($request->description);
        $psDesc = str_replace("\xc2\xa0", ' ', $psDesc);
        $psDesc = preg_replace('/\s+/', ' ', $psDesc);

        if (empty(trim($psDesc))) {
            return response()->json([
                'status' => 0,
                'message' => 'Product Description is Required',
            ]);
        }

        // Validate product tags using the helper
        $tagErrors = validate_product_tags(
            $request->tag,
            null, // No product_id yet, as this is a new product
            $request->vendor_id,
            true // isNew = true for adding
        );

        if (!empty($tagErrors)) {
            return response()->json([
                'status' => 0,
                'message' => 'Product has alias errors',
                'alias_error_message' => $tagErrors,
            ]);
        }

        try {
            DB::beginTransaction();
            // Check if product_name already exists in products table when product_id is empty
            if (empty($request->product_id)) {
                $existingProduct = DB::table('products')
                    ->where('product_name', $request->product_name)
                    ->first();

                if ($existingProduct) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'Product name already exists in products.',
                    ]);
                }

                // Check if product_name already exists in product_alias table
                $existingAlias = DB::table('product_alias')
                    ->where('alias', $request->product_name)
                    ->first();

                if ($existingAlias) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'Product name already exists as alias.',
                    ]);
                }
            }

            $vendorId = getParentUserId();
            $product = new VendorProduct();
            $product->product_id = $request->product_id ?? 0;  
            if(empty($request->product_id)){
              $product->product_name = strtoupper(preg_replace('/\s+/', ' ', trim($request->product_name)));
            }
            $product->description = strip_tags($request->description, '<a><b><h1><p><div><strong><ul><li>');
            $product->dealer_type_id = $request->dealer_type;
            $product->gst_id = $request->gst;
            $product->hsn_code = $request->hsn_code;
            $product->dealership = $request->dealership;
            $product->brand = $request->brand;
            $product->country_of_origin = $request->country_origin;
            $product->vendor_id = $vendorId;
            $product->added_by_user_id = auth()->id();
            $product->edit_status = empty($request->product_id) ? 2 : 3;
            $product->approval_status = 4;

            // Product Image
            if ($request->hasFile('product_image')) {
                // Delete old image if it exists
                if ($product->image && File::exists(public_path('Uploads/product/' . $product->image))) {
                    File::delete(public_path('Uploads/product/' . $product->image));
                }

                $file = $request->file('product_image');
                $extension = $file->getClientOriginalExtension();
                // Create a unique name with timestamp and convert to lowercase and replace underscores with hyphens
                $filename = strtolower(time() . ' - ' . str_replace('_', '-', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))) . '.' . $extension;

                $productDir = public_path('uploads/product');
                File::ensureDirectoryExists($productDir);

                $file->move($productDir, $filename);
                $product->image = $filename;

                // If you have a thumbnail creation method
                if (method_exists($this, 'createProductThumbnails')) {
                    $this->createProductThumbnails($filename);
                }
            }

            // Other file uploads
            $fileFields = [
                'product_catalogue' => 'catalogue',
                'dealership_attachment' => 'dealership_file',
            ];

            $targetDirectory = public_path('Uploads/product/docs');
            File::ensureDirectoryExists($targetDirectory);

            foreach ($fileFields as $field => $column) {
                if ($request->hasFile($field)) {
                    // Delete old file if it exists
                    if ($product->$column && File::exists(public_path('Uploads/product/docs/' . $product->$column))) {
                        File::delete(public_path('Uploads/product/docs/' . $product->$column));
                    }

                    $file = $request->file($field);
                    // Create a unique name with timestamp, lowercase, and replace underscores with hyphens
                    $fileName = strtolower(time() . ' - ' . str_replace('_', '-', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))) . '.' . $file->getClientOriginalExtension();
                    $file->move($targetDirectory, $fileName);
                    $product->$column = $fileName;
                }
            }

            // Save product (insert)
            $product->save();
            $this->checkAndInsertProductAliases($request->tag, $request->product_id,$vendorId);
           

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Product added successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'Error adding product: ' . $e->getMessage(),
            ]);
        }
    }

    public function edit($id)
    {
        $product = VendorProduct::with('product', 'product.division', 'product.category')->findOrFail($id);
        $dealertypes = DB::table('dealer_types')
            ->where('status', '1')
            ->get();
        $uoms = DB::table('uoms')
            ->where('status', '1')
            ->get();
        $taxes = DB::table('taxes')
            ->where('status', '1')
            ->get();

        return view('vendor.products.edit', compact('product', 'dealertypes', 'uoms', 'taxes', 'id'));
    }

    public function update(Request $request, $id)
    {
        $vendorId = getParentUserId();
        $product = VendorProduct::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'product_name' => 'required',
            'description' => 'required',
            'hsn_code' => 'required|digits_between:2,8',
            'gst' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
            ]);
        }

        // Clean and validate product description
        $psDesc = strip_tags($request->description);
        $psDesc = str_replace("\xc2\xa0", ' ', $psDesc);
        $psDesc = preg_replace('/\s+/', ' ', $psDesc);

        if (empty(trim($psDesc))) {
            return response()->json([
                'status' => 0,
                'message' => 'Product Description is Required',
            ]);
        }

        if (!empty($tagErrors)) {
            return response()->json([
                'status' => 0,
                'message' => 'Product has alias errors',
                'alias_error_message' => $tagErrors,
            ]);
        }

        try {
            DB::beginTransaction();

            // Update product using Eloquent
            $product->product_id = $request->product_id ?? 0;  // Set to 0 if product_id is empty
            $product->description = strip_tags($request->description, '<a><b><h1><p><div><strong><ul><li>');
            $product->dealer_type_id = $request->dealer_type ?? '1';
            $product->gst_id = $request->gst;
            $product->hsn_code = $request->hsn_code;
            $product->dealership = $request->dealership;
            $product->brand = $request->brand;
            $product->country_of_origin = $request->country_origin;
            $product->vendor_id = auth()->id();
            $product->added_by_user_id = auth()->id();
            // Check if product_id is empty, then set edit_status to 2, otherwise set it to 3
            $product->edit_status = empty($request->product_id) ? 2 : 1;
            $product->approval_status = 4;
            // Set updated_at to the current date and time
            $product->updated_at = Carbon::now();

            // Product Image
            if ($request->hasFile('product_image')) {
                // Delete old image if it exists
                if ($product->image && File::exists(public_path('Uploads/product/' . $product->image))) {
                    File::delete(public_path('Uploads/product/' . $product->image));
                }

                $file = $request->file('product_image');
                $extension = $file->getClientOriginalExtension();
                // Create a filename using timestamp, replace underscores with hyphens, convert to lowercase, and remove spaces
                $filename = strtolower(time() . '-' . str_replace(['_', ' ', '%20'], ['-', '', ''], pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))) . '.' . $extension;

                // Remove any space after the timestamp and before the filename
                $filename = str_replace(' ', '', $filename);

                $productDir = public_path('Uploads/product');
                File::ensureDirectoryExists($productDir);

                $file->move($productDir, $filename);
                $product->image = $filename;

                // If you have a thumbnail creation method
                if (method_exists($this, 'createProductThumbnails')) {
                    $this->createProductThumbnails($filename);
                }
            }

            // Other file uploads
            $fileFields = [
                'product_catalogue' => 'catalogue',
                'dealership_attachment' => 'dealership_file',
            ];

            $targetDirectory = public_path('Uploads/product/docs');
            File::ensureDirectoryExists($targetDirectory);

            foreach ($fileFields as $field => $column) {
                if ($request->hasFile($field)) {
                    // Delete old file if it exists
                    if ($product->$column && File::exists(public_path('Uploads/product/docs/' . $product->$column))) {
                        File::delete(public_path('Uploads/product/docs/' . $product->$column));
                    }

                    $file = $request->file($field);
                    // Create a filename using timestamp, replace underscores with hyphens, convert to lowercase, and remove spaces
                    $fileName = strtolower(time() . '-' . str_replace(['_', ' ', '%20'], ['-', '', ''], pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))) . '.' . $file->getClientOriginalExtension();

                    // Remove any space after the timestamp and before the filename
                    $filename = str_replace(' ', '', $fileName);

                    $file->move($targetDirectory, $fileName);
                    $product->$column = $fileName;
                }
            }

            // Save product (update)
            $product->save();
            $this->checkAndInsertProductAliases($request->tag, $product->product_id, $product->vendor_id);
            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Product updated successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'Error updating product: ' . $e->getMessage(),
            ]);
        }
    }

    private function createProductThumbnails(string $filename)
    {
        $manager = new ImageManager(new GdDriver());
        $originalPath = public_path('uploads/product/' . $filename);

        $sizes = [
            '100' => 100,
            '250' => 250,
            '500' => 500,
        ];

        foreach ($sizes as $folder => $size) {
            $thumbDir = public_path("uploads/product/thumbnails/{$folder}");
            if (!File::exists($thumbDir)) {
                File::makeDirectory($thumbDir, 0755, true);
            }

            $thumb = $manager->read($originalPath)->scale($size, $size);
            $thumb->save($thumbDir . '/' . $filename);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        // Find the product by ID
        $product = VendorProduct::find($id);

        if ($product) {
            // Update the product's status
            $product->vendor_status = $request->status;
            $product->save();

            // Set the success message based on the new status
            $statusMessage = $product->vendor_status == 1 
                            ? 'Product Status Successfully Active' 
                            : 'Product Status Successfully InActive';

            return response()->json(['success' => true, 'message' => $statusMessage]);
        }

        return response()->json(['success' => false], 400);
    }

    // Function to check if alias exists in products and product_alias
    public function aliasExists($tag)
    {
        // Check if the alias exists in the products table
        $productExists = DB::table('products')
            ->where('product_name', 'LIKE', '%' . $tag . '%')
            ->exists();

        // Check if the alias exists in the product_alias table
        $aliasExists = DB::table('product_alias')
            ->where('alias', '=', $tag)
            ->exists();

        return $productExists || $aliasExists; // Return true if alias exists in either table
    }

    // Function to check and insert product aliases on update
    public function checkAndInsertProductAliases($tags, $productId, $vendorId)
    {
        if (!is_array($tags)) {
            $tags = explode(',', $tags); // If it's a string, split it into an array
        }

        $tags = array_map('trim', $tags); // Trim spaces
        $tags = array_map('strtoupper', $tags); // Convert to uppercase
        $tags = array_unique($tags); // Ensure tags are unique

        $aliasData = [];

        // Get the existing aliases for the product from the database
        $existingAliases = DB::table('product_alias')
            ->where('product_id', $productId)
            ->pluck('alias')
            ->toArray();

        // Find the aliases that need to be removed (tags that are no longer part of the update)
        $tagsToRemove = array_diff($existingAliases, $tags);

        // Remove the aliases that are no longer included in the updated tags
        if (!empty($tagsToRemove)) {
            DB::table('product_alias')
                ->whereIn('alias', $tagsToRemove)
                ->delete();
        }

        foreach ($tags as $tag) {
            $tag = substr($tag, 0, 255); // Ensure alias doesn't exceed column length
            $tag = preg_replace('/\s+/', ' ', trim($tag)); // Clean up the tag (remove extra spaces)
            $tag = htmlspecialchars($tag, ENT_QUOTES); // Prevent XSS

            // Check if alias exists in either products or product_alias
            if (!empty($tag) && !$this->aliasExists($tag)) {
                // Use the method from the same class
                $aliasData[] = [
                    'product_id' => $productId,
                    'vendor_id' => $vendorId,
                    'alias' => $tag,
                    'alias_of' => 2, // 2 indicates the alias belongs to a vendor
                    'is_new' => 0, // is_new = 0 for updates
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert new aliases if any valid alias is found
        if (!empty($aliasData)) {
            DB::table('product_alias')->insert($aliasData);
        }
    }

    
}
