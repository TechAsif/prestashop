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

class PAPERFLYOrder extends ObjectModel
{
    public $id_order;
    public $id_cart;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'paperfly_order',
        'primary' => 'id_paperfly_order',
        'fields' => array(
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => false),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'current_state' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'tracking_number' => array('type' => self::TYPE_STRING, 'validate' => 'isUnsignedId'),
            'reference' => array('type' => self::TYPE_STRING, 'validate' => 'isUnsignedId'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate')
        ),
    );

}
