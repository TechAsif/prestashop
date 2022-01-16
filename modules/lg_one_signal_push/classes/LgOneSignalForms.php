<?php
/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class LgOneSignalForms
{
    protected static $module = false;

    public static function init($module)
    {
        if (self::$module == false) {
            self::$module = $module;
        }

        return self::$module;
    }

    public static function getPushForm($title = 'New push', $update = false)
    {
        $form = array(
            array(
                'form' => array(
                    'legend' => array(
                        'title' => self::$module->l($title),
                        'icon' => 'icon-plus',
                    ),
                    'input' => array(),
                    'submit' => array('title' => self::$module->l('Save', 'LgOneSignalForms'), 'type' => 'submit', 'class' => 'btn btn-default pull-right'),
                ),
            ),
        );


        if (($update == true) && (Tools::isSubmit('id_push'))) {
            $id_push = (int)Tools::getValue('id_push');
            if ((bool)$id_push == true) {
                $form[0]['form']['input'][] = array(
                    'type' => 'free',
                    'name' => 'push_content',
                    'label' => self::$module->l('Content', 'LgOneSignalForms'),
                    'placeholder' => self::$module->l('Update your push content', 'LgOneSignalForms'),
                );
            } else {
                $form[0]['form']['input'][] = array(
                    'type' => 'text',
                    'name' => 'push_content',
                    'label' => self::$module->l('Content', 'LgOneSignalForms'),
                    'desc' => self::$module->l('Enter your content  .', 'LgOneSignalForms'),
                    'placeholder' => self::$module->l('Enter your content', 'LgOneSignalForms'),
                );
            }
        } else {
            $form[0]['form']['input'][] = array(
                'type' => 'text',
                'name' => 'push_content',
                'label' => self::$module->l('Content', 'LgOneSignalForms'),
                'desc' => self::$module->l('Enter your content  .', 'LgOneSignalForms'),
                'placeholder' => self::$module->l('Enter your content', 'LgOneSignalForms'),
            );
        }

        $form[0]['form']['input'][] = array(
            'type' => 'select',
            'name' => 'push_channel',
            'label' => self::$module->l('Channel', 'LgOneSignalForms'),
            'desc' => self::$module->l('At what channel should this content be executed?', 'LgOneSignalForms'),
            'options' => array(
                'query' => self::getChannelOptions(),
                'id' => 'id', 'name' => 'name'
            ),
        );

        $form[0]['form']['input'][] = array(
            'type' => 'select',
            'name' => 'push_mood',
            'label' => self::$module->l('Mood', 'LgOneSignalForms'),
            'desc' => self::$module->l('At what mood should this content be executed?', 'LgOneSignalForms'),
            'options' => array(
                'query' => self::getMoodOptions(),
                'id' => 'id', 'name' => 'name'
            ),
        );

        return $form;
    }

    public static function getPushList()
    {
        return array(
            'push_channel' => array('title' => self::$module->l('Channel', 'LgOneSignalForms'), 'type' => 'text', 'orderby' => false),
            'push_content' => array('title' => self::$module->l('Content', 'LgOneSignalForms'), 'type' => 'text', 'orderby' => false),
            'push_mood' => array('title' => self::$module->l('Mood', 'LgOneSignalForms'), 'type' => 'text', 'orderby' => false),
            'api_response' => array('title' => self::$module->l('Api Response', 'LgOneSignalForms'), 'type' => 'text', 'orderby' => false),
            'total_recipient' => array('title' => self::$module->l('Number Of Recipient', 'LgOneSignalForms'), 'type' => 'text', 'orderby' => false),
            'push_response_id' => array('title' => self::$module->l('Response Id', 'LgOneSignalForms'), 'type' => 'text', 'orderby' => false),
            'created_at' => array('title' => self::$module->l('Created date', 'LgOneSignalForms'), 'type' => 'text', 'orderby' => false),
            'updated_at' => array('title' => self::$module->l('Updated date', 'LgOneSignalForms'), 'type' => 'text', 'orderby' => false),
//            'active' => array('title' => self::$module->l('Active', 'LgOneSignalForms'), 'active' => 'status', 'type' => 'bool', 'align' => 'center', 'orderby' => false),
        );
    }

    public static function getNewPushFormValues()
    {
        return array(
            'push_content' => Tools::safeOutput(Tools::getValue('push_content', null)),
            'push_channel' => Tools::safeOutput(Tools::getValue('push_channel', null)),
            'push_mood' => Tools::safeOutput(Tools::getValue('push_mood', null)),
            'active' => (int)Tools::getValue('active', 1),
        );
    }

    public static function getUpdatePushFormValues()
    {

        $id_push = (int)Tools::getValue('id_push');

        return array(
            'push_content' => '',
            'push_channel' => '',
            'push_mood' => '',
            'active' => '',
        );
    }

    public static function getPushListValues()
    {
        $db_table_name = 'one_signal_push_notify';
        $pushs = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . bqSQL($db_table_name) . '`');

        return $pushs;
    }

    protected static function getChannelOptions()
    {
        $data = array(array('id' => 'MOBILE', 'name' => 'MOBILE'),
            array('id' => 'WEB', 'name' => 'WEB'),
            array('id' => 'EMAIL', 'name' => 'EMAIL'),
            array('id' => 'IN_APP', 'name' => 'IN_APP'),
            array('id' => 'SMS', 'name' => 'SMS'),
        );

        return $data;
    }

    protected static function getMoodOptions()
    {
        $data = array( array('id' => 'DUMMY', 'name' => 'DUMMY'),
            array('id' => 'LIVE', 'name' => 'LIVE')
        );

        return $data;
    }

}
