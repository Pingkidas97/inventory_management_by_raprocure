@extends('vendor.layouts.app_second',['title' => 'Notification','sub_title' => 'All Notification'])
@section('css')
 
@endsection
@section('content')
<section class="container-fluid">
    <!-- Start Product Content Here -->
    <section class="manage-product card">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h1 class="card-title font-size-18 mb-0">All Notification</h1>
                <form id="searchForm" action="{{ route('vendor.notification.index') }}" method="GET">
                    <div class="row align-items-center flex-wrap flex-wrap gx-3 gy-4 pt-3">
                         
                    </div>
                </form>
            </div>
            <div class="card-body add-product-section">
                <div class="table-responsive" id="table-container">
                  @include('vendor.notification.partials.table', ['notifications' => $notifications])
                </div>
            </div>
        </div>
    </section>
</section>
 
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
</script>
@endsection
