@extends('admin.layouts.app',['title' => 'Buyer','sub_title' => 'Account'])
@section('css')
<style>
    .pagination .page-link { border-radius: 8px; margin: 0 3px; font-size: 16px; color: #3b82f6; }
    .pagination .active .page-link { background-color: #3b82f6; color: white; border-color: #3b82f6; }
</style>
@endsection
@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4 mt-4">
        <div class="card-header py-3 d-flex justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Account</h6>
        </div>
        <div class="card-body">
            <div id="export-progress">
                <p>Export Progress: <span id="progress-text">0%</span></p>
                <div id="progress-bar" style="width: 100%; background: #f3f3f3;">
                    <div id="progress" style="height: 20px; width: 0%; background: green;"></div>
                </div>
            </div>
            <form id="searchForm" action="{{ route('admin.accounts.buyer') }}" method="GET">
                <div class="row mb-4">
                    <div class="col-md-2">
                        <input type="text" name="buyer_name" id="buyer_name" class="form-control fillter-form-control" value="{{ request('buyer_name') }}" placeholder="Buyer Name">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="from_date" id="from_date" class="form-control fillter-form-control" value="{{ request('from_date') }}" placeholder="From Date">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="to_date" id="to_date" class="form-control fillter-form-control" value="{{ request('to_date') }}" placeholder="To Date">
                    </div>
                    <div class="col-md-2">
                        <select class="form-control fillter-form-control" id="status" name="status">
                            <option value="">Account Status</option>
                            <option {{ request('status')=='1' ? 'selected' : '' }} value="1"> Expired </option>
                            <option {{ request('status')=='2' ? 'selected' : '' }} value="2"> Current </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-primary">SEARCH</button>
                            <a href="{{ route('admin.accounts.vendor') }}" class="btn btn-sm btn-secondary m-1 mt-0 mb-0">RESET</a>
                            <button class="btn-style btn-style-white" id="export-btn"><i class="bi bi-download"></i>EXPORT</button>
                      </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive" id="table-container">
                @include('admin.account.partials.buyer-table', ['results'=>$results])
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $(document).on('submit', '#searchForm', function(e) {
        e.preventDefault();
        loadTable($(this).attr('action') + '?' + $(this).serialize());
    });

    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        loadTable($(this).attr('href'));
    });

    // Handle perPage dropdown change
    $(document).on('change', '#perPage', function () {
        const form = $('#searchForm');
        const formData = form.serialize(); // Get current search filters
        const perPage = $(this).val();
        const url = form.attr('action') + '?' + formData + '&per_page=' + perPage;

        loadTable(url);
    });

    function loadTable(url) {
        $.ajax({
            url: url,
            type: 'GET',
            beforeSend: function () {
                $('#table-container').html('<div class="text-center py-4">Loading...</div>');
            },
            success: function(response) {
                $('#table-container').html(response);
                if (history.pushState) {
                    history.pushState(null, null, url);
                }
            }
        });
    }

});

function assignedManager(user_id,manage_id) {
    $.ajax({
        url: "{{ route('admin.accounts.buyer.manager') }}",
        type: "post",
        data: { _token: "{{ csrf_token() }}", user_id,manage_id },
        success: function(res) {
            toastr.success(res.message);
            location.reload();
        },
        error: function() {
            toastr.error('Something went wrong.');  
        },
    });
}
 
function extendFreePlan(id,user_plan,user_id,buyer_name) {
    var extend_month = $('#extend_plan_month_'+id).val();
    if (confirm('Are you sure, you want to update free plan for '+buyer_name+'?')) {
        $.ajax({
            url: "{{ route('admin.accounts.buyer.plan.extend') }}",
            type: "post",
            data: { _token: "{{ csrf_token() }}", user_id, user_plan, extend_month },
            success: function(res) {
                if(res.status)
                {
                    toastr.success(res.message);
                    location.reload();
                }else{
                    toastr.error(res.message);
                }
            },
            error: function() {
                toastr.error('Something went wrong.');  
            },
        });
    }
} 
</script>
<script src="{{ asset('public/assets/xlsx/xlsx.full.min.js') }}"></script>
<script src="{{ asset('public/assets/xlsx/export.js') }}"></script>
<script>
    $(document).ready(function () {
        const exporter = new Exporter({
            chunkSize: 20,
            rowLimitPerSheet: 200000,
            headers: ["Buyer’s Name","Plan Name", "Invoice No", "No. Of Users", "Buyer Code","Buyer’s Contact","Buyer’s Email","Date Of Subscription","Period","Next Renewal Date","Amount","Assign Manager"],
            totalUrl: "{{ route('admin.accounts.exportBuyerTotal') }}",
            batchUrl: "{{ route('admin.accounts.exportBuyerBatch') }}",
            token: "{{ csrf_token() }}",
            exportName: "Buyer-Account",
            expButton: '#export-btn',
            exportProgress: '#export-progress',
            progressText: '#progress-text',
            progress: '#progress-bar',
            fillterReadOnly: '.fillter-form-control',
            getParams: function () {
                return {
                    buyer_name: $('#buyer_name').val(),
                    status: $('#status').val(),
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val()
                };
            }
        });

        $('#export-btn').on('click', function () {
            exporter.start();
        });

        $('#export-progress').hide();
    });
</script>
@endsection
