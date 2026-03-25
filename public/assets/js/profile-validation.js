

function appendError(obj, msg = '') {
    $(obj).parent().find('.error-message').remove();
    if (msg) {
        $(obj).parent().append('<span class="help-block error-message">' + msg + '</span>');
    }
}
function appendFileError(obj, msg = '') {
    $(obj).parents('.file-browse').parent().find('.error-message').remove();
    if (msg) {
        $(obj).parents('.file-browse').parent().append('<span class="help-block error-message">' + msg + '</span>');
    }
}

function validateDateFormat(_this, is_required = false) {
    // Check if date format is DD/MM/YYYY, year >= 1900
    let dateString = $(_this).val();

    //regex for DD/MM/YYYY or D/M/YYYY
    const regex = /^(0?[1-9]|[12][0-9]|3[01])\/(0?[1-9]|1[0-2])\/\d{4}$/;

    appendError(_this);

    if (dateString == '') {
        if (is_required == true) {
            appendError(_this, "This Field is Required");
            $(_this).val('');
        }
        return false;
    }

    if (!regex.test(dateString)) {
        appendError(_this, "Please enter the date in DD/MM/YYYY format");
        $(_this).val('');
        return false; // Format is incorrect
    }

    // Parse the date parts
    const [day, month, year] = dateString.split('/').map(Number);
    const date = new Date(year, month - 1, day);

    // Get today's date
    const today = new Date();
    today.setHours(0, 0, 0, 0); // Ignore the time part

    // Check if the date parts are valid and year >= 1900
    if (!(date.getFullYear() === year && date.getMonth() === month - 1 && date.getDate() === day) || year < 1900 || date > today) {
        $(_this).val('');
        appendError(_this, "Please enter a valid date");// (year must be 1900 or later)
    } else {
        const formattedDate = `${String(day).padStart(2, '0')}/${String(month).padStart(2, '0')}/${year}`;
        $(_this).val(formattedDate);
    }
}

$(document).on('input', '.date-masking', function () {
    let value = $(this).val().replace(/\D/g, ''); // Remove non-numeric characters
    if (value.length >= 2 && value.length < 4) {
        value = value.substring(0, 2) + '/' + value.substring(2);
    } else if (value.length >= 4) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4) + '/' + value.substring(4, 8);
    }
    $(this).val(value);
});
// Allow natural deletion (handles backspace/delete key)
$(document).on('keydown', '.date-masking', function (e) {
    if (e.key === 'Backspace' || e.key === 'Delete') {
        let value = $(this).val();
        // If deleting a '/', remove the previous number as well
        if (value.endsWith('/')) {
            $(this).val(value.slice(0, -1));
        }
    }
});

function validateFile(obj, ext) {
    let avatar = $(obj).val();
    let extension = avatar.split('.').pop().toUpperCase();
    checkExtension = ext.split('/');

    if (avatar && checkExtension.indexOf(extension) < 0) {
        $(obj).val('');
        $(obj).attr('src', '');
        appendFileError(obj, "Invalid file extension.");
    } else {
        appendFileError(obj);
    }
}
function validatePinCode(event, _this) {
    if (_this.value.length == 6) {
        return false
    }

    event = (event) ? event : window.event;
    let charCode = (event.which) ? event.which : event.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}

function validatePinCodeWithCountry(_this, is_branch = false) {
    appendError(_this);
    if (is_branch == true) {
        if ($(_this).parents(".branch-row").find(".branch-country").val() == 101) {
            validatePinCodeField(_this);
        }
    } else {
        if ($('.organization-country').val() == 101) {
            validatePinCodeField(_this);
        }
    }
}

function validatePinCodeField(_this) {
    let pin_code = $(_this).val();
    appendError(_this);

    if (pin_code == '') {
        appendError(_this, "This Field is Required");
        return false;
    }
    if (pin_code?.length != 6) {
        appendError(_this, "Please Enter a 6 digit Pin Code");
        return false;
    }
    let regex = /[1-9]{1}[0-9]{2}[0-9]{3}$/;
    if (!regex.test(pin_code)) {
        if (!regex.test(pin_code)) {
            appendError(_this, "Please Enter a valid Pin Code");
            return false;
        }
    }
    return true;
}

function validateGSTINVat(_this) {
    $(_this).val(($(_this).val()).toUpperCase());
    let gst_number = $(_this).val();

    appendError(_this);
    if (gst_number == '') {
        appendError(_this, "This Field is Required");
        return false;
    }
    if (gst_number.length != 15) {
        appendError(_this, "Please Enter a 15 digit of GSTIN/VAT Number");
        return false;
    }
    let regex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
    if (!regex.test(gst_number)) {
        if (!regex.test(gst_number)) {
            appendError(_this, "Please Enter a valid GSTIN/VAT Number");
            return false;
        }
    }
    return true;
}

function validatePanCardField(_this) {
    $(_this).val(($(_this).val()).toUpperCase());
    let pan_number = $(_this).val();
    appendError(_this);
    if (pan_number == '') {
        appendError(_this, "Please enter pan number");
        // toastr.error("Please enter pan number");
        return false;
    }
    if (pan_number.length != 10) {
        appendError(_this, "Please Enter a 10 digit of Pan card Number");
        return false;
    }
    let regex = /[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
    if (!regex.test(pan_number)) {
        if (!regex.test(pan_number)) {
            appendError(_this, "Please Enter a valid Pan card Number");
            return false;
        }
    }
    return true;
}

$(document).on("keydown keyup change", ".validate-max-length", function () {
    validateMinMaxLength(this);
});
function validateMinMaxLength(_this) {
    $(_this).on('keydown keyup change', function () {
        let char = $(this).val(),
            charLength = $(this).val().length,
            maxLength = $(this).data('maxlength'),
            minLength = $(this).data('minlength');
        if (charLength < minLength) {

        } else if (charLength > maxLength) {
            $(this).val(char.substring(0, maxLength));
        } else {

        }
    });
}

// remove special character in mobile number on input, on paste
$(document).on("paste, input", ".my-mobile-number", function () {
    setTimeout(() => $(this).val(($(this).val()).replace(/[^0-9]/g, '').trim()), 0);
});

function disableFormAutocomplete() {
    $('form input').each(function () {
        $(this).attr('autocomplete', 'off' + Math.random().toString(36).substring(7));
    });
    $('form textarea').each(function () {
        $(this).attr('autocomplete', 'off' + Math.random().toString(36).substring(7));
    });
}

function validateFormFields(form_id, for_class = ".required") {
    let error_flags = true;
    $('#' + form_id + ' ' + for_class).each(function () {
        appendError(this);
        if ($(this).val() == '') {
            error_flags = false;
            appendError(this, "This Field is Required");
        }
    });
    return error_flags;
}

function validateFileFields(form_id, for_class = ".required-file") {
    let error_flags = true;
    $('#' + form_id + ' ' + for_class).each(function () {
        appendFileError(this);
        if ($(this).val() == '') {
            error_flags = false;
            appendFileError(this, "This Field is Required");
        }
    });
    return error_flags;
}

function validateURL(form_id, for_class = ".website-url") {
    let error_flags = true;
    $('#' + form_id + ' ' + for_class).each(function () {
        appendError(this);
        let string = $(this).val().toLowerCase();
        if (!string || string == '') {
            return true;
        }
        let urlPattern = /^(https?:\/\/)?([\w-]+(\.[\w-]+)+)(:\d{1,5})?(\/.*)?$/i;
        // Test the URL against the pattern
        if (urlPattern.test(string)) {
            appendError(this, "");
        } else {
            error_flags = false;
            appendError(this, "Please Enter a valid Url");
        }
    });
    return error_flags;
}

function isValidEmail(email) {
    let regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}

function validateEmailFields(form_id, for_class = ".valid-email") {
    let error_flags = true;
    $('#' + form_id + ' ' + for_class).each(function () {
        appendError(this);
        let string = $(this).val();
        if (string == '') {
            error_flags = false;
            appendError(this, "This Field is Required");
        }
        if (isValidEmail(string) == false) {
            error_flags = false;
            appendError(this, "Please Enter a valid Email");
        }
    });
    return error_flags;
}

function validateMobileNumber(form_id, for_class = ".mobile-number") {
    let error_flags = true;
    $('#' + form_id + ' ' + for_class).each(function () {
        appendError(this);
        let string = $(this).val();
        if (string == '') {
            error_flags = false;
            appendError(this, "This Field is Required");
        }
        else if (string.length != 10) {
            appendError(this, 'Mobile Number Should Be 10 Digit');
            error_flags = false;
        }
    });
    return error_flags;
}

function isLetterOnly(form_id, for_class="letter-only") {
    let error_flags = true;
    $('#' + form_id + ' ' + for_class).each(function () {
        appendError(this);
        let string = $(this).val();
        let pattern = /^[A-Za-z]+$/;
        if (string == '') {
            error_flags = false;
            appendError(this, "This Field is Required");
        }
        else if (!string.match(pattern)) {
            error_flags = false;
            appendError(this, "Please Enter a valid Short Code.");
        }
    });
    return error_flags;
}

function removeCharacters(obj, ext="#") {
    let string = $(obj).val();
    let characters = ext.split('/');
    let updated_str = string;
    for (let index = 0; index < characters.length; index++) {
        updated_str = updated_str.replace(characters[index], '');
    }
    $(obj).val(updated_str);
}
function removeExtraWords(_this, maxWords) {
    let error_flags = true;
    let words = $(_this).val().trim().split(/\s+/);
    let wordCount = words.length;
    if (wordCount > maxWords) {
        appendError(_this, 'Organization Description Word Length should less than '+(maxWords+1)+' words');
        let truncatedText = words.slice(0, maxWords).join(' ');
        $(_this).val(truncatedText);
        error_flags = false;
    } else {
        appendError(_this);
    }
    return error_flags;
}