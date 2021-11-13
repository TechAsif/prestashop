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
	var mp_product_id = $('#mp_product_id').val();
	var catIds = $('#product_category').val();

	$('#categorycontainer').jstree({
		"checkbox" : {
		  "keep_selected_style" : false, //if true then selection will display highlighted
		  "three_state" : false, //if true then selection will work according to parent
		  "whole_node" : true //if true then whole node (checkbox and content will work with click event)
		},
		"plugins" : [ "checkbox" ],
		'core' : {
		  'data' : {
		    "url" : path_sellerproduct,
		    "dataType" : "json",
		    "data" : function (node) {
		      return {
			      	"catsingleId" : node.id,
			      	"id_mp_product" : mp_product_id,
			      	"catIds" : catIds,
			      	"token" : $('#wk-static-token').val(),
			      	"ajax": true,
			      	"action" : "productCategory"
			    };
		    }
		  }
		}
	})
	.on('changed.jstree', function (e, data) { //When category changed then default category options will change.
		$('#default_category').html('');
		var default_category_html = '';
	    var i, j;
	    for(i = 0, j = data.selected.length; i < j; i++) {

	    	var selected_category_id = data.instance.get_node(data.selected[i]).id;
	    	var selected_category_name = data.instance.get_node(data.selected[i]).text;

	    	default_category_html += '<option value="'+selected_category_id+'"id="default_cat'+selected_category_id+'" name="'+selected_category_name+'" ';

	    	if (typeof defaultIdCategory !== 'undefined' && defaultIdCategory == selected_category_id) {
                default_category_html += 'Selected="selected"';
            }

	    	default_category_html += '>'+selected_category_name+'</option>';
	    }

	    $('#default_category').html(default_category_html);
	});
});