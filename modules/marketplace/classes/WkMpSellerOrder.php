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

class WkMpSellerOrder extends ObjectModel
{
    public $seller_customer_id; /** @var id_customer of marketplace seller */
    public $seller_id;
    public $seller_shop;
    public $seller_firstname;
    public $seller_lastname;
    public $seller_email;
    public $total_earn_ti; /** @var total earn of shop with tax */
    public $total_earn_te; /** @var  total earn of shop without tax */
    public $total_admin_commission; /** @var total admin commission */
    public $total_admin_tax; /** @var total admin tax */
    public $total_seller_amount; /** @var  total seller amount */
    public $total_seller_tax; /** @var total seller tax */
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'wk_mp_seller_order',
        'primary' => 'id_mp_order',
        'fields' => array(
            'seller_customer_id' => array('type' => self::TYPE_INT),
            'seller_id' => array('type' => self::TYPE_INT),
            'seller_shop' => array('type' => self::TYPE_STRING),
            'seller_firstname' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
            'seller_lastname' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
            'seller_email' => array('type' => self::TYPE_STRING, 'validate' => 'isEmail'),
            'total_earn_ti' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'total_earn_te' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'total_admin_commission' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'total_admin_tax' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'total_seller_amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'total_seller_tax' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        ),
    );

    /**
     * Update Seller Order in seller order detail table
     *
     * @param  int $sellerCustomerID Seller Customer ID
     * @param  array $allProductInfo Details of Order's Product
     *
     * @return boolean/int false/or order detail id
     */
    public static function updateSellerOrder($sellerCustomerID, $allProductInfo)
    {
        if ($mpSellerInfo = WkMpSeller::getSellerDetailByCustomerId($sellerCustomerID)) {
            $orderDetails = self::getSellerRecord($sellerCustomerID);
            if ($orderDetails) {
                $objMpsellerorder = new self($orderDetails['id_mp_order']);
                $objMpsellerorder->total_earn_ti = round(
                    ($allProductInfo['total_earn_ti'] + $orderDetails['total_earn_ti']),
                    6
                );
                $objMpsellerorder->total_earn_te = round(
                    ($allProductInfo['total_earn_te'] + $orderDetails['total_earn_te']),
                    6
                );
                $objMpsellerorder->total_admin_commission = round(
                    ($allProductInfo['total_admin_commission'] + $orderDetails['total_admin_commission']),
                    6
                );
                $objMpsellerorder->total_admin_tax = round(
                    ($allProductInfo['total_admin_tax'] + $orderDetails['total_admin_tax']),
                    6
                );
                $objMpsellerorder->total_seller_amount = round(
                    ($allProductInfo['total_seller_amount'] + $orderDetails['total_seller_amount']),
                    6
                );
                $objMpsellerorder->total_seller_tax = round(
                    ($allProductInfo['total_seller_tax'] + $orderDetails['total_seller_tax']),
                    6
                );
            } else {
                $objMpsellerorder = new self();
                $objMpsellerorder->seller_customer_id = $sellerCustomerID;
                $objMpsellerorder->total_earn_ti = round($allProductInfo['total_earn_ti'], 6);
                $objMpsellerorder->total_earn_te = round($allProductInfo['total_earn_te'], 6);
                $objMpsellerorder->total_admin_commission = round($allProductInfo['total_admin_commission'], 6);
                $objMpsellerorder->total_admin_tax = round($allProductInfo['total_admin_tax'], 6);
                $objMpsellerorder->total_seller_amount = round($allProductInfo['total_seller_amount'], 6);
                $objMpsellerorder->total_seller_tax = round($allProductInfo['total_seller_tax'], 6);
            }

            $objMpsellerorder->seller_id = $mpSellerInfo['id_seller'];
            $objMpsellerorder->seller_shop = $mpSellerInfo['shop_name_unique'];
            $objMpsellerorder->seller_firstname = $mpSellerInfo['seller_firstname'];
            $objMpsellerorder->seller_lastname = $mpSellerInfo['seller_lastname'];
            $objMpsellerorder->seller_email = $mpSellerInfo['business_email'];
            $objMpsellerorder->save();
            if ($objMpsellerorder->id) {
                return $objMpsellerorder->id;
            }
        }

        return false;
    }

    /**
     * Get Seller's Orders
     *
     * @param  int  $idLang  language ID
     * @param  int  $idCustomer Seller Customer ID
     * @param  boolean $topFive Only five rows if need
     * @return array
     */
    public function getSellerOrders($idLang, $idCustomer, $topFive = false)
    {
        return Db::getInstance()->executeS('SELECT
            ordd.`id_order_detail`AS `id_order_detail`,
			ordd.`product_name` AS `ordered_product_name`,
			ordd.`product_price` AS product_price,
			ordd.`product_quantity` AS qty,
			ordd.`id_order` AS id_order,
			ord.`id_customer` AS buyer_id_customer,
			ord.`total_paid` AS total_paid,
			ord.`payment` AS payment_mode,
			ord.`reference` AS reference,
			cus.`firstname` AS seller_firstname,
			cus.`lastname` AS seller_lastname,
			cus.`email` AS seller_email,
			ord.`date_add`,ords.`name` AS order_status,
			ord.`id_currency` AS `id_currency`
			FROM `'._DB_PREFIX_.'wk_mp_seller_order_detail` msod
			JOIN `'._DB_PREFIX_.'order_detail` ordd ON (ordd.`product_id` = msod.`product_id` AND ordd.`id_order` = msod.`id_order`)
			JOIN `'._DB_PREFIX_.'orders` ord ON (ordd.`id_order` = ord.`id_order`)
			JOIN `'._DB_PREFIX_.'wk_mp_seller` msi ON (msi.`seller_customer_id` = msod.`seller_customer_id`)
			JOIN `'._DB_PREFIX_.'customer` cus ON (msi.`seller_customer_id` = cus.`id_customer`)
			JOIN `'._DB_PREFIX_.'order_state_lang` ords ON (ord.`current_state` = ords.`id_order_state`)
			WHERE ords.id_lang = '.(int) $idLang.' AND cus.`id_customer` = '.(int) $idCustomer.'
			GROUP BY ordd.`id_order` ORDER BY ordd.`id_order` DESC '.((int) $topFive ? 'LIMIT 5' : ''));
    }

    /**
     * Get Total of specific order using ID order
     *
     * @param  int $idOrder Order ID
     * @param  int $idCustomerSeller Seller customer ID
     * @return float   Order's total
     */
    public function getTotalOrder($idOrder, $idCustomerSeller)
    {
        return Db::getInstance()->getValue(
            'SELECT SUM(price_ti) as `totalorder` FROM `'._DB_PREFIX_.'wk_mp_seller_order_detail`
            WHERE `id_order` = '.(int) $idOrder.' AND `seller_customer_id` = '.(int) $idCustomerSeller
        );
    }

    /**
     * Get Seller's Total Earning
     *
     * @param  int $idCustomerSeller Seller Customer ID
     * @return float Seller's Total Amount
     */
    public static function getSellerTotalEarn($idCustomerSeller)
    {
        return Db::getInstance()->getValue('SELECT `total_earn_ti` FROM `'._DB_PREFIX_.'wk_mp_seller_order` WHERE `seller_customer_id`='.(int) $idCustomerSeller);
    }

    /**
     * Get Total commission earned by admin from the sellers
     *
     * @param  int $idCustomerSeller Seller customer ID
     * @return float Total Amount
     */
    public static function getAdminTotalCommissionByIdSeller($idCustomerSeller)
    {
        return Db::getInstance()->getValue('SELECT `total_admin_commission` FROM `'._DB_PREFIX_.'wk_mp_seller_order` WHERE `seller_customer_id`='.(int) $idCustomerSeller);
    }

    /**
     * Get Total earning of seller by Id Seller
     *
     * @param  int $idCustomerSeller Seller Customer ID
     * @return float Total Amount
     */
    public static function getTotalSellerEarnedByIdSeller($idCustomerSeller)
    {
        return Db::getInstance()->getValue('SELECT `total_seller_amount` FROM `'._DB_PREFIX_.'wk_mp_seller_order` WHERE `seller_customer_id`='.(int) $idCustomerSeller);
    }

    /**
     * Check order is belong to seller or not by using seller customer id
     *
     * @param  int  $idCustomerSeller Seller Customer ID
     * @return boolean  true/false
     */
    public static function getSellerRecord($idCustomerSeller)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_order`
            WHERE `seller_customer_id` = '.(int) $idCustomerSeller
        );
    }

    /**
     * Get currency conversion rate
     *
     * @param  int $id_currency_from Id Currency From
     * @param  int $id_currency_to   Id Currency To
     * @return float Conversion Rate
     */
    public static function getCurrencyConversionRate($idCurrencyFrom, $idCurrencyTo)
    {
        $conversionRate = 1;
        if ($idCurrencyTo != $idCurrencyFrom) {
            $currencyFrom = new Currency((int) $idCurrencyFrom);
            $conversionRate /= $currencyFrom->conversion_rate;
            $currencyTo = new Currency((int) $idCurrencyTo);
            $conversionRate *= $currencyTo->conversion_rate;
        }

        return $conversionRate;
    }

    /**
     * If seller update their unique shop name then that name will also update in seller orders seller_shop field
     *
     * @param  int $seller_customer_id Seller Customer ID
     * @param  int $shop_name_unique seller unique shop name
     * @return boolean true/false
     */
    public static function updateOrderShopUniqueBySellerCustomerId($sellerCustomerID, $shopNameUnique)
    {
        $orderDetails = self::getSellerRecord($sellerCustomerID);
        if ($orderDetails) {
            return Db::getInstance()->update('wk_mp_seller_order', array('seller_shop' => pSQL($shopNameUnique)), 'seller_customer_id = '. (int) $sellerCustomerID);
        }

        return true;
    }

    /**
     * Get total earned of seller
     *
     * @param  boolean $idCustomerSeller
     * @return array
     */
    public static function getTotalEarned($idCustomerSeller = false)
    {
        //This function is deprecated
        $sql = 'SELECT
            SUM(wkshipping.`shipping_amount`) AS total_shipping,
            SUM(mpsord.`price_ti` / cu.`conversion_rate`) AS total_earn_ti,
            SUM(mpsord.`price_te` / cu.`conversion_rate`) AS total_earn_te,
            SUM(mpsord.`admin_commission`/ cu.`conversion_rate`) AS total_admin_commission,
            SUM(mpsord.`admin_tax` / cu.`conversion_rate`) AS total_admin_tax,
            SUM(mpsord.`seller_amount` / cu.`conversion_rate`) AS total_seller_amount,
            SUM(mpsord.`admin_commission` / cu.`conversion_rate` ) + SUM(mpsord.`admin_tax` / cu.`conversion_rate`) + SUM(wkshipping.`shipping_amount`) as total_earned
            FROM '._DB_PREFIX_.'wk_mp_admin_shipping wkshipping
            JOIN `'._DB_PREFIX_.'wk_mp_seller_order_detail` mpsord ON (wkshipping.`order_id` = mpsord.`id_order`)
            JOIN `'._DB_PREFIX_.'currency` cu ON (cu.`id_currency` = mpsord.`id_currency`)
            JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = mpsord.`id_order`)
            WHERE o.`id_order` IN (SELECT wkt.`id_transaction` FROM `'._DB_PREFIX_.'wk_mp_seller_transaction_history` wkt WHERE wkt.`status`= '.(int) WkMpSellerTransactionHistory::MP_SELLER_ORDER_STATUS.')';

        if (Configuration::get('WK_MP_COMMISSION_DISTRIBUTE_ON') == 1) {
            // Payment accepted
            $sql .= ' AND (SELECT `id_order_state` FROM `'._DB_PREFIX_.'order_history` oh WHERE oh.`id_order` = o.`id_order` AND oh.`id_order_state`='.(int) Configuration::get('PS_OS_PAYMENT').' LIMIT 1)';
        }

        if ($idCustomerSeller) {
            $sql .= ' AND mpsord.`seller_customer_id` = '.(int)$idCustomerSeller;
        }

        $result = Db::getInstance()->getRow($sql);
        return $result;
    }

    /**
     * Get Seller's Order count
     *
     * @param  int $idCurrency       Prestashop Id Currency
     * @param  int $idCustomerSeller Seller's Customer ID
     * @param  boolean $paymentAccepted  Pass false if you all orders whether payment accepted or not
     * @return int
     */
    public static function countTotalOrder($idCurrency = false, $idCustomerSeller = false, $paymentAccepted = true)
    {
        //This function is deprecated
        $sql = 'SELECT COUNT(DISTINCT(wksod.`id_order`)) as total_order
        FROM '._DB_PREFIX_.'wk_mp_seller_order_detail wksod
        JOIN '._DB_PREFIX_.'orders o on (o.`id_order` = wksod.`id_order`) WHERE 1 ';
        if ($idCustomerSeller) {
            $sql .= ' AND wksod.`seller_customer_id` ='.(int) $idCustomerSeller;
        }
        if ($idCurrency) {
            $sql .= ' AND wksod.`id_currency`='.(int) $idCurrency;
        }
        $sql .= ' AND o.`id_order` IN (SELECT wkt.`id_transaction` FROM `'._DB_PREFIX_.'wk_mp_seller_transaction_history` wkt WHERE wkt.`status`= '.(int) WkMpSellerTransactionHistory::MP_SELLER_ORDER_STATUS.')';
        if ($paymentAccepted) {
            // Payment accepted
            $sql .= ' AND (SELECT `id_order_state` FROM `'._DB_PREFIX_.'order_history` oh WHERE oh.`id_order` = o.`id_order` AND oh.`id_order_state`='.(int) Configuration::get('PS_OS_PAYMENT').' LIMIT 1)';
        }
        $result = Db::getInstance()->getValue($sql);

        if ($result) {
            return $result;
        }

        return 0;
    }

    /**
    * Get Seller's Total number of orders according to date range
    *
    * @param  int $sellerCustomerId - Seller's Customer ID
    * @param  date $dateFrom - From Date
    * @param  date $dateTo - To Date
    * @return array/boolean
    */
    public static function getSellerTotalOrders($sellerCustomerId, $dateFrom, $dateTo)
    {
        $orders = array();
        $result = array();

        $sql = 'SELECT LEFT(sod.`date_add`, 10) as date
            FROM `'._DB_PREFIX_.'wk_mp_seller_order_detail` sod
            INNER JOIN `'._DB_PREFIX_.'orders` o ON (sod.`id_order` = o.`id_order`)
            LEFT JOIN `'._DB_PREFIX_.'order_state` os ON o.`current_state` = os.`id_order_state`
            WHERE sod.`seller_customer_id` = '.(int) $sellerCustomerId.'
            AND sod.`date_add` BETWEEN "'.pSQL($dateFrom).' 00:00:00"
            AND "'.pSQL($dateTo).' 23:59:59"
            AND o.`id_order` IN (SELECT wkt.`id_transaction` FROM `'._DB_PREFIX_.'wk_mp_seller_transaction_history` wkt WHERE wkt.`status`= '.(int) WkMpSellerTransactionHistory::MP_SELLER_ORDER_STATUS.'
            AND wkt.`id_customer_seller`= '.(int) $sellerCustomerId.')';

        if (Configuration::get('WK_MP_DASHBOARD_GRAPH') == '1') {
            //for payment accepted
            $sql .= ' AND (SELECT `id_order_state` FROM `'._DB_PREFIX_.'order_history` oh WHERE oh.`id_order` = o.`id_order` AND oh.`id_order_state`='.(int) Configuration::get('PS_OS_PAYMENT').' LIMIT 1)';
        }

        $sql .= 'GROUP BY sod.`id_order`';
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if ($result) {
            foreach ($result as $row) {
                $dateTotalOrders = 1;
                if (isset($orders[strtotime($row['date'])])) {
                    $dateTotalOrders = $orders[strtotime($row['date'])] + 1;
                }
                $orders[strtotime($row['date'])] = $dateTotalOrders;
            }
        }

        return $orders;
    }

    /**
    * Get Seller's Total sales according to date range
    *
    * @param  int $sellerCustomerId - Seller's Customer ID
    * @param  date $dateFrom - From Date
    * @param  date $dateTo - To Date
    * @return array/boolean
    */
    public static function getSellerTotalSales($sellerCustomerId, $dateFrom, $dateTo)
    {
        $sales = array();
        $result = array();

        $sql = 'SELECT sod.`id_order`, LEFT(sod.`date_add`, 10) as date, SUM(ordd.`total_price_tax_excl` / o.`conversion_rate`) as sales
        FROM `'._DB_PREFIX_.'wk_mp_seller_order_detail` sod
        INNER JOIN `'._DB_PREFIX_.'orders` o ON (sod.`id_order` = o.`id_order`)
        LEFT JOIN `'._DB_PREFIX_.'order_state` os ON o.`current_state` = os.`id_order_state`
        LEFT JOIN `'._DB_PREFIX_.'order_detail` ordd ON (ordd.`product_id` = sod.`product_id` AND ordd.`id_order` = sod.`id_order`)
        WHERE sod.`seller_customer_id` = '.(int) $sellerCustomerId.'
        AND sod.`date_add` BETWEEN "'.pSQL($dateFrom).' 00:00:00" AND "'.pSQL($dateTo).' 23:59:59"
        AND o.`id_order` IN (SELECT wkt.`id_transaction` FROM `'._DB_PREFIX_.'wk_mp_seller_transaction_history` wkt WHERE wkt.`status`= '.(int) WkMpSellerTransactionHistory::MP_SELLER_ORDER_STATUS.'
        AND wkt.`id_customer_seller`= '.(int) $sellerCustomerId.')';

        if (Configuration::get('WK_MP_DASHBOARD_GRAPH') == '1') {
            //for payment accepted
            $sql .= ' AND (SELECT `id_order_state` FROM `'._DB_PREFIX_.'order_history` oh WHERE oh.`id_order` = o.`id_order` AND oh.`id_order_state`='.(int) Configuration::get('PS_OS_PAYMENT').' LIMIT 1)';
        }

        $sql .= 'GROUP BY sod.`date_add`';
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if ($result) {
            foreach ($result as $row) {
                if ($cartRuleValue = self::checkIfCartRuleExist($row['id_order'])) {
                    if (isset($sales[strtotime($row['date'])])) {
                        $dateTotalSales = $sales[strtotime($row['date'])] + $row['sales'] - $cartRuleValue;
                    } else {
                        $dateTotalSales = $row['sales'] - $cartRuleValue;
                    }
                } else {
                    if (isset($sales[strtotime($row['date'])])) {
                        $dateTotalSales = $sales[strtotime($row['date'])] + $row['sales'];
                    } else {
                        $dateTotalSales = $row['sales'];
                    }
                }

                $sales[strtotime($row['date'])] = $dateTotalSales;
            }
        }

        return $sales;
    }

    public static function checkIfCartRuleExist($idOrder)
    {
        $sql = 'SELECT ocr.`value` FROM `'._DB_PREFIX_.'order_cart_rule` ocr WHERE ocr.`id_order` = '.(int) $idOrder;
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $cartRuleValue = 0;
        if ($result) {
            foreach ($result as $row) {
                $cartRuleValue += $row['value'];
            }

            return $cartRuleValue;
        }

        return $cartRuleValue;
    }

    public function checkSellerOrder($order, $idSeller)
    {
        $products = $order->getProducts();
        if ($products) {
            $flag = true;
            foreach ($products as $prod) {
                $isProductSeller = WkMpSellerProduct::checkPsProduct($prod['product_id'], $idSeller);
                if (!$isProductSeller) {
                    $flag = false;
                    break;
                }
            }
        }

        return $flag;
    }

    public static function updateSellerDetailsInOrder($sellerCustomerId, $shopNameUnique, $firstName, $lastName, $email)
    {
        $orderDetails = self::getSellerRecord($sellerCustomerId);
        if ($orderDetails) {
            return Db::getInstance()->update(
                'wk_mp_seller_order',
                array(
                    'seller_shop' => pSQL($shopNameUnique),
                    'seller_firstname' => pSQL($firstName),
                    'seller_lastname' => pSQL($lastName),
                    'seller_email' => pSQL($email)
                ),
                'seller_customer_id = '. (int) $sellerCustomerId
            );
        }

        return true;
    }

    public static function updateOrderCarrierTrackingNumber($idOrder, $trackingNumber)
    {
        return Db::getInstance()->update(
            'order_carrier',
            array('tracking_number' => pSQL($trackingNumber)),
            'id_order = '. (int) $idOrder
        );
    }
}
