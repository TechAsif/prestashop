{**
* 2016-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2016-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{hook h='displayComNav2'}

<script type="text/javascript">
  var wishlistProductsIds = '';
  var baseDir ='{$content_dir}';
  var static_token='{$static_token}';
  var isLogged ='{$isLogged}';
  var loggin_required='{l s='You must be logged in to manage your wishlist.' mod='blockwishlist' js=1}';
  var added_to_wishlist ='{l s='The product was successfully added to your wishlist.' mod='blockwishlist' js=1}';
  var mywishlist_url='{$link->getModuleLink('blockwishlist', 'mywishlist', array(), true)|escape:'quotes':'UTF-8'}';
  {if isset($isLogged)&&$isLogged}
    var isLoggedWishlist = true;
  {else}
    var isLoggedWishlist = false;
  {/if}
</script>

<li class="wishl d-inline-block text-xs-center">
  <a class="wishtlist_top"
    href="{$link->getModuleLink('blockwishlist', 'mywishlist', array(), true)|escape:'html':'UTF-8'}">
    <span class="wimg">

      <svg width="24" height="21" viewBox="0 0 24 21" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
          d="M20.8499 3.30842C20.3391 2.79742 19.7327 2.39207 19.0652 2.1155C18.3978 1.83894 17.6824 1.69659 16.9599 1.69659C16.2374 1.69659 15.522 1.83894 14.8545 2.1155C14.1871 2.39207 13.5806 2.79742 13.0699 3.30842L12.0099 4.36842L10.9499 3.30842C9.91819 2.27673 8.51892 1.69713 7.05988 1.69713C5.60085 1.69713 4.20158 2.27673 3.16988 3.30842C2.13819 4.34011 1.55859 5.73939 1.55859 7.19842C1.55859 8.65745 2.13819 10.0567 3.16988 11.0884L4.22988 12.1484L12.0099 19.9284L19.7899 12.1484L20.8499 11.0884C21.3609 10.5777 21.7662 9.97123 22.0428 9.30377C22.3194 8.63632 22.4617 7.92091 22.4617 7.19842C22.4617 6.47593 22.3194 5.76052 22.0428 5.09306C21.7662 4.42561 21.3609 3.81918 20.8499 3.30842Z"
          stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      <span class="hidden-md-down">{l s='wishlist' mod='blockwishlist'}</span>
    </span>
    {* <span class="cart-wishlist-number">{$count_product}</span> *}
  </a>
</li>