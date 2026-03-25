    //====show issue modal===
    window.show_stock_return_modal = function () {
        let checkedItems = $("input[name='inv_checkbox[]']:checked");
        if (checkedItems.length === 1) {
            let inventoryId = checkedItems.val();
            $.ajax({
                url: getInventoryDetailsForStockReturnUrl,
                type: "POST",
                data: {
                    inventory_id: inventoryId,
                    _token: $('meta[name="csrf-token"]').attr("content") // Secure CSRF token retrieval
                },
                success: function (response) {
                    if (response.status === 1) {
                        var data = response.data;
                        $('#StockReturnModal').find('input, select, textarea').val('').trigger('change');
                        $('#stock_return_inventory_id').val(inventoryId);
                        $("#stock_return_product_name").html(data.product_name);
                        $("#stock_return_specification").html(
                                data.specification && data.specification.length > 10
                                    ? `
                                        ${data.specification.substring(0, 10)}...
                                        <i class="bi bi-info-circle-fill ms-1" data-bs-toggle="tooltip" title="${data.specification.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;")}"></i>
                                    `
                                    : data.specification ?? ''
                            );
                        $("#stock_return_size").html(
                                data.size && data.size.length > 10
                                    ? `
                                        ${data.size.substring(0, 10)}...
                                        <i class="bi bi-info-circle-fill ms-1" data-bs-toggle="tooltip" title="${data.size.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;")}"></i>
                                    `
                                    : data.size ?? ''
                            );

                        $("#stock_return_uom").html(data.uom_name);

                        const $stockReturnFrom = $('#stock_return_from'), $maxQty = $('#stock_return_max_quantity'), stockMap = {},stockQtyMap={};

                        $stockReturnFrom.empty();
                        data.stockReturnfromList.forEach(({id, label, stock,stockQty}) => {
                            stockMap[id] = stock;
                            stockQtyMap[id] = stockQty;
                            $stockReturnFrom.append(`<option value="${id}">${label}</option>`);
                        });

                       $stockReturnFrom.off().on('change', function (e) {
                            const selectedId = e.target.value;
                            const stock = stockMap[selectedId] || 0;
                            const stockQty = stockQtyMap[selectedId] || 0;
                            const qty = parseFloat($('#stock_return_qty').val()) || 0;

                            if (qty > stock) {
                                $('#stock_return_qty').val(stockQty);
                            }

                            $maxQty.html(stock);
                            $('#stock_return_stock').val(stockQty);
                        }).trigger('change');

                        $('#stock_return_type').empty().append('<option value="">Select Issued Return Type</option>');
                        data.StockReturnType.forEach(item => {
                        $('#stock_return_type').append(`<option value="${item.id}">${item.name}</option>`);
                        });
                        $('#addeditStockReturnModalLabel').html('<i class="bi bi-pencil"></i> Add Return Stock');
                        $("#StockReturnModal").modal("show");
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
