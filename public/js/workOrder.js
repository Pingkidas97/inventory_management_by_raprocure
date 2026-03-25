$(document).ready(function () {
    $('.workOrder').on('click', function (e) {
        e.preventDefault();
        if (selectedIds.length > 0) {
            $('#generate_work_order_form')[0].reset();
            $('#wo_vendor_user_id').val('');
            $("#wo_other_term_check").val('1').prop('checked', true);
            fetchInventoryDetails(selectedIds);
        } else {
            toastr.error('Please select an inventory details.');
            return;
        }
    });

    function fetchInventoryDetails(ids) {
        $.post(manualPOFetchURL, {
            ids: ids,
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function (response) {
            if (response.status === 'error') {
                return toastr.error(response.message);
            }
            $('#wo_vendor_name').val('');
            $('#vendorNameIdWo').nextAll('tr').remove();
            const table = $('#forworkOrderInventoryDetailsTableBody');
            table.empty();

            const inventories = response.data.inventories;
            const taxes = response.data.taxes;

            /* ================= CURRENCY SETUP ================= */

                const currencySymbol = response.data.currency_symbol || '';
                let currencySelect = document.getElementById('wo_currency_id');
                currencySelect.innerHTML = "";

                if (currencySymbol === "") {

                    currencySelect.innerHTML = `<option value="" disabled selected>Select Currency</option>`;

                    response.data.currencies.forEach(c => {
                        currencySelect.innerHTML += `
                            <option value="${c.id}" data-symbol="${c.currency_symbol}">
                                ${c.currency_symbol} (${c.currency_name})
                            </option>`;
                    });

                    $('.mo_currency_symbol').text('');

                } else {

                    response.data.currencies.forEach(c => {
                        let selected = (c.currency_symbol === currencySymbol) ? 'selected' : '';
                        currencySelect.innerHTML += `
                            <option value="${c.id}" ${selected} data-symbol="${c.currency_symbol}">
                                ${c.currency_symbol} (${c.currency_name})
                            </option>`;
                    });

                    $('.mo_currency_symbol').text(currencySymbol);
                }

                /*  currency change handler (IMPORTANT) */
                currencySelect.onchange = function () {
                    let symbol = this.options[this.selectedIndex].getAttribute('data-symbol') || '';
                    $('.mo_currency_symbol').text(symbol);

                    // update total row symbol also
                    $('#total-row .mo_currency_symbol').text(symbol);
                };
            /* ================= CURRENCY SETUP ================= */
            let taxOptions = `<option value="">Select Tax</option>`;
            taxes.forEach(tax => {
                taxOptions += `<option value="${tax.id}" data-tax="${tax.tax}">${tax.tax}%</option>`;
            });
            inventories.forEach(item => {
                const row = `
                    <tr>
                        <td class="text-center align-middle">${item.product.product_name ?? item.buyer_product_name}<input type="hidden"  name="inventory_id[]" value="${item.id}"  /></td>
                        <td class="text-center align-middle">${item.specification}</td>
                        <td class="text-center align-middle">${item.size}</td>
                        <td class="text-center align-middle">${item.uom?.uom_name ?? ''}</td>
                        <td class="text-center align-middle"><input type="text" class="form-control bg-white w-100 wo-qty-input" name="qty[]" value="0"  inputmode="decimal" maxlength="10"/></td>
                        <td class="text-center align-middle"><input type="text" class="form-control bg-white w-125 wo-rate-input" name="rate[]" value="" min="0.01" step="0.01" inputmode="decimal" maxlength="10"/></td>
                        <td class="text-center align-middle"><input type="text" class="form-control bg-white w-125 wo-mrp-input" name="mrp[]" value="" min="0.01" step="0.01" inputmode="decimal" maxlength="10"/></td>
                        <td class="text-center align-middle"><input type="text" class="form-control bg-white w-100 wo-disc-input" name="disc[]" value="" min="0.01" max="100" step="0.01" inputmode="decimal" maxlength="10"/></td>
                        <td class="text-center align-middle">
                            <select class="form-select wo-gst-select form-select-sm w-120" name="gst[]">
                                ${taxOptions}
                            </select>
                        </td>
                        <td class="wo-total-amount text-center align-middle">0.00</td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-danger width-inherit wo_remove-row" title="Remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                `;
                table.append(row);
            });

            const totalRow = `
                <tr id="total-row" class="bg-white border border-top">
                    <td colspan="9" class="text-right">
                        <strong>Total Amount (<span class="mo_currency_symbol">${currencySymbol}</span>):</strong>
                    </td>
                    <td id="wo_grand-total" class="text-center align-middle">0.00</td>
                    <td></td>
                </tr>
            `;
            table.append(totalRow);

            $("#workOrderModal").modal("show");
            $('#vendorSuggestionsWo').hide();
            $('.wo-disc-input').prop('disabled', true);
        }).fail(() => toastr.error('Failed to load inventory details.'));
    }

    // Allow only numbers and one dot (.) in qty and rate inputs
    $(document).on('keypress', '.wo-qty-input, .wo-rate-input,.wo-disc-input,.wo-mrp-input', function (e) {
        const charCode = e.which ?? e.keyCode;
        const charStr = String.fromCharCode(charCode);
        const currentVal = $(this).val();

        // Block non-numeric characters except one dot
        if (!/[0-9.]/.test(charStr)) {
            e.preventDefault();
        }

        // Block multiple dots
        if (charStr === '.' && currentVal.includes('.')) {
            e.preventDefault();
        }
    });

    // Block invalid pasted content
    $(document).on('paste', '.wo-qty-input, .wo-rate-input,.wo-disc-input,.wo-mrp-input', function (e) {
        e.preventDefault();
        let text = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
        text = text.replace(/[^0-9.]/g, '');
        let parts = text.split('.');
        if (parts.length > 2) text = parts[0] + '.' + parts[1];
        if (parts[1]?.length > 2) text = parts[0] + '.' + parts[1].substring(0, 2);
        $(this).val(text).trigger('input');
    });

    // Enforce 2 decimal places on input
    $(document).on('input', '.wo-rate-input,.wo-disc-input,.wo-mrp-input', function () {
        let val = $(this).val().replace(/[^0-9.]/g, '');
        const parts = val.split('.');

        if (parts.length > 2) {
            val = parts[0] + '.' + parts[1];
        }

        if (parts[1]?.length > 2) {
            val = parts[0] + '.' + parts[1].substring(0, 2);
        }

        $(this).val(val);
    });

    $(document).on('input paste', '.wo-qty-input', function () {
        let val = $(this).val().replace(/[^0-9.]/g, '');
        const parts = val.split('.');

        if (parts.length > 3) {
            val = parts[0] + '.' + parts[1];
        }

        if (parts[1]?.length > 3) {
            val = parts[0] + '.' + parts[1].substring(0, 3);
        }

        $(this).val(val);
    });

    $(document).on('input paste', '.wo-rate-input', function () {
        const row = $(this).closest('tr');
        const rate = parseFloat($(this).val()) || 0;

        if (rate > 0) {
            row.find('.wo-mrp-input, .wo-disc-input').val('');
            row.find('.wo-disc-input').prop('disabled', true).val('');
        }

        recalculateTotals();
    });

    $(document).on('input paste', '.wo-mrp-input, .wo-disc-input', function () {
        const row = $(this).closest('tr');

        const mrp = parseFloat(row.find('.wo-mrp-input').val()) || 0;
        let disc = parseFloat(row.find('.wo-disc-input').val()) || 0;
        if (disc > 99) {
            disc = 99;
            row.find('.wo-disc-input').val(disc);
            recalculateTotals();
        }
        if (mrp > 0) {
            row.find('.wo-rate-input').prop('readOnly', true);
            row.find('.wo-disc-input').prop('disabled', false);

            // Rate = MRP - Discount %
            const rate = mrp - ((mrp * disc) / 100);
            row.find('.wo-rate-input').val(rate.toFixed(2));
        } else {
            row.find('.wo-rate-input').val('');
            row.find('.wo-rate-input').prop('readOnly', false).val('');
            row.find('.wo-disc-input').prop('disabled', true).val('');
        }
        updateInputStyles(row);
        recalculateTotals();
    });
    $(document).on('input paste', '.wo-mrp-input, .wo-disc-input, .wo-rate-input', function () {
        const row = $(this).closest('tr');

        const mrp = parseFloat(row.find('.wo-mrp-input').val()) || 0;
        let disc = parseFloat(row.find('.wo-disc-input').val()) || 0;
        if (disc > 99) {
            disc = 99;
            row.find('.wo-disc-input').val(disc);
            recalculateTotals();
        }
        if (mrp > 0) {
            row.find('.wo-rate-input').prop('readOnly', true);
            row.find('.wo-disc-input').prop('disabled', false);
        } else {
            row.find('.wo-rate-input').prop('readOnly', false);
            row.find('.wo-disc-input').prop('disabled', true);
        }

        // update style according to readonly/disabled
        updateInputStyles(row);
    });
    $(document).on('blur', '.wo-disc-input', function () {
        let disc = parseFloat($(this).val());

        if (isNaN(disc) || disc <= 0) {
            $(this).val('');
        } else if (disc >= 100) {
            $(this).val('99');
        } else {
            $(this).val(disc.toFixed(2));
        }
    });
    function updateInputStyles(row) {
        const rateInput = row.find('.wo-rate-input');
        rateInput.prop('readOnly')
            ? rateInput.css({'background-color':'#f5f5f5','opacity':'0.5','pointer-events':'none'})
            : rateInput.css({'background-color':'','opacity':'','pointer-events':''});

        const discInput = row.find('.wo-disc-input');
        discInput.prop('disabled')
            ? discInput.css({'background-color':'#f5f5f5','opacity':'0.5','pointer-events':'none'})
            : discInput.css({'background-color':'','opacity':'','pointer-events':''});

    }
    // Function to recalculate totals
    function recalculateTotals() {
        let grandTotal = 0;

        $('#forworkOrderInventoryDetailsTableBody tr').each(function () {
            const row = $(this);

            let qty = parseFloat(row.find('.wo-qty-input').val()) || 0;
            const rate = parseFloat(row.find('.wo-rate-input').val()) || 0;

            // Get selected option and extract GST percentage from data-tax
            const selectedGSTOption = row.find('.wo-gst-select option:selected');
            const gst = parseFloat(selectedGSTOption.data('tax'));

            // Validate inputs
            if(qty=='0'){
                qty=1;
            }
            const isValid =  rate >= 0.01 && !isNaN(gst);

            if (isValid) {
                const amount = qty * rate;
                const gstAmount = (amount * gst) / 100;
                const total = amount + gstAmount;

                row.find('.wo-total-amount').text(total.toFixed(2));
                grandTotal += total;
            } else {
                row.find('.wo-total-amount').text('0.00');
            }
        });

        $('#wo_grand-total').text(grandTotal.toFixed(2));
    }

    // On input/change — quantity, rate, or GST select
    $(document).on('input change', '.wo-qty-input, .wo-rate-input, .wo-gst-select', function () {
        recalculateTotals();
    });

    // Remove row when the cross icon is clicked
    $(document).on('click', '.wo_remove-row', function () {
        const row = $(this).closest('tr');
        const table = $('#forworkOrderInventoryDetailsTableBody');

        const dataRows = table.find('tr').filter(function () {
            return $(this).find('td').length > 0 && $(this).attr('id') !== 'total-row';
        });

        if (dataRows.length > 1) {
            row.remove();
            recalculateTotals(); // Recalculate after removal
        } else {
            toastr.error('Product is not removed. For generating Work Order, at least one product is mandatory!');
        }
    });



    $('#wo_vendor_name').on('keyup', function () {
        $('#currency_id').removeClass('locked-select');
        $('#forworkOrderInventoryDetailsTableBody tr').each(function () {
            $(this).find('.wo-qty-input').val('0');
            $(this).find('.wo-rate-input').val('');
            $(this).find('.wo-mrp-input').val('');
            $(this).find('.wo-disc-input').val('');
            $(this).find('.wo-gst-select').val('');
            $(this).find('.wo-total-amount').text('0.00');
        });

        $('#wo_grand-total').text('0.00');
        $('#wo_vendor_user_id').val('');
        $('#vendorNameIdWo').nextAll('tr').remove();


        const input = $(this).val();
        let dropdown = '';

        if (input.length < 3) {
            dropdown = `<span class="manualPOdropdown-item text-danger">Minimum 3 characters required</span>`;
            $('#vendorSuggestionsWo').html(dropdown).show();
            return;
        }

        $.ajax({
            url: searchVendorByVendornameURL,
            method: 'GET',
            data: { q: input },
            success: function (data) {
                if (data.length > 0) {
                    data.forEach(function (user) {
                        dropdown += `<a class="manualPOdropdown-item manualPOdropdown-item-border" href="#" onclick="selectVendor('${user.id}')">${user.name}</a>`;
                    });
                } else {
                    dropdown = `<span class="manualPOdropdown-item text-danger">No vendors found</span>`;
                }

                $('#vendorSuggestionsWo').html(dropdown).show();
            }
        });
    });

    $('#deliveryPeriod').on('keyup paste', function () {
        let input = $(this).val();
        input = input.replace(/\D/g, '');
        if (input.length > 3) {
            input = '999';
        }
        let value = parseInt(input, 10);
        if (isNaN(value) || value < 1) {
            value = '';
        } else if (value > 999) {
            value = 999;
        }
        $(this).val(value);
    });


    let isWorkOrderSubmitting = false;

    $('#generate_work_order_product').on('click', function (e) {
        e.preventDefault();

        if (isWorkOrderSubmitting) return;

        isWorkOrderSubmitting = true;

        let button = $(this);
        let originalHtml = button.html();

        //  Show Saving loader
        button.html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
        button.prop('disabled', true);

        const formData = $('#generate_work_order_form').serialize();
        const vendorId = $('#wo_vendor_user_id').val();
        const wo_created_date = $('#wo_created_date').val();
        const paymentTerms = $('#wo_paymentTerms').val();
        const priceBasis = $('#wo_priceBasis').val();
        const deliveryPeriod = $('#wo_deliveryPeriod').val();
        const remarks = $('#wo_remarks').val();
        const additionalRemarks = $('#wo_additionalRemarks').val();        
        const totalAmount = parseFloat($('.wo-total-amount').text().trim());

        // ---------- VALIDATION ----------
        if (!vendorId) return showError('Vendor cannot be empty.');
        if (!wo_created_date) return showError('Work Order Date cannot be empty.');

        let qtyError = false;
        $('.wo-qty-input').each(function () {
            const qty = $(this).val()?.trim();
            if (qty === '' || isNaN(qty) || parseFloat(qty) < 0) {
                qtyError = true;
                return false;
            }
        });
        if (qtyError) return showError('Quantity must not be negative.');

        let rateError = false;
        $('.wo-rate-input').each(function () {
            const rate = $(this).val()?.trim();
            if (rate === '' || isNaN(rate) || parseFloat(rate) <= 0) {
                rateError = true;
                return false;
            }
        });
        if (rateError) return showError('Rate must be greater than 0.');
        if (isNaN(totalAmount) || totalAmount <= 0) {
            return showError('Total amount must be greater than 0.');
        }
        if (!paymentTerms) return showError('Payment Terms cannot be empty.');
        if (!deliveryPeriod) return showError('Delivery Period cannot be empty.');
        if (!priceBasis) return showError('Price Basis cannot be empty.');

        // ---------- AJAX ----------
        $.ajax({
            url: genarateWorkOrderURL,
            type: 'POST',
            data: formData,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.status === '1') {
                    toastr.success(response.message);
                    $('#workOrderModal').modal('hide');
                    $('#generate_work_order_form')[0].reset();
                    $("#wo_other_term_check").val('1').prop('checked', true);
                    $('.inventory_chkd').prop('checked', false);
                    selectedIds = [];
                } else {
                    toastr.error(response.message || 'Failed to generate PO.');
                }
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    Object.values(xhr.responseJSON.errors).forEach(function (messages) {
                        messages.forEach(function (message) {
                            toastr.error(message);
                        });
                    });
                } else {
                    toastr.error(xhr.responseJSON?.message || 'Something went wrong.');
                }
            },
            complete: function () {
                //  Restore button AFTER mail + DB complete
                button.html(originalHtml);
                button.prop('disabled', false);
                isWorkOrderSubmitting = false;
            }
        });

        function showError(message) {
            toastr.error(message);
            button.html(originalHtml);
            button.prop('disabled', false);
            isWorkOrderSubmitting = false;
        }
    });


});

function selectVendor(vendorId) {
    $('#wo_vendor_name').val('Loading...');
    $('#vendorSuggestionsWo').hide();

    $.ajax({
        url: getVendorDetailsByNameURL,
        method: 'GET',
        data: { id: vendorId },
        success: function (vendor) {
            let detailsHtml = `
                <tr>
                    <td class="text-start text-wrap keep-word"><strong>Address</strong></td>
                    <td class="text-start text-wrap keep-word">${vendor.address}${vendor.city ? ', ' + vendor.city : ''}</td>

                    <td class="text-start text-wrap keep-word"><strong>Country</strong></td>
                    <td class="text-start text-wrap keep-word">${vendor.country}</td>
                </tr>
                <tr>
                    <td class="text-start text-wrap keep-word"><strong>State</strong></td>
                    <td class="text-start text-wrap keep-word">${vendor.state}</td>

                    <td class="text-start text-wrap keep-word"><strong>State Code</strong></td>
                    <td class="text-start text-wrap keep-word">${vendor.state_code ?? 'N/A'}</td>
                </tr>
                <tr>
                    <td class="text-start text-wrap keep-word"><strong>Pincode</strong></td>
                    <td class="text-start text-wrap keep-word">${vendor.pincode ?? 'N/A'}</td>
                    <td class="text-start text-wrap keep-word"><strong>GST/TIN No</strong></td>
                    <td class="text-start text-wrap keep-word">${vendor.gstin}</td>
                </tr>
                <tr>
                    <td class="text-start text-wrap keep-word"><strong>Mobile No</strong></td>
                    <td class="text-start text-wrap keep-word">${vendor.country_code ?? ''} ${vendor.mobile ?? 'N/A'}</td>
                    <td class="text-start text-wrap keep-word"><strong>Email Address</strong></td>
                    <td class="text-start text-wrap keep-word">${vendor.email}</td>
                </tr>
            `;

            $('#vendorNameIdWo').siblings('tr').remove();
            $('#vendorNameIdWo').after(detailsHtml);

            // Set values
            $('#wo_vendor_name').val(vendor.name);
            $('#wo_vendor_user_id').val(vendorId);
            if (vendor.currency_field_readonly) {
                $('#wo_currency_id').val(vendor.currency_id).trigger('change');
                $('#wo_currency_id').addClass('locked-select');
            } else {
                $('#wo_currency_id').removeClass('locked-select');
            }
        },
        error: function () {
            toastr.error("Failed to fetch vendor details.");
        }
    });
}

$(document).on("blur", "textarea[name='other_terms_textarea']", function () {
    if ($(this).val().length > 15600) {
        $(this).val($(this).val().substring(0, 15600));
    }
});
// on change input name="other_term_check"
$(document).on("change", "input[name='other_term_check']", function () {
    if ($(this).is(":checked")) {
        $(this).parent().find("textarea[name='other_terms_textarea']").prop("disabled", false);
    } else {
        $(this).parent().find("textarea[name='other_terms_textarea']").prop("disabled", true);
    }
});
