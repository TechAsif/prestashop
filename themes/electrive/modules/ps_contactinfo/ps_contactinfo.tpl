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

<div class="block-contact col-md-3 col-lg-3 col-xs-12 links wrapper">

  <div class="title clearfix hidden-md-up" data-toggle="collapse" data-target="#footer_contact">
    <span class="c-info h3">{l s='contact info' d='Shop.Theme.Catalog'}</span>
    <span class="float-xs-right">
      <span class="navbar-toggler collapse-icons">
        <i class="fa fa-plus add"></i>
        <i class="fa fa-minus remove"></i>
      </span>
    </span>
  </div>
  <span class="c-info hidden-sm-down">{l s='contact info' d='Shop.Theme.Catalog'}</span>

  <ul id="footer_contact" class="fthr collapse">
    <li class="block">
      <div class="icon"><svg width="20px" height="20px">
          <use xlink:href="#add"></use>
        </svg></div>
      <div class="data ad">{$contact_infos.address.formatted nofilter}</div>
    </li>

    {if $contact_infos.phone}
      <li class="block">
        <div class="icon"><svg width="20px" height="20px">
            <use xlink:href="#phone"></use>
          </svg></div>
        <div class="data">
          <a href="tel:{$contact_infos.phone}">{$contact_infos.phone}</a>
        </div>
        <div class="icon"><svg width="20px" height="20px">
            <use xlink:href="#phone"></use>
          </svg></div>
        <div class="data">
          <a href="tel:{$contact_infos.phone}">+8809666 757 779</a>
        </div>
      </li>
    {/if}

    {if $contact_infos.fax}
      <li class="block">
        <div class="icon"><svg width="21px" height="20px">
            <use xlink:href="#fax"></use>
          </svg></div>
        <div class="data">
          {$contact_infos.fax}
        </div>
      </li>
    {/if}
    {if $contact_infos.email}
      <li class="block">
        <div class="icon"><svg width="22px" height="22px">
            <use xlink:href="#mail"></use>
          </svg></div>
        <div class="data email ad">
          <a href="mailto:{$contact_infos.email}">{$contact_infos.email}</a>
        </div>
      </li>
    {/if}
    {block name='hook_footerAfter'}
      {hook h='displayFooterAfter'}
    {/block}
  </ul>

</div>