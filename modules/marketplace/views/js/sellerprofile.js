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
    if (typeof back_end !== 'undefined') { //For Back End
        //Need reason while deactivating seller or seller product by Admin
        $('.list-action-enable').on('click',function(e){
            if ($(this).hasClass('action-enabled')) {
                if (typeof is_need_reason !== 'undefined' && is_need_reason == '1') {
                    if ($(".row-selector").length) //there was no way to get id of the row, so used this static method :(
                    {
                        var actionId = $(this).closest('tr').children('td:nth-child(2)').text();
                        if (typeof seller_product_page != 'undefined') {
                            //For manage seller product page
                            var actionName = $(this).closest('tr').children('td:nth-child(5)').text();
                        } else {
                            //For manage seller profile page
                            var actionName = $(this).closest('tr').children('td:nth-child(6)').text();
                        }
                    }
                    else
                    {
                        var actionId = $(this).closest('tr').children('td:nth-child(1)').text();
                        if (typeof seller_product_page != 'undefined') {
                            //For manage seller product page
                            var actionName = $(this).closest('tr').children('td:nth-child(4)').text();
                        } else {
                            //For manage seller profile page
                            var actionName = $(this).closest('tr').children('td:nth-child(5)').text();
                        }
                    }

                    //actionId will be seller id or product id depending on dectivation seller or product
                    //actionName will be shop name or product name depending on dectivation seller or product
                    $('#actionId_for_reason').val(parseInt(actionId));
                    $(".wk_action_name").text(actionName);
                    $("#reason").modal('show');
                    return false;
                }
            }
        });

        $('#reason-ok').on("click",function(e){
            var reason_text = $('#reason_text').val();
            reason_text = $.trim(reason_text);
            if (reason_text == '')
            {
                $(".reason_error").show();
                $(".char_error").hide();
                return false;
            }
            else if (reason_text.length < 10)
            {
                $(".reason_error").hide();
                $(".char_error").show();
                return false;
            }
        });

        $("#reason-anyway").on("click", function(){
            $("#reason-form").submit();
        });
    } else { //For Front end
        //Manage profile and shop banner in width:height 4:1 according to screen size change
        const bannerImgHeight = parseInt($('.wk_banner_image').width()) / 4;
        $(".wk_banner_image").css("height", bannerImgHeight);

        $(window).on('resize', function() { //While resize screen
            const bannerImgHeight = parseInt($('.wk_banner_image').width()) / 4;
            $(".wk_banner_image").css("height", bannerImgHeight);
        });

        //Review form submit
        $('#review_submit').submit(function() {
            var rating_image = $("input[name='rating_image']").val();
            if (rating_image == '' || rating_image == ' ') {
                $(".rating_error").text(rate_req).css("color", "red");
                return false;
            }
        });

        var id = 'rating_image';
        var imgpath = moduledir + 'marketplace/views/img';
        if (typeof currenct_cust_review !== 'undefined') {
            $('#' + id).raty({
                path: imgpath,
                scoreName: id,
                score: currenct_cust_review.rating,
            });
        } else {
            $('#' + id).raty({
                path: imgpath,
                scoreName: id
            });
        }

        if (typeof avg_rating !== 'undefined') {
            $('.avg_rating').raty({
                path: imgpath,
                score: avg_rating,
                readOnly: true,
            });
        } else {
            $('.wk_seller_rating').hide();
        }

        $(".mp-delete-review").on("click", function() {
            if (confirm(confirm_msg)) {
                return true;
            } else {
                return false;
            }
        });
    }
});