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

class MarketplaceProductListModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        if (isset($this->context->customer->id)) {
            $idCustomer = $this->context->customer->id;
            $addPermission = 1;
            $editPermission = 1;
            $deletePermission = 1;

            //Override customer id if any staff of seller want to use this controller
            if (Module::isEnabled('mpsellerstaff')) {
                $staffDetails = WkMpSellerStaff::getStaffInfoByIdCustomer($idCustomer);
                if ($staffDetails
                    && $staffDetails['active']
                    && $staffDetails['id_seller']
                    && $staffDetails['seller_status']
                ) {
                    $idTab = WkMpTabList::MP_PRODUCT_TAB; //For Product
                    $staffTabDetails = WkMpTabList::getStaffPermissionWithTabName(
                        $staffDetails['id_staff'],
                        $this->context->language->id,
                        $idTab
                    );
                    if ($staffTabDetails) {
                        $addPermission = $staffTabDetails['add'];
                        $editPermission = $staffTabDetails['edit'];
                        $deletePermission = $staffTabDetails['delete'];
                    }
                }

                $getCustomerId = WkMpSellerStaff::overrideMpSellerCustomerId($idCustomer);
                if ($getCustomerId) {
                    $idCustomer = $getCustomerId;
                }
            }

            $seller = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
            if ($seller && $seller['active']) {
                //delete selected checkbox process
                if ($selectedProducts = Tools::getValue('mp_product_selected')) {
                    $this->deleteSelectedProducts($selectedProducts, $seller['id_seller']);
                }

                //change product status if seller can activate/deactivate their product
                if (Tools::getValue('mp_product_status')
                && Configuration::get('WK_MP_SELLER_PRODUCTS_SETTINGS')) {
                    $this->changeProductStatus($seller['id_seller']);
                }

                if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                    $idLang = $this->context->language->id;
                } else {
                    if (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '1') {
                        $idLang = Configuration::get('PS_LANG_DEFAULT');
                    } elseif (Configuration::get('WK_MP_MULTILANG_DEFAULT_LANG') == '2') {
                        $idLang = $seller['default_lang'];
                    }
                }
                $objLang = new Language((int) $idLang);
                if (!$objLang->active) {
                    $idLang = Configuration::get('PS_LANG_DEFAULT');
                }

                $sellerProduct = WkMpSellerProduct::getSellerProduct($seller['id_seller'], 'all', $idLang);
                if ($sellerProduct) {
                    $sellerProduct = $this->getProductDetails($sellerProduct);
                } else {
                    $sellerProduct = array();
                }

                $this->context->smarty->assign(array(
                    'products_status' => Configuration::get('WK_MP_SELLER_PRODUCTS_SETTINGS'),
                    'imageediturl' => $this->context->link->getModuleLink('marketplace', 'productimageedit'),
                    'product_lists' => $sellerProduct,
                    'is_seller' => $seller['active'],
                    'logic' => 3,
                    'static_token' => Tools::getToken(false),
                    'add_permission' => $addPermission,
                    'edit_permission' => $editPermission,
                    'delete_permission' => $deletePermission,
                ));

                $this->defineJSVars();
                $this->setTemplate('module:marketplace/views/templates/front/product/productlist.tpl');
            } else {
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'sellerrequest'));
            }
        } else {
            Tools::redirect('index.php?controller=authentication&back='.urlencode($this->context->link->getModuleLink('marketplace', 'productlist')));
        }
    }

    public function defineJSVars()
    {
        $jsVars = array(
                'ajax_urlpath' => $this->context->link->getModuleLink('marketplace', 'productimageedit'),
                'image_drag_drop' => 1,
                'space_error' => $this->module->l('Space is not allowed.', 'productlist'),
                'confirm_delete_msg' => $this->module->l('Are you sure you want to delete?', 'productlist'),
                'confirm_duplicate_msg' => $this->module->l('Are you sure you want to duplicate?', 'productlist'),
                'delete_msg' => $this->module->l('Deleted.', 'productlist'),
                'error_msg' => $this->module->l('An error occurred.', 'productlist'),
                'checkbox_select_warning' => $this->module->l('You must select at least one element.', 'productlist'),
                'display_name' => $this->module->l('Display', 'productlist'),
                'records_name' => $this->module->l('records per page', 'productlist'),
                'no_product' => $this->module->l('No product found', 'productlist'),
                'show_page' => $this->module->l('Showing page', 'productlist'),
                'show_of' => $this->module->l('of', 'productlist'),
                'no_record' => $this->module->l('No records available', 'productlist'),
                'filter_from' => $this->module->l('filtered from', 'productlist'),
                't_record' => $this->module->l('total records', 'productlist'),
                'search_item' => $this->module->l('Search', 'productlist'),
                'p_page' => $this->module->l('Previous', 'productlist'),
                'n_page' => $this->module->l('Next', 'productlist'),
                'update_success' => $this->module->l('Updated Successfully', 'productlist'),
            );
        Media::addJsDef($jsVars);
    }

    public function changeProductStatus($idSeller)
    {
        $idProduct = Tools::getValue('id_product');
        $objMpProduct = new WkMpSellerProduct();
        $sellerProduct = WkMpSellerProduct::getSellerProductByPsIdProduct($idProduct);
        if ($sellerProduct && ($sellerProduct['id_seller'] == $idSeller)) {
            $mpIdProduct = $sellerProduct['id_mp_product'];
            Hook::exec('actionBeforeToggleMPProductStatus', array('id_mp_product' => $mpIdProduct));
            if (!count($this->errors)) {
                $objMpProduct = new WkMpSellerProduct($mpIdProduct);
                if ($objMpProduct->active) {
                    $objMpProduct->active = 0;
                    $objMpProduct->status_before_deactivate = 0;
                    $objMpProduct->save();
                    $product = new Product($idProduct);
                    $product->active = 0;
                    $product->save();

                    //Update id_image as mp_image_id when product is going to deactivate
                    WkMpProductAttributeImage::setCombinationImagesAsMp($mpIdProduct);
                } else {
                    $objMpProduct->active = 1;
                    $objMpProduct->status_before_deactivate = 1;
                    $objMpProduct->save();

                    $objMpProduct->updateSellerProductToPs($mpIdProduct, 1);
                    $objMpProductAttribute = new WkMpProductAttribute();
                    $objMpProductAttribute->updateMpProductCombinationToPs($mpIdProduct, $idProduct);

                    Hook::exec('actionToogleMPProductActive', array('id_mp_product' => $mpIdProduct, 'active' => $objMpProduct->active));
                }

                Hook::exec('actionAfterToggleMPProductStatus', array('id_product' => $idProduct, 'active' => $objMpProduct->active));
                Tools::redirect($this->context->link->getModuleLink('marketplace', 'productlist', array('status_updated' => 1)));
            }
        }
    }

    public function deleteSelectedProducts($mpIdProducts, $idSeller)
    {
        $mpDelete = true;
        $objMpProduct = new WkMpSellerProduct();
        foreach ($mpIdProducts as $idProduct) {
            $objMpProduct = new WkMpSellerProduct($idProduct);
            if ($objMpProduct->id_seller == $idSeller) {
                if (!$objMpProduct->delete()) {
                    $mpDelete = false;
                }
            }
        }

        if ($mpDelete) {
            Tools::redirect($this->context->link->getModuleLink('marketplace', 'productlist', array('deleted' => 1)));
        }
    }

    public function getProductDetails($productList)
    {
        //if multilang is OFF then current lang will be PS Default Lang
        if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
            $idLang = $this->context->language->id;
        } else {
            $idLang = Configuration::get('PS_LANG_DEFAULT');
        }

        $language = Language::getLanguage((int) $idLang);

        foreach ($productList as &$product) {
            if ($product['id_ps_product']) { // if product activated
                $product['id_product'] = $product['id_ps_product'];
                $product['id_lang'] = $idLang;
                $product['lang_iso'] = $language['iso_code'];
            }

            $coverImage = WkMpSellerProductImage::getProductCoverImage($product['id_mp_product']);
            if ($coverImage) {
                $product['cover_image'] = $coverImage;
            }

            //convert price for multiple currency
            $product['price'] = Tools::convertPrice($product['price']);

            $product['price_without_sign'] = $product['price'];
            $product['price'] = Tools::displayPrice($product['price']);
        }

        return $productList;
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'productlist'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard')
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Product List', 'productlist'),
            'url' => ''
        );
        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->addJqueryPlugin('tablednd');
        $this->addjQueryPlugin('growl', null, false);
        $this->registerStylesheet('marketplace_account', 'modules/'.$this->module->name.'/views/css/marketplace_account.css');
        $this->registerJavascript('mp-imageedit-js', 'modules/'.$this->module->name.'/views/js/imageedit.js');

        //data table file included
        $this->registerStylesheet('datatable_bootstrap', 'modules/'.$this->module->name.'/views/css/datatable_bootstrap.css');
        $this->registerJavascript('mp-jquery-dataTables', 'modules/'.$this->module->name.'/views/js/jquery.dataTables.min.js');
        $this->registerJavascript('mp-dataTables.bootstrap', 'modules/'.$this->module->name.'/views/js/dataTables.bootstrap.js');
    }
}
