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

if (!defined('_PS_VERSION_')) {
    exit;
}
require_once dirname(__FILE__).'/classes/WkMpRequiredClasses.php';
if (Module::isEnabled('mpsellerstaff')) {
    include_once dirname(__FILE__).'/../mpsellerstaff/classes/WkMpStaffRequiredClasses.php';
}
class Marketplace extends Module
{
    public static $mpController = true;
    public $sellerDetailsView = array(
        array('id_group' => 1),
        array('id_group' => 2),
        array('id_group' => 3),
        array('id_group' => 4),
        array('id_group' => 5),
        array('id_group' => 6),
        array('id_group' => 7),
        array('id_group' => 8),
        array('id_group' => 9),
    );

    public function __construct()
    {
        $this->name = 'marketplace';
        $this->tab = 'market_place';
        $this->version = '5.3.1';
        $this->author = 'Webkul';
        $this->need_instance = 0;
        $this->secure_key = Tools::hash($this->name); //encrypt() deprecated in PS 1.7, use hash()
        $this->module_key = '92e753c36c07c56867a9169292c239e5';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->controllers = array(
            'addproduct',
            'allreviews',
            'dashboard',
            'editprofile',
            'managecombination',
            'mporder',
            'mporderdetails',
            'mppayment',
            'mptransaction',
            'productdetails',
            'productlist',
            'sellerprofile',
            'sellerrequest',
            'shopstore',
            'updateproduct',
            'createattribute',
            'createattributevalue',
            'productattribute',
            'viewattributegroupvalue',
            'addfeaturevalue',
            'createfeature',
            'productfeature',
            'viewfeaturevalue',
            'generatecombination',
            'abc',

        );
        parent::__construct();
        $this->displayName = $this->l('Marketplace');
        $this->description = $this->l('Turn your Prestashop store into a marketplace where sellers can add products, manage orders, manage profile, shop, product name and descriptions in multi-language.');
        $this->confirmUninstall = $this->l('Are you sure? All module data will be lost after uninstalling the module');

        $this->sellerDetailsView[0]['name'] = $this->l('Seller Name');
        $this->sellerDetailsView[1]['name'] = $this->l('Seller Email');
        $this->sellerDetailsView[2]['name'] = $this->l('Seller Phone & Fax');
        $this->sellerDetailsView[3]['name'] = $this->l('Address');
        $this->sellerDetailsView[4]['name'] = $this->l('About Shop');
        $this->sellerDetailsView[5]['name'] = $this->l('Social Profile');
        $this->sellerDetailsView[6]['name'] = $this->l('Contact Seller Link');
        $this->sellerDetailsView[7]['name'] = $this->l('Shop Products');
        $this->sellerDetailsView[8]['name'] = $this->l('Shop Name On Product Page');
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminMarketplaceGeneralSettings'));
    }

    // If customer is getting delete then we are updating seller with anonymous information
    public function hookActionDeleteGDPRCustomer($customer)
    {
        $objSeller = new WkMpSeller();
        $sellerInfo = WkMpSeller::getSellerByCustomerId($customer['id']);
        if ($sellerInfo) {
            $result = $objSeller->updateSellerInformation($sellerInfo, $customer['email']);
            if (!$result) {
                return json_encode($this->l('Unable to delete seller information.'));
            }
        }
    }

    // Showing seller information based on customer ID
    public function hookActionExportGDPRData($customer)
    {
        if (!Tools::isEmpty($customer['email']) && Validate::isEmail($customer['email'])) {
            $objSeller = new WkMpSeller();
            if ($res = $objSeller->exportSellerInformation($customer['id'])) {
                return json_encode($res);
            }
            return json_encode($this->l('Seller information not exist.'));
        }
    }

    public function sellersOrderMail(
        $mpOrderDetail,
        $customer,
        $address,
        $idCurrency,
        $idSeller,
        $idOrder
    ) {
        $order = new Order($idOrder);
        $idCurrency = (int) $order->id_currency;

        // Format price
        foreach ($mpOrderDetail['product_list'] as &$attributeList) {
            foreach ($attributeList as &$customizeList) {
                foreach ($customizeList as &$product) {
                    $product['unit_price_tax_excl'] = Tools::displayPrice($product['unit_price_tax_excl']);
                    $product['unit_price_tax_incl'] = Tools::displayPrice($product['unit_price_tax_incl']);
                    $product['total_price_tax_incl'] = Tools::displayPrice($product['total_price_tax_incl']);
                }
            }
        }

        $objMpSeller = new WkMpSeller($idSeller);
        $productHTML = $objMpSeller->getMpEmailTemplateContent(
            'mp_order_product_list.tpl',
            Mail::TYPE_HTML,
            $mpOrderDetail['product_list']
        );

        $currency = new Currency($idCurrency);
        $templateVars = array(
            '{order_reference}' => $order->reference,
            '{seller_name}' => $mpOrderDetail['seller_name'],
            '{customer_name}' => $customer->firstname.' '.$customer->lastname,
            '{customer_email}' => $customer->email,
            '{delivery_block_html}' => AddressFormat::generateAddress($address, array(), '<br>'),
            '{seller_product_total}' => Tools::displayPrice($mpOrderDetail['total_earn_ti'], $currency, false),
            '{seller_shipping}' => '',
            '{final_total_price}' => Tools::displayPrice($mpOrderDetail['total_price_tax_incl'], $currency, false),
            '{product_html}' => $productHTML,
            '{voucher_html}' => '',
        );

        $voucherInfo = WkMpSellerOrderDetail::setVoucherDetails($idOrder, $idSeller, $idCurrency);
        if ($voucherInfo) {
            $voucherHTML = $objMpSeller->getMpEmailTemplateContent(
                'mp_order_voucher_detail.tpl',
                Mail::TYPE_HTML,
                $voucherInfo
            );
            $templateVars['{voucher_html}'] = $voucherHTML;
        }

        if ($sellerShipping = WkMpAdminShipping::getSellerShippingByIdOrder(
            $idOrder,
            $objMpSeller->seller_customer_id
        )) {
            $shippingInfo = $objMpSeller->getMpEmailTemplateContent(
                'mp_shipping_detail.tpl',
                Mail::TYPE_HTML,
                Tools::displayPrice($sellerShipping, $currency, false)
            );
            $templateVars['{seller_shipping}'] = $shippingInfo;
            $templateVars['{final_total_price}'] = Tools::displayPrice(
                $mpOrderDetail['total_price_tax_incl'] + $sellerShipping,
                $currency,
                false
            );
        }

        if (Configuration::get('WK_MP_SUPERADMIN_EMAIL')) {
            $adminEmail = Configuration::get('WK_MP_SUPERADMIN_EMAIL');
        } else {
            $idEmployee = WkMpHelper::getSupperAdmin();
            $employee = new Employee($idEmployee);
            $adminEmail = $employee->email;
        }

        $fromTitle = Configuration::get('WK_MP_FROM_MAIL_TITLE');
        $to = $mpOrderDetail['seller_email'];
        Mail::Send(
            $mpOrderDetail['seller_default_lang_id'],
            'mp_order',
            Mail::l('Order Created', $mpOrderDetail['seller_default_lang_id']),
            $templateVars,
            $to,
            $mpOrderDetail['seller_name'],
            $adminEmail,
            $fromTitle,
            null,
            null,
            _PS_MODULE_DIR_.'marketplace/mails/',
            false,
            null,
            null
        );
    }

    public function hookDisplayAdminOrder($params)
    {
        if (isset($params['id_order'])) {
            $idOrder = $params['id_order'];
            $mpOrderDetails = new WkMpSellerOrderDetail();
            if ($mpOrders = $mpOrderDetails->getProductsFromOrder($idOrder)) {
                $order = new Order($idOrder);
                $sellerOrderDetail = array();
                foreach ($mpOrders as &$detail) {
                    $sellerProduct = WkMpSellerProduct::getSellerProductByPsIdProduct($detail['product_id']);
                    if ($sellerProduct) {
                        $detail['id_mp_product'] = $sellerProduct['id_mp_product'];
                    }

                    if (Configuration::get('WK_MP_TRACKING_NUMBER_IN_URL')) {
                        $detail['tracking_url'] = Tools::strReplaceFirst(
                            '@',
                            $detail['tracking_number'],
                            $detail['tracking_url']
                        );
                    } else {
                        $detail['tracking_url'] = $detail['tracking_url'];
                    }

                    $sellerOrderDetail[$detail['id_seller']][] = $detail;
                }
                $this->context->smarty->assign(array(
                    'wk_ps_version' => _PS_VERSION_,
                    'seller_order_details' => $mpOrders,
                    'mp_seller_order_details' => $sellerOrderDetail,
                    'link' => $this->context->link,
                    'currentState' => new OrderState($order->getCurrentState(), Configuration::get('PS_LANG_DEFAULT')),
                ));

                return $this->display(__FILE__, 'admin-order-view-seller-details.tpl');
            }
        }
    }

    public function hookDisplayMpMenu()
    {
        $idCustomer = $this->context->customer->id;
        $seller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
        if ($seller) {
            if ($seller['active']) {
                //Get Seller total products
                if ($sellerProduct = WkMpSellerProduct::getSellerProduct($seller['id_seller'], 'all', $this->context->language->id)) {
                    $totalSellerProducts = count($sellerProduct);
                } else {
                    $totalSellerProducts = 0;
                }

                if ($sellerOfferProduct = WkMpSellerProduct::getSellerOffersProduct($seller['id_seller'], 'all', $this->context->language->id)) {
                    $totalSellerOffersProducts = count($sellerOfferProduct);
                } else {
                    $totalSellerOffersProducts = 0;
                }

                $this->context->smarty->assign(array(
                    'name_shop' => $seller['link_rewrite'],
                    'totalSellerProducts' => $totalSellerProducts,
                    'totalSellerOffersProducts' => $totalSellerOffersProducts,
                ));
            }

            $this->context->smarty->assign('is_seller', $seller['active']);
        } else {
            $this->context->smarty->assign('is_seller', -1); // Not a seller
        }

        $this->context->smarty->assign('link', $this->context->link);
        return $this->fetch('module:marketplace/views/templates/hook/mpmenu.tpl');
    }

    public function hookDisplayMPMyAccountMenu()
    {
        $seller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
        if ($seller) {
            if ($seller['active']) {
                $this->context->smarty->assign(array(
                    'id_customer' => $this->context->customer->id,
                    'name_shop' => $seller['link_rewrite'],
                ));
            }
            $this->context->smarty->assign(array(
                'mpSellerShopSettings' => Configuration::get('WK_MP_SELLER_SHOP_SETTINGS'),
                'shop_approved' => $seller['shop_approved'],
                'is_seller' => $seller['active'],

            ));
        } else {
            $this->context->smarty->assign('is_seller', -1); // Not a seller
        }

        $this->context->smarty->assign('link', $this->context->link);
        return $this->fetch('module:marketplace/views/templates/hook/mpmyaccountmenu.tpl');
    }

    /**
     * Display Sell on link on navigation bar.
     *
     * @return html An link with text Sell on shop name
     */
    public function hookDisplayNav1()
    {
        if (Configuration::get('WK_MP_LINK_ON_NAV_BAR')) {
            $this->context->smarty->assign('wk_ad_nav', 1);

            return $this->displayAdvertisementLink();
        }
    }

    /**
     * Display Sell on link on footer.
     *
     * @return html An link with text Sell on shop name
     */
    public function hookDisplayMyAccountBlock()
    {
        if (Configuration::get('WK_MP_LINK_ON_FOOTER_BAR')) {
            $this->context->smarty->assign('wk_ad_footer', 1);

            return $this->displayAdvertisementLink();
        }
    }

    public function displayAdvertisementLink()
    {
        if ($this->context->customer->id) {
            $isSellerExist = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            if ($isSellerExist && $isSellerExist['active']) {
                return;
            } else {
                if (Module::isEnabled('mpsellerstaff')) {
                    $staffDetails = WkMpSellerStaff::getStaffInfoByIdCustomer($this->context->customer->id);
                    if ($staffDetails) {
                        return;  //If current customer is any seller's staff, can't register as seller
                    }
                }
            }
        }
        $this->context->smarty->assign(
            'sellerLink',
            $this->context->link->getModuleLink('marketplace', 'sellerrequest')
        );
        return $this->fetch('module:marketplace/views/templates/hook/advertisement.tpl');
    }

    /**
     * Add custom CSS and Ad link on pages.
     *
     * If you dont want custom CSS, Define Marketplace::$mpController = false,
     * in init() function in your front controller,
     */
    public function hookDisplayHeader()
    {
        // Apply custome CSS for all MP and MP addons controllers
        if (isset($this->context->controller->module)) {
            if (($this->context->controller->module->name == $this->name
                || in_array($this->name, $this->context->controller->module->dependencies))
                && self::$mpController) {
                if (Configuration::get('WK_MP_ALLOW_CUSTOM_CSS')) {
                    $this->context->controller->registerStylesheet(
                        'module-marketplace-custom-style-css',
                        'modules/'.$this->name.'/views/css/mp_custom_style.css'
                    );
                }

                // Assign Global vars from Marketplace to add MP front controllers
                WkMpHelper::assignGlobalVariables();
                WkMpHelper::defineGlobalJSVariables();
            }
        }

        if (Configuration::get('WK_MP_LINK_ON_POP_UP')) {
            $this->context->smarty->assign('wk_ad_footer_pop', 1);
            $this->context->smarty->assign('cms_content_only', Tools::getValue('content_only'));
            if (isset($_COOKIE['no_advertisement'])) {
                $this->context->smarty->assign('no_advertisement', $_COOKIE['no_advertisement']);
            }

            return $this->displayAdvertisementLink();
        }
    }

    /**
     * Display Sold by with seller's shop name and seller rating and also display edit product button on seller login
     *
     * @return html
     */
    public function hookDisplayProductButtons()
    {
        $idProduct = Tools::getValue('id_product');
        if ($sellerProduct = WkMpSellerProduct::getSellerProductByPsIdProduct($idProduct)) {
            if (Configuration::get('WK_MP_SHOW_SELLER_DETAILS')) {
                $this->context->smarty->assign('showDetail', Configuration::get('WK_MP_SHOW_SELLER_DETAILS'));
            }

            $mpIdSeller = $sellerProduct['id_seller'];

            //Display seller rating on product page
            if ($sellerRating = WkMpSellerReview::getSellerAvgRating($mpIdSeller)) {
                if ($totalReviewData = WkMpSellerReview::getSellerReviewByIdSeller($mpIdSeller)) {
                    $totalReview = count($totalReviewData);
                } else {
                    $totalReview = 0;
                }

                //Get seller rating full summary
                $sellerRatingDetail = WkMpSellerReview::getSellerRatingSummary($mpIdSeller, $totalReview);

                $this->context->smarty->assign(
                    array(
                        'sellerRating' => $sellerRating,
                        'sellerRatingDetail' => $sellerRatingDetail,
                        'totalReview' => $totalReview,
                    )
                );

                Media::addJsDef(array(
                    'sellerRating' => $sellerRating,
                    'rating_start_path' => _MODULE_DIR_.$this->name.'/views/img/',
                    'totalReview' => $totalReview,
                ));
            }

            //Get seller info details
            if ($seller = WkMpSeller::getSeller($mpIdSeller, $this->context->language->id)) {
                // Check Access display Edit product link on seller product page for Seller or their staff
                $allowProductEdit = false;
                if ($this->context->customer->id == $seller['seller_customer_id']) {
                    $allowProductEdit = true; // allow seller to edit their product
                }

                $mpShopImage = $seller['shop_image'];
                if ($mpShopImage && file_exists(_PS_MODULE_DIR_.'marketplace/views/img/shop_img/'.$mpShopImage)) {
                    $this->context->smarty->assign(
                        'shop_logo_path',
                        _MODULE_DIR_.'marketplace/views/img/shop_img/'.$mpShopImage
                    );
                } else {
                    $this->context->smarty->assign(
                        'shop_logo_path',
                        _MODULE_DIR_.'marketplace/views/img/shop_img/defaultshopimage.jpg'
                    );
                }

                //If Mp seller staff module enabled
                if (Module::isEnabled('mpsellerstaff')) {
                    //If Mp staff is enable then check - Is this staff is able to edit seller permission
                    $staffDetails = WkMpSellerStaff::getStaffInfoByIdCustomer($this->context->customer->id);
                    if ($staffDetails
                        && $staffDetails['active']
                        && $staffDetails['id_seller']
                        && $staffDetails['seller_status']
                    ) {
                        $idTab = WkMpTabList::MP_PRODUCT_TAB; //For Product
                        $productPermission = WkMpSellerStaffPermission::getStaffPermission(
                            $staffDetails['id_staff'],
                            $idTab
                        );
                        if ($productPermission && $productPermission['view'] && $productPermission['edit']) {
                            $allowProductEdit = true; // allow staff to view and edit seller product
                        }
                    }
                }

                if ($allowProductEdit) {
                    // allow seller or staff to edit seller product from product page
                    $this->context->smarty->assign('wk_mp_product_link', $this->context->link->getModuleLink(
                        'marketplace',
                        'updateproduct',
                        array('id_mp_product' => $sellerProduct['id_mp_product'])
                    ));
                }
                // END of Edit product button section

                $this->context->smarty->assign(array(
                    'product_page' => 1,
                    'mp_seller_info' => $seller,
                    'sellerprofile_link' => $this->context->link->getModuleLink('marketplace', 'sellerprofile', array('mp_shop_name' => $seller['link_rewrite'])),
                    'shopstore_link' => $this->context->link->getModuleLink('marketplace', 'shopstore', array('mp_shop_name' => $seller['link_rewrite'])),
                    'call_ajax' => Tools::getValue('action') ? Tools::getValue('action') : Tools::getValue('ajax'),
                ));
                WkMpSeller::checkSellerAccessPermission($seller['seller_details_access']);

                return $this->fetch('module:marketplace/views/templates/hook/mp_soldby.tpl');
            }
        }
    }

    /**
     * Display Sold by with seller's shop name and seller rating and also display edit product button on seller login
     *
     * @return html
     */
    public function hookDisplayProductAdditionalInfo()
    {
        return $this->hookDisplayProductButtons();
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        if ('AdminCarriers' === Tools::getValue('controller')) {
            //Change shipping distribution type of admin carriers
            $jsDef = array(
                'path_admin_mp_shipping' => $this->context->link->getAdminLink('AdminSellerOrders'),
                'success_msg' => $this->l('Updated Successfully'),
                'error_msg' => $this->l('Some error occured...'),
            );

            Media::addJsDef($jsDef);

            $this->context->controller->addJs($this->_path.'views/js/mp_shipping_distribution.js');
        }
    }

    public function hookActionFrontControllerSetMedia()
    {
        if ('product' === $this->context->controller->php_self) {
            //To display seller rating
            if ($sellerProduct = WkMpSellerProduct::getSellerProductByPsIdProduct(Tools::getValue('id_product'))) {
                if ($sellerRating = WkMpSellerReview::getSellerAvgRating($sellerProduct['id_seller'])) {
                    Media::addJsDef(array(
                        'sellerRating' => $sellerRating,
                        'rating_start_path' => _MODULE_DIR_.$this->name.'/views/img/',
                    ));

                    $this->context->controller->registerJavascript(
                        'module-marketplace-raty-js',
                        'modules/'.$this->name.'/views/js/libs/jquery.raty.min.js',
                        array('position' => 'bottom', 'priority' => 999)
                    );

                    //Display seller rating on product page through contactseller.js file
                    $this->context->controller->registerJavascript(
                        'module-marketplace-contactseller-js',
                        'modules/'.$this->name.'/views/js/contactseller.js',
                        array('position' => 'bottom', 'priority' => 999)
                    );
                }
            }
        }

        $this->context->controller->registerStylesheet(
            'module-marketplace-seller-rating-css',
            'modules/'.$this->name.'/views/css/mp_seller_rating.css',
            array('position' => 'bottom', 'priority' => 999)
        );

        $this->context->controller->registerStylesheet(
            'module-marketplace-mpheader-css',
            'modules/'.$this->name.'/views/css/mp_header.css',
            array('position' => 'bottom', 'priority' => 999)
        );
        $this->context->controller->registerJavascript(
            'module-marketplace-mpheader-js',
            'modules/'.$this->name.'/views/js/mp_header.js',
            array('position' => 'bottom', 'priority' => 999)
        );
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        $idOrder = $params['id_order'];
        $idOrderState = $params['newOrderStatus']->id;

        $order = new Order($idOrder);

        $objMpOrderDetail = new WkMpSellerOrderDetail();
        // if order commission not calculated
        if (!$objMpOrderDetail->getOrderCommissionDetails($idOrder)) {
            $params['cart'] = new Cart((int) $order->id_cart);
            $customer = new Customer((int) $order->id_customer);
            $address = new Address((int) $order->id_address_delivery, (int) $order->id_lang);
            $idCurrency = (int) $order->id_currency;

            $objMpSplit = new WkMpSellerPaymentSplit();
            $splitAmount = $objMpSplit->sellerWiseSplitedAmount($params, true);
            if ($splitAmount) {
                // adding shipping to admin's table when any order have seller's product
                WkMpAdminShipping::addingAdminShipping($idOrder, $splitAmount, $params['cart']);
                foreach ($splitAmount as $idCustomerSeller => $mpProduct) {
                    if ($idCustomerSeller != 'admin') {
                        $idSellerOrder = WkMpSellerOrder::updateSellerOrder($idCustomerSeller, $mpProduct);
                        $mpSeller = WkMpSeller::getSellerDetailByCustomerId($idCustomerSeller);
                        if ($idSellerOrder) {
                            // save seller's shipping cost seller wise
                            // product list of current seller
                            foreach ($mpProduct['product_list'] as $idProduct => $productAttribute) {
                                foreach ($productAttribute as $idProductAttribute => $productCustomization) {
                                    foreach ($productCustomization as $idProductCustomization => $product) {
                                        // Creating product list for order detail of marketplace
                                        $objMpOrderDetails = new WkMpSellerOrderDetail();
                                        $objMpOrderDetails->id_seller_order = $idSellerOrder;
                                        $objMpOrderDetails->product_id = $idProduct;
                                        $objMpOrderDetails->product_attribute_id = $idProductAttribute;
                                        $objMpOrderDetails->id_customization = $idProductCustomization;
                                        $objMpOrderDetails->seller_customer_id = $product['id_customer'];
                                        $objMpOrderDetails->seller_name = $product['firstname'].' '.$product['lastname'];
                                        $objMpOrderDetails->product_name = $product['product_name'];
                                        $objMpOrderDetails->quantity = $product['product_quantity'];
                                        $objMpOrderDetails->price_ti = Tools::ps_round(
                                            $product['total_price_tax_incl'],
                                            6
                                        );
                                        $objMpOrderDetails->price_te = Tools::ps_round(
                                            $product['total_price_tax_excl'],
                                            6
                                        );
                                        $objMpOrderDetails->admin_commission = Tools::ps_round(
                                            $product['admin_commission'],
                                            6
                                        );
                                        if ($product['admin_tax'] < 0) {
                                            $product['admin_tax'] = 0;
                                        }
                                        $objMpOrderDetails->admin_tax = Tools::ps_round($product['admin_tax'], 6);
                                        $objMpOrderDetails->seller_amount = Tools::ps_round((float) $product['seller_amount'], 6);
                                        $objMpOrderDetails->seller_tax = Tools::ps_round(
                                            (float) $product['seller_tax'],
                                            6
                                        );
                                        $objMpOrderDetails->id_order = $idOrder;
                                        $objMpOrderDetails->commission_type = $product['commission_type'];
                                        $objMpOrderDetails->commission_rate = $product['commission_rate'];
                                        $objMpOrderDetails->commission_amt = $product['commission_amt'];
                                        $objMpOrderDetails->commission_tax_amt = $product['commission_tax_amt'];
                                        $objMpOrderDetails->tax_distribution_type = Configuration::get('WK_MP_PRODUCT_TAX_DISTRIBUTION');
                                        $objMpOrderDetails->id_currency = $idCurrency;
                                        if ($objMpOrderDetails->save()) {
                                            $idMpOrderDetail = $objMpOrderDetails->id;

                                            // Creating transaction history (product wise)
                                            $objTransactionHistory = new WkMpSellerTransactionHistory();
                                            $idMpTransaction = $objTransactionHistory->saveSellerTransactionData(
                                                $product['id_customer'],
                                                $idCurrency,
                                                $idMpOrderDetail,
                                                Tools::ps_round($product['seller_amount'], 6),
                                                Tools::ps_round($product['seller_tax'], 6),
                                                false,
                                                false,
                                                false,
                                                Tools::ps_round($product['admin_commission'], 6),
                                                Tools::ps_round($product['admin_tax'], 6),
                                                false,
                                                false,
                                                $order->payment,
                                                WkMpSellerTransactionHistory::MP_SELLER_ORDER,
                                                $idOrder,
                                                $this->l('Seller product sold'),
                                                WkMpSellerTransactionHistory::MP_SELLER_ORDER_STATUS
                                            );

                                            if ($idMpTransaction) {
                                                //update(decrease) seller product quantity after order
                                                $mpProductDetail = WkMpSellerProduct::getSellerProductByPsIdProduct($idProduct);
                                                if ($mpProductDetail) {
                                                    $objMpProduct = new WkMpSellerProduct(
                                                        $mpProductDetail['id_mp_product']
                                                    );
                                                    $currentProductQty = $objMpProduct->quantity;
                                                    $objMpProduct->quantity = $currentProductQty - $product['product_quantity'];
                                                    $objMpProduct->save();

                                                    //Send mail of out of Stock if quantity is less than or equal low stock level
                                                    if (!$idProductAttribute && $mpProductDetail['low_stock_alert']) {
                                                        //Send only in standard product case
                                                        if ($objMpProduct->quantity <= $mpProductDetail['low_stock_threshold']) {
                                                            //Send out of stock mail to seller
                                                            WkMpSellerProduct::sendMail(
                                                                $mpProductDetail['id_mp_product'],
                                                                4
                                                            );
                                                        }
                                                    }

                                                    WkMpProductAttribute::updateAttributeQuantity(
                                                        $mpProductDetail['id_mp_product'],
                                                        $idProductAttribute,
                                                        $product['product_quantity'],
                                                        1
                                                    );
                                                    Hook::exec(
                                                        'actionSellerProductQtyUpdateAfterOrder',
                                                        array(
                                                            'id_order' => $idOrder,
                                                            'ordered_product_qty' => $product['product_quantity'],
                                                            'id_mp_product' => $mpProductDetail['id_mp_product'],
                                                            'id_ps_product_attribute' => $idProductAttribute,
                                                            'update_mode' => 1
                                                        )
                                                    );
                                                }
                                            }
                                        }

                                        unset($objMpOrderDetails);
                                    }
                                }
                            }

                            // Manage Seller Order Status In Mp table
                            $objOrderStatus = new WkMpSellerOrderStatus();
                            $objOrderStatus->processSellerOrderStatus($idOrder, $mpSeller['id_seller'], $idOrderState);

                            Hook::exec(
                                'actionSellerPaymentTransaction',
                                array(
                                    'id_currency' => $idCurrency,
                                    'seller_cart_product_data' => $mpProduct,
                                )
                            );

                            // order mail to every seller if his/her product in the cart
                            if (Configuration::get('WK_MP_MAIL_SELLER_PRODUCT_SOLD')) {
                                $this->sellersOrderMail(
                                    $mpProduct,
                                    $customer,
                                    $address,
                                    $idCurrency,
                                    $mpSeller['id_seller'],
                                    $idOrder
                                );
                            }
                        }
                    }
                }
            }
        }

        // Manage Seller Order Status in MP table, in case admin update seller's order status
        // For seller, processSellerOrderStatus function is already calling on mporderdetails page
        if (Tools::getValue('controller') !== 'mporderdetails') {
            $sellerArray = array();
            if ($products = $order->getProducts()) {
                foreach ($products as $prod) {
                    $seller = WkMpSellerOrderDetail::getSellerFromOrderProduct($idOrder, $prod['product_id']);
                    if ($seller) {
                        $sellerArray[$seller['seller_id']] = $prod['product_id'];
                    }
                }
            }
            if ($sellerArray && count($sellerArray) > 0) { //if admin change order status then change on each seller order
                foreach ($sellerArray as $idSeller => $prod) {
                    if ($prod) {
                        $objOrderStatus = new WkMpSellerOrderStatus();
                        $objOrderStatus->processSellerOrderStatus($idOrder, $idSeller, $idOrderState);
                    }
                }
            }
        }

        // If Admin change whole order status as cancelled
        if ($idOrderState == Configuration::get('PS_OS_CANCELED')) {
            $transactionData = $this->l('#Cancel');
            $remark = $this->l('Order Cancel');

            //Manage seller transaction only if seller order is cancelled or refunded
            $objTransactionHistory = new WkMpSellerTransactionHistory();
            $getOrderTransactionRows = $objTransactionHistory->getOrderTransactionHistoryByOrderId($idOrder);
            if ($getOrderTransactionRows) {
                //Increase product qty if order status updated as Cancelled (for whole ps order)
                if ($products = $order->getProducts()) {
                    foreach ($products as $orderProduct) {
                        $mpProductDetail = WkMpSellerProduct::getSellerProductByPsIdProduct($orderProduct['product_id']);
                        if ($mpProductDetail) {
                            $objMpProduct = new WkMpSellerProduct($mpProductDetail['id_mp_product']);
                            $currentProductQty = $objMpProduct->quantity;
                            $objMpProduct->quantity = $currentProductQty + $orderProduct['product_quantity'];
                            $objMpProduct->save();

                            if ($orderProduct['product_attribute_id']) {
                                WkMpProductAttribute::updateAttributeQuantity(
                                    $mpProductDetail['id_mp_product'],
                                    $orderProduct['product_attribute_id'],
                                    $orderProduct['product_quantity'],
                                    2
                                );
                            }
                            Hook::exec(
                                'actionSellerProductQtyUpdateAfterOrder',
                                array(
                                    'id_order' => $idOrder,
                                    'ordered_product_qty' => $orderProduct['product_quantity'],
                                    'id_mp_product' => $mpProductDetail['id_mp_product'],
                                    'id_ps_product_attribute' => $orderProduct['product_attribute_id'],
                                    'update_mode' => 2
                                )
                            );
                        }
                    }
                }

                foreach ($getOrderTransactionRows as $orderTransaction) {
                    $sellerRefundedAmount = $orderTransaction['seller_amount'] + $orderTransaction['seller_tax'] + $orderTransaction['seller_shipping'];
                    $adminRefundedAmount = $orderTransaction['admin_commission'] + $orderTransaction['admin_tax'] + $orderTransaction['admin_shipping'];

                    $idMpTransaction = $objTransactionHistory->saveSellerTransactionData(
                        $orderTransaction['id_customer_seller'],
                        $orderTransaction['id_currency'],
                        false,
                        Tools::ps_round($orderTransaction['seller_amount'], 6),
                        Tools::ps_round($orderTransaction['seller_tax'], 6),
                        Tools::ps_round($orderTransaction['seller_shipping'], 6),
                        Tools::ps_round($sellerRefundedAmount, 6),
                        false,
                        Tools::ps_round($orderTransaction['admin_commission'], 6),
                        Tools::ps_round($orderTransaction['admin_tax'], 6),
                        Tools::ps_round($orderTransaction['admin_shipping'], 6),
                        Tools::ps_round($adminRefundedAmount, 6),
                        'N/A',
                        WkMpSellerTransactionHistory::MP_ORDER_CANCEL,
                        $orderTransaction['id_transaction'].$transactionData,
                        $remark,
                        WkMpSellerTransactionHistory::MP_ORDER_CANCEL_STATUS
                    );
                    if ($idMpTransaction) {
                        $objTransaction = new WkMpSellerTransactionHistory(
                            $orderTransaction['id_seller_transaction_history']
                        );
                        $objTransaction->status = WkMpSellerTransactionHistory::MP_ORDER_CANCEL_STATUS;
                        $objTransaction->update();
                    }
                }
            }
        }
    }

    public function hookDisplayCustomerAccount()
    {
        if (Module::isEnabled('mpsellerstaff')) {
            $staffDetails = WkMpSellerStaff::getStaffInfoByIdCustomer($this->context->customer->id);
            if (!$staffDetails) {
                //If customer is not any seller's staff, not even active or deactive then can access Marketplace Shop Panel
                return $this->fetch('module:marketplace/views/templates/hook/customeraccount.tpl');
            }
        } else {
            return $this->fetch('module:marketplace/views/templates/hook/customeraccount.tpl');
        }
    }

    /**
     * Display restriction message on header
     *
     * @param array $params controller data
     */
    public function hookDisplayAdminAfterHeader($params)
    {
        if ('AdminProducts' === Tools::getValue('controller')) {
            if (Tools::getValue('notallow')) {
                $this->context->smarty->assign('seller_not_active', 1);
                return $this->display(__FILE__, 'product-restrict-message.tpl');
            }
        }
    }

    /**
     * Restrict product to activate if seller is deactive
     *
     * @param array $params Product details
     */
    public function hookActionObjectProductUpdateBefore($params)
    {
        if (isset($params['object']->id) && Tools::getValue('controller') == '') {
            $idProduct = $params['object']->id;
            if ($mpProduct = WkMpSellerProduct::getSellerProductByPsIdProduct($idProduct)) {
                $objMpSeller = new WkMpSeller($mpProduct['id_seller']);
                if (!$objMpSeller->active) { //if seller is not active
                    Tools::redirectAdmin(
                        $this->context->link->getAdminLink('AdminProducts', true, array('notallow' => 1))
                    );
                }
            }
        }
    }

    /**
     * Active/deactive seller product from catalog.
     *
     * We can send email after active/deactive product from catalog because this hook is also called
     * when we save the product from save button. But we want action only when admin click on active/deactive button.
     *
     * @param array $params Product details
     */
    public function hookActionProductSave($params)
    {
        //If function call only when admin active/deactive (or also save) from Catalog
        if ($params['id_product'] && $params['product'] && Tools::getValue('controller') == '') {
            $idProduct = $params['id_product'];
            if ($mpProduct = WkMpSellerProduct::getSellerProductByPsIdProduct($idProduct)) {
                $objMpSeller = new WkMpSeller($mpProduct['id_seller']);
                if ($objMpSeller->active) { //if seller is active
                    $objMpProduct = new WkMpSellerProduct($mpProduct['id_mp_product']);
                    if ($params['product']->active) {
                        //going to active
                        $objMpProduct->active = 1;
                        $objMpProduct->status_before_deactivate = 1;
                        $objMpProduct->admin_approved = 1;
                        Hook::exec('actionCatalogToogleMPProductActive', array('id_mp_product' => $mpProduct['id_mp_product'], 'active' => $objMpProduct->active));
                    } else {
                        //going to deactive
                        $objMpProduct->active = 0;
                        $objMpProduct->status_before_deactivate = 0;
                    }
                    $objMpProduct->save();
                    Hook::exec('actionCatalogToggleMPProductStatus', array('id_product' => $idProduct, 'active' => $objMpProduct->active));
                }
            }
        }
    }

    public function hookActionProductDelete($params)
    {
        if ($id_product = $params['id_product']) {
            if ($mpProduct = WkMpSellerProduct::getSellerProductByPsIdProduct($id_product)) {
                $mpProductId = $mpProduct['id_mp_product'];

                //Update id_image as mp_image_id when product is going to deactivate
                WkMpProductAttributeImage::setCombinationImagesAsMp($mpProductId);

                // Status inactive of seller product according to mp seller product id
                $objSellerProduct = new WkMpSellerProduct($mpProductId);
                $objSellerProduct->id_ps_product = 0;
                $objSellerProduct->active = 0;
                $objSellerProduct->admin_approved = 0;
                $objSellerProduct->update();

                // Status inactive of seller product image according to mp seller product id
                WkMpSellerProductImage::updateStatusBySellerIdProduct($mpProductId, 0, 0);
            }
        }
    }

    public function hookActionAdminCarriersListingFieldsModifier($list)
    {
        //Display shipping distribution section in carriers page
        if (Configuration::get('WK_MP_SHIPPING_DISTRIBUTION_ALLOW')) {
            $optionsDistributeType = array(
                'admin' => $this->l('Admin'),
                'seller' => $this->l('Seller'),
                'both' => $this->l('Both (on the basis of commission on shipping)'),
            );

            if (isset($list['select'])) {
                //By default Admin will selected
                $list['select'] .= ', IF(mcdt.`type` != "", mcdt.`type`, "admin") AS `distribute_type`';
            }
            if (isset($list['join'])) {
                $list['join'] .= ' LEFT JOIN `'._DB_PREFIX_.'wk_mp_carrier_distributor_type` mcdt
                ON (mcdt.`id_ps_reference` = a.`id_reference`)';
            }

            $list['fields']['distribute_type'] = array(
                'title' => 'Shipping Distribute To',
                'align' => 'text-center',
                'orderby' => false,
                'remove_onclick' => true,
                'type' => 'select',
                'hint' => $this->l('Distribution will applicable only on Marketplace Seller Product'),
                'list' => $optionsDistributeType,
                'filter_key' => 'mcdt!type',
                'callback' => 'callCarrierDistribution',
                'callback_object' => Module::getInstanceByName($this->name)
            );
        }
    }

    public function callCarrierDistribution($distributeType, $list)
    {
        $this->context->smarty->assign(array(
            'options_distribute_type' => array(
                'admin' => $this->l('Admin'),
                'seller' => $this->l('Seller'),
                'both' => $this->l('Both (on the basis of commission on shipping)'),
            ),
            'wk_distribute_type' => $distributeType,
            'wk_carrier_list' => $list,
        ));

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_.$this->name.'/views/templates/admin/carrier_distribution_type.tpl'
        );
    }

    /**
     * If admin add any language then an entry will add in defined $lang_tables array's lang table same as prestashop do.
     *
     * @param array $params
     */
    public function hookActionObjectLanguageAddAfter($params)
    {
        if ($params['object']->id) {
            $newIdLang = $params['object']->id;

            //Assign all lang's main table in an ARRAY
            $langTables = array('wk_mp_seller_product', 'wk_mp_seller');

            //If Admin update new language when we do entry in module all lang tables.
            WkMpHelper::updateIdLangInLangTables($newIdLang, $langTables);
        }
    }

    /**
     * If admin disable any langauge and that langauge is used in any seller's default lang
     * then ps default lang will be set as default lang for that seller.
     *
     * @param array $params
     */
    public function hookActionObjectLanguageUpdateAfter($params)
    {
        if ($params['object']->id) {
            if (!$params['object']->active) { //going to deactivate seller default lang
                $changedIdLang = $params['object']->id;
                WkMpSeller::updateSellerLanguage($changedIdLang);
            }
        }
    }

    /**
     * If admin delete any langauge and that langauge is used in any seller's default lang
     * then ps default lang will be set as default lang for that seller].
     *
     * @param array $params
     */
    public function hookActionObjectLanguageDeleteAfter($params)
    {
        if ($params['object']->id) {
            $deletedIdLang = $params['object']->id;
            WkMpSeller::updateSellerLanguage($deletedIdLang);
        }
    }

    /**
     * Make user pages url friendly.
     */
    public function hookModuleRoutes()
    {
        if (Configuration::get('WK_MP_URL_REWRITE_ADMIN_APPROVE')) {
            $wkProfileRewrite = Tools::link_rewrite(Configuration::get('WK_MP_SELLER_PROFILE_PREFIX'));
            $wkShopRewrite = Tools::link_rewrite(Configuration::get('WK_MP_SELLER_SHOP_PREFIX'));

            if (Configuration::get('WK_MP_SELLER_REVIEWS_PREFIX')) {
                $wkReviewsPrefix = Configuration::get('WK_MP_SELLER_REVIEWS_PREFIX');
            } else {
                // We have added 'else' condition so that if client update marketplace files over old version(V5.2.1)
                // And if didn't fill this condition then default value can be used.
                $wkReviewsPrefix = 'reviews';
            }
            $wkReviewsRewrite = Tools::link_rewrite($wkReviewsPrefix);

            if ($wkProfileRewrite) {
                $wkProfileRewrite .= '/';
            }
            if ($wkShopRewrite) {
                $wkShopRewrite .= '/';
            }
            if ($wkReviewsRewrite) {
                $wkReviewsRewrite .= '/';
            }

            return array(
                'module-marketplace-sellerprofile' => array(
                    'controller' => 'sellerprofile',
                    'rule' => "$wkProfileRewrite{:mp_shop_name}",
                    'keywords' => array(
                        'mp_shop_name' => array(
                        'regexp' => '[_a-zA-Z0-9\pL\pS-]*',
                        'param' => 'mp_shop_name',
                        ),
                    ),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'marketplace',
                        'controller' => 'sellerprofile',
                    ),
                ),
                'module-marketplace-shopstore' => array(
                    'controller' => 'shopstore',
                    'rule' => "$wkShopRewrite{:mp_shop_name}",
                    'keywords' => array(
                        'mp_shop_name' => array(
                        'regexp' => '[_a-zA-Z0-9\pL\pS-]*',
                        'param' => 'mp_shop_name',
                        ),
                    ),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'marketplace',
                        'controller' => 'shopstore',
                    ),
                ),
                'module-marketplace-allreviews' => array(
                    'controller' => 'allreviews',
                    'rule' => "$wkReviewsRewrite{:mp_shop_name}",
                    'keywords' => array(
                        'mp_shop_name' => array(
                        'regexp' => '[_a-zA-Z0-9\pL\pS-]*',
                        'param' => 'mp_shop_name',
                        ),
                    ),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'marketplace',
                        'controller' => 'allreviews',
                    ),
                ),
            );
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        if ((Tools::getValue('controller') == 'AdminLanguages') && Module::isEnabled('marketplace')) {
            $this->context->controller->warnings[] = $this->l('When you deactivate or delete any language and if that language is set as default language for any seller in Marketplace module then Admin default language will set as that seller default langauge.');
        }
    }

    public function hookActionTooglePsCombinationStatus($params)
    {
        //If Combination activate/deactivate module is enabled
        if (isset($params['id_ps_product_attribute'])) {
            $idMpProductAttribute = WkMpProductAttribute::getMpIdProductAttributeByPsIdAttribute($params['id_ps_product_attribute']);
            if ($idMpProductAttribute) {
                $objMpProductAttribute = new WkMpProductAttribute($idMpProductAttribute);
                if ($params['active']) {
                    $combiStatus = 1; //going to active
                } else {
                    $combiStatus = 0; //going to deactive
                }

                $objMpProductAttribute->active = $combiStatus;
                $objMpProductAttribute->save();
            }
        }
    }

    public function hookDisplayOrderDetail()
    {
        if (Configuration::get('WK_MP_SELLER_ORDER_TRACKING_ALLOW')
        && Configuration::get('WK_MP_TRACKING_ORDER_HISTORY_ALLOW')) {
            //Display tracking number and url to the customer
            $idOrder = Tools::getValue('id_order');
            $order = new Order($idOrder);
            if ($products = $order->getProducts()) {
                $sellerTrackingData = array();
                $objOrderStatus = new WkMpSellerOrderStatus();
                foreach ($products as $prod) {
                    $seller = WkMpSellerProduct::getSellerProductByPsIdProduct($prod['product_id']);
                    if ($seller) {
                        $trackingData = $objOrderStatus->isOrderExist($idOrder, $seller['id_seller']);
                        if ($trackingData && $trackingData['tracking_number'] && $trackingData['tracking_url']) {
                            $sellerDetails = WkMpSeller::getSeller($seller['id_seller'], $this->context->language->id);
                            if ($sellerDetails) {
                                $sellerTrackingData[$seller['id_seller']]['shop_name'] = $sellerDetails['shop_name'];
                                $sellerTrackingData[$seller['id_seller']]['shopstore_link'] = $this->context->link->getModuleLink(
                                    'marketplace',
                                    'shopstore',
                                    array('mp_shop_name' => $sellerDetails['link_rewrite'])
                                );
                                $sellerTrackingData[$seller['id_seller']]['tracking_number'] = $trackingData['tracking_number'];

                                if (Configuration::get('WK_MP_TRACKING_NUMBER_IN_URL')) {
                                    $wkTrackingURL = Tools::strReplaceFirst(
                                        '@',
                                        $trackingData['tracking_number'],
                                        $trackingData['tracking_url']
                                    );
                                } else {
                                    $wkTrackingURL = $trackingData['tracking_url'];
                                }
                                $sellerTrackingData[$seller['id_seller']]['tracking_url'] = $wkTrackingURL;
                            }
                        }
                    }
                }

                if ($sellerTrackingData) {
                    $this->context->smarty->assign('sellerTrackingData', $sellerTrackingData);
                    return $this->fetch('module:marketplace/views/templates/hook/tracking-order-detail.tpl');
                }
            }
        }
    }

    /**
     * Module Uninstallation Process.
     */
    public function deleteConfigKeys()
    {
        $var = array(
            'WK_MP_SUPERADMIN_EMAIL', 'WK_MP_SELLER_ADMIN_APPROVE', 'WK_MP_PRODUCT_ADMIN_APPROVE',
            'WK_MP_MULTILANG_ADMIN_APPROVE', 'WK_MP_MULTILANG_DEFAULT_LANG',
            'WK_MP_SHOW_SELLER_DETAILS', 'WK_MP_GLOBAL_COMMISSION_TYPE', 'WK_MP_GLOBAL_COMMISSION',
            'WK_MP_PRODUCT_TAX_DISTRIBUTION', 'WK_MP_REVIEWS_ADMIN_APPROVE', 'WK_MP_GLOBAL_TAX_FIXED_COMMISSION',
            'WK_MP_TITLE_BG_COLOR', 'WK_MP_TITLE_TEXT_COLOR', 'WK_MP_PHONE_DIGIT',
            'WK_MP_SELLER_SHOP_SETTINGS', 'WK_MP_SELLER_PRODUCTS_SETTINGS', 'WK_MP_SELLER_COUNTRY_NEED',
            'WK_MP_FROM_MAIL_TITLE', 'WK_MP_MAIL_SELLER_REQ_APPROVE',
            'WK_MP_MAIL_SELLER_REQ_DISAPPROVE', 'WK_MP_MAIL_SELLER_PRODUCT_APPROVE',
            'WK_MP_MAIL_SELLER_PRODUCT_DISAPPROVE', 'WK_MP_MAIL_SELLER_PRODUCT_SOLD',
            'WK_MP_MAIL_ADMIN_PRODUCT_ADD','WK_MP_MAIL_PRODUCT_DELETE', 'WK_MP_MAIL_SELLER_DELETE',
            'WK_MP_MAIL_ADMIN_SELLER_REQUEST', 'WK_MP_MAIL_SELLER_PRODUCT_ASSIGN',
            'WK_MP_SELLER_PRODUCTS_DEACTIVATE_REASON', 'WK_MP_SELLER_PROFILE_DEACTIVATE_REASON',
            'WK_MP_SHOW_ADMIN_COMMISSION', 'WK_MP_TERMS_AND_CONDITIONS_CMS',
            'WK_MP_TERMS_AND_CONDITIONS_STATUS', 'WK_MP_SELLER_DETAILS_ACCESS',
            'WK_MP_COMMISSION_DISTRIBUTE_ON', 'WK_MP_URL_REWRITE_ADMIN_APPROVE',
            'WK_MP_SELLER_PROFILE_PREFIX', 'WK_MP_SELLER_SHOP_PREFIX', 'WK_MP_SELLER_APPLIED_TAX_RULE',
            'WK_MP_LINK_ON_NAV_BAR', 'WK_MP_LINK_ON_FOOTER_BAR', 'WK_MP_LINK_ON_POP_UP',
            'WK_MP_PRODUCT_UPDATE_ADMIN_APPROVE', 'WK_MP_SELLER_ADMIN_SHIPPING',
            'WK_MP_SELLER_PRODUCT_ISBN', 'WK_MP_SELLER_PRODUCT_UPC', 'WK_MP_SELLER_PRODUCT_EAN',
            'WK_MP_SELLER_PRODUCT_REFERENCE', 'WK_MP_SELLER_PRODUCT_COMBINATION', 'WK_MP_PRODUCT_FEATURE',
            'WK_MP_SOCIAL_TABS', 'WK_MP_SELLER_FACEBOOK', 'WK_MP_SELLER_TWITTER',
            'WK_MP_SELLER_YOUTUBE', 'WK_MP_SELLER_INSTAGRAM', 'WK_MP_ALLOW_CUSTOM_CSS',
            'WK_MP_SELLER_PRODUCT_AVAILABILITY', 'WK_MP_SELLER_PRODUCT_VISIBILITY', 'WK_MP_SELLER_PRODUCT_SEO',
            'WK_MP_DASHBOARD_GRAPH', 'WK_MP_PRESTA_FEATURE_ACCESS', 'WK_MP_PRESTA_ATTRIBUTE_ACCESS',
            'WK_MP_PRODUCT_MIN_QTY', 'WK_MP_PRODUCT_CONDITION', 'WK_MP_PRODUCT_WHOLESALE_PRICE',
            'WK_MP_PRODUCT_PRICE_PER_UNIT', 'WK_MP_SHOW_ADMIN_DETAILS', 'WK_MP_SELLER_FAX',
            'WK_MP_SELLER_DETAILS_PERMISSION', 'WK_MP_SELLER_ORDER_STATUS_CHANGE', 'WK_MP_CONTACT_SELLER_SETTINGS',
            'WK_MP_PRODUCT_ON_SALE', 'WK_MP_PRODUCT_DELIVERY_TIME', 'WK_MP_PRODUCT_ADDITIONAL_FEES',
            'WK_MP_PRODUCT_LOW_STOCK_ALERT', 'WK_MP_REVIEW_DISPLAY_SORT', 'WK_MP_REVIEW_HELPFUL_SETTINGS',
            'WK_MP_REVIEW_SETTINGS', 'WK_MP_SELLER_TAX_IDENTIFICATION_NUMBER', 'WK_MP_PRODUCT_ALLOW_DUPLICATE',
            'WK_MP_PRODUCT_DUPLICATE_TITLE', 'WK_MP_SELLER_REVIEWS_PREFIX', 'WK_MP_REVIEW_DISPLAY_COUNT',
            'WK_MP_PRODUCT_DUPLICATE_QUANTITY', 'WK_MP_SHIPPING_DISTRIBUTION_ALLOW',
            'WK_MP_SHIPPING_ADMIN_DISTRIBUTION', 'WK_MP_SELLER_ORDER_TRACKING_ALLOW', 'WK_MP_TRACKING_PS_UPDATE_ALLOW',
            'WK_MP_TRACKING_ORDER_HISTORY_ALLOW', 'WK_MP_GLOBAL_COMMISSION_AMOUNT', 'WK_MP_TRACKING_NUMBER_IN_URL',
            'WK_MP_GLOBAL_SHIPPING_COMMISSION',
        );
        foreach ($var as $key) {
            if (!Configuration::deleteByName($key)) {
                return false;
            }
        }

        return true;
    }

    public function uninstallTab()
    {
        $moduleTabs = Tab::getCollectionFromModule($this->name);
        if (!empty($moduleTabs)) {
            foreach ($moduleTabs as $moduleTab) {
                $moduleTab->delete();
            }
        }

        return true;
    }

    public function uninstall($keep = true)
    {
        $objMpInstall = new WkMpInstall();

        if (!parent::uninstall()
            || ($keep && !$objMpInstall->deleteMpTables())
            || !WkMpSeller::deleteTinymceSourceFile()
            || !$this->uninstallTab()
            || !$this->deleteConfigKeys()) {
            return false;
        }

        return true;
    }

    public function reset()
    {
        if (!$this->uninstall(false)) {
            return false;
        }
        if (!$this->install(false)) {
            return false;
        }

        return true;
    }

    public function registerMpHook()
    {
        return $this->registerHook(array(
                'displayCustomerAccount', 'actionFrontControllerSetMedia', 'displayMPMyAccountMenu',
                'actionProductDelete', 'displayBackOfficeHeader',
                'actionProductSave', 'displayAdminOrder', 'actionObjectLanguageAddAfter',
                'actionObjectLanguageUpdateAfter', 'actionObjectLanguageDeleteAfter',
                'moduleRoutes', 'displayNav1', 'displayNav', 'displayHeader',
                'displayProductButtons', 'displayProductAdditionalInfo', 'actionOrderStatusPostUpdate',
                'actionTooglePsCombinationStatus', 'displayMpMenu', 'actionObjectProductUpdateBefore',
                'displayMyAccountBlock', 'registerGDPRConsent', 'actionDeleteGDPRCustomer',
                'actionExportGDPRData', 'displayAdminAfterHeader', 'actionAdminCarriersListingFieldsModifier',
                'actionAdminControllerSetMedia', 'displayOrderDetail',
            ));
    }

    public function installTab($className, $tabName, $tabParentName = false)
    {
        $tabParentId = 0; //Tab will display in Back-End
        if ($tabParentName) {
            $this->createMarketplaceModuleTab($className, $tabName, $tabParentId, $tabParentName);
        } else {
            $this->createMarketplaceModuleTab($className, $tabName, $tabParentId);
        }
    }

    public function installHiddenTab($className, $tabName)
    {
        //Tab will not display in Back-End, only we can use as an admin controller
        $tabParentId = -1;
        $this->createMarketplaceModuleTab($className, $tabName, $tabParentId);
    }

    public function createMarketplaceModuleTab($className, $tabName, $tabParentId, $tabParentName = false)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $className;
        $tab->name = array();

        if ($className == 'AdminMarketplaceManagement') { //Tab name for which you want to add icon
            $tab->icon = 'shopping_cart'; //Material Icon name
        }

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tabName;
        }

        if ($tabParentName) {
            $tab->id_parent = (int) Tab::getIdFromClassName($tabParentName);
        } else {
            $tab->id_parent = $tabParentId;
        }

        $tab->module = $this->name;

        return $tab->add();
    }

    public function callInstallTab()
    {
        $this->installTab('AdminMarketplace', 'Marketplace');
        $this->installTab('AdminMarketplaceManagement', 'Marketplace', 'AdminMarketplace');

        // configuration controller
        $this->installTab('AdminManageConfiguration', 'Configuration', 'AdminMarketplaceManagement');
        $this->installTab('AdminMarketplaceGeneralSettings', 'Default Settings', 'AdminManageConfiguration');
        $this->installTab('AdminMarketplaceApprovalSettings', 'Approval Settings', 'AdminManageConfiguration');
        $this->installTab('AdminCustomerCommision', 'Commission  Settings', 'AdminManageConfiguration');
        $this->installTab('AdminPaymentMode', 'Payment Modes', 'AdminManageConfiguration');

        // seller controller
        $this->installTab('AdminManageSellerDetails', 'Sellers', 'AdminMarketplaceManagement');
        $this->installTab('AdminSellerInfoDetail', 'Seller Profile', 'AdminManageSellerDetails');
        $this->installTab('AdminSellerReviews', 'Seller Reviews', 'AdminManageSellerDetails');

        // product controller
        $this->installTab('AdminManageSellerProduct', 'Products', 'AdminMarketplaceManagement');
        $this->installTab('AdminSellerProductDetail', 'Seller Product', 'AdminManageSellerProduct');

        // order controller
        $this->installTab('AdminManageSellerOrders', 'Orders', 'AdminMarketplaceManagement');
        $this->installTab('AdminSellerOrders', 'Seller Orders', 'AdminManageSellerOrders');

        // Transaction controller
        $this->installTab('AdminManageSellerTransactions', 'Transactions', 'AdminMarketplaceManagement');
        $this->installTab('AdminSellerTransactions', 'Seller Transactions', 'AdminManageSellerTransactions');

        //Mp Combination Tab that will not display as a Tab in backend
        $this->installHiddenTab('AdminMpAttributeManage', 'Product Combination');
        $this->installHiddenTab('AdminMpGenerateCombination', 'Attribute Generator');
        $this->installHiddenTab('AdminMpShippingCommission', 'Shipping Commission Settings');

        return true;
    }

    public function install()
    {
        $objMpInstall = new WkMpInstall();

        if (!parent::install()
            || !$objMpInstall->createMpTables()
            || !$this->registerMpHook()
            || !$this->callInstallTab()
            || !$this->setMpSellerConfigurationAsApproved()
            || !$this->setMpMailConfigurationAsApproved()
            || !$this->setMpUrlRewriteConfigurationAsApproved()
            || !$this->setMpAdvertisementConfiguration()
            || !$this->setMpSocialTabConfiguration()
            || !$this->createMailLangDirectoryWithFiles()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Ps all imported language's Mail directory will be created with all files in module's mails folder
     *
     * @return tpl
     */
    public function createMailLangDirectoryWithFiles()
    {
        $mailEnDir = _PS_MODULE_DIR_.'marketplace/mails/en/';
        if (is_dir($mailEnDir)) {
            $allFiles = scandir($mailEnDir);
            $allLanguages = Language::getLanguages(false, $this->context->shop->id);
            if ($allLanguages) {
                $moduleMailDir = _PS_MODULE_DIR_.'marketplace/mails/';
                foreach ($allLanguages as $language) {
                    $langISO = $language['iso_code'];
                    //Ignore 'en' and 'fr' directory because we already have this in our module folder
                    if ($langISO != 'en' && $langISO != 'fr') {
                        //create lang dir if not exist in module mails directory
                        if (!file_exists($moduleMailDir.$langISO)) {
                            @mkdir($moduleMailDir.$langISO, 0777, true);
                        }

                        //Now if lang dir is exist or created by above code
                        if (is_dir($moduleMailDir.$langISO)) {
                            foreach ($allFiles as $fileName) {
                                if ($fileName != '.' && $fileName != '..') {
                                    $source = $mailEnDir.$fileName;
                                    $destination = $moduleMailDir.$langISO.'/'.$fileName;
                                    //if file not exist in desti directory then create that file
                                    if (!file_exists($destination) && file_exists($source)) {
                                        Tools::copy($source, $destination);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    public function setMpSellerConfigurationAsApproved()
    {
        if (!Configuration::updateValue('WK_MP_SELLER_ADMIN_APPROVE', 1)
            || !Configuration::updateValue('WK_MP_PRODUCT_ADMIN_APPROVE', 1)
            || !Configuration::updateValue('WK_MP_REVIEWS_ADMIN_APPROVE', 1)
            || !Configuration::updateValue('WK_MP_SHOW_SELLER_DETAILS', 1)
            || !Configuration::updateValue('WK_MP_SELLER_ORDER_STATUS_CHANGE', 1)
            || !Configuration::updateValue('WK_MP_SELLER_DETAILS_PERMISSION', 1)
            || !Configuration::updateValue('WK_MP_MULTILANG_ADMIN_APPROVE', 1)
            || !Configuration::updateValue('WK_MP_MULTILANG_DEFAULT_LANG', 1)
            || !Configuration::updateValue('WK_MP_COMMISSION_DISTRIBUTE_ON', 2)
            || !Configuration::updateValue('WK_MP_ALLOW_CUSTOM_CSS', 1)
            || !Configuration::updateValue('WK_MP_TITLE_BG_COLOR', '#333333')
            || !Configuration::updateValue('WK_MP_TITLE_TEXT_COLOR', '#ffffff')
            || !Configuration::updateValue('WK_MP_PHONE_DIGIT', 12)
            || !Configuration::updateValue('WK_MP_GLOBAL_COMMISSION_TYPE', WkMpCommission::WK_COMMISSION_PERCENTAGE)
            || !Configuration::updateValue('WK_MP_GLOBAL_COMMISSION', 10)
            || !Configuration::updateValue('WK_MP_PRODUCT_TAX_DISTRIBUTION', 'admin')// default tax distribute to admin
            || !Configuration::updateValue('WK_MP_GLOBAL_TAX_FIXED_COMMISSION', 0)
            || !Configuration::updateValue('WK_MP_DASHBOARD_GRAPH', '2')
            || !Configuration::updateValue('WK_MP_REVIEW_SETTINGS', '1')
            || !Configuration::updateValue('WK_MP_REVIEW_DISPLAY_SORT', '1')
            || !Configuration::updateValue('WK_MP_REVIEW_DISPLAY_COUNT', '2')
            || !Configuration::updateValue('WK_MP_SELLER_ORDER_TRACKING_ALLOW', 1)
            || !Configuration::updateValue('WK_MP_GLOBAL_SHIPPING_COMMISSION', 10)
        ) {
            return false;
        }

        if ($idEmployee = WkMpHelper::getSupperAdmin()) {
            $employee = new Employee($idEmployee);
            Configuration::updateValue('WK_MP_SUPERADMIN_EMAIL', $employee->email);
        }

        if ($this->sellerDetailsView) {
            $sellerDetailsAccess = array();
            foreach ($this->sellerDetailsView as $sellerDetailsViewVal) {
                $sellerDetailsAccess[] = $sellerDetailsViewVal['id_group'];
            }

            Configuration::updateValue('WK_MP_SELLER_DETAILS_ACCESS', Tools::jsonEncode($sellerDetailsAccess));
        }

        $sellerOrderStatus = array();
        $statuses = OrderState::getOrderStates((int) $this->context->language->id);
        asort($statuses);
        foreach ($statuses as $status) {
            $sellerOrderStatus[] = $status['id_order_state'];
        }
        Configuration::updateValue('WK_MP_SELLER_ORDER_STATUS_ACCESS', Tools::jsonEncode($sellerOrderStatus));

        return true;
    }

    public function setMpMailConfigurationAsApproved()
    {
        if (!Configuration::updateValue('WK_MP_MAIL_SELLER_REQ_APPROVE', 1)
            || !Configuration::updateValue('WK_MP_MAIL_SELLER_REQ_DISAPPROVE', 1)
            || !Configuration::updateValue('WK_MP_MAIL_SELLER_PRODUCT_APPROVE', 1)
            || !Configuration::updateValue('WK_MP_MAIL_SELLER_PRODUCT_DISAPPROVE', 1)
            || !Configuration::updateValue('WK_MP_MAIL_SELLER_PRODUCT_ASSIGN', 1)
            || !Configuration::updateValue('WK_MP_MAIL_SELLER_PRODUCT_SOLD', 1)
            || !Configuration::updateValue('WK_MP_MAIL_ADMIN_SELLER_REQUEST', 1)
            || !Configuration::updateValue('WK_MP_MAIL_ADMIN_PRODUCT_ADD', 1)
            || !Configuration::updateValue('WK_MP_MAIL_SELLER_DELETE', 1)
            || !Configuration::updateValue('WK_MP_MAIL_PRODUCT_DELETE', 1)
        ) {
            return false;
        }

        return true;
    }

    public function setMpUrlRewriteConfigurationAsApproved()
    {
        if (!Configuration::updateValue('WK_MP_URL_REWRITE_ADMIN_APPROVE', 1)
            || !Configuration::updateValue('WK_MP_SELLER_PROFILE_PREFIX', 'profile')
            || !Configuration::updateValue(
                'PS_ROUTE_module-marketplace-sellerprofile',
                'profile/{:mp_shop_name}'
            )
            || !Configuration::updateValue('WK_MP_SELLER_SHOP_PREFIX', 'shop')
            || !Configuration::updateValue(
                'PS_ROUTE_module-marketplace-shopstore',
                'shop/{:mp_shop_name}'
            )
            || !Configuration::updateValue('WK_MP_SELLER_REVIEWS_PREFIX', 'reviews')
            || !Configuration::updateValue(
                'PS_ROUTE_module-marketplace-allreviews',
                'reviews/{:mp_shop_name}'
            )
        ) {
            return false;
        }

        return true;
    }

    public function setMpAdvertisementConfiguration()
    {
        if (!Configuration::updateValue('WK_MP_LINK_ON_NAV_BAR', 1)
            || !Configuration::updateValue('WK_MP_LINK_ON_FOOTER_BAR', 1)
        ) {
            return false;
        }

        return true;
    }

    public function setMpSocialTabConfiguration()
    {
        if (!Configuration::updateValue('WK_MP_SOCIAL_TABS', 1)
            || !Configuration::updateValue('WK_MP_SELLER_FACEBOOK', 1)
            || !Configuration::updateValue('WK_MP_SELLER_TWITTER', 1)
            || !Configuration::updateValue('WK_MP_SELLER_YOUTUBE', 1)
            || !Configuration::updateValue('WK_MP_SELLER_INSTAGRAM', 1)
        ) {
            return false;
        }

        return true;
    }
}
