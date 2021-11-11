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

class WkMpSellerOrderStatus extends ObjectModel
{
    /**
    * id_order of prestashop
    * @var int
    */
    public $id_order;

    /**
    * id_order_status prestashop order state id
    * @var int
    */
    public $current_state;

    /**
    * Tracking_number entered by seller itself
    * @var string
    */
    public $tracking_number;

    /**
    * Tracking url entered by seller itself
    * @var string
    */
    public $tracking_url;

    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'wk_mp_seller_order_status',
        'primary' => 'id_order_status',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id_seller' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'current_state' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'tracking_number' => array('type' => self::TYPE_STRING, 'validate' => 'isTrackingNumber'),
            'tracking_url' => array('type' => self::TYPE_STRING),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        ),
    );

    public function getCurrentOrderState($idOrder, $idSeller)
    {
        $result = Db::getInstance()->getValue(
            'SELECT `current_state` FROM '._DB_PREFIX_.'wk_mp_seller_order_status
            WHERE `id_order` ='.(int) $idOrder.' AND `id_seller` ='.(int) $idSeller
        );
        if ($result) {
            return $result;
        }
        return false;
    }

    public function isOrderExist($idOrder, $idSeller)
    {
        $result = Db::getInstance()->getRow(
            'SELECT * FROM '._DB_PREFIX_.'wk_mp_seller_order_status
            WHERE `id_order` ='.(int) $idOrder.' AND `id_seller` ='.(int) $idSeller
        );
        if ($result) {
            return $result;
        }
        return false;
    }

    public function updateSellerOrderHistory($idOrder, $idSeller, $idOrderState)
    {
        return Db::getInstance()->insert(
            'wk_mp_seller_order_history',
            array(
                'id_order' => (int) $idOrder,
                'id_seller' => (int) $idSeller,
                'id_order_state' => (int) $idOrderState,
                'date_add' => date('Y-m-d H:i:s'),
            )
        );
    }

    public function processSellerOrderStatus($idOrder, $idSeller, $idOrderState)
    {
        $currentStatus = $this->getCurrentOrderState($idOrder, $idSeller);
        if ($currentStatus != $idOrderState) {
            $alreadyExist = $this->isOrderExist($idOrder, $idSeller);
            if ($alreadyExist) {
                $objOrderStatus = new self($alreadyExist['id_order_status']);
            } else {
                $objOrderStatus = new self();
                $objOrderStatus->tracking_number = '';
                $objOrderStatus->tracking_url = '';
            }
            $objOrderStatus->id_order = $idOrder;
            $objOrderStatus->id_seller = $idSeller;
            $objOrderStatus->current_state = $idOrderState;
            if ($objOrderStatus->save()) {
                $this->updateSellerOrderHistory($idOrder, $idSeller, $idOrderState);
            }
        }
    }

    public function getHistory($idLang, $idSeller, $idOrder)
    {
        $result = Db::getInstance()->executeS(
            'SELECT
        	os.*,
        	oh.*,
        	osl.`name` as ostate_name
            FROM `'._DB_PREFIX_.'orders` o
            LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller_order_history` oh ON o.`id_order` = oh.`id_order`
            LEFT JOIN `'._DB_PREFIX_.'order_state` os ON os.`id_order_state` = oh.`id_order_state`
            LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl
            ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int) $idLang.')
            WHERE oh.id_order = '.(int) $idOrder.'
            AND oh.`id_seller` ='.(int) $idSeller.'
            ORDER BY oh.date_add DESC, oh.id_order_history DESC'
        );
        if ($result) {
            return $result;
        }
        return false;
    }

    /**
     * Update order state by using Order Id and new order state
     *
     * @param  int $idOrder Order ID
     * @param  int $idOrderState New Order State
     * @return boolean true/false
     */
    public function updateOrderByIdOrderAndIdOrderState($idOrder, $idOrderState)
    {
        if (isset($idOrder) && $idOrder != '' && isset($idOrderState) && $idOrderState != '') {
            $order = new Order($idOrder);
            if (isset($order)) {
                $orderState = new OrderState($idOrderState);
                $currentOrderState = $order->getCurrentOrderState();
                if ($currentOrderState->id != $orderState->id) {
                    // Create new OrderHistory
                    $history = new OrderHistory();
                    $history->id_order = $order->id;

                    $useExistingsPayment = false;
                    if (!$order->hasInvoice()) {
                        $useExistingsPayment = true;
                    }
                    $history->changeIdOrderState((int) $orderState->id, $order, $useExistingsPayment);

                    $carrier = new Carrier($order->id_carrier, $order->id_lang);
                    $templateVars = array();
                    if ($history->id_order_state == Configuration::get('PS_OS_SHIPPING') && $order->shipping_number) {
                        $templateVars = array('{followup}' => str_replace('@', $order->shipping_number, $carrier->url));
                    }
                    // Save all changes
                    if ($history->addWithemail(true, $templateVars)) {
                        // synchronizes quantities if needed..
                        if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                            foreach ($order->getProducts() as $product) {
                                if (StockAvailable::dependsOnStock($product['product_id'])) {
                                    StockAvailable::synchronize($product['product_id'], (int) $product['id_shop']);
                                }
                            }
                        }

                        return true;
                    }
                } elseif ($currentOrderState->id == $orderState->id) {
                    return true;
                }
            }
        }

        return false;
    }
}
