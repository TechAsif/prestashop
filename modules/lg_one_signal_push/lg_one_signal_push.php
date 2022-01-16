<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

if (defined('_PS_ADMIN_DIR_') === false) {
    define('_PS_ADMIN_DIR_', _PS_ROOT_DIR_ . '/admin/');
}

require_once(dirname(__FILE__) . '/classes/LgOneSignalForms.php');
require_once(dirname(__FILE__) . '/controllers/admin/AdminOneSignalApiController.php');

class Lg_one_signal_push extends Module
{
    const EACH = -1;

    protected $_successes;
    protected $_warnings;


    public function __construct()
    {
        $this->name = 'lg_one_signal_push';
        $this->tab = 'administration';
        $this->version = '1.4.0';
        $this->module_key = '';

        $this->author = 'rokan';
        $this->need_instance = true;

        $this->bootstrap = true;
        $this->display = 'view';
        parent::__construct();

        if ($this->id) {
            $this->init();
        }

        $this->displayName = $this->l('One Signal Push Notification');
        $this->description = $this->l('Manage all types of one signal push notification.');

        if (function_exists('curl_init') == false) {
            $this->warning = $this->l('To be able to use this module, please activate cURL (PHP extension).');
        }
    }

    public function install()
    {
        Configuration::updateValue('LG_ONE_SIGNAL_ADMIN_DIR', Tools::encrypt($this->getAdminDir()));
        Configuration::updateValue('LG_ONE_SIGNAL_MODULE_VERSION', $this->version);
        $token = Tools::encrypt(Tools::getShopDomainSsl() . time());
        Configuration::updateGlobalValue('LG_ONE_SIGNAL_EXECUTION_TOKEN', $token);

        if (parent::install()) {
            return $this->installDb()  &&
                $this->registerHook('backOfficeHeader');
        }

        return false;
    }

    protected function getAdminDir()
    {
        return basename(_PS_ADMIN_DIR_);
    }

    protected function init()
    {

    }

    public function uninstall()
    {
        return $this->uninstallDb() &&
            parent::uninstall();
    }

    public function installDb()
    {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'one_signal_push_notify` (
            `id_push` INTEGER(10) NOT NULL AUTO_INCREMENT,
            `push_channel` TEXT DEFAULT NULL,
            `push_content` tinytext DEFAULT NULL,
            `push_mood` TEXT DEFAULT NULL,
            `api_response` tinytext DEFAULT NULL,
            `total_recipient` INTEGER(10) DEFAULT NULL,
            `push_response_id` TEXT DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT NULL,
            `active` BOOLEAN DEFAULT FALSE,
            PRIMARY KEY(`id_push`),
            INDEX (`id_push`))
            ENGINE=' . _MYSQL_ENGINE_ . ' default CHARSET=utf8'
        );
    }

    public function uninstallDb()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'one_signal_push_notify`');
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            if (version_compare(_PS_VERSION_, '1.6', '<') == true) {
                $this->context->controller->addCSS($this->_path . 'views/css/bootstrap.min.css');
                $this->context->controller->addCSS($this->_path . 'views/css/configure-ps-15.css');
            } else {
                $this->context->controller->addCSS($this->_path . 'views/css/configure-ps-16.css');
            }
        }
    }

    public function getContent()
    {
        $output = null;
        LgOneSignalForms::init($this);
//        $this->checkLocalEnvironment();

        if (Tools::isSubmit('submitNewPush')) {
            $submit_push = $this->postProcessNewPush();
        } elseif (Tools::isSubmit('submitUpdatePush')) {
            $submit_push = $this->postProcessUpdatePush();
        }

        $this->context->smarty->assign(array(
            'module_dir' => $this->_path,
            'module_local_dir' => $this->local_path,
        ));

        $this->context->smarty->assign('form_errors', $this->_errors);
        $this->context->smarty->assign('form_infos', $this->_warnings);
        $this->context->smarty->assign('form_successes', $this->_successes);

        if ((Tools::isSubmit('submitNewPush') || Tools::isSubmit('newpush') || Tools::isSubmit('updatepush')) &&
            ((isset($submit_push) == false) || ($submit_push === false))) {
            $back_url = $this->context->link->getAdminLink('AdminModules', false)
                . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name
                . '&token=' . Tools::getAdminTokenLite('AdminModules');
        }

        $output = $output . $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        if (Tools::isSubmit('newpush') || ((isset($submit_push) == true) && ($submit_push === false))) {
            $output = $output . $this->renderForm(LgOneSignalForms::getPushForm(), LgOneSignalForms::getNewPushFormValues(), 'submitNewPush', true, $back_url);
        } elseif (Tools::isSubmit('updatepush') && Tools::isSubmit('id_push')) {
            $form_structure = LgOneSignalForms::getPushForm('Update push', true);
            $form = $this->renderForm($form_structure, LgOneSignalForms::getUpdatePushFormValues(), 'submitUpdatePush', true, $back_url, true);
            $output = $output . $form;
        } elseif (Tools::isSubmit('deletepush') && Tools::isSubmit('id_push')) {
            $this->postProcessDeletePush((int)Tools::getValue('id_push'));
        } elseif (Tools::isSubmit('statuspush')) {
            $this->postProcessUpdatePushStatus();
        }

        return $output . $this->renderPushList();
    }


    public static function isActive($id_module)
    {
        $module = Module::getInstanceByName('lg_one_signal_push');

        if (($module == false) || ($module->active == false)) {
            return false;
        }

        $query = 'SELECT `active` FROM ' . _DB_PREFIX_ . 'lg_one_signal_push WHERE `id_module` = \'' . (int)$id_module . '\'';
        return (bool)Db::getInstance()->getValue($query);
    }


    protected function renderForm($form, $form_values, $action, $cancel = false, $back_url = false, $update = false)
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = $action;

        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;

        if ($update == true) {
            $helper->currentIndex .= '&id_push=' . (int)Tools::getValue('id_push');
        }

        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $form_values,
            'id_language' => $this->context->language->id,
            'languages' => $this->context->controller->getLanguages(),
            'back_url' => $back_url,
            'show_cancel_button' => $cancel,
        );

        return $helper->generateForm($form);
    }

    protected function renderPushList()
    {
        $helper = new HelperList();

        $helper->title = $this->l('Push Contents');
        $helper->table = _DB_PREFIX_ . 'one_signal_push_notify';
        $helper->no_link = true;
        $helper->shopLinkType = '';
        $helper->identifier = 'id_push';
//        $helper->actions = array('edit', 'delete');

        $values = LgOneSignalForms::getPushListValues();
        $helper->listTotal = count($values);
        $helper->tpl_vars = array('show_filters' => false);

        $helper->toolbar_btn['new'] = array(
            'href' => $this->context->link->getAdminLink('AdminModules', false)
                . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name
                . '&newpush=1&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add new push')
        );

        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;


        return $helper->generateList($values, LgOneSignalForms::getPushList());
    }


    protected function postProcessNewPush()
    {

        $push_content = '"'  . Tools::getValue('push_content') . '"';
        $push_channel = "'" . Tools::getValue('push_channel') . "'";
        $push_mood = "'" . Tools::getValue('push_mood') . "'";

        if ($push_channel) {
            $db_table_name = 'one_signal_push_notify';
            $query = 'INSERT INTO ' . _DB_PREFIX_ . bqSQL($db_table_name) . '
                    (`push_content`, `push_channel`, `push_mood`, `active`)
                    VALUES (' . $push_content . ', ' . $push_channel . ',' . $push_mood . ', 1)';

            Db::getInstance()->execute($query);
            $push_id = Db::getInstance()->Insert_ID();
            if ($push_id != null) {
               $response= AdminOneSignalApiController::sendMessage($push_id,$push_content,$push_channel,Tools::getValue('push_mood'));
                return $this->setSuccessMessage('The notification has been successfully added.');
            }
            return $this->setErrorMessage('An error happened: the task could not be added.');
        }
        return $this->setErrorMessage('Please select a channel.');
    }

    protected function postProcessUpdatePush()
    {
        if (Tools::isSubmit('id_push') == false) {
            return false;
        }
        $db_table_name = 'one_signal_push_notify';
        $push_content = "'" . Db::getInstance()->escape(Tools::getValue('push_content')) . "'";
        $push_channel = "'" . Tools::getValue('push_channel') . "'";
        $id_push = (int)Tools::getValue('id_push');


        $query = 'UPDATE ' . _DB_PREFIX_ . bqSQL($db_table_name) . '
            SET `push_content` = ' . $push_content . ',
                `push_channel` = ' . $push_channel . '
            WHERE `id_push` = ' . (int)$id_push . '';

        if ((Db::getInstance()->execute($query)) != false) {
            return $this->setSuccessMessage('The content has been updated.');
        }
        return $this->setErrorMessage('The content has not been updated');
    }

    protected function postProcessUpdatePushStatus()
    {
        if (Tools::isSubmit('id_push') == false) {
            return false;
        }
        $db_table_name = 'one_signal_push_notify';
        $id_push = (int)Tools::getValue('id_push');


        $query = 'UPDATE ' . _DB_PREFIX_ . bqSQL($db_table_name) . '
            SET `active` = IF (`active`, 0, 1)
            WHERE `id_push` = ' . (int)$id_push . '';


        Db::getInstance()->execute($query);

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name
            . '&token=' . Tools::getAdminTokenLite('AdminModules'));
    }


    protected function setErrorMessage($message)
    {
        $this->_errors[] = $this->l($message);
        return false;
    }

    protected function setSuccessMessage($message)
    {
        $this->_successes[] = $this->l($message);
        return true;
    }

    protected function setWarningMessage($message)
    {
        $this->_warnings[] = $this->l($message);
        return false;
    }

    protected function postProcessDeletePush($id_push)
    {
        $id_push = Tools::getValue('id_push');
        $id_module = Db::getInstance()->getValue('SELECT `id_module` FROM ' . _DB_PREFIX_ . bqSQL($this->name) . ' WHERE `id_push` = \'' . (int)$id_push . '\'');

        if ((bool)$id_module == false) {
            Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . bqSQL($this->name) . ' WHERE `id_push` = \'' . (int)$id_push . '\'');
        } else {
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . bqSQL($this->name) . ' SET `active` = FALSE WHERE `id_push` = \'' . (int)$id_push . '\'');
        }

        return Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name
            . '&token=' . Tools::getAdminTokenLite('AdminModules'));
    }

}
