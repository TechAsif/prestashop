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

{hook h="displayMpMyAccountMenuTop"}
<h1 style="text-align: center;">{l s='Your Shop' mod='marketplace'}</h1>
<p style="text-align: center" class="info-account">{l s='Here you can manage marketplace shop.' mod='marketplace'}</p>
{if $is_seller == 1}
	<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" title="{l s='Dashboard' mod='marketplace'}" href="{if isset($dashboard_link)}{$dashboard_link}{else}{$link->getModuleLink('marketplace', 'dashboard')}{/if}">
		<span class="link-item">
			<i class="material-icons">&#xE871;</i>
			{l s='Dashboard' mod='marketplace'}
		</span>
	</a>
	<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" title="{l s='Edit Profile' mod='marketplace'}" href="{if isset($edit_profile_link)}{$edit_profile_link}{else}{$link->getModuleLink('marketplace', 'editprofile')}{/if}">
		<span class="link-item">
			<i class="material-icons">&#xE254;</i>
			{l s='Edit Profile' mod='marketplace'}
		</span>
	</a>
	<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" title="{l s='Seller Profile' mod='marketplace'}" href="{if isset($seller_profile_link)}{$seller_profile_link}{else}{$link->getModuleLink('marketplace', 'sellerprofile', ['mp_shop_name' => $name_shop])}{/if}">
		<span class="link-item">
			<i class="material-icons">&#xE851;</i>
			{l s='Seller Profile' mod='marketplace'}
		</span>
	</a>
	<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" title="{l s='View Shop' mod='marketplace'}" href="{if isset($shop_link)}{$shop_link}{else}{$link->getModuleLink('marketplace', 'shopstore', ['mp_shop_name' => $name_shop])}{/if}">
		<span class="link-item">
			<i class="material-icons">&#xE8D1;</i>
			{l s='Shop' mod='marketplace'}
		</span>
	</a>
	<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" title="{l s='Product List' mod='marketplace'}" href="{if isset($product_list_link)}{$product_list_link}{else}{$link->getModuleLink('marketplace', 'productlist')}{/if}">
		<span class="link-item">
			<i class="material-icons">&#xE149;</i>
			{l s='Product' mod='marketplace'}
		</span>
	</a>
	<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" title="{l s='Orders' mod='marketplace'}" href="{if isset($my_order_link)}{$my_order_link}{else}{$link->getModuleLink('marketplace', 'mporder')}{/if}">
		<span class="link-item">
			<i class="material-icons">&#xE8F6;</i>
			{l s='Orders' mod='marketplace'}
		</span>
	</a>
	<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" title="{l s='Transaction' mod='marketplace'}" href="{if isset($my_transaction_link)}{$my_transaction_link}{else}{$link->getModuleLink('marketplace', 'mptransaction')}{/if}">
		<span class="link-item">
			<i class="material-icons">swap_horiz</i>
			{l s='Transaction' mod='marketplace'}
		</span>
	</a>
	<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" title="{l s='Payment Detail' mod='marketplace'}" href="{if isset($payment_detail_link)}{$payment_detail_link}{else}{$link->getModuleLink('marketplace', 'mppayment')}{/if}">
		<span class="link-item">
			<i class="material-icons">&#xE8A1;</i>
			{l s='Payment Detail' mod='marketplace'}
		</span>
	</a>

	{if $customer.id_default_group == "4"}
           <a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" title="{l s='Payment Detail' mod='marketplace'}" href="{if isset($payment_detail_link)}{$payment_detail_link}{else}{$link->getModuleLink('marketplace', 'youroffers')}{/if}">
				<span class="link-item">
					<i class="material-icons">local_offer</i>
					Your Offers
				</span>
	      </a>
	{/if}	  	  		

	{if Configuration::get('WK_MP_PRESTA_ATTRIBUTE_ACCESS')}
		<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" href="{$link->getModuleLink('marketplace', 'productattribute')}" title="{l s='Product Attributes' mod='marketplace'}">
			<span class="link-item">
				<i class="material-icons">&#xE839;</i>
				<span>{l s='Product Attributes' mod='marketplace'}</span>
			</span>
		</a>
	{/if}
	{if Configuration::get('WK_MP_PRESTA_FEATURE_ACCESS')}
		<a href="{$link->getModuleLink('marketplace', 'productfeature')}" title="{l s='Product Features' mod='marketplace'}" class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
			<span class="link-item">
				<i class="material-icons">&#xE8D0;</i>
				<span>{l s='Product Features' mod='marketplace'}</span>
			</span>
		</a>
	{/if}
	{hook h="displayMpMyAccountMenuActiveSeller"}
{else if $is_seller == 0}
	{if isset($mpSellerShopSettings) && $mpSellerShopSettings && $shop_approved}
		<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" title="{l s='Re-Activate Your Shop' mod='marketplace'}" href="{$link->getModuleLink('marketplace', 'editprofile', ['reactivate' => 1])}">
			<span class="link-item">
				<i class="material-icons">&#xE871;</i>
				{l s='Re-Activate Your Shop' mod='marketplace'}
			</span>
		</a>
	{else}
		<div class="col-md-12">
			<div class="alert alert-info" role="alert">
				{l s='Your request has been already sent to admin. Please wait for admin approval' mod='marketplace'}
			</div>
		</div>
	{/if}


	{hook h="displayMpMyAccountMenuInactiveSeller"}
{else if $is_seller == -1}
	<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" title="{l s='Click Here for Seller Request' mod='marketplace'}" href="{$link->getModuleLink('marketplace', 'sellerrequest')}">
		<span class="link-item">
			<i class="material-icons">&#xE15E;</i>
			{l s='Click Here for Seller Request' mod='marketplace'}
		</span>
	</a>
	{hook h="displayMpMyAccountMenuSellerRequest"}
{/if}
