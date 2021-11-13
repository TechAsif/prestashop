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

class WkMpProductAttributeCombination extends ObjectModel
{
    public $id_ps_attribute;
    public $id_mp_product_attribute;

    public static $definition = array(
        'table' => 'wk_mp_product_attribute_combination',
        'primary' => array('id_ps_attribute','id_mp_product_attribute'),
        'fields' => array(
            'id_ps_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_mp_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
        ),
    );

    /**
     * Check if product with selected attribute is exist or not. If exist then don't allow to create same combination.
     *
     * @param int $idMpProduct          seller product id
     * @param int $productAttributeList combination attribute list
     * @param int $idMpProductAttribute product attribute id (combination id)
     *
     * @return int
     */
    public static function isProductCombinationExists($idMpProduct, $productAttributeList, $idMpProductAttribute = false)
    {
        $mpProductAttributeData = DB::getInstance()->executeS('SELECT `id_mp_product_attribute` FROM `'._DB_PREFIX_.'wk_mp_product_attribute` WHERE `id_mp_product` = '.(int) $idMpProduct);

        if ($mpProductAttributeData) {
            foreach ($mpProductAttributeData as $mpProductAttribute) {
                $existIdMpProductAttribute = $mpProductAttribute['id_mp_product_attribute'];

                $sql = 'SELECT `id_ps_attribute` FROM `'._DB_PREFIX_.'wk_mp_product_attribute_combination` WHERE `id_mp_product_attribute` = '.(int) $existIdMpProductAttribute.'';

                if ($idMpProductAttribute) {
                    $sql .= ' AND `id_mp_product_attribute` != '.(int) $idMpProductAttribute;
                }

                $psAttributeIds = DB::getInstance()->executeS($sql);

                $existProductAttributeList = array();
                if ($psAttributeIds) {
                    foreach ($psAttributeIds as $attributeValue) {
                        $existProductAttributeList[] = $attributeValue['id_ps_attribute'];
                    }
                }

                sort($existProductAttributeList);
                sort($productAttributeList);

                if ($existProductAttributeList == $productAttributeList) {
                    return $existIdMpProductAttribute;
                }
            }
        }

        return false;
    }

    /**
     * Delete product combination attribute by product attribute id.
     *
     * @param int $idMpProductAttribute product attribute id (combination id)
     *
     * @return int
     */
    public static function deleteProductAttributeCombination($idMpProductAttribute)
    {
        return Db::getInstance()->delete('wk_mp_product_attribute_combination', '`id_mp_product_attribute` = '.(int) $idMpProductAttribute);
    }

    /**
     * Make a collection of a single combination with attributes.
     *
     * @param int $attributeList array of combination attributes
     *
     * @return int
     */
    public static function insertDataIntoMpproductattributecombination($attributeList)
    {
        return Db::getInstance()->insert('wk_mp_product_attribute_combination', $attributeList);
    }

    public static function deleteProductAttrCombByPsAttrId($idPsProductAttribute)
    {
        return Db::getInstance()->delete('product_attribute_combination', '`id_product_attribute` = '.(int) $idPsProductAttribute);
    }

    /**
     * Get attribute name with group name and value for display in update combination selected combination box.
     *
     * @param int $idMpProductAttribute product attribute id
     *
     * @return array
     */
    public static function getPsAttributesSet($idMpProductAttribute)
    {
        return DB::getInstance()->executeS(
            'SELECT DISTINCT `id_ps_attribute` FROM `'._DB_PREFIX_.'wk_mp_product_attribute_combination` pac
            LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_ps_attribute`
            LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
            WHERE `id_mp_product_attribute` = '.(int) $idMpProductAttribute.
            ' GROUP BY ag.`id_attribute_group`'
        );
    }

    /**
     * Get Prestashop attribute id using marketplace product id.
     *
     * @param int $idMpProductAttribute Marketplace product attribute id
     *
     * @return array/bool
     */
    public static function getPsAttributeIdForMpProduct($idMpProductAttribute)
    {
        return Db::getInstance()->executeS('SELECT `id_ps_attribute` FROM `'._DB_PREFIX_.'wk_mp_product_attribute_combination` WHERE `id_mp_product_attribute` = '.(int) $idMpProductAttribute);
    }
}
