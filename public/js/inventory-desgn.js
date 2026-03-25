$(document).ready(function () {
    // Open Add Inventory Modal
    $('#addInventoryBtn').click(function (e) {
        e.preventDefault();
        $('#inventoryModalLabel').text('Add Inventory');
        $('#inventoryForm')[0].reset();
        $('#inventoryId').val('');
        $('#inventoryModal').modal('show');
    });

    // Open Edit Inventory Modal
    $('#editInventoryBtn').click(function (e) {
        e.preventDefault();
        if($("input[name='inv_checkbox[]']:checked").length== 1){
            var id = $('input[name="inv_checkbox[]"]:checked').val();
            $.get('/inventory/' + id + '/edit', function (response) {
                if(response.success){
                    let data    =   response.data;
                    $('#inventoryModalLabel').text('Edit Inventory');
                    $('#inventoryId').val(data.id);
                    $('#inventory_product_name').val(data.product_name);
                    $('#product_id').val(data.product_id);
                    $('#product_specification').val(data.specification);
                    $('#product_size').val(data.size);
                    $('#opening_stock').val(data.opening_stock);
                    $('#product_uom option[value="'+data.uom_id+'"]').attr("selected", "selected");
                    $('#stock_price').val(data.stock_price);
                    $('#product_brand').val(data.product_brand);
                    $('#buyer_product_name').val(data.buyer_product_name);
                    $('#inventory_grouping').val(data.inventory_grouping);
                    $('#inventory_type option[value="'+data.inventory_type_id+'"]').attr("selected", "selected");
                    $('#indent_min_qty').val(data.indent_min_qty);
                    $('#inventoryModal').modal('show');
                }
                else{
                    toastr.error('No Inventory Found');
                }
            });
        }
        else{
            if($("input[name='inv_checkbox[]']:checked").length> 1){
                toastr.error('You may select only one inventory.');
            }
            else{
                toastr.error('Select at least one inventory');
            }
        }
    });
    //===numeric only value
    $(document).on('keypress', '.smt_numeric_only', function (e) {
        let charCode = e.which || e.keyCode;

        // Prevent "e", spaces, and non-numeric characters except "."
        if (charCode === 69 || charCode === 101 || charCode === 32) { // "E", "e", and space
            return false;
        }

        let character = String.fromCharCode(charCode);
        let inputValue = $(this).val();

        // Allow only one decimal point
        if (character === '.' && inputValue.includes('.')) {
            return false;
        }

        // Allow numbers and one decimal point
        return /[0-9.]$/.test(character);
    }).on('paste', function (e) {
        let pastedData = e.originalEvent.clipboardData.getData('text');
        let numericValue = pastedData.replace(/[^0-9.]/g, '');

        // Prevent multiple decimals
        let decimalCount = (numericValue.match(/\./g) || []).length;
        if (decimalCount > 1) {
            numericValue = numericValue.replace(/\.(?=.*\.)/g, ''); // Remove extra decimal points
        }

        $(this).val(numericValue);
        e.preventDefault();
    }).on('blur', '.smt_numeric_only', function (e) {
        let value = e.target.value;

        // Remove extra decimal points or invalid characters
        value = value.replace(/[^0-9.]/g, '');

        // Ensure only two decimal places
        if (value.includes('.')) {
            let [integerPart, decimalPart] = value.split('.');
            decimalPart = decimalPart.slice(0, 2); // Keep only two decimals
            value = integerPart + (decimalPart ? '.' + decimalPart : '');
        }

        e.target.value = value;
    });
    //===numeric only qty value
    $(document).on('keypress', '.smt_numeric_only_qty', function (e) {
        let charCode = e.which || e.keyCode;

        // Prevent "e", spaces, and non-numeric characters except "."
        if (charCode === 69 || charCode === 101 || charCode === 32) { // "E", "e", and space
            return false;
        }

        let character = String.fromCharCode(charCode);
        let inputValue = $(this).val();

        // Allow only one decimal point
        if (character === '.' && inputValue.includes('.')) {
            return false;
        }

        // Allow numbers and one decimal point
        return /[0-9.]$/.test(character);
    }).on('paste', function (e) {
        let pastedData = e.originalEvent.clipboardData.getData('text');
        let numericValue = pastedData.replace(/[^0-9.]/g, '');

        // Prevent multiple decimals
        let decimalCount = (numericValue.match(/\./g) || []).length;
        if (decimalCount > 1) {
            numericValue = numericValue.replace(/\.(?=.*\.)/g, ''); // Remove extra decimal points
        }

        $(this).val(numericValue);
        e.preventDefault();
    }).on('blur', '.smt_numeric_only_qty', function (e) {
        let value = e.target.value;

        // Remove extra decimal points or invalid characters
        value = value.replace(/[^0-9.]/g, '');

        // Ensure only two decimal places
        if (value.includes('.')) {
            let [integerPart, decimalPart] = value.split('.');
            decimalPart = decimalPart.slice(0, 3); // Keep only three decimals
            value = integerPart + (decimalPart ? '.' + decimalPart : '');
        }

        e.target.value = value;
    });
    //===numeric only qtyvalue
    //====show indent modal===
    window.show_indent_modal = function () {
        let checkedItems = $("input[name='inv_checkbox[]']:checked");
        if (checkedItems.length === 1) {
            let inventoryId = checkedItems.val();
            $.ajax({
                url: getInventoryDetailsUrl,
                type: "POST",
                data: {
                    inventory_id: inventoryId,
                    _token: $('meta[name="csrf-token"]').attr("content") // Secure CSRF token retrieval
                },
                success: function (response) {
                    if (response.status === 1) {
                        var data = response.data;
                        $('#indent_inventory_id').val(inventoryId);
                        $("#indent_product_name").val(data.product_name);
                        $("#indent_specification").val(data.specification);
                        $("#indent_size").val(data.size);
                        $('#indent_uom option[value="'+data.uom_id+'"]').attr("selected", "selected");
                        $("#indent_remarks").val('');
                        $("#indent_qty").val('');
                        //==show indent modal
                        $('#addeditindentModalLabel').text('Add Indent');
                        $("#indentModal").modal("show");
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
    //====show indent modal===

    //====show bulk indent modal===
    window.show_bulk_indent_modal = function () {
        
        let inventoryId = checkedItems.val();
        $.ajax({
            url: getInventoryDetailsUrl,
            type: "POST",
            data: {
                inventory_id: inventoryId,
                _token: $('meta[name="csrf-token"]').attr("content") // Secure CSRF token retrieval
            },
            success: function (response) {
                if (response.status === 1) {
                    var data = response.data;
                    $('#indent_inventory_id').val(inventoryId);
                    $("#indent_product_name").val(data.product_name);
                    $("#indent_specification").val(data.specification);
                    $("#indent_size").val(data.size);
                    $('#indent_uom option[value="'+data.uom_id+'"]').attr("selected", "selected");
                    $("#indent_remarks").val('');
                    $("#indent_qty").val('');
                    //==show indent modal
                    $('#addeditindentModalLabel').text('Add Indent');
                    $("#indentModal").modal("show");
                } else {
                    toastr.error(response.message);
                }
            },
            error: function () {
                toastr.error("Something went wrong while fetching inventory details.");
            }
        });

       
    };
    //====show bulk indent modal===

    //===open indent div==//
    //function open_indent_tds(inventory) {
    $(document).on("click", ".open_indent_tds", function (event) {
        let inventory = $(this).attr("tab-index");
        if (inventory) {
            event.preventDefault();
            $.ajax({
                url: postindentlisturl, // Laravel route
                type: "POST",
                dataType: "json",
                data: {
                    inventory: inventory,
                    _token: $('meta[name="csrf-token"]').attr("content") // Secure CSRF token retrieval
                },
                beforeSend: function () {

                },
                success: function (response) {
                    var html = "";
                    $(".extra_tr_" + inventory).remove();
                    $("#header_" + inventory).remove();

                    if (response.status == 1) {
                        var responsedata = response.resp;
                        $("#minus_" + inventory).css("display", "inline");
                        $("#plus_" + inventory).css("display", "none");


                        html += '<tr class="old" id="header_' + inventory + '">';
                        html += '<td colspan="14" class="p-0">';
                        html += '<table class="w-100 append_table">';

                        html += '<thead>';
                        html += '<tr role="row" class="odd">';
                        html += "<th>&nbsp;&nbsp;&nbsp;</th>";
                        html += '<th colspan="2">Added Date</th>';
                        html += '<th >Added By</th>';
                        html += '<th >Updated By</th>';
                        html += '<th colspan="3">Remarks</th>';
                        html += '<th colspan="2">Indent Number</th>';
                        html += '<th>Indent Quantity</th>';
                        html += '<th>Status</th>';
                        html += "<th></th><th></th></tr></thead>";
                        html += "<tbody>";
                        responsedata.forEach((p_data) => {
                            var formattedDate = new Date(p_data.created_at).toLocaleString("en-US", {
                                year: "numeric",
                                month: "long",
                                day: "numeric",
                                hour: "numeric",
                                minute: "numeric",
                                hour12: true,
                            });

                            var final_qty = parseFloat(p_data.indent_qty);
                            var remarks = p_data.remarks ? p_data.remarks : "";

                            html += '<tr role="row" class="odd">';
                            html += "<td></td>";
                            html += "<td colspan='2'>" + formattedDate + "</td>";
                            html += "<td>" + (p_data.created_by || "") + "</td>";
                            html += "<td>" + (p_data.updated_by || "") + "</td>";

                            html += remarks.length > 40
                                ? `<td colspan="3">${remarks.substr(0, 40)}<i class="bi bi-info-circle-fill" title="${remarks}"></i></td>`
                                : `<td colspan="3">${remarks}</td>`;

                            html += "<td colspan='2'>" + p_data.inventory_unique_id + "</td>";

                            if (p_data.is_active == 1) {
                                var iseditindent = 1;
                                if(iseditindent){
                                    html+=`<td><span class="indent_qty_quant show_edit_indent_model" style="cursor: pointer" id="indent_qty_sel_${p_data.id}" data-indent="${p_data.id}">${final_qty.toFixed(3)}</span></td>`;
                                }
                                else{
                                    html+=`<td><span class="indent_qty_quant" style="cursor: pointer" id="indent_qty_sel_${p_data.id}" data-indent="${p_data.id}">${final_qty.toFixed(3)}</span></td>`;
                                }
                                html += `<td>Approved</td>`;
                            } else {
                                html += `<td><span class="indent_qty_quant show_edit_indent_model" data-indent="${p_data.id}">${final_qty.toFixed(3)}</span></td>`;
                                html += `<td>Unapproved</td>`;
                            }

                            html += "<td></td><td></td></tr>";
                        });
                        html += "</tbody></table></td></tr>";

                        $(".accordion_parent_" + inventory).parent().parent().after(html);
                    } else {
                        $("#minus_" + inventory).hide();
                        $("#plus_" + inventory).show();
                        toastr.error(response.message);
                    }
                },
                error: function () {
                    toastr.error("Something Went Wrong..");
                },
            });
        }
    });
    //===open indent div==//

    //===Close indent div==//
    $(document).on("click", ".close_indent_tds", function (event) {
        let inventory = $(this).attr("tab-index");
        if (inventory) {
            event.preventDefault();
            $(".extra_tr_" + inventory).remove();
            $("#header_" + inventory).remove();
            $("#plus_" + inventory).css("display", "inline");
            $("#minus_" + inventory).css("display", "none");
        }
    });
    //===Close indent div==//

    //===indent modal open in edit mode====//
    $(document).on("click", ".show_edit_indent_model", function (event) {
        let indentId = $(this).data("indent");
        if (!indentId) {
            toastr.error("Invalid Indent ID");
            return;
        }

        $.ajax({
            url: postindentdataurl, // Laravel route for fetching indent data
            type: "POST",
            data: {
                indent_id: indentId,
                _token: $('meta[name="csrf-token"]').attr("content") // Secure CSRF token retrieval
            },
            beforeSend: function () {

            },
            success: function (response) {
                if (response.status == 1) {
                    var data = response.data;

                    // Populate modal fields
                    $("#indent_id").val(data.id);
                    $("#indent_product_name").val(data.product_name);
                    $("#indent_specification").val(data.specification);
                    $("#indent_size").val(data.size);
                    $('#indent_uom option[value="'+data.uom_id+'"]').attr("selected", "selected");
                    $("#indent_remarks").val(data.remarks);
                    $("#indent_qty").val(data.indent_qty);
                    $("#indent_inventory_id").val(data.inventory_id);
                    // Show the modal
                    $('#addeditindentModalLabel').text('Edit Indent');
                    $("#indentModal").modal("show");
                } else {
                    toastr.error(response.message);
                }
            },
            error: function () {
                toastr.error("Something went wrong while fetching indent details.");
            }
        });
    });
    //===indent modal in edit mode===//
});
