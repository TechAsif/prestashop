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

class MarketplaceMpPaymentModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (isset($this->context->customer->id)) {
            $seller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);

            if ($seller && $seller['active']) {
                $mpPayment = new WkMpCustomerPayment();

                //if seller edit or delete payment details
                if ($idMpPayment = Tools::getValue('id')) {
                    if (WkMpCustomerPayment::getPaymentDetailById($idMpPayment)) {
                        if (Tools::getValue('delete_payment')) {
                            //if seller delete payment mode
                            $objPaymentDetail = new WkMpCustomerPayment($idMpPayment);
                            if ($objPaymentDetail->delete()) {
                                Tools::redirect($this->context->link->getModuleLink('marketplace', 'mppayment', array('deleted' => 1)));
                            }
                        } else {
                            $this->context->smarty->assign('edit', 1);
                        }
                    } else {
                        Tools::redirect($this->context->link->getModuleLink('marketplace', 'mppayment'));
                    }
                }

                //get all admin payment option
                if ($adminPaymentOption = WkMpSellerPaymentMode::getPaymentMode()) {
                    $this->context->smarty->assign('mp_payment_option', $adminPaymentOption);
                }

                //get seller selected payment
                if ($sellerPayments = $mpPayment->getPaymentDetailByIdCustomer($this->context->customer->id)) {
                    $this->context->smarty->assign('seller_payment_details', $sellerPayments);
                }

                $this->context->smarty->assign(array(
                        'customer_id' => $this->context->customer->id,
                        'is_seller' => $seller['active'],
                        'logic' => 6,
                    ));

                Media::addJsDef(array(
                        'required_payment' => $this->module->l('Payment mode is required field.', 'mppayment'),
                        'confirm_msg' => $this->module->l('Are you sure want to delete?', 'mppayment'),
                    ));

                $this->setTemplate('module:marketplace/views/templates/front/mppayment.tpl');
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
            }
        } else {
            Tools::redirect('index.php?controller=authentication&back='.urlencode($this->context->link->getModuleLink('marketplace', 'mppayment')));
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submit_payment_details') && $this->context->customer->id) {
            $seller = WkMpSeller::getSellerDetailByCustomerId($this->context->customer->id);
            if ($seller && $seller['active']) {
                $idMpPayment = Tools::getValue('id');
                $paymentMode = Tools::getValue('payment_mode_id');
                $paymentDetail = Tools::getValue('payment_detail');

                if (!$paymentMode) {
                    $this->errors[] = $this->module->l('Payment mode is required field.', 'mppayment');
                }

                if (empty($this->errors)) {
                    if ($idMpPayment) {
                        $mpPayment = new WkMpCustomerPayment($idMpPayment);
                    } else {
                        $mpPayment = new WkMpCustomerPayment();
                    }

                    $mpPayment->seller_customer_id = $this->context->customer->id;
                    $mpPayment->payment_mode_id = $paymentMode;
                    $mpPayment->payment_detail = $paymentDetail;
                    if ($mpPayment->save()) {
                        if ($idMpPayment) {
                            Tools::redirect($this->context->link->getModuleLink('marketplace', 'mppayment', array('edited' => 1)));
                        } else {
                            Tools::redirect($this->context->link->getModuleLink('marketplace', 'mppayment', array('created' => 1)));
                        }
                    }
                }
            }
        }
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'mppayment'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard')
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Payment', 'mppayment'),
            'url' => ''
        );
        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->registerStylesheet('marketplace_account-css', 'modules/'.$this->module->name.'/views/css/marketplace_account.css');
        $this->registerJavascript('mp_form_validation-js', 'modules/'.$this->module->name.'/views/js/mp_form_validation.js');
    }
}
