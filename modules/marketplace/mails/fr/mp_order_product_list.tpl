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

{foreach $list as $product_attribute}
	{foreach $product_attribute as $product_customization}
		{foreach $product_customization as $product}
			<tr>
				<td style="border:1px solid #D6D4D4;">
					<table class="table">
						<tr>
							<td width="10">&nbsp;</td>
							<td>
								<font size="2" face="Open-sans, sans-serif" color="#555454">
									<strong>{$product['product_name']}</strong>
								</font>
							</td>
							<td width="10">&nbsp;</td>
						</tr>
					</table>
				</td>
				<td style="border:1px solid #D6D4D4;">
					<table class="table">
						<tr>
							<td width="10">&nbsp;</td>
							<td align="right">
								<font size="2" face="Open-sans, sans-serif" color="#555454">
									{$product['unit_price_tax_excl']}
								</font>
							</td>
							<td width="10">&nbsp;</td>
						</tr>
					</table>
				</td>
				<td style="border:1px solid #D6D4D4;">
					<table class="table">
						<tr>
							<td width="10">&nbsp;</td>
							<td align="right">
								<font size="2" face="Open-sans, sans-serif" color="#555454">
									{$product['unit_price_tax_incl']}
								</font>
							</td>
							<td width="10">&nbsp;</td>
						</tr>
					</table>
				</td>
				<td style="border:1px solid #D6D4D4;">
					<table class="table">
						<tr>
							<td width="10">&nbsp;</td>
							<td align="right">
								<font size="2" face="Open-sans, sans-serif" color="#555454">
									{$product['product_quantity']}
								</font>
							</td>
							<td width="10">&nbsp;</td>
						</tr>
					</table>
				</td>
				<td style="border:1px solid #D6D4D4;">
					<table class="table">
						<tr>
							<td width="10">&nbsp;</td>
							<td align="right">
								<font size="2" face="Open-sans, sans-serif" color="#555454">
									{$product['total_price_tax_incl']}
								</font>
							</td>
							<td width="10">&nbsp;</td>
						</tr>
					</table>
				</td>
			</tr>
		{/foreach}
	{/foreach}
{/foreach}