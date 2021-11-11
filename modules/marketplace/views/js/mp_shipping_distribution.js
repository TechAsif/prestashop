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
    $(".distribute_type").on("change", function() {
        var shipping_distribute_type = $(this).val();
        var id_ps_reference = $(this).data('id-ps-reference');
        if (id_ps_reference) {
            $.ajax({
                url: path_admin_mp_shipping,
                data: {
                    ajax: true,
                    action: "changeShippingDistributionType",
                    id_ps_reference: id_ps_reference,
                    shipping_distribute_type: shipping_distribute_type
                },
                dataType: 'json',
                success: function(result) {
                    if (result == '1') {
                        showSuccessMessage(success_msg);
                    } else if (result == '0') {
                        showErrorMessage(error_msg);
                    }
                },
                error: function(xhr, status, error) {
                    return 0;
                }
            });
        }
    });
});

function showSuccessMessage(msg) {
    $.growl.notice({ title: "", message: msg });
}

function showErrorMessage(msg) {
    $.growl.error({ title: "", message: msg });
}