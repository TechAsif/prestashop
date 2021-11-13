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
{block name='product_miniature_item'}
  <article class="product-miniature js-product-miniature col-xs-12 propadding" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}" itemscope itemtype="http://schema.org/Product">
    <meta itemprop="brand" content="{if $product.manufacturer_name}{$product.manufacturer_name}{else}RMAG{/if}">
<meta itemprop="sku" content="{$product.reference}">
<meta itemprop="mpn" content="{$product.ean13}" />  
    <div class="thumbnail-container text-xs-center">
      <div class="wb-image-block col-xs-5 col-md-6 col-lg-5">
       {block name='product_thumbnail'}
        {if $product.cover}
          <a href="{$product.url}" class="thumbnail product-thumbnail first-img">
            <img
              class = "center-block img-responsive"
              src="{$product.cover.bySize.home_default.url}"
              alt = "{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name|truncate:30:'...'}{/if}"
              data-full-size-image-url = "{$product.cover.large.url}"
              width="{$product.cover.bySize.home_default.width}" height="{$product.cover.bySize.home_default.height}"
            >
            {$count=0}
           {foreach from=$product.images item=image}
            {if $count==0}
              <img class="second-img img-responsive center-block"  
                src="{$image.bySize.home_default.url}"
                alt="{$image.legend}"
                title="{$image.legend}"
                itemprop="image"
                width="{$product.cover.bySize.home_default.width}" height="{$product.cover.bySize.home_default.height}"
              >
              {/if} {$count=$count+1}
        {/foreach}
         {if $product.discount_type === 'percentage'}
                  <span class="discount-percentage discount-product">{$product.discount_percentage}</span>
                {elseif $product.discount_type === 'amount'}
                  <span class="discount-amount discount-product">{$product.discount_amount_to_display}</span>
                {/if}
                {block name='product_flags'}
            <ul class="product-flags">
              {foreach from=$product.flags item=flag}
                <li class="product-flag {$flag.type}">{$flag.label}</li>
              {/foreach}
            </ul>
          {/block}
          </a>
        {else}
         <a href="{$product.url}" class="thumbnail product-thumbnail">
            <img class="img-responsive center-block"
            src="{$urls.no_picture_image.bySize.home_default.url}"
            >
          </a>
        {/if}
             
               

          {/block}
           
          {* {if $product.has_discount}
            <span class="reducep hidden-md-down">{l s='reduced price' d='Shop.Theme.Catalog'}</span>
          {/if} *}
          
           {* <div class="highlighted-informations{if !$product.main_variants} no-variants{/if} hidden-md-down">
            {block name='product_variants'}
              {if $product.main_variants}
                {include file='catalog/_partials/variant-links.tpl' variants=$product.main_variants}
              {/if}
            {/block}
          </div> *}

        </div>

<div itemprop="offers" itemtype="http://schema.org/Offer" itemscope>
    <link itemprop="availability" href="{$product.seo_availability}"/>
    <meta itemprop="price" content={$product.price_amount}>
    <meta itemprop="description" content="{if $product.description_short}{$product.description_short}{else}{$product.name}{/if}">
    <meta itemprop="url" content="{$product.canonical_url}">
    <meta itemprop="itemCondition" content="New">
    <meta itemprop="priceCurrency" content="RON">
    <meta itemprop="priceValidUntil" content="{date('Y-m-d', strtotime('+120 days'))}" />
</div>
      <div class="wb-product-desc product-description col-xs-7 col-md-6 col-lg-7 text-xs-left">
       
        {block name='product_name'}
          {if $page.page_name == 'index'}
            <h3 class="h3 product-title" itemprop="name"><a href="{$product.url}">{$product.name}</a></h3>
          {else}
            <h2 class="h3 product-title" itemprop="name"><a href="{$product.url}">{$product.name}</a></h2>
          {/if}
        {/block}

        <div class="progre">
          {block name='product_reviews'}
            {hook h='displayProductListReviews' product=$product}
          {/block}
        </div>
        
        {block name='product_price_and_shipping'}
          {if $product.show_price}
            <div class="product-price-and-shipping">
              <span itemprop="price" class="price">{$product.price}</span>
              {if $product.has_discount}
                {hook h='displayProductPriceBlock' product=$product type="old_price"}

                <span class="sr-only">{l s='Regular price' d='Shop.Theme.Catalog'}</span>
                <span class="regular-price">{$product.regular_price}</span>
               
              {/if}

              {hook h='displayProductPriceBlock' product=$product type="before_price"}
              

              {hook h='displayProductPriceBlock' product=$product type='unit_price'}

            </div>
          {/if}
        {/block}<hr> 

        {block name='product_description_short'}
          <div id="product-description-short-{$product.id}" itemprop="description" class="listdss">{$product.description_short nofilter}</div>
        {/block}  
          {* <div class="pavilable">
                  {if $product.quantity > 0}
                    <div class="item-quantity text-xs-left">{l s='Available :' d='Shop.Theme.Catalog'} {$product.quantity|intval}</div>
                  {/if}
                  <div class="obar d-inline-block">
                  {if $product.quantity <= 50}
                    <div class="ibar bar1"></div>
                    {elseif $product.quantity <= 100}
                    <div class="ibar bar2"></div>
                    {elseif $product.quantity <= 150}
                    <div class="ibar bar3"></div>
                    {elseif $product.quantity <= 200}
                    <div class="ibar bar4"></div>
                    {else}
                    <div class="ibar bar5"></div>
                  {/if}

                  </div>
        </div> *}
        
          {hook h='displayProductPriceBlock' product=$product type='weight'}
          
          {* Winter Infotech Start 10-12-2020*}
        {if isset($product.flags['on-sale']) && isset($product.specific_prices.to) && ($product.specific_prices.to|date_format:"%Y/%m/%d" > $smarty.now|date_format:"%Y/%m/%d")}
          <div claas="wb_countdown">
          <div data-date="{$product.specific_prices.to|date_format}" class="wb_product_countdown">
            <div class="wb_countdown_days">
              <span class="wb_countdown_days_digit"></span>
              <p>{l s='Days' d='Shop.Theme.Catalog'}</p>
            </div>
            <div class="wb_countdown_hours">
              <span class="wb_countdown_hours_digit"></span>
              <p>{l s='Hours' d='Shop.Theme.Catalog'}</p>
            </div>
            <div class="wb_countdown_minutes">
              <span class="wb_countdown_minutes_digit"></span>
              <p>{l s='Min' d='Shop.Theme.Catalog'}</p>
            </div>
            <div class="wb_countdown_seconds">
              <span class="wb_countdown_seconds_digit"></span>
              <p>{l s='Sec' d='Shop.Theme.Catalog'}</p>
            </div>
          </div>
          </div>
        {/if}
        {* Winter Infotech End*}
        

            </div>

     

      

    </div>
  </article>
{/block}
