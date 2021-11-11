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
<script type="text/javascript" src="{$smarty.const._MODULE_DIR_}marketplace/views/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="{$smarty.const._MODULE_DIR_}marketplace/views/js/tinymce/tinymce_wk_setup.js"></script>
<input type="hidden" name="token" id="wk-static-token" value="{$static_token}">
{if isset($is_seller)}
	{if !$logged}
		<div class="alert alert-danger">
			{l s='You have to login to make a seller request.' mod='marketplace'}
		</div>
	{else}
		{if $is_seller == 0}
			<div class="alert alert-info">
				{l s='Your request has been sent to admin. Please wait till the approval from admin' mod='marketplace'}
			</div>
		{else}
			<div class="alert alert-info">
				{l s='Your request has been approved by admin. ' mod='marketplace'}
				<a href="{$link->getModuleLink('marketplace','addproduct')}">
					<button class="btn btn-primary btn-sm">
						{l s='Add Your First Product' mod='marketplace'}
					</button>
				</a>
			</div>
		{/if}
	{/if}
{else}
	<div class="wk-mp-block" style="border:1px solid #d5d5d5;">
		<div class="page-title" style="background-color:{$title_bg_color};">
			<span style="color:{$title_text_color};">{l s='Seller Request' mod='marketplace'}</span>
		</div>
		<form action="{$link->getModuleLink('marketplace', 'sellerrequest')}" method="post" id="wk_mp_seller_form" enctype="multipart/form-data">
			<div class="wk-mp-right-column">
				<div class="alert alert-danger wk_display_none" id="wk_mp_form_error"></div>
				<fieldset>
					<input type="hidden" name="current_lang_id" id="current_lang_id" value="{$current_lang.id_lang}">
					{if $total_languages > 1}
						<div class="form-group">
							<label for="default_lang" class="control-label required">
								{l s='Default Language' mod='marketplace'}
							</label>
							<div class="row">
								<div class="col-lg-3">
								  	<select class="form-control form-control-select" name="default_lang" id="default_lang">
								  		{foreach from=$languages item=language}
											<option data-lang-name="{$language.name}"
											value="{$language.id_lang}"
											{if isset($smarty.post.default_lang)}
												{if $smarty.post.default_lang == $language.id_lang}Selected="Selected"
												{/if}
											{else}
												{if $current_lang.id_lang == $language.id_lang}Selected="Selected"
												{/if}
											{/if}>
												{$language.name}
											</option>
										{/foreach}
								  	</select>
							  	</div>
						  	</div>
						</div>
					{else}
						<input type="hidden" name="default_lang" value="{$context_language}" />
					{/if}
					<div class="form-group seller_shop_name_uniq">
						<label for="shop_name_unique" class="control-label required">
							{l s='Shop Unique Name' mod='marketplace'}
							<div class="wk_tooltip">
								<span class="wk_tooltiptext">{l s='This name will be used in your shop URL' mod='marketplace'}</span>
							</div>
						</label>
						<img style="display: none;" width="25" src="{$modules_dir}marketplace/views/img/loading-small.gif" class="seller-loading-img"/>
						<input class="form-control"
							type="text"
							value="{if isset($smarty.post.shop_name_unique)}{$smarty.post.shop_name_unique}{/if}"
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
							{if $current_lang.id_lang == $language.id_lang}seller_default_shop{/if}"
							style="{if $current_lang.id_lang != $language.id_lang}display:none;{/if}"
							type="text"
							value="{if isset($smarty.post.$shop_name)}{$smarty.post.$shop_name}{/if}"
							id="shop_name_{$language.id_lang}"
							name="shop_name_{$language.id_lang}"
							data-lang-name="{$language.name}" />
						{/foreach}
						<span class="wk-msg-shopname"></span>
					</div>
					<div class="form-group row">
						<div class="col-md-6">
							<label for="seller_firstname" class="control-label required">
								{l s='First Name' mod='marketplace'}
							</label>
							<input class="form-control"
							type="text"
							value="{if isset($smarty.post.seller_firstname)}{$smarty.post.seller_firstname}{else}{$customer_firstname}{/if}"
							name="seller_firstname"
							id="seller_firstname" />
						</div>
						<div class="col-md-6">
							<label for="seller_lastname" class="control-label required">
								{l s='Last Name' mod='marketplace'}
							</label>
							<input class="form-control"
							type="text"
							value="{if isset($smarty.post.seller_lastname)}{$smarty.post.seller_lastname}{else}{$customer_lastname}{/if}"
							name="seller_lastname"
							id="seller_lastname" />
						</div>
					</div>
					<div class="form-group row">
						<div class="col-md-6">
							<label for="business_email" class="control-label required">
								{l s='Business Email' mod='marketplace'}
							</label>
							<input class="form-control"
							type="email"
							value="{if isset($smarty.post.business_email)}{$smarty.post.business_email}{else}{$customer_email}{/if}"
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
							value="{if isset($smarty.post.phone)}{$smarty.post.phone}{/if}"
							name="wk_phone"
							id="phone"
							maxlength="{$max_phone_digit}" />
						</div>
					</div>
					{if $seller_country_need}
						<div class="form-group row">
							<div class="col-md-6" id="seller_zipcode">
								<label for="postcode" class="control-label required">
									{l s='Zip/Postal Code' mod='marketplace'}
								</label>
								<input class="form-control"
								type="text"
								value="{if isset($smarty.post.postcode)}{$smarty.post.postcode|escape:'htmlall':'UTF-8'}{/if}"
								name="postcode"
								id="postcode" />
							</div>
							<div class="col-md-6">
								<label for="city" class="control-label required">
									{l s='City' mod='marketplace'}
								</label>
								<input class="form-control"
								type="text"
								value="{if isset($smarty.post.city)}{$smarty.post.city|escape:'htmlall':'UTF-8'}{/if}"
								name="city"
								id="city"
								maxlength="64" />
							</div>
						</div>
						{if isset($country)}
							<div class="form-group row">
								<div class="col-md-6">
									<label for="id_country" class="control-label required">
										{l s='Country' mod='marketplace'}
									</label>
									<select name="id_country" id="id_country" class="form-control form-control-select">
										<option value="">{l s='Select Country' mod='marketplace'}</option>
										{foreach $country as $countrydetail}
											<option value="{$countrydetail.id_country}">
												{$countrydetail.name}
											</option>
										{/foreach}
									</select>
								</div>
								<div id="wk_seller_state_div" class="col-md-6 wk_display_none">
									<label for="id_state" class="control-label required">
										{l s='State' mod='marketplace'}
									</label>
									<select name="id_state" id="id_state" class="form-control form-control-select">
										<option value="">{l s='Select State' mod='marketplace'}</option>
									</select>
									<input type="hidden" name="state_available" id="state_available" value="0" />
								</div>
							</div>
						{/if}
					{/if}
					{if $tax_identification_number}
						<div class="form-group row">
							<div class="col-md-6" id="seller_tax_identification_number">
								<label for="tax_identification_number" class="control-label">
									{l s='Tax Identification Number/VAT' mod='marketplace'}
								</label>
								<input class="form-control"
								type="text"
								value="{if isset($smarty.post.tax_identification_number)}{$smarty.post.tax_identification_number|escape:'htmlall':'UTF-8'}{/if}"
								name="tax_identification_number"
								id="tax_identification_number"
								maxlength="64" />
							</div>
						</div>
					{/if}
					{if $terms_and_condition_active}
						<div class="form-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" name="terms_and_conditions" id="terms_and_conditions" />
									<span>
										{l s='I agree to the' mod='marketplace'}
										{if isset($linkCmsPageContent)}
											<a href="{$linkCmsPageContent}" class="wk_terms_link">
												{l s='terms and condition' mod='marketplace'}
											</a>
										{else}
											{l s='terms and condition' mod='marketplace'}
										{/if}
										{l s='and will adhere to them unconditionally.' mod='marketplace'}
									</span>
								</label>
							</div>
						</div>
						{if isset($linkCmsPageContent)}
							<div class="modal fade" id="wk_terms_condtion_div" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
								<div class="modal-dialog" role="document">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
										</div>
										<div id="wk_terms_condtion_content" class="modal-body"></div>
									</div>
								</div>
							</div>
						{/if}
					{/if}

					{hook h="displayMpSellerRequestFooter"}
					{hook h='displayGDPRConsent' mod='psgdpr' id_module=$id_module}

					{block name='mp-form-fields-notification'}
						{include file='module:marketplace/views/templates/front/_partials/mp-form-fields-notification.tpl'}
					{/block}
				</fieldset>
			</div>
			<div class="wk-mp-right-column wk_border_top_none">
				<div class="form-group row">
					<div class="col-xs-6 col-sm-6 col-md-6">
						<a href="{$myaccount}" class="btn wk_btn_cancel wk_btn_extra">
							{l s='Cancel' mod='marketplace'}
						</a>
					</div>
					<div class="col-xs-6 col-sm-6 col-md-6 wk_text_right" id="wk-seller-submit" data-action="{l s='Register' mod='marketplace'}">
						<img class="wk_product_loader" src="{$modules_dir}marketplace/views/img/loader.gif" width="25" />
						<button type="submit" id="sellerRequest" name="sellerRequest" class="btn btn-success wk_btn_extra form-control-submit">
							<span>{l s='Register' mod='marketplace'}</span>
						</button>
					</div>
				</div>
			</div>
		</form>
	</div>
{/if}
{/block}