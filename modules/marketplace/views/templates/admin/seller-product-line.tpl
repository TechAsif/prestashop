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

<div class="clearfix modal-header" style="height:70px;">
    {if isset($frontcontroll)}
        {*<i class="material-icons">date_range</i>*}
    {else}
        <button type="button" class="close" data-dismiss="modal">&times;</button>
    {/if}
    <div class="col-xs-6 col-sm-8 box-stats color3 wktitle">
        {if isset($frontcontroll)}
            <h5>{l s='Order' mod='marketplace'} <strong>{$orderInfo.reference}</strong> {l s='from' mod='marketplace'} {$orderInfo.customer_name}</h5>
        {else}
            <h4>{l s='Order' mod='marketplace'} <strong>{$orderInfo.reference}</strong> {l s='from' mod='marketplace'} {$orderInfo.customer_name}</h4>
        {/if}
    </div>
    <div class="col-xs-6 col-sm-3 box-stats color3 wkdate">
        <div class="kpi-content" style="{if isset($frontcontroll)}font-size: 16px;padding-left:30px;{else}padding-left:30px;{/if}">
            <i class="material-icons">date_range</i>
            <span class="title">Date</span>
            <span class="value" style="{if isset($frontcontroll)}margin-left: 20px;float:left;{/if}">{$orderInfo.date|date_format:"%D"}</span>
        </div>
    </div>
</div>
<div class="modal-body">
    <div class="table-responsive">
        <table id="orderProducts" class="table">
            <thead>
                <tr>
                    <th><span class="title_box ">{l s='Product' mod='marketplace'}</span></th>
                    <th><span class="title_box ">{l s='Seller Amount' mod='marketplace'}</span></th>
                    <th><span class="title_box ">{l s='Seller Tax' mod='marketplace'}</span></th>
                    <th><span class="title_box ">{l s='Admin Commission' mod='marketplace'}</span></th>
                    <th><span class="title_box ">{l s='Admin Tax' mod='marketplace'}</span></th>
                    <th><span class="title_box ">{l s='Total' mod='marketplace'}</span></th>
                </tr>
            </thead>
            <tbody>
                {if isset($result)}
                    {foreach $result as $data}
                        <tr class="product-line-row">
                            <td class="text-center"><a target="_blank" href="{$data.product_link}"><span class="productName">{$data.product_name}</span><br></a></td>
                            <td class="text-center"><span>{$data.seller_amount}</span></td>
                            <td class="text-center">{$data.seller_tax}</td>
                            <td class="text-center">{$data.admin_commission}</td>
                            <td class="text-center">{$data.admin_tax}</td>
                            <td class="text-center">{$data.price_ti}</td>
                        </tr>
                    {/foreach}
                {/if}
            </tbody>
        </table>
    </div>
    <div class="clearfix model-footer">
        <a target="_blank" class="btn btn-primary pull-right" href="{$orderlink}">
            <span>{l s='View Order' mod='marketplace'}</span>
        </a>
    </div>
</div>
<style>
.product-line-row {
    height: 35px;
}

#wk_seller_product_line .wktitle h4,
#wk_seller_product_line .wkdate span.title,
#wk_seller_product_line .wkdate span.value {
    text-align: left;
}
</style>