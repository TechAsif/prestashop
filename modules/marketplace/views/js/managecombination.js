/**
* 2010-2021 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through LICENSE.txt file inside our module
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright 2010-2021 Webkul IN
* @license LICENSE.txt
*/

$(document).ready(function() {
    //Change attribute for getting its value (Create/Update Combination Page)
    $(document).on("change", "#attribute_select", function() {
        var attribute_group_id = $(this).val();
        var mp_id_product = $('#mp_id_product').val();

        $.ajax({
            url: path_managecombination,
            data: {
                attribute_group_id: attribute_group_id,
                id_mp_product: mp_id_product,
                token: $('#wk-static-token').val(),
                action: "getAttributeValue",
                ajax: true,
            },
            dataType: 'json',
            success: function(data) {
                $("#attribute_value_select").empty();
                if (data.length != 0) {
                    $.each(data, function(key, item) {
                        $("#attribute_value_select").append("<option value='" + item.id + "'>" + item.name + "</option>");
                    });
                }
            }
        });
    });

    //Add attribute in selected attribute (Create/Update Combination Page)
    $("#wk_add_attribute_button").on("click", function() {
        var attribute_group_name_id = $("#attribute_select option:selected").val();
        var attribute_val_id = $("#attribute_value_select option:selected").val();
        var attribute_group_name = $("#attribute_select option:selected").text();
        var attribute_val = $("#attribute_value_select option:selected").text();
        var new_attribute_name = attribute_group_name + " : " + attribute_val;

        if (attribute_group_name_id == "") {
            $('#attribute_error').html(req_attr);
            return false;
        } else if (attribute_val_id == "") {
            $('#attribute_error').html(req_attr_val);
            return false;
        } else if (selected_attribute_group.indexOf(attribute_group_name_id) > -1) {
            $('#attribute_error').html(attr_already_selected);
            return false;
        } else {
            selected_attribute_group[selected_attribute_group.length] = attribute_group_name_id;
        }

        $('#attribute_error').html('');
        $('#selected_combination_list').append('<p class="wk_each_group" id="wk_each_group_' + attribute_group_name_id + '"><span>' + new_attribute_name + '</span> <span class="wk_delete_attribute_option" onclick="deleteSelectedAttribute(' + attribute_group_name_id + ')">x</span></p>')

        //Save value in hidden select option
        $("#product_att_list").append('<option value="' + attribute_val_id + '" id="group_id_' + attribute_group_name_id + '">' + new_attribute_name + '</option>');
    });

    $("#mp_available_date").datepicker({
        dateFormat: "yy-mm-dd",
    });

    //create combination Validation form (Create/Update Combination Page)
    $(document).on('click', '#submitCombination, #submitStayCombination', function() {
        $("#product_att_list option").prop('selected', true);
        var attrib_id = $("#product_att_list option").val();

        if (isNaN(attrib_id)) {
            $('#attribute_error').html(attribute_req);
            return false;
        } else {
            $('#attribute_error').html('');
            return true;
        }
    });

    //when change Impact on price (tax excl.) on keyup (Create/Update Combination Page)
    $('#mp_price').on('keyup', function() {
        var priceTE = parseFloat($(this).val());
        var newPrice = priceTE * ((tax_rate / 100) + 1);

        //$('#attribute_priceTI').val((isNaN(newPrice) == true) ? '' : newPrice.toFixed(2));

        displayProductFinalPrice(priceTE);
    });

    //when change Impact on price (tax incl.) on keyup (Create/Update Combination Page)
    // $('#attribute_priceTI').on('keyup', function() {
    //     var priceTI = parseFloat($(this).val());
    //     priceTI = (isNaN(priceTI)) ? 0 : priceTI;
    //     var newPrice = priceTI / ((tax_rate / 100) + 1);

    //     $('#mp_price').val((isNaN(newPrice) == true) ? '' : newPrice.toFixed(2));

    //     displayProductFinalPrice($('#mp_price').val());
    // });

    //on page load (Create/Update Combination Page)
    displayProductFinalPrice($('#mp_price').val());

    //Change default attibute (Combination List Page)
    $('.default_attribute').click(function(e) {
        e.preventDefault();
        var attribute_status = $(this).data('status');
        if (attribute_status == 1) {
            var mp_product_attribute_id = $(this).data('id');
            var controller = $(this).attr('data-controller');
            updateProductDefaultAttribute(mp_product_attribute_id, controller);
        } else {
            alert(noAllowDefaultAttribute);
            return false;
        }
    });

    //Change combination qty from product combination list of update product page
    $('.wk-combi-list-qty').blur(function(e) {
        e.preventDefault();
        var mp_product_attribute_id = $(this).data('id-combination');
        if (mp_product_attribute_id !== '') {
            var combi_qty = $(this).val();

            updateProductCombinationQty(mp_product_attribute_id, combi_qty);
        }
    });

    //Delete combination from combination list (Combination List Page)
    $('.delete_attribute').on('click', function(e) {
        e.preventDefault();
        var mp_product_attribute_id = $(this).data('id');

        if (confirm(confirm_delete_combination)) {
            deleteCombination(mp_product_attribute_id);
            return true;
        }

        return false;
    });

    //change combination status for activate/deactivate that combination (Combination List Page)
    $('.change_combination_status').on('click', function(e) {
        e.preventDefault();
        var mp_product_attribute_id = $(this).data('id');
        changeCombinationStatus(mp_product_attribute_id);
        return true;
    });

    //Confirm message for going to combinaiton generator page
    $(".generate_combination").click(function() {
        return confirm(generate_combination_confirm_msg);
    });
});

if (typeof selected_attribute_group !== 'undefined') {
    //Convert string to object
    if (selected_attribute_group.length !== 0) {
        selected_attribute_group = jQuery.parseJSON(selected_attribute_group);
    }
}

//Delete attribute group from selected attribute (Create/Update Combination Page)
function deleteSelectedAttribute(attribute_group_name_id) {
    var newarray = [];
    var index = 0;
    $.each(selected_attribute_group, function(i, item) {
        if (item != attribute_group_name_id) {
            newarray[index] = item;
            index++;
        }
    });
    selected_attribute_group = newarray;

    $('#wk_each_group_' + attribute_group_name_id).remove();
    $('#group_id_' + attribute_group_name_id).remove();
}

//Display product final price of combination
function displayProductFinalPrice(priceTE) {
    var final_product_price = parseFloat(parseFloat(priceTE) + parseFloat($('#mp_product_price').val())).toFixed(2);
    if (isNaN(final_product_price) || final_product_price < 0) {
        $('#attribute_final_product_price').html('0.00');
    } else {
        $('#attribute_final_product_price').html(final_product_price);
    }
}

function updateProductDefaultAttribute(mp_product_attribute_id, controller) {
    var default_product_attribute = $('#default_product_attribute').val();

    $('#default_attribute_' + default_product_attribute).show();
    $('#default_attribute_' + mp_product_attribute_id).hide();
    $('#default_product_attribute').attr('value', mp_product_attribute_id);
    var mp_product_id = $('#mp_product_id').val();

    $.ajax({
        url: path_sellerproduct,
        data: {
            ajax: true,
            token: $('input[name="token"]').val(),
            action: "updateDefaultAttribute",
            id_mp_product: mp_product_id,
            id_combination: mp_product_attribute_id
        },
        dataType: 'json',
        success: function(result, status, xhr) {
            if (result == '1') {
                $(".combination").removeClass("highlighted");
                $("#combination_" + mp_product_attribute_id).addClass("highlighted");
            } else {
                alert(some_error);
            }
        },
        error: function(xhr, status, error) {
            return 0;
        }
    });
}

function deleteCombination(mp_product_attribute_id) {
    if (mp_product_attribute_id !== '') {
        var mp_product_id = $('#mp_product_id').val();

        $.ajax({
            url: path_sellerproduct,
            data: {
                ajax: true,
                token: $('input[name="token"]').val(),
                action: "deleteMpCombination",
                id_mp_product: mp_product_id,
                id_combination: mp_product_attribute_id
            },
            dataType: 'json',
            success: function(result) {
                if (result == '1') {
                    $("#combination_" + mp_product_attribute_id).remove();
                } else {
                    alert(some_error);
                }
            },
            error: function(xhr, status, error) {
                return 0;
            }
        });
    }
}

function changeCombinationStatus(mp_product_attribute_id) {
    if (mp_product_attribute_id !== '') {
        var mp_product_id = $('#mp_product_id').val();

        $.ajax({
            url: path_sellerproduct,
            data: {
                ajax: true,
                token: $('input[name="token"]').val(),
                action: "changeCombinationStatus",
                id_mp_product: mp_product_id,
                id_combination: mp_product_attribute_id
            },
            dataType: 'json',
            success: function(result) {
                if (result == '1') {
                    //activate combination
                    $("#change_combination_" + mp_product_attribute_id + " img").attr({ 'src': mp_image_dir + 'icon/icon-check.png', 'title': enabled, 'alt': enabled });
                } else if (result == '0') {
                    //deactivate combination
                    $("#change_combination_" + mp_product_attribute_id + " img").attr({ 'src': mp_image_dir + 'icon/icon-close.png', 'title': disabled, 'alt': disabled });
                } else if (result == '-1') {
                    //Not allow to deactivate
                    alert(not_allow_todeactivate_combination);
                    return false;
                } else if (result == '2') {
                    //default attribute
                    window.location.href = window.location.href;
                } else {
                    alert(some_error);
                    return false;
                }
            },
            error: function(xhr, status, error) {
                return 0;
            }
        });
    }
}

function updateProductCombinationQty(mp_product_attribute_id, combi_qty) {

    var mp_product_id = $('#mp_product_id').val();

    $.ajax({
        url: path_sellerproduct,
        data: {
            ajax: true,
            token: $('input[name="token"]').val(),
            action: "updateMpCombinationQuantity",
            combi_qty: combi_qty,
            id_mp_product: mp_product_id,
            mp_product_attribute_id: mp_product_attribute_id
        },
        dataType: 'json',
        success: function(result) {
            if (result == '1') {
                showSuccessMessage(update_success);
            } else if (result == '0') {
                showErrorMessage(invalid_value);
            }
        },
        error: function(xhr, status, error) {
            return 0;
        }
    });
}

function showSuccessMessage(msg) {
    $.growl.notice({ title: "", message: msg });
}

function showErrorMessage(msg) {
    $.growl.error({ title: "", message: msg });
}

function showNoticeMessage(msg) {
    $.growl.notice({ title: "", message: msg });
}