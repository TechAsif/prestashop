<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

function upgrade_module_2_8_2($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    // db tables for manufacturer_templates, supplier_templates
    $module_obj->prepareDatabaseTables();

    // additional settings for templates
    $module_obj->db->execute('
        ALTER TABLE '._DB_PREFIX_.'af_templates
        ADD additional_settings text NOT NULL AFTER template_filters
    ');
    $rows_to_update = array();
    foreach ($module_obj->db->executeS('SELECT * FROM '._DB_PREFIX_.'af_templates') as $row) {
        $additional_settings = $module_obj->getDefaultAdditionalSettings($row['template_controller']);
        $row['additional_settings'] = Tools::jsonEncode($additional_settings);
        $rows_to_update[] = '(\''.implode('\', \'', array_map('pSQL', $row)).'\')';
    }
    if ($rows_to_update) {
        $module_obj->db->execute('
            REPLACE INTO '._DB_PREFIX_.'af_templates VALUES '.implode(', ', $rows_to_update).'
        ');
    }

    // update settings for compact view
    $rows_to_update = array();
    $current_hook = $module_obj->getCurrentHook();
    foreach ($module_obj->db->executeS('SELECT * FROM '._DB_PREFIX_.'af_general_settings') as $row) {
        $settings = Tools::jsonDecode($row['settings'], true);
        $settings['layout'] = $current_hook == 'displayTopColumn' ? 'horizontal' : 'vertical';
        $settings['m_layout'] = !empty($settings['compact_view']) ? 'compact' : $settings['layout'];
        $row['settings'] = Tools::jsonEncode($settings);
        $rows_to_update[] = '(\''.implode('\', \'', array_map('pSQL', $row)).'\')';
    }

    if ($rows_to_update) {
        $module_obj->db->execute('
            REPLACE INTO '._DB_PREFIX_.'af_general_settings VALUES '.implode(', ', $rows_to_update).'
        ');
    }

    // add override for custom sorting + npp in 1.6
    if (!$module_obj->is_17) {
        $module_obj->processOverride('addOverride', 'classes/controller/FrontController.php', false);
    }

    return true;
}
