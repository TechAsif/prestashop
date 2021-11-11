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

<div class="modal-dialog" role="document">
	<div class="modal-content">
		<div class="modal-header">
			{if isset($displayCancelIcon)}
        		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			{/if}
        	<h4 class="modal-title" id="myModalLabel">{l s='Image' mod='marketplace'}</h4>
		</div>
		<div class="modal-body wk-productlist-images">
			<div class="table-responsive">
				<table id="imageTable" class="table table-hover {if isset($image_detail) && $image_detail && $edit_permission}mp-active-image-table{/if}">
					<thead>
						<tr>
							<th><center>{l s='Image Id' mod='marketplace'}</center></th>
							<th><center>{l s='Image' mod='marketplace'}</center></th>
							{if $edit_permission}
								<th><center>{l s='Position' mod='marketplace'}</center></th>
								<th><center>{l s='Cover' mod='marketplace'}</center></th>
								<th><center>{l s='Status' mod='marketplace'}</center></th>
								<th><center>{l s='Action' mod='marketplace'}</center></th>
							{/if}
						</tr>
					</thead>
					{if isset($image_detail) && $image_detail}
						<tbody>
							{foreach $image_detail as $image}
								<tr class="jFiler-items imageinforow{$image.id_mp_product_image}" id="mp_image_{$image.id_mp_product_image}" id_mp_product="{$id_mp_product}" id_mp_image="{$image.id_mp_product_image}" id_mp_image_position="{$image.position}">
									<td><center>{$image.id_mp_product_image}</center></td>
									<td><center>
										<a class="mp-img-preview" href="{$module_dir}marketplace/views/img/product_img/{$image.seller_product_image_name}">
											<img class="img-thumbnail" width="80" height="80" src="{$module_dir}marketplace/views/img/product_img/{$image.seller_product_image_name}" />
										</a>
										</center>
									</td>
									{if $edit_permission}
										<td><center>{$image.position}</center></td>
										<td><center>
											{if $image.cover == 1 }
												<img class="covered" id="changecoverimage{$image.id_mp_product_image}" alt="{$image.id_mp_product_image}" src="{$mp_image_dir}icon/icon-check.png" is_cover="1" id_mp_product="{$id_mp_product}"/>
											{else}
												<img class="covered" id="changecoverimage{$image.id_mp_product_image}" alt="{$image.id_mp_product_image}" src="{$mp_image_dir}forbbiden.gif" is_cover="0" id_mp_product="{$id_mp_product}" style="cursor:pointer" />
											{/if}
											</center>
										</td>
										<td><center>
											{if $image.active}
												<span class="wk-btn-active">{l s='Active' mod='marketplace'}</span>
											{else}
												<span class="wk-btn-inactive">{l s='Inactive' mod='marketplace'}</span>
											{/if}
										</center></td>
										<td><center>
											{if $image.cover == 1}
												<a class="delete_pro_image pull-left btn btn-default" href="" is_cover="1" id_mp_product="{$id_mp_product}" id_mp_image="{$image.id_mp_product_image}">
													<i class="material-icons">&#xE872;</i>
												</a>
											{else}
												<a class="delete_pro_image pull-left btn btn-default" href="" is_cover="0" id_mp_product="{$id_mp_product}" id_mp_image="{$image.id_mp_product_image}">
													<i class="material-icons">&#xE872;</i>
												</a>
											{/if}
											</center>
										</td>
									{/if}
								</tr>
							{/foreach}
						</tbody>
					{else}
						<tbody>
							<tr>
								<td colspan="6">{l s='No image available' mod='marketplace'}</td>
							</tr>
						</tbody>
					{/if}
				</table>
			</div>
		</div>
	</div>
</div>
