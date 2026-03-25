@extends('admin.layouts.app',['title' => 'Buyer','sub_title' => 'All Buyer'])
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
            <h6 class="m-0 font-weight-bold text-primary">All Buyer</h6>
        </div>
        <div class="card-body">
            <div id="progress-container" style="margin-top: 20px; display: none;">
                <div style="width: 100%; background: #eee; height: 25px; position: relative;">
                    <div id="progress-bar" style="width: 0%; height: 100%; background: green;"></div>
                    <span id="progress-text" style="position: absolute; left: 50%; top: 3px; transform: translateX(-50%); color: #fff;">0%</span>
                </div>
            </div>
            <form id="searchForm" action="{{ route('admin.buyer.index') }}" method="GET">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <input type="text" name="user" class="form-control" value="{{ request('user') }}" placeholder="Search By Buyer Name/Email/Contact">
                    </div>
                     <div class="col-md-4">
                        <select class="form-control" id="status" name="status">
                            <option value=""> Select Status</option>
                            <option {{ request('sender')=='1' ? 'selected' : '' }} value="1"> Active</option>
                            <option {{ request('sender')=='2' ? 'selected' : '' }} value="2"> Inactive </option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                      <div class="form-group">
                        <button type="submit" class="btn btn-primary">SEARCH</button>
                        <a href="{{ route('admin.buyer.index') }}" class="btn btn-secondary ml-2">RESET</a>
                        <a href="javascript:void(0);" onclick="exportData(this);" id="export-btn" class="btn btn-success ml-2">EXPORT</a>
                      </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive" id="table-container">
                @include('admin.buyer.partials.table', ['results'=>$results,'currencies'=>$currencies])
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

function changeProfileStatus(user_id,status) {
    $.ajax({
        url: "{{ route('admin.buyer.profile.status') }}",
        type: "post",
        data: { _token: "{{ csrf_token() }}", user_id,status: status },
        success: function(res) {
            toastr.success(res.message);
            location.reload();
        },
        error: function() {
            toastr.error('Something went wrong.');  
        },
    });
}

function changeStatus(user_id,status) {
    $.ajax({
        url: "{{ route('admin.buyer.status') }}",
        type: "post",
        data: { _token: "{{ csrf_token() }}",user_id, status: status },
        success: function(res) {
            toastr.success(res.message);
            location.reload();
        },
        error: function() {
            toastr.error('Something went wrong.');      
        }
    });
}
function changeInventoryStatus(user_id,status) {
    $.ajax({
        url: "{{ route('admin.buyer.inventory.status') }}",
        type: "post",
        data: { _token: "{{ csrf_token() }}",user_id, status: status },
        success: function(res) {
            toastr.success(res.message);
            location.reload();
        },
        error: function() {
            toastr.error('Something went wrong.');      
        }
    });
}
function changeApiStatus(user_id,status) {
    $.ajax({
        url: "{{ route('admin.buyer.api.status') }}",
        type: "post",
        data: { _token: "{{ csrf_token() }}",user_id, status: status },
        success: function(res) {
            toastr.success(res.message);
            location.reload();
        },
        error: function() {
            toastr.error('Something went wrong.');  
        }
    });
}
function changeCurrency(user_id,currency) {
    $.ajax({
        url: "{{ route('admin.buyer.currency') }}",
        type: "post",
        data: { _token: "{{ csrf_token() }}",user_id, currency: currency },
        success: function(res) {
            toastr.success(res.message);
            location.reload();
        },
        error: function() {
            toastr.error('Something went wrong.');
        }
    });
}
function exportData(e) {
    const btn = document.getElementById('export-btn');
    btn.disabled = true;
    btn.textContent = 'Exporting...';
    document.getElementById('progress-container').style.display = 'block';
    let form = document.getElementById('searchForm');
    let formData = new FormData(form);
    let data = Object.fromEntries(formData.entries());
    data['export'] = true;
    fetch("{{ route('admin.buyer.export') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
            trackProgress(data.export_id, btn);
        })
    .catch(err => {
            alert('Failed to start export.');
            btn.disabled = false;
            btn.textContent = 'Start Export';
        });
}

function trackProgress(exportId, button) {
    let interval = setInterval(() => {
        fetch("{{url('/super-admin/buyer/batch-progress')}}/" + exportId)
            .then(res => res.json())
            .then(data => {
                const progress = data.progress;
                document.getElementById('progress-bar').style.width = progress + '%';
                document.getElementById('progress-text').textContent = progress + '%';

                if (progress >= 100) {
                    clearInterval(interval);
                    button.disabled = false;
                    button.textContent = 'Start Export';
                    // alert('Export complete. Downloading...');
                    window.location.href = "{{url('/super-admin/buyer/export/download')}}" + `/${exportId}`;
                }
            });
    }, 20);
}
</script>
@endsection
