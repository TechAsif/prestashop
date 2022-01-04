<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

function upgrade_module_3_1_3($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    Media::clearCache(); // make sure front.js/css are updated in Smart cache
    $color_groups = array_column($module_obj->db->executeS('
        SELECT CONCAT(\'a\', id_attribute_group) as f_key
        FROM '._DB_PREFIX_.'attribute_group WHERE is_color_group = 1
    '), 'f_key', 'f_key');
    $upd_template_rows = array();
    foreach ($module_obj->db->executeS('SELECT * FROM '._DB_PREFIX_.'af_templates') as $row) {
        $filters = Tools::jsonDecode($row['template_filters'], true);
        foreach (array_keys($filters) as $key) {
            if (isset($color_groups[$key])) {
                $filters[$key]['color_display'] = 1;
            }
            $first_char = Tools::substr($key, 0, 1);
            $filters[$key]['visible_items'] = in_array($first_char, array('a', 'f', 'm', 's', 't')) ? 15 : '';
        }
        $row['template_filters'] = Tools::jsonEncode($filters);
        $upd_template_rows[] = '(\''.implode('\', \'', array_map('pSQL', $row)).'\')';
    }
    if ($upd_template_rows) {
        $module_obj->db->execute('
            REPLACE INTO '._DB_PREFIX_.'af_templates VALUES '.implode(', ', $upd_template_rows).'
        ');
    }
    return true;
}
