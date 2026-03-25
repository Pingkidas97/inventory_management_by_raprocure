@extends('buyer.layouts.app', ['title'=>'Manage Users'])

@section('css')
    {{-- <link rel="stylesheet" href="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.css') }}" /> --}}
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1 inner-main">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <h1 class="font-size-18 mb-0">Manage Users</h1>
                    @if(checkPermission('MANAGE_USERS','add','1'))
                        <a href="{{ route('buyer.user-management.create-user') }}" class="ra-btn small-btn ra-btn-primary my-1">Add User</a>
                    @endif
                </div>
                <div class="card-body">
                    <form id="searchForm" action="{{ route('buyer.user-management.users') }}" method="GET"></form>
                    <div class="table-responsive p-2">
                          @include('buyer.user-management.partials.table', ['results' => $results])
                    </div>
                </div>
            </div>
        </div>
    </main>
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
})
</script>
@endsection