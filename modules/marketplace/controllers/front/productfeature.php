<?php
/**
* 2010-2021 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through LICENSE.txt file inside our module
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright 2010-2021 Webkul IN
* @license LICENSE.txt
*/

class MarketplaceProductFeatureModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (isset($this->context->customer->id)) {
            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            if ($mpSeller && $mpSeller['active'] && Configuration::get('WK_MP_PRESTA_FEATURE_ACCESS')) {
                //Delete feature
                if (Tools::getValue('delete_feature') == '1') {
                    $idFeature = Tools::getValue('id_feature');
                    if ($idFeature != 0) {
                        if (!WkMpProductFeature::ifFeatureAssigned($idFeature)) {
                            $objFeature = new Feature($idFeature);
                            $objFeature->delete();
                            Tools::redirect($this->context->link->getModuleLink('marketplace', 'productfeature', array('success_attr' => 3,)));
                        } else {
                            Tools::redirect($this->context->link->getModuleLink('marketplace', 'productfeature', array('error_attr' => 1,)));
                        }
                    } else {
                        Tools::redirect($this->context->link->getModuleLink('marketplace', 'productfeature', array('error_attr' => 1,)));
                    }
                }

                //Features list
                $featureData = Feature::getFeatures($this->context->language->id);
                $i = 0;
                $featureSet = array();
                foreach ($featureData as $featureDataEach) {
                    $featureSet[$featureDataEach['id_feature']]['id'] = $featureDataEach['id_feature'];
                    $featureSet[$featureDataEach['id_feature']]['name'] = $featureDataEach['name'];
                    $featureSet[$featureDataEach['id_feature']]['values'] = count(FeatureValue::getFeatureValuesWithLang($this->context->language->id, $featureDataEach['id_feature']));

                    if (WkMpProductFeature::ifFeatureAssigned($featureDataEach['id_feature'])) {
                        $featureSet[$featureDataEach['id_feature']]['editable'] = 0;
                    } else {
                        $featureSet[$featureDataEach['id_feature']]['editable'] = $featureDataEach['id_feature'];
                    }

                    ++$i;
                }

                ksort($featureSet);
                $this->context->smarty->assign(array(
                    'wkself' => dirname(__FILE__),
                    'feature_set' => $featureSet,
                    'logic' => 'mp_prod_features',
                ));
                $this->defineJSVars();
                $this->setTemplate('module:marketplace/views/templates/front/product/features/productfeature.tpl');
            } else {
                Tools::redirect(__PS_BASE_URI__.'pagenotfound');
            }
        } else {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }
    }

    public function defineJSVars()
    {
        $jsVars = array(
                'url' => $this->context->link->getModuleLink('marketplace', 'viewfeaturevalue'),
                'error_msg1' => $this->module->l('This feature is already in use you cannot edit or delete it.', 'productfeature'),
                'sure_msg' => $this->module->l('Are you sure want to delete this feature?', 'productfeature'),
                'display_name' => $this->module->l('Display', 'productfeature'),
                'records_name' => $this->module->l('records per page', 'productfeature'),
                'no_product' => $this->module->l('No data found', 'productfeature'),
                'show_page' => $this->module->l('Showing page', 'productfeature'),
                'show_of' => $this->module->l('of', 'productfeature'),
                'no_record' => $this->module->l('No records available', 'productfeature'),
                'filter_from' => $this->module->l('filtered from', 'productfeature'),
                't_record' => $this->module->l('total records', 'productfeature'),
                'search_item' => $this->module->l('Search', 'productfeature'),
                'p_page' => $this->module->l('Previous', 'productfeature'),
                'n_page' => $this->module->l('Next', 'productfeature'),
            );

        Media::addJsDef($jsVars);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'productfeature'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Product Features', 'productfeature'),
            'url' => '',
        );

        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->registerStylesheet('mp-marketplace_account', 'modules/'.$this->module->name.'/views/css/marketplace_account.css');
        $this->registerStylesheet('mp_productfeature-css', 'modules/'.$this->module->name.'/views/css/productfeature.css');

        $this->registerJavascript('mp-productfeature', 'modules/'.$this->module->name.'/views/js/productfeature.js');

        //data table file included
        $this->registerStylesheet('datatable_bootstrap', 'modules/'.$this->module->name.'/views/css/datatable_bootstrap.css');
        $this->registerJavascript('mp-jquery-dataTables', 'modules/'.$this->module->name.'/views/js/jquery.dataTables.min.js');
        $this->registerJavascript('mp-dataTables.bootstrap', 'modules/'.$this->module->name.'/views/js/dataTables.bootstrap.js');
        $this->registerJavascript('wk-mp-dataTables', 'modules/'.$this->module->name.'/views/js/wk_mp_datatables.js');
    }
}
