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

<div class="bootstrap lg-brand">
<div class="row">
  <div class="col-sm-12"> <p>Please drug the following brands to "Custom Selected Brands" section to display them in home page </p></div>
  <div class="col-sm-6">
    <h2>Available Brands</h2>
    <ul id="sortable1" class="connectedSortable">
      {if isset($manufacturers)}
        {foreach $manufacturers as $manufacturer}
          <li class="ui-state-default" id="{$manufacturer['id']}">{$manufacturer['name']}</li>
        {/foreach}
      {/if}
    </ul>
  </div>
  <div class="col-sm-6">
    <h2>Custom Selected Brands</h2>
    <ul id="sortable2" class="connectedSortable">
      {if isset($custom_brands) }
        {foreach $custom_brands as $manufacturer}
          <li class="ui-state-default" id="{$manufacturer['id']}">{$manufacturer['name']}</li>
        {/foreach}
      {/if}
    </ul>
  </div>
</div>
</div>