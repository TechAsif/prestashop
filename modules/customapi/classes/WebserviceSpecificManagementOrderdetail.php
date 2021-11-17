<?php

class WebserviceSpecificManagementOrderdetail implements WebserviceSpecificManagementInterface
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


    function insertOrderDetail($order_id, $products)
    {
        //insert order detail
        $name = ($products->name)['1'];
        $total_price =  $products->price * $products->quantity;

        Db::getInstance()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'order_detail` (id_order, id_shop, product_id, product_name, product_weight, tax_name, product_quantity, product_price, product_attribute_id, unit_price_tax_incl, unit_price_tax_excl, total_price_tax_incl, total_price_tax_excl)
                VALUES (' . $order_id . ', ' . Shop::getContextShopID() . ', ' . $products->id . ', "' . $name . '", ' . $products->weight . ', "default", ' . $products->quantity . ', ' . $products->price . ', ' . ($products->id_supplier != '-1' ? $products->id_supplier : NULL) . ', ' . $products->price . ', ' . $products->price . ', ' . $total_price . ', ' . $total_price . ')'
        );

        return Db::getInstance()->Insert_ID();
    }

    public function manage()

    {
        $objects_products = array();
        $objects_products['empty'] = new OrderDetail();
        $order_id = $this->wsObject->urlFragments['order_id'] ?? null;
        $product = new Product($this->wsObject->urlFragments['product_id'] ?? null);

        $objects_products[] = new OrderDetail($this->insertOrderDetail($order_id, $product));


        // for ($i = 0; $i < count($matches); $i++) {
        //     $product = new Product($matches[$i]);
        //     $product->quantity = $quantity[$i];
        //     $product->id_supplier = $attribute[$i];
        //     if ($attribute[$i] != '-1') {
        //         $combination = new Combination($attribute[$i]);
        //         $product->price = number_format((float) ($product->price + $combination->price), 2, '.', '');
        //     }
        //     $products[] = $product;
        // }
        // $objects_products[] = $this->insertOrder($order, $products);
        $this->_resourceConfiguration = $objects_products['empty']->getWebserviceParameters();
        // $this->_resourceConfiguration = $products['empty']->getWebserviceParameters();
        $this->wsObject->setFieldsToDisplay();

        $this->output .= $this->objOutput->getContent($objects_products, null, $this->wsObject->fieldsToDisplay, $this->wsObject->depth, WebserviceOutputBuilder::VIEW_LIST, false);
    }
}
