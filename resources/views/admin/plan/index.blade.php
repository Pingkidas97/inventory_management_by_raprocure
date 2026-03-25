@extends('admin.layouts.app_second', [
    'title' => 'Manage Plan',
    'sub_title' => 'List'
])

@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span> -> Plan Module List </span>
        </h5>
    </div>
</div>
@endsection

@section('content')
<div class="page-start-section">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Plan Module List</h5>
                            <a href="{{ route('admin.plan.create') }}" class="btn-rfq btn-rfq-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> Add
                            </a>
                        </div>
                        <div id="table-container">
                          @include('admin.plan.partials.table', ['plans' => $plans])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')

@endsection