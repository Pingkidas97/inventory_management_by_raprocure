@extends('admin.layouts.app_second', ['title' => 'Buyer', 'sub_title' => 'All Users'])
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.buyer.index') }}"> -> Buyer Module </a>
            <a href="{{ route('admin.buyer.user', ['id' => $id]) }}"> -> All Users </a>
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
                    <h3 class="">All Users</h3>
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 col-md-12 mb-3">
                            <div class="col-md-12">
                                <div id="export-progress">
                                    <p>Export Progress: <span id="progress-text">0%</span></p>
                                    <div id="progress-bar" style="width: 100%; background: #f3f3f3;">
                                        <div id="progress" style="height: 20px; width: 0%; background: green;"></div>
                                    </div>
                                </div>
                            </div>
                            <form id="searchForm" action="{{ route('admin.buyer.user', ['id' => $id]) }}" method="GET">
                                <ul class="rfq-filter-button">
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <div class="form-floating">
                                                <input type="text" id="search_name" name="name"
                                                    class="form-control fillter-form-control"
                                                    value="{{ request('name') }}"
                                                    placeholder="Search by name">
                                                <label for="search_name">Search By Name</label>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                            <div class="form-floating">
                                                <input type="text" id="search_email" name="email"
                                                    class="form-control fillter-form-control"
                                                    value="{{ request('email') }}"
                                                    placeholder="Search by email">
                                                <label for="search_email">Search By Email</label>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                            <div class="form-floating">
                                                <input type="text" id="search_mobile" name="mobile"
                                                    class="form-control fillter-form-control"
                                                    value="{{ request('mobile') }}"
                                                    placeholder="Search by contact">
                                                <label for="search_mobile">Search By Contact</label>
                                            </div>
                                        </div>
                                    </li>
                                    
                                    <li class="notShow_on_mobile">
                                        <button type="submit" class="btn-style btn-style-primary">
                                            <i class="bi bi-search"></i> Search
                                        </button>
                                    </li>

                                    <li class="notShow_on_mobile">
                                        <a href="{{ route('admin.buyer.user', ['id' => $id]) }}"
                                            class="btn-style btn-style-danger">RESET</a>
                                    </li>

                                    <li class="notShow_on_mobile">
                                        <a href="{{ route('admin.buyer.index') }}"
                                            class="btn-style btn-style-danger">BACK</a>
                                    </li>
                                    
                                    <li class="notShow_on_mobile">
                                        <button class="btn-style btn-style-white" id="export-btn">
                                            <i class="bi bi-download"></i> EXPORT
                                        </button>
                                    </li>
                                </ul>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive" id="table-container">
                        @include('admin.buyer.partials.user-table', ['results' => $results])
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
                $('#table-container').html('<div class="text-center Md py-4">Loading...</div>');
            },
            success: function(response) {
                $('#table-container').html(response);
                if (history.pushState) {
                    history.pushState(null, null, url);
                }
            },
            error: function() {
                toastr.error('Failed to load data.');
            }
        });
    }
});
</script>

<script src="{{ asset('public/assets/xlsx/xlsx.full.min.js') }}"></script>
<script src="{{ asset('public/assets/xlsx/export.js') }}"></script>
<script>
$(document).ready(function() {
    const exporter = new Exporter({
        chunkSize: 20,
        rowLimitPerSheet: 200000,
        headers: ["S.No", "Username", "Email Address", "Mobile", "Status", "Date Added"],
        totalUrl: "{{ route('admin.buyer.exportUserTotal', ['id' => $id]) }}",
        batchUrl: "{{ route('admin.buyer.exportUserBatch', ['id' => $id]) }}",
        token: "{{ csrf_token() }}",
        exportName: "buyer-user",
        expButton: '#export-btn',
        exportProgress: '#export-progress',
        progressText: '#progress-text',
        progress: '#progress-bar',
        fillterReadOnly: '.fillter-form-control',
        getParams: function() {
            return {
                name: $('#search_name').val(),
                email: $('#search_email').val(),
                mobile: $('#search_mobile').val(),
                status: $('#user_status').val(),
                parent_id: "{{ $id }}"
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