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

class MarketplaceAddFeatureValueModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (isset($this->context->customer->id)) {
            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            if ($mpSeller && Configuration::get('WK_MP_PRESTA_FEATURE_ACCESS')) {
                if (Tools::getValue('id_feature')) {
                    $idFeature = Tools::getValue('id_feature');
                    $this->context->smarty->assign('id_feature', $idFeature);

                    //Edit feature value page
                    if (Tools::getValue('id_feature_value')) {
                        $idFeatureValue = Tools::getValue('id_feature_value');
                        $featureInfo = array();
                        $featureVal = array();
                        // IF FEATURE VALUE IS EDITABLE OR NOT ( 0 = NON EDITABLE)
                        if ($idFeatureValue != 0) {
                            $featureData = Feature::getFeature($this->context->language->id, $idFeature);
                            $featureInfo['name'] = $featureData['name'];
                            $featureInfo['id'] = $idFeature;
                            $data = FeatureValue::getFeatureValueLang($idFeatureValue);
                            foreach ($data as $data_each) {
                                $featureVal[$data_each['id_lang']] = $data_each['value'];
                            }

                            $this->context->smarty->assign('id_feature_value', $idFeatureValue);
                            $this->context->smarty->assign('feature_info', $featureInfo);
                            $this->context->smarty->assign('feature_val', $featureVal);
                        } else {
                            Tools::redirect($this->context->link->getModuleLink('marketplace', 'viewfeaturevalue', array('id_feature' => $idFeature, 'error_attr' => 1)));
                        }
                    }
                }

                $defaultLang = WkMpHelper::getDefaultLanguageBeforeFormSave($mpSeller['default_lang']);
                $featureData = Feature::getFeatures($defaultLang);
                $i = 0;
                $featureSet = array();
                foreach ($featureData as $featureDataEach) {
                    $featureSet[$i]['id'] = $featureDataEach['id_feature'];
                    $featureSet[$i]['name'] = $featureDataEach['name'];
                    ++$i;
                }

                // Set default lang at every form according to configuration multi-language
                WkMpHelper::assignDefaultLang($mpSeller['id_seller']);
                $this->context->smarty->assign(array(
                    'feature_set' => $featureSet,
                    'wkself' => dirname(__FILE__),
                    'logic' => 'mp_prod_features',
                ));
                $this->setTemplate('module:marketplace/views/templates/front/product/features/addfeaturevalue.tpl');
            } else {
                Tools::redirect(__PS_BASE_URI__.'pagenotfound');
            }
        } else {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('SubmitFeatureValue')) {
            if (isset($this->context->customer->id)) {
                $featureGroup = Tools::getValue('feature_group');
                $sellerDefaultLanguage = Tools::getValue('default_lang');
                $defaultLang = WkMpHelper::getDefaultLanguageBeforeFormSave($sellerDefaultLanguage);

                // Check fields sizes
                $className = 'FeatureValue';
                $rules = call_user_func(array($className, 'getValidationRules'), $className);

                if (!Tools::getValue('feature_value_'.$defaultLang)) {
                    if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                        $sellerLang = Language::getLanguage((int) $defaultLang);
                        $this->errors[] = sprintf($this->module->l('Feature value is required in %s', 'createfeature'), $sellerLang['name']);
                    } else {
                        $this->errors[] = $this->module->l('Feature value is required.', 'createfeature');
                    }
                } else {
                    $languages = Language::getLanguages();
                    foreach ($languages as $language) {
                        $languageName = '';
                        if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                            $languageName = '('.$language['name'].')';
                        }

                        if (!Validate::isGenericName(Tools::getValue('feature_value_'.$language['id_lang']))) {
                            $this->errors[] = sprintf($this->module->l('Feature value field %s is invalid', $className), $languageName);
                        } elseif (Tools::strlen(Tools::getValue('feature_value_'.$language['id_lang'])) > $rules['sizeLang']['value']) {
                            $this->errors[] = sprintf($this->module->l('Feature value field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['value']);
                        }
                    }
                }

                if (!count($this->errors)) {
                    $idFeatureValue = Tools::getValue('id_feature_value');
                    if ($idFeatureValue) {
                        $successAttr = 2;
                        $objFeatureValue = new FeatureValue($idFeatureValue);
                    } else {
                        $successAttr = 1;
                        $objFeatureValue = new FeatureValue();
                    }

                    $objFeatureValue->id_feature = $featureGroup;
                    $objFeatureValue->custom = 0;
                    foreach (Language::getLanguages(false) as $language) {
                        $featureLangId = $language['id_lang'];
                        if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                            //if feature value in other language is not available then fill with seller language same for others
                            if (!Tools::getValue('feature_value_'.$language['id_lang'])) {
                                $featureLangId = $defaultLang;
                            }
                        } else {
                            //if multilang is OFF then all fields will be filled as default lang content
                            $featureLangId = $defaultLang;
                        }
                        $objFeatureValue->value[$language['id_lang']] = Tools::getValue('feature_value_'.$featureLangId);
                    }
                    $objFeatureValue->save();

                    Tools::redirect($this->context->link->getModuleLink('marketplace', 'viewfeaturevalue', array('id_feature' => $featureGroup, 'success_attr' => $successAttr)));
                }
            } else {
                Tools::redirect($this->context->link->getPageLink('my-account'));
            }
        }
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'addfeaturevalue'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Add/Edit Feature Value', 'addfeaturevalue'),
            'url' => '',
        );

        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->registerStylesheet('mp-marketplace_account', 'modules/'.$this->module->name.'/views/css/marketplace_account.css');
        $this->registerStylesheet('mp_global_style-css', 'modules/'.$this->module->name.'/views/css/mp_global_style.css');
        $this->registerStylesheet('mp_productfeature-css', 'modules/'.$this->module->name.'/views/css/productfeature.css');

        $this->registerJavascript('mp-change_multilang', 'modules/'.$this->module->name.'/views/js/change_multilang.js');
    }
}
