$(document).ready(function () {
    $('.resetIndentRFQ').on('click', function (e) {
        e.preventDefault();
        checkPermissionAndExecute('INVENTORY_MANAGEMENT', 'edit', '1', function () {
            if (selectedIds.length > 0) {
                if (confirm('Are you Sure, You want to Reset Selected Inventory?')) {
                    $.ajax({
                        url: resetInventoryUrl,
                        method: 'POST',
                        data: {
                            inventory_ids: selectedIds,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.status === 1) {
                                toastr.success(response.message);
                                if (inventoryTable) {
                                    inventoryTable.ajax.reload();
                                }

                                selectedIds = [];
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function () {
                            toastr.error('Something went wrong. Please try again later.');
                        }
                    });
                }
            } else {
                toastr.error('Please select at least one Inventory.');
            }
        });
    });
});

