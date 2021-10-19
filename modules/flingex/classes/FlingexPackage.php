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

class FlingexPackage extends ObjectModel
{
    public $id_flingex_label;
    public $length;
    public $width;
    public $height;
    public $weight;
    public $package_type;
    public $shipment_number;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'flingex_package',
        'primary' => 'id_flingex_package',
        'fields' => array(
            'id_flingex_label' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'length' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'width' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'height' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'weight' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true),
            'package_type' => array('type' => self::TYPE_STRING, 'validate' => 'isMessage', 'size' => 30),
            'shipment_number' => array('type' => self::TYPE_STRING, 'validate' => 'isMessage', 'size' => 255),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate')
        ),
    );
}
