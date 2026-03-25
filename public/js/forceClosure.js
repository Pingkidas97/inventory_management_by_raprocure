$(document).ready(function () {

    let currentRfq = null;
    let currentInventoryId = null;

    $('.forceClosure').on('click', function (e) {
        checkPermissionAndExecute('FORCE_CLOSURE', 'add', '1', function () {
            e.preventDefault();

            if (selectedIds.length === 0) {
                toastr.error('Please select an inventory.');
                return;
            }

            if (selectedIds.length > 1) {
                toastr.error('You can select only one inventory at a time.');
                return;
            }

            $('#generate_force_closure_form')[0].reset();

            currentInventoryId = selectedIds[0];
            currentRfq = null;

            fetchInventoryDetails(currentInventoryId);
        });
    });


    function fetchInventoryDetails(inventoryId) {

        $.ajax({
            url: forceClosureFetchURL,
            type: "POST",
            data: {
                inventory_id: inventoryId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },

            success: function (response) {

                if (response.status === 'error') {
                    toastr.error(response.message);
                    return;
                }

                const inventory = response.inventory || {};
                const rfqs = response.rfqs || [];

                $('#fc_item_code').text(inventory.item_code || '-');
                $('#fc_product_name').text(inventory.product_name || '-');
                $('#fc_specification').text(inventory.specification || '-');
                $('#fc_size').text(inventory.size || '-');

                const rfqSelect = $('#rfqSelect');

                rfqSelect.empty().append(`<option value="">Select RFQ</option>`);

                rfqs.forEach(rfq => {
                    rfqSelect.append(
                        `<option value="${rfq.rfq_id}">${rfq.rfq_number}</option>`
                    );
                });

                if (rfqs.length === 1) {
                    rfqSelect.val(rfqs[0].rfq_id).prop('disabled', true);
                    currentRfq = rfqs[0];
                    displayRfqDetails(rfqs[0]);
                } else {
                    rfqSelect.prop('disabled', false);
                }

                rfqSelect.off('change').on('change', function () {

                    let rfqId = $(this).val();

                    const selectedRfq = rfqs.find(r => r.rfq_id == rfqId);

                    currentRfq = selectedRfq || null;

                    $('#rfqDetailsContent').empty();
                    $('#forceClosureMessage').empty();
                    $('#forceClosureButtonDiv').hide();

                    if (selectedRfq) {
                        displayRfqDetails(selectedRfq);
                    }

                });

                $('#forceClosureModal').modal('show');

            },

            error: function () {
                toastr.error('Failed to load inventory details.');
            }

        });

    }


    function displayRfqDetails(rfq) {

        const container = $('#rfqDetailsContent');

        container.empty();

        $('#forceClosureMessage').empty();
        $('#forceClosureButtonDiv').hide();

        container.append(`
            <div class="border rounded p-2 mb-2 bg-light">
                <div><strong>RFQ Quantity :</strong> ${rfq.rfqQty}</div>                
            </div>
        `);

        rfq.details.forEach(d => {

            const displayOrderNumber = d.order_number === '-' ? 'No order processed' : d.order_number;

            container.append(`
                <div class="border rounded p-2 mb-2 bg-light">
                    <div><strong>Order Number :</strong> ${displayOrderNumber}</div>
                    <div><strong>Order Quantity :</strong> ${d.order_quantity}</div>
                    <div><strong>GRN Quantity :</strong> ${d.grn_qty}</div>
                </div>
            `);

        });

        if (rfq.force_closure_status == 1) {
            $('#forceClosureButtonDiv').show();
        }
    }


    $('#forceClosureButton').on('click', function () {
        checkPermissionAndExecute('FORCE_CLOSURE', 'add', '1', function () {
            if (!currentRfq || !currentInventoryId) {
                toastr.error('Please select an RFQ.');
                return;
            }

            if (!confirm('Are you sure you want to perform Force Closure?')) {
                return;
            }

            $.ajax({

                url: genarateforceClosureURL,
                type: 'POST',

                data: {
                    rfq_id: currentRfq.rfq_id,
                    rfq_number: currentRfq.rfq_number,
                    inventory_id: currentInventoryId,
                    rfq_qty: currentRfq.rfqQty,
                    total_order_qty: currentRfq.totalOrderQty,
                    total_grn_qty: currentRfq.totalGrnQty,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },

                success: function () {

                    toastr.success('Force Closure completed successfully');

                    $('#forceClosureModal').modal('hide');
                    $('.inventory_chkd').prop('checked', false);
                    inventory_list_data();
                },

                error: function (xhr) {
                    let message = 'Something went wrong';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    toastr.error(message);
                }

            });
        });

    });

});