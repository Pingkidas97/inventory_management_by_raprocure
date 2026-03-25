@extends('vendor.layouts.app_second',['title'=>'Help and Support','sub_title'=>''])
@section('css')

@endsection
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i> <a href="{{ route('vendor.dashboard') }}">Dashboard</a>
            <a href="{{ route('vendor.help_support.index') }}"> ->Support</a>
        </h5>
    </div>
</div>
@endsection

@section('content')
<section class="container-fluid">
    <!-- Start Product Content Here -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-global py-2 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Help and Support</li>
        </ol>
    </nav>
    <section class="manage-product card">
        <div class="card">
            <div class="card-header bg-white py-3 d-flex justify-content-between">
                <h1 class="card-title font-size-18 mb-0">Help and Support</h1>
                <!-- Search Section -->
                <div class="d-flex gap-2">
                    <a href="{{ route('vendor.help_support.create') }}" class="ra-btn ra-btn-primary">Write to Us</a>
                    <a href="{{ asset('uploads/FAQ-Vendors.pdf') }}" target="_blank" class="ra-btn ra-btn-outline-danger">FAQ</a>
                </div>
            </div>
            <form id="searchForm" action="{{ route('vendor.help_support.index') }}" method="GET"></form>
            <div class="card-body add-product-section">
                <div class="table-responsive" id="table-container">
                    @include('vendor.help-support.partials.table', ['results' => $results])
                </div>
            </div>
        </div>
    </section>
</section>
 







 
 
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header justify-content-between">
                <h5 class="modal-title" id="exampleModalLongTitle">View Request</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <input type="hidden" class="REQUEST_ID" value="">
            <input type="hidden" class="USERNAME" value="">
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <span><b>REQUEST ID</b> :</span>
                    </div>
                    <div class="col-md-8">
                        <span class="request_id"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <span><b>ISSUE TYPE</b> :</span>
                    </div>
                    <div class="col-md-8">
                        <span class="issue_type"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <span><b>STATUS</b> :</span>
                    </div>
                    <div class="col-md-8">
                        <span class="status">
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <span><b>UPLOAD FILE</b> :</span>
                    </div>
                    <div class="col-md-8">
                        <span class="uploadfile">
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <span><b>DESCRIPTION</b> :</span>
                    </div>
                    <div class="col-md-8">
                        <span class="discription"></span>
                    </div>
                </div>
                <div class="row  d-none">
                    <div class="col-md-4">
                        <span><b>RAPROCURE ATTACHMENT</b> :</span>
                    </div>
                    <div class="col-md-8">
                        <span class="attachment"></span>
                    </div>
                </div>
                <div class="row d-none">
                    <div class="col-md-4">
                        <span><b>RAPROCURE RESPONSE</b> :</span>
                    </div>
                    <div class="col-md-8">
                        <span class="raprocure_response"></span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" style="position: relative;" data-bs-toggle="modal"
                    data-target="#internalChatt" class="btn-rfq btn-rfq-primary sendNewMessage" onclick="remove_attachment_file()">Send New
                    Message</button>
                <div class="card-body" style="width: 100%;">
                    <div class="all_reply_message" style="height: 570px;overflow-y: auto;">
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


function viewrequest(id, userid) {
    $.ajax({
        url: "{{ route('vendor.help_support.view') }}",
        type: 'POST',
        data: {
            id: id,
            userid: userid,
            _token: "{{ csrf_token() }}"
        },
        dataType: 'JSON',
        success: function(response) {
            if (response.status == true) {
                var responseData = response.data;
                var userData = response.user;
                var btn_disable = 'disabled';
                if (responseData.status == 3) {
                    $('.sendNewMessage').attr(btn_disable, true);
                } else {
                    $('.sendNewMessage').removeAttr(btn_disable, true);
                }
                var subject = 'Subject : ' + responseData.request_id + '/' + responseData.issue_type;
                 
                $('.all_reply_message').html('');
                $('.requestId').val(responseData.request_id);
                $('.request_id').html(responseData.request_id);
                $('.issue_type').html(responseData.issue_type);
                $('.discription').html(responseData.description);
                $('.attachment').html(responseData.raprocure_attachment);
                $('.raprocure_response').html(responseData.raprocure_response);
                if (responseData.status == 1) {
                    $('.status').html('Pending');
                } else if (responseData.status == 2) {
                    $('.status').html('Working');
                } else if (responseData.status == 3) {
                    $('.status').html('Close');
                }
                let asset_url="{{asset('uploads/ticket_document')}}";
                if (responseData.document != '') {
                    $('.uploadfile').html('<a download href="'+asset_url+'/' +
                        responseData.document + '">' + responseData.document + '</a>');
                }
                if (responseData.raprocure_attachment != '' && responseData.raprocure_attachment != null) {
                    $('.attachment').html('<a download href="'+asset_url+'/' +
                        responseData.raprocure_attachment + '">' + responseData.raprocure_attachment +
                        '</a>');
                }

                $('#exampleModalCenter').modal('show');
            } else if (response.status == false) {
                alert(response.message);
            }
        }

    })
}
</script>
@endsection