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

<div class="panel">
	<div class="panel-heading">
		{if isset($edit)}
			{l s='Edit Attribute Generator' mod='marketplace'}
		{else}
			{l s='Attribute Generator' mod='marketplace'}
		{/if}
	</div>
	<div class="form group">
		<a class="btn btn-link wk_padding_none" href="{$link->getAdminLink('AdminSellerProductDetail')}&updatewk_mp_seller_product&id_mp_product={$id_mp_product}">
			<i class="icon-arrow-left"></i>
			<span>{l s='Back to product' mod='marketplace'}</span>
		</a>
	</div>
	<div class="row">
		{include file="$wkself/../../views/templates/front/product/combination/_partials/generate-combination-fields.tpl"}
	</div>
</div>

{strip}
	{addJsDefL name=i18n_tax_exc}{l s='Tax Excluded' mod='marketplace' js=1}{/addJsDefL}
	{addJsDefL name=i18n_tax_inc}{l s='Tax Included' mod='marketplace' js=1}{/addJsDefL}
{/strip}