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

<a {if isset($seller_customer_id)}data-id-seller-customer="{$seller_customer_id}"{else}data-id-seller-customer="0"{/if}
data-id-order="{$id_order}"
title="{l s='View' mod='marketplace'}"
class="btn btn-default"
id="wk_order_detail_view"
href="javascript:void(0);">
    <i class="icon-search-plus"></i> {l s='View' mod='marketplace'}
</a>

<!--- Order Detail PopUp Box -->
<div class="modal fade" id="orderDetail" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" id="wk_seller_product_line"></div>
    </div>
</div>