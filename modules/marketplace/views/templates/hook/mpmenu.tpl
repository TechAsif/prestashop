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

<div class="wk_menu_item">
	{if $is_seller == 1}
		<div class="list_content">
			<ul>
				<li><span class="menutitle">{l s='Marketplace' mod='marketplace'}</span></li>
				<li {if $logic == 1}class="menu_active"{/if}>
					<span>
						<a href="{if isset($dashboard_link)}{$dashboard_link}{else}{$link->getModuleLink('marketplace', 'dashboard')|addslashes}{/if}">
							<i class="material-icons">&#xE871;</i>
							{l s='Dashboard' mod='marketplace'}
						</a>
					</span>
				</li>
				<li {if $logic == 2}class="menu_active"{/if}>
					<span>
						<a href="{if isset($edit_profile_link)}{$edit_profile_link}{else}{$link->getModuleLink('marketplace', 'editprofile')|addslashes}{/if}">
							<i class="material-icons">&#xE254;</i>
							{l s='Edit Profile' mod='marketplace'}
						</a>
					</span>
				</li>
				<li>
					<span>
						<a href="{if isset($seller_profile_link)}{$seller_profile_link}{else}{$link->getModuleLink('marketplace', 'sellerprofile', ['mp_shop_name' => $name_shop])|addslashes}{/if}">
							<i class="material-icons">&#xE851;</i>
							{l s='Seller Profile' mod='marketplace'}
						</a>
					</span>
				</li>
				<li>
					<span>
						<a href="{if isset($shop_link)}{$shop_link}{else}{$link->getModuleLink('marketplace', 'shopstore', ['mp_shop_name' => $name_shop])|addslashes}{/if}">
							<i class="material-icons">&#xE8D1;</i>
							{l s='Shop' mod='marketplace'}
						</a>
					</span>
				</li>
				<li {if $logic == 3}class="menu_active"{/if}>
					<span>
						<a href="{if isset($product_list_link)}{$product_list_link}{else}{$link->getModuleLink('marketplace', 'productlist')|addslashes}{/if}">
							<i class="material-icons">&#xE149;</i>
							{l s='Product' mod='marketplace'}
							<span class="wkbadge-primary" style="float:right;">{$totalSellerProducts}</span>
							<div class="clearfix"></div>
						</a>
					</span>
				</li>
				<li {if $logic == 4}class="menu_active"{/if}>
					<span>
						<a href="{if isset($my_order_link)}{$my_order_link}{else}{$link->getModuleLink('marketplace', 'mporder')|addslashes}{/if}">
							<i class="material-icons">&#xE8F6;</i>
							{l s='Orders' mod='marketplace'}
						</a>
					</span>
				</li>
				<li {if $logic == 5}class="menu_active"{/if}>
					<span>
						<a href="{if isset($my_transaction_link)}{$my_transaction_link}{else}{$link->getModuleLink('marketplace', 'mptransaction')|addslashes}{/if}">
							<i class="material-icons">swap_horiz</i>
							{l s='Transaction' mod='marketplace'}
						</a>
					</span>
				</li>
				<li {if $logic == 6}class="menu_active"{/if}>
					<span>
						<a href="{if isset($payment_detail_link)}{$payment_detail_link}{else}{$link->getModuleLink('marketplace', 'mppayment')|addslashes}{/if}">
							<i class="material-icons">&#xE8A1;</i>
							{l s='Payment Detail' mod='marketplace'}
						</a>
					</span>
				</li>
				{if Configuration::get('WK_MP_PRESTA_ATTRIBUTE_ACCESS')}
					<li {if $logic=='mp_prod_attribute'}class="menu_active"{/if}>
						<span>
							<a href="{$link->getModuleLink('marketplace', 'productattribute')}" title="{l s='Product Attributes' mod='marketplace'}">
								<i class="material-icons">&#xE839;</i>
								{l s='Product Attributes' mod='marketplace'}
							</a>
						</span>
					</li>
				{/if}
				{if Configuration::get('WK_MP_PRESTA_FEATURE_ACCESS')}
					<li {if $logic=='mp_prod_features'}class="menu_active"{/if}>
						<span>
							<a href="{$link->getModuleLink('marketplace', 'productfeature')}" title="{l s='Product Features' mod='marketplace'}">
								<i class="material-icons">&#xE8D0;</i>
								{l s='Product Features' mod='marketplace'}
							</a>
						</span>
					</li>
				{/if}

                {if $customer.id_default_group == "4"}
				<li {if $logic == 10}class="menu_active"{/if}>
					<span>
						<a href="{if isset($product_list_link)}{$product_list_link}{else}{$link->getModuleLink('marketplace', 'youroffers')|addslashes}{/if}">
							<i class="material-icons">&#xE149;</i>
							Your Offers
							<span class="wkbadge-primary" style="float:right;">{$totalSellerOffersProducts}</span>
							<div class="clearfix"></div>
						</a>
					</span>
				</li>
				{/if}
                

				{hook h="displayMPMenuBottom"}
			</ul>
		</div>
	{else}
		{hook h="displayMPStaffMenu"}
	{/if}
</div>