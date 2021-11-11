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

class MarketplaceMpTransactionModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (isset($this->context->customer->id)) {
            $idCustomer = $this->context->customer->id;
            //Override customer id if any staff of seller want to use this controller
            if (Module::isEnabled('mpsellerstaff')) {
                $getCustomerId = WkMpSellerStaff::overrideMpSellerCustomerId($idCustomer);
                if ($getCustomerId) {
                    $idCustomer = $getCustomerId;
                }
            }

            $seller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
            if ($seller && $seller['active']) {
                // get seller's total earning with respect of admin's admin n tax as currency wise
                $orderTotal = WkMpSellerTransactionHistory::getSellerOrderTotalByIdCustomer(
                    $idCustomer
                );
                if ($orderTotal) {
                    WkMpSellerTransactionHistory::assignSellerTransactionTotal(
                        $orderTotal,
                        $idCustomer
                    );
                }
                // ---------- Code End For Seller's Total Eearning ------//

                // Get seller transaction history
                $sellerPaymentHistory = WkMpSellerTransactionHistory::getDetailsByIdSeller(
                    $idCustomer
                );
                if ($sellerPaymentHistory) {
                    foreach ($sellerPaymentHistory as &$transaction) {
                        $idCurrency = $transaction['id_currency'];

                        if ($transaction['transaction_type'] == WkMpSellerTransactionHistory::MP_SELLER_ORDER) {
                            $order = new Order($transaction['id_transaction']);
                            $transaction['transaction'] = nl2br($this->module->l('Prestashop Order', 'mptransaction') ."\n"."(".$this->module->l('Ref', 'mptransaction').":".$order->reference.")");
                            $sellerShippingEarning = WkMpAdminShipping::getSellerShippingByIdOrder(
                                (int) $transaction['id_transaction'],
                                (int) $idCustomer
                            );
                            if (!$sellerShippingEarning) {
                                $sellerShippingEarning = 0;
                            }
                            $transaction['seller_shipping_earning_without_sign'] = $sellerShippingEarning;
                            $transaction['seller_shipping_earning'] = Tools::displayPrice(
                                $sellerShippingEarning,
                                (int) $transaction['id_currency']
                            );
                        } elseif ($transaction['transaction_type'] == WkMpSellerTransactionHistory::MP_ORDER_CANCEL) {
                            $transaction['transaction'] = $this->module->l('Prestashop Order Cancelled', 'mptransaction');
                        } elseif ($transaction['transaction_type'] == WkMpSellerTransactionHistory::MP_ORDER_REFUND) {
                            $transaction['transaction'] = $this->module->l('Prestashop Order Refunded', 'mptransaction');
                        } elseif ($transaction['transaction_type'] == WkMpSellerTransactionHistory::MP_SETTLEMENT) {
                            $val = explode('#', $transaction['id_transaction']);
                            if (isset($val[1])) {
                                if (!$val[1]) {
                                    $idTransaction = $this->module->l('N/A', 'mptransaction');
                                } else {
                                    $idTransaction = $val[1];
                                }
                            } else {
                                $idTransaction = $val[0];
                            }
                            $transaction['id_transaction'] = $idTransaction;
                            $transaction['transaction'] = nl2br($this->module->l('Seller Settlement', 'mptransaction')."\n"."(".$this->module->l('Ref', 'mptransaction').":".$transaction['id_transaction'].")");
                        } elseif ($transaction['transaction_type'] == WkMpSellerTransactionHistory::MP_SETTLEMENT_CANCEL) {
                            $val = explode('#', $transaction['id_transaction']);
                            if (isset($val[1])) {
                                if (!$val[1]) {
                                    $idTransaction = $this->module->l('N/A', 'mptransaction');
                                } else {
                                    $idTransaction = $val[1];
                                }
                            } else {
                                $idTransaction = $val[0];
                            }
                            $transaction['id_transaction'] = $idTransaction;
                            $transaction['transaction'] = nl2br($this->module->l('Seller Settlement Cancelled', 'mptransaction')."\n"."(".$this->module->l('Ref', 'mptransaction').":".$transaction['id_transaction'].")");
                        }

                        if ($transaction['seller_amount'] > 0) {
                            $transaction['seller_amount_without_sign'] = $transaction['seller_amount'];
                            $amount = Tools::displayPrice(
                                $transaction['seller_amount'],
                                (int) $transaction['id_currency']
                            );
                            if ($transaction['seller_refunded_amount'] > 0) {
                                $transaction['seller_amount'] = $amount." (".$this->module->l('Dr', 'mptransaction').")";
                            } else {
                                $transaction['seller_amount'] = $amount." (".$this->module->l('Cr', 'mptransaction').")";
                            }
                        } elseif ($transaction['seller_receive'] > 0) {
                            $transaction['seller_amount_without_sign'] = $transaction['seller_receive'];
                            $amount = Tools::displayPrice(
                                $transaction['seller_receive'],
                                (int) $transaction['id_currency']
                            );
                            $transaction['seller_amount'] = $amount." (".$this->module->l('Dr', 'mptransaction').")";
                        } else {
                            $transaction['seller_amount_without_sign'] = $transaction['seller_amount'];
                            $amount = Tools::displayPrice(
                                $transaction['seller_amount'],
                                (int) $transaction['id_currency']
                            );
                            $transaction['seller_amount'] = $amount." (".$this->module->l('Cr', 'mptransaction').")";
                        }

                        $transaction['seller_tax_without_sign'] = $transaction['seller_tax'];
                        $transaction['seller_tax'] = Tools::displayPrice($transaction['seller_tax'], (int) $idCurrency);
                        $transaction['seller_shipping'] = Tools::displayPrice($transaction['seller_shipping'], (int) $idCurrency);
                        $transaction['seller_refunded_amount'] = Tools::displayPrice($transaction['seller_refunded_amount'], (int) $idCurrency);
                        $transaction['seller_receive'] = Tools::displayPrice($transaction['seller_receive'], (int) $idCurrency);
                        $transaction['admin_commission_without_sign'] = $transaction['admin_commission'];
                        $transaction['admin_commission'] = Tools::displayPrice($transaction['admin_commission'], (int) $idCurrency);
                        $transaction['admin_tax_without_sign'] = $transaction['admin_tax'];
                        $transaction['admin_tax'] = Tools::displayPrice($transaction['admin_tax'], (int) $idCurrency);
                        $transaction['admin_shipping'] = Tools::displayPrice($transaction['admin_shipping'], (int) $idCurrency);
                        $transaction['admin_refunded_amount'] = Tools::displayPrice($transaction['admin_refunded_amount'], (int) $idCurrency);

                        if ($transaction['transaction_type'] == WkMpSellerTransactionHistory::MP_SELLER_ORDER
                        || $transaction['transaction_type'] == WkMpSellerTransactionHistory::MP_ORDER_CANCEL
                        || $transaction['transaction_type'] == WkMpSellerTransactionHistory::MP_ORDER_REFUND
                        ) {
                            $transaction['class'] = 'wk_view_detail';
                            $transaction['data'] = 'data-id-order= '.(int) $transaction['id_transaction'];
                        } elseif ($transaction['transaction_type'] == WkMpSellerTransactionHistory::MP_SETTLEMENT
                        || $transaction['transaction_type'] == WkMpSellerTransactionHistory::MP_SETTLEMENT_CANCEL
                        ) {
                            $transaction['class'] = 'wk_view_transaction_detail';
                            $transaction['data'] = 'data-id-transaction= '.(int) $transaction['id_seller_transaction_history'];
                        }
                    }
                    $this->context->smarty->assign('transactions', $sellerPaymentHistory);
                }
                // --- End of Seller Transaction History Code ---- //

                $this->context->smarty->assign(array(
                    'logic' => 5,
                    'is_seller' => $seller['active'],
                    'wkself' => dirname(__FILE__),

                ));
                $this->defineJSVars();
                $this->setTemplate('module:marketplace/views/templates/front/transaction/mptransaction.tpl');
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
            }
        } else {
            Tools::redirect('index.php?controller=authentication&back='.urlencode($this->context->link->getModuleLink('marketplace', 'mptransaction')));
        }
    }

    public function defineJSVars()
    {
        $jsVars = array(
            'mporderdetails_link' => $this->context->link->getModuleLink('marketplace', 'mptransaction'),
            'display_name' => $this->module->l('Display', 'mptransaction'),
            'records_name' => $this->module->l('records per page', 'mptransaction'),
            'no_product' => $this->module->l('No transaction found', 'mptransaction'),
            'show_page' => $this->module->l('Showing page', 'mptransaction'),
            'show_of' => $this->module->l('of', 'mptransaction'),
            'no_record' => $this->module->l('No records available', 'mptransaction'),
            'filter_from' => $this->module->l('filtered from', 'mptransaction'),
            't_record' => $this->module->l('total records', 'mptransaction'),
            'search_item' => $this->module->l('Search', 'mptransaction'),
            'p_page' => $this->module->l('Previous', 'mptransaction'),
            'n_page' => $this->module->l('Next', 'mptransaction'),
            'current_url' => $this->context->link->getModuleLink('marketplace', 'mptransaction')
        );

        if (Configuration::get('PS_REWRITING_SETTINGS')) {
            $jsVars['friendly_url'] = 1;
        } else {
            $jsVars['friendly_url'] = 0;
        }
        Media::addJsDef($jsVars);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'mptransaction'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Transaction', 'mptransaction'),
            'url' => '',
        );

        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->registerStylesheet('marketplace_account', 'modules/'.$this->module->name.'/views/css/marketplace_account.css');
        $this->registerStylesheet('mp_global_style-css', 'modules/'.$this->module->name.'/views/css/mp_global_style.css');

        //data table file included
        $this->registerStylesheet('datatable_bootstrap', 'modules/'.$this->module->name.'/views/css/datatable_bootstrap.css');
        $this->registerJavascript('mp-jquery-dataTables', 'modules/'.$this->module->name.'/views/js/jquery.dataTables.min.js');
        $this->registerJavascript('mp-dataTables.bootstrap', 'modules/'.$this->module->name.'/views/js/dataTables.bootstrap.js');
        $this->registerJavascript('mp-order', 'modules/'.$this->module->name.'/views/js/mporder.js');
        $this->registerJavascript('mp-sellertransaction', 'modules/'.$this->module->name.'/views/js/sellertransaction.js');
    }

    public function displayAjaxOrderDetail()
    {
        $output = false;
        $idOrder = Tools::getValue('id_order');
        $idCustomerSeller = Tools::getValue('id_customer_seller');
        $orderDetail = new WkMpSellerOrderDetail();
        $result = $orderDetail->getSellerProductFromOrder($idOrder, $idCustomerSeller);

        if ($result) {
            foreach ($result as $key => &$data) {
                $mpProduct = WkMpSellerProduct::getSellerProductByPsIdProduct($data['product_id']);
                $result[$key]['seller_amount'] = Tools::displayPrice($data['seller_amount'], new Currency($data['id_currency']));
                $result[$key]['seller_tax'] = Tools::displayPrice($data['seller_tax'], new Currency($data['id_currency']));
                $result[$key]['admin_commission'] = Tools::displayPrice($data['admin_commission'], new Currency($data['id_currency']));
                $result[$key]['admin_tax'] = Tools::displayPrice($data['admin_tax'], new Currency($data['id_currency']));
                $result[$key]['price_ti'] = Tools::displayPrice($data['price_ti'], new Currency($data['id_currency']));
                $result[$key]['product_link'] = $this->context->link->getModuleLink(
                    'marketplace',
                    'updateproduct',
                    array('id_mp_product' => (int) $mpProduct['id_mp_product'])
                );
            }
            $this->context->smarty->assign(array(
                'result' => $result,
                'orderInfo' => $orderDetail->getSellerOrderDetail((int) $idOrder),
                'orderlink' => $this->context->link->getModuleLink(
                    'marketplace',
                    'mporderdetails',
                    array('id_order' => (int) $idOrder)
                ),
                'frontcontroll' => 1
            ));
            $output = $this->context->smarty->fetch(
                _PS_MODULE_DIR_.'marketplace/views/templates/admin/seller-product-line.tpl'
            );
        }
        die($output);
    }

    public function displayAjaxTransactionDetail()
    {
        $output = false;
        $idTransaction = Tools::getValue('id_transaction');
        $idCustomerSeller = Tools::getValue('id_customer_seller');
        if ($idCustomerSeller && $idTransaction) {
            $objTransaction = new WkMpSellerTransactionHistory($idTransaction);
            if (Validate::isLoadedObject($objTransaction)) {
                if ($objTransaction->seller_receive > 0) {
                    $amount = -($objTransaction->seller_receive);
                } elseif ($objTransaction->seller_amount > 0) {
                    $amount = $objTransaction->seller_amount;
                } else {
                    $amount = $objTransaction->seller_amount;
                }

                if ($objTransaction->transaction_type == WkMpSellerTransactionHistory::MP_SETTLEMENT
                    || $objTransaction->transaction_type == WkMpSellerTransactionHistory::MP_SETTLEMENT_CANCEL
                ) {
                    $idTransaction = $this->checkTransactionID(
                        $objTransaction->id_transaction,
                        array(
                            'transaction_type' => WkMpSellerTransactionHistory::MP_SETTLEMENT
                        )
                    );
                    $objTransaction->id_transaction = $idTransaction;
                }

                $this->context->smarty->assign(
                    array(
                        'objTransaction' => $objTransaction,
                        'amount' => Tools::displayPrice($amount, new Currency($objTransaction->id_currency)),
                        'frontcontroll' => 1
                    )
                );
                $objSellerPayment = new WkMpCustomerPayment();
                $paymentModeDetail = $objSellerPayment->getPaymentDetailByIdCustomer($objTransaction->id_customer_seller);
                if ($paymentModeDetail && $paymentModeDetail['payment_detail']) {
                    $this->context->smarty->assign(
                        array(
                            'payment_mode_details' => $paymentModeDetail['payment_detail']
                        )
                    );
                }
                $output = $this->context->smarty->fetch(
                    _PS_MODULE_DIR_.'marketplace/views/templates/hook/seller-transaction-view-front.tpl'
                );
            }
        }
        die($output);
    }

    public function checkTransactionID($val, $arr)
    {
        if ($arr['transaction_type'] == WkMpSellerTransactionHistory::MP_SETTLEMENT) {
            $val = explode('#', $val);
            if (isset($val[1])) {
                if (!$val[1]) {
                    return $this->l('N/A');
                } else {
                    return $val[1];
                }
            } else {
                return $val[0];
            }
        } elseif ($arr['transaction_type'] == WkMpSellerTransactionHistory::MP_SETTLEMENT_CANCEL) {
            return $this->l('N/A');
        } else {
            if (!$val) {
                return $this->l('N/A');
            }
        }

        return $val;
    }
}
