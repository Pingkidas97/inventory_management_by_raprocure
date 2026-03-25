$(document).ready(function() {
    if (typeof $.fn.DataTable !== "function") {
        toastr.error("DataTables is not loaded! Check script order!");
        return;
    }
    report_list_data();
     $('#branch_id, #search_product_name, #search_category_id, #search_return_type,#search_buyer_id').on('change keyup', function() {
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
        createdRow: function(row) {
                    // Indent Qty (col 9)
                    $('td', row).eq(9).css({
                        "background-color": "#d1f7d6",
                        "color": "#000",
                        "font-weight": "bold",
                        "white-space": "nowrap"
                    });
            },
        ajax: {
            url: getStockReturnlistdataUrl,
            data: function (d) {
                d.branch_id                 =   $('#branch_id').val();
                d.search_product_name       =   $('#search_product_name').val();
                d.search_buyer_id           =   $('#search_buyer_id').val();
                d.search_return_type        =   $('#search_return_type').val();
                d.search_category_id        =   $('#search_category_id').val();
                d.from_date                 =   $('#from_date').val();
                d.to_date                   =   $('#to_date').val();
            },
        },
        columns: [
                { data: 'stock_number', name: 'stock_no' },
                { data: 'product', name: 'product' },
                { data: 'buyer_product_name', name: 'buyer_product_name' },
                { data: 'specification', name: 'specification'},
                { data: 'size', name: 'size' },
                { data: 'inventory_grouping', name: 'inventory_grouping' },
                { data: 'stock_return_type', name: 'stock_return_type' },
                { data: 'added_bY', name: 'updated_by' },
                { data: 'added_date', name: 'updated_at' },
                { data: 'quantity', name: 'qty' },
                { data: 'uom', name: 'uom' },
                { data: 'remarks', name: 'remarks' },
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

// $(document).on('click', '#export', function() {
//     let btn = $(this);
//     let url= getStockReturnExportUrl;
//     let data= {
//             _token                      :   $('meta[name="csrf-token"]').attr("content"),
//             branch_id                   :   $('#branch_id').val(),
//             search_product_name         :   $('#search_product_name').val(),
//             search_category_id          :   $('#search_category_id').val(),
//             search_return_type          :   $('#search_return_type').val(),
//             search_buyer_id             :   $('#search_buyer_id').val(),
//             from_date                   :   $('#from_date').val(),
//             to_date                     :   $('#to_date').val()
//         };
//     inventoryFileExport(btn,url,data,deleteExcelUrl);
// });
// Excel Export Script
$(document).ready(function() {
    $('#export').on('click', function() {
        const exporter = new Exporter({
            chunkSize: 100,
            rowLimitPerSheet: 200000,
            headers: ['Stock Number','Branch','Product Name','Our Product Name','Specification','Size','Inventory Grouping','Return Type','Added BY','Added Date','Quantity','UOM','Remarks'],
            totalUrl: exportTotalStockReturnUrl,
            batchUrl: exportBatchStockReturnUrl,
            token: "{{ csrf_token() }}",
            exportName: "Stock_Return_Report_",
            expButton: '#export',
            exportProgress: '#export-progress',
            progressText: '#progress-text',
            progress: '#progress',
            fillterReadOnly: '.fillter-form-control',
            getParams: function() {
                $('#search_product_name, #search_category_id, #from_date,#to_date,#branch_id,#search_return_type,#search_buyer_id').prop('disabled', true);
                $('button').prop('disabled', true);
                return {
                    branch_id                   :   $('#branch_id').val(),
                    search_product_name         :   $('#search_product_name').val(),
                    search_category_id          :   $('#search_category_id').val(),
                    search_return_type          :   $('#search_return_type').val(),
                    search_buyer_id             :   $('#search_buyer_id').val(),
                    from_date                   :   $('#from_date').val(),
                    to_date                     :   $('#to_date').val()
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
    $('#search_product_name, #search_category_id, #from_date,#to_date,#branch_id,#search_return_type,#search_buyer_id')
        .prop('disabled', false);
    $('button').prop('disabled', false);
}
// Excel Export Script

 // Open Report Modal
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
$('#to_date').datepicker({
    dateFormat: 'dd/mm/yy'
}).datepicker('disable');


$(document).on('click', '.stock-return-details', function () {
    var id = $(this).data('id');

    $.ajax({
        url: fetchStockReturnRowdataurl,
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            id: id
        },
        success: function (response) {
            $('#stock_return_id').val(response.id);
            $('#tdProductName').text(response.product_name);
            $('#tdProductSpecification').html(response.specification);
            $('#tdProductSize').html(response.size);
            $('#tdProductUom').text(response.uom);
            $('#tdAddedQuantity').text(response.qty);

            $('#tdremarks').val(response.remarks);
            $('#stock_vendor_name').val(response.stock_vendor_name);
            $('#stock_vehicle_no_lr_no').val(response.stock_vehicle_no_lr_no);
            $('#stock_debit_note_no').val(response.stock_debit_note_no);
            $('#stock_frieght').val(response.stock_frieght);
            $('#stockReturnQtyDetailsModal').modal('show');
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
