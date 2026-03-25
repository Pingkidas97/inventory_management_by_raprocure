<!---stock return  Modal-->
<div id="StockReturnModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h2 class="modal-title font-size-13" id="addeditStockReturnModalLabel"><i class="bi bi-pencil"></i> Add Return Stock </h2>
                <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addStockReturnForm">
                    @csrf
                    <input type="hidden" id="stock_return_inventory_id" name="inventory_id">
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
                                    <th scope="col">Stock <br> Return From <span class="text-danger">*</span></th>
                                    <th scope="col">Vendor Name</th>
                                    <th scope="col">Vehicle no / <br> LR No</th>
                                    <th scope="col">Debit <br> Note No</th>
                                    <th scope="col">Frieght</th>
                                    <th scope="col">Stock <br> Return Type <span class="text-danger">*</span></th>
                                    <th scope="col">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <!-- Autocomplete Product Name -->
                                    <td id="stock_return_product_name"></td>
                                    <td id="stock_return_specification"></td>
                                    <td id="stock_return_size"></td>
                                    <td id="stock_return_uom"></td>
                                    <td id="stock_return_max_quantity"></td>
                                    <td><input type="text" class="form-control w-100 bg-white smt_numeric_only_qty" name="qty" id="stock_return_qty" maxlength="22"> <input type="hidden" name="stock" id="stock_return_stock"></td>
                                    <td>
                                        <select class="form-select bg-white w-180" id="stock_return_from" name="stock_return_for">
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control bg-white w-180" name="stock_vendor_name" id="stock_return_vendor_name" maxlength="255"></td>
                                    <td><input type="text" class="form-control bg-white w-120" name="stock_vehicle_no_lr_no" id="stock_vehicle_no_lr_no" maxlength="50"></td>
                                    <td><input type="text" class="form-control bg-white w-120" name="stock_debit_note_no" id="stock_debit_note_no" maxlength="20"></td>
                                    <td><input type="text" class="form-control bg-white w-120 smt_numeric_only" name="stock_frieght" id="stock_frieght" maxlength="20"></td>
                                    <td>
                                        <select class="form-select w-220" id="stock_return_type" name="stock_return_type">
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control bg-white w-250 specialCharacterAllowed" name="remarks" id="stock_return_remarks" maxlength="155"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                     <!-- Save Button -->
                     <div class="d-flex justify-content-center mt-3">
                        <button type="submit" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 save_stock_return_button"><i class="bi bi-save font-size-11" aria-hidden="true"></i> Save Return Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--stock return Modal--->
<script>
    $('#stock_return_qty').on('input paste keyup', function () {
        let val = this.value;

        // if (!/^\d*\.?\d{0,2}$/.test(val)) {
        if (!/^\d*\.?\d{0,3}$/.test(val)) {
            val = val.slice(0, -1);
        }

        const maxQty = parseFloat($('#stock_return_stock').val()) || 0;

        const valFloat = parseFloat(val) || 0;

        // Fixing floating point precision by rounding to 2 decimal places
        if (parseFloat(valFloat.toFixed(3)) > parseFloat(maxQty.toFixed(3))) {
            val = maxQty.toFixed(3);
        }

        this.value = val;
    });
    let isSaveStockReturnSubmitting = false;
    $("#addStockReturnForm").off('submit').on('submit', function (e) {
        e.preventDefault();
        if (isSaveStockReturnSubmitting) return;
        isSaveStockReturnSubmitting = true;
        $('.save_stock_return_button').prop('disabled', true);

        var z = false;
        var inventory_id = $('#stock_return_inventory_id').val();
        var qty = $('#stock_return_qty').val();

        if (inventory_id == '') {
            toastr.error("Valid Inventory!");
            z = true;
        }

        if (qty == '') {
            toastr.error("Quantity is required!");
            $("#stock_return_qty").focus();
            z = true;
        }

        if (isNaN(qty) || qty < 0.001) {
            toastr.error("Please enter a valid quantity greater than 0.001.");
            $("#stock_return_qty").focus();
            z = true;
        }

        var invalidMaxlengthField = null;
        $('#addStockReturnForm [maxlength]').each(function () {
            var max = parseInt($(this).attr('maxlength'));
            var val = $(this).val();
            if (val.length > max) {
                invalidMaxlengthField = this;
                z = true;
                return false;
            }
        });

        if (invalidMaxlengthField) {
            var fieldName = $(invalidMaxlengthField).attr('name') || 'Field';
            toastr.error(fieldName + " must not exceed " + $(invalidMaxlengthField).attr('maxlength') + " characters.");
        }

        if (z) {
            $('.save_stock_return_button').prop('disabled', false);
            isSaveStockReturnSubmitting = false;
            return;
        }

        var formData = $(this).serialize();

        $.ajax({
            url: "{{ route('buyer.stock_return.store') }}",
            type: "POST",
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                isSaveStockReturnSubmitting = false;
                $('.save_stock_return_button').prop('disabled', false);
                if (response.status) {
                    $('#addStockReturnForm')[0].reset();
                    $('#addStockReturnForm').find('input[type="hidden"]').val('');
                    $('#StockReturnModal').modal('hide');
                    toastr.success(response.message);
                    if (inventoryTable) {
                        inventoryTable.ajax.reload();
                    }
                } else {
                    toastr.error("Failed to add Issue!");
                }
            },
            error: function (xhr) {
                isSaveStockReturnSubmitting = false;
                $('.save_stock_return_button').prop('disabled', false);
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        if (errors.hasOwnProperty(field)) {
                            toastr.error(errors[field][0]);
                        }
                    }
                } else if (xhr.status === 400 && xhr.responseJSON && xhr.responseJSON.message) {
                    toastr.error(xhr.responseJSON.message);
                } else {
                    toastr.error(xhr.responseJSON?.message || "Something went wrong!");
                }
            }
        });
    });

</script>
