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
use Illuminate\Support\Facades\Auth;

class MultipleProductController extends Controller
{
    public function index($id = null)
    {
        $dealertypes = DB::table('dealer_types')
            ->where('status', '1')
            ->get();
        $uoms = DB::table('uoms')
            ->where('status', '1')
            ->get();
        $taxes = DB::table('taxes')
            ->where('status', '1')
            ->get();
        return view('vendor.products.add-multiple-product', compact('dealertypes', 'uoms', 'taxes', 'id'));
    }

    public function autocomplete(Request $request)
    {
        $search = $request->get('term');
        $vendorId =  getParentUserId();
        
        if (empty($vendorId)) {
            return response()->json([]);
        }

        $words = preg_split('/\s+/', trim($search));

        $query = DB::table('products')
            ->leftJoin('product_alias', 'product_alias.product_id', '=', 'products.id')
            ->whereNotExists(function ($query) use ($vendorId) {
                $query->select(DB::raw(1))
                      ->from('vendor_products')
                      ->whereRaw('vendor_products.product_id = products.id')
                      ->where('vendor_products.vendor_id', $vendorId);
            })
            ->select('products.id', 'products.product_name')
            ->distinct()
            ->limit(50); // Changed from 100 to 50 as requested

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

    public function storeMultipleProducts(Request $request)
    {
        $request->validate([
            'products' => 'required|array|min:1',
            'products.*.checked' => 'required|in:0,1',
        ]);

        $validatedProducts = [];
        $vendorId = getParentUserId();
        $currentTime = now();
        $createdCount = 0;
        $groupId = \Illuminate\Support\Str::uuid()->toString(); // Use UUID for unique group_id

        foreach ($request->products as $index => $product) {
            if ($product['checked'] != 1) {
                continue;
            }

            // Validate only checked products
            $validator = Validator::make($product, [
                'product_id' => 'required|numeric',
                'ps_desc' => 'required|string|max:500',
                'dealer_type' => 'required|exists:dealer_types,id',
                'tax_class' => 'required|exists:taxes,id',
                'ean_code' => 'required|numeric|digits_between:2,8',
                'product_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => "Row {$index}: " . $validator->errors()->first(),
                ]);
            }

            // Handle file upload
            $imagePath = null;
            if ($request->hasFile("products.$index.product_image")) {
                $file = $request->file("products.$index.product_image");
                $extension = $file->getClientOriginalExtension();
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $filename = strtolower(time() . '-' . str_replace('_', '-', $originalName)) . '.' . $extension;

                $productDir = public_path('Uploads/product');
                File::ensureDirectoryExists($productDir);

                $file->move($productDir, $filename);
                $imagePath = $filename;

                if (method_exists($this, 'createProductThumbnails')) {
                    $this->createProductThumbnails($filename);
                }
            }

            // Insert into DB
            DB::table('vendor_products')->insert([
                'vendor_id' => $vendorId,
                'product_id' => $product['product_id'],
                'image' => $imagePath,
                'description' => $product['ps_desc'],
                'dealer_type_id' => $product['dealer_type'],
                'gst_id' => $product['tax_class'],
                'hsn_code' => $product['ean_code'],
                'group_id' => $groupId, // Add group_id
                'vendor_status' => '1',
                'edit_status' => '3',
                'approval_status' => '4',
                'added_by_user_id' => $vendorId,
                'created_at' => $currentTime,
                'updated_at' => $currentTime,
            ]);

            $createdCount++;
        }

        return response()->json([
            'status' => 1,
            'message' => "Successfully created {$createdCount} product(s)",
            'count' => $createdCount,
            'group_id' => $groupId,
        ]);
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


}
