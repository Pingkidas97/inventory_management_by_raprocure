$(document).ready(function () {
    $('.getPassBtn').on('click', function () {
        $('#po_number_search').val(''); 
        $('#getPassResult').html('');   
        $('#getPassModal').modal('show');
        $('#saveGetPassContainer').hide();
    });
});
