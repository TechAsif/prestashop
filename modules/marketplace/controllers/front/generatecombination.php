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

class MarketplaceGenerateCombinationModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if ($this->context->customer->id) {
            $idCustomer = $this->context->customer->id;
            //Override customer id if any staff of seller want to use this controller
            if (Module::isEnabled('mpsellerstaff')) {
                $getCustomerId = WkMpSellerStaff::overrideMpSellerCustomerId($idCustomer);
                if ($getCustomerId) {
                    $idCustomer = $getCustomerId;
                }
            }

            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
            if ($mpSeller && $mpSeller['active'] && Configuration::get('WK_MP_SELLER_PRODUCT_COMBINATION')) {
                $mpIdProduct = Tools::getValue('id_mp_product');
                $sellerProduct = WkMpSellerProduct::getSellerProductByIdProduct($mpIdProduct, $this->context->language->id);
                if ($sellerProduct && ($sellerProduct['id_seller'] == $mpSeller['id_seller'])) {
                    WkMpAttributeImpact::assignAttributeValues();
                    $this->context->smarty->assign(array(
                        'wkself' => dirname(__FILE__),
                        'logic' => 3,
                        'attribute_js' => $this->displayAndReturnAttributeJs(),
                    ));

                    $jsVars = array(
                        'i18n_tax_exc' => $this->module->l('Tax Excluded', 'generatecombination'),
                        'i18n_tax_inc' => $this->module->l('Tax Included', 'generatecombination'),
                        //'product_tax' => $tax_rates,
                    );
                    Media::addJsDef($jsVars);

                    $this->setTemplate('module:marketplace/views/templates/front/product/combination/generatecombination.tpl');
                } else {
                    Tools::redirect($this->context->link->getModuleLink('marketplace', 'dashboard'));
                }
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
            }
        } else {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }
    }

    protected static function displayAndReturnAttributeJs()
    {
        $attributes = Attribute::getAttributes(Context::getContext()->language->id, true);
        $attributeJs = array();
        foreach ($attributes as $attribute) {
            $attributeJs[$attribute['id_attribute_group']][$attribute['id_attribute']] = $attribute['name'];
        }

        return $attributeJs;
    }

    public function postProcess()
    {
        if (Tools::isSubmit('GenerateCombination')) {
            $this->id_mp_product = Tools::getValue('id_mp_product');
            if (!is_array(Tools::getValue('options'))) {
                $extra = array('msg' => 1, 'id_mp_product' => $this->id_mp_product);
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'generatecombination', $extra));
            } else {
                if (!Validate::isInt(Tools::getValue('quantity'))) {
                    $this->errors[] = $this->module->l('Quantity should be valid.', 'generatecombination');
                }
                if (Tools::getValue('reference') && !Validate::isReference(Tools::getValue('reference'))) {
                    $this->errors[] = $this->module->l('Reference is not valid.', 'generatecombination');
                }

                if (!count($this->errors)) {
                    $objSellerProduct = new WkMpSellerProduct($this->id_mp_product);
                    $idPsProduct = $objSellerProduct->id_ps_product;
                    $tab = array_values(Tools::getValue('options'));
                    if (count($tab) && Validate::isLoadedObject($objSellerProduct)) {
                        //Combination Attribute list
                        $this->combinations = array_values($this->createCombinations($tab));
                        //Combination Values
                        $productAttribute = array_values(array_map(array($this, 'addAttribute'), $this->combinations));

                        if ($productAttribute
                        && $this->combinations
                        && (count($productAttribute) == count($this->combinations))
                        ) {
                            foreach ($productAttribute as $attrValue) {
                                if (!Validate::isNegativePrice($attrValue['mp_price'])) {
                                    $this->errors[] = $this->module->l(
                                        'Impact price must be valid.',
                                        'generatecombination'
                                    );
                                }
                                if (!Validate::isFloat($attrValue['mp_weight'])) {
                                    $this->errors[] = $this->module->l(
                                        'Impact on weight must be valid.',
                                        'generatecombination'
                                    );
                                }
                            }
                        }

                        if (!count($this->errors)) {
                            //Delete all combination before generating combinations
                            $objSellerProduct->deleteCombinationAssociations($this->id_mp_product);
                            if ($idPsProduct && $objSellerProduct->active) {
                                $objProduct = new Product($idPsProduct);
                                $objProduct->deleteProductAttributes();
                            }


                            if ($productAttribute
                                && $this->combinations
                                && (count($productAttribute) == count($this->combinations))) {
                                foreach ($productAttribute as $attributeKey => $attributeValue) {
                                    $idMpProductAttribute = 0; //Because we are creating
                                    $idImages = array();

                                    WkMpProductAttribute::saveMpProductCombination(
                                        $this->id_mp_product,
                                        $idMpProductAttribute,
                                        $this->combinations[$attributeKey],
                                        $attributeValue['mp_reference'],
                                        '',
                                        '',
                                        '',
                                        $attributeValue['mp_price'],
                                        0,
                                        0,
                                        $attributeValue['mp_quantity'],
                                        $attributeValue['mp_weight'],
                                        1,
                                        $attributeValue['mp_available_date'],
                                        $idImages
                                    );
                                }

                                WkMpAttributeImpact::setAttributesImpacts($this->id_mp_product, $tab);
                                if ($idPsProduct) {
                                    WkMpAttributeImpact::setAttributesImpacts($idPsProduct, $tab, 1);
                                }
                            }

                            //To manage staff log (changes add/update/delete)
                            WkMpHelper::setStaffHook($this->context->customer->id, Tools::getValue('controller'), $this->id_mp_product, 2); // 2 for Update action

                            $param = array('id_mp_product' => $this->id_mp_product, 'edited_conf' => 1, 'tab' => 'wk-combination');
                            Tools::redirect($this->context->link->getModuleLink('marketplace', 'updateproduct', $param));
                        }
                    } else {
                        $extra = array('msg' => 2, 'id_mp_product' => $this->id_mp_product);
                        Tools::redirect($this->context->link->getModuleLink('marketplace', 'generatecombination', $extra));
                    }
                }
            }
        }
    }

    public function addAttribute($attributes, $price = 0, $weight = 0)
    {
        foreach ($attributes as $attribute) {
            $price += (float) preg_replace('/[^0-9.-]/', '', str_replace(',', '.', Tools::getValue('price_impact_'.(int) $attribute)));
            $weight += (float) preg_replace('/[^0-9.-]/', '', str_replace(',', '.', Tools::getValue('weight_impact_'.(int) $attribute)));
        }

        if ($this->id_mp_product) {
            return array(
                'id_mp_product' => (int) $this->id_mp_product,
                'mp_price' => (float) $price,
                'mp_weight' => (float) $weight,
                'mp_quantity' => (int) Tools::getValue('quantity'),
                'mp_reference' => pSQL(Tools::getValue('reference')),
                'mp_default_on' => 0,
                'mp_available_date' => '0000-00-00',
            );
        }

        return array();
    }

    public function createCombinations($list)
    {
        if (count($list) <= 1) {
            return count($list) ? array_map(
                function ($v) {
                    return array($v);
                },
                $list[0]
            ) : $list;
        }
        $res = array();
        $first = array_pop($list);
        foreach ($first as $attribute) {
            $tab = $this->createCombinations($list);
            foreach ($tab as $toadd) {
                $res[] = is_array($toadd) ? array_merge($toadd, array($attribute)) : array($toadd, $attribute);
            }
        }

        return $res;
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'generatecombination'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        );
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Generate Combination', 'generatecombination'),
            'url' => '',
        );
        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->registerStylesheet('mp-marketplace_account', 'modules/'.$this->module->name.'/views/css/marketplace_account.css');
        $this->registerStylesheet('mp_global_style-css', 'modules/'.$this->module->name.'/views/css/mp_global_style.css');

        $this->registerJavascript('tools-js', 'js/tools.js');
        $this->registerJavascript('mp-generatecombination', 'modules/'.$this->module->name.'/views/js/generatecombination.js');
    }
}
