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
	<div class="wk-mp-block">
		{hook h="displayMpMenu"}
		<div class="wk-mp-content">
			<div class="page-title" style="background-color:{$title_bg_color};">
				<span style="color:{$title_text_color};">{l s='Transaction' mod='marketplace'}</span>
			</div>
			<div class="wk-mp-right-column">
				{block name='mporderdetails_message'}
					{include file="module:marketplace/views/templates/front/transaction/seller_total_detail.tpl"}
				{/block}
				<div class="box-account wk_seller_total">
					{block name='mporder_list'}
						{include file="module:marketplace/views/templates/front/transaction/seller_transaction.tpl"}
					{/block}
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
	</div>
{/block}