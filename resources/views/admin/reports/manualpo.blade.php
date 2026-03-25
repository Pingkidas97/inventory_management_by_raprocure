<div class="card rounded">
    <div class="card-header bg-white">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h1 class="font-size-22 mb-0">Manual PO Report</h1>
        </div>
    </div>

    <div class="card-body">
        <div class="row g-3 pt-3 mb-3">                
            <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                <div class="input-group">
                    <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                    <div class="form-floating">
                        <input type="text" class="form-control" name="from_date" id="from_date" placeholder="From Date" readonly>
                        <label for="from_date">From Date</label>
                    </div>
                </div>
            </div>

            <div class="col-6 col-sm-4 col-md-4 col-lg-2 mb-3">
                <div class="input-group">
                    <span class="input-group-text"><span class="bi bi-calendar-date"></span></span>
                    <div class="form-floating">
                        <input type="text" class="form-control" name="to_date" id="to_date" placeholder="To Date" readonly>
                        <label for="to_date">To Date</label>
                    </div>
                </div>
            </div>

            <div class="col-6 col-sm-auto">
                <button type="button"
                    class="ra-btn ra-btn-outline-danger w-100 justify-content-center font-size-11"
                    onclick="window.location.href='{{ route('admin.reports.manualPO') }}'">
                    <span class="bi bi-arrow-clockwise" aria-hidden="true"></span>
                    Reset
                </button>
            </div>
            
            <div class="col-6 col-sm-auto">
                <button type="button"
                    class="ra-btn ra-btn-outline-primary w-100 justify-content-center font-size-11"
                    id="export">
                    <span class="bi bi-download" aria-hidden="true"></span>
                    Export
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- jQuery & DateTimePicker -->
<script src="{{ asset('public/assets/jQuery/jquery-3.6.0.min.js') }}"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-datetimepicker@2.5.21/jquery.datetimepicker.min.css">
<script src="https://cdn.jsdelivr.net/npm/jquery-datetimepicker@2.5.21/build/jquery.datetimepicker.full.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />

<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    $(document).ready(function () {
        // Set CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Clear inputs on load
        $('#from_date, #to_date').val('');
        $('#to_date').prop('disabled', true);

        const today = new Date();

        // Initialize From Date Picker
        $('#from_date').datetimepicker({
            format: 'd/m/Y',
            timepicker: false,
            maxDate: today,
            onSelectDate: function (ct) {
                const fromDate = new Date(ct);
                const maxDate = new Date(fromDate);
                maxDate.setMonth(maxDate.getMonth() + 4);

                const finalMaxDate = (maxDate > today) ? today : maxDate;

                $('#to_date').prop('disabled', false).val('');
                $('#to_date').datetimepicker({
                    format: 'd/m/Y',
                    timepicker: false,
                    minDate: fromDate,
                    maxDate: finalMaxDate
                });
            }
        });

        // Prevent manual typing
        $('#from_date, #to_date').on('keydown paste', function (e) {
            e.preventDefault();
        });

        // Export Button
        $('#export').on('click', function (e) {
            e.preventDefault();
            const fromDate = $('#from_date').val();
            const toDate = $('#to_date').val();

            if (fromDate && toDate) {
                let btn = $(this);
                let url = "{{ route('admin.reports.manualpoReport.export') }}";
                let data = {
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val()
                };
                
                inventoryFileExport(btn, url, data );
            } else {
                toastr.error("Please select both From Date and To Date before searching.");
            }
        });           
        
    });

    // Export Logic
    function inventoryFileExport(btn, url, data) {
        btn.prop('disabled', true).html('<i class="bi bi-arrow-clockwise"></i> Exporting...');

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            success: function (response) {
                if (response.fetchRow === false) {
                    toastr.error(response.message || 'No record found, Try another search!');
                    return;
                }

                if (response.success && response.download_url) {
                    const link = document.createElement('a');
                    link.href = response.download_url;
                    link.setAttribute('download', '');
                    document.body.appendChild(link);
                    link.click();
                    link.remove();

                    toastr.success('Download started!');
                } else {
                    toastr.error('Failed to prepare download.');
                }
            },
            error: function () {
                toastr.error('Error occurred while exporting.');
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="bi bi-download"></i> Export');
            }
        });
    }
</script>
