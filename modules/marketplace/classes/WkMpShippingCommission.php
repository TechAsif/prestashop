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

class WkMpShippingCommission extends ObjectModel
{
    public $id_seller;
    public $commission_rate;

    public static $definition = array(
        'table' => 'wk_mp_shipping_commission',
        'primary' => 'id_wk_mp_shipping_commission',
        'fields' => array(
            'id_seller' => array('type' => self::TYPE_INT, 'required' => true),
            'commission_rate' => array('type' => self::TYPE_FLOAT),
        ),
    );

    /**
     * Get all those sellers who has no shipping commission yet
     *
     * @return array/boolean
     */
    public function getSellerWithoutShippingCommission()
    {
        $mpSellerInfo = Db::getInstance()->executeS(
            'SELECT `id_seller`, `business_email` FROM `'._DB_PREFIX_.'wk_mp_seller`
            WHERE `active` = 1
            AND `id_seller` NOT IN (SELECT `id_seller` FROM `'._DB_PREFIX_.'wk_mp_shipping_commission`)'
        );

        if (empty($mpSellerInfo)) {
            return false;
        }

        return $mpSellerInfo;
    }

    /**
     * Get Commission Rate by using Seller customer ID, if customer id is false then current customer id will be used
     *
     * @return float
     */
    public function getCommissionRateBySellerCustomerId($sellerCustomerId = false)
    {
        if (!$sellerCustomerId) { // customer id is false we will take current customer's id
            $sellerCustomerId = Context::getContext()->customer->id;
        }

        if ($sellerInfo = WkMpSeller::getSellerDetailByCustomerId($sellerCustomerId)) {
            return Db::getInstance()->getValue(
                'SELECT `commission_rate` FROM `'._DB_PREFIX_.'wk_mp_shipping_commission`
                WHERE `id_seller` = '.(int) $sellerInfo['id_seller']
            );
        }

        return false;
    }
}
