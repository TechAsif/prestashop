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

class MarketplaceUploadImageModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        $this->display_header = false;
        $this->display_footer = false;

        if (Tools::getValue('action') == 'uploadimage') {
            // Upload image
            if (Tools::getValue('actionIdForUpload')) {
                $actionIdForUpload = Tools::getValue('actionIdForUpload'); //it will be Product Id OR Seller Id
                $adminupload = Tools::getValue('adminupload'); //if uploaded by Admin from backend

                $finalData = WkMpSellerProductImage::uploadImage($_FILES, $actionIdForUpload, $adminupload);

                echo Tools::jsonEncode($finalData);
            }
        } else if (Tools::getValue('action') == 'deleteimage' && Tools::getValue('actionpage') == 'product') {
            //Delete image (This action works only on Product page)
            $imageName = Tools::getValue('image_name');
            if ($imageName) {
                WkMpSellerProductImage::deleteProductImage($imageName);
            }
        }

        die; //ajax close
    }
}
