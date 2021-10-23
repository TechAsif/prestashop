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

require_once(dirname(__FILE__).'/classes/FlingexDHLFlingexApi.php');
require_once(dirname(__FILE__).'/classes/FLINGEXLabel.php');
require_once(dirname(__FILE__).'/classes/FlingexPackage.php');
require_once(dirname(__FILE__).'/classes/FlingexOrder.php');
require_once(dirname(__FILE__).'/classes/FlingexLGApi.php');
require_once(dirname(__FILE__).'/classes/FlingexLGApiTracking.php');
require_once(dirname(__FILE__).'/classes/FlingexApi.php');
require_once(dirname(__FILE__).'/classes/FlingExLGLabel.php');
require_once(dirname(__FILE__).'/classes/Logging.php');

class Flingex extends Module
{
    public $flingex_api;
    public static $conf_prefix = '_FLINGEX';


    public function __construct()
    {
        $this->name = 'flingex';
        $this->tab = 'shipping_logistics';
        $this->version = '0.0.1';
        $this->author = 'Bozlur Rahman';
//        $this->module_key = '96d5521c4c1259e8e87786597735aa4e';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Flingex LG Tech');
        $this->description = $this->l('Flingex and Lets go tech shipment service');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->flingex_api = new FlingexDHLFlingexApi($this);
        $this->dp_api = new FlingexApi();
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
        $return &= $this->installTab('AdminDhldpManifest', 'FLINGEX', 'AdminParentShipping', true);
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

        $this->dp_api->retrievePageFormats();

        return (bool)$return;
    }

    public function uninstall()
    {
        $return = true;
        $return &= $this->uninstallTab('AdminDhldpManifest');
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
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'flingex_label` (
                `id_flingex_label` int(11) NOT NULL AUTO_INCREMENT,
                `id_order_carrier` int(11) NOT NULL,
                `product_code` varchar(30) NOT NULL,
                `options` text,
                `shipment_number` varchar(255) DEFAULT NULL,
                `label_url` varchar(500) DEFAULT NULL,
                `export_label_url` varchar(500) DEFAULT NULL,
                `cod_label_url` varchar(500) DEFAULT NULL,
                `return_label_url` varchar(500) DEFAULT NULL,
                `is_complete` tinyint(1) NOT NULL DEFAULT \'0\',
                `is_return` tinyint(1) NOT NULL DEFAULT \'0\',
                `with_return` tinyint(1) NOT NULL DEFAULT \'0\',
                `api_version` varchar(10) NOT NULL DEFAULT \'1.0\',
                `shipment_date` datetime,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                `id_order_return` int(11),
                `routing_code` varchar(50),
                `idc` varchar(50),
                `idc_type` varchar(20),
                `int_idc` varchar(50),
                `int_idc_type` varchar(20),
                PRIMARY KEY (`id_flingex_label`)
                ) ENGINE='._MYSQL_ENGINE_.'  DEFAULT CHARSET=utf8'
        );
        $return &= (bool)Db::getInstance()->Execute(
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'flingex_package` (
                `id_flingex_package` int(11) NOT NULL AUTO_INCREMENT,
                `id_flingex_label` int(11) NOT NULL,
                `length` int(11) NOT NULL DEFAULT \'0\',
                `width` int(11) NOT NULL DEFAULT \'0\',
                `height` int(11) NOT NULL DEFAULT \'0\',
                `weight` decimal(20,6) NOT NULL DEFAULT \'0\',
                `package_type` varchar(30) NOT NULL,
                `shipment_number` varchar(255) DEFAULT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_flingex_package`)
                ) ENGINE='._MYSQL_ENGINE_.'  DEFAULT CHARSET=utf8'
        );

//        $return &= (bool)Db::getInstance()->Execute(
//            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'flingex_order` (
//                `id_flingex_order` int(11) NOT NULL AUTO_INCREMENT,
//                `id_cart` int(11) NOT NULL,
//                `id_order` int(11),
//                `permission_tpd` tinyint(1) NOT NULL DEFAULT \'0\',
//                `date_add` datetime NOT NULL,
//                `date_upd` datetime NOT NULL,
//                PRIMARY KEY (`id_flingex_order`)
//                ) ENGINE='._MYSQL_ENGINE_.'  DEFAULT CHARSET=utf8'
//        );

        $return &= (bool)Db::getInstance()->Execute(
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'flingex_product_customs` (
                `id_product` int(11) NOT NULL,
                `id_product_attribute` int(11),
                `customs_tariff_number` varchar(10),
                `country_of_origin` varchar(2),
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_product`, `id_product_attribute`)
                ) ENGINE='._MYSQL_ENGINE_.'  DEFAULT CHARSET=utf8'
        );

        $return &= (bool)Db::getInstance()->Execute(
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'flingex_lg_label` (
                `id_flingex_lg_label` int(11) NOT NULL AUTO_INCREMENT,
                `id_order_carrier` int(11) NOT NULL,
                `product` int(11) NOT NULL,
                `total` decimal(20,6) NOT NULL DEFAULT \'0\',
                `wallet_ballance` decimal(20,6) NOT NULL DEFAULT \'0\',
                `additional_info` varchar(80) DEFAULT NULL,
                `dp_order_id` varchar(255) DEFAULT NULL,
                `dp_voucher_id` varchar(64) DEFAULT NULL,
                `dp_link` varchar(255) DEFAULT NULL,
                `is_complete` tinyint(1) NOT NULL DEFAULT \'0\',
                `dp_track_id` varchar(64) DEFAULT NULL,
                `manifest_link` varchar(255) DEFAULT NULL,
                `label_format` varchar(3) DEFAULT NULL,
                `label_position` varchar(255) DEFAULT NULL,
                `page_format_id` int(11) NOT NULL  DEFAULT \'0\',
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_flingex_lg_label`)
                ) ENGINE='._MYSQL_ENGINE_.'  DEFAULT CHARSET=utf8'
        );
        $return &= (bool)Db::getInstance()->Execute(
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'flingex_lg_productlist` (
                `id_flingex_lg_productlist` int(11) NOT NULL AUTO_INCREMENT,
                `id` int(11) NOT NULL,
                `name` varchar(256) NOT NULL,
                `price` decimal(20,2) NOT NULL DEFAULT \'0\',
                `price_contract` decimal(20,2) NOT NULL DEFAULT \'0\',
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_flingex_lg_productlist`)
                ) ENGINE='._MYSQL_ENGINE_.'  DEFAULT CHARSET=utf8'
        );

        $return &= (bool)Db::getInstance()->Execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'flingex_order` (
                `id_flingex_order` int(11) NOT NULL AUTO_INCREMENT,
                `id_cart` int(11) NOT NULL,
                `id_order` int(11) NOT NULL,
                `id_customer` int(11) NOT NULL,
                `tracking_number` varchar(40) NOT NULL,
                `reference` varchar(40) NOT NULL,
                `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ,
                `api_response_status_code` varchar(40),
                `api_response_status_message` varchar(500),
                PRIMARY KEY (`id_flingex_order`)
                ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8'
        );

        $return &= (bool)Db::getInstance()->Execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'flingex_order_tracking` (
                `id_flingex_order_tracking` int(11) NOT NULL AUTO_INCREMENT,
                `id_order` int(11) NOT NULL,
                `reference` varchar(20) NOT NULL,
                `id_flingex_order` int(11) NOT NULL,
                `tracking_number` varchar(20) NOT NULL,
                `tracking_event_key` varchar(100) NOT NULL,
                `tracking_event_value` varchar(200) NOT NULL,
                `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `api_response_status_code` varchar(40),
                `api_response_status_message` varchar(500),
                PRIMARY KEY (`id_flingex_order_tracking`)
                ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8'
        );
        return $return;
    }

    public function deleteDeliveryLabel($shipment_number, $id_shop = null)
    {
        $this->flingex_api->setApiVersionByIdShop($id_shop);

        $operation = 'deleteShipmentOrder';
        $request = array(
            'shipmentNumber' => $shipment_number
        );

        $response = $this->flingex_api->callDHLApi(
            $operation,
            $request,
            $id_shop
        );
        if (is_array($response) && isset($response['shipmentNumber'])) {
            $id_flingex_label = FLINGEXLabel::getLabelIDByShipmentNumber($shipment_number);
            if ($id_flingex_label > 0) {
                $flingex_label = new FLINGEXLabel($id_flingex_label);
                if ($flingex_label->delete()) {
                    return true;
                }
            }
        }
        return false;
    }

    public function doManifest($shipment_number, $id_shop = null)
    {
        $this->flingex_api->setApiVersionByIdShop($id_shop);

        $operation = 'doManifest';
        $request = array(
           'shipmentNumber' => $shipment_number
        );

        $response = $this->flingex_api->callDHLApi(
            $operation,
            $request,
            $id_shop
        );
        if (is_object($response) && isset($response->shipmentNumber)) {
            return true;
        }
        return false;
    }

    public function createDhlRetoureLabel($sender_address, $id_order_carrier, $reference_number, $id_order_return = 0, $id_shop = null)
    {
        $cr = $this->getCountriesAndReceiverIDsForRA($sender_address['country']['countryISOCode']);
        $receiverid = '';
        if ($cr !== false) {
            $receiverid = $cr['receiverid'];
            $sender_address['country']['countryISOCode'] = $cr['iso_code3'];
        }
        $return_order = array(
            'receiverId' => $receiverid, //* max 30
            'customerReference' => 'Retoure '.$reference_number, //max 30 - is displayed visibly on the returns label
            'shipmentReference' => 'Retoure '.$reference_number, //max 30 - displayed exclusively in the returns overview
            'senderAddress' => $sender_address,
            'email' => '', //max 70
            'telephoneNumber' => '', //max 35
            //'weightInGrams' => 0,
            //'customsDocument' => array(),
            'returnDocumentType' => 'SHIPMENT_LABEL'
        );

        $response = $this->flingex_api->callDhlRetoureApi(
            $return_order,
            $id_shop
        );

        if (is_array($response) && isset($response['shipmentNumber'])) {
            $flingex_label = new FLINGEXLabel();

            $flingex_label->id_order_carrier = (int)$id_order_carrier;
            $flingex_label->product_code = 'ra';
            $flingex_label->options = '';
            $flingex_label->shipment_number = isset($response['shipmentNumber'])?$response['shipmentNumber']:$response['shipmentNumber'];
            $flingex_label->label_url = isset($response['shipmentNumber'])?$response['shipmentNumber']:$response['shipmentNumber'];
            $flingex_label->export_label_url = '';
            $flingex_label->cod_label_url = '';
            $flingex_label->return_label_url = '';
            $flingex_label->is_complete = 1;
            $flingex_label->is_return = 1;
            $flingex_label->with_return = 0;
            $flingex_label->id_order_return = (int)$id_order_return;
            $flingex_label->shipment_date = '';
            $flingex_label->api_version = $this->flingex_api->getApiVersion();
            $flingex_label->routing_code = $response['routingCode'];
            $flingex_label->idc = '';
            $flingex_label->idc_type = '';
            $flingex_label->int_idc = '';
            $flingex_label->int_idc_type = '';

            $pdf_file = $this->saveLabelFile($flingex_label->label_url, base64_decode($response['labelData']));

            if (!$flingex_label->save()) {
                return false;
            } else {
                $flingex_package = new FlingexPackage();
                $flingex_package->id_flingex_label = $flingex_label->id;
                $flingex_package->weight = 1;
                $flingex_package->length = 1;
                $flingex_package->width = 1;
                $flingex_package->height = 1;
                $flingex_package->package_type = 'PK';
                $flingex_package->shipment_number = $flingex_label->shipment_number;
                $flingex_package->save();

                $order_carrier = new OrderCarrier((int)$id_order_carrier);
                $id_order = $order_carrier->id_order;
                $order = new Order((int)$id_order);
                $id_shop = $order->id_shop;
                if (self::getConfig('DHL_RETURN_MAIL', $id_shop)) {
                    $customer = new Customer((int)$order->id_customer);
                    $data = array(
                        '{firstname}' => $customer->firstname,
                        '{lastname}' => $customer->lastname,
                        '{order_name}' => $order->reference,
                        '{id_order}' => $order->id
                    );
                    $template = 'dhl_return_label';
                    $subject = $this->l('Return label');

                    $pdf_file = $this->getLabelFilePathByLabelUrl($flingex_label->label_url);

                    if ($pdf_file != '') {
                        $file_attachment = array(
                            'dhl_return_label' => array(
                                'content' => Tools::file_get_contents($pdf_file),
                                'name' => 'flingex_return_label_'.$order->id.'_'.$id_order_return.'.pdf',
                                'mime' => 'application/pdf'
                            )
                        );
                    } else {
                        $file_attachment = array();
                    }

                    if (!Mail::Send(
                        (int)$order->id_lang,
                        $template,
                        $subject,
                        $data,
                        $customer->email,
                        $customer->firstname.' '.$customer->lastname,
                        null,
                        null,
                        $file_attachment,
                        null,
                        dirname(__FILE__).'/mails/',
                        false,
                        (int)$order->id_shop
                    )
                    ) {
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function createDhlDeliveryLabel(
        $flingex_delivery_address,
        $product_code,
        $packages,
        $options,
        $id_order_carrier,
        $reference_number,
        $is_return = false,
        $with_return = false,
        $id_order_return = 0,
        $id_shop = null
    ) {
        if (isset($options['addit_services']['show_DHLDP_additional_services'])) {
            unset($options['addit_services']['show_DHLDP_additional_services']);
        }
        if (isset($options['export_docs']['show_DHLDP_export_documents'])) {
            unset($options['export_docs']['show_DHLDP_export_documents']);
        }

        $this->flingex_api->setApiVersionByIdShop($id_shop);

        $aproduct_code = explode(':', $product_code);

        $receiver = $flingex_delivery_address;

        $def = $this->flingex_api->getDefinedProducts($aproduct_code[0], $receiver['countryISOCode'], 'bd', $this->flingex_api->getApiVersion());

        $shipment_order = array(
            'sequenceNumber' => 1,
            'Shipment' => array(
                'ShipmentDetails' => array(
                    'product' => $def['alias_v2'],
                    'accountNumber' => $def['procedure'].$aproduct_code[1],
                    'customerReference' => $reference_number,
                    'shipmentDate' => $options['shipment_date'],
                    'ShipmentItem' => array(),
                ),
                'Shipper' => 'flingex',
                'Receiver' => $receiver
            ),
        );

		if ($this->flingex_api->getMajorApiVersion() != 3) {
			$shipment_order['LabelResponseType'] = 'URL';
			$shipment_order['PRINTONLYIFCODEABLE'] = 0;
		} else {
			$shipment_order['PrintOnlyIfCodeable'] = array('active' => 0);
		}

        if ($with_return === true && in_array('DHLRetoure', $def['services'])) {
            $shipment_order['Shipment']['ShipmentDetails']['returnShipmentAccountNumber'] = $ekp.'07'.
                ((self::getConfig('DHL_RETURN_PARTICIPATION', $id_shop) != '') ? self::getConfig('DHL_RETURN_PARTICIPATION', $id_shop) : '01');
            $shipment_order['Shipment']['ShipmentDetails']['returnShipmentReference'] = 'Return for '.$reference_number;
            $shipment_order['Shipment']['ReturnReceiver'] = 'flingex';
        } else {
            $with_return = false;
        }

        //packages
        foreach ($packages as $package) {
            $shipment_order['Shipment']['ShipmentDetails']['ShipmentItem'] = array(
                'weightInKG' => (float)$package['weight'],
                'lengthInCM' => (float)$package['length'],
                'widthInCM' => (float)$package['width'],
                'heightInCM' => (float)$package['height'],
            );
        }
        if (isset($options['addit_services']['DayOfDelivery']) && $options['addit_services']['DayOfDelivery'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['DayOfDelivery'] = array(
                'active' => '1',
                'details' => $options['addit_services']['DayOfDelivery']
            );
        }
        if (isset($options['addit_services']['DeliveryTimeframe']) && $options['addit_services']['DeliveryTimeframe'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['DeliveryTimeframe'] = array(
                'active' => '1',
                'type' => $options['addit_services']['DeliveryTimeframe']
            );
        }
        if (isset($options['addit_services']['PreferredTime']) && $options['addit_services']['PreferredTime'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['PreferredTime'] = array(
                'active' => '1',
                'type' => $options['addit_services']['PreferredTime']
            );
        }
        if (isset($options['addit_services']['IndividualSenderRequirement']) && $options['addit_services']['IndividualSenderRequirement'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['IndividualSenderRequirement'] = array(
                'active' => '1',
                'details' => $options['addit_services']['IndividualSenderRequirement']
            );
        }
        if (isset($options['addit_services']['PackagingReturn']) && $options['addit_services']['PackagingReturn'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['PackagingReturn'] = array('active' => '1');
        }
        if (isset($options['addit_services']['ReturnImmediately']) && $options['addit_services']['ReturnImmediately'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['ReturnImmediately'] = array('active' => '1');
        }
        if (isset($options['addit_services']['NoticeOfNonDeliverability']) && $options['addit_services']['NoticeOfNonDeliverability'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['NoticeOfNonDeliverability'] = array('active' => '1');
        }
        if (isset($options['addit_services']['ShipmentHandling']) && $options['addit_services']['ShipmentHandling'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['ShipmentHandling'] = array(
                'active' => '1',
                'type' => $options['addit_services']['ShipmentHandling']
            );
        }
        if (isset($options['addit_services']['Endorsement']) && $options['addit_services']['Endorsement'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['Endorsement'] = array(
                'active' => '1',
                'type' => $options['addit_services']['Endorsement']
            );
        }
        if (isset($options['addit_services']['VisualCheckOfAge']) && $options['addit_services']['VisualCheckOfAge'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['VisualCheckOfAge'] = array(
                'active' => '1',
                'type' => $options['addit_services']['VisualCheckOfAge']
            );
        }
        if (isset($options['addit_services']['PreferredLocation']) && $options['addit_services']['PreferredLocation'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['PreferredLocation'] = array(
                'active' => '1',
                'details' => $options['addit_services']['PreferredLocation']
            );
        }
        if (isset($options['addit_services']['PreferredNeighbour']) && $options['addit_services']['PreferredNeighbour'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['PreferredNeighbour'] = array(
                'active' => '1',
                'details' => $options['addit_services']['PreferredNeighbour']
            );
        }
        if (isset($options['addit_services']['PreferredDay']) && $options['addit_services']['PreferredDay'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['PreferredDay'] = array(
                'active' => '1',
                'details' => $options['addit_services']['PreferredDay']
            );
        }
        if (isset($options['addit_services']['GoGreen']) && $options['addit_services']['GoGreen'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['GoGreen'] = array('active' => '1');
        }
        if (isset($options['addit_services']['Perishables']) && $options['addit_services']['Perishables'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['Perishables'] = array('active' => '1');
        }
        if (isset($options['addit_services']['Personally']) && $options['addit_services']['Personally'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['Personally'] = array('active' => '1');
        }
        if (isset($options['addit_services']['NoNeighbourDelivery']) && $options['addit_services']['NoNeighbourDelivery'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['NoNeighbourDelivery'] = array('active' => '1');
        }
        if (isset($options['addit_services']['NamedPersonOnly']) && $options['addit_services']['NamedPersonOnly'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['NamedPersonOnly'] = array('active' => '1');
        }
        if (isset($options['addit_services']['ReturnReceipt']) && $options['addit_services']['ReturnReceipt'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['ReturnReceipt'] = array('active' => '1');
        }
        if (isset($options['addit_services']['Premium']) && $options['addit_services']['Premium'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['Premium'] = array('active' => '1');
        }
        if (isset($options['addit_services']['CashOnDelivery']) && $options['addit_services']['CashOnDelivery'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['CashOnDelivery'] = array(
                'active' => '1',
                'addFee' => (isset($options['addit_services']['CashOnDelivery_addFee']) && $options['addit_services']['CashOnDelivery_addFee'] == 1) ? 1 : 0,
                'codAmount' => $options['addit_services']['CashOnDelivery_codAmount']
            );
            $shipment_order['Shipment']['ShipmentDetails']['BankData'] = array(
                'accountOwner' => self::getConfig('DHL_ACCOUNT_OWNER', $id_shop),
                'bankName' => self::getConfig('DHL_BANK_NAME', $id_shop),
                'iban' => self::getConfig('DHL_IBAN', $id_shop),
                'bic' => self::getConfig('DHL_BIC', $id_shop),
                'note1' => str_replace(
                    '[order_reference_number]',
                    $reference_number,
                    self::getConfig('DHL_NOTE', $id_shop)
                )
            );
        }
        if (isset($options['addit_services']['Notification']) && $options['addit_services']['Notification'] != '' &&
            isset($options['addit_services']['Notification_recepientEmailAddress']) && $options['addit_services']['Notification_recepientEmailAddress'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Notification']['recipientEmailAddress'] = $options['addit_services']['Notification_recepientEmailAddress'];
        }
        if (isset($options['addit_services']['AdditionalInsurance']) && $options['addit_services']['AdditionalInsurance'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['AdditionalInsurance'] = array(
                'active' => '1',
                'insuranceAmount' => $options['addit_services']['AdditionalInsurance_insuranceAmount']
            );
        }
		if (isset($options['addit_services']['ParcelOutletRouting']) && $options['addit_services']['ParcelOutletRouting'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['ParcelOutletRouting'] = array(
                'active' => '1',
                'details' => $options['addit_services']['ParcelOutletRouting_details']
            );
			if ($options['addit_services']['ParcelOutletRouting_details'] != '') {
				$shipment_order['Shipment']['ShipmentDetails']['Service']['ParcelOutletRouting']['details'] = $options['addit_services']['ParcelOutletRouting_details'];
			}
        }
        if (isset($options['addit_services']['BulkyGoods']) && $options['addit_services']['BulkyGoods'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['BulkyGoods'] = array('active' => '1');
        }
        if (isset($options['addit_services']['IdentCheck']) && $options['addit_services']['IdentCheck'] != '') {
            $shipment_order['Shipment']['ShipmentDetails']['Service']['IdentCheck'] = array(
                'active' => '1',
                'Ident' => array(
                    'surname' => $options['addit_services']['IdentCheck_Ident_surname'],
                    'givenName' => $options['addit_services']['IdentCheck_Ident_givenName'],
                    'dateOfBirth' => $options['addit_services']['IdentCheck_Ident_dateOfBirth'],
                    'minimumAge' => $options['addit_services']['IdentCheck_Ident_minimumAge'],
                )
            );
        }
        if (isset($def['export_documents']) && isset($options['export_docs'])) {
            $s = &$options['export_docs'];
            $shipment_order['Shipment']['ExportDocument'] = array();
            $d = &$shipment_order['Shipment']['ExportDocument'];
            $s_structure = array(
                'invoiceNumber',
                'exportType',
                'exportTypeDescription',
                'termsOfTrade',
                'placeOfCommital',
                'additionalFee',
                'permitNumber',
                'attestationNumber',
                'WithElectronicExportNtfctn',
                'ExportDocPosition'
            );
            $pos_structure = array(
                'description',
                'countryCodeOrigin',
                'customsTariffNumber',
                'amount',
                'netWeightInKG',
                'customsValue'
            );

            foreach ($s as $s_key => $s_value) {
                if (in_array($s_key, $s_structure) && $s[$s_key] != '') {
                    if ($s_key == 'WithElectronicExportNtfctn') {
                        $d[$s_key] = array('active' => 1);
                    } elseif ($s_key == 'ExportDocPosition' && is_array($s[$s_key])) {
                        $i_pos = 0;
                        foreach ($s[$s_key] as $pos_key => $pos) {
                            if (is_array($pos)) {
                                foreach ($pos as $key_field => $key_value) {
                                    if (in_array($key_field, $pos_structure) && $key_value != '') {
                                        $d[$s_key][$i_pos][$key_field] = $key_value;
                                    }
                                }
                            }
                            $i_pos++;
                        }
                    } else {
                        $d[$s_key] = $s[$s_key];
                    }
                }
            }
        }

       // echo '<pre>'.print_r($shipment_order, true).'</pre>'; exit;

		$shipment_order_request = array('ShipmentOrder' => $shipment_order);

		if ($this->flingex_api->getMajorApiVersion() == 3) {
			$shipment_order_request['labelResponseType'] = 'URL';
			if (self::getConfig('DHL_LABEL_FORMAT', $id_shop) != '') {
				$shipment_order_request['labelFormat'] = self::getConfig('DHL_LABEL_FORMAT', $id_shop);
			}
			if (self::getConfig('DHL_RETOURE_LABEL_FORMAT', $id_shop) != '') {
				$shipment_order_request['labelFormatRetoure'] = self::getConfig('DHL_RETOURE_LABEL_FORMAT', $id_shop);
			}
		}

        //echo '<pre>'.print_r($def, true).'</pre>';
        //echo '<pre>'.print_r($options, true).'</pre>';
        //echo '<pre>'.print_r($shipment_order, true).'</pre>'; exit;
        $response = $this->flingex_api->callDHLApi(
            'createShipmentOrder',
            $shipment_order_request,
            $id_shop
        );

        if (is_array($response) && isset($response['shipmentNumber'])) {
            $dhl_label = new FLINGEXLabel();

            $dhl_label->id_order_carrier = (int)$id_order_carrier;
            $dhl_label->product_code = $product_code;
            $dhl_label->options = Tools::jsonEncode($options);
            $dhl_label->shipment_number = $response['shipmentNumber'];
            $dhl_label->label_url = $response['labelUrl'];
            if (isset($response['exportLabelUrl'])) {
                $dhl_label->export_label_url = $response['exportLabelUrl'];
            } else {
                $dhl_label->export_label_url = '';
            }
            if (isset($response['codLabelUrl'])) {
                $dhl_label->cod_label_url = $response['codLabelUrl'];
            } else {
                $dhl_label->cod_label_url = '';
            }
            if (isset($response['returnLabelUrl'])) {
                $dhl_label->return_label_url = $response['returnLabelUrl'];
            } else {
                $dhl_label->return_label_url = '';
            }
            $dhl_label->is_complete = 1;
            $dhl_label->is_return = (int)$is_return;
            $dhl_label->with_return = (int)$with_return;
            $dhl_label->id_order_return = (int)$id_order_return;
            $dhl_label->shipment_date = $options['shipment_date'];
            $dhl_label->api_version = $this->flingex_api->getApiVersion();

            if (!$dhl_label->save()) {
                return false;
            } else {
                if (isset($shipment_order['Shipment']['ShipmentDetails']['ShipmentItem']['weightInKG'])) {
                    $dhl_package = new FlingexPackage();
                    $dhl_package->id_flingex_label = $dhl_label->id;
                    $dhl_package->weight = $shipment_order['Shipment']['ShipmentDetails']['ShipmentItem']['weightInKG'];
                    $dhl_package->length = $shipment_order['Shipment']['ShipmentDetails']['ShipmentItem']['lengthInCM'];
                    $dhl_package->width = $shipment_order['Shipment']['ShipmentDetails']['ShipmentItem']['widthInCM'];
                    $dhl_package->height = $shipment_order['Shipment']['ShipmentDetails']['ShipmentItem']['heightInCM'];
                    $dhl_package->package_type = 'PK';
                    $dhl_package->shipment_number = $response['shipmentNumber'];
                    $dhl_package->save();
                } else {
                    foreach ($shipment_order['Shipment']['ShipmentDetails']['ShipmentItem'] as $package) {
                        $dhl_package = new FlingexPackage();
                        $dhl_package->id_flingex_label = $dhl_label->id;
                        $dhl_package->weight = $package['WeightInKG'];
                        $dhl_package->length = $package['LengthInCM'];
                        $dhl_package->width = $package['WidthInCM'];
                        $dhl_package->height = $package['HeightInCM'];
                        $dhl_package->package_type = $package['PackageType'];
                        $dhl_package->shipment_number = $response['shipmentNumber'];
                        $dhl_package->save();
                    }
                }
                if ($is_return === false) {
                    $this->updateOrderCarrierWithTrackingNumber(
                        (int)$id_order_carrier,
                        $response['shipmentNumber']
                    );
                    $this->updateOrderStatus((int)$id_order_carrier);
                } else {
                    $order_carrier = new OrderCarrier((int)$id_order_carrier);
                    $id_order = $order_carrier->id_order;
                    $order = new Order((int)$id_order);
                    $id_shop = $order->id_shop;
                    if (self::getConfig('DHL_RETURN_MAIL', $id_shop)) {
                        $customer = new Customer((int)$order->id_customer);
                        $data = array(
                            '{firstname}' => $customer->firstname,
                            '{lastname}' => $customer->lastname,
                            '{order_name}' => $order->reference,
                            '{id_order}' => $order->id
                        );
                        $template = 'dhl_return_label';
                        $subject = $this->l('Return label');

                        $pdf_file = $this->getLabelFilePathByLabelUrl($response['labelUrl']);

                        if ($pdf_file != '') {
                            $file_attachment = array(
                                'dhl_return_label' => array(
                                    'content' => Tools::file_get_contents($pdf_file),
                                    'name' => 'dhl_return_label_'.$order->id.'.pdf',
                                    'mime' => 'application/pdf'
                                )
                            );
                        } else {
                            $file_attachment = array();
                        }

                        if (!Mail::Send(
                            (int)$order->id_lang,
                            $template,
                            $subject,
                            $data,
                            $customer->email,
                            $customer->firstname.' '.$customer->lastname,
                            null,
                            null,
                            $file_attachment,
                            null,
                            dirname(__FILE__).'/mails/',
                            false,
                            (int)$order->id_shop
                        )
                        ) {
                            return false;
                        }
                    }
                }
            }
            return true;
        }

        return false;
    }

    public function hookActionOrderReturn($params)
    {
        /*
         * $params['orderReturn']->id_order
         * $params['orderReturn']->id_customer
         * $params['orderReturn']->state = 1
         */
        $order = new Order((int)$params['orderReturn']->id_order);
        if (Validate::isLoadedObject($order) && $this->flingex_api->setApiVersionByIdShop($order->id_shop)) {
            if (self::getConfig('DHL_RETURNS_EXTEND', $order->id_shop) &&
                (self::getConfig('DHL_RETURNS_IMMED', $order->id_shop))) {
                // restriction - only for germany
                //if ($this->isGermanyAddress($order->id_address_delivery)) {
                $order_carriers = $this->filterShipping($order->getShipping(), (int)$order->id_shop);
                if (is_array($order_carriers) && count($order_carriers) > 0) {
                    // change state
                    $params['orderReturn']->state = 2;
                    $params['orderReturn']->save();

                    // mail will be send on hookActionObjectOrderReturnUpdateAfter
                }
                //}
            }
        }
    }

    public function hookActionValidateOrderAfter($params)
    {
//        var_dump($params['cart']);
//        var_dump($params['order']);
//        die('here');
        /*
         * $params['orderReturn']->id_order
         * $params['orderReturn']->id_customer
         * $params['orderReturn']->state = 1
         */
//        $order = new Order((int)$params['orderReturn']->id_order);
//        if (Validate::isLoadedObject($order) && $this->flingex_api->setApiVersionByIdShop($order->id_shop)) {
//            if (self::getConfig('DHL_RETURNS_EXTEND', $order->id_shop) &&
//                (self::getConfig('DHL_RETURNS_IMMED', $order->id_shop))) {
//                // restriction - only for germany
//                //if ($this->isGermanyAddress($order->id_address_delivery)) {
//                $order_carriers = $this->filterShipping($order->getShipping(), (int)$order->id_shop);
//                if (is_array($order_carriers) && count($order_carriers) > 0) {
//                    // change state
//                    $params['orderReturn']->state = 2;
//                    $params['orderReturn']->save();
//
//                    // mail will be send on hookActionObjectOrderReturnUpdateAfter
//                }
//                //}
//            }
//        }
    }

    public function hookActionValidateOrder($params)
    {

        /*
         * $params['orderReturn']->id_order
         * $params['orderReturn']->id_customer
         * $params['orderReturn']->state = 1
         */

        $order = $params['order'];
        $api_response = self::sentToFlingexOrder($order);

        // if paper fly is not accept the order
        if( json_decode($api_response)->response_code != '200' ) {
            // return;
        }

        $tracking_api_response = self::sentToFlingexOrderTrackingApi($order);
        $tracking_response_data = (json_decode($tracking_api_response)->response_code == '200') ? json_decode($tracking_api_response)->success->trackingStatus : '';


        $traking_number =  (json_decode($tracking_api_response)->response_code == '200') ?json_decode($api_response)->success->tracking_number : '';
        $order_api_response_code =  json_decode($api_response)->response_code;
        $order_api_response_message = ($order_api_response_code == '200') ? json_decode($api_response)->success->message :
            json_decode($api_response)->error->message;

        $tracking_api_response_code = json_decode($tracking_api_response)->response_code;
        $tracking_api_response_message = ($tracking_api_response_code == '200') ? json_decode($tracking_api_response)->success->message :
            json_decode($tracking_api_response)->error->message;
        $reference = $order->reference;
        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'flingex_order
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
        $id_flingex_order = Db::getInstance()->Insert_ID();

        foreach ((array)$tracking_response_data[0] as $key => $value) {
            $this_key = "'" . $key . "'";
            $this_val = "'" . $value . "'";
            $sql_tracking = 'INSERT INTO ' . _DB_PREFIX_ . 'flingex_order_tracking
            (`id_order`,`reference`, `id_flingex_order`, `tracking_number`,`tracking_event_key`,`tracking_event_value`,
            `api_response_status_code`,`api_response_status_message`)
            values(
             ' . (int)$order->id . ',
             "' . $reference . '",
             ' . (int)$id_flingex_order . ',
             "' . $traking_number . '",
             "' . $this_key . '",
             "' . $this_val . '",
             "' . $tracking_api_response_code . '",
             "' . $tracking_api_response_message . '"
            )';
            Db::getInstance()->execute($sql_tracking);
        }

    }

    public function getLastNonReturnLabelData($id_order_carrier)
    {
        return Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'flingex_label` l  WHERE l.`id_order_carrier`= '.
            (int)$id_order_carrier.' AND l.is_return != 1  ORDER BY l.`date_add` DESC LIMIT 1'
        );
    }

    public function getLastReturnLabelDataForIdOrderReturn($id_order_carrier, $id_order_return)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'flingex_label` l  WHERE l.`id_order_carrier`= '.
            (int)$id_order_carrier.' AND l.`id_order_return`='.(int)$id_order_return.' ORDER BY l.`date_add` DESC'
        );
    }

    public function hookActionObjectOrderReturnUpdateAfter($params)
    {
        /*
         * $params['object']
         */
        $order = new Order((int)$params['object']->id_order);
        if (Validate::isLoadedObject($order) && $this->flingex_api->setApiVersionByIdShop($order->id_shop)) {
            if (self::getConfig('DHL_RETURNS_EXTEND', $order->id_shop) && $params['object']->state == 2) {
                // restriction - only for germany
                //if ($this->isGermanyAddress($order->id_address_delivery)) {
                $order_carriers = $this->filterShipping($order->getShipping(), $order->id_shop);
                if (is_array($order_carriers) && count($order_carriers) > 0) {
                    foreach ($order_carriers as $order_carrier) {
                        $last_label = $this->getLastNonReturnLabelData($order_carrier['id_order_carrier']);

                        if (is_array($last_label) && isset($last_label[0]['id_flingex_label'])) {
                            //send mail with button
                            $customer = new Customer((int)$order->id_customer);
                            $data = array(
                                '{firstname}'        => $customer->firstname,
                                '{lastname}'         => $customer->lastname,
                                '{order_name}'       => $order->reference,
                                '{id_order}'         => $order->id,
                                '{order_return_url}' => Context::getContext()->link->getPageLink(
                                    'order-follow',
                                    true,
                                    Context::getContext()->language->id,
                                    null,
                                    false,
                                    $order->id_shop
                                )
                            );
                            $template = 'dhl_return_approved';
                            $subject = $this->l('Return has been approved. Get FLINGEX Return label');
                            $file_attachment = array();
                            Mail::Send(
                                (int)$order->id_lang,
                                $template,
                                $subject,
                                $data,
                                $customer->email,
                                $customer->firstname.' '.$customer->lastname,
                                null,
                                null,
                                $file_attachment,
                                null,
                                dirname(__FILE__).'/mails/',
                                false,
                                (int)$order->id_shop
                            );
                        }
                    }
                }
                //}
            }
        }
    }

    public function getDHLAddressTypes()
    {
        return array(
            'RE' => array('name' => $this->l('Regular address'), 'prefix' => ''),
            'PF' => array('name' => $this->l('DHL Postfiliale'), 'prefix' => 'Postfiliale'),
            'PS' => array('name' => $this->l('DHL Packstation'), 'prefix' => 'Packstation'),
        );
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

    public function getGoogleMapApiKey($id_shop = null)
    {
        return self::getConfig('DHL_GOOGLEMAPAPIKEY', $id_shop);
    }

    public function hookDisplayHeader($params)
    {
        // restriction - only for germany
        if (($this->context->controller instanceof AddressController) &&
            self::getConfig('DHL_PFPS', $this->context->shop->id)) {
            $this->context->controller->addJquery();
            $this->context->controller->addjqueryPlugin('fancybox');
            $this->context->controller->addjqueryPlugin('scrollTo');
            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                $this->context->controller->addJS(
                    '//maps.google.com/maps/api/js?v=3.21&region='.$this->context->language->iso_code.'&key='.$this->getGoogleMapApiKey($this->context->shop->id)
                );
            } else {
                $uri = '//maps.google.com/maps/api/js?v=3.21&region='.$this->context->language->iso_code.'&key='.$this->getGoogleMapApiKey($this->context->shop->id);
                $this->context->controller->registerJavascript(
                    sha1($uri),
                    $uri,
                    array('position' => 'bottom', 'priority' => 80, 'server' => 'remote')
                );
            }
            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                $this->context->controller->addJS($this->_path . 'views/js/address.js');
                $this->context->controller->addCSS($this->_path . 'views/css/map.css');
            } else {
                $this->context->controller->registerJavascript('flingex_address', 'modules/' . $this->name . '/views/js/address.js', array('position' => 'bottom', 'priority' => 100));
                $this->context->controller->registerStylesheet('flingex_map', 'modules/' . $this->name . '/views/css/map.css', array('media' => 'all', 'priority' => 150));
            }

            $flingex_address_data = array(
                'address_types' => $this->getDHLAddressTypes(),
                'input_values' => array()
            );


            $this->context->smarty->assign('flingex_address_data', $flingex_address_data);
            $this->context->smarty->assign(
                'flingex_ajax',
                $this->context->link->getModuleLink($this->name, 'address', array('ajax' => true), true)
            );
            $this->context->smarty->assign('flingex_path', $this->getPathUri());

            if (Configuration::get('PS_RESTRICT_DELIVERED_COUNTRIES')) {
                $countries = Carrier::getDeliveredCountries($this->context->language->id, true, true);
            } else {
                $countries = Country::getCountries($this->context->language->id, true);
            }
            $this->context->smarty->assign('flingex_country_data', $countries);
            return $this->display(__FILE__, '/views/templates/hook/address.tpl');
        } elseif (($this->context->controller instanceof OrderController) &&
            (self::getConfig('DHL_GOOGLEMAPAPIKEY', $this->context->shop->id) != '') &&
            self::getConfig('DHL_PFPS', $this->context->shop->id)) {
            $this->context->controller->addJquery();
            $this->context->controller->addjqueryPlugin('fancybox');
            $this->context->controller->addjqueryPlugin('scrollTo');

            if (self::getConfig('DHL_CONFIRMATION_PRIVATE')) {
                if (version_compare(_PS_VERSION_, '1.7', '<')) {
                    $this->context->controller->addJS($this->_path . 'views/js/private.js');
                    $this->context->controller->addCSS($this->_path . 'views/css/private.css');
                } else {
                    $this->context->controller->registerJavascript('dhl_private', 'modules/' . $this->name . '/views/js/private.js', array('position' => 'bottom', 'priority' => 100));
                    $this->context->controller->registerStylesheet('dhl_private', 'modules/' . $this->name . '/views/css/private.css', array('media' => 'all', 'priority' => 150));
                }
            }
            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                $this->context->controller->addJS(
                    '//maps.google.com/maps/api/js?v=3.21&region='.$this->context->language->iso_code.'&key='.$this->getGoogleMapApiKey($this->context->shop->id)
                );
            } else {
                $uri = '//maps.google.com/maps/api/js?v=3.21&region='.$this->context->language->iso_code.'&key='.$this->getGoogleMapApiKey($this->context->shop->id);
                $this->context->controller->registerJavascript(
                    sha1($uri),
                    $uri,
                    array('position' => 'bottom', 'priority' => 80, 'server' => 'remote')
                );
            }

            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                $this->context->controller->addJS($this->_path . 'views/js/address.js');
                $this->context->controller->addCSS($this->_path . 'views/css/map.css');
            } else {
                $this->context->controller->registerJavascript('flingex_address', 'modules/' . $this->name . '/views/js/address.js', array('position' => 'bottom', 'priority' => 100));
                $this->context->controller->registerStylesheet('flingex_map', 'modules/' . $this->name . '/views/css/map.css', array('media' => 'all', 'priority' => 150));
            }

            $flingex_address_data = array(
                'address_types' => $this->getDHLAddressTypes(),
                'input_values' => array()
            );

            $this->context->smarty->assign('flingex_address_data', $flingex_address_data);
            $this->context->smarty->assign(
                'flingex_ajax',
                $this->context->link->getModuleLink($this->name, 'address', array('ajax' => true))
            );
            $this->context->smarty->assign('flingex_path', $this->getPathUri());

            if (Configuration::get('PS_RESTRICT_DELIVERED_COUNTRIES')) {
                $countries = Carrier::getDeliveredCountries($this->context->language->id, true, true);
            } else {
                $countries = Country::getCountries($this->context->language->id, true);
            }
            $this->context->smarty->assign('flingex_country_data', $countries);
            return $this->display(__FILE__, '/views/templates/hook/address.tpl');
        } elseif (($this->context->controller instanceof OrderFollowController)  && $this->flingex_api->setApiVersionByIdShop($this->context->shop->id)) {
            if (self::getConfig('DHL_RETURNS_EXTEND')) {
                $this->context->controller->addJquery();
                if (version_compare(_PS_VERSION_, '1.7', '<')) {
                    $this->context->controller->addJS($this->_path . 'views/js/order_returns.js');
                } else {
                    $this->context->controller->registerJavascript('flingex_order_returns', 'modules/' . $this->name . '/views/js/order_returns.js', array('position' => 'bottom', 'priority' => 100));
                }
                $dhl_order_returns = array();
                $ordersReturn = OrderReturn::getOrdersReturn($this->context->customer->id);
                if (is_array($ordersReturn)) {
                    foreach ($ordersReturn as $order_return_index => $order_return) {
                        $url = '';
                        if ($order_return['state'] == 2) {
                            $order = new Order((int)$order_return['id_order']);
                            if (Validate::isLoadedObject($order)) {
                                // restriction - only for germany
                                //if ($this->isGermanyAddress($order->id_address_delivery) && $this->isDomesticDelivery($order->id_shop, $order->id_address_delivery)) {
                                    $order_carriers = $this->filterShipping($order->getShipping(), $order->id_shop);

                                    if (is_array($order_carriers) && (count($order_carriers) > 0)) {
                                        foreach ($order_carriers as $order_carrier) {
                                            $last_label = $this->getLastNonReturnLabelData(
                                                $order_carrier['id_order_carrier']
                                            );
                                            if (is_array($last_label) && isset($last_label[0]['id_flingex_label'])) {
                                                $url = $this->context->link->getModuleLink(
                                                    $this->name,
                                                    'return',
                                                    array('id_order_return' => $order_return['id_order_return'])
                                                );
                                            }
                                        }
                                    }
                                //}
                            }
                        }
                        $dhl_order_returns[$order_return_index] = array(
                            'id'  => $order_return['id_order_return'],
                            'url' => $url
                        );
                    }
                }

                return '<script type="text/javascript">
                var dhldp_translation = {
				"Get_DHL_Return_Label": "'.$this->l('Get DHL Return Label').'"
		        }
			    var dhldp_order_returns_items = '.Tools::jsonEncode($dhl_order_returns).
                '</script>';
            }
        }
    }

    public function hookDisplayAfterCarrier($params)
    {
        return $this->hookExtraCarrier($params);
    }

    public function hookExtraCarrier($params)
    {
        if (self::getConfig('DHL_CONFIRMATION_PRIVATE')) {
            $ids_dhl = $this->getDHLCarriers(true, true, $params['cart']->id_shop);
            if (is_array($ids_dhl) && count($ids_dhl)) {
                $this->context->smarty->assign(
                    array(
                        'js_flingex_path' => $this->getPathUri(),
                        'js_flingex_carriers' => $ids_dhl,
                        'dhl_permission_private' => FlingexOrder::hasPermissionForTransferring($params['cart']->id)
                    )
                );
                if (version_compare(_PS_VERSION_, '1.7', '>=') || version_compare(_PS_VERSION_, '1.6', '<')) {
                    return $this->display(__FILE__, '/views/templates/hook/private-17.tpl');
                } else {
                    return $this->display(__FILE__, '/views/templates/hook/private.tpl');
                }
            }
        }
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        $id_product = (int)Tools::getValue('id_product');
        if (!$id_product && array_key_exists('id_product', $params)) {
            $id_product = $params['id_product'];
        }

        if (!$id_product || !Validate::isLoadedObject($product = new Product((int)$id_product, false, (int)$this->context->language->id))) {
            $this->context->smarty->assign(
                array(
                    'allow_to_use' => false,
                    'ctn' => ''
                )
            );
        } else {
            $combinations = array();

            $this->context->smarty->assign(
                array(
                    'product_link_rewrite' => $product->link_rewrite,
                    'product_name' => $product->name,
                    'allow_to_use' => true,
                    'ctn' => Db::getInstance()->getValue('select customs_tariff_number from '._DB_PREFIX_.'flingex_product_customs WHERE id_product='.(int)$id_product.' AND id_product_attribute=0'),
                    'coo' => Db::getInstance()->getValue('select country_of_origin from '._DB_PREFIX_.'flingex_product_customs WHERE id_product='.(int)$id_product.' AND id_product_attribute=0'),
                    'combinations' => $combinations
                )
            );
        }

        return $this->display(__FILE__,  'admin-products-extra.tpl');
    }

    public function hookActionProductAdd($params) {
        if ($params['id_product'] > 0) {
            if (Db::getInstance()->getValue('select customs_tariff_number from '._DB_PREFIX_.'flingex_product_customs WHERE id_product='.(int)$params['id_product'].' AND id_product_attribute=0') !== false) {
                Db::getInstance()->update('flingex_product_customs', array('customs_tariff_number' => pSQL(Tools::getValue('flingex_ctn', '')), 'country_of_origin' => pSQL(Tools::getValue('flingex_coo', '')), 'date_upd' => date('Y-m-d H:i:s')), 'id_product='.(int)$params['id_product'].' AND id_product_attribute=0');
            } else {
                Db::getInstance()->insert('flingex_product_customs', array('customs_tariff_number' => pSQL(Tools::getValue('flingex_ctn', '')), 'country_of_origin' => pSQL(Tools::getValue('flingex_coo', '')), 'date_upd' => date('Y-m-d H:i:s'), 'id_product' => (int)$params['id_product'], 'id_product_attribute' => '0', 'date_add' => date('Y-m-d H:i:s')));
            }
        }
    }

    public function hookActionProductUpdate($params) {
        if ($params['id_product'] > 0) {
            if (Db::getInstance()->getValue('select customs_tariff_number from '._DB_PREFIX_.'flingex_product_customs WHERE id_product='.(int)$params['id_product'].' AND id_product_attribute=0') !== false) {
                Db::getInstance()->update('flingex_product_customs', array('customs_tariff_number' => pSQL(Tools::getValue('flingex_ctn', '')), 'country_of_origin' => pSQL(Tools::getValue('flingex_coo', '')), 'date_upd' => date('Y-m-d H:i:s')), 'id_product='.(int)$params['id_product'].' AND id_product_attribute=0');
            } else {
                Db::getInstance()->insert('flingex_product_customs', array('customs_tariff_number' => pSQL(Tools::getValue('flingex_ctn', '')), 'country_of_origin' => pSQL(Tools::getValue('flingex_coo', '')), 'date_upd' => date('Y-m-d H:i:s'), 'id_product' => (int)$params['id_product'], 'id_product_attribute' => '0', 'date_add' => date('Y-m-d H:i:s')));
            }
        }
    }

    public function hookActionProductDelete($params) {
        if ($params['id_product'] > 0) {
            Db::getInstance()->delete('flingex_product_customs', 'id_product='.(int)$params['id_product']);
        }
    }

    public function hookActionProductAttributeDelete($params) {
        if ($params['id_product'] > 0) {
            if ($params['id_product_attribute'] > 0) {
                Db::getInstance()->delete('flingex_product_customs', 'id_product=' . (int)$params['id_product'] . ' and id_product_attribute=' . (int)$params['id_product_attribute']);
            } elseif ((int)$params['id_product_attribute'] == 0) {
                Db::getInstance()->delete('flingex_product_customs', 'id_product=' . (int)$params['id_product'] . ' and id_product_attribute!=0');
            }
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

    public function getLabelData($id_order_carrier)
    {
        if (!is_array($id_order_carrier)) {
            $id_order_carrier = array($id_order_carrier);
        }

        if (count($id_order_carrier) > 0) {
            $selected_values = Db::getInstance()->executeS(
                'SELECT * FROM `'._DB_PREFIX_.'flingex_label` l
                 WHERE l.`id_order_carrier` IN ('.implode(',', array_map('intval', $id_order_carrier)).')'.
                ' ORDER BY `date_add`'
            );

            return $selected_values;
        }
        return false;
    }

    public function getCountryISOCodeByAddressID($id_address)
    {
        $country_and_state = Address::getCountryAndState((int)$id_address);
        $country_iso_code = '';
        if ($country_and_state) {
            $country = new Country((int)$country_and_state['id_country']);
            $country_iso_code = $country->iso_code;
        }
        return $country_iso_code;
    }

    public function isGermanyAddress($id_address)
    {
        if ($this->getCountryISOCodeByAddressID($id_address) == 'DE') {
            return true;
        }
        return false;
    }

    public function isEUAddress($id_address)
    {
        if (in_array($this->getCountryISOCodeByAddressID($id_address), $this->getEUCountriesCodes())) {
            return true;
        }
        return false;
    }

    public function getEUCountriesCodes()
    {
        return array('AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE',
            'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'/*, 'GB'*/);
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
                $labels = $this->getDPLabelData($shipping_item['id_order_carrier']);
                $last_label = array();
                if ($labels) {
                    $last_label = $labels[count($labels) - 1];
                }


                $default_page_format_desc = $this->dp_api->getPageFormats(Configuration::get('DHLDP_DP_PAGE_FORMAT', false, false, $order->id_shop));

                $this->context->controller->addCSS($this->_path.'views/css/admin.css');
                $this->context->smarty->assign(
                    array(
                        'module_path' => __PS_BASE_URI__.'modules/'.$this->name.'/',
                        'id_address' => $order->id_address_delivery,
                        'is177' => $this->is177,
                        'carrier' => $shipping_item,
                        'labels' => $labels,
                        'last_label' => $last_label,
                        'deutcshepost_products' => $this->dp_api->getProducts(),
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
        $this->flingex_api->setApiVersionByIdShop($order->id_shop);

        if (Tools::getIsset('deleteDHLDPDhlLabel')) {
            $dhl_errors = array();
            $dhl_confirmations = array();

            $shipment_number = Tools::getValue('shipment_number');
            if ($shipment_number != '') {
                if (FLINGEXLabel::getLabelIDByShipmentNumber($shipment_number) != false) {
                    $result = $this->deleteDeliveryLabel($shipment_number, $order->id_shop);
                    if (!$result) {
                        if (is_array($this->flingex_api->errors) && count($this->flingex_api->errors) > 0) {
                            $dhl_errors = array_merge($dhl_errors, $this->flingex_api->errors);
                        } else {
                            $dhl_errors[] = $this->l('Unable to delete label for this shipment number');
                        }
                    } else {
                        $dhl_confirmations = $this->l('Shipment has been deleted');
                    }
                } else {
                    $dhl_errors[] = $this->l('No label for this shipment number');
                }
            }
            $this->context->smarty->assign('dhl_errors', $dhl_errors);
            $this->context->smarty->assign('dhl_confirmations', $dhl_confirmations);
        }

        if (Tools::getIsset('doDHLDPDhlManifest')) {
            $dhl_errors = array();
            $dhl_confirmations = array();

            $shipment_number = Tools::getValue('shipment_number');
            if ($shipment_number != '') {
                if (FLINGEXLabel::getLabelIDByShipmentNumber($shipment_number) != false) {
                    $result = $this->doManifest($shipment_number, $order->id_shop);
                    if (!$result) {
                        if (is_array($this->flingex_api->errors) && count($this->flingex_api->errors) > 0) {
                            $dhl_errors = array_merge($dhl_errors, $this->flingex_api->errors);
                        } else {
                            $dhl_errors[] = $this->l('Unable to do manifest for this shipment number');
                        }
                    } else {
                        $dhl_confirmations = $this->l('Manifest has been done');
                    }
                } else {
                    $dhl_errors[] = $this->l('No label for this shipment number');
                }
            }
            $this->context->smarty->assign('dhl_errors', $dhl_errors);
            $this->context->smarty->assign('dhl_confirmations', $dhl_confirmations);
        }

        if (Tools::getIsset('submitDHLDPDhlLabelRequest') || Tools::getIsset('submitDHLDPDhlLabelWithReturnRequest') || Tools::getIsset('submitDHLDPDhlLabelReturnRequest')) {
            $id_address = (int)Tools::getValue('id_address');
            $product_code = Tools::getValue('dhl_product_code');
            $id_order_carrier = (int)Tools::getValue('id_order_carrier');
            $address_input = Tools::getValue('address');
            $addit_services_input = Tools::getValue('addit_services');
            $export_docs_input = Tools::getValue('export_docs');

            $receiver_address = $this->flingex_api->getDHLDeliveryAddress(
                $id_address,
                isset($address_input[$id_order_carrier]) ? $address_input[$id_order_carrier] : false,
                $order
            );

            $formatted_product = false;
            $product_params = false;
        

            $dhl_errors = array();
            $dhl_warnings = array();
            $dhl_confirmations = array();

            $packages = array(
                array(
                    'weight' => (float)str_replace(',', '.', Tools::getValue('dhl_weight_package', 0)),
                    'length' => (int)Tools::getValue('dhl_length', 0),
                    'width'  => (int)Tools::getValue('dhl_width', 0),
                    'height' => (int)Tools::getValue('dhl_height', 0),
                )
            );
            if ($this->flingex_api->getMajorApiVersion() == 1 && Tools::getIsset('submitDHLDPDhlLabelWithReturnRequest')) {
                $dhl_errors[] = $this->l('This operation is no available');
            } elseif ($this->flingex_api->getMajorApiVersion() == 2 && Tools::getIsset('submitDHLDPDhlLabelReturnRequest')) {
                $dhl_errors[] = $this->l('This operation is no available');
            } elseif (Tools::strlen($product_code) == 0) {
                $dhl_errors[] = $this->l('Please select product.');
            } elseif ($formatted_product == false) {
                $dhl_errors[] = $this->l('This product is not added in list.');
            } elseif (isset($product_params['weight_package']['min']) && ($product_params['weight_package']['min'] > $packages[0]['weight'] || $product_params['weight_package']['max'] < $packages[0]['weight'])) {
                $dhl_errors[] = $this->l('Weight is invalid').' (min. '.$product_params['weight_package']['min'].' kg, max. '.$product_params['weight_package']['max'].' kg)';
            } elseif (isset($product_params['length']['min']) && ($product_params['length']['min'] > $packages[0]['length'] || $product_params['length']['max'] < $packages[0]['length'])) {
                $dhl_errors[] = $this->l('Length is invalid').' (min. '.$product_params['length']['min'].' cm, max. '.$product_params['length']['max'].' cm)';
            } elseif (isset($product_params['width']['min']) && ($product_params['width']['min'] > $packages[0]['width'] || $product_params['width']['max'] < $packages[0]['width'])) {
                $dhl_errors[] = $this->l('Width is invalid').' (min. '.$product_params['width']['min'].' cm, max. '.$product_params['width']['max'].' cm)';
            } elseif (isset($product_params['height']['min']) && ($product_params['height']['min'] > $packages[0]['height'] || $product_params['height']['max'] < $packages[0]['height'])) {
                $dhl_errors[] = $this->l('Height is invalid').' (min. '.$product_params['height']['min'].' cm, max. '.$product_params['height']['max'].' cm)';
            } elseif (isset($product_def['export_documents']) && !isset($export_docs_input[$id_order_carrier])) {
                $dhl_errors[] = $this->l('No data of export document.');
            } elseif (isset($product_def['export_documents']) && (!isset($export_docs_input[$id_order_carrier]['exportType']) || ($export_docs_input[$id_order_carrier]['exportType'] == '') || ($this->getExportTypeOptions($export_docs_input[$id_order_carrier]['exportType']) === false))) {
                $dhl_errors[] = $this->l('Please select export type in export document.');
            } elseif (isset($product_def['export_documents']) && (!isset($export_docs_input[$id_order_carrier]['placeOfCommital']) || ($export_docs_input[$id_order_carrier]['placeOfCommital'] == ''))) {
                $dhl_errors[] = $this->l('Please fill Place of commital in export document.');
            } elseif (isset($product_def['export_documents']) && (!isset($export_docs_input[$id_order_carrier]['additionalFee']) || ($export_docs_input[$id_order_carrier]['additionalFee'] == ''))) {
                $dhl_errors[] = $this->l('Please enter Additional custom fees in export document.');
            } else {
                $options = array();
                if (isset($addit_services_input[$id_order_carrier])) {
                    $options['addit_services'] = $addit_services_input[$id_order_carrier];
                }
                if (isset($export_docs_input[$id_order_carrier])) {
                    $options['export_docs'] = $export_docs_input[$id_order_carrier];
                }
                $options['shipment_date'] = Tools::getValue('dhl_shipment_date', date('Y-m-d'));

                $with_return = (bool)Tools::getIsset('submitDHLDPDhlLabelWithReturnRequest') ||
                    self::getConfig('DHL_LABEL_WITH_RETURN', $order->id_shop);
                $is_return = false;

                $result = $this->createDhlDeliveryLabel(
                    $receiver_address,
                    $product_code,
                    $packages,
                    $options,
                    $id_order_carrier,
                    (self::getConfig('DHL_REF_NUMBER', $order->id_shop) ? $order->id : $order->reference),
                    $is_return,
                    $with_return,
                    0,
                    $order->id_shop
                );

                if (!$result) {
                    if (is_array($this->flingex_api->errors) && count($this->flingex_api->errors) > 0) {
                        $dhl_errors = array_merge($dhl_errors, $this->flingex_api->errors);
                    } else {
                        $dhl_errors[] = $this->l('Unable to generate label for this request');
                    }
                } else {
                    $dhl_confirmations[] = $this->l('Shipment order and shipping label have been created.');
                }
            }

            if (is_array($this->flingex_api->warnings) && count($this->flingex_api->warnings) > 0) {
                $dhl_warnings = array_merge($dhl_warnings, $this->flingex_api->warnings);
            }

            $this->context->smarty->assign('dhl_errors', $dhl_errors);
            $this->context->smarty->assign('dhl_warnings', $dhl_warnings);
            $this->context->smarty->assign('dhl_confirmations', $dhl_confirmations);
        }

        $shipping = $this->filterShipping($order->getShipping(), $order->id_shop);
        $html = '';


        if (is_array($shipping)) {
            foreach ($shipping as $shipping_item) {
                $labels = $this->getLabelData($shipping_item['id_order_carrier']);

                $car = new Carrier((int)$shipping_item['id_carrier']);
                $shipping_item['carrier_name'] = $car->name;

                $last_label = array();
                if (is_array($labels) && count($labels) > 0) {
                    $last_label = $labels[count($labels) - 1];
                }

                $this->context->smarty->assign(
                    array(
                        'module_path'         => __PS_BASE_URI__.'modules/'.$this->name.'/',
                        'id_address'          => $order->id_address_delivery,
                        'carrier'             => $shipping_item,
                        'labels'              => $labels,
                        'last_label'          => $last_label,
                        'enable_return'       => false,
                        'with_return'         => /*($this->flingex_api->getMajorApiVersion() == 2)*/false,
                        'dhl_visual_age_check'    => self::getConfig('DHL_AGE_CHECK', $order->id_shop),
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
				if ($this->is177) {
					$this->context->smarty->assign('is177', true);
				} else {
					$this->context->smarty->assign('is177', false);
				}
                $perm_c = FlingexOrder::getPermissionForTransferring($order->id_cart);

                //update address
                $this->context->smarty->assign(
                    'address',
                    $this->getUpdateAddressTemplateVars(
                        $order,
                        $shipping_item['id_order_carrier'],
                        $order->id_address_delivery,
                        $perm_c
                    )
                );
                //addit services
                $this->context->smarty->assign(
                    'addit_services',
                    $this->getAdditServicesTemplateVars(
                        $order,
                        $shipping_item['id_order_carrier'],
                        $order->id_address_delivery,
                        $perm_c
                    )
                );
                //export docs
                $this->context->smarty->assign(
                    'export_docs',
                    $this->getExportDocumentsTemplateVars(
                        $order,
                        $shipping_item['id_order_carrier'],
                        $order->id_address_delivery
                    )
                );
				if($this->is177) {
					$html .= $this->display(
						__FILE__,
						'177/admin-carriers.tpl'
					);
				} else {
					$html .= $this->display(
						__FILE__,
						'admin-carriers.tpl'
					);
				}
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
        if (Validate::isLoadedObject($order)) {
            $weight = 0;
            if (self::getConfig('DHL_ORDER_WEIGHT', $order->id_shop)) {
                if (self::getConfig('DHL_WEIGHT_RATE', $order->id_shop) != '') {
                    $weight = round($order->getTotalWeight() * (float)self::getConfig('DHL_WEIGHT_RATE', $order->id_shop), 1);
                } else {
                    $weight = $order->getTotalWeight();
                }
            }
        }
        if ($weight == 0) {
            $weight = (float)self::getConfig('DHL_DEFAULT_WEIGHT', $order->id_shop);
        } else {
            $weight += (float)self::getConfig('DHL_PACK_WEIGHT', $order->id_shop);
        }
        return $weight;
    }

    public function getUpdateAddressTemplateVars($order, $id_order_carrier, $id_address_delivery, $perm_c)
    {
        $conf_private = self::getConfig('DHL_CONFIRMATION_PRIVATE', $order->id_shop);
        $oc = new OrderCarrier((int)$id_order_carrier);
        if (Validate::isLoadedObject($oc)) {
            $id_address = Hook::exec('actionGetIDDeliveryAddressByIDCarrier', array('id_carrier' => $oc->id_carrier));
            if ($id_address != false) {
                $id_address_delivery = $id_address;
            }
        }
        $delivery_address = new Address((int)$id_address_delivery);
        $norm_address = $this->flingex_api->normalizeAddress($delivery_address);
        $zip = '';
        if (isset($norm_address['Address']['Origin']['countryISOCode'])) {
            if ($norm_address['Address']['Origin']['countryISOCode'] == 'DE') {
                if (isset($norm_address['Address']['Zip']['germany'])) {
                    $zip = $norm_address['Address']['Zip']['germany'];
                }
            } elseif ($norm_address['Address']['Origin']['countryISOCode'] == 'GB') {
                if (isset($norm_address['Address']['Zip']['england'])) {
                    $zip = $norm_address['Address']['Zip']['england'];
                }
            } else {
                if (isset($norm_address['Address']['Zip']['other'])) {
                    $zip = $norm_address['Address']['Zip']['other'];
                }
            }
        }

        $addresses_input = Tools::getValue('address');
        $address_input = $addresses_input[$id_order_carrier];

        return array(
            'id_order_carrier'      => $id_order_carrier,
            'delivery_address'      => $delivery_address,
            'delivery_country'      => Country::getNameById(
                $this->context->language->id,
                $delivery_address->id_country
            ),
            'delivery_state'        => State::getNameById($delivery_address->id_state),
            'show_update_address'   => isset($address_input['show_update_address']) ? $address_input['show_update_address'] : '',
            'name1'                 => isset($address_input['name1']) ? $address_input['name1'] : (isset($norm_address['name1']) ? $norm_address['name1'] : ''),
            'name2'                 => isset($address_input['name2']) ? $address_input['name2'] : (isset($norm_address['name2']) ? $norm_address['name2'] : ''),
            'address_type'          => isset($address_input['address_type']) ? $address_input['address_type'] : (isset($norm_address['Packstation']) ? 'ps' : (isset($norm_address['Postfiliale']) ? 'pf' : 're')),
            'ps_packstation_number' => isset($address_input['ps_packstation_number']) ? $address_input['ps_packstation_number'] : (isset($norm_address['Packstation']['PackstationNumber']) ? $norm_address['Packstation']['PackstationNumber'] : ''),
            'ps_post_number'        => isset($address_input['ps_post_number']) ? $address_input['ps_post_number'] : (isset($norm_address['Packstation']['PostNumber']) ? $norm_address['Packstation']['PostNumber'] : ''),
            'ps_zip'                => isset($address_input['ps_zip']) ? $address_input['ps_zip'] : (isset($norm_address['Packstation']['Zip']) ? $norm_address['Packstation']['Zip'] : ''),
            'ps_city'               => isset($address_input['ps_city']) ? $address_input['ps_city'] : (isset($norm_address['Packstation']['City']) ? $norm_address['Packstation']['City'] : ''),
            'pf_postfiliale_number' => isset($address_input['pf_postfiliale_number']) ? $address_input['pf_postfiliale_number'] : (isset($norm_address['Postfiliale']['PostfilialeNumber']) ? $norm_address['Postfiliale']['PostfilialeNumber'] : ''),
            'pf_post_number'        => isset($address_input['pf_post_number']) ? $address_input['pf_post_number'] : (isset($norm_address['Postfiliale']['PostNumber']) ? $norm_address['Postfiliale']['PostNumber'] : ''),
            'pf_zip'                => isset($address_input['pf_zip']) ? $address_input['pf_zip'] : (isset($norm_address['Postfiliale']['Zip']) ? $norm_address['Postfiliale']['Zip'] : ''),
            'pf_city'               => isset($address_input['pf_city']) ? $address_input['pf_city'] : (isset($norm_address['Postfiliale']['City']) ? $norm_address['Postfiliale']['City'] : ''),
            'street_name'           => isset($address_input['street_name']) ? $address_input['street_name'] : (isset($norm_address['Address']['streetName']) ? $norm_address['Address']['streetName'] : ''),
            'street_number'         => isset($address_input['street_number']) ? $address_input['street_number'] : (isset($norm_address['Address']['streetNumber']) ? $norm_address['Address']['streetNumber'] : ''),
            'address_addition'          => isset($address_input['address_addition']) ? $address_input['address_addition'] : (isset($norm_address['Address']['addressAddition']) ? $norm_address['Address']['addressAddition'] : ''),
            'zip'                   => isset($address_input['zip']) ? $address_input['zip'] : (isset($zip) ? $zip : ''),
            'country_iso_code'      => isset($address_input['country_iso_code']) ? $address_input['country_iso_code'] : (isset($norm_address['Address']['Origin']['countryISOCode']) ? $norm_address['Address']['Origin']['countryISOCode'] : ''),
            'city'                  => isset($address_input['city']) ? $address_input['city'] : (isset($norm_address['Address']['city']) ? $norm_address['Address']['city'] : ''),
            'state'                 => isset($address_input['state']) ? $address_input['state'] : '',
            'comm_email'            => ((!is_array($perm_c) && $conf_private) || (is_array($perm_c) && $perm_c['permission_tpd'] == 0))?'':(isset($address_input['comm_email']) ? $address_input['comm_email'] : (isset($norm_address['Communication']['email']) ? $norm_address['Communication']['email'] : '')),
            'comm_phone'            => ((!is_array($perm_c) && $conf_private) || (is_array($perm_c) && $perm_c['permission_tpd'] == 0))?'':(isset($address_input['comm_phone']) ? $address_input['comm_phone'] : (isset($norm_address['Communication']['phone']) ? $norm_address['Communication']['phone'] : '')),
            'comm_mobile'           => ((!is_array($perm_c) && $conf_private) || (is_array($perm_c) && $perm_c['permission_tpd'] == 0))?'':(isset($address_input['comm_mobile']) ? $address_input['comm_mobile'] : (isset($norm_address['Communication']['mobile']) ? $norm_address['Communication']['mobile'] : '')),
            'comm_person'           => isset($address_input['comm_person']) ? $address_input['comm_person'] : (isset($norm_address['Communication']['contactPerson']) ? $norm_address['Communication']['contactPerson'] : ''),
            'permission_confirmation' => $perm_c
        );
    }

    public function isDomesticDelivery($id_shop, $id_address_delivery)
    {
        if (self::getConfig('DHL_COUNTRY', $id_shop) == $this->getCountryISOCodeByAddressID($id_address_delivery)) {
            return true;
        }
        return false;
    }

    public function getAdditServicesTemplateVars($order, $id_order_carrier, $id_address_delivery, $perm_c)
    {
        $services_input = Tools::getValue('addit_services');
        $service_input = $services_input[$id_order_carrier];

        $customer = new Customer($order->id_customer);

        return array(
            'id_order_carrier'      => $id_order_carrier,
            'show_dhl_additional_services'   => isset($service_input['show_dhl_additional_services']) ? $service_input['show_dhl_additional_services'] : '',

            'deliverytimeframe_options' => $this->getDeliveryTimeframeOptions(),
            'preferredtime_options' => $this->getPreferredTimeOptions(),
            'shipmenthandling_options' => $this->getShipmentHandlingOptions(),
            'endorsement_options' =>  $this->getEndorsementOptions('', $this->isDomesticDelivery($order->id_shop, $id_address_delivery)),
            'visualcheckofage_options' =>  $this->getVisualCheckOfAgeOptions(),

            'DayOfDelivery'         => isset($service_input['DayOfDelivery']) ? $service_input['DayOfDelivery'] : '',
            'PreferredTime'         => isset($service_input['PreferredTime']) ? $service_input['PreferredTime'] : '',
            'ReturnImmediately'         => isset($service_input['ReturnImmediately']) ? $service_input['ReturnImmediately'] : '',
            'DeliveryTimeframe'         => isset($service_input['DeliveryTimeframe']) ? $service_input['DeliveryTimeframe'] : '',
            'IndividualSenderRequirement' => isset($service_input['IndividualSenderRequirement']) ? $service_input['IndividualSenderRequirement'] : '',
            'PackagingReturn' => isset($service_input['PackagingReturn']) ? $service_input['PackagingReturn'] : '',
            'NoticeOfNonDeliverability' => isset($service_input['NoticeOfNonDeliverability']) ? $service_input['NoticeOfNonDeliverability'] : '',
            'ShipmentHandling' => isset($service_input['ShipmentHandling']) ? $service_input['ShipmentHandling'] : '',
            'Endorsement' => isset($service_input['Endorsement']) ? $service_input['Endorsement'] : '',
            'VisualCheckOfAge' => isset($service_input['VisualCheckOfAge']) ? $service_input['VisualCheckOfAge'] : self::getConfig('DHL_AGE_CHECK', $order->id_shop),
            'PreferredLocation' => isset($service_input['PreferredLocation']) ? $service_input['PreferredLocation'] : '',
            'PreferredNeighbour' => isset($service_input['PreferredNeighbour']) ? $service_input['PreferredNeighbour'] : '',
            'PreferredDay' => isset($service_input['PreferredDay']) ? $service_input['PreferredDay'] : '',
            'GoGreen' => isset($service_input['GoGreen']) ? $service_input['GoGreen'] : '',
            'Perishables' => isset($service_input['Perishables']) ? $service_input['Perishables'] : '',
            'Personally' => isset($service_input['Personally']) ? $service_input['Personally'] : '',
            'NoNeighbourDelivery' => isset($service_input['NoNeighbourDelivery']) ? $service_input['NoNeighbourDelivery'] : '',
            'NamedPersonOnly' =>  isset($service_input['NamedPersonOnly']) ? $service_input['NamedPersonOnly'] : '',
            'ReturnReceipt' =>  isset($service_input['ReturnReceipt']) ? $service_input['ReturnReceipt'] : '',
            'Premium' =>  isset($service_input['Premium']) ? $service_input['Premium'] : '',
            'Notification' => isset($service_input['Notification']) ? $service_input['Notification'] : '',
            'Notification_recepientEmailAddress' => isset($service_input['Notification_recepientEmailAddress']) ? $service_input['Notification_recepientEmailAddress'] : $customer->email,
            'CashOnDelivery' => isset($service_input['CashOnDelivery']) ? $service_input['CashOnDelivery'] : '',
            'CashOnDelivery_addFee' => isset($service_input['CashOnDelivery_addFee']) ? $service_input['CashOnDelivery_addFee'] : '',
            'CashOnDelivery_codAmount' => isset($service_input['CashOnDelivery_codAmount']) ? $service_input['CashOnDelivery_codAmount'] : '',
            'AdditionalInsurance' => isset($service_input['AdditionalInsurance']) ? $service_input['AdditionalInsurance'] : '',
            'AdditionalInsurance_insuranceAmount' => isset($service_input['AdditionalInsurance_insuranceAmount']) ? $service_input['AdditionalInsurance_insuranceAmount'] : '',
            'BulkyGoods' =>  isset($service_input['BulkyGoods']) ? $service_input['BulkyGoods'] : '',
            'IdentCheck' =>  isset($service_input['IdentCheck']) ? $service_input['IdentCheck'] : '',
            'IdentCheck_Ident_surname' =>  isset($service_input['IdentCheck_Ident_surname']) ? $service_input['IdentCheck_Ident_surname'] : '',
            'IdentCheck_Ident_givenName' =>  isset($service_input['IdentCheck_Ident_givenName']) ? $service_input['IdentCheck_Ident_givenName'] : '',
            'IdentCheck_Ident_dateOfBirth' =>  isset($service_input['IdentCheck_Ident_dateOfBirth']) ? $service_input['IdentCheck_Ident_dateOfBirth'] : '',
            'IdentCheck_Ident_minimumAge' =>  isset($service_input['IdentCheck_Ident_minimumAge']) ? $service_input['IdentCheck_Ident_minimumAge'] : '',
			'ParcelOutletRouting' => isset($service_input['ParcelOutletRouting']) ? $service_input['ParcelOutletRouting'] : '',
            'ParcelOutletRouting_details' => isset($service_input['ParcelOutletRouting_details']) ? $service_input['ParcelOutletRouting_details'] : '',
            'permission_confirmation' => $perm_c
        );
    }

    public function getExportDocumentsTemplateVars($order, $id_order_carrier, $id_address_delivery)
    {
        $docs_input = Tools::getValue('export_docs');
        $doc_input = $docs_input[$id_order_carrier];

        if (isset($doc_input['ExportDocPosition'])) {
            $exportdoc_positions = $doc_input['ExportDocPosition'];
        } else {
            $order_positions = $order->getProducts();
            $exportdoc_positions = array();

            $i = 0;
            foreach ($order_positions as $order_position) {
                if ($i < 99) {
                    $product_customs = Db::getInstance()->getRow('select customs_tariff_number, country_of_origin from '._DB_PREFIX_.'flingex_product_customs WHERE id_product='.(int)$order_position['id_product'].' and id_product_attribute=0');
                    $exportdoc_positions[] = array(
                        'description' => $order_position['product_name'],
                        'countryCodeOrigin' => ($product_customs && $product_customs['country_of_origin'] != '')?$product_customs['country_of_origin']:self::getConfig('DHL_COUNTRY', $order->id_shop),
                        'customsTariffNumber' => ($product_customs)?$product_customs['customs_tariff_number']:'',
                        'amount' => $order_position['product_quantity'],
                        'netWeightInKG' => number_format($order_position['product_weight'], 2, '.', ''),
                        'customsValue' => number_format($order_position['total_price_tax_incl'], 2, '.', '')
                    );
                    $i++;
                }
            }
        }

        return array(
            'id_order_carrier'      => $id_order_carrier,
            'show_dhl_export_documents'   => isset($doc_input['show_dhl_export_documents']) ? $doc_input['show_dhl_export_documents'] : '',

            'exporttype_options' => $this->getExportTypeOptions(),
            'termsoftrade_options' => $this->getTermsOfTradeOptions(),
            'exportdoc_positions' => $exportdoc_positions,
            'exportdoc_positions_limit_exceed' => (isset($order_positions) && (count($order_positions) > count($exportdoc_positions)))?true:false,
            'invoiceNumber'         => isset($doc_input['invoiceNumber']) ? $doc_input['invoiceNumber'] : '',
            'exportType'            => isset($doc_input['exportType']) ? $doc_input['exportType'] : '',
            'exportTypeDescription' => isset($doc_input['exportTypeDescription']) ? $doc_input['exportTypeDescription'] : '',
            'termsOfTrade' => isset($doc_input['termsOfTrade']) ? $doc_input['termsOfTrade'] : '',
            'placeOfCommital' => isset($doc_input['placeOfCommital']) ? $doc_input['placeOfCommital'] : '',
            'additionalFee' => isset($doc_input['additionalFee']) ? $doc_input['additionalFee'] : '',
            'permitNumber' => isset($doc_input['permitNumber']) ? $doc_input['permitNumber'] : '',
            'attestationNumber' => isset($doc_input['attestationNumber']) ? $doc_input['attestationNumber'] : '',
            'WithElectronicExportNtfctn' => isset($doc_input['WithElectronicExportNtfctn']) ? $doc_input['WithElectronicExportNtfctn'] : '',
            'ExportDocPosition' => isset($doc_input['ExportDocPosition']) ? $doc_input['ExportDocPosition'] : '',
        );
    }

    public function getTermsOfTradeOptions($option_key = '')
    {
        $res = array(
            'DDP' => $this->l('DDP (Delivery Duty Paid)'),
            'DXV' => $this->l('DXV (Delivery duty paid (excl. VAT))'),
            'DDU' => $this->l('DDU (DDU - Delivery Duty Paid)'),
            'DDX' => $this->l('DDX (Delivery duty paid (excl. Duties, taxes and VAT)'),
        );
        if ($option_key != '') {
            if (isset($res[$option_key])) {
                return $res[$option_key];
            } else {
                return false;
            }
        }
        return $res;
    }

    public function getExportTypeOptions($option_key = '')
    {
        $res = array(
            'COMMERCIAL_GOODS' => $this->l('COMMERCIAL_GOODS'),
            'OTHER' => $this->l('OTHER'),
            'PRESENT' => $this->l('PRESENT'),
            'COMMERCIAL_SAMPLE' => $this->l('COMMERCIAL_SAMPLE'),
            'DOCUMENT' => $this->l('DOCUMENT'),
            'RETURN_OF_GOODS' => $this->l('RETURN_OF_GOODS'),
        );
        if ($option_key != '') {
            if (isset($res[$option_key])) {
                return $res[$option_key];
            } else {
                return false;
            }
        }
        return $res;
    }

    public function getVisualCheckOfAgeOptions($option_key = '')
    {
        $res = array(
            'A16' => $this->l('16+ years'),
            'A18' => $this->l('18+ years'),
        );
        if ($option_key != '') {
            if (isset($res[$option_key])) {
                return $res[$option_key];
            } else {
                return false;
            }
        }
        return $res;
    }

    public function getEndorsementOptions($option_key = '', $is_domestic_delivery = null)
    {
        $res = array(
            'SOZU' => $this->l('Return immediately'),
            'ZWZU' => $this->l('2nd attempt of Delivery'),
            'IMMEDIATE' => $this->l('Sending back immediately to sender'),
            'AFTER_DEADLINE' => $this->l('Sending back immediately to sender after expiration of time'),
            'ABANDONMENT' => $this->l('Abandonment of parcel at the hands of sender (free of charge)'),
        );
        if ($option_key != '') {
            if (isset($res[$option_key])) {
                return $res[$option_key];
            } else {
                return false;
            }
        } else {
            if ($is_domestic_delivery !== null) {
                foreach ($res as $item_key => $item_value) {
                    if ((bool)$is_domestic_delivery === true) {
                        if (!in_array($item_key, array('SOZU', 'ZWZU'))) {
                            unset($res[$item_key]);
                        }
                    } else {
                        if (!in_array($item_key, array('IMMEDIATE', 'AFTER_DEADLINE', 'ABANDONMENT'))) {
                            unset($res[$item_key]);
                        }
                    }
                }
            }
        }
        return $res;
    }

    public function getDeliveryTimeframeOptions($option_key = '')
    {
        $res = array(
            '10001200' => $this->l('10:00 until 12:00'),
            '12001400' => $this->l('12:00 until 14:00'),
            '14001600' => $this->l('14:00 until 16:00'),
            '16001800' => $this->l('16:00 until 18:00'),
            '18002000' => $this->l('18:00 until 20:00'),
            '19002100' => $this->l('19:00 until 21:00'),
        );
        if ($option_key != '') {
            if (isset($res[$option_key])) {
                return $res[$option_key];
            } else {
                return false;
            }
        }
        return $res;
    }

    public function getPreferredTimeOptions($option_key = '')
    {
        $res = array(
            '10001200' => $this->l('10:00 until 12:00'),
            '12001400' => $this->l('12:00 until 14:00'),
            '14001600' => $this->l('14:00 until 16:00'),
            '16001800' => $this->l('16:00 until 18:00'),
            '18002000' => $this->l('18:00 until 20:00'),
            '19002100' => $this->l('19:00 until 21:00'),
        );
        if ($option_key != '') {
            if (isset($res[$option_key])) {
                return $res[$option_key];
            } else {
                return false;
            }
        }
        return $res;
    }

    public function getShipmentHandlingOptions($option_key = '')
    {
        $res = array(
            'a' => $this->l('Remove content, return box'),
            'b' => $this->l('Remove content, pick up and dispose cardboard packaging'),
            'c' => $this->l('Handover parcel/box to customer; no disposal of cardboard/box'),
            'd' => $this->l('Remove bag from of cooling unit and handover to customer'),
            'e' => $this->l('Remove content, apply return label und seal box, return box'),
        );
        if ($option_key != '') {
            if (isset($res[$option_key])) {
                return $res[$option_key];
            } else {
                return false;
            }
        }
        return $res;
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
                    $tracking_url = str_replace('[tracking_number]', $tracking_number, FlingexDHLFlingexApi::$tracking_url);

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
		Media::addJsDef(array('is177' => $this->is177));
        unset($params);
        $script = '';

        if (($this->context->controller->controller_name == 'AdminOrders' || $this->context->controller instanceof AdminOrdersController) && $this->is177 && !Tools::getIsset('id_order')) {
            global $kernel;
            $id_order = $kernel->getContainer()->get('request_stack')->getCurrentRequest()->get('orderId');
        } else {
            $id_order = Tools::getValue('id_order');
        }
        if (($this->context->controller->controller_name == 'AdminOrders' || $this->context->controller instanceof AdminOrdersController) && !$id_order) {

            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path . 'views/js/order-list.js');
            if ($this->is177) {
                $this->context->controller->addCSS($this->_path . 'views/css/order_list.css');
            }

            return '<script type="text/javascript">
                var dhldp_request_path = "' . $this->getModuleUrl(array('view' => 'generateLabels')) . '";
                var dhldp_translation = ' .
                Tools::jsonEncode(
                    array(
                        'Generate FLINGEX labels' => $this->l('Generate FLINGEX labels'),
                        'Generate LGTech labels' => $this->l('Generate LGTech labels'),
                    )
                ) . '</script>';
        } elseif (($this->context->controller->controller_name == 'AdminOrders' || $this->context->controller instanceof AdminOrdersController) && $id_order) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path . 'views/js/admin_order.js');
            $this->context->controller->addJS($this->_path . 'views/js/dp-admin-order.js');

            $this->context->controller->addCSS($this->_path . 'views/css/admin_order.css');
            $this->context->controller->addJS($this->_path . 'views/js/jquery.maxlength.min.js');
        } elseif (Tools::getValue('configure') == $this->name) {
            if (Tools::getValue('view') == 'settings_dp') {
                $this->context->controller->addJquery();
                $this->context->controller->addJS($this->_path . 'views/js/dp_admin_configure.js');
            } else {
                $this->context->controller->addJquery();
                if ((_PS_VERSION_ < '1.6.0.0')) {
                    $this->context->controller->addCSS($this->_path . 'views/css/admin-15.css');
                }
                $this->context->controller->addCSS($this->_path . 'views/css/admin.css');

                if (Tools::getValue('view') == 'generateLabels') {
                    $this->context->controller->addJS($this->_path . 'views/js/jquery.maxlength.min.js');
                    $this->context->controller->addJS($this->_path . 'views/js/admin_orders.js');
                } else {
                    $this->context->controller->addJquery();
                    $this->context->controller->addJqueryPlugin(array('idTabs', 'select2'));


                    if (version_compare(_PS_VERSION_, '1.6', '<')) {
                        $this->context->controller->addJS($this->_path . 'views/js/jquery.validate.js');
                    } else {
                        $this->context->controller->addJqueryPlugin('validate');
                        $this->context->controller->addJS(
                            _PS_JS_DIR_ . 'jquery/plugins/validate/localization/messages_' . $this->context->language->iso_code . '.js'
                        );
                    }
                    $this->context->controller->addJS($this->_path . 'views/js/admin_configure.js');

                    $dhl_products_js = array();
                    $dhl_gogreen_options_js = array();
                    $dhl_gogreen_option_js = new stdClass();
                    $dhl_gogreen_option_js->name = '';
                    $dhl_gogreen_option_js->code = '';
                    $dhl_gogreen_options_js[] = $dhl_gogreen_option_js;

                    $dhl_gogreen_option_js = new stdClass();
                    $dhl_gogreen_option_js->name = 'GoGreen';
                    $dhl_gogreen_option_js->code = 'gogreen';
                    $dhl_gogreen_options_js[] = $dhl_gogreen_option_js;
                    $script .= '<script>
                    var defined_dhl_api_versions = 1;
                    var defined_dhl_products = ' . Tools::jsonEncode($dhl_products_js) . ';
                    var dhl_gogreen_options = ' . Tools::jsonEncode($dhl_gogreen_options_js) . ';
                    var dhl_translation = ' .
                        Tools::jsonEncode(
                            array(
                                'Remove' => $this->l('Remove'),
                                'ExistsParticipation' => $this->l('Such participation exists for this product'),
                                'Exists' => $this->l('This product already exists in the list')
                            )
                        ) .
                        '</script>';
                }
            }
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
        $log = new Logging( _PS_MODULE_DIR_.'flingex/logs/log_'.$key.'.log' );
        $log->lwrite($service."->".$msg.PHP_EOL);
    }

    public function getContent()
    {
        $html = '';
        $view_mode = Tools::getValue('view');


        switch ($view_mode) {
            case 'generateLabels':
                if (Tools::isSubmit('generateMultipleLabels') || Tools::isSubmit('generateMultipleLabelsWithReturn')) {
                    $this->createDhlLabels(Tools::getValue('carrier'), Tools::isSubmit('generateMultipleLabelsWithReturn')?true:false);
                }

                if (Tools::isSubmit('printMultipleLabels')) {
                    $this->printDhlLabels(Tools::getValue('printLabel'));
                }

                $html .= $this->displayMessages();

                $this->context->smarty->assign(
                    array(
                        'module'           => $this,
                        'order_list'       => $this->getSelectedOrdersInfo(Tools::getValue('order_list')),
                        'self'             => dirname(__FILE__),
                        'shipment_date' => date('Y-m-d'),
                        'is177' => $this->is177
                    )
                );

                $this->context->controller->addCSS($this->_path.'views/css/admin_order.css');

                $html .= $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/hook/order-list.tpl');
                break;
            case 'information':
                $definition_pages = $this->getDefinitionConfigurePages();
                $html .= $this->displayMenu($definition_pages);
                $html .= $this->displayInfo();
                break;
            case 'changelog':
                $changelog_file = dirname(__FILE__).'/Readme.md';
                if (file_exists($changelog_file)) {
                    die($this->displayChangelog($changelog_file));
                }
                break;
            case 'init_dhl':
                $definition_pages = $this->getDefinitionConfigurePages();
                $html .= $this->displayMenu($definition_pages);
                $html .= $this->postInitDHLProcess();
                $html .= $this->displayFormInitDHLSettings();
                break;
            case 'settings_dp':
                $definition_pages = $this->getDefinitionConfigurePages();
                $html .= $this->displayMenu($definition_pages);
                $html .= $this->postProcess();
                $html .= $this->displayFormDPSettings();
                break;
            default:
                $definition_pages = $this->getDefinitionConfigurePages();
                $html .= $this->displayMenu($definition_pages);
                $html .= $this->postProcess();
                $html .= $this->displayFormDHLSettings();
                break;
        }
        return $html;
    }

    public function getDefinitionConfigurePages()
    {
        return array(
            'cparam' => 'view',
            'pages' => array(
                'settings_dhl' => array('name' => $this->l('Flingex settings'), 'default' => true),
                'settings_dp' => array('name' => $this->l('Lets go tech settings')),
//                'information' => array('name' => $this->l('Information'), 'icon' => ''),
            )
        );
    }

    public function displayMenu($def_pages)
    {
        $menu_items = array();
        foreach ($def_pages['pages'] as $page_key => $page_item) {
            $menu_items[$page_key] = array(
                'name' => $page_item['name'],
                'icon' => isset($page_item['icon']) ? $page_item['icon'] : '',
                'url' => $this->getModuleUrl().'&'.$def_pages['cparam'].'='.$page_key,
                'active' => ((!in_array(Tools::getValue($def_pages['cparam']), array_keys($def_pages['pages'])) && isset($page_item['default']) && $page_item['default'] == true) || Tools::getValue($def_pages['cparam']) == $page_key) ? true : false
            );
        }

        $this->smarty->assign(array(
            'menu_items' => $menu_items,
            'module_version' => $this->version,
            'module_name' => $this->displayName,
            'changelog' => file_exists(dirname(__FILE__).'/Readme.md'),
            'changelog_path' => $this->getModuleUrl().'&'.$def_pages['cparam'].'=changelog',
            '_path' => $this->_path
        ));

        return $this->display(__FILE__, 'views/templates/admin/menu.tpl');
    }


    public function createDhlLabels($collection, $with_return = false)
    {
        $general_errors = array();
        $general_confirmations = array();

        $error_order_line = array();
        $success_order_line = array();
        $warning_order_line = array();
        $orders_errors = array();
        $orders_confirmations = array();
        $orders_warnings = array();

        if (!is_array($collection) || !count($collection)) {
            return false;
        }

        $address_input = Tools::getValue('address');
        $addit_services_input = Tools::getValue('addit_services');
        $export_docs_input = Tools::getValue('export_docs');

        foreach ($collection as $id_order_carrier => $c) {
            $order_errors = array();
            $order_confirmations = array();
            $order_warnings = array();

            if (!Validate::isUnsignedId($c['id_order_carrier']) ||
                !Validate::isLoadedObject($order_carrier = new OrderCarrier((int)$c['id_order_carrier'])) ||
                !Validate::isLoadedObject($order = new Order((int)$order_carrier->id_order))) {
                $order_errors[] = $this->l('Invalid order carrier');
            }
            if (!Validate::isUnsignedId($c['id_carrier'])) {
                $order_errors[] = $this->l('Invalid carrier id');
            }
            if (!Validate::isUnsignedId($c['id_address'])) {
                $order_errors[] = $this->l('Invalid address id');
            }
            if (!Validate::isFloat($c['weight'])) {
                $order_errors[] = $this->l('Invalid weight');
            }
            if (!Validate::isFloat($c['width'])) {
                $order_errors[]  = $this->l('Invalid width');
            }
            if (!Validate::isFloat($c['height'])) {
                $order_errors[] = $this->l('Invalid height');
            }
            if (!Validate::isFloat($c['length'])) {
                $order_errors[]  = $this->l('Invalid length');
            }

            $this->flingex_api->setApiVersionByIdShop($order->id_shop);

            $receiver_address = $this->flingex_api->getDHLDeliveryAddress(
                $c['id_address'],
                isset($address_input[$id_order_carrier]) ? $address_input[$id_order_carrier] : false,
                $order
            );

                $formatted_product = false;
                $product_params = false;

            $packages = array(
                array(
                    'weight' => (float)str_replace(',', '.', $c['weight']),
                    'length' => (int)$c['length'],
                    'width'  => (int)$c['width'],
                    'height' => (int)$c['height'],
                )
            );
            //echo '<pre>'.print_r($packages, true).'</pre>';
            if (Tools::strlen($c['dhl_product_code']) == 0) {
                $order_errors[] = $this->l('Please select product.');
            }
            if ($formatted_product == false) {
                $order_errors[] = $this->_errors[] = $this->l('This product is not added in list.');
            }
            if ((isset($product_params['weight_package']['min']) && $product_params['weight_package']['min'] > $packages[0]['weight']) ||
                (isset($product_params['weight_package']['max']) && $product_params['weight_package']['max'] < $packages[0]['weight'])
            ) {
                $order_errors[] = $this->l('Weight is invalid').' (min. '.$product_params['weight_package']['min'].' kg, max. '.$product_params['weight_package']['max'].' kg)';
            }
            if ((isset($product_params['length']['min']) && $product_params['length']['min'] > $packages[0]['length']) ||
                (isset($product_params['length']['max']) && $product_params['length']['max'] < $packages[0]['length'])
            ) {
                $order_errors[] = $this->l('Length is invalid').' (min. '.$product_params['length']['min'].' cm, max. '.$product_params['length']['max'].' cm)';
            }
            if ((isset($product_params['width']['min']) && $product_params['width']['min'] > $packages[0]['width']) ||
                (isset($product_params['width']['max']) && $product_params['width']['max'] < $packages[0]['width'])
            ) {
                $order_errors[] = $this->l('Width is invalid').' (min. '.$product_params['width']['min'].' cm, max. '.$product_params['width']['max'].' cm)';
            }
            if ((isset($product_params['height']['min']) && $product_params['height']['min'] > $packages[0]['height']) ||
                (isset($product_params['height']['max']) && $product_params['height']['max'] < $packages[0]['height'])
            ) {
                $order_errors[] = $this->l('Height is invalid').' (min. '.$product_params['height']['min'].' cm, max. '.$product_params['height']['max'].' cm)';
            }
            if (isset($product_def['export_documents']) && !isset($export_docs_input[$id_order_carrier])) {
                $order_errors[] = $this->l('No data of export document.');
            }
            if (isset($product_def['export_documents']) && (!isset($export_docs_input[$id_order_carrier]['exportType']) || ($export_docs_input[$id_order_carrier]['exportType'] == '') || ($this->getExportTypeOptions($export_docs_input[$id_order_carrier]['exportType']) === false))) {
                $order_errors[] = $this->l('Please select export type in export document.');
            }
            if (isset($product_def['export_documents']) && (!isset($export_docs_input[$id_order_carrier]['placeOfCommital']) || ($export_docs_input[$id_order_carrier]['placeOfCommital'] == ''))) {
                $order_errors[] = $this->l('Please fill Place of commital in export document.');
            }
            if (isset($product_def['export_documents']) && (!isset($export_docs_input[$id_order_carrier]['additionalFee']) || ($export_docs_input[$id_order_carrier]['additionalFee'] == ''))) {
                $order_errors[] = $this->l('Please enter Additional custom fees in export document.');
            }

            if (!count($order_errors)) {
                $options = array();
                if (isset($addit_services_input[$id_order_carrier])) {
                    $options['addit_services'] = $addit_services_input[$id_order_carrier];
                }
                if (isset($export_docs_input[$id_order_carrier])) {
                    $options['export_docs'] = $export_docs_input[$id_order_carrier];
                }
                $options['shipment_date'] = Tools::getValue($c['dhl_shipment_date'], date('Y-m-d'));

                $with_return = (bool)self::getConfig('DHL_LABEL_WITH_RETURN', $order->id_shop);
                $is_return = false;

                $result = $this->createDhlDeliveryLabel(
                    $receiver_address,
                    $c['dhl_product_code'],
                    $packages,
                    $options,
                    $c['id_order_carrier'],
                    (self::getConfig('DHL_REF_NUMBER', $order->id_shop) ? $order->id : $order->reference),
                    $is_return,
                    $with_return,
                    0,
                    $order->id_shop
                );

                if (!$result) {
                    if (is_array($this->flingex_api->errors) && count($this->flingex_api->errors) > 0) {
                        /*foreach ($this->flingex_api->errors as $flingex_api_error) {
                            $this->_errors[] = sprintf($this->l('Order #%s :'), $c['order_id']).' '.$flingex_api_error;
                        }*/
                        $order_errors = array_merge($order_errors, $this->flingex_api->errors);
                    } else {
                        $order_errors[] = $this->_errors[] = sprintf($this->l('Order #%s :'), $c['order_id']).' '.$this->l('Unable to generate label for this request');
                    }
                    $error_order_line[] = $c['order_id'];
                } else {
                    $order_confirmations[] = $this->l('Shipment order and shipping label have been created.');
                    if (is_array($this->flingex_api->warnings) && count($this->flingex_api->warnings) > 0) {
                        $order_warnings = array_merge($order_warnings, $this->flingex_api->warnings);
                        $warning_order_line[] = $c['order_id'];
                    } else {
                        $success_order_line[] = $c['order_id'];
                    }
                    $general_confirmations[] = $this->l('Label has been generated for #').$c['order_id'];
                }
            } else {
                $error_order_line[] = $c['order_id'];
            }
            $orders_errors[$c['order_id']] = $order_errors;
            $orders_confirmations[$c['order_id']] = $order_confirmations;
            $orders_warnings[$c['order_id']] = $order_warnings;
            if (count($order_errors) > 0) {
                $general_errors[] = sprintf($this->l('Order #%s :'), $c['order_id']).' '.$this->l('There area errors on the form');
            }
        }
        $this->context->smarty->assign('general_errors', $general_errors);
        $this->context->smarty->assign('general_confirmations', $general_confirmations);

        $this->context->smarty->assign('orders_errors', $orders_errors);
        $this->context->smarty->assign('orders_warnings', $orders_warnings);
        $this->context->smarty->assign('orders_confirmations', $orders_confirmations);

        $this->context->smarty->assign('success_order_line', $success_order_line);
        $this->context->smarty->assign('warning_order_line', $warning_order_line);
        $this->context->smarty->assign('error_order_line', $error_order_line);
    }

    public function printDhlLabels($collection)
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            require_once(_PS_TOOL_DIR_ . 'tcpdf/config/lang/eng.php');
            require_once(_PS_TOOL_DIR_ . 'tcpdf/tcpdf.php');
        }
        require_once(dirname(__FILE__).'/classes/fpdi/fpdi.php');
        require_once(dirname(__FILE__).'/classes/PDFMerger.php');

        if (!is_array($collection) || !count($collection)) {
            return false;
        }

        $pdf = new PDFMerger();
        $i = 0;
        foreach ($collection as $c) {
            $label_file = $this->getLabelFilePathByLabelUrl($c['label_url']);
            if ($label_file != '') {
                $pdf->addPDF($label_file, 'all');
                $i++;
            }
        }
        if ($i > 0) {
            try {
                $pdf->merge('download', 'labels_'.date('YmdHis').'.pdf'); //download, browser
                exit;
            } catch (Exception $e) {
                $general_errors = array($e->getMessage());
                $this->context->smarty->assign('general_errors', $general_errors);
            }
        }
    }

    public function getLabelFilePathByLabelUrl($label_url)
    {
        if ($label_url != '') {
            $label_file = $this->getLabelFileNameByLabelUrl($label_url);
            if (!file_exists($label_file) || ((int)filesize($label_file) == 0)) {
                $content = Tools::file_get_contents($label_url);
                //if (strpos($content, '%PDF') !== false) {
                    file_put_contents($label_file, $content);
                //}
            }
            if (file_exists($label_file) && ((int)filesize($label_file) != 0)) {
                return $label_file;
            }
        }
        return '';
    }

    public function getLabelFileNameByLabelUrl($label_url)
    {
        return $this->getLocalPath().'pdfs/'.str_replace(array('?', '=', ' '), '', basename($label_url)).'.pdf';
    }

    public function getLabelFileURIByLabelUrl($label_url)
    {
        return $this->getPathUri().'pdfs/'.str_replace(array('?', '=', ' '), '', basename($label_url)).'.pdf';
    }

    public function saveLabelFile($label_url, $data)
    {
        $label_file = $this->getLabelFileNameByLabelUrl($label_url);
        if (!file_exists($label_file) || ((int)filesize($label_file) == 0)) {
            file_put_contents($label_file, $data);
        }
        if (file_exists($label_file) && ((int)filesize($label_file) != 0)) {
            return $label_file;
        }
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

    public function getSelectedOrdersInfo($order_list)
    {
        if (!$order_list || !is_array($order_list) || !count($order_list)) {
            return false;
        }

        $orders = Db::getInstance()->ExecuteS(
            '
                        SELECT
                        o.`id_order`,
                        o.`reference`,
                        o.`id_address_delivery`,
                        o.`id_customer`,
                        a.`id_country`,
                        oc.*,
                        c.`name` as `carrier_name`
                        FROM
                        `'._DB_PREFIX_.'order_carrier` oc
			LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = oc.`id_order`)
			LEFT JOIN `'._DB_PREFIX_.'address` a ON (o.`id_address_delivery` = a.`id_address`)
			LEFT JOIN `'._DB_PREFIX_.'carrier` c ON (c.`id_carrier` = oc.`id_carrier`)
			WHERE
			oc.`id_order` IN ('.(implode(',', array_map('intval', $order_list))).')'
        );

        if (!$orders) {
            return false;
        }

        $carrier_input = Tools::getValue('carrier');

        foreach ($orders as &$order) {
            $order_obj = new Order((int)$order['id_order']);
            $selected_carriers = $this->getDHLCarriers(true, false, $order_obj->id_shop);
            $ids_carriers = array_keys($selected_carriers);
            if (in_array($order['id_carrier'], $ids_carriers)) {
                $order['default_dhl_product_code'] = $selected_carriers[$order['id_carrier']]['product'];
                $order['dhl_assigned'] = true;
                $order['show_minimum_age'] = $this->isGermanyAddress($order['id_address_delivery']);
                $order['labels'] = $this->getLabelData($order['id_order_carrier']);

                $car = new Carrier((int)$order['id_carrier']);
                $order['carrier_name'] = $car->name;

                $order['selected'] = array();
                if (is_array($order['labels']) && count($order['labels']) > 0) {
                    $order['selected'] = $order['labels'][count($order['labels']) - 1];
                }

                //echo '<pre>'.print_r($carrier_input[$order['id_order_carrier']], true).'</pre>';
                $order['input_default_values'] = array(
                    'weight' => (is_array($carrier_input) && isset($carrier_input[$order['id_order_carrier']]['weight'])) ? $carrier_input[$order['id_order_carrier']]['weight'] :
                            ((isset($order['selected']['packages'][0]['weight'])) ? $order['selected']['packages'][0]['weight'] : $this->getOrderWeight($order_obj)),
                    'width' => (is_array($carrier_input) && isset($carrier_input[$order['id_order_carrier']]['width'])) ? $carrier_input[$order['id_order_carrier']]['width'] :
                            ((isset($order['selected']['packages'][0]['width'])) ? $order['selected']['packages'][0]['width'] : self::getConfig('DHL_DEFAULT_LENGTH', $order_obj->id_shop)),
                    'height' => (is_array($carrier_input) && isset($carrier_input[$order['id_order_carrier']]['height'])) ? $carrier_input[$order['id_order_carrier']]['height'] :
                            ((isset($order['selected']['packages'][0]['height'])) ? $order['selected']['packages'][0]['height'] : self::getConfig('DHL_DEFAULT_HEIGHT', $order_obj->id_shop)),
                    'depth' => (is_array($carrier_input) && isset($carrier_input[$order['id_order_carrier']]['depth'])) ? $carrier_input[$order['id_order_carrier']]['depth'] :
                            ((isset($order['selected']['packages'][0]['depth'])) ? $order['selected']['packages'][0]['depth'] : self::getConfig('DHL_DEFAULT_WIDTH', $order_obj->id_shop)),
                    'DeclaredValueOfGoods' => (is_array($carrier_input) && isset($carrier_input[$order['id_order_carrier']]['DeclaredValueOfGoods'])) ? $carrier_input[$order['id_order_carrier']]['DeclaredValueOfGoods'] :
                            ((isset($order['selected']['options_decoded']['DeclaredValueOfGoods'])) ? $order['selected']['options_decoded']['DeclaredValueOfGoods'] : $order_obj->getTotalProductsWithTaxes(
                            )),
                    'COD_CODAmount' => (is_array($carrier_input) && isset($carrier_input[$order['id_order_carrier']]['COD_CODAmount'])) ? $carrier_input[$order['id_order_carrier']]['COD_CODAmount'] :
                            ((isset($order['selected']['options_decoded']['COD']['CODAmount'])) ? $order['selected']['options_decoded']['COD']['CODAmount'] : 0),
                    'HigherInsurance_InsuranceAmount' => (is_array($carrier_input) && isset($carrier_input[$order['id_order_carrier']]['HigherInsurance_InsuranceAmount'])) ? $carrier_input[$order['id_order_carrier']]['HigherInsurance_InsuranceAmount'] :
                            ((isset($order['selected']['options_decoded']['HigherInsurance']['InsuranceAmount'])) ? $order['selected']['options_decoded']['HigherInsurance']['InsuranceAmount'] : 0),
                    'CheckMinimumAge_MinimumAge' => (is_array($carrier_input) && isset($carrier_input[$order['id_order_carrier']]['CheckMinimumAge_MinimumAge'])) ? $carrier_input[$order['id_order_carrier']]['CheckMinimumAge_MinimumAge'] :
                            ((isset($order['selected']['options_decoded']['CheckMinimumAge']['MinimumAge'])) ? $order['selected']['options_decoded']['CheckMinimumAge']['MinimumAge'] : self::getConfig('DHL_AGE_CHECK', $order_obj->id_shop)),
                );


                $perm_c = FlingexOrder::getPermissionForTransferring($order_obj->id_cart);

                $order['address'] = $this->getUpdateAddressTemplateVars(
                    $order_obj,
                    $order['id_order_carrier'],
                    $order['id_address_delivery'],
                    $perm_c
                );
                $order['addit_services'] = $this->getAdditServicesTemplateVars(
                    $order_obj,
                    $order['id_order_carrier'],
                    $order['id_address_delivery'],
                    $perm_c
                );
                $order['export_docs'] = $this->getExportDocumentsTemplateVars(
                    $order_obj,
                    $order['id_order_carrier'],
                    $order['id_address_delivery']
                );
            } else {
                $order['dhl_assigned'] = false;
            }

            $customer = new Customer((int)$order['id_customer']);

            $order = array_merge(
                $order,
                array(
                    'reference' => $order['reference'],
                    'country'   => Country::getNameById($this->context->language->id, $order['id_country']),
                    'customer'  => $customer->firstname.' '.$customer->lastname,
                )
            );
        }
        return count($orders) ? $orders : false;
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

    public function displayChangelog($file)
    {
        $this->smarty->assign(
            array(
                'changelog_content' => Tools::file_get_contents($file),
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/changelog.tpl');
    }

    protected function setFormFieldsValue(&$helper, $keys)
    {
        if (is_array($keys)) {
            foreach ($keys as $key) {
                $helper->fields_value[self::$conf_prefix.$key] = Tools::getValue(self::$conf_prefix.$key, self::getConfig($key));
            }
        }
    }
    protected function displayFormDPSettings()
    {
        $helper = new HelperForm();

        // Helper Options
        $helper->required = false;
        $helper->id = null;// Tab::getCurrentTabId();

        // Helper
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.'&view=settings_dp';
        $helper->table = 'dp_configure';
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

        $carriers = Carrier::getCarriers($this->context->language->id, true);
        $option_carriers = array();
        foreach ($carriers as $carrier) {
            $option_carriers[] = array('id_carrier' => $carrier['id_carrier'], 'name' => $carrier['name']);
        }

        $this->context->smarty->assign(
            array(
                'page_format' => Tools::getValue(self::$conf_prefix.'DP_PAGE_FORMAT', Configuration::get(self::$conf_prefix.'DP_PAGE_FORMAT')),
            )
        );

        return $helper->generateForm($this->getFormFieldsDPSettings());
    }

    protected function displayFormDHLSettings()
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

        $fields_value_keys = array('DHL_MODE', 'DHL_RETURN_PARTICIPATION', 'DHL_LIVE_USER', 'DHL_LIVE_SIGN',
            'DHL_LIVE_EKP', 'DHL_LOG', 'DHL_REF_NUMBER', 'DHL_ORDER_WEIGHT', 'DHL_WEIGHT_RATE', 'DHL_DEFAULT_WEIGHT', 'DHL_PACK_WEIGHT', 'DHL_AGE_CHECK',
            'DHL_PFPS', 'DHL_GOOGLEMAPAPIKEY', 'DHL_CHANGE_OS', 'DHL_RETURN_MAIL', 'DHL_INTRANSIT_MAIL', 'DHL_LABEL_WITH_RETURN',
            'DHL_CONFIRMATION_PRIVATE', 'DHL_RETURNS_EXTEND', 'DHL_RETURNS_RP', 'DHL_RETURNS_IMMED', 'DHL_RETOUREPORTAL_ID', 'DHL_RETOUREPORTAL_DNAME',
            'DHL_RETOUREPORTAL_USER', 'DHL_RETOUREPORTAL_PASS', 'DHL_COMPANY_NAME_1', 'DHL_COMPANY_NAME_2', 'DHL_CONTACT_PERSON',
            'DHL_STREET_NAME', 'DHL_STREET_NUMBER', 'DHL_ZIP', 'DHL_CITY', 'DHL_STATE', 'DHL_PHONE', 'DHL_EMAIL', 'DHL_ACCOUNT_OWNER',
            'DHL_ACCOUNT_NUMBER', 'DHL_BANK_CODE', 'DHL_BANK_NAME', 'DHL_IBAN', 'DHL_BIC', 'DHL_NOTE', 'DHL_DEFAULT_LENGTH', 'DHL_DEFAULT_WIDTH',
			'DHL_DEFAULT_HEIGHT', 'DHL_LABEL_FORMAT', 'DHL_RETOURE_LABEL_FORMAT');

        $this->setFormFieldsValue($helper, $fields_value_keys);

        $helper->fields_value[self::$conf_prefix.'DHL_RA_COUNTRIES[]'] = Tools::getValue(self::$conf_prefix.'DHL_RA_COUNTRIES',
            explode(',', self::getConfig('DHL_RA_COUNTRIES')));

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

    protected function displayFormInitDHLSettings()
    {
        $helper = new HelperForm();

        // Helper Options
        $helper->required = false;
        $helper->id = null; // Tab::getCurrentTabId();

        // Helper
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.'&view=init_dhl';
        $helper->table = 'flingex_ini_configure';
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

        $helper->fields_value['DHLDP_DHL_COUNTRY'] = Tools::getValue('DHLDP_DHL_COUNTRY', self::getConfig('DHL_COUNTRY'));
        return $helper->generateForm($this->getFormFieldsInitDHLSettings());
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

    private function displayDHLLogInformation()
    {
        // $this->smarty->assign(array(
        //         'general_log_file_path' => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&view=settings_dhl&log_file=dhl_general',
        //         'api_log_file_path' => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules') . '&view=settings_dhl&log_file=dhl_api',
        //     ));

        // return $this->display(__FILE__, 'views/templates/admin/log_information.tpl');
    }

    protected function getFormFieldsInitDHLSettings()
    {
        $form_fields = array();

        $api_versions = FlingexDHLFlingexApi::getSupportedApiVersions();
        $api_version_options = array(
            'id'    => 'value',
            'name'  => 'label'
        );
        foreach ($api_versions as $apiv) {
            $api_version_options['query'][] = array(
                'value' => $apiv,
                'label' => $apiv
            );
        }

        $shipper_country_options = array(
            'id'    => 'value',
            'name'  => 'label'
        );
        foreach (array_keys(FlingexDHLFlingexApi::$supported_shipper_countries) as $iso_code) {
            $shipper_country_options['query'][] = array(
                'value' => $iso_code,
                'label' => Country::getNameById($this->context->language->id, Country::getByIso($iso_code))
            );
        }

        $form_fields = array_merge(
            $form_fields,
            array(
                'form'  => array(
                    'form' => array(
                        'id_form'     => 'flingex_init_settings',
                        'legend'      => array(
                            'title' => $this->l('DHL init settings'),
                            'icon'  => 'icon-circle',
                        ),
                        'description' => $this->l('Please select shipper country and version of DHL API.'),
                        'input'       => array(
                            array(
                                'name'     => 'DHLDP_DHL_COUNTRY',
                                'type'     => 'select',
                                'label'    => $this->l('Country'),
                                'desc'     => $this->l(''),
                                'required' => true,
                                'options'  => $shipper_country_options
                            ),
                        ),
                        'submit'      => array(
                            'title' => $this->l('Save'),
                            'name'  => 'submitSaveOptions',
                        )
                    )
                ),
            )
        );

        return $form_fields;
    }

    protected function getFormFieldsDPSettings()
    {
        $form_fields = array(
            'form1' => array(
                'form' => array(
                    'id_form' => 'dp_global_settings',
                    'legend' => array(
                        'title' => $this->l('Global settings'),
                        'icon' => 'icon-circle',
                    ),
                    'description' => $this->l('Please select mode and fill form with all relevant information regarding authentication in modes.'),
                    'input' => array(
                        // array(
                        //     'type' => 'free',
                        //     'name' => 'log_information',
                        // ),
                    ),
                    'submit' => array(
                        'title' => $this->l('Save options'),
                        'name' => 'submitSaveDPOptions',
                    )
                ),

            ),
        );

        return $form_fields;
    }

    protected function getFormFieldsDHLSettings()
    {
        $form_fields = array();

        $vcoa_options = array();
        foreach ($this->getVisualCheckOfAgeOptions() as $option_key => $option_value) {
            $vcoa_options[] = array(
              'value' =>  $option_key,
              'name' =>  $option_value,
            );
        }

        $this->flingex_api->setApiVersion(self::getConfig('DHL_API_VERSION'));


        $form_fields = array_merge(
            $form_fields,
            array(
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
                                'name'   => 'DHLDP_DHL_MODE',
                                'type'   => 'radio',
                                'label'  => $this->l('Mode'),
                                'desc'   => $this->l('Select "Sandbox" for testing'),
                                'class'  => 't',
                                'values' => array(
                                    array(
                                        'id'    => 'dhl_mode_live',
                                        'value' => 1,
                                        'label' => $this->l('Live')
                                    ),
                                    array(
                                        'id'    => 'dhl_mode_sbx',
                                        'value' => 0,
                                        'label' => $this->l('Sandbox')
                                    ),
                                ),
                            ),
                            array(
                                'name'             => 'DHLDP_DHL_LIVE_USER',
                                'type'             => 'text',
                                'label'            => $this->l('Username'),
                                'desc'             => $this->l('"Live" username for user authentication for business customer shipping API'),
                                'required'         => true,
                                'form_group_class' => 'dhl_authdata_live'
                            ),
                            array(
                                'name'             => 'DHLDP_DHL_LIVE_PWD',
                                'type'             => 'text',
                                'label'            => $this->l('Password'),
                                'desc'             => $this->l('"Live" signature for user authentication for business customer shipping API'),
                                'required'         => true,
                                'form_group_class' => 'dhl_authdata_live'
                            ),
                            array(
                                'type'  => 'free',
                                'label' => '',
                                'name'  => 'DHLDP_DHL_LIVE_RESET',
                            ),
                            array(
                                'name'     => 'DHLDP_DHL_LOG',
                                'type'     => 'radio',
                                'label'    => $this->l('Enable Log'),
                                'desc'     => $this->l('Logs of actions in').' '.DIRECTORY_SEPARATOR.'logs '.
                                    $this->l('directory. Please notice: logs information can take a lot of disk space after a time.'),
                                'class'    => 't',
                                'is_bool'  => true,
                                'disabled' => false,
                                'values'   => array(
                                    array(
                                        'id'    => 'log_yes',
                                        'value' => 1,
                                        'label' => $this->l('Yes')
                                    ),
                                    array(
                                        'id'    => 'log_no',
                                        'value' => 0,
                                        'label' => $this->l('No')
                                    ),
                                ),
                            ),
                            array(
                                'type' => 'free',
                                'name' => 'log_information',
                            ),
                            array(
                                'type'     => 'radio',
                                'label'    => $this->l('Reference number in label is '),
                                'name'     => 'DHLDP_DHL_REF_NUMBER',
                                'required' => true,
                                'class'    => 't',
                                'br'       => true,
                                'values'   => array(
                                    array(
                                        'id'    => 'order_ref',
                                        'value' => 0,
                                        'label' => $this->l('Order reference')
                                    ),
                                    array(
                                        'id'    => 'order_number',
                                        'value' => 1,
                                        'label' => $this->l('Order ID')
                                    )
                                )
                            ),
                        ),
                        'submit'      => array(
                            'title' => $this->l('Save'),
                            'name'  => 'submitSaveOptions',
                        )
                    )
                ),
                'form3' => array(
                    'form' => array(
                        'legend' => array(
                            'title' => $this->l('Miscellaneous settings'),
                            'icon'  => 'icon-truck'
                        ),
                        'submit' => array(
                            'title' => $this->l('Save'),
                            'name'  => 'submitSaveOptions',
                        )
                    )
                )
            )
        );

        $form_fields['form3']['form']['input'][] = array(
            'name'     => 'DHLDP_DHL_LABEL_WITH_RETURN',
            'type'     => (_PS_VERSION_ < '1.6.0.0') ? 'radio' : 'switch',
            'label'    => $this->l('Enable generate label with return label'),
            'desc'     => $this->l('This option adds enclosed return label to generated label. Your customers receive a fully prepared return label with their delivery. If they choose to send an item back, all they have to do is pack it and affix the label. Supported products: FLINGEX Paket, FLINGEX Paket Austria, FLINGEX Paket Taggleich, FLINGEX Kurier Taggleich, FLINGEX Karier Wunschzeit'),
            'class'    => (_PS_VERSION_ < '1.6.0.0') ? 't' : '',
            'is_bool'  => true,
            'disabled' => false,
            'values'   => array(
                array(
                    'value' => 1,
                ),
                array(
                    'value' => 0,
                )
            ),
        );

        $form_fields['form3']['form']['input'][] = array(
            'name'     => 'DHLDP_DHL_LABEL_FORMAT',
            'type'     => 'select',
            'label'    => $this->l('Label format'),
            'disabled' => false,
            'options' => array(
                'query' => array_merge(
                    array(
                        array(
                            'id' => '',
                            'name' => $this->l('-- By default --')
                        )
                    ),
                    $this->getAssocArrayOptionsForSelect($this->getLabelFormats())
                ),
                'id'    => 'id',
                'name'  => 'name'
            )
        );

        $form_fields['form3']['form']['input'][] = array(
            'name'     => 'DHLDP_DHL_RETOURE_LABEL_FORMAT',
            'type'     => 'select',
            'label'    => $this->l('Retoure label format'),
            'disabled' => false,
            'options' => array(
                'query' => array_merge(
                    array(
                        array(
                            'id' => '',
                            'name' => $this->l('-- By default --')
                        )
                    ),
                    $this->getAssocArrayOptionsForSelect($this->getRetoureLabelFormats())
                ),
                'id'    => 'id',
                'name'  => 'name'
            )
        );

        return $form_fields;
    }

    public function postInitDHLProcess()
    {
        if (Tools::isSubmit('submitSaveOptions')) {
            $form_errors = array();

            if (!in_array(Tools::getValue('DHLDP_DHL_COUNTRY'), array_keys(FlingexDHLFlingexApi::$supported_shipper_countries))) {
                $form_errors[] = $this->_errors[] = $this->l('Please select supported country');
            }
            
        }
        return $this->displayMessages();
    }

    public function postProcess()
    {
        switch (Tools::getValue('m')) {
            case 1:
                $this->_confirmations[] = $this->l('"Live" account data has been reset.');
                break;
            case 2:
                $this->_errors[] = $this->l('No any log data');
                break;
            case 3:
                $this->_confirmations[] = $this->l('Fixed');
                break;
            case 4:
                $this->_confirmations[] = $this->l('Country and Api Version have been saved successfully');
                break;
        }

        if (Tools::getIsset('log_file')) {
            if (in_array(Tools::getValue('log_file'), array('dhl_general', 'dhl_api', 'dp_general', 'dp_api'))) {
                $key = Tools::getValue('log_file');
                $file_path = dirname(__FILE__) . '/logs/log_' . $key . '.txt';
                if (file_exists($file_path)) {
                    header('Content-type: text/plain');
                    header('Content-Disposition: attachment; filename=' . $key . '.txt');
                    echo Tools::file_get_contents($file_path);
                    exit;
                }
            }
            Tools::redirectAdmin($this->getModuleUrl().'&view='.Tools::getValue('view').'&m=2');
        }

        if (Tools::isSubmit('resetLiveAccount')) {
            if (Tools::getValue('DHLDP_DHL_MODE') == 1) {
                if (Configuration::updateValue('DHLDP_DHL_LIVE_USER', '') &&
                    Configuration::updateValue('DHLDP_DHL_LIVE_PWD', '')
                ) {
                    Tools::redirectAdmin($this->getModuleUrl().'&m=1');
                }
            }
        }

        /*
        if (Tools::isSubmit('submitDPUpdatePPL')) {
            if ($this->dp_api->updatePPL()) {
                $this->_confirmations[] = $this->l('PPL has been updated successfully');
            } else {
                $this->_errors[] = $this->l('PPL update is failed');
            }
        }
        */

        if (Tools::isSubmit('submitDPGetProductList')) {
            if ($this->dp_api->getProductList()) {
                $this->_confirmations[] = $this->l('Product list has been updated successfully');
            } else {
                $this->_errors[] = $this->l('Product list updating has been failed');
            }

            if ($this->dp_api->retrieveContractProducts()) {
                $this->_confirmations[] = $this->l('Contract product list has been updated successfully');
            } else {
                $this->_errors[] = $this->l('Contract product list updating has been failed');
            }
        }

        if (Tools::isSubmit('submitDPRetrievePageFormats')) {
            if ($this->dp_api->retrievePageFormats()) {
                $this->_confirmations[] = $this->l('Page formats has been retrieved successfully');
            } else {
                $this->_errors[] = $this->l('Page formats retrieving is failed');
            }
        }

        if (Tools::isSubmit('submitSaveDPOptions')) {
            $form_errors = array();

            $page_formats = Tools::jsonDecode(Configuration::getGlobalValue('DHLDP_DP_PAGE_FORMATS'), true);
            $page_formats_keys = array_keys($page_formats, true);
            if (count($page_formats_keys) == 0) {
                $page_formats_keys = array(1); //A4
            }
            $page_format_id = (Tools::getValue('DHLDP_DP_PAGE_FORMAT', 1)) == ''?1:Tools::getValue('DHLDP_DP_PAGE_FORMAT', 1);


            if (count($form_errors) == 0) {
                $result_save = Configuration::updateValue('DHLDP_DP_PAGE_FORMAT', (int)$page_format_id) &&
                    Configuration::updateValue('DHLDP_DP_POSITION_PAGE', (int)Tools::getValue('DHLDP_DP_POSITION_PAGE', 1)) &&
                    Configuration::updateValue('DHLDP_DP_POSITION_ROW', (int)Tools::getValue('DHLDP_DP_POSITION_ROW', 1)) &&
                    Configuration::updateValue('DHLDP_DP_POSITION_COL', (int)Tools::getValue('DHLDP_DP_POSITION_COL', 1));
                if ($result_save == true) {
                    $this->_confirmations[] = $this->l('Settings updated');
                }
            }
        }

        if (Tools::isSubmit('submitAddDHLDP_dhl_configure')) {
            Configuration::updateValue('DHLDP_DHL_COUNTRY', 'DE');

            $form_errors = array();

            $dhl_mode = Tools::getValue('DHLDP_DHL_MODE');

            $dhl_live_user = Tools::getValue('DHLDP_DHL_LIVE_USER');
            $dhl_live_sign = Tools::getValue('DHLDP_DHL_LIVE_PWD');

            $dhl_log = Tools::getValue('DHLDP_DHL_LOG');
            $dhl_carriers = array();
            foreach (Tools::getValue('dhl_carriers', array()) as $value) {
                if (isset($value['carrier']) && isset($value['product'])) {
                    $dhl_carriers[] = $value['carrier'].'|'.$value['product'];
                }
            }

            $added_dhl_products = Tools::getValue('added_dhl_products', array());

            if (!in_array($dhl_mode, array('0', '1'))) {
                $form_errors[] = $this->_errors[] = $this->l('Please select mode of DHL');
            }

            if ($dhl_mode == '1' && $dhl_live_user == '') {
                $form_errors[] = $this->_errors[] = $this->l('Please fill username');
            }

            if ($dhl_mode == '1' && $dhl_live_sign == '') {
                $form_errors[] = $this->_errors[] = $this->l('Please fill signature');
            }

            if (count($form_errors) == 0) {
                
                $check_client = $this->flingex_api->checkDHLAccount($dhl_mode, $dhl_live_user, $dhl_live_sign);

                self::logToFile('DHL', implode(', ', $this->flingex_api->errors), 'general');
                
            }

            if (!in_array($dhl_log, array('0', '1'))) {
                $form_errors[] = $this->_errors[] = $this->l('Please select log mode');
            }

            if (!in_array((int)Tools::getValue('DHLDP_DHL_REF_NUMBER'), array('0', '1'))) {
                $form_errors[] = $this->_errors[] = $this->l('Please select Reference number');
            }


			if (Tools::getValue('DHLDP_DHL_LABEL_FORMAT') != '' && !in_array(Tools::getValue('DHLDP_DHL_LABEL_FORMAT'), array_keys($this->getLabelFormats()))) {
				$form_errors[] = $this->_errors[] = $this->l('Invalid label format');
			}

			if (Tools::getValue('DHLDP_DHL_RETOURE_LABEL_FORMAT') != '' && !in_array(Tools::getValue('DHLDP_DHL_RETOURE_LABEL_FORMAT'), array_keys($this->getRetoureLabelFormats()))) {
				$form_errors[] = $this->_errors[] = $this->l('Invalid retoure label format');
			}

            if (count($form_errors) == 0) {
                $result_save = Configuration::updateValue('DHLDP_DHL_MODE', (int)Tools::getValue('DHLDP_DHL_MODE')) &&
                    Configuration::updateValue('DHLDP_DHL_LIVE_USER', Tools::getValue('DHLDP_DHL_LIVE_USER')) &&
                    Configuration::updateValue('DHLDP_DHL_LIVE_PWD', Tools::getValue('DHLDP_DHL_LIVE_PWD')) &&
                    //
                    Configuration::updateValue('DHLDP_DHL_LOG', (int)Tools::getValue('DHLDP_DHL_LOG')) &&
                    Configuration::updateValue('DHLDP_DHL_REF_NUMBER', (int)Tools::getValue('DHLDP_DHL_REF_NUMBER')) &&
                    Configuration::updateValue('DHLDP_DHL_CARRIERS', implode(',', $dhl_carriers)) &&

                    Configuration::updateValue('DHLDP_DHL_RETURN_MAIL', (int)Tools::getValue('DHLDP_DHL_RETURN_MAIL', (int)self::getConfig('DHL_RETURN_MAIL'))) &&
                    Configuration::updateValue('DHLDP_DHL_LABEL_WITH_RETURN', (int)Tools::getValue('DHLDP_DHL_LABEL_WITH_RETURN', (int)self::getConfig('DHL_LABEL_WITH_RETURN'))) &&
					Configuration::updateValue('DHLDP_DHL_LABEL_FORMAT', Tools::getValue('DHLDP_DHL_LABEL_FORMAT', self::getConfig('DHL_LABEL_FORMAT'))) &&
					Configuration::updateValue('DHLDP_DHL_RETOURE_LABEL_FORMAT', Tools::getValue('DHLDP_DHL_RETOURE_LABEL_FORMAT', self::getConfig('DHL_RETOURE_LABEL_FORMAT')));

                if ($result_save == true) {
                    $this->_confirmations[] = $this->l('Settings updated');
                }
            }
        }
        return $this->displayMessages();
    }

	private function getLabelFormats()
	{
		return array(
		    'A4' => 'A4',
            '910-300-700' => '910-300-700 (A5)',
            '910-300-700-oZ' => '910-300-700-oZ (A5)',
            '910-300-600' => '910-300-600 (99x200mm)',
            '910-300-610' => '910-300-610 (99x200mm)' ,
            '910-300-710' => '910-300-710 (105x203mm)'
        );
	}

	private function getRetoureLabelFormats()
	{
		return $this->getLabelFormats();
	}

	private function getArrayOptionsForSelect($array)
	{
		if (is_array($array)) {
			$arr = array();
			foreach ($array as $value) {
				$arr[] = array('id' => $value, 'name' => $value);
			}
			return $arr;
		}
		return array();
	}

    private function getAssocArrayOptionsForSelect($array)
    {
        if (is_array($array)) {
            $arr = array();
            foreach ($array as $key => $value) {
                $arr[] = array('id' => $key, 'name' => $value);
            }
            return $arr;
        }
        return array();
    }

    public function getDPLabelData($id_order_carrier)
    {
        return false;
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

    public function createDPDeliveryLabel($id_shop, $id_address, $product, $additional_info, $label_position, $id_order_carrier, $reference_number)
    {
        return false;
    }

    public function updateDPOrderCarrierWithTrackingNumber($id_order_carrier, $tracking_number)
    {
        

        return false;
    }

	public function getCountriesAndReceiverIDsForRA($iso_code = null)
    {
        $res = array(
            array('iso_code' => 'BE', 'iso_code3' => 'BEL', 'receiverid' => 'bel'),
            array('iso_code' => 'BG', 'iso_code3' => 'BGR', 'receiverid' => 'bgr'),
            array('iso_code' => 'DK', 'iso_code3' => 'DNK', 'receiverid' => 'dnk'),
            array('iso_code' => 'DE', 'iso_code3' => 'DEU', 'receiverid' => 'deu'),
            array('iso_code' => 'EE', 'iso_code3' => 'EST', 'receiverid' => 'est'),
            array('iso_code' => 'FI', 'iso_code3' => 'FIN', 'receiverid' => 'fin'),
            array('iso_code' => 'FR', 'iso_code3' => 'FRA', 'receiverid' => 'fra'),
            array('iso_code' => 'GR', 'iso_code3' => 'GRC', 'receiverid' => 'grc'),
            array('iso_code' => 'GB', 'iso_code3' => 'GBR', 'receiverid' => 'gbr'),
            array('iso_code' => 'IE', 'iso_code3' => 'IRL', 'receiverid' => 'irl'),
            array('iso_code' => 'HR', 'iso_code3' => 'HRV', 'receiverid' => 'hrv'),
            array('iso_code' => 'LV', 'iso_code3' => 'LVA', 'receiverid' => 'lva'),
            array('iso_code' => 'LT', 'iso_code3' => 'LTU', 'receiverid' => 'ltu'),
            array('iso_code' => 'LU', 'iso_code3' => 'LUX', 'receiverid' => 'lux'),
            array('iso_code' => 'MT', 'iso_code3' => 'MLT', 'receiverid' => 'mlt'),
            array('iso_code' => 'NL', 'iso_code3' => 'NLD', 'receiverid' => 'nld'),
            array('iso_code' => 'AT', 'iso_code3' => 'AUT', 'receiverid' => 'aut'),
            array('iso_code' => 'PL', 'iso_code3' => 'POL', 'receiverid' => 'pol'),
            array('iso_code' => 'PT', 'iso_code3' => 'PRT', 'receiverid' => 'prt'),
            array('iso_code' => 'RO', 'iso_code3' => 'ROU', 'receiverid' => 'rou'),
            array('iso_code' => 'SE', 'iso_code3' => 'SWE', 'receiverid' => 'swe'),
            array('iso_code' => 'CH', 'iso_code3' => 'CHE', 'receiverid' => 'che'),
            array('iso_code' => 'SK', 'iso_code3' => 'SVK', 'receiverid' => 'svk'),
            array('iso_code' => 'SI', 'iso_code3' => 'SVN', 'receiverid' => 'svn'),
            array('iso_code' => 'ES', 'iso_code3' => 'ESP', 'receiverid' => 'esp'),
            array('iso_code' => 'CZ', 'iso_code3' => 'CZE', 'receiverid' => 'cze'),
            array('iso_code' => 'HU', 'iso_code3' => 'HUN', 'receiverid' => 'hun'),
            array('iso_code' => 'CY', 'iso_code3' => 'CYP', 'receiverid' => 'cyp')
        );
        if ($iso_code == null) {
            return $res;
        } else {
            foreach ($res as $country) {
                if ($country['iso_code'] == $iso_code) {
                    return $country;
                }
            }
        }
        return false;
    }

    public function getCountriesForRA($id_lang, $limited = array(), $with_keys = false)
    {
        $c = array();
        if (count($limited) == 0) {
            $res = $this->getCountriesAndReceiverIDsForRA();
            foreach ($res as $item) {
                $c[] = '\''.$item['iso_code'].'\'';
            }
        } else {
            $res = $limited;
            foreach ($res as $item) {
                $c[] = '\''.$item.'\'';
            }
        }

        $countries = Db::getInstance(_PS_USE_SQL_SLAVE_)->executes(
            'SELECT cl.`name`, c.iso_code
							FROM `' . _DB_PREFIX_ . 'country_lang` as cl, `' . _DB_PREFIX_ . 'country` as c
							WHERE cl.id_country=c.id_country and cl.`id_lang` = ' . (int) $id_lang . '
							and c.iso_code in ('.implode(',', $c).')');
        if ($with_keys) {
            $c = array();
            foreach ($countries as $country) {
                $c[$country['iso_code']] = $country['name'];
            }
            return $c;
        }
        return $countries;
    }


    public function phpCurlRequest($curlUrl, $method, $data,$headers) {
        $req = '';
        $curl = curl_init();
    
        switch ($method){
            case "POST":
                
                if ($data) {
                    if (is_array($data)) {
    
                        foreach ($data as $key => $value) {
                            $value = (stripslashes($value));
                            // $value = urlencode(stripslashes($value));
                            $req .= "&$key=$value";
                        }
                        $req =  substr($req, 1);
                    } else 
                        $req = $data;
                }
                curl_setopt($curl, CURLOPT_POSTFIELDS, $req);
                curl_setopt($curl, CURLOPT_POST, 1);
    
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                if ($data)
                    $curlUrl = sprintf("%s?%s", $curlUrl, http_build_query($data));
        }
        curl_setopt($curl, CURLOPT_URL, $curlUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    
        $res = curl_exec($curl);
    
        if (!$res) {
            $errno = curl_errno($curl);
            $errstr = curl_error($curl);
            curl_close($curl);
            throw new Exception("cURL error: [$errno] $errstr");
        }
    
        $info = curl_getinfo($curl);
    
        // Check the http response
        $httpCode = $info['http_code'];
        if ($httpCode >= 200 && $httpCode < 300) {
            curl_close($curl);
            return $res;
        } else {
           return $httpCode;
        }
    }
    

    /*******sent to shipping via flingex***********/

    public function sentToFlingexOrder($order){

        $delivery_address_id=$order->id_address_delivery;
        $address= Db::getInstance()->executeS(
            'SELECT *
            FROM `'._DB_PREFIX_.'address`
            WHERE id_address ='.$delivery_address_id
        );

        $post_data = new stdClass();
        $post_data->merOrderRef = $order->reference;
        $post_data->pickMerchantName = "Rokan";
        $post_data->pickMerchantAddress = "Dhanmondi";
        $post_data->pickMerchantThana = "Dhanmondi";
        $post_data->pickMerchantDistrict = "Dhaka";
        $post_data->pickupMerchantPhone = "01829331461";
        $post_data->productSizeWeight = "standard";
        $post_data->productBrief = "USB Fan";
        $post_data->packagePrice = "1500";
        $post_data->deliveryOption = "regular";
        $post_data->custname = $address[0]['firstname'].' '.$address[0]['lastname'];
        $post_data->custaddress = $address[0]['address1'];
        $post_data->customerThana = 'Badda';
        $post_data->customerDistrict = $address[0]['city'];
        $post_data->custPhone =$address[0]['phone'];
        $post_data->max_weight = "10";
        $post_data_obj = json_encode($post_data);

        $apiJsonResponse = self::callFlingexAPI("POST","https://sandbox.flingexbd.com/OrderPlacement",$post_data_obj,'Flingex_~La?Rj73FcLm');
//       print_r($apiJsonResponse);
//       die('here');
        return $apiJsonResponse;

    }

    /*******sent to shipping via flingex***********/

    public function sentToFlingexOrderTrackingApi($order){

        $post_data = new stdClass();
        $post_data->ReferenceNumber = $order->reference;
        $post_data_obj = json_encode($post_data);
        $apiJsonResponse = self::callFlingexAPI("POST","https://sandbox.flingexbd.com/API-Order-Tracking",$post_data_obj,'Flingex_~La?Rj73FcLm');
        return $apiJsonResponse;

    }

    /*******sent to shipping via flingex***********/

    public static function flingexOrderTrackingApiCronProcess($order){

        $post_data = new stdClass();
        $post_data->ReferenceNumber = $order;
        $post_data_obj = json_encode($post_data);
        $apiJsonResponse = self::callFlingexAPI("POST","https://sandbox.flingexbd.com/API-Order-Tracking",$post_data_obj,'Flingex_~La?Rj73FcLm');
        return $apiJsonResponse;

    }


    /********API call response********/

    public static function callFlingexAPI($method, $url, $data = null,$headers = false)
    {
        $curl = curl_init();
        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "c116552:1234");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'flingexkey: Flingex_~La?Rj73FcLm',
            'Authorization: Basic YzExNjU1MjoxMjM0',
            'Content-Type: application/json'
        ));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    // http://localhost/ps174/modules/flingex/cron.php?token=f4247629b6&return_message=1&run=1
    public static function cronProcess($value, $time)
    {

        $time = pSQL(Tools::getValue('time', microtime(true)));

        $order_query = Db::getInstance()->executeS(
            'SELECT po.reference
            FROM '._DB_PREFIX_.'flingex_order po 
            left JOIN '._DB_PREFIX_.'flingex_order_tracking pot 
            ON (po.id_flingex_order=pot.id_flingex_order)group by po.reference'
        );

        foreach (array_column($order_query,'reference') as $key=>$ref){
            $thi_ref="'".$ref."'";
            $del_sql='DELETE FROM `'._DB_PREFIX_.'flingex_order_tracking`
                        where `reference` = '.$thi_ref;
            $del_res=Db::getInstance()->execute($del_sql);

            $query = Db::getInstance()->getRow(
                'SELECT po.reference,po.id_flingex_order,po.tracking_number,po.id_order
            FROM '._DB_PREFIX_.'flingex_order po 
            where `reference` = '.$thi_ref);
            $flingex_order_id=$query['id_flingex_order'];
            $flingex_traking_number="'" .$query['tracking_number']."'";

            $tracking_api_response = self::flingexOrderTrackingApiCronProcess($ref);
            $tracking_response_data = (json_decode($tracking_api_response)->response_code == '200') ? json_decode($tracking_api_response)->success->trackingStatus : '';
            $tracking_api_response_code = "'" . json_decode($tracking_api_response)->response_code . "'";
            $tracking_api_response_message = (json_decode($tracking_api_response)->response_code == '200') ? 
                json_decode($tracking_api_response)->success->message
                : json_decode($tracking_api_response)->error->message;
            $res='';
            if(!is_array($tracking_response_data))
                $tracking_response_data = [[]];
            foreach ((array)($tracking_response_data[0]) as $key => $value) {
                $this_key = "'" . $key . "'";
                $this_val = "'" . $value . "'";
                $sql_tracking = 'INSERT INTO ' . _DB_PREFIX_ . 'flingex_order_tracking
            (`id_order`,`reference`, `id_flingex_order`, `tracking_number`,`tracking_event_key`,`tracking_event_value`,
            `api_response_status_code`,`api_response_status_message`)
            values(
             ' . (int)$query['id_order'] . ',
             ' . $thi_ref . ',
             ' . $flingex_order_id . ',
             ' . $flingex_traking_number . ',
             ' . $this_key . ',
             ' . $this_val . ',
             ' . $tracking_api_response_code . ',
             "' . $tracking_api_response_message . '"
            )';
              $res=Db::getInstance()->execute($sql_tracking);
            }

        }
        if($res){
            echo 'job complete '.'processing time: '.Tools::ps_round((microtime(true) - $time), 2).' seconds';
        }else{
            echo 'something is wrong';
        }
    }
}




