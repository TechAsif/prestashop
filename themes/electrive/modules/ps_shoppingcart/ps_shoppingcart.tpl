{**
 * 2007-2018 PrestaShop
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
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
<div id="_desktop_cart" class="d-inline-block">
  <div class="dropdown js-dropdown">
    <div class="blockcart cart-preview {if $cart.products_count > 0}active{else}inactive{/if}"
      data-refresh-url="{$refresh_url}">
      <div class="header">

        <div class="hcart d-inline-block" data-toggle="dropdown">
          <div class="d-inline-block sbg">

            <svg width="23" height="22" viewBox="0 0 23 22" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M7.35449 20.4355C7.90678 20.4355 8.35449 19.9878 8.35449 19.4355C8.35449 18.8832 7.90678 18.4355 7.35449 18.4355C6.80221 18.4355 6.35449 18.8832 6.35449 19.4355C6.35449 19.9878 6.80221 20.4355 7.35449 20.4355Z"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              <path
                d="M18.3545 20.4355C18.9068 20.4355 19.3545 19.9878 19.3545 19.4355C19.3545 18.8832 18.9068 18.4355 18.3545 18.4355C17.8022 18.4355 17.3545 18.8832 17.3545 19.4355C17.3545 19.9878 17.8022 20.4355 18.3545 20.4355Z"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              <path
                d="M4.35437 4.44528H21.3544L19.7544 12.8353C19.6629 13.2957 19.4125 13.7092 19.0468 14.0036C18.6812 14.2979 18.2237 14.4543 17.7544 14.4453H8.03437C7.56507 14.4543 7.10755 14.2979 6.74192 14.0036C6.37628 13.7092 6.12582 13.2957 6.03437 12.8353L3.79455 1.64453H1.82227"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>


            <span class="carti"><span>{$cart.products_count}</span>
          </div>
          <div class="hdis text-xs-left">
            <span class="hidden-md-down userdess">{l s='My cart' d='Shop.Theme.Catalog'}</span>
            {* <span class="cartiva hidden-md-down">{$cart.totals.total.value}</span> *}
            {* <span class="hidden-md-down cit"> {l s='items' d='Shop.Theme.Catalog'} : </span><span class="cartiva hidden-md-down">{$cart.totals.total.value}</span> </span> *}
          </div>
        </div>

        <ul class="dropdown-menu dropdown-menu-right head-cart-drop">
          {block name='cart_detailed_product'}
            <li class="cart-det" data-refresh-url="{url entity='cart' params=['ajax' => true, 'action' => 'refresh']}">
              {if $cart.products}
                <ul class="cart-drop-table">
                  {foreach from=$cart.products item=product}
                    <div class="cart-down">
                      <!--  image-->
                      <li class="cart-img d-inline-block">
                        <img class="" src="{$product.cover.bySize.cart_default.url}" alt="{$product.name|escape:'quotes'}">
                      </li>
                      <div class="qtyp d-inline-block">
                        <!--  name -->
                        <li class="cart-name">
                          <a class="label name-cart2" href="{$product.url}"
                            data-id_customization="{$product.id_customization|intval}">{$product.name}</a>
                        </li>
                        <!--  qty -->
                        <li>
                          <span>{$product.quantity}</span>&nbsp;<span>x</span>&nbsp;
                          <!-- price -->
                          {if isset($product.is_gift) && $product.is_gift}
                            <span>{l s='Gift' d='Shop.Theme.Checkout'}</span>
                          {else}
                            <span>{$product.total}</span>
                          {/if}
                        </li>
                      </div>
                      <!-- delete -->
                      <li class="float-xs-right cartclose">
                        <a class="remove-from-cart" rel="nofollow" href="{$product.remove_from_cart_url}"
                          data-link-action="delete-from-cart" data-id-product="{$product.id_product|escape:'javascript'}"
                          data-id-product-attribute="{$product.id_product_attribute|escape:'javascript'}"
                          data-id-customization="{$product.id_customization|escape:'javascript'}">
                          {if !isset($product.is_gift) || !$product.is_gift}
                            <i class="fa fa-close"></i>
                          {/if}
                        </a>
                      </li>
                      <!-- total -->
                    </div>
                    {if $product.customizations|count >1}
                    <hr>{/if}
                  {/foreach}
                </ul>
                <div class="cart-action-container">
                  <div class="cart-logo hidden-md-down">
                    <svg width="47" height="41">
                      <use xlink:href="#lgLogo">
                    </svg>
                  </div>

                  <table class="cdroptable">
                    <tbody>
                      <tr>
                        <td class="text-xs-left">{l s='Total products:' d='Shop.Theme.Checkout'}</td>
                        <td class="text-xs-right">{$cart.subtotals.products.value}</td>
                      </tr>
                      <tr>
                        <td class="text-xs-left">{l s='Total shipping:' d='Shop.Theme.Checkout'}</td>
                        <td class="text-xs-right">{$cart.subtotals.shipping.value}
                          {hook h='displayCheckoutSubtotalDetails' subtotal=$cart.subtotals.shipping}</td>
                      </tr>
                      <tr>
                        <td class="text-xs-left">{l s='Total:' d='Shop.Theme.Checkout'}</td>
                        <td class="text-xs-right">{$cart.totals.total.value} {$cart.labels.tax_short}</td>
                      </tr>
                    </tbody>
                  </table>
                  <!-- checkout -->
                  <!--    <button type="button">{l s='Continue shopping' d='Shop.Theme.Actions'}</button> -->
                  <a href="{$cart_url}"
                    class="btn btn-primary btn-block float-xs-right">{l s='checkout' d='Shop.Theme.Actions'}</a>
                </div>
              {else}
                <p class="no-items">{l s='Your cart is empty!' d='Shop.Theme.Checkout'}</p>
              {/if}
            </li>
          {/block}
        </ul>
        <!--dropdown-->
      </div>
    </div>
  </div>
</div>