<?php


class WebserviceSpecificManagementAttribute implements WebserviceSpecificManagementInterface
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

    public function getAttributes($id_product)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
            SELECT  `id_product_attribute`
            FROM `' . _DB_PREFIX_ . 'product_attribute`
            WHERE `id_product` = ' . $id_product . '
            '
        );
    }

    public function getStockQuantity($id_product_attribute, $id_shop_default)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
            SELECT  `id_stock_available`, `quantity`
            FROM `' . _DB_PREFIX_ . 'stock_available`
            WHERE `id_product_attribute` = ' . $id_product_attribute . ' ' . ($this->isExistIdShop($id_shop_default) ? ' AND `id_shop` = ' . $id_shop_default . '' : '') . ' 
            LIMIT 1'
        );
        if (empty($result)) return null;
        return $result;
    }

    public function isExistIdShop($id_shop_default) {
        if (empty($id_shop_default)) return false;
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
            SELECT  `id_shop`
            FROM `' . _DB_PREFIX_ . 'shop`
            WHERE `id_shop` = ' . $id_shop_default . '
            LIMIT 1'
        );
        if (empty($result)) return false;
        return true;
    }

    public function getSalePrice($product, $combination) {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
            SELECT  `id_specific_price`, `reduction`, `reduction_type`
            FROM `' . _DB_PREFIX_ . 'specific_price`
            WHERE `id_product` = ' . $product->id . '
            LIMIT 1'
        );
        if (empty($result)) return $combination->price;
        if ($result[0]['reduction_type'] != "percentage") return $combination->price - $result[0]['reduction'];
        return ($combination->price) * (1 - $result[0]['reduction']);
    }


    public function manage()

    {

        $objects_products = array();

        $objects_products['empty'] = new Combination();

        $id_product = $this->wsObject->urlFragments['id_product'];

        $id_shop_default = $this->wsObject->urlFragments['id_shop_default'] ?? null;

        $combinations = $this->getAttributes($id_product);

        $product = new Product($id_product);
        if (empty($product->price)) {
            $product->price = 0.0;
        }

        foreach ($combinations as $combination) {
            $combination = new Combination($combination['id_product_attribute']);
            if ($combination->id_product != $product->id) continue;
            $quantity = $this->getStockQuantity($combination->id, $id_shop_default) ?? [];
            $combination->quantity = count($quantity) > 0 ? $quantity[0]['quantity'] : '0';
            if (Configuration::get('CUSTOMAPI_ENABLE_TAX', true) == true) {
                $combination->price = number_format((float) (Product::getPriceStatic($combination->id_product, true, $combination->id, 2, null, false, false)), 2, '.', '');
            } else {
                $combination->price = number_format((float) ($product->price + $combination->price), 2, '.', '');
            }
            if (Configuration::get('CUSTOMAPI_ENABLE_TAX', true) == true) {
                $combination->wholesale_price = number_format((float) (Product::getPriceStatic($combination->id_product, true, $combination->id, 2)), 2, '.', '');
            } else {
                $combination->wholesale_price = number_format((float) ($this->getSalePrice($product, $combination)), 2, '.', '');
            }
            $objects_products[] = $combination;
        }
        $this->_resourceConfiguration = $objects_products['empty']->getWebserviceParameters();

        $this->wsObject->setFieldsToDisplay();

        $this->output .= $this->objOutput->getContent($objects_products, null, $this->wsObject->fieldsToDisplay, $this->wsObject->depth, WebserviceOutputBuilder::VIEW_LIST, false);
    }
}
