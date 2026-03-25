  /***inventory-table-accordian */
function openIndent() {
    var extraTr = document.querySelector(".fold");
    extraTr.classList.add('extra_tr');
    document.querySelector(".bi-dash-lg").style.display = 'inline'
    document.querySelector(".bi-plus-lg").style.display = 'none'
}
function closeIndent() {
    var extraTr = document.querySelector(".fold");
    extraTr.classList.remove('extra_tr');
    document.querySelector(".bi-dash-lg").style.display = 'none'
    document.querySelector(".bi-plus-lg").style.display = 'inline'
}

/****data-table****/
    $(document).ready(function () {
        $('#dataTables-example1').DataTable({
            responsive: true,
            paging: true,
            searching: true,
            ordering: true,
            info: true,

        });
    });