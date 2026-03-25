$(document).ready(function () {
    selectedIds = [];
    // $('#selectAll').on('click', function () {
    //     const isChecked = $(this).prop('checked');
    //     $('.inventory_chkd:visible').each(function () {
    //         $(this).prop('checked', isChecked);
    //         const id = $(this).val();

    //         if (isChecked) {
    //             if (!selectedIds.includes(id)) {
    //                 selectedIds.push(id);
    //             }
    //         } else {
    //             selectedIds = selectedIds.filter(i => i !== id);
    //         }
    //     });
    // });


    $(document).on('click', '.inventory_chkd', function () {
        const id = $(this).val();
        if ($(this).prop('checked')) {
            if (!selectedIds.includes(id)) {
                selectedIds.push(id);
            }
        } else {
            selectedIds = selectedIds.filter(i => i !== id);
        }
        const totalVisible = $('.inventory_chkd:visible').length;
        const totalChecked = $('.inventory_chkd:visible:checked').length;
        // $('#selectAll').prop('checked', totalVisible === totalChecked);
    });


    function reapplySelections() {
        $('.inventory_chkd').each(function () {
            const id = $(this).val();
            if (selectedIds.includes(id)) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        });

        const totalVisible = $('.inventory_chkd:visible').length;
        const totalChecked = $('.inventory_chkd:visible:checked').length;
        // $('#selectAll').prop('checked', totalVisible > 0 && totalVisible === totalChecked);
    }
    $('#inventory-table').on('draw.dt', function () {
        reapplySelections();
    });

});
