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

class WkMpSellerPaymentMode extends ObjectModel
{
    public $payment_mode;

    public static $definition = array(
        'table' => 'wk_mp_payment_mode',
        'primary' => 'id_mp_payment',
        'fields' => array(
            'payment_mode' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
        ),
    );

    public function delete()
    {
        $deleteMpPayment = Db::getInstance()->execute(
            'DELETE FROM `'._DB_PREFIX_.'wk_mp_customer_payment_detail`
            WHERE `payment_mode_id` = '.(int) $this->id
        );

        if (!$deleteMpPayment || !parent::delete()) {
            return false;
        }

        return true;
    }

    /**
     * Get payment modes created by admin for seller
     *
     * @return array
     */
    public static function getPaymentMode()
    {
        return Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'wk_mp_payment_mode`');
    }
}
