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
    <div class="thumbnail-container text-xs-left">
      <div class="wb-image-block col-md-4 col-sm-5 col-xs-4">
       {block name='product_thumbnail'}
        {if $product.cover}
          <a href="{$product.url}" class="thumbnail product-thumbnail first-img">
            <img
              class = "center-block img-responsive"
              src="{$product.cover.bySize.home_default.url}"
              alt = "{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name|truncate:30:'...'}{/if}"
              data-full-size-image-url = "{$product.cover.large.url}"
              width="{$product.cover.bySize.small_default.width}" height="{$product.cover.bySize.small_default.height}"
            >
            {$count=0}
           {foreach from=$product.images item=image}
            {if $count==0}
              <img class="second-img img-responsive center-block"  
                src="{$image.bySize.home_default.url}"
                alt="{$image.legend}"
                title="{$image.legend}"
                itemprop="image"
                width="{$product.cover.bySize.small_default.width}" height="{$product.cover.bySize.small_default.height}"
              >
              {/if} {$count=$count+1}
        {/foreach}
          </a>
        {else}
          <a href="{$product.url}" class="thumbnail product-thumbnail">
            <img class="img-responsive center-block"
            src="{$urls.no_picture_image.bySize.home_default.url}"
            >
          </a>
        {/if}
             
               
             
          {/block}
       
          {hook h='displayProductPriceBlock' product=$product type='weight'}
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
      <div class="wb-product-desc product-description text-xs-left col-md-8 col-sm-7 col-xs-8">
        {block name='product_name'}
          {if $page.page_name == 'index'}
            <h3 class="h3 product-title" itemprop="name"><a href="{$product.url}">{$product.name}</a></h3>
          {else}
            <h2 class="h3 product-title" itemprop="name"><a href="{$product.url}">{$product.name}</a></h2>
          {/if}
        {/block}
        
        {block name='product_reviews'}
          {hook h='displayProductListReviews' product=$product}
        {/block}
        
       
        
        
        {block name='product_description_short'}
          <div id="product-description-short-{$product.id}" itemprop="description" class="listds">{$product.description_short nofilter}</div>
        {/block} 
        
        {block name='product_price_and_shipping'}
          {if $product.show_price}
            <div class="product-price-and-shipping-top">
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
        {/block}
        <div class="button-group all-to">
                  <button title="{l s='Wishlist' d='Shop.Theme.Catalog'}" class="wish" id="wishlist_button" onclick="WishlistCart('wishlist_block_list', 'add', '{$product.id_product|intval}', {$product.id_product_attribute}, 1); return false;"><svg width="18px" height="18px"><use xlink:href="#heart"></use></svg></button>
                  {hook h='displayCompareButton' product=$product}
                  {block name='quick_view'}
                    <a class="quick-view quick" title="{l s='Quick-view' d='Shop.Theme.Catalog'}" href="#" data-link-action="quickview">
                      <svg width="18px" height="17px"><use xlink:href="#bquick" /></svg>
                    </a>
                  {/block}
              <div class="add-cartt d-inline-block">
                <form action="{$urls.pages.cart}" method="post" class="add-to-cart-or-refresh">
                <input type="hidden" name="token" value="{$static_token}">
                <input type="hidden" name="id_product" value="{$product.id}" class="product_page_product_id">
                <input type="hidden" name="qty" value="1">
                {if $product.quantity < 1 }
                <button  data-toggle="tooltip" title="{l s='stock out' d='Shop.Theme.Catalog'}" data-button-action="add-to-cart" class="cartbt" disabled>
                    <svg width="18px" height="17px"><use xlink:href="#pcart"></use></svg>
                </button>
                {else}
                <button  data-toggle="tooltip" title="{l s='add to cart' d='Shop.Theme.Catalog'}" data-button-action="add-to-cart" class="cartbt">
                    <svg width="18px" height="19px"><use xlink:href="#pcart"></use></svg>
                </button>
                {/if}
                </form>
              </div> 
        </div>
      </div>

     

      

    </div>
  </article>
{/block}
