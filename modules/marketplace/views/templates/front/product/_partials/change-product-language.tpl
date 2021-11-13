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

{if $allow_multilang && $total_languages > 1}
	<div class="form-group row">
		<div class="col-md-7">
			<label class="control-label">{l s='Choose Language' mod='marketplace'}</label>
			<input type="hidden" name="choosedLangId" id="choosedLangId" value="{$current_lang.id_lang}">
			<div class="row">
				<div class="col-md-7">
					<select class="form-control" name="seller_lang_btn" id="seller_lang_btn">
						{foreach from=$languages item=language}
							<option data-langname="{$language.name}" value="{$language.id_lang}" {if ($current_lang.id_lang == $language.id_lang)}selected="selected"{/if}>{$language.name}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<p class="wk_formfield_comment">{l s='Change language for updating information in multiple language.' mod='marketplace'}</p>
		</div>
	</div>
{/if}