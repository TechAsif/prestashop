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
	{if isset($smarty.get.created_conf)}
		<p class="alert alert-success">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='Created Successfully' mod='marketplace'}
		</p>
	{else if isset($smarty.get.edited_conf)}
		<p class="alert alert-success">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='Updated Successfully' mod='marketplace'}
		</p>
	{else if isset($smarty.get.edited_withdeactive)}
		<p class="alert alert-info">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='Product has been updated successfully but it has been deactivated. Please wait till the approval from admin.' mod='marketplace'}
		</p>
	{else if isset($smarty.get.deleted)}
		<p class="alert alert-success">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='Deleted Successfully' mod='marketplace'}
		</p>
	{else if isset($smarty.get.status_updated)}
		<p class="alert alert-success">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='Status updated Successfully' mod='marketplace'}
		</p>
	{else if isset($smarty.get.edited_qty) && isset($smarty.get.edited_price)}
		<p class="alert alert-success">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='Only Quantity and Price have been updated successfully. You do not have permission to edit other fields.' mod='marketplace'}
		</p>
	{else if isset($smarty.get.edited_qty)}
		<p class="alert alert-success">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='Only Quantity has been updated successfully. You do not have permission to edit other fields.' mod='marketplace'}
		</p>
	{else if isset($smarty.get.edited_price)}
		<p class="alert alert-success">
			<button data-dismiss="alert" class="close" type="button">×</button>
			{l s='Only Price has been updated successfully. You do not have permission to edit other fields.' mod='marketplace'}
		</p>
	{/if}
	<div class="wk-mp-block">
		{hook h="displayMpMenu"}
		<div class="wk-mp-content">
			<div class="page-title" style="background-color:{$title_bg_color};">
				<span style="color:{$title_text_color};">{l s='Product' mod='marketplace'}</span>
			</div>
			<div class="wk-mp-right-column">
				<div class="wk_product_list">
					{if $add_permission}
					<p class="wk_text_right">
						<a title="{l s='Add product' mod='marketplace'}" href="{$link->getModuleLink('marketplace', 'addproduct')}">
							<button class="btn btn-primary btn-sm" type="button">
								<i class="material-icons">&#xE145;</i>
								{l s='Add Product' mod='marketplace'}
							</button>
						</a>
						{hook h="displayMpProductListTop"}
					</p>
					{/if}
					<form action="{$link->getModuleLink('marketplace', productlist)}" method="post" id="mp_productlist_form">
						<input type="hidden" name="token" id="wk-static-token" value="{$static_token}">
						<table class="table table-striped" id="mp_product_list">
							<thead>
								<tr>
									{if $product_lists|is_array}
										{if $product_lists|@count > 1}
											<th class="no-sort"><input type="checkbox" title="{l s='Select all' mod='marketplace'}" id="mp_all_select"/></th>
										{/if}
									{/if}
									<th>{l s='ID' mod='marketplace'}</th>
									<th>{l s='Image' mod='marketplace'}</th>
									<th>{l s='Name' mod='marketplace'}</th>
									<th><center>{l s='Price' mod='marketplace'}</center></th>
									<th><center>{l s='Quantity' mod='marketplace'}</center></th>
									<th><center>{l s='Status' mod='marketplace'}</center></th>
									<th class="no-sort"><center>{l s='Actions' mod='marketplace'}</center></th>
								</tr>
							</thead>
							<tbody>
								{if $product_lists != 0}
									{foreach $product_lists as $key => $product}
										<tr class="{if $key%2 == 0}even{else}odd{/if}">
											{if $product_lists|is_array}{if $product_lists|@count > 1}<td><input type="checkbox" name="mp_product_selected[]" class="mp_bulk_select" value="{$product.id_mp_product}"/></td>{/if}{/if}
											<td>{$product.id_mp_product}</td>
											<td>
												{if isset($product.cover_image)}
													<a class="mp-img-preview" href="{$smarty.const._MODULE_DIR_}marketplace/views/img/product_img/{$product.cover_image.seller_product_image_name}">
														<img class="img-thumbnail" width="45" height="45" src="{$smarty.const._MODULE_DIR_}marketplace/views/img/product_img/{$product.cover_image.seller_product_image_name}">
													</a>
												{else}
													<img class="img-thumbnail" alt="{l s='No image' mod='marketplace'}"	width="45" height="45" src="{$smarty.const._MODULE_DIR_}/marketplace/views/img/home-default.jpg">
												{/if}
											</td>
											<td>
												<a href="{$link->getModuleLink('marketplace', 'productdetails', ['id_mp_product' => $product.id_mp_product])}">
												{$product.product_name}
												</a>
											</td>
											<td data-order="{$product.price_without_sign}"><center>{$product.price}</center></td>
											<td><center>{$product.quantity}</center></td>
											<td><center>
												{if isset($product.admin_approved) && $product.admin_approved}
													{if $product.active}
														{if $products_status == 1 && $edit_permission}
															<a href="{$link->getModuleLink('marketplace', 'productlist', ['id_product' => {$product.id_product}, 'mp_product_status' => 1])|addslashes}">
																<img alt="{l s='Enabled' mod='marketplace'}" title="{l s='Enabled' mod='marketplace'}" class="mp_product_status" src="{$smarty.const._MODULE_DIR_}marketplace/views/img/icon/icon-check.png" />
															</a>
														{else}
															<span class="wk_product_approved">{l s='Approved' mod='marketplace'}</span>
														{/if}
													{else}
														{if $products_status == 1 && $edit_permission}
															<a href="{$link->getModuleLink('marketplace', 'productlist', ['id_product' => {$product.id_product}, 'mp_product_status' => 1])|addslashes}">
																<img alt="{l s='Disabled' mod='marketplace'}" title="{l s='Disabled' mod='marketplace'}" class="mp_product_status" src="{$smarty.const._MODULE_DIR_}marketplace/views/img/icon/icon-close.png" />
															</a>
														{else}
															<span class="wk_product_pending">{l s='Pending' mod='marketplace'}</span>
														{/if}
													{/if}
												{else}
													<span class="wk_product_pending">{l s='Pending' mod='marketplace'}</span>
												{/if}
												</center>
											</td>
											<td>
												<center>
												<a title="{l s='Edit' mod='marketplace'}" href="{$link->getModuleLink('marketplace', 'updateproduct', ['id_mp_product' => $product.id_mp_product])}">
													<i class="material-icons">&#xE254;</i>
												</a>
												{if $delete_permission}
												<a title="{l s='Delete' mod='marketplace'}" href="{$link->getModuleLink('marketplace', 'updateproduct', ['id_mp_product' => $product.id_mp_product, 'deleteproduct' => 1])}" class="delete_mp_product">
													<i class="material-icons">&#xE872;</i>
												</a>
												{/if}
												<a class="edit_seq open_image_form" alt="1" product-id="{$product['id_mp_product']}" data-toggle="modal" data-target="#content{$product['id_mp_product']}" href="javascript:void(0)">
													<i class="material-icons mp-list-img-link" title="{l s='View Image' mod='marketplace'}" id="edit_seq{$product['id_mp_product']}">&#xE3F4;</i>
												</a>
												{if Configuration::get('WK_MP_PRODUCT_ALLOW_DUPLICATE')}
												<a title="{l s='Duplicate' mod='marketplace'}" href="{$link->getModuleLink('marketplace', 'updateproduct', ['id_mp_product' => $product.id_mp_product, 'duplicateproduct' => 1])}" class="duplicate_mp_product">
													<i class="material-icons">content_copy</i>
												</a>
												{/if}
												<input type="hidden" id="urlimageedit" value="{$imageediturl}"/>
												{hook h="displayMpProductListAction" id_product=$product.id_mp_product}
												</center>
											</td>
										</tr>

										<div class="modal fade" id="content{$product['id_mp_product']}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
										</div>
									{/foreach}
								{/if}
							</tbody>
						</table>
						{if $product_lists|is_array}
							{if $product_lists|@count > 1}
								<div class="btn-group">
									<button class="btn btn-default btn-sm dropdown-toggle wk_language_toggle" type="button" data-toggle="dropdown" aria-expanded="false">
									{l s='Bulk actions' mod='marketplace'} <span class="caret"></span>
									</button>
									<ul class="dropdown-menu wk_bulk_actions" role="menu">
										<li>
											<a href="" class="mp_bulk_delete_btn">
												<i class="material-icons">&#xE872;</i> {l s='Delete selected' mod='marketplace'}
											</a>
										</li>
									</ul>
								</div>
							{/if}
						{/if}
					</form>
				</div>
			</div>
		</div>
		<div class="left full">
			{hook h="displayMpProductListFooter"}
		</div>

		{block name='mp_image_preview'}
			{include file='module:marketplace/views/templates/front/product/_partials/mp-image-preview.tpl'}
		{/block}
	</div>
{/block}
