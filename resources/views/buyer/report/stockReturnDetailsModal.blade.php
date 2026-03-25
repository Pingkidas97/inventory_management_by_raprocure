<div class="modal fade" id="stockReturnQtyDetailsModal" tabindex="-1" aria-labelledby="QtyModalLabel1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xxl modal-fullscreen-md-down" style="max-width: 95% !important;">
        <div class="modal-content">
            <div class="modal-header bg-graident text-white">
                <h5 class="modal-title font-size-13" id="grnQtyModalLabel"><i class="bi bi-pencil"></i> Edit Return Stock</h5>
                <button type="button" class="btn-close font-size-10" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0">
                <div class="table-responsive">
                    <form>
                        <input type="hidden" id="stock_return_id" name="id">{{-- pingki type "text" to convert "hidden" --}}
                        <table class="product-listing-table w-100 text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Product Specification</th>
                                    <th>Product Size</th>
                                    <th>Product UOM</th>
                                    <th>Added Qty</th>
                                    <th>Vendor Name</th>
                                    <th>Vehicle no / LR No</th>
                                    <th>Debit Note No</th>
                                    <th>Frieght</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="tdProductName"></td>
                                    <td id="tdProductSpecification"></td>
                                    <td id="tdProductSize"></td>
                                    <td id="tdProductUom"></td>
                                    <td id="tdAddedQuantity"></td>
                                    <td><input type="text" class="form-control bg-white" id="stock_vendor_name" name="stock_vendor_name" maxlength="255"></td>
                                    <td><input type="text" class="form-control bg-white" id="stock_vehicle_no_lr_no" name="stock_vehicle_no_lr_no" maxlength="50"></td>
                                    <td><input type="text" class="form-control bg-white" id="stock_debit_note_no" name="stock_debit_note_no" maxlength="20" ></td>
                                    <td><input type="text" class="form-control bg-white smt_numeric_only_qty" id="stock_frieght"  name="stock_frieght" maxlength="20" ></td>
                                    <td><input type="text" class="form-control bg-white specialCharacterAllowed" maxlength="155" style="min-width: 250px;" id="tdremarks" name="remarks" maxlength="50"></td>
                                </tr>
                            </tbody>
                        </table>
                    </form>

                </div>
                <div class="d-flex justify-content-center mt-3" style="margin-bottom: 2%;">
                    <button type="submit" class="ra-btn btn-primary ra-btn-primary text-uppercase text-nowrap font-size-11 edit_stock_return_button">
                        <i class="bi bi-save"></i> Edit Stock Return
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('exJs')
    <script>

        $(document).on('click', '.edit_stock_return_button', function (e) {
            e.preventDefault();

            let formData = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: $('#stock_return_id').val(),
                remarks: $('#tdremarks').val(),
                stock_vendor_name: $('#stock_vendor_name').val(),
                stock_vehicle_no_lr_no: $('#stock_vehicle_no_lr_no').val(),
                stock_debit_note_no: $('#stock_debit_note_no').val(),
                freight_charges: $('#freight_charges').val(),
                stock_frieght: $('#stock_frieght').val()
            };

            $.ajax({
                url: editStockReturnRowdataurl,
                type: "POST",
                data: formData,
                success: function (response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        $('#stockReturnQtyDetailsModal').modal('hide');
                        $('#report-table').DataTable().destroy();
                        report_list_data();
                    } else {
                        toastr.error(response.message || 'Update failed');
                    }
                },
               error: function (xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        let errorMessages = '';

                        for (let key in errors) {
                            if (errors.hasOwnProperty(key)) {
                                errorMessages += errors[key].join('<br>') + '<br>';
                            }
                        }

                        toastr.error(errorMessages || 'Validation error occurred.');
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
