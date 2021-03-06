<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

function upgrade_module_2_7_0($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }

    $module_obj->processOverride('removeOverride', 'classes/Product.php', false);
    $module_obj->processOverride('addOverride', 'classes/Product.php', false);
    if (!$module_obj->is_17) {
        $module_obj->processOverride('removeOverride', 'controllers/front/ProductController.php', false);
    }

    return true;
}
