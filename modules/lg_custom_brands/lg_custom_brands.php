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

class Lg_Custom_Brands extends Module implements WidgetInterface
{
	public static $conf_prefix = 'LG_BRANDS_';

	public function __construct()
	{
		$this->name = 'lg_custom_brands';
		$this->tab = 'front_office_features';
		$this->version = '0.0.1';
		$this->author = 'Bozlur Rahman';
		//    $this->module_key = '96d5521c4c1259e8e87786597735aa4e';
		$this->need_instance = 0;

		$this->bootstrap = true;

		$this->displayName = $this->l('Lets Go Custom Brands');
		$this->description = $this->l('Selected custom branch to be display in home page');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

		parent::__construct();
	}

	public function install()
	{
		$return = true;
		$return &= parent::install();
		$return &= $this->installTab('AdminCustomBrand', 'Custom Brands', 'AdminCatalog', true);
		// $return &= $this->registerHook('displayHeader');
		$return &= $this->registerHook('displayBackOfficeHeader');
		return (bool)$return;
	}

	public function uninstall()
	{
		$return = true;
		$return &= parent::uninstall();
		$return &= $this->uninstallTab('AdminCustomBrand');
		return (bool)$return;
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

			if($parent == 'hidden') {
				$tab->id_parent = -1;
			} elseif ($parent) {
				$tab->id_parent = (int)Tab::getIdFromClassName($parent);
			} else {
				$tab->id_parent = 0;
			}
			
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


	public function reset()
	{
		$return = true;
		return (bool)$return;
	}

	public function getCustomBrands()
	{
		$manuddddList = Manufacturer::getManufacturers();
		$manuList = Manufacturer::getLiteManufacturersList();
		$custom_brand_ids = json_decode(Configuration::get('LG_CUSTOM_BRAND_IDS'));
		$images_types = ImageType::getImagesTypes('manufacturers');

		// $image = _PS_MANU_IMG_DIR_.$manufacturer->id.'.jpg';
		// $image_url = ImageManager::thumbnail($image, $table.'_'.(int)$manufacturer->id.'.'.$this->imageType, 350,
		// 		$this->imageType, true, true);
		// $image_size = file_exists($image) ? filesize($image) / 1000 : false;


		$custom_brands = [];
		$this->imageType = 'jpg';
		$table = 'manufacturer';
		foreach ($custom_brand_ids as $custom_brand_id) {
			$tempbrand = array_filter($manuList, function($v, $k) use($custom_brand_id) {
				return $v['id'] == $custom_brand_id;
			}, ARRAY_FILTER_USE_BOTH);

			foreach ($tempbrand as $key => $brand) {
				unset($manuList[$key]);
				
				// $current_logo_file = _PS_TMP_IMG_DIR_.'manufacturer_mini_'.$brand['id'].'_'.$this->context->shop->id.'.jpg';
						
				$image = _PS_MANU_IMG_DIR_.$brand['id'].'.jpg';
				$cacheImage = $table.'_'.(int)$brand['id'].'.'.$this->imageType;
				$image_url = ImageManager::thumbnail($image, $cacheImage , 350,$this->imageType, true, true);
				// $image_size = file_exists($image) ? filesize($image) / 1000 : false;

				$brand['image'] = $image_url;
				$brand['image_url'] = ImageManager::getThumbnailPath($cacheImage, false);
				$custom_brands[] = $brand;
			}
		}
		// var_dump($custom_brands);

		return [
			'manufacturers' => $manuList,
			'custom_brands' => $custom_brands,
		];
	}


	public function getContent()
	{
		$this->smarty->assign( $this->getCustomBrands() );
		return $this->display(__FILE__, 'views/templates/admin/custom_brand/config.tpl');
	}


	public function hookDisplayBackOfficeHeader()
	{
		if (
			(Tools::getValue('controller') != 'AdminCustomBrand') &&
			(Tools::getValue('controller') != 'AdminModules' && 
			Tools::getValue('configure') != $this->name)) {
				return;
		}

		Media::addJsDef(array(
			'custom_brand_ajax' => $this->context->link->getAdminLink('AdminCustomBrand'),
		));
		$this->context->controller->addJquery();
		$this->context->controller->addJqueryUI('ui.sortable');
		$this->context->controller->addJS($this->_path . 'views/js/jquery.simply-toast.js');
		$this->context->controller->addJS($this->_path . 'views/js/admin-brand-config.js');
		$this->context->controller->addCSS($this->_path . 'views/css/admin-brand-config.css');

		// $this->context->controller->registerJavascript('TweenMax', 'modules/' . $this->name . '/views/js/TweenMax.min.js', array('position' => 'bottom', 'priority' => 100));
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

		return $this->display(__FILE__, 'views/templates/hook/custom_brands.tpl');
	}


	/**
	 * @param string|null $hookName
	 * @param array $configuration
	 * @return array
	 * @throws Exception
	 */
	public function getWidgetVariables($hookName = null, array $configuration = [])
	{
		return $this->getCustomBrands();
	}
}
