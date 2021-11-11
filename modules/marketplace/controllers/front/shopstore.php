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

class MarketplaceShopStoreModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $shopLinkRewrite = Tools::getValue('mp_shop_name');
        $idCategory = Tools::getValue('id_category');

        $mpSeller = WkMpSeller::getSellerByLinkRewrite($shopLinkRewrite, $this->context->language->id);
        if ($mpSeller) {
            if ($mpSeller['active']) {
                $idSeller = $mpSeller['id_seller'];

                //Rating Information
                if ($avgRating = WkMpSellerReview::getSellerAvgRating($idSeller)) {
                    Media::addJsDef(array(
                        'avg_rating' => $avgRating,
                        'module_dir' => _MODULE_DIR_,
                    ));
                }

                /*-------------------------  Shop Collection --------------------------*/
                $orderBy = Tools::getValue('orderby');
                $orderWay = Tools::getValue('orderway');
                $p = Tools::getValue('p');
                $n = Configuration::get('PS_PRODUCTS_PER_PAGE');

                // default page number
                if (!$p) {
                    $p = 1;
                }

                // default orderby and orderway
                if (!$orderBy) {
                    $orderBy = 'id_mp_product'; //Display by default new product first
                }

                if (!$orderWay) {
                    $orderWay = 'desc'; //Display by default new product first
                }

                // for creating pagination
                $mpSellerProduct = WkMpSellerProduct::getSellerProduct(
                    $idSeller,
                    1,
                    $this->context->language->id,
                    $orderBy,
                    $orderWay
                );

                $activeProduct = array();
                if ($mpSellerProduct) {
                    if (Module::isEnabled('mpsellerpricecomparision')) {
                        foreach ($mpSellerProduct as $productDetails) {
                            $wkNormalProduct = true;
                            if (isset($productDetails['is_global']) && isset($productDetails['id_product_global'])) {
                                if (!$productDetails['is_global'] && !$productDetails['id_product_global']) {
                                    $wkNormalProduct = true;
                                } else {
                                    $wkNormalProduct = false;
                                }
                            }

                            if ($wkNormalProduct) {
                                if (($productDetails['visibility'] == 'both')
                                    || ($productDetails['visibility'] == 'catalog')
                                ) {
                                    $activeProduct[] = $productDetails;
                                }
                            } else {
                                $activeProduct[] = $productDetails;
                            }
                        }
                    } else {
                        foreach ($mpSellerProduct as $productDetails) {
                            if (($productDetails['visibility'] == 'both')
                                || ($productDetails['visibility'] == 'catalog')
                            ) {
                                $activeProduct[] = $productDetails;
                            }
                        }
                    }

                    $paginationProducts = $activeProduct;
                    if ($idCategory) {
                        foreach ($paginationProducts as $key => $product) {
                            $obj_prod = new Product($product['id_ps_product'], false, $this->context->language->id);
                            $catgs = $obj_prod->getCategories();
                            if (!in_array($idCategory, $catgs)) {
                                unset($paginationProducts[$key]);
                            }
                        }
                    }
                }

                //Get Seller total products
                if ($activeProduct) {
                    $sellerActiveProducts = count($activeProduct);
                } else {
                    $sellerActiveProducts = 0;
                }
                $this->context->smarty->assign('sellerActiveProducts', $sellerActiveProducts);

                //Display price tax Incl or excl and price hide/show according to customer group settings
                $displayPriceTaxIncl = 1;
                $showPriceByCustomerGroup = 1;
                if ($groupAccess = Group::getCurrent()) {
                    if (isset($groupAccess->price_display_method) && $groupAccess->price_display_method) {
                        $displayPriceTaxIncl = 0; //Display tax incl price
                    }
                    if (empty($groupAccess->show_prices)) {
                        $showPriceByCustomerGroup = 0; //Don't display product price
                    }
                }

                // mpShopProduct have product according filter while
                // activeProduct have all product of current seller for category list display
                if ($idCategory) {
                    $mpShopProduct = $this->getMpProductByCategory($idCategory, $activeProduct);
                } else {
                    $mpShopProduct = $activeProduct;
                }

                if ($orderBy == 'price' && $mpShopProduct) { // sort product by price
                    $mpShopProduct = $this->sortProductsByPrice($mpShopProduct, $orderWay);
                }

                if (is_array($mpShopProduct)) {
                    $productCount = count($mpShopProduct);
                } else {
                    $productCount = 0;
                }

                //For managing pagination
                $pagesNb = ceil($productCount / (int)$n);
                if ($p > $pagesNb && $productCount != 0) {
                    Tools::redirect($this->context->link->getPaginationLink(false, false, $n, false, $pagesNb, false));
                }

                $range = 2; /* how many pages around page selected */
                $start = (int)($p - $range);
                if ($start < 1) {
                    $start = 1;
                }

                $stop = (int)($p + $range);
                if ($stop > $pagesNb) {
                    $stop = (int)$pagesNb;
                }

                // get products by page
                $mpShopProduct = $this->filterProductsByPage($mpShopProduct, $p, $n);
                if ($mpShopProduct) {
                    //get category details
                    if ($activeProduct) {
                        $catg_details = $this->getMpProductCategoryCount($activeProduct);
                        if ($catg_details) {
                            $this->context->smarty->assign('catg_details', $catg_details);
                        }
                    }

                    //Product array by category or by default
                    foreach ($mpShopProduct as &$shopProduct) {
                        $product = new Product($shopProduct['id_ps_product'], true, $this->context->language->id);
                        if ($cover = Product::getCover($shopProduct['id_ps_product'])) {
                            $shopProduct['image'] = $shopProduct['id_ps_product'].'-'.$cover['id_image'];
                        } else {
                            $shopProduct['image'] = null;
                        }

                        $shopProduct['qty_available'] = StockAvailable::getQuantityAvailableByProduct($shopProduct['id_ps_product']);
                        $shopProduct['product'] = $product;
                        $shopProduct['link'] = $this->context->link->getProductLink($product);
                        $shopProduct['product_name'] = $product->name;
                        $shopProduct['lang_iso'] = $this->context->language->iso_code;
                        $shopProduct['link_rewrite'] = $product->link_rewrite;
                        $shopProduct['available_for_order'] = $product->available_for_order;
                        $shopProduct['show_price'] = $product->show_price;
                        $shopProduct['minimal_quantity'] = $product->minimal_quantity;

                        if ($displayPriceTaxIncl) {
                            $shopProduct['retail_price'] = Tools::displayPrice($product->getPriceWithoutReduct());
                            $shopProduct['price'] = Tools::displayPrice($product->getPrice(true));
                        } else {
                            $shopProduct['retail_price'] = Tools::displayPrice($product->getPriceWithoutReduct(true));
                            $shopProduct['price'] = Tools::displayPrice($product->getPrice(false));
                        }

                        //If product has combination
                        $productHasCombination = $product->hasAttributes();
                        if ($productHasCombination) {
                            $shopProduct['hasCombination'] = 1;

                            $defaultAttributeId = $product->getWsDefaultCombination();
                            if ($defaultAttributeId) {
                                $minimalQty = 1;
                                $combinationAttributeData = $product->getAttributeCombinationsById($defaultAttributeId, $this->context->language->id);
                                if ($combinationAttributeData) {
                                    foreach ($combinationAttributeData as $attributeKey => $combinationAttribute) {
                                        $shopProduct['combinationData'][$attributeKey]['id_attribute'] = $combinationAttribute['id_attribute'];
                                        $shopProduct['combinationData'][$attributeKey]['id_attribute_group'] = $combinationAttribute['id_attribute_group'];

                                        $minimalQty = $combinationAttribute['minimal_quantity'];
                                    }
                                }

                                $shopProduct['qty_available'] = StockAvailable::getQuantityAvailableByProduct($shopProduct['id_ps_product'], $defaultAttributeId);

                                if ($displayPriceTaxIncl) {
                                    $shopProduct['retail_price'] = Tools::displayPrice($product->getPriceWithoutReduct(false, $defaultAttributeId));
                                    $shopProduct['price'] = Tools::displayPrice($product->getPrice(true, $defaultAttributeId));
                                } else {
                                    $shopProduct['retail_price'] = Tools::displayPrice($product->getPriceWithoutReduct(true, $defaultAttributeId));
                                    $shopProduct['price'] = Tools::displayPrice($product->getPrice(false, $defaultAttributeId));
                                }

                                $shopProduct['minimal_quantity'] = $minimalQty;
                            }
                        }
                    }

                    $this->context->smarty->assign('mp_shop_collection', $mpShopProduct);

                    $filterURL = array();
                    // Define JS Vars
                    if ($idCategory = Tools::getValue('id_category')) {
                        $filterURL['id_category'] = $idCategory;

                        Media::addJSDef(array(
                            'requestSortProducts' => $this->context->link->getModuleLink(
                                'marketplace',
                                'shopstore',
                                array('mp_shop_name' => $shopLinkRewrite, 'id_category' => (int) $idCategory)
                            )
                        ));
                    } elseif ($page = Tools::getValue('p')) {
                        Media::addJSDef(array(
                            'requestSortProducts' => $this->context->link->getModuleLink(
                                'marketplace',
                                'shopstore',
                                array('mp_shop_name' => $shopLinkRewrite, 'p' => (int) $page)
                            )
                        ));
                    } else {
                        Media::addJSDef(array(
                            'requestSortProducts' => $this->context->link->getModuleLink(
                                'marketplace',
                                'shopstore',
                                array('mp_shop_name' => $shopLinkRewrite)
                            )
                        ));
                    }

                    if (Tools::getValue('orderby') || Tools::getValue('orderway')) {
                        $filterURL['orderby'] = Tools::getValue('orderby');
                        $filterURL['orderway'] = Tools::getValue('orderway');
                    }

                    $this->context->smarty->assign('filterURL', $filterURL);
                }
                /*-------------------------  Shop Collection END --------------------------*/

                //assign the seller details view vars
                WkMpSeller::checkSellerAccessPermission($mpSeller['seller_details_access']);

                // Set left Image column
                $this->setLeftImageBlock($mpSeller['shop_image']);

                //Check if shop banner exist
                $shopBannerPath = WkMpSeller::getShopBannerLink($mpSeller);
                if ($shopBannerPath) {
                    $this->context->smarty->assign('shop_banner_path', $shopBannerPath);
                }

                if ($mpSeller['id_country']) {
                    $mpSeller['country'] = Country::getNameById($this->context->language->id, $mpSeller['id_country']);
                }
                if ($mpSeller['id_state']) {
                    $mpSeller['state'] = State::getNameById($mpSeller['id_state']);
                }

                if (Configuration::get('WK_MP_CONTACT_SELLER_SETTINGS')) {
                    //If admin allowed only registered customers to contact with seller in configuration
                    if ($this->context->customer->id) {
                        $this->context->smarty->assign('contactSellerAllowed', 1);
                    }
                } else {
                    //Anyone can contact to seller
                    $this->context->smarty->assign('contactSellerAllowed', 1);
                }

                $this->context->smarty->assign(array(
                    'link' => $this->context->link,
                    'name_shop' => $shopLinkRewrite,
                    'seller_id' => $idSeller,
                    'id_customer' => $this->context->customer->id,
                    'customer_email' => $this->context->customer->email,
                    'mp_seller_info' => $mpSeller,
                    'showPriceByCustomerGroup' => $showPriceByCustomerGroup,
                    'timestamp' => WkMpHelper::getTimestamp(),
                    'PS_CATALOG_MODE' => Configuration::get('PS_CATALOG_MODE'),
                    'p' => $p,
                    'n' => $n,
                    'start' => $start,
                    'stop' => $stop,
                    'pagesNb' => $pagesNb,
                    'currentProductCount' => $productCount,
                    'defaultorederby' => 'id',
                    'orderby' => $orderBy,
                    'orderway' => $orderWay,
                    'nb_products' => $productCount,
                    'page_count' => (int) ceil($productCount/$n),
                    'static_token' => Tools::getToken(false),
                    'myAccount' => 'index.php?controller=authentication&back='.urlencode($this->context->link->getModuleLink('marketplace', 'shopstore', array('mp_shop_name' => $shopLinkRewrite))),
                ));
            } else {
                Tools::redirect(__PS_BASE_URI__.'pagenotfound');
            }
        } else {
            Tools::redirect(__PS_BASE_URI__.'pagenotfound');
        }

        $this->context->smarty->assign(array(
            'link' => $this->context->link,
            'timestamp' => WkMpHelper::getTimestamp(),
        ));

        $this->defineJSVars();
        $this->setTemplate('module:marketplace/views/templates/front/shop/shopstore.tpl');
    }

    public function setLeftImageBlock($mpShopImage)
    {
        if ($mpShopImage && file_exists(_PS_MODULE_DIR_.'marketplace/views/img/shop_img/'.$mpShopImage)) {
            $this->context->smarty->assign('seller_img_path', _MODULE_DIR_.'marketplace/views/img/shop_img/'.$mpShopImage);
            $this->context->smarty->assign('seller_img_exist', 1);
        } else {
            $this->context->smarty->assign('seller_img_path', _MODULE_DIR_.'marketplace/views/img/shop_img/defaultshopimage.jpg');
        }
    }

    public function defineJSVars()
    {
        $jsVars = array(
                'logged' => $this->context->customer->isLogged(),
                'moduledir' => _MODULE_DIR_,
                'mp_image_dir' => _MODULE_DIR_.'marketplace/views/img/',
                'contact_seller_ajax_link' => $this->context->link->getModuleLink('marketplace', 'contactsellerprocess'),
                'rate_req' => $this->module->l('Rating is required.', 'shopstore'),
                'not_logged_msg' => $this->module->l('Please login to write a review.', 'shopstore'),
                'review_yourself_msg' => $this->module->l('You can not write review to yourself.', 'shopstore'),
                'review_already_msg' => $this->module->l('You have already written a review for this seller.', 'shopstore'),
                'confirm_msg' => $this->module->l('Are you sure?', 'shopstore'),
                'email_req' => $this->module->l('Email is required field.', 'shopstore'),
                'invalid_email' => $this->module->l('Email is not valid.', 'shopstore'),
                'subject_req' => $this->module->l('Subject is required field.', 'shopstore'),
                'description_req' => $this->module->l('Description is required field.', 'shopstore'),
            );

        Media::addJsDef($jsVars);
    }

    public function filterProductsByPage($mpShopProduct, $p, $n)
    {
        $result = array();
        if ($mpShopProduct) {
            $start = ($p - 1) * $n;
            $end = $start + $n;
            for ($i = $start; $i < $end; $i++) {
                if (array_key_exists($i, $mpShopProduct)) {
                    $result[] = $mpShopProduct[$i];
                }
            }
        }

        return $result;
    }

    public function sortProductsByPrice($mpShopProduct, $orderWay)
    {
        // get all product price
        foreach ($mpShopProduct as &$product) {
            $product['price'] = Product::getPriceStatic($product['id_ps_product']);
        }

        if (is_array($mpShopProduct)) {
            $productCount = count($mpShopProduct);
        } else {
            $productCount = 0;
        }

        // use bubble sort to sort product by price
        $noOfProducts = ($productCount-1);
        for ($i = 0; $i < $noOfProducts; $i++) {
            for ($j = 0; $j < ($noOfProducts-$i); $j++) {
                if ($orderWay == 'desc') {
                    if ($mpShopProduct[$j]['price'] < $mpShopProduct[$j+1]['price']) {
                        $tempProduct = $mpShopProduct[$j];
                        $mpShopProduct[$j] = $mpShopProduct[$j+1];
                        $mpShopProduct[$j+1] = $tempProduct;
                    }
                } else {
                    if ($mpShopProduct[$j]['price'] > $mpShopProduct[$j+1]['price']) {
                        $tempProduct = $mpShopProduct[$j+1];
                        $mpShopProduct[$j+1] = $mpShopProduct[$j];
                        $mpShopProduct[$j] = $tempProduct;
                    }
                }
            }
        }

        return $mpShopProduct;
    }

    public function getMpProductCategoryCount($mpProduct)
    {
        $mpCategory = array();
        if ($mpProduct) {
            foreach ($mpProduct as $p) {
                if ($p['active']) {
                    $product = new Product($p['id_ps_product'], false, $this->context->language->id);
                    $categories = $product->getCategories();
                    foreach ($categories as $catg) {
                        $category = new Category($catg, $this->context->language->id);
                        if (isset($category->id) && $category->active) {
                            if (!array_key_exists($catg, $mpCategory)) {
                                if ($catg != Category::getRootCategory()->id) {
                                    $mpCategory[$catg] = array(
                                        'id_category' => $catg,
                                        'Name' => $category->name,
                                        'NoOfProduct' => 1,
                                    );
                                }
                            } else {
                                $mpCategory[$catg]['NoOfProduct'] += 1;
                            }
                        }
                    }
                }
            }
        }

        if ($mpCategory) {
            return $mpCategory;
        }

        return false;
    }

    public function getMpProductByCategory($idCategory, $activeProduct)
    {
        if ($activeProduct) {
            foreach ($activeProduct as $key => $mpProduct) {
                $product = new Product($mpProduct['id_ps_product'], false, $this->context->language->id);
                $catgs = $product->getCategories();
                if (!in_array($idCategory, $catgs)) {
                    unset($activeProduct[$key]);
                }
            }
        }

        return array_values($activeProduct);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Marketplace', 'shopstore'),
            'url' => $this->context->link->getModuleLink('marketplace', 'dashboard')
        );

        $breadcrumb['links'][] = array(
            'title' => $this->module->l('Shop', 'shopstore'),
            'url' => ''
        );

        return $breadcrumb;
    }

    public function setMedia()
    {
        parent::setMedia();

        $this->registerStylesheet(
            'mp_store_profile-css',
            'modules/'.$this->module->name.'/views/css/mp_store_profile.css'
        );
        $this->registerStylesheet(
            'mp_shop_store-css',
            'modules/'.$this->module->name.'/views/css/mp_shop_store.css'
        );

        $this->registerJavascript(
            'shopstore-js',
            'modules/'.$this->module->name.'/views/js/shopstore.js'
        );
        $this->registerJavascript(
            'imageedit-js',
            'modules/'.$this->module->name.'/views/js/imageedit.js'
        );
        $this->registerJavascript(
            'contactseller-js',
            'modules/'.$this->module->name.'/views/js/contactseller.js'
        );

        // bxslider removed in PS V1.7
        $this->registerJavascript(
            'bxslider',
            'modules/'.$this->module->name.'/views/js/jquery.bxslider.js'
        );

        $this->registerJavascript(
            'mp-jquery-raty-min',
            'modules/'.$this->module->name.'/views/js/libs/jquery.raty.min.js'
        );

        // mp product slider
        $this->registerStylesheet(
            'ps_gray',
            'modules/'.$this->module->name.'/views/css/product_slider_pager/ps_gray.css'
        );
        $this->registerJavascript(
            'mp_product_slider-js',
            'modules/'.$this->module->name.'/views/js/mp_product_slider.js'
        );
    }
}
