<?php
include_once(_PS_MODULE_DIR_ . 'customapi/classes/Json.php');

class CategoryDetailed extends ObjectModel
{
    public function __construct()
    {

        if ($_GET['cat_id'] == null) {
            Json::generate(400, "error", "You must provide a valid category id.");
        } else {
            $this->getCategory();
        }
    }

    private function getCategory()
    {

        $catId = $_GET['cat_id'];
        $objCat = new Category($catId);
        $catDetailResponse = '';
        if (empty($objCat->id)) {
            Json::generate(400, "error", "You have passed wrong cat id.");
        } else {
            $catDetailResponse = $this->getCategoryProductPriceInfo($catId);
        }

        Json::generate(200, "success", "Product Category information fetched successfully.", $catDetailResponse);
    }

    private function getCategoryMessage($catId)
    {
        $sql = "SELECT * FROM `"._DB_PREFIX_."message` WHERE id = $catId AND private=0";
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);


        return $result;
    }

    private function getCategoryProductPriceInfo($catId)
    {

        $product_query = Db::getInstance()->getRow(
            'SELECT max(price)max_price,min(price)min_price
            FROM '._DB_PREFIX_.'product 
            where `id_category_default` = '.$catId);

        $catMessages = array();
        $messages = $this->getCategoryMessage($catId);

        if (!empty($messages)) {
            foreach ($messages as $message) {
                $catMessages[] = array(
                    'message' => $message['message']
                );
            }
        }

        $resultResponse = array(
            'max_price'   => $product_query['max_price'],
            'min_price' => $product_query['min_price'],
            'message' => $catMessages[0]['message']
        );

        return $resultResponse;
    }
}
