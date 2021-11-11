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

class MarketplaceCreateAttributeModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (isset($this->context->customer->id)) {
            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            if ($mpSeller['active'] && Configuration::get('WK_MP_PRESTA_ATTRIBUTE_ACCESS')) {
                $idSeller = $mpSeller['id_seller'];
                $idGroup = Tools::getValue('id_group');
                if ($idGroup === '0') {
                    //if Attribute group is already in use
                    Tools::redirect($this->context->link->getModuleLink('marketplace', 'productattribute'));
                } elseif ($idGroup) {
                    // edit attribute
                    $attrGroup = new AttributeGroup($idGroup);
                    if ($attrGroup) {
                        $this->context->smarty->assign(array(
                            'attr_name' => $attrGroup->name,
                            'attr_public_name' => $attrGroup->public_name,
                            'group_type' => $attrGroup->group_type,
                        ));
                    }

                    $this->context->smarty->assign('id_group', $idGroup);
                }

                WkMpHelper::assignDefaultLang($idSeller);
                $this->context->smarty->assign(array(
                        'wkself' => dirname(__FILE__),
                        'logic' => 'mp_prod_attribute',
                    ));
                $this->setTemplate('module:marketplace/views/templates/front/product/combination/createattribute.tpl');
            } else {
                Tools::redirect(__PS_BASE_URI__.'pagenotfound');
            }
        } else {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('SubmitAttribute')) {
            $attribType = Tools::getValue('attrib_type');
            $sellerDefaultLanguage = Tools::getValue('default_lang');
            $defaultLang = WkMpHelper::getDefaultLanguageBeforeFormSave($sellerDefaultLanguage);

            // Check fields sizes
            $className = 'AttributeGroup';
            $rules = call_user_func(array($className, 'getValidationRules'), $className);

            if (!Tools::getValue('attrib_name_'.$defaultLang)) {
                if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                    $sellerLang = Language::getLanguage((int) $defaultLang);
                    $this->errors[] = sprintf($this->module->l('Attribute name is required in %s', 'createattribute'), $sellerLang['name']);
                } else {
                    $this->errors[] = $this->module->l('Attribute name is required', 'createattribute');
                }
            } elseif (!Tools::getValue('attrib_public_name_'.$defaultLang)) {
                if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                    $sellerLang = Language::getLanguage((int) $defaultLang);
                    $this->errors[] = sprintf($this->module->l('Attribute public name is required in %s', 'createattribute'), $sellerLang['name']);
                } else {
                    $this->errors[] = $this->module->l('Attribute public name is required', 'createattribute');
                }
            } else {
                $languages = Language::getLanguages();
                foreach ($languages as $language) {
                    $languageName = '';
                    if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                        $languageName = '('.$language['name'].')';
                    }

                    if (!Validate::isGenericName(Tools::getValue('attrib_name_'.$language['id_lang']))) {
                        $this->errors[] = sprintf($this->module->l('Attribute name field %s is invalid', $className), $languageName);
                    } elseif (Tools::strlen(Tools::getValue('attrib_name_'.$language['id_lang'])) > $rules['sizeLang']['name']) {
                        $this->errors[] = sprintf($this->module->l('Attribute name field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['name']);
                    }

                    if (!Validate::isGenericName(Tools::getValue('attrib_public_name_'.$language['id_lang']))) {
                        $this->errors[] = sprintf($this->module->l('Attribute public name field %s is invalid', $className), $languageName);
                    } elseif (Tools::strlen(Tools::getValue('attrib_public_name_'.$language['id_lang'])) > $rules['sizeLang']['public_name']) {
                        $this->errors[] = sprintf($this->module->l('Attribute public name field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['public_name']);
                    }
                }
            }

            if (!count($this->errors)) {
                $usedAttribute = 0;
                $idGroup = Tools::getValue('id_group');
                if ($idGroup) {
                    // edit attribute group
                    $objAttributeGroup = new AttributeGroup($idGroup);
                    if (!WkMpAttributeImpact::checkCombinationByGroup($this->context->language->id, $idGroup)) {
                        $usedAttribute = 1;
                    }
                } else {
                    $objAttributeGroup = new AttributeGroup();
                    $usedAttribute = 1;
                }

                if ($usedAttribute) {
                    $isColor = 0;
                    foreach (Language::getLanguages(false) as $language) {
                        $attributeLangId = $language['id_lang'];
                        $publicLangId = $language['id_lang'];
                        if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                            //if attribute name in other language is not available then fill with seller language same for others
                            if (!Tools::getValue('attrib_name_'.$language['id_lang'])) {
                                $attributeLangId = $defaultLang;
                            }
                            if (!Tools::getValue('attrib_public_name_'.$language['id_lang'])) {
                                $publicLangId = $defaultLang;
                            }
                        } else {
                            //if multilang is OFF then all fields will be filled as default lang content
                            $attributeLangId = $defaultLang;
                            $publicLangId = $defaultLang;
                        }
                        $objAttributeGroup->name[$language['id_lang']] = Tools::getValue('attrib_name_'.$attributeLangId);
                        $objAttributeGroup->public_name[$language['id_lang']] = Tools::getValue('attrib_public_name_'.$publicLangId);
                    }

                    $objAttributeGroup->group_type = $attribType;
                    if ($attribType == 'color') {
                        $isColor = 1;
                    }
                    $objAttributeGroup->is_color_group = $isColor;
                    $objAttributeGroup->save();
                    if ($idGroup) {
                        Tools::redirect($this->context->link->getModuleLink('marketplace', 'productattribute', array('updated' => 1)));
                    } else {
                        // To update Table layered_indexable_attribute_group
                        WkMpAttributeImpact::setIndexableValue(array(
                                            'id_attribute_group' => $objAttributeGroup->id,
                                            'indexable' => 1
                                        ));
                        Tools::redirect($this->context->link->getModuleLink('marketplace', 'productattribute', array('created' => 1)));
                    }
                } else {
                    $this->errors[] = $this->module->l('This Attribute group is already in use you cannot edit or delete it.', 'createattribute');
                }
            }
        }
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'createattribute'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        );
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Add/Edit Attribute', 'createattribute'),
            'url' => '',
        );
        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->registerStylesheet('mp-marketplace_account', 'modules/'.$this->module->name.'/views/css/marketplace_account.css');
        $this->registerStylesheet('mp_global_style-css', 'modules/'.$this->module->name.'/views/css/mp_global_style.css');

        $this->registerJavascript('mp-productattribute', 'modules/'.$this->module->name.'/views/js/productattribute.js');
        $this->registerJavascript('mp-change_multilang', 'modules/'.$this->module->name.'/views/js/change_multilang.js');
    }
}
