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

{if isset($mp_voucher_info) && $mp_voucher_info}
	<div class="wk-order-voucher">
			<div class="table-responsive">
				<table class="table">
					<thead>
						<tr>
							<th><strong>{l s='Discount name' mod='marketplace'}</strong></th>
							<th><strong>{l s='Value' mod='marketplace'}</strong></th>
						</tr>
					</thead>
					<tbody>
						{foreach $mp_voucher_info as $mp_voucher}
							<tr>
								<td>{$mp_voucher['voucher_name']}</td>
								<td>{$mp_voucher['voucher_value']}</td>
							</tr>
						{/foreach}
						<tr>
							<td><strong>{l s='Total' mod='marketplace'}</strong></td>
							<td>{$total_voucher}</td>
						</tr>
					</tbody>
				</table>
			</div>
	</div>
{/if}