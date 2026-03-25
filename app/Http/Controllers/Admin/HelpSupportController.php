<?php 
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;   
use Illuminate\Http\Request;
use App\Models\HelpSupport;

class HelpSupportController extends Controller
{
    public function index(Request $request){
        $query = HelpSupport::with('venderBuyer')->where('id', '>', '0');

        if ($request->filled('legal_name'))
        {
            $legal_name=$request->legal_name;
            $query->whereHas('venderBuyer', function ($q) use ($legal_name) {
                $q->where('legal_name', 'like', "%$legal_name%");
            });
        }
        if ($request->filled('issue_type')){
            $query->where('issue_type', $request->issue_type);
        }

        $search=$request->search;
        if (!empty($search)) {
            $query->where('request_id', 'LIKE', '%' . $search['value'] . '%');
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
            return view('admin.help-support.partials.table', compact('results'))->render();
        }
        return view('admin.help-support.index',compact('results'));
    }

    public function list(Request $request)
    {
        $column = ['id', 'request_id', 'created_by', 'created_by','created_at','issue_type','description','status','id'];
        $model = HelpSupport::with('venderBuyer')->where('id', '>', '0');

        $total_row = $model->count();
        $company_name=$request->company_name;
        if(!empty($company_name))
        {
            $model->whereHas('venderBuyer', function ($q) use ($company_name) {
                $q->where('legal_name', 'like', "%$company_name%");
            });
        }
        $issue_type=$request->issue_type;
        if (!empty($issue_type)) {
            $model->where('issue_type', $issue_type);
        }

        $search=$request->search;
        if (!empty($search)) {
            $model->where('request_id', 'LIKE', '%' . $search['value'] . '%');
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
            $action = '<a href="'.route('admin.help_support.edit', $value->id).'" class="btn btn-warning btn-sm m-1">Edit</a>';
            $sub_array = array();
            $sub_array[] = ++$key;
            $sub_array[] = $value->request_id;
            $sub_array[] = $value->creater->name ?? '';
            $sub_array[] = $value->venderBuyer->legal_name ?? '';
            $sub_array[] = date('d/m/Y', strtotime($value->created_at));
            $sub_array[] = $value->issue_type;
            if(strlen($value->description) > 20) {
                $description= substr($value->description, 0, 20).'<i title="'.$value->description.'" class="bi bi-info-circle-fill" aria-hidden="true"></i>';
            } else {
                $description=  $value->description;
            }
            $sub_array[] = $description;
            $status=HelpSupport::getStatus($value->status);
           
            $sub_array[] ='<span class="badge '.$status['class'].'">'.$status['status'].'</span>';
            $sub_array[] = $action;
            $data[] = $sub_array;
        }
        $output = array(
            "draw"       =>  intval($_POST["draw"]),
            "recordsTotal"   =>  $total_row,
            "recordsFiltered"  =>  $filter_row,
            "data"       =>  $data
        );

        echo json_encode($output); 
    }

    public function edit($id){
        $data=HelpSupport::find($id);
        return view('admin.help-support.edit',compact('data'));
    }

    public function update(Request $request,$id){
        $data=HelpSupport::find($id);
        $data->status=$request->status;
        $data->save();
        return redirect()->route('admin.help_support.index')->with('success','Status Updated Successfully');
    }
}