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
{block name='header_banner'}
  <div class="header-banner">
    {hook h='displayBanner'}
  </div>
{/block}

{block name='header_nav'}
<nav class="header-nav">
  <div class="container">
    <div class="row bo-bottom">
          <div class="col-lg-5 col-md-4 col-sm-4 hidden-md-down head-left">
              {hook h='displayNav1'}
          </div>
          <div class="col-lg-7 col-md-8 col-sm-8 right-nav text-xs-right hidden-md-down">
              {hook h='displayNav2'}
              
          </div>
       
      </div>
      </div>
</nav>

{/block}

{block name='header_top'}

<div class="allhead">
  <div class="header-top">
  <div class="container">
<div class="row text-xs-right">
    <div class="col-xl-2 col-lg-2 col-md-2 col-sm-3 hidden-md-down head-logo-l">
      {if $page.page_name == 'index'}
        <h1>
          <a href="{$urls.base_url}">
            <img class="logo img-responsive" src="{$shop.logo}" alt="{$shop.name}">
          </a>
        </h1>
      {else}
          <a href="{$urls.base_url}">
            <img class="logo img-responsive" src="{$shop.logo}" alt="{$shop.name}">
          </a>
      {/if}
    </div>
    <div class="col-xl-10 col-lg-10 col-md-10 col-sm-12 col-xs-12 head-right">
      <div class="hidden-lg-up text-sm-center mobile">
           {* menu *}
            <div class="float-xs-left">
            <div id="menu-icon">
            <div class="navbar-header">
                <button type="button" class="btn-navbar navbar-toggle" data-toggle="collapse" onclick="openNav()">
                <i class="fa fa-bars"></i></button>
            </div>
            </div>
            <div id="mySidenav" class="sidenav">
            <div class="close-nav">
                <span class="categories">{l s='Category' d='Shop.Theme.Catalog'}</span>
                <a href="javascript:void(0)" class="closebtn float-xs-right" onclick="closeNav()"><i class="fa fa-close"></i></a>
            </div>
            <div id="mobile_top_menu_wrapper" class="row hidden-lg-up">
                <div class="js-top-menu mobile" id="_mobile_top_menu"></div>
            </div>
            </div>
            </div>
            {* menu *}
            <div class="top-logo float-xs-left" id="_mobile_logo">
              <h1>
                <a href="{$urls.base_url}">
                  <img class="logo img-responsive" src="{$shop.logo}" alt="{$shop.name}">
                </a>
              </h1>
             </div>
      </div>
      
      <div class="hidden-lg-up text-sm-center mobile mobilei">
            <div class="float-xs-right" id="_mobile_cart"></div>
            <div class="float-xs-right" id="_mobile_user_info"></div>
            <div class="float-xs-right xsse"></div>
            <div id="_mobile_currency_selector"></div>
          <div id="_mobile_language_selector"></div>
            <div class="clearfix"></div>
        </div>
        {hook h='displayTop'}

    </div>
</div>
</div>
</div>
</div>
<div class="topmenu">
<div class="container">
    <div class="row">
      {hook h='displayNavFullWidth'}
    </div>
  </div>
</div>
{/block}