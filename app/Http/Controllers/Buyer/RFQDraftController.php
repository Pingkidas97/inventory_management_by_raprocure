<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Division;
use App\Models\Product;
use App\Models\Rfq;
use App\Models\RfqProduct;
use App\Models\RfqProductVariant;
use App\Models\RfqVendor;
use Illuminate\Support\Facades\Auth;
use DB;

class RFQDraftController extends Controller
{
    public function index(Request $request)
    {
        // DB::enableQueryLog();

        $query = Rfq::select('rfqs.*')
                    ->where('rfqs.buyer_id', getParentUserId())
                    // Apply filter only if any of the inputs are present
                    ->when(
                        $request->filled('division') || $request->filled('category') || $request->filled('product_name'),
                        function ($query1) use ($request) {
                            $query1->whereHas('rfqProducts.masterProduct', function ($q) use ($request) {
                                if ($request->filled('division') && !empty($request->division)) {
                                    $q->where('division_id', $request->division);
                                }
                                if ($request->filled('category') && !empty($request->category)) {
                                    $categories = explode(",", $request->category);
                                    $q->whereIn('category_id', $categories);
                                }
                                if ($request->filled('product_name')) {
                                    $q->where('product_name', 'like', '%' . $request->product_name . '%');
                                }
                            });
                        }
                    )
                    ->with([
                        'rfqProducts.masterProduct',
                        'buyerUser',
                    ]);

        if ($request->filled('draft_rfq_no')){
            $query->where('rfq_id', 'like', '%' .$request->draft_rfq_no . '%');
        }
        
        $query->orderBy('updated_at', 'DESC');
        $query->where('record_type', 1);
        
        $perPage = $request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());

        // dd(DB::getQueryLog());

        if ($request->ajax()) {
            return view('buyer.rfq.draft-rfq.partials.table', compact('results'))->render();
        }

        $divisions = Division::where("status", 1)->orderBy('division_name', 'asc')->get();
        $categories = Category::where("status", 1)->get();

        $unique_category = [];
        foreach ($categories as $category) {
            $name = $category->category_name;
            $id = $category->id;
            if (!isset($unique_category[$name])) {
                $unique_category[$name] = [];
            }
            $unique_category[$name][] = $id;
        }
        ksort($unique_category);

        return view('buyer.rfq.draft-rfq.index', compact('divisions', 'unique_category', 'results'));
    }

    function addToDraft(Request $request) {
        $product_id = $request->product_id;
        if(empty($product_id)){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
        $product = Product::where('status', 1)->find($product_id);
        if(empty($product)){
            return response()->json([
                'status' => false,
                'message' => 'Vendor Product not found'
            ]);
        }

        $vendors_id = $request->vendors_id;

        if(count($vendors_id)<=0){
            return response()->json([
                'status' => false,
                'message' => 'Please select at least one vendor to Generate RFQ'
            ]);
        }

        $rfq_draft_id = 'D'.time().rand(1000, 9999);
        // $rfq_draft_date = date('Y-m-d H:i:s');
        $company_id = getParentUserId();
        $current_user_id = Auth::user()->id;

        DB::beginTransaction();

        try {
                
            $rfq = new Rfq();
            $rfq->rfq_id = $rfq_draft_id;
            $rfq->buyer_id = $company_id;
            $rfq->buyer_user_id = $current_user_id;
            $rfq->record_type = 1;
            $rfq->is_bulk_rfq = 2;
            $rfq->buyer_rfq_status = 1;
            $rfq->save();

            $rfqProduct = new RfqProduct();
            $rfqProduct->rfq_id = $rfq->rfq_id;
            $rfqProduct->product_id = $product_id;
            $rfqProduct->product_order = 1;
            $rfqProduct->save();

            $variant_grp_id = now()->timestamp . mt_rand(10000, 99999);

            $rfqProductVariant = new RfqProductVariant();
            $rfqProductVariant->rfq_id = $rfq->rfq_id;
            $rfqProductVariant->product_id = $product_id;
            $rfqProductVariant->variant_order = 1;
            $rfqProductVariant->variant_grp_id = $variant_grp_id;            
            $rfqProductVariant->save();

            foreach ($vendors_id as $vendor_user_id) {
                $rfqVendor = new RfqVendor();
                $rfqVendor->rfq_id = $rfq->rfq_id;
                $rfqVendor->vendor_user_id = $vendor_user_id;
                $rfqVendor->vendor_status = 1;
                $rfqVendor->save();
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'RFQ Draft created',
                'redirectUrl' => route('buyer.rfq.compose-draft-rfq', ['draft_id'=>$rfq->rfq_id])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to create RFQ Draft. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }
        

    }
    function addToDraftRFQ(Request $request) {
        $product_id = $request->product_id;
        if(empty($product_id)){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
        $product = Product::where('status', 1)->find($product_id);
        if(empty($product)){
            return response()->json([
                'status' => false,
                'message' => 'Vendor Product not found'
            ]);
        }

        $vendors_id = $request->vendors_id;

        if(count($vendors_id)<=0){
            return response()->json([
                'status' => false,
                'message' => 'Please select at least one vendor to Add Product into Draft RFQ'
            ]);
        }

        $rfq_draft_id = $request->draft_id;
        $product_exists_into_rfq = RfqProduct::where("rfq_id", $rfq_draft_id)->where("product_id", $product_id)->first();
        if(!empty($product_exists_into_rfq)){
            return response()->json([
                'status' => false,
                'message' => 'Product already exists into RFQ'
            ]);
        }

        $company_id = getParentUserId();
        $current_user_id = Auth::user()->id;

        DB::beginTransaction();

        try {
            // first get new product order
            $rfq_product_order = DB::table("rfq_products")->where("rfq_id", $rfq_draft_id)->max('product_order');

            $variant_grp_id = now()->timestamp . mt_rand(10000, 99999);

            $rfqProduct = new RfqProduct();
            $rfqProduct->rfq_id = $rfq_draft_id;
            $rfqProduct->product_id = $product_id;
            $rfqProduct->product_order = $rfq_product_order+1;
            $rfqProduct->save();

            $rfqProductVariant = new RfqProductVariant();
            $rfqProductVariant->rfq_id = $rfq_draft_id;
            $rfqProductVariant->product_id = $product_id;
            $rfqProductVariant->variant_order = 1;
            $rfqProductVariant->variant_grp_id = $variant_grp_id;
            $rfqProductVariant->save();

            $existing_vendors = DB::table("rfq_vendors")->where("rfq_id", $rfq_draft_id)->pluck('vendor_user_id');

            // Remove $ids from $new_ids
            $new_vendors = array_diff($vendors_id, $existing_vendors->toArray());

            // If needed, reset array keys
            $new_vendors = array_values($new_vendors);

            // insert only new vendor
            if(count($new_vendors)>0){
                foreach ($new_vendors as $vendor_user_id) {
                    $rfqVendor = new RfqVendor();
                    $rfqVendor->rfq_id = $rfq_draft_id;
                    $rfqVendor->vendor_user_id = $vendor_user_id;
                    $rfqVendor->vendor_status = 1;
                    $rfqVendor->save();
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Product Added to Draft RFQ successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to create RFQ Draft. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }
        

    }

    function deleteDraftRFQ(Request $request) {
        $draft_rfq_ids = $request->draft_rfq_ids;
        $company_id = getParentUserId();
        
        $is_draft_exists = DB::table('rfqs')
                            ->whereIn('rfq_id', $draft_rfq_ids)
                            ->where('buyer_id', $company_id)
                            ->where('record_type', 1)
                            ->first();
        if(empty($is_draft_exists)){
            return response()->json([
                'status' => false,
                'type' => "DraftNotFound",
                'message' => 'Products from this tab has already been processed.',
            ]);
        }

        $rfqProductVariant = DB::table('rfq_product_variants')
                                ->whereIn("rfq_id", $draft_rfq_ids)
                                ->select("attachment")
                                ->get();
                                
        // echo "<pre>";
        // print_r($rfqProductVariant);
        // die;
        
        DB::beginTransaction();

        try {

            RfqProductVariant::whereIn("rfq_id", $draft_rfq_ids)->delete();
            RfqProduct::whereIn("rfq_id", $draft_rfq_ids)->delete();
            RfqVendor::whereIn("rfq_id", $draft_rfq_ids)->delete();
            Rfq::whereIn("rfq_id", $draft_rfq_ids)->delete();
            
            DB::commit();

            foreach ($rfqProductVariant as $key => $value) {
                if(!empty($value->attachment) && is_file(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$value->attachment))){
                    removeFile(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$value->attachment));
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Draft RFQ deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete Draft RFQ. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }
    }

}
