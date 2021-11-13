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

{if $allow_multilang && $total_languages > 1}
	<img class="all_lang_icon" data-lang-id="{$current_lang.id_lang}" src="{$ps_img_dir}{$current_lang.id_lang}.jpg">
{/if}