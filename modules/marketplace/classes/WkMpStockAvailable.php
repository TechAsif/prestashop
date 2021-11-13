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

class WkMpStockAvailable extends ObjectModel
{
    public $id_mp_stock_available;
    public $id_mp_product;
    public $id_mp_product_attribute;
    public $id_shop;
    public $id_shop_group;
    public $quantity;
    public $depends_on_stock;
    public $out_of_stock;

    // We can use these table for multishop.
    // Presently we manage combination qty throught `wk_mp_product_attribute` table `mp_quantity`
    // column (Function name - getMpProductQty())

    public static $definition = array(
        'table' => 'wk_mp_stock_available',
        'primary' => 'id_mp_stock_available',
        'fields' => array(
            'id_mp_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_mp_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_shop_group' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'quantity' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'shop' => true, 'required' => true),
            'depends_on_stock' => array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedId'),
            'out_of_stock' => array('type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedId', 'required' => true),
        ),
    );

    /**
    * Set seller product combination qty in stock table
    *
    * @param int $mpIdProduct seller product id
    * @param int $idMpProductAttribute seller product attribute id
    * @param int $mpQuantity seller product quantity
    * @param int $idShop ps shop id
    * @return bool
    */
    public static function setMpQuantity($idMpProduct, $idMpProductAttribute, $mpQuantity, $idShop = null)
    {
        if (!Validate::isUnsignedId($idMpProduct)) {
            return false;
        }

        // if there is no $id_shop, gets the context one
        if ($idShop === null && Shop::getContext() != Shop::CONTEXT_GROUP) {
            $idShop = (int) Context::getContext()->shop->id;
        }

        $dependsOnStock = self::dependsOnStock($idMpProduct);

        //Try to set available quantity if product does not depend on physical stock
        if (!$dependsOnStock) {
            $idMpStockAvailable = (int) self::getStockAvailableIdByProductId($idMpProduct, $idMpProductAttribute, $idShop);
            if ($idMpStockAvailable) {
                $stockAvailable = new self($idMpStockAvailable);
                $stockAvailable->quantity = (int) $mpQuantity;
                $stockAvailable->update();
            } else {
                $outOfStock = self::outOfStock($idMpProduct, $idShop);

                $stockAvailable = new self();
                $stockAvailable->out_of_stock = (int) $outOfStock;
                $stockAvailable->id_mp_product = (int) $idMpProduct;
                $stockAvailable->id_mp_product_attribute = (int) $idMpProductAttribute;
                $stockAvailable->quantity = (int) $mpQuantity;

                if ($idShop === null) {
                    $shopGroup = Shop::getContextShopGroup();
                } else {
                    $shopGroup = new ShopGroup((int) Shop::getGroupFromShop((int) $idShop));
                }

                // if quantities are shared between shops of the group
                if ($shopGroup->share_stock) {
                    $stockAvailable->id_shop = 0;
                    $stockAvailable->id_shop_group = (int) $shopGroup->id;
                } else {
                    $stockAvailable->id_shop = (int) $idShop;
                    $stockAvailable->id_shop_group = 0;
                }

                $stockAvailable->add();
            }
        }
    }

    public static function dependsOnStock($idMpProduct, $idShop = null)
    {
        if (!Validate::isUnsignedId($idMpProduct)) {
            return false;
        }

        $query = new DbQuery();
        $query->select('depends_on_stock');
        $query->from('wk_mp_stock_available');
        $query->where('id_mp_product = '.(int) $idMpProduct);
        $query->where('id_mp_product_attribute = 0');

        $query = StockAvailable::addSqlShopRestriction($query, $idShop);

        return (bool) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    public static function getStockAvailableIdByProductId($idMpProduct, $idMpProductAttribute = null, $idShop = null)
    {
        if (!Validate::isUnsignedId($idMpProduct)) {
            return false;
        }

        $query = new DbQuery();
        $query->select('id_mp_stock_available');
        $query->from('wk_mp_stock_available');
        $query->where('id_mp_product = '.(int) $idMpProduct);

        if ($idMpProductAttribute !== null) {
            $query->where('id_mp_product_attribute = '.(int) $idMpProductAttribute);
        }

        $query = StockAvailable::addSqlShopRestriction($query, $idShop);

        return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    public static function outOfStock($idMpProduct, $idShop = null)
    {
        if (!Validate::isUnsignedId($idMpProduct)) {
            return false;
        }

        $query = new DbQuery();
        $query->select('out_of_stock');
        $query->from('wk_mp_stock_available');
        $query->where('id_mp_product = '.(int) $idMpProduct);
        $query->where('id_mp_product_attribute = 0');

        $query = StockAvailable::addSqlShopRestriction($query, $idShop);

        return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }
}
