<!---indent Modal-->
<div id="IssueReturnModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-13" id="addeditIssueReturnModalLabel">Add Issue Return</h2>
                <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addIssueReturnForm">
                    @csrf
                    <input type="hidden" id="IssueReturn_inventory_id" name="inventory_id">
                    <div class="table-responsive">
                            <table class="product-listing-table w-100 text-center">
                            <thead>
                                <tr>
                                    <th scope="col">Product <br> Name</th>
                                    <th scope="col">Product <br> Specification</th>
                                    <th scope="col">Product <br> Size</th>
                                    <th scope="col">Product <br> UOM</th>
                                    <th scope="col">Max <br> Quantity</th>
                                    <th scope="col">Quantity <span class="text-danger">*</span></th>
                                    <th scope="col">Issue <br> Return From <span class="text-danger">*</span></th>
                                    <th scope="col">Issued <br> Type </th>
                                    <th scope="col">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <!-- Autocomplete Product Name -->
                                    <td id="issuereturn_product_name"></td>
                                    <td id="issuereturn_specification"></td>
                                    <td id="issuereturn_size"></td>
                                    <td id="issuereturn_uom"></td>
                                    <td id="issuereturn_max_quantity"></td>
                                    <td><input type="text" class="form-control bg-white w-100 smt_numeric_only_qty" name="qty" id="IssuedReturnQty" maxlength="22"> <input type="hidden" name="stock" id="IssuedReturnStock"></td>
                                    <td>
                                        <select class="form-select w-180" id="issue_return_from" name="issued_return_for">
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select w-220" id="issued_return_type" name="issued_return_type">
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control bg-white w-250  specialCharacterAllowed" name="remarks" id="issuereturn_remarks" maxlength="155"></td>
                                    
                                </tr>
                            </tbody>
                        </table>
                    </div>
                     <!-- Save Button -->
                     <div class="d-flex justify-content-center mt-3">
                        <button type="submit" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 save_issue_return_button"><i class="bi bi-save font-size-11" aria-hidden="true" ></i> Save Issued Return</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--Issue Modal--->
<script>
   $('#IssuedReturnQty').on('input paste keyup', function () {
        let val = this.value;

        // if (!/^\d*\.?\d{0,2}$/.test(val)) {
        if (!/^\d*\.?\d{0,3}$/.test(val)) {
            val = val.slice(0, -1);
        }

        const maxQty = parseFloat($('#IssuedReturnStock').val()) || 0;

        const valFloat = parseFloat(val) || 0;

        // Fixing floating point precision by rounding to 3 decimal places
        if (parseFloat(valFloat.toFixed(3)) > parseFloat(maxQty.toFixed(3))) {
            val = maxQty.toFixed(3);
        }

        this.value = val;
    });


    //===save Issue Return data==
    let isSaveIssueReturnSubmitting = false;
    $("#addIssueReturnForm").off('submit').on('submit', function(e) {
        e.preventDefault();
        if (isSaveIssueReturnSubmitting) return;

        let hasError = false;
        const branch_id = $('#branch_id').val();
        const inventory_id = $('#IssueReturn_inventory_id').val();
        const qty = $('#IssuedReturnQty').val();
        const $submitButton = $('.save_issue_return_button');

        $submitButton.prop('disabled', false);

        if (!inventory_id) {
            toastr.error("Please select a valid inventory.");
            hasError = true;
        }

        if (!qty) {
            toastr.error("Quantity is required.");
            $('#IssuedReturnQty').focus();
            hasError = true;
        }

        if (isNaN(qty) || parseFloat(qty) < 0.001) {
            toastr.error("Enter a valid quantity greater than 0.001.");
            $('#IssuedReturnQty').focus();
            hasError = true;
        }

        let invalidField = null;
        $('#addIssueReturnForm [maxlength]').each(function () {
            const max = parseInt($(this).attr('maxlength'));
            const val = $(this).val();
            if (val.length > max) {
                invalidField = this;
                hasError = true;
            }
        });

        if (invalidField) {
            const fieldName = $(invalidField).attr('name') || 'Field';
            toastr.error(`${fieldName} must not exceed ${$(invalidField).attr('maxlength')} characters.`);
            $(invalidField).focus();
        }
        if (hasError) return;
        isSaveIssueReturnSubmitting = true;
        $submitButton.prop('disabled', true);
        const formData = $(this).serialize();

        $submitButton.prop('disabled', true);

        $.ajax({
            url: "{{ route('buyer.issue_return.store') }}",
            type: "POST",
            data: formData + '&buyer_branch_id=' + branch_id,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                isSaveIssueReturnSubmitting = false;
                $submitButton.prop('disabled', false);
                if (response.status) {
                    $('#addIssueReturnForm')[0].reset();
                    $('#addIssueReturnForm').find('input[type="hidden"]').val('');
                    $('#IssueReturnModal').modal('hide');
                    toastr.success(response.message);
                    if (inventoryTable) {
                        inventoryTable.ajax.reload();
                    }
                } else {
                    toastr.error(response.message || "Failed to add issue return.");
                }
            },
            error: function (xhr) {
                isSaveIssueReturnSubmitting = false;
                $submitButton.prop('disabled', false);

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    let errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        toastr.error(errors[field][0]);
                    }
                } else if (xhr.status === 400 && xhr.responseJSON?.message) {
                    toastr.error(xhr.responseJSON.message);
                } else {
                    toastr.error("Something went wrong!");
                }
            }
        });
        
    });


    //===Save Issue Return Data==



</script>
