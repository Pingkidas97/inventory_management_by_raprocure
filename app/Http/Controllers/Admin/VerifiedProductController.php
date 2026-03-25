<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VendorProduct;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use App\Exports\VerifiedProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
// use App\Models\ExportJob;
// use App\Jobs\ProcessVerifiedProductsExport;


/**
 * Controller for managing verified vendor products in the admin panel
 * 
 * This controller handles operations related to verified vendor products including:
 * - Listing and filtering verified products
 * - Viewing product details
 * - Editing and updating product information
 * - Managing product status (active/inactive)
 * - Deleting products
 * - Exporting product data to Excel
 * - Managing product tags/badges
 */
class VerifiedProductController extends Controller
{   

    /**
     * Display a paginated list of verified products with filtering capabilities
     * 
     * @param Request $request HTTP request containing filter parameters
     * @return \Illuminate\View\View|\Illuminate\Http\Response Returns view for HTML or partial table for AJAX
     */
    public function index(Request $request)
    {   
        // slected column  
        $query = VendorProduct::with(['vendor', 'product'])->where('approval_status', 1)->orderBy('updated_at', 'desc'); // Order by updated_at in descending order
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

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = $request->input('per_page', 25); // default to 25 if not present
        $products = $query->paginate($perPage)->appends($request->all());

        if ($request->ajax()) {
            return view('admin.verified-products.partials.table', compact('products'))->render();
        }

        return view('admin.verified-products.index', compact('products'));
    }

   
    /**
     * Display detailed view of a single verified product
     * 
     * @param int $id Product ID
     * @return \Illuminate\View\View Returns product detail view
     */
    public function show($id)
    {
        $product = VendorProduct::with(['vendor', 'product'])->findOrFail($id);

        $dealertypes = DB::table('dealer_types')
            ->where('status', '1')
            ->get();

        $uoms = DB::table('uoms')
            ->where('status', '1')
            ->get();

        $taxes = DB::table('taxes')
            ->where('status', '1')
            ->get();

        return view('admin.verified-products.view', compact(
            'product',
            'dealertypes',
            'uoms',
            'taxes'
        ));
    }

    
    /**
     * Display edit form for a verified product
     * 
     * @param int $id Product ID
     * @return \Illuminate\View\View Returns product edit form
     */
    public function edit($id)
    {
        $product = VendorProduct::with(['vendor', 'product'])->findOrFail($id);

        $dealertypes = DB::table('dealer_types')
            ->where('status', '1')
            ->get();

        $uoms = DB::table('uoms')
            ->where('status', '1')
            ->get();

        $taxes = DB::table('taxes')
            ->where('status', '1')
            ->get();

        return view('admin.verified-products.edit', compact(
            'product',
            'dealertypes',
            'uoms',
            'taxes'
        ));
    }

    
    /**
     * Update verified product information
     * 
     * @param Request $request HTTP request containing updated product data
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success/error status
     */
    public function update(Request $request)
    {   


        $validator = Validator::make($request->all(), [
            'product_name' => 'required',
            'product_description' => 'required',
            'product_hsn_code' => 'required|digits_between:2,8',
            'product_dealer_type' => 'required',
            'product_uom' => 'required',
            'product_gst' => 'required',
           
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first()
            ]);
        }

        // Clean and validate product description
        $psDesc = strip_tags($request->product_description);
        $psDesc = str_replace("\xc2\xa0", ' ', $psDesc);
        $psDesc = preg_replace('/\s+/', ' ', $psDesc);

        if (empty(trim($psDesc))) {
            return response()->json([
                'status' => 0,
                'message' => 'Product Description is Required'
            ]);
        }

        // Validate product tags using the helper
        $tagErrors = validate_product_tags(
            $request->tag,
            $request->product_id,  // Current product ID
            $request->vendor_id,   // Current vendor ID
            false                  // isNew = false for update
        );

        if (!empty($tagErrors)) {
            return response()->json([
                'status' => 0,
                'message' => 'Product has alias errors',
                'alias_error_message' => $tagErrors,
            ]);
        }


        // Prepare product data
        $productData = [
            'description' => strip_tags($request->product_description, '<a><b><h1><p><div><strong><ul><li>'),
            'dealer_type_id' => $request->product_dealer_type,
            'uom' => $request->product_uom ?? 0,
            'gst_id' => $request->product_gst,
            'hsn_code' => $request->product_hsn_code ?? 0,
            'specification' => $request->product_specifications,
            'size' => $request->product_size,
            'certificates' => $request->product_certification,
            'dealership' => $request->product_dealership,
            'packaging' => $request->product_packaging,
            'model_no' => $request->product_model_no,
            'gorw' => $request->product_gorw,
            'gorw_year' => $request->product_gorw_year ?? 0,
            'gorw_month' => $request->product_gorw_month ?? 0,
            'brand' => $request->brand_name,
            'country_of_origin' => $request->product_country_origin,
        ];

        // Handle file uploads
        try {
           DB::beginTransaction();
          // Product Image
          if ($request->hasFile('product_image')) {
            $existingImage = DB::table('vendor_products')
                ->where('id', $request->id)
                ->value('image');

            if (!empty($existingImage)) {
                $this->deleteProductThumbnails($existingImage);
            }

            $file = $request->file('product_image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;

            $productDir = public_path('uploads/product');
            File::ensureDirectoryExists($productDir);

            $file->move($productDir, $filename);
            $productData['image'] = $filename;

            $this->createProductThumbnails($filename);
          }


           
           // Other file uploads
            $fileFields = [
                'product_catalogue_file' => 'catalogue',
                'product_specification_file' => 'specification_file',
                'product_certificates_file' => 'certificates_file',
                'product_dealership_file' => 'dealership_file'
            ];

            $targetDirectory = public_path('uploads/product/docs');

            // Ensure the directory exists
            if (!File::exists($targetDirectory)) {
                File::makeDirectory($targetDirectory, 0755, true);
            }

            // Fetch existing file info from DB
            $existingFiles = DB::table('vendor_products')
                ->where('id', $request->id)
                ->select(array_values($fileFields))
                ->first();

            foreach ($fileFields as $field => $column) {
                if ($request->hasFile($field)) {
                    // Delete old file if exists
                    if (!empty($existingFiles->$column)) {
                        $oldFilePath = $targetDirectory . '/' . $existingFiles->$column;
                        if (File::exists($oldFilePath)) {
                            File::delete($oldFilePath);
                        }
                    }

                    // Upload new file
                    $file = $request->file($field);
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $file->move($targetDirectory, $fileName);

                    // Save new filename in DB field
                    $productData[$column] = $fileName;
                }
            }
           
           
            $productData['edit_status'] = 0;
            $productData['verified_by'] = auth()->id();
            $productData['approval_status'] = 1;
            

            // Update product
            DB::table('vendor_products')
                ->where('id',$request->id)
                ->update($productData);



            // Handle tags/aliases
            if (!empty($request->tag)) {
                // Delete old aliases
                DB::table('product_alias')
                    ->where('product_id', $request->product_id)
                    ->where('vendor_id', $request->vendor_id)
                    ->where('alias_of', 2)
                    ->delete();

                // Process and insert new tags
                $tags = explode(',', $request->tag);
                $tags = array_map('trim', $tags);
                $tags = array_map('strtoupper', $tags);
                $tags = array_unique($tags);

                $aliasData = [];
                foreach ($tags as $tag) {
                    $tag = substr($tag, 0, 255);
                    $tag = preg_replace('/\s+/', ' ', trim($tag));
                    $tag = htmlspecialchars($tag, ENT_QUOTES);

                    if (!empty($tag)) {
                        $aliasData[] = [
                            'product_id' => $request->product_id,
                            'vendor_id' => $request->vendor_id,
                            'alias' => $tag,
                            'alias_of' => 2,
                            'is_new' => 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                }

                if (!empty($aliasData)) {
                    DB::table('product_alias')->insert($aliasData);
                }
            }

             DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Product updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'Error updating product: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Create thumbnail versions of product image
     * 
     * @param string $filename Original image filename
     * @return void
     */
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
    
     /**
     * Delete product image and its thumbnails
     * 
     * @param string $filename Image filename to delete
     * @return void
     */
    private function deleteProductThumbnails(string $filename)
    {
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
    
    /**
     * Update product status (active/inactive)
     * 
     * @param Request $request HTTP request containing new status
     * @param int $id Product ID
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status
     */
    public function updateStatus(Request $request, $id)
    {
        $product = VendorProduct::findOrFail($id);
        $product->status = $request->status;
        $product->save();

        return response()->json(['success' => true, 'message' => 'Status updated successfully']);
    }
    
    /**
     * Delete a verified product
     * 
     * @param int $id Product ID to delete
     * @return \Illuminate\Http\JsonResponse Returns JSON response with success status
     */
    public function destroy($id)
    {
        VendorProduct::destroy($id);
        return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
    }
    
    /**
     * Initiate export of verified products to Excel
     * 
     * @param Request $request HTTP request containing filter parameters
     * @return \Illuminate\Http\JsonResponse Returns JSON with export ID for tracking
     */
    public function export(Request $request)
    {
        ini_set('memory_limit', '2048M'); // 2GB
        set_time_limit(3000); // 5 minutes
        $filters = $request->only('product_name', 'vendor_name','status');
        $exportId = Str::uuid()->toString();
        $filePath = "/exports/$exportId.xlsx";
        cache()->put("export_progress:$exportId", 0, now()->addHours(1));
        Excel::queue(new VerifiedProductsExport($filters), $filePath)->chain([
            new \App\Jobs\NotifyExportFinished($exportId),
        ]);
        return response()->json(['export_id' => $exportId]);
    }
    
    /**
     * Check progress of an export job
     * 
     * @param string $id Export job ID
     * @return \Illuminate\Http\JsonResponse Returns JSON with progress percentage
     */
    public function buyerBatchProgress($id)
    {
        $progress = cache()->get("export_progress:$id", 0);
        return response()->json(['progress' => $progress]);
    }

     /**
     * Download completed export file
     * 
     * @param string $id Export job ID
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse Returns Excel file download
     */
    public function downloadBuyerExport($id)
    {
        $file = public_path("exports/$id.xlsx");
        if (!file_exists($file)) {
            abort(404);
        }
        return response()->download($file)->deleteFileAfterSend();
    }
    

    /**
     * Get total count of verified products matching filters
     * 
     * @param Request $request HTTP request containing filter parameters
     * @return \Illuminate\Http\JsonResponse Returns JSON with total count
     */
    public function exportTotal(Request $request)
    {
        $query = VendorProduct::with(['vendor', 'product'])->where('approval_status', 1)->orderBy('updated_at', 'desc'); // Order by updated_at in descending order
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
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        $total = $query->count();
        return response()->json(['total' => $total]);
    }
    
    /**
     * Get batch of verified products for export
     * 
     * @param Request $request HTTP request with pagination parameters
     * @return \Illuminate\Http\JsonResponse Returns JSON with product data batch
     */
    public function exportBatch(Request $request)
    {
        $offset = intval($request->input('start'));
        $limit = intval($request->input('limit'));
        $query=VendorProduct::query()
            ->leftJoin('users', 'users.id', '=', 'vendor_products.vendor_id')
            ->leftJoin('products', 'products.id', '=', 'vendor_products.product_id')
            ->leftJoin('divisions as d', 'd.id', '=', 'products.division_id')
            ->leftJoin('categories as c', 'c.id', '=', 'products.category_id')
            ->leftJoin('users as added_by', 'added_by.id', '=', 'vendor_products.added_by_user_id')
            ->leftJoin('users as verified_by', 'verified_by.id', '=', 'vendor_products.verified_by')
            ->where('vendor_products.edit_status', 0)
            ->where('vendor_products.approval_status', 1)
            ->orderBy('users.name')
            ->select(
                'vendor_products.product_id',
                'vendor_products.vendor_id',
                'users.name as vendor_name',
                'd.division_name as division_name',
                'c.category_name as category_name',
                'products.product_name as product_name',
                'added_by.name as added_by',
                'verified_by.name as verified_by'
            );

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
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        $products = $query->offset($offset)->limit($limit)->get();
        $result=[];
        foreach($products as $product){
            $result[]=[
                $product->vendor_name,
                $product->division_name,
                $product->category_name,
                $product->product_name,
                $this->getAliasesByProduct($product->product_id, $product->vendor_id),
                $product->added_by,
                $product->verified_by,
            ];
        }
        return response()->json(['data'=>$result]);
    }
    
    /**
     * Get product aliases/alternate names
     * 
     * @param int $productId Product ID
     * @param int|null $vendorId Vendor ID (optional)
     * @return string Comma-separated list of aliases
     */
    protected function getAliasesByProduct($productId, $vendorId = null)
    {
        $key = $productId . '-' . $vendorId;
        //return $this->aliasesByProduct[$key]->pluck('alias')->implode(', ') ?? '';
        if (isset($this->aliasesByProduct[$key]) && $this->aliasesByProduct[$key]->isNotEmpty()) {
            return $this->aliasesByProduct[$key]->pluck('alias')->implode(', ');
        }
        return ''; 
    }
    // public function updateTags(Request $request)
    // {
    //     $request->validate([
    //         'product_ids' => 'required|array',
    //         'prod_tag' => 'required|string',
    //         'valid_months' => 'nullable|numeric'
    //     ]);

    //     $products = Product::whereIn('id', $request->product_ids)->get();

    //     foreach ($products as $product) {
    //         $product->badge = $request->prod_tag;
    //         $product->badge_valid_till = $request->prod_tag !== 'NOTHING'
    //             ? now()->addMonths($request->valid_months)
    //             : null;
    //         $product->save();
    //     }

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Products updated successfully.'
    //     ]);
    // }
    
    /**
     * Update tags/badges for multiple products
     * 
     * @param Request $request HTTP request with product IDs and tag data
     * @return \Illuminate\Http\JsonResponse Returns JSON with success/error status
     */
    public function updateTags(Request $request)
    {   
        $productIds = $request->input('product_ids');
        $prodTag = $request->input('prod_tag');
        $validMonths = $request->input('valid_months');

        if (empty($productIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Data! Please select at least one product.'
            ]);
        }

        if ($prodTag !== 'NOTHING' && (empty($prodTag) || empty($validMonths))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Data! Please select badge and time period.'
            ]);
        }

        $validDate = null;
        if ($prodTag !== 'NOTHING') {
            $validDate = date('Y-m-d', strtotime("+$validMonths months"));
        } else {
            $prodTag = null;
        }

        $updated = \DB::table('vendor_products')
            ->whereIn('id', $productIds)
            ->update([
                'product_tag' => $prodTag,
                'product_tag_valid_date' => $validDate,
                'updated_at' => now(),
            ]);

        if ($updated) {
            return response()->json([
                'status' => 'success',
                'message' => 'Products updated successfully!'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating products. Please try again.'
            ]);
        }
    }


}

