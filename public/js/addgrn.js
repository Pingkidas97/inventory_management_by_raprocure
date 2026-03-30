
function grnPopUP(inventoryId) {
    const url = checkGrnEntry.replace('__ID__', inventoryId);

    fetch(url, {
    method: 'GET',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
    }})
        .then(async (response) => {
            const data = await response.json();

            if (response.status === 403 || data.status === 0) {
                toastr.error(data.message || 'Unauthorized access.');
                return;
            }

            if (data.has_pending_order) {
                document.getElementById('stockReturnGrnTable').getElementsByTagName('tbody')[0].innerHTML = '';
                document.getElementById('orderGrnTable').getElementsByTagName('tbody')[0].innerHTML = '';
                openGrnModal(data.inventoryId);

                // if (data.stockReturn && Array.isArray(data.stock_return_details) && data.stock_return_details.length > 0) {
                //     const stockReturnGrnTable = document.getElementById('stockReturnGrnTable');
                //     if (stockReturnGrnTable) {
                //         stockReturnGrnTable.style.display = 'block';
                //     }
                //     createStockReturnGrnTable(data.stock_return_details);
                // }

                // if (data.order && Array.isArray(data.order_details) && data.order_details.length > 0) {
                //     const orderGrnTable = document.getElementById('orderGrnTable');
                //     if (orderGrnTable) {
                //         orderGrnTable.style.display = 'block';
                //     }
                //     createOrderGrnTable(data.order_details);
                // }
                if (data.stockReturn ===true && Array.isArray(data.stock_return_details) && data.stock_return_details.length > 0) {
                    const stockReturnGrnTable = document.getElementById('stockReturnGrnTable');
                    if (stockReturnGrnTable) {
                        stockReturnGrnTable.style.display = 'block';
                    }
                    createStockReturnGrnTable(data.stock_return_details);
                }else{
                    const stockReturnGrnTable = document.getElementById('stockReturnGrnTable');
                    if (stockReturnGrnTable) {
                        stockReturnGrnTable.style.display = 'none';
                    }
                }

                if (data.order===true && Array.isArray(data.order_details) && data.order_details.length > 0) {
                    const orderGrnTable = document.getElementById('orderGrnTable');
                    if (orderGrnTable) {
                        orderGrnTable.style.display = 'block';
                    }
                    createOrderGrnTable(data.order_details, data.grn_tolerance);
                }else{
                    const orderGrnTable = document.getElementById('orderGrnTable');
                    if (orderGrnTable) {
                        orderGrnTable.style.display = 'none';
                    }
                }$('.grn-date').each(function () {

            const row = $(this).closest('tr');
            const minDateStr = row.find('.grn-min-date').val();

            let minDate = false;

            if (minDateStr) {
                if (minDateStr.includes('/')) {
                    const p = minDateStr.split('/');
                    minDate = new Date(p[2], p[1] - 1, p[0]);
                }

                if (minDateStr.includes('-')) {
                    const p = minDateStr.split('-');
                    minDate = new Date(p[0], p[1] - 1, p[2]);
                }
            }
            $(this).datetimepicker({
                format: 'd/m/Y',
                timepicker: false,
                minDate: minDate,
                maxDate: 0 ,
                value: new Date()
            });
        });
                initializeDatepickerForBillDates();
            } else {
                toastr.error('No pending order or stock return details found for this inventory to make a GRN entry.');
            }
        })
        .catch(error => {
            console.error('Error checking GRN entry:', error);
            toastr.error('Something went wrong.');
        });
}

function initializeDatepickerForBillDates() {
    $('.bill-date').datetimepicker({
        format: 'd/m/Y',
        timepicker: false,
        maxDate: 0
    });
    $('.grn-date').each(function () {

        const row = $(this).closest('tr');
        const minDateStr = row.find('.grn-min-date').val();

        let minDate = false;

        if (minDateStr) {
            if (minDateStr.includes('/')) {
                const p = minDateStr.split('/');
                minDate = new Date(p[2], p[1] - 1, p[0]);
            }

            if (minDateStr.includes('-')) {
                const p = minDateStr.split('-');
                minDate = new Date(p[0], p[1] - 1, p[2]);
            }
        }
        $(this).datetimepicker({
            format: 'd/m/Y',
            timepicker: false,
            minDate: minDate,
            maxDate: 0 ,
            value: new Date()
        });
    });
}
function createOrderGrnTable(order_details, grn_tolerance){
    var tbody = $('#grnaddModal #orderGrnTable tbody');
    tbody.empty();
    order_details.forEach(function(order) {
        var maxGrnQty = parseFloat(((parseFloat(order.order_quantity || 0) * grn_tolerance) - parseFloat(order.grn_entered || 0)).toFixed(3));
        const url = order.baseManualPoUrl.replace('__ID__', order.id);
        var row = `<tr>
            <td class="text-center">
                <input type="text" readonly class="form-control bg-white text-center w-120" style="background-color: #dbeff1 !important;" id="po_number" name="po_number[]"value="${order.order_number || ''}">
                <input type="hidden" class="form-control text-center" id="order_id" name="order_id[]" value="${order.id}">
            </td>
            <td class="text-center">
                ${
                    order.order_type !== 'manual_order'
                        ? `<input type="text" readonly class="form-control bg-white text-center w-120" id="rfq_no" style="background-color: #dbeff1 !important;" name="rfq_no[]" value="${order.rfq_number || ''}"><input type="hidden" id="grn_type" name="grn_type[]" value="1">`
                        : `- <input type="hidden" id="grn_type" name="grn_type[]" value="4">`
                }
            </td>

            <td class="text-center"> <input type="text" readonly class="form-control bg-white text-center w-100 grn-min-date" style="background-color: #dbeff1 !important;" value="${order.order_date || ''}"></td>
            <td class="text-center"><input type="text" readonly class="form-control bg-white text-center" style="background-color: #dbeff1 !important;"  value="${order.show_order_quantity || ''}"><input type="hidden" id="order_qty"  name="order_qty[]" value="${order.order_quantity || ''}"></td>
            <td class="text-center"><input type="text" readonly name="vendor_name[]" style="background-color: #dbeff1 !important;" class="form-control bg-white text-center w-120" id="vendor_name" value="${order.vendor_name || ''}"</td>
            <td class="text-center"><input type="text" name="grn_entered[]" readonly class="form-control bg-white text-center w-100" style="background-color: #dbeff1 !important;" value="${order.grn_entered || '0'}"></td>
            <td class="text-center">
            <input type="text" readonly class="form-control bg-white text-center w-120 "style="background-color: #dbeff1 !important;" value="${order.ratewithcurrency || ''}">
            <input type="hidden" class="rate" name="rate[]" value="${order.rate || ''}"></td>
            <td class="text-center">
                ${order.rate_in_local_currency === '1'
                    ? `<input
                            type="text"
                            class="form-control bg-white text-center rate_in_local_currency"
                            name="rate_in_local_currency[]"
                            maxlength="7" oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                            value="${order.grn_buyer_rate || ''}"
                            ${Number(order.grn_buyer_rate) > 0 ? 'readonly' : ''}
                            style="display: block;">`
                    : `<input
                            type="hidden"
                            name="rate_in_local_currency[]"
                            value="${order.grn_buyer_rate || ''}">-`
                }
                <input type="hidden" name="currency_symbol[]" value="${order.currency_symbol || ''}">
                <input type="hidden" name="gst_percentage[]" class="gst_percentage" value="${order.gst_percentage}">
                <input type="hidden" name="buyer_rate[]" class="buyer_rate" value="${order.grn_buyer_rate || ''}">
            </td>

            <td class="text-center"><input type="text" id="grn_qty" class="grn_quantity_input form-control bg-white text-center w-100" name="grn_qty[]" data-max="${maxGrnQty}" maxlength="20" min="0" step="any"  value="${order.grn_qty || ''}"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-80" id="invoice_number" name="invoice_number[]" maxlength="50" value="${order.invoice_number || ''}"></td>
            <td class="text-center"><input type="text" class="form-control dateTimePickerStart bg-white text-center w-80 bill-date" id="bill_date" name="bill_date[]" maxlength="50" value="${order.bill_date || ''}" ></td>
            <td class="text-center"><input type="text" class="form-control dateTimePickerStart bg-white text-center w-80 grn-date" id="grn_date" name="grn_date[]" maxlength="50" ></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-100" id="transporter_name" name="transporter_name[]" maxlength="255" value="${order.transporter_name || ''}"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-120 vehicle_lr_number" id="vehicle_lr_number" name="vehicle_lr_number[]" maxlength="20" value="${order.vehicle_lr_number || ''}"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-80 smt_numeric_only" id="gross_weight" name="gross_weight[]" maxlength="20" value="${order.gross_weight || ''}"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-100 smt_numeric_only gst" name="gst[]" maxlength="20" readonly style="background-color: #dbeff1 !important;"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-80 smt_numeric_only" id="freight_charges" name="freight_charges[]" maxlength="20" value="${order.freight_charges || ''}"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-100" id="approved_by" name="approved_by[]" maxlength="255" value="${order.approved_by || ''}"></td>
            <td class="text-center">
                <span data-id="${order.id}">
                <a href="${url}" target="_blank" rel="noopener noreferrer">View ${order.order_type === 'manual_order' ? 'Manual PO' : 'PO'}</a>
                </span>
            </td>

        </tr>`;
        tbody.append(row);
    });
}
function createStockReturnGrnTable(stock_return_details){
    var tbody = $('#grnaddModal #stockReturnGrnTable tbody');
    tbody.empty();
    stock_return_details.forEach(function(stock) {
        var maxGrnQty = parseFloat((parseFloat(stock.qty|| 0)  - parseFloat(stock.grn_entered || 0)).toFixed(3));
        var row = `<tr>
            <input type="hidden" name="gst_percentage[]" class="gst_percentage" value="${stock.gst_percentage}">
            <input type="hidden" name="rate[]" class="rate" value="${stock.rate}">
            <input type="hidden" name="buyer_rate[]" class="buyer_rate" value="${stock.grn_buyer_rate || ''}">
            <td class="text-center">
                <input type="text" readonly class="form-control bg-white text-center w-100" style="background-color: #dbeff1 !important;" id="stock_no" name="stock_no[]" value="${stock.stock_no || ''}">
                <input type="hidden" name="stock_return_id[]" value="${stock.stock_return_id}">
                <input type="hidden" name="stock_return_for[]" value="${stock.stock_return_for}">
                ${
                    stock.order_type == 'stock_return'
                        ?`<input type="hidden" name="stock_return_grn_type[]" value="3">`:``
                }
            </td>
            <td class="text-center"> <input type="text" readonly class="form-control bg-white text-center w-120 grn-min-date" style="background-color: #dbeff1 !important;" value="${stock.updated_at || ''}"></td>

            <td class="text-center"><input type="text" readonly class="form-control bg-white text-center w-120" id="order_qty" style="background-color: #dbeff1 !important;" value="${stock.remarks || ''}"></td>

            <td class="text-center"><input type="text" readonly class="form-control bg-white text-center w-120"  style="background-color: #dbeff1 !important;" value="${stock.show_qty || ''}"><input type="hidden"  id="order_qty" name="stock_return_qty[]" value="${stock.qty || ''}"></td>

            <td class="text-center"><input type="text" readonly name="stock_vendor_name[]" style="background-color: #dbeff1 !important;" class="form-control bg-white text-center w-180" id="stock_vendor_name" value="${stock.stock_vendor_name || ''}"</td>

            <td class="text-center"><input type="text" name="stock_return_grn_entered[]" readonly class="form-control bg-white text-center w-120" style="background-color: #dbeff1 !important;" value="${stock.grn_entered || '0'}"></td>

            <td class="text-center"><input type="text" id="grn_qty" class="grn_quantity_input form-control bg-white text-center w-120"  name="grn_stock_return_qty[]" data-max="${maxGrnQty}" maxlength="20" min="0" step="any" value="${stock.grn_qty || ''}"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-120" id="invoice_number" name="stock_invoice_number[]" maxlength="50"></td>
            <td class="text-center"><input type="text" class="form-control dateTimePickerStart bg-white text-center w-120 bill-date" id="bill_date" name="stock_bill_date[]" maxlength="50" ></td>
            <td class="text-center"><input type="text" class="form-control dateTimePickerStart bg-white text-center w-120 grn-date" id="grn_date" name="stock_grn_date[]" maxlength="50" ></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-120" id="transporter_name" name="stock_transporter_name[]" maxlength="255"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-120 vehicle_lr_number" id="vehicle_lr_number" name="stock_vehicle_lr_number[]" maxlength="20"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-120 smt_numeric_only" id="gross_weight" name="stock_gross_weight[]" maxlength="20"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-120 smt_numeric_only gst" name="stock_gst[]" maxlength="20" readonly style="background-color: #dbeff1 !important;"></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-120 smt_numeric_only" id="freight_charges" name="stock_freight_charges[]" maxlength="20" ></td>
            <td class="text-center"><input type="text" class="form-control bg-white text-center w-120" id="approved_by" name="stock_approved_by[]" maxlength="255"></td>

        </tr>`;
        tbody.append(row);
    });
}
function openGrnModal(inventoryId){
    $('#grnaddModal').modal('show');
    $('#inventory_id').val(inventoryId);
}



