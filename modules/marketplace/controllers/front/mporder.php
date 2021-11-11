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

class MarketplaceMpOrderModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (isset($this->context->customer->id)) {
            $idCustomer = $this->context->customer->id;
            //Override customer id if any staff of seller want to use this controller
            if (Module::isEnabled('mpsellerstaff')) {
                $getCustomerId = WkMpSellerStaff::overrideMpSellerCustomerId($idCustomer);
                if ($getCustomerId) {
                    $idCustomer = $getCustomerId;
                }
            }

            $seller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
            if ($seller && $seller['active']) {
                // ---------- Get Seller's Order Records ---------//
                $objMpOrder = new WkMpSellerOrder();
                $objOrderStatus = new WkMpSellerOrderStatus();
                if ($mporders = $objMpOrder->getSellerOrders($this->context->language->id, $idCustomer)) {
                    foreach ($mporders as &$order) {
                        $objOrder = new Order($order['id_order']);
                        if (!$objMpOrder->checkSellerOrder($objOrder, $seller['id_seller'])) {
                            $idOrderState = $objOrderStatus->getCurrentOrderState($order['id_order'], $seller['id_seller']);
                            if ($idOrderState) {
                                $state = new OrderState($idOrderState, $this->context->language->id);
                                $order['order_status'] = $state->name;
                            }
                        }
                        $order['buyer_info'] = new Customer($order['buyer_id_customer']);
                        if ($sellerOrderTotal = $objMpOrder->getTotalOrder($order['id_order'], $idCustomer)) {
                            //Add shipping amount in total orders
                            if ($sellerShippingEarning = WkMpAdminShipping::getSellerShippingByIdOrder($order['id_order'], $idCustomer)) {
                                $sellerOrderTotal += $sellerShippingEarning;
                            }

                            $order['total_paid'] = Tools::displayPrice($sellerOrderTotal, (int) $order['id_currency']);
                            $order['total_paid_without_sign'] = $sellerOrderTotal;
                        }
                    }

                    $this->context->smarty->assign('mporders', $mporders);
                }
                //----- End of Seller's order records ------

                $this->context->smarty->assign(array(
                        'logic' => 4,
                        'is_seller' => $seller['active'],
                    ));

                $this->defineJSVars();
                $this->setTemplate('module:marketplace/views/templates/front/order/mporder.tpl');
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
            }
        } else {
            Tools::redirect('index.php?controller=authentication&back='.urlencode($this->context->link->getModuleLink('marketplace', 'mporder')));
        }
    }

    public function defineJSVars()
    {
        $jsVars = array(
                'mporderdetails_link' => $this->context->link->getModuleLink('marketplace', 'mporderdetails'),
                'display_name' => $this->module->l('Display', 'mporder'),
                'records_name' => $this->module->l('records per page', 'mporder'),
                'no_product' => $this->module->l('No order found', 'mporder'),
                'show_page' => $this->module->l('Showing page', 'mporder'),
                'show_of' => $this->module->l('of', 'mporder'),
                'no_record' => $this->module->l('No records available', 'mporder'),
                'filter_from' => $this->module->l('filtered from', 'mporder'),
                't_record' => $this->module->l('total records', 'mporder'),
                'search_item' => $this->module->l('Search', 'mporder'),
                'p_page' => $this->module->l('Previous', 'mporder'),
                'n_page' => $this->module->l('Next', 'mporder'),
            );

        if (Configuration::get('PS_REWRITING_SETTINGS')) {
            $jsVars['friendly_url'] = 1;
        } else {
            $jsVars['friendly_url'] = 0;
        }
        Media::addJsDef($jsVars);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'mporder'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard'),
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Orders', 'mporder'),
            'url' => '',
        );

        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->registerStylesheet('marketplace_account', 'modules/'.$this->module->name.'/views/css/marketplace_account.css');
        $this->registerStylesheet('marketplace_global', 'modules/'.$this->module->name.'/views/css/mp_global_style.css');

        //data table file included
        $this->registerStylesheet('datatable_bootstrap', 'modules/'.$this->module->name.'/views/css/datatable_bootstrap.css');
        $this->registerJavascript('mp-jquery-dataTables', 'modules/'.$this->module->name.'/views/js/jquery.dataTables.min.js');
        $this->registerJavascript('mp-dataTables.bootstrap', 'modules/'.$this->module->name.'/views/js/dataTables.bootstrap.js');
        $this->registerJavascript('mp-order', 'modules/'.$this->module->name.'/views/js/mporder.js');
    }
}
