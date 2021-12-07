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

class AdminECourierController extends ModuleAdminController
{
	public function __construct()
	{
		$this->table = '';
		$this->bootstrap = true;
		$this->show_toolbar = false;
		$this->multishop_context = Shop::CONTEXT_SHOP;
		$this->context = Context::getContext();

		parent::__construct();
	}

	public function initContent()
	{
		parent::initContent();
		$ecourier_orders = Db::getInstance()->executeS(
			'SELECT * FROM ' . _DB_PREFIX_ . 'ecourier_order po 
            INNER JOIN ' . _DB_PREFIX_ . 'orders odr ON (po.id_order=odr.id_order)
            ORDER BY \'po.date_add\' DESC LIMIT 20
            '
			// INNER JOIN '._DB_PREFIX_.'order_detail od ON (po.id_order=od.id_order)
			// LEFT JOIN '._DB_PREFIX_.'ecourier_order_tracking pot ON (po.id_ecourier_order=pot.id_ecourier_order)group by po.reference'
		);
		// var_dump($ecourier_orders);

		foreach ($ecourier_orders as $key => $order) {
			$_SQL = 'SELECT * FROM ' . _DB_PREFIX_ . 'ecourier_order_tracking where id_order =' . $order['id_order'];
			$ecourier_orders[$key]['tracking_data'] = Db::getInstance()->executeS($_SQL);
			$ORDER_DETAILS_SQL = 'SELECT * FROM ' . _DB_PREFIX_ . 'order_detail where id_order =' . $order['id_order'];
			$ecourier_orders[$key]['products'] = Db::getInstance()->executeS($ORDER_DETAILS_SQL);
		}

		$this->context->smarty->assign([
			'manifestDate' => Tools::getValue('manifestDate', date('Y-m-d')),
			'orders' => $ecourier_orders,
		]);
		$this->setTemplate('track.tpl');
	}

	public function postProcess()
	{
		parent::postProcess();
	}

	public function initProcess()
	{
		parent::initProcess();
	}

	public function setMedia($isNewTheme = false)
	{
		parent::setMedia($isNewTheme);
		if (Tools::getValue('controller') == 'AdminECourier') {

			// Create a link with the good path
			$link = new Link;
			$parameters = array("action" => "action_name");
			$ajax_link = $link->getModuleLink($this->module->name, 'ajax.php', $parameters);

			Media::addJsDef(array(
				'ecourier_ajax' => $this->context->link->getAdminLink('AdminECourier'),
				'ecourier_ajax1' => $ajax_link,
				'ecourier_ajax2' => _PS_BASE_URL_ . __PS_BASE_URI__ . 'moduels/' . $this->module->name . '/ajax.php',
			));
			$this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/track.js');
			// $this->addCSS([_MODULE_DIR_ . $this->module->name . '/views/css/admin.css', _MODULE_DIR_ . $this->module->name . '/views/css/private.css']);
		}
	}


	public function ajaxProcessGetECourierOrders()
	{
		// Default response with translation from the module
		$response = array('status' => false, "message" => $this->trans('Nothing here.'));

		// $address= Db::getInstance()->executeS(
		// 	'SELECT *
		// 	FROM `'._DB_PREFIX_.'address`
		// 	WHERE id_address =18'
		// );

		// $bestPackage = $this->module->ecourier_api->getBestPackage($address);


		if (Tools::isSubmit('action')) {

			switch (Tools::getValue('action')) {

				case 'getECourierOrders':

					$res = null;
					$time = pSQL(Tools::getValue('time', microtime(true)));

					$order_sql = 'SELECT fo.reference,fo.id_ecourier_order,fo.tracking_number,fo.id_order
					FROM '._DB_PREFIX_.'ecourier_order fo 
					left JOIN '._DB_PREFIX_.'ecourier_order_tracking fot 
					ON (fo.id_ecourier_order=fot.id_ecourier_order)group by fo.reference';

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

	public function ajaxProcessTrackECourierOrder()
	{
		// Default response with translation from the module
		$response = array('status' => false, "message" => $this->trans('Nothing here.'));

		if (Tools::isSubmit('action')) {

			$ecourier_order = Tools::getValue('data');

			$res = null;

			$del_sql = 'DELETE FROM `' . _DB_PREFIX_ . 'ecourier_order_tracking`
			where `reference` = "' . $ecourier_order['reference'] . '"';

			$del_res = Db::getInstance()->execute($del_sql);

			$tracking_response = $this->module->ecourier_api->sentOrderToECourierTrackingApi($ecourier_order["tracking_number"]);

			$tracking_response_data = (isset($tracking_response["query_data"]) && $tracking_response["query_data"]) ? (isset($tracking_response["query_data"]["status"]) ? $tracking_response["query_data"]["status"] : $tracking_response["query_data"]): [];

			$tracking_response_status = (isset($tracking_response["response_code"]) && $tracking_response["response_code"]) ? $tracking_response["response_code"] : ( isset($tracking_response["status"]) ? $tracking_response["status"] : '');
	

			foreach ((array)$tracking_response_data as $key => $value) {
				$value = isset($value['status'])? $value['status'] : (is_array($value) ? json_encode($value) : (string)$value );

				$sql_tracking = 'INSERT INTO ' . _DB_PREFIX_ . 'ecourier_order_tracking
					(`id_order`,`reference`, `id_ecourier_order`, `tracking_number`,`parcel_status`,
					`api_response_status`,`api_response_message`)
					values(
					' . (int)$ecourier_order['id_order'] . ',
					"' . $ecourier_order['reference'] . '",
					' . (int)$ecourier_order['id_ecourier_order'] . ',
					"' . $ecourier_order['tracking_number'] . '",
					"' . $value . '",
					"' . (string)$tracking_response_status . '",
					""
					)';
				$res = Db::getInstance()->execute($sql_tracking);
			}


			// Edit default response and do some work here
			$response = array('status' => true, "data" => $res);
		}
		// Classic json response
		$json = json_encode($response);
		echo $json;
		exit;
	}
}
