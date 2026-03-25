@extends('admin.layouts.app',['title' => 'Manage Advertisement','sub_title' => 'List'])
@section('css')

@endsection
@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4 mt-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Manage Advertisement</h6>
            <a href="{{ route('admin.advertisement.create') }}" class="btn btn-primary">Add New</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="datatableDefault" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Buyer Name/ Vendor Name</th>
                            <th>Received On</th>
                            <th>Payment Received On</th>
                            <th>Validity Period</th>
                            <th>Image Upload</th>
                            <th>Ad Position</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function dataList() {
        $('#datatableDefault').DataTable().destroy();
        $('#datatableDefault').DataTable({
            processing: true,
            serverSide: true,
            // dom: "<'row mb-3'<'col-sm-4'l><'col-sm-8 text-end'<'d-flex justify-content-end'fB>>>t<'d-flex align-items-center'<'me-auto'i><'mb-0'p>>",
            // lengthMenu: [20, 50, 100, 200, 500, 1000, 2000, 5000, 10000],
            responsive: true,
            // columnDefs: [{
            //     width: 200,
            //     targets: 3
            // }],
            // fixedColumns: true,
            // buttons: [{
            //         extend: 'print',
            //         className: 'btn btn-default btn-sm'
            //     },
            //     {
            //         extend: 'csv',
            //         className: 'btn btn-default btn-sm'
            //     }
            // ],
            ajax: {
                url: "{{route('admin.advertisement.list')}}",
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}"
                },
            },
            "initComplete": function() {

            }
        });
    }
    dataList();
    function deleteData(url) {
        if (confirm('Are you sure you want to delete this user?')) {
            $.ajax({
                url: url,
                type: "post",
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        dataList();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('An error occurred. Please try again.');
                }
            });
        }
    }

</script>
@endsection