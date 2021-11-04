<?php
/**
 * DHL Deutschepost
 *
 * @author    silbersaiten <info@silbersaiten.de>
 * @copyright 2021 silbersaiten
 * @license   See joined file licence.txt
 * @category  Module
 * @support   silbersaiten <support@silbersaiten.de>
 * @version   1.0.6
 * @link      http://www.silbersaiten.de
 */

class PaperflyAPI
{
    public $errors;
    public $warnings;
    public $user_token = false;
    public $module;
    public $api_version;
    
    public static $conf_prefix = 'PAPERFLY_';

    public function __construct($module, $api_version = '3.1')
    {
        $this->module = $module;
    }
    
    public function phpCurlRequest($curlUrl, $method, $data,$headers) {
        $req = '';
        $curl = curl_init();
    
        switch ($method){
            case "POST":
                
                if ($data) {
                    if (is_array($data)) {
    
                        foreach ($data as $key => $value) {
                            $value = (stripslashes($value));
                            // $value = urlencode(stripslashes($value));
                            $req .= "&$key=$value";
                        }
                        $req =  substr($req, 1);
                    } else 
                        $req = $data;
                }
                curl_setopt($curl, CURLOPT_POSTFIELDS, $req);
                curl_setopt($curl, CURLOPT_POST, 1);
    
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                if ($data)
                    $curlUrl = sprintf("%s?%s", $curlUrl, http_build_query($data));
        }
        curl_setopt($curl, CURLOPT_URL, $curlUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    
        $res = curl_exec($curl);
    
        if (!$res) {
            $errno = curl_errno($curl);
            $errstr = curl_error($curl);
            curl_close($curl);
            throw new Exception("cURL error: [$errno] $errstr");
        }
    
        $info = curl_getinfo($curl);
    
        // Check the http response
        $httpCode = $info['http_code'];
        if ($httpCode >= 200 && $httpCode < 300) {
            curl_close($curl);
            return $res;
        } else {
           return $httpCode;
        }
    }

    public function sentToPaperFlyOrder($order, $cart){

        $products = $cart->getProducts(true);

        $delivery_address_id=$order->id_address_delivery;
        $address= Db::getInstance()->executeS(
            'SELECT *
            FROM `'._DB_PREFIX_.'address`
            WHERE id_address ='.$delivery_address_id
        );

        $post_data = new stdClass();
        $post_data->merOrderRef = $order->reference;
        $post_data->pickMerchantName = "Rokan";
        $post_data->pickMerchantAddress = "Dhanmondi";
        $post_data->pickMerchantThana = "Dhanmondi";
        $post_data->pickMerchantDistrict = "Dhaka";
        $post_data->pickupMerchantPhone = "01829331461";
        $post_data->productSizeWeight = "standard";
        $post_data->productBrief = $products[0]['name'];
        $post_data->packagePrice = $order->total_paid;
        $post_data->deliveryOption = "regular";
        $post_data->custname = $address[0]['firstname'].' '.$address[0]['lastname'];
        $post_data->custaddress = $address[0]["address1"]. $address[0]["address2"];
        $post_data->customerThana = 'Badda';// $address[0]['city'];
        $post_data->customerDistrict = $address[0]['city'];
        $post_data->custPhone =$address[0]['phone'];
        $post_data->max_weight = "10";
        $post_data_obj = json_encode($post_data);

        $mode = Configuration::get(self::$conf_prefix.'SANDBOX');
        $url = (int)$mode == 1  ? 'https://paperflybd.com/OrderPlacement' : 'https://sandbox.paperflybd.com/OrderPlacement';
        $apiJsonResponse = self::callPaperFlyAPI("POST",$url,$post_data_obj);
        return json_decode($apiJsonResponse, true);

    }

    /*******sent to shipping via paperfly***********/

    public function sentToPaperflyOrderTrackingApi($order){

        $post_data = new stdClass();
        $post_data->ReferenceNumber = $order->reference;
        $post_data_obj = json_encode($post_data);
        
        $mode = Configuration::get(self::$conf_prefix.'SANDBOX');
        $url = (int)$mode == 1  ? 'https://paperflybd.com/API-Order-Tracking' : 'https://sandbox.paperflybd.com/API-Order-Tracking';

        $apiJsonResponse = self::callPaperFlyAPI("POST",$url ,$post_data_obj);
        return json_decode($apiJsonResponse, true);

    }

    /*******sent to shipping via paperfly***********/

    public static function paperflyOrderTrackingApiCronProcess($order){
        $post_data = new stdClass();
        $post_data->ReferenceNumber = $order;
        $post_data_obj = json_encode($post_data);
        $mode = Configuration::get(self::$conf_prefix.'SANDBOX');
        $url = (int)$mode == 1  ? 'https://paperflybd.com/API-Order-Tracking' : 'https://sandbox.paperflybd.com/API-Order-Tracking';
        $apiJsonResponse = self::callPaperFlyAPI("POST",$url,$post_data_obj);
        return json_decode($apiJsonResponse,true);

    }

    public static function callPaperFlyAPI($method, $url, $data = [])
    {
        
        $user = Configuration::get(self::$conf_prefix.'LIVE_USER');
        $password = Configuration::get(self::$conf_prefix.'LIVE_PWD');

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
        curl_setopt($curl, CURLOPT_USERPWD, $user.":".$password);
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

    // http://localhost/ps174/modules/paperfly/cron.php?token=f4247629b6&return_message=1&run=1
    public static function cronProcess($value, $time)
    {
        $res = null;
        $time = pSQL(Tools::getValue('time', microtime(true)));

        $order_sql = 'SELECT po.reference,po.id_paperfly_order,po.tracking_number,po.id_order
        FROM '._DB_PREFIX_.'paperfly_order po 
        left JOIN '._DB_PREFIX_.'paperfly_order_tracking pot 
        ON (po.id_paperfly_order=pot.id_paperfly_order)group by po.reference';


        $order_query = Db::getInstance()->executeS($order_sql);

        foreach ($order_query as $key=>$paperfly_order){

            $del_sql='DELETE FROM `'._DB_PREFIX_.'paperfly_order_tracking`
                        where `reference` = "'.$paperfly_order['reference'].'"';
            $del_res=Db::getInstance()->execute($del_sql);
            
            $tracking_api_response = self::paperflyOrderTrackingApiCronProcess($paperfly_order['reference']);
            $tracking_response_data = [];
            if( $tracking_api_response['response_code'] == '200') {
                if(isset( $tracking_api_response['success']['trackingStatus'] ))
                    $tracking_response_data = $tracking_api_response['success']['trackingStatus'][0];
            }
            $tracking_api_response_message = ($tracking_api_response['response_code'] == '200') ? 
                $tracking_api_response['success']['message']
                : $tracking_api_response['error']['message'];
            
            foreach ((array)$tracking_response_data as $key => $value) {
                $sql_tracking = 'INSERT INTO ' . _DB_PREFIX_ . 'paperfly_order_tracking
            (`id_order`,`reference`, `id_paperfly_order`, `tracking_number`,`tracking_event_key`,`tracking_event_value`,
            `api_response_status_code`,`api_response_status_message`)
            values(
             ' . (int)$paperfly_order['id_order'] . ',
             "' . $paperfly_order['reference'] . '",
             ' . $paperfly_order['id_paperfly_order'] . ',
             "' . $paperfly_order['tracking_number']  . '",
             "' . $key . '",
             "' . $value . '",
             "' . $tracking_api_response['response_code'] . '",
             "' . $tracking_api_response_message . '"
            )';
              $res=Db::getInstance()->execute($sql_tracking);
            }

        }
        if($res){
            echo 'job complete '.'processing time: '.Tools::ps_round((microtime(true) - $time), 2).' seconds';
        }else{
            echo 'No order found on paperfly';
        }
    }

    public function getBDDistricts()
    {
        return ["Bagerhat","Bandarban","Barguna","Barisal","Bhola","Bogra","Brahmanbaria","Chandpur","Chapainawabganj","Chittagong","Chuadanga","Comilla","Cox's Bazar","Dhaka","Dinajpur","Faridpur","Feni","Gaibandha","Gazipur","Gopalganj","Habiganj","Jamalpur","Jessore","Jhalokati","Jhenaidah","Joypurhat","Khagrachhari","Khulna","Kishoreganj","Kurigram","Kushtia","Lakshmipur","Lalmonirhat","Madaripur","Magura","Manikganj","Meherpur","Moulvibazar","Munshiganj","Mymensingh","Naogaon","Narail","Narayanganj","Narsingdi","Natore","Netrokona","Nilphamari","Noakhali","Pabna","Panchagarh","Patuakhali","Pirojpur","Rajbari","Rajshahi","Rangamati","Rangpur","Satkhira","Shariatpur","Sherpur","Sirajganj","Sunamganj","Sylhet","Tangail","Thakurgaon"];
    }

}
