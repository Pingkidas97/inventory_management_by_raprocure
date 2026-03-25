<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Division;
use App\Models\Category;
use App\Models\VendorProduct;
use App\Models\Product;
use App\Models\RfqProduct;
use App\Helpers\CircularArray;
use DB;

class VendorProductController extends Controller
{
    public function index(Request $request, $product_id)
    {
        $product = Product::where('status', 1)->find($product_id);
        if(empty($product)){
            session()->flash('error', "Product not found");
            return redirect()->to(route('buyer.dashboard'));
        }
        // echo "<pre>";
        // print_r($category);
        // die;

        $dealer_types = DB::table("dealer_types")
                            ->select("id", "dealer_type")
                            ->where("status", 1)
                            ->orderBy("id", "ASC")
                            ->pluck("dealer_type", "id")->toArray();

        return view('buyer.vendor-product.product', compact('product', 'dealer_types'));
    }
    public function getVendorProduct(Request $request)
    {
        $page_name = $request->page_name;
        if(empty($page_name) || !in_array($page_name, array("draft-rfq", "vendor"))){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong, invalid source!!!'
            ]);
        }
        $product_id = $request->product_id;
        $product = Product::where('status', 1)->find($product_id);
        if(empty($product)){
            return response()->json([
                'status' => false,
                'message' => 'Vendor Product not found'
            ]);
        }

        $draft_id = $request->draft_id;
        if(!empty($draft_id)){
            $product_exists_into_rfq = RfqProduct::where("rfq_id", $draft_id)->where("product_id", $product_id)->first();
            // if(!empty($product_exists_into_rfq)){
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Product already exists into RFQ'
            //     ]);
            // }
        }
        $vendor_name = $request->vendor_name;
        $brand_name = $request->brand_name;
        $sort_type = $request->sort_type;
        $vendor_location = $request->vendor_location;
        $int_vendor_location = $request->int_vendor_location;
        $dealer_type = $request->dealer_type;
        $selected_vendors = $request->selected_vendors;
        if(empty($selected_vendors)){
            $selected_vendors = array();
        }
        $blacklisted_vendors = array();

        // DB::enableQueryLog();

        $products = Product::with([
                        'product_vendors' => function ($q) use ($sort_type, $vendor_name, $dealer_type, $vendor_location, $int_vendor_location, $brand_name, $blacklisted_vendors) {
                            $q->select('vendor_products.id', 'vendor_products.vendor_id', 'vendor_products.product_id', 'vendor_products.image', 'vendor_products.description', 'vendor_products.dealer_type_id')
                            ->where('vendor_status', 1)
                            ->where('edit_status', 0)
                            ->where('approval_status', 1);
                            
                            if(!empty($dealer_type)){
                                $q->whereIn('vendor_products.dealer_type_id', $dealer_type);
                            }
                            if(!empty($blacklisted_vendors)){
                                $q->whereNotIn('vendor_products.vendor_id', $blacklisted_vendors);
                            }
                            if(!empty($brand_name)){
                                $q->where(function ($query) use ($brand_name) {
                                    $query->where('vendor_products.description', 'like', '%' . $brand_name . '%')
                                            ->orWhere('vendor_products.brand', 'like', '%' . $brand_name . '%')
                                            ->orWhere('vendor_products.dealership', 'like', '%' . $brand_name . '%');
                                });
                            }

                            if(!empty($sort_type) || !empty($vendor_name) || !empty($vendor_location) || !empty($int_vendor_location)){
                                $q->join('vendors', 'vendors.user_id', '=', 'vendor_products.vendor_id');
                            }
                            if(empty($sort_type)){
                                $q->inRandomOrder();
                            }else{
                                $q->orderBy("vendors.legal_name", ($sort_type==1 ? "ASC" : "DESC"));
                            }
                            if(!empty($vendor_name)){
                                $words = preg_split('/\s+/', $vendor_name, -1, PREG_SPLIT_NO_EMPTY); // Split by space(s)

                                $q->where(function ($query) use ($words) {
                                    foreach ($words as $word) {
                                        $query->where('vendors.legal_name', 'like', '%' . $word . '%');
                                    }
                                });
                            }
                            if(!empty($vendor_location) && !empty($int_vendor_location)){
                                $q->where(function ($query) use ($vendor_location, $int_vendor_location) {
                                    $query->where('vendors.state', $vendor_location)
                                            ->orWhere('vendors.country', $int_vendor_location);
                                });
                            }else{
                                if(!empty($vendor_location)){
                                    $q->whereIn('vendors.state', $vendor_location);
                                }
                                if(!empty($int_vendor_location)){
                                    $q->whereIn('vendors.country', $int_vendor_location);
                                }
                            }

                            $q->whereHas('vendor_profile', function ($q) {
                                $q->whereNotNull('vendor_code')
                                    ->whereHas('user', function ($q) {
                                    $q->where('status', 1)
                                        ->where('is_verified', 1)
                                        ->where('user_type', 2);
                                });
                            });
                        },
                        'product_vendors.vendor_profile:id,user_id,legal_name,state,country',
                        'product_vendors.vendor_profile.user:id,mobile',
                        'product_vendors.vendor_profile.vendor_state:id,name,country_id',
                        'product_vendors.vendor_profile.vendor_country:id,name,phonecode',
                    ])
                    ->select("id", "product_name")
                    ->where("id", $product_id)
                    ->where("status", 1)
                    ->first();

        // $queries = DB::getQueryLog();

        // echo "<pre>";
        // print_r($products);
        // die;
        
        if(!empty($products) && count($products->product_vendors)>0){
            $colors_list = ['light-blue', 'light-pink', 'light-orange', 'light-green'];
            $colors = new CircularArray($colors_list);

            if($page_name=="vendor"){
                $product_html = view('buyer.vendor-product.product-item', compact('products', 'selected_vendors', 'colors'))->render();
            }else{
                $product_html = view('buyer.rfq.searched-product-item', compact('products', 'colors'))->render();
            }

            $vendor_locations = $this->extractVendorsLocation($product_id);
    
            return response()->json([
                'status' => true,
                // 'queries' => $queries,
                'message' => 'Vendor Product found',
                'products' => $product_html,
                'all_states' => $vendor_locations['states'],
                'all_country' => $vendor_locations['countries'],
                'vendor_count' => count($products->product_vendors),
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'No Vendor Found'
            ]);
        }
    }
    private function extractVendorsLocation($product_id){
        $vendor_locations = Product::with([
            'product_vendors' => function ($q) {
                $q->select('vendor_products.id', 'vendor_products.vendor_id', 'vendor_products.product_id')
                ->where('vendor_status', 1)
                ->where('edit_status', 0)
                ->where('approval_status', 1);
                
                $q->whereHas('vendor_profile', function ($q) {
                    $q->whereNotNull('vendor_code')
                        ->whereHas('user', function ($q) {
                        $q->where('status', 1)
                            ->where('is_verified', 1)
                            ->where('user_type', 2);                                            
                    });
                });
            },
            'product_vendors.vendor_profile.vendor_state:id,name,country_id',
            'product_vendors.vendor_profile.vendor_country:id,name,phonecode',
        ])
        ->select("id")
        ->where("id", $product_id)
        ->where("status", 1)
        ->first();

        $states = [];
        $countries = [];

        collect($vendor_locations->product_vendors)->each(function ($vendor) use (&$states, &$countries) {
            $profile = $vendor->vendor_profile ?? null;
            if (!$profile) return;
        
            $state = $profile->vendor_state ?? null;
            $country = $profile->vendor_country ?? null;
        
            // If no country from state, try getting from vendor_profile->country if it's a model
            if (!$country && is_object($profile->country ?? null)) {
                $country = $profile->country;
            }
        
            // Skip if country is still missing or not a model
            if (!$country || !is_object($country)) return;
        
            if ($country->id == 101 && $state) {
                // India: include state if exists
                $states[] = [
                    'id' => $state->id,
                    'name' => $state->name
                ];
            } elseif ($country->id != 101) {
                // Non-India: include only country
                $countries[] = [
                    'id' => $country->id,
                    'name' => $country->name
                ];
            }
        });

        $states = collect($states)->sortBy('name')->unique('id')->values()->toArray();
        $countries = collect($countries)->sortBy('name')->unique('id')->values()->toArray();

        return array('states'=> $states, 'countries'=> $countries);
    }
}
