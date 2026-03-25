<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Division;
use App\Models\LiveVendorProduct;
use App\Models\Rfq;
use App\Models\RfqProduct;
use Carbon\Carbon;
use DB;

class CISController extends Controller
{
    public function index($rfq_id)
    {
        $parent_user_id = getParentUserId();
        $rfq_data = Rfq::where('record_type', 2)->where('rfq_id', $rfq_id)->where('buyer_id', $parent_user_id)->first();
        if(empty($rfq_data)){
            return back()->with('error', 'RFQ not found.');
        }
        if($rfq_data->buyer_rfq_status==1){
            return back()->with('error', 'RFQ '.$rfq_id.' CIS did not received any vendor quote to open.');
        }

        $user_branch_id_only = getBuyerUserBranchIdOnly();
        if(!empty($user_branch_id_only) && !in_array($rfq_data->buyer_branch, $user_branch_id_only)){
            return back()->with('error', 'No RFQ found');
        }
        
        $rfq_details = Rfq::rfqDetails($rfq_id);
        $rfq_details1 = Rfq::analyzeRFQDetails($rfq_details);

        echo "<pre>";
        print_r($rfq_details);
        die;

        return view('buyer.rfq.cis.rfq-cis', compact('rfq_id'));
    }
    public function counter_offer($rfq_id, $vendor_id)
    {
        return view('buyer.rfq.cis.counter-offer', compact('rfq_id'));
    }
    public function quotation_received($rfq_id, $vendor_id)
    {
        return view('buyer.rfq.cis.quotation-received', compact('rfq_id'));
    }
}
