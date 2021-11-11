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

class AdminMpGenerateCombinationController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        if (!$this->loadObject(true)) {
            return;
        }
        parent::__construct();
        $this->toolbar_title = $this->l('Manage Attribute Generator');
    }

    public function initContent()
    {
        $this->initToolbar();
        $this->display = '';
        $this->content .= $this->renderForm();

        $this->context->smarty->assign(array(
            'content' => $this->content,
        ));
        parent::initContent();
    }

    public function renderForm()
    {
        WkMpAttributeImpact::assignAttributeValues();
        $this->context->smarty->assign(array(
                    'wkself' => dirname(__FILE__),
                    'attribute_js' => $this->displayAndReturnAttributeJs(),
                    'backendController' => 1,
                ));

        $this->fields_form = array(
            'submit' => array(
                'title' => $this->l('Save'),
                ),
            );
        return parent::renderForm();
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
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminMpGenerateCombination').'&msg=1&id_mp_product='.$this->id_mp_product);
            } else {
                if (!Validate::isInt(Tools::getValue('quantity'))) {
                    $this->errors[] = $this->l('Quantity should be valid.');
                }
                if (Tools::getValue('reference') && !Validate::isReference(Tools::getValue('reference'))) {
                    $this->errors[] = $this->l('Reference is not valid.');
                }

                if (!count($this->errors)) {
                    $objSellerProduct = new WkMpSellerProduct($this->id_mp_product);
                    $idPsProduct = $objSellerProduct->id_ps_product;
                    $tab = array_values(Tools::getValue('options'));
                    if (count($tab) && Validate::isLoadedObject($objSellerProduct)) {
                        //Delete all combination before generating combinations
                        $objSellerProduct->deleteCombinationAssociations($this->id_mp_product);
                        if ($idPsProduct && $objSellerProduct->active) {
                            $objProduct = new Product($idPsProduct);
                            $objProduct->deleteProductAttributes();
                        }

                        //Combination Attribute list
                        $this->combinations = array_values($this->createCombinations($tab));
                        //Combination Values
                        $productAttribute = array_values(array_map(array($this, 'addAttribute'), $this->combinations));

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

                        Tools::redirectAdmin($this->context->link->getAdminLink('AdminSellerProductDetail').'&updatewk_mp_seller_product&conf=4&tab=wk-combination&id_mp_product='.$this->id_mp_product);
                    } else {
                        Tools::redirectAdmin($this->context->link->getAdminLink('AdminMpGenerateCombination').'&msg=2&id_mp_product='.$this->id_mp_product);
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
                'mp_id_product' => (int) $this->id_mp_product,
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

    public function initBreadcrumbs($tabId = null, $tabs = null)
    {
        parent::initBreadcrumbs();
        $dummy = array('name' => '', 'href' => '', 'icon' => '');
        $breadcrumbs2 = array(
            'container' => $dummy,
            'tab' => $dummy,
            'action' => $dummy
        );

        $tabs = Tab::recursiveTab($this->id, $tabs);
        if (isset($tabs[0])) {
            $breadcrumbs2['tab']['name'] = $tabs[0]['name'];
            $breadcrumbs2['tab']['href'] = '';
        }

        $this->context->smarty->assign(array(
            'breadcrumbs2' => $breadcrumbs2,
            'quick_access_current_link_name' => $breadcrumbs2['tab']['name'].(isset($breadcrumbs2['action']) ? ' - '.$breadcrumbs2['action']['name'] : ''),
            'quick_access_current_link_icon' => $breadcrumbs2['container']['icon']
        ));

        /* BEGIN - Backward compatibility < 1.6.0.3 */
        $this->breadcrumbs[] = $tabs[0]['name'];
        $navigation_pipe = (Configuration::get('PS_NAVIGATION_PIPE') ? Configuration::get('PS_NAVIGATION_PIPE') : '>');
        $this->context->smarty->assign('navigationPipe', $navigation_pipe);
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/mp_global_style.css');
        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/generatecombination.js');
    }
}
