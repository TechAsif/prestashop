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

<ul class="nav nav-tabs">
	<li class="nav-item">
		<a class="nav-link active" href="#wk-information" data-toggle="tab">
			<i class="material-icons">&#xE88E;</i>
			{l s='Information' mod='marketplace'}
		</a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="#wk-images" data-toggle="tab">
			<i class="material-icons">&#xE410;</i>
			{l s='Images' mod='marketplace'}
		</a>
	</li>
	{if Configuration::get('WK_MP_SELLER_PRODUCT_COMBINATION') && $permissionData.combinationPermission}
		<li class="nav-item">
			<a class="nav-link" href="#wk-combination" data-toggle="tab">
				<i class="material-icons">&#xE335;</i>
				{l s='Combination' mod='marketplace'}
			</a>
		</li>
	{/if}
	{if Configuration::get('WK_MP_PRODUCT_FEATURE') && $permissionData.featuresPermission}
		<li class="nav-item">
			<a class="nav-link" href="#wk-feature" data-toggle="tab">
				<i class="material-icons">&#xE885;</i>
				{l s='Features' mod='marketplace'}
			</a>
		</li>
	{/if}
	{if (Configuration::get('WK_MP_SELLER_ADMIN_SHIPPING') || Module::isEnabled('mpshipping')) && $permissionData.shippingPermission}
		<li class="nav-item">
			<a class="nav-link" href="#wk-product-shipping" data-toggle="tab">
				<i class="material-icons">&#xE558;</i>
				{l s='Shipping' mod='marketplace'}
			</a>
		</li>
	{/if}
	{if Configuration::get('WK_MP_SELLER_PRODUCT_SEO') && $permissionData.seoPermission}
		<li class="nav-item">
			<a class="nav-link" href="#wk-seo" data-toggle="tab">
				<i class="material-icons">&#xE83A;</i>
				{l s='SEO' mod='marketplace'}
			</a>
		</li>
	{/if}
	{if (Configuration::get('WK_MP_SELLER_PRODUCT_EAN') || Configuration::get('WK_MP_SELLER_PRODUCT_UPC') || Configuration::get('WK_MP_SELLER_PRODUCT_ISBN') || Configuration::get('WK_MP_SELLER_PRODUCT_VISIBILITY') || Configuration::get('WK_MP_SELLER_PRODUCT_AVAILABILITY')) && $permissionData.optionsPermission}
		<li class="nav-item">
			<a class="nav-link" href="#wk-options" data-toggle="tab">
				<i class="material-icons">&#xE8EF;</i>
				{l s='Options' mod='marketplace'}
			</a>
		</li>
	{/if}
	{hook h='displayMpProductNavTab'}
</ul>