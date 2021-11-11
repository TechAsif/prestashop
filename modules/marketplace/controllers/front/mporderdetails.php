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

class MarketplaceMpOrderDetailsModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $objMpOrder = new WkMpSellerOrder();
        $objMpOrderDetail = new WkMpSellerOrderDetail();

        if (isset($this->context->customer->id)) {
            $editOrderPermission = 1;
            $idCustomer = $this->context->customer->id;
            //Override customer id if any staff of seller want to use this controller
            if (Module::isEnabled('mpsellerstaff')) {
                $staffDetails = WkMpSellerStaff::getStaffInfoByIdCustomer($idCustomer);
                if ($staffDetails
                    && $staffDetails['active']
                    && $staffDetails['id_seller']
                    && $staffDetails['seller_status']
                ) {
                    $staffTabDetails = WkMpTabList::getStaffPermissionWithTabName(
                        $staffDetails['id_staff'],
                        $this->context->language->id,
                        WkMpTabList::MP_ORDER_TAB
                    );
                    if ($staffTabDetails) {
                        //For edit order permission
                        $editOrderPermission = $staffTabDetails['edit'];
                    }
                }

                $getCustomerId = WkMpSellerStaff::overrideMpSellerCustomerId($idCustomer);
                if ($getCustomerId) {
                    $idCustomer = $getCustomerId;
                }
            }

            $seller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
            if ($seller && $seller['active']) {
                if ($idOrder = Tools::getValue('id_order')) {
                    $order = new Order($idOrder);
                    $idCurrency = (int) $order->id_currency;

                    $mpOrderDetails = $objMpOrderDetail->getSellerOrderDetail($idOrder, $this->context->language->id);
                    if ($mpOrderDetails) {
                        $orderProduct = $objMpOrderDetail->getSellerProductFromOrder($idOrder, $idCustomer);
                        if ($orderProduct) {
                            // Set voucher details
                            $this->setVoucherDetails($idOrder, $seller['id_seller'], $idCurrency);

                            $productTotal = 0;
                            $sellerTotal = 0;
                            $adminTotal = 0;

                            $taxBreakDown = array();
                            foreach ($orderProduct as &$product) {
                                $productTotal += $product['total_price_tax_incl'];
                                $sellerAmount = $product['seller_amount'] + $product['seller_tax'];
                                $sellerTotal += $sellerAmount;

                                $adminAmount = $product['admin_commission'] + $product['admin_tax'];
                                $adminTotal += $adminAmount;

                                $product['admin_commission_formatted'] = Tools::displayPrice(
                                    $product['admin_commission'],
                                    $idCurrency
                                );
                                $product['seller_amount_formatted'] = Tools::displayPrice(
                                    $product['seller_amount'],
                                    $idCurrency
                                );

                                $product['seller_total_amount'] = Tools::displayPrice($sellerAmount, $idCurrency);
                                $product['admin_total_commission'] = Tools::displayPrice($adminAmount, $idCurrency);

                                $product['unit_price_tax_excl'] = Tools::displayPrice(
                                    $product['unit_price_tax_excl'],
                                    $idCurrency
                                );
                                $product['unit_price_tax_incl'] = Tools::displayPrice(
                                    $product['unit_price_tax_incl'],
                                    $idCurrency
                                );

                                $product['total_price_tax_incl_formatted'] = Tools::displayPrice(
                                    $product['total_price_tax_incl'],
                                    $idCurrency
                                );

                                $product['price_ti'] = Tools::displayPrice($product['price_ti'], $idCurrency);
                                $product['price_te'] = Tools::displayPrice($product['price_te'], $idCurrency);

                                $product['total_tax'] = Tools::displayPrice(
                                    $product['seller_tax']+$product['admin_tax'],
                                    $idCurrency
                                );

                                $product['rate'] = Tools::ps_round(
                                    $objMpOrderDetail->getTaxRateByIdOrderDetail($product['id_order_detail']),
                                    2
                                );
                                $product['seller_tax'] = Tools::displayPrice($product['seller_tax'], $idCurrency);
                                $product['admin_tax'] = Tools::displayPrice($product['admin_tax'], $idCurrency);

                                $taxBreakDown[$product['id_order_detail']]['rate'] = Tools::ps_round(
                                    $objMpOrderDetail->getTaxRateByIdOrderDetail($product['id_order_detail']),
                                    2
                                );
                                $taxBreakDown[$product['id_order_detail']]['seller_tax'] = Tools::displayPrice(
                                    $product['seller_tax'],
                                    $idCurrency
                                );
                                $taxBreakDown[$product['id_order_detail']]['admin_tax'] = Tools::displayPrice(
                                    $product['admin_tax'],
                                    $idCurrency
                                );

                                $product['commission_data'] = WkMpCommission::finalCommissionSummary(
                                    $product['commission_type'],
                                    $product['commission_rate'],
                                    $product['commission_amt'],
                                    $product['commission_tax_amt'],
                                    $idCurrency
                                );
                            }

                            // get addresses
                            $this->mpOrderAddressDetails($idOrder);

                            // get order status
                            $this->shippingProcess($seller);

                            // get order reference
                            $order = new Order($idOrder);
                            if (Validate::isLoadedObject($order)) {
                                $this->context->smarty->assign('reference', $order->reference);
                            }

                            //Get shipping name of this order
                            if ($order->id_carrier) {
                                $objCarrier = new Carrier($order->id_carrier);
                                $this->context->smarty->assign('order_shipping_name', $objCarrier->name);
                            }

                            $sellerOrderTotal = $objMpOrder->getTotalOrder($idOrder, $idCustomer);
                            if ($sellerOrderTotal) {
                                //Add shipping amount in total orders
                                if ($sellerShippingEarning = WkMpAdminShipping::getSellerShippingByIdOrder($idOrder, $idCustomer)) {
                                    $this->context->smarty->assign(
                                        'seller_shipping_earning',
                                        Tools::displayPrice($sellerShippingEarning, $idCurrency)
                                    );

                                    $sellerOrderTotal += $sellerShippingEarning;
                                }
                            }

                            $this->context->smarty->assign(array(
                                'editOrderPermission' => $editOrderPermission,
                                'order_products' => $orderProduct,
                                'mp_total_order' => Tools::displayPrice($sellerOrderTotal, $idCurrency),
                                'mp_order_details' => $mpOrderDetails,
                                'is_seller' => '1',
                                'logic' => 4,
                                'wkself' => dirname(__FILE__),
                                'admin_commission_total' => Tools::displayPrice($adminTotal, $idCurrency),
                                'seller_total' => Tools::displayPrice($sellerTotal, $idCurrency),
                                'product_total' => Tools::displayPrice($productTotal, $idCurrency),
                                'taxBreakDown' => $taxBreakDown,
                                'id_order' => $idOrder,
                            ));

                            $this->setTemplate('module:marketplace/views/templates/front/order/mporderdetails.tpl');
                        } else {
                            Tools::redirect(__PS_BASE_URI__.'pagenotfound');
                        }
                    } else {
                        Tools::redirect(__PS_BASE_URI__.'pagenotfound');
                    }
                } else {
                    Tools::redirect(__PS_BASE_URI__.'pagenotfound');
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
        if ($this->context->customer->id) {
            $idCustomer = $this->context->customer->id;
            //Override customer id if any staff of seller want to use this controller
            if (Module::isEnabled('mpsellerstaff')) {
                $getCustomerId = WkMpSellerStaff::overrideMpSellerCustomerId($idCustomer);
                if ($getCustomerId) {
                    $idCustomer = $getCustomerId;
                }
            }

            $idOrder = Tools::getValue('id_order');
            $mpSeller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
            if ($mpSeller && $mpSeller['active'] && $idOrder) {
                $objMpOrderDetail = new WkMpSellerOrderDetail();
                if ($objMpOrderDetail->getSellerProductFromOrder($idOrder, $idCustomer)) { //if same seller's order
                    $objOrderStatus = new WkMpSellerOrderStatus();
                    $order = new Order($idOrder);

                    if (Tools::isSubmit('submitState')) {
                        $idOrderState = Tools::getValue('id_order_state');
                        if (!$idOrderState) {   // seller just update the status without selecting other
                            $idOrderState = Tools::getValue('id_order_state_checked');
                        }

                        $products = $order->getProducts();
                        if ($products) {
                            $flag = true;
                            foreach ($products as $prod) {
                                $isProductSeller = WkMpSellerProduct::checkPsProduct(
                                    $prod['product_id'],
                                    $mpSeller['id_seller']
                                );
                                if (!$isProductSeller) {
                                    $flag = false;
                                    break;
                                }
                            }
                        }
                        $oldOs = $objOrderStatus->getCurrentOrderState($idOrder, $mpSeller['id_seller']);
                        if ($oldOs == $idOrderState) {
                            $this->errors[] = $this->module->l('The new order status is invalid.', 'mporderdetails');
                        }
                        if (empty($this->errors)) {
                            $isUpdated = true;
                            $objOrderStatus->processSellerOrderStatus($idOrder, $mpSeller['id_seller'], $idOrderState);
                            if ($flag) {    // this order is belong to only current seller
                                $isUpdated = $objOrderStatus->updateOrderByIdOrderAndIdOrderState(
                                    $idOrder,
                                    $idOrderState
                                );
                                //If sellers change their order status as cancelled, data will be rollback
                                //using changeIdOrderState() inside above function updateOrderByIdOrderAndIdOrderState
                                //that will call hookActionOrderStatusPostUpdate of marketplace class
                            }

                            if ($isUpdated) {
                                Hook::exec('actionAfterSellerOrderStatusUpdate', array(
                                    'id_seller' => $mpSeller['id_seller'],
                                    'id_order' => $idOrder,
                                    'id_order_state' => $idOrderState
                                ));

                                //To manage staff log (changes add/update/delete)
                                WkMpHelper::setStaffHook(
                                    $this->context->customer->id,
                                    Tools::getValue('controller'),
                                    $idOrder,
                                    2
                                ); // 2 for Add action

                                Tools::redirect(
                                    $this->context->link->getModuleLink(
                                        'marketplace',
                                        'mporderdetails',
                                        array('id_order' => $idOrder, 'is_order_state_updated' => 1)
                                    )
                                );
                            } else {
                                Tools::redirect(
                                    $this->context->link->getModuleLink(
                                        'marketplace',
                                        'mporderdetails',
                                        array('id_order' => $idOrder)
                                    )
                                );
                            }
                        }
                    } elseif (Tools::isSubmit('submitTracking')
                    && Configuration::get('WK_MP_SELLER_ORDER_TRACKING_ALLOW')
                    ) {
                        $trackingNumber = trim(Tools::getValue('tracking_number'));
                        $trackingURL = trim(Tools::getValue('tracking_url'));
                        if ($trackingNumber && !Validate::isTrackingNumber($trackingNumber)) {
                            $this->errors[] = $this->module->l('Tracking number is not valid.', 'mporderdetails');
                        }
                        if (empty($this->errors)) {
                            $alreadyExist = $objOrderStatus->isOrderExist($idOrder, $mpSeller['id_seller']);
                            if ($alreadyExist) {
                                $objOrderStatus = new WkMpSellerOrderStatus($alreadyExist['id_order_status']);
                            } else {
                                $objOrderStatus->current_state = $order->getCurrentOrderState()->id;
                            }
                            $objOrderStatus->id_order = $idOrder;
                            $objOrderStatus->id_seller = $mpSeller['id_seller'];
                            $objOrderStatus->tracking_number = $trackingNumber;
                            $objOrderStatus->tracking_url = $trackingURL;
                            if ($objOrderStatus->save()) {
                                //If order has only single seller's product
                                //then update tracking number in prestashop tracking number according to configuration
                                if (Configuration::get('WK_MP_TRACKING_PS_UPDATE_ALLOW')) {
                                    $sellerArray = array();
                                    $objOrder = new Order($idOrder);
                                    if ($orderProducts = $objOrder->getProducts()) {
                                        foreach ($orderProducts as $prod) {
                                            $sellerData = WkMpSellerOrderDetail::getSellerFromOrderProduct(
                                                $idOrder,
                                                $prod['product_id']
                                            );
                                            if ($sellerData) {
                                                $sellerArray[$sellerData['seller_id']] = $prod['product_id'];
                                            }
                                        }
                                    }
                                    if ($sellerArray && count($sellerArray) == 1) { //If only one seller exist
                                        $objOrder->shipping_number = $trackingNumber;
                                        if ($objOrder->update()) {
                                            WkMpSellerOrder::updateOrderCarrierTrackingNumber($idOrder, $trackingNumber);
                                        }
                                    }
                                }

                                Hook::exec(
                                    'actionAfterSaveTracking',
                                    array(
                                        'idOrder' => $idOrder,
                                        'trackingURL' => $trackingURL,
                                        'trackingNumber' => $trackingNumber,
                                        'idSeller' => $mpSeller['id_seller']
                                    )
                                );

                                Tools::redirect(
                                    $this->context->link->getModuleLink(
                                        'marketplace',
                                        'mporderdetails',
                                        array('id_order' => $idOrder, 'tracking' => 1)
                                    )
                                );
                            } else {
                                $this->errors[] = $this->module->l('Some error occurred...', 'mporderdetails');
                            }
                        }
                    } elseif (Tools::isSubmit('submitTrackingMail')
                    && Configuration::get('WK_MP_SELLER_ORDER_TRACKING_ALLOW')
                    ) {
                        $trackingDetails = $objOrderStatus->isOrderExist($idOrder, $mpSeller['id_seller']);
                        if ($trackingDetails) {
                            if ($trackingDetails['tracking_number'] && $trackingDetails['tracking_url']) {
                                $customer = new Customer($order->id_customer);

                                if (Configuration::get('WK_MP_TRACKING_NUMBER_IN_URL')) {
                                    $wkTrackingURL = Tools::strReplaceFirst(
                                        '@',
                                        $trackingDetails['tracking_number'],
                                        $trackingDetails['tracking_url']
                                    );
                                } else {
                                    $wkTrackingURL = $trackingDetails['tracking_url'];
                                }

                                $templateVars = array(
                                    '{customer_name}' => $customer->firstname.' '.$customer->lastname,
                                    '{reference_number}' => $order->reference,
                                    '{tracking_number}' => $trackingDetails['tracking_number'],
                                    '{tracking_url}' => $wkTrackingURL,
                                    '{mail_reason}' => $this->module->l('Tracking information updated', 'mporderdetails'),
                                );

                                $tempPath = _PS_MODULE_DIR_.'marketplace/mails/';
                                if (Mail::Send(
                                    $this->context->language->id,
                                    'customer_tracking',
                                    Mail::l('Tracking updated', $this->context->language->id),
                                    $templateVars,
                                    $customer->email,
                                    null,
                                    $mpSeller['business_email'],
                                    null,
                                    null,
                                    null,
                                    $tempPath,
                                    false,
                                    null,
                                    null
                                )) {
                                    Tools::redirect(
                                        $this->context->link->getModuleLink(
                                            'marketplace',
                                            'mporderdetails',
                                            array('id_order' => $idOrder, 'sent' => 1)
                                        )
                                    );
                                } else {
                                    $this->errors[] = $this->module->l('Mail could not sent.', 'mporderdetails');
                                }
                            }
                        }
                    }
                } else {
                    Tools::redirect(__PS_BASE_URI__.'pagenotfound');
                }
            } else {
                Tools::redirect($this->context->link->getPageLink('my-account'));
            }
        } else {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }
    }

    public function setVoucherDetails($idOrder, $seller, $idCurrency)
    {
        WkMpSellerOrderDetail::setVoucherDetails($idOrder, $seller, $idCurrency, true);
    }

    public function shippingProcess($seller)
    {
        $idOrder = Tools::getValue('id_order');
        $objOrderStatus = new WkMpSellerOrderStatus();
        $order = new Order($idOrder);
        $history = $objOrderStatus->getHistory($this->context->language->id, $seller['id_seller'], $idOrder);
        if (!$history) {
            $history = $order->getHistory($this->context->language->id);
        }
        foreach ($history as &$orderState) {
            $orderState['text-color'] = Tools::getBrightness($orderState['color']) < 128 ? 'white !important' : 'black !important';
        }

        // process only those order status which allowed by admin
        $sellerOrderStatus = Configuration::get('WK_MP_SELLER_ORDER_STATUS_ACCESS');
        $status = '';
        if ($sellerOrderStatus) {
            $sellerOrderStatus = Tools::jsonDecode($sellerOrderStatus);
            if ($status = OrderState::getOrderStates($this->context->language->id)) {
                foreach ($status as $key => $state) {
                    if (!in_array($state['id_order_state'], $sellerOrderStatus)) {
                        unset($status[$key]);
                    }
                }
            }
        }

        $this->context->smarty->assign(array(
            'update_url_link' => $this->context->link->getModuleLink(
                'marketplace',
                'mporderdetails',
                array('id_order' => $idOrder)
            ),
            'states' => $status,
            'current_id_lang' => $this->context->language->id,
            'order' => $order,
            'history' => $history,
            'currentState' => $objOrderStatus->getCurrentOrderState($idOrder, $seller['id_seller']),
            'img_url' => _PS_IMG_,
            'trackingInfo' => $objOrderStatus->isOrderExist($idOrder, $seller['id_seller']),
        ));
    }

    public function mpOrderAddressDetails($idOrder)
    {
        $idLang = Context::getContext()->language->id;
        $order = new Order($idOrder);
        $customer = new Customer($order->id_customer);
        $addressInvoice = new Address($order->id_address_invoice, $idLang);
        if (Validate::isLoadedObject($addressInvoice) && $addressInvoice->id_state) {
            $invoiceState = new State((int) $addressInvoice->id_state);
        }
        $invoiceFormat = AddressFormat::generateAddress($addressInvoice, array(), '<br />');

        if ($order->id_address_invoice == $order->id_address_delivery) {
            $addressDelivery = $addressInvoice;
            if (isset($invoiceState)) {
                $deliveryState = $invoiceState;
            }
        } else {
            $addressDelivery = new Address($order->id_address_delivery, $idLang);
            if (Validate::isLoadedObject($addressDelivery) && $addressDelivery->id_state) {
                $deliveryState = new State((int) ($addressDelivery->id_state));
            }
        }
        $deliveryFormat = AddressFormat::generateAddress($addressDelivery, array(), '<br />');

        $this->context->smarty->assign(array(
            'customer_addresses' => $customer->getAddresses($idLang),
            'addresses' => array(
                'delivery' => $addressDelivery,
                'deliveryFormat' => $deliveryFormat,
                'deliveryState' => isset($deliveryState) ? $deliveryState : null,
                'invoice' => $addressInvoice,
                'invoiceFormat' => $invoiceFormat,
                'invoiceState' => isset($invoiceState) ? $invoiceState : null,
                ),
            ));
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->addJqueryUI('ui.datepicker');
        $this->registerStylesheet('marketplace_account', 'modules/'.$this->module->name.'/views/css/marketplace_account.css');
    }
}
