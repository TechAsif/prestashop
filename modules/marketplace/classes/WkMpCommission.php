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

class WkMpCommission extends ObjectModel
{
    const WK_COMMISSION_PERCENTAGE = 'percentage';
    const WK_COMMISSION_FIXED = 'fixed';
    const WK_COMMISSION_BOTH_TYPE = 'commission_both';

    public $id_seller;
    public $commision_type;
    public $commision_rate;
    public $commision_amt;
    public $commision_tax_amt;
    public $seller_customer_id;

    public static $definition = array(
        'table' => 'wk_mp_commision',
        'primary' => 'id_wk_mp_commision',
        'fields' => array(
            'id_seller' => array('type' => self::TYPE_INT, 'required' => true),
            'commision_type' => array('type' => self::TYPE_STRING, 'required' => true),
            'commision_rate' => array('type' => self::TYPE_FLOAT),
            'commision_amt' => array('type' => self::TYPE_FLOAT),
            'commision_tax_amt' => array('type' => self::TYPE_FLOAT),
            'seller_customer_id' => array('type' => self::TYPE_INT, 'required' => true),
        ),
    );

    /**
     * Get Commission Rate by using Customer ID, if customer id is false then current customer id will be used
     *
     * @return float
     */
    public function getCommissionRate($idCustomer = false)
    {
        if (!$idCustomer) { // customer id is false we will take current customer's id
            $idCustomer = Context::getContext()->customer->id;
        }

        return Db::getInstance()->getValue('SELECT `commision_rate` FROM `'._DB_PREFIX_.'wk_mp_commision`
            WHERE `seller_customer_id` = '.(int) $idCustomer);
    }

    /**
     * Get all those sellers who has not set commission yet
     *
     * @return array/boolean
     */
    public function getSellerWithoutCommission()
    {
        $mpSellerInfo = Db::getInstance()->executeS(
            'SELECT
                c.`id_customer` as `seller_customer_id`,
                c.`email`,
                mpsi.`business_email` FROM `'._DB_PREFIX_.'customer` c
                JOIN `'._DB_PREFIX_.'wk_mp_seller` mpsi ON (mpsi.seller_customer_id = c.id_customer)
                WHERE mpsi.`active` = 1 AND mpsi.`seller_customer_id` NOT IN
                (SELECT `seller_customer_id` FROM `'._DB_PREFIX_.'wk_mp_commision`)'
        );

        if (empty($mpSellerInfo)) {
            return false;
        }

        return $mpSellerInfo;
    }

    /**
     * get mp commission according to seller or global commission - deprecated function
     *
     * @param int $sellerCustomerId seller customer id
     *
     * @return bool
     */
    public static function getCommissionBySellerCustomerId($sellerCustomerId)
    {
        $objMpCommission = new WkMpCommission();
        if ($commission = $objMpCommission->getCommissionRate($sellerCustomerId)) {
            return $commission;
        } else {
            return Configuration::get('WK_MP_GLOBAL_COMMISSION');
        }

        return false;
    }

    public function getSellerCommissionDetails($idSeller)
    {
        $sellerCommission = Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_commision` WHERE `id_seller` = '.(int) $idSeller
        );
        if ($sellerCommission) {
            return $sellerCommission;
        }
        return false;
    }

    public static function mpCommissionType()
    {
        $objMp = new Marketplace();
        return array(
            array(
                'id' => WkMpCommission::WK_COMMISSION_PERCENTAGE,
                'name' => $objMp->l('Percentage', 'WkMpCommission')
            ),
            array(
                'id' => WkMpCommission::WK_COMMISSION_FIXED,
                'name' => $objMp->l('Fixed Amount', 'WkMpCommission')
            ),
            array(
                'id' => WkMpCommission::WK_COMMISSION_BOTH_TYPE,
                'name' => $objMp->l('Both (Percentage and Fixed Amount)', 'WkMpCommission')
            ),
        );
    }

    public static function getCommissionTypeName($commissionType)
    {
        $objMp = new Marketplace();
        if ($commissionType == WkMpCommission::WK_COMMISSION_PERCENTAGE) {
            return $objMp->l('Percentage', 'WkMpCommission');
        } elseif ($commissionType == WkMpCommission::WK_COMMISSION_FIXED) {
            return $objMp->l('Fixed Amount', 'WkMpCommission');
        } elseif ($commissionType == WkMpCommission::WK_COMMISSION_BOTH_TYPE) {
            return $objMp->l('Both (Percentage and Fixed Amount)', 'WkMpCommission');
        }
    }

    public function getFinalCommissionForSeller($idSeller)
    {
        $commissionRate = 0;
        $commissionFixedAmount = 0;
        $commissionFixedAmountOnTax = 0;

        $sellerCommission = $this->getSellerCommissionDetails($idSeller);
        if (empty($sellerCommission)) {
            //apply global commission, if commission by particular seller not defined
            if (Configuration::get('WK_MP_GLOBAL_COMMISSION_TYPE')) {
                $commissionType = Configuration::get('WK_MP_GLOBAL_COMMISSION_TYPE');
            } else {
                $commissionType = WkMpCommission::WK_COMMISSION_PERCENTAGE; //default commission type
            }

            if ($commissionType == WkMpCommission::WK_COMMISSION_PERCENTAGE) {
                if (Configuration::get('WK_MP_GLOBAL_COMMISSION')) {
                    $commissionRate = Configuration::get('WK_MP_GLOBAL_COMMISSION');
                }
            } else {
                if (($commissionType == WkMpCommission::WK_COMMISSION_FIXED)
                || ($commissionType == WkMpCommission::WK_COMMISSION_BOTH_TYPE)) {
                    //If both type
                    if ($commissionType == WkMpCommission::WK_COMMISSION_BOTH_TYPE) {
                        if (Configuration::get('WK_MP_GLOBAL_COMMISSION')) {
                            $commissionRate = Configuration::get('WK_MP_GLOBAL_COMMISSION');
                        }
                    }

                    if (Configuration::get('WK_MP_GLOBAL_COMMISSION_AMOUNT')) {
                        $commissionFixedAmount = Configuration::get('WK_MP_GLOBAL_COMMISSION_AMOUNT');
                    }

                    if ((Configuration::get('WK_MP_PRODUCT_TAX_DISTRIBUTION') == 'distribute_both')
                    && Configuration::get('WK_MP_GLOBAL_TAX_FIXED_COMMISSION')
                    ) {
                        $commissionFixedAmountOnTax = Configuration::get('WK_MP_GLOBAL_TAX_FIXED_COMMISSION');
                    }
                }
            }
        } else {
            $commissionType = $sellerCommission['commision_type'];
            if ($commissionType == WkMpCommission::WK_COMMISSION_PERCENTAGE) {
                $commissionRate = $sellerCommission['commision_rate'];
            } else {
                if (($commissionType == WkMpCommission::WK_COMMISSION_FIXED)
                || ($commissionType == WkMpCommission::WK_COMMISSION_BOTH_TYPE)) {
                    //If both type
                    if ($commissionType == WkMpCommission::WK_COMMISSION_BOTH_TYPE) {
                        $commissionRate = $sellerCommission['commision_rate'];
                    }

                    $commissionFixedAmount = $sellerCommission['commision_amt'];

                    if ((Configuration::get('WK_MP_PRODUCT_TAX_DISTRIBUTION') == 'distribute_both')
                    && $sellerCommission['commision_tax_amt']
                    ) {
                        $commissionFixedAmountOnTax = $sellerCommission['commision_tax_amt'];
                    }
                }
            }
        }

        return array(
            'commission_type' => $commissionType,
            'commission_rate' => $commissionRate,
            'commission_fixed_amt' => $commissionFixedAmount,
            'commission_fixed_tax_amt' => $commissionFixedAmountOnTax,
        );
    }

    public function finalCommissionSummaryForSeller($idSeller, $idCurrency = false)
    {
        if (!$idCurrency) {
            $idCurrency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        }

        $sellerFinalCommission = $this->getFinalCommissionForSeller($idSeller);
        if ($sellerFinalCommission) {
            return WkMpCommission::finalCommissionSummary(
                $sellerFinalCommission['commission_type'],
                $sellerFinalCommission['commission_rate'],
                $sellerFinalCommission['commission_fixed_amt'],
                $sellerFinalCommission['commission_fixed_tax_amt'],
                (int) $idCurrency
            );
        }
        return false;
    }

    public static function finalCommissionSummary(
        $commissionType,
        $commissionRate,
        $commissionFixedAmount,
        $commissionFixedAmountOnTax,
        $idCurrency
    ) {
        if ($commissionType == self::WK_COMMISSION_FIXED) {
            $commissionData = Tools::displayPrice($commissionFixedAmount, $idCurrency);
        } elseif ($commissionType == self::WK_COMMISSION_BOTH_TYPE) {
            $commissionData = Tools::ps_round($commissionRate, 2).'%'.' + '
            .Tools::displayPrice($commissionFixedAmount, $idCurrency);
        } else {
            $commissionData = Tools::ps_round($commissionRate, 2).'%';
        }

        if ($commissionFixedAmountOnTax > 0) { //If tax commission fixed amt exist
            $objMp = new Marketplace();
            return $commissionData.' + '.Tools::displayPrice(
                $commissionFixedAmountOnTax,
                $idCurrency
            ).' '.$objMp->l('(on tax)', 'WkMpCommission');
        } else {
            return $commissionData;
        }
    }

    public static function updateSellerIdInAllCommission()
    {
        $allCommissions = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'wk_mp_commision`');
        if ($allCommissions) {
            foreach ($allCommissions as $commissionData) {
                $idSeller = Db::getInstance()->getValue(
                    'SELECT `id_seller` FROM `'._DB_PREFIX_.'wk_mp_seller`
                    WHERE `seller_customer_id` = '.(int) $commissionData['seller_customer_id']
                );
                if ($idSeller) {
                    Db::getInstance()->update(
                        'wk_mp_commision',
                        array('id_seller' => (int) $idSeller),
                        'id_wk_mp_commision = '.(int) $commissionData['id_wk_mp_commision']
                    );
                }
            }
        }
        return true;
    }
}
