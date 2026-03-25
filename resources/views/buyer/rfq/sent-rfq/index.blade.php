@extends('buyer.layouts.app', ['title'=>'Active RFQs/CIS'])

@section('css')
@endsection

@section('content')
    <div class="bg-white">
        <!---Sidebar-->
        @include('buyer.layouts.sidebar')
    </div>

    <!---Section Main-->
    <main class="main flex-grow-1 inner-main">
        <div class="container-fluid">
            <div class="bg-white sent-rfq-page">
                <form class="mb-3">
                    <h3 class="card-head-line px-2">Sent RFQ</h3>
                    <div class="px-2">
                        <div class="row g-3 rfq-filter-button">
                            <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="RFQ No" placeholder="" value="" />
                                        <label for="">RFQ No</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-share"></span></span>
                                    <div class="form-floating">
                                        <select class="form-select">
                                            <option selected=""> Select </option>
                                            <option> NEW DIVISION FOR TESTING </option>
                                            <option> NEW DIVISION FOR TESTING </option>
                                            <option> CCM </option>
                                            <option> NEW DIVISION FOR TESTING </option>
                                            <option> NEW DIVISION FOR TESTING </option>
                                            <option> DRI </option>
                                            <option> NEW DIVISION FOR TESTING </option>
                                            <option> DRI </option>
                                            <option> NEW DIVISION FOR TESTING </option>
                                            <option> DRI </option>
                                            <option> CCM </option>
                                            <option> NEW DIVISION FOR TESTING </option>
                                            <option> CCM </option>
                                            <option> fbhbfkjdfkdkjvldkvlfkfmlfkgmlkmflkgvjflkgvjfjlkgfk;kgf;lgkd;lg;d</option>
                                            <option> CCM </option>
                                            <option> NEW DIVISION FOR TESTING </option>
                                            <option> NEW DIVISION FOR TESTING </option>
                                            <option> NEW DIVISION FOR TESTING </option>
                                            <option> NEW DIVISION FOR TESTING </option>
                                            <option> NEW DIVISION FOR TESTING </option>
                                            <option> CCM </option>
                                            <option> DRI </option>
                                            <option> NEW DIVISION FOR TESTING </option>
                                            <option> DRI </option>
                                            <option> CCM </option>
                                            <option> DRI </option>
                                            <option> NEW DIVISION FOR TESTING</option>
                                            <option> DRI </option>
                                        </select>
                                        <label for="">Division</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-signpost"></span></span>
                                    <div class="form-floating">
                                        <select class="form-select">
                                            <option selected=""> Select </option>
                                            <option> CIVIL </option>
                                            <option> CABLES </option>
                                        </select>
                                        <label>Category</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-ubuntu"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="name" placeholder="" value="" />
                                        <label for="">Product Name</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4 col-md-4 col-lg-auto mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><span class="bi bi-journal-text"></span></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="name" placeholder="" value="" />
                                        <label for="">PRN Number</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-auto mb-3">
                                <div class="d-flex gap-3">
                                    <button type="submit" class="ra-btn small-btn ra-btn-primary">
                                        <span class="bi bi-search"></span>
                                        Search
                                    </button>
                                    <button type="button" class="ra-btn small-btn ra-btn-outline-danger">
                                        <span class="bi bi-arrow-clockwise"></span>
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive p-2">
                    <table class="product-listing-table w-100">
                        <thead>
                            <tr>
                                <th>RFQ No.</th>
                                <th>RFQ Date</th>
                                <th>Product Name</th>
                                <th>PRN Number</th>
                                <th>Branch/Unit</th>
                                <th>Username</th>
                                <th>RFQ Status</th>
                                <th>RFQ Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>RATB-25-00049</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        KILN AIR INJECTOR TUBEKILN AIR INJECTOR TUBEKILN AIR INJECTOR TUBE
                                        </span>
                                        <button class="btn btn-link text-black border-0 p-0 font-size-12 bi bi-info-circle-fill ms-1"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="KILN AIR INJECTOR TUBEKILN AIR INJECTOR TUBEKILN AIR INJECTOR TUBE"></button>
                                    </div>
                                </td>
                                <td></td>
                                <td> Buyer Branch 1</td>
                                <td> BUYER TESTER</td>
                                <td>
                                    <span class="rfq-status Auction-Completed">Closed</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Re-Use</button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="list-unread">
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        AMIT TESTING PRODUCTS
                                        </span>
                                    </div>
                                </td>
                                <td></td>
                                <td>Branch 2</td>
                                <td>User 2</td>
                                <td>
                                    <span class="rfq-status rfq-generate">Active</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        KILN AIR INJECTOR TUBE
                                        </span>
                                    </div>
                                </td>
                                <td>60</td>
                                <td>Branch 3</td>
                                <td>User 3</td>
                                <td>
                                    <span class="rfq-status rfq-generate">Active</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary disabled">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        WATER TESTING KIT
                                        </span>
                                    </div>
                                </td>
                                <td></td>
                                <td>Branch 4</td>
                                <td>User 4</td>
                                <td>
                                    <span class="rfq-status rfq-generate">Active</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        AMIT TESTING
                                        </span>
                                    </div>
                                </td>
                                <td>64</td>
                                <td>Branch 5</td>
                                <td>User 5</td>
                                <td>
                                    <span class="rfq-status Partial-order">Order Confirmed</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Re-Use</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        KILN AIR INJECTOR TUBEKILN AIR
                                        </span>
                                    </div>
                                </td>
                                <td>21,22</td>
                                <td>Branch 6</td>
                                <td>User 6</td>
                                <td>
                                    <span class="rfq-status Partial-order">Partial Order</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light disabled">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>RATB-25-00049</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        KILN AIR INJECTOR TUBEKILN AIR INJECTOR TUBEKILN AIR INJECTOR TUBE
                                        </span>
                                    </div>
                                </td>
                                <td></td>
                                <td> Buyer Branch 1</td>
                                <td> BUYER TESTER</td>
                                <td>
                                    <span class="rfq-status Auction-Completed">Closed</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Re-Use</button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="list-unread">
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        AMIT TESTING PRODUCTS
                                        </span>
                                    </div>
                                </td>
                                <td></td>
                                <td>Branch 2</td>
                                <td>User 2</td>
                                <td>
                                    <span class="rfq-status rfq-generate">Active</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        KILN AIR INJECTOR TUBE
                                        </span>
                                    </div>
                                </td>
                                <td>60</td>
                                <td>Branch 3</td>
                                <td>User 3</td>
                                <td>
                                    <span class="rfq-status rfq-generate">Active</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary disabled">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        WATER TESTING KIT
                                        </span>
                                    </div>
                                </td>
                                <td></td>
                                <td>Branch 4</td>
                                <td>User 4</td>
                                <td>
                                    <span class="rfq-status rfq-generate">Active</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        AMIT TESTING
                                        </span>
                                    </div>
                                </td>
                                <td>64</td>
                                <td>Branch 5</td>
                                <td>User 5</td>
                                <td>
                                    <span class="rfq-status Partial-order">Order Confirmed</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light">Re-Use</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>TTEE-25-00134</td>
                                <td>21/04/2025</td>
                                <td>
                                    <div class="d-flex">
                                        <span class="rfq-product-name text-truncate">
                                        KILN AIR INJECTOR TUBEKILN AIR
                                        </span>
                                    </div>
                                </td>
                                <td>21,22</td>
                                <td>Branch 6</td>
                                <td>User 6</td>
                                <td>
                                    <span class="rfq-status Partial-order">Partial Order</span>
                                </td>
                                <td>
                                    <div class="rfq-table-btn-group">
                                        <button class="ra-btn small-btn ra-btn-primary">CIS</button>
                                        <button class="ra-btn small-btn ra-btn-outline-primary-light disabled">Edit</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <!-- jQuery UI -->
    <script src="{{ asset('public/assets/library/datetimepicker/jquery.datetimepicker.full.min.js') }}"></script>
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
        

        let dateToday = new Date();

        $('#from-date').datetimepicker({
            lang: 'en',
            timepicker: false,
            maxDate: dateToday,
            format: 'd/m/Y',
        }).disableKeyboard();

        let last_date_to_response = new Date();
        last_date_to_response.setDate(last_date_to_response.getDate() + 1);
        $('#to-date').datetimepicker({
            lang: 'en',
            timepicker: false,
            maxDate: dateToday,
            format: 'd/m/Y',
        }).disableKeyboard();

        $(document).on('blur', '#from-date', function () {
            let date = $("#from-date").val()
            const myArray = date.split("/");
            let finel_date = myArray[2]+"/"+myArray[1]+"/"+myArray[0]+" GMT"
            let to_date = new Date(finel_date)
            
            to_date.setDate(to_date.getDate());
            $("#to-date").datetimepicker({
                format: 'd/m/Y',
                timepicker:false,
                minDate : to_date,
                scrollMonth : false,
                scrollInput : false
            });
        }).on('change', '#from-date', function () {
            let from_date = $("#from-date").val();
            const from_array_date = from_date.split("/");
            let from_final_date = parseInt(from_array_date[2]+from_array_date[1]+from_array_date[0])
            let to_date = $("#to-date").val();
            const to_array_date = to_date.split("/");
            let to_finel_date = parseInt(to_array_date[2]+to_array_date[1]+to_array_date[0])
            if(to_finel_date<from_final_date){
                $("#to-date").val("");
            }
        }).on("click", "#reset-filter", function(){
            $(".rfq-filter-button").find("input").val("");
            $(".rfq-filter-button").find("select").val("0");
        });

    });
</script>
@endsection