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

<hr>
<div {if isset($backendController)}class="col-lg-6 col-lg-offset-2"{/if}>
	<div class="row">
		<h4 class="col-md-12">{l s='Pricing' mod='marketplace'}</h4>
	</div>
	{hook h='displayMpProductPriceTop'}
	<div class="form-group row">
		<div class="col-md-6">
			<label for="price" class="control-label required">
				{l s='Price (tax excl.)' mod='marketplace'}
				<div class="wk_tooltip">
					<span class="wk_tooltiptext">{l s='This is the retail price at which you intend to sell this product to your customers.' mod='marketplace'}</span>
				</div>
			</label>
			<div class="input-group">
				<input type="text"
		  		id="price"
		  		name="price"
		  		value="{if isset($smarty.post.price)}{$smarty.post.price}{else if isset($product_info)}{$product_info.price}{else}0.000000{/if}"
		  		class="form-control"
		  		data-action="input_excl"
		  		pattern="\d+(\.\d+)?"
		  		autocomplete="off"
		  		placeholder="{l s='Enter Product Base Price' mod='marketplace'}" />
				<span class="input-group-addon">{$defaultCurrencySign}</span>
			</div>
			{if isset($admin_commission)}
				<span id="wk_display_admin_commission" class="form-control-comment">
					{l s='Admin commission will be %s of base price you entered.' sprintf=[$admin_commission] mod='marketplace'}
				</span>
		  	{/if}
		</div>
		<!-- Product Tax Rule  -->
		{if isset($mp_seller_applied_tax_rule) && $mp_seller_applied_tax_rule && isset($tax_rules_groups)}
			<div class="col-md-6">
				<label for="id_tax_rules_group" class="control-label">
					{l s='Tax Rule' mod='marketplace'}
				</label>
				<div class="row">
					<div class="col-md-12">
						<select name="id_tax_rules_group" id="id_tax_rules_group" class="form-control form-control-select" data-action="input_excl">
							<option value="0">{l s='No tax' mod='marketplace'}</option>
							{foreach $tax_rules_groups as $tax_rule}
								<option value="{$tax_rule.id_tax_rules_group|escape:'html':'UTF-8'}"
								{if isset($id_tax_rules_group)}{if $id_tax_rules_group == $tax_rule.id_tax_rules_group} selected="selected"{/if}{else}{if $defaultTaxRuleGroup == $tax_rule.id_tax_rules_group} selected="selected" {/if}{/if}>
									{$tax_rule.name|escape:'html':'UTF-8'}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
		{/if}
	</div>
	{if isset($mp_seller_applied_tax_rule) && $mp_seller_applied_tax_rule && isset($tax_rules_groups)}
		<div class="alert alert-info">{l s='Product Price (tax incl.) will be calculated on the basis of customer address.' mod='marketplace'}</div>
	{/if}
	<div class="form-group row">
		{if Configuration::get('WK_MP_PRODUCT_WHOLESALE_PRICE') || isset($backendController)}
			<div class="col-md-6 ">
				<label for="wholesale_price" class="control-label">
					{l s='Wholesale Price' mod='marketplace'}
					<div class="wk_tooltip">
						<span class="wk_tooltiptext">{l s='The cost price is the price you paid for the product. Do not include the tax. It should be lower than the retail price: the difference between the two will be your margin.' mod='marketplace'}</span>
					</div>
				</label>
				<div class="input-group">
					<input type="text"
					id="wholesale_price"
					name="wholesale_price"
					value="{if isset($smarty.post.wholesale_price)}{$smarty.post.wholesale_price}{else if isset($product_info)}{$product_info.wholesale_price}{else}0.000000{/if}"
					class="form-control"
					pattern="\d+(\.\d+)?"
					placeholder="{l s='Enter Product Wholesale Price' mod='marketplace'}" />
					<span class="input-group-addon">{$defaultCurrencySign}</span>
				</div>
			</div>
		{/if}
		{if Configuration::get('WK_MP_PRODUCT_PRICE_PER_UNIT') || isset($backendController)}
			<div class="col-md-6">
				<label for="unit_price" class="control-label">
					{l s='Price per unit (tax excl.) ' mod='marketplace'}
					<div class="wk_tooltip">
						<span class="wk_tooltiptext">{l s='Some products can be purchased by unit (per bottle, per pound, etc.), and this is the price for one unit. For instance, if you’re selling fabrics, it would be the price per meter.' mod='marketplace'}</span>
					</div>
				</label>
				<div class="row">
					<div class="col-md-6">
						<div class="input-group">
							<input type="text"
							id="unit_price"
							name="unit_price"
							value="{if isset($smarty.post.unit_price)}{$smarty.post.unit_price}{else if isset($product_info)}{$product_info.unit_price}{else}0.000000{/if}"
							class="form-control"
							pattern="\d+(\.\d+)?" />
							<span class="input-group-addon">{$defaultCurrencySign}</span>
						</div>
					</div>
					<div class="col-md-6">
						<input type="text"
						id="unity"
						name="unity"
						value="{if isset($smarty.post.unity)}{$smarty.post.unity}{else if isset($product_info)}{$product_info.unity}{/if}"
						class="form-control"
						placeholder="{l s='Per kilo, per litre' mod='marketplace'}" />
					</div>
				</div>
			</div>
		{/if}
	</div>
	{* On Sale flag on product *}
	{if Configuration::get('WK_MP_PRODUCT_ON_SALE') || isset($backendController)}
		<div class="form-group">
			<div class="checkbox">
				<label>
					<input type="checkbox" name="on_sale" id="on_sale" value="1" {if isset($product_info) && $product_info.on_sale == '1'}Checked="checked"{/if}>
					<span>
						{l s='Display the "On sale!" flag on the product page, and on product listings.' mod='marketplace'}
					</span>
				</label>
			</div>
		</div>
	{/if}
	{hook h='displayMpProductPriceBottom'}
</div>
{if isset($backendController)}<div class="clearfix"></div>{/if}