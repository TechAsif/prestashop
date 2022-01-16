<?php


if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_0_6($module)
{
    if (Db::getInstance()->ExecuteS('SHOW COLUMNS FROM `'._DB_PREFIX_.$module->name.'` LIKE \'one_shot\'') == false) {
        Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.$module->name.'` ADD `one_shot` BOOLEAN NOT NULL DEFAULT 0 AFTER `updated_at`');
    }

    return $module;
}
