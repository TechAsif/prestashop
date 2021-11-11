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

{if isset($customer_id) && $customer_id == 0}
<div class="alert alert-danger">
	<p>{l s='This seller has been removed by admin from prestashop.' mod='marketplace'}</p>
</div>
{/if}
<div id="mp-container-customer">
	<div class="row">
		<div class="col-lg-6">
			<div class="panel clearfix">
				{if isset($mp_seller)}
					<div class="panel-heading">
						<i class="icon-user"></i>
						{$mp_seller.seller_firstname} {$mp_seller.seller_lastname} -
						<a href="mailto:{$mp_seller.business_email}">
							<i class="icon-envelope"></i>
							{$mp_seller.business_email}
						</a>
						<div class="panel-heading-action">
							<a href="{$current}&amp;updatewk_mp_seller&amp;id_seller={$mp_seller.id_seller|intval}&amp;token={$token}" class="btn btn-default">
								<i class="icon-edit"></i>
								{l s='Edit' mod='marketplace'}
							</a>
						</div>
					</div>
					<div class="form-horizontal">
						<div class="row">
							<label class="control-label col-lg-3">{l s='Social Title' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">{if $gender->name}{$gender->name}{else}{l s='Unknown' mod='marketplace'}{/if}</p>
							</div>
						</div>
						<div class="row">
							<label class="control-label col-lg-3">{l s='Registration Date' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">{dateFormat date=$mp_seller.date_add full=1}</p>
							</div>
						</div>
						<div class="row">
							<label class="control-label col-lg-3">{l s='Unique Shop Name' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">
									{if $mp_seller.active}
										<a href="{$link->getModuleLink('marketplace', 'shopstore', ['mp_shop_name' => $mp_seller.mp_shop_rewrite])}" target="_balnk" title="{l s='View Shop' mod='marketplace'}">
											{$mp_seller.shop_name_unique}
										</a>
									{else}
										{$mp_seller.shop_name_unique}
									{/if}
								</p>
							</div>
						</div>
						<div class="row">
							<label class="control-label col-lg-3">{l s='Default Language' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">{$mp_seller.default_lang}</p>
							</div>
						</div>
						<div class="row">
							<label class="control-label col-lg-3">{l s='Shop Name' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">{$mp_seller.shop_name}</p>
							</div>
						</div>
						<div class="row">
							<label class="control-label col-lg-3">{l s='Phone' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">{$mp_seller.phone}</p>
							</div>
						</div>
						{if $mp_seller.fax != ''}
						<div class="row">
							<label class="control-label col-lg-3">{l s='Fax' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">{$mp_seller.fax}</p>
							</div>
						</div>
						{/if}
						{if $mp_seller.tax_identification_number != ''}
						<div class="row">
							<label class="control-label col-lg-3">{l s='Tax Identification Number' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">{$mp_seller.tax_identification_number}</p>
							</div>
						</div>
						{/if}
						{if $mp_seller.address != ''}
						<div class="row">
							<label class="control-label col-lg-3">{l s='Address' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">{$mp_seller.address}</p>
							</div>
						</div>
						{/if}
						{if $mp_seller.postcode != ''}
						<div class="row">
							<label class="control-label col-lg-3">{l s='Zip/Postal Code' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">{$mp_seller.postcode}</p>
							</div>
						</div>
						{/if}
						{if $mp_seller.city != ''}
						<div class="row">
							<label class="control-label col-lg-3">{l s='City' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">{$mp_seller.city}</p>
							</div>
						</div>
						{/if}
						{if isset($mp_seller.country)}
						<div class="row">
							<label class="control-label col-lg-3">{l s='Country' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">{$mp_seller.country}</p>
							</div>
						</div>
						{/if}
						{if isset($mp_seller.state)}
						<div class="row">
							<label class="control-label col-lg-3">{l s='State' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">{$mp_seller.state}</p>
							</div>
						</div>
						{/if}
						<div class="row">
							<label class="control-label col-lg-3">{l s='Rating' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">
									{if isset($avg_rating)}
										<span class="avg_rating"></span>
									{else}
										{l s='No Rating' mod='marketplace'}
									{/if}
								</p>
							</div>
						</div>
						<div class="row">
							<label class="control-label col-lg-3">{l s='Seller Logo' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">
									<img class="img-thumbnail" width="100" height="100" src="{if isset($seller_img_path)}{$seller_img_path}?timestamp={$timestamp}{else}{$seller_default_img_path}?timestamp={$timestamp}{/if}" alt="{if isset($seller_img_path)}{l s='Seller Profile Image' mod='marketplace'}{else}{l s='Default Image' mod='marketplace'}{/if}"/>
								</p>
							</div>
						</div>
						<div class="row">
							<label class="control-label col-lg-3">{l s='Shop Logo' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">
									<img class="img-thumbnail" width="100" height="100" src="{if isset($shop_img_path)}{$shop_img_path}?timestamp={$timestamp}{else}{$shop_default_img_path}?timestamp={$timestamp}{/if}" alt="{if isset($shop_img_path)}{l s='Shop Logo' mod='marketplace'}{else}{l s='Default Image' mod='marketplace'}{/if}"/>
								</p>
							</div>
						</div>
						<div class="row">
							<label class="control-label col-lg-3">{l s='Status' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">
									{if $mp_seller.active}
										<span class="label label-success">
											<i class="icon-check"></i>
											{l s='Active' mod='marketplace'}
										</span>
									{else}
										<span class="label label-danger">
											<i class="icon-remove"></i>
											{l s='Inactive' mod='marketplace'}
										</span>
									{/if}
								</p>
							</div>
						</div>
						<div class="row">
							<label class="control-label col-lg-3">{l s='Seller Products' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<a href="{$link->getAdminLink('AdminSellerProductDetail')|addslashes}&amp;id_seller={$mp_seller.id_seller|intval}" class="btn btn-default" target="_blank"><i class="icon-search-plus"></i> {l s='View Products' mod='marketplace'}</a>
							</div>
						</div>
						{hook h='displayAdminSellerDetailViewBottom'}
					</div>
				{/if}
			</div>
			{hook h='displayAdminSellerDetailViewLeftColumn'}
		</div>
		<div class="col-lg-6">
			<div class="panel clearfix">
				<div class="panel-heading">
					<i class="icon-money"></i>
					{l s='Payment Account details' mod='marketplace'}
				</div>
				<div class="form-horizontal">
					{if isset($payment_detail)}
						<div class="row">
							<label class="control-label col-lg-3">{l s='Paymet Method' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">{$payment_detail.payment_mode}</p>
							</div>
						</div>
						<div class="row">
							<label class="control-label col-lg-3">{l s='Account Details' mod='marketplace'} :</label>
							<div class="col-lg-9">
								<p class="form-control-static">{$payment_detail.payment_detail}</p>
							</div>
						</div>
					{else}
						<p class="text-muted text-center">{l s='No account details available' mod='marketplace'}</p>
					{/if}
				</div>
			</div>
			{hook h='displayAdminSellerDetailViewRightColumn'}
		</div>
	</div>
</div>
{if isset($avg_rating)}
<script type="text/javascript">
	$('.avg_rating').raty(
	{
		path: '{$modules_dir}/marketplace/views/img',
		score: {$avg_rating},
		readOnly: true,
	});
</script>
{/if}
