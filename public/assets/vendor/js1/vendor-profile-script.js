
$(document).ready(function () {
    setInternationalStateCityValidation(true);
    $('.branch-country').each(function () {
        validateInternationalBranchStateCity(this);
    });
    disableFormAutocomplete();
});

function addMoreBranchFields() {
    let row_id = $(".branch-row").last().data('row-id') || 0;
    let new_row_id = parseInt(row_id) + 1;
    let myRules = getValidationRules();

    let branch_html = `
                <div class="row branch-row" data-row-id="`+ new_row_id + `">                    
                    <div class='add_remove'>
                        <h4 class="frm_head">Branch Information <span class="branch-serial-no">1</span></h4>
                        <a href="javascript:void(0);" class=" btn-rfq btn-rfq-danger btn-rfq-sm" onclick='removeBranchAddField(this)'><i class="fa fa-trash"></i> Remove</a>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Branch Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control required text-upper-case" name="branch_name[]" value="" placeholder="Enter Branch Name" maxlength="255" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')">
                        <input type="hidden" name="edit_id_branch[]" value="0">
                    </div>
                    <div class="form-group col-md-6">
                        <label>
                            <span class="gst-field-label-name">GSTIN/VAT`+(myRules.is_indian ? '' : '<i title="Please enter your Tax Identification Number" class="bi bi-info-circle-fill" aria-hidden="true"></i>')+`</span>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control required branch-gstin-vat" name="branch_gstin[]" value="" placeholder="Enter GSTIN/VAT" maxlength="`+ myRules.branch_gstvat_length + `" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&amp;\(\)\+,\- ]/,'')">
                    </div>
                    <div class="form-group col-md-12">
                        <label>Registered Address<span class="text-danger">*</span></label>
                        <textarea class="form-control required" name="branch_address[]" placeholder="Enter Registered Address" maxlength="1700"></textarea>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Country<span class="text-danger">*</span></label>
                        <select class="form-select branch-country disabled branch-country-a`+ new_row_id + ` required" name="branch_country[]"
                            onchange="getState('branch-country-a`+ new_row_id + `', 'branch-state-a` + new_row_id + `', 'branch-city-a` + new_row_id + `')" >
                            `+ branch_country + `
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>State<span class="text-danger">*</span></label>
                        <select class="form-select branch-state branch-state-a`+ new_row_id + ` `+(myRules.is_indian ? 'required' : '')+` "
                            name="branch_state[]">
                            <option value="">Select State</option>
                            `+ branch_state + `
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Pincode<span class="text-danger">*</span></label>
                        <input type="text" class="form-control branch-pincode required" name="branch_pincode[]" value=""
                        onkeypress="return validatePinCode(event, this)" placeholder="Enter Pin Code" minlength="6" maxlength="6">
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label>Name of Authorized Person & Designation<span class="text-danger">*</span></label>
                        <input type="text" class="form-control required" name="branch_authorized_designation[]" value="" placeholder="Enter Name of Authorized Person & Designation" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+,\- ]/,'')">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Mobile<span class="text-danger">*</span></label>
                        <input type="text" class="form-control validate-max-length my-mobile-number required" name="branch_mobile[]" value=""
                        data-maxlength="`+myRules.mobile_max_length+`" data-minlength="`+myRules.mobile_min_length+`" placeholder="Enter Mobile">
                    </div>

                    <div class="form-group col-md-6">
                        <label>Email<span class="text-danger">*</span></label>
                        <input type="email" class="form-control valid-email required" name="branch_email[]" value="" placeholder="Enter Email" oninput="this.value=this.value.replace(/[^a-zA-Z0-9.\&\(\)\+\@,\- ]/,'')">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Status<span class="text-danger">*</span></label>
                        <div class="custom-file branch-toggle-div">
                            <label class="radio-inline mr-3">
                                <label class="switch">
                                    <input onchange="branchStatus(this)" class="branch-status required" value="1" type="checkbox" checked>
                                    <span class="slider round"></span>
                                </label>
                            </label>
                            <input class="branch-status-hidden" value="1" type="hidden" name="branch_status[]">
                        </div>
                    </div>
                </div>
    `;
    
    $("#branch_container").append(branch_html);

    rewiesBranchSerialNumber();

    $('#branch_container .branch-country').each(function () {
        validateInternationalBranchStateCity(this);
    });

    validateMinMaxLength('.validate-max-length');
    disableFormAutocomplete();

    $('.branch-gstin-vat').on('input', function () {
        // Replace anything that is not a letter or number
        $(this).val($(this).val().replace(/[^a-zA-Z0-9]/g, ''));
    });

    if(!myRules.is_indian){
        $(".branch-row").last().find(".branch-country").val($('.organization-country').val()).trigger("change");
    }
}

function removeBranchAddField(element) {
    element.closest(".branch-row").remove();
    rewiesBranchSerialNumber();
}

function rewiesBranchSerialNumber() {
    let new_sr = 1;
    $(".branch-row").find(".branch-serial-no").each(function () {
        $(this).html(new_sr);
        new_sr++;
    });
}

function branchStatus(_this) {
    if (_this.checked) {
        $(_this).parents(".branch-toggle-div").find(".branch-status-hidden").val(1);
    } else {
        $(_this).parents(".branch-toggle-div").find(".branch-status-hidden").val(2);
    }
}

function getValidationRules(){
    let rules = {};
    if ($('.organization-country').val() == 101) {
        rules = {
            is_indian: true,
            mobile_min_length: 10,
            mobile_max_length: 10,
            gstvat_length: 15,
            branch_gstvat_length: 15,
        };
    }else{
        rules = {
            is_indian: false,
            mobile_min_length: 1,
            mobile_max_length: 25,
            gstvat_length: 150,
            branch_gstvat_length: 150,
        };
    }
    return rules;
}
function reValidateRegistrationDoc(_this){
    if(_this.id =='registration-msme-file' && _this.value!=''){
        $('.registration-msme').addClass('required');
    }else{
        $('.registration-msme').removeClass('required');
    }
    if(_this.id =='registration-iso-file' && _this.value!=''){
        $('.registration-iso').addClass('required');
    }else{
        $('.registration-iso').removeClass('required');
    }
}

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

$('#other-description').on('blur', function() {
    removeExtraWords(this, 300);
});

$(document).on("change", '.organization-country', function () {
    setInternationalStateCityValidation(false);
});
function setInternationalStateCityValidation(is_first) {
    let myRules = getValidationRules();
    if (myRules.is_indian) {
        $(".organisation-state, .organisation-city, .organisation-pincode").addClass('required');
        $(".gstin-vat").attr("maxlength", myRules.gstvat_length);
        $(".gst-field-label-name").html("GSTIN/VAT");

        $(".my-mobile-number").attr("data-maxlength", myRules.mobile_max_length).attr("data-minlength", myRules.mobile_min_length);
        $(".branch-state, .branch-city, .branch-pincode").addClass('required');
        $(".branch-gst-lable").html('GSTIN/VAT<span class="text-danger">*</span>');
    } else {
        $(".organisation-state, .organisation-city, .organisation-pincode").removeClass('required');
        $(".gstin-vat").attr("maxlength", myRules.gstvat_length);
        appendError(".organisation-state, .organisation-city, .organisation-pincode, .branch-state, .branch-city, .branch-pincode");
        $(".gst-field-label-name").html('GSTIN/VAT<i title="Please enter your Tax Identification Number" class="bi bi-info-circle-fill" aria-hidden="true"></i>');
        
        $(".my-mobile-number").attr("data-maxlength", myRules.mobile_max_length).attr("data-minlength", myRules.mobile_min_length);
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
    let myRules = getValidationRules();
    let current_row = $(_this).parents(".branch-row");
    if (current_row.find(".branch-country").val() == 101) {
        current_row.find(".branch-state").addClass('required');
        current_row.find(".branch-city").addClass('required');
        current_row.find('.branch-gstin-vat').attr("maxlength", myRules.branch_gstvat_length);
    } else {
        current_row.find(".branch-state").removeClass('required');
        current_row.find(".branch-city").removeClass('required');
        current_row.find(".branch-pincode").removeClass('required');
        current_row.find('.branch-gstin-vat').attr("maxlength", myRules.branch_gstvat_length);
    }
}

$('.gstin-vat').on('blur', function() {
    if (getValidationRules().is_indian) {
        validateGSTINVat(this);
        checkUniqueGstNumber(this);
    }else{
        $(this).val(($(this).val()).toUpperCase());
    }
});

let validateVendorProfile = function() {
    $('#other-description').val($('#other-description').val().trim());

    let status = validateFormFields("vendor-profile-form", '.required');

    if (!validateFileFields('vendor-profile-form', '.required-file')) {
        status = false;
    }
    if ($('.website-url').val()!='' && !validateURL('vendor-profile-form', '.website-url')) {
        status = false;
    }

    if (getValidationRules().is_indian) {
        if (!validatePinCodeField(".organisation-pincode")) {
            status = false;
        }
        if ($(".branch-row").length>0 ) {
            if ($(".branch-row").length>0 && !validatePinCodeField(".branch-pincode")) {
                status = false;
            }
        }
        if (!validateGSTINVat(".gstin-vat")) {
            status = false;
        }

        if (!validateMobileNumber('vendor-profile-form', '.my-mobile-number')) {
            status = false;
        }
    } else {
        appendError(".gstin-vat");
        if ($(".gstin-vat").val() == '' || $(".gstin-vat").val() == '0') {
            appendError(".gstin-vat", "This Field is Required");
            status = false;
        }
        if ($(".branch-row").length>0 ) {
            $('.my-mobile-number').each(function () {
                appendError(this);
                if ($(this).val() == '' || $(this).val() == '0') {
                    appendError(this, "This Field is Required");
                    status = false;
                }
            });
        }
    }
    if ($(".branch-row").length>0 ) {
        if (!validateEmailFields('vendor-profile-form', ".valid-email")) {
            status = false;
        }
        
        $('#vendor-profile-form .branch-gstin-vat').each(function () {
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
    }

    if($('#other-description').val()==''){
        status = false;
        appendError("#other-description", "This Field is Required");
    }else{
        if(!removeExtraWords('#other-description', 300)){
            status = false;
        }
    }
    return status;
}
