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
    $('#wk_contact_seller').on("click", function(e) {
        e.preventDefault();
        var email = $("#customer_email").val().trim();
        var querySubject = $("#query_subject").val().trim();
        var queryDescription = $("#query_description").val().trim();
        var reg = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        var query_error = false;

        $(".contact_seller_message").html('');
        if (email == '') {
            $(".contact_seller_message").append("<br>");
            $(".contact_seller_message").append(email_req).css("color", "#971414");
            query_error = true;
        } else if (!reg.test(email)) {
            $(".contact_seller_message").append("<br>");
            $(".contact_seller_message").append(invalid_email).css("color", "#971414");
            query_error = true;
        }
        if (querySubject == '') {
            $(".contact_seller_message").append("<br>");
            $(".contact_seller_message").append(subject_req).css("color", "#971414");
            query_error = true;
        }
        if (queryDescription == '') {
            $(".contact_seller_message").append("<br>");
            $(".contact_seller_message").append(description_req).css("color", "#971414");
            query_error = true;
        }

        if (!query_error) {
            $(".contact_seller_message").html("<img width='15' src='"+mp_image_dir+"loading-small.gif'>");
            // We are using seperated controller for contact seller because we can't use seller profile url as ajax url
            // Because seller profile and shop store controller with friendly URL
            $.ajax({
                url: contact_seller_ajax_link,
                type: 'POST',
                dataType: 'json',
                async: false,
                token : $('#wk-static-token').val(),
                data: $('#wk_contact_seller_form').serialize() + "&action=contactSeller&ajax=1",
                success: function(result) {
                    $(".contact_seller_message").html(result.msg);
                    if (result.status == 'ok') {
                        $(".contact_seller_message").css("color", "green");
                    } else {
                        $(".contact_seller_message").css("color", "red");
                    }
                }
            });
        } else {
            return false;
        }
    });

    //Display seller rating on seller product page
    if (typeof sellerRating !== 'undefined') {
        $('#seller_rating').raty({
            path: rating_start_path,
            score: sellerRating,
            readOnly: true,
        });
    }
});