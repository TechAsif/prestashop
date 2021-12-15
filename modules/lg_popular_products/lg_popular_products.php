<?php

/**
 * Lets Go Custom Brands
 *
 * @author    Letsgo <info@letsgobd.com>
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

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class lg_popular_products extends Module implements WidgetInterface
{

	public function __construct()
	{
		$this->name = 'lg_popular_products';
		$this->tab = 'front_office_features';
		$this->version = '0.0.1';
		$this->author = 'Asif Parvez Sarker';
		//    $this->module_key = '96d5521c4c1259e8e87786597735aa4e';
		$this->need_instance = 0;

		$this->bootstrap = true;

		$this->displayName = $this->l('Lets Go Popular Products');
		$this->description = $this->l('Selected Popular Product Manuallay');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

		parent::__construct();
	}

	public function install()
	{
		return parent::install();
	}

	public function uninstall()
	{
		return parent::uninstall();
	}


	public function reset()
	{
		$return = true;
		return (bool)$return;
	}



	public function getPopularProducts()
	{

		$product_ids = "5935, 5933, 5928, 5916, 5912, 5910, 5900";


		$sql = 'SELECT p.*, pl.*, image_shop.*, il.*, m.`name` AS manufacturer_name, s.`name` AS supplier_name
				FROM `' . _DB_PREFIX_ . 'product` p
				LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product`)
				LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
				LEFT JOIN `' . _DB_PREFIX_ . 'supplier` s ON (s.`id_supplier` = p.`id_supplier`)' . '' . '
				LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop  ON (p.`id_product` = image_shop.`id_product`)
				LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (image_shop.`id_image` = il.`id_image`)
				WHERE  p.`active` = 1  AND p.id_product IN('.$product_ids.') GROUP by p.id_product LIMIT 10';

		$rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

		foreach ($rq as $key => $product) {
			$link = Context::getContext()->link->getProductLink($product['id_product']);
			$rq[$key]["link"] = $link;
		}

		return ($rq);
	}


	/**
	 * @inheritdoc
	 * @param string $hookName
	 * @param array $configuration
	 * @return string
	 */
	public function renderWidget($hookName = null, array $configuration = [])
	{
		if (!$this->active) {
			return;
		}
		$this->smarty->assign($this->getWidgetVariables($hookName, $configuration));

		return $this->display(__FILE__, 'views/templates/hook/custom_popular_products.tpl');
	
	}


	/**
	 * @param string|null $hookName
	 * @param array $configuration
	 * @return array
	 * @throws Exception
	 */
	public function getWidgetVariables($hookName = null, array $configuration = [])
	{
		return array(
			"searchResults" => $this->getPopularProducts(),
			'link' => Context::getContext()->link
		);
	}
}
