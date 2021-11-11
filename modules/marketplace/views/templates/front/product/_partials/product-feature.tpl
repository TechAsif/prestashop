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

{if isset($available_features) && !empty($available_features)}
<div class="content" id="features-content">
	<div id="wk_display_none" class="alert alert-danger wk_display_none"></div>
	<input type="hidden" name="wk_feature_row" id="wk_feature_row" value="{if isset($productfeature)}{count($productfeature)}{else}0{/if}">
	{if isset($productfeature) && !empty($productfeature)}
		{foreach $productfeature as $key => $selectedfeature}
		<div class="row content wk_mp_feature_row" id="wk_mp_feature_row_field">
			<div class="col-lg-12 col-xl-4">
				<fieldset class="form-group">
					<label class="form-control-label">{l s='Feature' mod='marketplace'}</label>
					<select data-id-feature="{$key+1}" class="form-control form-control-select wk_mp_feature" name="wk_mp_feature_{$key+1}" >
						<option value="0">{l s='Choose a feature' mod='marketplace'}</option>
						{foreach $available_features as $feature}
							<option value="{$feature.id_feature}" {if $selectedfeature.ps_id_feature == $feature.id_feature}selected="selected"{/if}>{$feature.name}</option>
						{/foreach}
					</select>
				</fieldset>
			</div>
			<div class="col-lg-12 col-xl-4">
				<fieldset class="form-group">
					<label class="form-control-label">{l s='Pre-defined value' mod='marketplace'}</label>
					<select data-id-feature-val="{$key+1|escape:'html':'UTF-8'}" class="form-control form-control-select wk_mp_feature_val" name="wk_mp_feature_val_{$key+1|escape:'html':'UTF-8'}" style="width:260px;">
						<option {if !isset($selectedfeature.ps_id_feature_value)}selected="selected"{/if} value="0">{l s='Choose a value' mod='marketplace'}</option>
						{foreach $selectedfeature.field_value_option as $ps_feature_value}
							<option {if $ps_feature_value.id_feature_value == $selectedfeature.ps_id_feature_value}selected="selected"{/if} value="{$ps_feature_value.id_feature_value|escape:'html':'UTF-8'}">{$ps_feature_value.value|escape:'html':'UTF-8'}</option>
						{/foreach}
					</select>
				</fieldset>
			</div>
			<div class="col-lg-12 col-xl-3">
				<fieldset class="form-group">
					<label class="form-control-label">
						{l s='OR Customized value' mod='marketplace'}

						{if $allow_multilang && $total_languages > 1}
							<img class="all_lang_icon" data-lang-id="{$current_lang.id_lang}" src="{$ps_img_dir}{$current_lang.id_lang}.jpg">
						{/if}
					</label>
					<div class="translationsFields translation-label-en">
						{foreach from=$languages item=language}
							{assign var="wk_mp_feature_custom" value="product_name_`$language.id_lang`"}
							<input type="text"
							name="wk_mp_feature_custom_{$language.id_lang}_{$key+1}"
							value="{if isset($smarty.post.$wk_mp_feature_custom)}{$smarty.post.$wk_mp_feature_custom}{else if isset($selectedfeature.mp_field_value.{$language.id_lang}.value)}{$selectedfeature.mp_field_value.{$language.id_lang}.value}{/if}"
							class="form-control wkmp_feature_custom wk_mp_feature_custom_{$language.id_lang} custom_value_{$key+1}"
							data-lang-name="{$language.name}"
							{if $current_lang.id_lang != $language.id_lang} style="display: none;"{/if}/>
						{/foreach}
					</div>
				</fieldset>
			</div>
			{if !isset($editPermissionNotAllow) && $permissionData.featuresPermission.delete}
				<div class="col-lg-1 col-xl-1 wk_mp_feature_delete_row" data-feature-delete="1">
					<fieldset class="form-group">
						<label class="form-control-label">&nbsp;</label>
						<a title="{l s='Delete' mod='marketplace'}" href="javascript:void(0)" class="btn btn-invisible btn-block wkmp_feature_delete" type="button" style="padding: 0px;">
							<i class="material-icons">&#xE872;</i>
						</a>
					</fieldset>
				</div>
			{/if}
		</div>
		{/foreach}
	{/if}

</div>
<div class="row" id="wk_mp_feature_more">
	<div class="col-md-4">
		{if isset($controller) && $controller == 'admin'}
			<button id="add_feature_button" class="btn btn-primary sensitive add" type="button">
				<i class="icon-plus"></i>
				{l s='Add Feature' mod='marketplace'}
			</button>
		{else if !isset($editPermissionNotAllow) && $permissionData.featuresPermission.edit}
			<button id="add_feature_button" class="btn btn-primary-outline sensitive add" type="button">
				<i class="material-icons">&#xE145;</i>
				{l s='Add Feature' mod='marketplace'}
			</button>
		{/if}
		<img class="wk-feature-loader wk_display_none" src="{$module_dir}marketplace/views/img/loader.gif" width="25" />
	</div>
</div>
{else}
	<div class="alert alert-warning">{l s='No Features Available' mod='marketplace'}</div>
{/if}
{if isset($controller) && $controller == 'admin'}
<style type="text/css">
	.col-xl-4 {
		width: 25% !important;
		margin-right: 50px;
	}
	.col-xl-3 {
		width: 25% !important;
	}
	#features-content h2 {
		color: #363a41;
    	font-weight: 600;
    	font-size: 1rem;
    	margin-bottom: 0.9375rem;
    	font-family: Open Sans,sans-serif;
    	line-height: 1.1;
    	margin-top: 0;
	}
</style>
{/if}