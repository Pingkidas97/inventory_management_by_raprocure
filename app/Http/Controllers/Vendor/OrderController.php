<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\ManualOrder;
use App\Models\Vendor; 
use App\Models\OrdersPi;
use App\Models\Rfq;
class OrderController extends Controller
{
    public function rfqOrder(Request $request) {
       $query = Order::with('order_variants','buyer')->where('vendor_id', getParentUserId());
        
        if ($request->filled('buyer_name'))
        {
            $legal_name=$request->buyer_name;
            $query->whereHas('buyer', function ($q) use ($legal_name) {
                $q->where('legal_name', 'like', "%$legal_name%");
            });
        }
        if ($request->filled('order_no')){
            $query->where('po_number', $request->order_no);
        }
        if ($request->filled('rfq_no')){
            $query->where('rfq_id', $request->rfq_no);
        }
        if ($request->filled('status')){
            $query->where('order_status', $request->status);
        }
        $order=$request->order;
        if (!empty($order)) {
            $query->orderBy($column[$order['0']['column']], $order['0']['dir']);
        } else {
            $query->orderBy('id', 'desc');
        }
        $perPage = $request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());
       
        if ($request->ajax()) {
            return view('vendor.order.partials.rfq-table', compact('results'))->render();
        }
        return view('vendor.order.rfq-index',compact('results'));
    }

    public function rfqOrderView(Request $request,$id) {

        $order = Order::with(['order_variants.frq_variant','order_variants.frq_quotation_variant'=>function($q){
             //$q->where('vendor_id', getParentUserId());
        },'buyer'])->where('id', $id)->first();
        return view('vendor.order.rfq-view',compact('order'));
    }

    public function rfqOrderPrint(Request $request,$id) {
       // echo '<pre>';
        $order = Order::with(['vendor','vendor.user','buyer','buyer.users','rfq','rfq.buyer_branchs','order_variants.frq_variant','order_variants.frq_quotation_variant'])->where('id', $id)->first();
       //print_r($order);die;
        return view('vendor.order.rfq-pdf',compact('order'));
    }
    
    public function directOrder(Request $request) {

        $query = ManualOrder::with('order_products','buyer')->where('vendor_id', getParentUserId());
        
        if ($request->filled('buyer_name'))
        {
            $legal_name=$request->buyer_name;
            $query->whereHas('buyer', function ($q) use ($legal_name) {
                $q->where('legal_name', 'like', "%$legal_name%");
            });
        }
        if ($request->filled('order_no')){
            $query->where('manual_po_number', $request->order_no);
        }
        if ($request->filled('status')){
            $query->where('order_status', $request->status);
        }
        $order=$request->order;
        if (!empty($order)) {
            $query->orderBy($column[$order['0']['column']], $order['0']['dir']);
        } else {
            $query->orderBy('id', 'desc');
        }
        $perPage = $request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());
       
        if ($request->ajax()) {
            return view('vendor.order.partials.direct-table', compact('results'))->render();
        }
        return view('vendor.order.direct-index', compact('results'));
    }
    
    public function directOrderView(Request $request,$id) {
        
        $order = ManualOrder::with('order_products','order_products.inventory','buyer')->where('id', $id)->first();
        //print_r($order);die;
        return view('vendor.order.direct-view',compact('order'));
    }
    public function workOrderView(Request $request,$id) {
        
        $order = ManualOrder::with('order_products','order_products.inventory','buyer')->where('id', $id)->first();
        //print_r($order);die;
        return view('vendor.order.direct-view',compact('order'));
    }

    public function directOrderPrint(Request $request,$id) {
        // echo '<pre>';
        $order = ManualOrder::with('order_products','order_products.inventory','buyer')->where('id', $id)->first();
        //    print_r($order->order_products[0]->inventory->branch);die; 
        // print_r($order); 
        $vendor=Vendor::with(['vendor_country','vendor_state','vendor_city'])->where('user_id',getParentUserId())->first();
        // print_r($vendor);die;
        return view('vendor.order.direct-pdf',compact('order','vendor'));
    }

    public function uploadPiAttachment(Request $request) {
        $order_number = $request->order_number;
        $order_type = $request->order_type;
        if ($request->hasFile('pi_attachment')) {
            $file = $request->file('pi_attachment');
            $name = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pi-order/'), $name);
            $result = array('status' => true, 'file_name' => $name,'file_url'=>url('uploads/pi-order/'.$name));
            $buyer_branch='';
            if($order_type=='direct_order'){
                $order_data=ManualOrder::where('manual_po_number', $order_number)->where('vendor_id', getParentUserId())->first();
                $orderProduct=ManualOrderProduct::select('inventory_id')->where('manual_order_id', $order_data->id)->first();
                $inventory_id=$orderProduct->inventory_id;
                $inventory=Inventory::find($inventory_id);
                $buyer_branch=$inventory->buyer_branch_id;
            }
            if($order_type=='rfq_order'){
                $order_data=Order::where('po_number', $order_number)->where('vendor_id', getParentUserId())->first();
                $rfq_id=$order_data->rfq_id;
                $rfq=Rfq::select('buyer_branch')->where('rfq_id',$rfq_id)->first();
                $buyer_branch=$rfq->buyer_branch;
            }
            $order=new OrdersPi;
            $order->order_number=$order_number;
            $order->buyer_id=$order_data->buyer_id;
            $order->vendor_id=getParentUserId();
            $order->pi_attachment=$name;
            $order->order_date=$order_data->created_at;
            $order->pi_date=date('Y-m-d h:i:s');
            $order->vendor_user_id=auth()->user()->id;
            $order->buyer_branch_id=$buyer_branch;
            $order->save();
            $result = array('status' => true, 'file_name' => $name,'file_url'=>url('uploads/pi-order/'.$name));
        } else {
            $result = array('status' => false, 'file_name' => "File upload failed.",'file_url'=>null);
        }
        return response()->json($result);
    }
}
