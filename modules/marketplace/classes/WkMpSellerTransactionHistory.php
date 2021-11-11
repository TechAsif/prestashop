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

class WkMpSellerTransactionHistory extends ObjectModel
{
    public $id_customer_seller;
    public $id_currency;
    public $id_mp_order_detail;
    public $seller_amount;
    public $seller_tax;
    public $seller_shipping;
    public $seller_refunded_amount;
    public $seller_receive;
    public $admin_commission;
    public $admin_tax;
    public $admin_shipping;
    public $admin_refunded_amount;
    public $payment_method; // eg -stripe, paypal, etc
    public $transaction_type;   // (1. Order Paid, 2. Order Cancelled, 3. Settlement Paid, 4. Settlement cancelled)
    public $id_transaction; // transaction id of payment geteway
    public $remark;
    public $status; // (1. Seller Recieved, 2. Settlement Revert, 3. order cancel, 4. order refund)
    public $date_add;

    const MP_SELLER_ORDER_STATUS = 1; //While customer purchase seller product
    const MP_SELLER_SETTLEMENT_STATUS = 2; //While admin cancel settlement then set status as 2 of that settlement row
    const MP_ORDER_CANCEL_STATUS = 3; //While order is cancelled
    const MP_ORDER_REFUND_STATUS = 4; //While order is refunded

    const MP_SELLER_ORDER = 'order'; //While customer purchase seller product
    const MP_SETTLEMENT = 'settlement'; //While admin do settlement
    const MP_SETTLEMENT_CANCEL = 'settlement_cancelled'; //While admin cancel settlement
    const MP_ORDER_CANCEL = 'order_cancelled'; //While order is cancelled
    const MP_ORDER_REFUND = 'order_refunded'; //While order is refunded

    public static $definition = array(
        'table' => 'wk_mp_seller_transaction_history',
        'primary' => 'id_seller_transaction_history',
        'fields' => array(
            'id_customer_seller' => array('type' => self::TYPE_INT, 'required' => true),
            'id_currency' => array('type' => self::TYPE_INT, 'required' => true),
            'id_mp_order_detail' => array('type' => self::TYPE_INT),
            'seller_amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isNegativePrice'),
            'seller_tax' => array('type' => self::TYPE_FLOAT, 'validate' => 'isNegativePrice'),
            'seller_shipping' => array('type' => self::TYPE_FLOAT, 'validate' => 'isNegativePrice'),
            'seller_refunded_amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isNegativePrice'),
            'seller_receive' => array('type' => self::TYPE_FLOAT, 'validate' => 'isNegativePrice'),
            'admin_commission' => array('type' => self::TYPE_FLOAT, 'validate' => 'isNegativePrice'),
            'admin_tax' => array('type' => self::TYPE_FLOAT, 'validate' => 'isNegativePrice'),
            'admin_shipping' => array('type' => self::TYPE_FLOAT, 'validate' => 'isNegativePrice'),
            'admin_refunded_amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isNegativePrice'),
            'payment_method' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
            'transaction_type' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
            'id_transaction' => array('type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 254),
            'remark' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255),
            'status' => array('type' => self::TYPE_INT),
            'date_add' => array('type' => self::TYPE_DATE),
        ),
    );

    /**
     * Get seller payment transactions details according to seller id
     *
     * @param int $id_seller
     *
     * @return bool/array
     */
    public static function getDetailsByIdSeller($idCustomerSeller)
    {
        if ($idCustomerSeller) {
            return Db::getInstance()->executeS(
                'SELECT *,
                SUM(seller_amount) as seller_amount,
                SUM(seller_tax) as seller_tax,
                SUM(seller_shipping) as seller_shipping,
                SUM(seller_refunded_amount) as seller_refunded_amount,
                SUM(admin_commission) as admin_commission,
                SUM(admin_tax) as admin_tax,
                SUM(admin_shipping) as admin_shipping,
                SUM(admin_refunded_amount) as admin_refunded_amount
                FROM `' . _DB_PREFIX_ . 'wk_mp_seller_transaction_history`
                WHERE `id_customer_seller` = ' . (int) $idCustomerSeller . '
                Group By `id_transaction`, `status` Order by date_add DESC'
            );
        }

        return false;
    }

    public static function getTotalTransaction($idCustomerSeller)
    {
        return Db::getInstance()->getValue(
            'SELECT count(DISTINCT `id_transaction`) as count_values
            FROM ' . _DB_PREFIX_ . 'wk_mp_seller_transaction_history
            WHERE `id_customer_seller` = ' . (int) $idCustomerSeller
        );
    }

    /**
     * Get Order Total for admin and seller grouping by currency
     *
     * @param int $idCustomerSeller Seller Customer ID
     * @param int $idCurrency Currency ID
     *
     * @return array
     */
    public static function getSellerOrderTotalByIdCustomer($idCustomerSeller = false, $idCurrency = false)
    {
        $sql = 'SELECT
            id_customer_seller as `id_customer_seller`,
            wkt.`id_currency`,
            SUM(wkt.`seller_amount`) - SUM(IF(wkt.`status` = ' . (int) WkMpSellerTransactionHistory::MP_SELLER_SETTLEMENT_STATUS . ', wkt.`seller_receive`, 0)) + SUM(`seller_tax`) + SUM(`seller_shipping`) AS `seller_total_earned`,
            SUM(IF(wkt.`status` != ' . (int) WkMpSellerTransactionHistory::MP_SELLER_SETTLEMENT_STATUS . ', wkt.`seller_receive`, 0)) AS `seller_receive`,
            SUM(wkt.`seller_amount`) - SUM(IF(wkt.`status` = ' . (int) WkMpSellerTransactionHistory::MP_SELLER_SETTLEMENT_STATUS . ', wkt.`seller_receive`, 0)) as `seller_amount`,
            SUM(`seller_tax`) as seller_tax,
            SUM(`seller_refunded_amount`) as seller_refunded_amount,
            SUM(`seller_shipping`) as seller_shipping,
            SUM(`admin_commission`) + SUM(`admin_tax`) + SUM(`admin_shipping`) as admin_total_earned,
            SUM(`admin_commission`) as admin_commission,
            SUM(`admin_tax`) as admin_tax,
            SUM(`admin_shipping`) as admin_shipping,
            SUM(`admin_refunded_amount`) as admin_refunded_amount';

        $sql .= ' FROM `' . _DB_PREFIX_ . 'wk_mp_seller_transaction_history` wkt
        LEFT JOIN ' . _DB_PREFIX_ . 'orders o on (o.`id_order` = wkt.`id_transaction`) WHERE 1 ';

        if ($idCustomerSeller) {
            $sql .= ' AND wkt.`id_customer_seller`= ' . (int) $idCustomerSeller;
        }

        $sql .= ' AND (wkt.`status`= ' . (int) WkMpSellerTransactionHistory::MP_SELLER_ORDER_STATUS . ' OR wkt.`status`= ' . (int) WkMpSellerTransactionHistory::MP_SELLER_SETTLEMENT_STATUS . ')';

        if (Configuration::get('WK_MP_COMMISSION_DISTRIBUTE_ON') == 1) {
            // Payment accepted
            $sql .= ' AND (wkt.`transaction_type`= "' . pSQL(WkMpSellerTransactionHistory::MP_SETTLEMENT) . '" OR wkt.`transaction_type`= "' . pSQL(WkMpSellerTransactionHistory::MP_SETTLEMENT_CANCEL) . '" OR (SELECT `id_order_state` FROM `' . _DB_PREFIX_ . 'order_history` oh WHERE oh.`id_order` = o.`id_order` AND oh.`id_order_state`=' . (int) Configuration::get('PS_OS_PAYMENT') . ' LIMIT 1))';
        }

        if ($idCurrency) {
            $sql .= ' AND wkt.`id_currency`=' . (int) $idCurrency;

            return Db::getInstance()->executeS($sql);
        }

        $sql .= ' Group By wkt.`id_currency`';
        $orderTotal = Db::getInstance()->executeS($sql);

        return $orderTotal;
    }

    /**
     * Assigning currency wise order totals
     *
     * @param array $orderTotal Calculated array of order total
     * @param int $idSeller Seller ID
     * @param int $idCustomerSeller Seller Customer Id
     *
     * @return bool
     */
    public static function assignSellerTransactionTotal($orderTotal, $idCustomerSeller = false)
    {
        $context = Context::getContext();
        foreach ($orderTotal as $key => $detail) {
            $id_currency = empty($detail['id_currency']) ? Configuration::get('PS_CURRENCY_DEFAULT') : $detail['id_currency'];
            $currency = new Currency($id_currency);

            $sellerTotal = empty($detail['seller_total_earned']) ? '0' : $detail['seller_total_earned'];
            $sellerAmount = empty($detail['seller_amount']) ? '0' : $detail['seller_amount'];
            $sellerRecieve = empty($detail['seller_receive']) ? '0' : $detail['seller_receive'];
            $sellerTax = empty($detail['seller_tax']) ? '0' : $detail['seller_tax'];
            //$sellerShipping = empty($detail['seller_shipping']) ? '0' : $detail['seller_shipping'];
            $sellerRefundedAmount = empty($detail['seller_refunded_amount']) ? '0' : $detail['seller_refunded_amount'];

            $adminTotalAmount = empty($detail['admin_total_earned']) ? '0' : $detail['admin_total_earned'];
            $adminCommission = empty($detail['admin_commission']) ? '0' : $detail['admin_commission'];
            $adminTax = empty($detail['admin_tax']) ? '0' : $detail['admin_tax'];
            //$adminShipping = empty($detail['admin_shipping']) ? '0' : $detail['admin_shipping'];
            $adminRefundedAmount = empty($detail['admin_refunded_amount']) ? '0' : $detail['admin_refunded_amount'];

            $totalEarning = $adminTotalAmount + $sellerTotal;
            $seller_shipping = 0;
            $admin_shipping = 0;
            $shippingInfo = false;
            if ($idCustomerSeller) {
                $sellerShippingInfo = WkMpAdminShipping::getTotalSellerShipping(
                    $idCustomerSeller,
                    $detail['id_currency']
                );
                if (!$sellerShippingInfo) {
                    $sellerShippingInfo = array(
                        'seller_shipping' => '0',
                        'id_currency' => $id_currency,
                    );
                }
                $adminShippingInfo = WkMpAdminShipping::getTotalAdminShipping(
                    $detail['id_currency'],
                    $idCustomerSeller
                );
                if (!$adminShippingInfo) {
                    $sellerShippingInfo = array(
                        'admin_shipping' => '0',
                        'id_currency' => $id_currency,
                    );
                }
                $shippingInfo = array_merge($sellerShippingInfo, $adminShippingInfo);
            } else {
                $shippingInfo = WkMpAdminShipping::getTotalShippingByIdCurrency($detail['id_currency']);
            }

            if ($shippingInfo) {
                if (isset($shippingInfo['seller_shipping'])) {
                    $seller_shipping = $shippingInfo['seller_shipping'];
                }
                if (isset($shippingInfo['admin_shipping'])) {
                    $admin_shipping = $shippingInfo['admin_shipping'];
                }
                $sellerTotal += $seller_shipping;
                $totalEarning += $admin_shipping + $seller_shipping;
            }
            $orderTotal[$key]['no_prefix_admin_shipping'] = $admin_shipping;
            $orderTotal[$key]['no_prefix_seller_shipping'] = $seller_shipping;
            $orderTotal[$key]['admin_shipping'] = Tools::displayPrice($admin_shipping, $currency);
            $orderTotal[$key]['seller_shipping'] = Tools::displayPrice($seller_shipping, $currency);

            // without currency prefix values
            $orderTotal[$key]['no_prefix_seller_total'] = $sellerTotal;
            $orderTotal[$key]['no_prefix_seller_amount'] = $sellerAmount;
            $orderTotal[$key]['no_prefix_seller_recieve'] = $sellerRecieve;
            $orderTotal[$key]['no_prefix_seller_tax'] = $sellerTax;
            //$orderTotal[$key]['no_prefix_seller_shipping'] = $sellerShipping;
            $orderTotal[$key]['no_prefix_seller_refund'] = $sellerRefundedAmount;
            $orderTotal[$key]['no_prefix_seller_due'] = $sellerTotal - $sellerRecieve;

            $orderTotal[$key]['no_prefix_admin_total'] = $adminTotalAmount;
            $orderTotal[$key]['no_prefix_admin_commission'] = $adminCommission;
            $orderTotal[$key]['no_prefix_admin_tax'] = $adminTax;
            //$orderTotal[$key]['no_prefix_admin_shipping'] = $adminShipping;
            $orderTotal[$key]['no_prefix_admin_refund'] = $adminRefundedAmount;

            $orderTotal[$key]['no_prefix_total_earning'] = $adminTotalAmount + $sellerTotal;

            // with currency prefix values
            $orderTotal[$key]['seller_total'] = Tools::displayPrice($sellerTotal, $currency);
            $orderTotal[$key]['seller_amount'] = Tools::displayPrice($sellerAmount, $currency);
            $orderTotal[$key]['seller_recieve'] = Tools::displayPrice($sellerRecieve, $currency);
            $orderTotal[$key]['seller_tax'] = Tools::displayPrice($sellerTax, $currency);
            //$orderTotal[$key]['seller_shipping'] = Tools::displayPrice($sellerShipping, $currency);
            $orderTotal[$key]['seller_refund'] = Tools::displayPrice($sellerRefundedAmount, $currency);
            $orderTotal[$key]['seller_due'] = Tools::displayPrice($sellerTotal - $sellerRecieve, $currency);
            $orderTotal[$key]['seller_due_full'] = $currency->sign . ' ' . ($sellerTotal - $sellerRecieve);

            $orderTotal[$key]['admin_total'] = Tools::displayPrice($adminTotalAmount, $currency);
            $orderTotal[$key]['admin_commission'] = Tools::displayPrice($adminCommission, $currency);
            $orderTotal[$key]['admin_tax'] = Tools::displayPrice($adminTax, $currency);
            //$orderTotal[$key]['admin_shipping'] = Tools::displayPrice($adminShipping, $currency);
            $orderTotal[$key]['admin_refund'] = Tools::displayPrice($adminRefundedAmount, $currency);

            $orderTotal[$key]['total_earning'] = Tools::displayPrice($totalEarning, $currency);
            unset($currency);
        }

        $context->smarty->assign(array(
            'sellerOrderTotal' => $orderTotal,
        ));
    }

    public static function getlastRowValue()
    {
        return Db::getInstance()->getValue(
            'SELECT `id_seller_transaction_history`
            FROM ' . _DB_PREFIX_ . 'wk_mp_seller_transaction_history
            WHERE 1 ORDER BY `id_seller_transaction_history` DESC'
        );
    }

    public function saveSellerTransactionData(
        $idSellerCustomer,
        $idCurrency,
        $idMpOrderDetail = false,
        $sellerAmount = false,
        $sellerTax = false,
        $sellerShipping = false,
        $sellerRefundedAmount = false,
        $sellerReceive = false,
        $adminCommission = false,
        $adminTax = false,
        $adminShipping = false,
        $adminRefundedAmount = false,
        $paymentMethod = 'N/A',
        $transactionType = WkMpSellerTransactionHistory::MP_SELLER_ORDER,
        $idOrder = 'N/A',
        $remark = 'N/A',
        $status = WkMpSellerTransactionHistory::MP_SELLER_ORDER_STATUS
    ) {
        $wkMpSellerTransaction = new self();

        $wkMpSellerTransaction->id_customer_seller = (int) $idSellerCustomer;
        $wkMpSellerTransaction->id_currency = (int) $idCurrency;
        $wkMpSellerTransaction->id_mp_order_detail = (int) $idMpOrderDetail;

        $wkMpSellerTransaction->seller_amount = (float) $sellerAmount;
        $wkMpSellerTransaction->seller_tax = (float) $sellerTax;
        $wkMpSellerTransaction->seller_shipping = (float) $sellerShipping;
        $wkMpSellerTransaction->seller_refunded_amount = (float) $sellerRefundedAmount;

        $wkMpSellerTransaction->seller_receive = (float) $sellerReceive;

        $wkMpSellerTransaction->admin_commission = (float) $adminCommission;
        $wkMpSellerTransaction->admin_tax = (float) $adminTax;
        $wkMpSellerTransaction->admin_shipping = (float) $adminShipping;
        $wkMpSellerTransaction->admin_refunded_amount = (float) $adminRefundedAmount;

        $wkMpSellerTransaction->payment_method = pSQL($paymentMethod);
        $wkMpSellerTransaction->transaction_type = pSQL($transactionType);
        $wkMpSellerTransaction->id_transaction = pSQL($idOrder);
        $wkMpSellerTransaction->remark = pSQL($remark);
        $wkMpSellerTransaction->status = (int) $status;
        if ($wkMpSellerTransaction->save()) {
            return $wkMpSellerTransaction->id;
        }

        return false;
    }

    public function getOrderTransactionHistoryByOrderId($idOrder, $idCustomerSeller = false)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'wk_mp_seller_transaction_history
        WHERE `id_transaction` = ' . (int) $idOrder . '
        AND `transaction_type` = "order"
        AND `status` = ' . (int) WkMpSellerTransactionHistory::MP_SELLER_ORDER_STATUS . '';
        if ($idCustomerSeller) {
            $sql .= ' AND `id_customer_seller` = ' . (int) $idCustomerSeller . '';
        }

        return Db::getInstance()->executeS($sql);
    }

    public function getOrderTransactionHistoryByIdMpOrderDetail($idMpOrderDetail)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'wk_mp_seller_transaction_history
            WHERE `id_mp_order_detail` = ' . (int) $idMpOrderDetail . '
            AND `transaction_type` = "order"
            AND `status` = ' . (int) WkMpSellerTransactionHistory::MP_SELLER_ORDER_STATUS
        );
    }
}
