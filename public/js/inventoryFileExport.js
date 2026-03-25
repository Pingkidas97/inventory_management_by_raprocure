// function inventoryFileExport(btn,url,data,deleteExcelUrl){
//     btn.prop('disabled', true).html('<i class="bi bi-arrow-clockwise"></i> Exporting...');
//     // toastr.success('Preparing your file...');

//     $.ajax({
//         type: 'POST',
//         url: url,
//         data: data,
//         success: function (response) {
//             if (response.fetchRow === false) {
//                 toastr.error(response.message || 'No record found, Try another search!');
//                 return;
//             }
//             if (response.success && response.download_url) {
//                 window.location.href = response.download_url;

//                 toastr.success('Download started.');
//                 setTimeout(function() {
//                     $.ajax({
//                         url: deleteExcelUrl,
//                         method: 'POST',
//                         data: {
//                             file_path: response.download_url,
//                             _token: $('meta[name="csrf-token"]').attr('content')
//                         },
//                         success: function(res) {
//                             if (res.success) {
//                                 console.log('Export file deleted successfully.');
//                             } else {
//                                 console.warn('Failed to delete export file.');
//                             }
//                         },
//                         error: function() {
//                             console.warn('Error while deleting export file.');
//                         }
//                     });
//                 }, 300000);
//             } else if (response.fetchRow === false) {
//                 toastr.error(response.message || 'No record found to export.');
//             } else {
//                 toastr.error('Failed to prepare download.');
//             }
//         },
//         error: function () {
//             toastr.error('Error occurred while exporting.');
//         },
//         complete: function () {
//             btn.prop('disabled', false).html('<i class="bi bi-download"> </i> Export');
//         }
//     });
// }
function inventoryFileExport(btn, url, data) {
    btn.prop('disabled', true).html('<i class="bi bi-arrow-clockwise"></i> Exporting...');

    $.ajax({
        type: 'POST',
        url: url,
        data: data,
        success: function (response) {
            if (response.fetchRow === false) {
                toastr.error(response.message || 'No record found, Try another search!');
                return;
            }

            if (response.success && response.download_url) {
                const link = document.createElement('a');
                link.href = response.download_url;
                link.setAttribute('download', '');
                document.body.appendChild(link);
                link.click();
                link.remove();

                toastr.success('Download started !');
            } else {
                toastr.error('Failed to prepare download.');
            }
        },
        error: function () {
            toastr.error('Error occurred while exporting.');
        },
        complete: function () {
            // toastr.success('Export Successfully Done !');
            btn.prop('disabled', false).html('<i class="bi bi-download"> </i> Export');
        }
    });
}
