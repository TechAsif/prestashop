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

<div class="table-responsive table-responsive-row clearfix box-account wk_seller_total">
	{hook h="displayMpTransactionTopContent"}
	{if (Configuration::get('WK_MP_COMMISSION_DISTRIBUTE_ON') == 1)}
		<div class="alert alert-info">{l s='Only payment accepted earning are available' mod='marketplace'}</div>
	{/if}
	<table class="table table-bordered table-striped">
		<thead>
			<tr class="nodrag nodrop">
				<th class="wk_text_center">{l s='Total Earning' mod='marketplace'}</th>
				<th class="wk_text_center">{l s='Admin Commission' mod='marketplace'}</th>
				<th class="wk_text_center">{l s='Admin Tax' mod='marketplace'}</th>
				<th class="wk_text_center">{l s='Admin Shipping' mod='marketplace'}</th>
				<th class="wk_text_center">
					{l s='Your Earning' mod='marketplace'}
					<div class="wk_tooltip">
						<span class="wk_tooltiptext">
							{l s='Sum of seller amount, seller tax and seller shipping amount' mod='marketplace'}
						</span>
					</div>
				</th>
				<th class="wk_text_center">{l s='Your Withdrawal' mod='marketplace'}</th>
				<th class="wk_text_center">{l s='Your Due' mod='marketplace'}</th>
				{hook h=displayMpSellerTransactionTableColumnHead}
			</tr>
		</thead>
		<tbody>
		{if isset($sellerOrderTotal) && $sellerOrderTotal}
		{foreach $sellerOrderTotal as $orderTotal}
			<tr>
				<td class="wk_text_center">{$orderTotal.total_earning|escape:'htmlall':'UTF-8'}</td>
				<td class="wk_text_center">{$orderTotal.admin_commission|escape:'htmlall':'UTF-8'}</td>
				<td class="wk_text_center">{$orderTotal.admin_tax|escape:'htmlall':'UTF-8'}</td>
				<td class="wk_text_center">{$orderTotal.admin_shipping|escape:'htmlall':'UTF-8'}</td>
				<td class="wk_text_center">
					<span class="wkbadge wkbadge-success">{$orderTotal.seller_total|escape:'htmlall':'UTF-8'}</span>
				</td>
				<td class="wk_text_center">
					<span class="wkbadge wkbadge-paid">{$orderTotal.seller_recieve|escape:'htmlall':'UTF-8'}</span>
				</td>
				<td class="wk_text_center">
					<span class="wkbadge wkbadge-pending">{$orderTotal.seller_due|escape:'htmlall':'UTF-8'}</span>
				</td>
				{hook h=displayMpSellerTransactionTableColumnBody seller_payment_data = $orderTotal id_seller_customer = $id_customer}
			</tr>
		{/foreach}
		{else}
		<tr>
			<td colspan="12" class="wk_text_center">{l s='No data found' mod='marketplace'}</td>
		</tr>
		{/if}
		</tbody>
	</table>
</div>