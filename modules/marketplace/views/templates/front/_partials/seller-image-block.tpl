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

<div class="wk_profile_img">
	<a {if isset($seller_img_exist)}class="mp-img-preview" href="{$seller_img_path}"{/if}>
		<img class="wk_left_img" src="{$seller_img_path}?time={$timestamp}" alt="{l s='Image' mod='marketplace'}"/>
	</a>
</div>
<div class="wk_profile_img_belowlink">
	{if isset($sellerprofile)}
		<a class="wk_anchor_links" href="{$link->getModuleLink('marketplace','shopstore',['mp_shop_name' => $name_shop])}">
			<div class="wk_profile_left_display">
				<span>
					<i class="material-icons">&#xE8D1;</i> {$mp_seller_info.shop_name}
				</span>
			</div>
		</a>
	{else}
		<a class="wk_anchor_links" href="{$link->getModuleLink('marketplace','sellerprofile',['mp_shop_name' => $name_shop])}">
			<div class="wk_profile_left_display">
				<span>
					<i class="material-icons">&#xE851;</i>
					{if isset($WK_MP_SELLER_DETAILS_ACCESS_1)}
						{$mp_seller_info.seller_firstname} {$mp_seller_info.seller_lastname}
					{else}
						{l s='Seller Profile' mod='marketplace'}
					{/if}
				</span>
			</div>
		</a>
	{/if}

	{if isset($WK_MP_SELLER_DETAILS_ACCESS_7)}
		<a href="#wk_question_form" class="wk_anchor_links open-question-form" data-toggle="modal" data-target="#myModal" title="{l s='Contact Seller' mod='marketplace'}">
			<div class="wk_profile_left_display">
				<span>
					<i class="material-icons">&#xE0D0;</i> {l s='Contact Seller' mod='marketplace'}
				</span>
			</div>
		</a>
		{block name='product_images_modal'}
			{include file='module:marketplace/views/templates/front/_partials/contact-seller-form.tpl'}
	    {/block}
	{/if}

    {hook h='displayMpSellerImageBlockFooter'}
</div>
{block name='mp_image_preview'}
	{include file='module:marketplace/views/templates/front/product/_partials/mp-image-preview.tpl'}
{/block}