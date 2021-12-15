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

<div class="container">


  {* <div class="pro-tab tabs text-capitalize text-xs-right">
     <h2 class="bold-second-word float-xs-left">{l s='top product' d='Shop.Theme.Catalog'}</h2>
     <ul class="list-inline nav nav-tabs">
         <li class="nav-item"><a class="nav-link active" href="#tab-fea-0"  data-toggle="tab">{l s='featured' d='Shop.Theme.Catalog'}</a></li><li class="nav-item tabl"><a class="nav-link" href="#tab-best-0"  data-toggle="tab">{l s='popular' d='Shop.Theme.Catalog'}</a></li><li class="nav-item"><a class="nav-link" href="#tab-new-0"  data-toggle="tab">{l s='New' d='Shop.Theme.Catalog'}</a></li>
     </ul>
 </div> *}

  <section class="clearfix next-prevb">

    <div class="home-heading">
      <h2 class="bold-second-word"><span>{l s='Best Sellers' d='Shop.Theme.Catalog'}</span></h2>
    </div>
    <div class="products row marginrow">
      <div id="owl-fea" class="owl-carousel owl-theme">
        {$num_row=1}
        <!-- Number of Row Ex 2,3,4,5....etc-->
        {$i=0}
        {if count($products) <= 10}
          {foreach from=$products item="product"}
            {include file="catalog/_partials/miniatures/product.tpl" product=$product}
          {/foreach}
        {else}
          {foreach from=$products item="product"}
            {if $i == 0}
              <ul>
                <li>
                {/if}
                {include file="catalog/_partials/miniatures/product.tpl" product=$product}
                {$i=$i+1}
                {if $i == $num_row}
                </li>
              </ul>
              {$i=0}
            {/if}
          {/foreach}
        {/if}
      </div>
    </div>
    {*  <a class="all-product-link float-xs-left float-md-right h4" href="{$allProductsLink}">
     {l s='All products' d='Shop.Theme.Catalog'}<i class="material-icons">&#xE315;</i>
   </a> *}
  </section>

</div>