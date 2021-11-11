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

<div class="row">
	{if isset($backendController)}
		<h2 class="col-lg-6 col-lg-offset-3">{l s='Availability Preferences' mod='marketplace'}</h2>
	{else}
		<h4 class="col-md-12">{l s='Availability Preferences' mod='marketplace'}</h4>
	{/if}
</div>

<div class="form-group {if isset($backendController)}row{/if}">
	<label for="out_of_stock" class="control-label {if isset($backendController)}col-lg-3{/if}">
		{l s='Behavior when out of stock' mod='marketplace'}
	</label>
	<div class="row">
		<div class="{if isset($backendController)}col-lg-4{else}col-md-6{/if}">
			<div class="radio">
				<label>
					<input type="radio" value="0" name="out_of_stock" {if isset($product_info.out_of_stock) && $product_info.out_of_stock == '0'}checked{/if}>
					{l s='Deny orders' mod='marketplace'}
				</label>
			</div>
			<div class="radio">
				<label>
					<input type="radio" value="1" name="out_of_stock" {if isset($product_info.out_of_stock) && $product_info.out_of_stock == '1'}checked{/if}>
					{l s='Allow orders' mod='marketplace'}
				</label>
			</div>
			<div class="radio">
				<label>
					<input type="radio" value="2" name="out_of_stock" {if isset($product_info.out_of_stock)}{if $product_info.out_of_stock == '2'}checked{/if}{else}checked{/if}>
					{l s='Use default behavior (Deny orders)' mod='marketplace'}
				</label>
			</div>
		</div>
	</div>
</div>

<div class="form-group {if isset($backendController)}row{/if}">
	<label for="available_now" class="control-label {if isset($backendController)}col-lg-3{/if}">
		{l s='Label when in stock' mod='marketplace'}

		{if $allow_multilang && $total_languages > 1}
			<img class="all_lang_icon" data-lang-id="{$current_lang.id_lang}" src="{$ps_img_dir}{$current_lang.id_lang}.jpg">
		{/if}
	</label>
	<div class="{if isset($backendController)}col-lg-6{/if}">
		{foreach from=$languages item=language}
			{assign var="available_now" value="available_now_`$language.id_lang`"}
			<input type="text"
			id="available_now_{$language.id_lang}"
			name="available_now_{$language.id_lang}"
			value="{if isset($smarty.post.$available_now)}{$smarty.post.$available_now}{else if isset($product_info.available_now)}{$product_info.available_now[{$language.id_lang}]}{/if}"
			class="form-control wk_text_field_all wk_text_field_{$language.id_lang}"
			data-lang-name="{$language.name}" maxlength="255"
			{if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if} />
		{/foreach}
	</div>
</div>

<div class="form-group {if isset($backendController)}row{/if}">
	<label for="available_later" class="control-label {if isset($backendController)}col-lg-3{/if}">
		{l s='Label when out of stock (and back order allowed)' mod='marketplace'}

		{if $allow_multilang && $total_languages > 1}
			<img class="all_lang_icon" data-lang-id="{$current_lang.id_lang}" src="{$ps_img_dir}{$current_lang.id_lang}.jpg">
		{/if}
	</label>
	<div class="{if isset($backendController)}col-lg-6{/if}">
		{foreach from=$languages item=language}
			{assign var="available_later" value="available_later_`$language.id_lang`"}
			<input type="text"
			id="available_later_{$language.id_lang}"
			name="available_later_{$language.id_lang}"
			value="{if isset($smarty.post.$available_later)}{$smarty.post.$available_later}{else if isset($product_info.available_later)}{$product_info.available_later[{$language.id_lang}]}{/if}"
			class="form-control wk_text_field_all wk_text_field_{$language.id_lang}"
			data-lang-name="{$language.name}" maxlength="255"
			{if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if} />
		{/foreach}
	</div>
</div>

<!-- If Product combinations are not exist -->
{if !isset($hasAttribute)}
	<div class="form-group">
		<label for="available_date" class="control-label {if isset($backendController)}col-lg-3{/if}">
			{l s='Availability date' mod='marketplace'}
			<div class="wk_tooltip">
				<span class="wk_tooltiptext">{l s='If this product is out of stock, you can indicate when the product will be available again.' mod='marketplace'}</span>
			</div>
		</label>
		<div class="row">
			<div class="{if isset($backendController)}col-lg-4{else}col-md-6{/if}">
				<div class="input-group">
					<input type="text"
					name="available_date"
					id="available_date"
					value="{if isset($smarty.post.available_date)}{$smarty.post.available_date}{else if isset($product_info.available_date)}{$product_info.available_date}{/if}"
					class="form-control"
					placeholder="YYYY-MM-DD"
					autocomplete="off" />
					<span class="input-group-addon wk_calender_icon">
						<i class="material-icons">&#xE916;</i>
					</span>
				</div>
			</div>
		</div>
	</div>
{/if}