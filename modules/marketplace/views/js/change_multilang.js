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
    $('#default_lang').on("change", function(e) {
        e.preventDefault();
        if (typeof multi_lang !== 'undefined' && multi_lang == '1') {
            var select_lang_name = $(this).find("option:selected").data('lang-name');
            var select_lang_id = $(this).val();

            showSellerLangField(select_lang_name, select_lang_id);

            $('.shop_name_all').removeClass('seller_default_shop');
            $('#shop_name_' + select_lang_id).addClass('seller_default_shop');

            //Changes in HTML5 required attribute
            $('.shop_name_all').attr('required', false);
            $("#shop_name_"+select_lang_id).attr('required', true);

            //shop name in default lang is mandatory when change default language
            if ($("#shop_name_"+select_lang_id).val() == '') {
                $("#shop_name_"+select_lang_id).focus();
                $(".wk-msg-shopname").html(req_shop_name_lang + ' ' + select_lang_name);
            } else {
                $(".wk-msg-shopname").html('');
            }
        }
    });



    $('select[name=seller_lang_btn]').on('change', function(){
        var select_lang  = $('option:selected', this).attr('data-langname');
        var select_langid = $(this).val();

        showSellerLangField(select_lang, select_langid);

    });

    $('.shop_name_all').on("blur", function() {
        if ($(this).val() != '') {
            $(".wk-msg-shopname").html('');
        }
    });

    //Change shop customer from backend Add product
    $(document).on('change', "#wk_shop_customer", function() {
        var seller_customer_id = $("#wk_shop_customer option:selected").val();
        if (typeof multi_lang !== 'undefined' && multi_lang == '1') {
            getSellerDefaultLangId(seller_customer_id);
        } else if ({$multi_def_lang_off} == '2') { //seller default lang
            getSellerDefaultLangId(seller_customer_id);
        }
    });
});

//Find seller default lang on add product page according to seller choose
function getSellerDefaultLangId(customer_id)
{
    if (customer_id != '') {
        $.ajax({
            url: path_sellerproduct,
            method: 'POST',
            dataType: 'json',
            data: {
                customer_id: customer_id,
                token : $('#wk-static-token').val(),
                action: "findSellerDefaultLang",
                ajax: "1"
            },
            success: function(data) {
                $('#seller_default_lang').val(data.id_lang);
                $('#seller_default_lang_div').html(data.name);
                showProdLangField(data.name, data.id_lang);
            }
        });
    }
}

function showProdLangField(select_lang_name, id_lang)
{
    //$('.product_name_all').removeAttr('required');
    //$('#product_name_'+id_lang).prop('required', 'required');

    //For all fields except features
    $('.wk_text_field_all').hide();
    $('.wk_text_field_' + id_lang).show();

    $('.wkmp_feature_custom').hide();
    $('.wk_mp_feature_custom_'+id_lang).show();

    $('.all_lang_icon').attr('src', img_dir_l+id_lang+'.jpg');
    $('#choosedLangId').val(id_lang);
    $('#seller_lang_btn').val(id_lang);
}

function showSellerLangField(select_lang_name, id_lang)
{
    var defaultLang = $('select#default_lang option:selected').val();
    var current_select_lang_name = $('select#default_lang option:selected').data('lang-name');
    var current_select_lang_id = $('select#default_lang option:selected').val();
    if (($('#shop_name_'+current_select_lang_id).val() == '') && (defaultLang != id_lang)) {
        $(".wk-msg-shopname").html(req_shop_name_lang + ' ' + current_select_lang_name);
    }
    $('#seller_lang_btn').val(id_lang);

    $('.wk_text_field_all').hide();
    $('.wk_text_field_' + id_lang).show();

    $('.all_lang_icon').attr('src', img_dir_l+id_lang+'.jpg');
}

