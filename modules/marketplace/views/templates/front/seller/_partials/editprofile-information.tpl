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

{if $total_languages > 1}
<div class="form-group">
	<label for="default_lang" class="control-label required">
		{l s='Default Language' mod='marketplace'}
	</label>
	<div class="row">
		<div class="col-md-4">
			<select class="form-control form-control-select" name="default_lang" id="default_lang">
		  		{foreach from=$languages item=language}
		  			{if $allow_multilang}
		  				<option data-lang-name="{$language.name}"
						value="{$language.id_lang}"
						{if $current_lang.id_lang == $language.id_lang}Selected="Selected" {/if}>
							{$language.name}
						</option>
					{else}
			  			{if $mp_seller_info.default_lang == $language.id_lang}
							<option data-lang-name="{$language.name}" value="{$language.id_lang}">{$language.name}</option>
						{/if}
					{/if}
				{/foreach}
		  	</select>
		  	{if !$allow_multilang}
		  		<span class="wk_formfield_comment">{l s='You can\'t change default language.' mod='marketplace'}</span>
		  	{/if}
		</div>
	</div>
</div>
{else}
	<input type="hidden" name="default_lang" value="{$mp_seller_info.default_lang}" />
{/if}
<div class="form-group row">
	<div class="col-md-6">
		<label for="seller_firstname" class="control-label required">
			{l s='First Name' mod='marketplace'}
		</label>
		<input class="form-control"
		type="text"
		value="{if isset($smarty.post.seller_firstname)}{$smarty.post.seller_firstname}{else}{$mp_seller_info.seller_firstname}{/if}"
		name="seller_firstname"
		id="seller_firstname" />
	</div>
	<div class="col-md-6">
		<label for="seller_lastname" class="control-label required">
			{l s='Last Name' mod='marketplace'}
		</label>
		<input class="form-control"
		type="text"
		value="{if isset($smarty.post.seller_lastname)}{$smarty.post.seller_lastname}{else}{$mp_seller_info.seller_lastname}{/if}"
		name="seller_lastname"
		id="seller_lastname" />
	</div>
</div>
<div class="form-group seller_shop_name_uniq">
	<label for="shop_name_unique" class="control-label required">
		{l s='Shop Unique Name' mod='marketplace'}
		<div class="wk_tooltip">
			<span class="wk_tooltiptext">{l s='This name will be used in your shop URL.' mod='marketplace'}</span>
		</div>
	</label>
	<input class="form-control"
		type="text"
		value="{if isset($smarty.post.shop_name_unique)}{$smarty.post.shop_name_unique}{else}{$mp_seller_info.shop_name_unique}{/if}"
		id="shop_name_unique"
		name="shop_name_unique"
		onblur="onblurCheckUniqueshop();"
		autocomplete="off" />
	<span class="wk-msg-shopnameunique"></span>
</div>
<div class="form-group">
	<label for="shop_name" class="control-label required">
		{l s='Shop Name' mod='marketplace'}
		{block name='mp-form-fields-flag'}
			{include file='module:marketplace/views/templates/front/_partials/mp-form-fields-flag.tpl'}
		{/block}
	</label>
	{foreach from=$languages item=language}
		{assign var="shop_name" value="shop_name_`$language.id_lang`"}
		<input class="form-control shop_name_all wk_text_field_all wk_text_field_{$language.id_lang}
		{if $current_lang.id_lang == $language.id_lang}seller_default_shop{/if}
		{if $current_lang.id_lang != $language.id_lang}wk_display_none{/if}"
		type="text"
		value="{if isset($smarty.post.$shop_name)}{$smarty.post.$shop_name}{else}{$mp_seller_info.shop_name[{$language.id_lang}]}{/if}"
		id="shop_name_{$language.id_lang}"
		name="shop_name_{$language.id_lang}"
		data-lang-name="{$language.name}" />
	{/foreach}
	<span class="wk-msg-shopname"></span>
</div>
<div class="form-group row">
	<div class="col-md-6">
		<label for="business_email" class="control-label required">
			{l s='Business Email' mod='marketplace'}
		</label>
		<input class="form-control"
		type="email"
		value="{if isset($smarty.post.business_email)}{$smarty.post.business_email}{else}{$mp_seller_info.business_email}{/if}"
		name="business_email"
		id="business_email"
		onblur="onblurCheckUniqueSellerEmail();" />
		<span class="wk-msg-selleremail"></span>
	</div>
	<div class="col-md-6">
		<label for="phone" class="control-label required">
			{l s='Phone' mod='marketplace'}
		</label>
		<input class="form-control"
		type="text"
		value="{if isset($smarty.post.phone)}{$smarty.post.phone}{else}{$mp_seller_info.phone}{/if}"
		name="wk_phone"
		id="phone"
		maxlength="{$max_phone_digit}" />
	</div>
</div>
{if Configuration::get('WK_MP_SELLER_FAX') || Configuration::get('WK_MP_SELLER_TAX_IDENTIFICATION_NUMBER')}
	<div class="form-group row">
		{if Configuration::get('WK_MP_SELLER_FAX')}
			<div class="col-md-6">
				<label for="fax" class="control-label">{l s='Fax' mod='marketplace'}</label>
				<input class="form-control"
				type="text"
				value="{if isset($smarty.post.fax)}{$smarty.post.fax}{else}{$mp_seller_info.fax}{/if}"
				name="fax"
				id="fax" />
			</div>
		{/if}
		{if Configuration::get('WK_MP_SELLER_TAX_IDENTIFICATION_NUMBER')}
			<div class="col-md-6">
				<label for="fax" class="control-label">{l s='Tax Identification Number' mod='marketplace'}</label>
				<input class="form-control"
				type="text"
				value="{if isset($smarty.post.tax_identification_number)}{$smarty.post.tax_identification_number}{else}{$mp_seller_info.tax_identification_number}{/if}"
				name="tax_identification_number"
				id="tax_identification_number" />
			</div>
		{/if}
	</div>
{/if}
<div class="form-group">
	<label for="about_shop" class="control-label">
		{l s='About Shop' mod='marketplace'}

		{block name='mp-form-fields-flag'}
			{include file='module:marketplace/views/templates/front/_partials/mp-form-fields-flag.tpl'}
		{/block}
	</label>
	{foreach from=$languages item=language}
		{assign var="about_shop" value="about_shop_`$language.id_lang`"}
		<div id="about_business_div_{$language.id_lang}" class="wk_text_field_all wk_text_field_{$language.id_lang} {if $current_lang.id_lang != $language.id_lang}wk_display_none{/if}">
			<textarea
			name="about_shop_{$language.id_lang}"
			id="about_business_{$language.id_lang}" cols="2" rows="3"
			class="about_business wk_tinymce form-control">{if isset($smarty.post.$about_shop)}{$smarty.post.$about_shop}{else}{$mp_seller_info.about_shop[{$language.id_lang}]}{/if}</textarea>
		</div>
	{/foreach}
</div>
{hook h="displayMpEditProfileInformationBottom"}