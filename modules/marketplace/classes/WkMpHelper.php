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

class WkMpHelper extends ObjectModel
{
    /**
     * Get random name
     *
     * @param  integer $length length of the string
     * @return string
     */
    public static function randomImageName($length = 6)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $rand = '';

        for ($i = 0; $i < $length; ++$i) {
            $rand = $rand.$characters[mt_rand(0, Tools::strlen($characters) - 1)];
        }

        return $rand;
    }

    /**
     * Upload Seller Product Images or any other images by using this function
     *
     * @param string $dir  Path where to upload
     * @param float  $width  Image width
     * @param float  $height Image height
     *
     * @return bool/int [error/success image id]
     */
    public static function uploadMpImages($image, $dirAbsPath, $width = false, $height = false)
    {
        if (!$image) {
            return false;
        }

        if ($image['error']) {
            return $image['error'];
        }

        if (!$width) {
            $width = 200;
        }

        if (!$height) {
            $height = 200;
        }

        if (!ImageManager::isCorrectImageFileExt($image['name'])) {
            return 2;
        }

        return ImageManager::resize($image['tmp_name'], $dirAbsPath, $width, $height);
    }

    /**
     * Ureate with new row with default lang's value when admin add new language
     *
     * @param  int $newIdLang New Language ID
     * @param  string $lang_tables Table names
     * @return boolean
     */
    public static function updateIdLangInLangTables($newIdLang, $langTables, $primaryKey = false)
    {
        if ($langTables) {
            foreach ($langTables as $tables) {
                if ($primaryKey) {
                    $id = $primaryKey;
                } else {
                    if ($tables == 'wk_mp_seller') {
                        $id = 'id_seller';
                    } elseif ($tables == 'wk_mp_seller_product') {
                        $id = 'id_mp_product';
                    } else {
                        $id = 'id';
                    }
                }
                $tableIds = Db::getInstance()->executeS('SELECT '.$id.' FROM `'._DB_PREFIX_.$tables.'`');
                if ($tableIds) {
                    foreach ($tableIds as $tableId) {
                        $tableLangs = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.$tables.'_lang`
                            WHERE '.$id.' = '.pSQL($tableId[$id]).'
                            AND `id_lang` = '.(int) Configuration::get('PS_LANG_DEFAULT'));

                        if ($tableLangs) {
                            $tableValue = '';
                            foreach ($tableLangs as $key => $value) {
                                if ($key == $id) {
                                    $tableValue = "'".$value."'";
                                } elseif ($key == 'id_lang') {
                                    $tableValue = $tableValue.', '."'".$newIdLang."'";
                                } else {
                                    $content = str_replace("'", "\'", $value);
                                    $tableValue = $tableValue.', '."'".$content."'";
                                }
                            }
                        }

                        Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.$tables.'_lang` VALUES ('.$tableValue.')');
                    }
                }
            }
        }
    }

    /**
     * Set default lang at every form of module according to configuration multi-lang
     *
     * @param  int $idSeller Seller ID
     * @return boolean
     */
    public static function assignDefaultLang($idSeller)
    {
        if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
            Context::getContext()->smarty->assign('allow_multilang', 1);
            $curruntLang = WkMpSeller::getSellerDefaultLanguage($idSeller);
        } else {
            Context::getContext()->smarty->assign('allow_multilang', 0);

            if (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '1') {
                $curruntLang = Configuration::get('PS_LANG_DEFAULT');
            } elseif (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '2') {
                $curruntLang = WkMpSeller::getSellerDefaultLanguage($idSeller);
            }
        }
        $objLang = new Language((int) $curruntLang);
        if (!$objLang->active) {
            $curruntLang = Configuration::get('PS_LANG_DEFAULT');
        }
        if ($idSeller) {
            $mpSeller = new WkMpSeller($idSeller);
            $sellerDefaultLang = $mpSeller->default_lang;
            $objLang = new Language((int) $sellerDefaultLang);
            if (!$objLang->active) {
                $sellerDefaultLang = Configuration::get('PS_LANG_DEFAULT');
            }
            Context::getContext()->smarty->assign('default_lang', $sellerDefaultLang);
        }

        // assign image max size limit
        WkMpHelper::assignPsFileMaxSize();

        if (_PS_VERSION_ >= '1.7.3.0') {
            //Prestashop added this feature in PS V1.7.3.0 and above
            Context::getContext()->smarty->assign('deliveryTimeAllowed', 1);
        }

        Context::getContext()->smarty->assign('languages', Language::getLanguages());
        Context::getContext()->smarty->assign('total_languages', count(Language::getLanguages()));
        Context::getContext()->smarty->assign('current_lang', Language::getLanguage((int) $curruntLang));
        Context::getContext()->smarty->assign('multi_lang', Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE'));
        Context::getContext()->smarty->assign('multi_def_lang_off', Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG'));
    }

    /**
     * Assign Prestashop Default Max Size Length For Images and Files to be uploaded
     *
     * @return boolean Assignement
     */
    public static function assignPsFileMaxSize()
    {
        $objUploader = new Uploader();
        $psUploaderSize = $objUploader->getPostMaxSizeBytes();

        Context::getContext()->smarty->assign('psUploaderSize', $psUploaderSize);
        Context::getContext()->smarty->assign('post_max_size', ini_get('post_max_size'));
    }

    /**
     * Get Super Admin Of Prestashop
     *
     * @return int Super Admin Employee ID
     */
    public static function getSupperAdmin()
    {
        $data = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'employee` ORDER BY `id_employee`');
        if ($data) {
            foreach ($data as $emp) {
                $employee = new Employee($emp['id_employee']);
                if ($employee->isSuperAdmin()) {
                    return $emp['id_employee'];
                }
            }
        }

        return false;
    }

    /**
     * To avoid caching of image
     *
     * @return int Timestamp
     */
    public static function getTimestamp()
    {
        $date = new DateTime();
        return $date->getTimestamp();
    }

    /**
    * Get Seller Default Language from form according to config settings when seller add product or update product
    *
    * @param int $sellerDefaultLanguage seller current default language
    * @return int
    */
    public static function getDefaultLanguageBeforeFormSave($sellerDefaultLanguage)
    {
        //If multi-lang is OFF then PS default lang will be default lang for seller
        if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
            $defaultLang = $sellerDefaultLanguage;
        } else {
            if (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '1') {//Admin default lang
                $defaultLang = Configuration::get('PS_LANG_DEFAULT');
            } elseif (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '2') {//Seller default lang
                $defaultLang = $sellerDefaultLanguage;
            }
        }

        $objLang = new Language((int) $defaultLang);
        if (!$objLang->active) {
            $defaultLang = Configuration::get('PS_LANG_DEFAULT');
        }

        return $defaultLang;
    }

    /**
    * Assign global static variable on tpl
    *
    * @return assign
    */
    public static function assignGlobalVariables()
    {
        $objProduct = new Product();
        $context = Context::getContext();
        $context->smarty->assign(array(
            'mp_image_dir' => _MODULE_DIR_.'marketplace/views/img/',
            'module_dir' => _MODULE_DIR_,
            'img_ps_dir' => _PS_IMG_DIR_,
            'id_customer' => $context->customer->id,
            'link' => $context->link,
            'logged' => $context->customer->isLogged(),
            'title_text_color' => Configuration::get('WK_MP_TITLE_TEXT_COLOR'),
            'title_bg_color' => Configuration::get('WK_MP_TITLE_BG_COLOR'),
            'defaultTaxRuleGroup' => $objProduct->getIdTaxRulesGroup(),
            'ps_img_dir' => _PS_IMG_.'l/',
        ));
    }

    /**
    * Define global js variable on js file
    *
    * @return defined
    */
    public static function defineGlobalJSVariables()
    {
        $context = Context::getContext();
        $jsVars = array(
                    'mp_image_dir' => _MODULE_DIR_.'marketplace/views/img/',
                    'module_dir' => _MODULE_DIR_,
                    'img_dir_l' => _PS_IMG_.'l/',
                    'multi_lang' => Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE'),
                    'iso' => $context->language->iso_code,
                    'mp_tinymce_path' => _MODULE_DIR_.'marketplace/libs',
                    'id_lang' => $context->language->id,
                );

        Media::addJsDef($jsVars);
    }

    /**
     * Create a new row with default lang value when admin add new language - used in mp addons

     * @param  [int] $newLangId - new language id
     * @param  [string] $langTables - lang tables

     * @return bool
     */
    public static function insertLangIdinAllTables($newLangId, $langTables)
    {
        $langId = Configuration::get('PS_LANG_DEFAULT');
        if ($langTables) {
            foreach ($langTables as $tables) {
                $tableIdData = Db::getInstance()->executeS('SELECT `id` FROM `'._DB_PREFIX_.$tables.'` ');
                if ($tableIdData) {
                    foreach ($tableIdData as $tabledata) {
                        $tableLangData = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.$tables.'_lang` WHERE `id` = '.$tabledata['id'].' AND `id_lang` = '.(int) $langId);

                        if ($tableLangData) {
                            $tableAllVal = '';
                            foreach ($tableLangData as $table_key => $tableVal) {
                                if ($table_key == 'id') {
                                    $tableAllVal = "'".$tableVal."'";
                                } elseif ($table_key == 'id_lang') {
                                    $tableAllVal = $tableAllVal.', '."'".$newLangId."'";
                                } else {
                                    $content = str_replace("'", "\'", $tableVal);
                                    $tableAllVal = $tableAllVal.', '."'".$content."'";
                                }
                            }
                        }

                        Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.$tables.'_lang` VALUES ('.$tableAllVal.')');
                    }
                }
            }
        }
    }

    public static function setSellerAccessOnly()
    {
        if (!Context::getContext()->customer->id) {
            Tools::redirect(__PS_BASE_URI__.'pagenotfound');
        } else {
            $seller = WkMpSeller::getSellerDetailByCustomerId(Context::getContext()->customer->id);
            if (!$seller) {
                Tools::redirect(__PS_BASE_URI__.'pagenotfound');
            }
        }
    }

    public static function setStaffHook($idCustomer, $controllerName, $relatedId, $action)
    {
        //To manage staff log (changes add/update/delete)
        Hook::exec('actionAfterStaffUpdation', array(
                        'id_customer_staff' => $idCustomer,
                        'controller_name' => $controllerName,
                        'related_id' => $relatedId,
                        'action' => $action,  // 3 for Delete action
                    ));
    }

    public static function productTabPermission()
    {
        $tabData = array('view' => 1, 'add' => 1, 'edit' => 1, 'delete' => 1);
        $permissionData = array(
                                'combinationPermission' => $tabData,
                                'featuresPermission' => $tabData,
                                'shippingPermission' => $tabData,
                                'seoPermission' => $tabData,
                                'optionsPermission' => $tabData,
                            );
        return $permissionData;
    }
}
