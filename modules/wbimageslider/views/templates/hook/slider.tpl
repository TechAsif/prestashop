{*
* 2007-2018 PrestaShop
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
*  @copyright  2007-2018 PrestaShop SA
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="container">
<div class="row">
  <div class="menu-sp hidden-md-down col-xl-2 col-lg-2 col-md-3 col-sm-4"></div>
<div class="slider-banner col-sm-12 col-xs-12">
  <div class="s-panel">
    <div class="loader wrloader"></div>
    
      {if $wbslider.slides}
      <div class="homeslider-container slideshow-panel" data-interval="{$wbslider.speed}" data-wrap="{$wbslider.wrap}" data-pause="{$wbslider.pause}">
        <div class="slide-progress"></div>
        <ul id="owl-image-slider" class="image-slide owl-carousel owl-theme">
          {foreach from=$wbslider.slides item=slide}
            <li class="slide">
              <a href="{$slide.url}">
                <img src="{$slide.image_url}" alt="{$slide.legend}" class="img-responsive" />
              </a>
            </li>
          {/foreach}
        </ul>
      </div>
    {/if}
  </div>
</div>
</div>
</div>