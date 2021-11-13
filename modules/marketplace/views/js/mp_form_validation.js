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
    //Tab active code
    if ($('#active_tab').val() != '') {
        var active_tab = $('#active_tab').val();
        changeTabStatus(active_tab);
    }

    $('#SubmitProduct, #StayProduct, #mp_admin_saveas_button, #mp_admin_save_button').removeClass('wk_mp_disabled');
    //Seller registration form validation
    $('#sellerRequest,#updateProfile,#mp_seller_save_button,#mp_seller_saveas_button').on("click", function() {
        window.wkerror = false;

        getActiveTabAfterSubmitForm();

        if (typeof multi_lang !== 'undefined' && multi_lang == '1') {
            var default_lang = $('.seller_default_shop').data('lang-name');
            var shop_name = $('.seller_default_shop').val().trim();
            if (shop_name == '') {
                $('#wk_mp_form_error').text(req_shop_name_lang + ' ' + default_lang).show('slow');
                $('html,body').animate({
                    scrollTop: $("#wk_mp_form_error").offset().top - 10
                }, 'slow');
                return false;
            }
        }

        // validating all seller form field
        $('.wk_product_loader').show();
        $('#sellerRequest,#updateProfile,#mp_seller_save_button,#mp_seller_saveas_button').addClass('wk_mp_disabled');
        $.ajax({
            url: path_sellerdetails,
            cache: false,
            type: 'POST',
            async: false,
            dataType: "json",
            data: {
                ajax: true,
                action: 'validateMpSellerForm',
                formData: $("form").serialize(),
                token: $('#wk-static-token').val(),
                mp_seller_id: $('#mp_seller_id').val(),
            },
            success: function(result) {
                clearFormField();
                $('.wk_product_loader').hide();
                if (result.status == 'ko') {
                    $('#wk_mp_form_error').text(result.msg).show('slow');
                    changeTabStatus(result.tab);

                    $('html,body').animate({
                        scrollTop: $("#wk_mp_form_error").offset().top - 10
                    }, 'slow');

                    if (result.multilang == 1) {
                        $('.' + result.inputName).addClass('border_warning');
                    } else {
                        $('input[name="' + result.inputName + '"]').addClass('border_warning');
                    }
                    window.wkerror = true;
                }
            }
        });

        if (window.wkerror) {
            $('#sellerRequest,#updateProfile,#mp_seller_save_button,#mp_seller_saveas_button').removeClass('wk_mp_disabled');
            return false;
        }
        // end of ajax code
    });

    //Add product and update product form validation
    $('#wk_mp_seller_product_form,#mp_admin_save_button,#mp_admin_saveas_button').on("submit", function(e) {
        window.wkerror = false;

        getActiveTabAfterSubmitForm();

        //get all checked category value in a input hidden type name 'product_category'
        var rawCheckedID = [];
        $('#categorycontainer .jstree-clicked').each(function() {
            var rawIsChecked = $(this).parent('.jstree-node').attr('id');
            rawCheckedID.push(rawIsChecked);
        });

        $('#product_category').val(rawCheckedID.join(","));

        var checkbox_length = $('#product_category').val();
        if (checkbox_length == 0) {
            showErrorMessage(req_catg);
            return false;
        }

        // validate seller product form
        $('.wk_product_loader').show();
        $('#SubmitProduct, #StayProduct, button#mp_admin_saveas_button, button#mp_admin_save_button').addClass('wk_mp_disabled');
        $.ajax({
            url: path_addfeature,
            cache: false,
            type: 'POST',
            async: false,
            dataType: "json",
            data: {
                ajax: true,
                action: 'validateMpForm',
                formData: $("form").serialize(),
                token: $('#wk-static-token').val(),
                id_mp_product: $('#mp_product_id').val(),
            },
            success: function(result) {
                $('.wk_product_loader').hide();
                clearFormField();
                if (result.status == 'ko') {
                    $('#wk_mp_form_error').text(result.msg).show('slow');
                    changeTabStatus(result.tab);

                    $('html,body').animate({
                        scrollTop: $("#wk_mp_form_error").offset().top - 10
                    }, 'slow');

                    if (result.multilang == 1) {
                        $('.' + result.inputName).addClass('border_warning');
                    } else {
                        $('input[name="' + result.inputName + '"]').addClass('border_warning');
                    }
                    window.wkerror = true;
                    $("html, body").animate({ scrollTop: 300 }, "slow");
                }
            }
        });

        if (window.wkerror) {
            $('#SubmitProduct, #StayProduct, button#mp_admin_saveas_button, button#mp_admin_save_button').removeClass('wk_mp_disabled');
            return false;
        }

        //return false;
    });

    if ($("#updateProfile").length) {
        id_seller = $("#id_seller").val();
    }

    // payment details link
    $('#submit_payment_details').click(function() {
        var payment_mode = $('#payment_mode').val();
        if (payment_mode == "") {
            alert(req_payment_mode)
            $('#payment_mode').focus();
            return false;
        }
    });

    // code only for page where category tree is using
    if ($('#wk_mp_category_tree').length) {
        //for category tree
        $('#wk_mp_category_tree').checkboxTree({
            initializeChecked: 'expanded',
            initializeUnchecked: 'collapsed'
        });
    }

    //Only for update seller profile page
    if (typeof actionpage != 'undefined' && actionpage == 'seller') {
        $(document).on("click", ".wk_delete_img", function(e) {
            e.preventDefault();
            var img_uploaded = $(this).data("uploaded");
            if (img_uploaded) { //if image already upload for profile or shop
                if (confirm(confirm_delete_msg)) {
                    deleteSellerImages($(this));
                }
            } else {
                deleteSellerImages($(this));
            }
            return false;
        });
    }

    //When seller deactivate their shop then first confirm
    $(document).on("click", ".wk_shop_deactivate", function() {
        if (confirm(confirm_deactivate_msg)) {
            return true;
        }

        return false;
    });

    //Delete seller payment details then first confirm
    $(".delete_mp_data").on("click", function() {
        if (confirm(confirm_msg)) {
            return true;
        } else {
            return false;
        }
    });

    // check all check box when seller want to check all permission
    $(document).on('change', '#wk_select_all_checkbox', function() {
        $('input[name="seller_details_access[]"]').prop('checked', $(this).prop("checked"));
    });

    //" uncheck checkbox if any other checkbox get uncheck
    $('input[name="seller_details_access[]"]').change(function() {
        if (false == $(this).prop("checked")) {
            $("#wk_select_all_checkbox").prop('checked', false);
        }
    });

    //Product Visibility
    if ($('#available_for_order').is(':checked')) {
        $('#show_price').parent().parent().removeClass('checker');
        $('#show_price').prop('disabled', true);
        $('#show_price').prop('checked', true);
    }

    $('#available_for_order').click(function() {
        //check if checkbox is checked
        if ($('#available_for_order').is(':checked')) {
            $('#show_price').parent().parent().removeClass('checker');
            $('#show_price').prop('disabled', true);
            $('#show_price').prop('checked', true);
        } else {
            $('#show_price').removeAttr('disabled'); //disable
        }
    });
});

$(document).ready(function() {
    // get feature values when seller change the feature
    $(document).on('change', '.wk_mp_feature', function() {
        var idFeature = $(this).val();
        var dataIdFeature = $(this).attr('data-id-feature');
        if (idFeature > 0) {
            $.ajax({
                url: path_addfeature,
                cache: false,
                type: 'POST',
                data: {
                    id_mp_product: $('#mp_product_id').val(),
                    token: $('#wk-static-token').val(),
                    ajax: true,
                    idFeature: idFeature,
                    action: "getFeatureValue"
                },
                success: function(result) {
                    $("select[data-id-feature-val=" + dataIdFeature + "]").empty();
                    if (result) {
                        data = JSON.parse(result);
                        $("select[data-id-feature-val=" + dataIdFeature + "]").removeAttr('disabled');
                        $('.custom_value_' + dataIdFeature).prop('value', '');
                        $("select[data-id-feature-val=" + dataIdFeature + "]").append('<option value="0">' + choose_value + '</option>');
                        $.each(data, function(i, item) {
                            $("select[data-id-feature-val=" + dataIdFeature + "]").append('<option value="' + item.id_feature_value + '">' + item.value + '</option>');
                        });
                    } else {
                        $("select[data-id-feature-val=" + dataIdFeature + "]").append('<option value="0">' + no_value + '</option>');
                    }
                }
            });
        } else {
            $("select[data-id-feature-val=" + dataIdFeature + "]").empty();
        }
    });

    //When admin assigned product to seller
    $(document).on('click', '.wk-prod-assign', function() {
        if ($('select[name="id_product[]"] option:selected').val()) {
            if (confirm(confirm_assign_msg)) {
                $('.wk-prod-assign').addClass('wk_mp_disabled');
                return true;
            }
        } else {
            alert(choose_one);
        }

        return false;
    });

    $("#available_date").datepicker({
        dateFormat: "yy-mm-dd",
    });

    // add more feature list
    $(document).on('click', '#add_feature_button', function() {
        var fieldrow = parseInt($('#wk_feature_row').val());
        var idSeller = seller_default_lang = false;
        idSeller = $('select[name="shop_customer"] option:selected').val();
        sellerDefaultLang = $('#seller_default_lang').val();
        choosedLangId = $('#choosedLangId').val();
        $('.wk-feature-loader').css('display', 'inline-block');
        $('#add_feature_button').attr('disabled', 'disabled');

        $.ajax({
            url: path_addfeature,
            cache: false,
            type: 'POST',
            data: {
                id_mp_product: $('#mp_product_id').val(),
                token: $('#wk-static-token').val(),
                ajax: true,
                fieldrow: fieldrow,
                idSeller: idSeller,
                action: "addMoreFeature",
                sellerDefaultLang: sellerDefaultLang,
                choosedLangId: choosedLangId,
            },
            success: function(result) {
                $('.wk-feature-loader').hide();
                $('#add_feature_button').removeAttr('disabled');
                if (result) {
                    $('#features-content').last().append(result);
                    $('#wk_feature_row').val(fieldrow + 1);
                }
            }
        });
    });

    // delete feature list
    $(document).on('click', '.wkmp_feature_delete', function() {
        $(this).closest('.wk_mp_feature_delete_row').parent().fadeOut(500, function() {
            $(this).remove();
        });
    });

    //Display cms page in modal box
    $('.wk_terms_link').on('click', function() {
        var linkCmsPageContent = $(this).attr('href');
        $('#wk_terms_condtion_content').load(linkCmsPageContent, function() {
            //remove extra content
            $('#wk_terms_condtion_content section#wrapper').css({ "background-color": "#fff", "padding": "0px", "box-shadow": "0px 0px 0px #fff" });
            $('#wk_terms_condtion_content .breadcrumb').remove();
            $('#wk_terms_condtion_content header').remove();
            //display content
            $('#wk_terms_condtion_div').modal('show');
        });
        return false;
    });

    //if admin changed combination, features and shipping settings from assing product to seller page
    $(document).on('click', 'input[name="assignedValues[]"]', function() {
        if ($(this).prop('checked') == true) {
            if ($(this).val() == '1') {
                $('.wk-assign-combinations').show();
            } else if ($(this).val() == '2') {
                $('.wk-assign-features').show();
            } else if ($(this).val() == '3') {
                $('.wk-assign-shipping').show();
            }
        } else {
            if ($(this).val() == '1') {
                $('.wk-assign-combinations').hide();
            } else if ($(this).val() == '2') {
                $('.wk-assign-features').hide();
            } else if ($(this).val() == '3') {
                $('.wk-assign-shipping').hide();
            }
        }
    });

    //When click on all check checkbox when assing product to seller
    $(document).on('click', '#checkme', function() {
        if ($(this).prop('checked') == true) {
            $('.wk-assign-combinations').show();
            $('.wk-assign-features').show();
            $('.wk-assign-shipping').show();
        } else {
            $('.wk-assign-combinations').hide();
            $('.wk-assign-features').hide();
            $('.wk-assign-shipping').hide();
        }
    });
});

/*------------------------------  Mandatory Checked Functions  --------------------------*/

var i = 2;
var id_seller;
var shop_name_exist = false;
var seller_email_exist = false;

//Check Seller unique shop name validation
function onblurCheckUniqueshop() {
    var shop_name_unique = $('#shop_name_unique').val().trim();
    id_seller = $("#mp_seller_id").val();
    if (checkUniqueShopName(shop_name_unique, id_seller)) {
        $('#shop_name_unique').focus();
        return false;
    }
}

function getActiveTabAfterSubmitForm() {
    //put active tab in input hidden type
    if (typeof backend_controller !== 'undefined') { //for admin
        var active_tab_id = $('.wk-tabs-panel .nav-tabs li.active a').attr('href');
    } else {
        var active_tab_id = $('.wk-tabs-panel .nav-tabs li a.active').attr('href');
    }

    if (typeof active_tab_id !== 'undefined') {
        var active_tab_name = active_tab_id.substring(1, active_tab_id.length);
        $('#active_tab').val(active_tab_name);
    }
}

function changeTabStatus(active_tab) {
    //Remove all tabs from active (make normal)
    $('.wk-tabs-panel .tab-content .tab-pane').removeClass('active');
    $('.wk-tabs-panel .tab-content .tab-pane').removeClass('show');
    if (typeof backend_controller !== 'undefined') { //for admin
        $('.wk-tabs-panel .nav-tabs li').removeClass('active');
        $('.wk-tabs-panel .nav-tabs li').removeClass('show');
        $('[href*="#' + active_tab + '"]').parent('li').addClass('active');
        $('[href*="#' + active_tab + '"]').parent('li').addClass('show');
    } else {
        $('.wk-tabs-panel .nav-tabs li.nav-item a').removeClass('active');
        $('.wk-tabs-panel .nav-tabs li.nav-item a').removeClass('show');
        $('[href*="#' + active_tab + '"]').addClass('active');
        $('[href*="#' + active_tab + '"]').addClass('show');
    }
    //Add active class in selected tab
    $('#' + active_tab).addClass('active show in');
}

function checkUniqueShopName(shop_name_unique, id_seller) {
    if (shop_name_unique != "") {
        $('.seller-loading-img').css('display', 'inline-block');
        $('.wk-mp-block').css({'pointer-events': 'none'});
        $.ajax({
            url: path_sellerdetails,
            type: "POST",
            data: {
                ajax: true,
                action: "checkUniqueShopName",
                shop_name: shop_name_unique,
                token: $('#wk-static-token').val(),
                id_seller: id_seller !== 'undefined' ? id_seller : false,
            },
            success: function(result) {
                $('.wk-mp-block').css({'pointer-events': 'inherit'});
                $('.seller-loading-img').css('display', 'none');
                if (result == 1) {
                    $(".wk-msg-shopnameunique").html(shop_name_exist_msg);
                    shop_name_exist = true;
                } else if (result == 2) {
                    $(".wk-msg-shopnameunique").html(shop_name_error_msg);
                    $(".seller_shop_name_uniq").addClass('form-error').removeClass('form-ok');
                    shop_name_exist = true;
                } else {
                    $(".wk-msg-shopnameunique").empty();
                    shop_name_exist = false;
                }
            }
        });
    } else {
        $(".wk-msg-shopnameunique").empty();
        shop_name_exist = false;
    }

    return shop_name_exist;
}

//Check Seller registration unique email validation
function onblurCheckUniqueSellerEmail() {
    var business_email = $('#business_email').val().trim();
    id_seller = $("#mp_seller_id").val();
    if (checkUniqueSellerEmail(business_email, id_seller)) {
        $('#business_email').focus();
        return false;
    }
}

function checkUniqueSellerEmail(business_email, id_seller) {
    if (business_email != "") {
        $.ajax({
            url: path_sellerdetails,
            type: "POST",
            data: {
                ajax: true,
                action: "checkUniqueSellerEmail",
                token: $('#wk-static-token').val(),
                seller_email: business_email,
                id_seller: id_seller !== 'undefined' ? id_seller : false
            },
            async: false,
            success: function(result) {
                if (result == 1) {
                    $(".wk-msg-selleremail").html(seller_email_exist_msg);
                    seller_email_exist = true;
                } else {
                    $(".wk-msg-selleremail").empty();
                    seller_email_exist = false;
                }
            }
        });
    } else {
        $(".wk-msg-selleremail").empty();
        seller_email_exist = false;
    }

    return seller_email_exist;
}

//On delete seller profile image and shop image by seller or admin
function deleteSellerImages(t) {
    var id_seller = t.data("id_seller");
    var target = t.data("imgtype");

    if (id_seller != '' && target != '') {
        $.ajax({
            url: path_sellerdetails,
            type: 'POST',
            dataType: 'json',
            async: false,
            data: {
                id_seller: id_seller,
                token: $('#wk-static-token').val(),
                delete_img: target,
                ajax: true,
                action: "deleteSellerImage"
            },
            success: function(result) {

                if (result.status == 'ok') {
                    $('.jFiler-item-others').remove();
                    var target_container = $('.jFiler-items-' + target);
                    target_container.show();

                    if (target == 'seller_img') {
                        target_container.find('.jFiler-item-inner img').attr("src", seller_default_img_path);
                    } else if (target == 'shop_img') {
                        target_container.find('.jFiler-item-inner img').attr("src", shop_default_img_path);
                    } else {
                        target_container.find('.jFiler-item-inner img').attr("src", no_image_path);
                    }

                    target_container.find('.wk_delete_img').remove();
                    t.parent().removeClass('wk_hover_img');
                    t.remove();
                }
            }
        });
    }
}

function clearFormField() {
    $(":input").removeClass('border_warning');
}
function showErrorMessage(msg) {
    $.growl.error({ title: "", message: msg });
}