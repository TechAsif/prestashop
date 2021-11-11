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

class MarketplaceViewAttributeGroupValueModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (isset($this->context->customer->id)) {
            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            if ($mpSeller['active'] && Configuration::get('WK_MP_PRESTA_ATTRIBUTE_ACCESS')) {
                $idGroup = Tools::getValue('id_group');

                // Delete Attribute Value
                if (Tools::getValue('delete_attribute_value')) {
                    $deleteSuccess = 0;
                    if ($idAttribute = Tools::getValue('id_attribute')) {
                        if (!WkMpAttributeImpact::checkCombinationByAttribute($idAttribute)) {
                            $objAttribute = new Attribute($idAttribute);
                            if ($objAttribute->delete()) {
                                //code for texture image
                                $deletePath = _PS_IMG_DIR_.'co/'.$idAttribute.'.jpg';
                                if (file_exists($deletePath)) {
                                    unlink($deletePath);
                                }
                                $deleteSuccess =1;
                            }

                            Tools::redirect($this->context->link->getModuleLink('marketplace', 'viewattributegroupvalue', array('id_group' => $idGroup, 'deleted' => 1)));
                        }
                    }

                    if (!$deleteSuccess) {
                        $this->errors[] = $this->module->l('This Attribute value is already in use you cannot edit or delete it.', 'productattribute');
                    }
                }

                //Attrbute Value List
                $groupAttribute = AttributeGroup::getAttributes($this->context->language->id, $idGroup);
                if ($groupAttribute) {
                    $i = 0;
                    $valueSet = array();
                    foreach ($groupAttribute as $groupAttributeEach) {
                        $valueSet[$i]['id'] = $groupAttributeEach['id_attribute'];
                        $valueSet[$i]['name'] = $groupAttributeEach['name'];
                        if (WkMpAttributeImpact::ifColorAttributegroup($idGroup)) {
                            /* code for color texture */
                            $valueSet[$i]['color'] = $groupAttributeEach['color'];
                            $valueSet[$i]['imageTextureExists'] = file_exists(_PS_IMG_DIR_.'co/'.$groupAttributeEach['id_attribute'].'.jpg');
                        }
                        if (WkMpAttributeImpact::checkCombination($groupAttributeEach['id_attribute'])) {
                            $valueSet[$i]['editable'] = 0;
                        } else {
                            $valueSet[$i]['editable'] = $groupAttributeEach['id_attribute'];
                        }
                        ++$i;
                    }

                    $this->context->smarty->assign('value_set', $valueSet);
                }

                if (WkMpAttributeImpact::ifColorAttributegroup($idGroup)) {
                    $this->context->smarty->assign('is_color', 1);
                }

                $this->context->smarty->assign(array(
                        'id_group' => $idGroup,
                        'logic' => 'mp_prod_attribute',
                        'img_col_dir' => _THEME_COL_DIR_,
                    ));
                $this->defineJSVars();
                $this->setTemplate('module:marketplace/views/templates/front/product/combination/viewattributegroupvalue.tpl');
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
                'error_msg2' => $this->module->l('This Attribute is already in use you cannot edit or delete it.', 'viewattributegroupvalue'),
                'confirm_delete' => $this->module->l('Are you sure?', 'viewattributegroupvalue'),
                'display_name' => $this->module->l('Display', 'viewattributegroupvalue'),
                'records_name' => $this->module->l('records per page', 'viewattributegroupvalue'),
                'no_product' => $this->module->l('No data found', 'viewattributegroupvalue'),
                'show_page' => $this->module->l('Showing page', 'viewattributegroupvalue'),
                'show_of' => $this->module->l('of', 'viewattributegroupvalue'),
                'no_record' => $this->module->l('No records available', 'viewattributegroupvalue'),
                'filter_from' => $this->module->l('filtered from', 'viewattributegroupvalue'),
                't_record' => $this->module->l('total records', 'viewattributegroupvalue'),
                'search_item' => $this->module->l('Search', 'viewattributegroupvalue'),
                'p_page' => $this->module->l('Previous', 'viewattributegroupvalue'),
                'n_page' => $this->module->l('Next', 'viewattributegroupvalue'),
            );

        Media::addJsDef($jsVars);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'viewattributegroupvalue'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        );
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('View Attribute Value', 'viewattributegroupvalue'),
            'url' => '',
        );
        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->registerStylesheet('mp-marketplace_account', 'modules/'.$this->module->name.'/views/css/marketplace_account.css');
        $this->registerJavascript('mp-productattribute', 'modules/'.$this->module->name.'/views/js/productattribute.js');

        //data table file included
        $this->registerStylesheet('datatable_bootstrap', 'modules/'.$this->module->name.'/views/css/datatable_bootstrap.css');
        $this->registerJavascript('mp-jquery-dataTables', 'modules/'.$this->module->name.'/views/js/jquery.dataTables.min.js');
        $this->registerJavascript('mp-dataTables.bootstrap', 'modules/'.$this->module->name.'/views/js/dataTables.bootstrap.js');
        $this->registerJavascript('wk-mp-dataTables', 'modules/'.$this->module->name.'/views/js/wk_mp_datatables.js');
    }
}
