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

class DHLDPAddressModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();
        if (Tools::getIsset('ajax')) {
            
        }
    }
}
