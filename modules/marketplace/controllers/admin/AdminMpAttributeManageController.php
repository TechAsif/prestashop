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

class AdminMpAttributeManageController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();
        $this->toolbar_title = $this->l('Manage Product Combination');
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
        if (!($this->loadObject(true))) {
            return;
        }

        $idMpProductAttribute = Tools::getValue('id_combination');

        if ($idMpProductAttribute) {
            $objMpProductAttribute = new WkMpProductAttribute($idMpProductAttribute);
            $idMpProduct = $objMpProductAttribute->id_mp_product;
        } else {
            $idMpProduct = Tools::getValue('id');
        }

        $mpProduct = WkMpSellerProduct::getSellerProductByIdProduct($idMpProduct);
        if ($mpProduct) {
            //Assign Data for create/update combination
            if ($idMpProductAttribute) {
                WkMpProductAttribute::assignCombinationCreationFormData($mpProduct, $idMpProduct, $idMpProductAttribute);
            } else {
                WkMpProductAttribute::assignCombinationCreationFormData($mpProduct, $idMpProduct);
            }
        }

        $this->context->smarty->assign(array(
            'mp_image_dir' => _MODULE_DIR_.'marketplace/views/img/',
            'module_dir' => _MODULE_DIR_,
            'img_ps_dir' => _PS_IMG_DIR_,
            'link' => $this->context->link,
            'wkself' => dirname(__FILE__),
            'backendController' => 1,
        ));

        $this->fields_form = array(
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        return parent::renderForm();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitCombination')) {
            $idMpProduct = (int) Tools::getValue('mp_id_product');
            $mpReference = Tools::getValue('mp_reference');
            $mpEan13 = Tools::getValue('mp_ean13');
            $mpUPC = Tools::getValue('mp_upc');
            $mpISBN = Tools::getValue('mp_isbn');
            $productAttributeList = Tools::getValue('attribute_combination_list');
            $mpQuantity = Tools::getValue('mp_quantity');
            $mpMinimalQuantity = Tools::getValue('mp_minimal_quantity');
            $mpPrice = Tools::getValue('mp_price');
            $mpWholesalePrice = Tools::getValue('mp_wholesale_price');
            $mpUnitPriceImpact = Tools::getValue('mp_unit_price_impact');
            $mpWeight = Tools::getValue('mp_weight');
            $mpAvailableDate = Tools::getValue('mp_available_date');
            $idImages = Tools::getValue('id_image_attr');
            $lowStockThreshold = Tools::getValue('low_stock_threshold');
            if (!$lowStockThreshold) {
                $lowStockThreshold = 0;
            }
            if (Tools::getValue('low_stock_alert')) {
                $lowStockAlert = 1;
            } else {
                $lowStockAlert = 0;
            }

            if (!$productAttributeList) {
                $this->errors[] = $this->l('Combination attribute cannot be blank.');
            }
            if (!Validate::isInt($mpQuantity)) {
                $this->errors[] = $this->l('Quantity should be numeric.');
            }
            if (!Validate::isUnsignedInt($mpMinimalQuantity)) {
                $this->errors[] = $this->l('Minimum quantity should be valid.');
            }
            if (!Validate::isInt($lowStockThreshold)) {
                $this->errors[] = $this->l('Low stock level should be valid.');
            }
            if ($mpReference && !Validate::isReference($mpReference)) {
                $this->errors[] = $this->l('Reference is not valid.');
            }
            if ($mpEan13 && !Validate::isEan13($mpEan13)) {
                $this->errors[] = $this->l('EAN-13 or JAN barcode is not valid.');
            }
            if ($mpUPC && !Validate::isUpc($mpUPC)) {
                $this->errors[] = $this->l('UPC barcode is not valid.');
            }
            if ($mpISBN && !Validate::isIsbn($mpISBN)) {
                $this->errors[] = $this->l('ISBN code is not valid.');
            }
            if ($mpPrice) {
                if (!Validate::isNegativePrice($mpPrice)) {
                    $this->errors[] = $this->l('Impact price must be numeric.');
                }
            } else {
                $mpPrice = 0;
            }
            if ($mpWholesalePrice) {
                if (!Validate::isPrice($mpWholesalePrice)) {
                    $this->errors[] = $this->l('Wholesale price must be numeric.');
                }
            } else {
                $mpWholesalePrice = 0;
            }
            if ($mpUnitPriceImpact) {
                if (!Validate::isNegativePrice($mpUnitPriceImpact)) {
                    $this->errors[] = $this->l('Impact on unit price must be numeric.');
                }
            } else {
                $mpUnitPriceImpact = 0;
            }
            if ($mpWeight) {
                if (!Validate::isFloat($mpWeight)) {
                    $this->errors[] = $this->l('Impact on weight must be numeric.');
                }
            } else {
                $mpWeight = 0.00;
            }
            if ($mpAvailableDate && !Validate::isDateFormat($mpAvailableDate)) {
                $this->errors[] = $this->l('Available date must be valid.');
            }

            $idMpProductAttribute = Tools::getValue('mp_id_product_attribute');

            if ($productAttributeList) { //if same combination is already exist
                if (WkMpProductAttributeCombination::isProductCombinationExists($idMpProduct, $productAttributeList, $idMpProductAttribute)) {
                    $this->errors[] = $this->l('This Combination is already exists for this product.');
                }
            }

            if (!count($this->errors)) {
                $udpateMpIdProductAttribute = WkMpProductAttribute::saveMpProductCombination(
                    $idMpProduct,
                    $idMpProductAttribute,
                    $productAttributeList,
                    $mpReference,
                    $mpEan13,
                    $mpUPC,
                    $mpISBN,
                    $mpPrice,
                    $mpWholesalePrice,
                    $mpUnitPriceImpact,
                    $mpQuantity,
                    $mpWeight,
                    $mpMinimalQuantity,
                    $mpAvailableDate,
                    $idImages,
                    $lowStockThreshold,
                    $lowStockAlert
                );

                if ($udpateMpIdProductAttribute) {
                    Tools::redirectAdmin($this->context->link->getAdminLink('AdminMpAttributeManage').'&id_combination='.$udpateMpIdProductAttribute.'&conf=4');
                }
            }
        }

        parent::postProcess();
    }

    public function ajaxProcessGetAttributeValue()
    {
        //Change Attribute Value according to Attribute Group
        $attributeGroupId = Tools::getValue('attribute_group_id');

        $attributeVal = WkMpProductAttribute::getAttributeValueByGroup($attributeGroupId);
        $jsondata = Tools::jsonEncode($attributeVal);
        echo $jsondata;

        die; //ajax close
    }

    public function initBreadcrumbs($tabId = null, $tabs = null)
    {
        //Remove links from controller breadcrumbs
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

        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/managecombination.js');
        $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/mp_global_style.css');
    }
}
