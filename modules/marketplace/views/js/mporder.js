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
$(document).ready(function(){
	$(".mp_order_row").on("click", function(){
		var id_order =  $(this).attr('is_id_order');
		if (friendly_url) {
			window.location.href = mporderdetails_link+'?id_order='+id_order;
		} else {
			window.location.href = mporderdetails_link+'&id_order='+id_order;
		}
	});

	$('#my-orders-table').DataTable({
		"bStateSave": true,
		"language": {
			"lengthMenu": display_name+" _MENU_ "+records_name,
			"zeroRecords": no_product,
			"info": show_page+" _PAGE_ "+ show_of +" _PAGES_ ",
			"infoEmpty": no_record,
			"infoFiltered": "("+filter_from +" _MAX_ "+ t_record+")",
			"sSearch" : search_item,
			"oPaginate": {
				"sPrevious": p_page,
				"sNext": n_page
				}
		},
		"order": [[ 0, "desc" ]]
	});

	$('select[name="my-orders-table_length"]').addClass('form-control-select');
});