<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

function upgrade_module_3_0_0($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    Media::clearCache(); // make sure front.js/css are updated in Smart cache
    $module_obj->installation_process = true;
    $move_settings = array(
        'subcat_products' => array('from' => 'general', 'to' => 'indexation', 'key' => 'subcat_products'),
        'load_icons' => array('from' => 'general', 'to' => 'iconclass', 'key' => 'load_font'),
        'include_sorting' => array('from' => 'general', 'to' => 'general', 'key' => 'url_sorting'),
    );
    $update_settings = array(
        'general' => array('compact_btn' => 3),
    );
    foreach ($module_obj->all_shop_ids as $id_shop) {
        $all_saved_settings = $module_obj->getSavedSettings($id_shop);
        foreach ($move_settings as $key => $move) {
            if (isset($all_saved_settings[$move['from']][$key])) {
                $all_saved_settings[$move['to']][$move['key']] = $all_saved_settings[$move['from']][$key];
            }
        }
        foreach ($update_settings as $type => $settings) {
            foreach ($settings as $name => $value) {
                $all_saved_settings[$type][$name] = $value;
            }
        }
        foreach ($module_obj->getSettingsKeys() as $type) {
            $settings = isset($all_saved_settings[$type]) ? $all_saved_settings[$type] : array();
            $module_obj->saveSettings($type, $settings, array($id_shop));
        }
    }
    $module_obj->indexationTable('install');
    return true;
}
