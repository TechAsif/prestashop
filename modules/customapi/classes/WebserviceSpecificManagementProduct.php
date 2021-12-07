<?php


class WebserviceSpecificManagementProduct implements WebserviceSpecificManagementInterface
{

    /** @var WebserviceOutputBuilder */

    protected $objOutput;

    protected $output;



    /** @var WebserviceRequest */

    protected $wsObject;



    public function setUrlSegment($segments)

    {

        $this->urlSegment = $segments;

        return $this;
    }



    public function getUrlSegment()

    {

        return $this->urlSegment;
    }

    public function getWsObject()

    {

        return $this->wsObject;
    }



    public function getObjectOutput()

    {

        return $this->objOutput;
    }



    /**

     * This must be return a string with specific values as WebserviceRequest expects.

     *

     * @return string

     */

    public function getContent()

    {

        return $this->objOutput->getObjectRender()->overrideContent($this->output);
    }



    public function setWsObject(WebserviceRequestCore $obj)

    {

        $this->wsObject = $obj;

        return $this;
    }



    /**

     * @param WebserviceOutputBuilderCore $obj

     * @return WebserviceSpecificManagementInterface

     */

    public function setObjectOutput(WebserviceOutputBuilderCore $obj)

    {

        $this->objOutput = $obj;

        return $this;
    }

    public function getProducts($id_category, $limit, $name, $date, $sale, $attribute) {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
            SELECT DISTINCT p.`id_product`
            FROM `' . _DB_PREFIX_ . 'product` p
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.`id_product` = p.`id_product`
            LEFT JOIN `' . _DB_PREFIX_ . 'category_product` c ON c.`id_product` = p.`id_product`
            LEFT JOIN `' . _DB_PREFIX_ . 'specific_price` pr ON pr.`id_product` = p.`id_product`
            LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON pa.`id_product` = p.`id_product`
            LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
            WHERE 1 AND p.`active` = 1 
            ' . (!empty($attribute) ? ' AND pac.`id_attribute` = ' . $attribute . '' : '') . '
            ' . (!empty($sale) ? ' AND pr.`reduction` IS NOT NULL' : '') . '
            ' . (!empty($id_category) ? ' AND c.`id_category` IN (' . $id_category . ')' : '') . ' ' . (!empty($name) ? ' AND pl.`name` LIKE \'%' . pSQL($name) . '%\'
            OR p.`ean13` LIKE \'%' . pSQL($name) . '%\'
            OR p.`isbn` LIKE \'%' . pSQL($name) . '%\'
            OR p.`upc` LIKE \'%' . pSQL($name) . '%\'
            OR p.`reference` LIKE \'%' . pSQL($name) . '%\'
            OR p.`supplier_reference` LIKE \'%' . pSQL($name) . '%\'
            OR EXISTS(SELECT * FROM `' . _DB_PREFIX_ . 'product_supplier` sp WHERE sp.`id_product` = p.`id_product` AND `product_supplier_reference` LIKE \'%' . pSQL($name) . '%\')' : '') . '
            ' . (!empty($date) ? 'ORDER BY p.`date_upd` DESC' : 'ORDER BY p.`date_upd` DESC') . '
            LIMIT '.$limit);
    }

    public function getStockQuantity($id_product) {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
            SELECT  `quantity`
            FROM `' . _DB_PREFIX_ . 'stock_available`
            WHERE `id_product` = ' . $id_product . '
            LIMIT 1');
    }

    public function getLanguageId($lang) {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
            SELECT  `id_lang`
            FROM `' . _DB_PREFIX_ . 'lang`
            WHERE `language_code` = \'' . $lang . '\'
            LIMIT 1');
    }

    public function getSalePrice($product) {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
            SELECT  `id_specific_price`, `reduction`, `reduction_type`
            FROM `' . _DB_PREFIX_ . 'specific_price`
            WHERE `id_product` = ' . $product->id . '
            LIMIT 1'
        );
        if (empty($result)) return $product->price;
        if ($result[0]['reduction_type'] != "percentage") return $product->price - $result[0]['reduction'];
        return $product->price * (1 - $result[0]['reduction']);
    }


    public function manage()

    {

        $objects_products = array();
        
        $objects_products['empty'] = new Product();
        $id_category = $this->wsObject->urlFragments['id_category'] ?? null;
        $limit = $this->wsObject->urlFragments['limit'] ?? '0,10';
        $name = $this->wsObject->urlFragments['name'] ?? null;
        $date = $this->wsObject->urlFragments['date'] ?? null;
        $sale = $this->wsObject->urlFragments['sale'] ?? null;
        $attribute = $this->wsObject->urlFragments['attribute'] ?? null;
        $lang = $this->wsObject->urlFragments['lang'] ?? 'en';
        $lang = $this->getLanguageId($lang) ?? [];
        $lang = count($lang) > 0 ? strval($lang[0]['id_lang']) : '1';
        $products = $this->getProducts($id_category, $limit, $name, $date, $sale, $attribute);
        foreach($products as $product) {
            $pro = new Product($product['id_product']);
            if (Configuration::get('CUSTOMAPI_ENABLE_TAX', true) == true) {
                $pro->price = number_format((float) (Product::getPriceStatic($product['id_product'], true, null, 2, null, false, false)), 2, '.', '');
            }
            if (empty($pro->price)) {
                $pro->price = 0.0;
            }
            if (Configuration::get('CUSTOMAPI_ENABLE_TAX', true) == true) {
                $pro->wholesale_price = number_format((float) (Product::getPriceStatic($product['id_product'], true, null, 2)), 2, '.', '');
            } else {
                $pro->wholesale_price = number_format((float) ($this->getSalePrice($pro)), 2, '.', '');
            }
            //Get name product by lang
            if (gettype($pro->name) == 'array' && !empty(($pro->name)[$lang])) {
                $pro->name = ($pro->name)[$lang];
            }
            //Get description by lang
            if (gettype($pro->description) == 'array' && !empty(($pro->description)[$lang])) {
                $pro->description = ($pro->description)[$lang];
            }
            //Get description short by lang
            if (gettype($pro->description_short) == 'array' && !empty(($pro->description_short)[$lang])) {
                $pro->description_short = ($pro->description_short)[$lang];
            }
            //Get link rewrite by lang
            if (gettype($pro->link_rewrite) == 'array' && !empty(($pro->link_rewrite)[$lang])) {
                $pro->link_rewrite = ($pro->link_rewrite)[$lang];
            }
            //Get delivery_in_stock by lang
            if (gettype($pro->delivery_in_stock) == 'array' && !empty(($pro->delivery_in_stock)[$lang])) {
                $pro->delivery_in_stock = ($pro->delivery_in_stock)[$lang];
            }
            //Get delivery_out_stock by lang
            if (gettype($pro->delivery_out_stock) == 'array' && !empty(($pro->delivery_out_stock)[$lang])) {
                $pro->delivery_out_stock = ($pro->delivery_out_stock)[$lang];
            }
            //Get meta_description by lang
            if (gettype($pro->meta_description) == 'array' && !empty(($pro->meta_description)[$lang])) {
                $pro->meta_description = ($pro->meta_description)[$lang];
            }
            //Get meta_keywords by lang
            if (gettype($pro->meta_keywords) == 'array' && !empty(($pro->meta_keywords)[$lang])) {
                $pro->meta_keywords = ($pro->meta_keywords)[$lang];
            }
            //Get meta_title by lang
            if (gettype($pro->meta_title) == 'array' && !empty(($pro->meta_title)[$lang])) {
                $pro->meta_title = ($pro->meta_title)[$lang];
            }
            //Get available_now by lang
            if (gettype($pro->available_now) == 'array' && !empty(($pro->available_now)[$lang])) {
                $pro->available_now = ($pro->available_now)[$lang];
            }
            //Get available_later by lang
            if (gettype($pro->available_later) == 'array' && !empty(($pro->available_later)[$lang])) {
                $pro->available_later = ($pro->available_later)[$lang];
            }
            $quantity = $this->getStockQuantity($product['id_product']) ?? [];
            $pro->quantity = count($quantity) > 0 ? $quantity[0]['quantity'] : '0';
            $objects_products[] = $pro;
        }
        $this->_resourceConfiguration = $objects_products['empty']->getWebserviceParameters();

        $this->wsObject->setFieldsToDisplay();

        $this->output .= $this->objOutput->getContent($objects_products, null, $this->wsObject->fieldsToDisplay, $this->wsObject->depth, WebserviceOutputBuilder::VIEW_LIST, false);
    }
}