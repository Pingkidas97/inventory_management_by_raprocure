$(document).ready(function () {

    $('input').attr({
        autocomplete: 'off',
        autocorrect: 'off',
        autocapitalize: 'off',
        spellcheck: 'false'
    });
    $('#from_date, #to_date')
        .on('keydown', function (e) {
            e.preventDefault();
        })
        .on('paste', function (e) {
            e.preventDefault();
        });



    const specialCharRegex = /[!@#$%^&*()?":{}|<>[\]\\\/'`;+=~`_—✓©®™•¡¿§¤€£¥₩₹†‡‰¶∆∑∞µΩ≈≠≤≥÷±√°¢¡¬‽№☯☮☢☣♻⚡⚠✔️🔒🎉😊💡🌍🚀📦🧩🛠️🐍🔥💾📁🖥️⌨️🔧🔍]/g;
    const specialCharAllowCaseRegex = /[]/g;/*/[<>~"'`]/g;*/
    const allowed = /[^A-Za-z0-9~!@#$%^&*()_+\`\-={}[\]:;"'|\\<,>.?\/ \n\râ‚¹$à¤°à¥\u200B]/g;
    // Handle typing (input event is better than keypress)
    $(document).on("input", "input:not([readonly]):not([disabled]):not([type='file'])", function () {//pingki
        const input = $(this);
        let val = input.val();
        const max = parseInt(input.attr('maxlength'), 10);
        if (input.hasClass('vehicle_lr_number')) {
            val = val.replace(/[^a-zA-Z0-9\-_/ ]/g, '');
        } else {
            val= val.replace(allowed, '');
            // If not allowed, strip special characters
            if (!input.hasClass("specialCharacterAllowed")) {
                if (input.is("#search_order_no")) {
                    val = val.replace(/[!@#$%^&*()?":{}|<>[\]\\'`;+=~`_—✓©®™•…]/g, '');
                }else if (input.is("#product_brand")) {
                    val = val.replace(/[<>~"'`]/g, '');
                }else if (input.is("#transporter_name")) {
                    val = val.replace(/[^a-zA-Z0-9 ]/g, '');
                }else if (input.is("#invoice_number")|| input.is("#s_invoice_number")|| input.is("#O_invoice_number")) {
                    val = val.replace(/[^a-zA-Z0-9 /\\]/g, '');
                }else{
                    val = val.replace(specialCharRegex, '');
                }
            }else{
                val = val.replace(specialCharAllowCaseRegex, '');
            }
        }
        // Apply maxlength trimming
        if (!isNaN(max) && val.length > max) {
            val = val.substring(0, max);
        }

        input.val(val);
    });

    // Paste event (prevent default paste and sanitize)
    $(document).on('paste', 'input:not([readonly]):not([disabled]), textarea:not([readonly]):not([disabled])', function (e) {
        e.preventDefault();

        const input = $(this);
        const el = this;
        let pastedText = (e.originalEvent || e).clipboardData.getData('text');
        const allowed = /[^A-Za-z0-9~!@#$%^&*()_+\`\-={}[\]:;"'|\\<,>.?\/ \n\râ‚¹$à¤°à¥\u200B]/g;
        if (input.hasClass('vehicle_lr_number')) {
            pastedText = pastedText.replace(/[^a-zA-Z0-9\-_/ ]/g, '');
        } else {
            pastedText = pastedText.replace(allowed, '');
            // If not allowed, remove special characters
            if (!input.hasClass("specialCharacterAllowed")) {
                if (input.is("#search_order_no")) {
                    pastedText = pastedText.replace(/[!@#$%^&*()?":{}|<>[\]\\'`;+=~`_—✓©®™•…]/g, '');
                }else if (input.is("#product_brand")) {
                    pastedText = pastedText.replace(/[<>~"'`]/g, '');
                }else if (input.is("#transporter_name")) {
                    pastedText = pastedText.replace(/[^a-zA-Z0-9 ]/g, '');
                }else if (input.is("#invoice_number")|| input.is("#s_invoice_number")|| input.is("#O_invoice_number")) {
                    pastedText = pastedText.replace(/[^a-zA-Z0-9 /\\]/g, '');
                }else{
                    pastedText = pastedText.replace(specialCharRegex, '');
                }
            }else{
                pastedText = pastedText.replace(specialCharAllowCaseRegex, '');
            }
        }
        const max = parseInt(input.attr('maxlength'), 10);
        const start = el.selectionStart;
        const end = el.selectionEnd;
        const currentVal = input.val();

        let newVal = currentVal.slice(0, start) + pastedText + currentVal.slice(end);

        if (!isNaN(max) && newVal.length > max) {
            newVal = newVal.substring(0, max);
        }

        input.val(newVal);
        const caretPos = Math.min(start + pastedText.length, newVal.length);
        el.setSelectionRange(caretPos, caretPos);
    });

    //===numeric only Qty value
    $(document).on('keypress', '.smt_numeric_only_qty', function (e) {
        let charCode = e.which || e.keyCode;

        // Prevent "e", spaces, and non-numeric characters except "."
        if (charCode === 69 || charCode === 101 || charCode === 32) { // "E", "e", and space
            return false;
        }

        let character = String.fromCharCode(charCode);
        let inputValue = $(this).val();

        // Allow only one decimal point
        if (character === '.' && inputValue.includes('.')) {
            return false;
        }

        // Allow numbers and one decimal point
        return /[0-9.]$/.test(character);
    }).on('paste', function (e) {
        let pastedData = e.originalEvent.clipboardData.getData('text');
        let numericValue = pastedData.replace(/[^0-9.]/g, '');

        // Prevent multiple decimals
        let decimalCount = (numericValue.match(/\./g) || []).length;
        if (decimalCount > 1) {
            numericValue = numericValue.replace(/\.(?=.*\.)/g, ''); // Remove extra decimal points
        }

        $(this).val(numericValue);
        e.preventDefault();
    }).on('blur', '.smt_numeric_only_qty', function (e) {
        let value = e.target.value;

        // Remove extra decimal points or invalid characters
        value = value.replace(/[^0-9.]/g, '');

        // Ensure only two decimal places
        if (value.includes('.')) {
            let [integerPart, decimalPart] = value.split('.');
            decimalPart = decimalPart.slice(0, 3); // Keep only three decimals
            value = integerPart + (decimalPart ? '.' + decimalPart : '');
        }

        e.target.value = value;
    });
    //===numeric only Qty value
    //===numeric only value
    $(document).on('keypress', '.smt_numeric_only', function (e) {
        let charCode = e.which || e.keyCode;

        // Prevent "e", spaces, and non-numeric characters except "."
        if (charCode === 69 || charCode === 101 || charCode === 32) { // "E", "e", and space
            return false;
        }

        let character = String.fromCharCode(charCode);
        let inputValue = $(this).val();

        // Allow only one decimal point
        if (character === '.' && inputValue.includes('.')) {
            return false;
        }

        // Allow numbers and one decimal point
        return /[0-9.]$/.test(character);
    }).on('paste', function (e) {
        let pastedData = e.originalEvent.clipboardData.getData('text');
        let numericValue = pastedData.replace(/[^0-9.]/g, '');

        // Prevent multiple decimals
        let decimalCount = (numericValue.match(/\./g) || []).length;
        if (decimalCount > 1) {
            numericValue = numericValue.replace(/\.(?=.*\.)/g, ''); // Remove extra decimal points
        }

        $(this).val(numericValue);
        e.preventDefault();
    }).on('blur', '.smt_numeric_only', function (e) {
        let value = e.target.value;

        // Remove extra decimal points or invalid characters
        value = value.replace(/[^0-9.]/g, '');

        // Ensure only two decimal places
        if (value.includes('.')) {
            let [integerPart, decimalPart] = value.split('.');
            decimalPart = decimalPart.slice(0, 2); // Keep only two decimals
            value = integerPart + (decimalPart ? '.' + decimalPart : '');
        }

        e.target.value = value;
    });
    //===numeric only value
});
// resources/js/app.js

// CSRF token error handling
$(document).ajaxError(function(event, jqxhr, settings, thrownError) {
    const errorText = jqxhr.responseText || "";

    if (errorText.includes("CSRF token mismatch")) {
        toastr.error("Session expired. Reloading...");
        setTimeout(() => {
            location.reload();
        }, 1500);
    }
});

function checkPermissionAndExecute(module, type, forValue = '1', onSuccess) {
    $.ajax({
        url: routes.checkPermission,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            module: module,
            type: type,
            for: forValue
        },
        success: function (res) {
            if (res.status === 1) {
                if (typeof onSuccess === 'function') {
                    onSuccess();
                }
            } else {
                toastr.error(res.message || 'Unauthorized');
            }
        },
        error: function (xhr) {
            let msg = xhr.responseJSON?.message || 'Permission check failed';
            toastr.error(msg);
        }
    });
}
