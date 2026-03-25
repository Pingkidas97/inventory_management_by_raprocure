@extends('admin.layouts.app_second',['title'=>'Help and Support','sub_title'=>'Edit'])

@section('css')

@endsection
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i> <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.help_support.index') }}"> ->Support</a>
            <a href="javascript:void(0);"> -> Write to Us</a>
        </h5>
    </div>
</div>
@endsection

@section('content')
<div class="about_page_details">
    <div class="container-fluid">
        <div class="row justify-content-center pt-5">
            <div class="col-xl-8 col-lg-8 col-md-9 col-sm-12">
                <div class="card border-0">
                    <div class="card-body">
                        <h5 class="border-bottom">Write to Us</h5>
                        <form action="{{route('admin.help_support.update',$data->id)}}" method="post">
                            @csrf
                            @method('PUT')
                            <div class="row mb-3">
                                <div class="col-md-6 p-2">
                                    <div class="form-group">
                                        <label for="issue_type" class="form-label">Issue Type: <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="issue_type" name="issue_type" disabled>
                                            <option value="">Select Issue</option>
                                            <option
                                                {{ old('issue_type', $data->issue_type) == 'CIS Sheet Issue' ? 'selected' : ''}}
                                                value="CIS Sheet Issue">CIS Sheet Issue</option>
                                            <option
                                                {{ old('issue_type', $data->issue_type) == 'RFQ Received' ? 'selected' : ''}}
                                                value="RFQ Received">Compose RFQ Issue</option>
                                            <option
                                                {{ old('issue_type', $data->issue_type) == 'RFQ Received' ? 'selected' : ''}}
                                                value="RFQ Received">Bulk RFQ Issue</option>
                                            <option
                                                {{ old('issue_type', $data->issue_type) == 'RFQ Received' ? 'selected' : ''}}
                                                value="RFQ Received">RFQ Received</option>
                                            <option
                                                {{ old('issue_type', $data->issue_type) == 'Confirm Order' ? 'selected' : ''}}
                                                value="Confirm Order">Confirm Order</option>
                                            <option
                                                {{ old('issue_type', $data->issue_type) == 'Product Issue' ? 'selected' : ''}}
                                                value="Product Issue">Product Issue</option>
                                        </select>
                                        <span class="text-danger error-text issue_type_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-6 p-2">
                                    <div class="form-group">
                                        <label for="document" class="form-label">Image Upload<span
                                                class="text-danger">*</span></label>
                                        <input type="file" class="form-control" id="document" name="document" disabled>
                                        <input type="hidden" name="old_image" id="old_image"
                                            value="{{$data->document}}">
                                        @if($data->document)
                                        <a href="{{asset('uploads/ticket_document/'.$data->document)}}">{{$data->document}}</a>
                                        @endif
                                        <span class="text-danger error-text document_error"></span>
                                    </div>
                                </div>
                                <div class="col-md-6 p-2">
                                    <div class="form-group">
                                        <label for="status" class="form-label">Status <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="status" name="status">
                                            <option {{ $data->status == '1' ? 'selected' : ''}} value="1">Pending
                                            </option>
                                            <option {{ $data->status == '2' ? 'selected' : ''}} value="2">Working
                                            </option>
                                            <option {{ $data->status == '3' ? 'selected' : ''}} value="3">Close</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12 p-2">
                                    <div class="form-group">
                                        <label for="description" class="form-label">Description<span
                                                class="text-danger">*</span></label>
                                        <textarea class="form-control" id="description" name="description"
                                            disabled>{{$data->description}}</textarea>
                                        <span class="text-danger error-text description_error"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn-rfq btn-rfq-primary support-submit-btn m-1"><i
                                        class="bi bi-send"></i> Save</button>
                                <a href="{{ route('admin.help_support.index') }}"
                                    class="ml-3 btn-rfq btn-rfq-secondary m-1">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')

@endsection