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

class MarketplaceCreateAttributeValueModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (isset($this->context->customer->id)) {
            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            if ($mpSeller['active'] && Configuration::get('WK_MP_PRESTA_ATTRIBUTE_ACCESS')) {
                $idSeller = $mpSeller['id_seller'];
                $idAttribute = Tools::getValue('id_attribute');
                $idGroup = Tools::getValue('id_group');
                if ($idGroup) {
                    $this->context->smarty->assign('id_group', $idGroup);
                    $idGroupJsVal = $idGroup;
                } else {
                    $idGroupJsVal = 0;
                }
                if ($idAttribute === '0') {
                    //if Attribute value is already in use
                    Tools::redirect($this->context->link->getModuleLink('marketplace', 'viewattributegroupvalue'));
                } elseif ($idAttribute) {
                    $attributeGroupList = AttributeGroup::getAttributesGroups($this->context->language->id);
                    $attribGroup = array();
                    foreach ($attributeGroupList as $attributeGroupEach) {
                        if ($attributeGroupEach['id_attribute_group'] == $idGroup) {
                            $attribGroup['name'] = $attributeGroupEach['name'];
                            $attribGroup['id'] = $attributeGroupEach['id_attribute_group'];
                        }
                    }
                    $this->context->smarty->assign('attrib_grp', $attribGroup);

                    $groupAttributeSet = new Attribute($idAttribute);
                    if ($groupAttributeSet) {
                        $this->context->smarty->assign('attrib_valname', $groupAttributeSet->name);

                        if (WkMpAttributeImpact::ifColorAttributegroup($idGroup)) {
                            $this->context->smarty->assign('attrib_color', $groupAttributeSet->color);
                        }
                    }

                    // code for image texture
                    $image = _PS_IMG_DIR_.'co/'.$idAttribute.'.jpg';
                    $this->context->smarty->assign('imageTextureExists', file_exists($image));
                    $this->context->smarty->assign('id_attribute', $idAttribute);
                    $this->context->smarty->assign('id_group', $idGroup);
                } else {
                    $attributeGroupList = AttributeGroup::getAttributesGroups(WkMpSeller::getSellerDefaultLanguage($idSeller));
                    $attribSet = array();
                    foreach ($attributeGroupList as $attributeGroupEach) {
                        $i = $attributeGroupEach['id_attribute_group'];
                        $attribSet[$i]['name'] = $attributeGroupEach['name'];
                        $attribSet[$i]['id'] = $attributeGroupEach['id_attribute_group'];
                    }
                    ksort($attribSet);

                    $this->context->smarty->assign('attrib_set', $attribSet);
                }

                WkMpHelper::assignDefaultLang($idSeller);
                $this->context->smarty->assign(array(
                        'wkself' => dirname(__FILE__),
                        'logic' => 'mp_prod_attribute',
                        'img_col_dir' =>_THEME_COL_DIR_,
                    ));

                $jsVars = array(
                    'createattributevalue_controller' => $this->context->link->getModuleLink('marketplace', 'createattributevalue'),
                    'id_group' => $idGroupJsVal,
                );
                Media::addJsDef($jsVars);

                $this->setTemplate('module:marketplace/views/templates/front/product/combination/createattributevalue.tpl');
            } else {
                Tools::redirect(__PS_BASE_URI__.'pagenotfound');
            }
        } else {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('SubmitAttributeValue')) {
            $attribGroup = Tools::getValue('attrib_group');
            $sellerDefaultLanguage = Tools::getValue('default_lang');
            $defaultLang = WkMpHelper::getDefaultLanguageBeforeFormSave($sellerDefaultLanguage);

            // Check fields sizes
            $className = 'Attribute';
            $rules = call_user_func(array($className, 'getValidationRules'), $className);

            if (!Tools::getValue('attrib_value_'.$defaultLang)) {
                if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                    $sellerLang = Language::getLanguage((int) $defaultLang);
                    $this->errors[] = sprintf($this->module->l('Attribute value is required in %s', 'createattributevalue'), $sellerLang['name']);
                } else {
                    $this->errors[] = $this->module->l('Attribute value is required', 'createattributevalue');
                }
            } else {
                $languages = Language::getLanguages();
                foreach ($languages as $language) {
                    $languageName = '';
                    if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                        $languageName = '('.$language['name'].')';
                    }

                    if (!Validate::isGenericName(Tools::getValue('attrib_value_'.$language['id_lang']))) {
                        $this->errors[] = sprintf($this->module->l('Attribute value field %s is invalid', $className), $languageName);
                    } elseif (Tools::strlen(Tools::getValue('attrib_value_'.$language['id_lang'])) > $rules['sizeLang']['name']) {
                        $this->errors[] = sprintf($this->module->l('Attribute value field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['name']);
                    }
                }
            }

            // add attribute value
            $isColor = 0;
            if (WkMpAttributeImpact::ifColorAttributegroup($attribGroup)) {
                $isColor = 1;
                $attribValueColor = Tools::getValue('attrib_value_color');
                if (!$attribValueColor) {
                    $this->errors[] = $this->module->l('Problem occured while adding data.', 'createattributevalue');
                }

                //validate product texture image
                if (!empty($_FILES['color_img']['name'])) {
                    $this->validAddAttrTextureImage($_FILES['color_img']);
                }
            }

            if (!count($this->errors)) {
                $usedAttribute = 0;
                $idAttribute = Tools::getValue('id_attribute');
                if ($idAttribute) {
                    // edit attribute
                    $objAttribute = new Attribute($idAttribute);
                    if (!WkMpAttributeImpact::checkCombinationByAttribute($idAttribute)) {
                        $usedAttribute = 1;
                    }
                } else {
                    $objAttribute = new Attribute();
                    $usedAttribute = 1;
                }

                if ($usedAttribute) {
                    $objAttribute->id_attribute_group = $attribGroup;
                    foreach (Language::getLanguages(false) as $language) {
                        $attributeLangId = $language['id_lang'];
                        if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                            //if attribute name in other language is not available then fill with seller language same for others
                            if (!Tools::getValue('attrib_value_'.$language['id_lang'])) {
                                $attributeLangId = $defaultLang;
                            }
                        } else {
                            //if multilang is OFF then all fields will be filled as default lang content
                            $attributeLangId = $defaultLang;
                        }
                        $objAttribute->name[$language['id_lang']] = Tools::getValue('attrib_value_'.$attributeLangId);
                    }
                    if ($isColor) {
                        $objAttribute->color = $attribValueColor;
                    }
                    if ($objAttribute->save()) {
                        if ($isColor) {
                            $imageName = $objAttribute->id.'.jpg';
                            $uploadPath = _PS_IMG_DIR_.'co/';
                            ImageManager::resize($_FILES['color_img']['tmp_name'], $uploadPath.$imageName);
                        }
                    }

                    if ($idAttribute) {
                        Tools::redirect($this->context->link->getModuleLink('marketplace', 'viewattributegroupvalue', array('id_group' => $attribGroup, 'updated' => 1)));
                    } else {
                        Tools::redirect($this->context->link->getModuleLink('marketplace', 'viewattributegroupvalue', array('id_group' => $attribGroup, 'created' => 1)));
                    }
                } else {
                    $this->errors[] = $this->module->l('This Attribute value is already in use you cannot edit or delete it.', 'createattributevalue');
                }
            }
        }
    }

    public function validAddAttrTextureImage($image)
    {
        if ($image['size'] > 0) {
            if ($image['tmp_name'] != '') {
                if (!ImageManager::isCorrectImageFileExt($image['name'])) {
                    $this->errors[] = $_FILES['product_image']['name'].$this->module->l(' : Image format not recognized, allowed formats are: .gif, .jpg, .png');
                }
            }
        } else {
            return true;
        }
    }

    public function displayAjaxCheckColorType()
    {
        if ($idGroup = Tools::getValue('group_id')) {
            $flag = WkMpAttributeImpact::ifColorAttributegroup($idGroup);
            if ($flag) {
                die('1');
            }
        }
        die('0'); //ajax close
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'createattributevalue'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        );
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Add/Edit Attribute Value', 'createattributevalue'),
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
