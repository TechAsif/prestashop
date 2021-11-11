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
{if $logged}
	{hook h='displayMpAddProductHeader'}
	<div class="wk-mp-block">
		{hook h="displayMpMenu"}
		<div class="wk-mp-content">
			<div class="page-title" style="background-color:{$title_bg_color};">
				<span style="color:{$title_text_color};">{l s='Add Product' mod='marketplace'}</span>
			</div>
			{hook h="displayAddProductFormTop"}
			<form action="{$link->getModuleLink('marketplace', 'addproduct')}" method="post" id="wk_mp_seller_product_form" enctype="multipart/form-data" accept-charset="UTF-8,ISO-8859-1,UTF-16" name="wk_mp_seller_product_form">
				<div class="wk-mp-right-column">
					<input type="hidden" name="token" id="wk-static-token" value="{$static_token}">
					<input type="hidden" name="default_lang" value="{$default_lang}" id="default_lang">
					<input type="hidden" name="current_lang_id" value="{$current_lang.id_lang}" id="current_lang_id">
					<input type="hidden" name="active_tab" value="{if isset($active_tab)}{$active_tab}{/if}" id="active_tab">
					<input type="hidden" name="mp_product_id" value="" id="mp_product_id">
					{block name='change-product-language'}
						{include file='module:marketplace/views/templates/front/product/_partials/change-product-language.tpl'}
					{/block}
					<div class="alert alert-danger wk_display_none" id="wk_mp_form_error"></div>
					<hr>
					<div class="tabs wk-tabs-panel">
						{block name='product-nav-tabs'}
							{include file='module:marketplace/views/templates/front/product/_partials/product-nav-tabs.tpl'}
						{/block}
						<div class="tab-content" id="tab-content">
							<div class="tab-pane fade in active show" id="wk-information">
								{hook h='displayMpAddProductContentTop'}
								<div class="form-group">
									<label for="product_name" class="control-label required">
										{l s='Product Name' mod='marketplace'}
										{block name='mp-form-fields-flag'}
											{include file='module:marketplace/views/templates/front/_partials/mp-form-fields-flag.tpl'}
										{/block}
									</label>
									{foreach from=$languages item=language}
										{assign var="product_name" value="product_name_`$language.id_lang`"}
										<input type="text"
										id="product_name_{$language.id_lang}"
										name="product_name_{$language.id_lang}"
										value="{if isset($smarty.post.$product_name)}{$smarty.post.$product_name}{/if}"
										class="form-control product_name_all wk_text_field_all wk_text_field_{$language.id_lang} {if $allow_multilang}{if $default_lang == $language.id_lang}seller_default_lang_class{/if}
										{else}{if $current_lang.id_lang == $language.id_lang}seller_default_lang_class{/if}{/if} {if $current_lang.id_lang != $language.id_lang}wk_display_none{/if}"
										data-lang-name="{$language.name}"
										maxlength="128"
										placeholder="{l s='Enter Product Name' mod='marketplace'}" />
									{/foreach}
									<span class="wk-msg-productname"></span>
								</div>
								{hook h='displayMpAddProductNameBottom'}
						        <div class="form-group">
									<label for="short_description" class="control-label">
										{l s='Short Description' mod='marketplace'}
										{block name='mp-form-fields-flag'}
											{include file='module:marketplace/views/templates/front/_partials/mp-form-fields-flag.tpl'}
										{/block}
									</label>
									{foreach from=$languages item=language}
										{assign var="short_description" value="short_description_`$language.id_lang`"}
										<div id="short_desc_div_{$language.id_lang}" class="wk_text_field_all wk_text_field_{$language.id_lang} {if $current_lang.id_lang != $language.id_lang}wk_display_none{/if}">
											<textarea
											name="short_description_{$language.id_lang}"
											id="short_description_{$language.id_lang}" cols="2" rows="3"
											class="wk_tinymce form-control">{if isset($smarty.post.$short_description)}{$smarty.post.$short_description}{/if}</textarea>
										</div>
									{/foreach}
								</div>
								<div class="form-group">
									<label for="description" class="control-label">
										{l s='Description' mod='marketplace'}
										{block name='mp-form-fields-flag'}
											{include file='module:marketplace/views/templates/front/_partials/mp-form-fields-flag.tpl'}
										{/block}
									</label>
									{foreach from=$languages item=language}
										{assign var="description" value="description_`$language.id_lang`"}
										<div id="product_desc_div_{$language.id_lang}" class="wk_text_field_all wk_text_field_{$language.id_lang} {if $current_lang.id_lang != $language.id_lang}wk_display_none{/if}">
											<textarea
											name="description_{$language.id_lang}"
											id="description_{$language.id_lang}" cols="2" rows="3"
											class="wk_tinymce form-control">{if isset($smarty.post.$description)}{$smarty.post.$description}{/if}</textarea>
										</div>
									{/foreach}
								</div>
								<div class="form-group row">
									{if Configuration::get('WK_MP_SELLER_PRODUCT_REFERENCE')}
										<div class="col-md-6">
											<label for="reference" class="control-label">
												{l s='Reference Code' mod='marketplace'}
												<div class="wk_tooltip">
													<span class="wk_tooltiptext">{l s='Your internal reference code for this product. Allowed max 32 character. Allowed special characters' mod='marketplace'}:.-_#.</span>
												</div>
											</label>
											<input type="text"
											class="form-control"
											name="reference"
											id="reference"
											value="{if isset($smarty.post.reference)}{$smarty.post.reference}{/if}"
											maxlength="32"
											placeholder="{l s='Enter Product Reference' mod='marketplace'}" />
								  		</div>
									{/if}
									{if Configuration::get('WK_MP_PRODUCT_CONDITION')}
										<div class="col-md-6">
											<div class="form-group">
												<label for="condition" class="control-label">
													{l s='Condition' mod='marketplace'}
													<div class="wk_tooltip">
														<span class="wk_tooltiptext">{l s='This option enables you to indicate the condition of the product.' mod='marketplace'}</span>
													</div>
												</label>
												<select class="form-control form-control-select" name="condition" id="condition">
													<option value="new">{l s='New' mod='marketplace'}</option>
													<option value="used">{l s='Used' mod='marketplace'}</option>
													<option value="refurbished">{l s='Refurbished' mod='marketplace'}</option>
												</select>
											</div>
											<div class="checkbox">
												<label for="show_condition">
													<input type="checkbox" name="show_condition" id="show_condition" value="1" checked="checked" />
													<span>{l s='Display condition on product page' mod='marketplace'}</span>
												</label>
											</div>
										</div>
									{/if}
								</div>
								{* Product quantity section *}
								{block name='product-quantity'}
									{include file='module:marketplace/views/templates/front/product/_partials/product-quantity.tpl'}
								{/block}
								<div class="form-group row">
									<div class="col-md-6">
										<label for="product_category" class="control-label required" >
											{l s='Category' mod='marketplace'}
											<div class="wk_tooltip">
												<span class="wk_tooltiptext">{l s='Where should the product be available on your site? The main category is where the product appears by default: this is the category which is seen in the product page\'s URL.' mod='marketplace'}</span>
											</div>
										</label>
										<div id="categorycontainer"></div>
										<input type="hidden" name="product_category" id="product_category" value="2" />
									</div>
									<div class="col-md-6" id="default_category_div">
										<label for="default_category" class="control-label required">
											{l s='Main Category' mod='marketplace'}
										</label>
									  	<select class="form-control form-control-select" name="default_category" id="default_category">
									  		{if isset($defaultCategory)}
												{foreach $defaultCategory as $defaultCategoryVal}
													<option id="default_cat{$defaultCategoryVal.id_category}" value="{$defaultCategoryVal.id_category}" name="{$defaultCategoryVal.name}">{$defaultCategoryVal.name}</option>
												{/foreach}
											{/if}
									  	</select>
								  	</div>
								</div>
								{block name='product-pricing'}
									{include file='module:marketplace/views/templates/front/product/_partials/product-pricing.tpl'}
								{/block}
						        {hook h="displayMpAddProductFooter"}
						    </div>
						    <div class="tab-pane fade in" id="wk-images">
								<div class="alert alert-danger">
									{l s='You must save this product before adding images.' mod='marketplace'}
								</div>
							</div>
							{if Configuration::get('WK_MP_SELLER_PRODUCT_COMBINATION') && $permissionData.combinationPermission}
								<div class="tab-pane fade in" id="wk-combination">
									<div class="alert alert-danger">
										{l s='You must save this product before creating combinations.' mod='marketplace'}
									</div>
								</div>
							{/if}
							{if Configuration::get('WK_MP_PRODUCT_FEATURE') && $permissionData.featuresPermission}
								<div class="tab-pane fade in" id="wk-feature">
									{if empty($permissionData.featuresPermission.add)}
										<div class="alert alert-danger">{l s='You do not have permission to add feature.' mod='marketplace'}</div>
									{else}
										{block name='mp-product-feature'}
											{include file='module:marketplace/views/templates/front/product/_partials/product-feature.tpl'}
										{/block}
									{/if}
								</div>
							{/if}
							{if (Configuration::get('WK_MP_SELLER_ADMIN_SHIPPING') && $permissionData.shippingPermission) || Module::isEnabled('mpshipping')}
								<div class="tab-pane fade in" id="wk-product-shipping">
									{if empty($permissionData.shippingPermission.add)}
										<div class="alert alert-danger">{l s='You do not have permission to add shipping.' mod='marketplace'}</div>
									{else}
										{block name='mp-product-shipping'}
											{include file='module:marketplace/views/templates/front/product/_partials/product-shipping.tpl'}
										{/block}
									{/if}
								</div>
							{/if}
							{if Configuration::get('WK_MP_SELLER_PRODUCT_SEO') && $permissionData.seoPermission}
								<div class="tab-pane fade in" id="wk-seo">
									{if empty($permissionData.seoPermission.add)}
										<div class="alert alert-danger">{l s='You do not have permission to add seo.' mod='marketplace'}</div>
									{else}
										{block name='mp-product-seo'}
											{include file='module:marketplace/views/templates/front/product/_partials/product-seo.tpl'}
										{/block}
									{/if}
								</div>
							{/if}
							{if (Configuration::get('WK_MP_SELLER_PRODUCT_EAN') || Configuration::get('WK_MP_SELLER_PRODUCT_UPC') || Configuration::get('WK_MP_SELLER_PRODUCT_ISBN') || Configuration::get('WK_MP_SELLER_PRODUCT_VISIBILITY') || Configuration::get('WK_MP_SELLER_PRODUCT_AVAILABILITY')) && $permissionData.optionsPermission}
								<div class="tab-pane fade in" id="wk-options">
									{if empty($permissionData.optionsPermission.add)}
										<div class="alert alert-danger">{l s='You do not have permission to add options.' mod='marketplace'}</div>
									{else}
										{block name='mp-product-references'}
											{include file='module:marketplace/views/templates/front/product/_partials/product-references.tpl'}
										{/block}
										{if Configuration::get('WK_MP_SELLER_PRODUCT_VISIBILITY')}
											{block name='mp-product-visibility'}
												{include file='module:marketplace/views/templates/front/product/_partials/product-visibility.tpl'}
											{/block}
										{/if}
										{if Configuration::get('WK_MP_SELLER_PRODUCT_AVAILABILITY')}
											{block name='mp-product-availability-preferences'}
												{include file='module:marketplace/views/templates/front/product/_partials/product-availability-preferences.tpl'}
											{/block}
										{/if}
									{/if}
								</div>
							{/if}
						    {hook h='displayMpProductTabContent'}
						</div>
					</div>
					{block name='mp-form-fields-notification'}
						{include file='module:marketplace/views/templates/front/_partials/mp-form-fields-notification.tpl'}
					{/block}
				</div>
				<div class="wk-mp-right-column wk_border_top_none">
					<div class="form-group row">
						<div class="col-xs-4 col-sm-4 col-md-6">
							<a href="{url entity='module' name='marketplace' controller='productlist'}" class="btn wk_btn_cancel wk_btn_extra">
								{l s='Cancel' mod='marketplace'}
							</a>
						</div>
						<div class="col-xs-8 col-sm-8 col-md-6 wk_text_right" id="wk-product-submit" data-action="{l s='Save' mod='marketplace'}">
							<img class="wk_product_loader" src="{$module_dir}marketplace/views/img/loader.gif" width="25" />
							<button type="submit" id="StayProduct" name="StayProduct" class="btn btn-success wk_btn_extra form-control-submit">
								<span>{l s='Save & Stay' mod='marketplace'}</span>
							</button>
							<button type="submit" id="SubmitProduct" name="SubmitProduct" class="btn btn-success wk_btn_extra form-control-submit">
								<span>{l s='Save' mod='marketplace'}</span>
							</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
{else}
	<div class="alert alert-danger">
		{l s='You are logged out. Please login to add product.' mod='marketplace'}</span>
	</div>
{/if}
{/block}