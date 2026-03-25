<!-- Pending GRN Add Modal -->
<div class="modal fade" id="pendingGrngrnaddModal" tabindex="-1" aria-labelledby="pendingGrngrnaddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"> <!-- Wide modal -->
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h5 class="modal-title fw-bold" id="pendingGrngrnaddModalLabel">
                    <i class="bi bi-pencil"></i> Add GRN Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3">
                <form id="pendingGrngrnaddForm">
                    @csrf
                    <input type="hidden" name="inventory_id" id="inventory_id">
                    <input type="hidden" name="order_id" id="order_id">
                    <input type="hidden" name="order_type" id="order_type">

                    <!-- Common Fields -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label for="invoice_number" class="form-label fw-bold text-nowrap">Invoice Number</label>
                            <input type="text" class="form-control text-center bg-white" id="invoice_number" name="invoice_number[]" maxlength="50" autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label for="bill_date" class="form-label fw-bold text-nowrap">Bill Date</label>
                            <input type="text" class="form-control dateTimePickerStart text-center bg-white bill-date" id="bill_date" name="bill_date[]" maxlength="50" autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label for="transporter_name" class="form-label fw-bold text-nowrap">Transporter Name</label>
                            <input type="text" class="form-control text-center bg-white" id="transporter_name" name="transporter_name[]" maxlength="255" autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label for="vehicle_lr_number" class="form-label fw-bold text-nowrap">Vehicle No / LR No</label>
                            <input type="text" class="form-control text-center bg-white vehicle_lr_number" id="vehicle_lr_number" name="vehicle_lr_number[]" maxlength="20" autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label for="gross_weight" class="form-label fw-bold text-nowrap">Gross Wt (kgs)</label>
                            <input type="text" class="form-control text-center bg-white smt_numeric_only" id="gross_weight" name="gross_weight[]" maxlength="20" autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label for="freight_charges" class="form-label fw-bold text-nowrap">Freight / Other Charges ({{ session('user_currency.symbol', '₹') }})</label>
                            <input type="text" class="form-control text-center bg-white smt_numeric_only" id="freight_charges" name="freight_charges[]" maxlength="20" autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label for="approved_by" class="form-label fw-bold text-nowrap">Approved By</label>
                            <input type="text" class="form-control text-center bg-white" id="approved_by" name="approved_by[]" maxlength="255" autocomplete="off">
                        </div>
                    </div>

                    <!-- Product Table -->
                    <div class="table-responsive" style="max-height: 50vh; overflow-y:auto;">
                        <table class="product-listing-table w-100" id="orderGrnTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Product <br> Name</th>
                                    <th class="text-center">Specification</th>
                                    <th class="text-center">Size</th>
                                    <th class="text-center">Order <br> No.</th>
                                    <th class="text-center">RFQ <br> No.</th>
                                    <th class="text-center">Order <br> Date</th>
                                    <th class="text-center">Order <br> Qty</th>
                                    <th class="text-center">Vendor <br> Name</th>
                                    <th class="text-center">GRN <br> Entered</th>
                                    <th class="text-center">Rate</th>
                                    <th class="text-center">Rate in <br> Local <br> Currency ({{ session('user_currency.symbol', '₹') }})</th>
                                    <th class="text-center">GRN Qty</th>
                                    <th class="text-center">GRN Date</th>
                                    <th class="text-center">GST ({{ session('user_currency.symbol', '₹') }})</th>
                                    <th class="text-center">View</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dynamic product rows will come here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-flex justify-content-center mt-3">
                        <button type="submit" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 save_grn_button">
                                <i class="bi bi-save font-size-11" aria-hidden="true"></i> Save GRN
                            </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('exJs')
    <script>
        $(document).ready(function () {

            // $('#pendingGrngrnaddModal').on('input', '.grn_quantity_input', function () {
            //     var $input = $(this);
            //     var val = $input.val();

            //     // Remove invalid characters (+ - * /)
            //     val = val.replace(/[+\-*/]/g, '');
            //     $input.val(val);

            //     // Allow only numbers with up to 3 decimal places
            //     if (!/^\d*\.?\d{0,3}$/.test(val)) {
            //         val = val.slice(0, -1);
            //         $input.val(val);
            //         return;
            //     }

            //     var maxQty = parseFloat($input.data('max'));
            //     var enteredQty = parseFloat(val);

            //     if (!isNaN(maxQty) && !isNaN(enteredQty) && parseFloat(enteredQty.toFixed(3)) > parseFloat(maxQty.toFixed(3))) {
            //         $input.val('');
            //         toastr.error(`GRN Quantity cannot exceed available quantity (${maxQty}).`);
            //     }

            //     var $row = $input.closest('tr');
            //     calculateRowGST($row);
            // });
            $('#pendingGrngrnaddModal').on('input paste blur', '.grn_quantity_input', function (e) {
                let $input = $(this);
                let val = $input.val();

                // Remove invalid characters
                val = val.replace(/[^0-9.]/g, '');

                // Allow only one decimal point
                val = val.replace(/(\..*)\./g, '$1');

                // Limit to 3 decimal places
                if (val.includes('.')) {
                    let parts = val.split('.');
                    val = parts[0] + '.' + parts[1].slice(0, 3);
                }

                let maxQty = parseFloat($input.data('max'));
                let enteredQty = parseFloat(val);

                if (!isNaN(maxQty) && !isNaN(enteredQty) && enteredQty > maxQty) {
                    $input.val('');
                    toastr.error(`GRN Quantity cannot exceed available quantity (${maxQty}).`);
                } else {
                    $input.val(val);
                }

                let $row = $input.closest('tr');
                calculateRowGST($row);
            });

            function calculateRowGST($row) {
                var enteredQty = parseFloat($row.find('.grn_quantity_input').val()) || 0;

                var rate = parseFloat($row.find('.rate').val()) || 0;
                var buyer_rate = parseFloat($row.find('.buyer_rate').val()) || 0;
                var rate_in_local_currency = parseFloat($row.find('.rate_in_local_currency').val()) || 0;
                var gstPercentage = parseFloat($row.find('.gst_percentage').val()) || 0;

                if (enteredQty === '') {
                    $row.find('.gst').val('');
                    return;
                }

                rate = rate_in_local_currency !== 0
                    ? rate_in_local_currency
                    : buyer_rate !== 0
                        ? buyer_rate
                        : rate;

                var gstAmount = (enteredQty * rate * gstPercentage) / 100;

                $row.find('.gst').val(gstAmount.toFixed(2));
            }


        });

        let isSaveGrnSubmitting = false;
        $('#pendingGrngrnaddForm').off('submit').on('submit',function (e) {
            e.preventDefault();
            if (isSaveGrnSubmitting) {
                return; 
            }
            isSaveGrnSubmitting = true; 
            $('.save_grn_button').attr('disabled', true);
            $.ajax({
                type: "POST",
                url: "{{ route('buyer.report.storeFromPendingGRN') }}",
                data: $('#pendingGrngrnaddForm').serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function () {
                    $('.save_grn_button').attr('disabled', true);
                },
                success: function (response) {
                    if (response.status) {
                        toastr.success(response.message);
                        $("#pendingGrngrnaddModal").modal('hide');
                        $('#pendingGrngrnaddForm').find('input').val('');
                        $('#report-table').DataTable().ajax.reload();
                    } else {
                        toastr.error(response.message || 'Something went wrong. Please try again.');
                    }
                    isSaveGrnSubmitting = false;
                    $('.save_grn_button').removeAttr('disabled');
                    
                   

                },
                error: function (xhr) {
                    isSaveGrnSubmitting = false;
                    $('.save_grn_button').removeAttr('disabled');

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach(function (key) {
                            toastr.error(errors[key][0]);
                        });
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Something went wrong. Please try again.');
                    }
                }
            });
        });

    </script>
@endpush
