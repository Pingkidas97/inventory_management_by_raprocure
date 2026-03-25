<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{   
    
    /**
     * Constructor to check user authorization.
     */
    public function __construct()
    {
        if (auth()->check() && auth()->user()->user_type != 3) {
            abort(403, 'Unauthorized access.');
        }
    }

    /**
     * Display a listing of products with optional filtering.
     *
     * @param Request $request The HTTP request containing filter parameters
     * @param int|null $id Optional category ID to filter products by
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse Returns a view with products or JSON response for AJAX requests
     */
    public function index(Request $request, $id = null)
    {
        $query = Product::with(['division', 'category']);

        if ($id) {
            $query->where('category_id', $id);
        }

        if ($request->filled('product_name')) {
            $query->where('product_name', 'like', '%' . $request->input('product_name') . '%');
        }

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->input('division_id'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $products = $query->orderBy('product_name')->paginate(10);
        $divisions = Division::where('status', 1)->orderBy('division_name')->get();
        $categories = Category::where('status', 1)->orderBy('category_name')->get();

        // Fetch single category and its division
        $categoryName = null;
        $divisionName = null;
        $divisionId = null;

        if ($id) {
            $category = Category::with('division')->find($id);
            if ($category) {
                $categoryName = $category->category_name;
                $divisionName = $category->division->division_name ?? null;
                $divisionId = $category->division_id ?? null;
            }
        }

        if ($request->ajax()) {
            return view('admin.products.partials.table', compact('products'))->render();
        }

        return view('admin.products.index', compact('products', 'divisions', 'categories', 'id', 'categoryName', 'divisionName','divisionId'));
    }

    /**
     * Show the form for creating a new product.
     *
     * @param int|null $id Optional category ID to pre-select in the form
     * @return \Illuminate\View\View Returns the product creation form with divisions and categories
     */
    public function create($id = null)
    {
        $divisions = Division::where('status', 1)->orderBy('division_name')->get();
        $categories = Category::where('status', 1)->orderBy('category_name')->get();
        return view('admin.products.create', compact('divisions', 'categories', 'id'));
    }


    /**
     * Validates master product tags.
     * This method is assumed to exist elsewhere in your controller or a trait.
     * Example placeholder implementation:
     * protected function validate_master_product_tags(?string $tags): array
     * {
     * $errors = [];
     * if (empty($tags)) {
     * return $errors;
     * }
     *
     * $tagArray = array_map('trim', explode(',', $tags));
     * $tagArray = array_unique(array_filter($tagArray));
     *
     * // Example validation: Check if tags are too short or contain invalid characters
     * foreach ($tagArray as $tag) {
     * if (strlen($tag) < 2) {
     * $errors[] = "Tag '{$tag}' is too short. Tags must be at least 2 characters.";
     * }
     * // Add more specific validation rules if needed, e.g., regex for allowed characters
     * }
     *
     * // Example: Check for existing aliases (if aliases must be unique globally or per product)
     * // This part depends on your business logic for aliases.
     * // If aliases must be unique across all products, you'd query the product_alias table here.
     * // For this optimization, we assume the existing validation logic is sufficient.
     *
     * return $errors;
     * }
     */

    public function store(Request $request)
    {
        // 1. Initial Request Validation
        $validator = Validator::make($request->all(), [
            'division_id'   => 'required|exists:divisions,id',
            'category_id'   => 'required|exists:categories,id',
            'product_name'  => 'required|string|max:255|unique:products',
            'tags'          => 'nullable|string', // Accept comma-separated string
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        // 2. Validate tags (aliases) using a dedicated method
        $tags = $request->input('tags');
        // Ensure this method is defined in your controller or a trait it uses.
        // For example: `protected function validate_master_product_tags(string $tags): array`
        $tagErrors = $this->validate_master_product_tags($tags);

        if (!empty($tagErrors)) {
            return response()->json([
                'success' => false,
                'message' => implode('<br>', $tagErrors)
            ]);
        }

        // 3. Database Transaction for Atomicity
        DB::beginTransaction();
        try {
            // 4. Save product using mass assignment
            // Ensure 'division_id', 'category_id', 'product_name', 'created_by' are fillable in your Product model.
            $product = Product::create([
                'division_id'  => $request->division_id,
                'category_id'  => $request->category_id,
                'product_name' => strtoupper($request->product_name),
                'created_by'   => auth()->id(),
                'updated_by'   => auth()->id(), // Often handled by timestamps or model events
            ]);

            // 5. Prepare and save aliases (tags) in a single batch insert
            if (!empty($tags)) {
                $tagArray = array_map('trim', explode(',', $tags));
                $tagArray = array_unique(array_filter($tagArray)); // Clean up: remove empty and duplicate tags

                $aliasesToInsert = [];
                foreach ($tagArray as $alias) {
                    $aliasesToInsert[] = [
                        'product_id' => $product->id,
                        'vendor_id'  => null,
                        'alias_of'   => 1, // Master alias
                        'is_new'     => 1,
                        'alias'      => strtoupper($alias),
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ];
                }

                // Perform a single bulk insert for all aliases
                if (!empty($aliasesToInsert)) {
                    DB::table('product_alias')->insert($aliasesToInsert);
                }
            }

            // 6. Commit transaction if all operations are successful
            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Product created successfully',
                'redirect' => route('admin.products.index')
            ]);

        } catch (\Exception $e) {
            // 7. Rollback transaction on any failure
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create product. Error: ' . $e->getMessage()
            ]);
        }
    }




    /**
     * Show the form for editing the specified product.
     *
     * @param int $id The ID of the product to edit
     * @return \Illuminate\View\View Returns the product edit form with product data, divisions and categories
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $divisions = Division::where('status', 1)->orderBy('division_name')->get();
        $categories = Category::where('status', 1)->orderBy('category_name')->get();

        // Fetch aliases using direct query (like in CI helper)
        $aliasList = \DB::table('product_alias')
            ->where('alias_of', 1)
            ->where('is_new', 1)
            ->where('product_id', $product->id)
            ->pluck('alias')
            ->toArray();

        $aliases = implode(', ', $aliasList);

        $id = $product->category_id;
            
        return view('admin.products.edit', compact('product', 'divisions', 'categories', 'aliases','id'));
    }


     /**
     * Update the specified product in storage.
     *
     * @param Request $request The HTTP request containing updated product data
     * @param int $id The ID of the product to update
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status and message
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // 1. Initial Request Validation
        $validator = Validator::make($request->all(), [
            'division_id'   => 'required|exists:divisions,id',
            'category_id'   => 'required|exists:categories,id',
            'product_name'  => 'required|string|max:255|unique:products,product_name,' . $id, // Exclude current product ID
            'tags'          => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        // 2. Validate tags (aliases) using a dedicated method, passing the product ID
        $tags = $request->input('tags');
        // Ensure this method is defined in your controller or a trait it uses.
        // For example: `protected function validate_master_product_tags(string $tags, int $productId): array`
        $tagErrors = $this->validate_master_product_tags($tags, $product->id);

        if (!empty($tagErrors)) {
            return response()->json([
                'success' => false,
                'message' => implode('<br>', $tagErrors)
            ]);
        }

        // 3. Database Transaction for Atomicity
        DB::beginTransaction();
        try {
            // 4. Update product using the update method on the model instance
            // Ensure 'division_id', 'category_id', 'product_name', 'updated_by' are fillable in your Product model.
            $product->update([
                'division_id'  => $request->division_id,
                'category_id'  => $request->category_id,
                'product_name' => strtoupper($request->product_name),
                'updated_by'   => auth()->id(),
            ]);

            // 5. Delete existing master aliases for this product
            DB::table('product_alias')
                ->where('product_id', $product->id)
                ->where('alias_of', 1) // Assuming 1 means master alias
                ->delete();

            // 6. Prepare and save new aliases (tags) in a single batch insert
            if (!empty($tags)) {
                $tagArray = array_map('trim', explode(',', $tags));
                $tagArray = array_unique(array_filter($tagArray)); // Clean up: remove empty and duplicate tags

                $aliasesToInsert = [];
                foreach ($tagArray as $alias) {
                    $aliasesToInsert[] = [
                        'product_id' => $product->id,
                        'vendor_id'  => null,
                        'alias_of'   => 1,
                        'is_new'     => 1,
                        'alias'      => strtoupper($alias),
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => auth()->id(), // Keep created_by for new aliases
                        'updated_by' => auth()->id(),
                    ];
                }

                // Perform a single bulk insert for all aliases
                if (!empty($aliasesToInsert)) {
                    DB::table('product_alias')->insert($aliasesToInsert);
                }
            }

            // 7. Commit transaction if all operations are successful
            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Product updated successfully',
                'redirect' => route('admin.products.index')
            ]);

        } catch (\Exception $e) {
            // 8. Rollback transaction on any failure
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update product. Error: ' . $e->getMessage()
            ]);
        }
    }


    /**
     * Remove the specified product from storage.
     *
     * @param int $id The ID of the product to delete
     * @return \Illuminate\Http\RedirectResponse Redirects to product index with success message
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    /**
     * Update the status of the specified product.
     *
     * @param Request $request The HTTP request containing the new status
     * @param int $id The ID of the product to update
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status and message
     */
    public function updateStatus(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Fetch related Category
        $category = Category::findOrFail($product->category_id);

        // Check if the related Category is inactive (status = 2)
        if ($category->status == 2) {
            return response()->json([
                'success' => false,
                'message' => 'Product status cannot be updated because its related category is inactive.',
            ]);
        }

        // Update product status if category is active
        $product->status = $request->status;
        $product->updated_by = auth()->id();
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.'
        ]);
    }


    /**
     * Validate product tags/aliases for uniqueness across the system.
     *
     * @param string|null $tags Comma-separated list of tags to validate
     * @param int|null $product_id Optional product ID to exclude from checks (for updates)
     * @return array Returns array of error messages for invalid tags
     */
    public function validate_master_product_tags($tags, $product_id = null)
    {
        $errors = [];

        if (!empty($tags)) {
            $tag_array = array_map('trim', explode(',', $tags));
            $tag_array = array_map('strtoupper', $tag_array);
            $tag_array = array_unique($tag_array);

            foreach ($tag_array as $tag) {
                $tag = substr($tag, 0, 255); // Limit to 255 characters
                $tag = strtoupper($tag);
                $tag = preg_replace('/\s+/', ' ', trim($tag));
                // Check in products
                $existsInMaster = \DB::table('products')->where('product_name', $tag)->first();
                if ($existsInMaster) {
                    $errors[] = "<b>{$tag}</b> is already a Master Product <b>{$existsInMaster->product_name}</b>.";
                    continue;
                }

                // Check in product_alias where alias_of = 1 (Master aliases)
                $masterAliasQuery = \DB::table('product_alias')
                    ->where('alias', $tag)
                    ->where('alias_of', 1);

                if (!empty($product_id)) {
                    $masterAliasQuery->where('product_id', '!=', $product_id);
                }

                $masterAlias = $masterAliasQuery->first();
                if ($masterAlias) {
                    $prodName = $this->get_product_name_by_prod_id($masterAlias->product_id);
                    $errors[] = "<b>{$tag}</b> already used as alias for Master Product <b>{$prodName}</b>.";
                    continue;
                }

                // Check in product_alias where alias_of = 2 (Vendor aliases)
                $vendorAliasQuery = \DB::table('product_alias')
                    ->where('alias', $tag)
                    ->where('alias_of', 2);

                if (!empty($product_id)) {
                    $vendorAliasQuery->where('product_id', '!=', $product_id);
                }

                $vendorAlias = $vendorAliasQuery->first();
                if ($vendorAlias) {
                    $vendorName = $this->get_vendor_name_by_vend_id($vendorAlias->vendor_id);
                    $prodName = $this->get_product_name_by_prod_id($vendorAlias->product_id);
                    $errors[] = "<b>{$tag}</b> already used by Vendor <b>{$vendorName}</b> as an alias for Product <b>{$prodName}</b>.";
                    continue;
                }
            }
        }

        return $errors;
    }

    /**
     * Get a product's name by its ID.
     *
     * @param int $productId The ID of the product to look up
     * @return string|null The product name if found, null otherwise
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If product not found (optional)
     */
    public function get_product_name_by_prod_id($productId)
    {
        $product = Product::select('product_name')
                          ->where('id', $productId)
                          ->first();

        return $product ? $product->product_name : null;
    }

    /**
     * Get a vendor's name by their ID.
     *
     * @param int $vend_id The ID of the vendor/user to look up
     * @return string|null The vendor's name if found, null otherwise
     * @throws \Illuminate\Database\QueryException If database error occurs
     */
    public function get_vendor_name_by_vend_id($vend_id) 
    {
        $vendor = \DB::table('users')
                    ->select('name')
                    ->where('id', $vend_id)
                    ->first();

        return $vendor ? $vendor->name : null;
    }

}