@extends('vendor.layouts.app_second',['title'=>'RFQ','sub_title'=>'RFQ Received'])
@section('styles')

@endsection
@section('content')
 <section class="container-fluid">
    <!-- Start Product Content Here -->
    <section class="manage-product card">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h1 class="card-title font-size-18 mb-0">RFQ Received</h1>
                <!-- Search Section -->
                <form id="searchForm" action="{{ route('vendor.rfq.received.index') }}" method="GET">
                    <div class="row align-items-center flex-wrap flex-wrap gx-3 gy-4 pt-3">
                        <div class="col-12 col-sm-6 col-md-auto">
                            <div class="input-group generate-rfq-input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-journal-text" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="frq_no" name="frq_no" placeholder="RFQ no"  value="{{ request('frq_no') }}">
                                    <label for="frq_no">RFQ No</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-auto">
                            <div class="input-group generate-rfq-input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-person" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="buyer_name" name="buyer_name" placeholder="Buyer Name" value="{{ request('buyer_name') }}">
                                    <label for="buyer_name">Buyer Name</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-auto">
                            <div class="input-group generate-rfq-input-group">
                                <span class="input-group-text">
                                    <span class="bi bi-record2" aria-hidden="true"></span>
                                </span>
                                <div class="form-floating">
                                    <select name="status" id="status" class="form-select cw-200">
                                        <option value=""> Select </option>
                                        <option value="1">RFQ Received </option>
                                        <option value="4">Counter Offer Received</option>
                                        <option value="5">Order Confirmed </option>
                                        <option value="6">Counter Offer Sent</option>
                                        <option value="7">Quotation Sent </option>
                                        <option value="8">Closed </option>
                                    </select>
                                    <label for="rfqStatus">RFQ Status</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-auto">
                            <div class="d-flex align-item-center gap-3">
                                <button type="submit" class="ra-btn ra-btn-primary">
                                    <span class="bi bi-search font-size-12"></span>
                                    <span class="font-size-11">Search</span>
                                </button>
                                <a href="{{ route('vendor.rfq.received.index') }}" class="ra-btn ra-btn-outline-danger">
                                    <span class="font-size-11">Reset</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body add-product-section">
                <div class="table-responsive" id="table-container">
                    @include('vendor.rfq-received.partials.table', ['results' => $results])
                </div>
            </div>
        </div>
    </section>
</section>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.mng-custom-tooltip').tooltip({'placement':'top'});
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
                $('#table-container').html('<div class="text-center py-4 vh-100">Loading...</div>');
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
</script>

@endsection