@extends('admin.layouts.app_second',['title' => 'Vendor','sub_title' => 'Plan']) 
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span> -> Vendor Plan </span>
        </h5>
    </div>
</div>
@endsection 
@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card my_profile form-head border-0" style="margin-top: 20px;">
                <div class="card-header d-flex justify-content-between align-items-center bg-transparent">
                    <h4 class="card-title mb-0">Vendor Plan Details</h4>
                </div>

                <div class="card-body">
                    <form id="create-form" action="{{route('admin.vendor.plan.update', $vendor->id)}}" method="post">
                        @csrf @method('PUT')
                        <div class="row">
                            @foreach($plans as $plan)
                            <div class="col-md-3 p-2 plan-card">
                                <div class="card shadow">
                                    <div
                                        class="card-body text-center mng-plan {{!empty($user_plans)&&$user_plans->plan_id==$plan->id?'border border-primary':''}} " data-no-of-users="{{$plan->no_of_user}}"
                                        onclick="selectPlan(this,'{{$plan->id}}','{{$plan->plan_name}}','{{$plan->price}}','{{$plan->no_of_user}}');"
                                    >
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h5 class="text-black mb-2 font-w600">{{$plan->plan_name}}</h5>
                                                <h5 class="text-black mb-2 font-w600"><i class="fa fa-inr"></i> {{$plan->price}}</h5>
                                                <p class="mb-0 fs-14">For <span class="text-success me-1">{{$plan->no_of_user}}</span> Users/Year</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="row m-3">
                            <input type="hidden" name="plan_id" id="plan_id" value="" />
                            <div class="col-md-6 p-2">
                                <div class="form-group">
                                    <label for="no_of_user" class="form-label">Plan Duration<span class="text-danger">*</span></label>
                                    <select class="form-select" id="plan_duration" name="plan_duration">
                                        <option value="12">12 Months</option>
                                        <option value="6">6 Months</option>
                                        <option value="3">3 Months</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 p-2">
                                <div class="form-group">
                                    <label for="no_of_user" class="form-label">Number of Logins<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" disabled id="no_of_user" name="no_of_user" oninput="this.value=this.value.replace(/[^0-9]/g, '').slice(0, 5)" placeholder="Number of Logins" value="" />
                                    <span class="text-danger error-text no_of_user_error"></span>
                                </div>
                            </div>
                            <div class="col-md-6 p-2">
                                <div class="form-group">
                                    <label for="price" class="form-label">Amount<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" disabled id="price" name="price" oninput="this.value=this.value.replace(/[^0-9]/g, '').slice(0, 20)" onkeyup="calculateTotal();" placeholder="Amount" value="" />
                                    <span class="text-danger error-text price_error"></span>
                                </div>
                            </div>
                            <div class="col-md-6 p-2">
                                <div class="form-group">
                                    <label for="discount" class="form-label">Discount in %<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="discount" name="discount" oninput="this.value=this.value.replace(/[^0-9.]/g, '').replace(/^(\d*\.\d*).*$/, '$1').slice(0, 5)" onkeyup="calculateTotal();" placeholder="Discount in %" value="" />
                                    <span class="text-danger error-text discount_error"></span>
                                </div>
                            </div>
                            <div class="col-md-6 p-2">
                                <div class="form-group">
                                    <label for="gst" class="form-label">GST in %<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" disabled id="gst" name="gst" oninput="this.value=this.value.replace(/[^0-9]/g, '').slice(0, 2)" onkeyup="calculateTotal();" placeholder="GST in %" value="18" />
                                    <span class="text-danger error-text gst_error"></span>
                                </div>
                            </div>
                            <div class="col-md-6 p-2">
                                <div class="form-group">
                                    <label for="total" class="form-label">Final Amount<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" disabled id="total" name="total" oninput="this.value=this.value.replace(/[^0-9]/g, '').slice(0, 20)" placeholder="Final Amount" value="" />
                                    <span class="text-danger error-text total_error"></span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.vendor.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Activate</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 
@section('scripts')
<script>
    $(document).ready(function(){
        disableLowerPlan();
    });
    function disableLowerPlan(){
        let current_plan_users = $(".border.border-primary").data("no-of-users");
        $(".plan-card").each(function(){            
            if($(this).find(".mng-plan").data("no-of-users")<current_plan_users){
                $(this).find(".mng-plan").addClass("disabled");
            }
        });
        $(".border.border-primary").trigger("click");
    }
    function selectPlan(e, plan_id, plan_name, price, no_of_user) {
        $(".mng-plan").removeClass("border border-primary");
        $(e).addClass("border border-primary");

        $("#no_of_user").val(no_of_user);
        $("#price").val(price);
        $("#plan_name").val(plan_name);
        $("#plan_id").val(plan_id);
        calculateTotal();
    }
    $(document).on("change", '#plan_duration', function() {
        calculateTotal();
    });
    function calculateTotal() {
        let card_amount = $("#price").val();
        let price = 0;
        let discount = $("#discount").val();
        let gst = $("#gst").val();
        let plan_duration = parseInt($("#plan_duration").val());

        switch (plan_duration) {
            case 6:
                price = parseFloat(card_amount/2).toFixed(2);
                break;
            case 3:
                price = parseFloat(card_amount/4).toFixed(2);
                break;
            default:
                price = parseFloat(card_amount);
                break;
        }
        
        let discounted_amount = price;
        
        if(discount>0 && discount<100){
            discounted_amount = price-(price*discount/100);
        }else if(discount>=100){
            $("#discount").val('');
            alert("Discount can not be greater than 99%");
        }
        let total = parseFloat(discounted_amount) + parseFloat((discounted_amount * gst) / 100);

        $("#total").val(parseFloat(total).toFixed(2));
    }
    $("#create-form").submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr("action"),
            type: "POST",
            data: new FormData(this),
            dataType: "json",
            contentType: false,
            processData: false,
            sendBeforeSend: function () {
                $("#create-form").find('button[type="submit"]').prop("disabled", true);
            },
            success: function (response) {
                if (response.status) {
                    toastr.success(response.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function (key, value) {
                        const errorField = key.replace(".", "_");
                        $(`span.error-text.${errorField}_error`).text(value[0]);
                    });
                } else {
                    toastr.error("An error occurred. Please try again.");
                }
            },
            complete: function () {
                $("#create-form").find('button[type="submit"]').prop("disabled", false);
            },
        });
    });
</script>
@endsection
