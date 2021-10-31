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

require_once(dirname(__FILE__).'/classes/ECourierApi.php');
require_once(dirname(__FILE__).'/classes/ECourierLogging.php');

class ECourier extends Module
{
    public $ecourier_api;
    public static $conf_prefix = 'ECOURIER_';


    public function __construct()
    {
        $this->name = 'ecourier';
        $this->tab = 'shipping_logistics';
        $this->version = '0.0.1';
        $this->author = 'Bozlur Rahman';
        //    $this->module_key = '96d5521c4c1259e8e87786597735aa4e';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('ECourier LG Tech');
        $this->description = $this->l('ECourier and Lets go tech shipment service');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->ecourier_api = new ECourierApi($this);
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
		$this->is177 = version_compare(_PS_VERSION_, '1.7.7.0') >= 0 ? 1 : 0;
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
        $return &= $this->installTab('AdminECourierManifest', 'ECOURIER', 'AdminParentShipping', true);
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
        $return &= $this->uninstallTab('AdminECourierManifest');
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
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ecourier_order` (
                `id_ecourier_order` int(11) NOT NULL AUTO_INCREMENT,
                `id_cart` int(11) NOT NULL,
                `id_order` int(11) NOT NULL,
                `id_customer` int(11) NOT NULL,
                `tracking_number` varchar(40) NOT NULL,
                `reference` varchar(40) NOT NULL,
                `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ,
                `api_response_status` varchar(40),
                `api_response_message` varchar(500),
                PRIMARY KEY (`id_ecourier_order`)
                ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8'
        );

        $return &= (bool)Db::getInstance()->Execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ecourier_order_tracking` (
                `id_ecourier_order_tracking` int(11) NOT NULL AUTO_INCREMENT,
                `id_order` int(11) NOT NULL,
                `reference` varchar(20) NOT NULL,
                `id_ecourier_order` int(11) NOT NULL,
                `tracking_number` varchar(20) NOT NULL,
                `parcel_status` varchar(200) NOT NULL,
                `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `api_response_status` varchar(40),
                `api_response_message` varchar(500),
                PRIMARY KEY (`id_ecourier_order_tracking`)
                ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8'
        );
        return $return;
    }

    public function hookActionOrderReturn($params)
    {
        /*
         * $params['orderReturn']->id_order
         * $params['orderReturn']->id_customer
         * $params['orderReturn']->state = 1
         */
        $order = new Order((int)$params['orderReturn']->id_order);
        if (Validate::isLoadedObject($order) ) {
                $order_carriers = $this->filterShipping($order->getShipping(), (int)$order->id_shop);
                if (is_array($order_carriers) && count($order_carriers) > 0) {
                    // change state
                    $params['orderReturn']->state = 2;
                    $params['orderReturn']->save();

                    // mail will be send on hookActionObjectOrderReturnUpdateAfter
                }
        }
    }

    public function hookActionValidateOrder($params)
    {

        $order = $params['order'];
        $api_response = $this->ecourier_api->sentOrderToECourier($order);
        
        $tracking_id = (isset($api_response['tracking_id']) && $api_response['tracking_id']) ? $api_response['tracking_id'] : null;
        
        $tracking_response = $this->ecourier_api->sentOrderToECourierTrackingApi($tracking_id);

        $tracking_response_data = (isset($tracking_response['data']) && isset($tracking_response['data']['trackInfos']) && $tracking_response['data']['trackInfos']) ? $tracking_response['data']['trackInfos'] : [];
        
        $reference = $order->reference;
        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'ecourier_order
            (`id_cart`, `id_order`, `id_customer`,`tracking_number`, `reference`,`api_response_status`,`api_response_message`)
            values(
             ' . (int)$order->id_cart . ',
             ' . (int)$order->id . ',
             ' . (int)$order->id_customer . ',
             "' . $tracking_id . '",
             "' . $reference . '",
             "' . $api_response['status']. '",
             "' . $api_response['msg'] . '"
            
               )';

        Db::getInstance()->execute($sql);
        $id_ecourier_order = Db::getInstance()->Insert_ID();

        foreach ($tracking_response_data as $key => $value) {
            $sql_tracking = 'INSERT INTO ' . _DB_PREFIX_ . 'ecourier_order_tracking
            (`id_order`,`reference`, `id_ecourier_order`, `tracking_number`,`parcel_status`,
            `api_response_status`,`api_response_message`)
            values(
             ' . (int)$order->id . ',
             "' . $reference . '",
             ' . (int)$id_ecourier_order . ',
             "' . $tracking_id . '",
             "' . $value['parcelStatus'] . '",
             "' . $tracking_response['code']. '",
             "' . $tracking_response['msg'] . '"
            )';
            Db::getInstance()->execute($sql_tracking);
        }

    }

    public function getTranslationPFApiMessage($key)
    {
        $trans = array(
            'No result available.' => $this->l('No result available.'),
            'Zip or city required.' => $this->l('Zip or city required.'),
            'Missing street.' => $this->l('Missing street.'),
            'Invalid zip.' => $this->l('Invalid zip.'),
            'Invalid zip length.' => $this->l('Invalid zip length.'),
            'Invalid city length.' => $this->l('Invalid city length.'),
            'Invalid street length.' => $this->l('Invalid street length.'),
            'Invalid street number length.' => $this->l('Invalid street number length.'),
        );
        if (isset($trans[$key])) {
            return $trans[$key];
        }
        return $key;
    }

    public function hookDisplayHeader($params)
    {
        if (($this->context->controller instanceof OrderController)) {
            // $this->context->controller->addJquery();
            // $this->context->controller->addjqueryPlugin('fancybox');
            // $this->context->controller->addjqueryPlugin('scrollTo');
            // $this->context->controller->addJS($this->_path . 'views/js/private.js');
            // $this->context->controller->addCSS($this->_path . 'views/css/private.css');
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


    public function getShippedOrderStates($just_ids = false)
    {
        $states = array();
        foreach (OrderState::getOrderStates($this->context->language->id) as $state) {
            if ($just_ids) {
                $states[] = $state['id_order_state'];
            } else {
                $states[] = $state;
            }
        }
        return $states;
    }

    public function displayDPAdminOrder($params)
    {
        $order = new Order((int)$params['id_order']);
        $shipping = $this->filterDPShipping($order->getShipping(), (int)$order->id_shop);

        $html = '';
        if (is_array($shipping)) {
            foreach ($shipping as $shipping_item) {
                $this->context->controller->addCSS($this->_path.'views/css/admin.css');
                $this->context->smarty->assign(
                    array(
                        'module_path' => __PS_BASE_URI__.'modules/'.$this->name.'/',
                        'id_address' => $order->id_address_delivery,
                        'is177' => $this->is177,
                        'carrier' => $shipping_item,
                        'labels' => false,
                        'last_label' => false,
                        'form_action'         => $this->context->link->getAdminLink('AdminOrders', true, array(), array(
                            'vieworder' => 1,
                            'id_order' => (int) $order->id,
                        )),
                        'details_link' => $this->getModuleUrl(array('view' => 'labelDetails')),
                        'module_version' => $this->version,
                        'module_name' => $this->displayName
                    )
                );
                $html .= $this->display(__FILE__, 'dp-admin-carriers.tpl');
            }
        }
//        var_dump($html);
        return $html;
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
                        'total_weight'        => $this->getOrderWeight($order),
                        'package_length' 	  => self::getConfig('DHL_DEFAULT_LENGTH', $order->id_shop),
                        'package_width' 	  => self::getConfig('DHL_DEFAULT_WIDTH', $order->id_shop),
                        'package_height' 	  => self::getConfig('DHL_DEFAULT_HEIGHT', $order->id_shop),
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
       $html = $this->displayDPAdminOrder($params);
       $html .= $this->displayDHLAdminOrder($params);
       return $html;
    }

    public function getOrderWeight($order)
    {
        $weight = 0;
        
        return $weight;
    }



    public static function getConfig($key, $id_shop = null)
    {
        return Configuration::get(self::$conf_prefix.$key, null, null, $id_shop);
    }

    public function updateOrderStatus($id_order_carrier)
    {
        $order_carrier = new OrderCarrier((int)$id_order_carrier);
        $order = new Order((int)$order_carrier->id_order);
        $id_os = self::getConfig('DHL_CHANGE_OS', (int)$order->id_shop);
        $ret = Hook::exec('actionGetIDOrderStateByIDCarrier', array('id_carrier' => $order_carrier->id_carrier, 'id_shop' => (int)$order->id_shop), null, true);

        if (isset($ret['dhlcarrieraddress']['id_os'])) {
            $id_os_updated = $ret['dhlcarrieraddress']['id_os'];
            if ($id_os_updated === 0) {
                return true;
            } elseif ($id_os_updated != '' && $id_os_updated != -1) {
                $id_os = $id_os_updated;
            }
        }

        $order_state = new OrderState((int)$id_os);

        if (($id_os != '') && in_array((int)$id_os, $this->getShippedOrderStates(true))) {
            if (Validate::isLoadedObject($order_state)) {

                $current_order_state = $order->getCurrentOrderState();
                if ($current_order_state->id != $order_state->id) {
                    // Create new OrderHistory
                    $history = new OrderHistory();
                    $history->id_order = (int)$order->id;
                    $history->id_employee = (int)$this->context->employee->id;

                    $use_existings_payment = false;
                    if (!$order->hasInvoice()) {
                        $use_existings_payment = true;
                    }
                    $history->changeIdOrderState((int)$order_state->id, $order, $use_existings_payment);
                    $carrier = new Carrier($order->id_carrier, $order->id_lang);
                    $templateVars = array();
                    if ($history->id_order_state == Configuration::get('PS_OS_SHIPPING') && $order->shipping_number) {
                        $templateVars = array('{followup}' => str_replace('@', $order->shipping_number, $carrier->url));
                    }
                    if (isset($ret[['dhlcarrieraddress']]['id_os']) && isset($ret[['dhlcarrieraddress']]['send_changeos']) && $ret[['dhlcarrieraddress']]['send_changeos'] == 1) {
                        if ($history->addWithemail(true, $templateVars)) {
                            if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                                foreach ($order->getProducts() as $product) {
                                    if (StockAvailable::dependsOnStock($product['product_id'])) {
                                        StockAvailable::synchronize($product['product_id'], (int)$product['id_shop']);
                                    }
                                }
                            }
                            return true;
                        }
                    } else {
                        if ($history->add(true)) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    public function updateOrderCarrierWithTrackingNumber($id_order_carrier, $tracking_number)
    {
        $order_carrier = new OrderCarrier((int)$id_order_carrier);

        if (Validate::isLoadedObject($order_carrier)) {
            $order = new Order((int)$order_carrier->id_order);

            $order->shipping_number = $tracking_number;
            $order->update();

            $order_carrier->tracking_number = $tracking_number;

            if ($order_carrier->update()) {
                $customer = new Customer((int)$order->id_customer);
                $carrier = new Carrier((int)$order->id_carrier, $order->id_lang);

				$ret = Hook::exec('actionGetIDOrderStateByIDCarrier', array('id_carrier' => $order_carrier->id_carrier, 'id_shop' => $order->id_shop), null, true);

				// don't send in_transit if 0
                if (isset($ret['dhlcarrieraddress']['id_os']) && isset($ret['dhlcarrieraddress']['send_intransit']) && $ret['dhlcarrieraddress']['send_intransit'] == 0) {
                    return true;
                }

                // Send mail to customer
                if (self::getConfig('DHL_INTRANSIT_MAIL', $order->id_shop)) {
                    $tracking_url = '';

                    $template_vars = array(
                        '{followup}'        => $tracking_url,
                        '{firstname}'       => $customer->firstname,
                        '{lastname}'        => $customer->lastname,
                        '{id_order}'        => $order->id,
                        '{shipping_number}' => $order->shipping_number,
                        '{order_name}'      => $order->getUniqReference()
                    );

                    Mail::Send(
                        (int)$order->id_lang,
                        'in_transit',
                        $this->l('Package in transit'),
                        $template_vars,
                        $customer->email,
                        $customer->firstname.' '.$customer->lastname,
                        null,
                        null,
                        null,
                        null,
                        _PS_MAIL_DIR_,
                        true,
                        (int)$order->id_shop
                    );
                }

                Hook::exec(
                    'actionAdminOrdersTrackingNumberUpdate',
                    array('order' => $order, 'customer' => $customer, 'carrier' => $carrier),
                    null,
                    false,
                    true,
                    false,
                    $order->id_shop
                );

                return true;
            }
        }

        return false;
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
            // $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
            // $this->context->controller->addJqueryPlugin(array('idTabs', 'select2'));
            // $this->context->controller->addJqueryPlugin('validate');

            // $this->context->controller->addJS(
            //     _PS_JS_DIR_ . 'jquery/plugins/validate/localization/messages_' . $this->context->language->iso_code . '.js'
            // );
        
            // $this->context->controller->addJS($this->_path . 'views/js/admin_configure.js');
            
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
        $log = new Logging( _PS_MODULE_DIR_.'ecourier/logs/log_'.$key.'.log' );
        $log->lwrite($service."->".$msg.PHP_EOL);
    }

    public function getContent()
    {
        $html = '';
        $view_mode = Tools::getValue('view');
        $html .= $this->postProcess();
        $html .= $this->displayECourierSettings();
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

    protected function displayECourierSettings()
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

        $fields_value_keys = array('LIVE_USER','LIVE_PWD','LIVE_TOKEN','LIVE_RESET','LOG','REF_NUMBER');

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
                        array(
                            'name'  => self::$conf_prefix.'LIVE_RESET',
                            'type'  => 'free',
                            'label' => '',
                        ),
                        // array(
                        //     'name'     => self::$conf_prefix.'LOG',
                        //     'type'     => 'radio',
                        //     'label'    => $this->l('Enable Log'),
                        //     'desc'     => $this->l('Logs of actions in').' '.DIRECTORY_SEPARATOR.'logs '.
                        //         $this->l('directory. Please notice: logs information can take a lot of disk space after a time.'),
                        //     'class'    => 't',
                        //     'is_bool'  => true,
                        //     'disabled' => false,
                        //     'values'   => array(
                        //         array(
                        //             'id'    => 'log_yes',
                        //             'value' => 1,
                        //             'label' => $this->l('Yes')
                        //         ),
                        //         array(
                        //             'id'    => 'log_no',
                        //             'value' => 0,
                        //             'label' => $this->l('No')
                        //         ),
                        //     ),
                        // ),
                        // array(
                        //     'name'     => self::$conf_prefix.'REF_NUMBER',
                        //     'type'     => 'radio',
                        //     'label'    => $this->l('Reference number in label is '),
                        //     'required' => true,
                        //     'class'    => 't',
                        //     'br'       => true,
                        //     'values'   => array(
                        //         array(
                        //             'id'    => 'order_ref',
                        //             'value' => 0,
                        //             'label' => $this->l('Order reference')
                        //         ),
                        //         array(
                        //             'id'    => 'order_number',
                        //             'value' => 1,
                        //             'label' => $this->l('Order ID')
                        //         )
                        //     )
                        // ),
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

            if ( $dhl_live_user == '') {
                $form_errors[] = $this->_errors[] = $this->l('Please fill username');
            }

            if ( $dhl_live_sign == '') {
                $form_errors[] = $this->_errors[] = $this->l('Please fill password');
            }

            if (count($form_errors) == 0) {
                
                $check_client = $this->ecourier_api->checkAccount( $dhl_live_user, $dhl_live_sign);

                self::logToFile('DHL', $check_client, 'general');

                $clinetStatus = json_decode($check_client, true);
                if( $clinetStatus['status'] == 'success' ) {
                    $this->_confirmations[] = $this->l($clinetStatus['msg']);
                } else {
                    $form_errors[] = $this->_errors[] = $this->l($clinetStatus['msg']);
                    
                }
                
            }

            if (count($form_errors) == 0) {
                $result_save = Configuration::updateValue(self::$conf_prefix.'LIVE_USER', Tools::getValue(self::$conf_prefix.'LIVE_USER')) &&
                Configuration::updateValue(self::$conf_prefix.'LIVE_PWD', Tools::getValue(self::$conf_prefix.'LIVE_PWD')) &&
                Configuration::updateValue(self::$conf_prefix.'LIVE_TOKEN', $clinetStatus['token']);

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
    
    public function getDPCarriers($with_referenced_carriers = false, $id_shop = null)
    {
        $ids_carrier = explode(',', self::getConfig('DP_CARRIERS', $id_shop));
        if ($with_referenced_carriers === false) {
            return $ids_carrier;
        } else {
            $ids_ref_carriers = array();
            foreach ($ids_carrier as $id_carrier) {
                $carrier = new Carrier((int)$id_carrier);
                $ids_referenced_carrier = Db::getInstance()->executeS(
                    'SELECT `id_carrier` FROM `'._DB_PREFIX_.'carrier` WHERE id_reference = '.(int)$carrier->id_reference.' ORDER BY id_carrier'
                );
                foreach ($ids_referenced_carrier as $id_referenced_carrier) {
                    $ids_ref_carriers[] = $id_referenced_carrier['id_carrier'];
                }
            }
            return $ids_ref_carriers;
        }
    }


}
