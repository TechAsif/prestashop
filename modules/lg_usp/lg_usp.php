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

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class Lg_Usp extends Module implements WidgetInterface
{
	public function __construct()
	{
		$this->name = 'lg_usp';
		$this->version = '0.0.1';
		$this->author = 'Bozlur Rahman';
		//    $this->module_key = '96d5521c4c1259e8e87786597735aa4e';
		$this->need_instance = 0;
		$this->tab = 'front_office_features';

		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Let\'s Go USP');
		$this->description = $this->l('Let\'s Go module represent a text slider');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    $this->ps_versions_compliancy = array('min' => '1.7.2.0', 'max' => _PS_VERSION_);
	}

	public function install()
	{
		$return = true;
		$return &= parent::install();
		$return &= $this->createHook('displayUSP');
		$return &= $this->registerHook('displayUSP');
		$return &= $this->registerHook('displayHeader');

		return (bool)$return;
	}

	public function uninstall()
	{
		$return = true;
		$return &= $this->removeHook('displayUSP');
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

	public function hookDisplayHeader($params)
	{
		$this->context->controller->addJquery();
		$this->context->controller->addJS($this->_path . 'views/js/jquery.lettering.min.js');
		$this->context->controller->addJS($this->_path . 'views/js/jquery.textillate.min.js');
		$this->context->controller->addJS($this->_path . 'views/js/scripts.js');

		$this->context->controller->addCSS($this->_path . 'views/css/animate.min.css');
		$this->context->controller->addCSS($this->_path . 'views/css/styles.css');
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

		return $this->display(__FILE__, 'views/templates/widget/usp.tpl');
	}


	/**
	 * @param string|null $hookName
	 * @param array $configuration
	 * @return array
	 * @throws Exception
	 */
	public function getWidgetVariables($hookName = null, array $configuration = [])
	{
		return array();
	}
}
