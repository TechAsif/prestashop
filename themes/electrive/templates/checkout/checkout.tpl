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
<!doctype html>
<html lang="{$language.iso_code}">

  <head>
    {block name='head'}
      {include file='_partials/head.tpl'}
    {/block}
  </head>

    <body id="{$page.page_name}" class="{$page.body_classes|classnames} {if isset($WB_mainLayout)}{$WB_mainLayout}{/if} {if isset($WB_darklightLayout)}{$WB_darklightLayout}{/if} {if isset($WB_showDarkLightMenu)}{$WB_showDarkLightMenu}{/if}">
{if isset($WB_showPanelTool) && $WB_showPanelTool}
  {include file="modules/wbthemecustomizer/views/templates/front/colortool.tpl"}
{/if}
{if isset($WB_showDarkLightMenu) && $WB_showDarkLightMenu}
  {include file="modules/wbthemecustomizer/views/templates/front/wbdarklight.tpl"}
{/if}

    {block name='hook_after_body_opening_tag'}
      {hook h='displayAfterBodyOpeningTag'}
    {/block}
<main>
    <header id="header">
      {block name='header'}
        {include file='_partials/header.tpl'}
      {/block}
    </header>

    {block name='notifications'}
      {include file='_partials/notifications.tpl'}
    {/block}

    <section id="wrapper">
      {* {hook h="displayWrapperTop"} *}
      <div class="container">

        

      {block name='content'}
        <section id="content">
          <div class="row">
            {block name='breadcrumb'}
            {include file='_partials/breadcrumb.tpl'}
          {/block}
            <div class="col-md-8 col-xs-12">
              {block name='cart_summary'}
                {render file='checkout/checkout-process.tpl' ui=$checkout_process}
              {/block}
            </div>
            <div class="col-md-4 col-xs-12">

              {block name='cart_summary'}
                {include file='checkout/_partials/cart-summary.tpl' cart = $cart}
              {/block}
            </div>
          </div>
        </section>
      {/block}
      </div>

      {hook h="displayWrapperBottom"}
    </section>

    <footer id="footer">
      {block name='footer'}
        {include file='_partials/footer.tpl'}
      {/block}
    </footer>

    {block name='javascript_bottom'}
      {include file="_partials/javascript.tpl" javascript=$javascript.bottom}
    {/block}
</main>
    {block name='hook_before_body_closing_tag'}
      {hook h='displayBeforeBodyClosingTag'}
    {/block}

  </body>

</html>
