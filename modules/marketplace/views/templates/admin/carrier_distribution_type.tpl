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

{if isset($options_distribute_type)}
    <select name="distribute_type" class="distribute_type" data-id-ps-reference="{$wk_carrier_list.id_reference}">
        {foreach $options_distribute_type as $type_key => $type_value}
            <option value="{$type_key}" {if ($wk_distribute_type == $type_key)}selected = "selected"{/if}>
                {$type_value}
            </option>
        {/foreach}
    </select>
{/if}