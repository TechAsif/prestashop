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

class AdminPaymentModeController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'wk_mp_payment_mode';
        $this->className = 'WkMpSellerPaymentMode';
        $this->identifier = 'id_mp_payment';
        parent::__construct();
        $this->toolbar_title = $this->l('Manage Payment Modes');

        $this->fields_list = array(
            'id_mp_payment' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'payment_mode' => array(
                'title' => $this->l('Payment Mode'),
                'width' => '100',
            ),
        );
        $this->bulk_actions = array(
                                'delete' => array('text' => $this->l('Delete selected'),
                                                'icon' => 'icon-trash',
                                                'confirm' => $this->l('Delete selected items?'), ), );
    }

    public function initToolbar()
    {
        parent::initToolbar();
        $this->page_header_toolbar_btn['new'] = array(
            'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
            'desc' => $this->l('Add Payment Mode'),
        );
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        return parent::renderList();
    }

    public function renderForm()
    {
        $this->fields_form = array(
          'legend' => array(
            'title' => $this->l('Manage Payment Mode'),
          ),

          'input' => array(
            array(
              'type' => 'text',
              'name' => 'payment_mode',
              'label' => $this->l('Payment Mode'),
              'required' => true,
             ),
          ),

          'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        return parent::renderForm();
    }
}
