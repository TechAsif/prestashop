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

class MarketplaceAddProductModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (isset($this->context->customer->id)) {
            $idCustomer = $this->context->customer->id;
            $permissionData = WkMpHelper::productTabPermission();
            //Override customer id if any staff of seller want to use this controller with permission
            if (Module::isEnabled('mpsellerstaff')) {
                $staffDetails = WkMpSellerStaff::getStaffInfoByIdCustomer($idCustomer);
                if ($staffDetails
                    && $staffDetails['active']
                    && $staffDetails['id_seller']
                    && $staffDetails['seller_status']
                ) {
                    $idStaff = $staffDetails['id_staff'];
                    //Check product sub tab permission
                    $permissionDetails = WkMpSellerStaffPermission::getProductSubTabPermissionData($idStaff);
                    if ($permissionDetails) {
                        $permissionData = $permissionDetails;
                    }
                }

                //Replace staff customer id to seller customer id for using seller panel pages
                $getCustomerId = WkMpSellerStaff::overrideMpSellerCustomerId($idCustomer);
                if ($getCustomerId) {
                    $idCustomer = $getCustomerId;
                }
            }

            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
            if ($mpSeller && $mpSeller['active']) {
                // show admin commission on product base price for seller
                if (Configuration::get('WK_MP_SHOW_ADMIN_COMMISSION')) {
                    $objMpCommission = new WkMpCommission();
                    $adminCommission = $objMpCommission->finalCommissionSummaryForSeller($mpSeller['id_seller']);
                    if ($adminCommission) {
                        $this->context->smarty->assign('admin_commission', $adminCommission);
                    }
                }

                // Set default lang at every form according to configuration multi-language
                WkMpHelper::assignDefaultLang($mpSeller['id_seller']);

                //show tax rule group on add product page
                $taxRuleGroups = TaxRulesGroup::getTaxRulesGroups(true);
                if ($taxRuleGroups && Configuration::get('WK_MP_SELLER_APPLIED_TAX_RULE')) {
                    $this->context->smarty->assign('tax_rules_groups', $taxRuleGroups);
                    $this->context->smarty->assign('mp_seller_applied_tax_rule', 1);
                }

                // Admin Shipping
                $carriers = Carrier::getCarriers($this->context->language->id, true, false, false, null, ALL_CARRIERS);
                $carriersChoices = array();
                if ($carriers) {
                    foreach ($carriers as $carrier) {
                        $carriersChoices[$carrier['id_reference'].' - '.$carrier['name'].' ('.$carrier['delay'].')']
                        = $carrier['id_reference'];
                    }
                }

                $idCategory = array(Category::getRootCategory()->id); //home category id
                $defaultCategory = Category::getCategoryInformations($idCategory, $this->context->language->id);

                $objDefaultCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
                $this->context->smarty->assign(array(
                    'module_dir' => _MODULE_DIR_,
                    'active_tab' => Tools::getValue('tab'),
                    'static_token' => Tools::getToken(false),
                    'default_lang' => $mpSeller['default_lang'],
                    'defaultCategory' => $defaultCategory,
                    'defaultCurrencySign' => $objDefaultCurrency->sign,
                    'logic' => 3,
                    'logged' => $this->context->customer->isLogged(),
                    'carriersChoices' => $carriersChoices,
                    'ps_img_dir' => _PS_IMG_.'l/',
                    'available_features' => Feature::getFeatures($this->context->language->id, (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP)),
                    'permissionData' => $permissionData,
                ));

                $this->defineJSVars();
                $this->setTemplate('module:marketplace/views/templates/front/product/addproduct.tpl');
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
            }
        } else {
            Tools::redirect('index.php?controller=authentication&back='.urlencode($this->context->link->getModuleLink('marketplace', 'addproduct')));
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('SubmitProduct') || Tools::isSubmit('StayProduct')) {
            $objSellerProduct = new WkMpSellerProduct();

            //get data from add product form
            $quantity = Tools::getValue('quantity');

            //save product minimum quantity
            if (Configuration::get('WK_MP_PRODUCT_MIN_QTY')) {
                $minimalQuantity = Tools::getValue('minimal_quantity');
            } else {
                $minimalQuantity = 1; //default value
            }

            //save product condition new, used, refurbished
            if (Configuration::get('WK_MP_PRODUCT_CONDITION')) {
                $showCondition = Tools::getValue('show_condition');
                if (!$showCondition) {
                    $showCondition = 0;
                }
                $condition = Tools::getValue('condition');
            } else {
                $showCondition = 1;
                $condition = 'new';
            }

            //save product price
            $price = Tools::getValue('price');

            //save product wholesale price
            if (Configuration::get('WK_MP_PRODUCT_WHOLESALE_PRICE')) {
                $wholesalePrice = Tools::getValue('wholesale_price');
            } else {
                $wholesalePrice = 0;
            }

            //save product unit price
            if (Configuration::get('WK_MP_PRODUCT_PRICE_PER_UNIT')) {
                $unitPrice = Tools::getValue('unit_price');
                $unity = Tools::getValue('unity');
            } else {
                $unitPrice = 0;
                $unity = '';
            }

            //save product tax rule
            if (Configuration::get('WK_MP_SELLER_APPLIED_TAX_RULE')) {
                $idTaxRulesGroup = Tools::getValue('id_tax_rules_group');
            } else {
                $idTaxRulesGroup = 1;
            }

            // height, width, depth and weight
            $width = Tools::getValue('width');
            $width = empty($width) ? '0' : str_replace(',', '.', $width);

            $height = Tools::getValue('height');
            $height = empty($height) ? '0' : str_replace(',', '.', $height);

            $depth = Tools::getValue('depth');
            $depth = empty($depth) ? '0' : str_replace(',', '.', $depth);

            $weight = Tools::getValue('weight');
            $weight = empty($weight) ? '0' : str_replace(',', '.', $weight);

            // Admin Shipping
            $psIDCarrierReference = Tools::getValue('ps_id_carrier_reference');
            if ($psIDCarrierReference) {
                $psIDCarrierReference = serialize($psIDCarrierReference);
            } else {
                $psIDCarrierReference = 0;  // No Shipping Selected By Admin
            }

            $reference = trim(Tools::getValue('reference'));
            $ean13JanBarcode = trim(Tools::getValue('ean13'));
            $upcBarcode = trim(Tools::getValue('upc'));
            $isbn = trim(Tools::getValue('isbn'));

            $defaultCategory = Tools::getValue('default_category');
            $categories = Tools::getValue('product_category');
            $categories = explode(',', $categories);

            $sellerDefaultLanguage = Tools::getValue('default_lang');
            $defaultLang = WkMpHelper::getDefaultLanguageBeforeFormSave($sellerDefaultLanguage);

            if (Configuration::get('WK_MP_SELLER_PRODUCT_VISIBILITY')) {
                //Product Visibility
                $availableForOrder = trim(Tools::getValue('available_for_order'));
                $showPrice = $availableForOrder ? 1 : trim(Tools::getValue('show_price'));
                $onlineOnly = trim(Tools::getValue('online_only'));
                $visibility = trim(Tools::getValue('visibility'));
            }

            if (!Tools::getValue('product_name_'.$defaultLang)) {
                if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                    $sellerLang = Language::getLanguage((int) $defaultLang);
                    $this->errors[] = sprintf($this->module->l('Product name is required in %s', 'addproduct'), $sellerLang['name']);
                } else {
                    $this->errors[] = $this->module->l('Product name is required', 'addproduct');
                }
            } else {
                // Validate form
                $this->errors = WkMpSellerProduct::validateMpProductForm();

                $idCustomer = $this->context->customer->id;
                $permissionData = WkMpHelper::productTabPermission();
                //Override customer id if any staff of seller want to use this controller
                if (Module::isEnabled('mpsellerstaff')) {
                    $staffDetails = WkMpSellerStaff::getStaffInfoByIdCustomer($idCustomer);
                    if ($staffDetails
                        && $staffDetails['active']
                        && $staffDetails['id_seller']
                        && $staffDetails['seller_status']
                    ) {
                        $permissionDetails = WkMpSellerStaffPermission::getProductSubTabPermissionData(
                            $staffDetails['id_staff']
                        );
                        if ($permissionDetails) {
                            $permissionData = $permissionDetails;
                        }
                    }

                    $getCustomerId = WkMpSellerStaff::overrideMpSellerCustomerId($idCustomer);
                    if ($getCustomerId) {
                        $idCustomer = $getCustomerId;
                    }
                }

                $mpSeller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
                $idSeller = $mpSeller['id_seller'];

                Hook::exec('actionBeforeAddMPProduct', array('id_seller' => $idSeller));

                if (empty($this->errors)) {
                    $objSellerProduct->id_seller = $idSeller;
                    $objSellerProduct->quantity = $quantity;
                    $objSellerProduct->minimal_quantity = $minimalQuantity;
                    $objSellerProduct->id_ps_product = 0; // prestashop product id
                    $objSellerProduct->id_category = $defaultCategory;
                    $objSellerProduct->id_ps_shop = $this->context->shop->id;
                    $objSellerProduct->show_condition = $showCondition;
                    $objSellerProduct->condition = $condition;

                    //Low stock alert
                    if (Configuration::get('WK_MP_PRODUCT_LOW_STOCK_ALERT')) {
                        $objSellerProduct->low_stock_threshold = Tools::getValue('low_stock_threshold');
                        if (Tools::getValue('low_stock_alert')) {
                            $objSellerProduct->low_stock_alert = 1;
                        } else {
                            $objSellerProduct->low_stock_alert = 0;
                        }
                    }

                    //Pricing
                    $objSellerProduct->price = $price;
                    $objSellerProduct->wholesale_price = $wholesalePrice;
                    $objSellerProduct->unit_price = $unitPrice; //(Total price divide by unit price)
                    $objSellerProduct->unity = $unity;
                    $objSellerProduct->id_tax_rules_group = $idTaxRulesGroup;

                    if (Configuration::get('WK_MP_PRODUCT_ON_SALE')) {
                        if (Tools::getValue('on_sale')) {
                            $objSellerProduct->on_sale = 1;
                        } else {
                            $objSellerProduct->on_sale = 0;
                        }
                    }

                    if ((Configuration::get('WK_MP_SELLER_ADMIN_SHIPPING') || Module::isEnabled('mpshipping'))
                    && $permissionData['shippingPermission']['add']) {
                        $objSellerProduct->width = $width;
                        $objSellerProduct->height = $height;
                        $objSellerProduct->depth = $depth;
                        $objSellerProduct->weight = $weight;

                        $objSellerProduct->ps_id_carrier_reference = $psIDCarrierReference;

                        if (Configuration::get('WK_MP_PRODUCT_DELIVERY_TIME')) {
                            $objSellerProduct->additional_delivery_times = Tools::getValue('additional_delivery_times');
                        }
                        if (Configuration::get('WK_MP_PRODUCT_ADDITIONAL_FEES')) {
                            $objSellerProduct->additional_shipping_cost = Tools::getValue('additional_shipping_cost');
                        }
                    }

                    if (Configuration::get('WK_MP_SELLER_PRODUCT_REFERENCE')) {
                        $objSellerProduct->reference = $reference;
                    }

                    if ($permissionData['optionsPermission']['add']) {
                        if (Configuration::get('WK_MP_SELLER_PRODUCT_AVAILABILITY')) {
                            $objSellerProduct->out_of_stock = Tools::getValue('out_of_stock');
                            $objSellerProduct->available_date = Tools::getValue('available_date');
                        }
                        if (Configuration::get('WK_MP_SELLER_PRODUCT_EAN')) {
                            $objSellerProduct->ean13 = $ean13JanBarcode;
                        }
                        if (Configuration::get('WK_MP_SELLER_PRODUCT_UPC')) {
                            $objSellerProduct->upc = $upcBarcode;
                        }
                        if (Configuration::get('WK_MP_SELLER_PRODUCT_ISBN')) {
                            $objSellerProduct->isbn = $isbn;
                        }
                    }

                    //control product approval setting
                    if (Configuration::get('WK_MP_PRODUCT_ADMIN_APPROVE')) {
                        $objSellerProduct->active = 0;
                        $objSellerProduct->status_before_deactivate = 0;
                    } else {
                        $objSellerProduct->active = 1;
                        $objSellerProduct->status_before_deactivate = 1;
                        $objSellerProduct->admin_approved = 1;
                    }

                    foreach (Language::getLanguages(false) as $language) {
                        $productIdLang = $language['id_lang'];
                        $shortDescIdLang = $language['id_lang'];
                        $descIdLang = $language['id_lang'];

                        //if product name in other language is not available
                        //then fill with seller language same for others
                        if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                            if (!Tools::getValue('product_name_'.$language['id_lang'])) {
                                $productIdLang = $defaultLang;
                            }
                            if (!Tools::getValue('short_description_'.$language['id_lang'])) {
                                $shortDescIdLang = $defaultLang;
                            }
                            if (!Tools::getValue('description_'.$language['id_lang'])) {
                                $descIdLang = $defaultLang;
                            }
                        } else {
                            //if multilang is OFF then all fields will be filled as default lang content
                            $productIdLang = $defaultLang;
                            $shortDescIdLang = $defaultLang;
                            $descIdLang = $defaultLang;
                        }

                        if (Configuration::get('PS_LANG_DEFAULT') == $language['id_lang']) {
                            $nameDefaultLang = Tools::getValue('product_name_'.$productIdLang);
                        }

                        $objSellerProduct->product_name[$language['id_lang']] = Tools::getValue('product_name_'.$productIdLang);

                        $objSellerProduct->short_description[$language['id_lang']] = Tools::getValue('short_description_'.$shortDescIdLang);

                        $objSellerProduct->description[$language['id_lang']] = Tools::getValue('description_'.$descIdLang);

                        //Product SEO
                        if (Configuration::get('WK_MP_SELLER_PRODUCT_SEO')
                        && $permissionData['seoPermission']['add']) {
                            $metaTitleIdLang = $language['id_lang'];
                            $metaDescriptionIdLang = $language['id_lang'];

                            if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                                if (!Tools::getValue('meta_title_'.$language['id_lang'])) {
                                    $metaTitleIdLang = $defaultLang;
                                }
                                if (!Tools::getValue('meta_description_'.$language['id_lang'])) {
                                    $metaDescriptionIdLang = $defaultLang;
                                }
                            } else {
                                $metaTitleIdLang = $defaultLang;
                                $metaDescriptionIdLang = $defaultLang;
                            }

                            $objSellerProduct->meta_title[$language['id_lang']] = Tools::getValue('meta_title_'.$metaTitleIdLang);

                            $objSellerProduct->meta_description[$language['id_lang']] = Tools::getValue('meta_description_'.$metaDescriptionIdLang);

                            //Friendly URL
                            if (Tools::getValue('link_rewrite_'.$language['id_lang'])) {
                                $objSellerProduct->link_rewrite[$language['id_lang']] = Tools::link_rewrite(Tools::getValue('link_rewrite_'.$language['id_lang']));
                            } else {
                                $objSellerProduct->link_rewrite[$language['id_lang']] = Tools::link_rewrite(Tools::getValue('product_name_'.$productIdLang));
                            }
                        } else {
                            $objSellerProduct->link_rewrite[$language['id_lang']] = Tools::link_rewrite(Tools::getValue('product_name_'.$productIdLang));
                        }

                        //For Avalailiblity Preferences
                        if (Configuration::get('WK_MP_SELLER_PRODUCT_AVAILABILITY')
                        && $permissionData['optionsPermission']['add']) {
                            $availableNowIdLang = $language['id_lang'];
                            $availableLaterIdLang = $language['id_lang'];

                            if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                                if (!Tools::getValue('available_now_'.$language['id_lang'])) {
                                    $availableNowIdLang = $defaultLang;
                                }
                                if (!Tools::getValue('available_later_'.$language['id_lang'])) {
                                    $availableLaterIdLang = $defaultLang;
                                }
                            } else {
                                $availableNowIdLang = $defaultLang;
                                $availableLaterIdLang = $defaultLang;
                            }

                            $objSellerProduct->available_now[$language['id_lang']] = Tools::getValue('available_now_'.$availableNowIdLang);

                            $objSellerProduct->available_later[$language['id_lang']] = Tools::getValue('available_later_'.$availableLaterIdLang);
                        }

                        //Delivery Time
                        if ((Configuration::get('WK_MP_SELLER_ADMIN_SHIPPING') || Module::isEnabled('mpshipping'))
                        && $permissionData['shippingPermission']['add']) {
                            if (Configuration::get('WK_MP_PRODUCT_DELIVERY_TIME')) {
                                $deliveryInStockIdLang = $language['id_lang'];
                                $deliveryOutStockIdLang = $language['id_lang'];

                                if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                                    if (!Tools::getValue('delivery_in_stock_'.$language['id_lang'])) {
                                        $deliveryInStockIdLang = $defaultLang;
                                    }
                                    if (!Tools::getValue('delivery_out_stock_'.$language['id_lang'])) {
                                        $deliveryOutStockIdLang = $defaultLang;
                                    }
                                } else {
                                    $deliveryInStockIdLang = $defaultLang;
                                    $deliveryOutStockIdLang = $defaultLang;
                                }

                                $objSellerProduct->delivery_in_stock[$language['id_lang']] = Tools::getValue('delivery_in_stock_'.$deliveryInStockIdLang);

                                $objSellerProduct->delivery_out_stock[$language['id_lang']] = Tools::getValue('delivery_out_stock_'.$deliveryOutStockIdLang);
                            }
                        }
                    }

                    if (Configuration::get('WK_MP_SELLER_PRODUCT_VISIBILITY')
                    && $permissionData['optionsPermission']['add']) {
                        $objSellerProduct->available_for_order = $availableForOrder;
                        $objSellerProduct->show_price = $showPrice;
                        $objSellerProduct->online_only = $onlineOnly;
                        $objSellerProduct->visibility = $visibility;
                    }

                    $objSellerProduct->save();
                    $mpIdProduct = $objSellerProduct->id;

                    if ($mpIdProduct) {
                        //Add into category table
                        $objMpCategory = new WkMpSellerProductCategory();
                        $objMpCategory->id_seller_product = $mpIdProduct;

                        if ($categories) {
                            //set if more than one category selected
                            foreach ($categories as $pCategory) {
                                $objMpCategory->id_category = $pCategory;
                                if ($pCategory == $defaultCategory) {
                                    $objMpCategory->is_default = 1;
                                } else {
                                    $objMpCategory->is_default = 0;
                                }

                                $objMpCategory->add();
                            }
                        }

                        if (Configuration::get('WK_MP_PRODUCT_FEATURE')
                        && $permissionData['featuresPermission']['add']) {
                            // adding product feature into marketplace table
                            WkMpProductFeature::processProductFeature($mpIdProduct, $defaultLang);
                        }

                        // if default approve on, then entry product details in ps_product table
                        if (!Configuration::get('WK_MP_PRODUCT_ADMIN_APPROVE')) {
                            // creating ps_product when admin setting is default
                            $idProduct = $objSellerProduct->addSellerProductToPs($mpIdProduct, 1);
                            if ($idProduct) {
                                //save ps_product
                                $objSellerProduct->id_ps_product = $idProduct;
                                $objSellerProduct->save();

                                Hook::exec('actionToogleMPProductCreateStatus', array(
                                    'id_product' => $idProduct, 'id_mp_product' => $mpIdProduct, 'active' => 1));
                            }

                            //If seller product default active approval is ON then mail to seller of product activation
                            WkMpSellerProduct::sendMail($mpIdProduct, 1, 1);
                        }

                        if (Configuration::get('WK_MP_MAIL_ADMIN_PRODUCT_ADD')) {
                            //Mail to admin on product add by seller
                            $sellerDetail = WkMpSeller::getSeller($idSeller, Configuration::get('PS_LANG_DEFAULT'));
                            if ($sellerDetail) {
                                $sellerName = $sellerDetail['seller_firstname'].' '.$sellerDetail['seller_lastname'];
                                $shopName = $sellerDetail['shop_name'];
                                $objSellerProduct->mailToAdminOnProductAdd($nameDefaultLang, $sellerName, $sellerDetail['phone'], $shopName, $sellerDetail['business_email']);
                            }
                        }
                    }

                    Hook::exec('actionAfterAddMPProduct', array('id_mp_product' => $mpIdProduct));

                    //To manage staff log (changes add/update/delete)
                    WkMpHelper::setStaffHook($this->context->customer->id, Tools::getValue('controller'), $mpIdProduct, 1); // 1 for Add action

                    $params = array('created_conf' => 1);
                    if (Tools::isSubmit('StayProduct')) {
                        $params['id_mp_product'] = $mpIdProduct;
                        $params['tab'] = Tools::getValue('active_tab');
                        Tools::redirect($this->context->link->getModuleLink('marketplace', 'updateproduct', $params));
                    } else {
                        Tools::redirect($this->context->link->getModuleLink('marketplace', 'productlist', $params));
                    }
                }
            }
        }
    }

    public function defineJSVars()
    {
        $jsVars = array(
                'actionpage' => 'product',
                'path_sellerproduct' => $this->context->link->getModuleLink('marketplace', 'addproduct'),
                'path_addfeature' => $this->context->link->getModuleLink('marketplace', 'addproduct'),
                'req_prod_name' => $this->module->l('Product name is required in Default Language -', 'addproduct'),
                'amt_valid' => $this->module->l('Amount should be numeric only.', 'addproduct'),
                'req_catg' => $this->module->l('Please select atleast one category.', 'addproduct'),
                'req_price' => $this->module->l('Product price is required.', 'addproduct'),
                'notax_avalaible' => $this->module->l('No tax available', 'addproduct'),
                'some_error' => $this->module->l('Some error occured.', 'addproduct'),
                'no_value' => $this->module->l('No Value Found', 'addproduct'),
                'choose_value' => $this->module->l('Choose a value', 'addproduct'),
                'value_missing' => $this->module->l('Feature value is missing', 'addproduct'),
                'value_length_err' => $this->module->l('Feature value is too long', 'addproduct'),
                'value_name_err' => $this->module->l('Feature value is not valid', 'addproduct'),
                'feature_err' => $this->module->l('Feature is not selected', 'addproduct'),
            );

        Media::addJsDef($jsVars);
    }

    /**
     * Load Prestashop category with ajax load of plugin jstree.
     */
    public function displayAjaxProductCategory()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        WkMpSellerProduct::getMpProductCategory();
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'addproduct'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Add Product', 'addproduct'),
            'url' => '',
        );

        return $breadcrumb;
    }

    public function displayAjaxAddMoreFeature()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        $idCustomer = $this->context->customer->id;
        //Override customer id if any staff of seller want to use this controller
        if (Module::isEnabled('mpsellerstaff')) {
            $getCustomerId = WkMpSellerStaff::overrideMpSellerCustomerId($idCustomer);
            if ($getCustomerId) {
                $idCustomer = $getCustomerId;
            }
        }
        $mpSeller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
        WkMpHelper::assignDefaultLang($mpSeller['id_seller']);
        $permissionData = WkMpHelper::productTabPermission();
        $this->context->smarty->assign(
            array(
                'default_lang' => $mpSeller['default_lang'],
                'permissionData' => $permissionData,
                'fieldrow' => Tools::getValue('fieldrow'),
                'choosedLangId' => Tools::getValue('choosedLangId'),
                'available_features' => Feature::getFeatures($this->context->language->id, (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP)),
            )
        );
        die($this->context->smarty->fetch('module:marketplace/views/templates/front/product/_partials/more-product-feature.tpl'));
    }

    public function displayAjaxGetFeatureValue()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        $idCustomer = $this->context->customer->id;
        //Override customer id if any staff of seller want to use this controller
        if (Module::isEnabled('mpsellerstaff')) {
            $getCustomerId = WkMpSellerStaff::overrideMpSellerCustomerId($idCustomer);
            if ($getCustomerId) {
                $idCustomer = $getCustomerId;
            }
        }
        $mpSeller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
        if ($mpSeller && $mpSeller['active']) {
            $featuresValue = FeatureValue::getFeatureValuesWithLang($this->context->language->id, (int) Tools::getValue('idFeature'));
            if (!empty($featuresValue)) {
                die(Tools::jsonEncode($featuresValue));
            } else {
                die(false);
            }
        }
        die(false);
    }

    public function displayAjaxValidateMpForm()
    {
        $data = array('status' => 'ok');
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        $params = array();
        parse_str(Tools::getValue('formData'), $params);
        if (!empty($params)) {
            WkMpSellerProduct::validationProductFormField($params);

            // if features are enable or seller is trying to add features
            if (isset($params['wk_feature_row'])) {
                WkMpProductFeature::checkFeatures($params);
            }
        }
        die(Tools::jsonEncode($data));
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->addJqueryUI('ui.datepicker');
        $this->addJqueryPlugin('tablednd');
        $this->addjQueryPlugin('growl', null, false);

        $this->registerStylesheet('mp-marketplace_account', 'modules/'.$this->module->name.'/views/css/marketplace_account.css');
        $this->registerStylesheet('mp_global_style-css', 'modules/'.$this->module->name.'/views/css/mp_global_style.css');

        $this->registerJavascript('mp-mp_form_validation', 'modules/'.$this->module->name.'/views/js/mp_form_validation.js');
        $this->registerJavascript('mp-change_multilang', 'modules/'.$this->module->name.'/views/js/change_multilang.js');

        //Category tree
        $this->registerStylesheet('mp-categorytree-css', 'modules/'.$this->module->name.'/views/js/categorytree/themes/default/style.min.css');
        $this->registerJavascript('mp-jstree-js', 'modules/'.$this->module->name.'/views/js/categorytree/jstree.min.js');
        $this->registerJavascript('mp-wk_jstree-js', 'modules/'.$this->module->name.'/views/js/categorytree/wk_jstree.js');
    }
}
