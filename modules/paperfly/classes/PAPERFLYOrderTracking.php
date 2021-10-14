<?php
/**
 * DHL Deutschepost
 *
 * @author    silbersaiten <info@silbersaiten.de>
 * @copyright 2020 silbersaiten
 * @license   See joined file licence.txt
 * @category  Module
 * @support   silbersaiten <support@silbersaiten.de>
 * @version   1.0.0
 * @link      http://www.silbersaiten.de
 */

class PAPERFLYOrderTracking extends ObjectModel
{
    public $id_order;
    public $id_cart;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'paperfly_order_tracking',
        'primary' => 'id_paperfly_order_tracking',
        'fields' => array(
            'reference' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_paperfly_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false),
            'tracking_number' => array('type' => self::TYPE_STRING, 'validate' => 'isUnsignedId', 'required' => false),
            'state' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

}
