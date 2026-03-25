$(document).ready(function () {
    $('.getPassBtn').on('click', function () {
        checkPermissionAndExecute('GATE_PASS_ENTRY', 'add', '1', function () {
            $('#po_number_search').val(''); 
            $('#getPassResult').html('');   
            $('#getPassModal').modal('show');
            $('#saveGetPassContainer').hide();
        });
    });
});
