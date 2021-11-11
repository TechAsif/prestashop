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

{if Configuration::get('WK_MP_SHOW_SELLER_DETAILS')}
	<div class="box-account">
		<div class="box-content">
			<div class="wk-left-label">
				{if isset($WK_MP_SELLER_DETAILS_ACCESS_2)}
					<div class="wk_row">
						<label>{l s='Business Email -' mod='marketplace'}</label>
						<span>{$mp_seller_info.business_email}</span>
						<div class="clearfix"></div>
					</div>
				{/if}
				{if isset($WK_MP_SELLER_DETAILS_ACCESS_3)}
					<div class="wk_row">
						<label>{l s='Phone -' mod='marketplace'}</label>
						<span>{$mp_seller_info.phone}</span>
						<div class="clearfix"></div>
					</div>
					{if $mp_seller_info.fax != ''}
						<div class="wk_row">
							<label>{l s='Fax -' mod='marketplace'}</label>
							<span>{$mp_seller_info.fax}</span>
							<div class="clearfix"></div>
						</div>
					{/if}
				{/if}
				{if isset($WK_MP_SELLER_DETAILS_ACCESS_4)}
					{if $mp_seller_info.address != '' || $mp_seller_info.city != '' || $mp_seller_info.id_state != 0 || $mp_seller_info.id_country != 0}
						<div class="wk_row">
							<label>{l s='Address -' mod='marketplace'}</label>
							<span>
								{if $mp_seller_info.address != ''}{$mp_seller_info.address}{if $mp_seller_info.postcode != '' || $mp_seller_info.city != '' || $mp_seller_info.id_state != 0 || $mp_seller_info.id_country != 0}<br>{/if}{/if}
								{if $mp_seller_info.postcode != ''}
									{$mp_seller_info.postcode}
								{/if}
								{if $mp_seller_info.city != ''}
									{$mp_seller_info.city}<br>
								{/if}
								{if $mp_seller_info.id_state != 0}
									{$mp_seller_info.state},
								{/if}
								{if $mp_seller_info.id_country != 0}
									{$mp_seller_info.country}
								{/if}
							</span>
							<div class="clearfix"></div>
						</div>
					{/if}
				{/if}
				{if isset($WK_MP_SELLER_DETAILS_ACCESS_6)}
					{if $mp_seller_info.facebook_id != "" || $mp_seller_info.twitter_id != "" || $mp_seller_info.youtube_id != "" || $mp_seller_info.instagram_id != ""}
					<div class="wk_row">
						<label>{l s='Social Profile -' mod='marketplace'}</label>
						<span class="wk-social-icon">
							{if $mp_seller_info.facebook_id != ""}
								<a class="wk-facebook-button" target="_blank" title="{l s='Facebook' mod='marketplace'}" href="https://www.facebook.com/{$mp_seller_info.facebook_id}"></a>
							{/if}
							{if $mp_seller_info.twitter_id != ""}
								<a class="wk-twitter-button" target="_blank" title="{l s='Twitter' mod='marketplace'}" href="https://www.twitter.com/{$mp_seller_info.twitter_id}"></a>
							{/if}
							{if $mp_seller_info.youtube_id != ''}
								<a class="wk-youtube-button" target="_blank" title="{l s='Youtube' mod='marketplace'}" href="https://www.youtube.com/{$mp_seller_info.youtube_id}"></a>
							{/if}
							{if $mp_seller_info.instagram_id != ''}
								<a class="wk-instagram-button" target="_blank" title="{l s='Instagram' mod='marketplace'}" href="https://www.instagram.com/{$mp_seller_info.instagram_id}"></a>
							{/if}
						</span>
						<div class="clearfix"></div>
					</div>
					{/if}
				{/if}
				{if Configuration::get('WK_MP_REVIEW_SETTINGS')}
					<div class="wk_row wk_seller_rating">
						<label>{l s='Seller Rating -' mod='marketplace'}</label>
						<span class="avg_rating"></span>
						<div class="clearfix"></div>
					</div>
				{/if}
				{hook h="displayMpSellerDetailsBottom"}
			</div>
		</div>
	</div>
{/if}