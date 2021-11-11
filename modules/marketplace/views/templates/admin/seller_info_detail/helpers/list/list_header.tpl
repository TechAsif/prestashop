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

{extends file="helpers/list/list_header.tpl"}
{block name=leadin}
<div class="modal fade" id="reason" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				 <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        	<h4 class="modal-title" id="myModalLabel">{l s='Why are you deactivating' mod='marketplace'} <span class="wk_action_name"></span>?</h4>
	        </div>
			<form id="reason-form" action="{$link->getAdminLink('AdminSellerInfoDetail')}" method="post">
				<div class="modal-body">
					<input type="hidden" name="actionId_for_reason" id="actionId_for_reason"/>
					<div class="row">
						<label class="col-lg-12 control-label reason_error" style="color:red;display:none;">{l s='Please write a reason' mod='marketplace'}</label>
						<label class="col-lg-12 control-label char_error" style="color:red;display:none;">{l s='Reason must be more than 10 characters' mod='marketplace'}</label>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<textarea id="reason_text" name="reason_text"></textarea>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary" name="submit_btn" id="reason-ok"><span>{l s='Submit' mod='marketplace'}</span></button>
					<button type="button" id="reason-anyway" class="btn btn-primary" data-dismiss="modal"><span>{l s='Deactivate anyway' mod='marketplace'}</span></button>
				</div>
			</form>
		</div>
	</div>
</div>
{/block}