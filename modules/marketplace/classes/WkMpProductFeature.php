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

class WkMpProductFeature
{
    /**
     * Get Product feature value with custom values if exist using feature id.
     *
     * @param int $idFeature Prestashop Product Feature ID
     *
     * @return array/bool
     */
    public static function getFeatureValue($idFeature)
    {
        $featuresValue = false;
        if ($idFeature) {
            $featuresValue = FeatureValue::getFeatureValuesWithLang(
                Context::getContext()->language->id,
                (int) $idFeature
            );
        }

        return $featuresValue;
    }

    /**
     * Get Product Feature Prefined Value Using Product ID Feature.
     *
     * @param int $idFeatureValue Prestashop Id Feature
     *
     * @return array/bool
     */
    public static function getFeatureValues($idFeatureValue)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'feature_value`
            WHERE `id_feature_value` = '.(int) $idFeatureValue
        );
    }

    /**
     * Retrieve Marketplace feature values.
     *
     * @param int $mpProductID Seller Product ID
     * @param int $idFeature   Prestashop Feature ID
     *
     * @return array
     */
    public static function getFeatureValueByIdProduct($mpProductID, $idFeature)
    {
        return Db::getInstance()->getRow(
            'SELECT `feature_value` FROM `'._DB_PREFIX_.'wk_mp_product_feature`
            WHERE `mp_id_product` = '.(int) $mpProductID.'
            AND `id_feature` = '.(int) $idFeature
        );
    }

    /**
     * Retrieve Marketplace Product Feature ID.
     *
     * @param int $mpProductID Marketplace Product ID
     * @param int $psIDFeature Prestashop Feature ID
     *
     * @return array
     */
    public static function getMpProductFeatureByMpIdProduct($mpProductID, $psIDFeature = false)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_mp_product_feature` WHERE `mp_id_product` = '.(int) $mpProductID;
        if ($psIDFeature) {
            $sql .= ' AND `ps_id_feature` = '.(int) $psIDFeature;
            return Db::getInstance()->getRow($sql);
        }
        return Db::getInstance()->executeS($sql);
    }

    /**
     * Creating product feature in marketplace table.
     *
     * @param int  $idFeature   Prestashop Feature ID
     * @param bool $isCustom    1/0
     * @param bool $number      iteration (Loop iteration)
     * @param bool $defaultLang Seller Default Language ID
     *
     * @return int Marketplace Feature Value ID
     */
    public static function createProductCustomFeatureToMp(
        $idFeature,
        $isCustom = false,
        $number = false,
        $defaultLang = false
    ) {
        Db::getInstance()->insert(
            'wk_mp_product_feature_value',
            array(
                'ps_id_feature' => (int) $idFeature,
                'is_custom' => (int) $isCustom,
            )
        );
        $idCustomFeature = (int) Db::getInstance()->Insert_ID();

        foreach (Language::getLanguages(false) as $language) {
            $customIdLang = $language['id_lang'];
            $customValue = trim(Tools::getValue('wk_mp_feature_custom_'.$defaultLang.'_'.$number));
            if (!$customValue) {
                //$this->errors[] = $this->l('Value is missing');
            } else {
                if (!Tools::getValue('wk_mp_feature_custom_'.$language['id_lang'].'_'.$number)) {
                    $customIdLang = $defaultLang;
                }
                $customValue = trim(Tools::getValue('wk_mp_feature_custom_'.$customIdLang.'_'.$number));
                self::addProductCustomFeatureToMp($idCustomFeature, $customValue, $language['id_lang']);
            }
        }

        return $idCustomFeature;
    }

    /**
     * Creating Custom Feature Value with language in marketplace table.
     *
     * @param int  $idMpProduct    Marketplace Product ID
     * @param int  $idFeature      Feature Id
     * @param int  $idFeatureValue Marketplace Product Feature Value ID
     * @param bool $customValue    custom value true or false
     * @param int  $idLang         Language ID
     */
    public static function addProductCustomFeatureToMp($idFeatureValue = false, $customValue = false, $idLang = false)
    {
        Db::getInstance()->insert(
            'wk_mp_product_feature_value_lang',
            array(
                'ps_id_feature_value' => 0,
                'mp_id_feature_value' => (int) $idFeatureValue,
                'id_lang' => (int) $idLang,
                'value' => pSQL($customValue),
            )
        );
    }

    /**
     * Update Product feature value in marketplace if already exist.
     *
     * @param int $psIDFeatureValue Prestashop Id feature value
     * @param int $idMpProduct      Marketplace Product ID
     * @param int $mpIDFeatureValue Marketplace Id feature value
     *
     * @return bool
     */
    public static function updateProductFeatureToMp($psIDFeatureValue, $idMpProduct, $mpIDFeatureValue)
    {
        Db::getInstance()->update(
            'wk_mp_product_feature',
            array('ps_id_feature_value' => (int) $psIDFeatureValue),
            'mp_id_feature_value ='.(int) $mpIDFeatureValue.' AND `mp_id_product` = '.(int) $idMpProduct
        );

        Db::getInstance()->update(
            'wk_mp_product_feature_value_lang',
            array('ps_id_feature_value' => (int) $psIDFeatureValue),
            'mp_id_feature_value ='.(int) $mpIDFeatureValue
        );
    }

    /**
     * Adding Product feature value into marketplace table.
     *
     * @param int $idMpProduct      Marketplace Product ID
     * @param int $idFeature        Feature ID
     * @param int $psIdFeatureValue Prestashop Featur Value ID
     * @param int $mpIdFeatureValue Marketplace Feature Value ID
     */
    public static function addProductFeatureToMp($idMpProduct, $idFeature, $psIdFeatureValue, $mpIdFeatureValue)
    {
        if (_PS_VERSION_ >= '1.7.3.0') {
            // If prestashop version is greater than and equal to V1.7.3.0 then we manage multifeature functionality
            // Means - One feature id can have multiple values
            $featureExist = self::checkProductMultiFeature(
                $idMpProduct,
                $idFeature,
                $psIdFeatureValue,
                $mpIdFeatureValue
            );
        } else {
            // If prestashop version is lest than V1.7.3.0 then we manage single feature functionality
            // Means - One feature id can have only one value
            $featureExist = self::checkProductFeature(
                $idMpProduct,
                $idFeature
            );
        }

        if ($featureExist) {
            self::deleteProductCustomFeature($idMpProduct, $idFeature, false);
        } else {
            Db::getInstance()->insert(
                'wk_mp_product_feature',
                array(
                    'mp_id_product' => (int) $idMpProduct,
                    'ps_id_feature' => (int) $idFeature,
                    'ps_id_feature_value' => (int) $psIdFeatureValue,
                    'mp_id_feature_value' => (int) $mpIdFeatureValue,
                )
            );
        }
    }

    /**
     * If PS_VERSION < 1.7.3.0 THEN Check duplicate feature ID
     *
     * @param int $idMpProduct Marketplace Product ID
     * @param int $idFeature   Prestashop Feature ID
     *
     * @return array
     */
    public static function checkProductFeature($idMpProduct, $idFeature)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_product_feature`
            WHERE `mp_id_product` = '.(int) $idMpProduct.' AND `ps_id_feature` = '.(int) $idFeature
        );
    }

    /**
     * If PS_VERSION >= 1.7.3.0 THEN Check duplicate feature value with same feature Id
     *
     * @param int $idMpProduct Marketplace Product ID
     * @param int $idFeature   Prestashop Feature ID
     * @param int $psIdFeatureValue   Prestashop Feature Value
     * @param int $mpIdFeatureValue   Marketplace Feature Value
     *
     * @return array
     */
    public static function checkProductMultiFeature($idMpProduct, $idFeature, $psIdFeatureValue, $mpIdFeatureValue)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_product_feature`
            WHERE `mp_id_product` = '.(int) $idMpProduct.'
            AND `ps_id_feature` = '.(int) $idFeature.'
            AND `ps_id_feature_value` = '.(int) $psIdFeatureValue.'
            AND `mp_id_feature_value` = '.(int) $mpIdFeatureValue
        );
    }

    /**
     * Getting custom values from the marketplace table.
     *
     * @param int $idMpFeatureValue Marketplace Feature Value ID
     *
     * @return array/bool
     */
    public static function getMpFeatureValue($idMpFeatureValue)
    {
        return Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_product_feature_value_lang`
            WHERE `mp_id_feature_value` ='.(int) $idMpFeatureValue
        );
    }

    /**
     * Deleting product features from Marketplace and Prestashop.
     *
     * @param int  $idMpProduct   Marketplace Product ID
     * @param int  $idFeature     Prestashop Feature ID
     * @param bool $mpFeatures
     *
     * @return bool
     */
    public static function deleteProductCustomFeature($idMpProduct, $idFeature, $mpFeatures = true)
    {
        $idCustomFeatureVal = self::getMpProductFeatureByMpIdProduct($idMpProduct, $idFeature);
        if ($idCustomFeatureVal) {
            Db::getInstance()->delete(
                'wk_mp_product_feature_value',
                '`mp_id_feature_value` = '.(int) $idCustomFeatureVal['mp_id_feature_value']
            );

            Db::getInstance()->delete(
                'wk_mp_product_feature_value_lang',
                '`mp_id_feature_value` = '.(int) $idCustomFeatureVal['mp_id_feature_value']
            );

            if ($mpFeatures) {
                Db::getInstance()->delete(
                    'wk_mp_product_feature',
                    '`ps_id_feature` = '.(int) $idFeature.' AND `mp_id_product` = '.(int) $idMpProduct
                );
            }
        }
    }

    /**
     * Adding custom values created by seller into prestashop table.
     *
     * @param int  $idValue Feature Value ID
     * @param int  $lang    ID Language
     * @param bool $customVal    Is it custom value or not 1/0
     */
    public static function addFeaturesCustomToPS($idValue, $lang, $customVal)
    {
        $featureValData = array(
            'id_feature_value' => (int) $idValue,
            'id_lang' => (int) $lang,
            'value' => pSQL($customVal)
        );
        Db::getInstance()->insert('feature_value_lang', $featureValData);

        return (int) Db::getInstance()->Insert_ID();
    }

    /**
     * Adding Marketplace feature into prestashop.
     *
     * @param int  $idPsProduct Prestashop Product ID
     * @param int  $idFeature   Prestashop feature ID
     * @param int  $idValue     Feature Value ID
     * @param bool $customVal        true/false
     */
    public static function addFeaturesToPS($idPsProduct, $idFeature, $idValue, $customVal = 0)
    {
        if ($customVal) {
            $featureValData = array(
                'id_feature' => (int) $idFeature,
                'custom' => 1
            );
            Db::getInstance()->insert('feature_value', $featureValData);
            $idValue = (int) Db::getInstance()->Insert_ID();
        }

        $featureValData = array(
            'id_feature' => (int) $idFeature,
            'id_product' => (int) $idPsProduct,
            'id_feature_value' => (int) $idValue
        );

        if (_PS_VERSION_ >= '1.7.3.0') {
            // If prestashop version is greater than and equal to V1.7.3.0 then we manage multifeature functionality
            // Means - One feature id can have multiple values
            $psFeatureExist = self::checkPsProductMultiFeature($idFeature, $idPsProduct, $idValue);
        } else {
            // If prestashop version is lest than V1.7.3.0 then we manage single feature functionality
            // Means - One feature id can have only one value
            $psFeatureExist = self::checkPsProductFeature($idFeature, $idPsProduct);
        }

        if (!$psFeatureExist) {
            Db::getInstance()->insert('feature_product', $featureValData);
        }

        SpecificPriceRule::applyAllRules(array((int) $idPsProduct));
        if ($idValue) {
            return ($idValue);
        }
    }

    /**
     * If PS_VERSION >= 1.7.3.0 THEN Check duplicate prestashop feature id for particular product
     *
     * @param int $idFeature      Prestashop feature ID
     * @param int $idPsProduct    Prestashop Product ID
     *
     * @return bool
     */
    public static function checkPsProductFeature($idFeature, $idPsProduct)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'feature_product`
            WHERE `id_product` = '.(int) $idPsProduct.'
            AND `id_feature` ='.(int) $idFeature
        );
    }

    /**
     * If PS_VERSION >= 1.7.3.0 THEN Check duplicate prestashop feature value with same feature Id.
     *
     * @param int $idFeature      Prestashop feature ID
     * @param int $idPsProduct    Prestashop Product ID
     * @param int $idFeatureValue    Prestashop feature value
     *
     * @return bool
     */
    public static function checkPsProductMultiFeature($idFeature, $idPsProduct, $idFeatureValue)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM '._DB_PREFIX_.'feature_product
            WHERE `id_product` = '.(int) $idPsProduct.'
            AND `id_feature` ='.(int) $idFeature.'
            AND `id_feature_value` ='.(int) $idFeatureValue
        );
    }

    /**
     * Assigning Product Feature On Smarty template.
     *
     * @param int $idProduct Seller ID Product
     *
     * @return bool
     */
    public static function assignProductFeature($idProduct)
    {
        $features = self::getMpProductFeatureByMpIdProduct($idProduct);
        $mpValueArr = array();
        if (!empty($features)) {
            foreach ($features as $key => $value) {
                $features[$key]['field_value_option'] = FeatureValue::getFeatureValuesWithLang(
                    Context::getContext()->language->id,
                    (int) $value['ps_id_feature']
                );
                $mpFeatureValues = self::getMpFeatureValue($value['mp_id_feature_value']);
                if ($mpFeatureValues) {
                    foreach ($mpFeatureValues as $mpvalue) {
                        $mpValueArr[$mpvalue['id_lang']] = $mpvalue;
                    }

                    $features[$key]['mp_field_value'] = $mpValueArr;
                }
            }
            Context::getContext()->smarty->assign('productfeature', $features);
        }
    }

    /**
     * Adding Product feature into prestashop.
     *
     * @param int $mpIdProduct Marketplace Product ID
     * @param int $defaultLang Seller Default Language
     *
     * @return bool
     */
    public static function processProductFeature($mpIdProduct, $defaultLang, $from = false)
    {
        if (Configuration::get('WK_MP_PRODUCT_FEATURE') || $from == 'admin') {
            $wkFeatureRow = Tools::getValue('wk_feature_row');
            for ($i = 1; $i <= $wkFeatureRow; $i++) {
                $idFeature = Tools::getValue('wk_mp_feature_'.$i);
                $psIdFeatureValue = Tools::getValue('wk_mp_feature_val_'.$i);
                if ($idFeature) {
                    $checkValue = self::getFeatureValue($idFeature);
                    $customValue = trim(Tools::getValue('wk_mp_feature_custom_'.$defaultLang.'_'.$i));
                    if ($checkValue) {
                        //Pre-defined value priority is highen than custom value
                        if ($psIdFeatureValue) {
                            //if pre-defined value is selected then save that value
                            self::addProductFeatureToMp($mpIdProduct, $idFeature, $psIdFeatureValue, 0);
                        } elseif ($customValue) {
                            //if predefined is not selected and custom value is given
                            $idCustomFeature = self::createProductCustomFeatureToMp($idFeature, 1, $i, $defaultLang);
                            self::addProductFeatureToMp($mpIdProduct, $idFeature, 0, $idCustomFeature);
                        }
                    } else {
                        if ($customValue) {
                            //if predefined is not selected and custom value is given
                            $idCustomFeature = self::createProductCustomFeatureToMp($idFeature, 1, $i, $defaultLang);
                            self::addProductFeatureToMp($mpIdProduct, $idFeature, 0, $idCustomFeature);
                        }
                    }
                }
            }
        }
    }

    /**
     * Deleting Product features from marketplace and prestashop.
     *
     * @param int $idMpProduct Marketplace Product ID
     *
     * @return bool
     */
    public static function deleteProductFeature($idMpProduct)
    {
        $assignedFeatured = self::getMpProductFeatureByMpIdProduct($idMpProduct);
        if ($assignedFeatured) {
            foreach ($assignedFeatured as $mpfeature) {
                self::deleteProductCustomFeature($idMpProduct, $mpfeature['ps_id_feature']);
            }
        }
    }

    /**
     * Processing product features to add them into prestashop.
     *
     * @param int $idMpProduct Marketplace Product ID
     * @param int $idPsProduct Prestashop Product ID
     *
     * @return bool
     */
    public static function processProductFeatureToPS($idMpProduct, $idPsProduct)
    {
        //Delete PS product feature
        $psProductFeatures = Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'feature_product`
            WHERE `id_product` ='.(int) $idPsProduct
        );
        if ($psProductFeatures) {
            foreach ($psProductFeatures as $featureValue) {
                Db::getInstance()->delete(
                    'feature_product',
                    '`id_feature` = '.(int) $featureValue['id_feature'].' AND `id_product` = '.(int) $idPsProduct
                );

                $featureValue = self::getFeatureValues($featureValue['id_feature_value']);
                if (!empty($featureValue) && $featureValue['custom']) {
                    Db::getInstance()->delete(
                        'feature_value',
                        '`id_feature_value` = '.(int) $featureValue['id_feature_value']
                    );

                    Db::getInstance()->delete(
                        'feature_value_lang',
                        '`id_feature_value` = '.(int) $featureValue['id_feature_value']
                    );
                }
            }
        }

        $features = self::getMpProductFeatureByMpIdProduct($idMpProduct);
        if ($features) {
            foreach ($features as $value) {
                if (!$value['ps_id_feature_value'] && $value['mp_id_feature_value']) { // custom feature
                    $mpFeatures = self::getMpFeatureValue($value['mp_id_feature_value']);
                    if ($mpFeatures) {
                        $idValue = self::addFeaturesToPS(
                            $idPsProduct,
                            $value['ps_id_feature'],
                            0,
                            1
                        );
                        self::updateProductFeatureToMp($idValue, $idMpProduct, $value['mp_id_feature_value']);
                        foreach ($mpFeatures as $mpvalues) {
                            self::addFeaturesCustomToPS(
                                $idValue,
                                (int) $mpvalues['id_lang'],
                                $mpvalues['value']
                            );
                        }
                    }
                } elseif ($value['ps_id_feature_value']) {
                    $idValue = self::addFeaturesToPS(
                        $idPsProduct,
                        $value['ps_id_feature'],
                        $value['ps_id_feature_value'],
                        0
                    );
                }
            }
        }
    }

    /**
     * Validating All the features and their values.
     *
     * @param int $params Array of features and their values
     *
     * @return int
     */
    public static function checkFeatures($params)
    {
        $className = 'WkMpProductFeature';
        $objMp = new Marketplace();
        $data = array('status' => 'ok');
        if (!isset($params['default_lang'])) {
            $params['default_lang'] = $params['seller_default_lang'];
        }
        $defaultLang = WkMpHelper::getDefaultLanguageBeforeFormSave($params['default_lang']);
        $wkFeatureRow = $params['wk_feature_row'];
        $rules = call_user_func(array('FeatureValue', 'getValidationRules'), 'FeatureValue');
        if ($wkFeatureRow) {
            for ($i = 1; $i <= $wkFeatureRow; ++$i) {
                $idFeature = isset($params['wk_mp_feature_'.$i]) ? $params['wk_mp_feature_'.$i] : false;
                $psIdFeatureValue = isset($params['wk_mp_feature_val_'.$i]) ? $params['wk_mp_feature_val_'.$i] : false;
                if ($idFeature) {
                    $predefinedValue = self::getFeatureValue($idFeature);
                    $customValue = isset($params['wk_mp_feature_custom_'.$defaultLang.'_'.$i]) ? $params['wk_mp_feature_custom_'.$defaultLang.'_'.$i] : false;

                    if ($predefinedValue) {
                        if (!$psIdFeatureValue && !$customValue) {
                            $sellerLang = Language::getLanguage((int) $defaultLang);
                            $data = array(
                                'status' => 'ko',
                                'tab' => 'wk-feature',
                                'multilang' => '0',
                                'inputName' => 'wk_mp_feature_val',
                                'msg' => sprintf($objMp->l('Feature value is required in %s', $className), $sellerLang['name'])
                            );
                            die(Tools::jsonEncode($data));
                        } else {
                            if ($customValue) {
                                self::checkCustomFeatureValue($params, $rules, $i, $objMp, $className);
                            } elseif (!$psIdFeatureValue) {
                                $data = array(
                                    'status' => 'ko',
                                    'tab' => 'wk-feature',
                                    'multilang' => '0',
                                    'inputName' => 'wk_mp_feature_val',
                                    'msg' => $objMp->l('Feature value is not valid', $className)
                                );
                                die(Tools::jsonEncode($data));
                            }
                        }
                    } else {
                        if ($customValue) {
                            self::checkCustomFeatureValue($params, $rules, $i, $objMp, $className);
                        } else {
                            $sellerLang = Language::getLanguage((int) $defaultLang);
                            $data = array(
                                'status' => 'ko',
                                'tab' => 'wk-feature',
                                'multilang' => '0',
                                'inputName' => 'wk_mp_feature_val',
                                'msg' => sprintf($objMp->l('Feature value is required in %s', $className), $sellerLang['name'])
                            );
                            die(Tools::jsonEncode($data));
                        }
                    }
                }
            }
        }
        die(Tools::jsonEncode($data));
    }

    /**
     * Validating all the custom features values with language wise.
     *
     * @param int   $params Array containing all the information of product features
     * @param array $rules  Rules for product features definded by prestashop
     * @param int   $i      Iteration loop value
     *
     * @return int
     */
    public static function checkCustomFeatureValue($params, $rules, $i, $objMp, $className)
    {
        foreach (Language::getLanguages(false) as $language) {
            $customIdLang = $language['id_lang'];
            if (!isset($params['wk_mp_feature_custom_'.$language['id_lang'].'_'.$i])) {
                $customIdLang = $params['default_lang'];
            }
            $customValue = trim($params['wk_mp_feature_custom_'.$customIdLang.'_'.$i]);
            if (Tools::strlen($customValue) > $rules['sizeLang']['value']) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-feature',
                    'multilang' => '0',
                    'inputName' => 'wkmp_feature_custom',
                    'msg' => $objMp->l('Feature value is too long', $className)
                );
                die(Tools::jsonEncode($data));
            } elseif (!call_user_func(array('Validate', $rules['validateLang']['value']), $customValue)) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-feature',
                    'multilang' => '0',
                    'inputName' => 'wk_mp_feature_val',
                    'msg' => $objMp->l('Feature value is not valid', $className)
                );
                die(Tools::jsonEncode($data));
            }
        }
    }

    public static function assignProductCustomFeatureToMp($idFeature, $idFeatureValue, $isCustom = false)
    {
        Db::getInstance()->insert(
            'wk_mp_product_feature_value',
            array('ps_id_feature' => (int) $idFeature,
                'is_custom' => (int) $isCustom
            )
        );
        $idCustomFeature = (int) Db::getInstance()->Insert_ID();

        foreach (Language::getLanguages(false) as $language) {
            $customValue = self::getPsCustomValueByIdLang($language['id_lang'], $idFeatureValue);
            self::addProductCustomFeatureToMp($idCustomFeature, $customValue, $language['id_lang']);
        }

        return $idCustomFeature;
    }

    public static function getPsCustomValueByIdLang($idLang, $idFeatureValue)
    {
        return Db::getInstance()->getValue(
            'SELECT `value` FROM `'._DB_PREFIX_.'feature_value_lang`
            WHERE id_lang = '.(int) $idLang.'
            AND `id_feature_value` = '.(int) $idFeatureValue
        );
    }

    public static function assignPsProductFeatureToMp($idProduct, $idMpProduct)
    {
        $product = new Product($idProduct);
        $features = $product->getFeatures();
        if ($features) {
            foreach ($features as $feature) {
                if ($feature['custom']) {
                    $idCustomFeature = self::assignProductCustomFeatureToMp($feature['id_feature'], $feature['id_feature_value'], 1);
                    self::addProductFeatureToMp($idMpProduct, $feature['id_feature'], $feature['id_feature_value'], $idCustomFeature);
                } else {
                    self::addProductFeatureToMp($idMpProduct, $feature['id_feature'], $feature['id_feature_value'], 0);
                }
            }
        }
    }

    /**
     * [addPSLayeredIndexableFeature this function is used to insert data "layered_indexable_feature"
     * table of PS used in createfeatureprocess.php].
     *
     * @param [type] $data [description]
     */
    public static function addPSLayeredIndexableFeature($data)
    {
        Db::getInstance()->insert('layered_indexable_feature', $data);
    }

    /**
     * [ifFeatureAssigned description].
     *
     * @param [type] $idFeature [description]
     *
     * @return [type] TRUE  =>  if attribute is used for any product , FALSE =>  if not used
     */
    public static function ifFeatureAssigned($idFeature)
    {
        $result = Db::getInstance()->getValue(
            'SELECT * FROM `'._DB_PREFIX_.'feature_product`
            WHERE `id_feature` ='.(int) $idFeature
        );
        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * [ifFeatureValueAssigned description].
     *
     * @param [type] $idValue [description]
     *
     * @return [type] [true-> if attribute value is used for any product, false -> if not used ]
     */
    public static function ifFeatureValueAssigned($idValue)
    {
        $result = Db::getInstance()->getValue(
            'SELECT * FROM `'._DB_PREFIX_.'feature_product`
            WHERE `id_feature_value` ='.(int) $idValue
        );
        if (!$result) {
            return false;
        }

        return true;
    }

    public static function copyMpProductFeatures($originalMpProductId, $duplicateMpProductId)
    {
        $wkresult = true;
        $productFeatures = self::getMpProductFeatureByMpIdProduct($originalMpProductId);
        if ($productFeatures) {
            foreach ($productFeatures as $feature) {
                $newIdFeatureValue = 0;
                if ($feature['mp_id_feature_value']) { //If feature has mp_id_feature_value
                    $featureValue = Db::getInstance()->getRow(
                        'SELECT * FROM `'._DB_PREFIX_.'wk_mp_product_feature_value`
                        WHERE `mp_id_feature_value` = '.(int) $feature['mp_id_feature_value']
                    );
                    // Custom feature value, need to duplicate it
                    if ($featureValue['is_custom']) {
                        Db::getInstance()->insert(
                            'wk_mp_product_feature_value',
                            array('ps_id_feature' => (int) $feature['ps_id_feature'],
                                'is_custom' => (int) $featureValue['is_custom']
                            )
                        );
                        $newIdFeatureValue = (int) Db::getInstance()->Insert_ID();
                        if ($newIdFeatureValue) {
                            foreach (Language::getIDs(false) as $idLang) {
                                $featureValueLang = Db::getInstance()->getRow(
                                    'SELECT * FROM `'._DB_PREFIX_.'wk_mp_product_feature_value_lang`
                                    WHERE `mp_id_feature_value` = '.(int) $featureValue['mp_id_feature_value'].'
                                    AND `id_lang` = '.(int) $idLang
                                );
                                if ($featureValueLang) {
                                    self::addProductCustomFeatureToMp(
                                        $newIdFeatureValue,
                                        $featureValueLang['value'],
                                        $idLang
                                    );
                                }
                            }
                            $feature['mp_id_feature_value'] = $newIdFeatureValue;
                        }
                    }
                }
                $wkresult = Db::getInstance()->insert(
                    'wk_mp_product_feature',
                    array(
                        'mp_id_product' => (int) $duplicateMpProductId,
                        'ps_id_feature' => (int) $feature['ps_id_feature'],
                        'ps_id_feature_value' => (int) $feature['ps_id_feature_value'],
                        'mp_id_feature_value' => (int) $newIdFeatureValue,
                    )
                );
            }
        }

        return $wkresult;
    }
}
