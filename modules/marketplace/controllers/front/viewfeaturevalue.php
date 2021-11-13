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

class MarketplaceViewFeatureValueModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (isset($this->context->customer->id)) {
            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            if ($mpSeller && Configuration::get('WK_MP_PRESTA_FEATURE_ACCESS')) {
                //Delete feature
                if (Tools::getValue('delete_feature_val') == '1') {
                    $idFeatureValue = Tools::getValue('id_feature_value');
                    if ($idFeatureValue) {
                        if (!WkMpProductFeature::ifFeatureValueAssigned($idFeatureValue)) {
                            $objFeatureValue = new FeatureValue($idFeatureValue);
                            $objFeatureValue->delete();
                            Tools::redirect($this->context->link->getModuleLink('marketplace', 'viewfeaturevalue', array('id_feature' => Tools::getValue('id_feature'),'success_attr' => 3, )));
                        } else {
                            Tools::redirect($this->context->link->getModuleLink('marketplace', 'viewfeaturevalue', array('id_feature' => Tools::getValue('id_feature'),'error_attr' => 1, )));
                        }
                    } else {
                        Tools::redirect($this->context->link->getModuleLink('marketplace', 'viewfeaturevalue', array('id_feature' => Tools::getValue('id_feature'),'error_attr' => 1, )));
                    }
                }

                $idFeature = Tools::getValue('id_feature');
                $featureData = Feature::getFeature($this->context->language->id, $idFeature);
                if ($featureData) {
                    $this->context->smarty->assign('feature_name', $featureData['name']);
                }
                $idFeatureValueSet = FeatureValue::getFeatureValuesWithLang($this->context->language->id, $idFeature);
                // IF any value present for the feature  or not
                if (count($idFeatureValueSet) > 0) {
                    $valueSet = array();
                    $i = 0;
                    foreach ($idFeatureValueSet as $idFeatureValueSetEach) {
                        $valueSet[$idFeatureValueSetEach['id_feature_value']]['id'] = $idFeatureValueSetEach['id_feature_value'];
                        $valueSet[$idFeatureValueSetEach['id_feature_value']]['val_name'] = $idFeatureValueSetEach['value'];
                        if (WkMpProductFeature::ifFeatureValueAssigned($idFeatureValueSetEach['id_feature_value'])) {
                            $valueSet[$idFeatureValueSetEach['id_feature_value']]['editable'] = 0;
                        } else {
                            $valueSet[$idFeatureValueSetEach['id_feature_value']]['editable'] = $idFeatureValueSetEach['id_feature_value'];
                        }

                        ++$i;
                    }
                    ksort($valueSet);

                    $this->context->smarty->assign('value_set', $valueSet);
                } else {
                    $this->context->smarty->assign('empty_list', 1);
                }

                $this->context->smarty->assign(array(
                    'id_feature' => $idFeature,
                    'wkself' => dirname(__FILE__),
                    'logic' => 'mp_prod_features',
                ));
                $this->defineJSVars();
                $this->setTemplate('module:marketplace/views/templates/front/product/features/viewfeaturevalue.tpl');
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
                'error_msg_v' => $this->module->l('This feature value is already in use you cannot edit or delete it.', 'viewfeaturevalue'),
                'sure_msg_v' => $this->module->l('Are you sure want to delete this feature value?', 'viewfeaturevalue'),
                'display_name' => $this->module->l('Display', 'productfeature'),
                'records_name' => $this->module->l('records per page', 'viewfeaturevalue'),
                'no_product' => $this->module->l('No data found', 'viewfeaturevalue'),
                'show_page' => $this->module->l('Showing page', 'viewfeaturevalue'),
                'show_of' => $this->module->l('of', 'viewfeaturevalue'),
                'no_record' => $this->module->l('No records available', 'viewfeaturevalue'),
                'filter_from' => $this->module->l('filtered from', 'viewfeaturevalue'),
                't_record' => $this->module->l('total records', 'viewfeaturevalue'),
                'search_item' => $this->module->l('Search', 'viewfeaturevalue'),
                'p_page' => $this->module->l('Previous', 'viewfeaturevalue'),
                'n_page' => $this->module->l('Next', 'viewfeaturevalue'),
            );

        Media::addJsDef($jsVars);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'viewfeaturevalue'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('View Feature Value', 'viewfeaturevalue'),
            'url' => '',
        );

        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();
        Media::addJsDef(
            array('friendly_url' => Configuration::get('PS_REWRITING_SETTINGS'))
        );

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
