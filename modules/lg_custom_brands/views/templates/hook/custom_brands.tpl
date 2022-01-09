{**
* DHL Deutschepost
*
* @author    silbersaiten <info@silbersaiten.de>
* @copyright 2020 silbersaiten
* @license   See joined file licence.txt
* @category  Module
* @support   silbersaiten <support@silbersaiten.de>
* @version   1.0.0
* @link      http://www.silbersaiten.de
*}
<div id="brandings">
  <div class="row" >
    <div class="col-md-4">
    <div class="brand_list">
      <h3>Top Brands</h3>
      <ul id="top-brands" class="">
        {if $top_brands && !empty($top_brands) }
          {foreach $top_brands as $top_brand}
            <li class="item-brand" id="{$top_brand['id']}">
              <a href="{$top_brand['link']}" title="{$top_brand['name']}">{$top_brand['name']}</a>
            </li>
          {/foreach}
        {/if}
      </ul>
      <h3>All Brands</h3>
      <ul id="all-brands" class="">
        {if $manufacturers && !empty($manufacturers) }
          {foreach $manufacturers as $manufacturer}
            <li class="item-brand" id="{$manufacturer['id']}">
              <a href="{$manufacturer['link']}" title="{$manufacturer['name']}">{$manufacturer['name']}</a>
            </li>
          {/foreach}
        {/if}
      </ul>
    </div>
    </div>
    <div class="col-md-8 ">
      <div class="js_nav_tabs">
        <div class="brand-navs ">
          <span class="js_tab_nav highlight" data-class='top-brandbox'>Top Brands</span>
          <span class="js_tab_nav" data-class='new-brandbox'>New Brands</span>
          <span class="js_tab_nav" data-class='featured-brandbox'>Featured Brands</span>
        </div>
        <div class="brand-boxes">
          <div class="top-brandbox brand-box" style="display: block;">
            <ul class="brand-contents">
              {if $top_brands && !empty($top_brands) }
                {foreach $top_brands as $manufacturer}
                  <li class="item-brand" id="{$manufacturer['id']}">
                    <a href="{$manufacturer['link']}" title="{$manufacturer['name']}">
                      <img src="{$manufacturer['image_url']}" alt="{$manufacturer['name']}" class="imgm img-thumbnail" />
                    </a>
                  </li>
                {/foreach}
              {/if}
            </ul>
          </div>
          <div class="new-brandbox brand-box">
            <ul class="brand-contents">
              {if $new_brands && !empty($new_brands) }
                {foreach $new_brands as $manufacturer}
                  <li class="item-brand" id="{$manufacturer['id']}">
                    <a href="{$manufacturer['link']}" title="{$manufacturer['name']}">
                      <img src="{$manufacturer['image_url']}" alt="{$manufacturer['name']}" class="imgm img-thumbnail" />
                    </a>
                  </li>
                {/foreach}
              {/if}
            </ul>
          </div>
          <div class="featured-brandbox brand-box">
            <ul class="brand-contents">
              {if $featured_brands && !empty($featured_brands) }
                {foreach $featured_brands as $manufacturer}
                  <li class="item-brand" id="{$manufacturer['id']}">
                    <a href="{$manufacturer['link']}" title="{$manufacturer['name']}">
                      <img src="{$manufacturer['image_url']}" alt="{$manufacturer['name']}" class="imgm img-thumbnail" />
                    </a>
                  </li>
                {/foreach}
              {/if}
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>