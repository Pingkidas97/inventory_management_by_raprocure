$(document).ready(function () {
    // Initialize Datepicker
    $(".date-picker").datepicker({
        dateFormat: "dd/mm/yy",   // Format: dd-mm-yyyy
        changeMonth: true,
        changeYear: true,
        onClose: function () {
            // Optionally handle onClose event if you need to perform actions when the calendar is closed.
        }
    });

    // Open calendar on focus or click
    $(".date-picker").on("focus", function () {
        $(this).datepicker("show");  // Show the calendar when the input is focused
    });

    // Prevent typing and pasting in the input
    $(".date-picker").on("keydown paste", function (e) {
        e.preventDefault();  // Prevent typing/pasting
    });
});
