
$(document).ready(function() {
    if (typeof $.fn.DataTable !== "function") {
        toastr.error("DataTables is not loaded! Check script order!");
        return;
    }
    report_list_data();
    $('#branch_id, #search_product_name, #search_category_id, #search_is_active').on('change keyup', function() {
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
                // Indent Qty (col 7)
                $('td', row).eq(7).css({
                    "background-color": "#d1f7d6",
                    "color": "#000",
                    "font-weight": "bold",
                    "white-space": "nowrap"
                });
                // RFQ Qty (col 8)
                $('td', row).eq(8).css({
                    "white-space": "nowrap"
                });
            },
        ajax: {
            url: indentreportlistdataurl,
            data: function (d) {
                d.branch_id                 =   $('#branch_id').val();
                d.search_product_name       =   $('#search_product_name').val();
                d.search_category_id        =   $('#search_category_id').val();
                d.search_is_active          =   $('#search_is_active').val();
                d.from_date                 =   $('#from_date').val();
                d.to_date                   =   $('#to_date').val();
            },
        },
        columns: [
                { data: 'IndentNumber' },
                { data: 'product', name: 'product' },
                { data: 'buyer_product_name', name: 'buyer_product_name' },
                { data: 'specification', name: 'specification' },
                { data: 'size', name: 'size' },
                { data: 'inventory_grouping', name: 'inventory_grouping' },
                { data: 'users', name: 'users' },
                { data: 'indent_qty', name: 'indent_qty' },
                { data: 'rfq_qty', name: 'rfq_qty' },
                { data: 'uom', name: 'uom' },
                { data: 'remarks', name: 'remarks' },
                { data: 'status', name: 'status' },
                { data: 'updated_at', name: 'updated_at' }
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
//     let url= exportreportlistindenturl;
//     let data= {
//             _token                      :   $('meta[name="csrf-token"]').attr("content"),
//             branch_id                   :   $('#branch_id').val(),
//             search_product_name         :   $('#search_product_name').val(),
//             search_category_id          :   $('#search_category_id').val(),
//             search_is_active            :   $('#search_is_active').val(),
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
        headers: ['Indent Number','Branch','Product Name','Our Product Name','Specification','Size','Inventory Grouping','User', 'Indent Quantity','UOM', 'Remarks','Status','Added Date',],
        totalUrl: exportTotalIndentreportDataurl,
        batchUrl: exportBatchIndentreportDataUrl,
        token: "{{ csrf_token() }}",
        exportName: "Indent_Report_",
        expButton: '#export',
        exportProgress: '#export-progress',
        progressText: '#progress-text',
        progress: '#progress',
        fillterReadOnly: '.fillter-form-control',
        getParams: function() {
            $('#search_product_name, #search_category_id, #from_date,#to_date,#branch_id,#search_is_active').prop('disabled', true);
            $('button').prop('disabled', true);
            return {
                branch_id                   :   $('#branch_id').val(),
                search_product_name         :   $('#search_product_name').val(),
                search_category_id          :   $('#search_category_id').val(),
                search_is_active            :   $('#search_is_active').val(),
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
    $('#search_product_name, #search_category_id,#from_date, #to_date, #branch_id,#search_is_active')
        .prop('disabled', false);
    $('button').prop('disabled', false);
}
// Excel Export Script
$('#showreportmodal').click(function (e) {
    e.preventDefault();
    $('#reportModal').modal('show');
});

function getMultiIndentData() {

    // 🔹 collect selected checkbox ids
    // let ids = [];
    let idArray = [];

    $('.inventory_chkd:checked').each(function () {
        // ids.push($(this).val());
        idArray.push({
        inventory_id: $(this).val(),
        indent_id: $(this).data('indent-id')
    });
    });

    // 🔹 validation
    // if (ids.length === 0) {
    if (idArray.length === 0) {
        toastr.warning('Please select an indent.');
        return;
    }

    // 🔹 build URL (comma-separated ids)
    // const indentId = ids.join(',');
    // const url = postmultiindentdataurl.replace('__ID__', indentId);

    fetch(postmultiindentdataurl, {
        method: 'POST',
        // method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr("content")
        },
        body: JSON.stringify({
            data_array: idArray
        })
    })
    .then(res => res.json())
    .then(response => {

        if (response.status !== 1) {
            toastr.error(response.message ?? 'Something went wrong');
            return;
        }

        const rowsData = response.data; // ← ARRAY of indents
        let rowsHtml = "";

        // 🔹 tooltip-safe text
        const escapeHtml = (text) =>
            String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");

        // 🔹 build table rows
        rowsData.forEach((data) => {

            let specification = data.specification ?? '';
            let size = data.size ?? '';

            let specHtml = specification.length > 10
                ? `${specification.substring(0, 10)}...
                   <i class="bi bi-info-circle-fill ms-1"
                      data-bs-toggle="tooltip"
                      title="${escapeHtml(specification)}"></i>`
                : specification;

            let sizeHtml = size.length > 10
                ? `${size.substring(0, 10)}...
                   <i class="bi bi-info-circle-fill ms-1"
                      data-bs-toggle="tooltip"
                      title="${escapeHtml(size)}"></i>`
                : size;

            rowsHtml += `
                <tr>
                    <input type="hidden" name="inventory_id[]" value="${data.inventory_id}">
                    <input type="hidden" name="indent_id[]" value="${data.id}">

                    <td>${data.product_name}</td>
                    <td>${specHtml}</td>
                    <td>${sizeHtml}</td>
                    <td>${data.uom_name}</td>

                    <td>
                        <input type="text"
                            class="form-control bg-white specialCharacterAllowed"
                            name="remarks[]"
                            value="${data.remarks ?? ''}"
                            maxlength="100">
                    </td>

                    <td>
                        <input type="hidden" name="min_indent_qty[]" value="${data.min_indent_qty}">
                        <input type="text"
                            class="form-control bg-white smt_numeric_only_qty"
                            name="indent_qty[]"
                            value="${data.indent_qty}"
                            maxlength="10">
                    </td>
                </tr>
            `;
        });

        // 🔹 inject rows
        $('#indent_tbody').html(rowsHtml);

        // 🔹 use FIRST indent for modal-level actions
        const first = rowsData[0];

        $('#indent_id').val(first.id);
        $('#indent_inventory_id').val(first.inventory_id);

        // 🔹 modal title & button text
        $('#addeditindentModalLabel').html('<i class="bi bi-pencil"></i> Edit Indent');
        $('.save_indent_button').html((_, html) => html.replace('Save', 'Update'));

        // 🔹 remove old buttons
        $('.delete_indent_button, .approve_indent_button').remove();

        // 🔹 approve button
        if (first.showApproveButton === '1') {
            $('.save_indent_button').after(`
                <button type="button"
                    class="ra-btn btn-primary text-uppercase text-nowrap font-size-11 ms-2 approve_indent_button">
                    <i class="bi bi-check"></i> Approve Indent
                </button>
            `);
        }

        // 🔹 delete button
        if (first.showDelete) {
            const target = $('.approve_indent_button').length
                ? $('.approve_indent_button')
                : $('.save_indent_button');

            target.after(`
                <button type="button"
                    class="ra-btn btn-danger text-uppercase text-nowrap font-size-11 ms-2 delete_indent_button">
                    <i class="bi bi-trash"></i> Delete Indent
                </button>
            `);
        }

        // 🔹 show modal + enable tooltip
        $('#indentModal').modal('show');
        $('[data-bs-toggle="tooltip"]').tooltip();
    })
    .catch(() => {
        toastr.error('Server error. Please try again.');
    });
}



//active rfq pop up
function activeIndentRfqPopUP(indentId) {
    const url = activeRfqUrl.replace('__ID__', indentId);
    fetch(url, {
    method: 'GET',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
    }})
        .then(response => response.json())
        .then(data => {
            if (data.status === 1) {
                $('#rfqdetailsTable tbody').empty();

                data.data.forEach(function (item) {
                    let viewUrl = activeRfqDetailsbyIdUrl.replace('__RFQ_ID__', item.rfq_id);
                    let row = `
                        <tr>
                            <td>${item.rfq_no}</td>
                            <td>${item.rfq_date}</td>
                            <td>${item.rfq_closed}</td>
                            <td>${item.used_indent_qty}</td>
                            <td>
                                <a href="${viewUrl}" target="_blank">
                                    <i class="bi bi-eye-fill"></i> View Details
                                </a>
                            </td>
                        </tr>
                    `;
                    $('#rfqdetailsTable tbody').append(row);
                });

                $("#ActiveRfqDetailsModal").modal("show");
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching RFQ details:', error);
            toastr.error('Something went wrong.');
        });
}


