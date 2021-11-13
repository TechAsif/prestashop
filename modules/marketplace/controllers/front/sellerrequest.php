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

class MarketplaceSellerRequestModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $smartyVars = array();
        if (isset($this->context->customer->id)) {
            if (Module::isEnabled('mpsellerstaff')) {
                //If customer is a staff then restrict Staff to use seller request page
                WkMpSellerStaff::overrideMpSellerCustomerId($this->context->customer->id);
            }

            if ($mpSeller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id)) {
                $smartyVars['is_seller'] = $mpSeller['active'];

                // check if seller product exist and seller is active then redirect to dashboard page
                $mpProduct = WkMpSellerProduct::getSellerProduct($mpSeller['id_seller']);
                if ($mpProduct && $mpSeller['active']) {
                    Tools::redirect($this->context->link->getModulelink('marketplace', 'dashboard'));
                }
            }

            if (Configuration::get('WK_MP_TERMS_AND_CONDITIONS_STATUS')) {
                //Display CMS page link
                if (Configuration::get('WK_MP_TERMS_AND_CONDITIONS_CMS')) {
                    $objCMS = new CMS(Configuration::get('WK_MP_TERMS_AND_CONDITIONS_CMS'), $this->context->language->id);

                    $linkCmsPageContent = $this->context->link->getCMSLink($objCMS, $objCMS->link_rewrite, Configuration::get('PS_SSL_ENABLED'));
                    if (!strpos($linkCmsPageContent, '?')) {
                        $linkCmsPageContent .= '?content_only=1';
                    } else {
                        $linkCmsPageContent .= '&content_only=1';
                    }
                    $smartyVars['linkCmsPageContent'] = $linkCmsPageContent;
                }
            }

            $smartyVars['terms_and_condition_active'] = Configuration::get('WK_MP_TERMS_AND_CONDITIONS_STATUS');
            $smartyVars['max_phone_digit'] = Configuration::get('WK_MP_PHONE_DIGIT');

            $customer = new Customer($this->context->customer->id);
            $smartyVars['customer_firstname'] = $customer->firstname;
            $smartyVars['customer_lastname'] = $customer->lastname;
            $smartyVars['customer_email'] = $customer->email;

            if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                $this->context->smarty->assign('allow_multilang', 1);
                $currentLang = $this->context->language->id;
            } else {
                $this->context->smarty->assign('allow_multilang', 0);
                if (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '1') {//Admin default lang
                    $currentLang = Configuration::get('PS_LANG_DEFAULT');
                } elseif (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '2') {//Seller default lang
                    $currentLang = $this->context->language->id;
                }
            }
            $objLang = new Language((int) $currentLang);
            if (!$objLang->active) {
                $currentLang = Configuration::get('PS_LANG_DEFAULT');
            }

            $this->context->smarty->assign($smartyVars);
            $this->context->smarty->assign(array(
                'id_module' => $this->module->id,
                'myaccount' => $this->context->link->getPageLink('my-account', true),
                'modules_dir' => _MODULE_DIR_,
                'static_token' => Tools::getToken(false),
                'ps_img_dir' => _PS_IMG_.'l/',
                'country' => Country::getCountries($this->context->language->id, true),
                'seller_country_need' => Configuration::get('WK_MP_SELLER_COUNTRY_NEED'),
                'tax_identification_number' => Configuration::get('WK_MP_SELLER_TAX_IDENTIFICATION_NUMBER'),
                'languages' => Language::getLanguages(),
                'total_languages' => count(Language::getLanguages()),
                'current_lang' => Language::getLanguage((int) $currentLang),
                'context_language' => $this->context->language->id,
            ));
            $this->jsDefVars();
            $this->setTemplate('module:marketplace/views/templates/front/seller/sellerrequest.tpl');
        } else {
            Tools::redirect('index.php?controller=authentication&back='.urlencode($this->context->link->getModuleLink('marketplace', 'sellerrequest')));
        }

        parent::initContent();
    }

    public function jsDefVars()
    {
        $jsDef = array(
            'id_country' => 0,
            'id_state' => 0,
            'iso' => $this->context->language->iso_code,
            'multi_lang' => Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE'),
            'mp_tinymce_path' => _MODULE_DIR_.$this->module->name.'/libs',
            'img_module_dir' => _MODULE_DIR_.$this->module->name.'/views/img/',
            'path_sellerdetails' => $this->context->link->getModuleLink('marketplace', 'sellerrequest'),
            'seller_country_need' => Configuration::get('WK_MP_SELLER_COUNTRY_NEED'),
            'terms_and_condition_active' => Configuration::get('WK_MP_TERMS_AND_CONDITIONS_STATUS'),
            'selectstate' => $this->module->l('Select State', 'sellerrequest'),
            'req_shop_name_lang' => $this->module->l('Shop name is required in Default Language -', 'sellerrequest'),
            'shop_name_exist_msg' => $this->module->l('Shop Unique name already taken. Try another.', 'sellerrequest'),
            'shop_name_error_msg' => $this->module->l('Shop name can not contain any special character except underscore. Try another.', 'sellerrequest'),
            'seller_email_exist_msg' => $this->module->l('Email Id already exist.', 'sellerrequest'),
        );

        Media::addJsDef($jsDef);
    }

    public function postProcess()
    {
        if (Tools::isSubmit('sellerRequest')) {
            $shopNameUnique = trim(Tools::getValue('shop_name_unique'));
            $sellerFirstName = trim(Tools::getValue('seller_firstname'));
            $sellerLastName = trim(Tools::getValue('seller_lastname'));
            $sellerPhone = trim(Tools::getValue('wk_phone'));
            $businessEmail = trim(Tools::getValue('business_email'));

            if (Tools::getValue('postcode')) {
                $sellerPostalCode = trim(Tools::getValue('postcode'));
            } else {
                $sellerPostalCode = '';
            }

            if (Tools::getValue('city')) {
                $sellerCity = trim(Tools::getValue('city'));
            } else {
                $sellerCity = '';
            }

            if (Tools::getValue('id_country')) {
                $sellerCountryId = Tools::getValue('id_country');
            } else {
                $sellerCountryId = 0;
            }

            if (Tools::getValue('id_state')) {
                $sellerStateId = Tools::getValue('id_state');
            } else {
                $sellerStateId = 0;
            }

            if (Tools::getValue('tax_identification_number')) {
                $taxIdentificationNumber = Tools::getValue('tax_identification_number');
            } else {
                $taxIdentificationNumber = '';
            }

            if (Configuration::get('WK_MP_SELLER_ADMIN_APPROVE') == 0) {
                $active = 1;
            } else {
                $active = 0;
            }
            //If multi-lang is OFF then PS default lang will be default lang for seller
            if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                $defaultLang = Tools::getValue('default_lang');
            } else {
                if (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '1') {
                    $defaultLang = Configuration::get('PS_LANG_DEFAULT');
                } elseif (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '2') {
                    $defaultLang = Tools::getValue('current_lang_id');
                }
            }

            $objLang = new Language((int) $defaultLang);
            if (!$objLang->active) {
                $defaultLang = Configuration::get('PS_LANG_DEFAULT');
            }

            $shopName = trim(Tools::getValue('shop_name_'.$defaultLang));

            $this->validateSellerRegistrationForm($defaultLang);

            Hook::exec('actionBeforeAddSeller', array('id_customer' => $this->context->customer->id));

            //Saving seller details
            if (empty($this->errors)) {
                $objMpSeller = new WkMpSeller();
                $objMpSeller->shop_name_unique = $shopNameUnique;
                $objMpSeller->link_rewrite = Tools::link_rewrite($shopNameUnique);
                $objMpSeller->seller_firstname = $sellerFirstName;
                $objMpSeller->seller_lastname = $sellerLastName;
                $objMpSeller->business_email = $businessEmail;
                $objMpSeller->phone = $sellerPhone;
                $objMpSeller->postcode = $sellerPostalCode;
                $objMpSeller->city = $sellerCity;
                $objMpSeller->id_country = $sellerCountryId;
                $objMpSeller->id_state = $sellerStateId;
                $objMpSeller->tax_identification_number = $taxIdentificationNumber;
                $objMpSeller->default_lang = Tools::getValue('default_lang');
                $objMpSeller->active = $active;
                $objMpSeller->shop_approved = $active;
                $objMpSeller->seller_customer_id = $this->context->customer->id;

                if (Configuration::get('WK_MP_SHOW_SELLER_DETAILS')) {
                    //display all seller details for new seller
                    $objMpSeller->seller_details_access = Configuration::get('WK_MP_SELLER_DETAILS_ACCESS');
                }

                foreach (Language::getLanguages(false) as $language) {
                    $shopIdLang = $language['id_lang'];

                    if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                        //if shop name in other language is not available then fill with seller language same for others
                        if (!Tools::getValue('shop_name_'.$language['id_lang'])) {
                            $shopIdLang = $defaultLang;
                        }
                    } else {
                        //if multilang is OFF then all fields will be filled as default lang content
                        $shopIdLang = $defaultLang;
                    }

                    $objMpSeller->shop_name[$language['id_lang']] = Tools::getValue('shop_name_'.$shopIdLang);
                }

                $objMpSeller->save();
                $idSeller = $objMpSeller->id;
                if ($idSeller) {
                    //If seller default active approval is ON then mail to seller of account activation
                    if ($objMpSeller->active) {
                        WkMpSeller::sendMail($idSeller, 1, 1);
                    }

                    //If mpsellerstaff module is installed but currently disabled and current customer was a staff then delete this customer as staff from mpsellerstaff module table. Because a customer can not be a seller and a staff both in same time.
                    if (Module::isInstalled('mpsellerstaff') && !Module::isEnabled('mpsellerstaff')) {
                        WkMpSeller::deleteStaffDataIfBecomeSeller($objMpSeller->seller_customer_id);
                    }

                    if (Configuration::get('WK_MP_MAIL_ADMIN_SELLER_REQUEST')) {
                        //Mail to Admin on seller request
                        $sellerName = $sellerFirstName.' '.$sellerLastName;
                        $objMpSeller->mailToAdminWhenSellerRequest($sellerName, $shopName, $businessEmail, $sellerPhone);
                    }

                    Hook::exec('actionAfterAddSeller', array('id_seller' => $idSeller));

                    Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
                } else {
                    $this->errors[] = $this->module->l('Something wrong while creating seller.', 'sellerrequest');
                }
            }
        }
    }

    public function validateSellerRegistrationForm($defaultLang)
    {
        $shopNameUnique = trim(Tools::getValue('shop_name_unique'));
        $sellerFirstName = trim(Tools::getValue('seller_firstname'));
        $sellerLastName = trim(Tools::getValue('seller_lastname'));
        $sellerPhone = trim(Tools::getValue('wk_phone'));
        $businessEmail = trim(Tools::getValue('business_email'));
        $shopName = trim(Tools::getValue('shop_name_'.$defaultLang));
        $sellerLang = Language::getLanguage((int) $defaultLang);

        if (!$shopNameUnique) {
            $this->errors[] = $this->module->l('Unique name for shop is required field.', 'sellerrequest');
        } elseif (!Validate::isCatalogName($shopNameUnique) || !Tools::link_rewrite($shopNameUnique)) {
            $this->errors[] = $this->module->l('Invalid Unique name for shop', 'sellerrequest');
        } elseif (WkMpSeller::isShopNameExist(Tools::link_rewrite($shopNameUnique))) {
            $this->errors[] = $this->module->l('Unique name for shop is already taken. Try another.', 'sellerrequest');
        }

        if (!$shopName) {
            if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                $this->errors[] = sprintf($this->module->l('Shop name is required in %s', 'sellerrequest'), $sellerLang['name']);
            } else {
                $this->errors[] = $this->module->l('Shop name is required', 'sellerrequest');
            }
        }

        //Validate data
        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            $languageName = '';
            if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                $languageName = '('.$language['name'].')';
            }
            if (Tools::getValue('shop_name_'.$language['id_lang'])) {
                if (!Validate::isCatalogName(Tools::getValue('shop_name_'.$language['id_lang']))) {
                    $this->errors[] = sprintf($this->module->l('Shop name field %s is invalid.', 'sellerrequest'), $languageName);
                }
            }
        }

        if (!$sellerFirstName) {
            $this->errors[] = $this->module->l('Seller first name is required field.', 'sellerrequest');
        } elseif (!Validate::isName($sellerFirstName)) {
            $this->errors[] = $this->module->l('Invalid seller first name.', 'sellerrequest');
        }

        if (!$sellerLastName) {
            $this->errors[] = $this->module->l('Seller last name is required field.', 'sellerrequest');
        } elseif (!Validate::isName($sellerLastName)) {
            $this->errors[] = $this->module->l('Invalid seller last name.', 'sellerrequest');
        }

        if (!$sellerPhone) {
            $this->errors[] = $this->module->l('Phone is required field.', 'sellerrequest');
        } elseif (!Validate::isPhoneNumber($sellerPhone)) {
            $this->errors[] = $this->module->l('Invalid phone number.', 'sellerrequest');
        }

        if (!$businessEmail) {
            $this->errors[] = $this->module->l('Email ID is required field.', 'sellerrequest');
        } elseif (!Validate::isEmail($businessEmail)) {
            $this->errors[] = $this->module->l('Invalid Email ID.', 'sellerrequest');
        } elseif (WkMpSeller::isSellerEmailExist($businessEmail)) {
            $this->errors[] = $this->module->l('Email ID already exist.', 'sellerrequest');
        }

        if (Configuration::get('WK_MP_SELLER_TAX_IDENTIFICATION_NUMBER')) {
            $TINnumber = Tools::getValue('tax_identification_number');
            if ($TINnumber && !Validate::isGenericName($TINnumber)) {
                $this->errors[] = $this->module->l('Tax Identification Number must be valid.', 'sellerrequest');
            }
        }

        if (Configuration::get('WK_MP_SELLER_COUNTRY_NEED')) {
            $postcode = Tools::getValue('postcode');
            $countryNeedZipCode = true;
            $countryZipCodeFormat = false;
            if (Tools::getValue('id_country')) {
                $country = new Country(Tools::getValue('id_country'));
                $countryNeedZipCode = $country->need_zip_code;
                $countryZipCodeFormat = $country->zip_code_format;
            }

            if (!$postcode && $countryNeedZipCode) {
                $this->errors[] = $this->module->l('Zip/Postal Code is required field.', 'sellerrequest');
            } elseif ($countryZipCodeFormat) {
                if (!$country->checkZipCode($postcode)) {
                    $this->errors[] = sprintf($this->module->l('The Zip/Postal code you\'ve entered is invalid. It must follow this format: %s'), str_replace('C', $country->iso_code, str_replace('N', '0', str_replace('L', 'A', $countryZipCodeFormat))));
                }
            } elseif (!Validate::isPostCode($postcode)) {
                $this->errors[] = $this->module->l('Invalid Zip/Postal code', 'sellerrequest');
            }

            $sellerCity = Tools::getValue('city');
            if (!$sellerCity) {
                $this->errors[] = $this->module->l('City is required field.', 'sellerrequest');
            } elseif (!Validate::isName($sellerCity)) {
                $this->errors[] = $this->module->l('Invalid city name.', 'sellerrequest');
            }

            if (!Tools::getValue('id_country')) {
                $this->errors[] = $this->module->l('Country is required field.', 'sellerrequest');
            }

            //if state available in selected country
            if (Tools::getValue('state_available')) {
                if (!Tools::getValue('id_state')) {
                    $this->errors[] = $this->module->l('State is required field.', 'sellerrequest');
                }
            }
        }

        if (Configuration::get('WK_MP_TERMS_AND_CONDITIONS_STATUS') && !Tools::getValue('terms_and_conditions')) {
            $this->errors[] = $this->module->l('Please agree the terms and condition.', 'sellerrequest');
        }
    }

    public function displayAjaxCheckUniqueShopName()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        //check unique shop name and compare to other existing shop name unique
        WkMpSeller::validateSellerUniqueShopName();
    }

    public function displayAjaxCheckUniqueSellerEmail()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        //check seller email and compare to other existing seller email
        WkMpSeller::validateSellerEmail();
    }

    public function displayAjaxCheckZipCodeByCountry()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }

        //Display zip code field on the basis of country
        $countryNeedZipCode = true;
        if (Tools::getValue('id_country')) {
            $country = new Country(Tools::getValue('id_country'));
            $countryNeedZipCode = $country->need_zip_code;
        }

        if ($countryNeedZipCode) {
            die('1');
        } else {
            die('0');
        }
    }

    public function displayAjaxGetSellerState()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        //Get state by choosing country
        WkMpSeller::displayStateByCountryId();
    }

    public function displayAjaxValidateMpSellerForm()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }

        $params = array();
        parse_str(Tools::getValue('formData'), $params);
        if (!empty($params)) {
            WkMpSeller::validationSellerFormField($params);
        } else {
            die('1');
        }
    }

    public function getBreadcrumbLinks()
    {
        $mpURL = 'javascript:void(0)';
        if (isset($this->context->customer->id)) {
            if ($mpSeller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id)) {
                if ($mpSeller['active']) {
                    $mpURL = $this->context->link->getModuleLink('marketplace', 'dashboard');
                }
            }
        }

        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'sellerrequest'),
            'url' => $mpURL
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Seller Request', 'sellerrequest'),
            'url' => 'javascript:void(0)'
        );
        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->registerStylesheet('mp-marketplace_account', 'modules/'.$this->module->name.'/views/css/marketplace_account.css');
        $this->registerStylesheet('mp-marketplace_global', 'modules/'.$this->module->name.'/views/css/mp_global_style.css');

        $this->registerJavascript('mp-mp_form_validation', 'modules/'.$this->module->name.'/views/js/mp_form_validation.js');
        $this->registerJavascript('mp-change_multilang', 'modules/'.$this->module->name.'/views/js/change_multilang.js');
        $this->registerJavascript('mp-getstate', 'modules/'.$this->module->name.'/views/js/getstate.js');
    }
}
