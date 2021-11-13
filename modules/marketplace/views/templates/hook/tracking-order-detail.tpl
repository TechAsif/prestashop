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

<section class="box">
	<h3>{l s='Track the delivery of your order' mod='marketplace'}</h3>
	{if isset($sellerTrackingData)}
		{foreach $sellerTrackingData as $trackingData}
            <section class="box">
				<h4>
                    {l s='Shop' mod='marketplace'} :
                    <a title="{l s='Visit Shop' mod='marketplace'}" target="_blank" href="{$trackingData.shopstore_link}">
                        <span>{$trackingData.shop_name}</span>
                    </a>
                </h4>
				<table class="table table-bordered">
					<thead class="thead-default">
						<tr>
							<th width="50%">{l s='Tracking URL :' mod='marketplace'}</th>
							<th>{l s='Tracking Number :' mod='marketplace'}</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>{$trackingData.tracking_url}</td>
							<td>{$trackingData.tracking_number}</td>
						</tr>
					</tbody>
				</table>
			</section>
		{/foreach}
	{/if}
</section>