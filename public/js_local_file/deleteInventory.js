$(document).ready(function () {
    $('.deleteInventory').on('click', function (e) {
        e.preventDefault();
        checkPermissionAndExecute('INVENTORY_MANAGEMENT', 'delete', '1', function () {
            if (selectedIds.length > 0) {
                if (confirm('Are you sure, you want to delete the selected inventory?')) {
                    $.ajax({
                        url: deleteInventoryUrl,
                        method: 'DELETE',
                        data: JSON.stringify({
                            inventory_ids: selectedIds
                        }),
                        contentType: 'application/json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.status === 1) {
                                toastr.success(response.message);
                                selectedIds.forEach(function(id) {
                                    $('#inventory_id_' + id).closest('tr').remove();
                                });

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
