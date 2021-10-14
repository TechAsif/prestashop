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
    var dhldp_address = {
        map: null,
        bounds: null,
        markers: [],
        marker_image: {
            'ps':dhldp_path+'views/img/ps_logo.png',
            'pf':dhldp_path+'views/img/pf_logo.png'
        },
        marker_image_highlight: {
            'ps':dhldp_path+'views/img/ps_logo_highlight.png',
            'pf':dhldp_path+'views/img/pf_logo_highlight.png'
        },
        version: 16,
        form_block: null,
        address1_input_block: null,
        address2_input_block: null,
        id_country_input_block : null,
        init: function() {
            if ($('form#add_address').length > 0) {
                this.form_block = $('form#add_address');
                this.version = 16;
                this.address1_input_block = $('input[name=address1]').closest('.form-group');
                this.address2_input_block = $('input[name=address2]').closest('.form-group');
                this.id_country_input_block = $('select[name=id_country]').closest('.form-group');
            } else if ($('form#add_adress').length > 0) {
                this.form_block = $('form#add_adress');
                this.version = 15;
                this.address1_input_block = $('input[name=address1]').closest('p');
                this.address2_input_block = $('input[name=address2]').closest('p');
                this.id_country_input_block = $('select[name=id_country]').closest('p');
            } else if ($('div.address-form form').length > 0) {
                this.form_block = $('div.address-form form');
                this.version = 17;
                this.address1_input_block = $('input[name=address1]').closest('.form-group');
                this.address2_input_block = $('input[name=address2]').closest('.form-group');
                this.id_country_input_block = $('select[name=id_country]').closest('.form-group');
            } else if ($('div.js-address-form form').length > 0) {
                this.form_block = $('div.js-address-form form');
                this.version = 17;
                this.address1_input_block = $('input[name=address1]').closest('.form-group');
                this.address2_input_block = $('input[name=address2]').closest('.form-group');
                this.id_country_input_block = $('select[name=id_country]').closest('.form-group');
            }

            var self = this;

            if (this.form_block != null) {
                self.loadElements();

                $('select[name=id_country]').change(function(e) {
                    self.showElements();
                });

                if (this.version == 17) {
                    $(document).bind("ajaxComplete", function(event, xhr, settings ){
                        if (typeof(settings.data) != 'undefined') {
                            var ajax_data = {},
                                st;

                            st = settings.data.split('&');

                            if (st.length) {
                                for (var i in st) {
                                    ajax_data[decodeURIComponent(st[i].split('=')[0])] = decodeURIComponent(st[i].split('=')[1]);
                                }

                                if (typeof(ajax_data.id_country) != 'undefined') {
                                    // reinit after reloading address form
                                    self.init();
                                }
                            }
                        }
                    });
                }

                $(document).on('click', 'input[name=address_type]', function(e){
                    switch($(this).val()) {
                        case 'RE':
                            self.address1_input_block.show();
                            self.address2_input_block.show();
                            $('#block_PS').hide();
                            $('#block_PF').hide();
                            $('input[name=packstation_number]').removeAttr('required');
                            $('input[name=post_number_ps]').removeAttr('required');
                            $('input[name=post_number_pf]').removeAttr('required');
                            $('input[name=postfiliale_number]').removeAttr('required');
                            break;
                        case 'PS':
                            if ($('input[name=address1]').val() == '') {
                                $('input[name=address1]').val('.')
                            }
                            self.address1_input_block.hide();
                            self.address2_input_block.hide();
                            $('#block_PS').show();
                            $('#block_PF').hide();
                            $('input[name=packstation_number]').attr('required', 'required');
                            $('input[name=post_number_ps]').attr('required', 'required');
                            $('input[name=post_number_pf]').removeAttr('required');
                            $('input[name=postfiliale_number]').removeAttr('required');
                            break;
                        case 'PF':
                            if ($('input[name=address1]').val() == '') {
                                $('input[name=address1]').val('.')
                            }
                            self.address1_input_block.hide();
                            self.address2_input_block.hide();
                            $('#block_PS').hide();
                            $('#block_PF').show();
                            $('input[name=packstation_number]').removeAttr('required');
                            $('input[name=post_number_ps]').removeAttr('required');
                            $('input[name=post_number_pf]').attr('required', 'required');
                            $('input[name=postfiliale_number]').attr('required', 'required');
                            break;
                        default:
                            self.address1_input_block.show();
                            self.address2_input_block.show();
                            $('#block_PS').hide();
                            $('#block_PF').hide();
                            $('input[name=packstation_number]').removeAttr('required');
                            $('input[name=post_number_ps]').removeAttr('required');
                            $('input[name=post_number_pf]').removeAttr('required');
                            $('input[name=postfiliale_number]').removeAttr('required');
                    }
                });

                this.form_block.on('submit', function(e) {
                    if ($('input[name=address_type]:checked').val() == 'PS') {
                        $('input[name=address1]').val(dhldp_address_data['address_types']['PS']['prefix'] + ' '+ $.trim($('input[name=packstation_number]').val()));
                        $('input[name=address2]').val($.trim($('input[name=post_number_ps]').val()));
                    } else if ($('input[name=address_type]:checked').val() == 'PF') {
                        $('input[name=address1]').val(dhldp_address_data['address_types']['PF']['prefix'] + ' '+ $.trim($('input[name=postfiliale_number]').val()));
                        $('input[name=address2]').val($.trim($('input[name=post_number_pf]').val()));
                    }
                });

                $(document).on('click', '#searchPS', function(e) {
                    e.preventDefault();
                    self.openMap('ps');
                });

                $(document).on('click', '#searchPF', function(e) {
                    e.preventDefault();
                    self.openMap('pf');
                });

                $(document).on('click', '#dhl_map button#findOnMap', function(e) {
                    e.preventDefault();
                    self.searchOnMap();
                });

                $(document).on('mouseover', '#dhl_map .dhl_item', function(e) {
                    //google.maps.event.trigger(self.markers[$(this).attr('data-index')],'mouseover');

                    if (! $(this).hasClass('dhl_item_selected')) $(this).addClass('dhl_item_highlight');
                    self.map.setCenter(self.markers[$(this).attr('data-index')].getPosition());
                    self.map.setZoom(13);
                    if ($('#dhl_map input[name=dhl_map_type]').val() == 'ps')
                        self.markers[$(this).attr('data-index')].setIcon(new google.maps.MarkerImage(self.marker_image_highlight.ps));
                    else
                        self.markers[$(this).attr('data-index')].setIcon(new google.maps.MarkerImage(self.marker_image_highlight.pf));

                });

                $(document).on('mouseout', '#dhl_map .dhl_item', function(e) {
                    //google.maps.event.trigger(self.markers[$(this).attr('data-index')],'mouseout');
                    $(this).removeClass('#dhl_map dhl_item_highlight');
                    if ($('#dhl_map input[name=dhl_map_type]').val() == 'ps')
                        self.markers[$(this).attr('data-index')].setIcon(new google.maps.MarkerImage(self.marker_image.ps));
                    else
                        self.markers[$(this).attr('data-index')].setIcon(new google.maps.MarkerImage(self.marker_image.pf));
                });

                $(document).on('click', '#dhl_map .dhl_item button.select', function(e) {
                    e.preventDefault();
                    new google.maps.event.trigger(self.markers[$(this).parent().parent().attr('data-index')], 'click' );
                });

                $(document).on('click', '#dhl_map .dhl_item button.select_close', function(e) {
                    e.preventDefault();
                    new google.maps.event.trigger(self.markers[$(this).parent().parent().attr('data-index')], 'click' );
                    $.fancybox.close();
                });
            }
        },
        loadElements: function() {
            var self = this;
            $.post(dhldp_ajax, {getAddressAdditions: true}, function(data){
                if (self.id_country_input_block.length > 0) {
                    if (self.id_country_input_block.parent().find('#address_types').length == 0) {
                        self.id_country_input_block.after(data);
                    }
                }
                self.showElements();
            }, 'html');
        },
        showElements: function() {
            if ($('select[name=id_country]').length) {
                var country_data = dhldp_country_data[$('select[name=id_country]').val()];
                if (typeof country_data != 'undefined') {
                    if (country_data['iso_code'] == 'DE') {
                        if ($('input[name=address1]').val().search(/^Packstation/) != -1) {
                            $('input[name=packstation_number]').attr('required', 'required').val($.trim($('input[name=address1]').val().replace(/Packstation/, '')));
                            $('input[name=post_number_ps]').attr('required', 'required').val($.trim($('input[name=address2]').val()));
                            $('input[name=post_number_pf]').removeAttr('required');
                            $('input[name=postfiliale_number]').removeAttr('required');
                            $('input#address_type_PS').trigger('click');
                        } else if ($('input[name=address1]').val().search(/^Postfiliale/) != -1) {
                            $('input[name=postfiliale_number]').attr('required', 'required').val($.trim($('input[name=address1]').val().replace(/Postfiliale/, '')));
                            $('input[name=post_number_pf]').attr('required', 'required').val($.trim($('input[name=address2]').val()));
                            $('input[name=packstation_number]').removeAttr('required');
                            $('input[name=post_number_ps]').removeAttr('required');
                            $('input#address_type_PF').trigger('click');
                        } else {
                            $('input#address_type_RE').trigger('click');
                            $('input[name=packstation_number]').removeAttr('required');
                            $('input[name=post_number_ps]').removeAttr('required');
                            $('input[name=post_number_pf]').removeAttr('required');
                            $('input[name=postfiliale_number]').removeAttr('required');
                        }
                        $('#address_types').show();
                    } else {
                        $('#address_types').hide();
                        $('input#address_type_RE').trigger('click');
                        $('input[name=packstation_number]').removeAttr('required');
                        $('input[name=post_number_ps]').removeAttr('required');
                        $('input[name=post_number_pf]').removeAttr('required');
                        $('input[name=postfiliale_number]').removeAttr('required');
                        this.address1_input_block.show();
                        this.address2_input_block.show();
                    }
                }
            }
        },
        openMap: function(map_type) {
            var self = this;
            $('#dhl_map input[name=dhl_map_type]').val(map_type);
            $('.dhl_map_title').hide();
            $('.dhl_map_title#dhl_map_title_'+map_type).show();
            $('#dhl_map input[name=dhl_map_zip]').val($('input[name=postcode]').val());
            $('#dhl_map input[name=dhl_map_city]').val($('input[name=city]').val());
            $.fancybox({
                'href': '#dhl_map',
                'hideOnContentClick': false,
                'showCloseButton': true,
                'modal': false,
                'width': 1000,
                'height': 600,//$('#dhl_map').outerHeight(true)+10,
                'padding' : 10,
                'autoSize': false,
                beforeShow: function () {
                    if (self.map)
                        google.maps.event.trigger(self.map, "resize");
                },
                'afterShow': function() {
                    self.initializeMap($('#dhl_map #map'));
                    self.searchOnMap();
                },
                'onUpdate': function() {
                    $('.map_wrapper').css('height', $('.fancybox-inner').height() - $('.dhl_map_title').outerHeight(true));
                    $('#dhl_map #map').css('height', $('.map_wrapper').height());
                    $('#dhl_map #map').css('width', $('.map_wrapper').width());
                    $('#dhl_map .dhl_list').css('height', $('.map_wrapper').height());
                    if (self.map)
                        google.maps.event.trigger(self.map, "resize");
                }
            });
        },
        initializeMap: function(el) {
            var resize = false;
            if (this.map != null) {
                resize = true;
            }
            this.map = new google.maps.Map($(el).get(0), {
                center: new google.maps.LatLng(52.5498783, 13.425209099999961),
                zoom: 10,
                mapTypeId: 'roadmap',
                zoomControl: true,
                mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
            });
            this.map.setZoom( this.map.getZoom() );


            //this.infoWindow = new google.maps.InfoWindow();
            if (resize) {
                var timeout =
                    setTimeout(function() {
                        clearTimeout(timeout);
                        google.maps.event.trigger(this.map, 'resize')
                    }, 500);
            }
        },
        searchOnMap: function() {
            var self = this;
            request = {};
            switch ($('#dhl_map input[name=dhl_map_type]').val()) {
                case 'ps':
                    request.getPackstations = true;break;
                case 'pf':
                    request.getPostfiliales = true;break;
            }
            request.zip = $('#dhl_map input[name=dhl_map_zip]').val();
            request.city = $('#dhl_map input[name=dhl_map_city]').val();
            request.street = $('#dhl_map input[name=dhl_map_street]').val();
            request.streetNo = $('#dhl_map input[name=dhl_map_street_no]').val();

            $('.dhl_map #errors').hide();
            this.clearMarkersOnMap();
            this.markers = [];
            $('.dhl_map .dhl_item').detach();

            $.post(dhldp_ajax, request, function(data){
                if (!data.errors) {
                    self.initMarkersOnMap(data);
                } else {
                    self.showErrorsOnMap(data.errors);
                }
            }, 'json');
        },
        showErrorsOnMap: function (errors) {
            var html_errors = '<ol>';
            $('.dhl_map #errors').html();
            for (var i = 0; i < errors.length; i++) {
                html_errors += '<li>'+errors[i]+'</li>';
            }
            $('.dhl_map #errors').html(html_errors).slideDown('slow');
        },
        initMarkersOnMap: function(data) {
            this.markers = [];
            var json = JSON.parse(JSON.stringify(data));
            this.bounds = new google.maps.LatLngBounds();
            $('.dhl_map .dhl_list .dhl_item').remove();
            for (var i = 0; i < json.length; i++) {
                var type = '', item = '', item_id = '', item_name = '';

                if (json[i].packstationId) {
                    type = 'ps';
                    item = json[i].packstationId;
                    item_id = type + '_' + item;
                    item_name = dhldp_trans.packstation
                }
                if (json[i].depotServiceNo) {
                    type = 'pf';
                    item = json[i].depotServiceNo;
                    item_id = type + '_' + item;
                    item_name = dhldp_trans.postfiliale
                }
                var address = json[i].address.street + ' '+json[i].address.streetNo + ', '+json[i].address.zip+' ' +
                    json[i].address.city + ' '+json[i].address.district;
                if (json[i].address.remark) address += ', '+json[i].address.remark;


                var latlng = new google.maps.LatLng(
                    parseFloat(json[i].location.latitude),
                    parseFloat(json[i].location.longitude));

                this.createMarkerOnMap(latlng, type, item, address, json[i].address.city, json[i].address.zip);

                this.bounds.extend(latlng);

                //init list items
                $('.dhl_map .dhl_list').append('<div class="row dhl_item" data-index="'+i+'" id="'+item_id+ '">' +
                    '<div class="item">'+item_name+' '+item+'</div><div class="address">' + address + '</div>' +
                    '<div class="action"><button class="select button btn btn-default button-small"><span>'+dhldp_trans.select +' <i class="icon-ok-sign"></i></span>' +
                    '<button class="select_close button btn btn-default button-small"><span>'+dhldp_trans.select_close +' <i class="icon-ok-sign"><i class="icon-exit"></span></button></div></div>');
            };

            this.map.setCenter(this.bounds.getCenter());
            this.map.fitBounds(this.bounds);
            this.map.setZoom(this.map.getZoom());
        },
        clearMarkersOnMap: function () {
            for (var i = 0; i < this.markers.length; i++) {
                this.markers[i].setMap(null);
            }
        },
        createMarkerOnMap: function(latlng, type, item, address, city, zip) {
            var self = this;

            var marker = '';
            if (type == 'ps')
                var image = new google.maps.MarkerImage(this.marker_image.ps);
            else
                var image = new google.maps.MarkerImage(this.marker_image.pf);

            marker = new google.maps.Marker({ map: this.map, icon: image, position: latlng });
            google.maps.event.addListener(marker, 'click', function() {
                $('.dhl_item').removeClass('dhl_item_selected');
                $('#'+type+'_'+item).addClass('dhl_item_selected');
                for (var i = 0; i < self.markers.length; i++) {
                    self.markers[i].setAnimation(null);
                }
                marker.setAnimation(google.maps.Animation.BOUNCE);
                self.map.setCenter(marker.getPosition());
                self.map.setZoom(15);
                if (type == 'ps')
                    $('input[name=packstation_number]').val(item);
                else
                    $('input[name=postfiliale_number]').val(item);
                $('input[name=city]').val(city);
                $('input[name=postcode]').val(zip);
            });
            google.maps.event.addListener(marker, 'mouseover', function(){
                if (!$('#'+type+'_'+item).hasClass('dhl_item_selected')) $('#'+type+'_'+item).addClass('dhl_item_highlight');
                $('.dhl_list').scrollTo('#'+type+'_'+item, 50);
                //self.map.setCenter(marker.getPosition());
                self.map.panTo(marker.getPosition());
                self.map.setZoom(13);
                if (type == 'ps')
                    marker.setIcon(new google.maps.MarkerImage(self.marker_image_highlight.ps));
                else
                    marker.setIcon(new google.maps.MarkerImage(self.marker_image_highlight.pf));
            });

            google.maps.event.addListener(marker, 'mouseout', function(){
                //$('img[src="'+this.icon.url+'"]').stop().animate({opacity:.5});
                $('#'+type+'_'+item).removeClass('dhl_item_highlight');
                if (type == 'ps')
                    marker.setIcon(new google.maps.MarkerImage(self.marker_image.ps));
                else
                    marker.setIcon(new google.maps.MarkerImage(self.marker_image.pf));
            });

            this.markers.push(marker);
        }
    }

    dhldp_address.init();
});