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

class AdminPaperflyController extends ModuleAdminController
{
	public function __construct()
	{
		// $this->show_toolbar = false;
		// $this->multishop_context = Shop::CONTEXT_SHOP;
		// $this->context = Context::getContext();
		parent::__construct();
	}

	public function initContent()
	{
		parent::initContent();

		$paperfly_orders = Db::getInstance()->executeS(
			'SELECT * FROM '._DB_PREFIX_.'paperfly_order po 
			INNER JOIN '._DB_PREFIX_.'orders odr ON (po.id_order=odr.id_order)
			ORDER BY \'po.date_add\' DESC LIMIT 20            
			'
			// LEFT JOIN '._DB_PREFIX_.'paperfly_order_tracking pot ON (po.id_paperfly_order=pot.id_paperfly_order)group by po.reference'
		);

		foreach ($paperfly_orders as $key=>$order){
			$_SQL = 'SELECT * FROM '._DB_PREFIX_.'paperfly_order_tracking where id_order ='.$order['id_order'];
			$paperfly_orders[$key]['tracking_data'] = Db::getInstance()->executeS($_SQL);
			$ORDER_DETAILS_SQL = 'SELECT * FROM '._DB_PREFIX_.'order_detail where id_order ='.$order['id_order'];
			$paperfly_orders[$key]['products'] = Db::getInstance()->executeS($ORDER_DETAILS_SQL);
		}

		$this->context->smarty->assign([
			'manifestDate' => Tools::getValue('manifestDate', date('Y-m-d')),
			'orders' => $paperfly_orders,
		]);

		$this->setTemplate('paperfly_track.tpl');
	}
	public function init(){
		parent::init();
		$this->bootstrap = true;
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
		if( Tools::getValue('controller') == 'AdminPaperfly' ){

			// Create a link with the good path
			$link = new Link;
			$parameters = array("action" => "action_name");
			$ajax_link = $link->getModuleLink($this->module->name,'ajax.php', $parameters);

			Media::addJsDef(array(
				'paperfly_ajax' => $this->context->link->getAdminLink('AdminPaperfly'),
				'paperfly_ajax1' => $ajax_link,
				'paperfly_ajax2' => _PS_BASE_URL_.__PS_BASE_URI__.'moduels/'.$this->module->name.'/ajax.php',
			));
			$this->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/track.js');
			// $this->addCSS([_MODULE_DIR_ . $this->module->name . '/views/css/admin.css', _MODULE_DIR_ . $this->module->name . '/views/css/private.css']);
		}
	}


	public function ajaxProcessGetPaperflyOrders()
	{
		// Default response with translation from the module
		$response = array('status' => false, "message" => $this->trans('Nothing here.'));
		
		if (Tools::isSubmit('action')) {

			switch (Tools::getValue('action')) {

				case 'getPaperflyOrders':
								
					$res = null;
					$time = pSQL(Tools::getValue('time', microtime(true)));

					$order_sql = 'SELECT po.reference,po.id_paperfly_order,po.tracking_number,po.id_order
					FROM '._DB_PREFIX_.'paperfly_order po 
					left JOIN '._DB_PREFIX_.'paperfly_order_tracking pot 
					ON (po.id_paperfly_order=pot.id_paperfly_order)group by po.reference';

					$order_query = Db::getInstance()->executeS($order_sql);


					// Edit default response and do some work here
					$response = array('status' => true, "data" => $order_query );
					
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

	public function ajaxProcessTrackPaperflyOrder()
	{
		// Default response with translation from the module
		$response = array('status' => false, "message" => $this->trans('Nothing here.'));
		
		if (Tools::isSubmit('action')) {

			$paperfly_order = Tools::getValue('data' );
	
			$res = null;
			$del_sql='DELETE FROM `'._DB_PREFIX_.'paperfly_order_tracking`
			where `reference` = "'.$paperfly_order['reference'].'"';
			$del_res=Db::getInstance()->execute($del_sql);

			$tracking_api_response = $this->module->paperfly_api::paperflyOrderTrackingApiCronProcess($paperfly_order['reference']);
			$tracking_response_data = [];
			if( $tracking_api_response['response_code'] == '200') {
			if(isset( $tracking_api_response['success']['trackingStatus'] ))
				$tracking_response_data = $tracking_api_response['success']['trackingStatus'][0];
			}
			$tracking_api_response_message = ($tracking_api_response['response_code'] == '200') ? 
						$tracking_api_response['success']['message']
						: $tracking_api_response['error']['message'];

			foreach ((array)$tracking_response_data as $key => $value) {
				$sql_tracking = 'INSERT INTO ' . _DB_PREFIX_ . 'paperfly_order_tracking
				(`id_order`,`reference`, `id_paperfly_order`, `tracking_number`,`tracking_event_key`,`tracking_event_value`,
				`api_response_status_code`,`api_response_status_message`)
				values(
				' . (int)$paperfly_order['id_order'] . ',
				"' . $paperfly_order['reference'] . '",
				' . $paperfly_order['id_paperfly_order'] . ',
				"' . $paperfly_order['tracking_number']  . '",
				"' . $key . '",
				"' . $value . '",
				"' . $tracking_api_response['response_code'] . '",
				"' . $tracking_api_response_message . '"
				)';
				$res=Db::getInstance()->execute($sql_tracking);
			}


			// Edit default response and do some work here
			$response = array('status' => true, "data" => $res );
					
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
