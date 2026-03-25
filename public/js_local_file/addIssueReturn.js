    //====show issue modal===
    window.show_issue_return_modal = function () {
        let checkedItems = $("input[name='inv_checkbox[]']:checked");
        if (checkedItems.length === 1) {
            let inventoryId = checkedItems.val();
            $.ajax({
                url: getInventoryDetailsForIssueReturnUrl,
                type: "POST",
                data: {
                    inventory_id: inventoryId,
                    _token: $('meta[name="csrf-token"]').attr("content") // Secure CSRF token retrieval
                },
                success: function (response) {
                    if (response.status === 1) {
                        var data = response.data;
                        $('#IssueReturnModal').find('input, select, textarea').val('').trigger('change');
                        $('#IssueReturn_inventory_id').val(inventoryId);
                        $("#issuereturn_product_name").html(data.product_name);
                        $("#issuereturn_specification").html(
                                data.specification && data.specification.length > 10
                                    ? `
                                        ${data.specification.substring(0, 10)}...
                                        <i class="bi bi-info-circle-fill ms-1" data-bs-toggle="tooltip" title="${data.specification.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;")}"></i>
                                    `
                                    : data.specification ?? ''
                            );
                        $("#issuereturn_size").html(
                                data.size && data.size.length > 10
                                    ? `
                                        ${data.size.substring(0, 10)}...
                                        <i class="bi bi-info-circle-fill ms-1" data-bs-toggle="tooltip" title="${data.size.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;")}"></i>
                                    `
                                    : data.size ?? ''
                            );

                        $("#issuereturn_uom").html(data.uom_name);
                        $('#issued_return_type').empty().append('<option value="">Select Issued Return Type</option>');
                        data.IssuedType.forEach(item => {
                           $('#issued_return_type').append(`<option value="${item.id}">${item.name}</option>`);
                        });

                        const $issueReturnFrom = $('#issue_return_from'), $maxQty = $('#issuereturn_max_quantity'), stockMap = {},stockQtyMap={};

                        $issueReturnFrom.empty();
                        data.issuefromList.forEach(({id, label, stock,stockQty}) => {
                            stockMap[id] = stock;
                            stockQtyMap[id] = stockQty;
                            $issueReturnFrom.append(`<option value="${id}">${label}</option>`);
                        });

                       $issueReturnFrom.off().on('change', function (e) {
                            const selectedId = e.target.value;
                            const stock = stockMap[selectedId] || 0;
                            const stockQty = stockQtyMap[selectedId] || 0;
                            const qty = parseFloat($('#IssuedReturnQty').val()) || 0;

                            if (qty > stock) {
                                $('#IssuedReturnQty').val(stockQty);
                            }

                            $maxQty.html(stock);
                            $('#IssuedReturnStock').val(stockQty);
                        }).trigger('change');
                        $('#addeditIssueReturnModalLabel').html('<i class="bi bi-pencil"></i> Issue Return Details');
                        $("#IssueReturnModal").modal("show");
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function () {
                    toastr.error("Something went wrong while fetching inventory details.");
                }
            });

        } else {
            if (checkedItems.length > 1) {
                toastr.error('You may select only one inventory.');
            } else {
                toastr.error('Please select an inventory.');
            }
        }
    };
    //====show issue modal===
