/**
 * DHL Deutschepost
 *
 * @author    silbersaiten <info@silbersaiten.de>
 * @copyright 2020 silbersaiten
 * @license   See joined file licence.txt
 * @category  Module
 * @support   silbersaiten <support@silbersaiten.de>
 * @version   1.0.0
 * @link      http://www.silbersaiten.de
 */

var dhldp_admin_configure = {
    init: function() {
        var self = this;
        if( typeof($.fn.select2) == "function" )
            $('select.select2').select2({});
        self.validateForm();
    },
    validateForm: function() {
        if( typeof($.fn.validate) == "function" )
        $("#dhl_global_settings").validate({
            rules: {
                /*
                "DHL_SBX_CIGUSER":{
                    "required": {
                        depends: function(element) {
                            return $("#dhl_mode_sbx").is(":checked");
                        }
                    }
                },
                "DHL_SBX_CIGPASS": {
                    "required": {
                        depends: function(element) {
                            return $("#dhl_mode_sbx").is(":checked");
                        }
                    }
                }
                */
            },
            submitHandler: function(form) {
                //doAjaxLogin($('#redirect').val());
                form.submit();
            },
            // override jquery validate plugin defaults for bootstrap 3
            highlight: function(element) {
                $(element).closest('.form-group').addClass('has-error');
            },
            unhighlight: function(element) {
                $(element).closest('.form-group').removeClass('has-error');
            },
            errorElement: 'span',
            errorClass: 'help-block',
            errorPlacement: function(error, element) {
                if(element.parent('.input-group').length) {
                    error.insertAfter(element.parent());
                } else {
                    error.insertAfter(element);
                }
            }
        });
    }
}

$(function(){
    dhldp_admin_configure.init();
})