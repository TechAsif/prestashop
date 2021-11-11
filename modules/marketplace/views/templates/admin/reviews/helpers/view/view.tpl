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

<div id="row">
	<div class="col-lg-12">
		<div class="panel">
			<div class="panel-heading">
				<i class="icon-info"></i> {l s='Detail Information' mod='marketplace'}
				<div class="panel-heading-action">
					<a href="{$current|escape:'html':'UTF-8'}&amp;viewwk_mp_seller_review&amp;id_review={$id_review|intval}&amp;token={$token|escape:'html':'UTF-8'}" class="btn btn-default">
						<i class="icon-list"></i>
						{l s='Back to list' mod='marketplace'}
					</a>
				</div>
			</div>
			{if isset($customer_name)}
				<p><strong>{l s='Customer Name' mod='marketplace'} :  </strong>{$customer_name|escape:'html':'UTF-8'}</p>
				<p><strong>{l s='Customer Email' mod='marketplace'} :  </strong>{$review_detail->customer_email|escape:'html':'UTF-8'}</p>
			{else}
				<p><strong>{l s='Customer' mod='marketplace'} :  </strong>{l s='As a guest' mod='marketplace'}</p>
			{/if}

			<p><strong>{l s='Seller Name' mod='marketplace'} :  </strong>{$obj_mp_seller->seller_name|escape:'html':'UTF-8'}</p>
			<p><strong>{l s='Seller Email' mod='marketplace'} :  </strong>{$obj_mp_seller->business_email|escape:'html':'UTF-8'}</p>
			<p>
				<strong>{l s='Rating' mod='marketplace'} :  </strong>
				{for $foo=1 to $review_detail->rating}
					<img src="{$module_dir|escape:'htmlall':'UTF-8'}marketplace/views/img/star-on.png" />
				{/for}
			</p>
			<p><strong>{l s='Customer Review' mod='marketplace'} :  </strong>{$review_detail->review|escape:'html':'UTF-8'}</p>
			<p><strong>{l s='Time' mod='marketplace'} :  </strong>{$review_detail->date_add|escape:'html':'UTF-8'}</p>
		</div>
	</div>
</div>
