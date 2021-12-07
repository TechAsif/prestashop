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

class WkMpSellerProductImage extends ObjectModel
{
    public $seller_product_id;
    public $seller_product_image_name;
    public $id_ps_image;
    public $position;
    public $cover;
    public $active;

    public static $definition = array(
        'table' => 'wk_mp_seller_product_image',
        'primary' => 'id_mp_product_image',
        'fields' => array(
            'seller_product_id' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'seller_product_image_name' => array('type' => self::TYPE_STRING),
            'id_ps_image' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'position' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'cover' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
        ),
    );

    /**
     * Get Seller Product Images By Using Seller Product ID.
     *
     * @param int $idMpProduct Seller Product Id
     *
     * @return array/boolean
     */
    public function getProductImageBySellerIdProduct($idMpProduct)
    {
        $productImage = Db::getInstance()->executeS(
            'SELECT  * FROM ' . _DB_PREFIX_ . 'wk_mp_seller_product_image
            WHERE `seller_product_id` = ' . (int) $idMpProduct
        );

        if (!empty($productImage)) {
            return $productImage;
        }

        return false;
    }

    /**
     * Get Seller Product Images by using Prestashop Image ID.
     *
     * @param int $idPsImage Prestashop Image ID
     *
     * @return array/boolean
     */
    public function getProductImageByPsIdImage($idPsImage)
    {
        return Db::getInstance()->getRow(
            'SELECT  * FROM ' . _DB_PREFIX_ . 'wk_mp_seller_product_image
            WHERE `id_ps_image` = ' . (int) $idPsImage
        );
    }

    /**
     * Upload product images.
     *
     * @param int $mpIdProduct Seller Product ID
     * @param string $imageNewName Image name
     *
     * @return bool
     */
    public static function uploadProductImage($mpIdProduct, $imageNewName)
    {
        $objMpImage = new self();
        $objMpImage->seller_product_id = $mpIdProduct;
        $objMpImage->seller_product_image_name = $imageNewName;
        $objMpImage->position = self::getProductImageHighestPosition($mpIdProduct);
        if (!self::getProductCoverImage($mpIdProduct)) {
            $objMpImage->cover = 1;
        } else {
            $objMpImage->cover = 0;
        }
        if ($objMpImage->save()) {
            //if product is active then check configuration that product after update need to approved by admin or not
            WkMpSellerProduct::deactivateProductAfterUpdate($mpIdProduct);

            $mpProductDetails = WkMpSellerProduct::getSellerProductByIdProduct($mpIdProduct);
            if ($mpProductDetails && $mpProductDetails['active'] && $mpProductDetails['id_ps_product']) {
                //Upload images in ps product
                $objSellerProduct = new WkMpSellerProduct($mpIdProduct);
                $objSellerProduct->updatePsProductImage($mpIdProduct, $mpProductDetails['id_ps_product']);
            }

            return $objMpImage->id;
        }

        return false;
    }

    public static function getProductImageHighestPosition($mpIdProduct)
    {
        $position = Db::getInstance()->getValue(
            'SELECT MAX(`position`) AS max
            FROM `'._DB_PREFIX_.'wk_mp_seller_product_image`
            WHERE `seller_product_id` = '.(int) $mpIdProduct
        );

        if (gettype($position) === 'NULL') {
            $position = 1;
        } else {
            $position += 1;
        }

        return $position;
    }

    public static function getProductCoverImage($mpIdProduct)
    {
        $coverImage = Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_product_image`
            WHERE `seller_product_id` = '.(int) $mpIdProduct.' AND `cover` = 1'
        );
        if ($coverImage) {
            return $coverImage;
        }
        return false;
    }

    public static function cleanPositions($idMpProduct)
    {
        $return = true;
        Db::getInstance()->execute('SET @i = 0', false);
        $return = Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'wk_mp_seller_product_image`
            SET `position` = @i:=@i+1
            WHERE `seller_product_id` = '.(int) $idMpProduct.'
            ORDER BY `position`'
        );

        return $return;
    }

    /**
     * Delete Product images by using Image name
     * Delete image from ps product if product is active
     * If cover image deleting, make first image as a cover.
     *
     * @param string $imageName Image Name
     *
     * @return bool
     */
    public static function deleteProductImage($imageName)
    {
        $objMpImage = new self();
        $imageData = $objMpImage->getProductImageByImageName($imageName);
        if ($imageData) {
            $idMpProduct = $imageData['seller_product_id'];
            $objMpImage = new self($imageData['id_mp_product_image']);
            $isCover = $objMpImage->cover;
            if ($objMpImage->delete()) {
                $uploadDirPath = _MODULE_DIR_ . 'marketplace/views/img/product_img/';
                $imageFile = $uploadDirPath . $imageName;
                if (file_exists($imageFile)) {
                    unlink($imageFile);
                }

                self::cleanPositions($idMpProduct);

                $mpProductDetails = WkMpSellerProduct::getSellerProductByIdProduct($idMpProduct);

                //Delete image from ps product if product is active
                if ($objMpImage->active
                && $objMpImage->id_ps_image
                && $mpProductDetails
                && $mpProductDetails['id_ps_product']
                ) {
                    $idPsImage = $objMpImage->id_ps_image;

                    $image = new Image($idPsImage);
                    if ($image->delete()) {
                        Product::cleanPositions($idPsImage);
                    }
                }

                // if cover image deleting, make first image as a cover
                if ($isCover) {
                    $productFirstActiveImage = Db::getInstance()->getRow(
                        'SELECT  * FROM ' . _DB_PREFIX_ . 'wk_mp_seller_product_image
                        WHERE `seller_product_id` = '.(int) $idMpProduct.' AND `active` = 1'
                    );
                    if ($productFirstActiveImage) {
                        $objMpImageCover = new self($productFirstActiveImage['id_mp_product_image']);
                        $objMpImageCover->cover = 1;
                        if ($objMpImageCover->save() && $objMpImageCover->active && $objMpImageCover->id_ps_image) {
                            $objImage = new Image($objMpImageCover->id_ps_image);
                            $objImage->cover = 1;
                            $objImage->save();
                        }
                    }
                }

                return true;
            }
        }
        return false;
    }

    /**
     * Upload seller product imgage, profile image and shop image.
     *
     * @param file $files Image data that will get by $_FILES
     * @param int $actionIdForUpload Product Id or Seller Id
     * @param string $adminupload
     */
    public static function uploadImage($files, $actionIdForUpload, $adminupload)
    {
        if (isset($files['sellerprofileimage'])) { //upload seller profile image
            $dirName = 'seller_img/';
            $imageFiles = $files['sellerprofileimage'];
            $actionType = 'sellerprofileimage';
        } elseif (isset($files['shopimage'])) { //upload shop image
            $dirName = 'shop_img/';
            $imageFiles = $files['shopimage'];
            $actionType = 'shopimage';
        } elseif (isset($files['productimages'])) {//upload seller product image
            $dirName = 'product_img/';
            $imageFiles = $files['productimages'];
            $actionType = 'productimage';
        } elseif (isset($files['profilebannerimage'])) { //upload seller profile Banner
            $dirName = 'seller_banner/';
            $imageFiles = $files['profilebannerimage'];
            $actionType = 'profilebannerimage';
        } elseif (isset($files['shopbannerimage'])) { //upload shop banner
            $dirName = 'shop_banner/';
            $imageFiles = $files['shopbannerimage'];
            $actionType = 'shopbannerimage';
        }

        if ($adminupload) {
            $uploadDirPath = '../modules/marketplace/views/img/' . $dirName;
        } else {
            $uploadDirPath = 'modules/marketplace/views/img/' . $dirName;
        }

        if ($actionType == 'productimage') {
            //Max upload size for product image
            $wkMaxSize = (int)Configuration::get('PS_LIMIT_UPLOAD_IMAGE_VALUE');
        } else {
            //Max upload size for seller, shop image and banner
            $wkMaxSize = (int)str_replace('M', '', ini_get('post_max_size'));
        }

        $uploader = new WkMpImageUploader();
        $data = $uploader->upload($imageFiles, array(
            'actionType' => $actionType, //Maximum Limit of files. {null, Number}
            //'limit' => 10, //Maximum Limit of files.{null, Number}
            'maxSize' => $wkMaxSize, //Max Size of files{null, Number(in MB)}-If not set then it will take server size]
            'extensions' => array('jpg', 'png', 'gif', 'jpeg'), //Whitelist for file extension.
            'required' => false, //Minimum one file is required for upload {Boolean}
            'uploadDir' => $uploadDirPath, //Upload directory {String}
            'title' => array('name'), //New file name {null, String, Array} *please read documentation in README.md
        ));

        $finalData = array();
        $finalResult = false;
        if ($data['hasErrors']) {
            $finalData['status'] = 'fail';
            $finalData['file_name'] = '';
            $finalData['error_message'] = $data['errors'][0];
	    //echo $data['errors'][0];
        } elseif ($data['isComplete']) {
            if ($data['data']['metas'][0]['name']) {
                $imageNewName = $data['data']['metas'][0]['name'];

                if ($actionType == 'productimage') {
                    // $actionIdForUpload is mpIdProduct if it is product image
                    $finalResult = self::uploadProductImage($actionIdForUpload, $imageNewName);
                } elseif ($actionType == 'sellerprofileimage') {
                    // $actionIdForUpload is mpIdSeller if it is seller profile image
                    $objMpSeller = new WkMpSeller($actionIdForUpload);
                    if ($objMpSeller->profile_image) { //delete old seller image if exist
                        $imageFile = $uploadDirPath . $objMpSeller->profile_image;
                        if (file_exists($imageFile)) {
                            unlink($imageFile);
                        }
                    }
                    $objMpSeller->profile_image = $imageNewName;
                    if ($objMpSeller->save()) {
                        $finalResult = true;
                    }
                } elseif ($actionType == 'shopimage') {
                    // $actionIdForUpload is mpIdSeller if it is shop image
                    $objMpSeller = new WkMpSeller($actionIdForUpload);
                    if ($objMpSeller->shop_image) { //delete old shop image if exist
                        $imageFile = $uploadDirPath . $objMpSeller->shop_image;
                        if (file_exists($imageFile)) {
                            unlink($imageFile);
                        }
                    }
                    $objMpSeller->shop_image = $imageNewName;
                    if ($objMpSeller->save()) {
                        $finalResult = true;
                    }
                } elseif ($actionType == 'profilebannerimage') {
                    // $actionIdForUpload is mpIdSeller if it is profile banner
                    $objMpSeller = new WkMpSeller($actionIdForUpload);
                    if ($objMpSeller->profile_banner) { //delete old shop image if exist
                        $imageFile = $uploadDirPath . $objMpSeller->profile_banner;
                        if (file_exists($imageFile)) {
                            unlink($imageFile);
                        }
                    }
                    $objMpSeller->profile_banner = $imageNewName;
                    if ($objMpSeller->save()) {
                        $finalResult = true;
                    }
                } elseif ($actionType == 'shopbannerimage') {
                    // $actionIdForUpload is mpIdSeller if it is shop banner
                    $objMpSeller = new WkMpSeller($actionIdForUpload);
                    if ($objMpSeller->shop_banner) { //delete old shop image if exist
                        $imageFile = $uploadDirPath . $objMpSeller->shop_banner;
                        if (file_exists($imageFile)) {
                            unlink($imageFile);
                        }
                    }
                    $objMpSeller->shop_banner = $imageNewName;
                    if ($objMpSeller->save()) {
                        $finalResult = true;
                    }
                }

                if ($finalResult == true) {
                    $finalData['status'] = 'success';
                    $finalData['file_name'] = $imageNewName;
                    $finalData['error_message'] = '';
                }
            }
        }

        return $finalData;
    }

    /**
     * Get Product image by using image name from the seller product image table.
     *
     * @param string $imageName Image Name
     *
     * @return array
     */
    public function getProductImageByImageName($imageName)
    {
        return Db::getInstance()->getRow(
            'SELECT  * FROM ' . _DB_PREFIX_ . 'wk_mp_seller_product_image
            WHERE `seller_product_image_name` = \'' . pSQL($imageName) . '\''
        );
    }

    /**
     * Update id_ps_image and status of seller product name using Seller Product ID.
     *
     * @param int $idMpProduct Seller Product ID
     * @param int $status 1/0
     * @param int $idPsImage Prestashop Image ID
     *
     * @return bool
     */
    public static function updateStatusBySellerIdProduct($idMpProduct, $status, $idPsImage)
    {
        return Db::getInstance()->update(
            'wk_mp_seller_product_image',
            array(
                'active' => (int) $status,
                'id_ps_image' => (int) $idPsImage,
            ),
            '`seller_product_id` = ' . (int) $idMpProduct
        );
    }

    /**
     * Update id_ps_image and status of seller product name using Seller Product ID.
     *
     * @param int $id primary Id of table
     * @param int $status 1/0
     * @param int $idPsImage Prestashop Image ID
     * @param int $position Prestashop Image position
     *
     * @return bool
     */
    public static function updateStatusById($id, $status, $idPsImage)
    {
        return Db::getInstance()->update(
            'wk_mp_seller_product_image',
            array(
                'active' => (int) $status,
                'id_ps_image' => (int) $idPsImage,
            ),
            '`id_mp_product_image` = ' . (int) $id
        );
    }

    /**
     * Delete product image according to Mp Product Image Name.
     *
     * @param int $idImage primary Id of table
     * @param string $imageName Seller product image name
     *
     * @return bool
     */
    public static function deleteProductImageByMpProductImageName($idImage, $imageName)
    {
        return Db::getInstance()->delete(
            'wk_mp_seller_product_image',
            'id_mp_product_image =' . (int) $idImage . ' AND
            `seller_product_image_name` = \'' . pSQL($imageName) . '\''
        );
    }

    /**
     * Assign and display product active/inactive images at product page.
     *
     * @param int $mpIdProduct seller product id
     *
     * @return assign
     */
    public static function getProductImageDetails($mpIdProduct)
    {
        $context = Context::getContext();

        $productImage = Db::getInstance()->executeS(
            'SELECT  * FROM '._DB_PREFIX_.'wk_mp_seller_product_image
            WHERE `seller_product_id` = '.(int) $mpIdProduct.'
            ORDER BY `position` ASC'
        );
        if ($productImage) {
            $coverImage = self::getProductCoverImage($mpIdProduct);
            if (!$coverImage) {
                $coverImage = $productImage[0]; //Assign first image as cover if cover not exist
            }

            $context->smarty->assign(array(
                'image_detail' => $productImage,
                'cover_image' => $coverImage,
                'id_mp_product' => $mpIdProduct,
            ));
        }

        $editPermission = 1;
        if (Module::isEnabled('mpsellerstaff') && isset($context->customer->id)) {
            $staffDetails = WkMpSellerStaff::getStaffInfoByIdCustomer($context->customer->id);
            if ($staffDetails
                && $staffDetails['active']
                && $staffDetails['id_seller']
                && $staffDetails['seller_status']
            ) {
                $idTab = WkMpTabList::MP_PRODUCT_TAB; //For Product
                $staffTabDetails = WkMpTabList::getStaffPermissionWithTabName(
                    $staffDetails['id_staff'],
                    $context->language->id,
                    $idTab
                );
                if ($staffTabDetails) {
                    $editPermission = $staffTabDetails['edit'];
                }
            }
        }
        $context->smarty->assign('edit_permission', $editPermission);
    }

    public static function changeMpProductImagePosition($idMpProduct, $idMpImage, $toRowIndex, $idImagePosition)
    {
        if ($toRowIndex < $idImagePosition) {
            // Move to Top Position
            return Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'wk_mp_seller_product_image` SET `position` = position+1
                WHERE `position` >= '.(int) $toRowIndex.'
                AND `position` <= '.(int) $idImagePosition.'
                AND `id_mp_product_image` != '.(int) $idMpImage.'
                AND `seller_product_id` = '.(int) $idMpProduct
            );
        } elseif ($toRowIndex >= $idImagePosition) {
            // Move to Bottom Position
            return Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'wk_mp_seller_product_image` SET `position` = position-1
                WHERE `position` <= '.(int) $toRowIndex.'
                AND `position` >= '.(int) $idImagePosition.'
                AND `id_mp_product_image` != '.(int) $idMpImage . '
                AND `seller_product_id` = '.(int) $idMpProduct
            );
        }

        return false;
    }

    public static function changePsProductImagePosition($idPsProduct, $idImage, $toRowIndex, $idImagePosition)
    {
        if ($toRowIndex < $idImagePosition) {
            return Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'image` SET `position` = position+1
                WHERE position >= '.(int) $toRowIndex.'
                AND `position` <= '.(int) $idImagePosition.'
                AND `id_image` != '.(int) $idImage.'
                AND `id_product` = '.(int) $idPsProduct
            );
        } elseif ($toRowIndex >= $idImagePosition) {
            return Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'image` SET `position` = position-1
                WHERE position <= '.(int) $toRowIndex.'
                AND `position` >= '.(int) $idImagePosition.'
                AND `id_image` != '.(int) $idImage.'
                AND `id_product` =' . (int) $idPsProduct
            );
        }

        return false;
    }

    public static function setProductCoverImage($idMpProduct, $idMpImage)
    {
        self::deleteCover($idMpProduct);
        $objMpImage = new WkMpSellerProductImage($idMpImage);
        $objMpImage->cover = 1;
        if ($objMpImage->update()) {
            $mpProductDetails = WkMpSellerProduct::getSellerProductByIdProduct($idMpProduct);
            if ($objMpImage->active
            && $objMpImage->id_ps_image
            && $mpProductDetails
            && $mpProductDetails['id_ps_product']
            ) {
                Image::deleteCover((int) $mpProductDetails['id_ps_product']);
                $image = new Image((int) $objMpImage->id_ps_image);
                $image->cover = 1;

                // unlink existing cover image in temp folder
                @unlink(_PS_TMP_IMG_DIR_.'product_'.(int) $image->id_product);
                @unlink(_PS_TMP_IMG_DIR_.'product_mini_'.(int) $image->id_product.'_'.Context::getContext()->shop->id);

                $image->update();
            }

            return true;
        }
        return false;
    }

    public static function deleteCover($idMpProduct)
    {
        return Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'wk_mp_seller_product_image`
            SET `cover` = NULL
            WHERE `seller_product_id` = '.(int) $idMpProduct
        );
    }

    public static function copyMpProductImages($originalMpProductId, $duplicateMpProductId, $originalProductStatus)
    {
        $objMpImage = new self();
        $productImages = $objMpImage->getProductImageBySellerIdProduct($originalMpProductId);
        if ($productImages) {
            $imageMappingData = array();
            foreach ($productImages as $image) {
                $imageName = $image['seller_product_image_name'];
                $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
                $randomImageName = Tools::passwdGen(6).'.'.$imageExtension;

                $objMpProductImg = new self();
                $objMpProductImg->seller_product_id = (int) $duplicateMpProductId;
                $objMpProductImg->seller_product_image_name = pSQL($randomImageName);
                $objMpProductImg->id_ps_image = 0;
                $objMpProductImg->position = (int) $image['position'];
                $objMpProductImg->cover = $image['cover'];
                $objMpProductImg->active = 0;
                if ($objMpProductImg->add()) {
                    $mpImgPath = _PS_MODULE_DIR_.'marketplace/views/img/product_img/';
                    ImageManager::resize(
                        $mpImgPath.$imageName,
                        $mpImgPath.$randomImageName
                    );

                    if ($originalProductStatus) {
                        //If product is active then image mapping index will be id_ps_image
                        if ($image['id_ps_image']) {
                            $imageMappingData[$image['id_ps_image']] = $objMpProductImg->id;
                        }
                    } else {
                        //If product is deactive then image mapping index will be id_mp_product_image
                        $imageMappingData[$image['id_mp_product_image']] = $objMpProductImg->id;
                    }
                }
                unset($objMpProductImg);
            }
            return $imageMappingData;
        }
        return true;
    }
}
