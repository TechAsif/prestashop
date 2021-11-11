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
{if isset($smarty.get.review_submitted)}
	<p class="alert alert-success">
		{l s='Thanks for the feedback. Review will be active after admin approval.' mod='marketplace'}
	</p>
{/if}
{if isset($smarty.get.review_submit_default)}
	<p class="alert alert-success">
		{l s='Thanks for the feedback.' mod='marketplace'}
	</p>
{/if}
{if isset($review_deleted)}
	<p class="alert alert-success">
		{l s='Your review has been deleted successfully.' mod='marketplace'}
	</p>
{/if}
<div class="wk-mp-block">
	<div class="wk_profile_container">
		{if isset($seller_banner_path)}
			<img class="wk_banner_image" src="{$seller_banner_path}" alt="{l s='Banner' mod='marketplace'}"/>
		{/if}
	</div>
	<div class="wk_profile_container">
		<div class="wk_profile_left_bar" {if !isset($seller_banner_path)}style="top:0px;"{/if}>
			{block name='seller_image_block'}
				{include file='module:marketplace/views/templates/front/_partials/seller-image-block.tpl'}
			{/block}
			{hook h='displayMpSellerProfileLeftColumn'}
		</div>
		<div class="wk-mp-content" style="background:none;box-shadow:none;">
			<div class="wk-seller-profile-box">
				<div class="wk_profile_seller_name">
					<h1 class="col-xs-12 col-sm-8 col-md-8 text-uppercase">
						{if isset($WK_MP_SELLER_DETAILS_ACCESS_1)}
							{$mp_seller_info.seller_firstname} {$mp_seller_info.seller_lastname}
						{/if}
					</h1>
					{if isset($current_seller_login) && $current_seller_login}
						<div class="col-xs-12 col-sm-4 col-md-4" style="text-align:right;">
							<a title="{l s='Edit Profile' mod='marketplace'}" href="{$link->getModuleLink('marketplace', 'editprofile')}">
								<button class="btn btn-primary btn-sm wk_edit_profile_btn">
									<i class="material-icons">&#xE254;</i>
									{l s='Edit Profile' mod='marketplace'}
								</button>
							</a>
						</div>
					{/if}
					<div class="clearfix"></div>
				</div>
				{block name='seller_details'}
					{include file='module:marketplace/views/templates/front/seller/_partials/seller-details.tpl'}
				{/block}
				{hook h="displaySellerProfileDetailBottom"}
				{block name='mp_product_slider'}
					{include file='module:marketplace/views/templates/front/_partials/mp-product-slider.tpl'}
				{/block}
			</div>
			{if Configuration::get('WK_MP_REVIEW_SETTINGS')}
				<div class="wk-seller-profile-box">
					{block name='seller_review_details'}
						{include file='module:marketplace/views/templates/front/seller/_partials/seller-review-details.tpl'}
					{/block}
				</div>
			{/if}
			{hook h='displayMpSellerProfileFooter'}
		</div>
		<div class="clearfix"></div>
	</div>
</div>
{block name='seller_review_form'}
	{include file='module:marketplace/views/templates/front/seller/_partials/seller-review-form.tpl'}
{/block}
{/block}