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

<div class="wk-collection-top row">
	<div class="col-xs-12 col-sm-12 col-md-6 wk_collection_total_products">
		{l s='There are %1$d Products' sprintf=[$currentProductCount] mod='marketplace'}
	</div>
	<div class="col-md-6">
		<div class="row">
		<div class="select">
			<span class="col-xs-12 col-sm-12 col-md-3 wk-collection-sort-by">{l s='Sort by' mod='marketplace'}</span>
			<div class="col-xs-12 col-sm-12 col-md-9">
				<select id="selectMpProductSort" class="selectMpProductSort form-control form-control-select">
					<option value="{$defaultorederby}"{if $orderby eq 'id' AND $orderway eq 'desc'} selected="selected"{/if}>{l s='Select' mod='marketplace'}</option>
					{if !$PS_CATALOG_MODE}
						<option value="price:asc"{if $orderby eq 'price' AND $orderway eq 'asc'} selected="selected"{/if}>{l s='Price: Lowest first' mod='marketplace'}</option>
						<option value="price:desc"{if $orderby eq 'price' AND $orderway eq 'desc'} selected="selected"{/if}>{l s='Price: Highest first' mod='marketplace'}</option>
					{/if}
					<option value="name:asc"{if $orderby eq 'name' AND $orderway eq 'asc'} selected="selected"{/if}>{l s='Product Name: A to Z' mod='marketplace'}</option>
					<option value="name:desc"{if $orderby eq 'name' AND $orderway eq 'desc'} selected="selected"{/if}>{l s='Product Name: Z to A' mod='marketplace'}</option>
					<option value="quantity:desc"{if $orderby eq 'quantity' AND $orderway eq 'desc'} selected="selected"{/if}>{l s='In stock' mod='marketplace'}</option>
				</select>
			</div>
		</div>
		</div>
	</div>
</div>

