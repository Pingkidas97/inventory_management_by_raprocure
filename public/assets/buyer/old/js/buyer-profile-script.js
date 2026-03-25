
$(document).ready(function () {
    setInternationalStateCityValidation(true);
    $('.branch-country').each(function () {
        validateInternationalBranchStateCity(this);
    });
    disableFormAutocomplete();
    cleanDivisionFieldClass();
});

/****add-more-manage-fields****/
function addMoreTopManagementDetails() {
    let myRules = getValidationRules();
    const container = document.getElementById("load-container");
    const newField = document.createElement('div');
    newField.classList.add("row", "tmd-row");
    newField.innerHTML = `  <div class='add_remove'> <h4 class="frm_head"><span class="tmd-serial-no">1</span>. Top Management Details Information</h4>
                                <a href="javascript:void(0);" class=" btn-rfq btn-rfq-danger btn-rfq-sm" onclick='removeaddMoreManageField(this)'><i class="fa fa-trash"></i> Remove</a>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Name<span class="text-danger">*</span></label>
                                <input type="text" class="form-control required text-uppercase" name="tdm_name[]" value="" maxlength="255" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')" placeholder="Enter Name">
                                <input type="hidden" name="edit_id_tmd[]" value="0">
                            </div>
                            <div class="form-group col-md-6">
                                <div class="col-12">
                                    <label>Designation<span class="text-danger">*</span></label>
                                    <select class="form-select required" name="tdm_top_management_designation[]">
                                        `+ tmd_designation + `
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Mobile<span class="text-danger">*</span></label>
                                <input type="text" class="form-control validate-max-length my-mobile-number required" name="tdm_mobile[]" value="" data-maxlength="`+myRules.mobile_max_length+`" data-minlength="`+myRules.mobile_min_length+`" placeholder="Enter Mobile">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Email<span class="text-danger">*</span></label>
                                <input type="email" class="form-control valid-email required" name="tdm_email[]" value="" placeholder="Enter Email">
                            </div>`;
    container.appendChild(newField);
    rewiesTDMSerialNumber();
}

function removeaddMoreManageField(element) {
    element.closest(".tmd-row").remove();
    rewiesTDMSerialNumber();
}

function addMoreBranchFields() {
    let row_id = $(".branch-row").last().data('row-id') || 0;
    let new_row_id = parseInt(row_id) + 1;
    let myRules = getValidationRules();

    let branch_html = `
                <div class="row branch-row" data-row-id="`+ new_row_id + `">
                    <div class='add_remove'>
                        <h4 class="frm_head">BRANCH/UNIT <span class="branch-serial-no">1</span></h4> 
                        <a href="javascript:void(0);" class=" btn-rfq btn-rfq-danger btn-rfq-sm" onclick='removeBranchAddField(this)'><i class="fa fa-trash"></i> Remove</a>
                    </div>
                    <div class="form-group col-md-12">
                        <label>Branch/Unit Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control required text-uppercase" name="branch_name[]" value="" placeholder="Enter Branch/Unit Name" maxlength="255">
                        <input type="hidden" name="edit_id_branch[]" value="0">
                    </div>
                    <div class="form-group col-md-12">
                        <label>Address<span class="text-danger">*</span></label>
                        <input type="text" class="form-control required" maxlength="1700"
                            name="branch_address[]" value="" placeholder="Enter Address">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Country<span class="text-danger">*</span></label>
                        <select class="form-select branch-country disabled branch-country-a`+ new_row_id + ` required" name="branch_country[]"
                            onchange="getState('branch-country-a`+ new_row_id + `', 'branch-state-a` + new_row_id + `', 'branch-city-a` + new_row_id + `')" >
                            `+ branch_country + `
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>State<span class="text-danger">*</span></label>
                        <select class="form-select branch-state branch-state-a`+ new_row_id + ` `+(myRules.is_indian ? 'required' : '')+` "
                            
                            name="branch_state[]">
                            <option value="">Select State</option>
                            `+ branch_state + `
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Pincode<span class="text-danger">*</span></label>
                        <input type="text" class="form-control branch-pincode `+(myRules.is_indian ? 'required' : '')+` " name="branch_pincode[]" value=""
                        onkeypress="return validatePinCode(event, this)" placeholder="Enter Pin Code"
                        onblur="validatePinCodeWithCountry(this, true)">
                    </div>

                    <div class="form-group col-md-12">
                        <div class="row">
                            <div class="form-group col-xl-3 col-md-6 col-12">
                                <label class="branch-gst-lable">GSTIN/VAT`+(myRules.is_indian ? '' : '<i title="Please enter your Tax Identification Number" class="bi bi-info-circle-fill" aria-hidden="true"></i>')+`<span class="text-danger">*</span></label>
                                <input type="text" class="form-control branch-gstin-vat required" name="branch_gstin[]" value="" maxlength="`+myRules.branch_gstvat_length+`" onblur="validateGSTVatWithCountry(this, true)" placeholder="`+(myRules.is_indian ? 'Enter GSTIN/VAT' : 'Enter your Tax Identification Number')+`">
                            </div>
                            <div class="col-xl-3 col-md-6 col-12 file-browser">
                                <span class="text-dark">(File Type: JPG, JPEG, PDF) <span class="text-danger">*</span></span>
                                <div class="file-browse">
                                    <span class="button button-browse">
                                    Select <input type="file" class="logo" name="branch_gstin_file[]" value="" onchange="validateFile(this, 'JPG/JPEG/PDF')">
                                    </span>
                                    <input type="text" class="form-control" placeholder="Upload GSTIN/VAT" readonly="">
                                </div>    
                            </div>
                            <div class="form-group col-xl-6 col-md-12 col-12">
                                <label>Name of Authorized Person<span class="text-danger">*</span></label>
                                <input type="text" class="form-control required" name="branch_authorized_name[]" value="" placeholder="Enter Name of Authorized Person">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label>Designation of Authorized Person<span class="text-danger">*</span></label>
                        <input type="text" class="form-control required" name="branch_authorized_designation[]" value="" placeholder="Enter Designation of Authorized Person">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Mobile<span class="text-danger">*</span></label>
                        <input type="text" class="form-control validate-max-length my-mobile-number required" name="branch_mobile[]" value="" data-maxlength="`+myRules.mobile_max_length+`" data-minlength="`+myRules.mobile_min_length+`" placeholder="Enter Mobile">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Email<span class="text-danger">*</span></label>
                        <input type="email" class="form-control valid-email required" name="branch_email[]" value="" placeholder="Enter Email">
                    </div>
                    <div class="form-group col-md-6">
                        <div class="row">
                            <div class="col-12">
                                <label>Products Output/Products Manufactured</label>
                                <input type="text" class="form-control" name="branch_output_details[]" value="" maxlength="1700" placeholder="Enter Products Output/Products Manufactured">
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Annual Capacity in Tonnage</label>
                        <input type="text" placeholder="Enter Annual Capacity in Tonnage"
                            class="form-control" name="branch_installed_capacity[]" value="" onkeypress='return event.charCode >= 48 && event.charCode <= 57'>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Division<span class="text-danger">*</span></label>
                        <select class="form-select division-sumo-select required" name="branch_categories[`+ new_row_id + `][]" multiple>
                            `+ branch_division + `
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Status<span class="text-danger">*</span></label>
                        <div class="custom-file branch-toggle-div">
                            <label class="radio-inline mr-3">
                                <label class="switch">
                                    <input onchange="branchStatus(this)" class="branch-status required" value="1" type="checkbox" checked >
                                    <span class="slider round"></span>
                                </label>
                            </label>
                            <input class="branch-status-hidden" value="1" type="hidden" name="branch_status[]">
                        </div>
                    </div>
                </div>`;
    $("#branch_container").append(branch_html);

    $('.division-sumo-select').SumoSelect({ selectAll: true, csvDispCount: 7, placeholder: 'Select Division' });
    rewiesBranchSerialNumber();

    $('#branch_container .branch-country').each(function () {
        validateInternationalBranchStateCity(this);
    });

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
    document.querySelectorAll('input[type="file"]').forEach(function (input) {
        input.addEventListener('change', validateFileSize);
    });
    validateMinMaxLength('.validate-max-length');
    disableFormAutocomplete();
    $('.branch-gstin-vat').on('input', function () {
        // Replace anything that is not a letter or number
        $(this).val($(this).val().replace(/[^a-zA-Z0-9]/g, ''));
    });

    if($('.organization-country').val()!=101){
        $(".branch-row").last().find(".branch-country").val($('.organization-country').val()).trigger("change");
    }

    cleanDivisionFieldClass();
}

function removeBranchAddField(element) {
    element.closest(".branch-row").remove();
    rewiesBranchSerialNumber();
}

function rewiesTDMSerialNumber() {
    let new_sr = 1;
    $(".tmd-row").find(".tmd-serial-no").each(function () {
        $(this).html(new_sr);
        new_sr++;
    });
}
function rewiesBranchSerialNumber() {
    let new_sr = 1;
    $(".branch-row").find(".branch-serial-no").each(function () {
        $(this).html(new_sr);
        new_sr++;
    });
}

$(document).on("change", '.organization-country', function () {
    setInternationalStateCityValidation(false);
});

function getValidationRules(){
    let rules = {};
    if ($('.organization-country').val() == 101) {
        rules = {
            is_indian: true,
            mobile_min_length: 10,
            mobile_max_length: 10,
            branch_gstvat_length: 15,
        };
    }else{
        rules = {
            is_indian: false,
            mobile_min_length: 1,
            mobile_max_length: 25,
            branch_gstvat_length: 150,
        };
    }
    return rules;
}

function setInternationalStateCityValidation(is_first) {
    if ($('.organization-country').val() == 101) {
        $(".organisation-state, .organisation-city, .organisation-pincode").addClass('required');
        $(".gstin-vat").attr("maxlength", "15");
        $(".organisation-pan-number").attr("maxlength", "10");
        $(".gst-field-label-name").html("GSTIN/VAT");
        $(".buyer-pan-card-input-field").removeClass("d-none").find(".organisation-pan-number").addClass("required");
        $(".buyer-pan-card-upload-field").removeClass("col-sm-12").addClass("col-sm-7");

        $(".my-mobile-number").attr("data-maxlength", 10).attr("data-minlength", 10);
        $(".branch-state, .branch-city, .branch-pincode").addClass('required');
        $(".branch-gst-lable").html('GSTIN/VAT<span class="text-danger">*</span>');
    } else {
        $(".organisation-state, .organisation-city, .organisation-pincode").removeClass('required');
        $(".gstin-vat").attr("maxlength", "150");
        $(".organisation-pan-number").attr("maxlength", "100");
        appendError(".organisation-state, .organisation-city, .organisation-pincode, .branch-state, .branch-city, .branch-pincode");
        $(".gst-field-label-name").html("Please enter your Tax Identification Number");
        $(".buyer-pan-card-input-field").addClass("d-none").find(".organisation-pan-number").removeClass("required");
        $(".buyer-pan-card-upload-field").removeClass("col-sm-7").addClass("col-sm-12");
        
        $(".my-mobile-number").attr("data-maxlength", 25).attr("data-minlength", 1);
        $(".branch-state, .branch-city, .branch-pincode").removeClass('required');
        $(".branch-gst-lable").html('GSTIN/VAT<i title="Please enter your Tax Identification Number" class="bi bi-info-circle-fill" aria-hidden="true"></i><span class="text-danger">*</span>');
    }
    if(!is_first){
        $(".branch-country").val($('.organization-country').val()).trigger("change");
    }
}
$(document).on("change", '.branch-country', function () {
    validateInternationalBranchStateCity(this);
});
function validateInternationalBranchStateCity(_this) {
    let current_row = $(_this).parents(".branch-row");
    if (current_row.find(".branch-country").val() == 101) {
        current_row.find(".branch-state").addClass('required');
        current_row.find(".branch-city").addClass('required');
        current_row.find('.branch-gstin-vat').attr("maxlength", "15");
    } else {
        current_row.find(".branch-state").removeClass('required');
        current_row.find(".branch-city").removeClass('required');
        current_row.find(".branch-pincode").removeClass('required');
        current_row.find('.branch-gstin-vat').attr("maxlength", "150");
    }
}

function validatePanCardWithCountry(_this) {
    $(_this).val(($(_this).val()).toUpperCase());
    let pan_number = $(_this).val();
    appendError(_this);
    if (pan_number == '') {
        appendError(_this, "Please enter pan number");
        toastr.error("Please enter pan number");
        return false;
    }
    if ($('.organization-country').val() == 101) {
        validatePanCardField(_this);
    }
}

function branchStatus(_this) {
    if (getActiveBranchCount() == 0) {
        alert("All the branches can't be made inactive. One branch must stay.");
        if (_this.checked) {
            $(_this).prop("checked", false);
        } else {
            $(_this).prop("checked", true);
        }
        return false;
    }
    if (_this.checked) {
        $(_this).parents(".branch-toggle-div").find(".branch-status-hidden").val(1);
    } else {
        $(_this).parents(".branch-toggle-div").find(".branch-status-hidden").val(2);
    }
}
function getActiveBranchCount() {
    let active_branch_count = 0;
    $(".branch-status").each(function (val, index) {
        if (this.checked) {
            active_branch_count++;
        }
    });
    return active_branch_count;
}
function validateGSTVatWithCountry(_this, is_branch = false) {
    $(_this).val(($(_this).val()).toUpperCase());
    let gst_number = $(_this).val();

    appendError(_this);
    if (gst_number == '') {
        appendError(_this, "This Field is Required");
        return false;
    }
    if (is_branch == true) {
        if ($(_this).parents(".branch-row").find(".branch-country").val() == 101) {
            validateGSTINVat(_this);
        }
    } else {
        if ($('.organization-country').val() == 101) {
            validateGSTINVat(_this);
        }
        checkUniqueGstNumber(_this);
    }
}
$('.gstin-vat, .branch-gstin-vat').on('input', function () {
    // Replace anything that is not a letter or number
    $(this).val($(this).val().replace(/[^a-zA-Z0-9]/g, ''));
});

$('.organisation-short-code').on('change', function() {
    isLetterOnly('buyer-profile-form', ".organisation-short-code");
});
$('.subscribe-news-letter').on('click', function() {
    if ($(this).prop('checked')) {
        $($(".subscribe-news-letter-hidden")).val(1);
    } else {
        $($(".subscribe-news-letter-hidden")).val(2);
    }
});

$('input, textarea').on('keyup', function() {
    if($(this).val()!=""){
        $(this).closest('span.error-message').text('');
        $(this).siblings('span.error-message').text('');
    }
});
$('select').on('change', function() {
    $(this).closest('span.error-message').text('');
    $(this).siblings('span.error-message').text('');
});

$('#organisation-description').on('blur', function() {
    let status = removeExtraWords(this, 500);
});

function cleanDivisionFieldClass(){
    $("p.CaptionCont.SelectBox.form-select").removeClass("division-sumo-select").removeClass("required");
}

let validateBuyerProfile = function() {
    $('#organisation-description').val($('#organisation-description').val().trim());

    let status = validateFormFields("buyer-profile-form", '.required');

    if (!validateFileFields('buyer-profile-form', '.required-file')) {
        status = false;
    }
    if ($('.website-url').val()!='' && !validateURL('buyer-profile-form', '.website-url')) {
        status = false;
    }

    if ($('.organization-country').val() == 101) {
        if (!validatePanCardField(".organisation-pan-number")) {
            status = false;
        }
        if (!validatePinCodeField(".organisation-pincode")) {
            status = false;
        }
        if (!validatePinCodeField(".branch-pincode")) {
            status = false;
        }
        if (!validateGSTINVat(".gstin-vat")) {
            status = false;
        }

        if (!validateMobileNumber('buyer-profile-form', '.my-mobile-number')) {
            status = false;
        }
    } else {
        appendError(".gstin-vat");
        if ($(".gstin-vat").val() == '' || $(".gstin-vat").val() == '0') {
            appendError(".gstin-vat", "This Field is Required");
            status = false;
        }
        appendError(".organisation-pan-number");

        $('.my-mobile-number').each(function () {
            appendError(this);
            if ($(this).val() == '' || $(this).val() == '0') {
                appendError(this, "This Field is Required");
                status = false;
            }
        });
    }

    if (!validateEmailFields('buyer-profile-form', ".valid-email")) {
        status = false;
    }

    $('#buyer-profile-form .branch-gstin-vat').each(function () {
        $(this).val(($(this).val()).toUpperCase());

        appendError(this);
        if ($(this).val() == '' || $(this).val() == '0') {
            appendError(this, "This Field is Required");
            status = false;
        }
        if ($(this).parents(".branch-row").find(".branch-country").val() == 101) {
            if (!validateGSTINVat(this)) {
                status = false;
            }
        }
    });

    if (getActiveBranchCount() == 0) {
        alert("All the branches can't be made inactive. One branch must stay.");
        status = false;
    }

    if (!isLetterOnly('buyer-profile-form', ".organisation-short-code")) {
        status = false;
    }

    if (!validateShortCode()) {
        status = false;
    }

    if($('#organisation-description').val()==''){
        status = false;
        appendError("#organisation-description", "This Field is Required");
    }else{
        if(!removeExtraWords('#organisation-description', 500)){
            status = false;
        }
    }
    return status;
}
