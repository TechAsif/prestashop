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

{extends file=$layout}
{block name='content'}

{if isset($smarty.get.update)}
	<p class="alert alert-success">
		<button data-dismiss="alert" class="close" type="button">×</button>
		{l s='Combination Updated Successfully' mod='marketplace'}
	</p>
{else if isset($smarty.get.edited_qty)}
	<p class="alert alert-success">
		<button data-dismiss="alert" class="close" type="button">×</button>
		{l s='Only Quantity has been updated successfully.' mod='marketplace'}
	</p>
{/if}
{if (isset($editProductPermissionNotAllow) || isset($editPermissionNotAllow)) && isset($edit)}
	<p class="alert alert-danger">
		<button data-dismiss="alert" class="close" type="button">×</button>
		{if isset($qtyAllow)}
			{l s='You can edit only quantity. You do not have permission to edit other fields.' mod='marketplace'}
		{else}
			{l s='You do not have permission to edit this.' mod='marketplace'}
		{/if}
	</p>
{/if}
{if $logged}
	<div class="wk-mp-block">
		{hook h="DisplayMpMenu"}
		<div class="wk-mp-content">
			<div class="page-title" style="background-color:{$title_bg_color};">
				<span style="color:{$title_text_color};">
					{if isset($edit)}
						{l s='Edit Combination' mod='marketplace'}
					{else}
						{l s='Add Combination' mod='marketplace'}
					{/if}
				</span>
			</div>

			<form action="{if isset($edit)}{$link->getModuleLink('marketplace', 'managecombination', ['id_combination' => $mp_id_product_attribute])}{else}{$link->getModuleLink('marketplace', 'managecombination', ['id' => $mp_id_product])}{/if}" method="post" class="form-horizontal">
				<input type="hidden" name="token" id="wk-static-token" value="{$static_token}">
				<div class="wk-mp-right-column">
					<div class="row">
                        <div class="col-md-6">
                            <a href="{$link->getModuleLink('marketplace', 'updateproduct', ['id_mp_product' => $mp_id_product])}" class="btn btn-link wk_padding_none">
                                <i class="material-icons">&#xE5C4;</i>
                                <span>{l s='Back to product' mod='marketplace'}</span>
                            </a>
                        </div>
                        {if isset($edit) && $permissionData.combinationPermission.add}
                        <div class="col-md-6 wk_text_right">
                            <a href="{$link->getModuleLink('marketplace', 'managecombination', ['id' => $mp_id_product])}">
                                <button class="btn btn-primary-outline sensitive add" type="button">
                                    <i class="material-icons">&#xE145;</i>
                                    {l s='Create New' mod='marketplace'}
                                </button>
                            </a>
                            {hook h="displayMpCombinationListButton"}
                        </div>
                        {/if}
                    </div>

					{block name='mp-combination-fields'}
						{include file='module:marketplace/views/templates/front/product/combination/_partials/mp-combination-fields.tpl'}
					{/block}

					<div class="form-group row">
						<div class="col-xs-6 col-sm-6 col-md-6">
							<a href="{$link->getModuleLink('marketplace', 'updateproduct', ['id_mp_product' => $mp_id_product])}" class="btn wk_btn_cancel wk_btn_extra">
								{l s='Cancel' mod='marketplace'}
							</a>
						</div>
						<div class="col-xs-6 col-sm-6 col-md-6 wk_text_right">
							<button type="submit" class="btn btn-success wk_btn_extra" id="submitStayCombination" name="submitStayCombination">
								{l s='Save & Stay' mod='marketplace'}
							</button>
							<button type="submit" class="btn btn-success wk_btn_extra" id="submitCombination" name="submitCombination">
								{l s='Save' mod='marketplace'}
							</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
{else}
	<div class="alert alert-danger">
		{l s='You are logged out. Please login to update combination.' mod='marketplace'}</span>
	</div>
{/if}
{/block}
