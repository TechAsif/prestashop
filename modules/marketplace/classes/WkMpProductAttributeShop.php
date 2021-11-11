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

class WkMpProductAttributeShop extends ObjectModel
{
    public $id_mp_product;
    public $id_mp_product_attribute;
    public $id_shop;
    public $mp_price;
    public $mp_wholesale_price;
    public $mp_unit_price_impact;
    public $mp_weight;
    public $mp_default_on;
    public $mp_minimal_quantity;
    public $mp_available_date;

    public static $definition = array(
        'table' => 'wk_mp_product_attribute_shop',
        'primary' => array('id_mp_product_attribute','id_shop'),
        'fields' => array(
            'id_mp_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'mp_price' => array('type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isNegativePrice', 'size' => 20),
            'mp_wholesale_price' =>    array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'size' => 27),
            'mp_unit_price_impact' =>    array('type' => self::TYPE_FLOAT, 'validate' => 'isNegativePrice', 'size' => 20),
            'mp_weight' => array('type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isFloat'),
            'mp_minimal_quantity' => array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedId', 'required' => true),
            'mp_default_on' => array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isBool'),
            'mp_available_date' => array('type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDateFormat'),
        ),
    );

    /**
     * Adding product attributes into marketplace.
     *
     * @param int   $idMpProduct              Seller Product ID
     * @param int   $idMpProductAttribute     Seller Product ID Attribute
     * @param float $mpPrice                  Impact Price
     * @param float $mpWholesalePrice         Wholesale price
     * @param float $mpUnitPriceImpact        unit price impact
     * @param float $mpWeight          Attribute weight
     * @param int   $mpMinimalQuantity Quanity
     * @param date  $mpAvailableDate   Date to be available
     * @param bool  $combiDefault      Default combination for the product
     * @param type $lowStockThreshold - Combination low stock level value
     * @param type $lowStockAlert - Combination low stock level checkbox
     *
     * @return bool
     */
    public static function insertProductAttributeShopData(
        $idMpProduct,
        $idMpProductAttribute,
        $mpPrice,
        $mpWholesalePrice,
        $mpUnitPriceImpact,
        $mpWeight,
        $mpMinimalQuantity,
        $mpAvailableDate,
        $combiDefault = false,
        $lowStockThreshold = false,
        $lowStockAlert = false
    ) {
        if ($combiDefault) {
            $mpDefaultOn = 1;
        } else {
            $mpDefaultOn = 0;
        }

        if (!$lowStockThreshold) {
            $lowStockThreshold = 0;
        }
        if (!$lowStockAlert) {
            $lowStockAlert = 0;
        }

        $idShop = 1; //Default hardcoded

        return Db::getInstance()->insert('wk_mp_product_attribute_shop', array(
                                        'id_mp_product_attribute' => (int) $idMpProductAttribute,
                                        'id_shop' => (int) $idShop,
                                        'id_mp_product' => (int) $idMpProduct,
                                        'mp_price' => (float) $mpPrice,
                                        'mp_wholesale_price' => (float) $mpWholesalePrice,
                                        'mp_unit_price_impact' => (float) $mpUnitPriceImpact,
                                        'mp_weight' => (float) $mpWeight,
                                        'mp_default_on' => (int) $mpDefaultOn,
                                        'mp_minimal_quantity' => (int) $mpMinimalQuantity,
                                        'mp_available_date' => pSQL($mpAvailableDate),
                                        'low_stock_threshold' => pSQL($lowStockThreshold),
                                        'low_stock_alert' => pSQL($lowStockAlert),
                                    ));
    }

    /**
     * Update Product Attribute Values.
     *
     * @param int   $idMpProductAttribute     Seller Product ID Attribute
     * @param float $mpPrice                  Impact Price
     * @param float $mpWholesalePrice         Wholesale price
     * @param float $mpUnitPriceImpact        unit price impact
     * @param float $mpWeight          Attribute weight
     * @param int   $mpMinimalQuantity Quanity
     * @param date  $mpAvailableDate   Date to be available
     * @param bool  $combiDefault      Default combination for the product
     * @param type $lowStockThreshold - Combination low stock level value
     * @param type $lowStockAlert - Combination low stock level checkbox
     *
     * @return bool
     */
    public static function updateProductAttributeShopData(
        $idMpProductAttribute,
        $mpPrice,
        $mpWholesalePrice,
        $mpUnitPriceImpact,
        $mpWeight,
        $mpMinimalQuantity,
        $mpAvailableDate,
        $combiDefault = false,
        $lowStockThreshold = false,
        $lowStockAlert = false
    ) {
        if (!$lowStockThreshold) {
            $lowStockThreshold = 0;
        }
        if (!$lowStockAlert) {
            $lowStockAlert = 0;
        }

        $sql = 'UPDATE '._DB_PREFIX_.'wk_mp_product_attribute_shop
                SET `mp_price` = "'.(float) $mpPrice.'",
                    `mp_wholesale_price` = "'.(float) $mpWholesalePrice.'",
                    `mp_unit_price_impact` = "'.(float) $mpUnitPriceImpact.'",
                    `mp_weight` = "'.(float) $mpWeight.'",
                    `mp_minimal_quantity` = "'.(int) $mpMinimalQuantity.'",
                    `mp_available_date` = "'.pSQL($mpAvailableDate).'",
                    `low_stock_threshold` = "'.(int) $lowStockThreshold.'",
                    `low_stock_alert` = "'.(int) $lowStockAlert.'" ';

        if ($combiDefault) {
            $sql .= '", `mp_default_on` = "'.(int) $combiDefault.'"';
        }

        $sql .= ' WHERE `id_mp_product_attribute` = '.(int) $idMpProductAttribute;

        return Db::getInstance()->execute($sql);
    }

    /**
     * Change value for product attribute.
     *
     * @param int $idMpProductAttribute Product ID Attribute
     * @param int $mpDefaultOn          Set default attribute combination for product in mp
     *
     * @return bool
     */
    public static function changeAttributeShopDefaultValue($idMpProductAttribute, $mpDefaultOn = 0)
    {
        return Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'wk_mp_product_attribute_shop SET `mp_default_on` = '.(int) $mpDefaultOn.' WHERE id_mp_product_attribute = '.(int) $idMpProductAttribute);
    }
}
