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
	$('.open-popup-link').click(function() 	{
		var id_currency = $(this).attr('data-id-currency');
		var total_due = $('#wk_seller_due_'+id_currency).val();
		$(':hidden#total_due').attr('value', total_due);
		$(':hidden#id_currency').attr('value', id_currency);
		$('#wk_max_amount').text($('#wk_seller_due_'+id_currency).attr('data-value'));
	});

	$(document).on('click', '#pay_money', function() {
		var amount = $('#amount').val();
		var id_currency = $('#id_currency').val();
		var total_due = $('#wk_seller_due_'+id_currency).val();

		if(!amount) {
			$('#wk_seller_error').show('slow').empty().text(empty);
			return false;
		} else if(!$.isNumeric(amount)) {
			$('#wk_seller_error').show('slow').empty().text(invalid_amount);
			return false;
		}
		else if(parseFloat(amount) <= 0) {
			$('#wk_seller_error').show('slow').text(negative_err);
			return false;
		}
		else if(parseFloat(amount) > parseFloat(total_due)) {
			$('#wk_seller_error').show('slow').empty().text(pay_more_error);
			return false;
		}
		$('#pay_money').addClass('wk_mp_disabled');
		$('#wk_seller_error').hide().empty();
	});


	// Open modal box to show order detail with product wise on transaction controller
	$(document).on('click',  '.wk_view_detail', function(e){
		var idOrder = $(this).attr('data-id-order');
		var idCustomerSeller = $(this).attr('data-id-customer-seller');
		$.ajax({
			'type' : 'POST',
			'url' : current_url,
			'cache' : false,
			'async' : false,
			'data' : {
				'ajax' : true,
				'action' : 'orderDetail',
				'id_order' : idOrder,
				'id_customer_seller' : idCustomerSeller,
			},
			success : function(result) {
				$('#wk_seller_product_line').html(result);
				$('#orderDetail').modal('show');
			}
		});
		return false;
	});

	// Open modal box to show settlement detail
	$(document).on('click',  '.wk_view_transaction_detail', function(e){
		var idTransaction = $(this).attr('data-id-transaction');
		var idCustomerSeller = $(this).attr('data-id-customer-seller');
		$.ajax({
			'type' : 'POST',
			'url' : current_url,
			'cache' : false,
			'async' : false,
			'data' : {
				'ajax' : true,
				'action' : 'transactionDetail',
				'id_transaction' : idTransaction,
				'id_customer_seller' : idCustomerSeller,
			},
			success : function(result) {
				$('#wk_seller_transaction_line').html(result);
				$('#settlementDetail').modal('show');
			}
		});
		return false;
	});

	// Open modal box to show order detail with product wise on order controller
	$(document).on('click',  '#wk_order_detail_view', function(e){
		var idOrder = $(this).attr('data-id-order');
		var idSellerCustomer = $(this).attr('data-id-seller-customer');
		$.ajax({
			'type' : 'POST',
			'url' : current_url,
			'cache' : false,
			'async' : false,
			'data' : {
				'ajax' : true,
				'action' : 'viewOrderDetail',
				'id_order' : idOrder,
				'seller_customer_id' : idSellerCustomer
			},
			success : function(result) {
				$('#wk_seller_product_line').html(result);
				$('#orderDetail').modal('show');
			}
		});
		return false;
	});
});
