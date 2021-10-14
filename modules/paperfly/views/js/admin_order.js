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

$(function(){
    $('a.requestDHLDPDhlLabelData').click(function(evt){
        evt.preventDefault();

        var link = $(this);

        $.fancybox.open({
            href: link.attr('href'),
            type: 'iframe'
        });

        return false;
    });

    $(document).on('click', '#showAllDHLDPDhlLabels', function(e){
		$('#sectionAllDHLDPDhlLabels').toggle();
    });

    var dhldp_admin_order = {
        cur_dhl_product_params: null,
        init: function() {
            var self = this;

            //init
            if (typeof dhldp_dhl_products_params != 'undefined') {
                self.updateProduct();
            }

            //on change
            $(document).on('change', '#dhldp_dhl_product_code', function() {
                self.updateProduct();
            });

            $(document).on('click', '#submitDHLDPDhlUpdateAddress', function(e) {
                if(!$('#dhldp_dhl_update_address').is(':visible')) {
                    $('#dhldp_dhl_update_address').find('input#show_update_address').val(1);
                    $('#dhldp_dhl_update_address').slideDown(500);
                    offset = $('.page-head').length?$('.page-head').outerHeight():0;
                    offset += $('.navbar-header').length?$('.navbar-header').outerHeight():0;
                    offset += $('div.toolbarBox').length?$('div.toolbarBox').outerHeight():0;
                    $('html, body').animate({
                        scrollTop: $("#dhldp_dhl_update_address").offset().top - offset
                    }, 500);
                } else {
                    $('#dhldp_dhl_update_address').find('input#show_update_address').val('');
                    $('#dhldp_dhl_update_address').slideUp(500);
                }
            });

            $(document).on('click', '#submitDHLDPDhlAdditServices', function(e) {
                if(!$('#dhldp_dhl_addit_services').is(':visible')) {
                    $('#dhldp_dhl_addit_services').find('input#show_dhl_additional_services').val(1);
                    $('#dhldp_dhl_addit_services').slideDown(500);
                    offset = $('.page-head').length?$('.page-head').outerHeight():0;
                    offset += $('.navbar-header').length?$('.navbar-header').outerHeight():0;
                    offset += $('div.toolbarBox').length?$('div.toolbarBox').outerHeight():0;
                    $('html, body').animate({
                        scrollTop: $("#dhldp_dhl_addit_services").offset().top - offset
                    }, 500);
                } else {
                    $('#dhldp_dhl_addit_services').find('input#show_dhl_additional_services').val('');
                    $('#dhldp_dhl_addit_services').slideUp(500);
                }
            });

            $(document).on('click', '#submitDHLDPDhlExportDocuments', function(e) {
                if(!$('#dhldp_dhl_export_documents').is(':visible')) {
                    $('#dhldp_dhl_export_documents').find('input#show_dhl_export_documents').val(1);
                    $('#dhldp_dhl_export_documents').slideDown(500);
                    offset = $('.page-head').length?$('.page-head').outerHeight():0;
                    offset += $('.navbar-header').length?$('.navbar-header').outerHeight():0;
                    offset += $('div.toolbarBox').length?$('div.toolbarBox').outerHeight():0;
                    $('html, body').animate({
                        scrollTop: $("#dhl_export_documents").offset().top - offset
                    }, 500);
                } else {
                    $('#dhldp_dhl_export_documents').find('input#show_dhl_export_documents').val('');
                    $('#dhldp_dhl_export_documents').slideUp(500);
                }
            });

            $("#dhldp_dhl_update_address input[type=radio][name^=address][name*=address_type]:checked").each(function(index) {
                self.showAddressTypeInputs(this)
            });

            $(document).on('click', "#dhldp_dhl_update_address input[type=radio][name^=address][name*=address_type]", function(e) {
                if ($(this).is(':checked')) {
                    self.showAddressTypeInputs(this)
                }
            });

            $(document).on('click', "#dhldp_dhl_update_address input[type=radio][name^=address][name*=receiver_type]", function(e) {
                if ($(this).is(':checked')) {
                    self.showAddressTypeInputs(this)
                }
            });
        },
        updateProduct: function() {
            var self = this;
            $.each(dhldp_dhl_products_params, function(index, value ) {
                if ($('#dhldp_dhl_product_code').val() == value.fullcode) {
                    self.cur_dhl_product_params = value;
                    self.showParamsHelpBlocks();
                    self.updateExportDocumentsButton();
                    self.updateAdditionalServicesInputs();
                }
            })
        },
        updateAdditionalServicesInputs: function() {
            var self = this;
            if (typeof self.cur_dhl_product_params.definition.services != 'undefined') {
                $('#dhldp_dhl_addit_services input[type=checkbox], #dhldp_dhl_addit_services input[type=text], #dhldp_dhl_addit_services textarea, #dhldp_dhl_addit_services select').each(function(index) {

                   var name = $(this).attr('name').split('][')[1].replace(']', '')
                    if ($.inArray(name, self.cur_dhl_product_params.definition.services) != -1) {
                        $(this).closest('.form-group').show();
                    } else {
                        $(this).closest('.form-group').hide();
                    }
                });
            }
        },
        updateExportDocumentsButton: function() {
            if (typeof this.cur_dhl_product_params.definition.export_documents != 'undefined' && this.cur_dhl_product_params.definition.export_documents == 1) {
                $('#submitDHLDPDhlExportDocuments').show();
            } else {
                $('#submitDHLDPDhlExportDocuments').hide();
            }
        },
        showParamsHelpBlocks: function() {
            var params = this.cur_dhl_product_params.definition.params;
            $('#dhldp_dhl_params #dhl_height').closest('div.col-lg-3').find('span.help-block').text('max. '+params.height.max+' '+params.height.unit);
            $('#dhldp_dhl_params #dhl_length').closest('div.col-lg-3').find('span.help-block').text('max. '+params.length.max+' '+params.length.unit);
            $('#dhldp_dhl_params #dhl_width').closest('div.col-lg-3').find('span.help-block').text('max. '+params.width.max+' '+params.width.unit);
            $('#dhldp_dhl_params #dhl_weight_package').closest('div.col-lg-3').find('span.help-block').text('max. '+params.weight_package.max+' '+params.weight_package.unit);
        },
        showAddressTypeInputs: function(el) {
            checked_val = $(el).val();
            $(el).closest('#dhldp_dhl_update_address').find('input[type=radio][name^=address][name*=address_type]').each(function(index) {
                if ($(this).val() != checked_val) {
                    $(this).closest('#dhldp_dhl_update_address').find('.address_type_'+$(this).val()).hide();
                } else {
                    $(this).closest('#dhldp_dhl_update_address').find('.address_type_'+$(this).val()).show();
                }
            });
        },
        IsNumeric: function(input) {
            var RE = /^-{0,1}\d*\.{0,1}\d+$/;
            return (RE.test(input));
        }
    }
    dhldp_admin_order.init();
});