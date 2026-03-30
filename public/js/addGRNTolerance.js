window.show_grn_tolerance_modal = function () {

    //alert('Add GRN Tolerance clicked');

    $('#tolerance').val('');

    $.get(window.grnToleranceGetUrl, function (res) {
        if (res) {
            $('#tolerance').val(res.tolerance);
        }
    });

    $('#grnToleranceModal').modal('show');


    
};