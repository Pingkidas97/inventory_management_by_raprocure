<?php
namespace App\Http\Controllers\Vendor;
use App\Http\Controllers\Controller;
use App\Models\VendorProduct;
use App\Models\ProductGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class ProductGalleryController extends Controller {
    
    // Method to show the product gallery page
    public function create(VendorProduct $product) {
        $product->load('gallery');
        return view('vendor.products.gallery', compact('product'));
    }

    public function store(Request $request, VendorProduct $product) {
        // Validate the images input field to ensure it's an array of strings (filenames)
        $request->validate(['images' => 'required|array', 'images.*' => 'string', // Validate that each item is a string (filename)
        ]);
        try {
            // Define the target directory for storing images
            $productDir = public_path('uploads/product');
            // Ensure the directory exists
            if (!is_dir($productDir)) {
                mkdir($productDir, 0775, true); // Create the directory if it doesn't exist
                
            }
            foreach ($request->input('images') as $filename) {
                // Check if the temporary file exists
                if (Storage::disk('public')->exists('products/temp/' . $filename)) {
                    // Get the contents of the image
                    $imageContents = Storage::disk('public')->get('products/temp/' . $filename);
                    // Store the original image in the 'uploads/product/' folder
                    file_put_contents($productDir . '/' . $filename, $imageContents);
                    // Delete the temporary image file
                    Storage::disk('public')->delete('products/temp/' . $filename);
                    // Save image data to the database (ProductGallery)
                    ProductGallery::create(['product_id' => $product->id, 'image' => $filename, 'created_by' => auth()->id(), 'updated_by' => auth()->id(), ]);
                }
            }
            return response()->json(['success' => true, 'message' => 'Images uploaded successfully', ]);
        }
        catch(\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error uploading images: ' . $e->getMessage(), ], 500);
        }
    }

    // Method to handle uploading temporary images
    public function uploadTemp(Request $request) {
        // Validate the uploaded files
        $request->validate(['images' => 'required', 'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', ]);
        $uploadedImages = [];
        try {
            foreach ($request->file('images') as $image) {
                // Generate a unique filename with timestamp and replace underscores with hyphens
                $extension = $image->getClientOriginalExtension();
                $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $filename = strtolower(time() . '-' . str_replace('_', '-', $originalName)) . '.' . $extension;
                // Ensure the temporary directory exists
                $tempDir = public_path('storage/products/temp');
                File::ensureDirectoryExists($tempDir);
                // Move the file to the temporary directory
                $image->move($tempDir, $filename);
                // Prepare the response with the temporary URL
                $uploadedImages[] = ['name' => $filename, 'temp_url' => asset('public/storage/products/temp/' . $filename), ];
            }
            return response()->json(['success' => true, 'images' => $uploadedImages, ]);
        }
        catch(\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error uploading temporary images: ' . $e->getMessage(), ], 500);
        }
    }

    // Method to remove a temporary image
    public function removeTemp(Request $request) {
        $request->validate(['image' => 'required|string', ]);
        try {
            Storage::disk('public')->delete('products/temp/' . $request->image);
            return response()->json(['success' => true, 'message' => 'Image removed', ]);
        }
        catch(\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error removing temporary image: ' . $e->getMessage(), ], 500);
        }
    }
    
    // Method to delete an image from the gallery
    public function destroy(Request $request, VendorProduct $product) {
        $request->validate(['image_id' => 'required|integer', 'image_name' => 'required|string', ]);
        try {
            Storage::disk('public')->delete(['products/gallery/' . $request->image_name, 'products/gallery/thumbs/' . $request->image_name, ]);
            ProductGallery::where('id', $request->image_id)->where('product_id', $product->id)->delete();
            return response()->json(['success' => true, 'message' => 'Image deleted successfully', ]);
        }
        catch(\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting image: ' . $e->getMessage(), ], 500);
        }
    }
}
