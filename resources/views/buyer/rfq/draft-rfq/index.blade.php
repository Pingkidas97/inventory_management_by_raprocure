@extends('buyer.layouts.app', ['title'=>'Draft RFQ'])

@section('css')
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar-default')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1">
        <div class="container-fluid">
            <div class="bg-white active-rfq-page">                 
                <h3 class="card-head-line">Draft RFQ</h3>
                <div class="px-2">
                    <form id="filter-rfq" action="{{ route('buyer.rfq.draft-rfq') }}" method="GET">
                        <div class="row g-3 rfq-filter-button">
                            <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="draft_rfq_no" placeholder="" value="{{ request('draft_rfq_no') }}" id="rfq-no"/>
                                        <label for="rfq-no">Draft RFQ No</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-share"></span></span>
                                    <div class="form-floating">
                                        <select class="form-select" id="division" name="division">
                                            <option value="0" selected=""> Select </option>
                                            @foreach ($divisions as $item)
                                                <option value="{{ $item->id }}" {{ request('division')==$item->id ? 'selected' : '' }} >{{ $item->division_name }}</option>                                                
                                            @endforeach
                                        </select>
                                        <label for="division">Division</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-signpost"></span></span>
                                    <div class="form-floating">
                                        <select class="form-select" id="category" name="category">
                                            <option value="0" selected=""> Select </option>
                                            @foreach ($unique_category as $item => $ids)
                                                <option value="{{ implode(',', $ids) }}" {{ request('category')==implode(',', $ids) ? 'selected' : '' }}>{{ $item }}</option>                                                
                                            @endforeach
                                        </select>
                                        <label for="category">Category</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-ubuntu"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="product_name" placeholder="" value="{{ request('product_name') }}" id="product-name"/>
                                        <label for="product-name">Product Name</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col12 col-sm-auto mb-3">
                                <div class="d-flex gap-3">
                                    <button type="submit" class="ra-btn small-btn ra-btn-primary">
                                        <span class="bi bi-search"></span> Search
                                    </button>
                                    <a href="{{ route('buyer.rfq.draft-rfq') }}" class="ra-btn small-btn ra-btn-outline-danger" id="reset-filter">
                                        <span class="bi bi-arrow-clockwise"></span> Reset
                                    </a>
                                    <a href="javascript:void(0)" class="ra-btn small-btn ra-btn-outline-danger" id="delete-selected-draft-rfq">
                                        <span class="bi bi-trash3"></span>  Selected
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive p-2" id="table-container">
                    @include('buyer.rfq.draft-rfq.partials.table', ['results' => $results])
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $(document).on('submit', '#filter-rfq', function(e) {
            e.preventDefault();
            loadTable($(this).attr('action') + '?' + $(this).serialize());
        });

        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            loadTable($(this).attr('href'));
        });

        $(document).on('change', '#perPage', function () {
            const form = $('#filter-rfq');
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
        
        $(document).on("click", "#reset-filter", function(){
            $(".rfq-filter-button").find("input").val("");
            $(".rfq-filter-button").find("select").val("0");
        }).on("click", "#delete-selected-draft-rfq", function(){
            let ids = [];
            $(".select-draft-rfq:checked").each(function(){
                ids.push($(this).val());
            });
            if(ids.length > 0){
                deleteDraftRFQ(ids);
            }else{
                alert("Please Select atleast one Draft RFQ to Delete.");
            }
        }).on("click", ".delete-this-draft-rfq", function(){
            let ids = [];
            ids.push($(this).parents('tr.table-tr').find('.select-draft-rfq').val());
            if(ids.length > 0){
                deleteDraftRFQ(ids);
            }
        });

        function deleteDraftRFQ(ids){            
            if(ids.length > 0){
                let url = "{{ route('buyer.rfq.draft-rfq.delete-draft-rfq') }}";
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        draft_rfq_ids: ids
                    },
                    success: function(response) {
                        if(response.status == 'success'){
                            loadTable(response.url);
                        }
                    }
                });
            }
        }

    });
</script>
@endsection