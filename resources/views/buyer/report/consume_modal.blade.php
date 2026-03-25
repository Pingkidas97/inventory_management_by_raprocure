<!---Consume Modal-->
<div id="IssueConsumeModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h5 class="modal-title font-size-13" id="addeditIssueModalLabel"> <b class="bi bi-pencil"></b> Add Consume</h5>
                <button type="button" class="btn-close font-size-11" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addIssueConsumeForm">
                    @csrf
                    <input type="hidden" id="issue_id" name="issue_id">
                    <div class="table-responsive">
                            <table class="product-listing-table w-100 text-center" id="consumeTable">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-center">Max Allow Qty</th>
                                    <th scope="col" class="text-center">Already Consumed Qty</th>
                                    <th scope="col" class="text-center">Consume Qty  <span class="text-danger">*</span></th>
                                    <th scope="col" class="text-center">Issue Return Qty</th>
                                </tr>
                            </thead>
                            <tbody >
                                <tr>
                                    <!-- Autocomplete Product Name -->
                                    <td id="max_qty"></td>
                                    <td id="consume_qty"></td>
                                    <td>
                                        <input type="text" class="form-control bg-white smt_numeric_only_qty" name="qty" id="qty">
                                        <input type="hidden" id="available_stock_qty">
                                    </td>
                                    <td id="issue_return_qty"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                     <!-- Save Button -->
                     <div class="d-flex justify-content-center mt-3" id="save_consume_button">
                        <button type="submit" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 save_consume_button" ><i class="bi bi-save font-size-11" aria-hidden="true"></i> Save Consume</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--Consume Modal--->
<script>
    $('#qty').on('input paste keyup', function () {
        let val = this.value;

        if (!/^\d*\.?\d{0,3}$/.test(val)) {
            val = val.slice(0, -1);
        }

        // Convert to float
        const maxQty = parseFloat($('#available_stock_qty').val()) || 0;
        const valFloat = parseFloat(val) || 0;

        // Fixing floating point precision by rounding to 3 decimal places
        if (parseFloat(valFloat.toFixed(3)) > parseFloat(maxQty.toFixed(3))) {
            val = maxQty.toFixed(3);
        }

        this.value = val;
    });



    //===save consume  data==
    $("#addIssueConsumeForm").off('submit').on('submit', function (e) {
        e.preventDefault();

        let submitBtn = $('#save_consume_button');

        if (submitBtn.hasClass('processing')) {
            return;
        }

        let issue_qty = parseFloat($('#qty').val().trim());
        if (isNaN(issue_qty) || issue_qty < 0.001) {
            toastr.error("Minimum quantity must be 0.001");
            $('#qty').focus();
            return;
        }

        submitBtn.prop('disabled', true).addClass('processing');

        let formData = $(this).serialize();
        $.ajax({
            url: "{{ route('buyer.issue.consume.store') }}",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: formData,
            success: function (response) {
                if (response.status) {
                    $('#addIssueConsumeForm')[0].reset();
                    $('#addIssueConsumeForm').find('input[type="hidden"]').val('');
                    $('#IssueConsumeModal').modal('hide');
                    toastr.success(response.message);
                    $('#report-table').DataTable().destroy();
                    report_list_data();
                    $('#consume').data('clicked', false);
                } else {
                    toastr.error(response.message || "Failed to Consume.");
                }
                submitBtn.prop('disabled', false).removeClass('processing');
            },
            error: function (xhr) {
                let msg = xhr.responseJSON?.message || "Something went wrong!";
                toastr.error(msg);
                submitBtn.prop('disabled', false).removeClass('processing');
            }
        });
    });
    //===Save consume Data==
    $('#IssueConsumeModal').on('hidden.bs.modal', function () {
        $('#consume').data('clicked', false);
    });


</script>
