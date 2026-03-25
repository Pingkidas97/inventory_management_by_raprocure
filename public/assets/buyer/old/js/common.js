
const header = document.getElementById("project_header");
const stickyPoint = header.offsetTop;

window.addEventListener("scroll", function () {
    if (window.pageYOffset > stickyPoint) {
        header.classList.add("sticky");
    } else {
        header.classList.remove("sticky");
    }
});

$(document).on("input", ".text-upper-case", function(){
    $(this).val(($(this).val()).toUpperCase());
});

//for select file: start
$(document).on('change', '.button-browse :file', function () {
    let input = $(this),
        numFiles = input.get(0).files ? input.get(0).files.length : 1,
        label = input.val().replace(/\\/g, '/').replace(/.*\//, '');

    input.trigger('fileselect', [numFiles, label, input]);
});

$('.button-browse :file').on('fileselect', function (event, numFiles, label, input) {
    let val = numFiles > 1 ? numFiles + ' files selected' : label;
    input.parent('.button-browse').next(':text').val(val);
});
//for select file: end

// Function to validate file size
function validateFileSize(event) {
    const fileInput = event.target;
    // const errorMsg = fileInput.nextElementSibling;
    const maxSize = 2 * 1024 * 1024; // 2MB in bytes

    if (fileInput.files.length > 0) {
        const fileSize = fileInput.files[0].size;
        if (fileSize > maxSize) {
            alert('File size exceeds 2MB limit.');
            fileInput.value = ''; // Clear the file input

            // Get both span elements by their classes
            const spanElements = document.querySelectorAll('.help-block.rfq-file-name, .remove-rfq-file');

            // Add the class 'd-none' to each span element
            spanElements.forEach(function (element) {
                element.classList.add('d-none');
            });
        }
    }
}

// Attach event listener to all input elements of type file
document.querySelectorAll('input[type="file"]').forEach(function (input) {
    input.addEventListener('change', validateFileSize);
});

// function _selectOption(selecter, vals, multiple = false) {
//     const $select = $(selecter);
//     const values = multiple ? vals.split(",") : [vals];

//     $select.find('option').each(function () {
//         const $option = $(this);
//         const isSelected = values.includes($option.val());
//         $option.prop('selected', isSelected);
//     });
// }

(function ($) {
    $.fn.selectOption = function (vals, multiple = false) {
        const values = multiple ? vals.split(",") : [vals];
        return this.each(function () {
            const $select = $(this);
            $select.find('option').each(function () {
                const $option = $(this);
                const isSelected = values.includes($option.val());
                $option.prop('selected', isSelected);
            });
        });
    };
    $.fn.sanitizeNumberField = function() {
        return this.each(function() {
            var inputValue = $(this).val();

            // Allow only digits and the first decimal point
            var hasDecimal = false;
            var sanitized = '';

            for (var i = 0; i < inputValue.length; i++) {
                var char = inputValue[i];
                if (char >= '0' && char <= '9') {
                    sanitized += char;
                } else if (char === '.' && !hasDecimal) {
                    sanitized += char;
                    hasDecimal = true;
                }
            }

            // Prevent the value from being just a dot
            if (sanitized === '.') {
                sanitized = '';
            }

            $(this).val(sanitized);
        });
    };
    
})(jQuery);