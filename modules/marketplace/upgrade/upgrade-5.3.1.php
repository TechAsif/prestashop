<?php
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

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_5_3_1($module)
{
    $wkQueries = array(
        "ALTER TABLE `"._DB_PREFIX_."wk_mp_seller_product`
        MODIFY COLUMN `ps_id_carrier_reference` text",
    );

    $mpDatabaseInstance = Db::getInstance();
    $mpSuccess = true;
    foreach ($wkQueries as $mpQuery) {
        $mpSuccess &= $mpDatabaseInstance->execute(trim($mpQuery));
    }
    if ($mpSuccess) {
        return true;
    }
    return true;
}
