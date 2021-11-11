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

class AdminMarketplaceApprovalSettingsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'configuration';

        parent::__construct();
        $this->toolbar_title = $this->l('Manage Approval Settings');

        if (Module::isEnabled('wkcombinationcustomize')) {
            $wkCombinationCustomize = 1;
        } else {
            $wkCombinationCustomize = 0;
            Configuration::updateValue('WK_MP_PRODUCT_COMBINATION_CUSTOMIZE', 0);
        }

        $mpShippingModule = 0;
        if (Module::isEnabled('mpshipping')) {
            $mpShippingModule = 1;
        }

        if ((_PS_VERSION_ < '1.7.3.0') && Configuration::get('WK_MP_PRODUCT_DELIVERY_TIME')) {
            //Delivery time feature is not added in PS V1.7.3.0 and above versions
            Configuration::updateValue('WK_MP_PRODUCT_DELIVERY_TIME', 0);
        }

        $listReviewShow = array(
            array('id' => '1', 'name' => $this->l('Sort By Most Recent Review')),
            array('id' => '2', 'name' => $this->l('Sort By Most Helpful Review')),
        );

        $this->context->smarty->assign(
            'shipping_commission_link',
            $this->context->link->getAdminLink('AdminMpShippingCommission')
        );

        $this->fields_options = array(
            'profileapprovalsettings' => array(
                'title' => $this->l('Seller Profile Settings'),
                'icon' => 'icon-user',
                'fields' => array(
                    'WK_MP_REVIEWS_ADMIN_APPROVE' => array(
                        'title' => $this->l('Seller reviews to be approved by admin '),
                        'hint' => $this->l('If No, Marketplace Seller review will be automatically approved.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_SELLER_PROFILE_DEACTIVATE_REASON' => array(
                        'title' => $this->l('Seller profile deactivation needs reason'),
                        'hint' => $this->l('If Yes, Admin needs to provide a reason for deactivating seller\'s profile.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_SELLER_SHOP_SETTINGS' => array(
                        'title' => $this->l('Sellers can activate/deactivate their shop'),
                        'hint' => $this->l('Sellers can enable and disable their shop.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_SELLER_COUNTRY_NEED' => array(
                        'title' => $this->l('Sellers need to provide their city, country and zip/postal code'),
                        'hint' => $this->l('If Yes, Seller/Admin has to fill city, country and zip/postal code in seller address. Zip/postal code will be enable on the basis of country settings.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_SELLER_FAX' => array(
                        'title' => $this->l('Sellers can provide fax number'),
                        'hint' => $this->l('If Yes, Seller will be able to add fax in their profile.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_SELLER_TAX_IDENTIFICATION_NUMBER' => array(
                        'title' => $this->l('Sellers can provide tax identification number'),
                        'hint' => $this->l('If Yes, Seller will be able to add tax identification number in their profile.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_SHOW_ADMIN_DETAILS' => array(
                        'title' => $this->l('Sellers can contact admin via Email'),
                        'hint' => $this->l('If Yes, Seller can contact admin via Email from Edit profile page.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_PRESTA_ATTRIBUTE_ACCESS' => array(
                        'title' => $this->l('Sellers can manage attributes and their values'),
                        'hint' => $this->l('If Yes, Sellers can add, edit and delete prestashop attributes and their values.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_PRESTA_FEATURE_ACCESS' => array(
                        'title' => $this->l('Sellers can manage features and their values'),
                        'hint' => $this->l('If Yes, Sellers can add, edit and delete prestashop features and their values. '),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_SOCIAL_TABS' => array(
                        'title' => $this->l('Sellers can provide their social profile links'),
                        'hint' => $this->l('If Yes, Sellers will able to add their social IDS like Facebook ID, Twitter ID, Youtube ID and Instagram ID'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                    ),
                    'WK_MP_SELLER_FACEBOOK' => array(
                        'title' => $this->l('Facebook'),
                        'hint' => $this->l('If Yes, Sellers will be able to add their facebook id.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                        'form_group_class' => 'wk_mp_social_tab',
                    ),
                    'WK_MP_SELLER_TWITTER' => array(
                        'title' => $this->l('Twitter'),
                        'hint' => $this->l('If Yes, Sellers will be able to add their twitter id.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                        'form_group_class' => 'wk_mp_social_tab',
                    ),
                    'WK_MP_SELLER_YOUTUBE' => array(
                        'title' => $this->l('Youtube'),
                        'hint' => $this->l('If Yes, Sellers will be able to add their Youtube id.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                        'form_group_class' => 'wk_mp_social_tab',
                    ),
                    'WK_MP_SELLER_INSTAGRAM' => array(
                        'title' => $this->l('Instagram'),
                        'hint' => $this->l('If Yes, Sellers will be able to add their instagram id.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                        'form_group_class' => 'wk_mp_social_tab',
                    ),
                    'WK_MP_SELLER_DETAILS_PERMISSION' => array(
                        'title' => $this->l('Sellers can manage their display settings'),
                        'hint' => $this->l('If Yes, Seller will be able to change the display settings as per the options provided by the admin in Default Settings.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                    ),
                ),
                'submit' => array('title' => $this->l('Save'))
            ),
            'productapprovalsettings' => array(
                'title' => $this->l('Seller Product Approval Settings'),
                'icon' =>    'icon-list',
                'fields' => array(
                    'WK_MP_PRODUCT_ADMIN_APPROVE' => array(
                        'title' => $this->l('Product need to be approved by admin'),
                        'hint' => $this->l('If No, Marketplace Seller Product will be automatically approved.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_SELLER_PRODUCTS_DEACTIVATE_REASON' => array(
                        'title' => $this->l('Seller products deactivation needs reason'),
                        'hint' => $this->l('If Yes, Admin needs to provide a reason for deactivating seller\'s product.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_SELLER_PRODUCTS_SETTINGS' => array(
                        'title' => $this->l('Sellers can activate/deactivate their products'),
                        'hint' => $this->l("If Yes, Sellers can enable and disable their products when seller's products are created in catalog."),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_PRODUCT_UPDATE_ADMIN_APPROVE' => array(
                        'title' => $this->l('Updated products has to be approved by admin'),
                        'hint' => $this->l('If Yes, Product need to be approved by admin after updated by seller'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                    ),
                    'WK_MP_SHOW_ADMIN_COMMISSION' => array(
                        'title' => $this->l('Show admin commission to seller'),
                        'hint' => $this->l('Display admin commission to seller on add/update product and product details page.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_SELLER_APPLIED_TAX_RULE' => array(
                        'title' => $this->l('Sellers can apply tax rule on their products'),
                        'hint' => $this->l('If Yes, Seller will be able to apply tax rule on product.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_SELLER_PRODUCT_SEO' => array(
                        'title' => $this->l('Sellers can add SEO on their products'),
                        'hint' => $this->l('If Yes, Seller will be able to add SEO on product.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_SELLER_PRODUCT_VISIBILITY' => array(
                        'title' => $this->l('Sellers can set product visibility options on their products'),
                        'hint' => $this->l('If Yes, Seller will be able to change product visibility of product.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_SELLER_PRODUCT_AVAILABILITY' => array(
                        'title' => $this->l('Sellers can select Availability Preferences for their products'),
                        'hint' => $this->l('If Yes, Seller will be able to select Availability Preference for their out of stock products.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_SELLER_PRODUCT_REFERENCE' => array(
                        'title' => $this->l('Sellers can add Reference code on their products'),
                        'hint' => $this->l('If Yes, Seller will be able to add reference code on product.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_SELLER_PRODUCT_EAN' => array(
                        'title' => $this->l('Sellers can add EAN-13 or JAN barcode on their products'),
                        'hint' => $this->l('If Yes, Seller will be able to add EAN-13 or JAN barcode on product.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_SELLER_PRODUCT_UPC' => array(
                        'title' => $this->l('Sellers can add UPC barcode on their products'),
                        'hint' => $this->l('If Yes, Seller will be able to add UPC barcode on product.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_SELLER_PRODUCT_ISBN' => array(
                        'title' => $this->l('Sellers can add ISBN on their products'),
                        'hint' => $this->l('If Yes, Seller will be able to add ISBN on product.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_SELLER_PRODUCT_COMBINATION' => array(
                        'title' => $this->l('Sellers can create combinations for their products'),
                        'hint' => $this->l('If Yes, Sellers will be able to create combinations for their products using admin added attributes and values.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_PRODUCT_COMBINATION_CUSTOMIZE' => array(
                        'title' => $this->l('Sellers can activate/deactivate their combinations'),
                        'hint' => $this->l('If Yes, Seller can activate/deactivate their combinations through Prestashop Combination Activate/Deactivate Module.'),
                        'disabled' => ($wkCombinationCustomize ? false : true),
                        'desc' => (!$wkCombinationCustomize ? $this->l('Our module Prestashop combination activate/deactivate must be enable.') : ''),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                        'form_group_class' => 'wk_mp_combination_customize',
                    ),
                    'WK_MP_SELLER_ADMIN_SHIPPING' => array(
                        'title' => $this->l('Sellers can apply admin shipping on their products'),
                        'hint' => $this->l('If Yes, Sellers will be able to assign admin added shipping methods to their products.'),
                        'disabled' => ($mpShippingModule ? true : false),
                        'desc' => ($mpShippingModule ? $this->l('You can not manage this feature while seller shipping module is enabled.') : ''),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_PRODUCT_DELIVERY_TIME' => array(
                        'title' => $this->l('Sellers can add delivery time on their products'),
                        'hint' => $this->l('If Yes, Sellers will be able to add delivery time for their in-stock and out-of-stock products.'),
                        'disabled' => (_PS_VERSION_ < '1.7.3.0' ? true : false),
                        'desc' => (_PS_VERSION_ < '1.7.3.0' ? $this->l('Your Prestashop version must be greater than or equal to 1.7.3.0') : $this->l('When shipping tab is visible either through marketplace or through seller shipping module only then, delivery time can be managed by seller.')),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                    ),
                    'WK_MP_PRODUCT_ADDITIONAL_FEES' => array(
                        'title' => $this->l('Sellers can apply additional shipping costs on their products'),
                        'hint' => $this->l('If Yes, Sellers would be able to apply additional shipping costs for their products.'),
                        'desc' => $this->l('When shipping tab is visible either through marketplace or through seller shipping module only then, additional shipping cost can be managed by seller.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                    ),
                    'WK_MP_PRODUCT_FEATURE' => array(
                        'title' => $this->l('Sellers can add features on their products'),
                        'hint' => $this->l('If Yes, Sellers will be able to add admin added features to their products.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_PRODUCT_CONDITION' => array(
                        'title' => $this->l('Sellers can change condition of their products'),
                        'hint' => $this->l('If Yes, Sellers will be able to change condition of their products.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_PRODUCT_MIN_QTY' => array(
                        'title' => $this->l('Sellers can add minimum quantity on their products'),
                        'hint' => $this->l('If Yes, Sellers will be able to add minimum quantity to their products.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_PRODUCT_LOW_STOCK_ALERT' => array(
                        'title' => $this->l('Sellers can add low stock level on their products'),
                        'hint' => $this->l('If Yes, Sellers will be able to get notification on the basis of low stock level on their products.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_PRODUCT_WHOLESALE_PRICE' => array(
                        'title' => $this->l('Sellers can add wholesale price on their products'),
                        'hint' => $this->l('If Yes, Sellers will be able to add wholesale price to their products.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_PRODUCT_PRICE_PER_UNIT' => array(
                        'title' => $this->l('Sellers can add price per unit on their products'),
                        'hint' => $this->l('If Yes, Sellers will be able to add price per unit to their products.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_PRODUCT_ON_SALE' => array(
                        'title' => $this->l('Sellers can display "On sale!" flag on their products'),
                        'hint' => $this->l('If Yes, Sellers will be able to display "On sale!" flag on their products.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_PRODUCT_ALLOW_DUPLICATE' => array(
                        'title' => $this->l('Sellers can duplicate their products'),
                        'hint' => $this->l('If Yes, Sellers will be able to duplicate their products.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_PRODUCT_DUPLICATE_QUANTITY' => array(
                        'title' => $this->l('Duplicate product without stock'),
                        'hint' => $this->l('If Yes, Duplicate product will be created with zero quantity otherwise original product quantity will be set.'),
                        'type' => 'switch',
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_PRODUCT_DUPLICATE_TITLE' => array(
                        'title' => $this->l('Prefix title for duplicate product name'),
                        'hint' => $this->l('This title will be added as prefix in duplicate product name, if it is not already added.'),
                        'desc' => $this->l('Leave blank if you do not want to add any prefix title'),
                        'type' => 'textLang',
                    ),
                ),
                'submit' => array('title' => $this->l('Save'))
            ),
            'customerapprovalsettings' => array(
                'title' => $this->l('Customer Settings'),
                'icon' => 'icon-user',
                'fields' => array(
                    'WK_MP_CONTACT_SELLER_SETTINGS' => array(
                        'title' => $this->l('Only registered customers can contact with seller'),
                        'hint' => $this->l('If Yes, Visitors have to login as customer for contacting to a particular seller from profile and shop page.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                    ),
                    'WK_MP_REVIEW_SETTINGS' => array(
                        'type' => 'select',
                        'title' => $this->l('Customer can write a review or view seller rating and review'),
                        'hint' => $this->l('If Yes, Customer can give a review and can view ratings and reviews on seller profile page. Also customer can view rating on product page.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                    ),
                    'WK_MP_REVIEW_DISPLAY_SORT' => array(
                        'type' => 'select',
                        'title' => $this->l('Display review in order'),
                        'hint' => $this->l('Review will display according to selected option on Seller Profile page.'),
                        'list' => $listReviewShow,
                        'identifier' => 'id',
                        'form_group_class' => 'mp_review_settings',
                    ),
                    'WK_MP_REVIEW_DISPLAY_COUNT' => array(
                        'type' => 'text',
                        'title' => $this->l('Number of reviews on seller profile page'),
                        'hint' => $this->l('Given number of reviews will display on seller profile page after that View All button will be display.'),
                        'class' => 'fixed-width-xxl',
                        'form_group_class' => 'mp_review_settings',
                    ),
                    'WK_MP_REVIEW_HELPFUL_SETTINGS' => array(
                        'title' => $this->l('Customer can give feedback on seller review'),
                        'hint' => $this->l('If Yes, Customer can give feedback on seller review that review is helpful or not.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                        'form_group_class' => 'mp_review_settings',
                    ),
                ),
                'submit' => array('title' => $this->l('Save'))
            ),
            'shippingditributionsettings' => array(
                'title' => $this->l('Shipping Distribution Settings'),
                'icon' => 'icon-user',
                'fields' => array(
                    'WK_MP_SHIPPING_DISTRIBUTION_ALLOW' => array(
                        'title' => $this->l('Allow Shipping Distribution'),
                        'hint' => $this->l('If Yes, Shipping distribution feature will be enabled.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                    ),
                    'WK_MP_SHIPPING_ADMIN_DISTRIBUTION' => array(
                        'title' => $this->l('Distribute shipping between admin and seller both'),
                        'hint' => $this->l('If Yes, Shipping will be distributed between admin and seller.'),
                        'desc' => $this->l('If Admin product exists with any seller product in same order and that order carrier distribution is set as Seller or Both then Shipping will be distributed between admin and seller on the basis of product price or weight.').
                        $this->context->smarty->fetch(
                            _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/shipping_commission_link.tpl'
                        ),
                        'type' => 'bool',
                        'form_group_class' => 'mp_shipping_distribution',
                        'validation' => 'isBool',
                        'default' => '1',
                    ),
                ),
                'submit' => array('title' => $this->l('Save'))
            ),
            'mailconfiguration' => array(
                'title' => $this->l('Mail Configuration'),
                'icon' => 'icon-envelope',
                'fields' => array(
                    'WK_MP_FROM_MAIL_TITLE' => array(
                        'title' => $this->l('"From" title for seller mail'),
                        'hint' => $this->l('This text will be displayed in the "From" title in seller mail.'),
                        'type' => 'text',
                        'class' => 'fixed-width-xxl',
                    ),
                    'WK_MP_MAIL_SELLER_REQ_APPROVE' => array(
                        'title' => $this->l('Mail to seller on seller request approval or seller created by Admin'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_MAIL_SELLER_REQ_DISAPPROVE' => array(
                        'title' => $this->l('Mail to seller on seller disapproval'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_MAIL_SELLER_DELETE' => array(
                        'title' => $this->l('Mail to seller when admin delete seller account'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_MAIL_SELLER_PRODUCT_APPROVE' => array(
                        'title' => $this->l('Mail to seller on product approval'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_MAIL_SELLER_PRODUCT_DISAPPROVE' => array(
                        'title' => $this->l('Mail to seller on product disapproval'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_MAIL_SELLER_PRODUCT_ASSIGN' => array(
                        'title' => $this->l('Mail to seller on product assign'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_MAIL_SELLER_PRODUCT_SOLD' => array(
                        'title' => $this->l('Mail to seller on product sold'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_MAIL_PRODUCT_DELETE' => array(
                        'title' => $this->l('Mail to admin or seller on product delete'),
                        'hint' => $this->l('If admin delete product, mail will go to seller and if seller delete product, mail will go to admin.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_MAIL_ADMIN_SELLER_REQUEST' => array(
                        'title' => $this->l('Mail to admin on seller request'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                    'WK_MP_MAIL_ADMIN_PRODUCT_ADD' => array(
                        'title' => $this->l('Mail to admin when seller add new product'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1'
                    ),
                ),
                'submit' => array('title' => $this->l('Save'))
            ),
        );
    }

    public function initContent()
    {
        $this->content .= $this->renderForm();

        $this->context->smarty->assign(array(
            'content' => $this->content,
        ));

        parent::initContent();
    }

    public function renderForm()
    {
        $listCMSPages = array(
            array('id_cms' => '', 'meta_title' => $this->l('--- Select CMS Page ---')),
        );

        $cmsPages = CMS::getCMSPages($this->context->language->id);
        if ($cmsPages) {
            foreach ($cmsPages as $cpage) {
                $listCMSPages[] = $cpage;
            }
        }

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Seller Request Approval Settings'),
                'icon' => 'icon-user',
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Seller need to be approved by admin'),
                    'name' => 'WK_MP_SELLER_ADMIN_APPROVE',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
                    'hint' => $this->l('If No, Marketplace Seller request will be automatically approved.'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Sellers need to agree terms and conditions'),
                    'name' => 'WK_MP_TERMS_AND_CONDITIONS_STATUS',
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
                    'hint' => $this->l('Sellers have to agree to the terms and conditions while registering.'),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('CMS Page'),
                    'name' => 'WK_MP_TERMS_AND_CONDITIONS_CMS',
                    'hint' => $this->l('CMS page link will display on seller request page.'),
                    'form_group_class' => 'wk_mp_termsncond',
                    'options' => array(
                        'query' => $listCMSPages,
                        'id' => 'id_cms',
                        'name' => 'meta_title'
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submitSellerTermsCondition',
            ),
        );

        $this->fields_value = array(
            'WK_MP_SELLER_ADMIN_APPROVE' => Tools::getValue('WK_MP_SELLER_ADMIN_APPROVE', Configuration::get('WK_MP_SELLER_ADMIN_APPROVE')),
            'WK_MP_TERMS_AND_CONDITIONS_STATUS' => Tools::getValue('WK_MP_TERMS_AND_CONDITIONS_STATUS', Configuration::get('WK_MP_TERMS_AND_CONDITIONS_STATUS')),
            'WK_MP_TERMS_AND_CONDITIONS_CMS' => Tools::getValue('WK_MP_TERMS_AND_CONDITIONS_CMS', Configuration::get('WK_MP_TERMS_AND_CONDITIONS_CMS')),
        );

        return parent::renderForm();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitSellerTermsCondition')) {
            if (Tools::getValue('WK_MP_TERMS_AND_CONDITIONS_STATUS')) {
                if (!Tools::getValue('WK_MP_TERMS_AND_CONDITIONS_CMS')) {
                    $this->errors[] = $this->l('Choose atleast one CMS page');
                }
            }

            if (empty($this->errors)) {
                Configuration::updateValue('WK_MP_SELLER_ADMIN_APPROVE', Tools::getValue('WK_MP_SELLER_ADMIN_APPROVE'));
                Configuration::updateValue('WK_MP_TERMS_AND_CONDITIONS_STATUS', Tools::getValue('WK_MP_TERMS_AND_CONDITIONS_STATUS'));
                Configuration::updateValue('WK_MP_TERMS_AND_CONDITIONS_CMS', Tools::getValue('WK_MP_TERMS_AND_CONDITIONS_CMS'));

                Tools::redirectAdmin(self::$currentIndex.'&conf=6&token='.$this->token);
            }
        }

        if (Tools::isSubmit('submitOptionsconfiguration')) {
            foreach (Language::getLanguages(false) as $language) {
                if (Tools::getValue('WK_MP_PRODUCT_DUPLICATE_TITLE_'.$language['id_lang'])) {
                    if (!Validate::isCatalogName(Tools::getValue('WK_MP_PRODUCT_DUPLICATE_TITLE_'.$language['id_lang']))
                    ) {
                        $this->errors[] = $this->l('Prefix title for duplicate product name is invalid in ').$language['name'];
                    }
                }
            }
            if (Tools::getValue('WK_MP_REVIEW_DISPLAY_COUNT')) {
                if (!Validate::isUnsignedInt(Tools::getValue('WK_MP_REVIEW_DISPLAY_COUNT'))) {
                    $this->errors[] = $this->l('Number of reviews in customer settings must be valid.');
                }
            } else {
                $this->errors[] = $this->l('Number of reviews in customer settings is required field.');
            }
            if (empty($this->errors)) {
                //If no social tab is active and disbled whole social tabs
                if (!Tools::getValue('WK_MP_SELLER_FACEBOOK')
                    && !Tools::getValue('WK_MP_SELLER_TWITTER')
                    && !Tools::getValue('WK_MP_SELLER_YOUTUBE')
                    && !Tools::getValue('WK_MP_SELLER_INSTAGRAM')) {
                    Configuration::updateValue('WK_MP_SOCIAL_TABS', 0);
                    Tools::redirectAdmin(self::$currentIndex.'&conf=6&token='.$this->token);
                }
            }
        }

        parent::postProcess();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/mp_admin_config.js');
    }
}
