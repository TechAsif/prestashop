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

<div class="modal fade" id="wk_review_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		{if $logged}
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        		<h4 class="modal-title" id="myModalLabel">
	        			{if isset($currenct_cust_review)}
							{l s='Edit review' mod='marketplace'}
						{else}
							{l s='Write a review' mod='marketplace'}
						{/if}
	        		</h4>
				</div>
				<form id="review_submit" method="post" action="{$link->getModuleLink('marketplace', 'sellerprofile', ['mp_shop_name' => $name_shop])}">
					<div class="modal-body">
						<div class="form-group">
							<label for="rating" class="control-label required">{l s='Rating' mod='marketplace'}</label>
							&nbsp;<span id="rating_image"></span>
							<div class="rating_error"></div>
						</div>
						<div class="form-group">
							<label for="comment">{l s='Description' mod='marketplace'}</label>
							<textarea class="form-control" name="feedback" style="height:150px;">{if isset($currenct_cust_review)}{$currenct_cust_review.review}{/if}</textarea>
							<input type="hidden" name="seller_id" value="{$seller_id}">
							<input type="hidden" name="review_id" value="{if isset($currenct_cust_review)}{$currenct_cust_review.id_review}{else}0{/if}">
						</div>
						{block name='mp-form-fields-notification'}
							{include file='module:marketplace/views/templates/front/_partials/mp-form-fields-notification.tpl'}
						{/block}
					</div>
					<div class="modal-footer">
						<div class="col-xs-6 col-sm-6 col-md-6" style="text-align:left">
				        	<button type="button" class="btn wk_btn_cancel wk_btn_extra" data-dismiss="modal">{l s='Cancel' mod='marketplace'}</button>
				        </div>
				        <div class="col-xs-6 col-sm-6 col-md-6">
				        	<button type="submit" name="submit_feedback" class="btn btn-success wk_btn_extra">{l s='Submit' mod='marketplace'}</button>
				        </div>
					</div>
				</form>
			</div>
		{else}
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        		<h4 class="modal-title" id="myModalLabel">
	        			{l s='Please login to write a review.' mod='marketplace'}
	        		</h4>
				</div>
				<div class="modal-footer">
			        <button type="button" class="btn wk_btn_cancel wk_btn_extra" data-dismiss="modal">{l s='Cancel' mod='marketplace'}</button>
			        <a href="{$myAccount}">
						<button type="button" class="btn btn-success wk_btn_extra">{l s='Login' mod='marketplace'}</button>
			        </a>
				</div>
			</div>
		{/if}
	</div>
</div>