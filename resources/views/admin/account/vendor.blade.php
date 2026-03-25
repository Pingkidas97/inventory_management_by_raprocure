@extends('admin.layouts.app_second',['title' => 'Vendor Accounts','sub_title' => 'List'])

@section('breadcrumb')
<div class="breadcrumb-header">
    <div class="container-fluid">
        <h5 class="breadcrumb-line">
            <i class="bi bi-shop"></i>
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.accounts.vendor') }}"> -> Vendors Accounts List </a>
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
                    <h3 class="">Vendors Accounts List</h3>
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
                            <form id="searchForm" action="{{ route('admin.accounts.vendor') }}" method="GET">
                                <ul class="rfq-filter-button">
                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <div class="form-floating">
                                                <input 
                                                    type="text" 
                                                    id="vendor_name" 
                                                    name="vendor_name" 
                                                    class="form-control fillter-form-control" 
                                                    value="{{ request('vendor_name') }}" 
                                                    placeholder="Search by name/email/contact" 
                                                >
                                                <label for="vendor_name">Search By Vendor Name/Email/Contact</label>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                            <div class="form-floating">
                                                <input 
                                                    type="date" 
                                                    id="from_date" 
                                                    name="from_date" 
                                                    class="form-control fillter-form-control" 
                                                    value="{{ request('from_date') }}" 
                                                >
                                                <label for="from_date">From Date</label>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-calendar"></i></span>
                                            <div class="form-floating">
                                                <input 
                                                    type="date" 
                                                    id="to_date" 
                                                    name="to_date" 
                                                    class="form-control fillter-form-control" 
                                                    value="{{ request('to_date') }}" 
                                                >
                                                <label for="to_date">To Date</label>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-record2"></i></span>
                                            <div class="form-floating">
                                                <select 
                                                    name="status" 
                                                    id="status" 
                                                    class="form-select fillter-form-select"
                                                >
                                                    <option value="">Select</option>
                                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Expired</option>
                                                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Current</option>
                                                </select>
                                                <label for="status">Account Status</label>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="notShow_on_mobile">
                                        <button type="submit" class="btn-style btn-style-primary">
                                            <i class="bi bi-search"></i> Search
                                        </button>
                                    </li>

                                    <li class="notShow_on_mobile">
                                        <a href="{{ route('admin.accounts.vendor') }}" class="btn-style btn-style-danger">RESET</a>
                                    </li>

                                    <li class="notShow_on_mobile">
                                        <button class="btn-style btn-style-white" id="export-btn">
                                            <i class="bi bi-download"></i> EXPORT
                                        </button>
                                    </li>
                                

                                    <li class="notShow_on_mobile">
                                        <div class="input-group">
                                            <select class="form-select extend-plan-month" id="planDuration">
                                                <option value="">Select Months</option>
                                                <option value="1">1 Month</option>
                                                <option value="2">2 Months</option>
                                                <option value="3">3 Months</option>
                                                <option value="6">6 Months</option>
                                                <option value="12">12 Months</option>
                                            </select>
                                            <button class="btn-style btn-style-primary" type="button" id="extend_free_plan" onclick="extendFreePlanBulk();">
                                                Extend Plan
                                            </button>
                                        </div>
                                    </li>

                                </ul>

                            </form>
                        </div>
                    </div>

                    <div class="table-responsive" id="table-container">
                        @include('admin.account.partials.vendor-table', ['results' => $results])
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

function assignedManager(user_id,manager_id) {
    $.ajax({
        url: "{{ route('admin.accounts.vendor.manager') }}",
        type: "post",
        data: { _token: "{{ csrf_token() }}", user_id,manager_id},
        success: function(res) {
            toastr.success(res.message);
            location.reload();
        },
        error: function() {
            toastr.error('Something went wrong.');  
        },
    });
}


function extendFreePlan(id,user_plan,user_id,buyer_name) {
    var extend_month = $('#extend_plan_month_'+id).val();
    if (confirm('Are you sure, you want to update free plan for '+buyer_name+'?')) {
        $.ajax({
            url: "{{ route('admin.accounts.vendor.plan.extend') }}",
            type: "post",
            data: { _token: "{{ csrf_token() }}", user_id, user_plan, extend_month },
            success: function(res) {
                if(res.status)
                {
                    toastr.success(res.message);
                    location.reload();
                }else{
                    toastr.error(res.message);
                }
            },
            error: function() {
                toastr.error('Something went wrong.');  
            },
        });
    }
} 
 
function extendFreePlanBulk() {
    let button = document.getElementById("extend_free_plan");   
    // Get input values
    let fromDateInput = document.getElementById("from_date").value;
    let toDateInput = document.getElementById("to_date").value;
    let selectedYears = document.getElementById("planDuration").value;
    // Validate inputs
    if (!selectedYears) {
        alert("Please Fill Years.");
        return;
    }
    if (!fromDateInput || !toDateInput) {
        alert("Please Fill From Date and To Date. You can select a maximum range of 4 months.");
        return;
    }

    // Correctly parse DD/MM/YYYY format
    let fromDateParts = fromDateInput.split("/");
    let toDateParts = toDateInput.split("/");

    let fromDate = new Date(fromDateParts[2], fromDateParts[1] - 1, fromDateParts[0]); // Correct format (YYYY, MM, DD)
    let toDate = new Date(toDateParts[2], toDateParts[1] - 1, toDateParts[0]); // Correct format (YYYY, MM, DD)
    let today = new Date();
    today.setHours(0, 0, 0, 0); // Reset today's time to avoid errors

    // Validate date range (max 4 months selection)
    let maxDate = new Date(fromDate);
    maxDate.setMonth(maxDate.getMonth() + 4);

    if (toDate > maxDate) {
        alert("You can select a maximum range of 4 months.");
        return;
    }

    // Validate if "to date" is after "from date"
    if (toDate <= fromDate) {
        alert("The 'To Date' must be after the 'From Date'.");
        return;
    }

    if (confirm('Are you sure, you want to update free plan?')) {
    
        // Disable button and show spinner
        button.disabled = true;
        button.innerHTML = `<i class="fa fa-spinner fa-spin"></i> Processing...`;

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "{{ route('admin.accounts.vendor.plan.extend.bulk') }}",
            data: {
                fromDateInput: fromDateInput,
                toDateInput: toDateInput,
                selectedYears: selectedYears
            },
            cache: false,
            success: function(data) {
                // Re-enable button after request completes
                button.disabled = false;
                button.innerHTML = "Extend Free Plan";
                if (data.status == 1) {
                    toastr.success(data.message);
                    setTimeout(function () {
                        window.location.reload()
                    }, 300);
                } else {
                    toastr.error(data.message);
                }
            },
                error: function(xhr, status, error) {
                // Handle the error
                toastr.error("An error occurred. Please try again.");
            },
            complete: function() {
                // Re-enable button after request completes, regardless of success or error
                button.disabled = false;
                button.innerHTML = "Extend Free Plan";
            }

        });
    }
}
</script>
<script src="{{ asset('public/assets/xlsx/xlsx.full.min.js') }}"></script>
<script src="{{ asset('public/assets/xlsx/export.js') }}"></script>
<script>
    $(document).ready(function () {
        const exporter = new Exporter({
            chunkSize: 20,
            rowLimitPerSheet: 200000,
            headers: ["Vendor’s Name","Plan Name", "Invoice No", "No. Of Users", "Vendor Code","Vendor’s Contact","Vendor’s Email","Date Of Subscription","Period","Next Renewal Date","Amount","Assign Manager"],
            totalUrl: "{{ route('admin.accounts.exportVendorTotal') }}",
            batchUrl: "{{ route('admin.accounts.exportVendorBatch') }}",
            token: "{{ csrf_token() }}",
            exportName: "Vendor-Account",
            expButton: '#export-btn',
            exportProgress: '#export-progress',
            progressText: '#progress-text',
            progress: '#progress-bar',
            fillterReadOnly: '.fillter-form-control',
            getParams: function () {
                return {
                    vendor_name: $('#vendor_name').val(),
                    status: $('#status').val(),
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val()
                };
            }
        });

        $('#export-btn').on('click', function () {
            exporter.start();
        });

        $('#export-progress').hide();
    });
</script>
@endsection
