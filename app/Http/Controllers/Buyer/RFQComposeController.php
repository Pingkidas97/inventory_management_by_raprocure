<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Rfq;
use App\Models\RfqProduct;
use App\Models\RfqProductVariant;
use App\Models\RfqVendor;
use Illuminate\Support\Facades\Auth;
use DB;
use Carbon\Carbon;

class RFQComposeController extends Controller
{
    public $rfq_attachment_dir = 'rfq-attachment';

    function index($draft_id) {
        $company_id = getParentUserId();

        $draft_rfq = Rfq::where('rfq_id', $draft_id)->where('buyer_id', $company_id)->where('record_type', 1)->first();
        if(empty($draft_rfq)){
            session()->flash('error', "Draft RFQ not found");
            return redirect()->to(route('buyer.dashboard'));
        }

        $buyer_branch = DB::table('branch_details')
            ->select('id', 'branch_id', 'name')
            ->where("user_id", $company_id)
            ->where('user_type', 1)
            ->where('record_type', 1)
            ->where('status', 1)
            ->get();
        
        $uoms = DB::table('uoms')
                        ->select("id", "uom_name")
                        ->where("status", 1)
                        ->orderBy("id", "ASC")
                        ->pluck("uom_name", "id")->toArray();
        
        $dealer_types = DB::table("dealer_types")
                            ->select("id", "dealer_type")
                            ->where("status", 1)
                            ->orderBy("id", "ASC")
                            ->pluck("dealer_type", "id")->toArray();

        return view('buyer.rfq.compose', compact('draft_rfq', 'uoms', 'buyer_branch', 'dealer_types'));
    }

    function getDraftProduct(Request $request) {
        $draft_id = $request->draft_id;
        if(empty($draft_id)){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
        $company_id = getParentUserId();
        // $current_user_id = Auth::user()->id;

        $draft_rfq = Rfq::where('rfq_id', $draft_id)->where('buyer_id', $company_id)->where('record_type', 1)->first();

        if(empty($draft_rfq)){
            return response()->json([
                'status' => false,
                'message' => 'Draft RFQ not found'
            ]);
        }

        $draft_rfq = Rfq::with([
                        'rfqProducts',
                        'rfqProducts.masterProduct:id,division_id,category_id,product_name',
                        'rfqProducts.masterProduct.division:id,division_name',
                        'rfqProducts.masterProduct.category:id,category_name',
                        'rfqProducts.productVariants' => function ($q) use ($draft_id) {
                            $q->where('rfq_id', $draft_id);
                        },
                        'rfqProducts.productVendors' => function ($q) {
                            $q->select('id', 'vendor_id', 'product_id')
                            ->where('vendor_status', 1)
                            ->where('edit_status', 0)
                            ->where('approval_status', 1);
                            $q->whereHas('product', function ($q) {
                                $q->where('status', 1);
                            });

                            $q->whereHas('vendor_profile', function ($q) {
                                $q->whereNotNull('vendor_code')
                                    ->whereHas('user', function ($q) {
                                    $q->where('status', 1)
                                        ->where('is_verified', 1)
                                        ->where('user_type', 2);
                                });
                            });
                        },
                        'rfqProducts.productVendors.vendor_profile:id,user_id,legal_name,state,country',
                        'rfqProducts.productVendors.vendor_profile.vendor_state:id,name,country_id',
                        'rfqProducts.productVendors.vendor_profile.vendor_country:id,name',
                        'rfqVendors:id,rfq_id,vendor_user_id',
                    ])
                    ->where('rfq_id', $draft_id)
                    ->where('buyer_id', $company_id)
                    ->where('record_type', 1)
                    ->first();
                    
        $rfq_vendors = $this->extractRFQVendors($draft_rfq->rfqVendors);

        // echo "<pre>";
        // print_r($draft_rfq->rfqProducts[0]['productVendors'][0]->vendor_profile['legal_name']);
        // print_r($draft_rfq->rfqVendors);
        // print_r($rfq_vendors);
        // die;


        $uoms = DB::table('uoms')
                        ->select("id", "uom_name")
                        ->where("status", 1)
                        ->orderBy("id", "ASC")
                        ->pluck("uom_name", "id")->toArray();

        $product_html = view('buyer.rfq.rfq-product-item', compact('draft_rfq', 'uoms', 'rfq_vendors'))->render();

        $vendor_locations = $this->extractVendorsLocation($draft_rfq);

        return response()->json([
            'status' => true,
            'message' => 'Draft RFQ found',
            'products' => $product_html,
            'all_states' => $vendor_locations['states'],
            'all_country' => $vendor_locations['countries'],
            // 'draft_rfq' => $draft_rfq,
            // 'rfq_vendors' => $rfq_vendors,
        ]);
    }

    private function extractRFQVendors($rfqVendors){
        return collect($rfqVendors)
            ->filter() // removes nulls or falsy entries
            ->pluck('vendor_user_id')
            ->values()
            ->all();
    }
    private function extractVendorsLocation($draft_rfq){
        $states = collect();
        $countries = collect();
        
        foreach ($draft_rfq->rfqProducts as $product) {
            foreach ($product->productVendors as $vendor) {
                $profile = $vendor->vendor_profile;

                if ($profile?->vendor_country && $profile->vendor_country->id==101) {
                    $states->push($profile->vendor_state);
                }else{
                    $countries->push($profile->vendor_country);
                }
            }
        }

        // Remove duplicates by ID
        $uniqueStates = $states->unique('id')->sortBy('name')->values();
        $uniqueCountries = $countries->unique('id')->sortBy('name')->values();

        unset($countries);
        unset($states);

        return array('states'=> $uniqueStates, 'countries'=> $uniqueCountries);
    }
    private function isDraftExists($draft_id, $company_id){        
        return DB::table('rfqs')
                    ->where('rfq_id', $draft_id)
                    ->where('buyer_id', $company_id)
                    ->where('record_type', 1)
                    ->first();
    }

    function updateProduct(Request $request) {

        $draft_id = $request->rfq_draft_id;
        $company_id = getParentUserId();
        
        $is_draft_exists = $this->isDraftExists($draft_id, $company_id);
        if(empty($is_draft_exists)){
            return response()->json([
                'status' => false,
                'type' => "DraftNotFound",
                'message' => 'Products from this tab has already been processed.',
            ]);
        }
        
        $current_user_id = Auth::user()->id;
        $master_product_id = $request->master_product_id;
        $vendor_id = $request->vendor_id;
        $brand = $request->brand;
        $remarks = $request->remarks;
        $edit_id = $request->edit_id;
        $variant_grp_id = $request->variant_grp_id;
        $variant_order = $request->variant_order;
        $specification = $request->specification;
        $size = $request->size;
        $quantity = $request->quantity;
        $uom = $request->uom;
        $old_attachment = $request->old_attachment;
        $delete_attachment = $request->delete_attachment;

        // echo "<pre>";
        // print_r($_FILES);
        // die;
        DB::beginTransaction();

        try {

            RfqProduct::where("rfq_id", $draft_id)
                        ->where("product_id", $master_product_id)
                        ->update(["brand"=> $brand, "remarks"=> $remarks]);
            
            $is_file_uploaded = '';
            $is_file_deleted = '';
            $attachments = $request->file('attachment');
            if(!empty($edit_id) && count($edit_id)>0){
                $file_prefix = 'B' . $current_user_id. '-R';

                foreach ($edit_id as $key => $value) {
                    $is_new_variant = true;
                    $is_variant_exists = RfqProductVariant::where("rfq_id", $draft_id)
                        ->where("product_id", $master_product_id)
                        ->where("variant_grp_id", $variant_grp_id[$key])
                        ->first();
                    if(!empty($is_variant_exists)){
                        $is_new_variant = false;
                    }

                    $rfq_file_name = null;
                    if (is_array($attachments) && isset($attachments[$key])) {
                        $res = uploadMultipleFile($request, 'attachment', $this->rfq_attachment_dir, $key, $file_prefix);
                        if($res['status']){
                            if($is_new_variant==false && !empty($is_variant_exists->attachment)){
                                removeFile(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$is_variant_exists->attachment));
                            }
                            $rfq_file_name = $res['file_name'];
                            $is_file_uploaded = $res['file_name'];
                        }else{
                            throw new \Exception($res['file_name']);
                        }
                    }else if(!empty($request->old_attachment[$key])){
                        $rfq_file_name = $request->old_attachment[$key];
                    }

                    if(!empty($delete_attachment[$key]) && is_file(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$delete_attachment[$key]))){
                        removeFile(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$delete_attachment[$key]));
                        $is_file_deleted = $delete_attachment[$key];
                    }

                    if($is_new_variant==true){
                        $rfqProductVariant = new RfqProductVariant();
                        $rfqProductVariant->rfq_id = $draft_id;
                        $rfqProductVariant->product_id = $master_product_id;
                        $rfqProductVariant->variant_order = $variant_order[$key];
                        $rfqProductVariant->variant_grp_id = $variant_grp_id[$key];
                        $rfqProductVariant->specification = $specification[$key];
                        $rfqProductVariant->size = $size[$key];
                        $rfqProductVariant->quantity = $quantity[$key] ?? null;
                        $rfqProductVariant->uom = $uom[$key];
                        $rfqProductVariant->attachment = $rfq_file_name;
                        $rfqProductVariant->save();
                    }else{
                        RfqProductVariant::where("rfq_id", $draft_id)
                        ->where("product_id", $master_product_id)
                        ->where("variant_grp_id", $variant_grp_id[$key])
                        ->update(
                            [
                                "specification"=> $specification[$key],
                                "size"=> $size[$key],
                                "quantity"=> $quantity[$key] ?? null,
                                "uom"=> $uom[$key],
                                "attachment"=> $rfq_file_name,
                                "variant_order"=> $variant_order[$key]
                            ]
                        );
                    }
                }
            }

            $existing_vendors = DB::table("rfq_vendors")->where("rfq_id", $draft_id)->pluck('vendor_user_id');

            // 1. Get new vendors (in $vendor_id but not in $existing_vendors)
            $new_vendors = array_values(array_diff($vendor_id, $existing_vendors->toArray()));

            // 2. Get removed vendors (in $existing_vendors but not in $vendor_id)
            $removed_vendors = array_values(array_diff($existing_vendors->toArray(), $vendor_id));

            // Remove $ids from $new_ids
            // $new_vendors = array_diff($vendor_id, $existing_vendors->toArray());

            // If needed, reset array keys
            // $new_vendors = array_values($new_vendors);

            // insert only new vendor
            if(count($new_vendors)>0){
                foreach ($new_vendors as $vendor_user_id) {
                    $rfqVendor = new RfqVendor();
                    $rfqVendor->rfq_id = $draft_id;
                    $rfqVendor->vendor_user_id = $vendor_user_id;
                    $rfqVendor->vendor_status = 1;
                    $rfqVendor->save();
                }
            }

            // delete old vendor
            if(count($removed_vendors)>0){
                RfqVendor::where("rfq_id", $draft_id)->whereIn("vendor_user_id", $removed_vendors)->delete();
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Draft RFQ updated successfully',
                'is_file_deleted'=> $is_file_deleted,
                'is_file_uploaded'=> $is_file_uploaded,
                'file_url'=> $is_file_uploaded !='' ? url('public/uploads/rfq-attachment') : ''
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to update draft RFQ. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }

    }
    function updateDraftRFQ(Request $request) {
        
        $draft_id = $request->rfq_draft_id;
        $company_id = getParentUserId();
        
        $is_draft_exists = $this->isDraftExists($draft_id, $company_id);
        if(empty($is_draft_exists)){
            return response()->json([
                'status' => false,
                'type' => "DraftNotFound",
                'message' => 'Products from this tab has already been processed.',
            ]);
        }
        
        $prn_no = $request->prn_no;
        $buyer_branch = $request->buyer_branch;
        $last_response_date = $request->last_response_date;
        $buyer_price_basis = $request->buyer_price_basis;
        $buyer_pay_term = $request->buyer_pay_term;
        $buyer_delivery_period = $request->buyer_delivery_period;
        $warranty_gurarantee = $request->warranty_gurarantee;

        if(!empty($last_response_date)){
            $last_response_date = Carbon::createFromFormat('d/m/Y', $last_response_date)->format('Y-m-d');
        }

        DB::beginTransaction();

        try {

            Rfq::where("rfq_id", $draft_id)
                        ->update(
                            [
                                "prn_no"=> $prn_no,
                                "buyer_branch"=> $buyer_branch,
                                "last_response_date"=> $last_response_date,
                                "buyer_price_basis"=> $buyer_price_basis,
                                "buyer_pay_term"=> $buyer_pay_term,
                                "buyer_delivery_period"=> $buyer_delivery_period,
                                "warranty_gurarantee"=> $warranty_gurarantee,
                            ]
                        );
            
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Draft RFQ updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to update draft RFQ. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }

    }
    function deleteProduct(Request $request) {

        $draft_id = $request->rfq_draft_id;
        $company_id = getParentUserId();
        
        $is_draft_exists = $this->isDraftExists($draft_id, $company_id);
        if(empty($is_draft_exists)){
            return response()->json([
                'status' => false,
                'type' => "DraftNotFound",
                'message' => 'Products from this tab has already been processed.',
            ]);
        }
        
        $master_product_id = $request->master_product_id;

        // echo "<pre>";
        // print_r($_FILES);
        // die;

        $total_product_count = RfqProduct::where("rfq_id", $draft_id)->count();

        $rfqProductVariant = DB::table('rfq_product_variants')
                                ->where("rfq_id", $draft_id)
                                ->where("product_id", $master_product_id)
                                ->select("attachment")
                                ->get();

        DB::beginTransaction();

        try {

            RfqProductVariant::where("rfq_id", $draft_id)
                        ->where("product_id", $master_product_id)
                        ->delete();

            RfqProduct::where("rfq_id", $draft_id)
                        ->where("product_id", $master_product_id)
                        ->delete();
            
            if($total_product_count==1){
                Rfq::where("rfq_id", $draft_id)->delete();
                RfqVendor::where("rfq_id", $draft_id)->delete();
            }
            DB::commit();

            foreach ($rfqProductVariant as $key => $value) {
                if(!empty($value->attachment) && is_file(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$value->attachment))){
                    removeFile(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$value->attachment));
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Draft RFQ product deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete draft RFQ product. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }

    }
    function deleteProductVariant(Request $request) {

        $draft_id = $request->rfq_draft_id;
        $company_id = getParentUserId();
        
        $is_draft_exists = $this->isDraftExists($draft_id, $company_id);
        if(empty($is_draft_exists)){
            return response()->json([
                'status' => false,
                'type' => "DraftNotFound",
                'message' => 'Products from this tab has already been processed.',
            ]);
        }
        
        $master_product_id = $request->master_product_id;
        $variant_grp_id = $request->variant_grp_id;

        
        $rfqProductVariant = DB::table('rfq_product_variants')
                                ->where("rfq_id", $draft_id)
                                ->where("product_id", $master_product_id)
                                ->where("variant_grp_id", $variant_grp_id)
                                ->select("attachment")
                                ->get();
                                
        // echo "<pre>";
        // print_r($rfqProductVariant);
        // die;

        DB::beginTransaction();
        
        try {

            RfqProductVariant::where("rfq_id", $draft_id)
                        ->where("product_id", $master_product_id)
                        ->where("variant_grp_id", $variant_grp_id)
                        ->delete();

            DB::commit();

            foreach ($rfqProductVariant as $key => $value) {
                if(!empty($value->attachment) && is_file(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$value->attachment))){
                    removeFile(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$value->attachment));
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Draft RFQ product variant deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete draft RFQ product variant. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }

    }
    function deleteDraftRFQ(Request $request) {

        $draft_id = $request->rfq_draft_id;
        $company_id = getParentUserId();
        
        $is_draft_exists = $this->isDraftExists($draft_id, $company_id);
        if(empty($is_draft_exists)){
            return response()->json([
                'status' => false,
                'type' => "DraftNotFound",
                'message' => 'Products from this tab has already been processed.',
            ]);
        }

        $rfqProductVariant = DB::table('rfq_product_variants')
                                ->where("rfq_id", $draft_id)
                                ->select("attachment")
                                ->get();
                                
        // echo "<pre>";
        // print_r($rfqProductVariant);
        // die;
        
        DB::beginTransaction();

        try {

            RfqProductVariant::where("rfq_id", $draft_id)->delete();
            RfqProduct::where("rfq_id", $draft_id)->delete();
            RfqVendor::where("rfq_id", $draft_id)->delete();
            Rfq::where("rfq_id", $draft_id)->delete();
            
            DB::commit();

            foreach ($rfqProductVariant as $key => $value) {
                if(!empty($value->attachment) && is_file(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$value->attachment))){
                    removeFile(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$value->attachment));
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Draft deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete Draft. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }

    }

}
