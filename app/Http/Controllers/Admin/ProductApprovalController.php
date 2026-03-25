<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VendorProduct;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class ProductApprovalController extends Controller
{
    public function index(Request $request)
    {
        $query = VendorProduct::with(['vendor', 'product' , 'receivedfrom'])
            ->where('edit_status', 3)
            ->where('approval_status', '!=', 1)
            ->whereNull('group_id'); // equivalent to IS NULL

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

        $perPage = $request->input('per_page', 25);
        $products = $query->paginate($perPage)->appends($request->all());

        if ($request->ajax()) {
            return view('admin.products-for-approval.partials.table', compact('products'))->render();
        }

        return view('admin.products-for-approval.index', compact('products'));
    }

    public function approval($id)
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

        return view('admin.products-for-approval.approval', compact(
            'product',
            'dealertypes',
            'uoms',
            'taxes'
        ));
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


    

 
}