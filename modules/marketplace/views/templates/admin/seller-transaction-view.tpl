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
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <div class="col-xs-6 col-sm-8 box-stats color3">
        <h4>{l s='Settlement Transaction' mod='marketplace'} </h4>
    </div>
    <div class="col-xs-6 col-sm-3 box-stats color3">
        <div class="kpi-content" style="padding-left:30px;">
            <i class="icon-calendar-empty"></i>
            <span class="title">{l s='Date' mod='marketplace'}</span>
            <span class="value">{if isset($objTransaction->date_add)}{$objTransaction->date_add|date_format:"%D"}{/if}</span>
        </div>
    </div>
</div>
<div class="clearfix modal-body">
    <div class="clearfix form-group">
        <label class="col-lg-4 control-label">{l s='Payment Method:' mod='marketplace'}</label>
        <div class="col-lg-5">
            <p class="form-control-static">
                {if isset($objTransaction->payment_method) && $objTransaction->payment_method}{$objTransaction->payment_method|escape:'htmlall':'UTF-8'}{else}{l s='N/A' mod='marketplace'}{/if}
            </p>
        </div>
    </div>
    <div class="clearfix form-group">
        <label class="col-lg-4 control-label">{l s='Payment Details:' mod='marketplace'}</label>
        <div class="col-lg-5">
            <p class="form-control-static">
                {if isset($payment_mode_details) && $payment_mode_details}{$payment_mode_details|escape:'htmlall':'UTF-8'}{else}N/A{/if}
            </p>
        </div>
    </div>
    <div class="clearfix form-group">
        <label class="col-lg-4 control-label">
            {l s='Transaction ID:' mod='marketplace'}
        </label>
        <div class="col-lg-5">
            <p class="form-control-static">
                {if isset($objTransaction->id_transaction) && $objTransaction->id_transaction}{$objTransaction->id_transaction|escape:'htmlall':'UTF-8'}{else}{l s='N/A' mod='marketplace'}{/if}
            </p>
        </div>
    </div>
    <div class="clearfix form-group">
        <label class="col-lg-4 control-label">
            {l s='Remark:' mod='marketplace'}
        </label>
        <div class="col-lg-5">
            <p class="form-control-static">
                {if isset($objTransaction->remark) && $objTransaction->remark}{$objTransaction->remark|escape:'htmlall':'UTF-8'}{else}{l s='N/A' mod='marketplace'}{/if}
            </p>
        </div>
    </div>
    <div class="clearfix form-group">
        <label class="col-lg-4 control-label required">{l s='Amount:' mod='marketplace'}</label>
        <div class="col-lg-5">
            <p class="form-control-static">
                {if isset($amount)}{$amount|escape:'htmlall':'UTF-8'}{/if}
            </p>
        </div>
    </div>
    {hook h='displayExtraTransactionDetail' id_transaction_history=$objTransaction->id}

    {if !isset($frontcontroll)}
        {if $objTransaction->seller_receive > 0 && $objTransaction->status != 3}
            <div class="clearfix">
                <form method="POST" action="">
                    <input
                        type="hidden"
                        name="wk_id_settlement"
                        value="{if isset($objTransaction->id)}{$objTransaction->id|escape:'htmlall':'UTF-8'}{/if}"/>
                    {if isset($objTransaction->status) && $objTransaction->status == 2}
                        <button
                            disabled="disabled"
                            type="submit"
                            class="btn btn-primary pull-right"
                            name="wk_settlement_canceled">{l s='Canceled' mod='marketplace'}
                        </button>
                    {else}
                        <button
                            type="submit"
                            class="btn btn-primary pull-right"
                            name="wk_settlement_cancel">{l s='Cancel settlement' mod='marketplace'}
                        </button>
                    {/if}
                </form>
            </div>
        {/if}
    {/if}
</div>