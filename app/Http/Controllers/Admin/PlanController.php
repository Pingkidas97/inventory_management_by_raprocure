<?php 
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function __construct()
    {
        if (auth()->check() && auth()->user()->user_type != 3) {
            abort(403, 'Unauthorized access.');
        }
    }
    
    public function index(Request $request)
    {
        $query = Plan::query()->orderBy('id', 'desc');

        $perPage = $request->input('per_page', 25); // Default: 25 per page
        $plans = $query->paginate($perPage)->appends($request->all());

        if ($request->ajax()) {
            return view('admin.plan.partials.table', compact('plans'))->render();
        }

        return view('admin.plan.index', compact('plans'));
    }


    public function create()
    {
        return view('admin.plan.create');
    }

    public function store(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'plan_name'=>'required',
            'type' => 'required',
            'no_of_user' => 'required',
            'price' => 'required',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'errors' => $validator->errors()->toArray()
            ], 422);
        }
        $data = $request->all();
        Plan::create($data);
        return response()->json([
            'success' => 1,
            'message' => 'Data has been saved successfully'
        ]);
        //return redirect()->route('admin.advertisement.index')->with('success', 'Data has been saved successfully');
    }

    public function edit($id)
    {
        $data = Plan::find($id);
        return view('admin.plan.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {   
        $validator = Validator::make($request->all(), [
            'plan_name'=>'required',
            'type' => 'required',
            'no_of_user' => 'required',
            'price' => 'required',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'errors' => $validator->errors()->toArray()
            ], 422);
        }
        $data = $request->all();
         
        Plan::find($id)->update($data);
        return response()->json([
            'success' => 1,
            'message' => 'Data has been updated successfully'
        ]);
        //return redirect()->route('admin.advertisement.index')->with('success', 'Data has been updated successfully');
    }

    public function destroy($id)
    {
        $data=Plan::find($id);
        $data->delete();
        return response()->json([
            'success' => 1,
            'message' => 'Data has been deleted successfully'
        ]);
        //return redirect()->route('admin.plan.index')->with('success', 'Data has been deleted successfully');
    }

    public function list(Request $request)
    {
        $column = ['plan_name', 'type', 'no_of_user', 'price','status','created_at','id'];
        $model = Plan::where('id', '>', '0');

        $total_row = $model->count();
        $search=$request->search;
        if (!empty($search)) {
            $model->where('plan_name', 'LIKE', '%' . $search['value'] . '%');
        }
        $order=$request->order;
        if (!empty($order)) {
            $model->orderBy($column[$order['0']['column']], $order['0']['dir']);
        } else {
            $model->orderBy('plan_name', 'desc');
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
            $action = '<a href="'.route('admin.plan.edit', $value->id).'" class="btn btn-warning btn-sm m-1">Edit</a>';
            $action .= '<a href="javascript:void(0);" onclick="deleteData(`' . route('admin.plan.destroy', $value->id) . '`);" class="btn btn-danger btn-sm m-1">Delete</a>';
            $sub_array = array();
            $sub_array[] = $value->plan_name;
            $sub_array[] = Plan::getType()[$value->type] ?? $value->type;
            $sub_array[] =  $value->no_of_user;
            $sub_array[] =  $value->price;
            $sub_array[] =  Plan::getStatus()[$value->status] ?? $value->status;
            $sub_array[] = date('d/m/Y', strtotime($value->created_at));
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