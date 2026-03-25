<?php 
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AdvertisementController extends Controller
{
    public function __construct()
    {
        if (auth()->check() && auth()->user()->user_type != 3) {
            abort(403, 'Unauthorized access.');
        }
    }
    public function index(Request $request)
    {
        $query = Advertisement::query()->orderBy('id', 'desc');

        $perPage = $request->input('per_page', 25); // Default: 25 per page
        $advertisements = $query->paginate($perPage)->appends($request->all());

        if ($request->ajax()) {
            return view('admin.advertisement.partials.table', compact('advertisements'))->render();
        }

        return view('admin.advertisement.index', compact('advertisements'));
    }



    public function create()
    {
        return view('admin.advertisement.create');
    }

    public function store(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'types'=>'required',
            'images' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'ad_position' => 'required',
            'status' => 'required',
            'buyer_vendor_name' => 'required',
            'received_on' => 'required',
            'payment_received_on' => 'required',
            'validity_period_from' => 'required',
            'validity_period_to' => 'required',
            'ads_url' => 'required',
            'ad_position' => 'required' 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'errors' => $validator->errors()->toArray()
            ], 422);
        }
        $data = $request->all();
        if($request->hasFile('images')){
            $file = $request->file('images');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move(public_path('uploads/advertisment/'), $filename);
            $data['images'] = $filename;
        }
        Advertisement::create($data);
        return response()->json([
            'success' => 1,
            'message' => 'Data has been saved successfully'
        ]);
        //return redirect()->route('admin.advertisement.index')->with('success', 'Data has been saved successfully');
    }

    public function edit($id)
    {
        $data = Advertisement::find($id);
        return view('admin.advertisement.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {   
        $validator = Validator::make($request->all(), [
            'types'=>'required',
            'images' => 'mimes:jpeg,png,jpg,gif,svg|max:2048',
            'ad_position' => 'required',
            'status' => 'required',
            'buyer_vendor_name' => 'required',
            'received_on' => 'required',
            'payment_received_on' => 'required',
            'validity_period_from' => 'required',
            'validity_period_to' => 'required',
            'ads_url' => 'required',
            'ad_position' => 'required' 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'errors' => $validator->errors()->toArray()
            ], 422);
        }
        $data = $request->all();
        if($request->hasFile('images')){
            $file = $request->file('images');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('uploads/images/', $filename);
            $data['images'] = $filename;
            if($request->old_image){
                @unlink('uploads/images/'.$request->old_image);
            }
        }else{
            $data['images'] = $request->old_image;
        }
        Advertisement::find($id)->update($data);
        return response()->json([
            'success' => 1,
            'message' => 'Data has been updated successfully'
        ]);
        //return redirect()->route('admin.advertisement.index')->with('success', 'Data has been updated successfully');
    }

    public function destroy($id)
    {
        $data=Advertisement::find($id);
        if($data->images){
            @unlink('uploads/images/'.$data->images);
        }
        $data->delete();
        return response()->json([
            'success' => 1,
            'message' => 'Data has been deleted successfully'
        ]);
        //return redirect()->route('admin.advertisement.index')->with('success', 'Data has been deleted successfully');
    }

    public function list(Request $request)
    {
        $column = ['buyer_vendor_name', 'received_on', 'payment_received_on', 'validity_period_from','images','ad_position','status', 'id'];
        $model = Advertisement::where('id', '>', '0');

        $total_row = $model->count();

        $search=$request->search;
        if (!empty($search)) {
            $model->where('buyer_vendor_name', 'LIKE', '%' . $search['value'] . '%');
        }
        $order=$request->order;
        if (!empty($order)) {
            $model->orderBy($column[$order['0']['column']], $order['0']['dir']);
        } else {
            $model->orderBy('id', 'desc');
        }
        $filter_row = $model->count();
        $length=$request->length;
        if (!empty($length) && $length != -1) {
            $start=$request->start;
            $model->skip($start)->take($length);
        }
        $result = $model->get();
        $data = array();
        foreach ($result as $key => $value) {
            $action = '';
            $action = '<a href="'.route('admin.advertisement.edit', $value->id).'" class="btn btn-warning btn-sm m-1">Edit</a>';
            $action .= '<a href="javascript:void(0);" onclick="deleteData(`' . route('admin.advertisement.destroy', $value->id) . '`);" class="btn btn-danger btn-sm m-1">Delete</a>';
            $sub_array = array();
            $sub_array[] = ($value->types==1?'Buyer':'Vendor').':'.$value->buyer_vendor_name;
            $sub_array[] = date('d/m/Y', strtotime($value->received_on));
            $sub_array[] = date('d/m/Y', strtotime($value->payment_received_on));
            $sub_array[] = date('d/m/Y', strtotime($value->validity_period_from)).'-'.date('d/m/Y', strtotime($value->validity_period_to));
            $sub_array[] =  $value->images;
            $sub_array[] =  $value->ad_position==1?'Buyer Ads only on the Vendor Side':'Vendor Ads only on the Buyer Side';
            $sub_array[] =  Advertisement::getStatus()[$value->status] ?? $value->status;
            $sub_array[] =  $action;
            $data[] = $sub_array;
        }
        $output = array(
            "draw"       =>  intval($request->draw),
            "recordsTotal"   =>  $total_row,
            "recordsFiltered"  =>  $filter_row,
            "data"       =>  $data
        );

        echo json_encode($output); 
    }
}