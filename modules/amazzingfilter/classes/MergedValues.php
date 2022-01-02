<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class MergedValues
{
    public function __construct($af)
    {
        $this->af = $af;
        $this->context = Context::getContext();
    }

    public function extendSQL($action, &$sql)
    {
        switch ($action) {
            case 'install':
                foreach (array('attribute', 'feature') as $type) {
                    $sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'af_merged_'.pSQL($type).' (
                        id_merged int(10) unsigned NOT NULL AUTO_INCREMENT,
                        id_group int(10) unsigned NOT NULL,
                        position int(10) unsigned NOT NULL,
                        PRIMARY KEY (id_merged)
                        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
                    $sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'af_merged_'.pSQL($type).'_lang (
                        id_merged int(10) unsigned NOT NULL,
                        id_lang int(10) unsigned NOT NULL,
                        name text NOT NULL,
                        PRIMARY KEY (id_merged, id_lang), KEY id_lang (id_lang)
                        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
                    $sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'af_merged_'.pSQL($type).'_map (
                        id_original int(10) unsigned NOT NULL,
                        id_merged int(10) unsigned NOT NULL,
                        PRIMARY KEY (id_original, id_merged),
                        KEY id_original (id_original), KEY id_merged (id_merged)
                        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';
                }
                break;
            case 'uninstall':
                foreach (array('attribute', 'feature') as $type) {
                    $sql[] = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'af_merged_'.pSQL($type);
                    $sql[] = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'af_merged_'.pSQL($type).'_lang';
                    $sql[] = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'af_merged_'.pSQL($type).'_map';
                }
                break;
        }
    }

    public function getGeneralSettingsFields()
    {
        return array(
            'merged_attributes' => array(
                'display_name'  => $this->l('Activate merged attributes'),
                'tooltip'  => $this->l('For example shoe sizes US-10, UK-9.5 and EUR-43 can be merged in one value'),
                'class' => 'mergedattributes',
                'value' => 0,
                'type'  => 'switcher',
                'subtitle' => $this->l('Merged parameters'),
            ),
            'merged_features' => array(
                'display_name'  => $this->l('Activate merged features'),
                'class' => 'mergedfeatures',
                'value' => 0,
                'type'  => 'switcher',
            ),
        );
    }

    public function assignConfigVariables()
    {
        $smarty_array = array(
            'merged_data' => array(
                'attribute' => array(
                    'title' => $this->l('Merged attributes'),
                    'groups' => $this->af->getGroupOptions('attribute', $this->af->id_lang),
                    'selected_group' => $this->getGroupWithMaxMergedItems('attribute'),
                ),
                'feature' => array(
                    'title' => $this->l('Merged features'),
                    'groups' => $this->af->getGroupOptions('feature', $this->af->id_lang),
                    'selected_group' => $this->getGroupWithMaxMergedItems('feature'),
                ),
            ),
        );
        $this->context->smarty->assign($smarty_array);
    }

    public function getGroupWithMaxMergedItems($type)
    {
        return  $this->af->db->getValue('
            SELECT id_group, COUNT(*) as count FROM '._DB_PREFIX_.'af_merged_'.pSQL($type).'
            GROUP BY id_group ORDER BY count DESC
        ');
    }

    public function renderItems($type, $id_group, $id_lang, $specific_items = false)
    {
        $items = $specific_items ? $specific_items : $this->getItems($type, $id_group);
        $this->context->smarty->assign(array(
            'items' => $items,
            'item_options' => $this->getOriginalValues($type, $id_group, $id_lang),
            'merging_params' => array('id_group' => $id_group, 'type' => $type),
            'multiple_selection_label' => $this->l('Select values that should be merged'),
        ));
        $this->af->assignLanguageVariables();
        return $this->af->display($this->af->name, 'views/templates/admin/merged-items.tpl');
    }

    public function getItems($type, $id_group)
    {
        $items = array();
        $table_name = _DB_PREFIX_.'af_merged_'.pSQL($type);
        $data = $this->af->db->executeS('
            SELECT *, main.id_merged FROM '.pSQL($table_name).' main
            LEFT JOIN '.pSQL($table_name).'_lang l ON l.id_merged = main.id_merged
            LEFT JOIN '.pSQL($table_name).'_map m ON m.id_merged = main.id_merged
            WHERE main.id_group = '.(int)$id_group.'
            ORDER BY main.position ASC, main.id_merged ASC
        ');
        foreach ($data as $row) {
            $id = $row['id_merged'];
            if (!isset($items[$id])) {
                $items[$id] = array(
                    'name' => array($row['id_lang'] => $row['name']),
                    'value' => array($row['id_original'] => $row['id_original']),
                    'position' => $row['position'] + 1, // same format as native attribute positions
                );
            } else {
                $items[$id]['name'][$row['id_lang']]= $row['name'];
                $items[$id]['value'][$row['id_original']]= $row['id_original'];
            }
        }
        return $items;
    }

    public function getOriginalValues($type, $id_group, $id_lang)
    {
        $values = array();
        $get_values_method = 'get'.Tools::ucfirst($type).'s';
        foreach ($this->af->$get_values_method($id_lang, $id_group, false) as $v) {
            if (!empty($v['custom'])) {
                $v['name'] .= ' ('.$this->l('custom').')';
            }
            $values[$v['id']] = $v['name'];
        }
        return $values;
    }

    public function saveRow($data)
    {
        $sql = $upd_rows = array();
        $id_merged = $data['id_merged'];
        $table = _DB_PREFIX_.'af_merged_'.$data['type'];
        $position = $data['position'] - 1; // same format as native attribute positions
        $this->af->db->execute('
            REPLACE INTO '.pSQL($table).'
            VALUES ('.(int)$id_merged.', '.(int)$data['id_group'].', '.(int)$position.')
        ');
        if (!$id_merged) {
            $id_merged = $this->af->db->Insert_ID();
        }
        foreach ($data['name'] as $id_lang => $name) {
            if (!$name && isset($data['name'][$this->af->id_lang])) {
                 $name = $data['name'][$this->af->id_lang];
            }
            $upd_rows[$table.'_lang'][] = '('.(int)$id_merged.', '.(int)$id_lang.', \''.pSQL($name).'\')';
        }
        foreach ($data['merged_values'] as $id_original) {
            $upd_rows[$table.'_map'][] = '('.(int)$id_original.', '.(int)$id_merged.')';
        }
        foreach (array('_lang', '_map') as $ext) {
            $full_table_name = $table.$ext;
            $sql[] = 'DELETE FROM '.pSQL($full_table_name).' WHERE id_merged = '.(int)$id_merged;
            if (!empty($upd_rows[$full_table_name])) {
                $sql[] = 'INSERT INTO '.pSQL($full_table_name).' VALUES '.implode(', ', $upd_rows[$full_table_name]);
            }
        }
        $this->af->cache('clear', Tools::substr($data['type'], 0, 1).'_list');
        return $this->af->runSQL($sql) ? $id_merged : false;
    }

    public function deleteRow($type, $id_merged)
    {
        $table_name = _DB_PREFIX_.'af_merged_'.$type;
        $sql = array(
            'DELETE FROM '.pSQL($table_name).' WHERE id_merged = '.(int)$id_merged,
            'DELETE FROM '.pSQL($table_name).'_lang WHERE id_merged = '.(int)$id_merged,
            'DELETE FROM '.pSQL($table_name).'_map WHERE id_merged = '.(int)$id_merged,
        );
        $this->af->cache('clear', Tools::substr($type, 0, 1).'_list');
        return $this->af->runSQL($sql);
    }

    public function mapRows($original_rows, $id_lang, $id_group, $type)
    {
        $updated_rows = $map = array();
        $table_name = _DB_PREFIX_.'af_merged_'.$type;
        $merged_data = $this->af->db->executeS('
            SELECT * FROM '.pSQL($table_name).' main
            LEFT JOIN '.pSQL($table_name).'_map m ON m.id_merged = main.id_merged
            LEFT JOIN '.pSQL($table_name).'_lang l ON l.id_merged = m.id_merged AND l.id_lang = '.(int)$id_lang.'
            '.($id_group ? 'WHERE main.id_group = '.(int)$id_group : '').'
        ');
        if ($merged_data) {
            foreach ($merged_data as $merged_row) {
                $map[$merged_row['id_original']]['map'.$merged_row['id_merged']] = $merged_row;
            }
            foreach ($original_rows as $orig_row) {
                if (isset($map[$orig_row['id']])) {
                    foreach ($map[$orig_row['id']] as $id_merged => $merged_row) {
                        if (!isset($updated_rows[$id_merged])) { // color is taken from 1st matching option
                            $updated_rows[$id_merged] = array('id' => $id_merged) + $merged_row + $orig_row;
                        }
                    }
                } else {
                    $updated_rows[$orig_row['id']] = $orig_row;
                }
            }
            $updated_rows = $this->af->sortByKey($updated_rows, 'name');
        } else {
            $updated_rows = $original_rows;
        }
        return $updated_rows;
    }

    public function mapAttributesInSortedCombinations($raw_data, &$sorted_combinations)
    {
        $map = array();
        $data = $this->af->db->executeS('SELECT * FROM '._DB_PREFIX_.'af_merged_attribute_map');
        foreach ($data as $row) {
            $map[$row['id_original']][] = 'map'.$row['id_merged'];
        }
        foreach ($raw_data as $d) {
            if (isset($map[$d['id_att']])) {
                $c_orig = $sorted_combinations[$d['id_product']][$d['id_comb']];
                $suffix = '';
                foreach ($map[$d['id_att']] as $id_merged) {
                    $c_orig[$d['id_group']] = $id_merged;
                    $sorted_combinations[$d['id_product']][$d['id_comb'].$suffix] = $c_orig;
                    $suffix .= '_';
                }
            }
        }
    }

    public function replaceMergedAttsWithOriginalValues(&$selected_atts)
    {
        $merged_map = array();
        foreach ($this->af->db->executeS('SELECT * FROM '._DB_PREFIX_.'af_merged_attribute_map') as $row) {
            $merged_map['map'.$row['id_merged']][] = $row['id_original'];
        }
        foreach ($selected_atts as $id_group => $atts) {
            foreach (array_keys($atts) as $id_att) {
                if (isset($merged_map[$id_att])) {
                    foreach ($merged_map[$id_att] as $id_original) {
                        $selected_atts[$id_group][$id_original] = $id_original;
                    }
                    unset($selected_atts[$id_group][$id_att]);
                }
            }
        }
    }

    public function ajaxAction($action)
    {
        $ret = array();
        switch ($action) {
            case 'getItems':
                $type = Tools::getValue('type');
                $id_group = Tools::getValue('id_group');
                $ret['html'] = utf8_encode($this->renderItems($type, $id_group, $this->af->id_lang));
                break;
            case 'addRow':
                $type = Tools::getValue('type');
                $id_group = Tools::getValue('id_group');
                $position = Tools::getValue('position');
                $items = array(0 => array('name' => array(), 'value' => array(), 'position' => $position));
                $ret['html'] = utf8_encode($this->renderItems($type, $id_group, $this->af->id_lang, $items));
                break;
            case 'saveRow':
                $data = $this->af->parseStr(Tools::getValue('data'));
                $ret['saved_id'] = $this->saveRow($data);
                break;
            case 'deleteRow':
                $type = Tools::getValue('type');
                $id_merged = Tools::getValue('id_merged');
                $ret['deleted'] = $this->deleteRow($type, $id_merged);
                break;
        }
        exit(Tools::jsonEncode($ret));
    }

    public function l($string)
    {
        return $this->af->l($string, 'MergedValues');
    }
}
