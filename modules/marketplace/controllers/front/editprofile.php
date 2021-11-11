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

class MarketplaceEditProfileModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $objMpSeller = new WkMpSeller();

        if ($this->context->customer->isLogged()) {
            $smartyVar = array();
            $seller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            if ($seller) {
                $idSeller = $seller['id_seller'];

                if (Tools::getValue('deactivate') == 1 && Configuration::get('WK_MP_SELLER_SHOP_SETTINGS')) {
                    //De-activate seller's shop
                    $objSeller = new WkMpSeller($idSeller);
                    $objSeller->active = 0;
                    $objSeller->save();

                    //deactive seller all products
                    WkMpSeller::changeSellerProductStatus($idSeller, 0);

                    Hook::exec('actionToogleSellerStatus', array('id_seller' => $idSeller, 'is_seller' => $objSeller->shop_approved, 'status' => $objSeller->active));

                    Tools::redirect($this->context->link->getPageLink('my-account'));
                } elseif (Tools::getValue('reactivate') == 1 && Configuration::get('WK_MP_SELLER_SHOP_SETTINGS')) {
                    //Re-activate seller's shop
                    $objSeller = new WkMpSeller($idSeller);
                    $objSeller->active = 1;
                    $objSeller->save();

                    //activate or deactive seller all products according to last status
                    WkMpSeller::changeSellerProductStatus($idSeller, false, 1);

                    Hook::exec('actionToogleSellerStatus', array('id_seller' => $idSeller, 'is_seller' => $objSeller->shop_approved, 'status' => $objSeller->active));

                    Tools::redirect($this->context->link->getModuleLink('marketplace', 'dashboard'));
                }

                if ($seller['active']) {
                    $smartyVar['logic'] = 2;

                    $mpSellerLang = $objMpSeller->getSellerShopLang($idSeller);
                    if ($mpSellerLang) {
                        foreach ($mpSellerLang as $sellerLang) {
                            $seller['shop_name'][$sellerLang['id_lang']] = $sellerLang['shop_name'];
                            $seller['about_shop'][$sellerLang['id_lang']] = $sellerLang['about_shop'];
                        }
                    }

                    if (Tools::getValue('updated')) {
                        $smartyVar['updated'] = 1;
                    }

                    //Check if seller image exist
                    $sellerImagePath = WkMpSeller::getSellerImageLink($seller);
                    if ($sellerImagePath) {
                        $this->context->smarty->assign('seller_img_path', $sellerImagePath);
                    }

                    //Check if seller banner exist
                    $sellerBannerPath = WkMpSeller::getSellerBannerLink($seller);
                    if ($sellerBannerPath) {
                        $this->context->smarty->assign('seller_banner_path', $sellerBannerPath);
                    }

                    //Check if shop image exist
                    $shopImagePath = WkMpSeller::getShopImageLink($seller);
                    if ($shopImagePath) {
                        $this->context->smarty->assign('shop_img_path', $shopImagePath);
                    }

                    //Check if shop banner exist
                    $shopBannerPath = WkMpSeller::getShopBannerLink($seller);
                    if ($shopBannerPath) {
                        $this->context->smarty->assign('shop_banner_path', $shopBannerPath);
                    }

                    $this->context->smarty->assign($smartyVar);

                    // Set default lang at every form according to configuration multi-language
                    WkMpHelper::assignDefaultLang($idSeller);

                    //Settings (Permission) for seller to display selected details of seller
                    $selectedDetailsByAdmin = array();
                    $sellerDetailsAccess = Tools::jsonDecode(Configuration::get('WK_MP_SELLER_DETAILS_ACCESS'));
                    if ($sellerDetailsAccess) {
                        $objMarketplace = new Marketplace();
                        if ($objMarketplace->sellerDetailsView) {
                            foreach ($objMarketplace->sellerDetailsView as $sellerDetailsVal) {
                                if ($sellerDetailsAccess && in_array($sellerDetailsVal['id_group'], $sellerDetailsAccess)) {
                                    $selectedDetailsByAdmin[] = array(
                                        'id_group' => $sellerDetailsVal['id_group'],
                                        'name' => $sellerDetailsVal['name']
                                    );
                                }
                            }
                        }
                    }

                    if ($seller['seller_details_access']) {
                        $this->context->smarty->assign('selectedDetailsBySeller', Tools::jsonDecode($seller['seller_details_access']));
                    }

                    //Check selected country need zip code or not
                    $countryNeedZipCode = true;
                    if ($seller['id_country']) {
                        $country = new Country($seller['id_country']);
                        $countryNeedZipCode = $country->need_zip_code;
                    }
                    $this->context->smarty->assign('country_need_zipcode', $countryNeedZipCode);

                    //Show admin details to seller
                    if (Configuration::get('WK_MP_SHOW_ADMIN_DETAILS')) {
                        if (Configuration::get('WK_MP_SUPERADMIN_EMAIL')) {
                            $adminContactEmail = Configuration::get('WK_MP_SUPERADMIN_EMAIL');
                        } else {
                            $idEmployee = WkMpHelper::getSupperAdmin();
                            $employee = new Employee($idEmployee);
                            $adminContactEmail = $employee->email;
                        }

                        $this->context->smarty->assign('adminContactEmail', $adminContactEmail);
                    }

                    $this->context->smarty->assign(array(
                        'mp_seller_info' => $seller,
                        'active_tab' => Tools::getValue('tab'),
                        'static_token' => Tools::getToken(false),
                        'selectedDetailsByAdmin' => $selectedDetailsByAdmin,
                        'country' => Country::getCountries($this->context->language->id, true),
                        'seller_country_need' => Configuration::get('WK_MP_SELLER_COUNTRY_NEED'),
                        'link' => $this->context->link,
                        'mpSellerShopSettings' => Configuration::get('WK_MP_SELLER_SHOP_SETTINGS'),
                        'timestamp' => WkMpHelper::getTimestamp(),
                        'shop_default_img_path' => _MODULE_DIR_.$this->module->name.'/views/img/shop_img/defaultshopimage.jpg',
                        'seller_default_img_path' => _MODULE_DIR_.$this->module->name.'/views/img/seller_img/defaultimage.jpg',
                        'no_image_path' => _MODULE_DIR_.$this->module->name.'/views/img/home-default.jpg',
                        'ps_img_dir' => _PS_IMG_.'l/',
                        'max_phone_digit' => Configuration::get('WK_MP_PHONE_DIGIT'),
                        'marketplace_address' => trim($seller['address']),
                    ));

                    Media::addJsDef(array(
                        'id_country' => $seller['id_country'],
                        'id_state' => $seller['id_state'],
                    ));

                    $this->defineJSVars($idSeller);
                    $this->setTemplate('module:marketplace/views/templates/front/seller/editprofile.tpl');
                } else {
                    Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
                }
            } else {
                $this->redirectMyAccount();
            }
        } else {
            $this->redirectMyAccount();
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('updateProfile')) {
            if (isset($this->context->customer->id)) {
                $seller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
                if ($seller && $seller['active']) {
                    $idSeller = $seller['id_seller'];

                    $shopNameUnique = Tools::getValue('shop_name_unique');
                    $sellerFirstName = trim(Tools::getValue('seller_firstname'));
                    $sellerLastName = trim(Tools::getValue('seller_lastname'));
                    $businessEmail = Tools::getValue('business_email');
                    $phone = Tools::getValue('wk_phone');
                    $sellerDefaultLanguage = Tools::getValue('default_lang');
                    $defaultLang = WkMpHelper::getDefaultLanguageBeforeFormSave($sellerDefaultLanguage);

                    // Validate data
                    $this->validateSellerForm($defaultLang, $idSeller);

                    Hook::exec('actionBeforeUpdateSeller', array('id_seller' => $idSeller));

                    if (empty($this->errors)) {
                        //update seller details
                        $objSeller = new WkMpSeller($idSeller);
                        $objSeller->shop_name_unique = $shopNameUnique;
                        $objSeller->link_rewrite = Tools::link_rewrite($shopNameUnique);
                        $objSeller->seller_firstname = $sellerFirstName;
                        $objSeller->seller_lastname = $sellerLastName;
                        $objSeller->business_email = $businessEmail;
                        $objSeller->phone = $phone;

                        if (Configuration::get('WK_MP_SELLER_FAX')) {
                            $objSeller->fax = Tools::getValue('fax');
                        }

                        if (Configuration::get('WK_MP_SELLER_TAX_IDENTIFICATION_NUMBER')) {
                            $objSeller->tax_identification_number = trim(Tools::getValue('tax_identification_number'));
                        }

                        if (Configuration::get('WK_MP_SELLER_COUNTRY_NEED')) {
                            $objSeller->postcode = Tools::getValue('postcode');
                            $objSeller->city = Tools::getValue('city');
                            $objSeller->id_country = Tools::getValue('id_country');
                            $objSeller->id_state = Tools::getValue('id_state');
                        }

                        $objSeller->default_lang = Tools::getValue('default_lang');

                        //If admin allow for social profile tab
                        if (Configuration::get('WK_MP_SOCIAL_TABS') && Configuration::get('WK_MP_SELLER_FACEBOOK')) {
                            $objSeller->facebook_id = trim(Tools::getValue('facebook_id'));
                        }

                        if (Configuration::get('WK_MP_SOCIAL_TABS') && Configuration::get('WK_MP_SELLER_TWITTER')) {
                            $objSeller->twitter_id = trim(Tools::getValue('twitter_id'));
                        }

                        if (Configuration::get('WK_MP_SOCIAL_TABS') && Configuration::get('WK_MP_SELLER_YOUTUBE')) {
                            $objSeller->youtube_id = trim(Tools::getValue('youtube_id'));
                        }

                        if (Configuration::get('WK_MP_SOCIAL_TABS') && Configuration::get('WK_MP_SELLER_INSTAGRAM')) {
                            $objSeller->instagram_id = trim(Tools::getValue('instagram_id'));
                        }

                        if (Configuration::get('WK_MP_SHOW_SELLER_DETAILS')
                            && Configuration::get('WK_MP_SELLER_DETAILS_PERMISSION')
                            ) {
                            if (Tools::getValue('seller_details_access')) {
                                $objSeller->seller_details_access = Tools::jsonEncode(Tools::getValue('seller_details_access'));
                            } else {
                                $objSeller->seller_details_access = '';
                            }
                        }

                        foreach (Language::getLanguages(false) as $language) {
                            $shopLangId = $language['id_lang'];
                            $aboutShopLangId = $language['id_lang'];

                            if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                                //if shop name in other language is not available then fill with seller language same for others
                                if (!Tools::getValue('shop_name_'.$language['id_lang'])) {
                                    $shopLangId = $defaultLang;
                                }
                                if (!Tools::getValue('about_shop_'.$language['id_lang'])) {
                                    $aboutShopLangId = $defaultLang;
                                }
                            } else {
                                //if multilang is OFF then all fields will be filled as default lang content
                                $shopLangId = $defaultLang;
                                $aboutShopLangId = $defaultLang;
                            }

                            $objSeller->shop_name[$language['id_lang']] = Tools::getValue('shop_name_'.$shopLangId);

                            $objSeller->about_shop[$language['id_lang']] = Tools::getValue('about_shop_'.$aboutShopLangId);
                        }
                        $objSeller->address = Tools::getValue('address');
                        $objSeller->save();

                        //update seller details in seller order table
                        WkMpSellerOrder::updateSellerDetailsInOrder(
                            $this->context->customer->id,
                            $shopNameUnique,
                            $sellerFirstName,
                            $sellerLastName,
                            $businessEmail
                        );

                        Hook::exec('actionAfterUpdateSeller', array('id_seller' => $idSeller));
                        Tools::redirect($this->context->link->getModuleLink('marketplace', 'editprofile', array('updated' => 1, 'tab' => Tools::getValue('active_tab'))));
                    }
                } else {
                    Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
                }
            } else {
                Tools::redirect($this->context->link->getPageLink('my-account'));
            }
        }
    }

    public function validateSellerForm($defaultLang, $idSeller)
    {
        $shopNameUnique = Tools::getValue('shop_name_unique');
        $sellerFirstName = trim(Tools::getValue('seller_firstname'));
        $sellerLastName = trim(Tools::getValue('seller_lastname'));
        $businessEmail = Tools::getValue('business_email');
        $phone = Tools::getValue('wk_phone');
        $shopName = trim(Tools::getValue('shop_name_'.$defaultLang));
        $sellerLang = Language::getLanguage((int) $defaultLang);

        if ($shopNameUnique == '') {
            $this->errors[] = $this->module->l('Unique name for shop is required field.', 'editprofile');
        } elseif (!Validate::isCatalogName($shopNameUnique) || !Tools::link_rewrite($shopNameUnique)) {
            $this->errors[] = $this->module->l('Invalid Unique name for shop');
        } elseif (WkMpSeller::isShopNameExist(Tools::link_rewrite($shopNameUnique), $idSeller)) {
            $this->errors[] = $this->module->l('Unique name for shop is already taken. Try another.', 'editprofile');
        }

        if ($shopName == '') {
            if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                $this->errors[] = sprintf($this->module->l('Shop name is required in %s', 'editprofile'), $sellerLang['name']);
            } else {
                $this->errors[] = $this->module->l('Shop name is required', 'editprofile');
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
                    $this->errors[] = sprintf($this->module->l('Shop name field %s is invalid.', 'editprofile'), $languageName);
                }
            }
            if (Tools::getValue('about_shop_'.$language['id_lang'])) {
                if (!Validate::isCleanHtml(Tools::getValue('about_shop_'.$language['id_lang']), (int) Configuration::get('PS_ALLOW_HTML_IFRAME'))) {
                    $this->errors[] = sprintf($this->module->l('Shop description field %s is invalid.', 'editprofile'), $languageName);
                }
            }
        }

        if (!$sellerFirstName) {
            $this->errors[] = $this->module->l('Seller first name is required field.', 'editprofile');
        } elseif (!Validate::isName($sellerFirstName)) {
            $this->errors[] = $this->module->l('Invalid seller first name.', 'editprofile');
        }

        if (!$sellerLastName) {
            $this->errors[] = $this->module->l('Seller last name is required field.', 'editprofile');
        } elseif (!Validate::isName($sellerLastName)) {
            $this->errors[] = $this->module->l('Invalid seller last name.', 'editprofile');
        }

        if ($phone == '') {
            $this->errors[] = $this->module->l('Phone is required field.', 'editprofile');
        } elseif (!Validate::isPhoneNumber($phone)) {
            $this->errors[] = $this->module->l('Invalid phone number.', 'editprofile');
        }

        if ($businessEmail == '') {
            $this->errors[] = $this->module->l('Email ID is required field.', 'editprofile');
        } elseif (!Validate::isEmail($businessEmail)) {
            $this->errors[] = $this->module->l('Invalid Email ID.', 'editprofile');
        } elseif (WkMpSeller::isSellerEmailExist($businessEmail, $idSeller)) {
            $this->errors[] = $this->module->l('Email ID already exist.', 'editprofile');
        }

        if (Configuration::get('WK_MP_SELLER_FAX')) {
            $fax = Tools::getValue('fax');
            if ($fax && !Validate::isPhoneNumber($fax)) {
                $this->errors[] = $this->module->l('Fax must be numeric.', 'editprofile');
            }
        }

        if (Configuration::get('WK_MP_SELLER_TAX_IDENTIFICATION_NUMBER')) {
            $TINnumber = Tools::getValue('tax_identification_number');
            if ($TINnumber && !Validate::isGenericName($TINnumber)) {
                $this->errors[] = $this->module->l('Tax Identification Number must be valid.', 'editprofile');
            }
        }

        $address = Tools::getValue('address');
        if ($address && !Validate::isAddress($address)) {
            $this->errors[] = $this->module->l('Address format is invalid.', 'editprofile');
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
                $this->errors[] = $this->module->l('Zip/Postal Code is required field.', 'editprofile');
            } elseif ($countryZipCodeFormat) {
                if (!$country->checkZipCode($postcode)) {
                    $this->errors[] = sprintf($this->module->l('The Zip/Postal code you\'ve entered is invalid. It must follow this format: %s'), str_replace('C', $country->iso_code, str_replace('N', '0', str_replace('L', 'A', $countryZipCodeFormat))));
                }
            } elseif (!Validate::isPostCode($postcode)) {
                $this->errors[] = $this->module->l('Invalid Zip/Postal code', 'editprofile');
            }

            $sellerCity = Tools::getValue('city');
            if (!$sellerCity) {
                $this->errors[] = $this->module->l('City is required field.', 'editprofile');
            } elseif (!Validate::isName($sellerCity)) {
                $this->errors[] = $this->module->l('Invalid city name.', 'editprofile');
            }

            if (!Tools::getValue('id_country')) {
                $this->errors[] = $this->module->l('Country is required field.', 'editprofile');
            }

            //if state available in selected country
            if (Tools::getValue('state_available')) {
                if (!Tools::getValue('id_state')) {
                    $this->errors[] = $this->module->l('State is required field.', 'editprofile');
                }
            }
        }

        if (Configuration::get('WK_MP_SOCIAL_TABS') && Configuration::get('WK_MP_SELLER_FACEBOOK')) {
            $facebookId = Tools::getValue('facebook_id');
            if ($facebookId && !Validate::isGenericName($facebookId)) {
                $this->errors[] = $this->module->l('Facebook Id is invalid.', 'editprofile');
            }
        }

        if (Configuration::get('WK_MP_SOCIAL_TABS') && Configuration::get('WK_MP_SELLER_TWITTER')) {
            $twitterId = Tools::getValue('twitter_id');
            if ($twitterId && !Validate::isGenericName($twitterId)) {
                $this->errors[] = $this->module->l('Twitter Id is invalid.', 'editprofile');
            }
        }

        if (Configuration::get('WK_MP_SOCIAL_TABS') && Configuration::get('WK_MP_SELLER_YOUTUBE')) {
            $youtubeId = Tools::getValue('youtube_id');
            if ($youtubeId && !Validate::isGenericName($youtubeId)) {
                $this->errors[] = $this->module->l('Youtube Id is invalid.', 'editprofile');
            }
        }

        if (Configuration::get('WK_MP_SOCIAL_TABS') && Configuration::get('WK_MP_SELLER_INSTAGRAM')) {
            $instagramId = Tools::getValue('instagram_id');
            if ($instagramId && !Validate::isGenericName($instagramId)) {
                $this->errors[] = $this->module->l('Instagram Id is invalid.', 'editprofile');
            }
        }
    }

    public function defineJSVars($mpIdSeller)
    {
        $jsDef = array(
            'terms_and_condition_active' => 0,
            'actionIdForUpload' => $mpIdSeller,
            'actionpage' => 'seller',
            'deleteaction' => '',
            'adminupload' => 0,
            'upload_single' => 1, //assigned in 'jquery.filer.js' for differenciate seller page and product page
            'seller_country_need' => Configuration::get('WK_MP_SELLER_COUNTRY_NEED'),
            'multi_lang' => Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE'),
            'iso' => $this->context->language->iso_code,
            'path_sellerdetails' => $this->context->link->getModuleLink('marketplace', 'editprofile'),
            'path_uploader' => $this->context->link->getModulelink('marketplace', 'uploadimage'),
            'seller_default_img_path' => _MODULE_DIR_.$this->module->name.'/views/img/seller_img/defaultimage.jpg',
            'shop_default_img_path' => _MODULE_DIR_.$this->module->name.'/views/img/shop_img/defaultshopimage.jpg',
            'no_image_path' => _MODULE_DIR_.$this->module->name.'/views/img/home-default.jpg',
            'mp_tinymce_path' => _MODULE_DIR_.$this->module->name.'/libs',
            'img_module_dir' => _MODULE_DIR_.$this->module->name.'/views/img/',
            'req_shop_name_lang' => $this->module->l('Shop name is required in Default Language -', 'editprofile'),
            'shop_name_exist_msg' => $this->module->l('Shop Unique name already taken. Try another.', 'editprofile'),
            'shop_name_error_msg' => $this->module->l('Shop name can not contain any special character except underscore. Try another.', 'editprofile'),
            'seller_email_exist_msg' => $this->module->l('Email Id already exist.', 'editprofile'),
            'confirm_deactivate_msg' => $this->module->l('Are you sure you want to deactivate your shop?', 'editprofile'),
            'selectstate' => $this->module->l('Select State', 'editprofile'),
            'drag_drop' => $this->module->l('Drag & Drop to Upload', 'editprofile'),
            'or' => $this->module->l('or', 'editprofile'),
            'pick_img' => $this->module->l('Pick Image', 'editprofile'),
            'choosefile' => $this->module->l('Choose Images', 'editprofile'),
            'choosefiletoupload' => $this->module->l('Choose Images To Upload', 'editprofile'),
            'imagechoosen' => $this->module->l('Images were chosen', 'editprofile'),
            'dragdropupload' => $this->module->l('Drop file here to Upload', 'editprofile'),
            'confirm_delete_msg' => $this->module->l('Are you sure you want to delete this image?', 'editprofile'),
            'only' => $this->module->l('Only', 'editprofile'),
            'imagesallowed' => $this->module->l('Images are allowed to be uploaded.', 'editprofile'),
            'onlyimagesallowed' => $this->module->l('Only Images are allowed to be uploaded.', 'editprofile'),
            'imagetoolarge' => $this->module->l('is too large! Please upload image up to', 'editprofile'),
            'imagetoolargeall' => $this->module->l('Images you have choosed are too large! Please upload images up to', 'editprofile'),
            'notmorethanone' => $this->module->l('You can not upload more than one image.', 'editprofile'),
            'success_msg' => $this->module->l('Success', 'editprofile'),
            'error_msg' => $this->module->l('Error', 'editprofile'),
        );

        Media::addJsDef($jsDef);
    }

    public function redirectMyAccount()
    {
        Tools::redirect('index.php?controller=authentication&back='.urlencode($this->context->link->getModuleLink('marketplace', 'editprofile')));
    }

    public function displayAjaxDeleteSellerImage()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        //delete seller images
        WkMpSeller::deleteSellerImages();
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
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'editprofile'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard')
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Edit Profile', 'editprofile'),
            'url' => ''
        );
        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->addJqueryUI('ui.datepicker');

        $this->registerStylesheet('mp-marketplace_account', 'modules/'.$this->module->name.'/views/css/marketplace_account.css');
        $this->registerStylesheet('mp_global_style-css', 'modules/'.$this->module->name.'/views/css/mp_global_style.css');

        $this->registerJavascript('mp-mp_form_validation', 'modules/'.$this->module->name.'/views/js/mp_form_validation.js');
        $this->registerJavascript('mp-change_multilang', 'modules/'.$this->module->name.'/views/js/change_multilang.js');
        $this->registerJavascript('mp-getstate', 'modules/'.$this->module->name.'/views/js/getstate.js');

        //Upload images
        $this->registerStylesheet('mp-filer-css', 'modules/'.$this->module->name.'/views/css/uploadimage-css/jquery.filer.css');
        $this->registerStylesheet('mp-filer-dragdropbox-theme-css', 'modules/'.$this->module->name.'/views/css/uploadimage-css/jquery.filer-dragdropbox-theme.css');
        $this->registerStylesheet('mp-uploadphoto-css', 'modules/'.$this->module->name.'/views/css/uploadimage-css/uploadphoto.css');
        $this->registerJavascript('mp-filer-js', 'modules/'.$this->module->name.'/views/js/uploadimage-js/jquery.filer.js');
        $this->registerJavascript('mp-uploadimage-js', 'modules/'.$this->module->name.'/views/js/uploadimage-js/uploadimage.js');
        $this->registerJavascript('mp-imageedit', 'modules/'.$this->module->name.'/views/js/imageedit.js');
    }
}
