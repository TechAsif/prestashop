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
			{l s='Edit Combination' mod='marketplace'}
		{else}
			{l s='Create New Combination' mod='marketplace'}
		{/if}
	</div>
	<div class="row">
        <div class="col-md-6">
            <a href="{$link->getAdminLink('AdminSellerProductDetail')}&updatewk_mp_seller_product&id_mp_product={$mp_id_product}" class="btn btn-link wk_padding_none">
                <i class="icon-arrow-left"></i>
                <span>{l s='Back to product' mod='marketplace'}</span>
            </a>
        </div>
        {if isset($edit)}
        <div class="col-md-6 wk_text_right">
            <a href="{$link->getAdminLink('AdminMpAttributeManage')}&id={$mp_id_product}">
                <button class="btn btn-primary sensitive add" type="button">
                    <i class="icon-plus"></i>
                    {l s='Create New' mod='marketplace'}
                </button>
            </a>
            {hook h="displayMpCombinationListButton"}
        </div>
        {/if}
    </div>
	<div class="form-group">
		<form action="{if isset($edit)}{$current}&token={$token}&id_combination={$mp_id_product_attribute}{else}{$current}&token={$token}&id={$mp_id_product}{/if}" method="post" class="defaultForm">
			<div class="row">
				<div class="col-md-11">
					{include file="$wkself/../../views/templates/front/product/combination/_partials/mp-combination-fields.tpl"}
				</div>
			</div>
			<div class="panel-footer">
				<a href="{$link->getAdminLink('AdminSellerProductDetail')}&updatewk_mp_seller_product&id_mp_product={$mp_id_product}" class="btn btn-default">
					<i class="process-icon-cancel"></i>{l s='Cancel' mod='marketplace'}
				</a>
				<button type="submit" class="btn btn-default pull-right" id="submitCombination" name="submitCombination">
					<i class="process-icon-save"></i>{l s='Save' mod='marketplace'}
				</button>
			</div>
		</form>
	</div>
</div>

{strip}
	{addJsDef path_managecombination = $link->getAdminlink('AdminMpAttributeManage')}
	{addJsDefL name=attribute_req}{l s='Combination attribute cannot be blank.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=attribute_unity_invalid}{l s='Impact on price per unit should be integer.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=req_attr}{l s='Attribute is not selected.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=req_attr_val}{l s='Value is not selected.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=attr_already_selected}{l s='Attribute is already selected.' js=1 mod='marketplace'}{/addJsDefL}
{/strip}


