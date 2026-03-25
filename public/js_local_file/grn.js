$(document).ready(function() {
    if (typeof $.fn.DataTable !== "function") {
        toastr.error("DataTables is not loaded! Check script order!");
        return;
    }
    report_list_data();
    $('#branch_id, #search_product_name, #search_category_id').on('change keyup', function() {
        $('#report-table').DataTable().ajax.reload();
    });
});

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
                    // Grn Qty (col 14)
                    $('td', row).eq(14).css({
                        "background-color": "#d1f7d6",
                        "color": "#000",
                        "font-weight": "bold",
                        "white-space": "nowrap"
                    });
                    // Amount (col 16)
                    $('td', row).eq(16).css({
                        "white-space": "nowrap"
                    });
            },
        ajax: {
            url: grnreportlistdataurl,
            data: function (d) {
                d.branch_id                 =   $('#branch_id').val();
                d.search_product_name       =   $('#search_product_name').val();
                d.search_category_id        =   $('#search_category_id').val();
                d.from_date                 =   $('#from_date').val();
                d.to_date                   =   $('#to_date').val();
            },
        },
        columns: [
                { data: 'grn_no', name: 'grn_n0' },
                { data: 'product', name: 'product' },
                { data: 'buyer_product_name', name: 'buyer_product_name' },
                { data: 'specification', name: 'specification' },
                { data: 'size', name: 'size' },
                { data: 'inventory_grouping', name: 'inventory_grouping' },
                { data: 'vendor_name', name: 'vendor_name' },
                { data: 'vendor_invoice_no', name: 'vendor_invoice_no' },
                { data: 'vehicle_no_lr_no', name: 'vehicle_no_lr_no' },
                { data: 'gross_wt', name: 'gross_wt' },
                { data: 'gst_no', name: 'gst_no' },
                { data: 'frieght_other_charges', name: 'frieght_other_charges' },
                { data: 'added_by', name: 'added_by' },
                { data: 'added_date', name: 'added_date' },
                { data: 'grn_qty', name: 'grn_qty' },
                { data: 'uom', name: 'uom' },
                { data: 'amount', name: 'amount' }
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
//     let url= exportreportlistGrnurl;
//     let data= {
//             _token                      :   $('meta[name="csrf-token"]').attr("content"),
//             branch_id                   :   $('#branch_id').val(),
//             search_product_name         :   $('#search_product_name').val(),
//             search_category_id          :   $('#search_category_id').val(),
//             from_date                   :   $('#from_date').val(),
//             to_date                     :   $('#to_date').val(),
//         };
//     inventoryFileExport(btn,url,data,deleteExcelUrl);
// });
// Excel Export Script
$(document).ready(function() {
    $('#export').on('click', function() {
        const exporter = new Exporter({
            chunkSize: 100,
            rowLimitPerSheet: 200000,
            headers: [
                'Grn Number','Purchase Order','Branch','Product Name',
                'Our Product Name','Specification','Size',
                'Inventory Grouping','Vendor Name',
                'Vendor Invoice No','Bill Date','Transporter Name',
                'Vehicle No/ LR No With Date','Gross Wt (kgs)',
                'GST ('+currency_symbol+')',
                'Frieght / Other Charges ('+currency_symbol+')',
                'Gate Entry Number with Date',
                'Added BY','Added Date',
                'GRN Quantity','UOM',
                'Rate('+currency_symbol+')',
                'Amounts ('+currency_symbol+')'
            ],
            totalUrl: exportTotalGrnReporturl,
            batchUrl: exportBatchGrnReporturl,
            token: "{{ csrf_token() }}",
            exportName: "Grn_Report_",
            expButton: '#export',
            exportProgress: '#export-progress',
            progressText: '#progress-text',
            progress: '#progress',
            fillterReadOnly: '.fillter-form-control',
            getParams: function() {
                $('#search_product_name, #search_category_id, #from_date,#to_date,#branch_id').prop('disabled', true);
                $('button').prop('disabled', true);
                return {
                    branch_id: $('#branch_id').val(),
                    search_product_name: $('#search_product_name').val(),
                    search_category_id: $('#search_category_id').val(),
                    from_date: $('#from_date').val(),
                    to_date: $('#to_date').val(),
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
    $('#search_product_name, #search_category_id,#from_date, #to_date, #branch_id')
        .prop('disabled', false);
    $('button').prop('disabled', false);
}
// Excel Export Script
$('#showreportmodal').click(function (e) {
    e.preventDefault();
    $('#reportModal').modal('show');
});
 //from date
$('#from_date').datepicker({
    dateFormat: 'dd/mm/yy',
    maxDate: 0, // today
    onSelect: function (selectedDate) {
        // Set min and max date for To Date
        $('#to_date').datepicker('option', 'minDate', selectedDate);
        $('#to_date').datepicker('option', 'maxDate', 0); // today
        $('#to_date').datepicker('enable');
    }
});
// Search button click
$('#searchBtn').on('click', function (e) {
    e.preventDefault();
    const fromDate = $('#from_date').val();
    const toDate = $('#to_date').val();

    if (fromDate && toDate) {
        $('#report-table').DataTable().ajax.reload();
    } else {
        toastr.error("Please select both From Date and To Date before searching.");
    }
});
// Initialize To Date (disabled until From Date is selected)
$('#to_date').datepicker({
    dateFormat: 'dd/mm/yy'
}).datepicker('disable');

$(document).on('click', '.grn-entry-details', function () {
    $('#orderGrnTableGrnReport').hide();
    $('#stockReturnGrnTableGrnReport').hide();
    var id = $(this).data('id');

    $.ajax({
        url: fetchGrnRowdataurl,
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            id: id
        },
        success: function (response) {
            if (response.order_no) {
                $('#orderGrnTableGrnReport').show();
                $('#stockReturnGrnTableGrnReport').hide();

                $('#tdOrderNo').text(response.order_no);
                $('#tdRfqNo').text(response.rfq_no);
                $('#tdOrderDate').text(response.order_date);
                $('#tdOrderQty').text(response.order_qty);
                $('#tdGrnNo').text(response.grn_no);
                $('#tdGrnDate').text(response.grn_date);
                $('#O_tdVendorName').text(response.vendor_name);
                $('#O_tdGrnQty').text(response.grn_qty);
                $('#O_invoice_number').val(response.vendor_invoice_no || '');
                $('#O_bill_date').val(response.bill_date || '');
                $('#O_transporter_name').val(response.transporter_name || '');
                $('#id').val(response.id || '');
                $('#O_gross_weight').val(response.gross_wt || '');
                $('#O_vehicle_lr_number').val(response.vehicle_no_lr_no || '');
                $('#O_gst').val(response.gst_no || '');
                $('#O_freight_charges').val(response.frieght_other_charges || '');
                $('#O_approved_by').val(response.approved_by || '');

            } else {
                $('#orderGrnTableGrnReport').hide();
                $('#stockReturnGrnTableGrnReport').show();

                $('#tdStockReturnNo').text(response.stock_return_no);
                $('#tdStockReturnDate').text(response.stock_return_date);
                $('#tdStockReturnQty').text(response.stock_return_qty);
                $('#s_tdVendorName').text(response.vendor_name);
                $('#s_tdGrnQty').text(response.grn_qty);
                $('#s_invoice_number').val(response.vendor_invoice_no || '');
                $('#s_bill_date').val(response.bill_date || '');
                $('#s_transporter_name').val(response.transporter_name || '');
                $('#id').val(response.id || '');
                $('#s_gross_weight').val(response.gross_wt || '');
                $('#s_vehicle_lr_number').val(response.vehicle_no_lr_no || '');
                $('#s_gst').val(response.gst_no || '');
                $('#s_freight_charges').val(response.frieght_other_charges || '');
                $('#s_approved_by').val(response.approved_by || '');
            }

            $('#grnQtyDetailsModal').modal('show');

        },

        error: function (xhr) {
            let msg = 'Something went wrong. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            toastr.error(msg);
        }
    });
});



