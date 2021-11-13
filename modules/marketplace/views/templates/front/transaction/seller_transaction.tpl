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

{hook h="DisplayMpWalletRefundhook"}
<div id="seller_transactions">
	<div class="table-responsive">
		<table class="table" id="my-orders-table">
			<thead>
				<tr>
					{*Add Id as first column for managing data in descending order*}
					<th class="wk_display_none">{l s='ID' mod='marketplace'}</th>
					<th>{l s='Transaction Type' mod='marketplace'}</th>
					<th>{l s='Transaction ID' mod='marketplace'}</th>
					<th>{l s='Seller Amount ' mod='marketplace'}</th>
					<th>{l s='Seller Tax' mod='marketplace'}</th>
					<th>{l s='Admin Comm.' mod='marketplace'}</th>
					<th>{l s='Admin Tax' mod='marketplace'}</th>
					<th>{l s='Seller Shipping' mod='marketplace'}</th>
					<th>{l s='Payment Mode' mod='marketplace'}</th>
					<th>{l s='Remark' mod='marketplace'}</th>
					<th>{l s='Date' mod='marketplace'}</th>
					<th>{l s='Action' mod='marketplace'}</th>
				</tr>
			</thead>
			<tbody>
				{if isset($transactions)}
					{foreach from=$transactions item=data}
						<tr>
							<td class="wk_display_none">{$data.id_seller_transaction_history}</td>
							<td>{$data.transaction nofilter}</td>
							<td>{$data.id_transaction}</td>
							<td data-order="{$data.seller_amount_without_sign}">
								{if isset($data.transaction_type) && $data.transaction_type == WkMpSellerTransactionHistory::MP_SELLER_ORDER}
									<span class="wkbadge wkbadge-success">{$data.seller_amount}<span>
								{else if isset($data.transaction_type) && ($data.transaction_type == WkMpSellerTransactionHistory::MP_ORDER_CANCEL || $data.transaction_type == WkMpSellerTransactionHistory::MP_ORDER_REFUND)}
									<span class="wkbadge wkbadge-danger">{$data.seller_amount}<span>
								{else if isset($data.transaction_type) && $data.transaction_type == WkMpSellerTransactionHistory::MP_SETTLEMENT}
									<span class="wkbadge wkbadge-danger">{$data.seller_amount}<span>
								{else if isset($data.transaction_type) && $data.transaction_type == WkMpSellerTransactionHistory::MP_SETTLEMENT_CANCEL}
									<span class="wkbadge wkbadge-success">{$data.seller_amount}<span>
								{/if}
							</td>
							<td data-order="{$data.seller_tax_without_sign}">{$data.seller_tax}</td>
							<td data-order="{$data.admin_commission_without_sign}">{$data.admin_commission}</td>
							<td data-order="{$data.admin_tax_without_sign}">{$data.admin_tax}</td>
							{if isset($data.seller_shipping_earning_without_sign) && isset($data.seller_shipping_earning)}
								<td data-order="{$data.seller_shipping_earning_without_sign}">
									{$data.seller_shipping_earning}
								</td>
							{else}
								<td>-</td>
							{/if}
							<td>
								{if $data.payment_method}
									{$data.payment_method}
								{else}
									{l s='N/A' mod='marketplace'}
								{/if}
							</td>
							<td>{if isset($data.remark) && $data.remark}{$data.remark}{else}--{/if}</td>
							<td>{dateFormat date=$data.date_add full=1}</td>
							<td style="text-align:center;">
								<a
									{$data.data}
									data-id-customer-seller="{$data.id_customer_seller}"
									class="{$data.class}"
									title="{l s='View Detail' mod='marketplace'}"
									class="btn btn-default" href=""><i class="material-icons">remove_red_eye</i></i>
								</a>
							</td>
						</tr>
					{/foreach}
				{/if}
			</tbody>
		</table>
	</div>
</div>
<!--- Order Detail PopUp Box -->
<div class="modal fade" id="orderDetail" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" id="wk_seller_product_line"></div>
    </div>
</div>

<!--- Order Detail PopUp Box -->
<div class="modal fade" id="settlementDetail" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" id="wk_seller_transaction_line"></div>
    </div>
</div>