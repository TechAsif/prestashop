<?php
/**
 * DHL Deutschepost
 *
 * @author    silbersaiten <info@silbersaiten.de>
 * @copyright 2021 silbersaiten
 * @license   See joined file licence.txt
 * @category  Module
 * @support   silbersaiten <support@silbersaiten.de>
 * @version   1.0.11
 * @link      http://www.silbersaiten.de
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__).'/classes/PaperflyAPI.php');
require_once(dirname(__FILE__).'/classes/PaperflyLogging.php');

class PaperFly extends Module
{
    public $paperfly_api;
    public static $conf_prefix = 'PAPERFLY_';


    public function __construct()
    {
        $this->name = 'paperfly';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.11';
        $this->author = 'Rokan';
//        $this->module_key = '96d5521c4c1259e8e87786597735aa4e';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Paperfly LG Tech');
        $this->description = $this->l('Paperfly and Lets go tech shipment service');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->paperfly_api = new PaperflyAPI($this);
    }

    public function install()
    {
        if (!extension_loaded('soap')) {
            $this->_errors[] = $this->l('You need to enable SOAP extension in PHP.');
            return false;
        }

        $return = true;

        $return &= parent::install();

        $return &= $this->createDbTables();
        $return &= $this->installTab('AdminPaperfly', 'Paperfly', 'AdminParentShipping', true);
        $return &= $this->registerHook('displayBackOfficeHeader');
        $return &= $this->registerHook('displayAdminOrder');
        $return &= $this->registerHook('actionOrderReturn');
        $return &= $this->registerHook('actionValidateOrder');
        $return &= $this->registerHook('actionValidateOrderAfter');
        $return &= $this->registerHook('actionObjectOrderReturnUpdateAfter');
        $return &= $this->registerHook('displayHeader');
        $return &= $this->registerHook('actionProductAdd');
        $return &= $this->registerHook('actionProductUpdate');
        $return &= $this->registerHook('actionProductDelete');
        $return &= $this->registerHook('actionProductAttributeDelete');
        $return &= $this->registerHook('displayAdminProductsExtra');
        $return &= ((version_compare(_PS_VERSION_, '1.7', '<')) ? $this->registerHook('extraCarrier') : $this->registerHook('displayAfterCarrier'));
        $return &= $this->createHook('actionGetIDDeliveryAddressByIDCarrier');
        $return &= $this->createHook('actionGetIDOrderStateByIDCarrier');

        return (bool)$return;
    }

    public function uninstall()
    {
        $return = true;
        $return &= $this->uninstallTab('AdminPaperfly');
        $return &= $this->removeHook('actionGetIDDeliveryAddressByIDCarrier');
        $return &= $this->removeHook('actionGetIDOrderStateByIDCarrier');
        $return &= parent::uninstall();

        return (bool)$return;
    }

    public function reset()
    {
        $return = true;
        return (bool)$return;
    }

    public function createHook($name, $title = '')
    {
        if (!Hook::getIdByName($name)) {
            $hook = new Hook();
            $hook->name = $name;
            $hook->title = $title;
            return $hook->add();
        }
        return true;
    }

    public function removeHook($name)
    {
        $id = Hook::getIdByName($name);
        if ($id) {
            $hook = new Hook();
            return $hook->delete();
        }
        return true;
    }

    public function createDbTables()
    {
        $return = true;

        $return &= (bool)Db::getInstance()->Execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'paperfly_order` (
                `id_paperfly_order` int(11) NOT NULL AUTO_INCREMENT,
                `id_cart` int(11) NOT NULL,
                `id_order` int(11) NOT NULL,
                `id_customer` int(11) NOT NULL,
                `tracking_number` varchar(40) NOT NULL,
                `reference` varchar(40) NOT NULL,
                `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ,
                `api_response_status_code` varchar(40),
                `api_response_status_message` varchar(500),
                PRIMARY KEY (`id_paperfly_order`)
                ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8'
        );

        $return &= (bool)Db::getInstance()->Execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'paperfly_order_tracking` (
                `id_paperfly_order_tracking` int(11) NOT NULL AUTO_INCREMENT,
                `id_order` int(11) NOT NULL,
                `reference` varchar(20) NOT NULL,
                `id_paperfly_order` int(11) NOT NULL,
                `tracking_number` varchar(20) NOT NULL,
                `tracking_event_key` varchar(100) NOT NULL,
                `tracking_event_value` varchar(200) NOT NULL,
                `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `api_response_status_code` varchar(40),
                `api_response_status_message` varchar(500),
                PRIMARY KEY (`id_paperfly_order_tracking`)
                ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8'
        );
        return $return;
    }

    public function hookActionValidateOrder($params)
    {

        /*
         * $params['orderReturn']->id_order
         * $params['orderReturn']->id_customer
         * $params['orderReturn']->state = 1
         */

        $order = $params['order'];
        $api_response = $this->paperfly_api->sentToPaperFlyOrder($order, $params['cart']);

        // if paper fly is not accept the order
        if( $api_response['response_code'] != '200' ) {
            // return;
        }
        

        $tracking_resp = $this->paperfly_api->sentToPaperflyOrderTrackingApi($order);
        $tracking_response_data = (($tracking_resp['response_code']) == '200') ? $tracking_resp['success']['trackingStatus'] : '';


        $traking_number =  (($tracking_resp['response_code']) == '200') ?($api_response['success']['tracking_number']) : '';
        $order_api_response_code =  $api_response['response_code'];
        $order_api_response_message = ($order_api_response_code == '200') ? ($api_response['success']['message']) : $api_response['error']['message'];

        $tracking_resp_code = ($tracking_resp['response_code']);
        $tracking_resp_message = ($tracking_resp_code == '200') ? $tracking_resp['success']['message'] : $tracking_resp['error']['message'];
        $reference = $order->reference;
        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'paperfly_order
            (`id_cart`, `id_order`, `id_customer`,`tracking_number`, `reference`,`api_response_status_code`,`api_response_status_message`)
            values(
             ' . (int)$order->id_cart . ',
             ' . (int)$order->id . ',
             ' . (int)$order->id_customer . ',
             "' . $traking_number . '",
             "' . $reference . '",
             "' . $order_api_response_code . '",
             "' . $order_api_response_message . '"
            
               )';

        Db::getInstance()->execute($sql);
        $id_paperfly_order = Db::getInstance()->Insert_ID();

        foreach ((array)$tracking_response_data[0] as $key => $value) {
            $this_key = "'" . $key . "'";
            $this_val = "'" . $value . "'";
            $sql_tracking = 'INSERT INTO ' . _DB_PREFIX_ . 'paperfly_order_tracking
            (`id_order`,`reference`, `id_paperfly_order`, `tracking_number`,`tracking_event_key`,`tracking_event_value`,
            `api_response_status_code`,`api_response_status_message`)
            values(
             ' . (int)$order->id . ',
             "' . $reference . '",
             ' . (int)$id_paperfly_order . ',
             "' . $traking_number . '",
             "' . $this_key . '",
             "' . $this_val . '",
             "' . $tracking_resp_code . '",
             "' . $tracking_resp_message . '"
            )';
            Db::getInstance()->execute($sql_tracking);
        }

    }

    public function filterShipping($shipping, $id_shop)
    {
        $dhl_carriers = $this->getDhlCarriers(true, false, $id_shop);
        $dhl_carriers_ids = array_keys($dhl_carriers);
        $return_shipping = array();
        if (is_array($shipping)) {
            foreach ($shipping as $shipping_item) {
                if (in_array($shipping_item['id_carrier'], $dhl_carriers_ids)) {
                    $shipping_item['default_dhl_product_code'] = $dhl_carriers[$shipping_item['id_carrier']]['product'];
                    $return_shipping[] = $shipping_item;
                }
            }
            return $return_shipping;
        }
        return array();
    }

    public function displayDHLAdminOrder($params)
    {
        $order = new Order((int)$params['id_order']);

        $shipping = $this->filterShipping($order->getShipping(), $order->id_shop);
        $html = '';


        if (is_array($shipping)) {
            foreach ($shipping as $shipping_item) {
                $car = new Carrier((int)$shipping_item['id_carrier']);
                $shipping_item['carrier_name'] = $car->name;


                $this->context->smarty->assign(
                    array(
                        'module_path'         => __PS_BASE_URI__.'modules/'.$this->name.'/',
                        'id_address'          => $order->id_address_delivery,
                        'carrier'             => $shipping_item,
                        'labels'              => false,
                        'last_label'          => false,
                        'enable_return'       => false,
                        'form_action'         => $this->context->link->getAdminLink('AdminOrders', true, array(), array(
                            'vieworder' => 1,
                            'id_order' => (int) $order->id,
                        )),
                        'details_link'        => $this->getModuleUrl(array('view' => 'labelDetails')),
                        'total_products'      => $order->getTotalProductsWithTaxes(),
                        'shipment_date'       => date('Y-m-d'),
                        'self'                => dirname(__FILE__),
                        'module_version' => $this->version,
                        'module_name' => $this->displayName
                    )
                );
            
            }
        }
        return $html;
    }

    public function hookDisplayAdminOrder($params)
    {
    //    $html = $this->displayDPAdminOrder($params);
       $html = $this->displayDHLAdminOrder($params);
       return $html;
    }

    public static function getConfig($key, $id_shop = null)
    {
        return Configuration::get(self::$conf_prefix.$key, null, null, $id_shop);
    }


    public function getTabLink($tab, $params = false)
    {
        $link = 'index.php?controller='.$tab.'&token='.Tools::getAdminTokenLite($tab, $this->context);

        if (is_array($params) && count($params)) {
            foreach ($params as $k => $v) {
                $link .= '&'.$k.'='.$v;
            }
        }

        return $link;
    }


    public function hookDisplayHeader($params)
    {
        if (($this->context->controller instanceof OrderController)) {
            $this->context->controller->addjqueryPlugin('fancybox');
            $this->context->controller->addjqueryPlugin('scrollTo');

                if (version_compare(_PS_VERSION_, '1.7', '<')) {
                    $this->context->controller->addJS($this->_path . 'views/js/private.js');
                    $this->context->controller->addCSS($this->_path . 'views/css/private.css');
                } else {
                    $this->context->controller->registerJavascript('dhl_private', 'modules/' . $this->name . '/views/js/private.js', array('position' => 'bottom', 'priority' => 100));
                    $this->context->controller->registerStylesheet('dhl_private', 'modules/' . $this->name . '/views/css/private.css', array('media' => 'all', 'priority' => 150));
                }

        } 
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        $script = '';

        if (($this->context->controller->controller_name == 'AdminOrders' || $this->context->controller instanceof AdminOrdersController) ) {

            $this->context->controller->addJquery();
            // $this->context->controller->addJS($this->_path . 'views/js/order-list.js');

            // $script .= '<script type="text/javascript">
            //     var dhldp_request_path = "' . $this->getModuleUrl(array('view' => 'viewParamValue')) . '";</script>';

        } elseif (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJquery();
            $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
            // $this->context->controller->addJqueryPlugin(array('idTabs', 'select2'));
            // $this->context->controller->addJqueryPlugin('validate');

            // $this->context->controller->addJS(
            //     _PS_JS_DIR_ . 'jquery/plugins/validate/localization/messages_' . $this->context->language->iso_code . '.js'
            // );
        
            $this->context->controller->addJS($this->_path . 'views/js/admin_configure.js');
            
        }
        return $script;
    }

    public function getModuleUrl($params = false)
    {
        $url = $this->context->link->getAdminLink('AdminModules', true);
        //'index.php?controller=AdminModules&token='.Tools::getAdminTokenLite('AdminModules', $this->context).
        //'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $url .= '&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        if (is_array($params) && count($params)) {
            foreach ($params as $k => $v) {
                $url .= '&'.$k.'='.$v;
            }
        }

        return $url;
    }


    public function installTab($tab_class, $tab_name, $parent = 'AdminModules', $active = false)
    {
        $tab = new Tab();
        $tab->active = (int)$active;
        $tab->class_name = $tab_class;
        $tab->name = array();

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tab_name;
        }

        $tab->id_parent = (int)Tab::getIdFromClassName($parent);
        $tab->module = $this->name;

        return $tab->add();
    }

    public function uninstallTab($tab_class)
    {
        $id_tab = (int)Tab::getIdFromClassName($tab_class);

        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }

        return false;
    }

    public static function logToFile($service, $msg, $key = '')
    {
        $log = new Logging( _PS_MODULE_DIR_.'paperfly/logs/log_'.$key.'.log' );
        $log->lwrite($service."->".$msg.PHP_EOL);
    }

    public function getContent()
    {
        $html = '';
        $view_mode = Tools::getValue('view');


        switch ($view_mode) {
            case 'information':
                $html .= $this->displayInfo();
                break;
            default:
                $html .= $this->postProcess();
                $html .= $this->displayPaperflySettings();
                break;
        }
        return $html;
    }


    public function displayMessages()
    {
        $messages = '';
        foreach ($this->_errors as $error) {
            $messages .= $this->displayError($error);
        }
        foreach ($this->_confirmations as $confirmation) {
            $messages .= $this->displayConfirmation($confirmation);
        }
        return $messages;
    }

    public function displayInfo()
    {
        $this->smarty->assign(
            array(
                '_path'       => $this->_path,
                'displayName' => $this->displayName,
                'author'      => $this->author,
                'description' => $this->description,
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/info.tpl');
    }

    protected function setFormFieldsValue(&$helper, $keys)
    {
        if (is_array($keys)) {
            foreach ($keys as $key) {
                $helper->fields_value[self::$conf_prefix.$key] = Tools::getValue(self::$conf_prefix.$key, self::getConfig($key));
            }
        }
    }


    public function getDhlCarriers($with_referenced_carriers = false, $ids_only = true, $id_shop = null)
    {
        $carriers_data = explode(',', self::getConfig('DHL_CARRIERS', $id_shop));
        $result = array();
        foreach ($carriers_data as $carrier_data) {
            $adata = explode('|', $carrier_data);
            if (isset($adata[1])) {
                $result[(int)$adata[0]] = array('product' => $adata[1]);
            } else {
                $result[(int)$adata[0]] = array('product' => '');
            }
        }
        if ($with_referenced_carriers === false) {
            if ($ids_only == true) {
                return array_keys($result);
            } else {
                return $result;
            }
        } else {
            foreach ($result as $id_carrier => $data) {
                $carrier = new Carrier((int)$id_carrier);
                $ids_referenced_carrier = Db::getInstance()->executeS(
                    'SELECT `id_carrier` FROM `'._DB_PREFIX_.'carrier` WHERE id_reference = '.(int)$carrier->id_reference.' ORDER BY id_carrier'
                );
                foreach ($ids_referenced_carrier as $id_referenced_carrier) {
                    $result[(int)$id_referenced_carrier['id_carrier']] = $data;
                }
            }
            if ($ids_only == true) {
                return array_keys($result);
            } else {
                return $result;
            }
        }
    }

    protected function displayPaperflySettings()
    {
        $helper = new HelperForm();
        $helper->required = false;
        $helper->id = null;
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->table = 'DHLDP_dhl_configure';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->module = $this;
        $helper->identifier = null;
        $helper->toolbar_btn = null;
        $helper->ps_help_context = null;
        $helper->title = null;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = false;
        $helper->bootstrap = true;

        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');

        if (_PS_VERSION_ < '1.6.0.0') {
            $helper->show_toolbar = false;
            $helper->title = $this->displayName;
        }

        $fields_value_keys = array('LIVE_USER','LIVE_PWD','SANDBOX','REF_NUMBER');

        $this->setFormFieldsValue($helper, $fields_value_keys);

        $carriers = Carrier::getCarriers($this->context->language->id, true);
        $option_carriers = array();
        foreach ($carriers as $carrier) {
            $option_carriers[] = array('id_carrier' => $carrier['id_carrier'], 'name' => $carrier['name']);
        }

        $this->context->smarty->assign(
            array(
                'carriers'     => $option_carriers,
                'dhl_carriers' => $this->getDhlCarriers(true, false),
                'link'         => $this->context->link->getAdminLink(
                    'AdminCarrierWizard',
                    false
                ).'&token='.Tools::getAdminTokenLite('AdminCarrierWizard'),
                'added_dhl_products' => []
            )
        );

        return $helper->generateForm($this->getFormFieldsDHLSettings());
    }

    protected function getFormFieldsDHLSettings()
    {
        $form_fields = array(
            'form'  => array(
                'form' => array(
                    'id_form'     => 'dhl_global_settings',
                    'legend'      => array(
                        'title' => $this->l('Global settings'),
                        'icon'  => 'icon-circle',
                    ),
                    'description' => $this->l('Please select mode and fill form with all relevant information regarding authentication in modes.'),
                    'input'       => array(
                        array(
                            'name'   => self::$conf_prefix.'SANDBOX',
                            'type'   => 'radio',
                            'label'  => $this->l('Mode'),
                            'desc'   => $this->l('Select "Sandbox" for testing'),
                            'class'  => 't',
                            'values' => array(
                                array(
                                    'id'    => 'mode_live',
                                    'value' => 1,
                                    'label' => $this->l('Live')
                                ),
                                array(
                                    'id'    => 'mode_sbx',
                                    'value' => 0,
                                    'label' => $this->l('Sandbox')
                                ),
                            ),
                        ),
                        array(
                            'name'             => self::$conf_prefix.'LIVE_USER',
                            'type'             => 'text',
                            'label'            => $this->l('Username'),
                            'desc'             => $this->l('"Live" username for user authentication for business customer shipping API'),
                            'required'         => true,
                            'form_group_class' => 'dhl_authdata_live'
                        ),
                        array(
                            'name'             => self::$conf_prefix.'LIVE_PWD',
                            'type'             => 'text',
                            'label'            => $this->l('Password'),
                            'desc'             => $this->l('"Live" signature for user authentication for business customer shipping API'),
                            'required'         => true,
                            'form_group_class' => 'dhl_authdata_live'
                        ),
                    ),
                    'submit'      => array(
                        'title' => $this->l('Save'),
                        'name'  => 'submitSaveOptions',
                    )
                )
            ),
        );

        return $form_fields;
    }

    public function postProcess()
    {
        switch (Tools::getValue('m')) {
            case 1:
                $this->_confirmations[] = $this->l('"Live" account data has been reset.');
                break;
            case 3:
                $this->_confirmations[] = $this->l('Fixed');
                break;
        }

        if (Tools::isSubmit('resetLiveAccount')) {
            if (Configuration::updateValue(self::$conf_prefix.'LIVE_USER', '') &&
                Configuration::updateValue(self::$conf_prefix.'LIVE_PWD', '')
            ) {
                Tools::redirectAdmin($this->getModuleUrl().'&m=1');
            }
        }

        if (Tools::isSubmit('submitAddDHLDP_dhl_configure')) {
            $form_errors = array();

            $dhl_live_user = Tools::getValue(self::$conf_prefix.'LIVE_USER');
            $dhl_live_sign = Tools::getValue(self::$conf_prefix.'LIVE_PWD');
            $dhl_mode = Tools::getValue(self::$conf_prefix.'SANDBOX');

            if ( $dhl_live_user == '') {
                $form_errors[] = $this->_errors[] = $this->l('Please fill username');
            }

            if ( $dhl_live_sign == '') {
                $form_errors[] = $this->_errors[] = $this->l('Please fill password');
            }

            // if (count($form_errors) == 0) {
                
            //     $check_client = $this->paperfly_api->checkAccount( $dhl_live_user, $dhl_live_sign);

            //     self::logToFile('AccountStatus->', $check_client, 'general');

            //     $clinetStatus = json_decode($check_client, true);
            //     if( $clinetStatus['status'] == 'success' ) {
            //         $this->_confirmations[] = $this->l($clinetStatus['msg']);
            //     } else {
            //         $form_errors[] = $this->_errors[] = $this->l($clinetStatus['msg']);
                    
            //     }
                
            // }

            if (count($form_errors) == 0) {
                $result_save = Configuration::updateValue(self::$conf_prefix.'LIVE_USER', Tools::getValue(self::$conf_prefix.'LIVE_USER')) &&
                Configuration::updateValue(self::$conf_prefix.'LIVE_PWD', Tools::getValue(self::$conf_prefix.'LIVE_PWD')) &&
                Configuration::updateValue(self::$conf_prefix.'SANDBOX', Tools::getValue(self::$conf_prefix.'SANDBOX'));

                if ($result_save == true) {
                    $this->_confirmations[] = $this->l('Settings updated');
                }
            }
        }
        return $this->displayMessages();
    }

    public function filterDPShipping($shipping, $id_shop)
    {
        $return_shipping = array();
        if (is_array($shipping)) {
            foreach ($shipping as $shipping_item) {
                if (in_array($shipping_item['id_carrier'], $this->getDPCarriers(true, $id_shop))) {
                    $return_shipping[] = $shipping_item;
                }
            }
            return $return_shipping;
        }
        return array();
    }

}




