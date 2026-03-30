$(document).ready(function() {
    if (typeof $.fn.DataTable !== "function") {
        toastr.error("DataTables is not loaded! Check script order!");
        return;
    }
    report_list_data();
    $('#branch_id, #search_product_name, #search_category_id, #search_order_no').on('change keyup', function() {
        $('#report-table').DataTable().ajax.reload();
    });
});
let reportAjax = null;
function report_list_data() {
    $('#report-table').DataTable({
        processing  : true,
        serverSide  : true,
        searching   : false,
        paging      : true,
        scrollY     : 300,
        scrollX     : true,
        pageLength  : 25,
        destroy: true,
        createdRow: function(row) {
                    // Indent Qty (col 14)
                    $('td', row).eq(14).css({
                        "background-color": "#d1f7d6",
                        "color": "#000",
                        "font-weight": "bold",
                        "white-space": "nowrap"
                    });
                    //order Qty (col 12)
                    $('td', row).eq(12).css({
                        "white-space": "nowrap"
                    });
                    //total GRN Qty (col 13)
                    $('td', row).eq(13).css({
                        "white-space": "nowrap"
                    });
            },
        ajax: function (data, callback) {
            if (reportAjax) {
                reportAjax.abort();
            }
            reportAjax = $.ajax({
                url: pendingGrnreportlistdataurl,
                data: $.extend({}, data, {
                    branch_id: $('#branch_id').val(),
                    search_product_name: $('#search_product_name').val(),
                    search_category_id: $('#search_category_id').val(),
                    search_order_no: $('#search_order_no').val(),
                }),
                success: function (response) {
                    callback(response); // DataTables will use this
                },
                complete: function () {
                    reportAjax = null;
                }
            });
        },
        columns: [
            // {
            //     data: null,
            //     render: function (data, type, row, meta) {
            //         return meta.row + meta.settings._iDisplayStart + 1;
            //     },
            //     orderable: false,
            // },
            { data: 'serial_number', name: 'serial_number' },
            { data: 'order_number', name: 'order_number' },
            { data: 'order_date', name: 'order_date' },
            { data: 'product_name', name: 'product_name' },
            { data: 'buyer_product_name', name: 'buyer_product_name' },
            { data: 'vendor_name', name: 'vendor_name' },
            { data: 'specification', name: 'specification' },
            { data: 'size', name: 'size' },
            { data: 'inventory_grouping', name: 'inventory_grouping' },
            { data: 'added_by', name: 'added_by' },
            { data: 'added_date', name: 'added_date' },
            { data: 'uom', name: 'uom' },
            { data: 'order_quantity', name: 'order_quantity' },
            { data: 'total_grn_quantity', name: 'total_grn_quantity' },
            { data: 'pending_grn_quantity', name: 'pending_grn_quantity' }
        ],
        columnDefs: [
            { "orderable": false, "targets": "_all" }
        ],
        order: [],
        language: {
                    processing: "<div class='spinner-border spinner-border-sm'></div> Loading..."
                }
    });
}

// $(document).on('click', '#export', function () {
//     let btn = $(this);
//     let url= exportreportlistPendingGrnurl;
//     let data= {
//              _token                      :   $('meta[name="csrf-token"]').attr("content"),
//             branch_id                   :   $('#branch_id').val(),
//             search_product_name         :   $('#search_product_name').val(),
//             search_category_id          :   $('#search_category_id').val(),
//             search_order_no          :   $('#search_order_no').val(),
//         };
//     inventoryFileExport(btn,url,data,deleteExcelUrl);
// });
// Excel Export Script
$(document).ready(function() {
    $('#export').on('click', function() {
        const exporter = new Exporter({
            chunkSize: 100,
            rowLimitPerSheet: 200000,
            headers: ['SN','Branch','Order Number','Order Date','Product Name','Our Product Name','Vendor Name','Specification','Size','Inventory Grouping','Added By','Added Date','UOM','Order Quantity','Total GRN Quantity','Pending GRN Quantity'],
            totalUrl: exportTotalPendingGrnReporturl,
            batchUrl: exportBatchPendingGrnReporturl,
            token: "{{ csrf_token() }}",
            exportName: "Pending_Grn_Report_",
            expButton: '#export',
            exportProgress: '#export-progress',
            progressText: '#progress-text',
            progress: '#progress',
            fillterReadOnly: '.fillter-form-control',
            getParams: function() {
                $('#search_product_name, #search_category_id, #search_order_no,#branch_id').prop('disabled', true);
                $('button').prop('disabled', true);
                return {
                    branch_id                   :   $('#branch_id').val(),
                    search_product_name         :   $('#search_product_name').val(),
                    search_category_id          :   $('#search_category_id').val(),
                    search_order_no          :   $('#search_order_no').val(),
                };
            },
        });

        exporter.start();

        const watcher = setInterval(function () {
            if ($('#export-progress').is(':hidden')) {
                enableFilters();
                clearInterval(watcher);
            }
        }, 100);
    });

    $('#export-progress').hide();
});

function enableFilters() {
    $('#export-progress').hide();
    $('#search_product_name, #search_category_id,#search_order_no,#branch_id')
        .prop('disabled', false);
    $('button').prop('disabled', false);
}
// Excel Export Script
$('#showreportmodal').click(function (e) {
    e.preventDefault();
    $('#reportModal').modal('show');
});

$(document).on("click", ".editable-grn", function () {
    let span = $(this),
        oldValue = parseFloat(span.data("value")) || 0,
        orderId = span.data("order-id"),
        inventoryId = span.data("id"),
        grnType = span.data("grn-type"),
        po_number = span.data("po-number"),
        vendor_name = span.data("vendor-name"),
        rate = span.data("rate"),
        orderQty = parseFloat(span.data("order-qty")) || 0,
        maxtotalQty = orderQty * 1.02,
        maxQty = maxtotalQty - oldValue;


    // Prevent opening multiple inputs
    if (span.find("input").length) return;

    // Create input
    let input = $('<input>', {
        type: "text",
        class: "form-control form-control-sm",
        value: 0,
        style: "width:80px;"
    });

    span.html(input);
    input.focus().select();

    // Numeric input validation
    input.on("input", function () {
        let val = this.value;

        // Allow only numbers and 1 dot
        val = val.replace(/[^0-9.]/g, '');

        // Allow only one dot
        val = val.replace(/(\..*)\./g, '$1');

        // Limit to 2 decimal places
        let parts = val.split('.');
        if (parts[1]) {
            parts[1] = parts[1].substring(0, 3);
            val = parts.join('.');
        }

        // Limit by maxQty
        let numericVal = parseFloat(val);
        if (!isNaN(numericVal) && numericVal > maxQty) {
            // val = maxQty.toFixed(2);
            toastr.error(`You can't enter more than ${maxQty.toFixed(3)} as GRN quantity.`, 'Error', {
                timeOut: 10000,
                extendedTimeOut: 3000
            });
        }

        this.value = val;
    });



    // Save function
    function saveValue() {
        let val = parseFloat(input.val());
        if (isNaN(val) || val < 0.001) {
            toastr.error("Enter minimum 0.001. GRN quantity cannot be 0 or less.");
            span.text(oldValue).data("value", oldValue);
            return;
        }

        if (val > maxQty) {
            toastr.error(`You can't enter more than ${maxQty.toFixed(3)} as GRN quantity.`, 'Error', {
                timeOut: 1000,
                extendedTimeOut: 300
            });
            span.text(oldValue).data("value", oldValue);
            return;
        }
            // val = maxQty;

        let formData = {
            _token: $('meta[name="csrf-token"]').attr("content"),
            order_id: [orderId],
            po_number: [po_number],
            vendor_name: [vendor_name],
            inventory_id: inventoryId,
            grn_type: [grnType],
            grn_qty: [val],
            grn_entered: [oldValue],
            rate: [rate],
            order_qty: [orderQty],
        };

        $.ajax({
            url: updateGrnValueUrl,
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(res) {
                if (res.status) {
                    toastr.success("GRN Updated");
                    $('#report-table').DataTable().ajax.reload(null, false);
                    span.text(val).data("value", val);
                } else {
                    if (res.errors && res.errors.grn_qty) {
                        toastr.error(res.errors.grn_qty.join(", "));
                    } else if (res.message) {
                        toastr.error(res.message);
                    } else {
                        toastr.error("Failed to update GRN");
                    }
                    span.text(oldValue).data("value", oldValue);
                }
            },
            error: function(xhr) {
                let msg = "Failed to update GRN";
                if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.grn_qty) {
                    msg = xhr.responseJSON.errors.grn_qty.join(", ");
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                toastr.error(msg);
                span.text(oldValue).data("value", oldValue);
            }
        });
    }


    input.on("blur", saveValue);
    input.on("keypress", function (e) { if (e.which === 13) saveValue(); });
});

function show_add_grn_modal() {
    var $checked = $('.inventory_chkd:checked');

    if ($checked.length === 0) {
        toastr.error('Please select at least one item');
        return;
    }

    let inventoryIds = [];
    let orderIds = [];
    let orderTypes = [];
    let grnTypes = [];

    $checked.each(function() {
        var $row = $(this);
        inventoryIds.push($row.data('inventory-id'));
        orderIds.push($row.data('order-id'));
        orderTypes.push($row.data('order-type'));
        grnTypes.push($row.data('grn-type'));
    });

    fetch(`${fetchOrderDetailsforPendingGrnurl}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        body: JSON.stringify({
            inventory_ids: inventoryIds,
            order_ids: orderIds,
            order_types: orderTypes,
            grn_types: grnTypes
        })
    })
    .then(response => response.json())
    .then(data => {
        let tbody = document.querySelector('#pendingGrngrnaddModal tbody');
        let tolerance = data.grnTolerance || 1.02;
        tbody.innerHTML = '';
        console.log(data.grnTolerance);
        data.orders.forEach(item => {
            var maxGrnQty = parseFloat(((parseFloat(item.order_quantity || 0) * tolerance) - parseFloat(item.grn_entered || 0)).toFixed(3));
            const url = item.baseManualPoUrl.replace('__ID__', item.id);
            let row = `
                <tr>
                    <td>${item.product_name}</td>
                    <td>${item.specification}</td>
                    <td>${item.size}</td>
                    <td>${item.order_number}</td>
                    <td>${
                            item.order_type !== 'manual_order'
                                ? `${item.rfq_number || ''}<input type="hidden" id="grn_type" name="grn_type[]" value="1">`
                                : `- <input type="hidden" id="grn_type" name="grn_type[]" value="4">`
                        }
                    </td>
                    <td>${item.order_date}</td>
                    <td>${item.order_quantity}</td>
                    <td>${item.vendor_name}</td>
                    <td>${item.grn_entered}</td>
                    <td>${item.rate}</td>
                    <td>${item.rate_in_local_currency === '1' ? item.grn_buyer_rate : ''}</td>
                    <td>
                       <input type="text" id="grn_qty" class="grn_quantity_input form-control bg-white text-center w-100" name="grn_qty[]" data-max="${maxGrnQty}" maxlength="20" min="0" step="any" style="width: 200px;">
                        <input type="hidden" name="rate[]" class="rate" value="${item.rate || ''}">
                        <input type="hidden" name="inventory_id[]" class="inventory_id" value="${item.inventory_id || ''}">
                        <input type="hidden" name="order_id[]" class="order_id" value="${item.id || ''}">
                        <input type="hidden" name="gst_percentage[]" class="gst_percentage" value="${item.gst_percentage}">
                        <input type="hidden" name="buyer_rate[]" class="buyer_rate" value="${item.grn_buyer_rate || ''}">
                        <input type="hidden" name="po_number[]"value="${item.order_number || ''}">
                        <input type="hidden" name="order_qty[]" value="${item.order_quantity || ''}">
                        <input type="hidden" name="vendor_name[]" value="${item.vendor_name || ''}">
                        <input type="hidden" name="grn_entered[]" value="${item.grn_entered || '0'}"></td>
                        <input type="hidden" name="rate_in_local_currency[]" value="${item.grn_buyer_rate || '0'}"></td>
                    </td>
                    <td><input type="text" class="form-control dateTimePickerStart bg-white text-center w-80 grn-date" id="grn_date" name="grn_date[]" maxlength="50" >
                        <input type="hidden" class="grn-min-date" name="grn_min_date[]" value="${item.order_date || ''}">
                    </td>
                    <td><input type="text" class="form-control bg-white text-center w-100 smt_numeric_only gst" name="gst[]" maxlength="20" readonly style="background-color: #dbeff1 !important;"></td>
                    <td> <span data-id="${item.id}">
                            <a href="${url}" target="_blank" rel="noopener noreferrer">View ${item.order_type === 'manual_order' ? 'Manual PO' : 'PO'}</a>
                        </span>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        $('#pendingGrngrnaddModal').modal('show');
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
    })
    .catch(() => {
        toastr.error('Failed to load data.');
    });
}

