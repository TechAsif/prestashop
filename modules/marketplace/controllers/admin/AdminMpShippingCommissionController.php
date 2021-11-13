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

class AdminMpShippingCommissionController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'wk_mp_shipping_commission';
        $this->className = 'WkMpShippingCommission';
        $this->_select = '
        CONCAT(wms.`seller_firstname`, " ", wms.`seller_lastname`) as seller_name,
        wms.`shop_name_unique`';
        $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller` wms ON (wms.`id_seller` = a.`id_seller`)';
        $this->bootstrap = true;
        $this->identifier = 'id_wk_mp_shipping_commission';

        parent::__construct();
        $this->toolbar_title = $this->l('Manage Admin Commission On Shipping');

        $this->fields_options = array(
            'global' => array(
                'title' => $this->l('Global Admin Commission On Shipping'),
                'icon' => 'icon-globe',
                'fields' => array(
                    'WK_MP_GLOBAL_SHIPPING_COMMISSION' => array(
                        'title' => $this->l('Commission Rate'),
                        'hint' => $this->l('The default commission rate on shipping will apply on all sellers.'),
                        'desc' => $this->l('Commission will be calculate only if order carrier distribution is set as Both (on the basis of commission on shipping)'),
                        'validation' => 'isFloat',
                        'required' => true,
                        'type' => 'text',
                        'class' => 'fixed-width-xxl',
                        'suffix' => $this->l('%'),
                    ),
                ),
                'submit' => array('title' => $this->l('Save')),
            ),
        );

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
            'commission_rate' => array(
                'title' => $this->l('Commission Rate'),
                'align' => 'center',
                'suffix' => $this->l('%'),
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

    public function initContent()
    {
        parent::initContent();
        $this->content .= $this->renderList();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->tpl_list_vars['title'] = $this->l('Seller Wise Admin Commission On Shipping');
        return parent::renderList();
    }

    public function initToolbar()
    {
        parent::initToolbar();
        $this->page_header_toolbar_btn['new'] = array(
            'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
            'desc' => $this->l('Add Admin Commission on Shipping'),
        );

        $this->page_header_toolbar_btn['commissionsettings'] = array(
            'href' => $this->context->link->getAdminLink('AdminCustomerCommision'),
            'desc' => $this->l('Manage Commission Settings'),
            'imgclass' => 'new',
        );
    }

    public function renderForm()
    {
        $remainSeller = array();
        if ($id = Tools::getValue('id_wk_mp_shipping_commission')) {
            $objMpShippingCommission = new WkMpShippingCommission($id);
            $objMpSeller = new WkMpSeller($objMpShippingCommission->id_seller);
            if (isset($objMpSeller->id) && $objMpSeller->id) {
                $remainSeller[] = array(
                    'id_seller' => $objMpShippingCommission->id_seller,
                    'business_email' => $objMpSeller->business_email,
                );
            }
        } else {
            $objMpShippingCommission = new WkMpShippingCommission();
            $remainSeller = $objMpShippingCommission->getSellerWithoutShippingCommission();
        }

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Admin Commission on Shipping'),
                'icon' => 'icon-money',
            ),
            'input' => array(
                array(
                    'label' => $this->l('Select Seller'),
                    'name' => 'id_seller',
                    'type' => 'select',
                    'required' => true,
                    'identifier' => 'id',
                    'options' => array(
                        'query' => $remainSeller,
                        'id' => 'id_seller',
                        'name' => 'business_email',
                    ),
                ),
                array(
                    'label' => $this->l('Commission'),
                    'name' => 'add',
                    'type' => 'hidden',
                    'value' => '1',
                ),
                array(
                    'label' => $this->l('Admin Commission'),
                    'name' => 'commission_rate',
                    'hint' => $this->l('Commission will be calculate only if order carrier distribution is set as Both (on the basis of commission on shipping)'),
                    'type' => 'text',
                    'required' => true,
                    'default' => '10',
                    'col' => 2,
                    'suffix' => $this->l('%'),
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
        $commission = trim(Tools::getValue('commission_rate'));

        if ($commission == '') {
            $this->errors[] = $this->l('Commission rate is required.');
        } elseif (!Validate::isUnsignedFloat($commission)) {
            $this->errors[] = $this->l('Commission rate is invalid.');
        } elseif ($commission > 100) {
            $this->errors[] = $this->l('Commission rate must be a valid percentage (0 to 100).');
        }
        if (empty($this->errors)) {
            parent::processSave();
        } else {
            $this->display = 'add';
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitOptionswk_mp_shipping_commission')) {
            $globalCommission = trim(Tools::getValue('WK_MP_GLOBAL_SHIPPING_COMMISSION'));
            if ($globalCommission > 100 || $globalCommission < 0) {
                $this->errors[] = $this->l('Commission rate must be a valid percentage (0 to 100).');
            }
        }

        parent::postProcess();
    }
}
