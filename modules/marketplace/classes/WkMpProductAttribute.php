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

class WkMpProductAttribute extends ObjectModel
{
    public $id_mp_product_attribute;
    public $id_mp_product;
    public $id_ps_product_attribute;
    public $id_ps_product;
    public $mp_reference;
    public $mp_ean13;
    public $mp_upc;
    public $mp_isbn;
    public $mp_price;
    public $mp_wholesale_price;
    public $mp_unit_price_impact;
    public $mp_quantity;
    public $mp_weight;
    public $mp_default_on;
    public $mp_minimal_quantity = 1;
    public $mp_available_date = '0000-00-00';
    public $low_stock_threshold = null;
    public $low_stock_alert = false;
    public $active = 1; //this column is used when ps combination customize module will enable.

    public static $definition = array(
        'table' => 'wk_mp_product_attribute',
        'primary' => 'id_mp_product_attribute',
        'fields' => array(
            'id_mp_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_ps_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_ps_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'mp_ean13' => array('type' => self::TYPE_STRING, 'validate' => 'isEan13', 'size' => 13),
            'mp_upc' => array('type' => self::TYPE_STRING, 'validate' => 'isUpc', 'size' => 12),
            'mp_isbn' => array('type' => self::TYPE_STRING, 'validate' => 'isIsbn', 'size' => 13),
            'mp_quantity' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'mp_reference' => array('type' => self::TYPE_STRING, 'size' => 32),
            'active' => array('type' => self::TYPE_INT,  'validate' => 'isUnsignedInt'),
            /* Assigned as also Shop fields */
            'mp_price' => array('type' => self::TYPE_FLOAT, 'validate' => 'isNegativePrice', 'size' => 20),
            'mp_wholesale_price' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'size' => 27),
            'mp_unit_price_impact' => array('type' => self::TYPE_FLOAT, 'validate' => 'isNegativePrice', 'size' => 20),
            'mp_weight' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'mp_minimal_quantity' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'mp_default_on' => array('type' => self::TYPE_INT, 'validate' => 'isBool'),
            'mp_available_date' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'low_stock_threshold' => array('type' => self::TYPE_INT, 'allow_null' => true, 'validate' => 'isInt'),
            'low_stock_alert' => array('type' => self::TYPE_BOOL, 'allow_null' => true, 'validate' => 'isBool'),
        ),
    );

    public function delete()
    {
        Hook::exec('actionBeforeMpProductAttributeDelete', array('id_mp_product_attribute' => (int) $this->id));
        //First delete ps product combination if product is active
        if ($this->id_ps_product && $this->id_ps_product_attribute) {
            $objProduct = new Product($this->id_ps_product);
            if (!$objProduct->deleteAttributeCombination($this->id_ps_product_attribute)) {
                return false;
            }
        }

        if (!$this->deleteAssociations() || !parent::delete()) {
            return false;
        }

        return true;
    }

    public function deleteAssociations()
    {
        $result = Db::getInstance()->delete(
            'wk_mp_product_attribute_combination',
            '`id_mp_product_attribute` = '.(int) $this->id
        );

        $result &= Db::getInstance()->delete(
            'wk_mp_product_attribute_image',
            '`id_mp_product_attribute` = '.(int) $this->id
        );

        $result &= Db::getInstance()->delete(
            'wk_mp_product_attribute_shop',
            '`id_mp_product_attribute` = '.(int) $this->id
        );

        $result &= Db::getInstance()->delete(
            'wk_mp_stock_available',
            '`id_mp_product_attribute` = '.(int) $this->id
        );

        return $result;
    }

    /**
     * Get all combinations of marketplace seller product according to seller product id.
     *
     * @param int $idShop      by default shop id will be 1
     * @param int $idMpProduct seller product id
     * @param int $idLang      context language id
     *
     * @return array
     */
    public function getMpProductCombinations($idMpProduct, $idLang, $idShop = 1)
    {
        if (!Combination::isFeatureActive()) {
            return array();
        }

        $combinations = Db::getInstance()->executeS('SELECT
            pac.`id_mp_product_attribute`,
            pa.*,
            pas.*,
            ag.`id_attribute_group`,
            ag.`is_color_group`,
            agl.`name` AS group_name,
            al.`name` AS attribute_name FROM `'._DB_PREFIX_.'wk_mp_product_attribute_combination` pac
                LEFT JOIN `'._DB_PREFIX_.'wk_mp_product_attribute` pa ON pa.`id_mp_product_attribute` = pac.`id_mp_product_attribute`
                LEFT JOIN `'._DB_PREFIX_.'wk_mp_product_attribute_shop` pas ON (pas.`id_mp_product_attribute` = pa.`id_mp_product_attribute` AND pas.`id_shop` = '.(int) $idShop.')
                LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_ps_attribute`
                LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
                LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int) $idLang.')
                LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.(int) $idLang.')
                WHERE pac.`id_mp_product_attribute` IN (SELECT id_mp_product_attribute FROM '._DB_PREFIX_.'wk_mp_product_attribute WHERE `id_mp_product` = '.(int) $idMpProduct.')
                GROUP BY pa.`id_mp_product_attribute`, ag.`id_attribute_group`');

        if ($combinations) {
            return $combinations;
        }

        return false;
    }

    /**
     * Get marketplace product all combinations only `id_mp_product_attribute` according to seller product id.
     *
     * @param int $idMpProduct seller product id
     *
     * @return array
     */
    public static function getProductAttributesIds($idMpProduct)
    {
        $result = Db::getInstance()->executeS('SELECT `id_mp_product_attribute` FROM `'._DB_PREFIX_.'wk_mp_product_attribute` WHERE `id_mp_product` = '.(int) $idMpProduct);

        if ($result) {
            return $result;
        }

        return false;
    }

    /**
     * Get ps product default combination details according to ps product id.
     *
     * @param int $idPsProduct PS product id
     *
     * @return array
     */
    public static function getPsProductDefaultAttributesIds($idPsProduct)
    {
        return Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'product_attribute`
            WHERE `default_on` = 1 AND `id_product` = '.(int) $idPsProduct);
    }

    /**
     * Get ps product default combination details according to ps product id.
     *
     * @param int $idAttribute        collection of id attribute of making combination
     * @param int $idProductAttribute product attribute id
     *
     * @return bool
     */
    public function insertIntoPsProductCombination($idAttribute, $idProductAttribute)
    {
        return Db::getInstance()->insert(
            'product_attribute_combination',
            array(
                'id_attribute' => (int) $idAttribute,
                'id_product_attribute' => (int) $idProductAttribute,
            )
        );
    }

    /**
     * Assign and Display product combination list at update product page in frontend/backend.
     *
     * @param int $mpIdProduct seller product id
     *
     * @return bool
     */
    public static function displayProductCombinationList($mpIdProduct)
    {
        $context = Context::getContext();

        // check if pack product or virtual product If yes combinations will not be shown.
        $isVirtualProduct = 0;
        $isPackProduct = 0;

        if (Module::isInstalled('mppackproducts')) {
            include_once _PS_MODULE_DIR_.'mppackproducts/classes/WkMpPackProduct.php';
            $objPackProduct = new WkMpPackProduct();
            $isPackProduct = $objPackProduct->isPackProduct($mpIdProduct);
        }
        if (Module::isInstalled('mpvirtualproduct') && Module::isEnabled('mpvirtualproduct')) {
            include_once _PS_MODULE_DIR_.'mpvirtualproduct/classes/MarketplaceVirtualProduct.php';
            $objVirtualProduct = new MarketplaceVirtualProduct();
            $isVirtualProduct = $objVirtualProduct->isMpProductIsVirtualProduct($mpIdProduct);
        }

        if (!$isPackProduct && !$isVirtualProduct) {
            //if product is not a virtual product or pack product
            //check ps product (if exist) is virtual or not
            $flag = 0;
            $mpShopProduct = WkMpSellerProduct::getSellerProductByIdProduct($mpIdProduct);
            if ($mpShopProduct) {
                $psProductId = $mpShopProduct['id_ps_product'];
                $objProduct = new Product($psProductId);
                $isVirtualProduct = $objProduct->is_virtual;
                if ($isVirtualProduct == 1) {
                    $flag = 1;
                }
            }

            if ($flag == 0) { //if product is not a virtual product
                $combinationDetail = self::getMpCombinationsResume($mpIdProduct);
                if ($combinationDetail) {
                    $context->smarty->assign('combination_detail', $combinationDetail);
                }

                if (Module::isEnabled('wkcombinationcustomize')) {
                    if (Configuration::get('WK_MP_PRODUCT_COMBINATION_CUSTOMIZE')) {
                        $context->smarty->assign('allowCombinationCustomizeFrontEnd', 1);
                    }

                    $context->smarty->assign('combinationCustomizeInstalled', 1);
                }

                $context->smarty->assign('id', $mpIdProduct);
                $context->smarty->assign('def_currency_id', Configuration::get('PS_CURRENCY_DEFAULT'));
                $context->smarty->assign('ps_weight_unit', Configuration::get('PS_WEIGHT_UNIT'));
                $context->smarty->assign('admin_img_path', _PS_ADMIN_IMG_);
                $context->smarty->assign('modules_dir', _MODULE_DIR_);
                $context->smarty->assign('link', $context->link);
            }
        }
    }

    public static function getMpCombinationsResume($mpIdProduct)
    {
        $context = Context::getContext();
        $combinationDetail = array();

        $objProductAttribute = new self();
        $mpCombinationDetail = $objProductAttribute->getMpProductCombinations($mpIdProduct, $context->language->id, $context->shop->id);
        if (isset($mpCombinationDetail) && $mpCombinationDetail) {
            foreach ($mpCombinationDetail as $valCombination) {
                $idMpProductAttribute = $valCombination['id_mp_product_attribute'];

                if (!isset($combinationDetail[$idMpProductAttribute]['attribute_designation'])) {
                    $combinationDetail[$idMpProductAttribute]['attribute_designation'] = '';
                }
                $combinationDetail[$idMpProductAttribute]['id_mp_product_attribute'] = $idMpProductAttribute;
                $combinationDetail[$idMpProductAttribute]['id_mp_product'] = $valCombination['id_mp_product'];
                $combinationDetail[$idMpProductAttribute]['mp_quantity'] = $valCombination['mp_quantity'];
                $combinationDetail[$idMpProductAttribute]['mp_price'] = Tools::displayPrice($valCombination['mp_price'], new Currency(Configuration::get('PS_CURRENCY_DEFAULT')));
                $combinationDetail[$idMpProductAttribute]['mp_weight'] = $valCombination['mp_weight'];
                $combinationDetail[$idMpProductAttribute]['mp_reference'] = $valCombination['mp_reference'];
                $combinationDetail[$idMpProductAttribute]['mp_default_on'] = $valCombination['mp_default_on'];
                $combinationDetail[$idMpProductAttribute]['attribute_designation'] .= $valCombination['group_name'].' - '.$valCombination['attribute_name'].', ';
                $combinationDetail[$idMpProductAttribute]['active'] = $valCombination['active'];
            }
        }

        return $combinationDetail;
    }

    /**
     * Get mp product attribute details according to seller product id.
     *
     * @param int $mpProductId seller product id
     *
     * @return array
     */
    public function getProductAttributes($mpProductId)
    {
        return Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'wk_mp_product_attribute` WHERE `id_mp_product` = '.(int) $mpProductId);
    }


    /**
     * This function is deprecated in Mp V5.2.0 and V3.2.0 (but may be using in mp addons)
     */
    public static function createOrUpdateMpProductCombination(
        $idMpProduct,
        $idMpProductAttribute = false,
        $productAttributeList,
        $mpReference,
        $mpEan13,
        $mpUPC,
        $mpISBN,
        $mpPrice,
        $mpWholesalePrice,
        $mpUnitPriceImpact,
        $mpQuantity,
        $mpWeight,
        $mpMinimalQuantity,
        $mpAvailableDate,
        $idImages,
        $lowStockThreshold = false,
        $lowStockAlert = false
    ) {
        if (!$idMpProductAttribute) {
            $idMpProductAttribute = 0;
        }
        if (!$lowStockThreshold) {
            $lowStockThreshold = 0;
        }
        if (!$lowStockAlert) {
            $lowStockAlert = 0;
        }

        return self::saveMpProductCombination(
            $idMpProduct,
            $idMpProductAttribute,
            $productAttributeList,
            $mpReference,
            $mpEan13,
            $mpUPC,
            $mpISBN,
            $mpPrice,
            $mpWholesalePrice,
            $mpUnitPriceImpact,
            $mpQuantity,
            $mpWeight,
            $mpMinimalQuantity,
            $mpAvailableDate,
            $idImages,
            $lowStockThreshold,
            $lowStockAlert
        );
    }

    /**
     * Create/Update marketplace product combination with given data and
     * if product is active then also create/update for prestashop product.
     *
     * @param type $idMpProduct - Seller product id
     * @param type $idMpProductAttribute - Seller product attribute id
     * @param type $productAttributeList - Product combination attribute list
     * @param type $mpReference - Combination reference
     * @param type $mpEan13 - Combination EAN
     * @param type $mpUPC - Combination UPC
     * @param type $mpISBN - Combination ISBN
     * @param type $mpPrice - Combination price
     * @param type $mpWholesalePrice - Combination wholesale price
     * @param type $mpUnitPriceImpact - Combination impact on unit price price
     * @param type $mpQuantity - Combination quantity
     * @param type $mpWeight - Combination weight
     * @param type $mpMinimalQuantity - Combination minimum quantity
     * @param type $mpAvailableDate - Combination available date
     * @param type $idImages - Combination images
     * @param type $lowStockThreshold - Combination low stock level value
     * @param type $lowStockAlert - Combination low stock level checkbox
     *
     * @return int
     */
    public static function saveMpProductCombination(
        $idMpProduct,
        $idMpProductAttribute = false,
        $productAttributeList,
        $mpReference,
        $mpEan13,
        $mpUPC,
        $mpISBN,
        $mpPrice,
        $mpWholesalePrice,
        $mpUnitPriceImpact,
        $mpQuantity,
        $mpWeight,
        $mpMinimalQuantity,
        $mpAvailableDate,
        $idImages,
        $lowStockThreshold = false,
        $lowStockAlert = false
    ) {
        if (!$lowStockThreshold) {
            $lowStockThreshold = 0;
        }
        if (!$lowStockAlert) {
            $lowStockAlert = 0;
        }

        $objSellerProduct = new WkMpSellerProduct($idMpProduct);

        if ($idMpProductAttribute) {
            //edit combination
            $editCombi = 1;
            $objMpProductAttribute = new self($idMpProductAttribute);
        } else {
            //Create combination
            $editCombi = 0;
            $objMpProductAttribute = new self();
        }

        $objMpProductAttribute->id_mp_product = $idMpProduct;
        $objMpProductAttribute->mp_reference = $mpReference;
        $objMpProductAttribute->mp_ean13 = $mpEan13;
        $objMpProductAttribute->mp_upc = $mpUPC;
        $objMpProductAttribute->mp_isbn = $mpISBN;
        $objMpProductAttribute->mp_price = $mpPrice;
        $objMpProductAttribute->mp_wholesale_price = $mpWholesalePrice;
        $objMpProductAttribute->mp_unit_price_impact = $mpUnitPriceImpact;
        $objMpProductAttribute->mp_quantity = $mpQuantity;
        $objMpProductAttribute->mp_weight = $mpWeight;
        $objMpProductAttribute->mp_minimal_quantity = $mpMinimalQuantity;
        $objMpProductAttribute->mp_available_date = $mpAvailableDate;
        $objMpProductAttribute->low_stock_threshold = $lowStockThreshold;
        $objMpProductAttribute->low_stock_alert = $lowStockAlert;

        $productHasCombination = self::getProductAttributesIds($idMpProduct);
        if (!$productHasCombination) {
            $objMpProductAttribute->mp_default_on = 1;
        }

        $objMpProductAttribute->save();
        $attributeList = array();

        if ($editCombi) {
            WkMpProductAttributeCombination::deleteProductAttributeCombination($idMpProductAttribute);

            foreach ($productAttributeList as $group) {
                $attributeList[] = array(
                    'id_mp_product_attribute' => (int) $idMpProductAttribute,
                    'id_ps_attribute' => (int) $group,
                );
            }

            WkMpProductAttributeCombination::insertDataIntoMpproductattributecombination($attributeList);

            if (!$productHasCombination) {
                WkMpProductAttributeShop::updateProductAttributeShopData(
                    $idMpProductAttribute,
                    $mpPrice,
                    $mpWholesalePrice,
                    $mpUnitPriceImpact,
                    $mpWeight,
                    $mpMinimalQuantity,
                    $mpAvailableDate,
                    1,
                    $lowStockThreshold,
                    $lowStockAlert
                );
            } else {
                WkMpProductAttributeShop::updateProductAttributeShopData(
                    $idMpProductAttribute,
                    $mpPrice,
                    $mpWholesalePrice,
                    $mpUnitPriceImpact,
                    $mpWeight,
                    $mpMinimalQuantity,
                    $mpAvailableDate,
                    false,
                    $lowStockThreshold,
                    $lowStockAlert
                );
            }
        } else {
            $idMpProductAttribute = $objMpProductAttribute->id;

            foreach ($productAttributeList as $group) {
                $attributeList[] = array(
                    'id_mp_product_attribute' => (int) $idMpProductAttribute,
                    'id_ps_attribute' => (int) $group,
                );
            }

            WkMpProductAttributeCombination::insertDataIntoMpproductattributecombination($attributeList);

            if (!$productHasCombination) {
                WkMpProductAttributeShop::insertProductAttributeShopData(
                    $idMpProduct,
                    $idMpProductAttribute,
                    $mpPrice,
                    $mpWholesalePrice,
                    $mpUnitPriceImpact,
                    $mpWeight,
                    $mpMinimalQuantity,
                    $mpAvailableDate,
                    1,
                    $lowStockThreshold,
                    $lowStockAlert
                );
            } else {
                WkMpProductAttributeShop::insertProductAttributeShopData(
                    $idMpProduct,
                    $idMpProductAttribute,
                    $mpPrice,
                    $mpWholesalePrice,
                    $mpUnitPriceImpact,
                    $mpWeight,
                    $mpMinimalQuantity,
                    $mpAvailableDate,
                    false,
                    $lowStockThreshold,
                    $lowStockAlert
                );
            }
        }

        //Set Mp combination mp images
        if ($idImages) {
            WkMpProductAttributeImage::setMpImages($idImages, $idMpProductAttribute);
        }

        //Set Mp Quantity
        WkMpStockAvailable::setMpQuantity($idMpProduct, $idMpProductAttribute, $mpQuantity);

        //when combination created/updated then update mp product total quantity
        $currentQty = self::getMpProductQty($idMpProduct);
        if (!$currentQty) {
            $currentQty = 0;
        }

        //Update Seller product qty
        self::updateMpProductQty($currentQty, $idMpProduct);

        if (Configuration::get('WK_MP_PRODUCT_UPDATE_ADMIN_APPROVE') && 'managecombination' == Tools::getValue('controller')) {
            WkMpSellerProduct::deactivateProductAfterUpdate($idMpProduct);
        } else {
            //if Product is active as a Prestashop product
            if ($objSellerProduct->active && $objSellerProduct->id_ps_product) {
                $idPsProduct = $objSellerProduct->id_ps_product;

                $idPsProductAttribute = $objMpProductAttribute->id_ps_product_attribute;
                if ($idPsProductAttribute) {
                    $objCombination = new Combination($idPsProductAttribute);
                } else {
                    $objCombination = new Combination();
                }

                $objCombination->id_product = $idPsProduct;
                $objCombination->reference = $mpReference;
                $objCombination->ean13 = $mpEan13;
                $objCombination->upc = $mpUPC;
                $objCombination->isbn = $mpISBN;
                $objCombination->price = $mpPrice;
                $objCombination->wholesale_price = $mpWholesalePrice;
                $objCombination->unit_price_impact = $mpUnitPriceImpact;
                $objCombination->weight = $mpWeight;
                $objCombination->minimal_quantity = $mpMinimalQuantity;
                $objCombination->available_date = $mpAvailableDate;
                if (_PS_VERSION_ >= '1.7.3.0') {
                    //Prestashop added this feature in PS V1.7.3.0 and above
                    $objCombination->low_stock_threshold = $lowStockThreshold;
                    $objCombination->low_stock_alert = $lowStockAlert;
                }

                $psProductHasCombination = self::getPsProductDefaultAttributesIds($idPsProduct);
                if (!$psProductHasCombination) {
                    $objCombination->default_on = 1;
                }

                $objCombination->save();

                if ($editCombi) {
                    //if admin delete combination from catalog then another combination will automatially created when seller update the combination of product.

                    WkMpProductAttributeCombination::deleteProductAttrCombByPsAttrId($idPsProductAttribute);
                }

                $idPsProductAttribute = $objCombination->id;

                foreach ($productAttributeList as $group) {
                    $objMpProductAttribute->insertIntoPsProductCombination($group, $idPsProductAttribute);
                }

                //Update ps product id and ps product attribute id, If product is active
                $objMpAttribute = new self($idMpProductAttribute);
                $objMpAttribute->id_mp_product = $idMpProduct;
                $objMpAttribute->id_ps_product_attribute = $idPsProductAttribute;
                $objMpAttribute->id_ps_product = $idPsProduct;
                $objMpAttribute->save();

                //combination ps Images
                $objCombination->setImages($idImages);

                StockAvailable::setQuantity($idPsProduct, $idPsProductAttribute, $mpQuantity);
            }
        }

        //If mp product combination create or update
        Hook::exec('actionAfterUpdateMPProductCombination', array('id_mp_product' => $idMpProduct, 'id_mp_product_attribute' => $idMpProductAttribute));

        return $idMpProductAttribute;
    }

    /**
     * Update ps product combination data by getting details of mp product's each combination.
     * This functions will call only when product is going to active either first time or after deactive
     *
     * @param int $mpProductId seller product id
     * @param int $psProductId ps product id
     *
     * @return array
     */
    public function updateMpProductCombinationToPs($mpProductId, $psProductId)
    {
        $generateCombinationProduct = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'attribute_impact` WHERE `id_product` = '.(int) $psProductId);
        if ($generateCombinationProduct) {
            //if product combination generated through generate combination features then delete old combination then recreate (It calls while reactive seller product)
            $objProduct = new Product($psProductId);
            $objProduct->deleteProductAttributes();
        }

        $objMpProductAttribute = new self();
        $productAttributes = $objMpProductAttribute->getProductAttributes($mpProductId);
        if ($productAttributes) {
            foreach ($productAttributes as $attributeVal) {
                if ($attributeVal['id_ps_product_attribute']) {
                    //if combination already created in PS
                    $objCombination = new Combination($attributeVal['id_ps_product_attribute']);

                    WkMpProductAttributeCombination::deleteProductAttrCombByPsAttrId($attributeVal['id_ps_product_attribute']);
                } else {
                    $objCombination = new Combination();
                }

                $objCombination->id_product = $psProductId;
                $objCombination->reference = $attributeVal['mp_reference'];
                $objCombination->ean13 = $attributeVal['mp_ean13'];
                $objCombination->upc = $attributeVal['mp_upc'];
                $objCombination->isbn = $attributeVal['mp_isbn'];
                $objCombination->price = $attributeVal['mp_price'];
                $objCombination->wholesale_price = $attributeVal['mp_wholesale_price'];
                $objCombination->unit_price_impact = $attributeVal['mp_unit_price_impact'];
                $objCombination->quantity = $attributeVal['mp_quantity'];
                $objCombination->weight = $attributeVal['mp_weight'];
                $objCombination->default_on = $attributeVal['mp_default_on'];
                $objCombination->minimal_quantity = $attributeVal['mp_minimal_quantity'];
                $objCombination->available_date = $attributeVal['mp_available_date'];
                $objCombination->save();

                $idPsProductAttribute = $objCombination->id;
                $idMpProductAttribute = $attributeVal['id_mp_product_attribute'];

                $productAttributeList = WkMpProductAttributeCombination::getPsAttributeIdForMpProduct($idMpProductAttribute);
                foreach ($productAttributeList as $productAttribute) {
                    $objMpProductAttribute->insertIntoPsProductCombination($productAttribute['id_ps_attribute'], $idPsProductAttribute);
                }

                StockAvailable::setQuantity($psProductId, $idPsProductAttribute, $attributeVal['mp_quantity']);

                //Update ps product id and ps product attribute id, If product is active
                $objMpAttribute = new self($idMpProductAttribute);
                $objMpAttribute->id_mp_product = $mpProductId;
                $objMpAttribute->id_ps_product_attribute = $idPsProductAttribute;
                $objMpAttribute->id_ps_product = $psProductId;
                $objMpAttribute->save();

                //Save images in ps product
                $attributeImages = WkMpProductAttributeImage::getAttributeImages($idMpProductAttribute);
                if ($attributeImages) {
                    $idPsImages = array();
                    foreach ($attributeImages as $images) {
                        //Get ps image id from marketplace product image table according to mp id image
                        $objMpProductImage = new WkMpSellerProductImage($images['id_image']);
                        $idPsImages[] = $objMpProductImage->id_ps_image;

                        unset($objMpProductImage);
                    }

                    $objCombination->setImages($idPsImages);

                    //Set Ps images in attibute image table if product is active
                    WkMpProductAttributeImage::setMpImages($idPsImages, $idMpProductAttribute);
                }

                if (Module::isEnabled('wkcombinationcustomize')) {
                    //if combination is deactivated then enter this combination ps combination customize module table
                    if (!$attributeVal['active']) {
                        $combiData = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'wk_combination_status` WHERE `id_ps_product` = '.(int) $psProductId.' AND `id_ps_product_attribute` = '.(int) $idPsProductAttribute);
                        if (!$combiData) {
                            $objCombiStatus = new WkCombinationStatus();
                            $objCombiStatus->id_ps_product = (int) $psProductId;
                            $objCombiStatus->id_ps_product_attribute = (int) $idPsProductAttribute;
                            $objCombiStatus->save();
                        }
                    }
                }

                unset($objCombination);
            }
        }
    }

    /**
     * Get total quantity of all combinations of any seller product.
     *
     * @param int $idMpProduct seller product id
     *
     * @return array
     */
    public static function getMpProductQty($idMpProduct)
    {
        return Db::getInstance()->getValue('SELECT SUM(`mp_quantity`) FROM `'._DB_PREFIX_.'wk_mp_product_attribute` WHERE `id_mp_product` = '.(int) $idMpProduct);
    }

    public static function updateMpProductQty($mpProductQty, $idMpProduct)
    {
        return Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'wk_mp_seller_product` SET `quantity` = '.(int) $mpProductQty.' WHERE `id_mp_product` = '.(int) $idMpProduct);
    }

    /**
     * Set a default attribute in existing combination of seller product by product attribute id.
     *
     * @param int $idMpProductAttribute seller product attribute id
     *
     * @return array
     */
    public static function setMpProductDefaultAttribute($idMpProductAttribute)
    {
        $objProductAttribute = new self($idMpProductAttribute);

        $idMpProduct = $objProductAttribute->id_mp_product;
        $idPsProduct = $objProductAttribute->id_ps_product;
        $idPsProductAttribute = $objProductAttribute->id_ps_product_attribute;

        //first set mp_default_on as Zero of all combination
        if ($objProductAttribute->changeAttributeDefaultValue($idMpProduct)) {
            //Make MP combination as default attibute
            $objProductAttribute->mp_default_on = 1;

            if ($objProductAttribute->save()) {
                $attributes = self::getProductAttributesIds($idMpProduct, true);
                foreach ($attributes as $attribute) {
                    if ($attribute['id_mp_product_attribute'] == $idMpProductAttribute) {
                        WkMpProductAttributeShop::changeAttributeShopDefaultValue($attribute['id_mp_product_attribute'], 1);
                    } else {
                        WkMpProductAttributeShop::changeAttributeShopDefaultValue($attribute['id_mp_product_attribute']);
                    }
                }

                //Make default attibute to ps combination
                if ($idPsProduct && $idPsProductAttribute) {
                    $objProduct = new Product($idPsProduct);
                    $objProduct->deleteDefaultAttributes();
                    $objProduct->setDefaultAttribute($idPsProductAttribute);
                }

                return true;
            }
        }

        return false;
    }

    public function changeAttributeDefaultValue($idMpProduct, $mpDefaultOn = 0)
    {
        return Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'wk_mp_product_attribute SET `mp_default_on` = '.(int) $mpDefaultOn.' WHERE `id_mp_product` = '.(int) $idMpProduct);
    }

    public static function deleteSellerProductCombination($idMpProductAttribute)
    {
        $objMpAttribute = new self($idMpProductAttribute);
        $isDefaultAttribute = $objMpAttribute->mp_default_on;
        $idMpProduct = $objMpAttribute->id_mp_product;

        if ($objMpAttribute->delete()) { //if mp combination is deleted
            //If delete default combination then make first combination in existing set as default
            if ($isDefaultAttribute) {
                $productAttributeIds = self::getProductAttributesIds($idMpProduct);
                if ($productAttributeIds) {
                    $firstMpIdProductAttribute = $productAttributeIds[0]['id_mp_product_attribute'];

                    //Active default combination
                    $objProductAttr = new self($firstMpIdProductAttribute);
                    $objProductAttr->active = 1;
                    $objProductAttr->save();

                    //Set a combination as default combination in existing combinations
                    self::setMpProductDefaultAttribute($firstMpIdProductAttribute);

                    if ($objProductAttr->id_ps_product_attribute) {
                        if (Module::isInstalled('wkcombinationcustomize')) {
                            Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'wk_combination_status` WHERE `id_ps_product_attribute` = '.(int) $objProductAttr->id_ps_product_attribute);
                        }
                    }
                }
            }

            //when combination deleted then decrease mp product total quantity
            $currentQty = self::getMpProductQty($idMpProduct);
            if (!$currentQty) {
                $currentQty = 0;
            }

            //Update Seller product qty
            self::updateMpProductQty($currentQty, $idMpProduct);

            return true;
        }

        return false;
    }

    /**
     * Assign all details that are used for creating a combination ie. groups, countries, currencied etc.
     * at create combination page.
     *
     * @param int $mpProduct            seller product details array
     * @param int $idMpProduct          seller product id
     * @param int $idMpProductAttribute seller product attribute id
     *
     * @return array
     */
    public static function assignCombinationCreationFormData($mpProduct, $idMpProduct, $idMpProductAttribute = false)
    {
        $context = Context::getContext();

        $idPsProduct = $mpProduct['id_ps_product'];
        $mpProductPrice = $mpProduct['price'];

        // ADDED FOR TAX CALCULATION
        $idTaxRulesGroup = $mpProduct['id_tax_rules_group'];
        $taxesRatesByGroup = TaxRulesGroup::getAssociatedTaxRatesByIdCountry(Configuration::get('PS_COUNTRY_DEFAULT'));
        if (isset($taxesRatesByGroup[$idTaxRulesGroup])) {
            $taxRate = $taxesRatesByGroup[$idTaxRulesGroup];
        } else {
            $taxRate = 0;
        }

        // ADDED FOR COMBINATION IMAGES
        if ($idPsProduct && $mpProduct['active']) {
            $psImages = Image::getImages($context->language->id, $idPsProduct);
            $i = 0;
            foreach ($psImages as $k => $image) {
                $psImages[$k]['obj'] = new Image($image['id_image']);
                ++$i;
            }

            $context->smarty->assign('mp_pro_image', $psImages);
            $context->smarty->assign('is_ps_product', 1);
        } else {
            $objMpProductImage = new WkMpSellerProductImage();
            $mpProductImage = $objMpProductImage->getProductImageBySellerIdProduct($idMpProduct);
            if ($mpProductImage) {
                $context->smarty->assign('mp_pro_image', $mpProductImage);
            }
        }

        if ($idMpProductAttribute) { //edit combination
            $attributeBoxGroupIds = array();
            $objMpProductAttribute = new self($idMpProductAttribute);

            if ($idPsProduct && $mpProduct['active']) {
                $idPsProductAttribute = $objMpProductAttribute->id_ps_product_attribute;
                $quantity = StockAvailable::getQuantityAvailableByProduct($idPsProduct, $idPsProductAttribute);

                $psAttributeImages = WkMpProductAttributeImage::getPsAttributeImages($idPsProductAttribute);
                $context->smarty->assign('ps_attribute_images', $psAttributeImages);
            } else {
                $quantity = $objMpProductAttribute->mp_quantity;

                $attributeImages = WkMpProductAttributeImage::getAttributeImages($idMpProductAttribute);
                $context->smarty->assign('attribute_images', $attributeImages);
            }

            //Get Selected combination attribute group and value for display combination in Box
            $i = 0;
            $selectedAttributeInBox = array();
            $attributeIdsSet = WkMpProductAttributeCombination::getPsAttributesSet($idMpProductAttribute);
            $attributes = Attribute::getAttributes($context->language->id, true);
            if ($attributes && $attributeIdsSet) {
                foreach ($attributes as $attributeVal) {
                    foreach ($attributeIdsSet as $attributeIdsSetVal) {
                        if ($attributeVal['id_attribute'] == $attributeIdsSetVal['id_ps_attribute']) {
                            $selectedAttributeInBox[$i]['groupid'] = $attributeVal['id_attribute_group'];
                            $selectedAttributeInBox[$i]['id'] = $attributeVal['id_attribute'];
                            $selectedAttributeInBox[$i]['name'] = $attributeVal['attribute_group'].' : '.$attributeVal['name'];
                            $attributeBoxGroupIds[$i] = $attributeVal['id_attribute_group'];
                            ++$i;
                        }
                    }
                }
            }

            // Calculate tax include impact price on this combination
            $impactPrice = $objMpProductAttribute->mp_price;
            $impactTaxIncl = ($impactPrice) * (($taxRate / 100) + 1);

            $context->smarty->assign(array(
                'impact_tax_incl' => $impactTaxIncl,
                'selectedAttributeInBox' => $selectedAttributeInBox,
                'mp_id_product_attribute' => $idMpProductAttribute,
                'productAttribute' => (array) $objMpProductAttribute,
                'quantity' => $quantity,
                'edit' => 1,
            ));
        }

        if (isset($attributeBoxGroupIds)) {
            $selectedAttributeGroup = Tools::jsonEncode($attributeBoxGroupIds);
        } else {
            $selectedAttributeGroup = array();
        }

        Media::addJsDef(array(
                'selected_attribute_group' => $selectedAttributeGroup,
                'tax_rate' => $taxRate,
            ));

        $context->smarty->assign(array(
            'mp_id_product' => $idMpProduct,
            'mp_product_price' => $mpProductPrice,
            'attributeGroup' => AttributeGroup::getAttributesGroups($context->language->id),
            'def_currency' => new Currency(Configuration::get('PS_CURRENCY_DEFAULT')),
            'ps_weight_unit' => Configuration::get('PS_WEIGHT_UNIT'),
            'logic' => 1,
        ));
    }

    /**
     * Get Attribute Value after choosing group ie. when choose color, display all colors in value field.
     *
     * @param int $attributeGroupId Attribute Group id
     *
     * @return array
     */
    public static function getAttributeValueByGroup($attributeGroupId)
    {
        //Get Attribute Value according to Attribute Group
        if ($attributeGroupId) {
            $i = 0;
            $attributeVal = array();
            $attributes = Attribute::getAttributes(Context::getContext()->language->id, true);
            if ($attributes) {
                foreach ($attributes as $attribute) {
                    if ($attributeGroupId == $attribute['id_attribute_group']) {
                        $attributeVal[$i]['id'] = $attribute['id_attribute'];
                        $attributeVal[$i]['name'] = $attribute['name'];
                        ++$i;
                    }
                }

                return $attributeVal;
            }
        }

        return false;
    }

    /**
     * Update default combination for seller product through ajaxProcess.
     */
    public static function updateMpProductDefaultAttribute()
    {
        $idMpProductAttribute = Tools::getValue('id_combination');
        if ($idMpProductAttribute) {
            $mpIdProduct = Tools::getValue('id_mp_product');
            $objMpProductAttribute = new WkMpProductAttribute($idMpProductAttribute);
            //Check condition if combination is existing in seller product (seller condition will be check by initContent())
            if ($objMpProductAttribute->id_mp_product == $mpIdProduct) {
                //Set a combination as default combination in existing combinations
                if (self::setMpProductDefaultAttribute($idMpProductAttribute)) {
                    //To manage staff log (changes add/update/delete)
                    if (Tools::getValue('controller') == 'managecombination') {
                        WkMpHelper::setStaffHook(Context::getContext()->customer->id, 'managecombination', $mpIdProduct, 2); // 2 for Update action
                    }
                    die('1'); //ajax close
                }
            }
        }

        die('fail'); //ajax close
    }

    /**
     * Delete seller product combination through ajaxProcess.
     */
    public static function deleteMpProductAttribute()
    {
        //Delete Product combination from combination list at edit product page
        $idMpProductAttribute = Tools::getValue('id_combination');
        if ($idMpProductAttribute) {
            $mpIdProduct = Tools::getValue('id_mp_product');
            $objMpProductAttribute = new WkMpProductAttribute($idMpProductAttribute);
            //Check condition if combination is existing in seller product (seller condition will be check by initContent())
            if ($objMpProductAttribute->id_mp_product == $mpIdProduct) {
                //delete Mp combination
                if (self::deleteSellerProductCombination($idMpProductAttribute)) {
                    //To manage staff log (changes add/update/delete)
                    if (Tools::getValue('controller') == 'managecombination') {
                        WkMpHelper::setStaffHook(Context::getContext()->customer->id, 'managecombination', $mpIdProduct, 3); // 3 for delete action
                    }
                    die('1'); //ajax close
                }
            }
        }

        die('fail'); //ajax close
    }

    /**
     * Change combination status through ajaxProcess if combination activate/deactivate module is enabled.
     */
    public static function changeCombinationStatus()
    {
        ///If Prestashop Combination Activate/Deactivate module is enabled.
        if (Module::isEnabled('wkcombinationcustomize')) {
            $mpIdProduct = Tools::getValue('id_mp_product');
            $mpIdProductAttribute = Tools::getValue('id_combination');

            if ($mpIdProduct && $mpIdProductAttribute) {
                $mpProductDetail = WkMpSellerProduct::getSellerProductByIdProduct($mpIdProduct);
                if ($mpProductDetail) {
                    Hook::exec('actionBeforeToggleMpCombinationStatus', array('mp_id_product_attribute' => $mpIdProductAttribute));

                    $idPsProduct = $mpProductDetail['id_ps_product'];

                    $objMpProductAttribute = new self($mpIdProductAttribute);
                    if ($objMpProductAttribute->mp_default_on) {
                        $isDefaultAttribute = 1;
                    }

                    $combiStatus = self::getMpProductCombinationStatus($mpIdProduct, $mpIdProductAttribute, $idPsProduct);

                    if ('-1' == $combiStatus) {
                        die('-1');
                    } else {
                        Hook::exec('actionToogleMpCombinationStatus', array('mp_id_product' => $mpIdProduct, 'mp_id_product_attribute' => $mpIdProductAttribute, 'active' => $combiStatus));

                        //To manage staff log (changes add/update/delete)
                        if (Tools::getValue('controller') == 'managecombination') {
                            WkMpHelper::setStaffHook(Context::getContext()->customer->id, 'managecombination', $mpIdProduct, 2); // 2 for Update action
                        }

                        if (isset($isDefaultAttribute) && $isDefaultAttribute) {
                            die('2');
                        } else {
                            if ('1' == $combiStatus) {
                                die('1'); //ajax close
                            } elseif ('0' == $combiStatus) {
                                die('0'); //ajax close
                            }
                        }
                    }
                }
            }
        }

        die; //ajax close
    }

    /**
     * Get combination status after activate/deactive that combination.
     *
     * @param int $mpIdProduct          mp product id
     * @param int $mpIdProductAttribute mp product attribute id
     * @param int $idPsProduct          ps product id
     *
     * @return array
     */
    public static function getMpProductCombinationStatus($mpIdProduct, $mpIdProductAttribute, $idPsProduct)
    {
        $objMpProductAttribute = new self($mpIdProductAttribute);

        if ($objMpProductAttribute->active) {
            $activeCombination = Db::getInstance()->executeS('SELECT `id_mp_product_attribute` FROM `'._DB_PREFIX_.'wk_mp_product_attribute` WHERE `id_mp_product` = '.(int) $mpIdProduct.' AND `active` = 1');
            if (count($activeCombination) <= 1) { //if combination is last active combination then it will not deactivate
                return -1;
            }
        }

        if ($objMpProductAttribute->mp_default_on) {
            $firstActiveCombination = Db::getInstance()->getRow('SELECT `id_mp_product_attribute` FROM `'._DB_PREFIX_.'wk_mp_product_attribute` WHERE `id_mp_product` = '.(int) $mpIdProduct.' AND `mp_default_on` = 0 AND `active` = 1');
            if ($firstActiveCombination) {
                $firstActionMpAttributeid = $firstActiveCombination['id_mp_product_attribute'];

                $objProductAttr = new self();
                $objProductAttr->setMpProductDefaultAttribute($firstActionMpAttributeid);
            }
        }

        if ($objMpProductAttribute->active) {
            $combiStatus = 0; //going to deactive
        } else {
            $combiStatus = 1; //going to active
        }

        $result = Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'wk_mp_product_attribute` SET `active` = '.(int) $combiStatus.' WHERE `id_mp_product_attribute` = '.(int) $mpIdProductAttribute);
        if ($result) {
            //If product is active
            if ($idPsProduct && $objMpProductAttribute->id_ps_product_attribute) {
                $idPsProductAttribute = $objMpProductAttribute->id_ps_product_attribute;
                $combiData = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'wk_combination_status` WHERE `id_ps_product` = '.(int) $idPsProduct.' AND `id_ps_product_attribute` = '.(int) $idPsProductAttribute);
                if ($combiData) {
                    $objCombiStatus = new WkCombinationStatus((int) $combiData['id']);
                } else {
                    $objCombiStatus = new WkCombinationStatus();
                }

                if ($combiStatus) {
                    $objCombiStatus->delete();
                } else {
                    $objCombiStatus->id_ps_product = (int) $idPsProduct;
                    $objCombiStatus->id_ps_product_attribute = (int) $idPsProductAttribute;
                    $objCombiStatus->save();
                }
            }
        }

        return $combiStatus;
    }

    /**
     * Manage seller product quantity combination wise.
     *
     * @param int  $idMpProduct          Seller Product ID
     * @param int  $idPsProductAttribute Prestashop Prodcut Attribute ID
     * @param bool $quantity             Bought quantity
     *
     * @return bool/array
     */
    public static function updateAttributeQuantity($idMpProduct, $idPsProductAttribute, $quantity, $action)
    {
        $result = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'wk_mp_product_attribute WHERE `id_mp_product` = '.(int) $idMpProduct.' AND `id_ps_product_attribute` = '.(int) $idPsProductAttribute);

        if ($result) {
            if ($quantity) {
                $objProductAttr = new self($result['id_mp_product_attribute']);

                // ps has not prevented reorder for those product which quantity has become zero
                if ($objProductAttr->mp_quantity == 0) {
                    $quantity = 0;
                }
                if ($action == '1') { //qty decrease
                    $objProductAttr->mp_quantity = $objProductAttr->mp_quantity - $quantity;
                } else { //qty increase
                    $objProductAttr->mp_quantity = $objProductAttr->mp_quantity + $quantity;
                }
                $objProductAttr->update();

                //when combination created/updated then update mp product total quantity
                $currentQty = self::getMpProductQty($idMpProduct);
                if (!$currentQty) {
                    $currentQty = 0;
                }

                //Update Seller product qty
                self::updateMpProductQty($currentQty, $idMpProduct);

                //Send mail of out of Stock if quantity is less than or equal low stock level
                if ($objProductAttr->low_stock_alert) {
                    //Send only in combination product case
                    if ($objProductAttr->mp_quantity <= $objProductAttr->low_stock_threshold) {
                        //Send out of stock mail to seller
                        WkMpSellerProduct::sendMail($idMpProduct, 4, false, false, $result['id_mp_product_attribute']);
                    }
                }
            } else {
                return $result;
            }
        }
    }

    /**
     * Assign Ps Product with combinations if admin choosed combination checkbox
     *
     * @param int  $idProduct    Prestashop Product Id
     * @param int $idMpProduct   Seller Product Id
     *
     * @return bool
     */
    public static function assignProductCombinations($idProduct, $idMpProduct)
    {
        $objProduct = new Product($idProduct);
        $productAttributeData = $objProduct->getAttributesResume(Context::getContext()->language->id);
        if ($productAttributeData) {
            foreach ($productAttributeData as $productAttribute) {
                $idPsProductAttribute = $productAttribute['id_product_attribute'];
                $productAttributeQty = $productAttribute['quantity'];

                $objMpProductAttribute = new self();
                $objMpProductAttribute->id_mp_product = $idMpProduct;
                $objMpProductAttribute->id_ps_product_attribute = $idPsProductAttribute;
                $objMpProductAttribute->id_ps_product = $idProduct;
                $objMpProductAttribute->mp_reference = $productAttribute['reference'];
                $objMpProductAttribute->mp_ean13 = $productAttribute['ean13'];
                $objMpProductAttribute->mp_upc = $productAttribute['upc'];
                $objMpProductAttribute->mp_isbn = $productAttribute['isbn'];
                $objMpProductAttribute->mp_price = $productAttribute['price'];
                $objMpProductAttribute->mp_wholesale_price = $productAttribute['wholesale_price'];
                $objMpProductAttribute->mp_unit_price_impact = $productAttribute['unit_price_impact'];
                $objMpProductAttribute->mp_quantity = $productAttributeQty;
                $objMpProductAttribute->mp_weight = $productAttribute['weight'];
                $objMpProductAttribute->mp_minimal_quantity = $productAttribute['minimal_quantity'];
                $objMpProductAttribute->mp_available_date = $productAttribute['available_date'];
                $objMpProductAttribute->mp_default_on = $productAttribute['default_on'];

                if (_PS_VERSION_ >= '1.7.3.0') {
                    //Prestashop added this feature in PS V1.7.3.0 and above
                    $objMpProductAttribute->low_stock_threshold = $productAttribute['low_stock_threshold'];
                    $objMpProductAttribute->low_stock_alert = $productAttribute['low_stock_alert'];
                }

                //If combination activate/deactivate module is enabled and ps combination is deactive
                $active = 1;
                if (Module::isEnabled('wkcombinationcustomize')) {
                    $combiDataResult = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'wk_combination_status` WHERE `id_ps_product_attribute` = '.(int) $idPsProductAttribute);
                    if ($combiDataResult) {
                        $active = 0;
                    }
                }

                $objMpProductAttribute->active = $active;
                $objMpProductAttribute->save();

                $idMpProductAttribute = $objMpProductAttribute->id;

                $attributeList = array();
                $productAttributeList = Product::getAttributesParams($idProduct, $idPsProductAttribute);
                if ($productAttributeList) {
                    foreach ($productAttributeList as $attributeValue) {
                        $attributeList[] = array(
                            'id_mp_product_attribute' => (int) $idMpProductAttribute,
                            'id_ps_attribute' => (int) $attributeValue['id_attribute'],
                        );
                    }
                }

                WkMpProductAttributeCombination::insertDataIntoMpproductattributecombination($attributeList);

                WkMpProductAttributeShop::insertProductAttributeShopData($idMpProduct, $idMpProductAttribute, $productAttribute['price'], $productAttribute['wholesale_price'], $productAttribute['unit_price_impact'], $productAttribute['weight'], $productAttribute['minimal_quantity'], $productAttribute['available_date']);

                if ($productAttribute['default_on']) { //make default attribute
                    WkMpProductAttributeShop::changeAttributeShopDefaultValue($idMpProductAttribute, 1);
                }

                //Set Mp combination mp images according to ps images
                $idPsImages = Product::_getAttributeImageAssociations($idPsProductAttribute);
                if ($idPsImages) {
                    WkMpProductAttributeImage::setMpImages($idPsImages, $idMpProductAttribute);
                }

                //Set Mp Quantity
                WkMpStockAvailable::setMpQuantity($idMpProduct, $idMpProductAttribute, $productAttributeQty);
            }
        }

        return true;
    }

    /**
     * Get mp product id attribute by ps product id attribute
     *
     * @param int $idPsProductAttribute PS product id attribute
     *
     * @return array
     */
    public static function getMpIdProductAttributeByPsIdAttribute($idPsProductAttribute)
    {
        return Db::getInstance()->getValue('SELECT `id_mp_product_attribute` FROM `'._DB_PREFIX_.'wk_mp_product_attribute` WHERE `id_ps_product_attribute` = '.(int) $idPsProductAttribute);
    }

    /**
     * Set Mp product combination qty
     *
     * @param int  $idMpProductAttribute    Prestashop Product Combination Id
     * @param int $combinationQty   Combination qty
     *
     * @return bool
     */
    public static function setMpProductCombinationQuantity($idMpProductAttribute, $combinationQty)
    {
        if (($combinationQty == '') || (!Validate::isInt($combinationQty))) {
            die('0'); //If invalid value
        }

        $currentProductId = Tools::getValue('id_mp_product');
        if ($idMpProductAttribute && $currentProductId) {
            $objMpProductAttribute = new self($idMpProductAttribute);
            $idMpProduct = $objMpProductAttribute->id_mp_product;
            if ($idMpProduct == $currentProductId) { //If product matched
                $oldQuantity = $objMpProductAttribute->mp_quantity;

                //If seller change combination qty
                if ($oldQuantity != $combinationQty) {
                    $objMpProductAttribute->mp_quantity = $combinationQty;
                    if ($objMpProductAttribute->update()) {
                        //Set Mp combination qty
                        WkMpStockAvailable::setMpQuantity($idMpProduct, $idMpProductAttribute, $combinationQty);

                        //when combination qty updated then update mp product total quantity
                        $currentQty = self::getMpProductQty($idMpProduct);
                        if (!$currentQty) {
                            $currentQty = 0;
                        }

                        //Update Seller product qty
                        self::updateMpProductQty($currentQty, $idMpProduct);

                        if (Configuration::get('WK_MP_PRODUCT_UPDATE_ADMIN_APPROVE')
                        && ('updateproduct' == Tools::getValue('controller'))) {
                            //deactivate product if seller update product by change combi qty
                            WkMpSellerProduct::deactivateProductAfterUpdate($idMpProduct);
                        } else {
                            if ($objMpProductAttribute->id_ps_product && $objMpProductAttribute->id_ps_product_attribute) {
                                //Set Ps combination qty
                                StockAvailable::setQuantity($objMpProductAttribute->id_ps_product, $objMpProductAttribute->id_ps_product_attribute, $combinationQty);
                            }
                        }

                        //To manage staff log (changes add/update/delete)
                        if (Tools::getValue('controller') == 'managecombination') {
                            WkMpHelper::setStaffHook(Context::getContext()->customer->id, 'managecombination', $idMpProduct, 2); // 2 for Update action
                        }

                        die('1');
                    }

                    die('0'); //If error occured
                }
            } else {
                die('Something went wrong!');
            }
        }

        die('no change'); //ajax close
    }

    public static function copyMpProductCombination($originalMpProductId, $duplicateMpProductId, $imageMappingData)
    {
        $objOriginalMpProduct = new self();
        $productCombinations = $objOriginalMpProduct->getProductAttributes($originalMpProductId);
        if ($productCombinations) {
            foreach ($productCombinations as $combination) {
                $idMpProductAttributeOld = $combination['id_mp_product_attribute'];
                //Get product attribute combination
                $productAttributeList = array();
                $attributeCombinations = WkMpProductAttributeCombination::getPsAttributeIdForMpProduct(
                    $idMpProductAttributeOld
                );
                if ($attributeCombinations) {
                    foreach ($attributeCombinations as $attribute) {
                        $productAttributeList[] = $attribute['id_ps_attribute'];
                    }
                }

                //Get product attribute images
                $idImages = array();
                $attributeImages = WkMpProductAttributeImage::getAttributeImages($idMpProductAttributeOld);
                if ($attributeImages && $imageMappingData && is_array($imageMappingData)) {
                    foreach ($attributeImages as $image) {
                        if (isset($imageMappingData[$image['id_image']])) {
                            $idImages[] = $imageMappingData[$image['id_image']];
                        }
                    }
                }

                if (Configuration::get('WK_MP_PRODUCT_DUPLICATE_QUANTITY')) {
                    $wkMpCombinationQty = 0; //if zero quantity settings is enabled
                } else {
                    $wkMpCombinationQty = $combination['mp_quantity'];
                }

                self::saveMpProductCombination(
                    $duplicateMpProductId,
                    false,
                    $productAttributeList,
                    $combination['mp_reference'],
                    $combination['mp_ean13'],
                    $combination['mp_upc'],
                    $combination['mp_isbn'],
                    $combination['mp_price'],
                    $combination['mp_wholesale_price'],
                    $combination['mp_unit_price_impact'],
                    $wkMpCombinationQty,
                    $combination['mp_weight'],
                    $combination['mp_minimal_quantity'],
                    $combination['mp_available_date'],
                    $idImages,
                    $combination['low_stock_threshold'],
                    $combination['low_stock_alert']
                );
            }
        }

        self::copyMpAttributeImpact($originalMpProductId, $duplicateMpProductId);

        return true;
    }

    public static function copyMpAttributeImpact($originalMpProductId, $duplicateMpProductId)
    {
        $objMpImpact = new WkMpAttributeImpact();
        $impacts = $objMpImpact->getAttributesImpacts($originalMpProductId);
        if ($impacts) {
            $sql = 'INSERT INTO `'._DB_PREFIX_.'wk_mp_attribute_impact` (`id_mp_product`, `id_attribute`, `mp_weight`, `mp_price`) VALUES ';

            foreach ($impacts as $idAttribute => $impact) {
                $sql .= '('.(int)$duplicateMpProductId.', '.(int)$idAttribute.', '
                .(float)$impacts[$idAttribute]['weight'].', '.(float)$impacts[$idAttribute]['price'].'),';
            }

            $sql = substr_replace($sql, '', -1);
            $sql .= ' ON DUPLICATE KEY UPDATE `mp_price` = VALUES(mp_price), `mp_weight` = VALUES(mp_weight)';
            return Db::getInstance()->execute($sql);
        }
        return true;
    }
}
