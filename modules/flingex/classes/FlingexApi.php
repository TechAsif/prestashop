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

class FlingexApi
{
    public $errors;
    public $warnings;
    public $user_token = false;
    public $module;
    public $api_version;
    
    public static $conf_prefix = 'FLINGEX_';

    public function __construct($module, $api_version = '3.1')
    {
        $this->module = $module;
    }
    
    public function phpCurlRequest($curlUrl, $method, $data=array(),$headers=array()) {
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
    


    public function checkAccount($username, $password)
    {
        $curlUrl = 'https://flingex.com/api/merchant/login?username='.$username.'&password='. $password;
        $response = $this->phpCurlRequest($curlUrl, 'POST');

        Flingex::logToFile('Response',$response, 'account');

        return $response;
    }


    public function callPFApi($function, $params, $id_shop = null)
    {
        $this->errors = array();
        $msg = "\n-----------------------------------------";
        Flingex::logToFile('DHL', $msg, 'dhl_api');
        return false;
    }

    
    /*******sent to shipping via flingex***********/

    public function sentOrderToFlingex($order) {

        $token = Configuration::get(self::$conf_prefix.'LIVE_TOKEN');

        $delivery_address_id=$order->id_address_delivery;
        $address= Db::getInstance()->executeS(
            'SELECT *
            FROM `'._DB_PREFIX_.'address`
            WHERE id_address ='.$delivery_address_id
        );
        $bestService = $this->getBestServiceType($address);
        $reciveZone = $this->getReciveZone($address);

        if(count($address)) {
            
            $post_data = [
                'token' => $token,
                'choose_service_type_id' => $bestService ? $bestService['id'] : 1,
                'reciveZone' => $reciveZone ? $reciveZone['id'] : '7', // default sub-dhaka
                'cod' => ($order->total_paid + (int)$bestService['deliverycharge']), // collected amount of price
                'name' => $address[0]["firstname"] .' '. $address[0]["lastname"], // customer name
                'weight' => 1,
                'address' => $address[0]["address1"]. $address[0]["address2"],
                'phonenumber' => $address[0]["phone"],
                'invoiceNo' => $order->reference,
                'note' => '',
            ];
    
            $curlUrl = 'https://flingex.com/api/merchant/create?'. http_build_query($post_data);

            $apiJsonResponse = $this->phpCurlRequest($curlUrl, 'POST');
    
            return json_decode($apiJsonResponse, true);
        }
        return ["status"=> "error","msg"=> "Address Not Found"];

    }


    public function getBestServiceType($address)
    {
        $disticts = $this->getBDDistricts();
             
        $token = Configuration::get(self::$conf_prefix.'LIVE_TOKEN');
        $curlUrl = 'https://flingex.com/api/merchant/choose-service';
        $formData = array('token' => $token);
        $apiJsonResponse = json_decode($this->phpCurlRequest($curlUrl, 'GET', $formData), true);

        if($apiJsonResponse['code'] != 200)
            return false;

        $services = isset($apiJsonResponse['data']['pricing']) ? $apiJsonResponse['data']['pricing']: [['deliverycharge'=> 0]];

        usort($services, function($a, $b) {
            return $a['deliverycharge'] > $b['deliverycharge'];
        });
        $bestService = null;
        $userAddress = $address[0];
        $marchantAddress = 'Dhaka'; // initally let consider all vendor marchandizer address inside dhaka


        foreach ($disticts as $key => $distict) {
            foreach ($services as $service_key => $service) {
                if(
                    preg_match("/inside-dhaka/i", $service['slug'])
                    && preg_match("/dhaka/i", $userAddress['city'])
                    && preg_match("/dhaka/i", strtolower($marchantAddress))
                ) {
                    $bestService = $service;
                    break;break;
                } else if( 
                    preg_match("/own.*city/i", $service['slug'])
                    && strtolower($userAddress['city'])==strtolower($distict) 
                    && strtolower($userAddress['city'])==strtolower($marchantAddress) 
                ) {
                    $bestService = $service;
                    break;break;
                } else if(
                    preg_match("/sub.*dhaka/i", $service['slug'])
                    && preg_match("/Savar|Gazipur|Kamrangirchar/i", $userAddress['city'])
                    && preg_match("/Savar|Gazipur|Kamrangirchar/i", strtolower($marchantAddress))
                ) {
                    $bestService = $service;
                    break;break;
                }
                
            }
        }
        if($bestService != null) {
            return $bestService;
        } else {
            foreach ($services as $service_key => $service) {
                if (preg_match("/outside.*dhaka/i", $service['slug']))
                    return $service;
                
            }
            return null;
        }
    }

    public function getReciveZone($address)
    {
        $disticts = $this->getBDDistricts();
             
        $token = Configuration::get(self::$conf_prefix.'LIVE_TOKEN');
        $curlUrl = 'https://flingex.com/api/merchant/zone';
        $formData = array('token' => $token);
        $apiJsonResponse = json_decode($this->phpCurlRequest($curlUrl, 'GET', $formData), true);

        if($apiJsonResponse['code'] != 200)
            return false;

        $zones = isset($apiJsonResponse['data']['nearestzones']) ? $apiJsonResponse['data']['nearestzones']: [['id'=> 6]];

        $nearestZone = null;
        $userAddress = $address[0];
        $addKeywords = preg_split("/[\s,]+/", $userAddress['address1'].$userAddress['address2']);

        foreach ($zones as $zone_key => $zone) {
            foreach ($addKeywords as $addKeyword) {
                if( strtolower($zone['zonename']) == strtolower($addKeyword)) {
                    $nearestZone = $zone;
                    break;break;
                }
                if(
                    preg_match("/sub.*dhaka/i", $zone['zonename'])
                    && preg_match("/Savar|Gazipur|Kamrangirchar/i", $userAddress['city'])
                ) {
                    $nearestZone = $zone;
                    break;break;
                }
            }
            
        }
    
        if($nearestZone != null) {
            return $nearestZone;
        } else {
            foreach ($zones as $zone_key => $zone) {
                if (preg_match("/outside.*dhaka/i", $zone['zonename']))
                    return $zone;
                
            }
            return null;
        }
    }

    /*******sent to shipping via flingex***********/

    public function sentOrderToFlingexTrackingApi($tracking_id){

        if( !$tracking_id )
            return ["status"=> "error","msg"=> "Tracking ID not found"]; 

        $token = Configuration::get(self::$conf_prefix.'LIVE_TOKEN');

        $post_data = ['token' => $token];
        $curlUrl = 'https://flingex.com/api/merchant/parcel/track/'.$tracking_id.'?'. http_build_query($post_data);
        $trackingResponse = $this->phpCurlRequest($curlUrl, 'GET');

        return json_decode($trackingResponse, true);

    }


    // http://localhost/ps174/modules/flingex/cron.php?token=f4247629b6&return_message=1&run=1
    public function cronProcess($value, $time)
    {

        $time = pSQL(Tools::getValue('time', microtime(true)));

        $order_query = Db::getInstance()->executeS(
            'SELECT fo.reference,fo.id_flingex_order,fo.tracking_number,fo.id_order
            FROM '._DB_PREFIX_.'flingex_order fo 
            left JOIN '._DB_PREFIX_.'flingex_order_tracking fot 
            ON (fo.id_flingex_order=fot.id_flingex_order)group by fo.reference'
        );

        foreach ($order_query as $key=>$flingex_order){

            $del_sql='DELETE FROM `'._DB_PREFIX_.'flingex_order_tracking`
                where `reference` = "'.$flingex_order['reference'].'"';

            $del_res=Db::getInstance()->execute($del_sql);
            
            $tracking_response = $this->sentOrderToFlingexTrackingApi($flingex_order["tracking_number"]);

            $tracking_response_data = (isset($tracking_response['data']) && isset($tracking_response['data']['trackInfos']) && $tracking_response['data']['trackInfos']) ? $tracking_response['data']['trackInfos'] : [];

            foreach ($tracking_response_data as $key => $value) {
                $sql_tracking = 'INSERT INTO ' . _DB_PREFIX_ . 'flingex_order_tracking
                (`id_order`,`reference`, `id_flingex_order`, `tracking_number`,`parcel_status`,
                `api_response_status`,`api_response_message`)
                values(
                ' . (int)$flingex_order['id_order'] . ',
                "' . $flingex_order['reference'] . '",
                ' . (int)$flingex_order['id_flingex_order'] . ',
                "' . $flingex_order['tracking_number'] . '",
                "' . $value['parcelStatus'] . '",
                "' . $tracking_response['code']. '",
                "' . $tracking_response['msg'] . '"
                )';
                $res=Db::getInstance()->execute($sql_tracking);
            }
        }

        if($res){
            echo 'job complete processing time: '.Tools::ps_round((microtime(true) - $time), 2).' seconds';
        }else{
            echo 'something is wrong';
        }
    }

    public function getBDDistricts()
    {
        return ["Bagerhat","Bandarban","Barguna","Barisal","Bhola","Bogra","Brahmanbaria","Chandpur","Chapainawabganj","Chittagong","Chuadanga","Comilla","Cox's Bazar","Dhaka","Dinajpur","Faridpur","Feni","Gaibandha","Gazipur","Gopalganj","Habiganj","Jamalpur","Jessore","Jhalokati","Jhenaidah","Joypurhat","Khagrachhari","Khulna","Kishoreganj","Kurigram","Kushtia","Lakshmipur","Lalmonirhat","Madaripur","Magura","Manikganj","Meherpur","Moulvibazar","Munshiganj","Mymensingh","Naogaon","Narail","Narayanganj","Narsingdi","Natore","Netrokona","Nilphamari","Noakhali","Pabna","Panchagarh","Patuakhali","Pirojpur","Rajbari","Rajshahi","Rangamati","Rangpur","Satkhira","Shariatpur","Sherpur","Sirajganj","Sunamganj","Sylhet","Tangail","Thakurgaon"];
    }

}
