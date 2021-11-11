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

class WkMpCustomerPayment extends ObjectModel
{
    public $seller_customer_id;
    public $payment_mode_id;
    public $payment_detail;

    public static $definition = array(
        'table' => 'wk_mp_customer_payment_detail',
        'primary' => 'id_customer_payment',
        'fields' => array(
            'seller_customer_id' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'payment_mode_id' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'payment_detail' => array('type' => self::TYPE_STRING),
        ),
    );

    /**
     * Get Payment Details by using Customer ID
     *
     * @param  int $id_customer Customer ID
     * @return array
     */
    public function getPaymentDetailByIdCustomer($idCustomer)
    {
        return Db::getInstance()->getRow(
            'SELECT mcpd.*, mpm.`payment_mode`
            FROM `'._DB_PREFIX_.'wk_mp_customer_payment_detail` mcpd
            LEFT JOIN  `'._DB_PREFIX_.'wk_mp_payment_mode` mpm ON (mcpd.`payment_mode_id`= mpm.`id_mp_payment`)
            WHERE mcpd.`seller_customer_id` = '.(int) $idCustomer
        );
    }

    /**
     * Get Payment Detail by using primary ID, we can also create object of this class by using ID instead
     *
     * @param  int $id Primary ID
     * @return array
     */
    public static function getPaymentDetailById($id)
    {
        return Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'wk_mp_customer_payment_detail` WHERE `id_customer_payment` = '.(int) $id);
    }
}
