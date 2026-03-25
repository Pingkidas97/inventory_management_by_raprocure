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

class NewProductRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = VendorProduct::with(['vendor','receivedfrom'])
            ->where('edit_status', 2) // 1 => New Request
            ->where('approval_status', '!=', 1)
            ->whereNull('group_id')->orderBy('updated_at', 'DESC'); // <-- NEW: Orders by latest updates first;

        if ($request->filled('product_name')) {
            $query->where('product_name', 'like', '%' . $request->input('product_name') . '%');
        }

        if ($request->filled('vendor_name')) {
            $query->whereHas('vendor', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('vendor_name') . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = $request->input('per_page', 25);
        $products = $query->paginate($perPage)->appends($request->all());


        if ($request->ajax()) {
            return view('admin.new-product-requests.partials.table', compact('products'))->render();
        }

        return view('admin.new-product-requests.index', compact('products'));
    }

    public function approval($id)
    {
        $product = VendorProduct::with(['vendor', 'product'])->findOrFail($id);

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

        return view('admin.new-product-requests.approval', compact(
            'product',
            'divisions',
            'dealertypes',
            'uoms',
            'taxes'
        ));
    }


    public function autocomplete(Request $request)
    {
        $search = $request->get('term');
        $words = preg_split('/\s+/', trim($search)); // split input by spaces

        $query = DB::table('products')
            ->leftJoin('product_alias', 'product_alias.product_id', '=', 'products.id')
            ->select('products.id', 'products.product_name')
            ->distinct()
            ->limit(100);

        $query->where(function ($q) use ($words) {
            foreach ($words as $word) {
                $q->where(function ($subQ) use ($word) {
                    $subQ->where('products.product_name', 'like', "%$word%")
                         ->orWhere('product_alias.alias', 'like', "%$word%");
                });
            }
        });

        $results = $query->get();

        $response = [];
        foreach ($results as $item) {
            $response[] = [
                'label' => $item->product_name,
                'value' => $item->product_name,
                'id'    => $item->id,
            ];
        }

        return response()->json($response);
    }




    
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

        $psDesc = strip_tags($request->product_description);
        $psDesc = str_replace("\xc2\xa0", ' ', $psDesc);
        $psDesc = preg_replace('/\s+/', ' ', $psDesc);

        if (empty(trim($psDesc))) {
            return response()->json([
                'status' => 0,
                'message' => 'Product Description is required'
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

        try {
            DB::beginTransaction();

            // Step 1: Insert into master product table if needed
            $prod_id = $request->product_id;
            if (empty($prod_id)) {

                $validator = Validator::make($request->all(), [
                    'product_name' => 'required',
                    'division_id' => 'required',
                    'category_id' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 0,
                        'message' => $validator->errors()->first()
                    ]);
                }

                $master_product = [
                    'division_id'  => $request->division_id,
                    'category_id'  => $request->category_id,
                    'product_name'    => $request->product_name,
                    'status' => 1,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $prod_id = DB::table('products')->insertGetId($master_product);
            }

            // Step 2: Prepare vendor product update
            $productData = [
                'product_id' => $prod_id,
                'description' => $request->product_description,
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
                'edit_status' => 0,
                'verified_by' => auth()->id(),
                'approval_status' => 1,
            ];

            // Upload product image
            if ($request->hasFile('product_image')) {
                $existingImage = VendorProduct::where('id', $request->id)->value('image');
                if (!empty($existingImage)) {
                    $this->deleteProductThumbnails($existingImage);
                }

                $file = $request->file('product_image');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $productDir = public_path('uploads/product');
                File::ensureDirectoryExists($productDir);
                $file->move($productDir, $filename);
                $productData['image'] = $filename;

                $this->createProductThumbnails($filename);
            }

            // Upload additional documents
            $fileFields = [
                'product_catalogue_file' => 'catalogue',
                'product_specification_file' => 'specification_file',
                'product_certificates_file' => 'certificates_file',
                'product_dealership_file' => 'dealership_file'
            ];

            $targetDirectory = public_path('uploads/product/docs');
            File::ensureDirectoryExists($targetDirectory);

            $existingFiles = VendorProduct::where('id', $request->id)->first($fileFields);

            foreach ($fileFields as $inputField => $dbColumn) {
                if ($request->hasFile($inputField)) {
                    if (!empty($existingFiles->$dbColumn)) {
                        $oldFilePath = $targetDirectory . '/' . $existingFiles->$dbColumn;
                        if (File::exists($oldFilePath)) {
                            File::delete($oldFilePath);
                        }
                    }

                    $file = $request->file($inputField);
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $file->move($targetDirectory, $fileName);
                    $productData[$dbColumn] = $fileName;
                }
            }

            VendorProduct::where('id', $request->id)->update($productData);

            // Tags / Aliases
            if (!empty($request->tag)) {
                DB::table('tbl_prod_alias')
                    ->where('prod_id', $prod_id)
                    ->where('vend_id', $request->vend_id)
                    ->where('alias_of', 2)
                    ->delete();

                $tags = array_unique(array_map('trim', explode(',', $request->tag)));
                $aliasData = [];

                foreach ($tags as $tag) {
                    $aliasData[] = [
                        'prod_id' => $prod_id,
                        'vend_id' => $request->vend_id,
                        'alias' => htmlspecialchars(strtoupper(substr($tag, 0, 255)), ENT_QUOTES),
                        'alias_of' => 2,
                        'is_new' => 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                if (!empty($aliasData)) {
                    DB::table('tbl_prod_alias')->insert($aliasData);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Product update approved and saved successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'Error during update: ' . $e->getMessage()
            ]);
        }
    }

    public function update_old(Request $request)
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

        $psDesc = strip_tags($request->product_description);
        $psDesc = str_replace("\xc2\xa0", ' ', $psDesc);
        $psDesc = preg_replace('/\s+/', ' ', $psDesc);

        if (empty(trim($psDesc))) {
            return response()->json([
                'status' => 0,
                'message' => 'Product Description is required'
            ]);
        }

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

        try {
            DB::beginTransaction();

            // Upload Product Image
            if ($request->hasFile('product_image')) {
                $existingImage = VendorProduct::where('id', $request->id)->value('image');

                if (!empty($existingImage)) {
                    $this->deleteProductThumbnails($existingImage);
                }

                $file = $request->file('product_image');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $productDir = public_path('uploads/product');
                File::ensureDirectoryExists($productDir);
                $file->move($productDir, $filename);
                $productData['image'] = $filename;

                $this->createProductThumbnails($filename);
            }

            // Upload other documents
            $fileFields = [
                'product_catalogue_file' => 'catalogue',
                'product_specification_file' => 'specification_file',
                'product_certificates_file' => 'certificates_file',
                'product_dealership_file' => 'dealership_file'
            ];

            $targetDirectory = public_path('uploads/product/docs');
            File::ensureDirectoryExists($targetDirectory);

            $existingFiles = VendorProduct::where('id', $request->id)->first($fileFields);

            foreach ($fileFields as $inputField => $dbColumn) {
                if ($request->hasFile($inputField)) {
                    if (!empty($existingFiles->$dbColumn)) {
                        $oldFilePath = $targetDirectory . '/' . $existingFiles->$dbColumn;
                        if (File::exists($oldFilePath)) {
                            File::delete($oldFilePath);
                        }
                    }

                    $file = $request->file($inputField);
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $file->move($targetDirectory, $fileName);
                    $productData[$dbColumn] = $fileName;
                }
            }

            $productData['edit_status'] = 0;
            $productData['verified_by'] = auth()->id();
            $productData['approval_status'] = 1;

            VendorProduct::where('id', $request->id)->update($productData);

            // Update Tags/Aliases
            if (!empty($request->tag)) {
                DB::table('tbl_prod_alias')
                    ->where('prod_id', $request->prod_id)
                    ->where('vend_id', $request->vend_id)
                    ->where('alias_of', 2)
                    ->delete();

                $tags = array_unique(array_map('trim', explode(',', $request->tag)));
                $aliasData = [];

                foreach ($tags as $tag) {
                    $aliasData[] = [
                        'prod_id' => $request->prod_id,
                        'vend_id' => $request->vend_id,
                        'alias' => htmlspecialchars(strtoupper(substr($tag, 0, 255)), ENT_QUOTES),
                        'alias_of' => 2,
                        'is_new' => 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                if (!empty($aliasData)) {
                    DB::table('tbl_prod_alias')->insert($aliasData);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Product update approved successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'Error during update: ' . $e->getMessage()
            ]);
        }
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

    // Method to delete a new product request
    public function destroy($id)
    {
        $productRequest = VendorProduct::find($id);  // Replace with your actual model

        if ($productRequest) {
            $productRequest->delete();
            return response()->json(['status' => 1, 'message' => 'Product request deleted successfully']);
        }

        return response()->json(['status' => 0, 'message' => 'Product request not found']);
    }
}
