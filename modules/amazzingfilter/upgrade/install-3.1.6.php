<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

function upgrade_module_3_1_6($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    Media::clearCache(); // make sure front.js/css are updated in Smart cache
    $file_to_remove = _PS_MODULE_DIR_.$module_obj->name.'/views/js/attribute-indexer.js';
    if (file_exists($file_to_remove)) {
        unlink($file_to_remove);
    }
    return true;
}
