<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MiniWebPage;
class MiniWebPageController extends Controller
{
    public function index()
    {
        // You can pass user data to the view
        $user = Auth::user();
        $data=MiniWebPage::where('vendor_id',getParentUserId())->first();
        return view('vendor.web_pages.index', compact('data','user'));
    }
    public function store(Request $request)
    {
        //print_r($request->all());die;
        $data=MiniWebPage::where('vendor_id',$request->store_id)->first();
        if(empty($data)){
            $data=new MiniWebPage();
        }
        $data->vendor_id=$request->store_id;
        $data->about_us=$request->about_us;
        if($request->hasFile('catalogue')) {
            $file = $request->file('catalogue');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('uploads/web-page/', $filename);
            if(isset($data->catalogue)){
                unlink('uploads/web-page/'.$data->catalogue);
            }
            $data->catalogue = $filename;
        }
        if($request->hasFile('certification')) {
            $file = $request->file('certification');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('uploads/web-page/', $filename);
            if(isset($data->certification)){
                unlink('uploads/web-page/'.$data->certification);
            }
            $data->other_credentials = $filename;
        }
        if($request->hasFile('banner1')) {
            $file = $request->file('banner1');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('uploads/web-page/', $filename);
            if(isset($data->left_banner)){
                unlink('uploads/web-page/'.$data->left_banner);
            }
            $data->left_banner = $filename;
        }
        if($request->hasFile('banner2')) {
            $file = $request->file('banner2');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('uploads/web-page/', $filename);
            if(isset($data->right_banner)){
                unlink('uploads/web-page/'.$data->right_banner);
            }
            $data->right_banner = $filename;
        }
        $data->save();

        return response()->json([
            'success' => 1,
            'message' => 'Data has been saved successfully'
        ]);
    }
}
