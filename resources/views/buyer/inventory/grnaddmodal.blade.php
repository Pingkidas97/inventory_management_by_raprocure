<!---add grn Modal-->
<div class="modal fade" id="grnaddModal" tabindex="-1" aria-labelledby="grnaddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-grn">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-13" id="grnaddModalLabel"><i class="bi bi-pencil"></i> GRN Details</h2>
                <button type="button" class="btn-close font-size-11" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="overflow-auto" style="max-height: 70vh;">
                    <form id="grnaddForm">
                        @csrf
                        <input type="hidden" name="inventory_id" id="inventory_id" value="">

                        <div class="table-responsive">
                            <table class="product-listing-table w-100" id="orderGrnTable">
                                <thead>
                                    <tr>
                                        <th class="text-center text-nowrap">Order No. <span class="text-danger">*</span></th>
                                        <th class="text-center text-nowrap">RFQ No. <span class="text-danger">*</span></th>
                                        <th class="text-center text-nowrap">Order Date</th>
                                        <th class="text-center text-nowrap">Order Qty <span class="text-danger">*</span></th>
                                        <th class="text-center text-nowrap">Vendor Name <span class="text-danger">*</span></th>
                                        <th class="text-center text-nowrap">GRN Entered</th>
                                        <th class="text-center text-nowrap">Rate <span class="text-danger">*</span></th>
                                        <th class="text-center text-nowrap">
                                            Rate in Local <br> Currency ({{ session('user_currency.symbol', '₹')}}) <span class="text-danger">*</span>
                                        </th>
                                        <th class="text-center text-nowrap">GRN Qty <span class="text-danger">*</span></th>
                                        <th class="text-center text-nowrap">Invoice <br> Number</th>
                                        <th class="text-center text-nowrap">Bill Date</th>
                                        <th class="text-center text-nowrap">Grn Date</th>
                                        <th class="text-center text-nowrap">Transporter <br> Name</th>
                                        <th class="text-center text-nowrap">Vehicle No /<br> LR No with Date</th>
                                        <th class="text-center text-nowrap">Gross Wt (kgs)</th>
                                        <th class="text-center text-nowrap">GST ({{ session('user_currency.symbol', '₹')}})</th>
                                        <th class="text-center text-nowrap">Freight /  Other <br>Charges ({{ session('user_currency.symbol', '₹')}})</th>
                                        <th class="text-center text-nowrap">Approved By</th>
                                        <th class="text-center text-nowrap">View</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dynamic rows here -->
                                </tbody>
                            </table>
                        </div>
                        <div class="table-responsive mt-4">                            
                            <table class="product-listing-table w-100" id="stockReturnGrnTable">
                                <thead>
                                    <tr>
                                        <th class="text-center text-nowrap">Stock <br> Return No.</th>
                                        <th class="text-center text-nowrap">Stock <br> Return Date</th>
                                        <th class="text-center text-nowrap">Remarks</th>
                                        <th class="text-center text-nowrap">Stock <br> Return Qty</th>
                                        <th class="text-center text-nowrap">Vendor Name</th>
                                        <th class="text-center text-nowrap">GRN Entered</th>
                                        <th class="text-center text-nowrap">GRN Qty <span class="text-danger">*</span></th>
                                        <th class="text-center text-nowrap">Invoice <br> Number</th>
                                        <th class="text-center text-nowrap">Bill Date</th>
                                        <th class="text-center text-nowrap">Grn Date</th>
                                        <th class="text-center text-nowrap">Transporter <br> Name</th>
                                        <th class="text-center text-nowrap">Vehicle No / <br> LR No with Date</th>
                                        <th class="text-center text-nowrap">Gross Wt (kgs)</th>
                                        <th class="text-center text-nowrap">GST ({{ session('user_currency.symbol', '₹')}})</th>
                                        <th class="text-center text-nowrap">Freight / <br> Other Charges ({{ session('user_currency.symbol', '₹')}})</th>
                                        <th class="text-center text-nowrap">Approved By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dynamic rows here -->
                                </tbody>
                            </table>
                        </div>
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
</div>



@push('exJs')
    <script>
        $(document).ready(function () {

            
            ///start pingki
            const orderGrnTable = document.getElementById('orderGrnTable');
            if (orderGrnTable) {
                orderGrnTable.style.display = 'none';
            }
            const stockReturnGrnTable = document.getElementById('stockReturnGrnTable');
            if (stockReturnGrnTable) {
                stockReturnGrnTable.style.display = 'none';
            }
            //start pingki
            // $('#grnaddModal').on('input', '.grn_quantity_input', function () {
            //     var $input = $(this);
            //     var val = $input.val();

            //     // Remove invalid characters (+ - * /)
            //     val = val.replace(/[+\-*/]/g, '');
            //     $input.val(val);

            //     // Allow only numbers with up to 3 decimal places
            //     // if (!/^\d*\.?\d{0,2}$/.test(val)) {
            //     if (!/^\d*\.?\d{0,3}$/.test(val)) {
            //         val = val.slice(0, -1);
            //         $input.val(val);
            //         return;
            //     }

            //     var maxQty = parseFloat($input.data('max'));
            //     var enteredQty = parseFloat(val);

            //    if (!isNaN(maxQty) && !isNaN(enteredQty) && parseFloat(enteredQty.toFixed(3)) > parseFloat(maxQty.toFixed(3))) {
            //         $input.val('');
            //         toastr.error(`GRN Quantity cannot exceed available quantity (${maxQty}).`);
            //     }
                
            //     var $row = $input.closest('tr');
            //     calculateRowGST($row);
            // });
            $('#grnaddModal').on('input paste blur', '.grn_quantity_input', function (e) {
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
            $('#grnaddModal').on('input', '.rate_in_local_currency', function () {
                var $row = $(this).closest('tr');
                calculateRowGST($row);
            });

            $('#grnaddModal').on('shown.bs.modal', function () {

                $('#grnaddModal .grn_quantity_input').each(function () {

                    let $input = $(this);
                    let qty = parseFloat($input.val()) || 0;

                    if (qty > 0) {
                        let $row = $input.closest('tr');
                        calculateRowGST($row);
                    } else {
                        let $row = $input.closest('tr');
                        $row.find('.gst').val('');
                    }

                });

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
        $('#grnaddForm').off('submit').on('submit',function (e) {
            e.preventDefault();
            if (isSaveGrnSubmitting) {
                return; // Prevent multiple submissions
            }
            isSaveGrnSubmitting = true; 
            $('.save_grn_button').attr('disabled', true);
            $.ajax({
                type: "POST",
                url: "{{ route('buyer.grn.store') }}",
                data: $('#grnaddForm').serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function () {
                    $('.save_grn_button').attr('disabled', true);
                },
                success: function (response) {
                    if (response.status) {
                        toastr.success(response.message);
                        $("#grnaddModal").modal('hide');
                        if (inventoryTable) {
                            inventoryTable.ajax.reload();
                        }
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
