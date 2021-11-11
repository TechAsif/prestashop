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

class WkMpAttributeImpact extends ObjectModel
{
    public $id_mp_attribute_impact;
    public $id_mp_product;
    public $id_attribute;
    public $mp_weight;
    public $mp_price;

    public static $definition = array(
        'table' => 'wk_mp_attribute_impact',
        'primary' => 'id_mp_attribute_impact',
        'fields' => array(
            'id_mp_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'mp_weight' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'mp_price' => array('type' => self::TYPE_FLOAT, 'validate' => 'isNegativePrice', 'size' => 20),
        ),
    );

    public function entryExist($mpIdProductCheck, $idAttributeCheck)
    {
        $existance = null;
        $existance = Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_attribute_impact`
            WHERE `id_mp_product` = '.(int) $mpIdProductCheck.' AND `id_attribute` = '.(int) $idAttributeCheck
        );
        if ($existance == null) {
            return true;
        }

        return false;
    }

    public function insertDataInToAttributeimpact($attributes, $isPsProductId = 0)
    {
        if ($isPsProductId) {
            $sql = 'INSERT INTO `'._DB_PREFIX_.'attribute_impact` (`id_product`, `id_attribute`, `price`, `weight`) VALUES '.pSQL(implode(',', $attributes)).' ON DUPLICATE KEY UPDATE `price` = VALUES(price), `weight` = VALUES(weight)';
        } else {
            $sql = 'INSERT INTO `'._DB_PREFIX_.'wk_mp_attribute_impact` (`id_mp_product`, `id_attribute`, `mp_price`, `mp_weight`) VALUES '.pSQL(implode(',', $attributes)).' ON DUPLICATE KEY UPDATE `mp_price` = VALUES(mp_price), `mp_weight` = VALUES(mp_weight)';
        }

        $result = Db::getInstance()->execute($sql);
        if ($result) {
            return $result;
        }

        return false;
    }

    public static function getMpAttributesGroups($idLang, $psIdShop, $mpIdProduct)
    {
        $sql = 'SELECT
        ag.`id_attribute_group`,
        ag.`is_color_group`,
        agl.`name` AS group_name,
        agl.`public_name` AS public_group_name,
        a.`id_attribute`,
        al.`name` AS attribute_name,
        a.`color` AS attribute_color,
        mpas.`id_mp_product_attribute`,
        IFNULL(stock.`quantity`, 0) as quantity,
        mpas.`mp_price`,
        mpas.`mp_weight`,
        mpas.`mp_default_on`,
        pa.`mp_reference` as reference,
        mpas.`mp_minimal_quantity` as minimal_quantity,
        mpas.`mp_minimal_quantity` as minimal_quantity,
        mpas.`mp_available_date` as available_date,
        ag.`group_type`
        FROM `'._DB_PREFIX_.'wk_mp_product_attribute` pa
        INNER JOIN `'._DB_PREFIX_.'wk_mp_product_attribute_shop` mpas ON (mpas.`id_mp_product_attribute` = pa.`id_mp_product_attribute` AND mpas.`id_shop` = '.(int) $psIdShop.')
        LEFT JOIN `'._DB_PREFIX_.'wk_mp_stock_available` stock ON (stock.id_mp_product = pa.id_mp_product AND stock.id_mp_product_attribute = IFNULL(`pa`.id_mp_product_attribute, 0) AND stock.id_shop = '.(int) $psIdShop.')
        LEFT JOIN `'._DB_PREFIX_.'wk_mp_product_attribute_combination` pac ON (pac.`id_mp_product_attribute` = pa.`id_mp_product_attribute`)
        LEFT JOIN `'._DB_PREFIX_.'attribute` a ON (a.`id_attribute` = pac.`id_ps_attribute`)
        LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON (ag.`id_attribute_group` = a.`id_attribute_group`)
        LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute`)
        LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group`)
        INNER JOIN `'._DB_PREFIX_.'attribute_shop` attribute_shop ON (attribute_shop.id_attribute = a.id_attribute AND attribute_shop.id_shop = '.(int) $psIdShop.')
        WHERE pa.`id_mp_product` = '.(int) $mpIdProduct.' AND al.`id_lang` = '.(int) $idLang.' AND agl.`id_lang` = '.(int) $idLang.'
        GROUP BY id_attribute_group, id_mp_product_attribute
        ORDER BY ag.`position` ASC, a.`position` ASC, agl.`name` ASC';

        return Db::getInstance()->executeS($sql);
    }

    public function getAttributesImpacts($mpIdProduct)
    {
        $tab = array();
        $result = Db::getInstance()->executeS(
            'SELECT ai.`id_attribute`, ai.`mp_price`, ai.`mp_weight`
            FROM `'._DB_PREFIX_.'wk_mp_attribute_impact` ai
            WHERE ai.`id_mp_product` = '.(int) $mpIdProduct
        );

        if (!$result) {
            return array();
        }
        foreach ($result as $impact) {
            $tab[$impact['id_attribute']]['price'] = (float) $impact['mp_price'];
            $tab[$impact['id_attribute']]['weight'] = (float) $impact['mp_weight'];
        }

        return $tab;
    }

    public function getMpProductTaxRate($idMpProduct)
    {
        $sql = 'SELECT `id_tax_rules_group` FROM `'._DB_PREFIX_.'wk_mp_seller_product` WHERE `id_mp_product` = '.(int) $idMpProduct;
        $result = Db::getInstance()->getValue($sql);
        if ($result) {
            return $result;
        }

        return false;
    }

    public static function ifColorAttributegroup($groupId)
    {
        $objAttrGroup = new AttributeGroup($groupId);
        $flag = $objAttrGroup->is_color_group;
        if ($flag == 1) {
            return true;
        }

        return false;
    }

    public static function checkCombination($attributeId)
    {
        $result = Db::getInstance()->getValue('SELECT `id_product_attribute` FROM `'._DB_PREFIX_.'product_attribute_combination` WHERE `id_attribute` = '.(int) $attributeId);
        if (!$result) {
            return false;
        }

        return true;
    }

    public static function checkCombinationByGroup($idLang, $attribute_group)
    {
        $groupAttribute = AttributeGroup::getAttributes($idLang, $attribute_group);
        $existFlag = 0;
        foreach ($groupAttribute as $groupAttributeEach) {
            $attId = $groupAttributeEach['id_attribute'];
            $result = Db::getInstance()->getValue('SELECT `id_product_attribute` FROM `'._DB_PREFIX_.'product_attribute_combination` WHERE `id_attribute` = '.(int) $attId);
            if ($result) {
                $existFlag = 1;
            }
        }

        return $existFlag;
    }

    public static function setIndexableValue($data)
    {
        return Db::getInstance()->insert('layered_indexable_attribute_group', $data);
    }

    public static function checkCombinationByAttribute($attributeId)
    {
        $result = Db::getInstance()->getValue('SELECT `id_product_attribute` FROM `'._DB_PREFIX_.'product_attribute_combination` WHERE `id_attribute` = '.(int) $attributeId);
        if (!$result) {
            return false;
        }

        return true;
    }

    public static function setAttributesImpacts($idProduct, $tab, $isPsProductId = 0)
    {
        $attributes = array();
        foreach ($tab as $group) {
            foreach ($group as $attribute) {
                $price = preg_replace('/[^0-9.-]/', '', str_replace(',', '.', Tools::getValue('price_impact_'.(int) $attribute)));
                $weight = preg_replace('/[^0-9.-]/', '', str_replace(',', '.', Tools::getValue('weight_impact_'.(int) $attribute)));
                $attributes[] = '('.(int) $idProduct.', '.(int) $attribute.', '.(float) $price.', '.(float) $weight.')';
            }
        }

        $objMpAttributeImpact = new WkMpAttributeImpact();
        return $objMpAttributeImpact->insertDataInToAttributeimpact($attributes, $isPsProductId);
    }

    public static function groupTable($idLang, $psIdShop, $mpProductId)
    {
        return WkMpAttributeImpact::getMpAttributesGroups($idLang, $psIdShop, $mpProductId);
    }

    public static function assignAttributeValues()
    {
        $message = Tools::getValue('msg');
        if ($message) {
            Context::getContext()->smarty->assign('message', $message);
        } else {
            Context::getContext()->smarty->assign('message', 0);
        }

        $idLang = Context::getContext()->language->id;
        $mpProductId = Tools::getValue('id_mp_product');
        $mpAttirbuteCombination = Configuration::get('MP_ATTRIBUTE_COMBINATION');
        if ($mpAttirbuteCombination == 1) {
            Hook::exec('actionAttibuteDisplayBySeller', array('id_mp_product' => $mpProductId));
        } elseif ($mpAttirbuteCombination == 2) {
            Hook::exec('actionAttibuteDisplayByBoth', array('id_mp_product' => $mpProductId));
        } else {
            //only by admin
            $attributeData = array();
            $attributeDetail = AttributeGroup::getAttributesGroups($idLang);
            foreach ($attributeDetail as $attributeDetailEach) {
                $name = $attributeDetailEach['name'];
                $idAttributeGroup = $attributeDetailEach['id_attribute_group'];
                $attributeValueInfo = AttributeGroup::getAttributes($idLang, $idAttributeGroup);
                $attributeData[] = array('attibute_group_name' => $name, 'id_attribute_group' => $idAttributeGroup, 'attribute_value' => $attributeValueInfo);
            }
        }

        $combinationsGroups = WkMpAttributeImpact::groupTable($idLang, Context::getContext()->shop->id, $mpProductId);
        $attributes = array();
        $objMpAttributeImpact = new WkMpAttributeImpact();

        $taxRate = 0;
        $taxesRatesByGroup = TaxRulesGroup::getAssociatedTaxRatesByIdCountry(Configuration::get('PS_COUNTRY_DEFAULT'));
        $idTaxRule = $objMpAttributeImpact->getMpProductTaxRate($mpProductId);
        if (isset($taxesRatesByGroup[$idTaxRule])) {
            $taxRate = $taxesRatesByGroup[$idTaxRule];
        } else {
            $taxRate = 0;
        }

        $impacts = $objMpAttributeImpact->getAttributesImpacts($mpProductId);
        foreach ($combinationsGroups as &$combination) {
            $newPrice = ($combination['mp_price']) * (($taxRate / 100) + 1);
            $target = &$attributes[$combination['id_attribute_group']][$combination['id_attribute']];
            $combination['price_tx_incl'] = $newPrice;
            $target = $combination;
            if (isset($impacts[$combination['id_attribute']])) {
                $newPrice = ($impacts[$combination['id_attribute']]['price']) * (($taxRate / 100) + 1);
                $target['price'] = $impacts[$combination['id_attribute']]['price'];
                $target['price_incl'] = $newPrice;
                $target['weight'] = $impacts[$combination['id_attribute']]['weight'];
            }
        }

        $defaultCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        Context::getContext()->smarty->assign(array(
                'wkself' => dirname(__FILE__),
                'logic' => 'mp_prod_attribute',
                'currency_sign' => $defaultCurrency->sign,
                'attribute_groups' => AttributeGroup::getAttributesGroups($idLang),
                'weight_unit' => Configuration::get('PS_WEIGHT_UNIT'),
                'id_mp_product' => $mpProductId,
                'attribute_array' => $attributeData,
                'mp_attirbute_com' => $mpAttirbuteCombination,
                'tax_rates' => $taxRate,
                'attributes' => $attributes,
            ));

        $jsVars = array(
                    'product_tax' => $taxRate,
                );
        Media::addJsDef($jsVars);
    }
}
