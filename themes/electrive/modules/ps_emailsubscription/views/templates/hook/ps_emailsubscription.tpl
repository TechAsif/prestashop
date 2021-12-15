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

<div class="newsc">
 <div class="block_newsletter row">  
    <div class="hidden-sm-down col-lg-5 col-sm-6 col-xs-12">
      <span class="d-inline-block">
        <h3 class="h3"><span>{l s='join our Newsletter now' d='Shop.Theme.Catalog'}</span></h3>
        {* <h4>{l s='Get our updates on new arrivals' d='Shop.Theme.Catalog'}</h4> *}
        {if $conditions}
          <h5 class="emsg hidden-sm-down">{$conditions}</h5>
        {/if}
      </span>
    </div> 
    <div class="fthr col-lg-7 col-sm-6 col-xs-12">
      {* <h3 class="h3">{l s='Newsletter' d='Shop.Theme.Catalog'}</h3>
     {if $conditions}
          <h5 class="emsg hidden-md-down">{$conditions}</h5>
        {/if} *}
      <form action="{$urls.pages.index}#footer" method="post">
            
            <div class="input-wrapper">
              <input
                name="email"
                type="email"
                value="{$value}"
                placeholder="{l s='Your email address' d='Shop.Forms.Labels'}"
                aria-labelledby="block-newsletter-label"
              >
            </div>
            <input
              class="btn btn-primary hidden-xs-down"
              name="submitNewsletter"
              type="submit"
              value="{l s='Subscribe' d='Shop.Theme.Actions'}"
            >
            <input
              class="btn btn-primary hidden-sm-up"
              name="submitNewsletter"
              type="submit"
              value="{l s='OK' d='Shop.Theme.Actions'}"
            >
            <input type="hidden" name="action" value="0">
            <div class="clearfix"></div>
          <div class="col-xs-12">
              {if $msg}
                <p class="alert {if $nw_error}alert-danger{else}alert-success{/if}">
                  {$msg}
                </p>
              {/if}
              {if isset($id_module)}
                {hook h='displayGDPRConsent' id_module=$id_module}
              {/if}
          </div>
      </form>
    </div>
</div>

</div>

