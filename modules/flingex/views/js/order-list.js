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

(function($){
  $.fn.serializeObject = function () {
    "use strict";

    var result = {};
    var extend = function (i, element) {
    var node = result[element.name];
    
      if ('undefined' !== typeof node && node !== null) {
        if ($.isArray(node)) {
          node.push(element.value);
        } else {
          result[element.name] = [node, element.value];
        }
      } else {
        result[element.name] = element.value;
      }
    };

    $.each(this.serializeArray(), extend);
    return result;
  };
})(jQuery);

var dhldp_list = {
  init: function() {
    dhldp_list.createButton();

    $('#generateDHLDPDhlLabels').click(function(evt){
      evt.preventDefault();

      dhldp_list.processButtonClick('dhl');

      return false;
    });
    $('#generateDHLDPDpLabels').click(function(evt){
      evt.preventDefault();

      dhldp_list.processButtonClick('dp');

      return false;
    });
  },
  
  createButton: function() {
      var el_after;
	  if (is177) {
		  if($('#order_grid .btn-group').length) {
			  var btn = $('#order_grid .btn-group');
			  el_after = $(btn[0]);
		  }
	  } else {
		  if ($('.bulk-actions.btn-group').length) {
			el_after = $('.bulk-actions.btn-group');
		  } else if ($('table.order').length) {
			el_after = $('table.order');
		  }
	  }
      if (typeof el_after != "undefined")
        $(document.createElement('a')).addClass('button btn btn-warning bulk-actions')
            .attr({'href': '#','id': 'generateDHLDPDhlLabels'}).text(dhldp_list.translate('Generate FLINGEX labels')).insertAfter($(el_after));
    $(document.createElement('a')).addClass('button btn btn-warning bulk-actions')
        .attr({'href': '#','id': 'generateDHLDPDpLabels'}).text(dhldp_list.translate('Generate LGTech labels')).insertAfter($(el_after));
  },
  
  processButtonClick: function(mode = 'dhl') {
    list = dhldp_list.collectSelectedBoxes();
    
    if (list) {
      var link = dhldp_request_path + '&mode=' + mode;
      
      for (var i in list) {
        link+= '&order_list[]=' + list[i];
      }
      
      window.location = link;
    }
  },
  
  collectSelectedBoxes: function() {
	  if (is177) {
		var collection = $('input:checkbox[name^=order_orders_bulk]:checked');
	  } else {
		var collection = $('input:checkbox[name^=orderBox]:checked');
	  }
        list = [];
    
    if (collection.length) {
      collection.each(function(){
        list.push($(this).attr('value'));
      });
      
      return list;
    }
    
    return false;
  },
  
  translate: function(str) {
    if (typeof(dhldp_translation) != 'undefined' && str in dhldp_translation) {
      return dhldp_translation[str];
    }
    
    return str;
  }
}

$(function(){
    dhldp_list.init();
})