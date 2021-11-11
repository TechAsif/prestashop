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

class WkMpSellerOrderDetail extends ObjectModel
{
    public $id_seller_order;  /** @var id of wk_mp_seller_order */
    public $product_id;
    public $product_attribute_id;
    public $id_customization;
    public $seller_customer_id; /** @var id_customer of marketplace seller */
    public $seller_name;
    public $product_name;
    public $quantity;
    public $price_ti; /* product price with tax */
    public $price_te; /* product price without tax */
    public $admin_commission; /* admin commission */
    public $admin_tax; /* admin tax */
    public $seller_amount; /* seller amount */
    public $seller_tax;    /* seller tax*/
    public $id_order;
    public $commission_type;
    public $commission_rate;
    public $commission_amt;
    public $commission_tax_amt;
    public $tax_distribution_type;
    public $id_currency; /* order currency id */
    public $date_add;

    public static $definition = array(
        'table' => 'wk_mp_seller_order_detail',
        'primary' => 'id_mp_order_detail',
        'fields' => array(
            'product_id' => array('type' => self::TYPE_INT, 'required' => true),
            'product_attribute_id' => array('type' => self::TYPE_INT, 'required' => true),
            'id_customization' => array('type' => self::TYPE_INT),
            'id_seller_order' => array('type' => self::TYPE_INT, 'required' => true),
            'seller_customer_id' => array('type' => self::TYPE_INT, 'required' => true),
            'seller_name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
            'product_name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
            'quantity' => array('type' => self::TYPE_INT, 'required' => true),
            'price_ti' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'price_te' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'admin_commission' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'admin_tax' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'seller_amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'seller_tax' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'id_order' => array('type' => self::TYPE_INT, 'required' => true),
            'commission_type' => array('type' => self::TYPE_STRING, 'required' => true),
            'commission_rate' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'commission_amt' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'commission_tax_amt' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'tax_distribution_type' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
            'id_currency' => array('type' => self::TYPE_INT, 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        ),
    );

    /**
     * Get order details from the individual order or seller
     *
     * @param int $idOrder Order ID
     * @param int $sellerCustomerId seller customer ID
     *
     * @return array/bool
     */
    public function getOrderCommissionDetails($idOrder, $sellerCustomerId = false)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_order_detail` WHERE `id_order` = '.(int) $idOrder;
        if ($sellerCustomerId) {
            $sql .=  ' AND seller_customer_id = '.(int) $sellerCustomerId;
        }
        $details = Db::getInstance()->executeS($sql);
        if ($details) {
            return $details;
        }
        return false;
    }

    /**
     * Get Products from the order using Order ID.
     *
     * @param int $idOrder Order ID
     * @param int $idLang Language ID
     *
     * @return array
     */
    public function getProductsFromOrder($idOrder, $idLang = false)
    {
        if (!$idLang) {
            $idLang = Configuration::get('PS_LANG_DEFAULT');
        }
        $orderDetail = Db::getInstance()->executeS(
            'SELECT
                os.*,
                mp_sel_ord_st.*,
                mp_com_calc.*,
                osl.`name` as ostate_name,
                mp_sel_ord.`seller_shop`,
                mp_sel_info.`id_seller`,
                CONCAT(mp_sel_info.`seller_firstname`," ",mp_sel_info.`seller_lastname`) as seller_name,
                mp_sel_info.`business_email` FROM `'._DB_PREFIX_.'wk_mp_seller_order_detail` mp_com_calc
        	INNER JOIN  `'._DB_PREFIX_.'wk_mp_seller_order` mp_sel_ord ON (mp_com_calc.`id_seller_order` = `mp_sel_ord`.id_mp_order)
            INNER JOIN `'._DB_PREFIX_.'wk_mp_seller` mp_sel_info ON (mp_sel_ord.`seller_shop` = mp_sel_info.`shop_name_unique`)

            LEFT JOIN  `'._DB_PREFIX_.'wk_mp_seller_order_status` mp_sel_ord_st ON (mp_com_calc.`id_order` = mp_sel_ord_st.`id_order` AND mp_sel_info.`id_seller` = mp_sel_ord_st.`id_seller`)
            LEFT JOIN `'._DB_PREFIX_.'order_state` os ON os.`id_order_state` = mp_sel_ord_st.`current_state`
            LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int) $idLang.')
        	WHERE `mp_com_calc`.id_order = '.(int) $idOrder
        );
        if ($orderDetail) {
            return $orderDetail;
        }
        return false;
    }

    /**
     * Get all orders from the seller order detail table.
     *
     * @return array
     */
    public function getOrders()
    {
        $details = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_order_detail`');
        if ($details) {
            return $details;
        }
        return false;
    }

    /**
     * Get seller Product details by Using Order ID.
     *
     * @param int $idOrder Order ID
     * @param int $langID  Language ID
     *
     * @return array
     */
    public function getSellerProductByIdOrder($idOrder, $langID = false)
    {
        if (!$langID) {
            $langID = Configuration::get('PS_LANG_DEFAULT');
        }
        $details = Db::getInstance()->executeS(
            'SELECT
            c.*,
            msi.*,
            msil.*,
            ordd.*,
            mssp.`id_mp_product` as `mp_id_product`,
            mssp.`id_seller`,
            mssp.`id_ps_product`
            FROM `'._DB_PREFIX_.'order_detail` ordd
            LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller_product` mssp ON (mssp.`id_ps_product` = ordd.`product_id`)
            LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller` msi ON (msi.`id_seller` = mssp.`id_seller`)
            LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller_lang` msil ON (msi.`id_seller` = msil.`id_seller` AND msil.`id_lang` = '.(int) $langID.')
            LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = msi.`seller_customer_id`)
            WHERE ordd.`id_order`= '.(int) $idOrder
        );
        if ($details) {
            return $details;
        }
        return false;
    }

    /**
     * Get seller Order details.
     *
     * @param int $idOrder Order ID
     * @param int $langID  Language ID
     *
     * @return array
     */
    public function getSellerOrderDetail($idOrder, $idLang = false)
    {
        if (!$idLang) {
            $idLang = Configuration::get('PS_LANG_DEFAULT');
        }

        return Db::getInstance()->getRow('SELECT cntry.`name` AS `country`,
            ads.`postcode` AS `postcode`,
            ads.`city` AS `city`,
            ads.`phone` AS `phone`,
            ads.`phone_mobile` AS `mobile`,
            ordd.`id_order_detail` AS `id_order_detail`,
            ordd.`product_name` AS `ordered_product_name`,
            ordd.`product_price` AS total_price,
            ordd.`product_quantity` AS qty,
            ordd.`id_order` AS id_order,
            ord.`id_customer` AS buyer_id_customer,
            ord.`payment` AS payment_mode,
            ord.`current_state` AS current_state,
            ord.`reference` AS reference,
            cus.`firstname` AS name,
            cus.`lastname` AS lastname,
            CONCAT(cus.`firstname`," ",cus.`lastname`) as customer_name,
            ord.`date_add` AS `date`,
            ords.`name`AS order_status,
            ads.`address1` AS `address1`,
            ads.`address2` AS `address2`
            FROM  `'._DB_PREFIX_.'order_detail` ordd
            JOIN `'._DB_PREFIX_.'orders` ord ON (ord.`id_order` = ordd.`id_order`)
            JOIN `'._DB_PREFIX_.'order_state_lang` ords ON (ord.`current_state` = ords.`id_order_state`)
            LEFT JOIN `'._DB_PREFIX_.'address` ads ON (ads.`id_customer`= ord.`id_customer`)
            LEFT JOIN `'._DB_PREFIX_.'country_lang` cntry ON (cntry.`id_country` = ads.`id_country`)
            LEFT JOIN `'._DB_PREFIX_.'customer` cus ON (cus.`id_customer` = ord.`id_customer`)
            WHERE ordd.`id_order`='.(int) $idOrder.' AND ords.`id_lang` = '.(int) $idLang);
    }

    /**
     * Changed as per marketplace commission calc table for preventing order data even.
     *
     * If seller delete anyproduct which has ordered by any buyer.
     *
     * @param int $id_order
     * @param int $id_customer
     *
     * @return array
     */
    public function getSellerProductFromOrder($idOrder, $idCustomer)
    {
        return Db::getInstance()->executeS(
            'SELECT psod.*, msod.*, msp.`active` FROM `'._DB_PREFIX_.'order_detail` as psod
            JOIN `'._DB_PREFIX_.'wk_mp_seller_order_detail` as msod
            ON (psod.`product_id` = msod.`product_id` AND psod.`product_attribute_id` = msod.`product_attribute_id` AND psod.`id_customization` = msod.`id_customization` AND psod.`id_order` = msod.`id_order`)
            LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller_product` msp ON (msp.`id_ps_product` = psod.`product_id`)
            WHERE msod.`id_order`='.(int) $idOrder.' AND msod.`seller_customer_id`='.(int) $idCustomer
        );
    }

    /**
     * Get Total of orders by using from date and customer ID.
     *
     * @param date $dateAdd    From Date
     * @param int  $idCustomer Customer ID
     * @param int  $idLang     Language ID
     *
     * @return float
     */
    public function getOrderTotal($dateAdd, $idCustomer, $idLang)
    {
        $fDateAdd = $dateAdd.' 00:00:00';
        $lDateAdd = $dateAdd.' 23:59:59';
        $result = Db::getInstance()->executeS(
            'SELECT ordd.`total_price_tax_incl`, msod.`id_currency`
            FROM `'._DB_PREFIX_.'wk_mp_seller_product` msep
            JOIN `'._DB_PREFIX_.'order_detail` ordd ON (ordd.`product_id`= msep.`id_ps_product`)
            JOIN `'._DB_PREFIX_.'wk_mp_seller_order_detail` msod ON (ordd.`product_id` = msod.`product_id` AND ordd.`id_order` = msod.`id_order`)
            JOIN `'._DB_PREFIX_.'orders` ord ON (ordd.`id_order`= ord.`id_order`)
            JOIN `'._DB_PREFIX_.'wk_mp_seller` msi ON (msi.`id_seller` = msep.`id_seller`)
            JOIN `'._DB_PREFIX_.'customer` cus ON (msi.`seller_customer_id`= cus.`id_customer`)
            JOIN `'._DB_PREFIX_.'order_state_lang` ords ON (ord.`current_state`= ords.`id_order_state`)
            WHERE ords.id_lang='.(int) $idLang.' AND cus.`id_customer`= '.(int) $idCustomer."
            AND ord.`date_add` BETWEEN '".pSQL($fDateAdd)."' AND '".pSQL($lDateAdd)."'"
        );
        if (!$result) {
            return;
        }

        return $result;
    }

    /**
     * Get total number of orders done from a specific date and using customer ID.
     *
     * @param date $dateAdd    From Date
     * @param int  $idCustomer Customer ID
     * @param int  $idLang     Language ID
     *
     * @return int
     */
    public function getTotalOrderCount($dateAdd, $idCustomer, $idLang)
    {
        $fDateAdd = $dateAdd.' 00:00:00';
        $lDateAdd = $dateAdd.' 23:59:59';
        $result = Db::getInstance()->getValue(
            'SELECT IFNULL(count(ord.`id_order`), 0)
            FROM `'._DB_PREFIX_.'orders` ord
            JOIN `'._DB_PREFIX_.'order_state_lang` ords on (ord.`current_state`=ords.`id_order_state`)
            WHERE ord.`id_order` IN (
            SELECT ordd.`id_order` FROM `'._DB_PREFIX_.'order_detail` ordd
            JOIN `'._DB_PREFIX_.'wk_mp_seller_order_detail` msod ON (ordd.`product_id` = msod.`product_id` AND ordd.`id_order` = msod.`id_order`)
            JOIN `'._DB_PREFIX_.'wk_mp_seller_product` msp on (ordd.`product_id`= msp.`id_ps_product`)
            JOIN `'._DB_PREFIX_.'wk_mp_seller` msi on (msi.`id_seller` = msp.`id_seller`)
            JOIN `'._DB_PREFIX_.'customer` cus on (msi.`seller_customer_id`= cus.`id_customer`)
            WHERE cus.`id_customer` = '.(int) $idCustomer.')
            AND ord.`date_add` BETWEEN \''.pSQL($fDateAdd).'\' AND \''.pSQL($lDateAdd).'\' AND ords.id_lang = '.(int) $idLang
        );
        if (!$result) {
            return;
        }

        return $result;
    }

    public static function setVoucherDetails($idOrder, $idSeller, $idCurrency, $smarty = false)
    {
        $mpVoucher = WkMpOrderVoucher::getVoucherDetailByIdSeller($idOrder, $idSeller);
        if ($mpVoucher) {
            $voucherTotal = 0;
            foreach ($mpVoucher as &$voucher) {
                $voucherTotal = $voucherTotal + $voucher['voucher_value'];
                $voucher['voucher_value'] = Tools::displayPrice($voucher['voucher_value'], $idCurrency);
            }
            if ($smarty) {
                Context::getContext()->smarty->assign(array(
                    'total_voucher' => Tools::displayPrice($voucherTotal, $idCurrency),
                    'mp_voucher_info' => $mpVoucher,
                ));
            } else {
                $voucherInfo = array(
                    'total_voucher' => Tools::displayPrice($voucherTotal, $idCurrency),
                    'mp_voucher_info' => $mpVoucher,
                );
                return $voucherInfo;
            }
        }
        return false;
    }

    public function getTaxRateByIdOrderDetail($idOrderDetail)
    {
        $idTax = Db::getInstance()->getValue('SELECT `id_tax` FROM '._DB_PREFIX_.'order_detail_tax WHERE `id_order_detail` = '.(int)$idOrderDetail);
        if ($idTax) {
            $tax = new Tax($idTax);
            if (Validate::isLoadedObject($tax)) {
                return $tax->rate;
            }
        }

        return false;
    }

    public static function getSellerFromOrderProduct($idOrder, $idProduct)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'order_detail` as psod
            JOIN `'._DB_PREFIX_.'wk_mp_seller_order_detail` as msod
            ON (psod.`product_id` = msod.`product_id` AND psod.`product_attribute_id` = msod.`product_attribute_id` AND psod.`id_customization` = msod.`id_customization` AND psod.`id_order` = msod.`id_order`)
            LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller_order` mso ON (mso.`seller_customer_id` = msod.`seller_customer_id`)
            WHERE msod.`id_order`='.(int) $idOrder.' AND msod.`product_id`='.(int) $idProduct
        );
    }

    public function getAllSellerProductFromOrder($idOrder)
    {
        return Db::getInstance()->executeS(
            'SELECT psod.*, msod.*, msp.`active` FROM `'._DB_PREFIX_.'order_detail` as psod
            JOIN `'._DB_PREFIX_.'wk_mp_seller_order_detail` as msod
            ON (psod.`product_id` = msod.`product_id`
            AND psod.`product_attribute_id` = msod.`product_attribute_id` AND psod.`id_order` = msod.`id_order`)
            LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller_product` msp ON (msp.`id_ps_product` = psod.`product_id`)
            WHERE msod.`id_order`='.(int) $idOrder
        );
    }
}
