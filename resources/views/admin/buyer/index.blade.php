@extends('admin.layouts.app_second',['title' => 'Buyer','sub_title' => 'Module'])

@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.buyer.index') }}"> -> Buyer Module </a>
        </h5>
    </div>
</div>
@endsection
  
@section('content')
<div class="about_page_details">
    <div class="container-fluid">
        <div class="card border-0">
            <div class="card-body">
                <div class="col-md-12 botom-border">
                    <h3 class="">Buyer Module</h3>
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 col-md-12 mb-3">
                            <div class="col-md-12">
                                <div id="export-progress" style="display: none;">
                                    <p>Export Progress: <span id="progress-text">0%</span></p>
                                    <div id="progress-bar" style="width: 100%; background: #f3f3f3;">
                                        <div id="progress" style="height: 20px; width: 0%; background: green;"></div>
                                    </div>
                                </div>
                            </div>
                            <form id="searchForm" action="{{ route('admin.buyer.index') }}" method="GET">
                                <ul class="rfq-filter-button">
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <div class="form-floating">
                                                <input 
                                                    type="text" 
                                                    id="search_user" 
                                                    name="user" 
                                                    class="form-control fillter-form-control" 
                                                    value="{{ request('user') }}" 
                                                    placeholder="Search by name/email/contact" 
                                                    style="width: 350px;"
                                                >
                                                <label for="search_user">Search By Buyer Name/Email/Contact</label>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-record2"></i></span>
                                            <div class="form-floating">
                                                <select 
                                                    name="status" 
                                                    id="buyer_status" 
                                                    class="form-select fillter-form-select"
                                                >
                                                    <option value="">Select</option>
                                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                                <label for="buyer_status">Status</label>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="notShow_on_mobile">
                                        <button type="submit" class="btn-style btn-style-primary">
                                            <i class="bi bi-search"></i> Search
                                        </button>
                                    </li>

                                    <li class="notShow_on_mobile">
                                        <a href="{{ route('admin.buyer.index') }}" class="btn-style btn-style-danger">RESET</a>
                                    </li>

                                    <li class="notShow_on_mobile">
                                        <button class="btn-style btn-style-white" id="export-btn"><i class="bi bi-download"></i>
                                            EXPORT
                                        </button>
                                    </li>
                                </ul>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive" id="table-container">
                        @include('admin.buyer.partials.table', ['results' => $results, 'currencies' => $currencies])
                    </div>
                </div>
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

    $(document).on('change', '#perPage', function () {
        const form = $('#searchForm');
        const formData = form.serialize();
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
        },
        error: function() {
            toastr.error('Something went wrong.');  
        },
    });
}

function changeStatus(user_id, status) {
    $.ajax({
        url: "{{ route('admin.buyer.status') }}",
        type: "post",
        data: { _token: "{{ csrf_token() }}", user_id, status: status },
        success: function(res) {
            toastr.success(res.message);
            if(status==1){
                $(".buyer-delete-"+user_id).addClass("d-none");
            }else{
                $(".buyer-delete-"+user_id).removeClass("d-none");
            }
        },
        error: function() {
            toastr.error('Something went wrong.');      
        }
    });
}
function deleteBuyer(_this, user_id, buyer_name) {
    if(!confirm("Are you sure want to delete '"+buyer_name+"' buyer?")){
        return false;
    }
    $(_this).addClass('disabled');
    $.ajax({
        url: "{{ route('admin.buyer.delete') }}",
        type: "post",
        dataType: 'json',
        data: { _token: "{{ csrf_token() }}", user_id },
        success: function(res) {
            if(res.status){
                toastr.success(res.message);
                setTimeout(() => {
                    location.reload();
                }, 1500);
            }else{
                $(_this).removeClass('disabled');
                toastr.error(res.message);
            }
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

</script>

<script src="{{ asset('public/assets/xlsx/xlsx.full.min.js') }}"></script>
<script src="{{ asset('public/assets/xlsx/export.js') }}"></script>
<script>
    $(document).ready(function () {
        const exporter = new Exporter({
            chunkSize: 20,
            rowLimitPerSheet: 200000,
            headers: ["Buyer Code","Buyer Name", "Primary Contact", "Buyer Email", "Date Of Verification","Buyer Contact","Status"],//"Profile Status",
            totalUrl: "{{ route('admin.buyer.exportBuyerTotal') }}",
            batchUrl: "{{ route('admin.buyer.exportBuyerBatch') }}",
            token: "{{ csrf_token() }}",
            exportName: "buyer",
            expButton: '#export-btn',
            exportProgress: '#export-progress',
            progressText: '#progress-text',
            progress: '#progress-bar',
            fillterReadOnly: '.fillter-form-control',
            getParams: function () {
                return {
                    user: $('#search_user').val(),
                    status: $('#buyer_status').val(),
                    profile_status: $('#profile_status').val()
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
