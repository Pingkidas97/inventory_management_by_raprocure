@extends('admin.layouts.app_second',['title' => 'Help and Support','sub_title' => 'List'])
@section('css')

@endsection
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i> <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.help_support.index') }}"> ->Support</a>
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
                    <h3 class="">Help and Support</h3>
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 col-md-12">
                            <form id="searchForm" action="{{ route('admin.help_support.index') }}" method="GET">
                                <ul class="rfq-filter-button">
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-journal-text"></i></span>
                                            <div class="form-floating">
                                                <input type="text" name="company_name" class="form-control fillter-form-control" value="{{ request('company_name') }}" placeholder="Search By Company Name">
                                                <label>Search By Company Name</label>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <div class="form-floating">
                                                <select name="issue_type" id="issue_type" class="form-control">
                                                    <option value=""> Select </option>
                                                    <option value="Product Issue">Product Issue</option>
                                                    <option value="Compose RFQ Issue">Compose RFQ Issue</option>
                                                    <option value="CIS Sheet Issue">CIS Sheet Issue</option>
                                                    <option value="Bulk RFQ Issue">Bulk RFQ Issue</option>
                                                    <option value="RFQ Received">RFQ Received</option>
                                                    <option value="Confirm Order">Confirm Order</option>
                                                </select>
                                                <!-- <input type="text" name="vendor_name" class="form-control fillter-form-control" value="{{ request('vendor_name') }}" placeholder="Vendor Name"> -->
                                                <label>Issue Type</label>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="notShow_on_mobile">
                                        <button type="submit" class="btn-style btn-style-primary"><i class="bi bi-search"></i> Search</button>
                                    </li>
                                    <li class="notShow_on_mobile">
                                        <a href="{{ route('admin.help_support.index') }}" class="btn-style btn-style-danger">RESET</a>
                                    </li>
                                </ul>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive product_listing_table_wrap" id="table-container">
                        @include('admin.help-support.partials.table', ['results' => $results])
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

    $(document).on('change', '.product-status-toggle', function() {
        const id = $(this).data('id');
        const status = $(this).is(':checked') ? 1 : 0;
        const checkbox = $(this);

        $.ajax({
            url: "{{ url('admin/product-approvals') }}/" + id + "/status",
            type: "PUT",
            data: { _token: "{{ csrf_token() }}", status: status },
            success: function(res) {
                toastr.success(res.message);
            },
            error: function() {
                toastr.error('Something went wrong.');
                checkbox.prop('checked', !checkbox.prop('checked'));
            }
        });
    });

    $(document).on('click', '.btn-delete-product', function() {
        if (!confirm('Are you sure?')) return;
        const id = $(this).data('id');

        $.ajax({
            url: "{{ url('admin/product-approvals') }}/" + id,
            type: "DELETE",
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                toastr.success(res.message);
                location.reload();
            }
        });
    });
});
</script>
@endsection