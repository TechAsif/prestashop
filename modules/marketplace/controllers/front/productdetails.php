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

class MarketplaceProductDetailsModuleFrontController extends ModuleFrontController
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
                $mpIdProduct = Tools::getValue('id_mp_product');
                $sellerProduct = WkMpSellerProduct::getSellerProductByIdProduct(
                    $mpIdProduct,
                    $this->context->language->id
                );
                if ($sellerProduct && ($sellerProduct['id_seller'] == $seller['id_seller'])) {
                    if ($sellerProduct['active']) {
                        $idProduct = $sellerProduct['id_ps_product'];
                        $product = new Product($idProduct, false, $this->context->language->id);

                        $this->context->smarty->assign(array(
                            'link_rewrite' => $product->link_rewrite,
                            'id_product' => $idProduct,
                            'obj_product' => $product,
                            'is_approved' => 1,
                        ));
                    }

                    //Assign and display product active/inactive images
                    WkMpSellerProductImage::getProductImageDetails($mpIdProduct);

                    // show admin commission on product base price for seller
                    if (Configuration::get('WK_MP_SHOW_ADMIN_COMMISSION')) {
                        $objMpCommission = new WkMpCommission();
                        $adminCommission = $objMpCommission->finalCommissionSummaryForSeller($seller['id_seller']);
                        if ($adminCommission) {
                            $this->context->smarty->assign('admin_commission', $adminCommission);
                        }
                    }

                    $conversionRate = 1;
                    $idCurrencyDefault = Configuration::get('PS_CURRENCY_DEFAULT');
                    if ($idCurrencyDefault != $this->context->currency->id) {
                        $currencyOrigin = new Currency((int)$idCurrencyDefault);
                        $conversionRate /= $currencyOrigin->conversion_rate;
                        $currencySelect = new Currency((int) $this->context->currency->id);
                        $conversionRate *= $currencySelect->conversion_rate;
                    }

                    $sellerProduct['price'] = Tools::displayPrice($sellerProduct['price'] * $conversionRate);

                    $this->context->smarty->assign(array(
                        'product' => $sellerProduct,
                        'logic' => 3,
                        'is_seller' => 1,
                        'static_token' => Tools::getToken(false),
                    ));

                    $this->defineJsVars();
                    $this->setTemplate('module:marketplace/views/templates/front/product/productdetails.tpl');
                } else {
                    Tools::redirect($this->context->link->getModuleLink('marketplace', 'dashboard'));
                }
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
            }
        } else {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }
    }

    public function defineJsVars()
    {
        $jsVars = array(
                'ajax_urlpath' => $this->context->link->getModuleLink('marketplace', 'productimageedit'),
                'image_drag_drop' => 1,
                'space_error' => $this->module->l('Space is not allowed.', 'productdetails'),
                'confirm_delete_msg' => $this->module->l('Are you sure you want to delete this image?', 'productdetails'),
                'delete_msg' => $this->module->l('Deleted.', 'productdetails'),
                'error_msg' => $this->module->l('An error occurred.', 'productdetails'),
                'update_success' => $this->module->l('Updated Successfully', 'productdetails'),
            );

        Media::addJsDef($jsVars);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'productdetails'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard')
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Product Details', 'productdetails'),
            'url' => ''
        );
        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->addJqueryPlugin('tablednd');
        $this->addjQueryPlugin('growl', null, false);
        $this->registerJavascript(
            'mp-imageedit',
            'modules/'.$this->module->name.'/views/js/imageedit.js'
        );
        $this->registerStylesheet(
            'marketplace_account',
            'modules/'.$this->module->name.'/views/css/marketplace_account.css'
        );
        $this->registerStylesheet(
            'mp-product-details',
            'modules/'.$this->module->name.'/views/css/mpproductdetails.css'
        );
    }
}
