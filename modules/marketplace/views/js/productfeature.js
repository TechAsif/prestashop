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
    $(document).on('click', '.wk-mp-data-list', function() {
        var url = $(this).data('value-url');
        window.location.href = url;
    });

    $(document).on('click', '.edit_button', function() {
        var id = $(this).attr('edit');
        if (id == 0) {
            alert(error_msg1);
            return false;
        }
    });

    $(document).on('click', '.delete_button', function() {
        var id = $(this).attr('edit');
        if (id == 0) {
            alert(error_msg1);
            return false;
        } else {
            return confirm(sure_msg);
        }
    });

    $(document).on('click', '.edit_button_v', function() {
        var id = $(this).attr('edit');
        if (id == 0) {
            alert(error_msg_v);
            return false;
        }
    });

    $(document).on('click', '.delete_button_v', function() {
        var id = $(this).attr('edit');
        if (id == 0) {
            alert(error_msg_v);
            return false;
        } else {
            return confirm(sure_msg_v);
        }
    });
});