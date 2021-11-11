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

<div class="panel">
	<div class="panel-heading">
		<i class="icon-user"></i>
		{if isset($edit)}
			{l s='Edit seller' mod='marketplace'}
		{else}
			{l s='Add new seller' mod='marketplace'}
		{/if}
	</div>
    <form id="{$table}_form" class="defaultForm {$name_controller} form-horizontal" action="{if isset($edit)}{$current}&update{$table}&id_seller={$mp_seller_info.id_seller}&token={$token}{else}{$current}&add{$table}&token={$token}{/if}" method="post" enctype="multipart/form-data">
		<input type="hidden" name="current_lang_id" id="current_lang_id" value="{$current_lang.id_lang}">
		<div class="form-group">
			{if !isset($edit)}
				<label class="col-lg-2 control-label required">{l s='Choose Customer' mod='marketplace'}</label>
				<div class="col-lg-4">
					{if isset($customer_info)}
						<select name="shop_customer" class="fixed-width-xl">
							{foreach $customer_info as $cusinfo}
								<option value="{$cusinfo['id_customer']}" {if isset($smarty.post.shop_customer)}{if $smarty.post.shop_customer == $cusinfo['id_customer']}Selected="Selected"{/if}{/if}>
									{$cusinfo['email']}
								</option>
							{/foreach}
						</select>
					{else}
						<p class="alert alert-danger">{l s='There is no customer found on your shop to add as a Marketplace seller. You can add only registered customer as a marketplace seller' mod='marketplace'}</p>
					{/if}
				</div>
			{/if}
			{if $allow_multilang && $total_languages > 1}
				<div class="col-lg-6">
					<label class="control-label">{l s='Choose Language' mod='marketplace'}</label>
					<button type="button" id="seller_lang_btn" class="btn btn-default dropdown-toggle wk_language_toggle" data-toggle="dropdown">
						{$current_lang.name}
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu wk_language_menu" style="left:14%;top:32px;">
						{foreach from=$languages item=language}
							<li>
								<a href="javascript:void(0)" onclick="showSellerLangField('{$language.name}', {$language.id_lang});">
									{$language.name}
								</a>
							</li>
						{/foreach}
					</ul>
					<p class="help-block">{l s='Change language for updating information in multiple language.' mod='marketplace'}</p>
				</div>
			{/if}
			{if isset($edit)}
			<div class="col-lg-6">
				<input type="hidden" value="{$mp_seller_info.id_seller}" name="mp_seller_id" id="mp_seller_id"/>
				<input type="hidden" value="{$mp_seller_info.shop_name_unique}" name="pre_shop_name_unique" />
			</div>
			{/if}
			<input type="hidden" name="active_tab" value="{if isset($active_tab)}{$active_tab}{/if}" id="active_tab">
		</div>
		<div class="alert alert-danger wk_display_none" id="wk_mp_form_error"></div>
		<hr>
		<div class="tabs wk-tabs-panel">
			<ul class="nav nav-tabs">
				<li class="active">
					<a href="#wk-information" data-toggle="tab">
						<i class="icon-info-sign"></i>
						{l s='Information' mod='marketplace'}
					</a>
				</li>
				<li>
					<a href="#wk-contact" data-toggle="tab">
						<i class="icon-envelope"></i>
						{l s='Address' mod='marketplace'}
					</a>
				</li>
				<li>
					<a href="#wk-images" data-toggle="tab">
						<i class="icon-image"></i>
						{l s='Images' mod='marketplace'}
					</a>
				</li>
				<li>
					<a href="#wk-social" data-toggle="tab">
						<i class="icon-user"></i>
						{l s='Social' mod='marketplace'}
					</a>
				</li>
				<li>
					<a href="#wk-permission" data-toggle="tab">
						<i class="icon-ok-circle"></i>
						{l s='Permission' mod='marketplace'}
					</a>
				</li>
				<li>
					<a href="#wk-seller-payment-details" data-toggle="tab">
						<i class="icon-credit-card"></i>
						{l s='Payment Details' mod='marketplace'}
					</a>
				</li>
				{hook h='displayMpEditProfileTab'}
			</ul>
			<div class="tab-content panel collapse in" id="tab-content">
				<div class="tab-pane fade in active" id="wk-information">
					{if $total_languages > 1}
						<div class="form-group">
							<label class="col-lg-3 control-label required">
								{l s='Default Language' mod='marketplace'}
							</label>
							<div class="col-lg-4">
							  	<select class="form-control fixed-width-xl" name="default_lang" id="default_lang">
							  		{foreach from=$languages item=language}
								  		{if $language.active}
								  			{if isset($edit)}
								  				{if $allow_multilang}
									  				<option data-lang-name="{$language.name}"
													value="{$language.id_lang}"
													{if $current_lang.id_lang == $language.id_lang}Selected="Selected" {/if}>
														{$language.name}
													</option>
												{else}
										  			{if $mp_seller_info.default_lang == $language.id_lang}
														<option data-lang-name="{$language.name}" value="{$language.id_lang}">
															{$language.name}
														</option>
													{/if}
												{/if}
											{else}
												<option data-lang-name="{$language.name}" value="{$language.id_lang}"
													{if isset($smarty.post.default_lang)}
														{if $smarty.post.default_lang == $language.id_lang}Selected="Selected"
														{/if}
													{else}
														{if $current_lang.id_lang == $language.id_lang}Selected="Selected"{/if}
													{/if}>
													{$language.name}
												</option>
											{/if}
										{/if}
									{/foreach}
							  	</select>
							  	{if isset($edit) && !$allow_multilang}
							  		<p class="help-block">{l s='You can\'t change default language.' mod='marketplace'}</p>
							  	{/if}
						  	</div>
						</div>
					{else}
						<input type="hidden" name="default_lang" value="{if isset($edit)}{$mp_seller_info.default_lang}{else}{$context_language}{/if}" />
					{/if}
					<div class="form-group seller_shop_name_uniq">
						<label class="col-lg-3 control-label required">
							{l s='Shop Unique Name' mod='marketplace'}
							<div class="wk_tooltip">
								<span class="wk_tooltiptext">{l s='This name will be used in your shop URL.' mod='marketplace'}</span>
							</div>
						</label>
						<div class="col-lg-6">
							<input class="form-control wk_text_field"
								type="text"
								value="{if isset($edit)}{if isset($smarty.post.shop_name_unique)}{$smarty.post.shop_name_unique|escape:'htmlall':'UTF-8'}{else}{$mp_seller_info.shop_name_unique|escape:'htmlall':'UTF-8'}{/if}{else}{if isset($smarty.post.shop_name_unique)}{$smarty.post.shop_name_unique|escape:'htmlall':'UTF-8'}{/if}{/if}"
								id="shop_name_unique"
								name="shop_name_unique"
								onblur="onblurCheckUniqueshop();"
								autocomplete="off" />
							<p class="help-block wk-msg-shopnameunique" style="color:#8F0000;"></p>
						</div>
					</div>
					<div class="form-group seller_shop_name">
						<label class="col-lg-3 control-label required">
							{l s='Shop Name' mod='marketplace'}
							{include file="$wkself/../../views/templates/front/_partials/mp-form-fields-flag.tpl"}
						</label>
						<div class="col-lg-6">
							{foreach from=$languages item=language}
								{assign var="shop_name" value="shop_name_`$language.id_lang`"}
								<input class="form-control wk_text_field shop_name_all wk_text_field_all wk_text_field_{$language.id_lang} {if isset($edit)}{if $mp_seller_info.default_lang == $language.id_lang}seller_default_shop{/if}{else}{if $current_lang.id_lang == $language.id_lang}seller_default_shop{/if}{/if}"
								type="text"
								data-lang-name="{$language.name}"
								value="{if isset($edit)}{if isset($smarty.post.$shop_name)}{$smarty.post.$shop_name|escape:'htmlall':'UTF-8'}{else}{$mp_seller_info.shop_name.{$language.id_lang}|escape:'htmlall':'UTF-8'}{/if}{else}{if isset($smarty.post.$shop_name)}{$smarty.post.$shop_name|escape:'htmlall':'UTF-8'}{/if}{/if}"
								id="shop_name_{$language.id_lang}"
								name="shop_name_{$language.id_lang}"
								{if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}/>
							{/foreach}
							<p class="help-block wk-msg-shopname" style="color:#971414;"></p>
						</div>
					</div>
					{if !isset($edit)}
						<div class="form-group">
							<label class="col-lg-3 control-label">{l s='Enable seller' mod='marketplace'}</label>
							<div class="col-lg-6">
								<span class="switch prestashop-switch fixed-width-lg">
									<input type="radio" checked="checked" value="1" id="seller_active_on" name="seller_active">
									<label for="seller_active_on">{l s='Yes' mod='marketplace'}</label>
									<input type="radio" value="0" id="seller_active_off" name="seller_active">
									<label for="seller_active_off">{l s='No' mod='marketplace'}</label>
									<a class="slide-button btn"></a>
								</span>
							</div>
						</div>
					{/if}
					<div class="form-group">
						<label for="seller_firstname" class="col-lg-3 control-label required">
							<span class="label-tooltip" "="" ?{}_$%:=" title="" data-html="true" data-toggle="tooltip" data-original-title="{l s='Invalid characters' mod='marketplace'} 0-9!&lt;&gt;,;?=+()@#">{l s='First Name' mod='marketplace'}</span>
						</label>
						<div class="col-lg-6">
							<input type="text"
							name="seller_firstname"
							id="seller_firstname"
							value="{if isset($edit)}{if isset($smarty.post.seller_firstname)}{$smarty.post.seller_firstname}{else}{$mp_seller_info.seller_firstname}{/if}{else}{if isset($smarty.post.seller_firstname)}{$smarty.post.seller_firstname}{/if}{/if}" />
						</div>
					</div>
					<div class="form-group">
						<label for="seller_lastname" class="col-lg-3 control-label required">
							<span class="label-tooltip" "="" ?{}_$%:=" title="" data-html="true" data-toggle="tooltip" data-original-title="{l s='Invalid characters' mod='marketplace'} 0-9!&lt;&gt;,;?=+()@#">{l s='Last Name' mod='marketplace'}</span>
						</label>
						<div class="col-lg-6">
							<input type="text"
							name="seller_lastname"
							id="seller_lastname"
							value="{if isset($edit)}{if isset($smarty.post.seller_lastname)}{$smarty.post.seller_lastname}{else}{$mp_seller_info.seller_lastname}{/if}{else}{if isset($smarty.post.seller_lastname)}{$smarty.post.seller_lastname}{/if}{/if}" />
						</div>
					</div>
					<div class="form-group">
						<label for="business_email" class="col-lg-3 control-label required">
							{l s='Business Email' mod='marketplace'}
						</label>
						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-addon">
									<i class="icon-envelope-o"></i>
								</span>
								<input class="form-control-static"
								type="text"
								name="business_email"
								id="business_email"
								value="{if isset($edit)}{if isset($smarty.post.business_email)}{$smarty.post.business_email}{else}{$mp_seller_info.business_email}{/if}{else}{if isset($smarty.post.business_email)}{$smarty.post.business_email}{/if}{/if}"
								onblur="onblurCheckUniqueSellerEmail();" />
							</div>
							<p class="help-block wk-msg-selleremail" style="color:#971414;"></p>
						</div>
					</div>
					<div class="form-group">
						<label for="phone" class="col-lg-3 control-label required">
							{l s='Phone' mod='marketplace'}
						</label>
						<div class="col-lg-6">
							<input class="form-control"
							type="text"
							value="{if isset($edit)}{if isset($smarty.post.phone)}{$smarty.post.phone}{else}{$mp_seller_info.phone}{/if}{else}{if isset($smarty.post.phone)}{$smarty.post.phone}{/if}{/if}"
							name="wk_phone"
							id="phone"
							maxlength="{$max_phone_digit}" />
						</div>
					</div>
					<div class="form-group">
						<label for="fax" class="col-lg-3 control-label">{l s='Fax' mod='marketplace'}</label>
						<div class="col-lg-6">
							<input class="form-control-static"
							type="text"
							name="fax"
							id="fax"
							value="{if isset($edit)}{if isset($smarty.post.fax)}{$smarty.post.fax}{else}{$mp_seller_info.fax}{/if}{else}{if isset($smarty.post.fax)}{$smarty.post.fax}{/if}{/if}" />
						</div>
					</div>
					<div class="form-group">
						<label for="fax" class="col-lg-3 control-label">{l s='Tax Identification Number' mod='marketplace'}</label>
						<div class="col-lg-6">
							<input class="form-control-static"
							type="text"
							name="tax_identification_number"
							id="tax_identification_number"
							value="{if isset($edit)}{if isset($smarty.post.tax_identification_number)}{$smarty.post.tax_identification_number}{else}{$mp_seller_info.tax_identification_number}{/if}{else}{if isset($smarty.post.tax_identification_number)}{$smarty.post.tax_identification_number}{/if}{/if}" />
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">
							{l s='Shop Description' mod='marketplace'}

							{include file="$wkself/../../views/templates/front/_partials/mp-form-fields-flag.tpl"}
						</label>
						<div class="col-lg-6">
							{foreach from=$languages item=language}
								{assign var="about_shop" value="about_shop_`$language.id_lang`"}
								<div id="about_business_div_{$language.id_lang}" class="wk_text_field_all wk_text_field_{$language.id_lang}" {if $current_lang.id_lang != $language.id_lang}style="display:none;"{/if}>
									<textarea
									name="about_shop_{$language.id_lang}"
									id="about_shop_{$language.id_lang}" cols="2" rows="3"
									class="about_business wk_tinymce form-control">{if isset($edit)}{if isset($smarty.post.$about_shop)}{$smarty.post.$about_shop}{else}{$mp_seller_info.about_shop.{$language.id_lang}}{/if}{else}{if isset($smarty.post.$about_shop)}{$smarty.post.$about_shop}{/if}{/if}
									</textarea>
								</div>
							{/foreach}
						</div>
					</div>
					{hook h="displayMpEditProfileInformationBottom"}
				</div>
				<div class="tab-pane fade in" id="wk-contact">
					<div class="form-group">
						<label for="address" class="col-lg-3 control-label">{l s='Address' mod='marketplace'}</label>
						<div class="col-lg-6">
							<div id="address_div">
								<textarea
								name="address"
								id="address" rows="4" cols="35"
								class="validate form-control">{if isset($edit)}{if isset($smarty.post.address)}{$smarty.post.address}{else}{$mp_seller_info.address}{/if}{else}{if isset($smarty.post.address)}{$smarty.post.address}{/if}{/if}</textarea>
							</div>
						</div>
					</div>
					<div class="form-group" id="seller_zipcode">
						<label for="postcode" class="col-lg-3 control-label {if Configuration::get('WK_MP_SELLER_COUNTRY_NEED')}required{/if}">{l s='Zip/Postal Code' mod='marketplace'}</label>
						<div class="col-lg-6">
							<input class="form-control-static"
							type="text"
							name="postcode"
							id="postcode"
							maxlength="10"
							value="{if isset($edit)}{if isset($smarty.post.postcode)}{$smarty.post.postcode}{else}{$mp_seller_info.postcode}{/if}{else}{if isset($smarty.post.postcode)}{$smarty.post.postcode}{/if}{/if}" />
						</div>
					</div>
					<div class="form-group">
						<label for="city" class="col-lg-3 control-label {if Configuration::get('WK_MP_SELLER_COUNTRY_NEED')}required{/if}">{l s='City' mod='marketplace'}</label>
						<div class="col-lg-6">
							<input class="form-control"
							type="text"
							value="{if isset($edit)}{if isset($smarty.post.city)}{$smarty.post.city}{else}{$mp_seller_info.city}{/if}{else}{if isset($smarty.post.city)}{$smarty.post.city}{/if}{/if}"
							name="city"
							id="city"
							maxlength="64" />
						</div>
					</div>
					<div class="form-group">
						<label for="id_country" class="col-lg-3 control-label {if Configuration::get('WK_MP_SELLER_COUNTRY_NEED')}required{/if}">{l s='Country' mod='marketplace'}</label>
						<div class="col-lg-6">
							{if isset($country)}
								<select name="id_country" id="id_country" class="form-control" >
									<option value="">{l s='Select Country' mod='marketplace'}</option>
									{foreach $country as $countrydetail}
										<option value="{$countrydetail.id_country}"
										{if isset($edit)}{if $mp_seller_info.id_country == $countrydetail.id_country}Selected="Selected"{/if}{/if}>
											{$countrydetail.name}
										</option>
									{/foreach}
								</select>
							{/if}
						</div>
					</div>
					<div id="wk_seller_state_div" class="form-group" {if isset($edit)}{if !$mp_seller_info.id_state}wk_display_none{/if}{else}wk_display_none{/if}>
						<label for="id_state" class="col-lg-3 control-label {if Configuration::get('WK_MP_SELLER_COUNTRY_NEED')}required{/if}">{l s='State' mod='marketplace'}</label>
						<div class="col-lg-6">
							<select name="id_state" id="id_state" class="form-control">
								<option value="0">{l s='Select State' mod='marketplace'}</option>
							</select>
							<input type="hidden" name="state_available" id="state_available" value="0" />
						</div>
					</div>
				</div>
				<div class="tab-pane fade in" id="wk-images">
					{if isset($edit)}
						{include file="$wkself/../../views/templates/front/seller/_partials/editprofile-images.tpl"}
					{else}
						<div class="alert alert-danger">
							{l s='You must save this seller before adding images.' mod='marketplace'}
						</div>
					{/if}
				</div>
				<div class="tab-pane fade in" id="wk-social">
					<div class="alert alert-info">
						{l s='Enter Social Profile User id’s to be displayed on Seller’s product page, profile page and shop page (Display of these will depend on the "Seller Social profile link" option selected/not selected by seller in ‘Permission’ Tab )' mod='marketplace'}
					</div>
					<div class="form-group">
						<label for="facebook_id" class="col-lg-3 control-label">{l s='Facebook ID' mod='marketplace'}</label>
						<div class="col-lg-6">
							<input class="form-control"
							type="text"
							value="{if isset($edit)}{if isset($smarty.post.facebook_id)}{$smarty.post.facebook_id}{else}{$mp_seller_info.facebook_id}{/if}{else}{if isset($smarty.post.facebook_id)}{$smarty.post.facebook_id}{/if}{/if}"
							name="facebook_id"
							id="facebook_id" />
						</div>
					</div>
					<div class="form-group">
						<label for="twitter_id" class="col-lg-3 control-label">{l s='Twitter ID' mod='marketplace'}</label>
						<div class="col-lg-6">
							<input class="form-control"
							type="text"
							value="{if isset($edit)}{if isset($smarty.post.twitter_id)}{$smarty.post.twitter_id}{else}{$mp_seller_info.twitter_id}{/if}{else}{if isset($smarty.post.twitter_id)}{$smarty.post.twitter_id}{/if}{/if}"
							name="twitter_id"
							id="twitter_id" />
						</div>
					</div>
					<div class="form-group">
						<label for="youtube_id" class="col-lg-3 control-label">{l s='Youtube ID' mod='marketplace'}</label>
						<div class="col-lg-6">
							<input class="form-control"
							type="text"
							value="{if isset($edit)}{if isset($smarty.post.youtube_id)}{$smarty.post.youtube_id}{else}{$mp_seller_info.youtube_id}{/if}{else}{if isset($smarty.post.youtube_id)}{$smarty.post.youtube_id}{/if}{/if}"
							name="youtube_id"
							id="youtube_id" />
						</div>
					</div>
					<div class="form-group">
						<label for="instagram_id" class="col-lg-3 control-label">{l s='Instagram ID' mod='marketplace'}</label>
						<div class="col-lg-6">
							<input class="form-control"
							type="text"
							value="{if isset($edit)}{if isset($smarty.post.instagram_id)}{$smarty.post.instagram_id}{else}{$mp_seller_info.instagram_id}{/if}{else}{if isset($smarty.post.instagram_id)}{$smarty.post.instagram_id}{/if}{/if}"
							name="instagram_id"
							id="instagram_id" />
						</div>
					</div>
				</div>
				<div class="tab-pane fade in" id="wk-permission">
					{if isset($selectedDetailsByAdmin) && $selectedDetailsByAdmin && Configuration::get('WK_MP_SHOW_SELLER_DETAILS')}
						<div class="alert alert-info">
							{l s='Select which details to be displayed for customers on Seller’s product page, Profile page and Shop page (subject to access provided by admin)' mod='marketplace'}
						</div>
						<div class="form-group">
				            <label class="control-label col-lg-3">
				                <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Select which details to be displayed for customers on Seller’s product page, Profile page and Shop page (subject to access provided by admin)' mod='marketplace'}">
				                    {l s='Seller Permission' mod='marketplace'}
				                </span>
				            </label>
				            <div class="col-lg-4">
				                <table class="table table-bordered">
				                    <thead>
				                        <tr>
				                            <th class="fixed-width-xs">
				                                <span class="title_box">
				                                    <input type="checkbox" name="checkme" id="checkme" onclick="checkDelBoxes(this.form, 'groupBox[]', this.checked)" />
				                                </span>
				                            </th>
				                            <th class="fixed-width-xs"><span class="title_box">{l s='ID' mod='marketplace'}</span></th>
				                            <th><span class="title_box">{l s='Permission name' mod='marketplace'}</span></th>
				                        </tr>
				                    </thead>
				                    <tbody>
				                        {foreach $selectedDetailsByAdmin as $key => $detailsVal}
				                            <tr>
				                                <td>
				                                    <input id="groupBox_{$detailsVal.id_group}" type="checkbox" name="groupBox[]" value="{$detailsVal.id_group}" class="groupBox" {if isset($edit)}{if isset($selectedDetailsBySeller) && in_array($detailsVal.id_group, $selectedDetailsBySeller)} checked {/if}{else} checked {/if}>
				                                </td>
				                                <td>{$detailsVal.id_group}</td>
				                                <td><label for="">{$detailsVal.name}</label></td>
				                            </tr>
				                        {/foreach}
				                    </tbody>
				                </table>
				            </div>
				        </div>
			        {else}
						<div class="alert alert-danger">
							{l s='You do not permit display of seller details.' mod='marketplace'}
						</div>
					{/if}
				</div>
				<div class="tab-pane fade in" id="wk-seller-payment-details">
					{if isset($mp_payment_option)}
						<div class="form-wrapper">
							<div class="form-group">
								<label for="payment_mode_id" class="col-lg-3 control-label">{l s='Payment Mode' mod='marketplace'}</label>
								<div class="col-lg-6">
									<select id="payment_mode_id" name="payment_mode_id" class="form-control">
										<option value="">{l s='--- Select Payment Mode ---' mod='marketplace'}</option>
										{foreach $mp_payment_option as $payment}
											<option id="{$payment.id_mp_payment}" value="{$payment.id_mp_payment}"
											{if isset($seller_payment_details)}{if $seller_payment_details.payment_mode_id == $payment.id_mp_payment}selected{/if}{/if}>{$payment.payment_mode}
											</option>
										{/foreach}
									</select>
									<div class="mp_payment_error"></div>
								</div>
							</div>
							<div class="form-group">
							    <label for="payment_detail" class="col-lg-3 control-label">{l s='Account Details' mod='marketplace'}</label>
							    <div class="col-lg-6">
							    	<textarea id="payment_detail" name="payment_detail" class="form-control" rows="4" cols="50">{if isset($seller_payment_details)}{$seller_payment_details.payment_detail}{/if}</textarea>
							    </div>
							</div>
						</div>
					{else}
						<div class="alert alert-info">
							{l s='There are no payment method yet.' mod='marketplace'}
						</div>
					{/if}
				</div>
				{hook h="displayMpEditProfileTabContent"}
			</div>
		</div>

		{if isset($edit)}
			{hook h="displayMpEditProfileFooter"}
		{else}
			{hook h="displayMpSellerRequestFooter"}
		{/if}
		<div class="panel-footer">
			<a href="{$link->getAdminLink('AdminSellerInfoDetail')}" class="btn btn-default">
				<i class="process-icon-cancel"></i>{l s='Cancel' mod='marketplace'}
			</a>
			<button type="submit" name="submitAdd{$table}" class="btn btn-default pull-right" id="mp_seller_save_button">
				<i class="process-icon-save"></i>{l s='Save' mod='marketplace'}
			</button>
			<button type="submit" name="submitAdd{$table}AndStay" class="btn btn-default pull-right" id="mp_seller_saveas_button">
				<i class="process-icon-save"></i> {l s='Save and stay' mod='marketplace'}
			</button>
		</div>
		{if isset($edit)}
			{hook h="displayUpdateMpSellerBottom"}
		{else}
			{hook h="displayAddMpSellerBottom"}
		{/if}
	</form>
</div>

<style type="text/css">
	.mce-tinymce{
		width : auto !important;
	}
</style>
<script type="text/javascript">
$(document).ready(function() {
    tinySetup({
        editor_selector: "about_business",
        width: 700
    });
});
</script>

{strip}
	{addJsDef path_uploader = $link->getAdminlink('AdminSellerInfoDetail')}
	{addJsDef path_sellerdetails = $link->getAdminlink('AdminSellerInfoDetail')}

	{addJsDef adminupload = 1}
	{addJsDef backend_controller = 1}
	{addJsDef iso = $iso}
	{addJsDef ad = $ad}
	{addJsDef pathCSS = $smarty.const._THEME_CSS_DIR_}
	{addJsDef multi_lang = $multi_lang}

	{if isset($edit)}
		{addJsDef actionIdForUpload = $mp_seller_info.id_seller}
		{addJsDef upload_single = 1}
		{addJsDef actionpage = 'seller'}
		{addJsDef deleteaction = ''}
		{addJsDef id_country = $mp_seller_info['id_country']}
		{addJsDef id_state = $mp_seller_info['id_state']}
		{addJsDef seller_default_img_path=$seller_default_img_path}
		{addJsDef shop_default_img_path=$shop_default_img_path}
	{else}
		{addJsDef actionIdForUpload = ''}
		{addJsDef id_country = 0}
		{addJsDef id_state = 0}
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
	{addJsDefL name=notmorethanone}{l s='You can not upload more than one image.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=selectstate}{l s='Select State' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=city_req}{l s='City is required.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=country_req}{l s='Country is required.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=state_req}{l s='State is required.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=req_shop_name_lang}{l s='Shop name is required in Default Language -' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=shop_name_exist_msg}{l s='Shop Unique name already taken. Try another.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=shop_name_error_msg}{l s='Shop name can not contain any special character except underscore. Try another.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=seller_email_exist_msg}{l s='Email Id already exist.' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=success_msg}{l s='Success' js=1 mod='marketplace'}{/addJsDefL}
	{addJsDefL name=error_msg}{l s='Error' js=1 mod='marketplace'}{/addJsDefL}
{/strip}