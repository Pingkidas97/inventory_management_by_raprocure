
function rewiesSerialNumber(_this) {
    let new_sr = 1;
    $(_this).parents(".rfq-product-row").find(".variants-record").find(".table-tr").each(function() {
        $(this).find(".row-count-number").html(new_sr);
        $(this).find(".variant-order").val(new_sr);
        new_sr++;
    });
}

function rewiseProductSerialNumber() {
    let new_sr = 1;
    $(".product-order").each(function() {
        $(this).html(new_sr + ".");
        $(this).parent().find('.product-order-hidden').val(new_sr);
        new_sr++;
    });
}

function removeVariant(_this) {

    let rowCount = $(_this).parents('.variants-record').find('tr').length;
    // console.log("rowCount", rowCount);
    
    if (rowCount > 1) {
        if (confirm("Are you sure want to remove this Product Variant?")) {
            deleteRFQProductVariant(_this);
        }
    } else {
        alert("You Can't Delete Variant, at least One Variant is Compulsory.");
        $(_this).parents("tr").find(".remove-product-variant-file").trigger('click');
        $(_this).parents("tr").find(".old-attachment").val('');
        $(_this).parents("tr").find(".specification").val('');
        $(_this).parents("tr").find(".size").val('');
        $(_this).parents("tr").find(".quantity").val('');
        $(_this).parents("tr").find(".uom").selectOption('');

        $(_this).parents("tr").find(".specification").trigger('change');
        updateRFQProduct(_this);
    }
}

function updateCheckedUncheckedForAllVendors(){
    $('.vendor-list').each(function() {
        const rfq_form_box_section = $(this);
        const checkListing = rfq_form_box_section.find('.vendor-list-div');
        const rfq_form_box_selectAll = rfq_form_box_section.find('.select-product-all-vendor');
        const vendorCheckboxes = rfq_form_box_section.find('.vendor-input-checkbox');
        
        // code to reorder vendors in the current rfq_form_box_section
        const checkedVendors = vendorCheckboxes.filter(':checked').closest('div.form-check');
        const uncheckedVendors = vendorCheckboxes.not(':checked').closest('div.form-check');
        
        // Clear the list and re-append items in the current rfq_form_box_section
        checkListing.empty();
        checkListing.append(rfq_form_box_selectAll.closest('div.form-check')); // Add "Select All" at the top
        checkListing.append(checkedVendors); // Add checked vendors next
        checkListing.append(uncheckedVendors); // Add unchecked vendors at the end
    });
}

$(document).on("change keyup paste", ".search-product-vendor", function() {
    let input = $(this).val();
    let list_items = $(this).parents(".vendor-list").find(".vendor-input-checkbox");
    list_items.each(function(idx, li) {
        let text = $(this).data('vendor-name');
        text = text.toLowerCase();
        input = input.toLowerCase();
        if (text.indexOf(input) > -1) {
            $(this).parents("div.form-check").show();
        } else if (input == '') {
            $(this).parents("div.form-check").show();
        } else {
            $(this).parents("div.form-check").hide();
        }
    });
    markSelectedCurrentSelectAllCheckbox(this);
});
$(document).on("change", ".vendor-input-checkbox", function() {
    let _this = this;
    let vendor_id = $(this).val();
    let is_vendor_checked = $(this).prop("checked");
    $('.vendor-has-' + vendor_id + '-id').each(function() {                
        if($(this).data("product-id")!=$(_this).data("product-id")){
            $(this).prop("checked", is_vendor_checked);
        }
    });
    markSelectedSelectAllCheckbox();
    setTimeout(updateCheckedUncheckedForAllVendors, 300);
});
$(document).on("change", ".select-product-all-vendor", function() {
    $(this).parents(".vendor-list-div").find(".vendor-input-checkbox").prop("checked", $(this).prop("checked"));
});
function markSelectedSelectAllCheckbox() {
    let all_selected_vendor = [];
    $(".vendor-input-checkbox:checked").each(function () {
        const val = $(this).val();
        if (!all_selected_vendor.includes(val)) {
            all_selected_vendor.push(val);
        }
    });
    
    all_selected_vendor.forEach(function (vendorId) {
        $(".vendor-has-" + vendorId + "-id").prop("checked", true);
    });
    // Loop through each product form section to toggle "select all vendor" checkbox
    $(".product-form-section").each(function () {
        const $section = $(this);
        const $checkboxes = $section.find(".vendor-input-checkbox");
        const $visibleCheckboxes = $checkboxes.filter(function () {
            return $(this).closest("div").is(":visible");
        });
        const $checkedVisible = $visibleCheckboxes.filter(":checked");

        $section.find("input.select-product-all-vendor").prop(
            "checked",
            $visibleCheckboxes.length > 0 && $checkedVisible.length === $visibleCheckboxes.length
        );
    });
}
// function markSelectedSelectAllCheckboxOLD() {
//     let all_selected_vendor = new Array();
//     $(".vendor-input-checkbox:checked").each(function(){
//         if(!all_selected_vendor.includes($(this).val())){
//             all_selected_vendor.push($(this).val());
//         }
//     });
//     for (var i = 0; i < all_selected_vendor.length; i++) {
//         $(".vendor-has-"+all_selected_vendor[i]+"-id").prop('checked', true);
//     }
//     $(".product-form-section").each(function() {
//         if ($(this).find(".vendor-input-checkbox:checked").length == $(this).find(".vendor-input-checkbox").length) {
//             $(this).find("input.select-product-all-vendor").prop("checked", true);
//         } else {
//             var all_vendor=true;
//             $(this).find('.vendor-input-checkbox').each(function(){
//                 if($(this).closest("div").css('display')!='none'){
//                     if ($(this).prop('checked')==true){
//                     } else{
//                         all_vendor=false;
//                         return false; // Break out of the loop
//                     }
//                 }
//             });
//             if(all_vendor){
//                 $(this).find("input.select-product-all-vendor").prop("checked", true);
//             }else{
//                 $(this).find("input.select-product-all-vendor").prop("checked", false);
//             }	            
//         }
//     });
// }
function markSelectedCurrentSelectAllCheckbox(_this) {
    let select_all_vendor = true;
    $(_this).closest(".vendor-list").find(".vendor-input-checkbox").each(function () {
        if ($(this).is(":visible") && !$(this).prop("checked")) {
            select_all_vendor = false;
            return false; // Exit loop early
        }
    });
    $(_this).closest(".vendor-list").find("input.select-product-all-vendor").prop("checked", select_all_vendor);
}
// function markSelectedCurrentSelectAllCheckboxOLD(_this) {
//     let select_all_vendor=true;
//     $(_this).parents(".vendor-list").find(".vendor-input-checkbox").each(function(){
//         if($(this).closest("div").css('display')!='none'){
//             if ($(this).prop('checked')==false){
//                 select_all_vendor=false;
//                 return false; // Break out of the loop
//             }
//         }
//     });
    
//     if(select_all_vendor){
//         $(_this).parents(".vendor-list").find("input.select-product-all-vendor").prop("checked", true);
//     }else{
//         $(_this).parents(".vendor-list").find("input.select-product-all-vendor").prop("checked", false);
//     }
// }

function printVendorLocation(responce, vendor_location, int_vendor_location){
    let all_vendor_state = responce.all_states;
    let all_international_vendor_country = responce.all_country;
    
    let vendor_location_html = '';
    if (all_vendor_state.length > 0) {
        for (var i = 0; i < all_vendor_state.length; i++) {
            let state = all_vendor_state[i];
            let selected = '';
            if (vendor_location.map(Number).includes(state.id)) {
                selected = 'selected';
            }
            vendor_location_html += '<option value="' + state.id + '" '+selected+' class="domestic-vendor">' + state.name + '</option>';
        }
    }
    if (all_international_vendor_country.length > 0) {
        for (var i = 0; i < all_international_vendor_country.length; i++) {
            let country = all_international_vendor_country[i];
            let selected = '';
            if (int_vendor_location.map(Number).includes(country.id)) {
                selected = 'selected';
            }
            vendor_location_html += '<option value="' + country.id + '" '+selected+' class="domestic-vendor">' + country.name + '</option>';
        }
    }
    
    $('.location-sumo-select').html(vendor_location_html);
    $('.location-sumo-select')[0].sumo.reload();
}

$(document).on("click", "#search-product-for-rfq", function() {
    let p_name = $("#rfq-search-product-name").val();
    let p_id = $("#rfq-searched-product-id").val();

    if($(".product-form-section").length<=0){
        window.location.href = "{{ route('buyer.vendor.product', ['id'=>0]) }}".slice(0, -1)+p_id;
    }else{
        $("#dealer-type").val('');
        $('#location-type').html('');
        $("#location-type").SumoSelect().sumo.reload();

        $("#rfq-search-product-name").val(p_name);
        $("#rfq-searched-product-id").val(p_id);

        showSelectedProduct();
    }
});
$(document).on("change", ".vendor-product-checkbox", function () {
    if($(".vendor-product-checkbox:checked").length>0){
        $(".add-this-vendor-product-to-draft").addClass("disabled");
    }else{
        $(".add-this-vendor-product-to-draft").removeClass("disabled");
    }
});
$(document).on("change", ".select-all-searched-product-vendor", function() {
    if ($(this).prop("checked") == true) {
        $(".vendor-product-checkbox").prop("checked", true);
    } else {
        $(".vendor-product-checkbox").prop("checked", false);
    }
});
$(document).on("click", ".add-this-vendor-product-to-draft", function () {
    let vendors_id = new Array();
    vendors_id.push($(this).parents(".vendor-product-card").find(".vendor-product-checkbox").val());
    addToDraftRFQ(vendors_id);
});
$(document).on("click", "#add-selected-vendor-product-to-draft", function () {
    let vendors_id = new Array();
    if($(".vendor-product-checkbox:checked").length<=0){
        alert("Please Select at least one Vendor.");
        return false;
    }
    $(".vendor-product-checkbox:checked").each(function(){
        vendors_id.push($(this).val());
    });
    if(vendors_id.length==0){
        alert("Please Select at least one Vendor.");
        return false;
    }
    addToDraftRFQ(vendors_id);
});

function clearAddProductModal(){
    $('.location-sumo-select').html('<option value="">Select Location</option>');
    $('.location-sumo-select')[0].sumo.reload();
    $(".searched-product-card").html("");
    $("#rfq-searched-product-id, #rfq-search-product-name").val("");
    $("#add-product-to-rfq").modal("hide");
}

$(document).on("change", ".select-all-vendor", function() {
    if ($(this).prop("checked") == true) {
        $(this).parents('ul').find(".supplier-id").each(function(){
            if($(this).closest("li").css('display')!='none'){
                $(this).prop("checked", true);
            }
        });
    } else {
        $(this).parents('ul').find(".supplier-id").each(function(){
            if($(this).closest("li").css('display')!='none'){
                $(this).prop("checked", false);
            }
        });
        let this_prod_vendor = new Array();
        $(this).parents('ul').find(".supplier-input-checkbox").each(function(){
            if(!this_prod_vendor.includes($(this).val())){
                this_prod_vendor.push($(this).val());
            }
        });

        for (var i = 0; i < this_prod_vendor.length; i++) {
            $(".supplier-has-"+this_prod_vendor[i]+"-id").prop('checked', false);
        }
    }
    $(this).parents('ul').find(".supplier-id").first().trigger('change');
});

$(document).on("blur, change", ".sync-field-changes", function() {
    // add/update loader after original html intgreation
    // if(global_loder>0){
    //     updateRFQProduct(this);//update current product data
    //     return false;
    // }
    updateRFQProduct(this);//update current product data
    
    markSelectedSelectAllCheckbox();
});
$(document).on("blur, change", ".sync-draft-rfq-changes", function() {
    updateDraftRFQ();
});

$(document).on("click", ".remove-product-variant-file", function() {
    let brand_field = $(this).parents(".rfq-product-row").find('input[name="brand"]');
    let old_attachment = $(this).parents(".table-tr").find('.old-attachment').val();
    $(this).parents(".table-tr").find('.delete-attachment').val(old_attachment).attr("data-file", old_attachment);
    $(this).parents(".table-tr").find('.old-attachment').val('');
    $(this).parents(".table-tr").find(".attachment-link").html('');
    brand_field.trigger('change');
});

function validateRFQFile(obj) {
    let file = $(obj).get(0).files[0]; // Get the first file
    let extension = file.name.split('.').pop().toUpperCase();
    let fileSize = file.size;
    console.log(extension, fileSize);
    
    // Validate file extension
    if (extension != "PDF" && extension != "DOC" && extension != "DOCX" && extension != "JPEG" && extension != "JPG" &&
        extension != "PNG" && extension != "XLSX" && extension != "DWG" && extension != "CDR") {
        $(obj).val('');
        $(obj).attr('src', '');
        appendFileError(obj, "Invalid file extension.");
    }
    // Validate file size (maximum 1 MB)
    else if (fileSize > 1048576) {
        $(obj).val('');
        $(obj).attr('src', '');
        alert("File size exceeds the maximum limit of 1 MB."); // Display an alert
    }
    // If valid, clear any previous error messages
    else {
        appendFileError(obj);
    }
}
function appendFileError(obj, msg = '') {
    $(obj).parents('.file-browse').parent().find('.error-message').remove();
    if (msg) {
        alert(msg);
        $(obj).parents('.file-browse').parent().append('<span class="text-danger error-message view-btn-error">' + msg + '</span>');
    }
}

$('#openLocationFilter').on('click', function() {
    $('#locationSidebar').addClass('active');
});

$('#closeLocationSidebar').on('click', function() {
    $('#locationSidebar').removeClass('active');
});
function showVendorLocation(responce){
    let states = responce.all_states;
    let countries = responce.all_country;
    let location = '', checked = '';

    states.forEach((e) => {
        checked = '';
        if($(".domestic-vendor.vendor-location-has-"+ e.id+"-id").length){
            $(".domestic-vendor.vendor-location-has-"+ e.id+"-id").each(function(){
                if($(this).prop("checked")){
                    checked = 'checked';
                }
            });
        }

        location +=`
            <div class="form-check">
                <label class="form-check-label">
                    <input class="form-check-input vendor-location domestic-vendor-state" type="checkbox" onclick="locationFilter(this)" value="${e.name}" ${checked} data-location-id="${e.id}">
                    ${e.name}
                </label>
            </div>
        `;
    });
    countries.forEach((e) => {
        checked = '';
        if($(".international-vendor.vendor-location-has-"+ e.id+"-id").length){
            $(".international-vendor.vendor-location-has-"+ e.id+"-id").each(function(){
                if($(this).prop("checked")){
                    checked = 'checked';
                }
            });
        }

        location +=`
            <div class="form-check">
                <label class="form-check-label">
                    <input class="form-check-input vendor-location international-vendor-country" type="checkbox" ${checked} value="${e.name}" onclick="locationFilter(this)" data-location-id="${e.id}">
                    ${e.name}
                </label>
            </div>
        `;
    });
    $('#location-list-div').html(location);
}

function locationFilter(_this) {
    if($(".vendor-location:checked").length<=0){
        $(_this).prop("checked", true);
        alert("At least one location is required");
        return false;
    }
        
    $(".vendor-location-has-"+$(_this).data("location-id")+"-id").prop("checked", $(_this).prop("checked"));
    updateCheckedUncheckedForAllVendors();
    markSelectedSelectAllCheckbox();
}

$('#select-all-vendor').on('click', function() {
    $(".product-form-section").each(function(){
        $(this).find('.vendor-input-checkbox').prop('checked', true);
    });
    markSelectedSelectAllCheckbox();
});

$(document).on('click', 'a', function() {
    hrefAttribute = $(this).attr('href');
    let hrefTarget = $(this).attr('target');
    if(hrefTarget!='' && hrefTarget=="_blank"){
        window.open(hrefAttribute, '_blank').focus();
        return false;
    }
    if ($(this).hasClass("show-product-data") || $(this).hasClass("move-this-product-to-rfq") || $(this)
        .hasClass("add-all-to-rfq-btn") || $(this).hasClass("delte_product") || $(this).hasClass(
            "file-links") || $(this).hasClass("schedule-and-generate-rfq") || hrefAttribute.indexOf('#') > -1
    ) {} else {
        if ($(".product-form-section").length > 0) {
            if (confirm('Are you sure, you want to leave the page?') === false) {
                return false;
            } else {
                finalUpdateRFQ();
                saveformData(hrefAttribute);
            }
        }
    }
});
function saveformData(hreflocation = null) {
    if (hreflocation != null && hreflocation != '') {
        setTimeout(function() {
            window.location.href = hreflocation;
        }, 1000);
    } else {
        window.location.reload();
    }
}

$(document).on("blur", 'input[name="quantity[]"]', function() {
    let qty = $(this).val();
    if (parseFloat(qty) < 0.1) {
        $(this).val('');
        toastr.error('Product Quantity can not 0.');
    }
});


function validateRFQForm() {
    let status = true;
    // $('#buyer-price-basis').trigger("change"); 
    
    let error_msg = '';
    if (!validateRFQFields('.product-form-section', '.uom')) {
        status = false;
    }
    if (!validateRFQFields('.product-form-section', 'input[name="quantity[]"]')) {
        status = false;
    }
    let branch_unit = $('#buyer-branch').val();
    if (branch_unit == '') {
        error_msg = 'Branch/Unit is required';
        status = false;
    }
    if (status == false) {
        if(error_msg==''){
            error_msg = 'Please Enter all Manadatory Fields';
        }
        toastr.error(error_msg);
        return status;
    }
    
    if (!isSelectedVendors()) {
        status = false;
    }
    if (status == false) {
        toastr.error('Please select vendor for each product.');
    }
    return status;
}

function validateRFQFields(form_selector, for_class) {
    let error_flags = true;
    $('' + form_selector + ' ' + for_class).each(function() {
        appendError(this);
        if ($(this).val() == '') {
            error_flags = false;
            appendError(this, "Required*"); 
        }

    });

    return error_flags;
}

function isSelectedVendors() {
    let is_all_selected = true;
    $(".product-form-section").each(function() {
        $(this).find(".vendor-input-checkbox").parents(".vendor-list-div").find('.vendor-error').remove();
        if ($(this).find(".vendor-input-checkbox:checked").length <= 0) {
            is_all_selected = false;
            $(this).find(".vendor-input-checkbox").parents(".vendor-list-div").prepend('<div class="form-check text-danger"> Vendor is Required </div>');
        }
    });
    return is_all_selected;
}

function appendError(obj, msg = '') {
    $(obj).parent().find('.error-message').remove();
    if (msg) {
        $(obj).parent().append('<span class="text-danger error-message view-btn-error">' + msg + '</span>');
        setTimeout(removeErrorMessage, 3000);
    }
}
function removeErrorMessage() {
    $(".view-btn-error").remove();
}