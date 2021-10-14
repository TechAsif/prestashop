/**
 * DHL Deutschepost
 *
 * @author    silbersaiten <info@silbersaiten.de>
 * @copyright 2021 silbersaiten
 * @license   See joined file licence.txt
 * @category  Module
 * @support   silbersaiten <support@silbersaiten.de>
 * @version   1.0.10
 * @link      http://www.silbersaiten.de
 */
$(function(){
    var dhldp_admin_orders = {
        cur_dhl_product_params: {},

        init_products: function () {
            var  self = this;
            if (typeof dhl_products_params != 'undefined') {
                $.each(dhl_products_params, function(k, v) {
                    if ($('#ordercarrier-' + k + ' #dhl_product_code').length) {
                        self.updateProduct(k);
                        //on change
                        $(document).on('change', '#ordercarrier-' + k + ' #dhl_product_code', function () {
                            self.updateProduct(k);
                        });
                    }
                });
            }
        },
        init: function () {
            var self = this;

            self.init_products();


            $(document).on('click', '#submitDhlUpdateAddress', function(e) {
                block = $(this).closest('table').find('#dhldp_dhl_update_address');
                if(!$(block).is(':visible')) {
                    $(block).find('input#show_update_address').val(1);
                    $(block).show();
                    offset = $('.page-head').length?$('.page-head').height():0;
                    offset += $('.navbar-header').length?$('.navbar-header').height():0;
                    $('html, body').animate({
                        scrollTop: $(block).offset().top - offset
                    }, 500);
                } else {
                    $(block).find('input#show_update_address').val('');
                    $(block).hide();
                }
            });

            $(document).on('click', '#submitDhlAdditServices', function(e) {
                block = $(this).closest('table').find('#dhldp_dhl_addit_services');
                if(!$(block).is(':visible')) {
                    $(block).find('input#show_dhl_additional_services').val(1);
                    $(block).show();
                    offset = $('.page-head').length?$('.page-head').height():0;
                    offset += $('.navbar-header').length?$('.navbar-header').height():0;
                    $('html, body').animate({
                        scrollTop: $(block).offset().top - offset
                    }, 500);
                } else {
                    $(block).find('input#show_dhl_additional_services').val('');
                    $(block).hide();
                }
            });

            $(document).on('click', '#submitDhlExportDocuments', function(e) {
                block = $(this).closest('table').find('#dhldp_dhl_export_documents');
                if(!$(block).is(':visible')) {
                    $(block).find('input#show_dhl_export_documents').val(1);
                    $(block).show();
                    offset = $('.page-head').length?$('.page-head').height():0;
                    offset += $('.navbar-header').length?$('.navbar-header').height():0;
                    $('html, body').animate({
                        scrollTop: $(block).offset().top - offset
                    }, 500);
                } else {
                    $(block).find('input#show_dhl_export_documents').val('');
                    $(block).hide();
                }
            });

            $(document).on('click', '#toggleAllDHLLabelsForOrder', function(e){
                e.preventDefault();
                $(this).parent().parent().parent().find('.hiddenLabel').toggle();
            });

            $("#dhl_update_address input[type=radio][name^=address][name*=address_type]:checked").each(function(index) {
                self.showAddressTypeInputs(this)
            });

            $(document).on('click', "#dhl_update_address input[type=radio][name^=address][name*=address_type]", function(e) {
                if ($(this).is(':checked')) {
                    self.showAddressTypeInputs(this)
                }
            });

            $(document).on('click', "#dhl_update_address input[type=radio][name^=address][name*=receiver_type]", function(e) {
                if ($(this).is(':checked')) {
                    self.showReceiverTypeInputs(this)
                }
            });
        },
        showAddressTypeInputs: function(el) {
            checked_val = $(el).val();
            $(el).closest('#dhl_update_address').find('input[type=radio][name^=address][name*=address_type]').each(function(index) {
                if ($(this).val() != checked_val) {
                    $(this).closest('#dhl_update_address').find('.address_type_'+$(this).val()).hide();
                } else {
                    $(this).closest('#dhl_update_address').find('.address_type_'+$(this).val()).show();
                }
            });
        },
        updateProduct: function(id_order_carrier) {
            var self = this;
            $.each(dhl_products_params[id_order_carrier], function(index, value ) {
                if ($('#ordercarrier-' + id_order_carrier +' #dhl_product_code').val() == value.fullcode) {
                    self.cur_dhl_product_params[id_order_carrier] = value;
                    self.updateExportDocumentsButton(id_order_carrier);
                    self.updateAdditionalServicesInputs(id_order_carrier);
                }
            })
        },
        updateAdditionalServicesInputs: function(id_order_carrier) {
            var self = this;
            if (typeof self.cur_dhl_product_params[id_order_carrier].definition.services != 'undefined') {
                $('#ordercarrier-' + id_order_carrier +' #dhldp_dhl_addit_services input[type=checkbox], #dhldp_dhl_addit_services input[type=text], #dhldp_dhl_addit_services textarea, #dhldp_dhl_addit_services select').each(function(index) {

                    var name = $(this).attr('name').split('][')[1].replace(']', '')
                    if ($.inArray(name, self.cur_dhl_product_params[id_order_carrier].definition.services) != -1) {
                        $(this).closest('.form-group').show();
                    } else {
                        $(this).closest('.form-group').hide();
                    }
                });
            }
        },
        updateExportDocumentsButton: function(id_order_carrier) {
            if (typeof this.cur_dhl_product_params[id_order_carrier].definition.export_documents != 'undefined' &&
                this.cur_dhl_product_params[id_order_carrier].definition.export_documents == 1) {
                $('#ordercarrier-' + id_order_carrier +' #submitDhlExportDocuments').show();
            } else {
                $('#ordercarrier-' + id_order_carrier +' #submitDhlExportDocuments').hide();
            }
        },
    };

    dhldp_admin_orders.init();
});