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

<div class="form-group row">
	<div class="col-md-6">
		<div class="form-group">
			<button type="button" class="btn btn-primary wk_uploader_margin" id="uploadprofileimg">{l s='Upload Profile Image' mod='marketplace'}</button>
			<div id="profileuploader" class="wk_uploader_wholediv">
				<div class="col-md-11 wk_padding_none">
					<input type="file" name="sellerprofileimage[]" class="uploadimg_container" data-jfiler-name="seller_img">
			    </div>
				<div class="clearfix"></div>
				<label class="wk_formfield_required_notify">{l s='Recommended Dimension : 200 x 200 pixels' mod='marketplace'}</label>
		    </div>
			<div class="jFiler-items-seller_img {if isset($seller_img_path)}wk_hover_img{/if}">
				<ul class="jFiler-items-list jFiler-items-grid" style="padding:0px;">
					<li class="jFiler-item">
						<div class="jFiler-item-container">
							<div class="jFiler-item-inner">
								<img src="{if isset($seller_img_path)}{$seller_img_path}?timestamp={$timestamp}{else}{$seller_default_img_path}?timestamp={$timestamp}{/if}" alt="{if isset($seller_img_path)}{l s='Seller Profile Image' mod='marketplace'}{else}{l s='Default Image' mod='marketplace'}{/if}"/>
								{if isset($seller_img_path)}
								<div class="wk_text_right">
									<a class="icon-jfi-trash wk_delete_img" data-id_seller="{$mp_seller_info.id_seller}" data-imgtype="seller_img" data-uploaded="1" title="{l s='Delete' mod='marketplace'}"></a>
								</div>
								{/if}
							</div>
						</div>
					</li>
					<div class="clearfix"></div>
				</ul>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<button type="button" class="btn btn-primary wk_uploader_margin" id="uploadshoplogo">{l s='Upload Shop Logo' mod='marketplace'}</button>
			<div id="shopuploader" class="wk_uploader_wholediv">
				<div class="col-md-11 wk_padding_none">
					<input type="file" name="shopimage[]" class="uploadimg_container" data-jfiler-name="shop_img">
			    </div>
				<div class="clearfix"></div>
				<label class="wk_formfield_required_notify">{l s='Recommended Dimension : 200 x 200 pixels' mod='marketplace'}</label>
		    </div>
			<div class="jFiler-items-shop_img {if isset($shop_img_path)}wk_hover_img{/if}">
				<ul class="jFiler-items-list jFiler-items-grid" style="padding:0px;">
					<li class="jFiler-item">
						<div class="jFiler-item-container">
							<div class="jFiler-item-inner">
								<img src="{if isset($shop_img_path)}{$shop_img_path}?timestamp={$timestamp}{else}{$shop_default_img_path}?timestamp={$timestamp}{/if}" alt="{if isset($shop_img_path)}{l s='Shop Logo' mod='marketplace'}{else}{l s='Default Image' mod='marketplace'}{/if}"/>
								{if isset($shop_img_path)}
								<div class="wk_text_right">
									<a class="icon-jfi-trash wk_delete_img" data-id_seller="{$mp_seller_info.id_seller}" data-imgtype="shop_img" data-uploaded="1" title="{l s='Delete' mod='marketplace'}"></a>
								</div>
								{/if}
							</div>
						</div>
					</li>
					<div class="clearfix"></div>
				</ul>
			</div>
		</div>
	</div>
</div>

<h2 class="text-uppercase" style="border-bottom: 1px solid #d5d5d5;padding-bottom: 11px;">
	{l s='Banner Image' mod='marketplace'}
</h2>

<div class="form-group row">
	<!-- Seller Profile Page Banner -->
	<div class="col-md-6">
		<div class="form-group">
			<button type="button" class="btn btn-primary wk_uploader_margin" id="uploadsellerbanner">{l s='Upload Profile Banner' mod='marketplace'}</button>
			<div id="profilebanneruploader" class="wk_uploader_wholediv">
				<div class="col-md-11 wk_padding_none">
					<input type="file" name="profilebannerimage[]" class="uploadimg_container" data-jfiler-name="seller_banner">
			    </div>
				<div class="clearfix"></div>
				<label class="wk_formfield_required_notify">{l s='Recommended Dimension : 1140 x 285 pixels' mod='marketplace'}</label>
		    </div>
			<div class="jFiler-items-seller_banner {if isset($seller_banner_path)}wk_hover_img{/if}">
				<ul class="jFiler-items-list jFiler-items-grid" style="padding:0px;">
					<li class="jFiler-item">
						<div class="jFiler-item-container">
							<div class="jFiler-item-inner">
								<img width="225" src="{if isset($seller_banner_path)}{$seller_banner_path}?timestamp={$timestamp}{else}{$no_image_path}{/if}" alt="{if isset($seller_banner_path)}{l s='Seller Profile Banner' mod='marketplace'}{else}{l s='No Image' mod='marketplace'}{/if}"/>
								{if isset($seller_banner_path)}
								<div class="wk_text_right">
									<a class="icon-jfi-trash wk_delete_img" data-id_seller="{$mp_seller_info.id_seller}" data-imgtype="seller_banner" data-uploaded="1" title="{l s='Delete' mod='marketplace'}"></a>
								</div>
								{/if}
							</div>
						</div>
					</li>
					<div class="clearfix"></div>
				</ul>
			</div>
		</div>
	</div>

	<!-- Shop Store Page Banner -->
	<div class="col-md-6">
		<div class="form-group">
			<button type="button" class="btn btn-primary wk_uploader_margin" id="uploadshopbanner">{l s='Upload Shop Banner' mod='marketplace'}</button>
			<div id="shopbanneruploader" class="wk_uploader_wholediv">
				<div class="col-md-11 wk_padding_none">
					<input type="file" name="shopbannerimage[]" class="uploadimg_container" data-jfiler-name="shop_banner">
			    </div>
				<div class="clearfix"></div>
				<label class="wk_formfield_required_notify">{l s='Recommended Dimension : 1140 x 285 pixels' mod='marketplace'}</label>
		    </div>
			<div class="jFiler-items-shop_banner {if isset($shop_banner_path)}wk_hover_img{/if}">
				<ul class="jFiler-items-list jFiler-items-grid" style="padding:0px;">
					<li class="jFiler-item">
						<div class="jFiler-item-container">
							<div class="jFiler-item-inner">
								<img width="225" src="{if isset($shop_banner_path)}{$shop_banner_path}?timestamp={$timestamp}{else}{$no_image_path}{/if}" alt="{if isset($shop_banner_path)}{l s='Shop Logo' mod='marketplace'}{else}{l s='No Image' mod='marketplace'}{/if}"/>
								{if isset($shop_banner_path)}
								<div class="wk_text_right">
									<a class="icon-jfi-trash wk_delete_img" data-id_seller="{$mp_seller_info.id_seller}" data-imgtype="shop_banner" data-uploaded="1" title="{l s='Delete' mod='marketplace'}"></a>
								</div>
								{/if}
							</div>
						</div>
					</li>
					<div class="clearfix"></div>
				</ul>
			</div>
		</div>
	</div>
</div>