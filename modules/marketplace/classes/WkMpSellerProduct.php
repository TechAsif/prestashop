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

class WkMpSellerProduct extends ObjectModel
{
    public $id_seller;
    public $id_ps_product;  // prestashop id product first time 0 when product is not created in ps
    public $id_mp_duplicate_product_parent;
    public $id_ps_shop;
    public $id_category;
    public $price;
    public $wholesale_price;
    public $unity;
    public $unit_price; //Here we stored direct unit price (input field value) but Ps hold unit_price_ratio
    public $id_tax_rules_group;
    public $on_sale = false;
    public $additional_shipping_cost = 0;
    public $quantity;
    public $minimal_quantity = 1;
    public $low_stock_threshold = null;
    public $low_stock_alert = false;
    public $active;
    public $status_before_deactivate; //Product Status before seller activated
    public $show_condition;
    public $condition;
    public $available_for_order = true;
    public $show_price = true;
    public $online_only;
    public $visibility;
    public $admin_assigned;  // if product assigned by admin to seller this will be 1
    public $width = 0;
    public $height = 0;
    public $depth = 0;
    public $weight = 0;
    public $reference;
    public $ean13;
    public $upc;
    public $isbn;
    public $out_of_stock = 2; //Use default behavior (Deny orders)
    public $available_date = '0000-00-00';
    public $ps_id_carrier_reference;
    public $admin_approved; //Approved by Admin or not
    public $additional_delivery_times = 1;
    public $date_add;
    public $date_upd;

    public $product_name;
    public $short_description;
    public $description;
    public $link_rewrite;
    public $available_now;
    public $available_later;
    public $meta_title;
    public $meta_description;
    public $delivery_in_stock;
    public $delivery_out_stock;

    public static $definition = array(
        'table' => 'wk_mp_seller_product',
        'primary' => 'id_mp_product',
        'multilang' => true,
        'fields' => array(
            'id_seller' => array('type' => self::TYPE_INT, 'required' => true),
            'id_ps_product' => array('type' => self::TYPE_INT, 'required' => true),
            'id_mp_duplicate_product_parent' => array('type' => self::TYPE_INT, 'required' => false),
            'id_ps_shop' => array('type' => self::TYPE_INT),
            'id_category' => array('type' => self::TYPE_INT),
            'price' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'wholesale_price' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'unity' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'unit_price' => array('type' => self::TYPE_FLOAT),
            'id_tax_rules_group' => array('type' => self::TYPE_INT),
            'on_sale' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'additional_shipping_cost' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'quantity' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'minimal_quantity' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'low_stock_threshold' => array('type' => self::TYPE_INT, 'allow_null' => true, 'validate' => 'isInt'),
            'low_stock_alert' => array('type' => self::TYPE_BOOL, 'allow_null' => true, 'validate' => 'isBool'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'status_before_deactivate' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'show_condition' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'condition' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'values' => array('new', 'used', 'refurbished'), 'default' => 'new'),
            'available_for_order' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'show_price' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'online_only' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'visibility' => array('type' => self::TYPE_STRING, 'validate' => 'isProductVisibility', 'values' => array('both', 'catalog', 'search', 'none'), 'default' => 'both'),
            'admin_assigned' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'width' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'height' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'depth' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'weight' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'reference' => array('type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 64),
            'ean13' => array('type' => self::TYPE_STRING, 'validate' => 'isEan13', 'size' => 13),
            'upc' => array('type' => self::TYPE_STRING, 'validate' => 'isUpc', 'size' => 12),
            'isbn' => array('type' => self::TYPE_STRING, 'validate' => 'isIsbn', 'size' => 32),
            'out_of_stock' => array('type' => self::TYPE_INT),
            'available_date' => array('type' => self::TYPE_DATE,  'validate' => 'isDateFormat'),
            'ps_id_carrier_reference' => array('type' => self::TYPE_HTML),
            'admin_approved' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'additional_delivery_times' =>  array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat', 'required' => false),

            /* Lang fields */
            'product_name' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'required' => true, 'size' => 128),
            'short_description' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
            'description' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'),
            'link_rewrite' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isLinkRewrite', 'required' => false, 'size' => 128),
            'available_now' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
            'available_later' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
            'meta_title' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 128),
            'meta_description' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
            'delivery_in_stock' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
            'delivery_out_stock' => array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255),
        ),
    );

    public function toggleStatus()
    {
        return true;
    }

    public function delete()
    {
        if (!$this->deleteSellerProduct($this->id)
            || !$this->deleteCombinationAssociations($this->id)
            || !$this->deleteMpProductFeature($this->id)
            || !parent::delete()) {
            return false;
        }

        return true;
    }

    /**
     * Delete seller product from all tables if product is activated.
     *
     * @param int $mpIdProduct seller's product id
     *
     * @return bool
     */
    public function deleteSellerProduct($mpIdProduct)
    {
        $objMpProduct = new self($mpIdProduct);
        //This hook must be call before delete of mp product details otherwise we can't check mp product was exist or not
        Hook::exec('actionMpProductDelete', array('id_mp_product' => (int) $mpIdProduct));
        if ($objMpProduct->id) {
            //if activated

            if (!$objMpProduct->admin_assigned && $objMpProduct->id_ps_product) {
                //delete only seller created products not the admin assigned products from catalog list
                $objProduct = new Product($objMpProduct->id_ps_product);
                $objProduct->delete();
            }

            //mail to admin or seller on mp product delete
            if (Configuration::get('WK_MP_MAIL_PRODUCT_DELETE')) {
                $mpProduct = WkMpSellerProduct::getSellerProductByIdProduct($mpIdProduct, Configuration::get('PS_LANG_DEFAULT'));
                if ($mpProduct) {
                    $sellerDetail = WkMpSeller::getSeller($mpProduct['id_seller'], Configuration::get('PS_LANG_DEFAULT'));
                    if ($sellerDetail) {
                        $productName = $mpProduct['product_name'];
                        $sellerName = $sellerDetail['seller_firstname'].' '.$sellerDetail['seller_lastname'];
                        $shopName = $sellerDetail['shop_name'];
                        $sellerPhone = $sellerDetail['phone'];
                        $sellerEmail = $sellerDetail['business_email'];

                        if (Tools::getValue('controller') == 'productlist'
                        || Tools::getValue('controller') == 'updateproduct') {
                            //mail to admin if seller delete product from product list page
                            $mailLangId = Configuration::get('PS_LANG_DEFAULT');
                            WkMpSellerProduct::mailOnProductDelete($productName, $sellerName, $sellerPhone, $shopName, $sellerEmail, $mailLangId, 'admin');
                        } else {
                            // mail to seller if admin delete or delete by other way
                            $mailLangId = $sellerDetail['default_lang'];
                            WkMpSellerProduct::mailOnProductDelete($productName, $sellerName, $sellerPhone, $shopName, $sellerEmail, $mailLangId, 'seller');
                        }
                    }
                }
            }
        }

        $deleteMpCategory = Db::getInstance()->delete('wk_mp_seller_product_category', 'id_seller_product = '.(int) $mpIdProduct);

        if (!$deleteMpCategory
            || !self::deleteSellerProductImage($mpIdProduct)) {
            return false;
        }

        return true;
    }

    public function deleteCombinationAssociations($mpIdProduct)
    {
        $mpIdProductAttributesData = WkMpProductAttribute::getProductAttributesIds($mpIdProduct);
        if ($mpIdProductAttributesData) {
            foreach ($mpIdProductAttributesData as $mpIdProductAttributeVal) {
                $idMpProductAttribute = $mpIdProductAttributeVal['id_mp_product_attribute'];
                $objProductAttribute = new WkMpProductAttribute($idMpProductAttribute);
                $objProductAttribute->deleteAssociations();

                unset($objProductAttribute);
            }
        }

        return Db::getInstance()->delete('wk_mp_product_attribute', 'id_mp_product = '.(int) $mpIdProduct);
    }

    public function deleteMpProductFeature($mpIdProduct)
    {
        WkMpProductFeature::deleteProductFeature($mpIdProduct);

        return true;
    }

    /**
     * Delete seller product image.
     *
     * @param int $mpIdProduct seller product id
     * @param int $mpIDImage   seller image id
     * @param int $idPsImage   prestashop image id
     *
     * @return bool
     */
    public static function deleteSellerProductImage($mpIdProduct = false, $mpIDImage = false, $idPsImage = false)
    {
        if ($mpIdProduct) {
            $productImages = self::getSellerProductImages($mpIdProduct);
            if ($productImages) {
                foreach ($productImages as $image) {
                    if (file_exists(_PS_MODULE_DIR_.'marketplace/views/img/product_img/'.$image['seller_product_image_name'])) {
                        if (unlink(_PS_MODULE_DIR_.'marketplace/views/img/product_img/'.$image['seller_product_image_name'])) {
                            if (!Db::getInstance()->delete('wk_mp_seller_product_image', 'seller_product_id = '.(int) $mpIdProduct)) {
                                return false;
                            }
                        }
                    }
                }
            }
        } elseif ($mpIDImage) {
            if (file_exists(_PS_MODULE_DIR_.'marketplace/views/img/product_img/'.$mpIDImage)) {
                if (unlink(_PS_MODULE_DIR_.'marketplace/views/img/product_img/'.$mpIDImage)) {
                    if (!Db::getInstance()->delete('wk_mp_seller_product_image', 'seller_product_image_name = '.pSQL($mpIDImage))) {
                        return false;
                    }
                }
            }
        } elseif ($idPsImage) {
            $objMpImage = new WkMpSellerProductImage();
            $mpImageDetails = $objMpImage->getProductImageByPsIdImage($idPsImage);
            if ($mpImageDetails) {
                if (file_exists(_PS_MODULE_DIR_.'marketplace/views/img/product_img/'.$mpImageDetails['seller_product_image_name'])) {
                    if (unlink(_PS_MODULE_DIR_.'marketplace/views/img/product_img/'.$mpImageDetails['seller_product_image_name'])) {
                        if (!Db::getInstance()->delete('wk_mp_seller_product_image', 'id_mp_product_image = '.(int) $mpImageDetails['id_mp_product_image'])) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Duplicate seller product
     *
     * @param int $originalMpProductId - Seller Original Product ID
     * @param int $targetSellerId            - Seller ID for which product want to duplicate
     *
     * @return array/bool containing seller's new product information
     */
    public function duplicateSellerProduct($originalMpProductId, $targetSellerId = false)
    {
        Hook::exec('actionBeforeDuplicateMPProduct', array('id_mp_product' => $originalMpProductId));

        $objOriginalMpProduct = new self($originalMpProductId);
        if (Validate::isLoadedObject($objOriginalMpProduct)) {
            if (!$targetSellerId) {
                //If targetSellerId is not defined then create duplicate product for same seller
                $targetSellerId = $objOriginalMpProduct->id_seller;
            }

            if ($duplicateMpProductId = $this->copyMpProduct($originalMpProductId)) {
                $objMpProduct = new self($duplicateMpProductId);
                $objMpProduct->id_seller = $targetSellerId;
                $objMpProduct->id_ps_product = 0;
                $objMpProduct->id_mp_duplicate_product_parent = $originalMpProductId;
                $objMpProduct->admin_assigned = 0;
                $objMpProduct->active = 0;
                $objMpProduct->admin_approved = 0;
                $objMpProduct->status_before_deactivate = 0;
                $nameDefaultLang = '';
                foreach (Language::getLanguages(false) as $language) {
                    if (Configuration::get('WK_MP_PRODUCT_DUPLICATE_TITLE', $language['id_lang'])
                    && Validate::isCatalogName(Configuration::get('WK_MP_PRODUCT_DUPLICATE_TITLE', $language['id_lang']))) {
                        $wkNamePattern = trim(Configuration::get('WK_MP_PRODUCT_DUPLICATE_TITLE', $language['id_lang'])).' %s';
                    } else {
                        $wkNamePattern = '%s';
                    }

                    if (isset($objMpProduct->product_name[$language['id_lang']])) {
                        $oldName = $objMpProduct->product_name[$language['id_lang']];
                        if (!preg_match('/^'.str_replace('%s', '.*', preg_quote($wkNamePattern, '/').'$/'), $oldName)) {
                            $newName = sprintf($wkNamePattern, $oldName);
                            if (mb_strlen($newName, 'UTF-8') <= 127) {
                                $objMpProduct->product_name[$language['id_lang']] = $newName;
                            }
                        }

                        if (Configuration::get('PS_LANG_DEFAULT') == $language['id_lang']) {
                            $nameDefaultLang = $objMpProduct->product_name[$language['id_lang']];
                        }
                    }
                }
                if ($objMpProduct->update()) {
                    // if default approve ON & old product is approved by admin
                    if (!Configuration::get('WK_MP_PRODUCT_ADMIN_APPROVE') && $objOriginalMpProduct->id_ps_product) {
                        // creating ps_product when admin setting is default
                        $idPsProduct = $objMpProduct->addSellerProductToPs(
                            $duplicateMpProductId,
                            $objOriginalMpProduct->active
                        );
                        if ($idPsProduct) {
                            //save ps_product
                            $objMpProduct->id_ps_product = $idPsProduct;
                            $objMpProduct->active = $objOriginalMpProduct->active;
                            $objMpProduct->admin_approved = 1;
                            $objMpProduct->status_before_deactivate = 1;
                            if ($objMpProduct->update()) {
                                Hook::exec(
                                    'actionToogleMPProductCreateStatus',
                                    array(
                                        'id_product' => $idPsProduct,
                                        'id_mp_product' => $duplicateMpProductId,
                                        'active' => 1
                                    )
                                );
                            }
                        }

                        //If seller product default active approval is ON then mail to seller of product activation
                        self::sendMail($duplicateMpProductId, 1, 1);
                    }

                    if (Configuration::get('WK_MP_MAIL_ADMIN_PRODUCT_ADD')) {
                        //Mail to admin on product add by seller
                        $sellerDetail = WkMpSeller::getSeller($targetSellerId, Configuration::get('PS_LANG_DEFAULT'));
                        if ($sellerDetail) {
                            $sellerName = $sellerDetail['seller_firstname'].' '.$sellerDetail['seller_lastname'];
                            $objMpProduct->mailToAdminOnProductAdd(
                                $nameDefaultLang,
                                $sellerName,
                                $sellerDetail['phone'],
                                $sellerDetail['shop_name'],
                                $sellerDetail['business_email']
                            );
                        }
                    }

                    Hook::exec('actionAfterAddMPProduct', array('id_mp_product' => $originalMpProductId));

                    Hook::exec(
                        'actionAfterDuplicateMPProduct',
                        array(
                            'id_mp_product_original' => $originalMpProductId,
                            'id_mp_product_duplicate' => $duplicateMpProductId
                        )
                    );
                    return $duplicateMpProductId;
                }
            }
        }
        return false;
    }

    public function copyMpProduct($originalMpProductId)
    {
        $objOriginalProduct = new self($originalMpProductId);
        if (Validate::isLoadedObject($objOriginalProduct)) {
            $objDuplicateProduct = $objOriginalProduct->duplicateObject();
            if (Validate::isLoadedObject($objDuplicateProduct)) {
                if ($duplicateMpProductId = $objDuplicateProduct->id) {
                    $objMpSellerProduct = new self($duplicateMpProductId);
                    $objMpSellerProduct->id_seller = 0;
                    $objMpSellerProduct->id_ps_product = 0;
                    $objMpSellerProduct->admin_assigned = 0;
                    $objMpSellerProduct->active = 0;
                    $objMpSellerProduct->admin_approved = 0;
                    $objMpSellerProduct->status_before_deactivate = 0;
                    if (Configuration::get('WK_MP_PRODUCT_DUPLICATE_QUANTITY')) { //if zero quantity settings is enabled
                        $objMpSellerProduct->quantity = 0;
                    }
                    if ($objMpSellerProduct->update()) {
                        $imageMappingData = WkMpSellerProductImage::copyMpProductImages(
                            $originalMpProductId,
                            $duplicateMpProductId,
                            $objOriginalProduct->active
                        );
                        if ($imageMappingData
                        && WkMpSellerProductCategory::copyMpProductCategories($originalMpProductId, $duplicateMpProductId)
                        && WkMpProductFeature::copyMpProductFeatures($originalMpProductId, $duplicateMpProductId)
                        && WkMpProductAttribute::copyMpProductCombination($originalMpProductId, $duplicateMpProductId, $imageMappingData)
                        ) {
                            return $duplicateMpProductId;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get seller's product with prestashop product object and image.
     *
     * @param int    $idSeller Seller ID
     * @param int    $idPsShop prestashop shop ID
     * @param object $objProd  If you pass true then prestashop product object will be added in objProduct
     * @param bool   $active   true/false
     * @param bool   $idLang   pass language specific id if you want
     *
     * @return array/bool containing seller's product information
     */
    public static function getSellerProductWithPs($idSeller, $objProd = false, $active = 1, $idPsShop = false, $idLang = false)
    {
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        if (!$idPsShop) {
            $idPsShop = Context::getContext()->shop->id;
        }
        $mpProducts = Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_product` mpsp
            JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product` = mpsp.`id_ps_product`)
            JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`)
            WHERE mpsp.`id_seller` = '.(int) $idSeller.'
            AND pl.`id_shop` = '.(int) $idPsShop.'
            AND pl.`id_lang` = '.(int) $idLang.'
            AND p.`active` = '.(int) $active.'
            AND mpsp.`id_ps_product` != 0
            ORDER BY p.`date_add` DESC LIMIT 10'
        );

        if ($mpProducts && $objProd) {
            foreach ($mpProducts as $key => $product) {
                $objProduct = new Product($product['id_product'], true, $idLang);
                $mpProducts[$key]['objproduct'] = $objProduct;
                $mpProducts[$key]['lang_iso'] = Context::getContext()->language->iso_code;
                $mpProducts[$key]['price'] = $objProduct->price;
                $cover = Product::getCover($product['id_product']);
                if ($cover) {
                    $mpProducts[$key]['image'] = $product['id_product'].'-'.$cover['id_image'];
                } else {
                    $mpProducts[$key]['image'] = 0;
                }
            }

            return $mpProducts;
        } else {
            return $mpProducts;
        }

        return false;
    }

    /**
     * Get converted price according to context currency.
     *
     * @param float $price       price/amount
     * @param int   $id_currency if you want to specify currency ID or it will take context currency
     *
     * @return float/false
     */
    public static function getConvertedPrice($price, $idCurrency = false)
    {
        if (!$idCurrency) {
            $idCurrency = Context::getContext()->currency->id;
        }

        if ($price != '') {
            $objCurreny = Currency::getCurrency($idCurrency);
            $conversionRate = $objCurreny['conversion_rate'];

            return ($price * $conversionRate);
        }

        return false;
    }

    /**
     * Get seller's product images with seller id product.
     *
     * @param int $mpIdProduct Seller Product ID
     *
     * @return array/bool containing product images
     */
    public static function getSellerProductImages($mpIdProduct)
    {
        $productImages = Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_product_image`
            WHERE `seller_product_id` = '.(int) $mpIdProduct
        );

        if ($productImages && !empty($productImages)) {
            return $productImages;
        }

        return false;
    }

    /**
     * Get seller's product whether added into prestashop or not.
     *
     * @param int  $idSeller Seller ID
     * @param bool $idLang   Language id
     * @param bool $active   activated or not
     *
     * @return array
     */
    public static function getSellerProduct($idSeller = false, $active = 'all', $idLang = false, $orderby = false, $orderway = false, $start_point = 0, $limit_point = 10000000)
    {
        if (!$idLang) {
            $idLang = Configuration::get('PS_LANG_DEFAULT');
        }

        if (!$orderway) {
            $orderway = 'desc';
        }

        $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_product` msp
                LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller_product_lang` mspl ON (mspl.id_mp_product = msp.id_mp_product)
                WHERE mspl.`id_lang` = '.(int) $idLang;

        if ($idSeller) {
            $sql .= ' AND msp.`id_seller` = '.(int) $idSeller;
        }

        if ($active === true || $active === 1) {
            $sql .= ' AND msp.`active` = 1 ';
        } elseif ($active === false || $active === 0) {
            $sql .= ' AND msp.`active` = 0 ';
        }

        if (!$orderby) {
            $sql .= ' ORDER BY msp.`id_mp_product` '.pSQL($orderway);
        } elseif ($orderby == 'name') {
            $sql .= ' ORDER BY mspl.`product_name` '.pSQL($orderway);
        } else {
            $sql .= ' ORDER BY msp.`'.$orderby.'` '.pSQL($orderway);
        }
        $sql .= ' LIMIT '.$start_point.', '.$limit_point;

        $mpProducts = Db::getInstance()->executeS($sql);

        Hook::exec(
            'actionSellerProductsListResultModifier',
            array('seller_product_list' => &$mpProducts)
        );

        if ($mpProducts && !empty($mpProducts)) {
            return $mpProducts;
        }

        return false;
    }

    /**
     * Get Seller Product By Using Seller Id product.
     *
     * @param int  $idMpProduct Seller Id Product
     * @param bool $idLang      language ID
     *
     * @return array/bool array containing seller's product
     */
    public static function getSellerProductByIdProduct($idMpProduct, $idLang = false)
    {
        if (!$idLang) {
            $idLang = Configuration::get('PS_LANG_DEFAULT');
        }

        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_product` msp
                JOIN `'._DB_PREFIX_.'wk_mp_seller_product_lang` mspl ON (mspl.id_mp_product = msp.id_mp_product)
                WHERE msp.`id_mp_product` = '.(int) $idMpProduct.' AND mspl.`id_lang` = '.(int) $idLang
        );
    }

    /**
     * Add seller's created product into prestashop catalog.
     *
     * @param int  $mpIdProduct Seller Id Product
     * @param bool $active      true/false
     */
    public function addSellerProductToPs($mpIdProduct, $active)
    {
        Hook::exec('actionBeforeAddSellerProductToPs', array('mp_id_product' => $mpIdProduct));

        $mpSellerProduct = new self($mpIdProduct);
        $productInfo = (array) $mpSellerProduct;

        $quantity = (int) $productInfo['quantity'];
        $categoryID = (int) $productInfo['id_category'];

        // Add Product
        $product = new Product();

        $product->name = array();
        $product->description = array();
        $product->description_short = array();
        $product->meta_title = array();
        $product->meta_description = array();
        $product->link_rewrite = array();
        $product->available_now = array();
        $product->available_later = array();
        $product->delivery_in_stock = array();
        $product->delivery_out_stock = array();

        foreach (Language::getLanguages(false) as $lang) {
            $product->name[$lang['id_lang']] = $productInfo['product_name'][$lang['id_lang']];
            $product->description[$lang['id_lang']] = $productInfo['description'][$lang['id_lang']];
            $product->description_short[$lang['id_lang']] = $productInfo['short_description'][$lang['id_lang']];

            $product->meta_title[$lang['id_lang']] = $productInfo['meta_title'][$lang['id_lang']];
            $product->meta_description[$lang['id_lang']] = $productInfo['meta_description'][$lang['id_lang']];
            $product->link_rewrite[$lang['id_lang']] = $productInfo['link_rewrite'][$lang['id_lang']];

            $product->available_now[$lang['id_lang']] = $productInfo['available_now'][$lang['id_lang']];
            $product->available_later[$lang['id_lang']] = $productInfo['available_later'][$lang['id_lang']];

            if (_PS_VERSION_ >= '1.7.3.0') {
                //Prestashop added this feature in PS V1.7.3.0 and above
                $product->delivery_in_stock[$lang['id_lang']] = $productInfo['delivery_in_stock'][$lang['id_lang']];
                $product->delivery_out_stock[$lang['id_lang']] = $productInfo['delivery_out_stock'][$lang['id_lang']];
            }
        }

        $product->id_shop_default = Context::getContext()->shop->id;
        $product->id_category_default = $categoryID;
        $product->active = $active;
        $product->indexed = 1;
        $product->show_condition = $productInfo['show_condition'];
        $product->condition = $productInfo['condition'];

        $product->price = $productInfo['price'];
        $product->wholesale_price = $productInfo['wholesale_price'];
        $product->unit_price = $productInfo['unit_price']; //ps automatically get unit_price_ratio
        $product->unity = $productInfo['unity'];
        $product->on_sale = $productInfo['on_sale'];

        $product->height = $productInfo['height'];
        $product->width = $productInfo['width'];
        $product->depth = $productInfo['depth'];
        $product->weight = $productInfo['weight'];
        $product->additional_shipping_cost = $productInfo['additional_shipping_cost'];

        $product->minimal_quantity = $productInfo['minimal_quantity'];
        $product->out_of_stock = $productInfo['out_of_stock'];
        $product->available_date = $productInfo['available_date'];
        if (_PS_VERSION_ >= '1.7.3.0') {
            //Prestashop added this feature in PS V1.7.3.0 and above
            $product->additional_delivery_times = $productInfo['additional_delivery_times'];
            $product->low_stock_threshold = $productInfo['low_stock_threshold'];
            $product->low_stock_alert = $productInfo['low_stock_alert'];
        }

        //Product Visibility Options
        $product->available_for_order = $productInfo['available_for_order'];
        $product->show_price = $productInfo['show_price'];
        $product->online_only = $productInfo['online_only'];
        $product->visibility = $productInfo['visibility'];

        $product->reference = $productInfo['reference'];
        $product->ean13 = $productInfo['ean13'];
        $product->isbn = $productInfo['isbn'];
        $product->upc = $productInfo['upc'];

        $idTaxRulesGroup = $productInfo['id_tax_rules_group'];
        $objTaxRule = new TaxRulesGroup($idTaxRulesGroup);
        if ($objTaxRule->active) {
            $product->id_tax_rules_group = $idTaxRulesGroup;
        } else {
            $product->id_tax_rules_group = 0;
        }

        $product->save();
        $psIdProduct = $product->id;

        foreach (Language::getLanguages(false) as $lang) {
            Search::indexation($productInfo['link_rewrite'][$lang['id_lang']], $psIdProduct);
        }

        if ($psIdProduct > 0) {
            if ($categoryID > 0) {
                $categoryIds = WkMpSellerProductCategory::getMultipleCategories($mpIdProduct);
                $product->addToCategories($categoryIds);
            }

            //Set product quantity on PS
            StockAvailable::updateQuantity($psIdProduct, null, $quantity);

            //Entry for deny orders, allow orders or Default
            StockAvailable::setProductOutOfStock($psIdProduct, $productInfo['out_of_stock']);

            $this->updatePsProductImage($mpIdProduct, $psIdProduct);

            if ($productInfo['ps_id_carrier_reference']) {
                $product->setCarriers(unserialize($productInfo['ps_id_carrier_reference']));
            }

            //Save combination when product is going to active at first time
            $objMpProductAttribute = new WkMpProductAttribute();
            $objMpProductAttribute->updateMpProductCombinationToPs($mpIdProduct, $psIdProduct);

            // create/update product features into prestashop
            WkMpProductFeature::processProductFeatureToPS($mpIdProduct, $psIdProduct);

            Hook::exec(
                'actionAfterAddSellerProductToPs',
                array(
                    'mp_id_product' => $mpIdProduct,
                    'ps_id_product' => $psIdProduct,
                )
            );

            return $psIdProduct;
        }

        return false;
    }

    /**
     * Get seller's product whether added into prestashop or not.
     *
     * @param int  $idSeller Seller ID
     * @param bool $idLang   Language id
     * @param bool $active   activated or not
     *
     * @return array
     */
    public static function getSellerOffersProduct($idSeller = false, $active = 'all', $idLang = false, $orderby = false, $orderway = false, $start_point = 0, $limit_point = 10000000)
    {
        if (!$idLang) {
            $idLang = Configuration::get('PS_LANG_DEFAULT');
        }

        if (!$orderway) {
            $orderway = 'desc';
        }

        $sql = 'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_product` msp
                LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller_product_lang` mspl ON (mspl.id_mp_product = msp.id_mp_product)
                WHERE mspl.`id_lang` = '.(int) $idLang;

        $sqltest = 'SELECT * FROM `'._DB_PREFIX_.'specific_price` 
                WHERE id_group = 4';

         $sqltestfinal =       'SELECT  *  FROM `ps_specific_price` sp inner JOIN ps_product_lang pl on sp.id_product = pl.id_product inner JOIN ps_wk_mp_seller_product on ps_wk_mp_seller_product.id_ps_product = sp.id_product   WHERE sp.id_group = 4 and ps_wk_mp_seller_product.active = 1 GROUP by sp.id_product';
                 //inner JOIN ps_wk_mp_seller_product on ps_wk_mp_seller_product.id_ps_product =


        if ($idSeller) {
            $sql .= ' AND msp.`id_seller` = '.(int) $idSeller;
        }

        if ($active === true || $active === 1) {
            $sql .= ' AND msp.`active` = 1 ';
        } elseif ($active === false || $active === 0) {
            $sql .= ' AND msp.`active` = 0 ';
        }

        if (!$orderby) {
            $sql .= ' ORDER BY msp.`id_mp_product` '.pSQL($orderway);
        } elseif ($orderby == 'name') {
            $sql .= ' ORDER BY mspl.`product_name` '.pSQL($orderway);
        } else {
            $sql .= ' ORDER BY msp.`'.$orderby.'` '.pSQL($orderway);
        }
        $sql .= ' LIMIT '.$start_point.', '.$limit_point;

        $mpProducts = Db::getInstance()->executeS($sqltestfinal);

        Hook::exec(
            'actionSellerProductsListResultModifier',
            array('seller_product_list' => &$mpProducts)
        );

        if ($mpProducts && !empty($mpProducts)) {
            return $mpProducts;
        }

        return false;
    }

    public static function getAllSpecificProduct(){

       

        //$sqltestfinal =  'SELECT * FROM `ps_specific_price` sp inner JOIN ps_product pp on sp.id_product = pp.id_product WHERE sp.id_group = 4 order by sp.id_product,sp.reduction';

        $sqltestfinal =  'SELECT * FROM `ps_specific_price` sp inner JOIN ps_product pp on sp.id_product = pp.id_product inner JOIN ps_wk_mp_seller_product on pp.id_product = ps_wk_mp_seller_product.id_ps_product WHERE sp.id_group = 4 and ps_wk_mp_seller_product.active = 1 order by sp.id_product,sp.reduction';



       $mpProducts = Db::getInstance()->executeS($sqltestfinal);

       
       if ($mpProducts && !empty($mpProducts)) {
           return $mpProducts;
       }

       return false;

   }

    /**
     * Update Seller Product into prestashop catalog when seller change something in thier product.
     *
     * @param int  $mpIdProduct Seller Id Product
     * @param bool $active      true/false
     *
     * @return bool true/false
     */
    public function updateSellerProductToPs($mpIdProduct, $active)
    {
        $mpSellerProduct = new self($mpIdProduct);
        $productInfo = (array) $mpSellerProduct;

        $quantity = (int) $productInfo['quantity'];
        $categoryID = (int) $productInfo['id_category'];
        $idPsShop = (int) $productInfo['id_ps_shop'];

        // Update Product
        $product = new Product($productInfo['id_ps_product']);

        $product->name = array();
        $product->description = array();
        $product->description_short = array();
        $product->meta_title = array();
        $product->meta_description = array();
        $product->link_rewrite = array();
        $product->available_now = array();
        $product->available_later = array();
        $product->delivery_in_stock = array();
        $product->delivery_out_stock = array();

        foreach (Language::getLanguages(false) as $lang) {
            $product->name[$lang['id_lang']] = $productInfo['product_name'][$lang['id_lang']];
            $product->description[$lang['id_lang']] = $productInfo['description'][$lang['id_lang']];
            $product->description_short[$lang['id_lang']] = $productInfo['short_description'][$lang['id_lang']];

            $product->meta_title[$lang['id_lang']] = $productInfo['meta_title'][$lang['id_lang']];
            $product->meta_description[$lang['id_lang']] = $productInfo['meta_description'][$lang['id_lang']];
            $product->link_rewrite[$lang['id_lang']] = $productInfo['link_rewrite'][$lang['id_lang']];

            $product->available_now[$lang['id_lang']] = $productInfo['available_now'][$lang['id_lang']];
            $product->available_later[$lang['id_lang']] = $productInfo['available_later'][$lang['id_lang']];

            if (_PS_VERSION_ >= '1.7.3.0') {
                //Prestashop added this feature in PS V1.7.3.0 and above
                $product->delivery_in_stock[$lang['id_lang']] = $productInfo['delivery_in_stock'][$lang['id_lang']];
                $product->delivery_out_stock[$lang['id_lang']] = $productInfo['delivery_out_stock'][$lang['id_lang']];
            }
        }

        $product->id_shop_default = Context::getContext()->shop->id;
        $product->id_category_default = $categoryID;
        $product->active = $active;
        $product->indexed = 1;
        $product->show_condition = $productInfo['show_condition'];
        $product->condition = $productInfo['condition'];

        $product->price = $productInfo['price'];
        $product->wholesale_price = $productInfo['wholesale_price'];
        $product->unit_price = $productInfo['unit_price']; //ps automatically get unit_price_ratio
        $product->unity = $productInfo['unity'];
        $product->on_sale = $productInfo['on_sale'];

        $product->height = $productInfo['height'];
        $product->width = $productInfo['width'];
        $product->depth = $productInfo['depth'];
        $product->weight = $productInfo['weight'];
        $product->additional_shipping_cost = $productInfo['additional_shipping_cost'];

        $product->minimal_quantity = $productInfo['minimal_quantity'];
        $product->out_of_stock = $productInfo['out_of_stock'];
        $product->available_date = $productInfo['available_date'];
        if (_PS_VERSION_ >= '1.7.3.0') {
            //Prestashop added this feature in PS V1.7.3.0 and above
            $product->additional_delivery_times = $productInfo['additional_delivery_times'];
            $product->low_stock_threshold = $productInfo['low_stock_threshold'];
            $product->low_stock_alert = $productInfo['low_stock_alert'];
        }

        //Product Visibility Options
        $product->available_for_order = $productInfo['available_for_order'];
        $product->show_price = $productInfo['show_price'];
        $product->online_only = $productInfo['online_only'];
        $product->visibility = $productInfo['visibility'];

        $product->reference = $productInfo['reference'];
        $product->ean13 = $productInfo['ean13'];
        $product->isbn = $productInfo['isbn'];
        $product->upc = $productInfo['upc'];

        $idTaxRulesGroup = $productInfo['id_tax_rules_group'];
        $objTaxRule = new TaxRulesGroup($idTaxRulesGroup);
        if ($objTaxRule->active) {
            $product->id_tax_rules_group = $idTaxRulesGroup;
        } else {
            $product->id_tax_rules_group = 0;
        }

        $product->save();
        $psIdProduct = $product->id;

        foreach (Language::getLanguages(false) as $lang) {
            Search::indexation($productInfo['link_rewrite'][$lang['id_lang']], $psIdProduct);
        }

        if ($psIdProduct > 0) {
            if ($categoryID > 0) {
                $categoryIds = WkMpSellerProductCategory::getMultipleCategories($mpIdProduct);
                $product->updateCategories($categoryIds);
            }

            //Set product quantity on PS
            StockAvailable::setQuantity($psIdProduct, 0, $quantity, $idPsShop);

            //Entry for deny orders, allow orders or Default
            StockAvailable::setProductOutOfStock($psIdProduct, $productInfo['out_of_stock']);

            $this->updatePsProductImage($mpIdProduct, $psIdProduct);

            if ($productInfo['ps_id_carrier_reference']) {
                $product->setCarriers(unserialize($productInfo['ps_id_carrier_reference']));
            } else {
                $this->removeCarriers($productInfo['id_ps_product']);
            }

            if ($active) {
                // create/update product features into prestashop
                WkMpProductFeature::processProductFeatureToPS($mpIdProduct, $psIdProduct);
            }

            return $psIdProduct;
        }

        return false;
    }

    /**
     * Remove carriers from the prestashop table.
     *
     * @param int $id_product Prestashop Id Product
     *
     * @return bool
     */
    public function removeCarriers($id_product)
    {
        return Db::getInstance()->delete('product_carrier', 'id_product='.(int) $id_product);
    }

    /**
     * Update prestashop product's images.
     *
     * @param int $mpIdProduct Seller Id Product
     * @param int $psIdProduct Prestashop Id Product
     *
     * @return bool true/false
     */
    public function updatePsProductImage($mpIdProduct, $psIdProduct)
    {
        $imageList = self::getInactiveProductImageByIdProduct($mpIdProduct);
        if ($imageList) {
            $imageDir = _PS_MODULE_DIR_.'marketplace/views/img/product_img';
            foreach ($imageList as $image) {
                //If inactive product has cover image then delete cover from all active images
                if ($image['cover']) {
                    Image::deleteCover((int) $psIdProduct);
                }

                $oldPath = $imageDir.'/'.$image['seller_product_image_name'];
                $objImage = new Image();
                $objImage->id_product = $psIdProduct;
                $objImage->position = $image['position'];
                $objImage->cover = $image['cover'];
                $objImage->add();
                $imageId = $objImage->id;
                $newPath = $objImage->getPathForCreation();
                $imagesTypes = ImageType::getImagesTypes('products');

                if ($imagesTypes) {
                    foreach ($imagesTypes as $imageType) {
                        ImageManager::resize(
                            $oldPath,
                            $newPath.'-'.Tools::stripslashes($imageType['name']).'.'.$objImage->image_format,
                            $imageType['width'],
                            $imageType['height'],
                            $objImage->image_format
                        );
                    }
                }

                ImageManager::resize($oldPath, $newPath.'.'.$objImage->image_format);
                Hook::exec(
                    'actionWatermark',
                    array(
                        'id_image' => $imageId,
                        'id_product' => $psIdProduct
                    )
                );
                Hook::exec(
                    'actionPsMpImageMap',
                    array(
                        'mp_product_id' => $mpIdProduct,
                        'mp_id_image' => $image['id_mp_product_image'],
                        'ps_id_product' => $psIdProduct,
                        'ps_id_image' => $imageId
                    )
                );
                //updating mp_product_image status ...
                WkMpSellerProductImage::updateStatusById($image['id_mp_product_image'], 1, $imageId);
            }
        }
    }

    /**
     * Assign prestashop product to Seller.
     *
     * @param int $id_product  Prestashop Id Product
     * @param int $id_customer Prestashop Id Customer
     *
     * @return int/boolean Seller ID product or false
     */
    public function assignProductToSeller($idProduct, $idCustomer)
    {
        $mpSeller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);

        if (!$mpSeller) {
            return false;
        }

        $idSeller = $mpSeller['id_seller'];

        //If this prestashop product is not assigned to any seller OR not mapped with any seller
        if (!WkMpSellerProduct::getSellerProductByPsIdProduct($idProduct)) {
            //get ps product details
            $objProduct = new Product($idProduct);
            //Insert into wk_mp_seller_product table
            $objSellerProduct = new self();
            $objSellerProduct->id_seller = $idSeller;
            $objSellerProduct->id_ps_product = $idProduct;

            $productQty = StockAvailable::getQuantityAvailableByProduct($idProduct);
            $objSellerProduct->quantity = $productQty;
            $objSellerProduct->minimal_quantity = $objProduct->minimal_quantity;
            $objSellerProduct->id_category = $objProduct->id_category_default;
            $objSellerProduct->id_tax_rules_group = $objProduct->id_tax_rules_group;
            $objSellerProduct->active = 1;
            $objSellerProduct->admin_approved = 1;
            $objSellerProduct->status_before_deactivate = 1;
            $objSellerProduct->admin_assigned = 1;  // if product assigned by admin to seller
            $objSellerProduct->id_ps_shop = Context::getContext()->shop->id;

            //Pricing
            $objSellerProduct->price = $objProduct->price;
            $objSellerProduct->wholesale_price = $objProduct->wholesale_price;
            $objSellerProduct->unity = $objProduct->unity;
            if ($objProduct->unit_price_ratio != '0.000000' || $objProduct->unit_price_ratio != '0') {
                //By default presta save 0.000000 for unit_price_ratio
                $objSellerProduct->unit_price = $objProduct->price / $objProduct->unit_price_ratio;
            }
            $objSellerProduct->show_condition = $objProduct->show_condition;
            $objSellerProduct->condition = $objProduct->condition;
            $objSellerProduct->on_sale = $objProduct->on_sale;
            $objSellerProduct->additional_shipping_cost = $objProduct->additional_shipping_cost;

            //Availability Preferences
            $objSellerProduct->out_of_stock = StockAvailable::outOfStock($idProduct);
            $objSellerProduct->available_date = $objProduct->available_date;
            if (_PS_VERSION_ >= '1.7.3.0') {
                //Prestashop added this feature in PS V1.7.3.0 and above
                $objSellerProduct->additional_delivery_times = $objProduct->additional_delivery_times;
                $objSellerProduct->low_stock_threshold = $objProduct->low_stock_threshold;
                $objSellerProduct->low_stock_alert = $objProduct->low_stock_alert;
            }

            //Reference, UPC, ISBN, EAN
            $objSellerProduct->reference = $objProduct->reference;
            $objSellerProduct->ean13 = $objProduct->ean13;
            $objSellerProduct->upc = $objProduct->upc;
            $objSellerProduct->isbn = $objProduct->isbn;

            //Product Visibility Options
            $objSellerProduct->available_for_order = $objProduct->available_for_order;
            $objSellerProduct->show_price = $objProduct->show_price;
            $objSellerProduct->online_only = $objProduct->online_only;
            $objSellerProduct->visibility = $objProduct->visibility;

            //Multi-Lang fields
            foreach (Language::getLanguages(false) as $lang) {
                if (isset($objProduct->name[$lang['id_lang']])) {
                    $objSellerProduct->product_name[$lang['id_lang']] = $objProduct->name[$lang['id_lang']];
                    $objSellerProduct->description[$lang['id_lang']] = $objProduct->description[$lang['id_lang']];
                    $objSellerProduct->short_description[$lang['id_lang']] = $objProduct->description_short[$lang['id_lang']];

                    $objSellerProduct->meta_title[$lang['id_lang']] = $objProduct->meta_title[$lang['id_lang']];
                    $objSellerProduct->meta_description[$lang['id_lang']] = $objProduct->meta_description[$lang['id_lang']];
                    $objSellerProduct->link_rewrite[$lang['id_lang']] = $objProduct->link_rewrite[$lang['id_lang']];

                    $objSellerProduct->available_now[$lang['id_lang']] = $objProduct->available_now[$lang['id_lang']];
                    $objSellerProduct->available_later[$lang['id_lang']] = $objProduct->available_later[$lang['id_lang']];

                    if (_PS_VERSION_ >= '1.7.3.0') {
                        //Prestashop added this feature in PS V1.7.3.0 and above
                        $objSellerProduct->delivery_in_stock[$lang['id_lang']] = $objProduct->delivery_in_stock[$lang['id_lang']];
                        $objSellerProduct->delivery_out_stock[$lang['id_lang']] = $objProduct->delivery_out_stock[$lang['id_lang']];
                    }
                }
            }

            $objSellerProduct->save();
            $idMpProduct = $objSellerProduct->id;

            if ($idMpProduct) {
                //get prestashop product categories
                $categories = $objProduct->getCategories();

                if (!$categories) {
                    return false;
                }

                //save product categories in marketplace
                $objSellerProductCategory = new WkMpSellerProductCategory();
                $objSellerProductCategory->id_seller_product = $idMpProduct;
                foreach ($categories as $category) {
                    $objSellerProductCategory->id_category = $category;
                    if ($category == $objProduct->id_category_default) {
                        $objSellerProductCategory->is_default = 1;
                    } else {
                        $objSellerProductCategory->is_default = 0;
                    }

                    $objSellerProductCategory->add();
                }

                //upload prestashop product images to marketplace
                $images = $objProduct->getImages(Context::getContext()->language->id);
                if ($images) {
                    foreach ($images as $image) {
                        $idPsImage = $image['id_image'];
                        $objImage = new Image($idPsImage);

                        $randomImageName = Tools::passwdGen(6).'.'.$objImage->image_format;

                        $objMpProductImg = new WkMpSellerProductImage();
                        $objMpProductImg->seller_product_id = (int) $idMpProduct;
                        $objMpProductImg->seller_product_image_name = pSQL($randomImageName);
                        $objMpProductImg->id_ps_image = (int) $idPsImage;
                        $objMpProductImg->position = (int) $objImage->position;
                        $objMpProductImg->cover = $objImage->cover;
                        $objMpProductImg->active = 1;
                        $objMpProductImg->add();

                        $psImgPath = $objImage->getPathForCreation().'.'.$objImage->image_format;
                        $mpImgPath = _PS_MODULE_DIR_.'marketplace/views/img/product_img/';

                        ImageManager::resize($psImgPath, $mpImgPath.$randomImageName);

                        unset($objImage);
                        unset($objMpProductImg);
                    }
                }

                //Assigned product combinations, features and shipping according to admin choice
                if (Tools::getValue('assignedValues')) {
                    $assignedValues = Tools::getValue('assignedValues');
                    foreach ($assignedValues as $assignvalue) {
                        if ($assignvalue == 1) { //Assigned Product Combinations
                            WkMpProductAttribute::assignProductCombinations($idProduct, $idMpProduct);
                        } elseif ($assignvalue == 2) { //Assigned Product Features
                            WkMpProductFeature::assignPsProductFeatureToMp($idProduct, $idMpProduct);
                        } elseif ($assignvalue == 3) { //Assigned Product Shipping
                            $psIDCarrierReference = 0;
                            $productCarriers = $objProduct->getCarriers();
                            if ($productCarriers) {
                                $carrierData = array();
                                foreach ($productCarriers as $carrier) {
                                    $carrierData[] = $carrier['id_reference'];
                                }

                                $psIDCarrierReference = serialize($carrierData);
                            }

                            $objSellerProduct = new self($idMpProduct);
                            $objSellerProduct->width = $objProduct->width;
                            $objSellerProduct->height = $objProduct->height;
                            $objSellerProduct->depth = $objProduct->depth;
                            $objSellerProduct->weight = $objProduct->weight;
                            $objSellerProduct->ps_id_carrier_reference = $psIDCarrierReference;
                            $objSellerProduct->save();
                        }
                    }
                }

                Hook::exec(
                    'actionAfterAssignProduct',
                    array(
                        'id_seller' => $idSeller,
                        'id_product' => $idProduct,
                        'mp_id_product' => $idMpProduct,
                    )
                );

                return $idMpProduct;
            }
        }

        return false;
    }

    /**
     * Get Seller's Product Categories by using Seller ID product.
     *
     * @param int $idSellerProduct Seller Id Product
     *
     * @return array/boolean Array of categories/false
     */
    public function getSellerProductCategories($idSellerProduct)
    {
        $sellerProductCategories = Db::getInstance()->executeS('SELECT `id_category` FROM `'._DB_PREFIX_.'wk_mp_seller_product_category` WHERE `id_seller_product` ='.(int) $idSellerProduct);

        if (!empty($sellerProductCategories)) {
            return $sellerProductCategories;
        }

        return false;
    }

    /**
     * Send Email on various action perfom on product
     * Please read all the variables used in functions because same function is using for various event.
     *
     * @param int    $mpIdProduct Seller Id Product
     * @param string $subject     Mail Subject
     * @param bool   $mailFor     1 active product, 2 deactive product, 3 delete product
     *
     * @return bolean
     */
    public static function sendMail(
        $mpIdProduct,
        $subject,
        $mailFor = false,
        $reason = false,
        $idMpProductAttribute = false
    ) {
        $objSellerProdVal = new self($mpIdProduct);
        $mpIDSeller = $objSellerProdVal->id_seller;
        $objSellerVal = new WkMpSeller($mpIDSeller);
        $idLang = $objSellerVal->default_lang;

        $objSellerProduct = new self($mpIdProduct, $idLang);

        if ($mailFor == 1) {
            $mailReason = 'activated';
        } elseif ($mailFor == 2) {
            $mailReason = 'deactivated';
        } elseif ($mailFor == 3) {
            $mailReason = 'deleted';
        } else {
            $mailReason = 'activated';
        }

        if ($mailFor == 'assignment') {
            $objMp = new Marketplace();
            $mailReason = $objMp->l('Admin Assign Product To You', 'WkMpSellerProduct');
        }

        $productName = $objSellerProduct->product_name;

        //If product combination exist then add combination name is product name
        if ($idMpProductAttribute) {
            $combinationName = '';
            $attributeIdsSet = WkMpProductAttributeCombination::getPsAttributesSet($idMpProductAttribute);
            $attributes = Attribute::getAttributes($idLang, true);
            if ($attributes && $attributeIdsSet) {
                foreach ($attributes as $attributeVal) {
                    foreach ($attributeIdsSet as $attributeIdsSetVal) {
                        if ($attributeVal['id_attribute'] == $attributeIdsSetVal['id_ps_attribute']) {
                            $combinationName .= $attributeVal['attribute_group'].' : '.$attributeVal['name'].' ';
                        }
                    }
                }
            }
            $productName = $productName.' - '.$combinationName;

            $objMpProductAttribute = new WkMpProductAttribute($idMpProductAttribute);
            $quantity = $objMpProductAttribute->mp_quantity;
            $lowStockThreshold = $objMpProductAttribute->low_stock_threshold;
        } else {
            $quantity = $objSellerProduct->quantity;
            $lowStockThreshold = $objSellerProduct->low_stock_threshold;
        }

        $idCategory = $objSellerProduct->id_category;
        $idPsShop = $objSellerProduct->id_ps_shop;
        $productPrice = $objSellerProduct->price;

        $objCategory = new Category($idCategory, $idLang);
        $categoryName = $objCategory->name;

        $objSeller = new WkMpSeller($mpIDSeller, $idLang);
        $mpSellerName = $objSeller->seller_firstname.' '.$objSeller->seller_lastname;
        $mpShopName = $objSeller->shop_name;
        $businessEmail = $objSeller->business_email;
        if ($businessEmail == '') {
            $idCustomer = $objSeller->id_customer;
            $objCustomer = new Customer($idCustomer);
            $businessEmail = $objCustomer->email;
        }

        $objShop = new Shop($idPsShop);
        $psShopName = $objShop->name;

        $tempPath = _PS_MODULE_DIR_.'marketplace/mails/';

        $templateVars = array(
            '{seller_name}' => $mpSellerName,
            '{product_name}' => $productName,
            '{mp_shop_name}' => $mpShopName,
            '{mail_reason}' => $mailReason,
            '{category_name}' => $categoryName,
            '{product_price}' => Tools::displayPrice($productPrice),
            '{quantity}' => $quantity,
            '{last_quantity}' => $lowStockThreshold,
            '{ps_shop_name}' => $psShopName,
        );
        if ($reason && $reason != '') {
            $templateVars['{reason_text}'] = $reason;
        } else {
            $templateVars['{reason_text}'] = '';
        }

        if (Configuration::get('WK_MP_SUPERADMIN_EMAIL')) {
            $adminEmail = Configuration::get('WK_MP_SUPERADMIN_EMAIL');
        } else {
            $idEmployee = WkMpHelper::getSupperAdmin();
            $employee = new Employee($idEmployee);
            $adminEmail = $employee->email;
        }

        $fromTitle = Configuration::get('WK_MP_FROM_MAIL_TITLE');

        if ($subject == 1) {
            //Product Activated
            if (Configuration::get('WK_MP_MAIL_SELLER_PRODUCT_APPROVE')) {
                Mail::Send(
                    $idLang,
                    'product_active',
                    Mail::l('Product Activated', $idLang),
                    $templateVars,
                    $businessEmail,
                    $mpSellerName,
                    $adminEmail,
                    $fromTitle,
                    null,
                    null,
                    $tempPath,
                    false,
                    null,
                    null
                );
            }
        } elseif ($subject == 2) {
            //Product Deactivated
            if (Configuration::get('WK_MP_MAIL_SELLER_PRODUCT_DISAPPROVE')) {
                Mail::Send(
                    $idLang,
                    'product_deactive',
                    Mail::l('Product Deactivated', $idLang),
                    $templateVars,
                    $businessEmail,
                    $mpSellerName,
                    $adminEmail,
                    $fromTitle,
                    null,
                    null,
                    $tempPath,
                    false,
                    null,
                    null
                );
            }
        } elseif ($subject == 3) {
            if (Configuration::get('WK_MP_MAIL_SELLER_PRODUCT_ASSIGN')) {
                //Admin assign product to seller
                Mail::Send(
                    $idLang,
                    'product_assignment_to_seller',
                    Mail::l('Product Assignment', $idLang),
                    $templateVars,
                    $businessEmail,
                    $mpSellerName,
                    $adminEmail,
                    $fromTitle,
                    null,
                    null,
                    $tempPath,
                    false,
                    null,
                    null
                );
            }
        } elseif ($subject == 4) {
            //Product Out of stock mail to seller
            //Here we are not checking configuration settins for Low Stock level because If seller is not able to set low stock level according to configuration but Admin can still set this on behalf seller
            Mail::Send(
                $idLang,
                'product_out_of_stock',
                Mail::l('Product out of stock', $idLang),
                $templateVars,
                $businessEmail,
                $mpSellerName,
                $adminEmail,
                $fromTitle,
                null,
                null,
                $tempPath,
                false,
                null,
                null
            );
        }

        return true;
    }

    /**
     * Get Seller Product Details by using prestashop product ID.
     *
     * @param int $id_product Prestashop Product ID
     *
     * @return array/boolean If exist then array of current product else false
     */
    public static function getSellerProductByPsIdProduct($idProduct)
    {
        return Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'wk_mp_seller_product WHERE `id_ps_product` = '.(int) $idProduct);
    }

    /**
     * Send Email to admin when seller add any product in their seller account.
     *
     * @param string $productName     Product Name
     * @param string $sellerName      Seller name
     * @param int    $phone           Seller Phone Number
     * @param string $shopName        Seller Shop Name
     * @param string $businessEmailID Seller Email Address
     *
     * @return bool true/false
     */
    public function mailToAdminOnProductAdd($productName, $sellerName, $phone, $shopName, $businessEmailID)
    {
        if (Configuration::get('WK_MP_SUPERADMIN_EMAIL')) {
            $adminEmail = Configuration::get('WK_MP_SUPERADMIN_EMAIL');
        } else {
            $idEmployee = WkMpHelper::getSupperAdmin();
            $employee = new Employee($idEmployee);
            $adminEmail = $employee->email;
        }

        $sellerVars = array(
            '{product_name}' => $productName,
            '{seller_name}' => $sellerName,
            '{seller_shop}' => $shopName,
            '{seller_email_id}' => $businessEmailID,
            '{seller_phone}' => $phone,
        );

        $templatePath = _PS_MODULE_DIR_.'marketplace/mails/';
        Mail::Send(
            (int) Configuration::get('PS_LANG_DEFAULT'),
            'mp_product_add',
            Mail::l('New product added', (int) Configuration::get('PS_LANG_DEFAULT')),
            $sellerVars,
            $adminEmail,
            null,
            null,
            null,
            null,
            null,
            $templatePath,
            false,
            null,
            null
        );
    }

    /**
     * Send Email to admin or seller when admin/seller delete mp product
     *
     * @param string $productName     Seller product name
     * @param string $sellerName      Seller name
     * @param int    $phone           Seller phone number
     * @param string $shopName        Seller Shop Name
     * @param string $businessEmailID Seller email address
     * @param string $mailLangId      mail send in language
     * @param string $mailTo          mail send to admin or seller
     *
     * @return bool true/false
     */
    public static function mailOnProductDelete($productName, $sellerName, $phone, $shopName, $businessEmailID, $mailLangId, $mailTo)
    {
        if (Configuration::get('WK_MP_SUPERADMIN_EMAIL')) {
            $adminEmail = Configuration::get('WK_MP_SUPERADMIN_EMAIL');
        } else {
            $idEmployee = WkMpHelper::getSupperAdmin();
            $employee = new Employee($idEmployee);
            $adminEmail = $employee->email;
        }

        $sellerVars = array(
            '{product_name}' => $productName,
            '{seller_name}' => $sellerName,
            '{seller_shop}' => $shopName,
            '{seller_email_id}' => $businessEmailID,
            '{seller_phone}' => $phone,
        );

        if ($mailTo == 'admin') {
            //deleted by seller
            $mailToEmail = $adminEmail;
            $sellerVars['{to_mail_person}'] = 'Admin';
            $sellerVars['{from_mail_person}'] = $sellerName;
        } else {
            //deleted by admin
            $mailToEmail = $businessEmailID;
            $sellerVars['{to_mail_person}'] = $sellerName;
            $sellerVars['{from_mail_person}'] = 'Admin';
        }

        $templatePath = _PS_MODULE_DIR_.'marketplace/mails/';
        Mail::Send(
            (int) $mailLangId,
            'mp_product_delete',
            Mail::l('Product Deleted', (int) $mailLangId),
            $sellerVars,
            $mailToEmail,
            null,
            null,
            null,
            null,
            null,
            $templatePath,
            false,
            null,
            null
        );
    }

    /**
     * Get Seller Inactive Product Images.
     *
     * @param int $id Seller Id Product
     *
     * @return array
     */
    public static function getInactiveProductImageByIdProduct($idSellerProduct)
    {
        $unactiveImage = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_product_image` WHERE `seller_product_id` = '.(int) $idSellerProduct.' AND active = 0');

        if (!empty($unactiveImage)) {
            return $unactiveImage;
        }

        return false;
    }

    /**
     * Get Seller Default Language when seller add product or update product.
     *
     * @param int $sellerDefaultLanguage seller current default language
     *
     * @return array
     */
    public static function getDefaultLanguageOnProductSave()
    {
        //If multi-lang is OFF then PS default lang will be default lang for seller
        if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
            $defaultLang = Tools::getValue('seller_default_lang');
        } else {
            if (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '1') {
                //Admin default lang
                $defaultLang = Configuration::get('PS_LANG_DEFAULT');
            } elseif (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '2') {
                //Seller default lang
                $defaultLang = Tools::getValue('seller_default_lang');
            }
        }
        $objLang = new Language((int) $defaultLang);
        if (!$objLang->active) {
            $defaultLang = Configuration::get('PS_LANG_DEFAULT');
        }

        return $defaultLang;
    }

    /**
     * If admin set configuration Yes for product update after need approval
     * then seller product will be activated after product update from update product page in front end.
     *
     * @param int $mpIdProduct seller product id
     *
     * @return bool
     */
    public static function deactivateProductAfterUpdate($mpIdProduct, $extraController = false)
    {
        // Product after update need to approved is ON only for product update page
        if (Configuration::get('WK_MP_PRODUCT_UPDATE_ADMIN_APPROVE')
            && (
                'updateproduct' == Tools::getValue('controller') ||
                'managecombination' == Tools::getValue('controller') ||
                'uploadimage' == Tools::getValue('controller') ||
                $extraController == 1
            )
            ) {
            //Deactivate the product after seller update that product
            $objSellerProduct = new self($mpIdProduct);
            if (Validate::isLoadedObject($objSellerProduct) && $objSellerProduct->active) {
                $objSellerProduct->active = 0;
                $objSellerProduct->status_before_deactivate = 0;
                $objSellerProduct->admin_approved = 0;
                if ($objSellerProduct->save() && $objSellerProduct->id_ps_product) {
                    $objProduct = new Product($objSellerProduct->id_ps_product);
                    if (Validate::isLoadedObject($objProduct)) {
                        $objProduct->active = 0;
                        if ($objProduct->save()) {
                            self::sendMail($mpIdProduct, 2, 2);
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * When seller or admin change tax rule or price from add/update product page
     * then display product price with Tax included (Final price after Tax Incl.).
     */
    public static function getMpProductTaxIncludedPrice()
    {
        //Get Tax Include Product Price according to selected Tax Rate and Product Price (tax excl.)
        $productPrice = Tools::getValue('product_price');
        $productTIPrice = Tools::getValue('productTI_price');
        $idTaxRulesGroup = Tools::getValue('id_tax_rules_group');
        $inputAction = Tools::getValue('input_action');

        if ($productTIPrice == '') {
            $productTIPrice = 0;
        }

        $idCountryDefault = Configuration::get('PS_COUNTRY_DEFAULT');
        $adminDefaultCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $taxesRatesByGroup = TaxRulesGroup::getAssociatedTaxRatesByIdCountry($idCountryDefault);
        if ($taxesRatesByGroup) {
            if (isset($taxesRatesByGroup[$idTaxRulesGroup]) && $taxesRatesByGroup[$idTaxRulesGroup]) {
                $taxRate = $taxesRatesByGroup[$idTaxRulesGroup];
            } else {
                $taxRate = 0;
            }

            if ($inputAction == 'input_incl') {
                //Get tax incl price to tax excl price
                $productPrice = (float) $productTIPrice / (($taxRate / 100) + 1);
            } else {
                //Get tax excl price to tax incl price
                $productPrice = (float) $productPrice + ((float) $productPrice * $taxRate) / 100;
            }
        }

        die(Tools::jsonEncode(
            array(
                'status' => 'ok',
                'prod_price' => Tools::ps_round($productPrice),
                'display_product_price' => Tools::displayPrice($productPrice, $adminDefaultCurrency),
                'display_productTI_price' => Tools::displayPrice($productTIPrice, $adminDefaultCurrency),
            )
        ));

        //ajax close
    }

    /**
     * Load Prestashop category with ajax load of plugin jstree.
     */
    public static function getMpProductCategory()
    {
        $objSellerProductCategory = new WkMpSellerProductCategory();
        if (Tools::getValue('id_mp_product') == '') {
            // Add product
            $catId = Tools::getValue('catsingleId');
            $selectedCatIds = array(Category::getRootCategory()->id); //Root Category will be automatically selected
        } else {
            // Edit product
            $catId = Tools::getValue('catsingleId');
            $selectedCatIds = explode(',', Tools::getValue('catIds'));
        }

        $treeLoad = $objSellerProductCategory->getProductCategory(
            $catId,
            $selectedCatIds,
            Context::getContext()->language->id
        );
        if ($treeLoad) {
            die(Tools::jsonEncode($treeLoad)); //ajax close
        } else {
            die('fail'); //ajax close
        }
    }

    /**
     * Check whether prestashop product belong to seller or not.
     *
     * @param int $SellerIdCustomer Prestashop Product ID
     * @param int $idSeller         Seller ID
     *
     * @return array
     */
    public static function checkPsProduct($idPsProduct, $idSeller)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_product`
            WHERE `id_ps_product` ='.(int) $idPsProduct.' AND `id_seller` = '.(int) $idSeller
        );
    }

    /**
     * Get only ps catalog product list (seller product will not include)
     * @param int $idLang - context lang id
     * @return array
     */
    public static function getPsProductsForAssigned($idLang, $idPsShop = false)
    {
        if (!$idPsShop) {
            $idPsShop = Context::getContext()->shop->id;
        }

        $sql =  'SELECT p.`id_product`, pl.`name` FROM `'._DB_PREFIX_.'product` p
                JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`)
                WHERE p.`id_product` NOT IN (SELECT `id_ps_product` FROM '._DB_PREFIX_.'wk_mp_seller_product)
                AND pl.`id_lang` = '.(int) $idLang.'
                AND pl.`id_shop` = '.(int) $idPsShop.'
                AND p.`active` = 1';

        if (!Module::isInstalled('mpvirtualproduct')) { //if mp virtual product module is installed
            $sql .= ' AND p.`is_virtual` = 0';
        }
        if (!Module::isInstalled('mppackproducts')) { //if mp pack product module is installed
            $sql .= ' AND p.`cache_is_pack` = 0';
        }

        $sql .= ' ORDER BY p.`id_product` ASC';
        $assignedProducts = Db::getInstance()->executeS($sql);
        if ($assignedProducts) {
            return $assignedProducts;
        }

        return false;
    }

    /**
     * PHP Validation all the fields entered by seller during add or update product.
     *
     * @return array/bool
     */
    public static function validateMpProductForm()
    {
        $className = 'WkMpSellerProduct';
        $objMp = new Marketplace();
        $wkErrors = array();

        $quantity = Tools::getValue('quantity');
        if (Configuration::get('WK_MP_PRODUCT_MIN_QTY') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $minimalQuantity = Tools::getValue('minimal_quantity');
        } else {
            $minimalQuantity = 1; //default value
        }

        if (Configuration::get('WK_MP_PRODUCT_LOW_STOCK_ALERT') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $lowStockThreshold = Tools::getValue('low_stock_threshold');
        } else {
            $lowStockThreshold = '';
        }

        $categories = Tools::getValue('product_category');

        $price = Tools::getValue('price');

        if (Configuration::get('WK_MP_PRODUCT_WHOLESALE_PRICE') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $wholesalePrice = Tools::getValue('wholesale_price');
        } else {
            $wholesalePrice = '';
        }

        if (Configuration::get('WK_MP_PRODUCT_PRICE_PER_UNIT') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $unitPrice = Tools::getValue('unit_price');
        } else {
            $unitPrice = '';
        }

        if (Configuration::get('WK_MP_PRODUCT_ADDITIONAL_FEES') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $additionalFees = Tools::getValue('additional_shipping_cost');
        } else {
            $additionalFees = '';
        }

        $reference = trim(Tools::getValue('reference'));
        $ean13JanBarcode = trim(Tools::getValue('ean13'));
        $upcBarcode = trim(Tools::getValue('upc'));
        $isbn = trim(Tools::getValue('isbn'));
        $availableDate = Tools::getValue('available_date');

        // height, width, depth and weight
        $width = Tools::getValue('width');
        $width = empty($width) ? '0' : str_replace(',', '.', $width);

        $height = Tools::getValue('height');
        $height = empty($height) ? '0' : str_replace(',', '.', $height);

        $depth = Tools::getValue('depth');
        $depth = empty($depth) ? '0' : str_replace(',', '.', $depth);

        $weight = Tools::getValue('weight');
        $weight = empty($weight) ? '0' : str_replace(',', '.', $weight);

        // Check fields sizes
        $rules = call_user_func(array($className, 'getValidationRules'), $className);

        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            $languageName = '';
            if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                $languageName = '('.$language['name'].')';
            }

            if (!Validate::isCatalogName(Tools::getValue('product_name_'.$language['id_lang']))) {
                $wkErrors[] = sprintf($objMp->l('Product name field %s is invalid', $className), $languageName);
            } elseif (Tools::strlen(Tools::getValue('product_name_'.$language['id_lang'])) > $rules['sizeLang']['product_name']) {
                $wkErrors[] = sprintf($objMp->l('The Product Name field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['product_name']);
            }

            if (Tools::getValue('short_description_'.$language['id_lang'])) {
                $shortDesc = Tools::getValue('short_description_'.$language['id_lang']);
                $limit = (int) Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT');
                if ($limit <= 0) {
                    $limit = 400;
                }
                if (!Validate::isCleanHtml($shortDesc, (int) Configuration::get('PS_ALLOW_HTML_IFRAME'))) {
                    $wkErrors[] = sprintf($objMp->l('Short description field %s is invalid', $className), $languageName);
                } elseif (Tools::strlen(strip_tags($shortDesc)) > $limit) {
                    $wkErrors[] = sprintf($objMp->l('Short description field %s is too long: (%d chars max).', $className), $languageName, $limit);
                }
            }

            if (Tools::getValue('description_'.$language['id_lang'])) {
                if (!Validate::isCleanHtml(Tools::getValue('description_'.$language['id_lang']), (int) Configuration::get('PS_ALLOW_HTML_IFRAME'))) {
                    $wkErrors[] = sprintf($objMp->l('Product description field %s is invalid', $className), $languageName);
                }
            }

            //Product Availability Preferences Validation
            if (Tools::getValue('available_now_'.$language['id_lang'])) {
                if (!Validate::isGenericName(Tools::getValue('available_now_'.$language['id_lang']))) {
                    $wkErrors[] = sprintf($objMp->l('Label when in stock field %s is invalid', $className), $languageName);
                } elseif (Tools::strlen(Tools::getValue('available_now_'.$language['id_lang'])) > $rules['sizeLang']['available_now']) {
                    $wkErrors[] = sprintf($objMp->l('Label when in stock field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['available_now']);
                }
            }
            if (Tools::getValue('available_later_'.$language['id_lang'])) {
                if (!Validate::isGenericName(Tools::getValue('available_later_'.$language['id_lang']))) {
                    $wkErrors[] = sprintf($objMp->l('Label when out of stock field %s is invalid', $className), $languageName);
                } elseif (Tools::strlen(Tools::getValue('available_later_'.$language['id_lang'])) > $rules['sizeLang']['available_later']) {
                    $wkErrors[] = sprintf($objMp->l('Label when out of stock field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['available_later']);
                }
            }

            //Product Delivery Time Validation
            if (Tools::getValue('delivery_in_stock_'.$language['id_lang'])) {
                if (!Validate::isGenericName(Tools::getValue('delivery_in_stock_'.$language['id_lang']))) {
                    $wkErrors[] = sprintf($objMp->l('Delivery time of in-stock products field %s is invalid', $className), $languageName);
                } elseif (Tools::strlen(Tools::getValue('delivery_in_stock_'.$language['id_lang'])) > $rules['sizeLang']['delivery_in_stock']) {
                    $wkErrors[] = sprintf($objMp->l('Delivery time of in-stock products field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['delivery_in_stock']);
                }
            }
            if (Tools::getValue('delivery_out_stock_'.$language['id_lang'])) {
                if (!Validate::isGenericName(Tools::getValue('delivery_out_stock_'.$language['id_lang']))) {
                    $wkErrors[] = sprintf($objMp->l('Delivery time of out-of-stock products field %s is invalid', $className), $languageName);
                } elseif (Tools::strlen(Tools::getValue('delivery_out_stock_'.$language['id_lang'])) > $rules['sizeLang']['delivery_out_stock']) {
                    $wkErrors[] = sprintf($objMp->l('Delivery time of out-of-stock products field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['delivery_out_stock']);
                }
            }

            //Product SEO Validation
            if (Tools::getValue('meta_title_'.$language['id_lang'])) {
                if (!Validate::isGenericName(Tools::getValue('meta_title_'.$language['id_lang']))) {
                    $wkErrors[] = sprintf($objMp->l('Product meta title field %s is invalid', $className), $languageName);
                } elseif (Tools::strlen(Tools::getValue('meta_title_'.$language['id_lang'])) > $rules['sizeLang']['meta_title']) {
                    $wkErrors[] = sprintf($objMp->l('Product meta title field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['meta_title']);
                }
            }
            if (Tools::getValue('meta_description_'.$language['id_lang'])) {
                if (!Validate::isGenericName(Tools::getValue('meta_description_'.$language['id_lang']))) {
                    $wkErrors[] = sprintf($objMp->l('Product meta description field %s is invalid', $className), $languageName);
                } elseif (Tools::strlen(Tools::getValue('meta_description_'.$language['id_lang'])) > $rules['sizeLang']['meta_description']) {
                    $wkErrors[] = sprintf($objMp->l('Product meta description field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['meta_description']);
                }
            }
            if (Tools::getValue('link_rewrite_'.$language['id_lang'])) {
                if (!Validate::isGenericName(Tools::getValue('link_rewrite_'.$language['id_lang']))) {
                    $wkErrors[] = sprintf($objMp->l('Product friendly url field %s is invalid', $className), $languageName);
                } elseif (Tools::strlen(Tools::getValue('link_rewrite_'.$language['id_lang'])) > $rules['sizeLang']['link_rewrite']) {
                    $wkErrors[] = sprintf($objMp->l('Product friendly url field is too long (%2$d chars max).', $className), call_user_func(array($className, 'displayFieldName'), $className), $rules['sizeLang']['link_rewrite']);
                }
            }
        }

        //Product Price Validation
        if ($price == '') {
            $wkErrors[] = $objMp->l('Product price is required field.', $className);
        } elseif (!Validate::isPrice($price)) {
            $wkErrors[] = $objMp->l('Product price should be valid.', $className);
        }
        if ($wholesalePrice != '') {
            if (!Validate::isPrice($wholesalePrice)) {
                $wkErrors[] = $objMp->l('Wholesale price should be valid.', $className);
            }
        }
        if ($unitPrice != '') {
            if (!Validate::isPrice($unitPrice)) {
                $wkErrors[] = $objMp->l('Price per unit should be valid.', $className);
            }
        }

        //Product Quantity Validation
        if ($quantity == '') {
            $wkErrors[] = $objMp->l('Product quantity is required field.', $className);
        } elseif (!Validate::isInt($quantity)) {
            $wkErrors[] = $objMp->l('Product quantity should be valid.', $className);
        }
        if ($minimalQuantity == '') {
            $wkErrors[] = $objMp->l('Product minimum quantity is required field.', $className);
        } elseif (!Validate::isUnsignedInt($minimalQuantity)) {
            $wkErrors[] = $objMp->l('Product minimum quantity should be valid.', $className);
        }

        if ($lowStockThreshold != '') {
            if (!Validate::isInt($lowStockThreshold)) {
                $wkErrors[] = $objMp->l('Low stock level should be valid.', $className);
            }
        }

        if (!$categories) {
            $wkErrors[] = $objMp->l('You have not selected any category.', $className);
        }

        //Product Package Dimension Validation
        if ($width && !Validate::isUnsignedFloat($width)) {
            $wkErrors[] = $objMp->l('Value of width is not valid.', $className);
        }
        if ($height && !Validate::isUnsignedFloat($height)) {
            $wkErrors[] = $objMp->l('Value of height is not valid.', $className);
        }
        if ($depth && !Validate::isUnsignedFloat($depth)) {
            $wkErrors[] = $objMp->l('Value of depth is not valid.', $className);
        }
        if ($weight && !Validate::isUnsignedFloat($weight)) {
            $wkErrors[] = $objMp->l('Value of weight is not valid.', $className);
        }

        if ($additionalFees != '') {
            if (!Validate::isPrice($additionalFees)) {
                $wkErrors[] = $objMp->l('Shipping fees should be valid.', $className);
            }
        }

        // Product Reference, SEO, EAN, ISBN and UPC Code
        if ($reference && !Validate::isReference($reference)) {
            $wkErrors[] = $objMp->l('Reference is not valid.', $className);
        }
        if ($ean13JanBarcode) {
            if (!Validate::isEan13($ean13JanBarcode)) {
                $wkErrors[] = $objMp->l('EAN-13 or JAN barcode is not valid.', $className);
            }
        }
        if ($upcBarcode && !Validate::isUpc($upcBarcode)) {
            $wkErrors[] = $objMp->l('UPC Barcode is not valid.', $className);
        }
        if ($isbn && !Validate::isIsbn($isbn)) {
            $wkErrors[] = $objMp->l('ISBN Code is not valid.', $className);
        }

        if ($availableDate && !Validate::isDateFormat($availableDate)) {
            $wkErrors[] = $objMp->l('Available date must be valid.', $className);
        }

        if ($wkErrors) {
            return $wkErrors;
        }

        return false;
    }

    /**
     * JS Validation on all the fields entered by seller during add or update product.
     *
     * @param int $idLang - context lang id
     *
     * @return array/bool
     */
    public static function validationProductFormField($params)
    {
        $className = 'WkMpSellerProduct';
        $objMp = new Marketplace();

        if (isset($params['default_lang'])) {
            $sellerDefaultLanguage = $params['default_lang'];
        } else {
            $sellerDefaultLanguage = $params['seller_default_lang'];
        }
        $defaultLang = WkMpHelper::getDefaultLanguageBeforeFormSave($sellerDefaultLanguage);

        $quantity = $params['quantity'];
        if (Configuration::get('WK_MP_PRODUCT_MIN_QTY') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $minimalQuantity = $params['minimal_quantity'];
        } else {
            $minimalQuantity = 1; //default value
        }

        if (Configuration::get('WK_MP_PRODUCT_LOW_STOCK_ALERT') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $lowStockThreshold = $params['low_stock_threshold'];
        } else {
            $lowStockThreshold = '';
        }

        $categories = $params['product_category'];

        $price = $params['price'];

        if (Configuration::get('WK_MP_PRODUCT_WHOLESALE_PRICE') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $wholesalePrice = $params['wholesale_price'];
        } else {
            $wholesalePrice = '';
        }

        if (Configuration::get('WK_MP_PRODUCT_PRICE_PER_UNIT') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $unitPrice = $params['unit_price'];
        } else {
            $unitPrice = '';
        }

        if (Configuration::get('WK_MP_PRODUCT_ADDITIONAL_FEES') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $additionalFees = $params['additional_shipping_cost'];
        } else {
            $additionalFees = '';
        }

        if (Configuration::get('WK_MP_SELLER_PRODUCT_REFERENCE') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $reference = trim($params['reference']);
        } else {
            $reference = '';
        }
        if (Configuration::get('WK_MP_SELLER_PRODUCT_EAN') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $ean13JanBarcode = trim($params['ean13']);
        } else {
            $ean13JanBarcode = '';
        }
        if (Configuration::get('WK_MP_SELLER_PRODUCT_UPC') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $upcBarcode = trim($params['upc']);
        } else {
            $upcBarcode = '';
        }
        if (Configuration::get('WK_MP_SELLER_PRODUCT_ISBN') || (Tools::getValue('controller') == 'AdminSellerProductDetail')) {
            $isbn = trim($params['isbn']);
        } else {
            $isbn = '';
        }

        if (Configuration::get('WK_MP_SELLER_ADMIN_SHIPPING') || Module::isEnabled('mpshipping')) {
            // height, width, depth and weight
            $width = $params['width'];
            $width = empty($width) ? '0' : str_replace(',', '.', $width);

            $height = $params['height'];
            $height = empty($height) ? '0' : str_replace(',', '.', $height);

            $depth = $params['depth'];
            $depth = empty($depth) ? '0' : str_replace(',', '.', $depth);

            $weight = $params['weight'];
            $weight = empty($weight) ? '0' : str_replace(',', '.', $weight);
        } else {
            $width = '';
            $height = '';
            $depth = '';
            $weight = '';
        }

        $languages = Language::getLanguages();
        foreach ($languages as $language) {
            if (!Validate::isCatalogName($params['product_name_'.$language['id_lang']])) {
                $invalidProductName = 1;
            }

            if ($params['short_description_'.$language['id_lang']]) {
                $shortDesc = $params['short_description_'.$language['id_lang']];
                $limit = (int) Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT');
                if ($limit <= 0) {
                    $limit = 400;
                }
                if (!Validate::isCleanHtml($shortDesc, (int) Configuration::get('PS_ALLOW_HTML_IFRAME'))) {
                    $invalidSortDesc = 1;
                } elseif (Tools::strlen(strip_tags($shortDesc)) > $limit) {
                    $invalidSortDesc = 2;
                }
            }

            if ($params['description_'.$language['id_lang']]) {
                if (!Validate::isCleanHtml($params['description_'.$language['id_lang']], (int) Configuration::get('PS_ALLOW_HTML_IFRAME'))) {
                    $invalidDesc = 1;
                }
            }
        }

        if (!$params['product_name_'.$defaultLang]) {
            if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                $sellerLang = Language::getLanguage((int) $defaultLang);
                $msg = sprintf($objMp->l('Product name is required in %s', $className), $sellerLang['name']);
            } else {
                $msg = $objMp->l('Product name is required', $className);
            }
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '1',
                'inputName' => 'product_name_all',
                'msg' => $msg
            );
            die(Tools::jsonEncode($data));
        } elseif (isset($invalidProductName)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '1',
                'inputName' => 'product_name_all',
                'msg' => $objMp->l('Product name have Invalid characters.', $className)
            );
            die(Tools::jsonEncode($data));
        }

        if (isset($invalidSortDesc)) {
            if ($invalidSortDesc == 1) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-information',
                    'multilang' => '1',
                    'inputName' => 'wk_short_desc',
                    'msg' => $objMp->l('Short description have not valid data.', $className)
                );
                die(Tools::jsonEncode($data));
            } elseif ($invalidSortDesc == 2) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-information',
                    'multilang' => '1',
                    'inputName' => 'wk_short_desc',
                    'msg' => sprintf($objMp->l('This short description field is too long: %s characters max.', $className), $limit)
                );
                die(Tools::jsonEncode($data));
            }
        }

        if (isset($invalidDesc)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '1',
                'inputName' => 'wk_desc',
                'msg' => $objMp->l('Product description does not have valid data.', $className)
            );
            die(Tools::jsonEncode($data));
        }

        //Product Price Js Validation
        if ($price == '') {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '0',
                'inputName' => 'price',
                'msg' => $objMp->l('Product price is required field.', $className)
            );
            die(Tools::jsonEncode($data));
        } elseif (!Validate::isPrice($price)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '0',
                'inputName' => 'price',
                'msg' => $objMp->l('Product price should be valid.', $className)
            );
            die(Tools::jsonEncode($data));
        }
        if ($wholesalePrice != '') {
            if (!Validate::isPrice($wholesalePrice)) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-information',
                    'multilang' => '0',
                    'inputName' => 'wholesale_price',
                    'msg' => $objMp->l('Wholesale price should be valid.', $className)
                );
                die(Tools::jsonEncode($data));
            }
        }
        if ($unitPrice != '') {
            if (!Validate::isPrice($unitPrice)) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-information',
                    'multilang' => '0',
                    'inputName' => 'unit_price',
                    'msg' => $objMp->l('Price per unit should be valid.', $className)
                );
                die(Tools::jsonEncode($data));
            }
        }

        //Product Quantity Js Validation
        if ($quantity == '') {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '0',
                'inputName' => 'quantity',
                'msg' => $objMp->l('Product quantity is required field.', $className)
            );
            die(Tools::jsonEncode($data));
        } elseif (!Validate::isInt($quantity)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '0',
                'inputName' => 'quantity',
                'msg' => $objMp->l('Product quantity should be valid.', $className)
            );
            die(Tools::jsonEncode($data));
        }
        if ($minimalQuantity == '') {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '0',
                'inputName' => 'minimal_quantity',
                'msg' => $objMp->l('Product minimum quantity is required field.', $className)
            );
            die(Tools::jsonEncode($data));
        } elseif (!Validate::isUnsignedInt($minimalQuantity)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '0',
                'inputName' => 'minimal_quantity',
                'msg' => $objMp->l('Product minimum quantity should be valid.', $className)
            );
            die(Tools::jsonEncode($data));
        }

        if ($lowStockThreshold != '') {
            if (!Validate::isInt($lowStockThreshold)) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-information',
                    'multilang' => '0',
                    'inputName' => 'low_stock_threshold',
                    'msg' => $objMp->l('Low stock level should be valid.', $className)
                );
                die(Tools::jsonEncode($data));
            }
        }

        if (!$categories) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '0',
                'inputName' => 'categorycontainer',
                'msg' => $objMp->l('You have not selected any category.', $className)
            );
            die(Tools::jsonEncode($data));
        }

        //Product Package Dimenstion Js Validation
        if ($width && !Validate::isUnsignedFloat($width)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-product-shipping',
                'multilang' => '0',
                'inputName' => 'width',
                'msg' => $objMp->l('Value of width is not valid.', $className)
            );
            die(Tools::jsonEncode($data));
        }
        if ($height && !Validate::isUnsignedFloat($height)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-product-shipping',
                'multilang' => '0',
                'inputName' => 'height',
                'msg' => $objMp->l('Value of height is not valid.', $className)
            );
            die(Tools::jsonEncode($data));
        }
        if ($depth && !Validate::isUnsignedFloat($depth)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-product-shipping',
                'multilang' => '0',
                'inputName' => 'depth',
                'msg' => $objMp->l('Value of depth is not valid.', $className)
            );
            die(Tools::jsonEncode($data));
        }
        if ($weight && !Validate::isUnsignedFloat($weight)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-product-shipping',
                'multilang' => '0',
                'inputName' => 'weight',
                'msg' => $objMp->l('Value of weight is not valid.', $className)
            );
            die(Tools::jsonEncode($data));
        }

        if ($additionalFees != '') {
            if (!Validate::isPrice($additionalFees)) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-product-shipping',
                    'multilang' => '0',
                    'inputName' => 'additional_shipping_cost',
                    'msg' => $objMp->l('Shipping fees should be valid.', $className)
                );
                die(Tools::jsonEncode($data));
            }
        }

        //Product Reference, EAN, UPC Js Validation
        if ($reference && !Validate::isReference($reference)) {
            $data = array(
                'status' => 'ko',
                'tab' => 'wk-information',
                'multilang' => '0',
                'inputName' => 'reference',
                'msg' => $objMp->l('Reference is not valid.', $className)
            );
            die(Tools::jsonEncode($data));
        }
        if ($ean13JanBarcode) {
            if (!Validate::isEan13($ean13JanBarcode)) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-options',
                    'multilang' => '0',
                    'inputName' => 'ean13',
                    'msg' => $objMp->l('EAN-13 or JAN barcode is not valid.', $className)
                );
                die(Tools::jsonEncode($data));
            }
        }
        if ($upcBarcode) {
            if (!Validate::isUpc($upcBarcode)) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-options',
                    'multilang' => '0',
                    'inputName' => 'upc',
                    'msg' => $objMp->l('UPC Barcode is not valid.', $className)
                );
                die(Tools::jsonEncode($data));
            }
        }
        if ($isbn) {
            if (!Validate::isIsbn($isbn)) {
                $data = array(
                    'status' => 'ko',
                    'tab' => 'wk-options',
                    'multilang' => '0',
                    'inputName' => 'isbn',
                    'msg' => $objMp->l('ISBN Code is not valid.', $className)
                );
                die(Tools::jsonEncode($data));
            }
        }
    }

    public static function getCover($mpIdProduct)
    {
        $coverImage = Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_product_image`
            WHERE `cover` = 1 AND `seller_product_id` = '.(int) $mpIdProduct
        );

        if ($coverImage && !empty($coverImage)) {
            return $coverImage;
        }

        return false;
    }
}
