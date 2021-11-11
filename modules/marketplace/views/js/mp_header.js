/**
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
*/

$(document).ready(function(){
	$('#wk_ad_close').on('click', function() {
		$('footer.wk_ad_footer').remove();
		var now = new Date();
		var time = now.getTime();
		time += 3600 * 1000;
		now.setTime(time);
		document.cookie =
			'no_advertisement=' + 1 + '; expires=' + now.toUTCString() + '; path=/';
		});
});