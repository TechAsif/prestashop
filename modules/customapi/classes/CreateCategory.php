<?php
include_once(_PS_MODULE_DIR_ . 'customapi/classes/Json.php');

class CreateCategory extends ObjectModel
{
    public function __construct()
    {

        if ($_POST['id_parent'] == null) {
            Json::generate(400, "error", "You must provide a parent.");
        }elseif ($_POST['id_shop_default'] == null){
            Json::generate(400, "error", "You must provide default shop.");
        }
        elseif ($_POST['position'] == null){
            Json::generate(400, "error", "You must provide position.");
        }
        elseif ($_POST['active'] == null){
            Json::generate(400, "error", "You must provide status");
        }
        elseif ($_POST['is_root_category'] == null){
            Json::generate(400, "error", "You must provide root category");
        }
        else {
            $this->postCategory();
        }
    }

    private function postCategory()
    {
        $data = var_export($_POST, true);
        $response = '';
        if (empty($data)) {
            Json::generate(400, "error", "No data found");
        } else {
            $response = $this->createNewCategory($data);
        }
        Json::generate(200, "success", "Category inserted successfully.", $response);
    }


    private function createNewCategory($data)
    {
        $now = new DateTime();
        $cur_date_time="'".$now->format('Y-m-d H:i:s')."'";

        $insert_op = 'INSERT INTO ' . _DB_PREFIX_ . 'category
            (`id_parent`,`id_shop_default`,`level_depth`, `nleft`, `nright`,`active`,`date_add`,`date_upd`,`position`,
            `is_root_category`)
            values(
             ' . (int)$data->id_parent . ',
             ' . (int)$data->id_shop_default . ',
             ' . (int)$data->level_depth . ',
             ' . (int)$data->nleft . ',
             ' . (int)$data->nright . ',
             ' . (int)$data->active . ',
             ' .  $cur_date_time . ',
             ' .  $cur_date_time . ',
             ' . (int)$data->position . ',
             ' . (int)$data->is_root_category . '
            )';

        $res=Db::getInstance()->execute($insert_op);
        if($res){
            $resultResponse = array(
                'result' => true
            );
        }else{
            $resultResponse = array(
                'result' => false
            );
        }

        return $resultResponse;
    }
}
