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

<ul id="custom-brands" class="connectedSortable">
  {if isset($custom_brands) }
    {foreach $custom_brands as $manufacturer}
      <li class="item-brand" id="{$manufacturer['id']}">
        <a href="{$manufacturer['link']}" title="{$manufacturer['name']}">
          <img src="{$manufacturer['image_url']}" alt="{$manufacturer['name']}" class="imgm img-thumbnail" />
        </a>
      </li>
    {/foreach}
  {/if}
</ul>