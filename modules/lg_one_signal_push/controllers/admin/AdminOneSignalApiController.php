<?php

class AdminOneSignalApiController extends ModuleAdminController
{
    protected static $auth_key = 'ZGFmMmFjNzQtYmU5MC00OGQyLTk2ZmItZDBlM2ZmOTJiYTM3';
    protected static $rest_api_key = 'ZGFmMmFjNzQtYmU5MC00OGQyLTk2ZmItZDBlM2ZmOTJiYTM3';
    protected static $app_id = '2f5d659c-cdff-4fb1-a44d-dfd6d85d4adc';
    protected static $dummy_include_player_ids = ["4dc5dec2-0f5c-419b-a5c7-46683508323d"];

   public static function sendMessage($id,$content,$channel,$mood) {
       $content      = array(
           "en" => $content
       );

    if($mood== 'DUMMY'){
        $fields = array(
            'app_id' => self::$app_id,
            'include_player_ids' => self::$dummy_include_player_ids,
            'contents' => $content,
            'data' => array(
                "data" => $content
            ),
        );
    }else{
       $fields = array(
           'app_id' => self::$app_id,
           'included_segments' => array('Active Users'),
           'contents' => $content,
           'data' => array(
               "data" => $content
           ),
       );
    }

       $fields = json_encode($fields);
       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
       curl_setopt($ch, CURLOPT_HTTPHEADER, array(
           'Content-Type: application/json; charset=utf-8',
           'Authorization: Basic YjhmM2JjMDUtZGI0Ni00MjJkLWExOWEtYzZhNjhkOTlkOTNh',
       ));
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
       curl_setopt($ch, CURLOPT_HEADER, FALSE);
       curl_setopt($ch, CURLOPT_POST, TRUE);
       curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

       $response = curl_exec($ch);
       self::update_notification($id,$response);
       curl_close($ch);
       return $response;
    }

    public static function update_notification($id,$response){
        $db_table_name = 'one_signal_push_notify';
        $push_response = "'" . $response . "'";
        $query = 'update  ' . _DB_PREFIX_ . bqSQL($db_table_name) . '
                    set `api_response`='.$push_response.'
                        where `id_push`='.$id.'';
    
        Db::getInstance()->execute($query);
    }
}
