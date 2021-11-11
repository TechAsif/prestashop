{*
* 2010-2021 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through LICENSE.txt file inside our module
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright 2010-2021 Webkul IN
* @license LICENSE.txt
*}

<div class="wk-collection-bottom">
  <div class="row">
    <div class="col-sm-4">
      {if ($n*$p) < $nb_products }
        {assign var='productShowing' value=$n*$p}
      {else}
          {assign var='productShowing' value=($n*$p-$nb_products-$n*$p)*-1}
      {/if}

      {if $p==1}
        {assign var='productShowingStart' value=1}
      {else}
        {assign var='productShowingStart' value=$n*$p-$n+1}
      {/if}

      {if $nb_products > 1}
        <p>{l s='Showing %1$d - %2$d of %3$d items' sprintf=[$productShowingStart, $productShowing, $nb_products] mod='marketplace'}</p>
      {else}
        <p>{l s='Showing %1$d - %2$d of 1 item' sprintf=[$productShowingStart, $productShowing] mod='marketplace'}</p>
      {/if}
    </div>
    <div class="col-sm-8 wk-collection-pagination">
      {$filterURL['mp_shop_name'] = $name_shop}
      <ul class="pagination">
        {if $p != 1}
          {$filterURL['p'] = $p-1}
          <li>
            <a class="page-link" href="{$link->getModuleLink('marketplace', 'shopstore', $filterURL)}" rel="prev">
              <i class="icon-chevron-left"></i> <b>{l s='Previous' mod='marketplace'}</b>
            </a>
          </li>
        {else}
          <li>
            <span class="page-link mp-disabled">
              <i class="icon-chevron-left"></i> <b>{l s='Previous' mod='marketplace'}</b>
            </span>
          </li>
        {/if}
        {if $start==3}
          <li>
            {$filterURL['p'] = 1}
            <a class="page-link" href="{$link->getModuleLink('marketplace', 'shopstore', $filterURL)}">
              <span>1</span>
            </a>
          </li>
          <li>
            {$filterURL['p'] = 2}
            <a class="page-link" href="{$link->getModuleLink('marketplace', 'shopstore', $filterURL)}">
              <span>2</span>
            </a>
          </li>
        {/if}
        {if $start==2}
          <li>
            {$filterURL['p'] = 1}
            <a class="page-link" href="{$link->getModuleLink('marketplace', 'shopstore', $filterURL)}">
              <span>1</span>
            </a>
          </li>
        {/if}
        {if $start>3}
          <li>
            {$filterURL['p'] = 1}
            <a class="page-link" href="{$link->getModuleLink('marketplace', 'shopstore', $filterURL)}">
              <span>1</span>
            </a>
          </li>
          <li class="truncate">
            ...
          </li>
        {/if}
        {section name=pagination start=$start loop=$stop+1 step=1}
          {if $p == $smarty.section.pagination.index}
            <li class="mp-page-active current">
              <span class="page-link">
                <span>{$p|escape:'html':'UTF-8'}</span>
              </span>
            </li>
          {else}
            <li>
              {$filterURL['p'] = $smarty.section.pagination.index}
              <a class="page-link" href="{$link->getModuleLink('marketplace', 'shopstore', $filterURL)}">
                <span>{$smarty.section.pagination.index|escape:'html':'UTF-8'}</span>
              </a>
            </li>
          {/if}
        {/section}
        {if $pagesNb>$stop+2}
          <li class="truncate">
            ...
          </li>
          <li>
            {$filterURL['p'] = $pagesNb}
            <a class="page-link" href="{$link->getModuleLink('marketplace', 'shopstore', $filterURL)}">
              <span>{$pagesNb|intval}</span>
            </a>
          </li>
        {/if}
        {if $pagesNb==$stop+1}
          <li>
            {$filterURL['p'] = $pagesNb}
            <a class="page-link" href="{$link->getModuleLink('marketplace', 'shopstore', $filterURL)}">
              <span>{$pagesNb|intval}</span>
            </a>
          </li>
        {/if}
        {if $pagesNb==$stop+2}
          <li>
            {$filterURL['p'] = $pagesNb-1}
            <a class="page-link" href="{$link->getModuleLink('marketplace', 'shopstore', $filterURL)}">
              <span>{$pagesNb-1|intval}</span>
            </a>
          </li>
          <li>
            {$filterURL['p'] = $pagesNb}
            <a class="page-link" href="{$link->getModuleLink('marketplace', 'shopstore', $filterURL)}">
              <span>{$pagesNb|intval}</span>
            </a>
          </li>
        {/if}
        {if $pagesNb > 1 AND $p != $pagesNb}
          {$filterURL['p'] = $p+1}
          <li>
            <a class="page-link" href="{$link->getModuleLink('marketplace', 'shopstore', $filterURL)}" rel="next">
              <b>{l s='Next' mod='marketplace'}</b> <i class="icon-chevron-right"></i>
            </a>
          </li>
        {else}
          <li>
            <span class="page-link mp-disabled">
              <b>{l s='Next' mod='marketplace'}</b> <i class="icon-chevron-right"></i>
            </span>
          </li>
        {/if}
      </ul>
    </div>
  </div>
</div>