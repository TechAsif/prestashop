<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

function upgrade_module_2_8_4($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    // update settings for compact offset direction
    $rows_to_update = array();
    foreach ($module_obj->db->executeS('SELECT * FROM '._DB_PREFIX_.'af_general_settings') as $row) {
        $settings = Tools::jsonDecode($row['settings'], true);
        $settings['compact_offset'] = 2;
        $row['settings'] = Tools::jsonEncode($settings);
        $rows_to_update[] = '(\''.implode('\', \'', array_map('pSQL', $row)).'\')';
    }
    if ($rows_to_update) {
        $module_obj->db->execute('
            REPLACE INTO '._DB_PREFIX_.'af_general_settings VALUES '.implode(', ', $rows_to_update).'
        ');
    }
    return true;
}
