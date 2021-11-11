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

class AdminSellerProductDetailController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->table = 'wk_mp_seller_product';
        $this->className = 'WkMpSellerProduct';
        $this->bootstrap = true;

        $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller_product_lang` mspl ON (mspl.`id_mp_product` = a.`id_mp_product`)';
        $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'wk_mp_seller` mpsi ON (mpsi.`id_seller` = a.`id_seller`)';
        $this->_select = '
            CONCAT(mpsi.`seller_firstname`, " ", mpsi.`seller_lastname`) as seller_name,
            a.`id_mp_product` as `seller_product_id`,
            mpsi.`shop_name_unique`,
            mspl.`id_lang`,
            mspl.`product_name`,
            a.`id_ps_product` as temp_ps_id';
        $this->_where = 'AND mspl.`id_lang` = '.(int) $this->context->language->id;

        //if filter only seller products by seller view page
        if ($idSeller = Tools::getValue('id_seller')) {
            $this->_where = 'AND a.`id_seller` = '.(int) $idSeller;
        }

        $this->_group = 'GROUP BY mspl.`id_mp_product`';
        $this->identifier = 'id_mp_product';

        parent::__construct();
        $this->toolbar_title = $this->l('Manage Seller Product');

        $this->fields_list = array();
        $this->fields_list['id_mp_product'] = array(
            'title' => $this->l('ID'),
            'align' => 'center',
            'class' => 'fixed-width-xs',
        );

        $this->fields_list['id_ps_product'] = array(
            'title' => $this->l('Prestashop Product ID'),
            'align' => 'center',
            'class' => 'fixed-width-xs',
            'hint' => $this->l('Generated Prestashop ID in Catalog'),
            'callback' => 'prestashopDisplayId',
        );

        $this->fields_list['seller_product_id'] = array(
            'title' => $this->l('Image'),
            'callback' => 'displayProductImage',
            'search' => false,
            'havingFilter' => true,
        );

        $this->fields_list['product_name'] = array(
            'title' => $this->l('Product Name'),
        );

        $this->fields_list['seller_name'] = array(
            'title' => $this->l('Seller Name'),
            'havingFilter' => true,
        );

        $this->fields_list['shop_name_unique'] = array(
            'title' => $this->l('Unique Shop Name'),
            'havingFilter' => true,
        );

        $this->fields_list['active'] = array(
            'title' => $this->l('Status'),
            'active' => 'status',
            'type' => 'bool',
            'orderby' => false,
        );

        $this->fields_list['temp_ps_id'] = array(
            'title' => $this->l('Preview'),
            'align' => 'center',
            'search' => false,
            'remove_onclick' => true,
            'hint' => $this->l('Preview Active Products Only'),
            'callback' => 'previewProduct',
            'orderby' => false,
        );

        $this->fields_list['date_add'] = array(
            'title' => $this->l('Add Date'),
            'type' => 'date',
            'havingFilter' => true,
        );

        $hookColumn = Hook::exec('addColumnSellerProductList');

        $i = 0;
        if ($hookColumn) {
            $column = explode('-', $hookColumn);
            $numColums = count($column);
            for ($i = 0; $i < $numColums; $i = $i + 2) {
                $this->fields_list[$column[$i]] = array(
                    'title' => $this->l($column[$i + 1]),
                    'align' => 'center',
                );
            }
        }

        $this->bulk_actions = array(
            'duplicate' => array(
                'text' => $this->l('Duplicate selected'),
                'icon' => 'icon-copy',
                'confirm' => $this->l('Duplicate selected items?'),
            ),
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?'),
            )
        );

        if ($wkErrorCode = Tools::getValue('wk_error_code')) {
            if ($wkErrorCode == 1) {
                $this->errors[] = $this->l('There is some error to map marketplace product.');
            } elseif ($wkErrorCode == 2) {
                $this->errors[] = $this->l('Can not able to create product in prestashop catalog.');
            }
        }
    }

    public function prestashopDisplayId($idPsProduct)
    {
        if ($idPsProduct) {
            return $idPsProduct;
        } else {
            return '-';
        }
    }

    public function displayProductImage($idMpProduct)
    {
        $coverImage = WkMpSellerProduct::getCover($idMpProduct);
        if ($coverImage) {
            return '<img class="img-thumbnail" width="45" height="45" src="'._MODULE_DIR_.'marketplace/views/img/product_img/'.$coverImage['seller_product_image_name'].'">';
        } else {
            return '<img class="img-thumbnail" width="45" height="45" src="'._MODULE_DIR_.'/marketplace/views/img/home-default.jpg">';
        }
    }

    public function displayDuplicateLink($token, $id, $name = null)
    {
        $adminSellerProductLink = $this->context->link->getAdminlink('AdminSellerProductDetail')
        .'&id_mp_product='.(int) $id.'&wkduplicate'.$this->table;
        return '<li><a href="'.$adminSellerProductLink.'"><i class="icon-copy"></i> '.$this->l('Duplicate').'</a></li>';
    }

    public function previewProduct($id, $rowData)
    {
        if ($id && $rowData['active']) {
            $productLink = $this->context->link->getProductLink((int) $id, null, null, null, (int) $this->context->language->id);

            return '<span class="btn-group-action"><span class="btn-group">
                        <a target="_blank" class="btn btn-default" href="'.$productLink.'">
                        <i class="icon-eye"></i>&nbsp;'.$this->l('Preview').'</a>
                    </span>
                </span>';
        }
    }

    public function initToolbar()
    {
        if (WkMpSeller::getAllSeller()) {
            parent::initToolbar();
            $this->page_header_toolbar_btn['new'] = array(
                'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token,
                'desc' => $this->l('Add new product'),
            );
            $this->page_header_toolbar_btn['assignproducts'] = array(
                'href' => self::$currentIndex.'&add'.$this->table.'&token='.$this->token.'&assignmpproduct=1',
                'desc' => $this->l('Assign product to seller'),
                'imgclass' => 'new',
            );
        }
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('duplicate');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function postProcess()
    {
        if (!$this->loadObject(true)) {
            return;
        }

        Media::addJsDef(array(
            'back_end' => 1,
            'image_drag_drop' => 1,
            'seller_product_page' => 1,
            'is_need_reason' => Configuration::get('WK_MP_SELLER_PRODUCTS_DEACTIVATE_REASON'),
            'path_addfeature' => $this->context->link->getAdminlink('AdminSellerProductDetail'),
            'generate_combination_confirm_msg' => $this->l('You will lose all unsaved modifications. Are you sure that you want to proceed?', 'updateproduct'),
        ));

        $this->addjQueryPlugin('growl', null, false);
        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/sellerprofile.js');
        if (isset($this->display)) {
            $this->addJqueryPlugin(array('fancybox', 'tablednd'));

            $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/mp_global_style.css');
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/mp_form_validation.js');
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/change_multilang.js');

            //tinymce
            $this->addJS(_PS_JS_DIR_.'tiny_mce/tiny_mce.js');
            if (version_compare(_PS_VERSION_, '1.6.0.11', '>')) {
                $this->addJS(_PS_JS_DIR_.'admin/tinymce.inc.js');
            } else {
                $this->addJS(_PS_JS_DIR_.'tinymce.inc.js');
            }

            //Category tree
            $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/js/categorytree/themes/default/style.min.css');
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/categorytree/jstree.min.js');
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/categorytree/wk_jstree.js');
        }

        // send reason for deactivating product
        if ($idProductForReason = Tools::getValue('actionId_for_reason')) {
            $msg = trim(Tools::getValue('reason_text'));
            if (!$msg) {
                $msg = $this->l('Admin has deactivated your product.');
            }
            $this->activeSellerProduct($idProductForReason, $msg);
            Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token.'&conf=5');
        }

        if (Tools::isSubmit('status'.$this->table)) {
            $this->activeSellerProduct();
        }

        //Duplicate seller product
        if (Tools::getIsset('wkduplicate'.$this->table)) {
            if ($duplicateMpProductId = $this->duplicateMpProduct()) {
                Tools::redirectAdmin(
                    self::$currentIndex.'&id_mp_product='.(int) $duplicateMpProductId.'&update'.$this->table
                    .'&conf=19&token='.$this->token
                );
            }
        }

        parent::postProcess();
    }

    public function renderForm()
    {
        $permissionData = WkMpHelper::productTabPermission();
        //tinymce setup
        $this->context->smarty->assign(array(
                'path_css' => _THEME_CSS_DIR_,
                'ad' => __PS_BASE_URI__.basename(_PS_ADMIN_DIR_),
                'autoload_rte' => true,
                'lang' => true,
                'iso' => $this->context->language->iso_code,
                'permissionData' => $permissionData,
            ));

        $objMpProduct = new WkMpSellerProduct();

        if (Tools::getValue('assignmpproduct')) {
            $mpSellers = WkMpSeller::getAllSeller();
            if ($mpSellers) {
                $psProducts = WkMpSellerProduct::getPsProductsForAssigned($this->context->language->id);
                if ($psProducts) {
                    $this->context->smarty->assign('ps_products', $psProducts);
                }
                $this->context->smarty->assign('mp_sellers', $mpSellers);
            }
            $this->context->smarty->assign('assignmpproduct', 1);
        }

        // Admin Shipping
        $carriers = Carrier::getCarriers($this->context->language->id, true, false, false, null, ALL_CARRIERS);
        $carriersChoices = array();
        if ($carriers) {
            foreach ($carriers as $carrier) {
                $carriersChoices[$carrier['id_reference'].' - '.$carrier['name'].' ('.$carrier['delay'].')']
                = $carrier['id_reference'];
            }
        }

        if ($this->display == 'add') {
            $customerInfo = WkMpSeller::getAllSeller();
            if ($customerInfo) {
                $this->context->smarty->assign('customer_info', $customerInfo);

                //get first seller from the list
                $firstSellerDetails = $customerInfo[0];
                $mpIdSeller = $firstSellerDetails['id_seller'];
            } else {
                $mpIdSeller = 0;
            }
        } elseif ($this->display == 'edit') {
            $id = Tools::getValue('id_mp_product');

            $mpSellerProduct = new WkMpSellerProduct($id);
            $product = (array) $mpSellerProduct;

            if ($product) {
                $mpIdSeller = $product['id_seller'];

                // Category tree
                $objMpProductCategory = new WkMpSellerProductCategory();
                $defaultIdCategory = $objMpProductCategory->getSellerProductDefaultCategory($id);

                $idCategory = array();
                $checkedProductCategory = $objMpProduct->getSellerProductCategories($id);
                if ($checkedProductCategory) {
                    // Default category
                    foreach ($checkedProductCategory as $checkIdCategory) {
                        $idCategory[] = $checkIdCategory['id_category'];
                    }

                    $catIdsJoin = implode(',', $idCategory);
                    $this->context->smarty->assign('catIdsJoin', $catIdsJoin);
                }

                $defaultCategory = Category::getCategoryInformations($idCategory, $this->context->language->id);

                //Assign and display product active/inactive images
                WkMpSellerProductImage::getProductImageDetails($id);

                //Assign product shipping
                $selectedCarriers = $product['ps_id_carrier_reference'];
                if ($selectedCarriers) {
                    $selectedCarriers = unserialize($selectedCarriers);
                }

                // Get Seller Product Features and Assign on Smarty
                WkMpProductFeature::assignProductFeature($id);

                //Display Product Combination list
                WkMpProductAttribute::displayProductCombinationList($id);

                // checking current product has attribute or not
                $objMpAttribute = new WkMpProductAttribute();
                $hasAttribute = $objMpAttribute->getProductAttributes($id);
                if ($hasAttribute) {
                    $this->context->smarty->assign('hasAttribute', 1);
                }
                // End of -- hasAttribute code ---

                $this->context->smarty->assign(array(
                    'selectedCarriers' => $selectedCarriers,
                    'product_info' => $product,
                    'id_tax_rules_group' => $product['id_tax_rules_group'],
                    'defaultCategory' => $defaultCategory,
                    'defaultIdCategory' => $defaultIdCategory,
                    'edit' => 1,
                    'id' => $id,
                ));
            }
        }

        // Set default lang at every form according to configuration multi-language
        WkMpHelper::assignDefaultLang($mpIdSeller);

        //show tax rule group on add product page
        $taxRuleGroups = TaxRulesGroup::getTaxRulesGroups(true);
        if ($taxRuleGroups) {
            $this->context->smarty->assign('tax_rules_groups', $taxRuleGroups);
        }

        $this->context->smarty->assign('mp_seller_applied_tax_rule', 1);

        WkMpHelper::defineGlobalJSVariables(); // Define global js variable on js file

        $objProduct = new Product();
        $objDefaultCurrency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $this->context->smarty->assign(array(
                'modules_dir' => _MODULE_DIR_,
                'mp_image_dir' => _MODULE_DIR_.'marketplace/views/img/',
                'img_ps_dir' => _MODULE_DIR_.$this->module->name.'/views/img/',
                'wkself' => dirname(__FILE__),
                'active_tab' => Tools::getValue('tab'),
                'defaultCurrencySign' => $objDefaultCurrency->sign,
                'img_module_dir' => _MODULE_DIR_.$this->module->name.'/views/img/',
                'carriersChoices' => $carriersChoices,
                'backendController' => 1,
                'available_features' => Feature::getFeatures($this->context->language->id, (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP)),
                'controller' => 'admin',
                'ps_img_dir' => _PS_IMG_.'l/',
                'defaultTaxRuleGroup' => $objProduct->getIdTaxRulesGroup(),
            ));

        $this->fields_form = array(
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        return parent::renderForm();
    }

    public function processSave()
    {
        if (Tools::getValue('assignmpproduct')) { //Process of assigning products
            $idCustomer = Tools::getValue('id_customer');
            if ($idCustomer) {
                $assignedProducts = Tools::getValue('id_product');
                if (!$assignedProducts) {
                    $this->errors[] = $this->l('Choose atleast one product.');
                }

                if (empty($this->errors)) {
                    $objSellerProduct = new WkMpSellerProduct();
                    if ($assignedProducts) {
                        foreach ($assignedProducts as $idProduct) {
                            Hook::exec('actionBeforeAssignMpProduct', array('id_product' => $idProduct, 'id_customer' => $idCustomer));
                            if (empty($this->errors)) {
                                $idMpProduct = $objSellerProduct->assignProductToSeller($idProduct, $idCustomer);
                                if ($idMpProduct) {
                                    Hook::exec('actionAfterAssignMpProduct', array('id_mp_product' => $idMpProduct));
                                    WkMpSellerProduct::sendMail($idMpProduct, 3, 'assignment', 'assignment');
                                }
                            }
                        }
                    }

                    if (empty($this->errors)) {
                        if (Tools::isSubmit('submitAddwk_mp_seller_productAndAssignStay')) {
                            $redirect = self::$currentIndex.'&add'.$this->table.'&conf=3&token='.$this->token.'&assignmpproduct=1';
                            $this->redirect_after = $redirect;
                        } elseif (Tools::isSubmit('submitAddwk_mp_seller_product')) {
                            Tools::redirectAdmin(self::$currentIndex.'&conf=3&token='.$this->token);
                        }
                    }
                } else {
                    if (Tools::isSubmit('submitAdd'.$this->table.'AndAssignStay')) {
                        $this->display = 'edit';
                    }
                }
            } else {
                Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
            }
        } else {
            $productQuantity = Tools::getValue('quantity');
            $minimalQuantity = Tools::getValue('minimal_quantity');
            $productShowCondition = Tools::getValue('show_condition');
            if (!$productShowCondition) {
                $productShowCondition = 0;
            }
            $productCondition = Tools::getValue('condition');

            $productPrice = Tools::getValue('price');
            $wholesalePrice = Tools::getValue('wholesale_price');
            $unitPrice = Tools::getValue('unit_price');
            $unity = Tools::getValue('unity');
            $idTaxRulesGroup = Tools::getValue('id_tax_rules_group');

            // height, width, depth and weight
            $width = Tools::getValue('width');
            $width = empty($width) ? '0' : str_replace(',', '.', $width);

            $height = Tools::getValue('height');
            $height = empty($height) ? '0' : str_replace(',', '.', $height);

            $depth = Tools::getValue('depth');
            $depth = empty($depth) ? '0' : str_replace(',', '.', $depth);

            $weight = Tools::getValue('weight');
            $weight = empty($weight) ? '0' : str_replace(',', '.', $weight);

            $reference = Tools::getValue('reference');
            $ean13JanBarcode = Tools::getValue('ean13');
            $upcBarcode = Tools::getValue('upc');
            $isbn = Tools::getValue('isbn');

            // Admin Shipping
            $psIDCarrierReference = Tools::getValue('ps_id_carrier_reference');
            if ($psIDCarrierReference) {
                $psIDCarrierReference = serialize($psIDCarrierReference);
            } else {
                $psIDCarrierReference = 0;  // No Shipping Selected By Admin
            }

            $defaultCategory = Tools::getValue('default_category');
            $productCategory = Tools::getValue('product_category');

            $idMpProduct = Tools::getValue('id'); //if edit

            $sellerDefaultLanguage = Tools::getValue('seller_default_lang');
            $defaultLang = WkMpHelper::getDefaultLanguageBeforeFormSave($sellerDefaultLanguage);

            //Product Visibility
            $availableForOrder = trim(Tools::getValue('available_for_order'));
            $showPrice = $availableForOrder ? 1 : trim(Tools::getValue('show_price'));
            $onlineOnly = trim(Tools::getValue('online_only'));
            $visibility = trim(Tools::getValue('visibility'));

            //Product Name Validate
            if (!Tools::getValue('product_name_'.$defaultLang)) {
                if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                    $sellerLang = Language::getLanguage((int) $defaultLang);
                    $this->errors[] = sprintf($this->l('Product name is required in %s'), $sellerLang['name']);
                } else {
                    $this->errors[] = $this->l('Product name is required');
                }
            } else {
                // Validate form
                $this->errors = WkMpSellerProduct::validateMpProductForm();
            }

            if ($idMpProduct) {
                Hook::exec('actionBeforeUpdateMPProduct', array('id_mp_product' => $idMpProduct));
            } else {
                $idCustomer = Tools::getValue('shop_customer');
                $mpShopInfo = WkMpSeller::getSellerDetailByCustomerId($idCustomer);
                $idSeller = $mpShopInfo['id_seller'];
                Hook::exec('actionBeforeAddMPProduct', array('id_seller' => $idSeller));
            }

            if (empty($this->errors)) {
                //$psDefaultLangProName = '';
                $productCategory = explode(',', $productCategory);

                if ($idMpProduct) { //if update product
                    $objSellerProduct = new WkMpSellerProduct($idMpProduct);
                } else { //if add new product
                    $objSellerProduct = new WkMpSellerProduct();
                }

                // If current product has no combination then product qty will update
                $objMpAttribute = new WkMpProductAttribute();
                $hasAttribute = $objMpAttribute->getProductAttributes($idMpProduct);
                if (!$hasAttribute) {
                    $objSellerProduct->quantity = $productQuantity;
                    $objSellerProduct->minimal_quantity = $minimalQuantity;

                    //Low stock alert
                    $objSellerProduct->low_stock_threshold = Tools::getValue('low_stock_threshold');
                    if (Tools::getValue('low_stock_alert')) {
                        $objSellerProduct->low_stock_alert = 1;
                    } else {
                        $objSellerProduct->low_stock_alert = 0;
                    }
                }

                $objSellerProduct->id_category = $defaultCategory;
                $objSellerProduct->show_condition = $productShowCondition;
                $objSellerProduct->condition = $productCondition;

                //Pricing
                $objSellerProduct->price = $productPrice;
                $objSellerProduct->wholesale_price = $wholesalePrice;
                $objSellerProduct->unit_price = $unitPrice;
                $objSellerProduct->unity = $unity;
                $objSellerProduct->id_tax_rules_group = $idTaxRulesGroup;

                if (Tools::getValue('on_sale')) {
                    $objSellerProduct->on_sale = 1;
                } else {
                    $objSellerProduct->on_sale = 0;
                }

                $objSellerProduct->width = $width;
                $objSellerProduct->height = $height;
                $objSellerProduct->depth = $depth;
                $objSellerProduct->weight = $weight;

                $objSellerProduct->additional_delivery_times = Tools::getValue('additional_delivery_times');
                $objSellerProduct->additional_shipping_cost = Tools::getValue('additional_shipping_cost');

                $objSellerProduct->out_of_stock = Tools::getValue('out_of_stock');
                $objSellerProduct->available_date = Tools::getValue('available_date');

                $objSellerProduct->reference = $reference ? $reference : '';
                $objSellerProduct->ean13 = $ean13JanBarcode ? $ean13JanBarcode : '';
                $objSellerProduct->upc = $upcBarcode ? $upcBarcode : '';
                $objSellerProduct->isbn = $isbn ? $isbn : '';

                $objSellerProduct->ps_id_carrier_reference = $psIDCarrierReference;

                if (!$idMpProduct) { //add product
                    $objSellerProduct->id_seller = $idSeller;
                    $objSellerProduct->id_ps_shop = $this->context->shop->id;
                    $objSellerProduct->id_ps_product = 0;
                    $objSellerProduct->active = Tools::getValue('product_active');
                    $objSellerProduct->status_before_deactivate = Tools::getValue('product_active');
                    $objSellerProduct->admin_approved = Tools::getValue('product_active');
                }

                foreach (Language::getLanguages(false) as $language) {
                    $productIdLang = $language['id_lang'];
                    $shortDescIdLang = $language['id_lang'];
                    $descIdLang = $language['id_lang'];
                    $availableNowIdLang = $language['id_lang'];
                    $availableLaterIdLang = $language['id_lang'];
                    $metaTitleIdLang = $language['id_lang'];
                    $metaDescriptionIdLang = $language['id_lang'];
                    $deliveryInStockIdLang = $language['id_lang'];
                    $deliveryOutStockIdLang = $language['id_lang'];

                    if (Configuration::get('WK_MP_MULTILANG_ADMIN_APPROVE')) {
                        //if product name in other language is not available then fill with seller language same for others
                        if (!Tools::getValue('product_name_'.$language['id_lang'])) {
                            $productIdLang = $defaultLang;
                        }
                        if (!Tools::getValue('short_description_'.$language['id_lang'])) {
                            $shortDescIdLang = $defaultLang;
                        }
                        if (!Tools::getValue('description_'.$language['id_lang'])) {
                            $descIdLang = $defaultLang;
                        }
                        if (!Tools::getValue('meta_title_'.$language['id_lang'])) {
                            $metaTitleIdLang = $defaultLang;
                        }
                        if (!Tools::getValue('meta_description_'.$language['id_lang'])) {
                            $metaDescriptionIdLang = $defaultLang;
                        }
                        if (!Tools::getValue('available_now_'.$language['id_lang'])) {
                            $availableNowIdLang = $defaultLang;
                        }
                        if (!Tools::getValue('available_later_'.$language['id_lang'])) {
                            $availableLaterIdLang = $defaultLang;
                        }
                        if (!Tools::getValue('delivery_in_stock_'.$language['id_lang'])) {
                            $deliveryInStockIdLang = $defaultLang;
                        }
                        if (!Tools::getValue('delivery_out_stock_'.$language['id_lang'])) {
                            $deliveryOutStockIdLang = $defaultLang;
                        }
                    } else {
                        //if multilang is OFF then all fields will be filled as default lang content
                        $productIdLang = $defaultLang;
                        $shortDescIdLang = $defaultLang;
                        $descIdLang = $defaultLang;
                        $availableNowIdLang = $defaultLang;
                        $availableLaterIdLang = $defaultLang;
                        $metaTitleIdLang = $defaultLang;
                        $metaDescriptionIdLang = $defaultLang;
                        $deliveryInStockIdLang = $defaultLang;
                        $deliveryOutStockIdLang = $defaultLang;
                    }

                    // if (!$idMpProduct && Configuration::get('PS_LANG_DEFAULT') == $language['id_lang']) { //add product
                    //     $psDefaultLangProName = Tools::getValue('product_name_'.$productIdLang);
                    // }

                    $objSellerProduct->product_name[$language['id_lang']] = Tools::getValue('product_name_'.$productIdLang);

                    $objSellerProduct->short_description[$language['id_lang']] = Tools::getValue('short_description_'.$shortDescIdLang);

                    $objSellerProduct->description[$language['id_lang']] = Tools::getValue('description_'.$descIdLang);

                    //Product SEO
                    $objSellerProduct->meta_title[$language['id_lang']] = Tools::getValue('meta_title_'.$metaTitleIdLang);

                    $objSellerProduct->meta_description[$language['id_lang']] = Tools::getValue('meta_description_'.$metaDescriptionIdLang);

                    //Friendly URL
                    if (Tools::getValue('link_rewrite_'.$language['id_lang'])) {
                        $objSellerProduct->link_rewrite[$language['id_lang']] = Tools::link_rewrite(Tools::getValue('link_rewrite_'.$language['id_lang']));
                    } else {
                        $objSellerProduct->link_rewrite[$language['id_lang']] = Tools::link_rewrite(Tools::getValue('product_name_'.$productIdLang));
                    }

                    $objSellerProduct->available_now[$language['id_lang']] = Tools::getValue('available_now_'.$availableNowIdLang);

                    $objSellerProduct->available_later[$language['id_lang']] = Tools::getValue('available_later_'.$availableLaterIdLang);

                    $objSellerProduct->delivery_in_stock[$language['id_lang']] = Tools::getValue('delivery_in_stock_'.$deliveryInStockIdLang);

                    $objSellerProduct->delivery_out_stock[$language['id_lang']] = Tools::getValue('delivery_out_stock_'.$deliveryOutStockIdLang);
                }

                $objSellerProduct->available_for_order = $availableForOrder;
                $objSellerProduct->show_price = $showPrice;
                $objSellerProduct->online_only = $onlineOnly;
                $objSellerProduct->visibility = $visibility;

                $objSellerProduct->save();

                if ($idMpProduct) { //update product
                    $objMpCategory = new WkMpSellerProductCategory();
                    // for Updating new categories first delete previous category
                    $objMpCategory->deleteProductCategory($idMpProduct);

                    //Add new category into table
                    $this->assignMpProductCategory($productCategory, $idMpProduct, $defaultCategory);

                    // while updating the features delete the product features first
                    WkMpProductFeature::deleteProductFeature($idMpProduct);

                    // Update product features
                    WkMpProductFeature::processProductFeature($idMpProduct, $defaultLang, 'admin');

                    if ($objSellerProduct->active && $objSellerProduct->id_ps_product) {
                        $objSellerProduct->updateSellerProductToPs($idMpProduct, 1);
                    }
                    Hook::exec('actionAfterUpdateMPProduct', array('id_mp_product' => $idMpProduct, 'id_mp_product_attribute' => 0));
                } else { //add product
                    $sellerProductId = $objSellerProduct->id;

                    //Add into category table
                    $this->assignMpProductCategory($productCategory, $sellerProductId, $defaultCategory);

                    //if default approval on, then entry of a product in ps_product table
                    if (Tools::getValue('product_active')) {
                        // creating ps_product when admin setting is default
                        $psProductId = $objSellerProduct->addSellerProductToPs($sellerProductId, 1);
                        if ($psProductId) {
                            $objSellerProduct->id_ps_product = $psProductId;
                            $objSellerProduct->save();

                            //Hook added on first time seller product added as PS product
                            Hook::exec('actionToogleMPProductCreateStatus', array(
                                'id_product' => $psProductId, 'id_mp_product' => $sellerProductId, 'active' => 1));
                        }
                        WkMpSellerProduct::sendMail($sellerProductId, 1, 1);
                    }

                    // adding product feature into marketplace table
                    WkMpProductFeature::processProductFeature($sellerProductId, $defaultLang, 'admin');

                    Hook::exec('actionAfterAddMPProduct', array('id_mp_product' => $sellerProductId));
                }

                if (Tools::isSubmit('submitAdd'.$this->table.'AndStay')) {
                    if ($idMpProduct) {
                        Tools::redirectAdmin(self::$currentIndex.'&id_mp_product='.(int) $idMpProduct.'&update'.$this->table.'&conf=4&tab='.Tools::getValue('active_tab').'&token='.$this->token);
                    } else {
                        Tools::redirectAdmin(self::$currentIndex.'&id_mp_product='.(int) $sellerProductId.'&update'.$this->table.'&conf=3&tab='.Tools::getValue('active_tab').'&token='.$this->token);
                    }
                } else {
                    if ($idMpProduct) {
                        Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
                    } else {
                        Tools::redirectAdmin(self::$currentIndex.'&conf=3&token='.$this->token);
                    }
                }
            } else {
                if ($idMpProduct) {
                    $this->display = 'edit';
                } else {
                    $this->display = 'add';
                }
            }
        }
    }

    public function assignMpProductCategory($productCategory, $mpIdProduct, $defaultCategory)
    {
        if (!is_array($productCategory)) {
            return false;
        }

        $objSellerProductCategory = new WkMpSellerProductCategory();
        $objSellerProductCategory->id_seller_product = $mpIdProduct;

        foreach ($productCategory as $categoryVal) {
            $objSellerProductCategory->id_category = $categoryVal;

            if ($categoryVal == $defaultCategory) {
                $objSellerProductCategory->is_default = 1;
            } else {
                $objSellerProductCategory->is_default = 0;
            }
            $objSellerProductCategory->add();
        }
    }

    public function processStatus()
    {
        if (empty($this->errors)) {
            parent::processStatus();
        }
    }

    public function activeSellerProduct($mpProductId = false, $reasonText = false)
    {
        $psProductId = 0;
        if (!$mpProductId) {
            $mpProductId = Tools::getValue('id_mp_product');
        }

        Hook::exec('actionBeforeToggleMPProductStatus', array('id_mp_product' => $mpProductId));
        if (!count($this->errors)) {
            $objMpProduct = new WkMpSellerProduct($mpProductId);

            if ($objMpProduct->active) { // going to be deactive
                //product created but deactive now
                $objMpProduct->active = 0;
                $objMpProduct->status_before_deactivate = 0;
                $objMpProduct->save();

                //Update id_image as mp_image_id when product is going to deactivate
                WkMpProductAttributeImage::setCombinationImagesAsMp($mpProductId);

                if ($objMpProduct->id_ps_product) {
                    $psProductId = $objMpProduct->id_ps_product;
                    $product = new Product($psProductId);
                    $product->active = 0;
                    $product->save();
                }
                WkMpSellerProduct::sendMail($mpProductId, 2, 2, $reasonText);
            } else {
                $objMpSeller = new WkMpSeller($objMpProduct->id_seller);
                if ($objMpSeller->active) { //if seller is active
                    // going to be active
                    if ($objMpProduct->id_ps_product) {
                        //product created but dactivated right now, need to active
                        $objMpProduct->active = 1;
                        $objMpProduct->status_before_deactivate = 1;
                        $objMpProduct->admin_approved = 1;
                        $objMpProduct->save();
                        $objMpProduct->updateSellerProductToPs($mpProductId, 1);
                        $psProductId = $objMpProduct->id_ps_product;

                        $objMpProductAttribute = new WkMpProductAttribute();
                        $objMpProductAttribute->updateMpProductCombinationToPs($mpProductId, $psProductId);
                    } else {
                        //not yet product created, first time activated
                        if (!$objMpProduct->id_ps_product) {
                            $idProduct = $objMpProduct->addSellerProductToPs($mpProductId, 1);
                            if ($idProduct) {
                                $psProductId = $idProduct;
                                $objMpProduct->active = 1;
                                $objMpProduct->status_before_deactivate = 1;
                                $objMpProduct->admin_approved = 1;
                                $objMpProduct->id_ps_product = $psProductId;
                                $objMpProduct->save();

                                Hook::exec('actionToogleMPProductCreateStatus', array('id_product' => $idProduct, 'id_mp_product' => $mpProductId, 'active' => 1));
                            } else {
                                Tools::redirectAdmin(self::$currentIndex.'&wk_error_code=2&token='.$this->token);
                            }
                        }
                    }
                    Hook::exec('actionToogleMPProductActive', array('id_mp_product' => $mpProductId, 'active' => $objMpProduct->active));
                    WkMpSellerProduct::sendMail($mpProductId, 1, 1);
                } else {
                    $this->context->controller->errors[] = sprintf($this->l('You can not activate this product because shop %s is not active right now.'), $objMpSeller->shop_name_unique);
                }
            }
            Hook::exec('actionAfterToggleMPProductStatus', array('id_product' => $psProductId, 'active' => $objMpProduct->active));
        }
    }

    protected function processBulkEnableSelection()
    {
        return $this->processBulkStatusSelection(1);
    }

    protected function processBulkDisableSelection()
    {
        return $this->processBulkStatusSelection(0);
    }

    protected function processBulkStatusSelection($status)
    {
        if ($status == 1) {
            if (is_array($this->boxes) && !empty($this->boxes)) {
                foreach ($this->boxes as $id) {
                    $objSellerProduct = new WkMpSellerProduct($id);
                    if ($objSellerProduct->active == 0) {
                        $this->activeSellerProduct($id);
                    }
                }
            }
        } elseif ($status == 0) {
            if (is_array($this->boxes) && !empty($this->boxes)) {
                foreach ($this->boxes as $id) {
                    $objSellerProduct = new WkMpSellerProduct($id);
                    if ($objSellerProduct->active == 1) {
                        $this->activeSellerProduct($id);
                    }
                }
            }
        }
    }

    public function duplicateMpProduct($mpProductId = false)
    {
        if (!$mpProductId) {
            $mpProductId = Tools::getValue('id_mp_product');
        }

        $objMpSellerProduct = new WkMpSellerProduct();
        return $objMpSellerProduct->duplicateSellerProduct($mpProductId);
    }

    public function processBulkDuplicate()
    {
        if (is_array($this->boxes) && !empty($this->boxes)) {
            foreach ($this->boxes as $mpProductId) {
                if (!empty($mpProductId) && $mpProductId) {
                    $this->duplicateMpProduct($mpProductId);
                }
            }
            if (empty($this->context->controller->errors)) {
                Tools::redirectAdmin(
                    AdminController::$currentIndex.'&token='.$this->context->controller->token.'&conf=19'
                );
            }
        } else {
            $this->context->controller->errors[] = $this->l('You have to select at least one product in order to duplicate the product.');
        }
    }

    public function ajaxProcessDeleteProductImage()
    {
        //Delete images
        $idMpImage = Tools::getValue('id_mp_image');
        $idMpProduct = Tools::getValue('id_mp_product');
        $objMpImage = new WkMpSellerProductImage($idMpImage);
        if ($objMpImage->seller_product_id == $idMpProduct) {
            $deleted = $objMpImage->deleteProductImage($objMpImage->seller_product_image_name);
            if ($deleted) {
                if (Tools::getValue('is_cover')) {
                    die('2'); // if cover image deleted
                } else {
                    die('1'); // if normal image deleted
                }
            }
        }
        die('0');
    }

    public function ajaxProcessChangeCoverImage()
    {
        //Change cover image in product images
        $idMpImage = Tools::getValue('id_mp_image');
        $idMpProduct = Tools::getValue('id_mp_product');
        $objMpImage = new WkMpSellerProductImage($idMpImage);
        if ($objMpImage->seller_product_id == $idMpProduct) {
            $success = $objMpImage->setProductCoverImage($idMpProduct, $idMpImage);
            if ($success) {
                die('1');
            }
        }
        die('0');
    }

    public function ajaxProcessfindSellerDefaultLang()
    {
        //Get seller default langauge
        $mpIdCustomer = Tools::getValue('customer_id');
        $mpSellerInfo = WkMpSeller::getSellerDetailByCustomerId($mpIdCustomer);
        if ($mpSellerInfo) {
            $sellerLanguageData = Language::getLanguage((int) $mpSellerInfo['default_lang']);
            die(Tools::jsonEncode($sellerLanguageData)); //close ajax
        }
    }

    public function ajaxProcessUploadimage()
    {
        //Update product image
        if (Tools::getValue('actionIdForUpload')) {
            $actionIdForUpload = Tools::getValue('actionIdForUpload'); //it will be Product Id OR Seller Id
            $adminupload = Tools::getValue('adminupload'); //if uploaded by Admin from backend

            $finalData = WkMpSellerProductImage::uploadImage($_FILES, $actionIdForUpload, $adminupload);

            echo Tools::jsonEncode($finalData);
        }

        die; //ajax close
    }

    public function ajaxProcessDeleteimage()
    {
        //Delete product image
        if (Tools::getValue('actionpage') == 'product') {
            $imageName = Tools::getValue('image_name');
            if ($imageName) {
                WkMpSellerProductImage::deleteProductImage($imageName);
            }
        }

        die; //ajax close
    }

    public function ajaxProcessChangeImagePosition()
    {
        $idMpImage = Tools::getValue('id_mp_image');
        if ($idMpImage) {
            $idMpProduct = Tools::getValue('id_mp_product');
            $idImagePosition = Tools::getValue('id_mp_image_position');
            $toRowIndex = Tools::getValue('to_row_index') + 1;

            if ($sellerProduct = WkMpSellerProduct::getSellerProductByIdProduct($idMpProduct)) {
                $result = false;
                $objMpImage = new WkMpSellerProductImage($idMpImage);
                $objMpImage->position = $toRowIndex;
                if ($objMpImage->update()) {
                    $result = WkMpSellerProductImage::changeMpProductImagePosition($idMpProduct, $idMpImage, $toRowIndex, $idImagePosition);
                    if ($result) {
                        if (($idPsProduct = $sellerProduct['id_ps_product']) && ($idImage = $objMpImage->id_ps_image)) {
                            $objImage = new Image($idImage);
                            $objImage->position = $toRowIndex;
                            if ($objImage->update()) {
                                $result = WkMpSellerProductImage::changePsProductImagePosition($idPsProduct, $idImage, $toRowIndex, $idImagePosition);
                                if ($result) {
                                    die('1');//ajax close
                                }
                            }
                        } else {
                            die('1');//ajax close
                        }
                    }
                }
            }
        }

        die('0');//ajax close
    }

    public function ajaxProcessProductCategory()
    {
        //Load Prestashop category with ajax load of plugin jstree
        WkMpSellerProduct::getMpProductCategory();
    }

    public function ajaxProcessUpdateDefaultAttribute()
    {
        //Update default combination for seller product
        WkMpProductAttribute::updateMpProductDefaultAttribute();
    }

    public function ajaxProcessDeleteMpCombination()
    {
        //Delete Product combination from combination list at edit product page
        WkMpProductAttribute::deleteMpProductAttribute();
    }

    public function ajaxProcessChangeCombinationStatus()
    {
        //Change combination status through ajaxProcess if combination activate/deactivate module is enabled
        WkMpProductAttribute::changeCombinationStatus();
    }

    public function ajaxProcessUpdateMpCombinationQuantity()
    {
        //Change combination qty from product combination list
        $idMpProductAttribute = Tools::getValue('mp_product_attribute_id');
        $combinationQty = Tools::getValue('combi_qty');

        WkMpProductAttribute::setMpProductCombinationQuantity($idMpProductAttribute, $combinationQty);
    }

    public function ajaxProcessAddMoreFeature()
    {
        $mpSeller = WkMpSeller::getSellerDetailByCustomerId(Tools::getValue('idSeller'));
        WkMpHelper::assignDefaultLang($mpSeller['id_seller']);
        $sellerDefaultLanguage = Tools::getValue('sellerDefaultLang');
        if ($sellerDefaultLanguage) {
            $defaultLang = WkMpHelper::getDefaultLanguageBeforeFormSave($sellerDefaultLanguage);
            $this->context->smarty->assign(array(
                'current_lang' => Language::getLanguage((int) $defaultLang),
                'default_lang' => $defaultLang,
                ));
        }
        $permissionData = WkMpHelper::productTabPermission();
        $this->context->smarty->assign(
            array(
                'ps_img_dir' => _PS_IMG_.'l/',
                'controller' => 'admin',
                'fieldrow' => Tools::getValue('fieldrow'),
                'choosedLangId' => Tools::getValue('choosedLangId'),
                'available_features' => Feature::getFeatures($this->context->language->id, (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP)),
                'permissionData' => $permissionData,
            )
        );
        die($this->context->smarty->fetch(_PS_MODULE_DIR_.'marketplace/views/templates/front/product/_partials/more-product-feature.tpl'));
    }

    public function ajaxProcessGetFeatureValue()
    {
        $featuresValue = FeatureValue::getFeatureValuesWithLang(
            $this->context->language->id,
            (int) Tools::getValue('idFeature')
        );
        if (!empty($featuresValue)) {
            die(Tools::jsonEncode($featuresValue));
        }

        die(false);
    }

    public function ajaxProcessValidateMpForm()
    {
        $data = array('status' => 'ok');
        $params = array();
        parse_str(Tools::getValue('formData'), $params);
        if (!empty($params)) {
            WkMpSellerProduct::validationProductFormField($params);

            // if features are enable or seller is trying to add features
            if (isset($params['wk_feature_row'])) {
                WkMpProductFeature::checkFeatures($params);
            }
        }
        die(Tools::jsonEncode($data));
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        if ($this->display == 'edit') {
            //Upload images
            $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/uploadimage-css/jquery.filer.css');
            $this->addCSS(
                _MODULE_DIR_.$this->module->name.'/views/css/uploadimage-css/jquery.filer-dragdropbox-theme.css'
            );
            $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/uploadimage-css/uploadphoto.css');
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/uploadimage-js/jquery.filer.js');
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/uploadimage-js/uploadimage.js');
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/imageedit.js');
            $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/managecombination.js');
        }
    }
}
