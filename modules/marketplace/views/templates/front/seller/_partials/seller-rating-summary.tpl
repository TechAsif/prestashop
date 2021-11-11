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

{if isset($sellerRating) && isset($totalReview)}
    <div id="wk_review_rating_container">
        <div class="col-md-4 col-sm-12">
            <div class="wk_seller_rating_box_heading">{l s='Seller Rating' mod='marketplace'}</div>
            <div class="wk_average_seller_rating_box">
                {* Display Rating Star *}
                <div id="seller_rating"></div>
                <div class="seller_rating_data">
                    {$sellerRating}
                </div>
                <div class="seller_rating_box_content">
                    {l s='Based On' mod='marketplace'}<br>
                    {$totalReview} {if $totalReview > 1}{l s='Reviews' mod='marketplace'}{else}{l s='Review' mod='marketplace'}{/if}
                </div>
            </div>
        </div>
        <div class="col-md-8 col-sm-12 wkseller_rating_table">
            {if $sellerRatingDetail}
                <table class="table">
                    <thead>
                        <tr>
                            <th>{l s='Rating' mod='marketplace'}</th>
                            <th style="width:52%;">{l s='Stats' mod='marketplace'}</th>
                            <th>{l s='Based On' mod='marketplace'}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$sellerRatingDetail item=rating key=key name=ratings}
                            <tr>
                                <td>{$rating.rating}{l s=' star' mod='marketplace'}</td>
                                <td>
                                    <div class="wk_progress" >
                                        <div class="wk_progress_bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width:{$rating.percent}%">
                                        </div>
                                    </div>
                                </td>
                                <td>{$rating.count} {if $rating.count > 1}{l s='Reviews' mod='marketplace'}{else}{l s='Review' mod='marketplace'}{/if}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            {/if}
        </div>
        <div class="clearfix"></div>
    </div>
{/if}