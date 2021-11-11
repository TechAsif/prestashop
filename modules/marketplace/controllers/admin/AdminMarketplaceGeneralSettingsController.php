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

class AdminMarketplaceGeneralSettingsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'configuration';

        parent::__construct();
        $this->toolbar_title = $this->l('Manage Default Settings');

        $orderStatusType = array(
            array('id' => '1', 'name' => $this->l('Payment Accepted')),
            array('id' => '2', 'name' => $this->l('Order Confirmation')),
        );

        $listDefaultLang = array(
            array('id' => '1', 'name' => $this->l('Prestashop Default Language')),
            array('id' => '2', 'name' => $this->l('Seller Default Language')),
        );

        $this->fields_options = array(
            'Configuration' => array(
                'title' => $this->l('General Configuration'),
                'fields' => array(
                    'WK_MP_SUPERADMIN_EMAIL' => array(
                        'title' => $this->l('SuperAdmin Email'),
                        'hint' => $this->l('All marketplace mails related to admin will be sent to this Email.'),
                        'type' => 'text',
                        'class' => 'fixed-width-xxl',
                    ),
                    'WK_MP_PHONE_DIGIT' => array(
                        'title' => $this->l('Seller phone maximum digit '),
                        'hint' => $this->l('Enter the maximum number of digits that a seller can enter while registering the phone number.'),
                        'type' => 'text',
                        'class' => 'fixed-width-xxl',
                    ),
                    'WK_MP_MULTILANG_ADMIN_APPROVE' => array(
                        'title' => $this->l('Marketplace Multilanguage'),
                        'hint' => $this->l('If Yes, Seller can use multi-language in Marketplace'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                    ),
                    'WK_MP_MULTILANG_DEFAULT_LANG' => array(
                        'type' => 'select',
                        'title' => $this->l('Choose Default Language'),
                        'desc' => $this->l('Note : If sellers update their product or edit their profile then product and profile data of all the languages will be filled same as the data in selected default language.'),
                        'list' => $listDefaultLang,
                        'identifier' => 'id',
                        'hint' => $this->l('When Multi language is OFF then Selected Language will be default language.'),
                        'form_group_class' => 'multilang_def_lang',
                    ),
                    'WK_MP_COMMISSION_DISTRIBUTE_ON' => array(
                        'title' => $this->l('Earnings will display on the basis of'),
                        'type' => 'select',
                        'list' => $orderStatusType,
                        'identifier' => 'id',
                        'hint' => $this->l('Admin/Seller can view their earnings of payment accepted orders or of confirmed orders on transaction page. This settings will work if prestashop full order is payment accepted.'),
                    ),
                    'WK_MP_DASHBOARD_GRAPH' => array(
                        'title' => $this->l('Seller dashboard graph will display on the basis of'),
                        'type' => 'select',
                        'list' => $orderStatusType,
                        'identifier' => 'id',
                        'hint' => $this->l('Seller can view graph of only payment accepted orders or of confirmed orders on dashboard page. This settings will work if prestashop full order is payment accepted.'),
                    ),
                ),
                'submit' => array('title' => $this->l('Save')),
            ),
            'theme' => array(
                'title' => $this->l('Theme Settings'),
                'icon' => 'icon-paint-brush',
                'fields' => array(
                    'WK_MP_ALLOW_CUSTOM_CSS' => array(
                        'title' => $this->l('Allow custom css in Front-End'),
                        'hint' => $this->l('If Yes, All seller pages will use Custom CSS.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                    ),
                    'WK_MP_TITLE_BG_COLOR' => array(
                        'title' => $this->l('Page Title Background Color '),
                        'hint' => $this->l('Background color will display in seller panel page title.'),
                        'type' => 'color',
                        'size' => 3,
                        'name' => 'WK_MP_TITLE_BG_COLOR',
                    ),
                    'WK_MP_TITLE_TEXT_COLOR' => array(
                        'title' => $this->l('Page Title Text Color '),
                        'hint' => $this->l('Text color will display in seller panel page title.'),
                        'type' => 'color',
                        'size' => 3,
                        'name' => 'WK_MP_TITLE_TEXT_COLOR',
                    ),
                ),
                'submit' => array('title' => $this->l('Save')),
            ),
            'rewriteURL' => array(
                'title' => $this->l('Rewrite URL Settings'),
                'icon' => 'icon-anchor',
                'fields' => array(
                    'WK_MP_URL_REWRITE_ADMIN_APPROVE' => array(
                        'title' => $this->l('Marketplace SEO URL'),
                        'hint' => $this->l('If Yes, Seller\'s profile page, shop page and all reviews page url will be seo compatible.'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                    ),
                    'WK_MP_SELLER_PROFILE_PREFIX' => array(
                        'title' => $this->l('Seller Profile'),
                        'hint' => $this->l('Rewritten URL for seller\'s profile page'),
                        'type' => 'text',
                        'class' => 'fixed-width-xxl',
                        'form_group_class' => 'mp_url_rewrite',
                    ),
                    'WK_MP_SELLER_SHOP_PREFIX' => array(
                        'title' => $this->l('Seller Shop'),
                        'hint' => $this->l('Rewritten URL for seller\'s shop page'),
                        'type' => 'text',
                        'class' => 'fixed-width-xxl',
                        'form_group_class' => 'mp_url_rewrite',
                    ),
                    'WK_MP_SELLER_REVIEWS_PREFIX' => array(
                        'title' => $this->l('Seller Reviews'),
                        'hint' => $this->l('Rewritten URL for seller\'s all reviews page'),
                        'type' => 'text',
                        'class' => 'fixed-width-xxl',
                        'form_group_class' => 'mp_url_rewrite',
                    ),
                ),
                'submit' => array('title' => $this->l('Save')),
            ),
            'advertisementSetting' => array(
                'title' => $this->l('Advertisement Settings'),
                'icon' => 'icon-picture',
                'fields' => array(
                    'WK_MP_LINK_ON_NAV_BAR' => array(
                        'title' => $this->l('Display "Become a Seller" Option In Navigation Bar'),
                        'hint' => $this->l('If Yes, A link with "Become a seller" Option will be displayed in navigation bar'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                    ),
                    'WK_MP_LINK_ON_FOOTER_BAR' => array(
                        'title' => $this->l('Display "Become a Seller" Option In Footer Bar'),
                        'hint' => $this->l('If Yes, A link with "Become a seller" Option will be displayed in footer bar'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                    ),
                    'WK_MP_LINK_ON_POP_UP' => array(
                        'title' => $this->l('Display "Become a Seller" Option In Bottom Info Bar'),
                        'hint' => $this->l('If Yes, Info bar of "Become a seller" Option will be displayed at bottom of your site'),
                        'type' => 'bool',
                        'validation' => 'isBool',
                        'default' => '1',
                    ),
                ),
                'submit' => array('title' => $this->l('Save')),
            ),
        );
    }

    public function renderForm()
    {
        $objMarketplace = new Marketplace();
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Display Seller Details Settings'),
                'icon' => 'icon-user',
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display seller details'),
                    'name' => 'WK_MP_SHOW_SELLER_DETAILS',
                    'required' => false,
                    'class' => 't',
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
                    'hint' => $this->l('If Yes, Seller details can be displayed on seller\'s shop page and profile page.'),
                ),
                array(
                    'type' => 'group',
                    'label' => $this->l('Customize details'),
                    'name' => 'groupBox',
                    'values' => $objMarketplace->sellerDetailsView,
                    'col' => '6',
                    'form_group_class' => 'wk_mp_seller_details',
                    'hint' => $this->l('Select the details that will be available to seller for displaying it on seller\'s shop page and profile page.'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submitSellerDisplaySettings',
            ),
        );

        $this->fields_value = array(
            'WK_MP_SHOW_SELLER_DETAILS' => Tools::getValue('WK_MP_SHOW_SELLER_DETAILS', Configuration::get('WK_MP_SHOW_SELLER_DETAILS')),
        );

        if ($objMarketplace->sellerDetailsView) {
            $i = 1;
            $sellerDetailsAccess = Tools::jsonDecode(Configuration::get('WK_MP_SELLER_DETAILS_ACCESS'));
            foreach ($objMarketplace->sellerDetailsView as $sellerDetailsVal) {
                if ($sellerDetailsAccess && in_array($sellerDetailsVal['id_group'], $sellerDetailsAccess)) {
                    $groupVal = 1;
                } else {
                    $groupVal = '';
                }

                $this->fields_value['groupBox_'.$i] = $groupVal;

                ++$i;
            }
        }
        return parent::renderForm();
    }

    public function sellerOrderStatusForm()
    {
        $statuses = OrderState::getOrderStates((int)$this->context->language->id);
        asort($statuses);
        foreach ($statuses as $key => $status) {
            $this->statuses_array[$key]['id_group'] = $status['id_order_state'];
            $this->statuses_array[$key]['name'] = $status['name'];
        }
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Order Settings'),
                'icon' => 'icon-user',
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Sellers can add tracking details on their order'),
                    'name' => 'WK_MP_SELLER_ORDER_TRACKING_ALLOW',
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
                    'hint' => $this->l('If Yes, Sellers can add tracking number and url on their order for sending mail to customer.'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Update tracking number on Prestashop order tracking number'),
                    'name' => 'WK_MP_TRACKING_PS_UPDATE_ALLOW',
                    'required' => false,
                    'form_group_class' => 'wk_mp_tracking_ps_update',
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
                    'hint' => $this->l('If Yes, seller can update tracking number on prestashop order tracking number.'),
                    'desc' => $this->l('Tracking number will update only if that order has products of single seller.'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display tracking details to customer'),
                    'name' => 'WK_MP_TRACKING_ORDER_HISTORY_ALLOW',
                    'required' => false,
                    'form_group_class' => 'wk_mp_tracking_ps_update',
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
                    'hint' => $this->l('If Yes, tracking number and url will display to customer on order history.'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display tracking number in tracking url'),
                    'name' => 'WK_MP_TRACKING_NUMBER_IN_URL',
                    'required' => false,
                    'form_group_class' => 'wk_mp_tracking_ps_update',
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
                    'hint' => $this->l('If Yes, tracking number will display in place of @ in tracking url.'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Sellers can change their order status'),
                    'name' => 'WK_MP_SELLER_ORDER_STATUS_CHANGE',
                    'required' => false,
                    'class' => 't',
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
                    'hint' => $this->l('If Yes, Seller will able to change their order status for only their products.'),
                ),
                array(
                    'type' => 'group',
                    'label' => $this->l('Order Status'),
                    'name' => 'groupBoxStatus',
                    'values' => $this->statuses_array,
                    'col' => '9',
                    'form_group_class' => 'wk_mp_seller_order_status',
                    'hint' => $this->l('Select the order status that will be available to seller for changing their order status.'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'name' => 'submitSellerOrderStatus',
            ),
        );

        $this->fields_value = array(
            'WK_MP_SELLER_ORDER_TRACKING_ALLOW' => Tools::getValue(
                'WK_MP_SELLER_ORDER_TRACKING_ALLOW',
                Configuration::get('WK_MP_SELLER_ORDER_TRACKING_ALLOW')
            ),
            'WK_MP_TRACKING_PS_UPDATE_ALLOW' => Tools::getValue(
                'WK_MP_TRACKING_PS_UPDATE_ALLOW',
                Configuration::get('WK_MP_TRACKING_PS_UPDATE_ALLOW')
            ),
            'WK_MP_TRACKING_NUMBER_IN_URL' => Tools::getValue(
                'WK_MP_TRACKING_NUMBER_IN_URL',
                Configuration::get('WK_MP_TRACKING_NUMBER_IN_URL')
            ),
            'WK_MP_TRACKING_ORDER_HISTORY_ALLOW' => Tools::getValue(
                'WK_MP_TRACKING_ORDER_HISTORY_ALLOW',
                Configuration::get('WK_MP_TRACKING_ORDER_HISTORY_ALLOW')
            ),
            'WK_MP_SELLER_ORDER_STATUS_CHANGE' => Tools::getValue(
                'WK_MP_SELLER_ORDER_STATUS_CHANGE',
                Configuration::get('WK_MP_SELLER_ORDER_STATUS_CHANGE')
            ),
        );

        if ($this->statuses_array) {
            $sellerOrderStatus = Tools::jsonDecode(Configuration::get('WK_MP_SELLER_ORDER_STATUS_ACCESS'));
            foreach ($this->statuses_array as $sellerOrderStatusVal) {
                if ($sellerOrderStatus && in_array($sellerOrderStatusVal['id_group'], $sellerOrderStatus)) {
                    $groupVal = 1;
                } else {
                    $groupVal = '';
                }

                $this->fields_value['groupBox_'.$sellerOrderStatusVal['id_group']] = $groupVal;
            }
        }

        return parent::renderForm();
    }

    public function initContent()
    {
        parent::initContent();
        $this->initToolbar();
        $this->display = '';
        $this->content .= $this->renderForm();
        $this->content .= $this->sellerOrderStatusForm();

        $this->context->smarty->assign(array(
            'content' => $this->content,
        ));
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitOptionsconfiguration')) {
            if (!Validate::isEmail(Tools::getValue('WK_MP_SUPERADMIN_EMAIL'))) {
                $this->errors[] = $this->l('Invalid Email Id.');
            }
            if (!Validate::isUnsignedInt(Tools::getValue('WK_MP_PHONE_DIGIT'))
                || trim(Tools::getValue('WK_MP_PHONE_DIGIT')) == 0
            ) {
                $this->errors[] = $this->l('Invalid Phone Digit.');
            }
            if (Tools::getValue('WK_MP_URL_REWRITE_ADMIN_APPROVE')) {
                if (Tools::getValue('WK_MP_SELLER_PROFILE_PREFIX') == '') {
                    $this->errors[] = $this->l('Seller\'s profile page prefix is required field.');
                } elseif (!Tools::link_rewrite(Tools::getValue('WK_MP_SELLER_PROFILE_PREFIX'))) {
                    $this->errors[] = $this->l('Seller\'s profile page prefix is invalid.');
                }
                if (Tools::getValue('WK_MP_SELLER_SHOP_PREFIX') == '') {
                    $this->errors[] = $this->l('Seller\'s shop page prefix is required field.');
                } elseif (!Tools::link_rewrite(Tools::getValue('WK_MP_SELLER_SHOP_PREFIX'))) {
                    $this->errors[] = $this->l('Seller\'s shop page prefix is invalid.');
                }
                if (Tools::getValue('WK_MP_SELLER_REVIEWS_PREFIX') == '') {
                    $this->errors[] = $this->l('Seller\'s reviews page prefix is required field.');
                } elseif (!Tools::link_rewrite(Tools::getValue('WK_MP_SELLER_REVIEWS_PREFIX'))) {
                    $this->errors[] = $this->l('Seller\'s reviews page prefix is invalid.');
                }

                $wkAllPrefix = array(
                    Tools::getValue('WK_MP_SELLER_PROFILE_PREFIX'),
                    Tools::getValue('WK_MP_SELLER_SHOP_PREFIX'),
                    Tools::getValue('WK_MP_SELLER_REVIEWS_PREFIX'),
                );
                if (count(array_unique($wkAllPrefix)) != 3) { //If all prefix are not same it will return 3
                    $this->errors[] = $this->l('All prefix for rewrite URL must have different name.');
                }

                if (empty($this->errors)) {
                    $wkProfilePrefix = Tools::getValue('WK_MP_SELLER_PROFILE_PREFIX');
                    $wkShopPrefix = Tools::getValue('WK_MP_SELLER_SHOP_PREFIX');
                    $wkReviewsPrefix = Tools::getValue('WK_MP_SELLER_REVIEWS_PREFIX');

                    Configuration::updateValue(
                        'PS_ROUTE_module-marketplace-sellerprofile',
                        $wkProfilePrefix.'/{:mp_shop_name}'
                    );
                    Configuration::updateValue(
                        'PS_ROUTE_module-marketplace-shopstore',
                        $wkShopPrefix.'/{:mp_shop_name}'
                    );
                    Configuration::updateValue(
                        'PS_ROUTE_module-marketplace-allreviews',
                        $wkReviewsPrefix.'/{:mp_shop_name}'
                    );
                }
            }
        } elseif (Tools::isSubmit('submitSellerDisplaySettings')) {
            Configuration::updateValue('WK_MP_SHOW_SELLER_DETAILS', Tools::getValue('WK_MP_SHOW_SELLER_DETAILS'));

            // save seller details access details
            $sellerDetailsAccess = Tools::getValue('groupBox');
            if ($sellerDetailsAccess) {
                Configuration::updateValue('WK_MP_SELLER_DETAILS_ACCESS', Tools::jsonEncode($sellerDetailsAccess));
            } else {
                Configuration::updateValue('WK_MP_SELLER_DETAILS_ACCESS', '');
                Configuration::updateValue('WK_MP_SHOW_SELLER_DETAILS', 0);
            }

            Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
        } elseif (Tools::isSubmit('submitSellerOrderStatus')) {
            Configuration::updateValue(
                'WK_MP_SELLER_ORDER_TRACKING_ALLOW',
                Tools::getValue('WK_MP_SELLER_ORDER_TRACKING_ALLOW')
            );
            Configuration::updateValue(
                'WK_MP_TRACKING_PS_UPDATE_ALLOW',
                Tools::getValue('WK_MP_TRACKING_PS_UPDATE_ALLOW')
            );
            Configuration::updateValue(
                'WK_MP_TRACKING_NUMBER_IN_URL',
                Tools::getValue('WK_MP_TRACKING_NUMBER_IN_URL')
            );
            Configuration::updateValue(
                'WK_MP_TRACKING_ORDER_HISTORY_ALLOW',
                Tools::getValue('WK_MP_TRACKING_ORDER_HISTORY_ALLOW')
            );
            Configuration::updateValue(
                'WK_MP_SELLER_ORDER_STATUS_CHANGE',
                Tools::getValue('WK_MP_SELLER_ORDER_STATUS_CHANGE')
            );

            // save seller details access details
            $sellerOrderStatus = Tools::getValue('groupBox');
            if ($sellerOrderStatus) {
                Configuration::updateValue('WK_MP_SELLER_ORDER_STATUS_ACCESS', Tools::jsonEncode($sellerOrderStatus));
            } else {
                Configuration::updateValue('WK_MP_SELLER_ORDER_STATUS_ACCESS', '');
                Configuration::updateValue('WK_MP_SELLER_ORDER_STATUS_CHANGE', 0);
            }

            Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
        }

        parent::postProcess();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        Media::addJSDef(array(
            'color_picker_custom' => 1,
        ));
        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/mp_admin_config.js');
    }
}
