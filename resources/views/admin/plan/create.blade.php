@extends('admin.layouts.app_second', ['title' => 'Manage Plan', 'sub_title' => 'Create'])
@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-pin"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.plan.index') }}"> -> Plan Module </a>
            <span> -> Add Plan Module </span>
        </h5>
    </div>
</div>
@endsection
@section('content')
<div class="page-start-section">
   <div class="container-fluid">
      <div class="row">
         <div class="col-sm-12">
            <div class="card">
               <div class="card-body">
                  <div class="col-md-12">
                     <h3 class="">Add Plan Module</h3>
                     <hr>
                  </div>
                  <form id="createPlanForm" class="form-horizontal form-material" action="{{route('admin.plan.store')}}" method="post">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="plan_name" class="form-label"><strong>Plan Name</strong> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="Enter Plan Name" oninput="limitCharacters(this, 255)">
                            <span class="text-danger error-text plan_name_error"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="type" class="form-label"><strong>Customer Type</strong> <span class="text-danger">*</span></label>
                            <select class="form-control" id="type" name="type">
                                <option value="">Select Type</option>
                                <option value="1">Buyer</option>
                                <option value="2">Vendor</option>
                            </select>
                            <span class="text-danger error-text type_error"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="no_of_user" class="form-label"><strong>No. of Logins</strong> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="no_of_user" name="no_of_user" placeholder="Enter Number of Logins" oninput="restrictToNumber(this, 5)">
                            <span class="text-danger error-text no_of_user_error"></span>
                        </div>
                        <div class="col-md-6">
                            <label for="price" class="form-label"><strong>Amount</strong> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="price" name="price" placeholder="Enter Amount" oninput="restrictToDecimal(this, 20)">
                            <span class="text-danger error-text price_error"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label d-block"><strong>Status</strong> <span class="text-danger">*</span></label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="active" value="1" checked>
                                <label class="form-check-label" for="active">Active</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="inactive" value="2">
                                <label class="form-check-label" for="inactive">Inactive</label>
                            </div>
                            <span class="text-danger error-text status_error"></span>
                        </div>
                    </div>
                    <div class="d-flex gap-1 justify-content-center">
                        <button type="submit" class="btn-rfq btn-rfq-primary">Save</button>
                        <a href="{{ route('admin.plan.index') }}" class="btn-rfq btn-rfq-danger">Cancel</a>
                    </div>
                </form>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@endsection
@section('scripts')
<script>
   $(document).ready(function () {
       // Clear error messages on input change
       $('#createPlanForm input, #createPlanForm select').on('keyup change', function () {
           const fieldName = $(this).attr('name');
           $(`span.error-text.${fieldName}_error`).text('');
       });
   
       $('#createPlanForm').submit(function (e) {
           e.preventDefault();
           $('span.error-text').text('');
   
           const plan_name = $('#plan_name').val().trim();
           const type = $('#type').val().trim();
           const no_of_user = $('#no_of_user').val().trim();
           const price = $('#price').val().trim();
           const status = $('input[name="status"]:checked').val();
   
           let hasErrors = false;
   
           if (!plan_name) {
               $('span.error-text.plan_name_error').text('Please enter plan name.');
               hasErrors = true;
           }
   
           if (!type) {
               $('span.error-text.type_error').text('Please select type.');
               hasErrors = true;
           }
   
           if (!no_of_user) {
               $('span.error-text.no_of_user_error').text('Please enter number of logins.');
               hasErrors = true;
           }
   
           if (!price) {
               $('span.error-text.price_error').text('Please enter price.');
               hasErrors = true;
           }
   
           if (!status) {
               $('span.error-text.status_error').text('Please select status.');
               hasErrors = true;
           }
   
           if (hasErrors) return false;
   
           // Submit via AJAX
           $.ajax({
               url: $(this).attr('action'),
               type: "POST",
               data: new FormData(this),
               dataType: "json",
               contentType: false,
               processData: false,
               beforeSend: function () {
                   $('#create-form').find('button[type="submit"]').prop('disabled', true);
               },
               success: function (response) {
                   if (response.success) {
                       toastr.success(response.message);
                        setTimeout(() => {
                            window.location.href = "{{ route('admin.plan.index') }}";
                        }, 300); 
                   } else {
                       toastr.error(response.message || "Something went wrong!");
                   }
               },
               error: function (xhr) {
                   if (xhr.status === 422) {
                       $.each(xhr.responseJSON.errors, function (key, value) {
                           const errorField = key.replace('.', '_');
                           $(`span.error-text.${errorField}_error`).text(value[0]);
                       });
                   } else {
                       toastr.error('An error occurred. Please try again.');
                   }
               },
               complete: function () {
                   $('#create-form').find('button[type="submit"]').prop('disabled', false);
               }
           });
       });
   });
</script>
@endsection