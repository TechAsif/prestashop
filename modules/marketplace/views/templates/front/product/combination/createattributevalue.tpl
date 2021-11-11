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
					{if isset($id_attribute)}
						{l s='Edit Value' mod='marketplace'}
					{else}
						{l s='Create New Value' mod='marketplace'}
					{/if}
				</span>
			</div>
			<div class="wk-mp-right-column">
				<form action="{if isset($id_attribute)}{$link->getModuleLink('marketplace', 'createattributevalue', ['id_attribute' => $id_attribute])}{else}{$link->getModuleLink('marketplace', 'createattributevalue')}{/if}" method="post" enctype="multipart/form-data">
					{if isset($id_group)}
						<input type="hidden" name="id_group" id="id_group" value="{$id_group}">
					{/if}
					<input type="hidden" name="default_lang" id="default_lang" value="{$current_lang.id_lang}">
					{block name='change-product-language'}
						{include file='module:marketplace/views/templates/front/product/_partials/change-product-language.tpl'}
					{/block}
					<div class="form-group">
						<label for="attrib_group" class="control-label required">
							{l s='Attribute Type' mod='marketplace'}
						</label>
						<div class="row">
							<div class="col-md-4">
								<select name="attrib_group" id="attrib_group" class="form-control">
									{if isset($id_attribute)}
										<option value="{$attrib_grp['id']}">{$attrib_grp['name']}</option>
									{else}
										{foreach $attrib_set as $attrib_set_each}
											<option value="{$attrib_set_each['id']}" {if isset($id_group)}{if $id_group == $attrib_set_each['id']}selected{/if}{/if}>{$attrib_set_each['name']}</option>
										{/foreach}
									{/if}
								</select>
							</div>
							<div class="pull-left" style="padding-top:2px;">
								<span class="loading_img"></span>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="attrib_value" class="control-label required">
							{l s='Attribute Value' mod='marketplace'}
							{block name='mp-form-fields-flag'}
								{include file='module:marketplace/views/templates/front/_partials/mp-form-fields-flag.tpl'}
							{/block}
						</label>
						{foreach from=$languages item=language}
							{assign var="attrib_value" value="attrib_value_`$language.id_lang`"}
							<input type="text"
							id="attrib_value_{$language.id_lang}"
							name="attrib_value_{$language.id_lang}"
							class="form-control wk_text_field_all wk_text_field_{$language.id_lang} {if $current_lang.id_lang == $language.id_lang}current_attrib_value{/if}"
							data-lang-name="{$language.name}"
							value="{if isset($smarty.post.$attrib_value)}{$smarty.post.$attrib_value}{elseif isset($id_attribute)}{$attrib_valname[{$language.id_lang}]}{/if}"
							{if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}
							maxlength="128" />
						{/foreach}
					</div>
					<div id="attrib_value_color_div" {if !isset($attrib_color)}style="display:none;"{/if}>
						<div class="form-group">
							<label for="attrib_value_color" class="control-label">
								{l s='Color' mod='marketplace'}
							</label>
							<div class="row">
								<div class="col-md-4">
									<input type="color" value="{if isset($attrib_color)}{$attrib_color}{/if}" name="attrib_value_color" id="attrib_value_color" class="form-control" />
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="color_img" class="control-label">
								{l s='Texture' mod='marketplace'}
							</label>
							<div class="row">
								<div class="col-md-5">
									<input type="file" name="color_img" class="color_img form-control">
								</div>
							</div>
						</div>
					</div>
					{if isset($attrib_color)}
						<div class="form-group">
							<label class="control-label">
								{l s='Current texture' mod='marketplace'}
							</label>
							{if isset($imageTextureExists) && $imageTextureExists}
								<div class="row">
									<div class="col-md-1">
										<img src="{$img_col_dir}{$id_attribute|intval}.jpg" alt="{l s='Texture_img' mod='marketplace'}" class="img-thumbnail" />
									</div>
								</div>
							{else}
								<p class="form-control-static">{l s='No Texture Avalaible' mod='marketplace'}</p>
							{/if}
						</div>
					{/if}
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
							<button type="submit" id="SubmitAttributeValue" name="SubmitAttributeValue" class="btn btn-success wk_btn_extra form-control-submit">
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