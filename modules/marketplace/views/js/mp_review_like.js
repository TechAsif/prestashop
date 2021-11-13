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
	$('.wk_like_action').on('click', function() {
        var id_review = $(this).data('id-review');
        callReviewHelpfulAction(id_review, 1, $(this)); //1 for helpful review
    });

    $('.wk_dislike_action').on('click', function() {
        var id_review = $(this).data('id-review');
        callReviewHelpfulAction(id_review, 2, $(this)); //2 for not helpful review
    });
});

function callReviewHelpfulAction(id_review, btn_action, current_obj)
{
    if (logged != 'undefined' && logged == true) {
        $.ajax({
            url: contact_seller_ajax_link,
            method: 'POST',
            dataType: 'json',
            data: {
                id_review: id_review,
                btn_action: btn_action, // Means review is helpful
                action: "reviewHelpful",
                ajax: "1"
            },
            success: function(result) {
                if (result.status == 'ok') {
                    $('.wk_like_number_'+id_review).html(result.data.total_likes);
                    $('.wk_dislike_number_'+id_review).html(result.data.total_dislikes);
                    $('.wk_icon_'+id_review).css('background-color', '#4A4A4A');
                    if (result.like == '1' || result.like == '0') {
                        //If review select as helpful or not helpful
                        if (btn_action == '1') {
                            //helpful
                            current_obj.css('background-color', '#30A728');
                        } else {
                            //Not helpful
                            current_obj.css('background-color', '#E23939');
                        }
                    }
                } else {
                    alert(some_error);
                    return false;
                }
            }
        });
    }
}