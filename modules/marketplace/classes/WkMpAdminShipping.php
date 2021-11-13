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

class WkMpAdminShipping extends ObjectModel
{
    public $order_id;
    public $order_reference;
    public $shipping_amount;
    public $admin_earn;
    public $seller_earn;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'wk_mp_admin_shipping',
        'primary' => 'id_wk_mp_admin_shipping',
        'fields' => array(
            'order_id' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'order_reference' => array('type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 9),
            'shipping_amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'admin_earn' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'seller_earn' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
        ),
    );

    /**
     * Get Order Shipping Detail by using order id
     *
     * @param int $idOrder Order ID
     *
     * @return array
     */
    public function getOrderByIdOrder($idOrder)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'wk_mp_admin_shipping`
            WHERE `order_id` = ' . (int) $idOrder
        );
    }

    /**
     * Get Total Shipping Cost by adding all shipping cost (Only seller's orders)
     *
     * @return float
     */
    public static function getTotalShippingCost()
    {
        $result = Db::getInstance()->getValue(
            'SELECT SUM(`shipping_amount`) as shipping FROM `' . _DB_PREFIX_ . 'wk_mp_admin_shipping`'
        );
        if ($result) {
            return $result;
        }

        return false;
    }

    /**
     * Get Total Shipping Cost by adding all shipping cost (Only seller's orders)
     *
     * @param int $idCurrency Currency ID
     *
     * @return array
     */
    public static function getTotalShippingByIdCurrency($idCurrency)
    {
        $sql = 'SELECT
                    o.`id_currency`,
                    SUM(`admin_earn`) as admin_shipping,
                    SUM(`seller_earn`) as seller_shipping
                    FROM ' . _DB_PREFIX_ . 'wk_mp_admin_shipping wkshp
                    LEFT JOIN ' . _DB_PREFIX_ . 'orders o on (o.`id_order` = wkshp.`order_id`)
                    WHERE o.`id_currency` = ' . (int) $idCurrency . '
                    AND o.`id_order` IN (SELECT wkt.`id_transaction` FROM `' . _DB_PREFIX_ . 'wk_mp_seller_transaction_history` wkt WHERE wkt.`status`= ' . (int) WkMpSellerTransactionHistory::MP_SELLER_ORDER_STATUS . ')';

        if (Configuration::get('WK_MP_COMMISSION_DISTRIBUTE_ON') == 1) {
            // Payment accepted
            $sql .= ' AND (SELECT `id_order_state` FROM `' . _DB_PREFIX_ . 'order_history` oh WHERE oh.`id_order` = o.`id_order` AND oh.`id_order_state`=' . (int) Configuration::get('PS_OS_PAYMENT') . ' LIMIT 1)';
        }

        $totalShipping = Db::getInstance()->getRow($sql);
        if ($totalShipping) {
            //Deduct refunded shipping amount
            $refundedShipping = Db::getInstance()->getRow(
                'SELECT SUM(wkt.`admin_shipping`) as admin_shipping, SUM(wkt.`seller_shipping`) as seller_shipping
                FROM `' . _DB_PREFIX_ . 'wk_mp_seller_transaction_history` wkt
                WHERE wkt.`id_currency` = ' . (int) $idCurrency . '
                AND wkt.`status`= ' . (int) WkMpSellerTransactionHistory::MP_ORDER_REFUND_STATUS . ''
            );
            if ($refundedShipping) {
                $totalShipping['admin_shipping'] = $totalShipping['admin_shipping'] - $refundedShipping['admin_shipping'];
                $totalShipping['seller_shipping'] = $totalShipping['seller_shipping'] - $refundedShipping['seller_shipping'];
            }
        }

        return $totalShipping;
    }

    /**
     * Get Total Shipping cost by adding all shipping cost only those order
     * which are payment accepted status (Only Seller's Orders)
     *
     * @return float
     */
    public static function getTotalShippingCostWithPaymentAccepted()
    {
        //This function is deprecated
        $result = Db::getInstance()->getValue(
            'SELECT SUM(mstshp.`shipping_amount`) as shipping
            FROM `' . _DB_PREFIX_ . 'wk_mp_admin_shipping` mstshp
            LEFT JOIN ' . _DB_PREFIX_ . 'orders ordr on (mstshp.`order_id` = ordr.`id_order`)
            WHERE ordr.`id_order` IN (SELECT wkt.`id_transaction` FROM `' . _DB_PREFIX_ . 'wk_mp_seller_transaction_history` wkt WHERE wkt.`status`= ' . (int) WkMpSellerTransactionHistory::MP_SELLER_ORDER_STATUS . ')
            AND (SELECT `id_order_state` FROM `' . _DB_PREFIX_ . 'order_history` oh WHERE oh.`id_order` = ordr.`id_order` AND oh.`id_order_state`=' . (int) Configuration::get('PS_OS_PAYMENT') . ' LIMIT 1)'
        );
        if ($result) {
            return $result;
        }

        return false;
    }

    public static function checkSellerShippingDistributionExist()
    {
        return Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'wk_mp_seller_shipping_distribution`');
    }

    public static function addingAdminShipping($idOrder, $sellerSplitAmount = false, $cart = false)
    {
        $isSeller = false;
        $order = new Order($idOrder);

        $products = $order->getProducts();
        foreach ($products as $product) {
            if (WkMpSellerProduct::getSellerProductByPsIdProduct($product['product_id'])) {
                $totalShipping = $order->total_shipping_tax_incl;
                $isSeller = true;
                break;
            }
        }

        if (Db::getInstance()->getValue(
            'SELECT `free_shipping` FROM `' . _DB_PREFIX_ . 'order_cart_rule` WHERE `id_order` = ' . (int) $idOrder
        )) {
            $totalShipping = 0;
        }

        if ($isSeller) {
            $adminEarning = $totalShipping;
            $sellerEarning = 0;
            $distributorShippingCost = Hook::exec(
                'actionShippingDistribution',
                array(
                    'seller_splitDetail' => $sellerSplitAmount,
                    'order' => $order
                ),
                null,
                true
            );
            if ($distributorShippingCost) {
                //If any module is managing shipping distribution at their end
                //then they can override marketplace seller distribution
                foreach ($distributorShippingCost as $module) {
                    $adminEarning = 0;
                    if ($module) {
                        foreach ($module as $sellerIdCustomer => $shippingAmount) {
                            if ($sellerIdCustomer == 'admin') {
                                $adminEarning = $shippingAmount;
                            } else {
                                Db::getInstance()->insert(
                                    'wk_mp_seller_shipping_distribution',
                                    array(
                                        'order_id' => (int) $idOrder,
                                        'order_reference' => $order->reference,
                                        'seller_customer_id' => (int) $sellerIdCustomer,
                                        'seller_earn' => Tools::ps_round($shippingAmount, 6),
                                    )
                                );
                                $sellerEarning += $shippingAmount;
                            }
                        }
                    }
                }
            } else {
                if ($sellerSplitAmount && $cart && $order) {
                    $distributorShippingCost = self::getShippingDistributionData($sellerSplitAmount, $cart, $order);
                    if ($distributorShippingCost) {
                        $adminEarning = 0;
                        foreach ($distributorShippingCost as $sellerIdCustomer => $shippingAmount) {
                            if ($sellerIdCustomer == 'admin') {
                                $adminEarning = $shippingAmount;
                            } else {
                                Db::getInstance()->insert(
                                    'wk_mp_seller_shipping_distribution',
                                    array(
                                        'order_id' => (int) $idOrder,
                                        'order_reference' => pSQL($order->reference),
                                        'seller_customer_id' => (int) $sellerIdCustomer,
                                        'seller_earn' => (float) Tools::ps_round($shippingAmount, 6),
                                    )
                                );

                                $sellerEarning += $shippingAmount;
                            }
                        }
                    }
                }
            }

            // adding shipping to marketplace admin shipping table
            $objAdminShipping = new self();
            $objAdminShipping->order_id = $idOrder;
            $objAdminShipping->order_reference = $order->reference;
            $objAdminShipping->shipping_amount = Tools::ps_round($totalShipping, 6);
            $objAdminShipping->admin_earn = Tools::ps_round($adminEarning, 6);
            $objAdminShipping->seller_earn = Tools::ps_round($sellerEarning, 6);
            $objAdminShipping->save();
        }
    }

    public static function getSellerShippingByIdOrder($idOrder, $idSellerCustomer)
    {
        return Db::getInstance()->getValue(
            'SELECT `seller_earn` FROM `' . _DB_PREFIX_ . 'wk_mp_seller_shipping_distribution`
            WHERE `order_id` = ' . (int) $idOrder . ' AND `seller_customer_id` = ' . (int) $idSellerCustomer
        );
    }

    public static function getTotalSellerShipping($idSellerCustomer, $idCurrency)
    {
        $sql = 'SELECT SUM(wkshpdist.`seller_earn`) as seller_shipping,
                o.`id_currency` FROM `' . _DB_PREFIX_ . 'wk_mp_admin_shipping` wkshp
                INNER JOIN ' . _DB_PREFIX_ . 'orders o on (o.`id_order` = wkshp.`order_id`)
                INNER JOIN ' . _DB_PREFIX_ . 'wk_mp_seller_shipping_distribution wkshpdist on (wkshp.`order_id` = wkshpdist.`order_id`)
                WHERE wkshpdist.`seller_customer_id` = ' . (int) $idSellerCustomer . '
                AND o.`id_currency` = ' . (int) $idCurrency . '
                AND o.`id_order` IN (SELECT wkt.`id_transaction` FROM `' . _DB_PREFIX_ . 'wk_mp_seller_transaction_history` wkt WHERE wkt.`status`= ' . (int) WkMpSellerTransactionHistory::MP_SELLER_ORDER_STATUS . '
                AND wkt.`id_customer_seller`= '.(int) $idSellerCustomer.')';

        if (Configuration::get('WK_MP_COMMISSION_DISTRIBUTE_ON') == 1) {
            // Payment accepted
            $sql .= ' AND (SELECT `id_order_state` FROM `' . _DB_PREFIX_ . 'order_history` oh WHERE oh.`id_order` = o.`id_order` AND oh.`id_order_state`=' . (int) Configuration::get('PS_OS_PAYMENT') . ' LIMIT 1)';
        }
        $sql .= ' group by o.`id_currency`';

        $totalSellerShipping = Db::getInstance()->getRow($sql);
        if ($totalSellerShipping) {
            //Deduct refunded shipping amount
            $refundedSellerShipping = Db::getInstance()->getValue(
                'SELECT SUM(wkt.`seller_shipping`) FROM `' . _DB_PREFIX_ . 'wk_mp_seller_transaction_history` wkt
                WHERE wkt.`id_customer_seller` = ' . (int) $idSellerCustomer . '
                AND wkt.`id_currency` = ' . (int) $idCurrency . '
                AND wkt.`status`= ' . (int) WkMpSellerTransactionHistory::MP_ORDER_REFUND_STATUS . ''
            );
            if ($refundedSellerShipping) {
                $totalSellerShipping['seller_shipping'] = $totalSellerShipping['seller_shipping'] - $refundedSellerShipping;
            }
        }

        return $totalSellerShipping;
    }

    public static function getTotalAdminShipping($idCurrency, $idSellerCustomer)
    {
        $sql = 'SELECT SUM(wkshp.`admin_earn`) as admin_shipping,
                o.`id_currency` FROM `' . _DB_PREFIX_ . 'wk_mp_admin_shipping` wkshp
                INNER JOIN ' . _DB_PREFIX_ . 'orders o on (o.`id_order` = wkshp.`order_id`)
                INNER JOIN (SELECT DISTINCT id_order, seller_customer_id FROM ' . _DB_PREFIX_ . 'wk_mp_seller_order_detail) wksod ON (o.id_order = wksod.id_order)
                WHERE wksod.`seller_customer_id` = ' . (int) $idSellerCustomer . '
                AND o.`id_currency` = ' . (int) $idCurrency . '
                AND o.`id_order` IN (SELECT wkt.`id_transaction` FROM `' . _DB_PREFIX_ . 'wk_mp_seller_transaction_history` wkt WHERE wkt.`status`= ' . (int) WkMpSellerTransactionHistory::MP_SELLER_ORDER_STATUS . '
                AND wkt.`id_customer_seller`= '.(int) $idSellerCustomer.')';

        if (Configuration::get('WK_MP_COMMISSION_DISTRIBUTE_ON') == 1) {
            // Payment accepted
            $sql .= ' AND (SELECT `id_order_state` FROM `' . _DB_PREFIX_ . 'order_history` oh WHERE oh.`id_order` = o.`id_order` AND oh.`id_order_state`=' . (int) Configuration::get('PS_OS_PAYMENT') . ' LIMIT 1)';
        }

        $totalAdminShipping = Db::getInstance()->getRow($sql);
        if ($totalAdminShipping) {
            //Deduct refunded shipping amount
            $refundedAdminShipping = Db::getInstance()->getValue(
                'SELECT SUM(wkt.`admin_shipping`) FROM `' . _DB_PREFIX_ . 'wk_mp_seller_transaction_history` wkt
                WHERE wkt.`id_customer_seller` = ' . (int) $idSellerCustomer . '
                AND wkt.`id_currency` = ' . (int) $idCurrency . '
                AND wkt.`status`= ' . (int) WkMpSellerTransactionHistory::MP_ORDER_REFUND_STATUS . ''
            );
            if ($refundedAdminShipping) {
                $totalAdminShipping['admin_shipping'] = $totalAdminShipping['admin_shipping'] - $refundedAdminShipping;
            }
        }

        return $totalAdminShipping;
    }

    public static function getShippingDistributionByReference($idPsReference)
    {
        $distributionType = Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_carrier_distributor_type`
            WHERE `id_ps_reference` = '.(int) $idPsReference
        );
        if ($distributionType) {
            return $distributionType;
        } else {
            return false;
        }
    }

    public static function updatePsShippingDistributionType($idPsReference, $shippingDistributeType)
    {
        if ($idPsReference && $shippingDistributeType) {
            $distributionExist = self::getShippingDistributionByReference($idPsReference);
            if ($distributionExist) {
                $updated = Db::getInstance()->update(
                    'wk_mp_carrier_distributor_type',
                    array(
                        'type' => pSQL($shippingDistributeType)
                    ),
                    'id_ps_reference = '. (int) $idPsReference
                );
            } else {
                $updated = Db::getInstance()->insert(
                    'wk_mp_carrier_distributor_type',
                    array(
                        'id_ps_reference' => (int) $idPsReference,
                        'type' => pSQL($shippingDistributeType),
                    )
                );
            }

            if ($updated) {
                return true;
            }
        }

        return false;
    }

    /**
    * Get Shipping Distribution with Marketplace
    *
    * @param  array $sellerProduct - seller product details
    * @param  array $cart - cart details
    * @param  array $order - order details
    *
    * @return array
    */
    public static function getShippingDistributionData($sellerProduct, $cart, $order = false)
    {
        $distributorShippingCost = array();

        //We have to do this because when customer reorder any product then they don't update id_carrier in cart table.
        //But in payment gateway split time, we don't have order id so we have to get through cart table
        if ($order) {
            //if get distribute shipping amount after order complete
            $distributorShippingCost = self::distributedShippingDataAfterOrder($sellerProduct, $cart, $order);
        } else {
            //if get distribute shipping amount before order complete (for payment gateway split function)
            $distributorShippingCost = self::distributedShippingDataBeforeOrder($sellerProduct, $cart);
        }

        return $distributorShippingCost;
    }

    public static function distributedShippingDataAfterOrder($sellerProduct, $cart, $order)
    {
        $distributorShippingCost = array();
        //Distribute shipping amount with Admin, Seller Or Both (If allowed from configuration)
        if (Configuration::get('WK_MP_SHIPPING_DISTRIBUTION_ALLOW')) {
            $idCarrier = $order->id_carrier;
            $distributionType = 'admin';
            $objCarrier = new Carrier($idCarrier);
            // if distribution type is set for currect carrier from PS carrier tab
            $distributionExist = self::getShippingDistributionByReference($objCarrier->id_reference);
            if ($distributionExist) {
                $distributionType = $distributionExist['type'];
            }

            if ($sellerProduct && ($distributionType == 'seller' || $distributionType == 'both')) {
                //If shipping is distributed to seller or both (admin & seller)
                if (Module::isEnabled('mpcartordersplit')) {
                    //If 'mpcartordersplit' is enabled then shipping full amt will be distribute between each seller
                    $orderProductIds = array();
                    $orderProducts = $order->getProducts();
                    if ($orderProducts) {
                        foreach ($orderProducts as $orderProduct) {
                            $orderProductIds[] = $orderProduct['product_id'];
                        }
                    }
                    $carrierProductList = $cart->getProducts();
                    $sellerProductList = array();
                    if ($carrierProductList) {
                        foreach ($carrierProductList as $carrierProduct) {
                            if ($orderProductIds && in_array($carrierProduct['id_product'], $orderProductIds)) {
                                //Check if product is seller product
                                if ($sellerProductData = WkMpSellerProduct::getSellerProductByPsIdProduct(
                                    $carrierProduct['id_product']
                                )) {
                                    //Get seller customer id of seller product
                                    if ($sellerDetails = WkMpSeller::getSeller($sellerProductData['id_seller'])) {
                                        $sellerProductList[$sellerDetails['seller_customer_id']][] = $carrierProduct;
                                    }
                                }
                            }
                        }
                    }

                    if ($sellerProductList) {
                        foreach ($sellerProductList as $sellerIdCustomer => $sellerCarrierProduct) {
                            // $sellerIdCustomer index can be 'admin' or actual seller customer id
                            $totalShippingCost = $cart->getPackageShippingCost(
                                $idCarrier,
                                true,
                                null,
                                $sellerCarrierProduct
                            );
                            if ($totalShippingCost) {
                                if ($distributionType == 'both') {
                                    //Distribute seller individual cost between admin and seller
                                    //On basis of commission rate
                                    $distributorShippingCost = self::getDistributionDataForBoth(
                                        $totalShippingCost,
                                        $sellerIdCustomer,
                                        $distributorShippingCost
                                    );
                                } else {
                                    if (isset($distributorShippingCost[$sellerIdCustomer])) {
                                        $distributorShippingCost[$sellerIdCustomer] += $totalShippingCost;
                                    } else {
                                        $distributorShippingCost[$sellerIdCustomer] = $totalShippingCost;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $totalShippingCost = $cart->getPackageShippingCost($idCarrier);
                    if ($totalShippingCost) {
                        //if shipping is distributed to seller or both (not only admin) from PS carrier Tab
                        $distributorsCount = array();
                        $distributorsCount['no_of_sellers'] = 0;
                        $distributorsCount['admin_exist'] = 0;
                        $totalUnit = 0; //total weight or total price

                        foreach ($sellerProduct as $distributorKey => $distributorValue) {
                            if ($distributorKey == 'admin') {
                                $distributorsCount['admin_exist'] += 1;
                            } else {
                                $distributorsCount['no_of_sellers'] += 1;
                            }

                            $addProductUnit = true;
                            if (($distributorKey == 'admin')
                            && !Configuration::get('WK_MP_SHIPPING_ADMIN_DISTRIBUTION')) {
                                //if admin product exist but distribution not allowed for admin
                                //then admin product unit will not calculate in totalUnit
                                $addProductUnit = false;
                            }

                            //If particular product is allowed for adding its unit price or weight
                            if ($addProductUnit) {
                                if ($objCarrier->shipping_method == 1) {
                                    //For shipping method - Weight
                                    $totalUnit += $distributorValue['total_product_weight'];
                                } elseif ($objCarrier->shipping_method == 2) {
                                    //For shipping method - Price
                                    $totalUnit += $distributorValue['total_price_tax_incl'];
                                }
                            }
                        }

                        if ((count($sellerProduct) == 1)
                        && ($distributorsCount['no_of_sellers'] == 1)
                        && ($distributorsCount['admin_exist'] == 0)
                        ) {
                            //If only one seller product is exist in Order_cart
                            foreach ($sellerProduct as $sellerIdCustomer => $seller) {
                                if ($distributionType == 'both') {
                                    //Distribute seller individual cost between admin and seller
                                    //On basis of commission rate
                                    $distributorShippingCost = self::getDistributionDataForBoth(
                                        $totalShippingCost,
                                        $sellerIdCustomer,
                                        $distributorShippingCost
                                    );
                                } else {
                                    $distributorShippingCost[$sellerIdCustomer] = $totalShippingCost;
                                }
                                break;
                            }
                        } else {
                            //If in order, admin product exist with seller product
                            //and Admin set shipping distributed to Seller in Carriers page
                            //then shipping distributed between both seller and admin.
                            //Otherwise shipping will go to sellers only
                            $adminDistribute = false;
                            if (($distributorsCount['admin_exist'] > 0)
                            && Configuration::get('WK_MP_SHIPPING_ADMIN_DISTRIBUTION')) {
                                $adminDistribute = true;
                            }

                            // totalUnit can be total weight or total amount of all products in Order_cart
                            //and after this, we will calculate distributePercent of the basis of each product
                            if ($totalUnit > 0) {
                                if ($adminDistribute) {
                                    //Admin product exists with seller products then divide shipping
                                    //according to total weight or total price including admin
                                    foreach ($sellerProduct as $sellerIdCustomer => $seller) {
                                        if ($objCarrier->shipping_method == 1) {
                                            //For shipping method - Weight
                                            $distributePercent = ($seller['total_product_weight']/$totalUnit) * 100;
                                        } elseif ($objCarrier->shipping_method == 2) {
                                            //For shipping method - Price
                                            $distributePercent = ($seller['total_price_tax_incl']/$totalUnit) * 100;
                                        }

                                        //Get seller and admin individual cost
                                        $individualShippingCost = ($totalShippingCost * $distributePercent) / 100;

                                        if ($distributionType == 'both') {
                                            if ($sellerIdCustomer == 'admin') {
                                                //send admin individual cost to admin
                                                if (isset($distributorShippingCost['admin'])) {
                                                    $distributorShippingCost['admin'] += $individualShippingCost;
                                                } else {
                                                    $distributorShippingCost['admin'] = $individualShippingCost;
                                                }
                                            }
                                            //Now Distribute seller individual cost between admin and seller
                                            //on basis of commission rate
                                            $distributorShippingCost = self::getDistributionDataForBoth(
                                                $individualShippingCost,
                                                $sellerIdCustomer,
                                                $distributorShippingCost
                                            );
                                        } else {
                                            //if only seller then send individual distribution cost to admin and seller
                                            $distributorShippingCost[$sellerIdCustomer] = $individualShippingCost;
                                        }
                                    }
                                } else {
                                    //Atleast 2 seller's product exist then divide shipping in that sellers only
                                    //according to total weight or total price (not admin)
                                    foreach ($sellerProduct as $sellerIdCustomer => $seller) {
                                        if ($sellerIdCustomer != 'admin') {
                                            if ($objCarrier->shipping_method == 1) {
                                                //For shipping method - Weight
                                                $distributePercent = ($seller['total_product_weight']/$totalUnit) * 100;
                                            } elseif ($objCarrier->shipping_method == 2) {
                                                //For shipping method - Price
                                                $distributePercent = ($seller['total_price_tax_incl']/$totalUnit) * 100;
                                            }

                                            //Get seller and admin individual cost
                                            $individualShippingCost = ($totalShippingCost * $distributePercent) / 100;

                                            if ($distributionType == 'both') {
                                                //Now Distribute seller individual cost between admin and seller
                                                //on basis of commission rate
                                                $distributorShippingCost = self::getDistributionDataForBoth(
                                                    $individualShippingCost,
                                                    $sellerIdCustomer,
                                                    $distributorShippingCost
                                                );
                                            } else {
                                                //if only seller then send individual distribution cost to both
                                                $distributorShippingCost[$sellerIdCustomer] = $individualShippingCost;
                                            }
                                        }
                                    }
                                }
                            } else {
                                //Divide equally
                                if ($adminDistribute) {
                                    //Admin product exists with seller products
                                    //then divide shipping in all members equally including admin
                                    $totalDistributionCount = count($sellerProduct);
                                    foreach ($sellerProduct as $sellerIdCustomer => $seller) {
                                        //Get seller and admin individual cost
                                        $individualShippingCost = $totalShippingCost/$totalDistributionCount;

                                        if ($distributionType == 'both') {
                                            if ($sellerIdCustomer == 'admin') {
                                                //send admin individual cost to admin
                                                if (isset($distributorShippingCost['admin'])) {
                                                    $distributorShippingCost['admin'] += $individualShippingCost;
                                                } else {
                                                    $distributorShippingCost['admin'] = $individualShippingCost;
                                                }
                                            }
                                            //Now Distribute seller individual cost between admin and seller
                                            //on basis of commission rate
                                            $distributorShippingCost = self::getDistributionDataForBoth(
                                                $individualShippingCost,
                                                $sellerIdCustomer,
                                                $distributorShippingCost
                                            );
                                        } else {
                                            //if only seller then send individual distribution cost to admin and seller
                                            $distributorShippingCost[$sellerIdCustomer] = $individualShippingCost;
                                        }
                                    }
                                } else {
                                    //Atleast 2 seller's product exist
                                    //then divide shipping in that sellers only as equally (not admin)
                                    $totalDistributionCount = $distributorsCount['no_of_sellers'];
                                    foreach ($sellerProduct as $sellerIdCustomer => $seller) {
                                        if ($sellerIdCustomer != 'admin') {
                                            //Get seller and admin individual cost
                                            $individualShippingCost = $totalShippingCost/$totalDistributionCount;

                                            if ($distributionType == 'both') {
                                                //Now Distribute seller individual cost between admin and seller
                                                //on basis of commission rate
                                                $distributorShippingCost = self::getDistributionDataForBoth(
                                                    $individualShippingCost,
                                                    $sellerIdCustomer,
                                                    $distributorShippingCost
                                                );
                                            } else {
                                                //if only seller then send individual distribution cost to both
                                                $distributorShippingCost[$sellerIdCustomer] = $individualShippingCost;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $distributorShippingCost;
    }

    public static function distributedShippingDataBeforeOrder($sellerProduct, $cart)
    {
        $distributorShippingCost = array();
        if (Configuration::get('WK_MP_SHIPPING_DISTRIBUTION_ALLOW')) {
            $carrierList = array();
            //May be same cart has one or more carrier, So we get all carriers for split before order
            $cartDeliveryList = $cart->getDeliveryOptionList();
            if ($cartDeliveryList) {
                $selectedCarriers = $cart->getDeliveryOption();
                if ($selectedCarriers) {
                    foreach ($selectedCarriers as $idDeliveryAddress => $selectedCarriersString) {
                        $carrierList = $cartDeliveryList[$idDeliveryAddress][$selectedCarriersString]['carrier_list'];
                        break;
                    }
                }
            }

            if ($carrierList) {
                foreach ($carrierList as $idCarrier => $carrierData) {
                    //All Carriers distribution (one by one) will be divide b/w sellers and admin
                    $splitPersons = array();
                    $sellerProductList = array();
                    if (isset($carrierData['product_list'])) {
                        foreach ($carrierData['product_list'] as $carrierProduct) {
                            //Check if product is seller product
                            if ($sellerProductData = WkMpSellerProduct::getSellerProductByPsIdProduct(
                                $carrierProduct['id_product']
                            )) {
                                //Get seller customer id of seller product
                                if ($sellerDetails = WkMpSeller::getSeller($sellerProductData['id_seller'])) {
                                    $splitPersons[] = $sellerDetails['seller_customer_id'];
                                    $sellerProductList[$sellerDetails['seller_customer_id']][] = $carrierProduct;
                                }
                            } else {
                                $splitPersons[] = 'admin';
                            }
                        }
                    }

                    //Distribute shipping amount with Admin, Seller Or Both (If allowed from configuration)
                    if ($splitPersons && $idCarrier) {
                        $distributionType = 'admin';
                        $objCarrier = new Carrier($idCarrier);
                        // if distribution type is set for currect carrier from PS carrier tab
                        $distributionExist = self::getShippingDistributionByReference($objCarrier->id_reference);
                        if ($distributionExist) {
                            $distributionType = $distributionExist['type'];
                        }

                        if ($sellerProduct && ($distributionType == 'seller' || $distributionType == 'both')) {
                            //If shipping is distributed to seller or both (admin & seller)
                            if (Module::isEnabled('mpcartordersplit')) {
                                //If mp cart and order split is enabled then shipping full amount will be distribute between each seller
                                if ($sellerProductList) {
                                    foreach ($sellerProductList as $sellerIdCustomer => $sellerCarrierProduct) {
                                        // $sellerIdCustomer index can be 'admin' or actual seller customer id
                                        $totalShippingCost = $cart->getPackageShippingCost(
                                            $idCarrier,
                                            true,
                                            null,
                                            $sellerCarrierProduct
                                        );
                                        if ($totalShippingCost) {
                                            if ($distributionType == 'both') {
                                                //Distribute seller individual cost between admin and seller
                                                //on basis of commission rate
                                                $distributorShippingCost = self::getDistributionDataForBoth(
                                                    $totalShippingCost,
                                                    $sellerIdCustomer,
                                                    $distributorShippingCost
                                                );
                                            } else {
                                                if (isset($distributorShippingCost[$sellerIdCustomer])) {
                                                    $distributorShippingCost[$sellerIdCustomer] += $totalShippingCost;
                                                } else {
                                                    $distributorShippingCost[$sellerIdCustomer] = $totalShippingCost;
                                                }
                                            }
                                        }
                                    }
                                }
                            } else {
                                $totalShippingCost = $cart->getPackageShippingCost($idCarrier);
                                if ($totalShippingCost) {
                                    //if shipping is distributed to seller or both (not only admin) from PS carrier Tab
                                    $distributorsCount = array();
                                    $distributorsCount['no_of_sellers'] = 0;
                                    $distributorsCount['admin_exist'] = 0;
                                    $totalUnit = 0; //total weight or total price

                                    foreach ($sellerProduct as $distributorKey => $distributorValue) {
                                        if (in_array($distributorKey, $splitPersons)) {
                                            if ($distributorKey == 'admin') {
                                                $distributorsCount['admin_exist'] += 1;
                                            } else {
                                                $distributorsCount['no_of_sellers'] += 1;
                                            }

                                            $addProductUnit = true;
                                            if (($distributorKey == 'admin')
                                            && !Configuration::get('WK_MP_SHIPPING_ADMIN_DISTRIBUTION')) {
                                                //if admin product exist but distribution not allowed for admin
                                                //then admin product unit will not calculate in totalUnit
                                                $addProductUnit = false;
                                            }

                                            //If particular product is allowed for adding its unit price or weight
                                            if ($addProductUnit) {
                                                if ($objCarrier->shipping_method == 1) {
                                                    //For shipping method - Weight
                                                    $totalUnit += $distributorValue['total_product_weight'];
                                                } elseif ($objCarrier->shipping_method == 2) {
                                                    //For shipping method - Price
                                                    $totalUnit += $distributorValue['total_price_tax_incl'];
                                                }
                                            }
                                        }
                                    }

                                    if ((count($sellerProduct) == 1)
                                    && ($distributorsCount['no_of_sellers'] == 1)
                                    && ($distributorsCount['admin_exist'] == 0)
                                    ) {
                                        //If only one seller product is exist in Order_cart
                                        foreach ($sellerProduct as $sellerIdCustomer => $seller) {
                                            if (in_array($sellerIdCustomer, $splitPersons)) {
                                                if ($distributionType == 'both') {
                                                    //Distribute seller individual cost between admin and seller
                                                    //on basis of commission rate
                                                    $distributorShippingCost = self::getDistributionDataForBoth(
                                                        $totalShippingCost,
                                                        $sellerIdCustomer,
                                                        $distributorShippingCost
                                                    );
                                                } else {
                                                    if (isset($distributorShippingCost[$sellerIdCustomer])) {
                                                        $distributorShippingCost[$sellerIdCustomer] += $totalShippingCost;
                                                    } else {
                                                        $distributorShippingCost[$sellerIdCustomer] = $totalShippingCost;
                                                    }
                                                }
                                                break;
                                            }
                                        }
                                    } else {
                                        //If in order, admin product exist with seller product
                                        //and Admin set shipping distributed to Seller in Carriers page
                                        //then shipping distributed between both seller and admin.
                                        //Otherwise shipping will go to sellers only
                                        $adminDistribute = false;
                                        if (($distributorsCount['admin_exist'] > 0)
                                        && Configuration::get('WK_MP_SHIPPING_ADMIN_DISTRIBUTION')) {
                                            $adminDistribute = true;
                                        }

                                        $individualShippingCost = 0;
                                        // totalUnit can be total weight or total amount of all products in Order_cart
                                        //and then we will calculate distributePercent of the basis of each product
                                        if ($totalUnit > 0) {
                                            if ($adminDistribute) {
                                                //Admin product exists with seller products then divide shipping
                                                //according to total weight or total price including admin
                                                foreach ($sellerProduct as $sellerIdCustomer => $seller) {
                                                    if (in_array($sellerIdCustomer, $splitPersons)) {
                                                        if ($objCarrier->shipping_method == 1) {
                                                            //For shipping method - Weight
                                                            $distributePercent = ($seller['total_product_weight']/$totalUnit) * 100;
                                                        } elseif ($objCarrier->shipping_method == 2) {
                                                            //For shipping method - Price
                                                            $distributePercent = ($seller['total_price_tax_incl']/$totalUnit) * 100;
                                                        }

                                                        //Get seller and admin individual cost
                                                        if (isset($distributorShippingCost[$sellerIdCustomer])) {
                                                            $individualShippingCost += (($totalShippingCost * $distributePercent) / 100);
                                                        } else {
                                                            $individualShippingCost = (($totalShippingCost * $distributePercent) / 100);
                                                        }

                                                        if ($distributionType == 'both') {
                                                            if ($sellerIdCustomer == 'admin') {
                                                                //send admin individual cost to admin
                                                                if (isset($distributorShippingCost['admin'])) {
                                                                    $distributorShippingCost['admin'] += $individualShippingCost;
                                                                } else {
                                                                    $distributorShippingCost['admin'] = $individualShippingCost;
                                                                }
                                                            }
                                                            //Now Distribute seller individual cost
                                                            //between admin and seller on basis of commission rate
                                                            $distributorShippingCost = self::getDistributionDataForBoth(
                                                                $individualShippingCost,
                                                                $sellerIdCustomer,
                                                                $distributorShippingCost
                                                            );
                                                        } else {
                                                            //if only seller then send individual distribution cost
                                                            //to admin and seller
                                                            $distributorShippingCost[$sellerIdCustomer] = $individualShippingCost;
                                                        }
                                                    }
                                                }
                                            } else {
                                                //Atleast 2 seller's product exist
                                                //then divide shipping in that sellers only
                                                //according to total weight or total price (not admin)
                                                foreach ($sellerProduct as $sellerIdCustomer => $seller) {
                                                    if (in_array($sellerIdCustomer, $splitPersons)) {
                                                        if ($sellerIdCustomer != 'admin') {
                                                            if ($objCarrier->shipping_method == 1) {
                                                                //For shipping method - Weight
                                                                $distributePercent = ($seller['total_product_weight']/$totalUnit) * 100;
                                                            } elseif ($objCarrier->shipping_method == 2) {
                                                                //For shipping method - Price
                                                                $distributePercent = ($seller['total_price_tax_incl']/$totalUnit) * 100;
                                                            }

                                                            //Get seller and admin individual cost
                                                            if (isset($distributorShippingCost[$sellerIdCustomer])) {
                                                                $individualShippingCost += (($totalShippingCost * $distributePercent) / 100);
                                                            } else {
                                                                $individualShippingCost = (($totalShippingCost * $distributePercent) / 100);
                                                            }

                                                            if ($distributionType == 'both') {
                                                                //Now Distribute seller individual cost
                                                                //between admin and seller on basis of commission rate
                                                                $distributorShippingCost = self::getDistributionDataForBoth(
                                                                    $individualShippingCost,
                                                                    $sellerIdCustomer,
                                                                    $distributorShippingCost
                                                                );
                                                            } else {
                                                                //if only seller then send individual distribution cost
                                                                //to admin and seller
                                                                $distributorShippingCost[$sellerIdCustomer] = $individualShippingCost;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        } else {
                                            //Divide equally
                                            if ($adminDistribute) {
                                                //Admin product exists with seller products
                                                //then divide shipping in all members equally including admin
                                                $totalDistributionCount = count($sellerProduct);
                                                foreach ($sellerProduct as $sellerIdCustomer => $seller) {
                                                    if (in_array($sellerIdCustomer, $splitPersons)) {
                                                        //Get seller and admin individual cost
                                                        if (isset($distributorShippingCost[$sellerIdCustomer])) {
                                                            $individualShippingCost += ($totalShippingCost/$totalDistributionCount);
                                                        } else {
                                                            $individualShippingCost = $totalShippingCost/$totalDistributionCount;
                                                        }

                                                        if ($distributionType == 'both') {
                                                            if ($sellerIdCustomer == 'admin') {
                                                                //send admin individual cost to admin
                                                                if (isset($distributorShippingCost['admin'])) {
                                                                    $distributorShippingCost['admin'] += $individualShippingCost;
                                                                } else {
                                                                    $distributorShippingCost['admin'] = $individualShippingCost;
                                                                }
                                                            }
                                                            //Now Distribute seller individual cost
                                                            //between admin and seller on basis of commission rate
                                                            $distributorShippingCost = self::getDistributionDataForBoth(
                                                                $individualShippingCost,
                                                                $sellerIdCustomer,
                                                                $distributorShippingCost
                                                            );
                                                        } else {
                                                            //if only seller then send individual distribution cost
                                                            //to admin and seller
                                                            $distributorShippingCost[$sellerIdCustomer] = $individualShippingCost;
                                                        }
                                                    }
                                                }
                                            } else {
                                                //Atleast 2 seller's product exist then divide shipping in that sellers
                                                //only as equally (not admin)
                                                $totalDistributionCount = $distributorsCount['no_of_sellers'];
                                                foreach ($sellerProduct as $sellerIdCustomer => $seller) {
                                                    if (in_array($sellerIdCustomer, $splitPersons)) {
                                                        if ($sellerIdCustomer != 'admin') {
                                                            //Get seller and admin individual cost
                                                            if (isset($distributorShippingCost[$sellerIdCustomer])) {
                                                                $individualShippingCost += ($totalShippingCost/$totalDistributionCount);
                                                            } else {
                                                                $individualShippingCost = $totalShippingCost/$totalDistributionCount;
                                                            }

                                                            if ($distributionType == 'both') {
                                                                //Now Distribute seller individual cost between
                                                                //admin and seller on basis of commission rate
                                                                $distributorShippingCost = self::getDistributionDataForBoth(
                                                                    $individualShippingCost,
                                                                    $sellerIdCustomer,
                                                                    $distributorShippingCost
                                                                );
                                                            } else {
                                                                //if only seller then send individual distribution cost
                                                                //to admin and seller
                                                                $distributorShippingCost[$sellerIdCustomer] = $individualShippingCost;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $distributorShippingCost;
    }

    public static function getDistributionDataForBoth($totalShippingCost, $sellerIdCustomer, $distributorShippingCost)
    {
        //Distribute seller individual cost between admin and seller on basis of commission rate
        if ($sellerIdCustomer != 'admin') {
            $objMpShippingCommission = new WkMpShippingCommission();
            $commissionBySeller = $objMpShippingCommission->getCommissionRateBySellerCustomerId($sellerIdCustomer);
            if (!is_numeric($commissionBySeller)) {
                if ($globalCommission = Configuration::get('WK_MP_GLOBAL_SHIPPING_COMMISSION')) {
                    $commissionRate = $globalCommission;
                } else {
                    $commissionRate = 0;
                }
            } else {
                $commissionRate = $commissionBySeller;
            }

            $adminShippingCommission = ($totalShippingCost * $commissionRate) / 100;
            if (isset($distributorShippingCost['admin'])) {
                $distributorShippingCost['admin'] += $adminShippingCommission;
            } else {
                $distributorShippingCost['admin'] = $adminShippingCommission;
            }

            $sellerShippingAmount = (($totalShippingCost) * (100 - $commissionRate)) / 100;
            if (isset($distributorShippingCost[$sellerIdCustomer])) {
                $distributorShippingCost[$sellerIdCustomer] += $sellerShippingAmount;
            } else {
                $distributorShippingCost[$sellerIdCustomer] = $sellerShippingAmount;
            }
        }

        return $distributorShippingCost;
    }
}
