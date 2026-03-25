<!---indent Modal-->
<div id="indentModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-13" id="addeditindentModalLabel">Add Indent</h2>
                <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-floating w-75 mt-2" id="search_bulk_indent_product" style="display:none;">
                    <input type="text" class="form-control specialCharacterAllowed" name="search_product_name" id="search_indent_product_name" placeholder="" value="" />
                    <label for="search_product_name">Search for Product Name / Specification</label>
                </div>
                <ul id="product_search_list" class="list-group position-absolute w-75" style="z-index:999; display:none;"></ul>
                <form id="addIndentForm">
                    @csrf
                    <input type="hidden" id="indent_inventory_id" name="inventory_id">
                    <input type="hidden" id="indent_id" name="indent_id">
                    <div class="table-responsive">
                        <table class="product-listing-table w-100 text-center">
                            <thead>
                                <tr>
                                    <th scope="col">Product Name</th>
                                    <th scope="col">Product Specification</th>
                                    <th scope="col">Product Size</th>
                                    <th scope="col">Product UOM</th>
                                    <th scope="col">Remarks</th>
                                    <th scope="col">QTY <span class="text-danger">*</span></th>
                                </tr>
                            </thead>
                            <tbody id ="indent_tbody">
                                
                            </tbody>
                        </table>
                    </div>
                     <!-- Save Button -->
                      <div class="d-flex justify-content-center mt-3">
                        <button type="submit" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 save_indent_button"><span class="bi bi-save font-size-11" aria-hidden="true"></span> Save Indent</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--indent Modal--->
<script>
    //===save indent data==
    let isSaveIndentSubmitting = false;
    $("#addIndentForm").off('submit').on('submit', function (e) {
        e.preventDefault();

        if (isSaveIndentSubmitting) return;

        const $form = $(this);
        const $saveButton = $('.save_indent_button');
        let hasError = false;

        const branchId = $('#branch_id').val();


        if (!branchId) {
            toastr.error("Branch Name is required");
            hasError = true;
        }

        const inventoryIds = $form.find('input[name="inventory_id[]"]').map(function () {
            return $(this).val();
        }).get();

        const indentQtyArr = $form.find('input[name="indent_qty[]"]').map(function () {
            return $(this).val();
        }).get();
        let minIndentQtyArr = [];

        const indentQtyField = $form.find('input[name="indent_qty"]');
        const minIndentQtyField = $form.find('input[name="min_indent_qty"]');

        if (indentQtyField.length > 0 && minIndentQtyField.length > 0) {
            const indentQty = parseFloat(parseFloat(indentQtyField.val()).toFixed(3));
            const minQty = parseFloat(parseFloat(minIndentQtyField.val()).toFixed(3));

            if (!isNaN(indentQty) && !isNaN(minQty) && indentQty < minQty) {
                toastr.error(`Indent quantity cannot be less than existing RFQ quantity (${minQty.toFixed(3)})`);
                hasError = true;
            }
        }

        for (let i = 0; i < inventoryIds.length; i++) {
            if (!inventoryIds[i]) {
                toastr.error(`Row ${i + 1}: Valid Inventory is required`);
                hasError = true;
            }

            const qty = parseFloat(parseFloat(indentQtyArr[i]).toFixed(3));

            if (!indentQtyArr[i]) {
                toastr.error(`Row ${i + 1}: QTY is required`);
                hasError = true;
            } else if (isNaN(qty) || qty < 0.001) {
                toastr.error(`Row ${i + 1}: QTY must be a number and at least 0.001`);
                hasError = true;
            }
        }

        $form.find('[maxlength]').each(function () {
            const max = parseInt($(this).attr('maxlength'));
            const val = $(this).val() || '';
            if (val.length > max) {
                const fieldName = $(this).attr('name') || 'Field';
                toastr.error(fieldName + ` must not exceed ${max} characters.`);
                hasError = true;
                return false;
            }
        });

        if (hasError) {
            isSaveIndentSubmitting = false;
            return;
        }

        isSaveIndentSubmitting = true;
        $saveButton.prop('disabled', true);

        let formData = $form.serialize();
        formData += '&buyer_branch_id=' + encodeURIComponent(branchId);
    
        $.ajax({
            url: "{{ route('buyer.indent.store') }}",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: formData,
            success: function (response) {
                isSaveIndentSubmitting = false;
                $saveButton.prop('disabled', false);
                if (response.status) {
                    $form[0].reset();
                    $form.find('input[type="hidden"]').val('');
                    $('#indentModal').modal('hide');
                    toastr.success(response.message);
                    if (inventoryTable) {
                            inventoryTable.ajax.reload();
                        }
                } else {
                    toastr.error(response.message || "Failed to add indent!");
                }
            },
            error: function (xhr) {
                isSaveIndentSubmitting = false;
                $saveButton.prop('disabled', false);
                const res = xhr.responseJSON || {};
                if (xhr.status === 422 && res.errors) {
                    let first = true;
                    $.each(res.errors, function (key, messages) {
                        toastr.error(messages[0]);
                        if (first) {
                            $('[name="' + key + '"]').focus();
                            first = false;
                        }
                    });
                } else {
                    toastr.error(res.message || "Something went wrong!");
                }
            }
        });
    });
    //===Save Indent Data==

    $(document).on('click', '.delete_indent_button', function () {
        checkPermissionAndExecute('INDENT', 'delete', '1', function () {
            const indentId = $('#indent_id').val();
            const inventoryId = $('#indent_inventory_id').val();
            const indent_qty = $('#indent_qty').val();
            if (!indentId || !inventoryId) {
                toastr.error("Invalid indent or inventory ID.");
                return;
            }

            if (confirm("Are you sure you want to delete this indent?")) {
                $('.delete_indent_button').attr('disabled', 'disabled');
                $.ajax({
                    url: '{{ route("buyer.indent.delete", ":id") }}'.replace(':id', indentId),
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        indent_inventory_id: inventoryId,
                        indent_qty: indent_qty,
                        indent_id:indentId
                    },
                    success: function (response) {
                        if (response.status=='1') {
                            $('#addIndentForm')[0].reset();
                            $('#addIndentForm').find('input[type="hidden"]').val('');
                            $('#indentModal').modal('hide');

                            toastr.success(response.message || 'Indent deleted successfully.');
                            $('.delete_indent_button').removeAttr('disabled');
                            if (inventoryTable) {
                                    inventoryTable.ajax.reload();
                                }
                        } else {
                            $('.delete_indent_button').removeAttr('disabled');
                            toastr.error(response.message || 'Failed to delete indent.');
                        }
                    },
                    error: function () {
                        $('.delete_indent_button').removeAttr('disabled');
                        toastr.error('Server error. Please try again.');
                    }
                });

            }
        });
    });
    $(document).on('click', '.approve_indent_button', function () {
        checkPermissionAndExecute('INDENT_APPROVE', 'add', '1', function () {
            const indentId = $('#indent_id').val();
            const inventoryId = $('#indent_inventory_id').val();
            const indent_qty = $('#indent_qty').val();
            if (!indentId || !inventoryId) {
                toastr.error("Invalid indent or inventory ID.");
                return;
            }

            $('.approve_indent_button').attr('disabled', 'disabled');
            $.ajax({
                url: '{{ route("buyer.indent.approve", ":id") }}'.replace(':id', indentId),
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    indent_inventory_id: inventoryId,
                    indent_qty: indent_qty
                },
                success: function (response) {
                    if (response.status=='1') {
                        $('#addIndentForm')[0].reset();
                        $('#addIndentForm').find('input[type="hidden"]').val('');
                        $('#indentModal').modal('hide');

                        toastr.success(response.message || 'Indent approved successfully.');
                        $('.approve_indent_button').removeAttr('disabled');
                        if (inventoryTable) {
                                inventoryTable.ajax.reload();
                            }
                    } else {
                        $('.approve_indent_button').removeAttr('disabled');
                        toastr.error(response.message || 'Failed to approve indent.');
                    }
                },
                error: function () {
                    $('.delete_indent_button').removeAttr('disabled');
                    toastr.error('Server error. Please try again.');
                }
            });
        });
    });


</script>
