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

class WkMpProductAttributeImage extends ObjectModel
{
    public $id_mp_product_attribute;
    public $id_image; //If product is active then it will be Ps Image ID (ps_id_image) otherwise it will be mp_id_image

    public static $definition = array(
        'table' => 'wk_mp_product_attribute_image',
        'primary' => array('id_mp_product_attribute','id_image'),
        'fields' => array(
            'id_mp_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_image' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
        ),
    );

    /**
     * Get images of product attribute using seller product attribute id.
     *
     * @param int $idMpProductAttribute Seller product attribute Id
     *
     * @return array/bool
     */
    public static function getAttributeImages($idMpProductAttribute)
    {
        $attributeImages = Db::getInstance()->executeS(
            'SELECT `id_image` FROM `'._DB_PREFIX_.'wk_mp_product_attribute_image`
            WHERE `id_mp_product_attribute`  = '.(int) $idMpProductAttribute
        );

        if ($attributeImages) {
            return $attributeImages;
        }

        return false;
    }

    /**
     * Get images of product attribute using prestashop product attribute id.
     *
     * @param int $idPsProductAttribute Prestashop Product attribute id
     *
     * @return array/bool
     */
    public static function getPsAttributeImages($idPsProductAttribute)
    {
        return Db::getInstance()->executeS(
            'SELECT `id_image` FROM `'._DB_PREFIX_.'product_attribute_image`
            WHERE `id_product_attribute`  = '.(int) $idPsProductAttribute
        );
    }

    /**
     * Assign Images to seller product attributes.
     *
     * @param int $idImages             Image ID
     * @param int $idMpProductAttribute Seller Product Attribute ID
     */
    public static function setMpImages($idImages, $idMpProductAttribute)
    {
        if (Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'wk_mp_product_attribute_image`
            WHERE `id_mp_product_attribute` = '.(int) $idMpProductAttribute) === false) {
            return false;
        }

        if ($idImages) {
            $imageGroupData = array();
            foreach ($idImages as $idImage) {
                $imageGroupData[] = '('.(int) $idMpProductAttribute.', '.(int) $idImage.')';
            }

            Db::getInstance()->execute(
                'INSERT INTO `'._DB_PREFIX_.'wk_mp_product_attribute_image` (`id_mp_product_attribute`, `id_image`)
                VALUES '.pSQL(implode(',', $imageGroupData))
            );
        }

        return true;
    }

    /**
     * Set all id_image with mp id image
     *
     * @param int $idMpProduct seller product id
     */
    public static function setCombinationImagesAsMp($idMpProduct)
    {
        $getAllAttributeIds = WkMpProductAttribute::getProductAttributesIds($idMpProduct);
        if ($getAllAttributeIds) {
            foreach ($getAllAttributeIds as $mpProductAttribute) {
                $idMpProductAttribute = $mpProductAttribute['id_mp_product_attribute'];
                $idImages = self::getAttributeImages($idMpProductAttribute);
                if ($idImages) {
                    foreach ($idImages as $images) {
                        $idPsImage = $images['id_image'];
                        $objProductImage = new WkMPSellerProductImage();
                        $productImages = $objProductImage->getProductImageByPsIdImage($idPsImage);
                        if ($productImages) {
                            $idMpImage = $productImages['id_mp_product_image'];
                            Db::getInstance()->execute(
                                'UPDATE `'._DB_PREFIX_.'wk_mp_product_attribute_image`
                                SET `id_image` = '.(int) $idMpImage.'
                                WHERE `id_image` = '.(int) $idPsImage.'
                                AND `id_mp_product_attribute` = '.(int) $idMpProductAttribute
                            );
                        }
                    }
                }
            }
        }
    }
}
