<?php


if (!defined('_PS_VERSION_')) {
    exit;
}
include_once (_PS_MODULE_DIR_.'/customapi/classes/OrderDetailed.php');
include_once (_PS_MODULE_DIR_.'/customapi/classes/CategoryDetailed.php');
include_once (_PS_MODULE_DIR_.'/customapi/classes/UpdateStatus.php');

class customapi extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'customapi';
        $this->tab = 'others';
        $this->version = '1.0.0';
        $this->author = 'rokan';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Custom Web Service Api');
        $this->description = $this->l('This module help to find all custom api');

        $this->confirmUninstall = $this->l('Aue you want to uninstall this module');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function hookAddWebserviceResources($resources)
    {
        $added_resources['orderdetailed'] = [
            'description' => 'Export Detailed Orders',
            'class' => 'OrderDetailed'
        ];

        $added_resources['order_status_update'] = [
            'description' => 'Update Order Status',
            'class' => 'UpdateStatus'
        ];

        $added_resources['custom_category_detailed'] = [
            'description' => 'Custom categories information',
            'class' => 'CategoryDetailed'
        ];


        return $added_resources;
    }

    public function install()
    {
        if (!parent::install() || !$this->registerHook('addWebserviceResources')) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }


}
