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
    //Call on page load
    hideAndShowSellerDetails();
    hideAndShowTermsCondition();
    hideAndShowMultiLangAdminApprove();
    hideAndShowLinkRewriteURL();
    hideAndShowMpCombinationActivateDeactivate();
    hideAndShowMpSocialTab();
    hideSellerOrderStatus();
    hideCustomerReviewSettings();
    hideShippingDistributionSettings();
    hidePsTrackingNumberSettings();

    //hide and show seller details tab according to switch
    $('input[name="WK_MP_SHOW_SELLER_DETAILS"]').on("click", function() {
        hideAndShowSellerDetails();
    });

    //hide and show terms and condition text box according to switch
    $('input[name="WK_MP_TERMS_AND_CONDITIONS_STATUS"]').on("click", function() {
        hideAndShowTermsCondition();
    });

    //hide and show multilang options text box according to switch
    $('input[name="WK_MP_MULTILANG_ADMIN_APPROVE"]').on("click", function() {
        hideAndShowMultiLangAdminApprove();
    });

    //hide and show link rewrite text box according to switch
    $('input[name="WK_MP_URL_REWRITE_ADMIN_APPROVE"]').on("click", function() {
        hideAndShowLinkRewriteURL();
    });

    //hide and show combination activate/deactive options for seller
    $('input[name="WK_MP_SELLER_PRODUCT_COMBINATION"]').on("click", function() {
        hideAndShowMpCombinationActivateDeactivate();
    });

    //hide and show social tabs
    $('input[name="WK_MP_SOCIAL_TABS"]').on("click", function() {
        hideAndShowMpSocialTab();
    });

    //hide and show order status
    $('input[name="WK_MP_SELLER_ORDER_STATUS_CHANGE"]').on("click", function() {
        hideSellerOrderStatus();
    });

    //hide and show customer review settings
    $('input[name="WK_MP_REVIEW_SETTINGS"]').on("click", function() {
        hideCustomerReviewSettings();
    });

    //hide and show shipping distribution settings
    $('input[name="WK_MP_SHIPPING_DISTRIBUTION_ALLOW"]').on("click", function() {
        hideShippingDistributionSettings();
    });

    //hide and show ps tracking number update settings
    $('input[name="WK_MP_SELLER_ORDER_TRACKING_ALLOW"]').on("click", function() {
        hidePsTrackingNumberSettings();
    });

    if (typeof wk_commission_controller !== 'undefined') {
        //hide and show mp commission settings
        hideMpCommissionSettings(); //on page load

        $('#WK_MP_GLOBAL_COMMISSION_TYPE, #commision_type').on("change", function() {
            hideMpCommissionSettings();
        });

        $('#WK_MP_PRODUCT_TAX_DISTRIBUTION').on("change", function() {
            hideMpTaxCommissionSettings();
        });
    }

    // If color picker is not working  background image for color then we have to change the path.
    if (typeof color_picker_custom != 'undefined') {
        $.fn.mColorPicker.defaults.imageFolder = '../img/admin/';
    }
});

function hideAndShowSellerDetails() {
    if ($('input[name="WK_MP_SHOW_SELLER_DETAILS"]:checked').val() == 1) {
        $(".wk_mp_seller_details").show();
    } else {
        $(".wk_mp_seller_details").hide();
    }
}

function hideAndShowTermsCondition() {
    if ($('input[name="WK_MP_TERMS_AND_CONDITIONS_STATUS"]:checked').val() == 1) {
        $(".wk_mp_termsncond").show();
    } else {
        $(".wk_mp_termsncond").hide();
    }
}

function hideAndShowMultiLangAdminApprove() {
    if ($('input[name="WK_MP_MULTILANG_ADMIN_APPROVE"]:checked').val() == 1) {
        $('.multilang_def_lang').hide();
    } else {
        $('.multilang_def_lang').show();
    }
}

function hideAndShowLinkRewriteURL() {
    if ($('input[name="WK_MP_URL_REWRITE_ADMIN_APPROVE"]:checked').val() == 1) {
        $('.mp_url_rewrite').show();
    } else {
        $('.mp_url_rewrite').hide();
    }
}

function hideAndShowMpCombinationActivateDeactivate() {
    if ($('input[name="WK_MP_SELLER_PRODUCT_COMBINATION"]:checked').val() == 1) {
        $('.wk_mp_combination_customize').show();
    } else {
        $('.wk_mp_combination_customize').hide();
    }
}

function hideAndShowMpSocialTab() {
    if ($('input[name="WK_MP_SOCIAL_TABS"]:checked').val() == 1) {
        $('.wk_mp_social_tab').show();
    } else {
        $('.wk_mp_social_tab').hide();
    }
}

function hideSellerOrderStatus() {
    if ($('input[name="WK_MP_SELLER_ORDER_STATUS_CHANGE"]:checked').val() == 1) {
        $('.wk_mp_seller_order_status').show('slow');
    } else {
        $('.wk_mp_seller_order_status').hide('slow');
    }
}

function hideCustomerReviewSettings() {
    if ($('input[name="WK_MP_REVIEW_SETTINGS"]:checked').val() == 1) {
        $('.mp_review_settings').show('slow');
    } else {
        $('.mp_review_settings').hide('slow');
    }
}

function hideShippingDistributionSettings() {
    if ($('input[name="WK_MP_SHIPPING_DISTRIBUTION_ALLOW"]:checked').val() == 1) {
        $('.mp_shipping_distribution').show('slow');
    } else {
        $('.mp_shipping_distribution').hide('slow');
    }
}

function hidePsTrackingNumberSettings() {
    if ($('input[name="WK_MP_SELLER_ORDER_TRACKING_ALLOW"]:checked').val() == 1) {
        $('.wk_mp_tracking_ps_update').show('slow');
    } else {
        $('.wk_mp_tracking_ps_update').hide('slow');
    }
}

if (typeof wk_commission_controller !== 'undefined') {
    function hideMpCommissionSettings() {
        if ($('#WK_MP_GLOBAL_COMMISSION_TYPE, #commision_type').val() == wk_percentage) {
            $('.wk_mp_commission_rate').show('slow');
            $('.wk_mp_commission_amt').hide();
        } else if ($('#WK_MP_GLOBAL_COMMISSION_TYPE, #commision_type').val() == wk_fixed) {
            $('.wk_mp_commission_amt').show('slow');
            $('.wk_mp_commission_rate').hide();
        } else if ($('#WK_MP_GLOBAL_COMMISSION_TYPE, #commision_type').val() == wk_both_type) {
            $('.wk_mp_commission_rate').show('slow');
            $('.wk_mp_commission_amt').show('slow');
        }

        //Manage tax fixed amount
        hideMpTaxCommissionSettings();
    }

    function hideMpTaxCommissionSettings() {
        if (typeof $('#WK_MP_PRODUCT_TAX_DISTRIBUTION').val() !== 'undefined') {
            var tax_distribution_type = $('#WK_MP_PRODUCT_TAX_DISTRIBUTION').val();
        } else {
            var tax_distribution_type = product_tax_distribution;
        }

        if ((tax_distribution_type == 'distribute_both')
        && ($('#WK_MP_GLOBAL_COMMISSION_TYPE, #commision_type').val() != wk_percentage)) {
            $('.wk_mp_commission_amt_on_tax').show('slow');
        } else {
            $('.wk_mp_commission_amt_on_tax').hide();
        }
    }
}