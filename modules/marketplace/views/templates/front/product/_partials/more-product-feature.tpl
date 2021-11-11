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

<div class="row wk_mp_feature_row" id="wk_mp_feature_row_field">
	<div class="col-lg-12 col-xl-4">
		<fieldset class="form-group">
			<label class="form-control-label">{l s='Feature' mod='marketplace'}</label>
			<select data-id-feature="{$fieldrow+1}" class="form-control form-control-select wk_mp_feature" id="wk_mp_feature_{$fieldrow+1}" name="wk_mp_feature_{$fieldrow+1}" >
				<option value="">{l s='Choose a feature' mod='marketplace'}</option>
				{foreach $available_features as $feature}
					<option value="{$feature.id_feature}">{$feature.name}</option>
				{/foreach}
			</select>
		</fieldset>
	</div>
	<div class="col-lg-12 col-xl-4">
		<fieldset class="form-group">
			<label class="form-control-label">{l s='Pre-defined value' mod='marketplace'}</label>
			<select data-id-feature-val="{$fieldrow+1}" class="form-control form-control-select wk_mp_feature_val" id="wk_mp_feature_val_{$fieldrow+1}" name="wk_mp_feature_val_{$fieldrow+1}">
				<option selected="selected" value="0" disabled="disabled">
					{l s='Choose a value' mod='marketplace'}
				</option>
			</select>
		</fieldset>
	</div>
	<div class="col-lg-12 col-xl-3">
		<fieldset class="form-group">
			<label class="form-control-label">
				{l s='OR Customized value' mod='marketplace'}
				{if $allow_multilang && $total_languages > 1}
					<img class="all_lang_icon" data-lang-id="{$choosedLangId}" src="{$ps_img_dir}{$choosedLangId}.jpg">
				{/if}
			</label>
			<div class="translationsFields translation-label-en">
				{foreach from=$languages item=language}
					{assign var="wk_mp_feature_custom" value="product_name_`$language.id_lang`"}
					<input type="text"
					name="wk_mp_feature_custom_{$language.id_lang}_{$fieldrow+1}"
					value="{if isset($smarty.post.$wk_mp_feature_custom)}{$smarty.post.$wk_mp_feature_custom}{/if}"
					class="form-control wkmp_feature_custom wk_mp_feature_custom_{$language.id_lang} custom_value_{$fieldrow+1}"
					data-lang-name="{$language.name}"
					{if $current_lang.id_lang != $language.id_lang} style="display: none;"{/if}/>
				{/foreach}
			</div>
		</fieldset>
	</div>
	{if $permissionData.featuresPermission.delete}
		<div class="col-lg-1 col-xl-1 wk_mp_feature_delete_row" data-feature-delete="{$fieldrow+1}">
			<fieldset class="form-group">
				<label class="form-control-label">&nbsp;</label>
				<a title="{l s='Delete' mod='marketplace'}" href="javascript:void(0)" class="btn btn-invisible btn-block wkmp_feature_delete" type="button" style="padding: 0px;">
					<i class="material-icons">&#xE872;</i>
				</a>
			</fieldset>
		</div>
	{/if}
</div>