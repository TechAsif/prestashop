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

class MarketplaceProductImageEditModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        $this->display_header = false;
        $this->display_footer = false;
    }

    public function initContent()
    {
        WkMpHelper::assignGlobalVariables(); // Assign global static variable on tpl
        WkMpHelper::defineGlobalJSVariables(); // Define global js variable on js file

        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }

        if ($mpIdProduct = Tools::getValue('id_product')) {
            //Assign and display product active/inactive images
            WkMpSellerProductImage::getProductImageDetails($mpIdProduct);
            $this->context->smarty->assign('displayCancelIcon', 1);
            $this->setTemplate('module:marketplace/views/templates/front/product/imageedit.tpl');
        }
    }

    public function displayAjaxDeleteProductImage()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }

        // Ajax Delete image
        $idMpImage = Tools::getValue('id_mp_image');
        $idMpProduct = Tools::getValue('id_mp_product');
        if ($idMpImage && $idMpProduct) {
            $objMpImage = new WkMpSellerProductImage($idMpImage);
            if ($objMpImage->seller_product_id == $idMpProduct) {
                $deleted = $objMpImage->deleteProductImage($objMpImage->seller_product_image_name);
                if ($deleted) {
                    //To manage staff log (changes add/update/delete) => 2 for Add action
                    WkMpHelper::setStaffHook($this->context->customer->id, 'updateproduct', $idMpProduct, 2);
                    if (Tools::getValue('is_cover')) {
                        die('2'); // if cover image deleted
                    } else {
                        die('1'); // if normal image deleted
                    }
                }
            }
        }
        die('0');
    }

    public function displayAjaxChangeCoverImage()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }

        // Ajax Change cover image
        $idMpImage = Tools::getValue('id_mp_image');
        $idMpProduct = Tools::getValue('id_mp_product');
        if ($idMpImage && $idMpProduct) {
            $objMpImage = new WkMpSellerProductImage($idMpImage);
            if ($objMpImage->seller_product_id == $idMpProduct) {
                $success = $objMpImage->setProductCoverImage($idMpProduct, $idMpImage);
                if ($success) {
                    //To manage staff log (changes add/update/delete) => 2 for Add action
                    WkMpHelper::setStaffHook($this->context->customer->id, 'updateproduct', $idMpProduct, 2);
                    die('1');
                }
            }
        }
        die('0');
    }

    public function displayAjaxChangeImagePosition()
    {
        if (!$this->isTokenValid()) {
            die('Something went wrong!');
        }

        // Ajax Image position
        $idMpImage = Tools::getValue('id_mp_image');
        $idMpProduct = Tools::getValue('id_mp_product');
        if ($idMpImage && $idMpProduct) {
            $idImagePosition = Tools::getValue('id_mp_image_position');
            $toRowIndex = Tools::getValue('to_row_index') + 1;

            if ($sellerProduct = WkMpSellerProduct::getSellerProductByIdProduct($idMpProduct)) {
                $result = false;
                $objMpImage = new WkMpSellerProductImage($idMpImage);
                $objMpImage->position = $toRowIndex;
                if ($objMpImage->update()) {
                    $result = WkMpSellerProductImage::changeMpProductImagePosition(
                        $idMpProduct,
                        $idMpImage,
                        $toRowIndex,
                        $idImagePosition
                    );
                    if ($result) {
                        if ($sellerProduct['id_ps_product'] && $objMpImage->id_ps_image) {
                            $objImage = new Image($objMpImage->id_ps_image);
                            $objImage->position = $toRowIndex;
                            if ($objImage->update()) {
                                $result = WkMpSellerProductImage::changePsProductImagePosition(
                                    $sellerProduct['id_ps_product'],
                                    $objMpImage->id_ps_image,
                                    $toRowIndex,
                                    $idImagePosition
                                );
                                if ($result) {
                                    //To manage staff log (changes add/update/delete)
                                    WkMpHelper::setStaffHook($this->context->customer->id, 'updateproduct', $idMpProduct, 2); // 2 for Add action
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
}
