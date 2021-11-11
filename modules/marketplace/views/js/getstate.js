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
    //get state by country
    if (typeof id_country != 'undefined') {
        checkZipCode(id_country);
        getState(id_country, id_state);
        $("#id_country").change(function() {
            var id_country = $("#id_country").val();
            $('#id_country').siblings('label').append("<span id='state_load'><img width='15' src='" + mp_image_dir + "loading-small.gif'></span>");
            checkZipCode(id_country);
            getState(id_country, id_state);
        });
    }
});

function checkZipCode(id_country) {
    $.ajax({
        method: "POST",
        url: path_sellerdetails,
        data: {
            id_country: id_country,
            ajax: true,
            token: $('#wk-static-token').val(),
            action: "checkZipCodeByCountry"
        },
        success: function(result) {
            if (result == '1') {
                $('#seller_zipcode').show();
            } else {
                $('#seller_zipcode').hide();
            }
        }
    });
}

function getState(id_country, id_state) {
    $.ajax({
        method: "POST",
        url: path_sellerdetails,
        data: {
            id_country: id_country,
            id_state: id_state,
            ajax: true,
            dataType: 'json',
            token: $('#wk-static-token').val(),
            action: "getSellerState"
        },
        success: function(result) {
            $('#state_load').remove();
            if (result) {
                $("#wk_seller_state_div").show();
                $("#id_state").empty();
                $("#id_state").attr('required', true);
                var selectstatename = '';
                $.each(jQuery.parseJSON(result), function(index, state) {
                    var stateHTML = '';
                    stateHTML = '<option value="' + state.id_state + '"';
                    if (id_state && id_state == state.id_state) {
                        selectstatename = state.name;
                        stateHTML += ' Selected="Selected"';

                    } else if (index == 0) {
                        selectstatename = state.name;
                        stateHTML += ' Selected="Selected"';
                    }
                    stateHTML += '>' + state.name + '</option>';
                    $("#id_state").append(stateHTML);
                });

                $("#id_state span").html(selectstatename);
                $("#id_state").css('width', '100%');
                $("#id_state span").css('width', '100%');
                $("#state_available").val(1);
            } else {
                $("#wk_seller_state_div").hide();
                $("#id_state").empty();
                $("#id_state").attr('required', false);
                $("#state_available").val(0);
            }
        }
    });
}