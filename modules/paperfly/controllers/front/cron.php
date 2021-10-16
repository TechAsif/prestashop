<?php
// url: http://localhost/ps174/index.php?fc=module&module=paperfly&controller=cron
// require_once dirname(__FILE__) . '/../../index.php';
class  paperflycronModuleFrontController extends ModuleFrontControllerCore
{
    public function initContent()
    {
        // var_dump($this);
        // $this->module->defineSettings();
        $token = Tools::getValue('token');
        // if ($token == $this->module->getCronToken()) {
//            $id_shop = (int)Tools::getValue('id_shop');
            $action = pSQL(Tools::getValue('action'));
            $total_indexed = (int)Tools::getValue('total_indexed');
            $time = pSQL(Tools::getValue('time', microtime(true)));
            if (Tools::getValue('complete')) {
                echo 'Total products indexed: '.$total_indexed;
                echo '<br>';
                echo 'Processing time: '.Tools::ps_round((microtime(true) - $time), 2).' seconds';
            } else {
//                $this->indexProducts($action,  $total_indexed, $time);
                $this->indexOrders($action, $total_indexed,$time);
            }
        // }
        exit();
    }


    private function indexOrders($action, $total_indexed, $time)
    {


        $order_query = Db::getInstance()->executeS(
            'SELECT po.reference
            FROM '._DB_PREFIX_.'paperfly_order po 
            LEFT JOIN '._DB_PREFIX_.'paperfly_order_tracking pot ON (po.id_paperfly_order=pot.id_paperfly_order)group by po.reference'
        );


        foreach ($order_query as $key=>$value){
            $thi_ref=$value['reference'];

            $_the_sql = 'DELETE FROM `'._DB_PREFIX_.'paperfly_order_tracking` WHERE `reference` = "'.$thi_ref.'"';

            Db::getInstance()->execute($_the_sql);

            $_SQL = 'SELECT po.reference, po.id_paperfly_order, po.tracking_number FROM '._DB_PREFIX_.'paperfly_order po where `reference` = "'.$thi_ref.'"';

            $query = Db::getInstance()->executeS($_SQL)[0];

            $tracking_api_response = self::sentToPaperflyOrderTrackingApi($thi_ref);
            $tracking_response_data = (json_decode($tracking_api_response)->response_code == '200') ? json_decode($tracking_api_response)->success->trackingStatus : '';
            $tracking_api_response_code = json_decode($tracking_api_response)->response_code;
            $tracking_api_response_message = ($tracking_api_response_code == '200') ? 
                json_decode($tracking_api_response)->success->message
                : json_decode($tracking_api_response)->error->message;

            foreach ((array)$tracking_response_data[0] as $key => $value) {
                $this_key = $key;
                $this_val = $value;
                $sts =  '';
                $sql_tracking = 'INSERT INTO ' . _DB_PREFIX_ . 'paperfly_order_tracking
            (`reference`, `id_paperfly_order`, `tracking_number`,`tracking_event_key`,`tracking_event_value`,
            `api_response_status_code`,`api_response_status_message`)
            values(
             "' . $thi_ref . '",
             ' . $query['id_paperfly_order'] . ',
             "' . $query['tracking_number'] . '",
             "' . $this_key . '",
             "' . $this_val . '",
             "' . $tracking_api_response_code . '",
             "' . $tracking_api_response_message . '"
            )';
            
                Db::getInstance()->execute($sql_tracking);
            }
        }

//        $url = $this->module->getCronURL($id_shop, $params);
//        Tools::redirect($url);
    }

    /*******sent to shipping via paperfly***********/

    public function sentToPaperflyOrderTrackingApi($ref_number){
        $post_data = new stdClass();
        $post_data->ReferenceNumber = $ref_number;
        $post_data_obj = json_encode($post_data);

        $apiJsonResponse = self::callPaperFlyAPI("POST","https://sandbox.paperflybd.com/API-Order-Tracking",$post_data_obj,'Paperfly_~La?Rj73FcLm');
        return $apiJsonResponse;

    }

    /********API call response********/

    public function callPaperFlyAPI($method, $url, $data = false,$headers = false)
    {
        $curl = curl_init();
        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "c116552:1234");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'paperflykey: Paperfly_~La?Rj73FcLm',
            'Authorization: Basic YzExNjU1MjoxMjM0',
            'Content-Type: application/json'
        ));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
}