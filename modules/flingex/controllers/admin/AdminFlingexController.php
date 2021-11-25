<?php

/**
 * DHL Deutschepost
 *
 * @author    silbersaiten <info@silbersaiten.de>
 * @copyright 2021 silbersaiten
 * @license   See joined file licence.txt
 * @category  Module
 * @support   silbersaiten <support@silbersaiten.de>
 * @version   1.0.7
 * @link      http://www.silbersaiten.de
 */

class AdminFlingexController extends ModuleAdminController
{
	public function __construct()
	{
		// $this->table = '';
		$this->show_toolbar = false;
		$this->multishop_context = Shop::CONTEXT_SHOP;
		$this->context = Context::getContext();

		parent::__construct();

		// $this->display = 'manifest';
	}
	
	public function init()
	{
		parent::init();
		$this->bootstrap = true;
	}

	public function initContent()
	{
		parent::initContent();

		$flingex_orders = Db::getInstance()->executeS(
			'SELECT * FROM ' . _DB_PREFIX_ . 'flingex_order po 
            INNER JOIN ' . _DB_PREFIX_ . 'orders odr ON (po.id_order=odr.id_order)
            ORDER BY \'po.date_add\' DESC LIMIT 20
            '
			// INNER JOIN '._DB_PREFIX_.'order_detail od ON (po.id_order=od.id_order)
			// LEFT JOIN '._DB_PREFIX_.'flingex_order_tracking pot ON (po.id_flingex_order=pot.id_flingex_order)group by po.reference'
		);
		// var_dump($flingex_orders);

		foreach ($flingex_orders as $key => $order) {
			$_SQL = 'SELECT * FROM ' . _DB_PREFIX_ . 'flingex_order_tracking where id_order =' . $order['id_order'];
			$flingex_orders[$key]['tracking_data'] = Db::getInstance()->executeS($_SQL);
			$ORDER_DETAILS_SQL = 'SELECT * FROM ' . _DB_PREFIX_ . 'order_detail where id_order =' . $order['id_order'];
			$flingex_orders[$key]['products'] = Db::getInstance()->executeS($ORDER_DETAILS_SQL);
		}

		$this->context->smarty->assign([
			'manifestDate' => Tools::getValue('manifestDate', date('Y-m-d')),
			'orders' => $flingex_orders,
		]);
		$this->setTemplate('track.tpl');
	}

	public function initProcess()
	{
		parent::initProcess();
	}

	public function postProcess()
	{
		parent::postProcess();
	}

	public function setMedia($isNewTheme = false)
	{
		parent::setMedia($isNewTheme);
		if (Tools::getValue('controller') == 'AdminFlingex') {

			// Create a link with the good path
			$link = new Link;
			$parameters = array("action" => "action_name");
			$ajax_link = $link->getModuleLink($this->module->name, 'ajax.php', $parameters);

			Media::addJsDef(array(
				'flingex_ajax' => $this->context->link->getAdminLink('AdminFlingex'),
				'flingex_ajax1' => $ajax_link,
				'flingex_ajax2' => _PS_BASE_URL_ . __PS_BASE_URI__ . 'moduels/' . $this->module->name . '/ajax.php',
			));
			$this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/track.js');
			// $this->addCSS([_MODULE_DIR_ . $this->module->name . '/views/css/admin.css', _MODULE_DIR_ . $this->module->name . '/views/css/private.css']);
		}
	}


	public function ajaxProcessGetFlingexOrders()
	{
		// Default response with translation from the module
		$response = array('status' => false, "message" => $this->trans('Nothing here.'));

		if (Tools::isSubmit('action')) {

			switch (Tools::getValue('action')) {

				case 'getFlingexOrders':

					$res = null;
					$time = pSQL(Tools::getValue('time', microtime(true)));

					$order_sql = 'SELECT fo.reference,fo.id_flingex_order,fo.tracking_number,fo.id_order
					FROM '._DB_PREFIX_.'flingex_order fo 
					left JOIN '._DB_PREFIX_.'flingex_order_tracking fot 
					ON (fo.id_flingex_order=fot.id_flingex_order)group by fo.reference';

					$order_query = Db::getInstance()->executeS($order_sql);


					// Edit default response and do some work here
					$response = array('status' => true, "data" => $order_query);

					break;

				default:
					break;
			}
		}

		// Classic json response
		$json = json_encode($response);
		echo $json;
		exit;
	}

	public function ajaxProcessTrackFlingexOrder()
	{
		// Default response with translation from the module
		$response = array('status' => false, "message" => $this->trans('Nothing here.'));

		if (Tools::isSubmit('action')) {

			$flingex_order = Tools::getValue('data');

			$res = null;
			
			$del_sql='DELETE FROM `'._DB_PREFIX_.'flingex_order_tracking`
			where `reference` = "'.$flingex_order['reference'].'"';

			$del_res=Db::getInstance()->execute($del_sql);

			$tracking_response = $this->module->flingex_api::sentOrderToFlingexTrackingApi($flingex_order["tracking_number"]);

			$tracking_response_data = (isset($tracking_response['data']) && isset($tracking_response['data']['trackInfos']) && $tracking_response['data']['trackInfos']) ? $tracking_response['data']['trackInfos'] : [];

			foreach ($tracking_response_data as $key => $value) {
					$sql_tracking = 'INSERT INTO ' . _DB_PREFIX_ . 'flingex_order_tracking
					(`id_order`,`reference`, `id_flingex_order`, `tracking_number`,`parcel_status`,
					`api_response_status`,`api_response_message`)
					values(
					' . (int)$flingex_order['id_order'] . ',
					"' . $flingex_order['reference'] . '",
					' . (int)$flingex_order['id_flingex_order'] . ',
					"' . $flingex_order['tracking_number'] . '",
					"' . $value['parcelStatus'] . '",
					"' . $tracking_response['code']. '",
					"' . $tracking_response['msg'] . '"
					)';
					$res=Db::getInstance()->execute($sql_tracking);
			}


			// Edit default response and do some work here
			$response = array('status' => true, "data" => $res);
		}
		// Classic json response
		$json = json_encode($response);
		echo $json;
		exit;
	}

	// public function hookDisplayBackOfficeHeader()
	// {
	//     $this->context->controller->addCSS($this->getPathUri() . 'views/css/admin.css');
	// }

	// public function hookActionAdminControllerSetMedia($params)
	// {
	//     $this->context->controller->addCss($this->_path.'views/css/admin.css');
	//     $this->context->controller->addJS( $this->_path .'views/js/admin_configure.js' );
	// }
}
