<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

function upgrade_module_3_0_3($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    Media::clearCache(); // make sure front.js/css are updated in Smart cache
    foreach ($module_obj->all_shop_ids as $id_shop) {
        $all_saved_settings = $module_obj->getSavedSettings($id_shop);
        foreach ($module_obj->getSettingsKeys() as $type) {
            $settings = isset($all_saved_settings[$type]) ? $all_saved_settings[$type] : array();
            $module_obj->saveSettings($type, $settings, array($id_shop));
        }
    }
    return true;
}
