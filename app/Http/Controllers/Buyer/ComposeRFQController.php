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
use Illuminate\Support\Facades\Session;
// use App\Helpers\EmailHelper;
use Carbon\Carbon;

class ComposeRFQController extends Controller
{
    // function composeRFQ(Request $request) {
    //     $draft_id = $request->rfq_draft_id;
    //     $company_id = getParentUserId();
        
    //     $is_draft_exists = $this->isDraftExists($draft_id, $company_id);
    //     if(empty($is_draft_exists)){
    //         return response()->json([
    //             'status' => false,
    //             'type' => "DraftNotFound",
    //             'message' => 'Products from this tab has already been processed.',
    //         ]);
    //     }

    //     $is_vendor_exists = $this->validateProductVendors($draft_id);
    //     if($is_vendor_exists==false){
    //         return response()->json([
    //             'status' => false,
    //             'type' => "UpdateBranchAndReload",
    //             'message' => 'Please select vendor for each product.',
    //         ]);
    //     }
        
    //     $is_draft_valid = $this->validateDraftProducts($is_draft_exists);
    //     if ($is_draft_valid != "Verified") {
    //         return response()->json([
    //             'status' => false,
    //             'type' => "UpdateBranchAndReload",
    //             'message' => $is_draft_valid,
    //         ]);
    //     }

    //     DB::beginTransaction();

    //     try {
    //         $rfq_number = generateBuyerRFQNumber($company_id);
    //         $rfq_number_status = validateRFQNumber($rfq_number);
    //         if($rfq_number_status==false){
    //             return response()->json([
    //                 'status' => false,
    //                 'type' => "UpdateBranchAndReload",
    //                 'message' => "Duplicate RFQ Number Found.",
    //             ]);
    //         }
            
    //         // add product to order tables:START
    //         $date = date('Y-m-d H:i:s');
    //         if (!empty($is_draft_exists->scheduled_date)) {
    //             $rfq_status = 2;
    //         } else {
    //             $rfq_status = 1;
    //         }

    //         $rfq = Rfq::find($is_draft_exists->id);
    //         $rfq->rfq_id = $rfq_number;
    //         $rfq->buyer_user_id = Auth::user()->id;
    //         $rfq->record_type = 2;
    //         $rfq->buyer_rfq_status = $rfq_status;
    //         $rfq->save();

    //         RfqProduct::where('rfq_id', $is_draft_exists->rfq_id)->update(['rfq_id'=>$rfq_number]);
    //         RfqProductVariant::where('rfq_id', $is_draft_exists->rfq_id)->update(['rfq_id'=>$rfq_number]);
    //         RfqVendor::where('rfq_id', $is_draft_exists->rfq_id)->update(['rfq_id'=>$rfq_number]);

    //         if (empty($is_draft_exists->scheduled_date)) {
    //             $rfq_vendors = RfqVendor::with(
    //                             'rfqVendorProfile:id,user_id,legal_name',
    //                             'rfqVendorDetails:id,name,email'
    //                         )
    //                         ->where('rfq_id', $rfq_number)
    //                         ->select('vendor_user_id')
    //                         ->get();

    //             $vendor_details = [];

    //             foreach ($rfq_vendors as $vendor) {
    //                 // Make sure the relationships exist
    //                 if ($vendor->rfqVendorProfile && $vendor->rfqVendorDetails) {
    //                     $userId = $vendor->rfqVendorProfile->user_id;

    //                     $vendor_details[$userId] = [
    //                         'legal_name' => $vendor->rfqVendorProfile->legal_name,
    //                         'email'      => $vendor->rfqVendorDetails->email,
    //                     ];
    //                 }
    //             }

    //             $notification_data = array();
    //             $notification_data['rfq_no'] = $rfq_number;
    //             $notification_data['message_type'] = 'RFQ Received';
    //             $notification_data['notification_link'] = route("login");//route will change after RFQ Details page created on vendor side
    //             $notification_data['to_user_id'] = array_keys($vendor_details);
    //             $status = sendNotifications($notification_data);
                
    //             $this->sendEmailToVendors($rfq_number, $vendor_details);

    //         }
            
    //         // echo "<pre>";
    //         // echo $rfq_number;
    //         // die;

    //         Session::put('rfq_number', $rfq_number);

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'redirect_url' => route("buyer.rfq.compose-rfq-success", [$rfq_number]),
    //             'message' => 'RFQ Generated successfully'
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         // Handle the error
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Failed to Generate RFQ. '.$e->getMessage(),
    //             'complete_message' => $e
    //         ]);
    //     }

    // }
    function composeRFQ(Request $request) {       
        
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

        // if (!empty($is_draft_exists->scheduled_date)) {
        //     $this->ensurePermission('SCHEDULED_RFQ', 'add', '1');
        // } else {
        //     $this->ensurePermission('GENERATE_NEW_RFQ', 'add', '1');
        // }

        $is_vendor_exists = $this->validateProductVendors($draft_id);
        if($is_vendor_exists==false){
            return response()->json([
                'status' => false,
                'type' => "UpdateBranchAndReload",
                'message' => 'Please select vendor for each product.',
            ]);
        }
        
        $is_draft_valid = $this->validateDraftProducts($is_draft_exists);
        if ($is_draft_valid != "Verified") {
            return response()->json([
                'status' => false,
                'type' => "UpdateBranchAndReload",
                'message' => $is_draft_valid,
            ]);
        }

        $vendor_limit = config('constants.VENDOR_LIMIT_ON_RFQ');
        $draft_rfq_vendor_count = DB::table('rfq_vendors')
                                    ->where('rfq_id', $is_draft_exists->rfq_id)
                                    ->distinct('vendor_user_id')
                                    ->count('vendor_user_id');
        // if($draft_rfq_vendor_count>$vendor_limit){
        //     return response()->json([
        //         'status' => false,
        //         'draft_rfq_vendor_count' => $draft_rfq_vendor_count,
        //         'message' => "You can not Generate RFQ with more than ".$vendor_limit." vendors.",
        //     ]);
        // }//comment on pingki side
        /* Checking Duplicate spec and size for same product */


        $rfq_product_variants = DB::table('rfq_product_variants')
            ->where('rfq_id', $is_draft_exists->rfq_id)
            ->get(); // actually fetch data

        $duplicateRows = [];
        $seen = [];
        
        foreach ($rfq_product_variants as $index => $product) {
            $productId = $product->product_id;
            $spec = strtolower(trim($product->specification ?? ''));
            $size = strtolower(trim($product->size ?? ''));

            $key = $spec . '|' . $size;

            if (!isset($seen[$productId])) {
                $seen[$productId] = [];
            }

            if (isset($seen[$productId][$key])) {
                $duplicateRows[] = $index + 1; // row number in iteration
            } else {
                $seen[$productId][$key] = true;
            }
        }
        
        if (!empty($duplicateRows)) {
            return response()->json([
                'status' => false,
                'type' => "Duplicatevariants",
                'message' => 'Duplicate Product + Specification + Size found in rows: ' . implode(', ', $duplicateRows)
            ]);
            
        }
        
        /* End Duplicate spec and size for same product */
        DB::beginTransaction();

        try {
            $rfq_number = generateBuyerRFQNumber($company_id);
            $rfq_number_status = validateRFQNumber($rfq_number);
            if($rfq_number_status==false){
                return response()->json([
                    'status' => false,
                    'type' => "UpdateBranchAndReload",
                    'message' => "Duplicate RFQ Number Found.",
                ]);
            }
            
            // add product to order tables:START
            $date = date('Y-m-d H:i:s');
            if (!empty($is_draft_exists->scheduled_date)) {
                $rfq_status = 2;
            } else {
                $rfq_status = 1;
            }

            $rfq = Rfq::find($is_draft_exists->id);
            $rfq->rfq_id = $rfq_number;
            $rfq->buyer_user_id = Auth::user()->id;
            $rfq->record_type = 2;
            $rfq->buyer_rfq_status = $rfq_status;
            $rfq->created_at = date('Y-m-d H:i:s');
            $rfq->save();
            
            DB::table('rfq_products')->where('rfq_id', $is_draft_exists->rfq_id)->update(['rfq_id'=>$rfq_number]);
            DB::table('rfq_product_variants')->where('rfq_id', $is_draft_exists->rfq_id)->update(['rfq_id'=>$rfq_number]);
            DB::table('rfq_vendors')->where('rfq_id', $is_draft_exists->rfq_id)->update(['rfq_id'=>$rfq_number]);
            //echo $rfq_number; die('AAAA');
           


            if (empty($is_draft_exists->scheduled_date)) {
                $rfq_vendors = RfqVendor::with(
                                'rfqVendorProfile:id,user_id,legal_name',
                                'rfqVendorDetails:id,name,email'
                            )
                            ->where('rfq_id', $rfq_number)
                            ->groupBy('vendor_user_id')
                            ->select('vendor_user_id')
                            ->get();

                $vendor_details = [];

                foreach ($rfq_vendors as $vendor) {
                    // Make sure the relationships exist
                    if ($vendor->rfqVendorProfile && $vendor->rfqVendorDetails) {
                        $userId = $vendor->rfqVendorProfile->user_id;

                        $vendor_details[$userId] = [
                            'legal_name' => $vendor->rfqVendorProfile->legal_name,
                            'email'      => $vendor->rfqVendorDetails->email,
                        ];
                    }
                }

                $notification_data = array();
                $notification_data['rfq_no'] = $rfq_number;
                $notification_data['message_type'] = 'RFQ Received';
                $notification_data['notification_link'] = route("vendor.rfq.reply", ["rfq_id"=> $rfq_number]);//route will change after RFQ Details page created on vendor side
                $notification_data['to_user_id'] = array_keys($vendor_details);
                $status = sendNotifications($notification_data);
                
                $this->sendEmailToVendors($rfq_number, $vendor_details);

            }
            
            // echo "<pre>";
            // echo $rfq_number;
            // die;

            // setSessionWithExpiry('rfq_number', $rfq_number);//pingki code comment

            /*need to webhook for inventory*/
            $inventoryController = app(InventoryController::class);
            $RfqProductVariants = DB::table('rfq_product_variants')->where('rfq_id', $rfq_number)->get();
            DB::commit();
            $rfq_products_for_inventory = DB::table('rfq_products')->where('rfq_id', $rfq_number)->select("product_id", "brand")->pluck("brand", "product_id")->toArray();
            foreach ($RfqProductVariants as $variant) {
                $productId = $variant->product_id;
                $specification = $variant->specification;
                $size = $variant->size;
                $uomId = $variant->uom;
                $qty = $variant->quantity;
                $branchId = $rfq->buyer_branch;
                $product_brand = $rfq_products_for_inventory[$productId] ?? null;
                
               $inventoryController->updateInventory($productId, $specification, $size, $uomId, $qty, $company_id, $branchId, $rfq_number, $product_brand/*,$draft_id,'add'*/);
            }
            /*need to webhook for inventory*/

            return response()->json([
                'status' => true,
                'redirect_url' => route("buyer.rfq.compose-rfq-success", [$rfq_number]),
                'message' => 'RFQ Generated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to Generate RFQ. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }

    }

    private function isDraftExists($draft_id, $company_id){
        return DB::table('rfqs')
                    ->where('rfq_id', $draft_id)
                    ->where('buyer_id', $company_id)
                    ->where('record_type', 1)
                    ->first();
    }
    private function sendEmailToVendors($rfq_number, $vendor_details){
        $subject = "RFQ Received (RFQ No." . $rfq_number . " )";

        $mail_data = vendorEmailTemplet('user-success-order');
        $admin_msg = $mail_data->mail_message;

        $admin_msg = str_replace('$rfqdate', now()->format('d M Y'), $admin_msg);
        $admin_msg = str_replace('$rfq_date_formate', now()->format('d/m/Y'), $admin_msg);
        $admin_msg = str_replace('$rfq_number', $rfq_number, $admin_msg);
        $admin_msg = str_replace('$buyer_name', session('legal_name'), $admin_msg);
        $admin_msg = str_replace('$website_url', route("login"), $admin_msg);

        $mail_arr = array();
        foreach ($vendor_details as $key => $value) {
            $new_admin_msg = $admin_msg;

            $vendor_leagl_name = $value['legal_name'];
            $vendor_email = $value['email'];
            $new_admin_msg = str_replace('$vendor_name', $vendor_leagl_name, $new_admin_msg);

            $mail_arr[] = array('subject' => $subject, 'body' => $new_admin_msg, 'to' => $vendor_email);
        }
        sendMultipleDBEmails($mail_arr);
    }
    private function validateProductVendors($draft_id){
        
        $draft_products = DB::table('rfq_products')->where("rfq_id", $draft_id)->select("product_id")->pluck("product_id")->toArray();
        
        $products_vendors = Product::with([
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
                        'product_vendors.vendor_profile:id,user_id,vendor_code',
                        'product_vendors.vendor_profile.user:id',
                    ])
                    ->select("id")
                    ->whereIn("id", $draft_products)
                    ->where("status", 1)
                    ->get();

        $draft_vendors = DB::table('rfq_vendors')->where("rfq_id", $draft_id)->select("vendor_user_id")->pluck("vendor_user_id")->toArray();

        $productVendorsArray = [];
        foreach ($products_vendors as $product) {
            $vendorIds = $product->product_vendors->pluck('vendor_id')->all();

            $matchingVendorIds = array_values(array_intersect($vendorIds, $draft_vendors));

            if (!empty($matchingVendorIds)) {
                $productVendorsArray[$product->id] = $matchingVendorIds;
            }
        }

        $hasEmpty = collect($productVendorsArray)->contains(function ($vendors) {
            return empty($vendors);
        });

        unset($draft_products);
        unset($products_vendors);
        unset($draft_vendors);
        unset($productVendorsArray);

        if ($hasEmpty) {
            return false;
        }else{
            return true;
        }
    }
    
    private function validateDraftProducts($draft_data){
        if (empty($draft_data->buyer_branch) || $draft_data->buyer_branch == 0 ) {
            return 'Buyer Branch is required';
        }

        $user_branch_id_only = getBuyerUserBranchIdOnly();
        $branch_names = getBuyerBranchs($user_branch_id_only);
        $is_branch_available = !empty(array_filter($branch_names, fn($item) => $item->branch_id == $draft_data->buyer_branch));
        unset($branch_names);
        if($is_branch_available==false){
            return 'Buyer Branch is not active to generate the RFQ';
        }
        unset($factory_names);
        unset($user_branch_id_only);

        if (!empty($draft_data->last_response_date) && date("Y-m-d", strtotime($draft_data->last_response_date)) < date("Y-m-d", strtotime("+1 days"))) {
            return 'Last Response Date can not be less than tomorrow date';
        }

        if (!empty($draft_data->scheduled_date) && date("Y-m-d", strtotime($draft_data->scheduled_date)) < date("Y-m-d", strtotime("+1 days"))) {
            $edit_rfq_id = $draft_data->edit_rfq_id;
            $is_original_rfq = array();

            if(!empty($edit_rfq_id)){
                $company_id = getParentUserId();
                $is_original_rfq = DB::table('rfqs')
                    ->select('buyer_rfq_status')
                    ->where('rfq_id', $edit_rfq_id)
                    ->where('buyer_id', $company_id)
                    ->where('record_type', 2)
                    ->first();
            }
            if(!empty($is_original_rfq)){
                if($is_original_rfq->buyer_rfq_status==2){
                    return 'Scheduled Date can not be less than tomorrow date.';
                }
            }else{
                return 'Scheduled Date can not be less than tomorrow date.';
            }
        }

        if (!empty($draft_data->last_response_date) && !empty($draft_data->scheduled_date) && date("Y-m-d", strtotime($draft_data->last_response_date)) <= date("Y-m-d", strtotime($draft_data->scheduled_date))) {
            return 'Last Response Date can not be less than or equal to scheduled date.';
        }

        $variants = DB::table('rfq_product_variants')->select('quantity', 'uom')->where('rfq_id', $draft_data->rfq_id)->get();
        $hasQuantityBlank = $variants->contains(function ($variant) {
            return empty($variant->quantity);
        });
        $hasUOMBlank = $variants->contains(function ($variant) {
            return empty($variant->uom);
        });
        unset($variants);
        $is_verified = "Verified";
        if ($hasQuantityBlank || $hasUOMBlank) {
            $field = '';
            if ($hasQuantityBlank) {
                $field .= 'Quantity';
            }
            if ($hasUOMBlank) {
                $field .= !empty($field) ? ' and UOM' : ' UOM';
            }
            $is_verified = 'Product ' . $field . ' is manadatory.';
        }
        return $is_verified;
    }
    
    function composeRFQSuccess(Request $request, $rfq_number) {
        if($rfq_number!=session('rfq_number')){
            session()->flash('error', "Page not found");
            return redirect()->route('buyer.dashboard');
        }
        $rfq = DB::table('rfqs')->where('rfq_id', $rfq_number)->first();
        if(empty($rfq)){
            session()->flash('error', "Page not found");
            return redirect()->route('buyer.dashboard');
        }
        unset($rfq);
        
        return view('buyer.rfq.rfq-success', compact('rfq_number'));
    }
}
