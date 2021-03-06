<?php
/*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class Lgmegamenu extends Module implements WidgetInterface
{


    public function __construct()
    {
        $this->name = 'lgmegamenu';
        $this->author = 'LetsGo Mart';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('LG Mega Menu');
        $this->description = $this->l(
            'Adds Mega Menu in header'
        );
        $this->ps_versions_compliancy = array('min' => '1.7.2.0', 'max' => _PS_VERSION_);
    }

    /**
     * @return bool
     */
    public function install()
    {
        return parent::install() &&
        $this->registerHook('header') && $this->createHook('displayLgMegaMenu','LG Static MegaMenu');
    }


    public function uninstall()
    {
        return parent::uninstall();
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

        return $this->display(__FILE__, 'views/templates/widget/lgmegamenu.tpl');
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


        );
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


    public function hookHeader()
    {
        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->_path.'views/js/jquery.smartmenus.js');
        $this->context->controller->addJS($this->_path.'views/js/script.js');

        $this->context->controller->addCSS($this->_path.'views/css/sm-core-css.css');
        $this->context->controller->addCSS($this->_path.'views/css/sm-simple.css');
        $this->context->controller->addCSS($this->_path.'views/css/style.css');
    }
    

    
}
