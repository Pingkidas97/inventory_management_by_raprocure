@extends('admin.layouts.app_second',['title' => 'Vendor','sub_title' => 'Module'])
@section('css')

@endsection
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.vendor.index') }}"> -> Vendor Module </a>
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
                    <h3 class="">Vendor Module</h3>
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
                            <form id="searchForm" action="{{ route('admin.vendor.index') }}" method="GET">
                                <ul class="rfq-filter-button">
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <div class="form-floating">
                                                <input type="text" id="search_user" name="user"
                                                    class="form-control fillter-form-control"
                                                    value="{{ request('user') }}"
                                                    placeholder="Search by name/email/contact" style="width: 350px;">
                                                <label for="search_user">Search By Vendor Name/Email/Contact</label>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-record2"></i></span>
                                            <div class="form-floating">
                                                <select name="status" id="vendor_status"
                                                    class="form-select fillter-form-select">
                                                    <option value="">Select</option>
                                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>
                                                        Active</option>
                                                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>
                                                        Inactive</option>
                                                </select>
                                                <label for="vendor_status">Status</label>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-record2"></i></span>
                                            <div class="form-floating">
                                                <select name="profile_status" id="profile_status"
                                                    class="form-select fillter-form-select">
                                                    <option value="">Select</option>
                                                    <option value="1"
                                                        {{ request('profile_status') == '1' ? 'selected' : '' }}>Pending
                                                    </option>
                                                    <option value="2"
                                                        {{ request('profile_status') == '2' ? 'selected' : '' }}>
                                                        Incomplete</option>
                                                </select>
                                                <label for="profile_status">Profile Status</label>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <button type="submit" class="btn-style btn-style-primary">
                                            <i class="bi bi-search"></i> Search
                                        </button>
                                    </li>

                                    <li>
                                        <a href="{{ route('admin.vendor.index') }}"
                                            class="btn-style btn-style-danger">RESET</a>
                                    </li>

                                    <li>
                                        <button class="btn-style btn-style-white" id="export-btn"><i
                                                class="bi bi-download"></i>
                                            EXPORT
                                        </button>
                                    </li>

                                    <li>
                                        <a href="{{ route('admin.vendor.registration') }}"
                                            class="btn-rfq btn-rfq-white"> + Add New Vendor</a>
                                    </li>


                                    
                                </ul>
                            </form>

                        </div>

                    </div>

                    <div class="table-responsive" id="table-container">
                        @include('admin.vendor.partials.table', ['results' => $results])
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

    $(document).on('change', '#perPage', function() {
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
            beforeSend: function() {
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

    $('.profile-status-toggle').on('change', function() {
        let userId = $(this).data('id');
        let newStatus = $(this).is(':checked') ? 1 : 2;

        changeProfileStatus(this, userId, newStatus);
    });

    function changeProfileStatus(_this, user_id, status) {
        $.ajax({
            url: "{{ route('admin.vendor.profile.status') }}",
            type: "post",
            dataType: "json",
            data: { _token: "{{ csrf_token() }}", user_id, status: status },
            success: function(res) {
                if(res.status){
                    toastr.success(res.message);
                    if(res.is_reload){
                        setTimeout(function(){
                            window.location.reload();
                        }, 1500);
                    }
                }else{
                    toastr.error(res.message);
                }
                if(status==1){
                    $(_this).parents("tr").find(".web-page").removeClass("d-none");
                }else{
                    $(_this).parents("tr").find(".web-page").addClass("d-none");
                }
            },
            error: function() {
                toastr.error('Something went wrong.');  
            },
        });
    }

    $('.status-toggle').on('change', function() {
        var userId = $(this).data('id');
        var newStatus = $(this).is(':checked') ? 1 : 2;

        changeStatus(this, userId, newStatus);
    });

    function changeStatus(_this, user_id, status) {
        $.ajax({
            url: "{{ route('admin.vendor.status') }}",
            type: "post",
            dataType: "json",
            data: { _token: "{{ csrf_token() }}",user_id, status: status },
            success: function(res) {
                toastr.success(res.message);
                if(status==1){
                    $(".vendor-delete-"+user_id).addClass("d-none");
                    $(_this).parents("tr").find(".vendor-product-btn").removeClass("d-none");
                }else{
                    $(".vendor-delete-"+user_id).removeClass("d-none");
                    $(_this).parents("tr").find(".vendor-product-btn").addClass("d-none");
                }
            },
            error: function() {
                toastr.error('Something went wrong.');      
            }
        });
    }
});
function deleteVendor(_this, user_id, vendor_name) {
    if(!confirm("Are you sure want to delete '"+vendor_name+"' vendor?")){
        return false;
    }
    $(_this).addClass('disabled');
    $.ajax({
        url: "{{ route('admin.vendor.delete') }}",
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
</script>

<script src="{{ asset('public/assets/xlsx/xlsx.full.min.js') }}"></script>
<script src="{{ asset('public/assets/xlsx/export.js') }}"></script>
<script>
$(document).ready(function() {
    const exporter = new Exporter({
        chunkSize: 20,
        rowLimitPerSheet: 200000,
        headers: ["Vendor Code", "Vendor Name", "Primary Contact", "Vendor Email",
            "Date Of Verification", "No Of Verified Products", "Vendor Contact", "Profile Status",
            "Status", "Added by Super Admin"
        ],
        totalUrl: "{{ route('admin.vendor.exportTotal') }}",
        batchUrl: "{{ route('admin.vendor.exportBatch') }}",
        token: "{{ csrf_token() }}",
        exportName: "vendor",
        expButton: '#export-btn',
        exportProgress: '#export-progress',
        progressText: '#progress-text',
        progress: '#progress-bar',
        fillterReadOnly: '.fillter-form-control',
        getParams: function() {
            return {
                user: $('#search_user').val(),
                status: $('#vendor_status').val(),
                profile_status: $('#profile_status').val()
            };
        }
    });

    $('#export-btn').on('click', function() {
        exporter.start();
    });

    $('#export-progress').hide();
});
</script>
@endsection