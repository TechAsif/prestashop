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

{if Configuration::get('WK_MP_SELLER_PRODUCT_EAN')}
	<div class="form-group">
		<label for="ean13" class="control-label">
			{l s='EAN-13 or JAN barcode' mod='marketplace'}
			<div class="wk_tooltip">
				<span class="wk_tooltiptext">{l s='Allowed max 13 numbers. This type of product code is specific to Europe and Japan, but is widely used internationally. It is a superset of the UPC code: all products marked with an EAN will be accepted in North America.' mod='marketplace'}</span>
			</div>
		</label>
		<input type="text"
		class="form-control"
		name="ean13"
		id="ean13"
		value="{if isset($smarty.post.ean13)}{$smarty.post.ean13}{else if isset($product_info.ean13)}{$product_info.ean13}{/if}"
		maxlength="13" />
	</div>
{/if}

{if Configuration::get('WK_MP_SELLER_PRODUCT_UPC')}
	<div class="form-group">
		<label for="upc" class="control-label">
			{l s='UPC Barcode' mod='marketplace'}
			<div class="wk_tooltip">
				<span class="wk_tooltiptext">{l s='Allowed max 12 numbers. This type of product code is widely used in the United States, Canada, the United Kingdom, Australia, New Zealand and in other countries.' mod='marketplace'}</span>
			</div>
		</label>
		<input type="text"
		class="form-control"
		name="upc"
		id="upc"
		value="{if isset($smarty.post.upc)}{$smarty.post.upc}{else if isset($product_info.upc)}{$product_info.upc}{/if}"
		maxlength="12" />
	</div>
{/if}

{if Configuration::get('WK_MP_SELLER_PRODUCT_ISBN')}
	<div class="form-group">
		<label for="isbn" class="control-label">
			{l s='ISBN' mod='marketplace'}
			<div class="wk_tooltip">
				<span class="wk_tooltiptext">{l s='Allowed max 13 character. This type of code is widely used internationally to identify books and their various editions' mod='marketplace'}</span>
			</div>
		</label>
		<input type="text"
		class="form-control"
		name="isbn"
		id="isbn"
		value="{if isset($smarty.post.isbn)}{$smarty.post.isbn}{else if isset($product_info.isbn)}{$product_info.isbn}{/if}"
		maxlength="13" />
	</div>
{/if}