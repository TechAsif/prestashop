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

class MarketplaceCreateFeatureModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (isset($this->context->customer->id)) {
            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            if ($mpSeller && Configuration::get('WK_MP_PRESTA_FEATURE_ACCESS')) {
                if (Tools::getValue('id_feature')) {
                    $idFeature = Tools::getValue('id_feature');

                    // IF FEATURE IS EDITABLE OR NOT ( 0 = NON EDITABLE)
                    if ($idFeature != 0) {
                        $objFeatureData = new Feature($idFeature);
                        $this->context->smarty->assign('feature_name_val', $objFeatureData->name);
                        $this->context->smarty->assign('id_feature', $idFeature);
                    } else {
                        Tools::redirect($this->context->link->getModuleLink('marketplace', 'productfeature', array('error_attr' => 1)));
                    }
                }

                // Set default lang at every form according to configuration multi-language
                WkMpHelper::assignDefaultLang($mpSeller['id_seller']);
                $this->context->smarty->assign(array(
                    'wkself' => dirname(__FILE__),
                    'logic' => 'mp_prod_features',
                ));
                $this->setTemplate('module:marketplace/views/templates/front/product/features/createfeature.tpl');
            } else {
                Tools::redirect(__PS_BASE_URI__.'pagenotfound');
            }
        } else {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('SubmitFeature')) {
            if (isset($this->context->customer->id)) {
                $sellerDefaultLanguage = Tools::getValue('default_lang');
                $defaultLang = WkMpHelper::getDefaultLanguageBeforeFormSave($sellerDefaultLanguage);

                // Check fields sizes
                $className = 'Feature';
                $rules = call_user_func(array($className, 'getValidationRules'), $className);

                if (!Tools::getValue('feature_name_'.$defaultLang)) {
                    if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                        $sellerLang = Language::getLanguage((int) $defaultLang);
                        $this->errors[] = sprintf($this->module->l('Feature name is required in %s', 'createfeature'), $sellerLang['name']);
                    } else {
                        $this->errors[] = $this->module->l('Feature name is required.', 'createfeature');
                    }
                } else {
                    $languages = Language::getLanguages();
                    foreach ($languages as $language) {
                        $languageName = '';
                        if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                            $languageName = '('.$language['name'].')';
                        }

                        if (!Validate::isGenericName(Tools::getValue('feature_name_'.$language['id_lang']))) {
                            $this->errors[] = sprintf($this->module->l('Feature name field %s is invalid', $className), $languageName);
                        } elseif (Tools::strlen(Tools::getValue('feature_name_'.$language['id_lang'])) > $rules['sizeLang']['name']) {
                            $this->errors[] = sprintf($this->module->l('Feature name field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['name']);
                        }
                    }
                }

                if (!count($this->errors)) {
                    $idFeature = Tools::getValue('id_feature');
                    if ($idFeature) {
                        $objFeature = new Feature($idFeature); //edit feature
                    } else {
                        $objFeature = new Feature(); //create feature
                    }

                    foreach (Language::getLanguages(false) as $language) {
                        $featureLangId = $language['id_lang'];
                        if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                            //if feature name in other language is not available then fill with seller language same for others
                            if (!Tools::getValue('feature_name_'.$language['id_lang'])) {
                                $featureLangId = $defaultLang;
                            }
                        } else {
                            //if multilang is OFF then all fields will be filled as default lang content
                            $featureLangId = $defaultLang;
                        }
                        $objFeature->name[$language['id_lang']] = Tools::getValue('feature_name_'.$featureLangId);
                    }
                    $objFeature->save();
                    if ($idFeature) {
                        $successAttr = 2;
                    } else {
                        if ($objFeature->id) {
                            WkMpProductFeature::addPSLayeredIndexableFeature(array('id_feature' => $objFeature->id, 'indexable' => 1));
                        }
                        $successAttr = 1;
                    }

                    Tools::redirect($this->context->link->getModuleLink('marketplace', 'productfeature', array('success_attr' => $successAttr)));
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
            'title' => $this->module->l('Marketplace', 'createfeature'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Add/Edit Feature', 'createfeature'),
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
