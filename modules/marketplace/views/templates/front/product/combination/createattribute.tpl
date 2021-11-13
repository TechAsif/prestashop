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
{if $logged}
	<div class="wk-mp-block">
		{hook h="displayMpMenu"}
		<div class="wk-mp-content">
			<div class="page-title" style="background-color:{$title_bg_color};">
				<span style="color:{$title_text_color};">
					{if isset($id_group)}
						{l s='Edit Attribute' mod='marketplace'}
					{else}
						{l s='Create Attribute' mod='marketplace'}
					{/if}
				</span>
			</div>
			<div class="wk-mp-right-column">
				<form action="{if isset($id_group)}{$link->getModuleLink('marketplace', 'createattribute', ['id_group' => $id_group])}{else}{$link->getModuleLink('marketplace', 'createattribute')}{/if}" method="POST">
					<input type="hidden" name="default_lang" id="default_lang" value="{$current_lang.id_lang}">
					{block name='change-product-language'}
						{include file='module:marketplace/views/templates/front/product/_partials/change-product-language.tpl'}
					{/block}
					<div class="form-group">
						<label for="attrib_name" class="control-label required">
							{l s='Attribute Name' mod='marketplace'}
							{block name='mp-form-fields-flag'}
								{include file='module:marketplace/views/templates/front/_partials/mp-form-fields-flag.tpl'}
							{/block}
						</label>
						{foreach from=$languages item=language}
							{assign var="attrib_name" value="attrib_name_`$language.id_lang`"}
							<input type="text"
							id="attrib_name_{$language.id_lang}"
							name="attrib_name_{$language.id_lang}"
							class="form-control wk_text_field_all wk_text_field_{$language.id_lang} {if $current_lang.id_lang == $language.id_lang}current_attrib_name{/if}"
							data-lang-name="{$language.name}"
							value="{if isset($smarty.post.$attrib_name)}{$smarty.post.$attrib_name}{elseif isset($id_group)}{$attr_name[{$language.id_lang}]}{/if}"
							{if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}
							maxlength="128" />
						{/foreach}
					</div>
					<div class="form-group">
						<label for="attrib_public_name" class="control-label required">
							{l s='Attribute Public name' mod='marketplace'}
							{block name='mp-form-fields-flag'}
								{include file='module:marketplace/views/templates/front/_partials/mp-form-fields-flag.tpl'}
							{/block}
						</label>
						{foreach from=$languages item=language}
							{assign var="attrib_public_name" value="attrib_public_name_`$language.id_lang`"}
							<input type="text"
							id="attrib_public_name_{$language.id_lang}"
							name="attrib_public_name_{$language.id_lang}"
							class="form-control wk_text_field_all wk_text_field_{$language.id_lang} {if $current_lang.id_lang == $language.id_lang}current_public_name{/if}"
							data-lang-name="{$language.name}"
							value="{if isset($smarty.post.$attrib_public_name)}{$smarty.post.$attrib_public_name}{elseif isset($id_group)}{$attr_public_name[{$language.id_lang}]}{/if}"
							{if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}
							maxlength="64" />
						{/foreach}
					</div>
					<div class="form-group">
						<label for="attrib_type" class="control-label required">
							{l s='Attribute type' mod='marketplace'}
						</label>
						<div class="row">
							<div class="col-md-4">
								<select name="attrib_type" class="form-control" required>
									<option value="select" {if isset($id_group)}{if ($group_type == 'select')}Selected="Selected"{/if}{/if}>{l s='Drop-down list' mod='marketplace'}</option>
									<option value="radio" {if isset($id_group)}{if ($group_type == 'radio')}Selected="Selected"{/if}{/if}>{l s='Radio buttons' mod='marketplace'}</option>
									<option value="color" {if isset($id_group)}{if ($group_type == 'color')}Selected="Selected"{/if}{/if}>{l s='Color or texture' mod='marketplace'}</option>
								</select>
							</div>
						</div>
					</div>
					{block name='mp-form-fields-notification'}
						{include file='module:marketplace/views/templates/front/_partials/mp-form-fields-notification.tpl'}
					{/block}
					<div class="form-group row">
						<div class="col-xs-4 col-sm-4 col-md-6">
							<a href="{$link->getModuleLink('marketplace', 'productattribute')}" class="btn wk_btn_cancel wk_btn_extra">
								{l s='Cancel' mod='marketplace'}
							</a>
						</div>
						<div class="col-xs-8 col-sm-8 col-md-6 wk_text_right" data-action="{l s='Save' mod='marketplace'}">
							<button type="submit" id="SubmitAttribute" name="SubmitAttribute" class="btn btn-success wk_btn_extra form-control-submit">
								<span>{l s='Save' mod='marketplace'}</span>
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
{/if}
{/block}