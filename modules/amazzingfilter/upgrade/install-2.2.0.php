<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

function upgrade_module_2_2_0($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    $module_obj->registerHook('actionAdminTagsControllerSaveAfter');
    $module_obj->registerHook('actionAdminTagsControllerDeleteBefore');
    $module_obj->registerHook('actionAdminTagsControllerDeleteAfter');
    return true;
}
