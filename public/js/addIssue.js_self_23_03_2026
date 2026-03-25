    //====show issue modal===
    window.show_issue_modal = function () {
        checkPermissionAndExecute('ISSUED', 'add', '1', function () {
            let checkedItems = $("input[name='inv_checkbox[]']:checked");
            if (checkedItems.length === 1) {
                let inventoryId = checkedItems.val();
                $.ajax({
                    url: getInventoryDetailsForIssueUrl,
                    type: "POST",
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: {
                        inventory_id: inventoryId,
                    },
                    success: function (response) {
                        if (response.status === 1) {
                            var data = response.data;
                            $('#IssueModal').find('input, select, textarea').val('').trigger('change');
                            $('#issue_inventory_id').val(inventoryId);
                            $("#issue_product_name").html(data.product_name);
                            $("#issue_specification").html(
                                    data.specification && data.specification.length > 10
                                        ? `
                                            ${data.specification.substring(0, 10)}...
                                            <i class="bi bi-info-circle-fill ms-1" data-bs-toggle="tooltip" title="${data.specification.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;")}"></i>
                                        `
                                        : data.specification ?? ''
                                );
                            $("#issue_size").html(
                                    data.size && data.size.length > 10
                                        ? `
                                            ${data.size.substring(0, 10)}...
                                            <i class="bi bi-info-circle-fill ms-1" data-bs-toggle="tooltip" title="${data.size.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;")}"></i>
                                        `
                                        : data.size ?? ''
                                );

                            $("#issue_uom").html(data.uom_name);
                            $('#issue_issuedTo').empty().append('<option value="">Select Issued To</option>');
                            data.issuedtoList.forEach(item => {
                            $('#issue_issuedTo').append(`<option value="${item.id}">${item.name}</option>`);
                            });

                            const $issueFrom = $('#issue_from'), $maxQty = $('#issue_max_quantity'), stockMap = {}, stockQtyMap={};

                            $issueFrom.empty();
                            data.issuefromList.forEach(({id, label, stock,stockQty}) => {
                                stockMap[id] = stock;
                                stockQtyMap[id] = stockQty;
                                $issueFrom.append(`<option value="${id}">${label}</option>`);
                            });

                        $issueFrom.off().on('change', function (e) {
                                const selectedId = e.target.value;
                                const stock = stockMap[selectedId] || 0;
                                const stockQty = stockQtyMap[selectedId] || 0;
                                const qty = parseFloat($('#qty').val()) || 0;

                                if (qty > stock) {
                                    $('#qty').val(stockQty);
                                }

                                $maxQty.html(stock);
                                $('#IssueStock').val(stockQty);
                            }).trigger('change');
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
                        } else if (xhr.responseText) {
                            try {
                                const res = JSON.parse(xhr.responseText);
                                if (res.message) {
                                    errorMsg = res.message;
                                }
                            } catch (e) {
                                // Ignore JSON parse errors
                            }
                        }

                        toastr.error(errorMsg);
                    }
                });

            } else {
                if (checkedItems.length > 1) {
                    toastr.error('You may select only one inventory.');
                } else {
                    toastr.error('Please select an inventory.');
                }
            }
        });
    };
    //====show issue modal===
