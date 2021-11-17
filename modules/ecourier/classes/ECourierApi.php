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

class ECourierApi
{
    public $errors;
    public $warnings;
    public $user_token = false;
    public $module;
    public $base_url;
    public $request_header;
    
    public static $conf_prefix = 'ECOURIER_';

    public function __construct($module)
    {
        $this->module = $module;
        
        $USER_ID = Configuration::get(self::$conf_prefix . 'USER_ID');
        $API_KEY = Configuration::get(self::$conf_prefix . 'API_KEY');
        $API_SECRET = Configuration::get(self::$conf_prefix . 'API_SECRET');

        $this->request_header = [
            'USER-ID: '.$USER_ID,
            'API-KEY: '.$API_KEY,
            'API-SECRET: '.$API_SECRET,
            'Content-Type: application/json'
        ];

        $mode = Configuration::get(self::$conf_prefix.'SANDBOX'); // sandbox 1 = live
        $this->base_url  = (int)$mode == 1  ? 'https://backoffice.ecourier.com.bd/api/' : 'https://staging.ecourier.com.bd/api/';
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
    
    
    /*******sent to shipping via ecourier***********/

    public function sentOrderToECourier($params) {


        $order = $params['order'];
        $products = $params['cart']->getProducts();

        $delivery_address_id=$order->id_address_delivery;
        $address= Db::getInstance()->executeS(
            'SELECT *
            FROM `'._DB_PREFIX_.'address`
            WHERE id_address ='.$delivery_address_id
        );

        $bestPackage = $this->getBestPackage($address);
        
        if(count($address)) {
            
            $post_data = [                
                "package_code"=> isset($bestPackage['package_code']) ? $bestPackage['package_code']: "#2416", // mandatory
                "product_id"=> $products[0]['id_product'],
                "ep_id"=> "32132212", // mandatory // Unique eCommerce Partner ID
                "ep_name"=> "Trank", // eCommerce Partner (EP) Name
                "pick_contact_person"=> "Mahbub",
                "pick_division"=> "Dhaka",
                "pick_district"=> "Dhaka", // mandatory
                "pick_thana"=> "Badda", // mandatory
                "pick_hub"=> "18490", // mandatory
                "pick_union"=> "1212", // mandatory
                "pick_address"=> "House no 7, Road 5, middle badda, dhaka", // mandatory
                "pick_mobile"=> "01738457162", // mandatory
                "recipient_name"=> $address[0]["firstname"] .' '. $address[0]["lastname"], // mandatory
                "recipient_mobile"=> $address[0]["phone"], // mandatory
                "recipient_division"=> "Dhaka",
                "recipient_district"=> "Dhaka",
                "recipient_city"=> $address[0]["city"], // mandatory
                "recipient_area"=> "A K Khan", // mandatory
                "recipient_thana"=> "Babuganj", // mandatory
                "recipient_union"=> "8216", // mandatory
                "recipient_address"=> $address[0]["address1"]. $address[0]["address2"], // mandatory
                "parcel_detail"=> "parcel detail",
                "number_of_item"=> count($products),
                "product_price"=> $order->total_paid_tax_incl, // mandatory
                "payment_method"=> "COD", // $order->payment, // mandatory
                "actual_product_price"=> $order->total_paid,
                "pgwid"=> 8888, //Payment gateway ID. eCourier will share with merchant
                "pgwtxn_id"=>"asdasdsad" // Payment gateway transaction ID
            ];

            $curlUrl = $this->base_url. 'order-place-reseller';

            $apiJsonResponse = $this->phpCurlRequest($curlUrl, 'POST', json_encode($post_data), $this->request_header);
    
            return json_decode($apiJsonResponse, true);
        }
        return ["status"=> "error","msg"=> "Address Not Found"];

    }


    public function getBestPackage($address)
    {
        $disticts = $this->getBDDistricts();

        $curlUrl = $this->base_url. 'packages';
        $packages = json_decode($this->phpCurlRequest($curlUrl, 'POST', [], $this->request_header), true);

        if(!isset($package['coverage']))
            return null;

        usort($packages, function($a, $b) {
            return $a['shipping_charge'] > $b['shipping_charge'];
        });
        $bestPackage = null;
        $userAddress = $address[0];
        $marchantAddress = 'Dhaka'; // initally let consider all vendor marchandizer address inside dhaka


        foreach ($disticts as $key => $distict) {
            foreach ($packages as $package_key => $package) {
                if(
                    preg_match("/inside.*dhaka/i", $package['coverage'])
                    && preg_match("/dhaka/i", $userAddress['city'])
                    && preg_match("/dhaka/i", strtolower($marchantAddress))
                ) {
                    $bestPackage = $package;
                    break;break;
                } else if(
                    preg_match("/sub.*dhaka/i", $package['coverage'])
                    && preg_match("/Savar|Gazipur|Kamrangirchar/i", $userAddress['city'])
                    && preg_match("/Savar|Gazipur|Kamrangirchar/i", strtolower($marchantAddress))
                ) {
                    $bestPackage = $package;
                    break;break;
                }
                
            }
        }
        if($bestPackage != null) {
            return $bestPackage;
        } else {
            foreach ($packages as $package_key => $package) {
                if (preg_match("/outside.*dhaka/i", $package['coverage']))
                    return $package;
                
            }
            return null;
        }
    }
    
    /*******sent to shipping via ecourier***********/

    public function sentOrderToECourierTrackingApi($tracking_id){

        if( !$tracking_id )
            return ["status"=> "error","msg"=> "Tracking ID not found"]; 
        
        $curlUrl = $this->base_url. 'track';
        $post_data = [
            'ecr' => $tracking_id // eCourier ID
        ];

        $trackingResponse = $this->phpCurlRequest($curlUrl, 'POST', json_encode($post_data), $this->request_header);

        return json_decode($trackingResponse, true);

    }


    // http://localhost/ps174/modules/ecourier/cron.php?token=f4247629b6&return_message=1&run=1
    public function cronProcess($value, $time)
    {

        $time = pSQL(Tools::getValue('time', microtime(true)));

        $order_query = Db::getInstance()->executeS(
            'SELECT fo.reference,fo.id_ecourier_order,fo.tracking_number,fo.id_order
            FROM '._DB_PREFIX_.'ecourier_order fo 
            left JOIN '._DB_PREFIX_.'ecourier_order_tracking fot 
            ON (fo.id_ecourier_order=fot.id_ecourier_order)group by fo.reference'
        );

        foreach ($order_query as $key=>$ecourier_order){

            $del_sql='DELETE FROM `'._DB_PREFIX_.'ecourier_order_tracking`
                where `reference` = "'.$ecourier_order['reference'].'"';

            $del_res=Db::getInstance()->execute($del_sql);
            
            $tracking_response = $this->sentOrderToECourierTrackingApi($ecourier_order["tracking_number"]);

            $tracking_response_data = (isset($tracking_response['data']) && isset($tracking_response['data']['trackInfos']) && $tracking_response['data']['trackInfos']) ? $tracking_response['data']['trackInfos'] : [];

            foreach ($tracking_response_data as $key => $value) {
                $sql_tracking = 'INSERT INTO ' . _DB_PREFIX_ . 'ecourier_order_tracking
                (`id_order`,`reference`, `id_ecourier_order`, `tracking_number`,`parcel_status`,
                `api_response_status`,`api_response_message`)
                values(
                ' . (int)$ecourier_order['id_order'] . ',
                "' . $ecourier_order['reference'] . '",
                ' . (int)$ecourier_order['id_ecourier_order'] . ',
                "' . $ecourier_order['tracking_number'] . '",
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
