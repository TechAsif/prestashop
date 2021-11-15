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
			
			<div class="wk-mp-right-column">
				<div class="wk_product_list">
					
					<div class="panel panel-default">
            
            <div class="panel-body">
                <table id="mp_b" class="table table-striped" >
                    <thead>
                        <tr>
                          
                            <th>Product Code</th>
                            <th>Name</th>
                            <th>Volume</th>
                            <th>Price</th>
                            <th>Action</th>
                            <th>Function</th>
                        </tr>
                    </thead>

                    <tbody>
					{foreach $product_lists as $key => $product}
					   <a href="#">
					   
                        <tr >
						   <a href="#">
                            <td data-toggle="collapse" data-target="#{$product.id_product}" class="accordion-toggle">{$product.id_product}</td>
                            <td data-toggle="collapse" data-target="#{$product.id_product}" class="accordion-toggle">{$product.name}</td>
                            <td data-toggle="collapse" data-target="#{$product.id_product}" class="accordion-toggle">{$product.from_quantity}+</td>
                            <td data-toggle="collapse" data-target="#{$product.id_product}" class="accordion-toggle">-{$product.reduction}</td>
							</a>

                            <td><a title="{l s='View Product' mod='marketplace'}" target="_blank" href="{$link->getProductLink($product)}" class="wk-edit-profile-link">
						<button class="btn btn-primary btn-sm wk_edit_profile_btn">
							{l s='Buy this' mod='marketplace'}
						</button>
					</a></td>
                            <td>Sell Like this</td>
                        </tr>
						</a>

                        <tr>
                            <td colspan="12" class="hiddenRow">
                                <div class="accordian-body collapse hiddenRow" id="{$product.id_product}">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr class="info">
                                                <th>Product Code</th>
                                                <th>Name</th>
                                                <th>From</th>
												<th>Unit</th>
                                                {* <th>To</th> *}
                                                <th>Price</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            
									    {foreach $specificAllProduct as $key => $specificProduct}	
										    {if $product.id_product eq $specificProduct.id_product }	
												<tr>
													<td>{$product.id_product}</td>
													<td>{$product.name}</td>
													<td>{$specificProduct.from_quantity}</td>
													{if $specificProduct.unity eq '' }
													<td>Number</td>
													{else}
												    <td>{$specificProduct.unity}</td>
													{/if}
													
													{* <td>demo</td> *}
													<td>-{$specificProduct.reduction}</td>
													
													
												</tr>
											{else}
												
											{/if}	
										{/foreach}	
                                            
                                     
                                        </tbody>
                                    </table>

                                </div>
                            </td>
                        </tr>
                    {/foreach}


                        
                    </tbody>
                </table>
				{*
				<ul class="pagination">
				<li class="page-item"><a class="page-link" href="#">Previous</a></li>
				<li class="page-item"><a class="page-link" href="#">1</a></li>
				<li class="page-item active"><a class="page-link" href="#">2</a></li>
				<li class="page-item"><a class="page-link" href="#">3</a></li>
				<li class="page-item"><a class="page-link" href="#">Next</a></li>
				</ul>
				*}
            </div>

        </div>


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
