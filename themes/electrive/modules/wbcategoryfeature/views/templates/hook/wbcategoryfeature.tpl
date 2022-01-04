{*
* 2007-2019 PrestaShop
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
*  @copyright  2007-2019 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="category-back container">
    <div id="wb_category_feature" class="wb_category_feature clearfix">
        {if isset($wb_categories) && $wb_categories|@count > 0}
            <div class="list_carousel responsive">
                <div id="wb_cat_carousel"
                    class="product-list list-inline list-unstyled {if isset($wbconfig->used_slider) && $wbconfig->used_slider == 1}owl-carousel owl-theme{/if}">
                    {foreach from=$wb_categories item=item_category name=wb_categories}
                        {assign var=category value=$item_category.category}
                        {if $category->level_depth <= 2}
                            <div class="categorybr text-xs-center">
                                <a href="{$link->getCategoryLink($category->id_category, $category->link_rewrite)|escape:'html':'UTF-8'}"
                                    title="{$category->name|escape:'html':'UTF-8'}">
                                    {if $item_category.cat_thumb == 0}
                                        <img src="{$path_ssl|escape:'html':'UTF-8'}img/c/{$iso_code|escape:'html':'UTF-8'}.jpg"
                                            class="img-responsive center-block float-xs-right" />
                                    {else}
                                        <img src="{$path_ssl}img/c/{$category->id_category}.jpg" class="img-responsive center-block" />
                                    {/if}
                                    <h5>{$category->name|escape:'html':'UTF-8'}</h5>
                                </a>
                            </div>
                        {/if}
                    {/foreach}
                </div>
            </div>
        {/if}
    </div>
</div>