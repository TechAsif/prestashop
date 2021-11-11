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

{if isset($assignmpproduct)}
	{if isset($mp_sellers)}
		{if isset($ps_products) && $ps_products}
		<form id="wk_mp_seller_assign_product_form" method="post" action="{$current}&{if !empty($submit_action)}{$submit_action}{/if}&token={$token}&assignmpproduct=1" class="defaultForm form-horizontal {$name_controller}" enctype="multipart/form-data" name="wk_mp_seller_assign_product_form">
			<div class="panel">
				<div class="panel-heading">
					<i class="icon-user"></i> {l s='Assign product' mod='marketplace'}
				</div>
				<div class="form-wrapper">
					<div class="form-group">
						<label class="control-label col-lg-3 required">
							<span>{l s='Select Seller' mod='marketplace'}</span>
						</label>
						<div class="col-lg-3">
							<select name="id_customer">
								{foreach $mp_sellers as $seller}
									<option value="{$seller.id_customer}">{$seller.business_email}</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-lg-3 required">
							<span>{l s='Select Product' mod='marketplace'}</span>
						</label>
						<div class="col-lg-3">
							<select class="chosen" name="id_product[]" multiple style="height:112px;">
								{foreach $ps_products as $product}
									<option value="{$product.id_product}">{$product.name} ({$product.id_product})</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-lg-3">
							<span>{l s='Assign product with' mod='marketplace'}</span>
						</label>
						<div class="col-lg-3">
			                <table class="table table-bordered">
			                    <thead>
			                        <tr>
			                            <th class="fixed-width-xs">
			                                <span class="title_box">
			                                    <input type="checkbox" name="checkme" id="checkme" onclick="checkDelBoxes(this.form, 'assignedValues[]', this.checked)" {if Configuration::get('WK_MP_SELLER_PRODUCT_COMBINATION') && Configuration::get('WK_MP_PRODUCT_FEATURE') && Configuration::get('WK_MP_SELLER_ADMIN_SHIPPING')}checked{/if} />
			                                </span>
			                            </th>
			                            <th><label class="title_box">{l s='Name' mod='marketplace'}</label></th>
			                        </tr>
			                    </thead>
			                    <tbody>
		                            <tr>
		                                <td><input type="checkbox" name="assignedValues[]" value="1" {if Configuration::get('WK_MP_SELLER_PRODUCT_COMBINATION')}checked{/if} /></td>
		                                <td><span>{l s='Combinations' mod='marketplace'}</span></td>
		                            </tr>
		                            <tr>
		                                <td><input type="checkbox" name="assignedValues[]" value="2" {if Configuration::get('WK_MP_PRODUCT_FEATURE')}checked{/if} /></td>
		                                <td><span>{l s='Features' mod='marketplace'}</span></td>
		                            </tr>
		                            <tr>
		                                <td><input type="checkbox" name="assignedValues[]" value="3" {if Configuration::get('WK_MP_SELLER_ADMIN_SHIPPING')}checked{/if} /></td>
		                                <td><span>{l s='Shipping' mod='marketplace'}</span></td>
		                            </tr>
			                    </tbody>
			                </table>
				        </div>
					</div>
					<div class="form-group">
						{if !Configuration::get('WK_MP_SELLER_PRODUCT_COMBINATION')}
							<div class="wk-assign-combinations" style="display:none;">
								<div class="col-lg-6 col-lg-offset-3 alert alert-warning">{l s='Combinations selected but you did not allow sellers to manage the combinations from Configuration (Approval Settings).' mod='marketplace'}</div>
							</div>
						{/if}
						{if !Configuration::get('WK_MP_PRODUCT_FEATURE')}
							<div class="wk-assign-features" style="display:none;">
								<div class="col-lg-6 col-lg-offset-3 alert alert-warning">{l s='Features selected but you did not allow sellers to manage the features from Configuration (Approval Settings).' mod='marketplace'}</div>
							</div>
						{/if}
						{if !Configuration::get('WK_MP_SELLER_ADMIN_SHIPPING')}
							<div class="wk-assign-shipping" style="display:none;">
								<div class="col-lg-6 col-lg-offset-3 alert alert-warning">{l s='Shipping selected but you did not allow sellers to manage the shipping from Configuration (Approval Settings).' mod='marketplace'}</div>
							</div>
						{/if}
						<div class="col-lg-6 col-lg-offset-3 alert alert-info">
							{l s='If you do not choose any option then only standard product(s) will be assigned.' mod='marketplace'}
						</div>
					</div>
				</div>
				<div class="panel-footer">
					<a href="{$link->getAdminLink('AdminSellerProductDetail')}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel' mod='marketplace'}</a>
					<button type="submit" name="submitAdd{$table}" class="btn btn-default pull-right wk-prod-assign"><i class="process-icon-save"></i> {l s='Assign' mod='marketplace'}</button>
					<button type="submit" name="submitAdd{$table}AndAssignStay" class="btn btn-default pull-right wk-prod-assign">
						<i class="process-icon-save"></i> {l s='Assign and stay' mod='marketplace'}
					</button>
				</div>
			</div>
		</form>
		{else}
			<div class="alert alert-danger">
				{l s='No products available for assign' mod='marketplace'}
			</div>
		{/if}
	{else}
		<div class="alert alert-danger">
			{l s='No seller found' mod='marketplace'}
		</div>
	{/if}
{else}
	<div class="panel">
		<div class="panel-heading">
			{if isset($edit)}
				{l s='Edit Product' mod='marketplace'}
			{else}
				{l s='Add New Product' mod='marketplace'}
			{/if}
		</div>
	    <form name="mp_admin_saveas_button" id="mp_admin_saveas_button" class="defaultForm {$name_controller} form-horizontal" action="{if isset($edit)}{$current}&update{$table}&id_mp_product={$product_info.id}&token={$token}{else}{$current}&add{$table}&token={$token}{/if}" method="post" enctype="multipart/form-data" {if isset($style)}style="{$style}"{/if}>

			{hook h='displayMpAddProductHeader'}
			<div class="form-group">
				<div class="col-lg-6">
					{if !isset($edit)}
						<div class="form-group">
							<label class="control-label pull-left required">
								{l s='Choose Seller' mod='marketplace'}&nbsp;
							</label>
							{if isset($customer_info)}
								<select name="shop_customer" id="wk_shop_customer" class="fixed-width-xl pull-left">
									{foreach $customer_info as $cusinfo}
										<option value="{$cusinfo['id_customer']}">
											{$cusinfo['business_email']}
										</option>
									{/foreach}
								</select>
							{else}
								<p>{l s='No seller found.' mod='marketplace'}</p>
							{/if}
						</div>
					{/if}
					{if $multi_lang}
						<div class="form-group">
							<label class="control-label">
								&nbsp;&nbsp;{l s='Seller Default Language -' mod='marketplace'}
								<span id="seller_default_lang_div">{$current_lang.name}</label>
							</label>
						</div>
					{/if}
				</div>
				{if $allow_multilang && $total_languages > 1}
					<div class="col-lg-6">
						<label class="control-label">{l s='Choose Language' mod='marketplace'}</label>
						<input type="hidden" name="choosedLangId" id="choosedLangId" value="{$current_lang.id_lang}">
						<button type="button" id="seller_lang_btn" class="btn btn-default dropdown-toggle wk_language_toggle" data-toggle="dropdown">
							{$current_lang.name}
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu wk_language_menu" style="left:14%;top:32px;">
							{foreach from=$languages item=language}
								<li>
									<a href="javascript:void(0)" onclick="showProdLangField('{$language.name}', {$language.id_lang});">
										{$language.name}
									</a>
								</li>
							{/foreach}
						</ul>
						<p class="help-block">{l s='Change language for updating information in multiple language.' mod='marketplace'}</p>
					</div>
				{/if}
			</div>

			<input type="hidden" name="active_tab" value="{if isset($active_tab)}{$active_tab}{/if}" id="active_tab">
			<input type="hidden" value="{if isset($edit)}{$product_info.id}{/if}" name="id" id="mp_product_id" />
			<input type="hidden" name="seller_default_lang" id="seller_default_lang" value="{$current_lang.id_lang}">
			<div class="alert alert-danger wk_display_none" id="wk_mp_form_error"></div>
			<div class="tabs wk-tabs-panel">
				<ul class="nav nav-tabs">
					<li class="active">
						<a href="#wk-information" data-toggle="tab">
							<i class="icon-info-sign"></i>
							{l s='Information' mod='marketplace'}
						</a>
					</li>
					<li>
						<a href="#wk-images" data-toggle="tab">
							<i class="icon-image"></i>
							{l s='Images' mod='marketplace'}
						</a>
					</li>
					<li>
						<a href="#wk-combination" data-toggle="tab">
							<i class="icon-cubes"></i>
							{l s='Combination' mod='marketplace'}
						</a>
					</li>
					<li>
						<a href="#wk-feature" data-toggle="tab">
							<i class="icon-star"></i>
							{l s='Features' mod='marketplace'}
						</a>
					</li>
					<li>
						<a href="#wk-product-shipping" data-toggle="tab">
							<i class="icon-truck"></i>
							{l s='Shipping' mod='marketplace'}
						</a>
					</li>
					<li>
						<a href="#wk-seo" data-toggle="tab">
							<i class="icon-star-empty"></i>
							{l s='SEO' mod='marketplace'}
						</a>
					</li>
					<li>
						<a href="#wk-options" data-toggle="tab">
							<i class="icon-list"></i>
							{l s='Options' mod='marketplace'}
						</a>
					</li>
					{hook h='displayMpProductNavTab'}
				</ul>
				<div class="tab-content panel collapse in">
					<div class="tab-pane active" id="wk-information">
						{if isset($edit)}
							{hook h='displayMpUpdateProductContentTop'}
						{else}
							{hook h='displayMpAddProductContentTop'}
						{/if}
						<div class="form-group">
							<label for="product_name" class="col-lg-3 control-label required">
								{l s='Product Name' mod='marketplace'}
								{include file="$wkself/../../views/templates/front/_partials/mp-form-fields-flag.tpl"}
							</label>
							<div class="col-lg-6">
								{foreach from=$languages item=language}
									{assign var="product_name" value="product_name_`$language.id_lang`"}
									<input type="text"
									id="product_name_{$language.id_lang}"
									name="product_name_{$language.id_lang}"
									value="{if isset($smarty.post.$product_name)}{$smarty.post.$product_name|escape:'htmlall':'UTF-8'}{elseif isset($edit)}{$product_info.product_name[{$language.id_lang}]|escape:'htmlall':'UTF-8'}{/if}"
									class="form-control product_name_all wk_text_field_all wk_text_field_{$language.id_lang}"
									maxlength="128"
									{if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if} />
								{/foreach}
							</div>
						</div>
						{if !isset($edit)}
							{hook h="displayMpAddProductNameBottom"}
						{else}
							{hook h="DisplayMpUpdateProductNameBottom"}
						{/if}
						<div class="form-group">
							<label for="short_description" class="col-lg-3 control-label">
								{l s='Short Description' mod='marketplace'}
								{include file="$wkself/../../views/templates/front/_partials/mp-form-fields-flag.tpl"}
							</label>
							<div class="col-lg-6">
								{foreach from=$languages item=language}
									{assign var="short_desc_name" value="short_description_`$language.id_lang`"}
									<div id="short_desc_div_{$language.id_lang}" class="wk_text_field_all wk_text_field_{$language.id_lang}" {if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}>
										<textarea
										name="short_description_{$language.id_lang}"
										id="short_description_{$language.id_lang}"
										cols="2" rows="3"
										class="wk_tinymce form-control">{if isset($smarty.post.$short_desc_name)}{$smarty.post.$short_desc_name}{elseif isset($edit)}{$product_info.short_description[{$language.id_lang}]}{/if}</textarea>
									</div>
								{/foreach}
							</div>
						</div>
						<div class="form-group">
							<label for="product_description" class="col-lg-3 control-label">
								{l s='Description' mod='marketplace'}
								{include file="$wkself/../../views/templates/front/_partials/mp-form-fields-flag.tpl"}
							</label>
							<div class="col-lg-6">
								{foreach from=$languages item=language}
									{assign var="description" value="description_`$language.id_lang`"}
									<div id="product_desc_div_{$language.id_lang}" class="wk_text_field_all wk_text_field_{$language.id_lang}" {if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}>
										<textarea
										name="description_{$language.id_lang}"
										id="description_{$language.id_lang}"
										cols="2" rows="3"
										class="wk_tinymce form-control">{if isset($smarty.post.$description)}{$smarty.post.$description}{elseif isset($edit)}{$product_info.description[{$language.id_lang}]}{/if}</textarea>
									</div>
								{/foreach}
							</div>
						</div>
						<div class="form-group">
							<label for="reference" class="col-lg-3 control-label">
								{l s='Reference Code' mod='marketplace'}
								<div class="wk_tooltip">
									<span class="wk_tooltiptext">{l s='Your internal reference code for this product. Allowed max 32 character. Allowed special characters' mod='marketplace'}:.-_#.</span>
								</div>
							</label>
				   			<div class="col-lg-6">
					   			<input type="text"
					   			class="form-control"
					   			name="reference"
					   			id="reference"
					   			value="{if isset($smarty.post.reference)}{$smarty.post.reference}{else if isset($edit)}{$product_info.reference}{/if}"
					   			maxlength="32" />
				   			</div>
						</div>
						<div class="form-group">
							<label for="condition" class="control-label col-lg-3">
								{l s='Condition' mod='marketplace'}
								<div class="wk_tooltip">
									<span class="wk_tooltiptext">{l s='This option enables you to indicate the condition of the product.' mod='marketplace'}</span>
								</div>
							</label>
							<div class="col-lg-4">
								<div>
									<select class="form-control" name="condition" id="condition">
										<option value="new" {if isset($edit)}{if $product_info.condition == 'new'}Selected="Selected"{/if}{else}{if isset($smarty.post.condition)}{if $smarty.post.condition == 'new'}Selected="Selected"{/if}{/if}{/if}>
											{l s='New' mod='marketplace'}
										</option>
										<option value="used" {if isset($edit)}{if $product_info.condition == 'used'}Selected="Selected"{/if}{else}{if isset($smarty.post.condition)}{if $smarty.post.condition == 'used'}Selected="Selected"{/if}{/if}{/if}>
											{l s='Used' mod='marketplace'}
										</option>
										<option value="refurbished" {if isset($edit)}{if $product_info.condition == 'refurbished'}Selected="Selected"{/if}{else}{if isset($smarty.post.condition)}{if $smarty.post.condition == 'refurbished'}Selected="Selected"{/if}{/if}{/if}>
											{l s='Refurbished' mod='marketplace'}
										</option>
									</select>
								</div>
								<div class="checkbox">
									<label for="show_condition">
										<input type="checkbox" name="show_condition" id="show_condition" value="1" {if isset($edit)}{if $product_info.show_condition == '1'}checked="checked"{/if}{else}{if isset($smarty.post.show_condition)}{if $smarty.post.show_condition == '1'}checked="checked"{/if}{/if}{/if} />
										<span>{l s='Display condition on product page' mod='marketplace'}</span>
									</label>
								</div>
						  	</div>
						</div>
						{if !isset($edit)}
							<div class="form-group">
								<label class="col-lg-3 control-label">{l s='Enable product' mod='marketplace'}</label>
								<div class="col-lg-6">
									<span class="switch prestashop-switch fixed-width-lg">
										<input type="radio" checked="checked" value="1" id="product_active_on" name="product_active">
										<label for="product_active_on">{l s='Yes' mod='marketplace'}</label>
										<input type="radio" value="0" id="product_active_off" name="product_active">
										<label for="product_active_off">{l s='No' mod='marketplace'}</label>
										<a class="slide-button btn"></a>
									</span>
								</div>
							</div>
						{/if}
						{* Product quantity section *}
						{include file="$wkself/../../views/templates/front/product/_partials/product-quantity.tpl"}
						<div class="form-group">
							<label class="col-lg-3 control-label required" for="product_category">
								{l s='Category' mod='marketplace'}
								<div class="wk_tooltip">
									<span class="wk_tooltiptext">{l s='Where should the product be available on your site? The main category is where the product appears by default: this is the category which is seen in the product page\'s URL.' mod='marketplace'}</span>
								</div>
							</label>
							<div class="col-lg-6">
								<div id="categorycontainer"></div>
								<input type="hidden" name="product_category" id="product_category" value="{if isset($catIdsJoin)}{$catIdsJoin}{/if}" />
							</div>
						</div>
						<div class="form-group" id="default_category_div">
							<label class="col-lg-3 control-label required" for="default_category">
								{l s='Main Category' mod='marketplace'}
							</label>
							<div class="col-lg-4">
								<select name="default_category" class="form-control" id="default_category">
									{if isset($defaultCategory)}
										{foreach $defaultCategory as $defaultCategoryVal}
											<option
											id="default_cat{$defaultCategoryVal.id_category}"
											value="{$defaultCategoryVal.id_category}"
											{if isset($defaultIdCategory)}{if $defaultIdCategory == $defaultCategoryVal.id_category} selected {/if}{/if}>
												{$defaultCategoryVal.name}
											</option>
										{/foreach}
									{else}
										<option id="default_cat2" value="2">Home</option>
									{/if}
								</select>
							</div>
						</div>
						{include file="$wkself/../../views/templates/front/product/_partials/product-pricing.tpl"}
						{if !isset($edit)}
							{hook h="displayMpAddProductFooter"}
						{else}
							{hook h="displayMpUpdateProductFooter"}
						{/if}
					</div>
					<div class="tab-pane" id="wk-images">
						{if isset($edit)}
							<div class="form-group">
								<div class="wk_upload_product_image">
									<input type="file" name="productimages[]" class="uploadimg_container" data-jfiler-name="productimg">
							    </div>
							</div>
							{include file="$wkself/../../views/templates/front/product/imageedit.tpl"}
						{else}
							<div class="alert alert-danger">
								{l s='You must save this product before adding images.' mod='marketplace'}
							</div>
						{/if}
					</div>
					<div class="tab-pane fade in" id="wk-combination">
						{if isset($edit)}
							{include file="$wkself/../../views/templates/front/product/_partials/product-combination.tpl"}
						{else}
							<div class="alert alert-danger">
								{l s='You must save this product before adding combination.' mod='marketplace'}
							</div>
						{/if}
					</div>
					<div class="tab-pane fade in" id="wk-feature">
						{include file="$wkself/../../views/templates/front/product/_partials/product-feature.tpl"}
					</div>
					<div class="tab-pane fade in" id="wk-product-shipping">
						{include file="$wkself/../../views/templates/front/product/_partials/product-shipping.tpl"}
					</div>
					<div class="tab-pane fade in" id="wk-seo">
						{include file="$wkself/../../views/templates/front/product/_partials/product-seo.tpl"}
					</div>
					<div class="tab-pane" id="wk-options">
						<div class="form-group">
							<label for="reference" class="col-lg-3 control-label">
								{l s='EAN-13 or JAN barcode' mod='marketplace'}
								<div class="wk_tooltip">
									<span class="wk_tooltiptext">{l s='Allowed max 13 character. This type of product code is specific to Europe and Japan, but is widely used internationally. It is a superset of the UPC code: all products marked with an EAN will be accepted in North America.' mod='marketplace'}</span>
								</div>
							</label>
				   			<div class="col-lg-6">
					   			<input type="text"
					   			class="form-control"
					   			name="ean13"
					   			id="ean13"
					   			value="{if isset($smarty.post.ean13)}{$smarty.post.ean13}{else if isset($edit)}{$product_info.ean13}{/if}" maxlength="13" />
				   			</div>
						</div>
						<div class="form-group">
							<label for="reference" class="col-lg-3 control-label">
								{l s='UPC Barcode' mod='marketplace'}
								<div class="wk_tooltip">
									<span class="wk_tooltiptext">{l s='Allowed max 12 character. This type of product code is widely used in the United States, Canada, the United Kingdom, Australia, New Zealand and in other countries.' mod='marketplace'}</span>
								</div>
							</label>
				   			<div class="col-lg-6">
					   			<input type="text"
					   			class="form-control"
					   			name="upc"
					   			id="upc"
					   			value="{if isset($smarty.post.upc)}{$smarty.post.upc}{else if isset($edit)}{$product_info.upc}{/if}"
					   			maxlength="12"/>
				   			</div>
						</div>
						<div class="form-group">
							<label for="reference" class="col-lg-3 control-label">
								{l s='ISBN' mod='marketplace'}
								<div class="wk_tooltip">
									<span class="wk_tooltiptext">{l s='Allowed max 13 character. This type of code is widely used internationally to identify books and their various editions' mod='marketplace'}</span>
								</div>
							</label>
				   			<div class="col-lg-6">
					   			<input type="text"
					   			class="form-control"
					   			name="isbn"
					   			id="isbn"
					   			value="{if isset($smarty.post.isbn)}{$smarty.post.isbn}{else if isset($edit)}{$product_info.isbn}{/if}"
					   			maxlength="13"/>
				   			</div>
						</div>
						<!-- Product visibility options -->
						{include file="$wkself/../../views/templates/front/product/_partials/product-visibility.tpl"}
						{include file="$wkself/../../views/templates/front/product/_partials/product-availability-preferences.tpl"}
					</div>
					{hook h='displayMpProductTabContent'}
				</div>
			</div>
			<div class="panel-footer">
				<a href="{$link->getAdminLink('AdminSellerProductDetail')}" class="btn btn-default">
					<i class="process-icon-cancel"></i>{l s='Cancel' mod='marketplace'}
				</a>
				<button type="submit" name="submitAdd{$table}" class="btn btn-default pull-right" id="mp_admin_save_button">
					<i class="process-icon-save"></i> {l s='Save' mod='marketplace'}
				</button>
				<button type="submit" name="submitAdd{$table}AndStay" class="btn btn-default pull-right" id="mp_admin_saveandstay_button">
					<i class="process-icon-save"></i> {l s='Save and stay' mod='marketplace'}
				</button>
			</div>
		</form>
	</div>
{/if}

{block name=script}
<script type="text/javascript">
	$(document).ready(function() {
	    tinySetup({
	        editor_selector: "wk_tinymce",
	        width: 450
	    });
	});

	$('.fancybox').fancybox();
</script>
{/block}

<style type="text/css">
.price_with_tax {
	font-size: 14px;
    font-weight: bold;
    padding-top: 8px;
}
</style>

{strip}
	{addJsDef path_sellerproduct = $link->getAdminlink('AdminSellerProductDetail')}
	{addJsDef path_uploader = $link->getAdminlink('AdminSellerProductDetail')}
	{addJsDef ajax_urlpath = $link->getAdminlink('AdminSellerProductDetail')}

	{addJsDef actionpage = 'product'}
	{addJsDef adminupload = 1}
	{addJsDef backend_controller = 1}
	{addJsDef img_module_dir = 1}
	{addJsDef mp_image_dir = $mp_image_dir}
	{addJsDef iso = $iso}
	{addJsDef ad = $ad}
	{addJsDef pathCSS = $smarty.const._THEME_CSS_DIR_}
	{addJsDef multi_lang = $multi_lang}
	{addJsDef deleteaction = 'jFiler-item-trash-action'}

	{if isset($edit)}
		{addJsDef actionIdForUpload = $product_info.id}
		{addJsDef defaultIdCategory = $defaultIdCategory}
	{else}
		{addJsDef actionIdForUpload = ''}
		{addJsDef actionIdForUpload = ''}
	{/if}

	{addJsDefL name='drag_drop'}{l s='Drag & Drop to Upload' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name='or'}{l s='or' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name='pick_img'}{l s='Pick Image' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=choosefile}{l s='Choose Images' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=choosefiletoupload}{l s='Choose Images To Upload' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=imagechoosen}{l s='Images were chosen' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=dragdropupload}{l s='Drop file here to Upload' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=confirm_delete_msg}{l s='Are you sure want to delete this image?' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=only}{l s='Only' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=imagesallowed}{l s='Images are allowed to be uploaded.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=onlyimagesallowed}{l s='Images are allowed to be uploaded.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=imagetoolarge}{l s='is too large! Please upload image up to' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=imagetoolargeall}{l s='Images you have choosed are too large! Please upload images up to' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=error_msg}{l s='Some error occurs while deleting image' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=req_price}{l s='Product price is required.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=notax_avalaible}{l s='No tax available' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=some_error}{l s='Some error occured.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=Choose}{l s='Choose' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=confirm_delete_combination}{l s='Are you sure you want to delete this combination?' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=noAllowDefaultAttribute}{l s='You can not make deactivated attribute as default attribute.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=not_allow_todeactivate_combination}{l s='You can not deactivate this combination. Atleast one combination must be active.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=req_prod_name}{l s='Product name is required in Default Language -' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=req_catg}{l s='Please select atleast one category.' js=1 mod='marketplace'}{/addJsDefL}
    {addJsDef path_addfeature = $link->getAdminlink('AdminSellerProductDetail')}
    {addJsDefL name=choose_value}{l s='Choose a value' js=1 mod='marketplace'}{/addJsDefL}
    {addJsDefL name=no_value}{l s='No Value Found' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=value_missing}{l s='Feature value is missing' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=value_length_err}{l s='Feature value is too long' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=value_name_err}{l s='Feature value is not valid' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=feature_err}{l s='Feature is not selected' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=enabled}{l s='Enabled' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=disabled}{l s='Disabled' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=update_success}{l s='Updated Successfully' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=invalid_value}{l s='Invalid Value' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=choose_one}{l s='Choose atleast one product' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=confirm_assign_msg}{l s='Are you sure you want to assign selected product(s) to seller?' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=success_msg}{l s='Success' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=error_msg}{l s='Error' js=1 mod='marketplace'}{/addJsDefL}
{/strip}