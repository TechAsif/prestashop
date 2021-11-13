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

class WkMpSellerProductCategory extends ObjectModel
{
    public $id_category;
    public $id_seller_product;
    public $is_default; //In marketplace, this field is not using any where but may be it is using in any mp addons

    public $checkCategories;
    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id);

        $this->checkCategories = false;
    }

    public static $definition = array(
        'table' => 'wk_mp_seller_product_category',
        'primary' => 'id_mp_category_product',
        'fields' => array(
            'id_category' => array('type' => self::TYPE_INT),
            'is_default' => array('type' => self::TYPE_INT),
            'id_seller_product' => array('type' => self::TYPE_INT),
        ),
    );

    /**
     * Get Seller's Product Default Category by using Id Product
     *
     * @param  int $mpproductid Seller's Product ID
     * @return int Value of category
     */
    public function getSellerProductDefaultCategory($mpproductid)
    {
        $defaultcategory = Db::getInstance()->getValue('SELECT `id_category` FROM `'._DB_PREFIX_.'wk_mp_seller_product` WHERE `id_mp_product` = '.(int) $mpproductid);
        if ($defaultcategory) {
            return $defaultcategory;
        }

        return false;
    }

    /**
     * Get prestashop jstree category
     *
     * @param  int $catId node id of jstree category
     * @param  int $selectedCatIds selected category in jstree
     * @param  int $idLang content display of selected language
     * @return category load
     */
    public function getProductCategory($catId, $selectedCatIds, $idLang)
    {
        if ($catId == '#') {
            //First time load
            $root = Category::getRootCategory();
            $category = Category::getHomeCategories($idLang, false);
            $categoryArray = array();
            foreach ($category as $catkey => $cat) {
                $categoryArray[$catkey]['id'] = $cat['id_category'];
                $categoryArray[$catkey]['text'] = $cat['name'];
                $subcategory = $this->getPsCategories($cat['id_category'], $idLang);
                $subChildSelect = false;
                if ($subcategory) {
                    $categoryArray[$catkey]['children'] = true;

                    foreach ($subcategory as $subcat) {
                        if (in_array($subcat['id_category'], $selectedCatIds)) {
                            $subChildSelect = true;
                        } else {
                            $this->findChildCategory($subcat['id_category'], $idLang, $selectedCatIds);
                            if ($this->checkCategories) {
                                $subChildSelect = true;
                                $this->checkCategories = false;
                            }
                        }
                    }
                } else {
                    $categoryArray[$catkey]['children'] = false;
                }

                if (in_array($cat['id_category'], $selectedCatIds) && $subChildSelect == true) {
                    $categoryArray[$catkey]['state'] = array('opened' => true, 'selected' => true);
                } elseif (in_array($cat['id_category'], $selectedCatIds) && $subChildSelect == false) {
                    $categoryArray[$catkey]['state'] = array('selected' => true);
                } elseif (!in_array($cat['id_category'], $selectedCatIds) && $subChildSelect == true) {
                    $categoryArray[$catkey]['state'] = array('opened' => true);
                }
            }

            $treeLoad = array();
            if (in_array($root->id_category, $selectedCatIds)) {
                $treeLoad =  array("id" => $root->id_category,
                                    "text" => $root->name,
                                    "children" => $categoryArray,
                                    "state" => array('opened' => true, 'selected' => true)
                                );
            } else {
                $treeLoad =  array("id" => $root->id_category,
                                    "text" => $root->name,
                                    "children" => $categoryArray,
                                    "state" => array('opened' => true)
                                );
            }
        } else {
            //If sub-category is selected then its automatically called
            $childcategory = $this->getPsCategories($catId, $idLang);
            $treeLoad = array();
            $singletreeLoad = array();
            foreach ($childcategory as $cat) {
                $subcategoryArray = array();
                $subcategoryArray['id'] = $cat['id_category'];
                $subcategoryArray['text'] = $cat['name'];
                $subcategory = $this->getPsCategories($cat['id_category'], $idLang);

                $subChildSelect = false;
                if ($subcategory) {
                    $subcategoryArray['children'] = true;

                    foreach ($subcategory as $subcat) {
                        if (in_array($subcat['id_category'], $selectedCatIds)) {
                            $subChildSelect = true;
                        } else {
                            $this->findChildCategory($subcat['id_category'], $idLang, $selectedCatIds);
                            if ($this->checkCategories) {
                                $subChildSelect = true;
                                $this->checkCategories = false;
                            }
                        }
                    }
                } else {
                    $subcategoryArray['children'] = false;
                }

                if (in_array($cat['id_category'], $selectedCatIds) && $subChildSelect == true) {
                    $subcategoryArray['state'] = array('opened' => true, 'selected' => true);
                } elseif (in_array($cat['id_category'], $selectedCatIds) && $subChildSelect == false) {
                    $subcategoryArray['state'] = array('selected' => true);
                } elseif (!in_array($cat['id_category'], $selectedCatIds) && $subChildSelect == true) {
                    $subcategoryArray['state'] = array('opened' => true);
                }

                $singletreeLoad[] = $subcategoryArray;
            }

            $treeLoad = $singletreeLoad;
        }

        return $treeLoad;
    }

    public function findChildCategory($id_category, $idLang, $selectedCatIds)
    {
        $subcategory = $this->getPsCategories($id_category, $idLang);
        if ($subcategory) {
            foreach ($subcategory as $subcat) {
                if (in_array($subcat['id_category'], $selectedCatIds)) {
                    $this->checkCategories = true;
                    return;
                } else {
                    $this->findChildCategory($subcat['id_category'], $idLang, $selectedCatIds);
                }
            }
        } else {
            return false;
        }
    }

    /**
    * Get seller product categories by using seller product ID
    *
    * @param int $mpProductID Seller Product ID
    * @return array/boolean
    */
    public static function getMultipleCategories($mpProductID)
    {
        $mcategory = Db::getInstance()->executeS(
            'SELECT `id_category` FROM `'._DB_PREFIX_.'wk_mp_seller_product_category`
            WHERE `id_seller_product` = '.(int) $mpProductID
        );

        if (empty($mcategory)) {
            return false;
        }

        $mcat = array();
        foreach ($mcategory as $cat) {
            $mcat[] = $cat['id_category'];
        }

        return $mcat;
    }

    // Not using any more, using getHomeCategories funrcion instead of
    public function getPsCategories($id_parent, $id_lang)
    {
        return Db::getInstance()->executeS(
            'SELECT a.`id_category`, a.`id_parent`, l.`name` FROM `'._DB_PREFIX_.'category` a
            LEFT JOIN `'._DB_PREFIX_.'category_lang` l ON (a.`id_category` = l.`id_category`)
            WHERE a.`id_parent` = '.(int) $id_parent.'
            AND l.`id_lang` = '.(int) $id_lang.'
            AND l.`id_shop` = '.(int) Context::getContext()->shop->id.'
            ORDER BY a.`id_category`'
        );
    }

    /**
    * Delete product category By seller product id
    *
    * @param int $idMpProduct Seller Product ID
    * @return array/boolean
    */
    public function deleteProductCategory($idMpProduct)
    {
        return Db::getInstance()->delete('wk_mp_seller_product_category', 'id_seller_product = '. (int) $idMpProduct);
    }

    /**
    * Copy seller product category into other product
    *
    * @param int $originalMpProductId - Original Product ID
    * @param int $duplicateMpProductId - Duplicate Product ID
    *
    * @return array/boolean
    */
    public static function copyMpProductCategories($originalMpProductId, $duplicateMpProductId)
    {
        $categories = Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'wk_mp_seller_product_category`
            WHERE `id_seller_product` = '.(int) $originalMpProductId
        );
        if ($categories) {
            //Add into category table
            foreach ($categories as $pCategory) {
                $objMpCategory = new self();
                $objMpCategory->id_seller_product = $duplicateMpProductId;
                $objMpCategory->id_category = $pCategory['id_category'];
                $objMpCategory->is_default = $pCategory['is_default'];
                $objMpCategory->add();
                unset($objMpCategory);
            }
        }
        return true;
    }
}
