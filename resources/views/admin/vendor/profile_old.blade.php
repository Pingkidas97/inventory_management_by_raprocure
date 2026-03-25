@extends('admin.layouts.app',['title' => 'Vendor','sub_title' => 'Profile'])
@section('css')

@endsection
@section('content')

<div class="container-fluid">
    <div class="card shadow mb-4 mt-3">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary">Profile</h5>
        </div>
        <div class="card-body">
            <form id="create-form" action="{{route('admin.vendor.profile.update',$data->id)}}" method="post">
                @csrf
                @method('PUT')
                <div class="row mb-3">
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="plan_name" class="form-label">Company Name / Legal Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="Company Name / Legal Name" value="{{$data->plan_name}}">
                            <span class="text-danger error-text plan_name_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="plan_name" class="form-label">Date of Incorporation(DD/MM/YYYY)<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="Date of Incorporation(DD/MM/YYYY)" value="{{$data->plan_name}}">
                            <span class="text-danger error-text plan_name_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="plan_name" class="form-label">(File Type: JPG,JPEG,PNG)
                            Select<span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="plan_name" name="plan_name" placeholder="Date of Incorporation(DD/MM/YYYY)" value="{{$data->plan_name}}">
                            <span class="text-danger error-text plan_name_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="plan_name" class="form-label">Registered Address<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="Registered Address" value="{{$data->plan_name}}">
                            <span class="text-danger error-text plan_name_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="plan_name" class="form-label">Country<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="Country" value="{{$data->plan_name}}">
                            <span class="text-danger error-text plan_name_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="plan_name" class="form-label">State<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="State" value="{{$data->plan_name}}">
                            <span class="text-danger error-text plan_name_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="plan_name" class="form-label">City<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="City" value="{{$data->plan_name}}">
                            <span class="text-danger error-text plan_name_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="plan_name" class="form-label">Pincode<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="Pincode" value="{{$data->plan_name}}">
                            <span class="text-danger error-text plan_name_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="plan_name" class="form-label">GSTIN/VAT<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="GSTIN/VAT" value="{{$data->plan_name}}">
                            <span class="text-danger error-text plan_name_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="plan_name" class="form-label">PAN/TIN<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="PAN/TIN" value="{{$data->plan_name}}">
                            <span class="text-danger error-text plan_name_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="plan_name" class="form-label">(File Type: JPG, JPEG, PDF)<span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="plan_name" name="plan_name" placeholder="(File Type: JPG, JPEG, PDF)" value="{{$data->plan_name}}">
                            <span class="text-danger error-text plan_name_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="plan_name" class="form-label">Website<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="Website" value="{{$data->plan_name}}">
                            <span class="text-danger error-text plan_name_error"></span>
                        </div>
                    </div>
                    <div class="col-md-6 p-2">
                        <div class="form-group">
                            <label for="plan_name" class="form-label">Output / Product Details<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="Output / Product Details" value="{{$data->plan_name}}">
                            <span class="text-danger error-text plan_name_error"></span>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.plan.index') }}" class="btn btn-secondary  me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')

@endsection