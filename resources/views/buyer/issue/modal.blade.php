<!-- Issue Modal -->
<div id="IssueModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-13" id="addeditIssueModalLabel">
                    <span class="bi bi-pencil" aria-hidden="true"></span> Add Issue
                </h2>
                <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addIssueForm">
                    @csrf
                    <input type="hidden" id="issue_inventory_id" name="inventory_id">
                    <div class="table-responsive">
                        <table class="product-listing-table w-100 text-center">
                            <thead>
                                <tr>
                                    <th>Product <br> Name</th>
                                    <th>Product <br> Specification</th>
                                    <th>Product <br> Size</th>
                                    <th>Product <br> UOM</th>
                                    <th>Max <br> Quantity</th>
                                    <th>Issued To</th>
                                    <th>Issued <br> Quantity <span class="text-danger">*</span></th>
                                    <th>Issued <br> From <span class="text-danger">*</span></th>
                                    <th>Remarks</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="issue_table_body"></tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        <button type="submit" class="ra-btn btn-primary ra-btn-primary save_issue_button text-uppercase text-nowrap font-size-11">
                            <span class="bi bi-save font-size-11" aria-hidden="true"></span> Save Issued
                        </button>
                        
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Script -->
<script>
    $(document).on('input change', '#addIssueForm input, #addIssueForm select', function () {
        $(this).closest('tr').removeClass('table-danger');
    });
    $(document).on('click', '.issue_remove-row', function () {
        let $tbody = $('#issue_table_body');
        let rowCount = $tbody.find('tr').length;

        if (rowCount > 1) {
            $(this).closest('tr').remove();
        } else {
            toastr.error("At least one row must remain.");
        }
    });
    // $(document).on('input', '[class^="qty_"]', function () {

    //     let $row = $(this).closest('tr');

    //     let val = this.value;

    //     if (!/^\d*\.?\d{0,3}$/.test(val)) {
    //         val = val.slice(0, -1);
    //     }

    //     let issueFrom = $row.find('select[name="issued_from[]"] option:selected');

    //     let maxQty = parseFloat(issueFrom.data('stockqty')) || 0;

    //     if (parseFloat(val) > maxQty) {
    //         val = maxQty;
    //     }

    //     this.value = val;
    // });
    // On input, limit max quantity
    // Numeric only + max per row
    $(document).on('input', '.bulkIssueQty', function () {
        let $row = $(this).closest('tr');

        // Get the selected "Issued From" option in this row
        let $issueFrom = $row.find('select[name="issued_from[]"] option:selected');
        let maxQty = parseFloat($issueFrom.data('stockqty')) || 0;

        // Round maxQty to 3 decimals to avoid floating point issues
        maxQty = Math.round(maxQty * 1000) / 1000;

        // Get current value
        let val = this.value;

        // Remove any non-numeric characters except decimal
        val = val.replace(/[^0-9.]/g, '');

        // Limit to 3 decimal places
        if (/^\d*\.?\d{0,3}$/.test(val) === false) {
            val = val.slice(0, -1);
        }

        // Parse to float and round to 3 decimals
        let numericVal = parseFloat(val) || 0;
        numericVal = Math.round(numericVal * 1000) / 1000;

        // Enforce max quantity
        if (numericVal > maxQty) {
            numericVal = maxQty;
        }

        this.value = numericVal;
    });

    let isSubmitting = false;

    $('#addIssueForm').off('submit').on('submit', function (e) {
        e.preventDefault();
        checkPermissionAndExecute('GATE_PASS_ENTRY', 'add', '1', function () {
            $('#issue_table_body tr').removeClass('table-danger');
            if (isSubmitting) return;
            isSubmitting = true;

            const $submitBtn = $('.save_issue_button');
            $submitBtn.prop('disabled', true);

            let hasError = false;
            let firstInvalid = null;

            const inventory_id = $('#issue_inventory_id').val();

            let qtyInputs = $('input[name="qty[]"]');
            let issuedToInputs = $('select[name="issued_to[]"]');
            let issuedFromInputs = $('select[name="issued_from[]"]');

            if (!inventory_id) {
                toastr.error("Valid Inventory required!");
                hasError = true;
            }

            if (qtyInputs.length === 0) {
                toastr.error("No items to issue!");
                hasError = true;
            }

            qtyInputs.each(function (i) {
                let $row = $(this).closest('tr');
                let qty = $(this).val().trim();
                let issuedTo = issuedToInputs.eq(i).val();
                let issuedFrom = issuedFromInputs.eq(i).val();
                let productName = $row.find('td:eq(0)').text().trim();

                // Quantity validation
                if (!qty) {
                    toastr.error(`Row ${i + 1} - ${productName}: Quantity is blank. Enter quantity or remove this row.`);
                    if (!firstInvalid) firstInvalid = this;
                    hasError = true;
                    return false;
                }

                let num = parseFloat(qty);

                if (isNaN(num) || num < 0.001) {
                    $row.addClass('table-danger');
                    toastr.error(`Invalid quantity in row ${i + 1}`);
                    if (!firstInvalid) firstInvalid = this;
                    hasError = true;
                    return false;
                }

                // // Issued To validation
                // if (!issuedTo) {
                //     toastr.error(`Select Issued To in row ${i + 1}`);
                //     if (!firstInvalid) firstInvalid = issuedToInputs[i];
                //     hasError = true;
                //     return false;
                // }

                // Issued From validation
                if (!issuedFrom) {
                    $row.addClass('table-danger');
                    toastr.error(`Select Issed From in row ${i + 1}`);
                    if (!firstInvalid) firstInvalid = issuedFromInputs[i];
                    hasError = true;
                    return false;
                }

            });
    
            // maxlength validation
            let invalidMaxlengthField = null;

            $('#addIssueForm [maxlength]').each(function () {
                const max = parseInt($(this).attr('maxlength'));
                const val = $(this).val();

                if (val.length > max) {
                    invalidMaxlengthField = this;
                    hasError = true;
                    return false;
                }
            });

            if (invalidMaxlengthField) {
                const fieldName = $(invalidMaxlengthField).attr('name') || 'Field';
                toastr.error(`${fieldName} must not exceed ${$(invalidMaxlengthField).attr('maxlength')} characters.`);
                firstInvalid = invalidMaxlengthField;
            }

            // Focus first invalid
            if (firstInvalid) {
                $(firstInvalid).focus();
            }

            if (hasError) {
                isSubmitting = false;
                $submitBtn.prop('disabled', false);
                return;
            }

            const formData = $(this).serialize();

            $.ajax({
                url: "{{ route('buyer.issue.store') }}",
                type: "POST",
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },

                success: function (response) {
                    isSubmitting = false;
                    $submitBtn.prop('disabled', false);

                    if (response.status) {
                        $('#addIssueForm')[0].reset();
                        $('#issue_table_body').empty(); // clear dynamic rows
                        $('#IssueModal').modal('hide');

                        toastr.success(response.message);

                        if (inventoryTable) {
                            inventoryTable.ajax.reload();
                        }
                    } else {
                        toastr.error("Failed to add Issue!");
                    }
                },

                error: function (xhr) {
                    isSubmitting = false;
                    $submitBtn.prop('disabled', false);

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        Object.values(xhr.responseJSON.errors).forEach(err => {
                            toastr.error(err[0]);
                        });
                    } else if (xhr.responseJSON?.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error("Something went wrong!");
                    }
                }
            });
        });
    });
</script>
