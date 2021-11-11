{*
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
*}

<div class="box-content">
	<div class="table-responsive wk_order_table">
		<table class="table table-hover" id="my-orders-table">
			<thead>
				<tr>
					<th>{l s='ID' mod='marketplace'}</th>
					<th>{l s='Reference' mod='marketplace'}</th>
					<th>{l s='Customer' mod='marketplace'}</th>
					<th>{l s='Total' mod='marketplace'}</th>
					<th>{l s='Status' mod='marketplace'}</th>
					<th>{l s='Payment' mod='marketplace'}</th>
					<th>{l s='Date' mod='marketplace'}</th>
				</tr>
			</thead>
			<tbody>
				{if isset($mporders)}
					{foreach $mporders as $order}
						<tr class="mp_order_row" is_id_order="{$order.id_order}" is_id_order_detail="{$order.id_order_detail}">
							<td>{$order.id_order}</td>
							{*<td class="wk_cust">
								<div class="wk_cust_left">
									<input value="" type="checkbox" name="wkmp_order_status">
								</div>
								<div class="wk_cust_right">
									<span>{$order.id_order}</span>
								</div>
							</td>*}
							<td>{$order.reference}</td>
							<td>{$order.buyer_info->firstname} {$order.buyer_info->lastname}</td>
							<td data-order="{$order.total_paid_without_sign}">
								{$order.total_paid}{*TODO:should not be currency convertable*}
							</td>
							<td>{$order.order_status}</td>
							<td>{$order.payment_mode}</td>
							<td data-order="{$order.date_add}">{dateFormat date=$order.date_add full=1}</td>
						</tr>
					{/foreach}
				{/if}
			</tbody>
		</table>
	</div>
</div>