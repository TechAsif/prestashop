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

class AdminCustomerCommisionController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'wk_mp_commision';
        $this->className = 'WkMpCommission';
        $this->_select = '
        CONCAT(wms.`seller_firstname`, " ", wms.`seller_lastname`) as seller_name,
        wms.`shop_name_unique`';
        $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller` wms ON (wms.`id_seller` = a.`id_seller`)';
        $this->bootstrap = true;
        $this->identifier = 'id_wk_mp_commision';

        parent::__construct();
        $this->toolbar_title = $this->l('Manage Commission');

        $taxDistributor = array(
            array('id' => 'admin'),
            array('id' => 'seller'),
            array('id' => 'distribute_both'),
        );
        $taxDistributor[0]['name'] = $this->l('Admin');
        $taxDistributor[1]['name'] = $this->l('Seller');
        $taxDistributor[2]['name'] = $this->l('Distribute between seller and admin');
        $shippingDistributor = $taxDistributor;
        unset($shippingDistributor[2]);

        $defaultCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        //Global Commission Settings
        $this->fields_options = array(
            'global' => array(
                'title' => $this->l('Global Commission'),
                'icon' => 'icon-globe',
                'fields' => array(
                    'WK_MP_GLOBAL_COMMISSION_TYPE' => array(
                        'title' => $this->l('Commission Type'),
                        'hint' => $this->l('The default commission type apply on all sellers.'),
                        'required' => true,
                        'type' => 'select',
                        'list' => WkMpCommission::mpCommissionType(),
                        'identifier' => 'id',
                    ),
                    'WK_MP_GLOBAL_COMMISSION' => array(
                        'title' => $this->l('Commission Rate'),
                        'hint' => $this->l('The default admin commission rate apply on all sellers.'),
                        'type' => 'text',
                        'class' => 'fixed-width-xxl',
                        'suffix' => $this->l('%'),
                        'form_group_class' => 'wk_mp_commission_rate',
                    ),
                    'WK_MP_GLOBAL_COMMISSION_AMOUNT' => array(
                        'title' => $this->l('Fixed Commission on Product Price (tax excl.)'),
                        'hint' => $this->l('The default admin commission amount apply on all sellers.'),
                        'desc' => $this->l('If fixed commission will be greater than product price (tax excl.) then full price (tax excl.) will added in admin commission and seller will get zero amount.'),
                        'type' => 'text',
                        'class' => 'fixed-width-xxl',
                        'suffix' => $defaultCurrency->sign,
                        'form_group_class' => 'wk_mp_commission_amt',
                    ),
                    'WK_MP_GLOBAL_TAX_FIXED_COMMISSION' => array(
                        'title' => $this->l('Fixed Commission on Product Tax'),
                        'hint' => $this->l('The default admin commision on tax apply on all sellers.'),
                        'desc' => $this->l('Set commission on tax if product tax is distributing between seller and admin. If fixed commission will be greater than product tax then full tax amount will added in admin tax and seller will get zero tax amount.'),
                        'type' => 'text',
                        'class' => 'fixed-width-xxl',
                        'suffix' => $defaultCurrency->sign,
                        'form_group_class' => 'wk_mp_commission_amt_on_tax',
                    ),
                ),
                'submit' => array('title' => $this->l('Save')),
            ),
            'tax_distribution' => array(
                'title' => $this->l('Tax Distribution'),
                'icon' => 'icon-globe',
                'fields' => array(
                    'WK_MP_PRODUCT_TAX_DISTRIBUTION' => array(
                        'title' => $this->l('Product Tax'),
                        'type' => 'select',
                        'list' => $taxDistributor,
                        'identifier' => 'id',
                    ),
                ),
                'submit' => array('title' => $this->l('Save')),
            ),
        );

        //Display Seller commission list
        $this->fields_list = array(
            'id_seller' => array(
                'title' => $this->l('Seller ID'),
                'align' => 'center',
               'class' => 'fixed-width-xs',
               'havingFilter' => true,
            ),
            'seller_name' => array(
                'title' => $this->l('Seller Name'),
                'align' => 'center',
                'havingFilter' => true,
            ),
            'shop_name_unique' => array(
                'title' => $this->l('Unique Shop Name'),
                'align' => 'center',
                'havingFilter' => true,
            ),
            'commision_type' => array(
                'title' => $this->l('Commission Type'),
                'align' => 'center',
                'callback' => 'commissionTypeName',
                'havingFilter' => true,
            ),
            'commision_rate' => array(
                'title' => $this->l('Commission Rate'),
                'align' => 'center',
                'suffix' => $this->l('%'),
            ),
            'commision_amt' => array(
                'title' => $this->l('Commission Amount'),
                'align' => 'center',
                'suffix' => $defaultCurrency->sign,
            ),
        );

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?'),
            ),
        );
    }

    public function commissionTypeName($val, $data)
    {
        return WkMpCommission::getCommissionTypeName($val);
    }

    public function initContent()
    {
        parent::initContent();
        $this->content .= $this->renderList();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->tpl_list_vars['title'] = $this->l('Seller Wise Commission');
        return parent::renderList();
    }

    public function initToolbar()
    {
        parent::initToolbar();
        $this->page_header_toolbar_btn['new'] = array(
            'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
            'desc' => $this->l('Add Admin Commission'),
        );

        $this->page_header_toolbar_btn['shippingdistribution'] = array(
            'href' => $this->context->link->getAdminLink('AdminMpShippingCommission'),
            'desc' => $this->l('Manage Admin Commission On Shipping'),
            'imgclass' => 'new',
        );
    }

    public function renderForm()
    {
        $remainSeller = array();
        if ($id = Tools::getValue('id_wk_mp_commision')) {
            $objMpCommission = new WkMpCommission($id);
            if ($sellerInfo = WkMpSeller::getSellerDetailByCustomerId($objMpCommission->seller_customer_id)) {
                $remainSeller[] = array(
                    'seller_customer_id' => $objMpCommission->seller_customer_id,
                    'business_email' => $sellerInfo['business_email'],
                );
            }
        } else {
            $objMpComm = new WkMpCommission();
            $remainSeller = $objMpComm->getSellerWithoutCommission();
        }

        $defaultCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Admin Commission'),
                'icon' => 'icon-money',
            ),
            'input' => array(
                array(
                    'label' => $this->l('Select Seller'),
                    'name' => 'seller_customer_id',
                    'type' => 'select',
                    'required' => true,
                    'identifier' => 'id',
                    'options' => array(
                        'query' => $remainSeller,
                        'id' => 'seller_customer_id',
                        'name' => 'business_email',
                    ),
                ),
                array(
                    'label' => $this->l('Commission Type'),
                    'name' => 'commision_type',
                    'type' => 'select',
                    'required' => true,
                    'identifier' => 'id',
                    'options' => array(
                        'query' => WkMpCommission::mpCommissionType(),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'label' => $this->l('Commission Rate'),
                    'name' => 'commision_rate',
                    'type' => 'text',
                    'class' => 'fixed-width-xxl',
                    'suffix' => $this->l('%'),
                    'form_group_class' => 'wk_mp_commission_rate',
                ),
                array(
                    'label' => $this->l('Fixed Commission on Product Price (tax excl.)'),
                    'name' => 'commision_amt',
                    'desc' => $this->l('If fixed commission will be greater than product price (tax excl.) then full price (tax excl.) will added in admin commission and seller will get zero amount.'),
                    'type' => 'text',
                    'class' => 'fixed-width-xxl',
                    'suffix' => $defaultCurrency->sign,
                    'form_group_class' => 'wk_mp_commission_amt',
                ),
                array(
                    'label' => $this->l('Fixed Commission on Product Tax'),
                    'name' => 'commision_tax_amt',
                    'desc' => $this->l('Set commission on tax if product tax is distributing between seller and admin. If fixed commission will be greater than product tax then full tax amount will added in admin tax and seller will get zero tax amount.'),
                    'type' => 'text',
                    'class' => 'fixed-width-xxl',
                    'suffix' => $defaultCurrency->sign,
                    'form_group_class' => 'wk_mp_commission_amt_on_tax',
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        if (!$remainSeller) { //if no seller fond or active and commission set for all
            $this->displayWarning(
                $this->l('No active marketplace seller OR you have already set commission for all sellers.')
            );
        } else {
            return parent::renderForm();
        }
    }

    public function processSave()
    {
        $idSeller = 0;
        $sellerCustomerId = Tools::getValue('seller_customer_id');
        if ($sellerCustomerId) {
            if ($sellerDetails = WkMpSeller::getSellerByCustomerId($sellerCustomerId)) {
                $idSeller = $sellerDetails['id_seller'];
            }
        }

        if (!$sellerCustomerId || !$idSeller) {
            $this->errors[] = $this->l('Choose atleast one seller.');
        }

        $commissionType = Tools::getValue('commision_type');
        $commissionRate = trim(Tools::getValue('commision_rate'));
        $commissionAmt = trim(Tools::getValue('commision_amt'));
        $commissionTaxAmt = trim(Tools::getValue('commision_tax_amt'));

        if ($commissionType == WkMpCommission::WK_COMMISSION_PERCENTAGE) {
            $commissionAmt = 0;
        } elseif ($commissionType == WkMpCommission::WK_COMMISSION_FIXED) {
            $commissionRate = 0;
        }

        $this->validateCommissionData($commissionType, $commissionRate, $commissionAmt, $commissionTaxAmt);

        if (empty($this->errors)) {
            if ($idMpCommission = Tools::getValue('id_wk_mp_commision')) {
                $objMpCommission = new WkMpCommission($idMpCommission);
                $wkConf = 4; //for update
            } else {
                $objMpCommission = new WkMpCommission();
                $wkConf = 3; //for create
            }
            $objMpCommission->id_seller = $idSeller;
            $objMpCommission->commision_type = $commissionType;
            $objMpCommission->commision_rate = $commissionRate;
            $objMpCommission->commision_amt = $commissionAmt;
            $objMpCommission->commision_tax_amt = $commissionTaxAmt;
            $objMpCommission->seller_customer_id = $sellerCustomerId;
            $objMpCommission->save();

            Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token.'&conf='.$wkConf);
        } else {
            $this->display = 'add';
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitOptionswk_mp_commision')) {
            $commissionType = Tools::getValue('WK_MP_GLOBAL_COMMISSION_TYPE');
            $commissionRate = trim(Tools::getValue('WK_MP_GLOBAL_COMMISSION'));
            $commissionAmt = trim(Tools::getValue('WK_MP_GLOBAL_COMMISSION_AMOUNT'));
            $commissionTaxAmt = trim(Tools::getValue('WK_MP_GLOBAL_TAX_FIXED_COMMISSION'));

            $this->validateCommissionData($commissionType, $commissionRate, $commissionAmt, $commissionTaxAmt);
        }

        parent::postProcess();
    }

    public function validateCommissionData($commissionType, $commissionRate, $commissionAmt, $commissionTaxAmt)
    {
        if (($commissionType == WkMpCommission::WK_COMMISSION_PERCENTAGE)
        || ($commissionType == WkMpCommission::WK_COMMISSION_BOTH_TYPE)) {
            if ($commissionRate == '') {
                $this->errors[] = $this->l('Commission Rate is required.');
            } elseif (!Validate::isUnsignedFloat($commissionRate)) {
                $this->errors[] = $this->l('Commission Rate is invalid.');
            } elseif ($commissionRate > 100 || $commissionRate < 0) {
                $this->errors[] = $this->l('Commission Rate must be a valid percentage (0 to 100).');
            }
        }

        if (($commissionType == WkMpCommission::WK_COMMISSION_FIXED)
        || ($commissionType == WkMpCommission::WK_COMMISSION_BOTH_TYPE)) {
            if ($commissionAmt == '') {
                $this->errors[] = $this->l('Fixed Commission on Product Price is required.');
            } elseif (!Validate::isUnsignedFloat($commissionAmt)) {
                $this->errors[] = $this->l('Fixed Commission on Product Price is invalid.');
            }
        }

        if ((Tools::getValue('WK_MP_PRODUCT_TAX_DISTRIBUTION') == 'distribute_both')
        && ($commissionType != WkMpCommission::WK_COMMISSION_PERCENTAGE)) {
            if ($commissionTaxAmt == '') {
                $this->errors[] = $this->l('Fixed Commission on Product Tax is required.');
            } elseif (!Validate::isUnsignedFloat($commissionTaxAmt)) {
                $this->errors[] = $this->l('Fixed Commission on Product Tax is invalid.');
            }
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        Media::addJSDef(array(
            'wk_commission_controller' => 1,
            'product_tax_distribution' => Configuration::get('WK_MP_PRODUCT_TAX_DISTRIBUTION'),
            'wk_percentage' => WkMpCommission::WK_COMMISSION_PERCENTAGE,
            'wk_fixed' => WkMpCommission::WK_COMMISSION_FIXED,
            'wk_both_type' => WkMpCommission::WK_COMMISSION_BOTH_TYPE,
        ));

        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/mp_admin_config.js');
    }
}
