    //====show issue modal===
    window.show_issue_modal = function () {

    checkPermissionAndExecute('ISSUED', 'add', '1', function () {

        let checkedItems = $("input[name='inv_checkbox[]']:checked");

        if (checkedItems.length > 0) {

            let inventoryIds = [];

            checkedItems.each(function () {
                inventoryIds.push($(this).val());
            });

            $.ajax({
                url: getInventoryDetailsForIssueUrl,
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    inventory_id: inventoryIds,
                },

                success: function (response) {

                    if (response.status === 1) {

                        let data = response.data;

                        // Reset modal
                        $('#IssueModal').find('input, select, textarea').val('').trigger('change');

                        // Store IDs
                        $('#issue_inventory_id').val(inventoryIds.join(','));

                        let tbody = $('#issue_table_body');
                        tbody.empty();

                        // LOOP ALL INVENTORIES
                        data.inventories.forEach((inventory, index) => {

                            let row = `
                                <tr>
                                    <td>${inventory.product_name}</td>
                                    <td>${inventory.specification ?? ''}</td>
                                    <td>${inventory.size ?? ''}</td>
                                    <td>${inventory.uom_name}</td>

                                    <td class="maxQty_${index}">
                                        ${inventory.issuefromList.length > 0 ? inventory.issuefromList[0].stock : 0}
                                    </td>

                                    <td>
                                        <select name="issued_to[]" class="form-select">
                                            <option value="">Select Issued To</option>
                                            ${data.issuedtoList.map(item =>
                                                `<option value="${item.id}">${item.name}</option>`
                                            ).join('')}
                                        </select>
                                    </td>

                                    <td>
                                        <input type="text" name="qty[]" class="form-control w-100 bg-white smt_numeric_only_qty bulkIssueQty qty_${index}" min="1" style="background-color: #fff;" maxlength="22" autocomplete="off">
                                    </td>

                                    <td>
                                        <select name="issued_from[]" class="form-select issueFrom_${index}">
                                            ${inventory.issuefromList.map(item =>
                                                `<option value="${item.id}" data-stock="${item.stock}" data-stockqty="${item.stockQty}">
                                                    ${item.label}
                                                </option>`
                                            ).join('')}
                                        </select>
                                    </td>

                                    <td>
                                        <input type="text" name="remarks[]" class="form-control">
                                    </td>
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-danger width-inherit issue_remove-row" title="Remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>

                                    <input type="hidden" name="inventory_ids[]" value="${inventory.inventory_id}">
                                </tr>
                                `;

                            tbody.append(row);
                        });

                        // Attach events per row
                        data.inventories.forEach((inventory, index) => {

                            let $issueFrom = $(`.issueFrom_${index}`);
                            let $qtyInput = $(`.qty_${index}`);
                            let $maxQty = $(`.maxQty_${index}`);

                            $issueFrom.on('change', function () {

                                let selected = $(this).find(':selected');

                                let stock = parseFloat(selected.data('stock')) || 0;
                                let stockQty = parseFloat(selected.data('stockqty')) || 0;
                                let qty = parseFloat($qtyInput.val()) || 0;

                                if (qty > stock) {
                                    $qtyInput.val(stockQty);
                                }

                                $maxQty.html(stock);
                            });

                        });

                        $('#addeditIssueModalLabel').html('<i class="bi bi-pencil"></i> Issued Details');

                        $("#IssueModal").modal("show");

                    } else {
                        toastr.error(response.message);
                    }
                },

                error: function (xhr) {
                    let errorMsg = "Something went wrong while fetching inventory details.";

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    toastr.error(errorMsg);
                }
            });

        } else {
            toastr.error('Please select at least one inventory.');
        }
    });
};
