$(document).ready(function() {
    if (typeof $.fn.DataTable !== "function") {
        toastr.error("DataTables is not loaded! Check script order!");
        return;
    }
    report_list_data();
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
                    //  Qty (col 12)
                    $('td', row).eq(12).css({
                        "background-color": "#d1f7d6",
                        "color": "#000",
                        "font-weight": "bold",
                        "white-space": "nowrap"
                    });
                    //stock return Qty (col 10)
                    $('td', row).eq(10).css({
                        "white-space": "nowrap"
                    });
                    //pending GRN Qty (col 11)
                    $('td', row).eq(11).css({
                        "white-space": "nowrap"
                    }); 
            },
        ajax: {
            url: pendingGrnStockReturnreportlistdataurl,
            data: function (d) {
                d.branch_id                 =   $('#branch_id').val();
                d.search_product_name       =   $('#search_product_name').val();
                d.search_category_id        =   $('#search_category_id').val();
                d.from_date                 =   $('#from_date').val();
                d.to_date                   =   $('#to_date').val();
            },
        },
        columns: [
            { data: 'grn_no', name: 'grn_no' },
            { data: 'stock_no', name: 'stock_no' },
            { data: 'product_name', name: 'product_name' },
            { data: 'buyer_product_name', name: 'buyer_product_name' },
            { data: 'specification', name: 'specification' },
            { data: 'size', name: 'size' },
            { data: 'stock_vendor_name', name: 'stock_vendor_name' },
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
//     let url= exportreportlistPendingGrnStockReturnurl;
//     let data= {
//              _token                      :   $('meta[name="csrf-token"]').attr("content"),
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
            headers: ['GRN Number','Branch','Stock Return No','Product Name','Our Product Name','Specification','Size','Vendor Name','Added By','Added Date','UOM','Stock Return Quantity','Total GRN Quantity','Pending GRN Quantity'],
            totalUrl: exportTotalPendingGrnStockReturnReporturl,
            batchUrl: exportBatchPendingGrnStockReturnReporturl,
            token: "{{ csrf_token() }}",
            exportName: "Pending_Grn_For_Stock_Return_Report_",
            expButton: '#export',
            exportProgress: '#export-progress',
            progressText: '#progress-text',
            progress: '#progress',
            fillterReadOnly: '.fillter-form-control',
            getParams: function() {
                $('#search_product_name, #search_category_id, #from_date,#to_date,#branch_id').prop('disabled', true);
                $('button').prop('disabled', true);
                return {
                    branch_id                   :   $('#branch_id').val(),
                    search_product_name         :   $('#search_product_name').val(),
                    search_category_id          :   $('#search_category_id').val(),
                    from_date                   :   $('#from_date').val(),
                    to_date                     :   $('#to_date').val(),
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
    $('#search_product_name, #search_category_id, #from_date,#to_date,#branch_id')
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





