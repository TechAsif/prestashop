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

class AdminCustomBrandController extends ModuleAdminController
{
	public function __construct()
	{
		// $this->table = '';
		$this->show_toolbar = false;
		$this->multishop_context = Shop::CONTEXT_SHOP;
		$this->context = Context::getContext();

		parent::__construct();
	}
	
	public function init()
	{
		parent::init();
		$this->bootstrap = true;
	}

	public function initContent()
	{
		parent::initContent();

		$this->context->smarty->assign($this->module->getCustomBrands());
		$this->setTemplate('config.tpl');
	}

	public function initProcess()
	{
		parent::initProcess();
	}

	public function postProcess()
	{
		parent::postProcess();
	}



	public function ajaxProcessSetCustomBrands()
	{
		$message = $this->trans('Brands are not updated.');
		$response = array('status' => false, "message" => $message);

		if (Tools::isSubmit('action')) {

			$submitted_brands = json_encode(Tools::getValue('data'));
			$brandKey = Tools::getValue('brandType');
			
			$order_query = Configuration::updateValue($brandKey, $submitted_brands);
			$message = ($order_query) ? $this->trans('Brands are updated.'): $message;
			$response = array('status' => true, "message" =>  $message );

		}

		// Classic json response
		$json = json_encode($response);
		echo $json;
		exit;
	}

}
