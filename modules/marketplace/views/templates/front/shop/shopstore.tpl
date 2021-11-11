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

<div class="wk-mp-block">
	<div class="wk_profile_container">
		{if isset($shop_banner_path)}
			<img class="wk_banner_image" src="{$shop_banner_path}" alt="{l s='Banner' mod='marketplace'}"/>
		{/if}
	</div>
	<div class="wk_profile_container">
		<div class="wk_shop_left_bar">
			{block name='seller_image_block'}
				{include file='module:marketplace/views/templates/front/_partials/seller-image-block.tpl'}
			{/block}
			{hook h='displayMpShopLeftColumn'}
		</div>

		<div class="wk-mp-content">
			{if Configuration::get('WK_MP_SHOW_SELLER_DETAILS')}
				<div class="wk_profile_seller_name">
					<h1 class="col-md-12 text-uppercase">
						{$mp_seller_info.shop_name}
					</h1>
					<div class="clearfix"></div>
				</div>
				<div class="box-account">
					<div class="box-content">
						{if isset($WK_MP_SELLER_DETAILS_ACCESS_8)}
							<div class="wk_row">
								<label class="collection_label">{l s='Number of Products -' mod='marketplace'}</label>
								<label class="collection_products">
									{$sellerActiveProducts}
								</label>
								<div class="clearfix"></div>
							</div>
						{/if}

						{if isset($WK_MP_SELLER_DETAILS_ACCESS_5) && isset($mp_seller_info.about_shop) && $mp_seller_info.about_shop}
							<div class="wk_row">
								<label class="collection_label">{l s='About Shop -' mod='marketplace'}</label>
								<div class="clearfix"></div>
							</div>
							<div class="wk_about_shop">
								{$mp_seller_info.about_shop nofilter}
							</div>

							{hook h="displayMpShopDetailsBottom"}
						{/if}
						{hook h="displayExtraShopDetails"}

						{if isset($WK_MP_SELLER_DETAILS_ACCESS_1)}
							<div class="wk_profile_seller_name">
								<h1 class="col-md-8 text-uppercase">
									{$mp_seller_info.seller_firstname} {$mp_seller_info.seller_lastname}
								</h1>
								<div class="clearfix"></div>
							</div>
						{/if}

						{block name='mp_product_slider'}
							{include file='module:marketplace/views/templates/front/seller/_partials/seller-details.tpl'}
						{/block}
					</div>
				</div>
			{/if}
		</div>

		<div class="clearfix"></div>
	</div>

	{if isset($WK_MP_SELLER_DETAILS_ACCESS_8)}
		{block name='shopcollection'}
			{include file='module:marketplace/views/templates/front/shop/_partials/shopcollection.tpl'}
		{/block}
	{/if}
</div>
{/block}