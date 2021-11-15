<?php


class WebserviceSpecificManagementOrder implements WebserviceSpecificManagementInterface
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

    function getModules()
    {
        $hook_payment = 'Payment';
        if (Db::getInstance()->getValue('SELECT `id_hook` FROM `' . _DB_PREFIX_ . 'hook` WHERE `name` = \'paymentOptions\'')) {
            $hook_payment = 'paymentOptions';
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT DISTINCT m.`id_module`, h.`id_hook`, m.`name`, hm.`position`
        FROM `' . _DB_PREFIX_ . 'module` m
        LEFT JOIN `' . _DB_PREFIX_ . 'hook_module` hm ON hm.`id_module` = m.`id_module`
        LEFT JOIN `' . _DB_PREFIX_ . 'hook` h ON hm.`id_hook` = h.`id_hook`
        WHERE h.`name` = \'' . pSQL($hook_payment) . '\'
        GROUP BY hm.id_hook, hm.id_module
        ORDER BY hm.`position`, m.`name` DESC'
        );
    }

    function getModuleDisplayName($module)
    {
        // Config file
        $config_file = _PS_MODULE_DIR_ . $module . '/config.xml';
        // For "en" iso code, we keep the default config.xml name
        if (!file_exists($config_file)) {
            return 'Module ' . ucfirst($module);
        }

        // Load config.xml
        libxml_use_internal_errors(true);
        $xml_module = @simplexml_load_file($config_file);
        if (!$xml_module) {
            return 'Module ' . ucfirst($module);
        }

        // Return Display Name
        return Module::configXmlStringFormat($xml_module->displayName);
    }

    function getOrderStateByModule($module)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
            SELECT  `id_order_state`
            FROM `' . _DB_PREFIX_ . 'order_state`
            WHERE `module_name` = \'' . $module . '\'
            LIMIT 1'
        );
        if (empty($result)) return 1;
        return $result[0]['id_order_state'];
    }

    function _getFormatedAddress(Address $the_address, $line_sep, $fields_style = array())
    {
        return AddressFormat::generateAddress($the_address, array('avoid' => array()), $line_sep, ' ', $fields_style);
    }

    function sendEmail($order_id, $id_customer)
    {
        // Order is reloaded because the status just changed
        $order = new Order((int) $order_id);
        $customer = new Customer($id_customer);
        $currency = null;

        // Send an e-mail to customer (one order = one email)
        $invoice = new Address((int) $order->id_address_invoice);
        $delivery = new Address((int) $order->id_address_delivery);
        $delivery_state = $delivery->id_state ? new State((int) $delivery->id_state) : false;
        $invoice_state = $invoice->id_state ? new State((int) $invoice->id_state) : false;
        $carrier = $order->id_carrier ? new Carrier($order->id_carrier) : false;
        $product_list_txt = '';
        $product_list_html = '';
        $cart_rules_list_txt = '';
        $cart_rules_list_html = '';


        $data = array(
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{email}' => $customer->email,
            '{delivery_block_txt}' => $this->_getFormatedAddress($delivery, AddressFormat::FORMAT_NEW_LINE),
            '{invoice_block_txt}' => $this->_getFormatedAddress($invoice, AddressFormat::FORMAT_NEW_LINE),
            '{delivery_block_html}' => $this->_getFormatedAddress($delivery, '<br />', array(
                'firstname' => '<span style="font-weight:bold;">%s</span>',
                'lastname' => '<span style="font-weight:bold;">%s</span>',
            )),
            '{invoice_block_html}' => $this->_getFormatedAddress($invoice, '<br />', array(
                'firstname' => '<span style="font-weight:bold;">%s</span>',
                'lastname' => '<span style="font-weight:bold;">%s</span>',
            )),
            '{delivery_company}' => $delivery->company,
            '{delivery_firstname}' => $delivery->firstname,
            '{delivery_lastname}' => $delivery->lastname,
            '{delivery_address1}' => $delivery->address1,
            '{delivery_address2}' => $delivery->address2,
            '{delivery_city}' => $delivery->city,
            '{delivery_postal_code}' => $delivery->postcode,
            '{delivery_country}' => $delivery->country,
            '{delivery_state}' => $delivery->id_state ? $delivery_state->name : '',
            '{delivery_phone}' => ($delivery->phone) ? $delivery->phone : $delivery->phone_mobile,
            '{delivery_other}' => $delivery->other,
            '{invoice_company}' => $invoice->company,
            '{invoice_vat_number}' => $invoice->vat_number,
            '{invoice_firstname}' => $invoice->firstname,
            '{invoice_lastname}' => $invoice->lastname,
            '{invoice_address2}' => $invoice->address2,
            '{invoice_address1}' => $invoice->address1,
            '{invoice_city}' => $invoice->city,
            '{invoice_postal_code}' => $invoice->postcode,
            '{invoice_country}' => $invoice->country,
            '{invoice_state}' => $invoice->id_state ? $invoice_state->name : '',
            '{invoice_phone}' => ($invoice->phone) ? $invoice->phone : $invoice->phone_mobile,
            '{invoice_other}' => $invoice->other,
            '{order_name}' => $order->getUniqReference(),
            '{date}' => Tools::displayDate(date('Y-m-d H:i:s'), null, 1),
            '{carrier}' => $carrier->name,
            '{payment}' => Tools::substr($order->payment, 0, 255),
            '{products}' => $product_list_html,
            '{products_txt}' => $product_list_txt,
            '{discounts}' => $cart_rules_list_html,
            '{discounts_txt}' => $cart_rules_list_txt,
            '{total_paid}' => $order->total_paid,
            '{total_products}' => $order->total_products,
            '{total_discounts}' => $order->total_discounts,
            '{total_shipping}' => $order->total_shipping,
            '{total_shipping_tax_excl}' => $order->total_shipping_tax_excl,
            '{total_shipping_tax_incl}' => $order->total_shipping_tax_incl,
            '{total_wrapping}' => $order->total_wrapping,
            '{total_tax_paid}' => ($order->total_products_wt - $order->total_products) + ($order->total_shipping_tax_incl - $order->total_shipping_tax_excl)
        );

        if (Validate::isEmail($customer->email)) {
            Mail::Send(
                (int) $order->id_lang,
                'order_conf',
                'Order confirmation',
                $data,
                $customer->email,
                $customer->firstname . ' ' . $customer->lastname,
                null,
                null,
                null,
                null,
                _PS_MAIL_DIR_,
                false,
                (int) $order->id_shop
            );
        }
    }

    function insertOrderDetail($order_id, $product, $id_lang)
    {
        //insert order detail
        $name = is_string($product->name) ? $product->name : '';
        if (gettype($product->name == 'array')) {
            $values = array_values($product->name);
            if (!empty(($product->name)[$id_lang])) {
                $name = ($product->name)[$id_lang];
            } else {
                if (!empty($values)) {
                    $name = $values[0];
                } else {
                    $name = '';
                }
            }
        }

        $total_price =  $product->price * $product->quantity;

        if (empty($product->id_supplier)) {
            Db::getInstance()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'order_detail` (id_order, id_shop, product_id, product_name, product_weight, tax_name, product_quantity, product_price, product_attribute_id, unit_price_tax_incl, unit_price_tax_excl, total_price_tax_incl, total_price_tax_excl)
                    VALUES (' . $order_id . ', ' . Shop::getContextShopID() . ', ' . $product->id . ', "' . $name . '", ' . $product->weight . ', "default", ' . $product->quantity . ', ' . $product->price . ', NULL, ' . $product->price . ', ' . $product->price . ', ' . $total_price . ', ' . $total_price . ')'
            );
        } else {
            Db::getInstance()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'order_detail` (id_order, id_shop, product_id, product_name, product_weight, tax_name, product_quantity, product_price, product_attribute_id, unit_price_tax_incl, unit_price_tax_excl, total_price_tax_incl, total_price_tax_excl)
                    VALUES (' . $order_id . ', ' . Shop::getContextShopID() . ', ' . $product->id . ', "' . $name . '", ' . $product->weight . ', "default", ' . $product->quantity . ', ' . $product->price . ', ' . $product->id_supplier . ', ' . $product->price . ', ' . $product->price . ', ' . $total_price . ', ' . $total_price . ')'
            );
        }

        return Db::getInstance()->Insert_ID();
    }

    function insertOrder($order, $products)
    {
        $order->id_cart = 'default';
        $order->total_paid = number_format((float) ($order->total_shipping + $order->total_products), 2, '.', '');
        $order->secure_key = md5(uniqid(mt_rand(0, mt_getrandmax()), true));
        $modules = $this->getModules();
        foreach ($modules as $module) {
            $name = $this->getModuleDisplayName($module['name']);
            if ($name == $order->payment) {
                $order->module = $module['name'];
                break;
            }
        }
        //insert order
        $result = Db::getInstance()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'orders` (id_carrier, id_lang, id_customer, id_cart, id_currency, id_address_delivery, id_address_invoice, current_state, payment, invoice_date, delivery_date, date_add, date_upd, total_shipping, total_shipping_tax_incl, total_shipping_tax_excl, total_products, reference, total_paid, total_paid_tax_incl, total_paid_tax_excl, module, secure_key)
            VALUES (' . $order->id_carrier . ', ' . $order->id_lang . ', ' . $order->id_customer . ', ' . $order->id_cart . ', ' . $order->id_currency . ', ' . $order->id_address_delivery . ', ' . $order->id_address_invoice . ', ' . $order->current_state . ', "' . $order->payment . '", "' . $order->invoice_date . '", "' . $order->delivery_date . '", "' . $order->date_add . '", "' . $order->date_upd . '", ' . $order->total_shipping . ', ' . $order->total_shipping . ', ' . $order->total_shipping . ', ' . $order->total_products . ', "' . Order::generateReference() . '", ' . $order->total_paid . ', ' . $order->total_paid . ', ' . $order->total_paid . ', "' . $order->module . '", "' . $order->secure_key . '")'
        );
        if ($result == true) {
            $order->id = Db::getInstance()->Insert_ID();
        }

        //insert order detail
        for ($i = 0; $i < count($products); $i++) {
            $this->insertOrderDetail($order->id, $products[$i], $order->id_lang);
        }

        //insert order history
        Db::getInstance()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'order_history` (id_employee, id_order, id_order_state, date_add)
            VALUES ( 0 , ' . $order->id . ', ' . !empty($order->current_state) ? $order->current_state : $this->getOrderStateByModule($order->module) . ', "' . $order->date_add . '")'
        );

        //insert order carrier
        Db::getInstance()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'order_carrier` (id_order, id_carrier, id_order_invoice, weight, shipping_cost_tax_excl, shipping_cost_tax_incl, date_add )
            VALUES ( ' . $order->id . ', ' . $order->id_carrier . ', 0, 0.000, ' . $order->total_shipping . ', ' . $order->total_shipping . ', "' . $order->date_add . '")'
        );

        //update product amount
        for ($i = 0; $i < count($products); $i++) {
            $quantity = $products[$i]->quantity;
            Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'stock_available` 
                    SET `quantity` = `quantity` - ' . $quantity . '
                    WHERE `id_product` = ' . $products[$i]->id . ' ' . (!empty($products[$i]->id_supplier) ? ' AND `id_product_attribute` = ' . $products[$i]->id_supplier . ' ' : '') . ' '
            );
        }
        if (Configuration::get('CUSTOMAPI_SEND_MAIL', true) == true) {
            $this->sendEmail($order->id, $order->id_customer);
        }

        return $order;
    }

    public function manage()

    {
        $objects_products = array();
        $objects_products['empty'] = new Order();

        $order = new Order();
        $order->id_carrier = $this->wsObject->urlFragments['id_carrier'];
        $order->id_lang = $this->wsObject->urlFragments['id_lang'];
        $order->id_customer = $this->wsObject->urlFragments['id_customer'];
        $order->id_currency = $this->wsObject->urlFragments['id_currency'];
        $order->id_address_delivery = $this->wsObject->urlFragments['id_address_delivery'];
        $order->id_address_invoice = $this->wsObject->urlFragments['id_address_invoice'];
        $order->current_state = $this->wsObject->urlFragments['current_state'];
        $order->payment = $this->wsObject->urlFragments['payment'];
        $order->invoice_date = "0000-00-00";
        $order->delivery_date = "0000-00-00";
        $order->date_add = date("Y-m-d H:i:s");
        $order->date_upd = date("Y-m-d H:i:s");
        /*
            more detail
        */
        $order->module = $this->wsObject->urlFragments['module'];
        $order->total_shipping = $this->wsObject->urlFragments['total_shipping'];
        $order->total_products = $this->wsObject->urlFragments['total_products'];
        $matches = json_decode($this->wsObject->urlFragments['products']);
        $quantity = json_decode($this->wsObject->urlFragments['quantity']);
        $attribute = json_decode($this->wsObject->urlFragments['attribute']);
        $products = array();
        for ($i = 0; $i < count($matches); $i++) {
            $product = new Product($matches[$i]);
            $product->quantity = $quantity[$i];
            $product->id_supplier = strval($attribute[$i]) != '-1' ? (int)$attribute[$i] : NULL;
            if (!empty($product->id_supplier)) {
                $combination = new Combination($attribute[$i]);
                $product->price = number_format((float) ($product->price + $combination->price), 2, '.', '');
            }
            $products[] = $product;
        }
        $objects_products[] = $this->insertOrder($order, $products);
        $this->_resourceConfiguration = $objects_products['empty']->getWebserviceParameters();
        // $this->_resourceConfiguration = $products['empty']->getWebserviceParameters();
        $this->wsObject->setFieldsToDisplay();

        $this->output .= $this->objOutput->getContent($objects_products, null, $this->wsObject->fieldsToDisplay, $this->wsObject->depth, WebserviceOutputBuilder::VIEW_LIST, false);
    }
}
