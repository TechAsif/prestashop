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

<div class="form-group">
	<label for="meta_title" class="control-label">
		{l s='Meta Title' mod='marketplace'}

		<div class="wk_tooltip">
			<span class="wk_tooltiptext">{l s='Public title for the product\'s page, and for search engines. Leave blank to use the product name. The number of remaining characters is displayed to the left of the field.' mod='marketplace'}</span>
		</div>
		{if $allow_multilang && $total_languages > 1}
			<img class="all_lang_icon" data-lang-id="{$current_lang.id_lang}" src="{$ps_img_dir}{$current_lang.id_lang}.jpg">
		{/if}
	</label>
	{foreach from=$languages item=language}
		{assign var="meta_title" value="meta_title_`$language.id_lang`"}
		<div id="meta_title_div_{$language.id_lang}" class="wk_text_field_all wk_text_field_{$language.id_lang} {if $current_lang.id_lang != $language.id_lang}wk_display_none{/if}">
			<input type="text"
			name="meta_title_{$language.id_lang}"
			id="meta_title_{$language.id_lang}"
			class="form-control" maxlength="128"
			value="{if isset($smarty.post.$meta_title)}{$smarty.post.$meta_title}{else}{if isset($product_info)}{$product_info.meta_title[{$language.id_lang}]}{/if}{/if}"
			placeholder="{l s='To have a different title from the product name, enter it here.' mod='marketplace'}" >
		</div>
	{/foreach}
</div>
<div class="form-group">
	<label for="meta_description" class="control-label">
		{l s='Meta Description' mod='marketplace'}

		<div class="wk_tooltip">
			<span class="wk_tooltiptext">{l s='This description will appear in search engines. You need a single sentence, shorter than 160 characters (including spaces).' mod='marketplace'}</span>
		</div>
		{if $allow_multilang && $total_languages > 1}
			<img class="all_lang_icon" data-lang-id="{$current_lang.id_lang}" src="{$ps_img_dir}{$current_lang.id_lang}.jpg">
		{/if}
	</label>
	{foreach from=$languages item=language}
		{assign var="meta_description" value="meta_description_`$language.id_lang`"}
		<div id="meta_description_div_{$language.id_lang}" class="wk_text_field_all wk_text_field_{$language.id_lang} {if $current_lang.id_lang != $language.id_lang}wk_display_none{/if}">
			<textarea
			name="meta_description_{$language.id_lang}"
			id="meta_description_{$language.id_lang}"
			class="form-control" cols="2" rows="3" maxlength="255"
			placeholder="{l s='To have a different description than your product summary in search results pages, write it here.' mod='marketplace'}" >{if isset($smarty.post.$meta_description)}{$smarty.post.$meta_description}{else}{if isset($product_info)}{$product_info.meta_description[{$language.id_lang}]}{/if}{/if}</textarea>
		</div>
	{/foreach}
</div>
<div class="form-group">
	<label for="link_rewrite" class="control-label">
		{l s='Friendly URL' mod='marketplace'}

		<div class="wk_tooltip">
			<span class="wk_tooltiptext">{l s='This is the human-readable URL, as generated from the product\'s name. You can change it if you want.' mod='marketplace'}</span>
		</div>
		{if $allow_multilang && $total_languages > 1}
			<img class="all_lang_icon" data-lang-id="{$current_lang.id_lang}" src="{$ps_img_dir}{$current_lang.id_lang}.jpg">
		{/if}
	</label>
	{foreach from=$languages item=language}
		{assign var="link_rewrite" value="link_rewrite_`$language.id_lang`"}
		<div id="link_rewrite_div_{$language.id_lang}" class="wk_text_field_all wk_text_field_{$language.id_lang} {if $current_lang.id_lang != $language.id_lang}wk_display_none{/if}">
			<input type="text"
			name="link_rewrite_{$language.id_lang}"
			id="link_rewrite_{$language.id_lang}"
			class="form-control" maxlength="128"
			value="{if isset($smarty.post.$link_rewrite)}{$smarty.post.$link_rewrite}{else}{if isset($product_info)}{$product_info.link_rewrite[{$language.id_lang}]}{/if}{/if}" >
		</div>
	{/foreach}
</div>