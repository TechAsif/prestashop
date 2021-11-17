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

class MarketplaceUpdateProductModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if ($this->context->customer->id) {
            $idCustomer = $this->context->customer->id;
            $addPermission = 1;
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
                    //Assign variable to display message that permission is allowed or not of this page
                    WkMpSellerStaffPermission::assignProductTabPermission($idStaff, WkMpTabList::MP_PRODUCT_TAB);

                    $staffTabDetails = WkMpTabList::getStaffPermissionWithTabName(
                        $idStaff,
                        $this->context->language->id,
                        WkMpTabList::MP_PRODUCT_TAB
                    );
                    if ($staffTabDetails) {
                        //For add product button permission
                        $addPermission = $staffTabDetails['add'];
                    }

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

            $mpIdProduct = Tools::getValue('id_mp_product');
            $seller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
            if ($seller && $seller['active']) {
                $idSeller = $seller['id_seller'];

                // show admin commission on product base price for seller
                if (Configuration::get('WK_MP_SHOW_ADMIN_COMMISSION')) {
                    $objMpCommission = new WkMpCommission();
                    $adminCommission = $objMpCommission->finalCommissionSummaryForSeller($idSeller);
                    if ($adminCommission) {
                        $this->context->smarty->assign('admin_commission', $adminCommission);
                    }
                }

                $mpSellerProduct = new WkMpSellerProduct($mpIdProduct);
                $mpProduct = (array) $mpSellerProduct;

                // If seller of current product and current seller customer is match
                if ($mpProduct['id_seller'] == $idSeller) {
                    // If delete product by seller
                    $deleteProduct = Tools::getValue('deleteproduct');
                    if ($deleteProduct) {
                        // if seller delete product, delete process
                        $objMpSellerProduct = new WkMpSellerProduct($mpIdProduct);
                        if ($objMpSellerProduct->delete()) {
                            //To manage staff log (changes add/update/delete)
                            WkMpHelper::setStaffHook(
                                $this->context->customer->id,
                                Tools::getValue('controller'),
                                $mpIdProduct,
                                3
                            ); // 3 for Delete action

                            Tools::redirect(
                                $this->context->link->getModuleLink(
                                    'marketplace',
                                    'productlist',
                                    array('deleted' => 1)
                                )
                            );
                        }
                    }

                    // If duplicate product by seller
                    if (Configuration::get('WK_MP_PRODUCT_ALLOW_DUPLICATE') && Tools::getValue('duplicateproduct')) {
                        //If seller is allowed to duplicate product
                        $objMpSellerProduct = new WkMpSellerProduct();
                        if ($duplicateMpProductId = $objMpSellerProduct->duplicateSellerProduct($mpIdProduct)) {
                            Tools::redirect(
                                $this->context->link->getModuleLink(
                                    'marketplace',
                                    'updateproduct',
                                    array(
                                        'id_mp_product' => (int) $duplicateMpProductId,
                                        'duplicate' => 1
                                    )
                                )
                            );
                        }
                    }

                    Hook::exec('actionBeforeShowUpdatedProduct', array('mp_product_details' => $mpProduct));

                    //Assign and display product active/inactive images
                    WkMpSellerProductImage::getProductImageDetails($mpIdProduct);

                    // Category tree
                    $objMpCategory = new WkMpSellerProductCategory();
                    $defaultIdCategory = $objMpCategory->getSellerProductDefaultCategory($mpIdProduct);

                    $idCategory = array();
                    $checkedProductCategory = $mpSellerProduct->getSellerProductCategories($mpIdProduct);
                    if ($checkedProductCategory) {
                        // Default category
                        foreach ($checkedProductCategory as $checkIdCategory) {
                            $idCategory[] = $checkIdCategory['id_category'];
                        }

                        $catIdsJoin = implode(',', $idCategory);
                        $this->context->smarty->assign('catIdsJoin', $catIdsJoin);
                    }

                    $defaultCategory = Category::getCategoryInformations($idCategory, $this->context->language->id);

                    // Set default lang at every form according to configuration multi-language
                    WkMpHelper::assignDefaultLang($idSeller);

                    //show tax rule group on update product page
                    $taxRuleGroups = TaxRulesGroup::getTaxRulesGroups(true);
                    if ($taxRuleGroups && Configuration::get('WK_MP_SELLER_APPLIED_TAX_RULE')) {
                        $this->context->smarty->assign('tax_rules_groups', $taxRuleGroups);
                        $this->context->smarty->assign('mp_seller_applied_tax_rule', 1);
                    }
                    $this->context->smarty->assign('id_tax_rules_group', $mpProduct['id_tax_rules_group']);

                    // Admin Shipping
                    $carriers = Carrier::getCarriers(
                        $this->context->language->id,
                        true,
                        false,
                        false,
                        null,
                        ALL_CARRIERS
                    );
                    $carriersChoices = array();
                    if ($carriers) {
                        foreach ($carriers as $carrier) {
                            $carriersChoices[$carrier['id_reference'].' - '.$carrier['name'].' ('.$carrier['delay'].')']
                             = $carrier['id_reference'];
                        }
                    }
                    $selectedCarriers = $mpProduct['ps_id_carrier_reference'];
                    if ($selectedCarriers) {
                        $selectedCarriers = unserialize($selectedCarriers);
                    }

                    //Display Product Combination list
                    WkMpProductAttribute::displayProductCombinationList($mpIdProduct);

                    // Get Seller Product Features and Assign on Smarty
                    WkMpProductFeature::assignProductFeature($mpIdProduct);
                    $this->defineJSVars($mpIdProduct, $defaultIdCategory);

                    // checking current product has attribute or not
                    $objMpAttribute = new WkMpProductAttribute();
                    $hasAttribute = $objMpAttribute->getProductAttributes($mpIdProduct);
                    if ($hasAttribute) {
                        $this->context->smarty->assign('hasAttribute', 1);
                    }
                    // End of -- hasAttribute code ---

                    $link = new Link();

                    $objDefaultCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
                    $this->context->smarty->assign(array(
                        'id' => $mpIdProduct,
                        'controller' => 'updateproduct',
                        'active_tab' => Tools::getValue('tab'),
                        'static_token' => Tools::getToken(false),
                        'module_dir' => _MODULE_DIR_,
                        'ps_img_dir' => _PS_IMG_.'l/',
                        'defaultCategory' => $defaultCategory,
                        'defaultIdCategory' => $defaultIdCategory,
                        'product_info' => $mpProduct,
                        'is_seller' => 1,
                        'logic' => 3,
                        'defaultCurrencySign' => $objDefaultCurrency->sign,
                        'default_lang' => $seller['default_lang'],
                        'carriersChoices' => $carriersChoices,
                        'selectedCarriers' => $selectedCarriers,
                        'available_features' => Feature::getFeatures($this->context->language->id, (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP)),
                        'add_permission' => $addPermission,
                        'permissionData' => $permissionData,
                        'countries' => Country::getCountries((int)$this->context->language->id, false),
                        'currencies' => Currency::getCurrencies(),
                        'groupes' => Group::getGroups((int)$this->context->language->id),
                        'admin_link'=> _PS_BASE_URL_.__PS_BASE_URI__."testadmin/".$link->getAdminLink('AdminTest', true),
                        'admin_link2'=> Tools::getHttpHost(true)._PS_BASE_URL_,
                        
                    ));

                    $this->setTemplate('module:marketplace/views/templates/front/product/updateproduct.tpl');
                } else {
                    Tools::redirect($this->context->link->getModuleLink('marketplace', 'dashboard'));
                }
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
            }
        } else {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }
    }

    public function postProcess()
    {
        if ((Tools::isSubmit('SubmitProduct') || Tools::isSubmit('StayProduct')) && $this->context->customer->id) {
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
                    $permissionDetails = WkMpSellerStaffPermission::getProductSubTabPermissionData(
                        $staffDetails['id_staff']
                    );
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
            // If seller updated the product, update process
            $seller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
            if ($seller && $seller['active']) {
                $idMpProduct = Tools::getValue('id_mp_product');

                $mpSellerProduct = new WkMpSellerProduct($idMpProduct);
                $mpProduct = (array) $mpSellerProduct;

                // If seller of current product and current seller customer is match
                if ($mpProduct['id_seller'] == $seller['id_seller']) {
                    $quantity = Tools::getValue('quantity');

                    //save product minimum quantity
                    if (Configuration::get('WK_MP_PRODUCT_MIN_QTY')) {
                        $minimalQuantity = Tools::getValue('minimal_quantity');
                    } else {
                        $minimalQuantity = $mpProduct['minimal_quantity'];
                    }

                    //save product condition new, used, refurbished
                    if (Configuration::get('WK_MP_PRODUCT_CONDITION')) {
                        $showCondition = Tools::getValue('show_condition');
                        if (!$showCondition) {
                            $showCondition = 0;
                        }
                        $condition = Tools::getValue('condition');
                    } else {
                        $showCondition = $mpProduct['show_condition'];
                        $condition = $mpProduct['condition'];
                    }

                    //save product price
                    $price = Tools::getValue('price');

                    //save product wholesale price
                    if (Configuration::get('WK_MP_PRODUCT_WHOLESALE_PRICE')) {
                        $wholesalePrice = Tools::getValue('wholesale_price');
                    } else {
                        $wholesalePrice = $mpProduct['wholesale_price'];
                    }

                    //save product unit price
                    if (Configuration::get('WK_MP_PRODUCT_PRICE_PER_UNIT')) {
                        $unitPrice = Tools::getValue('unit_price');
                        $unity = Tools::getValue('unity');
                    } else {
                        $unitPrice = $mpProduct['unit_price'];
                        $unity = $mpProduct['unity'];
                    }

                    //save product tax rule
                    if (Configuration::get('WK_MP_SELLER_APPLIED_TAX_RULE')) {
                        $idTaxRulesGroup = Tools::getValue('id_tax_rules_group');
                    } else {
                        $idTaxRulesGroup = $mpProduct['id_tax_rules_group'];
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

                    if (Configuration::get('WK_MP_SELLER_PRODUCT_VISIBILITY')
                    && $permissionData['optionsPermission']['edit']) {
                        //Product Visibility
                        $availableForOrder = trim(Tools::getValue('available_for_order'));
                        $showPrice = $availableForOrder ? 1 : trim(Tools::getValue('show_price'));
                        $onlineOnly = trim(Tools::getValue('online_only'));
                        $visibility = trim(Tools::getValue('visibility'));
                    }

                    if (!Tools::getValue('product_name_'.$defaultLang)) {
                        if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                            $sellerLang = Language::getLanguage((int) $defaultLang);
                            $this->errors[] = sprintf($this->module->l('Product name is required in %s', 'updateproduct'), $sellerLang['name']);
                        } else {
                            $this->errors[] = $this->module->l('Product name is required', 'updateproduct');
                        }
                    } else {
                        // Validate form
                        $this->errors = WkMpSellerProduct::validateMpProductForm();

                        $objSellerProduct = new WkMpSellerProduct($idMpProduct);

                        Hook::exec('actionBeforeUpdateMPProduct', array('id_mp_product' => $idMpProduct));
                        if (empty($this->errors)) {
                            // If current product has no combination then product qty will update
                            $objMpAttribute = new WkMpProductAttribute();
                            $hasAttribute = $objMpAttribute->getProductAttributes($idMpProduct);
                            if (!$hasAttribute) {
                                $objSellerProduct->quantity = $quantity;
                                $objSellerProduct->minimal_quantity = $minimalQuantity;

                                //Low stock alert
                                if (Configuration::get('WK_MP_PRODUCT_LOW_STOCK_ALERT')) {
                                    $objSellerProduct->low_stock_threshold = Tools::getValue('low_stock_threshold');
                                    if (Tools::getValue('low_stock_alert')) {
                                        $objSellerProduct->low_stock_alert = 1;
                                    } else {
                                        $objSellerProduct->low_stock_alert = 0;
                                    }
                                } else {
                                    $objSellerProduct->low_stock_threshold = $mpProduct['low_stock_threshold'];
                                    $objSellerProduct->low_stock_alert = $mpProduct['low_stock_alert'];
                                }
                            }

                            $objSellerProduct->id_category = $defaultCategory;
                            $objSellerProduct->show_condition = $showCondition;
                            $objSellerProduct->condition = $condition;

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
                            } else {
                                $objSellerProduct->on_sale = $mpProduct['on_sale'];
                            }

                            $objSellerProduct->additional_delivery_times = $mpProduct['additional_delivery_times'];
                            $objSellerProduct->additional_shipping_cost = $mpProduct['additional_shipping_cost'];
                            if ((Configuration::get('WK_MP_SELLER_ADMIN_SHIPPING') || Module::isEnabled('mpshipping'))
                            && $permissionData['shippingPermission']['edit']) {
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
                            if ($permissionData['optionsPermission']['edit']) {
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

                            foreach (Language::getLanguages(false) as $language) {
                                $productIdLang = $language['id_lang'];
                                $shortDescIdLang = $language['id_lang'];
                                $descIdLang = $language['id_lang'];

                                if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                                    //if product name in other language is not available then fill with seller language same for others
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

                                $objSellerProduct->product_name[$language['id_lang']] = Tools::getValue('product_name_'.$productIdLang);

                                $objSellerProduct->short_description[$language['id_lang']] = Tools::getValue('short_description_'.$shortDescIdLang);

                                $objSellerProduct->description[$language['id_lang']] = Tools::getValue('description_'.$descIdLang);

                                //Product SEO
                                if (Configuration::get('WK_MP_SELLER_PRODUCT_SEO')
                                && $permissionData['seoPermission']['edit']) {
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
                                && $permissionData['optionsPermission']['edit']) {
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
                                && $permissionData['shippingPermission']['edit']) {
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
                            && $permissionData['optionsPermission']['edit']) {
                                $objSellerProduct->available_for_order = $availableForOrder;
                                $objSellerProduct->show_price = $showPrice;
                                $objSellerProduct->online_only = $onlineOnly;
                                $objSellerProduct->visibility = $visibility;
                            }

                            $objSellerProduct->save();

                            $objMpCategory = new WkMpSellerProductCategory();

                            // for Updating new categories first delete previous category
                            $objMpCategory->deleteProductCategory($idMpProduct);

                            // Add new category into table
                            $objMpCategory->id_seller_product = $idMpProduct;

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
                            && $permissionData['featuresPermission']['edit']) {
                                // while updating the features delete the product features first
                                WkMpProductFeature::deleteProductFeature($idMpProduct);

                                // Update product features
                                WkMpProductFeature::processProductFeature($idMpProduct, $defaultLang);
                            }

                            if ($objSellerProduct->active) {
                                //if product is active then check admin configure value that product after update need to approved by admin or not
                                $deactivateAfterUpdate = WkMpSellerProduct::deactivateProductAfterUpdate($idMpProduct);
                                if (!Configuration::get('WK_MP_PRODUCT_UPDATE_ADMIN_APPROVE')) {
                                    // Update also in prestashop if product is active
                                    $objSellerProduct->updateSellerProductToPs($idMpProduct, 1);
                                }
                            }

                            Hook::exec('actionAfterUpdateMPProduct', array('id_mp_product' => $idMpProduct, 'id_mp_product_attribute' => 0));

                            //To manage staff log (changes add/update/delete)
                            WkMpHelper::setStaffHook($this->context->customer->id, Tools::getValue('controller'), $idMpProduct, 2); // 2 for Update action

                            if (isset($deactivateAfterUpdate) && $deactivateAfterUpdate) {
                                $successParams = array('edited_withdeactive' => 1);
                            } else {
                                $successParams = array('edited_conf' => 1);
                            }
                            if (Tools::isSubmit('StayProduct')) {
                                $successParams['id_mp_product'] = $idMpProduct;
                                $successParams['tab'] = Tools::getValue('active_tab');
                                Tools::redirect($this->context->link->getModuleLink('marketplace', 'updateproduct', $successParams));
                            } else {
                                Tools::redirect($this->context->link->getModuleLink('marketplace', 'productlist', $successParams));
                            }
                        }
                    }
                }
            }
        }
    }

    public function defineJSVars($mpIdProduct, $defaultIdCategory)
    {
        $jsVars = array(
                'actionpage' => 'product',
                'adminupload' => 0,
                'actionIdForUpload' => $mpIdProduct,
                'defaultIdCategory' => $defaultIdCategory,
                'deleteaction' => 'jFiler-item-trash-action',
                'path_sellerproduct' => $this->context->link->getModuleLink('marketplace', 'updateproduct'),
                'path_uploader' => $this->context->link->getModulelink('marketplace', 'uploadimage'),
                'ajax_urlpath' => $this->context->link->getModuleLink('marketplace', 'productimageedit'),
                'path_addfeature' => $this->context->link->getModuleLink('marketplace', 'updateproduct'),
                'req_prod_name' => $this->module->l('Product name is required in Default Language -', 'updateproduct'),
                'req_catg' => $this->module->l('Please select atleast one category.', 'updateproduct'),
                'space_error' => $this->module->l('Space is not allowed.', 'updateproduct'),
                'confirm_delete_msg' => $this->module->l('Are you sure you want to delete this image?', 'updateproduct'),
                'delete_msg' => $this->module->l('Deleted.', 'updateproduct'),
                'error_msg' => $this->module->l('An error occurred.', 'updateproduct'),
                'mp_tinymce_path' => _MODULE_DIR_.$this->module->name.'/libs',
                'img_module_dir' => _MODULE_DIR_.$this->module->name.'/views/img/',
                'image_drag_drop' => 1,
                'drag_drop' => $this->module->l('Drag & Drop to Upload', 'updateproduct'),
                'or' => $this->module->l('or', 'updateproduct'),
                'pick_img' => $this->module->l('Pick Image', 'updateproduct'),
                'choosefile' => $this->module->l('Choose Images', 'updateproduct'),
                'choosefiletoupload' => $this->module->l('Choose Images To Upload', 'updateproduct'),
                'imagechoosen' => $this->module->l('Images were chosen', 'updateproduct'),
                'dragdropupload' => $this->module->l('Drop file here to Upload', 'updateproduct'),
                'only' => $this->module->l('Only', 'updateproduct'),
                'imagesallowed' => $this->module->l('Images are allowed to be uploaded.', 'updateproduct'),
                'onlyimagesallowed' => $this->module->l('Only Images are allowed to be uploaded.', 'updateproduct'),
                'imagetoolarge' => $this->module->l('is too large! Please upload image up to', 'updateproduct'),
                'imagetoolargeall' => $this->module->l('Images you have choosed are too large! Please upload images up to', 'updateproduct'),
                'req_price' => $this->module->l('Product price is required.', 'updateproduct'),
                'notax_avalaible' => $this->module->l('No tax available', 'updateproduct'),
                'some_error' => $this->module->l('Some error occured.', 'updateproduct'),
                'Choose' => $this->module->l('Choose', 'updateproduct'),
                'confirm_delete_combination' => $this->module->l('Are you sure you want to delete this combination?', 'updateproduct'),
                'noAllowDefaultAttribute' => $this->module->l('You can not make deactivated attribute as default attribute.', 'updateproduct'),
                'not_allow_todeactivate_combination' => $this->module->l('You can not deactivate this combination. Atleast one combination must be active.', 'updateproduct'),
                'no_value' => $this->module->l('No Value Found', 'updateproduct'),
                'choose_value' => $this->module->l('Choose a value', 'updateproduct'),
                'value_missing' => $this->module->l('Feature value is missing', 'updateproduct'),
                'value_length_err' => $this->module->l('Feature value is too long', 'updateproduct'),
                'value_name_err' => $this->module->l('Feature value is not valid', 'updateproduct'),
                'feature_err' => $this->module->l('Feature is not selected', 'updateproduct'),
                'generate_combination_confirm_msg' => $this->module->l('You will lose all unsaved modifications. Are you sure that you want to proceed?', 'updateproduct'),
                'enabled' => $this->module->l('Enabled', 'updateproduct'),
                'disabled' => $this->module->l('Disabled', 'updateproduct'),
                'update_success' => $this->module->l('Updated Successfully', 'updateproduct'),
                'invalid_value' => $this->module->l('Invalid Value', 'updateproduct'),
                'success_msg' => $this->module->l('Success', 'updateproduct'),
                'error_msg' => $this->module->l('Error', 'updateproduct'),

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

    public function displayAjaxUpdateDefaultAttribute()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        //Update default combination for seller product
        WkMpProductAttribute::updateMpProductDefaultAttribute();
    }

    public function displayAjaxDeleteMpCombination()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        //Delete Product combination from combination list at edit product page
        WkMpProductAttribute::deleteMpProductAttribute();
    }

    /**
     * Change combination status through ajaxProcess if combination activate/deactivate module is enabled.
     */
    public function displayAjaxChangeCombinationStatus()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }
        WkMpProductAttribute::changeCombinationStatus();
    }

    /**
     * Change combination qty from product combination list
     */
    public function displayAjaxUpdateMpCombinationQuantity()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }

        $idMpProductAttribute = Tools::getValue('mp_product_attribute_id');
        $combinationQty = Tools::getValue('combi_qty');

        WkMpProductAttribute::setMpProductCombinationQuantity($idMpProductAttribute, $combinationQty);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'updateproduct'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Update Product', 'updateproduct'),
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

        Media::addJsDef(array(
            'spacificprice_ajax' => $this->context->link->getModuleLink($this->module->name,'spacificprice', array('ajax'=>true)),
        ));

        $this->registerJavascript('mp-bloodhound', 'modules/'.$this->module->name.'/views/js/bloodhound.js');
        $this->registerJavascript('mp-typeahead', 'modules/'.$this->module->name.'/views/js/typeahead.bundle.js');
        $this->registerJavascript('mp-specific_price', 'modules/'.$this->module->name.'/views/js/specific_price.js');

        //for mp product combination list
        $this->registerJavascript('mp-managecombination-js', 'modules/'.$this->module->name.'/views/js/managecombination.js');

        //Upload images
        $this->registerStylesheet('mp-filer-css', 'modules/'.$this->module->name.'/views/css/uploadimage-css/jquery.filer.css');
        $this->registerStylesheet('mp-filer-dragdropbox-theme-css', 'modules/'.$this->module->name.'/views/css/uploadimage-css/jquery.filer-dragdropbox-theme.css');
        $this->registerStylesheet('mp-uploadphoto-css', 'modules/'.$this->module->name.'/views/css/uploadimage-css/uploadphoto.css');
        $this->registerJavascript('mp-filer-js', 'modules/'.$this->module->name.'/views/js/uploadimage-js/jquery.filer.js');
        $this->registerJavascript('mp-uploadimage-js', 'modules/'.$this->module->name.'/views/js/uploadimage-js/uploadimage.js');
        $this->registerJavascript('mp-imageedit', 'modules/'.$this->module->name.'/views/js/imageedit.js');

        //Category tree
        $this->registerStylesheet('mp-categorytree-css', 'modules/'.$this->module->name.'/views/js/categorytree/themes/default/style.min.css');
        $this->registerJavascript('mp-jstree-js', 'modules/'.$this->module->name.'/views/js/categorytree/jstree.min.js');
        $this->registerJavascript('mp-wk_jstree-js', 'modules/'.$this->module->name.'/views/js/categorytree/wk_jstree.js');
    }
}
