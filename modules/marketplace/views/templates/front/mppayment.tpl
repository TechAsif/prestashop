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

{extends file=$layout}
{block name='content'}
{if isset($smarty.get.deleted)}
	<p class="alert alert-success">
		<button data-dismiss="alert" class="close" type="button">×</button>
		{l s='Payment method deleted successfully' mod='marketplace'}
	</p>
{else if isset($smarty.get.edited)}
	<p class="alert alert-success">
		<button data-dismiss="alert" class="close" type="button">×</button>
		{l s='Payment method updated successfully' mod='marketplace'}
	</p>
{else if isset($smarty.get.created)}
	<p class="alert alert-success">
		<button data-dismiss="alert" class="close" type="button">×</button>
		{l s='Payment method created successfully' mod='marketplace'}
	</p>
{/if}

<div class="wk-mp-block">
	{hook h="displayMpMenu"}
	<div class="wk-mp-content">
		<div class="page-title" style="background-color:{$title_bg_color};">
			<span style="color:{$title_text_color};">{l s='Payment Details' mod='marketplace'}</span>
		</div>
		<div class="wk-mp-right-column">
			{if isset($seller_payment_details) && !isset($edit)}
				<div class="wk_product_list">
					<div>
						<label class="wk_payment_mode_heading">{l s='Payment Mode -' mod='marketplace'}</label>
						<span id="label_payment_mode" class="wk_payment_mode_details">
							{$seller_payment_details.payment_mode}
						</span>
						<div class="clearfix"></div>
					</div>
					<div>
						<label class="wk_payment_mode_heading">
							{l s='Account Details -' mod='marketplace'}
						</label>
						<span id="label_payment_mode_details" class="wk_payment_mode_details">
							{if $seller_payment_details.payment_detail !== ''}
								{$seller_payment_details.payment_detail}
							{else}
								<i>{l s='NA' mod='marketplace'}</i>
							{/if}
						</span>
						<div class="clearfix"></div>
					</div>
					<p class="wk_btn_payment_mode_edit">
						<a href="{$link->getModuleLink('marketplace', 'mppayment', ['id' => $seller_payment_details.id_customer_payment])}" class="pull-left">
							<button class="btn btn-primary btn-sm">
								<i class="material-icons">&#xE254;</i>{l s='Edit' mod='marketplace'}
							</button>
						</a>
						<a href="{$link->getModuleLink('marketplace', 'mppayment', ['id' => $seller_payment_details.id_customer_payment, 'delete_payment' => 1])}" class="pull-left">
							<button class="btn btn-primary btn-sm delete_mp_data">
								<i class="material-icons">&#xE872;</i>{l s='Delete' mod='marketplace'}
							</button>
						</a>
					</p>
				</div>
			{else}
				{if isset($mp_payment_option)}
					<div class="alert alert-info">
						{l s='Provide your account details to obtain payment from admin for your orders.' mod='marketplace'}
					</div>
					<form action="{if isset($edit)}{$link->getModuleLink('marketplace', 'mppayment', ['id' => $seller_payment_details.id_customer_payment])}{else}{$link->getModuleLink('marketplace', 'mppayment')}{/if}" method="post" class="form-horizontal" enctype="multipart/form-data" role="form" accept-charset="UTF-8,ISO-8859-1,UTF-16">
						<div class="form-wrapper">
							<div class="form-group">
								<label for="payment_mode_id" class="control-label required">{l s='Payment Mode' mod='marketplace'}</label>
								<div class="row">
									<div class="col-md-5">
										<select id="payment_mode_id" name="payment_mode_id" class="form-control form-control-select" required>
											<option value="">{l s='--- Select Payment Mode ---' mod='marketplace'}</option>
											{foreach $mp_payment_option as $payment}
												<option id="{$payment.id_mp_payment}" value="{$payment.id_mp_payment}"
												{if isset($edit)}{if $seller_payment_details.payment_mode_id == $payment.id_mp_payment}selected{/if}{/if}>{$payment.payment_mode}
												</option>
											{/foreach}
										</select>
									</div>
								</div>
							</div>
							<div class="form-group">
							    <label for="payment_detail" class="control-label">{l s='Account Details' mod='marketplace'}</label>
							    <textarea id="payment_detail" name="payment_detail" class="form-control" rows="4" cols="50">{if isset($edit)}{$seller_payment_details.payment_detail}{/if}</textarea>
							</div>

							<div class="form-group row">
								<div class="col-md-12 wk_text_right">
									<input type="hidden" id="customer_id" name="customer_id" value="{$customer_id}" />
									<button type="submit" name="submit_payment_details" id="submit_payment_details" class="btn btn-success wk_btn_extra form-control-submit">
										<span>{l s='Save' mod='marketplace'}</span>
									</button>
								</div>
							</div>
						</div>
					</form>
				{else}
					<div class="alert alert-info">
						{l s='Admin has not created any payment method yet' mod='marketplace'}
					</div>
				{/if}
			{/if}
			<div class="left full">
				{hook h="displayMpPaymentDetailBottom"}
			</div>
		</div>
	</div>
</div>
{/block}