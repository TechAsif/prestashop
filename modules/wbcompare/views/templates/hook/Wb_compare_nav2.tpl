{**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
{if $comparator_max_item}
  <li class="hcom d-inline-block text-xs-center">
    <form method="post" action="{$link->getModuleLink('wbcompare', 'WbCompareProduct')|escape:'html':'UTF-8'}"
      class="compare-form">
      <a href="{$link->getModuleLink('wbcompare', 'WbCompareProduct', array(), true)|escape:'html':'UTF-8'}">
        <span class="wcom">

          <svg width="24" height="22" viewBox="0 0 24 22" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M3.44824 4.04971H7.73827C9.16251 4.04971 10.5192 3.44233 11.4678 2.37998L12.0105 1.77222L12.5533 2.37998C13.5019 3.44233 14.8585 4.04971 16.2828 4.04971H20.5728"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            <path
              d="M22.6618 18.3912L22.599 18.6529C22.3835 19.5524 21.5791 20.1867 20.6541 20.1867H18.6956H16.7371C15.8121 20.1867 15.0078 19.5524 14.7922 18.6529L14.7295 18.3912M15.9469 15.2169L18.6956 8.04968L21.4444 15.2169"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            <path
              d="M9.29262 18.3912L9.2299 18.6529C9.01432 19.5524 8.20999 20.1867 7.28498 20.1867H5.32648H3.36799C2.44297 20.1867 1.63865 19.5524 1.42306 18.6529L1.36035 18.3912M2.57773 15.2169L5.32648 8.04968L8.07523 15.2169"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>

          <span class="hidden-md-down"> {l s='compare' mod='wbcompare'}</span>
        </span>
        {* <button type="submit" class="btn btn-default button button-medium bt_compare" disabled="disabled">
			<span>{l s='Compare' mod='wbcompare'} (<strong class="total-compare-val">{$count_product}</strong>)<i class="icon-chevron-right right"></i></span>
		</button> *}
        <input type="hidden" name="compare_product_count" class="compare_product_count" value="{$count_product}" />
        <input type="hidden" name="compare_product_list" class="compare_product_list" value="" />
      </a>
    </form>
  </li>
{/if}