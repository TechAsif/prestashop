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


{if isset($seller_wise_rating)}
	<span id="seller_wise_rating_{$list.id_review|escape:'htmlall':'UTF-8'}"></span>
	<script type="text/javascript">
		$(document).ready(function(){
			$('#seller_wise_rating_{$list.id_review}').raty({
		        path: "{$rating_start_path|escape:'htmlall':'UTF-8'}",
		        score: "{$sellerRating|escape:'htmlall':'UTF-8'}",
		        readOnly: true,
		    });
		});
	</script>
{else}
	<span id="seller_main_avg_rating_{$list.id_seller|escape:'htmlall':'UTF-8'}"></span>
	<script type="text/javascript">
		$(document).ready(function(){
			$('#seller_main_avg_rating_{$list.id_seller}').raty({
		        path: "{$rating_start_path|escape:'htmlall':'UTF-8'}",
		        score: "{$sellerRating|escape:'htmlall':'UTF-8'}",
		        readOnly: true,
		    });
		});
</script>
{/if}