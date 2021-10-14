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
            if (Tools::getIsset('getAddressAdditions')) {
                $this->context->smarty->assign(
                    array(
                        'self' => dirname(__FILE__).'/../..',
                    )
                );

                $this->setTemplate((version_compare(_PS_VERSION_, '1.7', '<')?
                            '':'module:dhldp/views/templates/front/').'address_additions'.(version_compare(_PS_VERSION_, '1.7', '<')?'':'-17').'.tpl');

                die($this->context->smarty->fetch($this->template));
            } elseif (Tools::getIsset('getPackstations') || Tools::getIsset('getPostfiliales')) {
                $address = array(
                    'street' => Tools::getValue('street', ''),
                    'streetNo' => Tools::getValue('streetNo', ''),
                    'zip' => Tools::getValue('zip', ''),
                    'city' => Tools::getValue('city', ''),
                );
                if (Tools::getIsset('getPackstations')) {
                    die(Tools::jsonEncode($this->module->dhldp_api->getPackstations($address)));
                } else {
                    die(Tools::jsonEncode($this->module->dhldp_api->getPostfiliales($address)));
                }
            } elseif (Tools::getValue('action') == 'setprivate') {
                die(DHLDPOrder::updatePermission((int)Context::getContext()->cart->id, (Tools::getValue('permission', 0)) == '1' ? 1 : 0));
            } elseif (Tools::getValue('action') == 'getprivate') {
                if (DHLDPOrder::hasPermissionForTransferring((int)Context::getContext()->cart->id) === false) {
                    DHLDPOrder::updatePermission((int)Context::getContext()->cart->id, 0);
                }
                $permission = DHLDPOrder::hasPermissionForTransferring((int)Context::getContext()->cart->id);
                die(Tools::jsonEncode(array('permission' => ($permission)?1:0)));
            }
        }
    }
}
