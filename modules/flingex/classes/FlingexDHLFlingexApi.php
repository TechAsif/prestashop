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

class FlingexDHLFlingexApi
{
    public static $supported_shipper_countries = array('DE' => array('api_versions' => array('3.1')));

    public $errors;
    public $warnings;
    public static $soap_client;
    public static $soap_client_pf;
    public static $soap_client_rp;
    public $user_token = false;
    public $module;
    public $api_version;

    public function __construct($module, $api_version = '3.1')
    {
        $this->module = $module;
        $this->setApiVersion($api_version);
    }

    public function setApiVersion($api_version)
    {
        if (in_array((string)$api_version, self::getSupportedApiVersions())) {
            $this->api_version = $api_version;
        } else {
            $this->api_version = '3.1';
        }
        return true;
    }

    public function setApiVersionByIdShop($id_shop)
    {
        return 3;
    }

    public function getApiVersion()
    {
        return $this->api_version;
    }

    public function getMajorApiVersion($api_version = '')
    {
        preg_match('/(?P<major>\d+).(?P<minor>\d+)/', ($api_version != '')?$api_version:$this->api_version, $matches);
        if (isset($matches['major'])) {
            return $matches['major'];
        }
        return false;
    }

    public static function getSupportedApiVersions($country_code = 'DE')
    {
        return self::$supported_shipper_countries[$country_code]['api_versions'];
    }

    public function getRequestDefaultParams()
    {
        list($majorRelease, $minorRelease) = explode('.', $this->api_version);
        return array(
            'Version' =>
                array(
                    'majorRelease' => $majorRelease,
                    'minorRelease' => $minorRelease
                )
        );
    }

    public function getShipperCountry($id_shop = null)
    {
        return  Configuration::get('DHLDP_DHL_COUNTRY', null, null, $id_shop);
    }

    public function getDHLRASenderAddress($id_address, $address_input = false, $id_shop = null)
    {
        if ($address_input == false) {
            $address = $this->normalizeAddressForRA(new Address((int)$id_address));
        } else {
            $address = array();

            $address['name1'] = $address_input['name1'];
            $address['name2'] = $address_input['name2'];
            $address['name3'] = $address_input['name3'];
            $address['streetName'] = $address_input['streetName'];
            $address['houseNumber'] = $address_input['houseNumber'];
            $address['postCode'] = $address_input['postCode'];
            $address['city'] = $address_input['city'];
            $address['country'] = array('countryISOCode' => $address_input['country']['countryISOCode']);
        }
        return $address;
    }

    public function normalizeAddressForRA(Address $address)
    {
        $country_and_state = Address::getCountryAndState($address->id);

        if ($country_and_state) {
            $country = new Country((int)$country_and_state['id_country']);
            $state_obj = new State((int)$country_and_state['id_state']);
            if (Validate::isLoadedObject($state_obj)) {
                $state = $state_obj->iso_code;
            } else {
                $state = '';
            }
            $customer = new Customer($address->id_customer);

            $res_address = array();
            if ($address->company != '') {
                $res_address['name1'] = $address->firstname.' '.$address->lastname;
                $res_address['name2'] = $address->company;
            } else {
                $res_address['name1'] = $address->firstname.' '.$address->lastname;
                $res_address['name2'] = '';
            }

            //$res_address['sendercareofname'] = $address->address2;
            //$res_address['sendercontactphone'] = ($address->phone_mobile != '')?$address->phone_mobile:$address->phone;
            //$res_address['sendercontactemail'] = $customer->email;
            $res_address['postCode'] = $address->postcode;
            $res_address['city'] = $address->city;

            $matches = array();
            preg_match(
                '/^(?P<streetname>[^\d]+) (?P<streetnumber>([ \/0-9-])+.?)$/',
                trim($address->address1),
                $matches
            );
            if (!count($matches)) {
                preg_match(
                    '/^(?P<streetnumber>[ \/0-9-]+.?) (?P<streetname>[^\d]+.?)$/',
                    trim($address->address1),
                    $matches
                );
                if (!count($matches)) {
                    preg_match(
                        '/(?P<streetnumber>[ \/0-9-]+.?) (?P<streetname>[^\d]+.?)/',
                        trim($address->address1),
                        $matches
                    );
                    if (!count($matches)) {
                        $street_name = $address->address1;
                        $street_number = '';
                    } else {
                        $street_name = trim($matches['streetname']);
                        $street_number = trim($matches['streetnumber']);
                    }
                } else {
                    $street_name = trim($matches['streetname']);
                    $street_number = trim($matches['streetnumber']);
                }
            } else {
                $street_name = trim($matches['streetname']);
                $street_number = trim($matches['streetnumber']);
            }
            $res_address['streetName'] = $street_name;
            $res_address['houseNumber'] = $street_number;
            $res_address['country'] = array('state' => $state, 'countryISOCode' => Tools::strtoupper($country->iso_code));

            return $res_address;
        }

        return false;
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
    

    public function getDHLDeliveryAddress($id_address, $address_input = false, $id_shop = null)
    {

        return false;
    }

    public function normalizeAddress(Address $address)
    {

        return false;
    }

    public function getSoapClient($mode, $dhl_ciguser = '', $dhl_cigpass = '')
    {
        return false;
    }

    public function getPFSoapClient($mode)
    {
        return false;
    }

    public function checkDHLAccount($dhl_mode, $username, $password)
    {
        $curlUrl = 'https://flingex.com/api/merchant/login?username='.$username.'&password='. $password;
        $formData = array();
        $headers = array();
        // $headers[] = 'Content-Type: application/x-www-form-urlencoded';

        $response = $this->phpCurlRequest($curlUrl, 'POST', $formData, $headers);

        Flingex::logToFile('Response',$response, 'account');

        return $response;
    }

    public function getVersion()
    {
        return false;
    }

    public function callDhlApi($function, $params, $id_shop = null)
    {
        return false;
    }

    public function callDhlRetoureApi($params, $id_shop = null)
    {
        $this->errors = array();
        $this->warnings = array();

        $mode = Configuration::get('DHLDP_DHL_MODE', null, null, $id_shop);
        $curl_handle = curl_init('url');

        $parameters_string = Tools::jsonEncode($params);

        $curlopt = array();
        $curlopt[CURLINFO_HEADER_OUT] = true;
        $curlopt[CURLINFO_PRIVATE] = true;
        $curlopt[CURLOPT_HEADER] = true;
        $curlopt[CURLOPT_RETURNTRANSFER] = true;
        $curlopt[CURLOPT_CUSTOMREQUEST] = "POST";
        $curlopt[CURLOPT_POSTFIELDS] = $parameters_string;

        $curlopt[CURLOPT_FOLLOWLOCATION] = true;
        $curlopt[CURLOPT_HTTPHEADER] = array(
            'Content-Length:'.Tools::strlen($curlopt[CURLOPT_POSTFIELDS]),
            'cache-control:nocache',
            'Connection:keep-alive',
            'accept_encoding:gzip, deflate',
            'Content-Type:application/json',
            'Accept:application/json',
            'Authorization:Basic '.base64_encode($mode?self::$dhl_live_ciguser['3.1'].':'.self::$dhl_live_cigpass['3.1']:self::$dhl_sbx_ciguser.':'.self::$dhl_sbx_cigpass),
            'DPDHL-User-Authentication-Token:'.$token
        );
        curl_setopt_array($curl_handle, $curlopt);

        $res = curl_exec($curl_handle);
        $header_size = curl_getinfo($curl_handle, CURLINFO_HEADER_SIZE);
        $http_code = curl_getinfo($curl_handle, CURLINFO_RESPONSE_CODE);
        //$header = substr($res, 0, $header_size);
        $body = Tools::substr($res, $header_size);
        $msg = "\nREQUEST:\n" . $parameters_string . "\n";
        $msg .= "\nRESPONSE:\n" . $res . "\n";
        Flingex::logToFile('DHL', $msg, 'dhl_api');

        return $this->getResponseRA($http_code, $body);
    }

    public function getResponseRA($http_code, $res)
    {
        if ($http_code == '201') {
            $this->errors[] = 'Validation failed';
        } elseif ($http_code == '400') {
            $this->errors[] = 'Bad Request';
        } elseif ($http_code == '401') {
            $this->errors[] = 'Authentication failed';
        } elseif ($http_code == '403') {
            $this->errors[] = 'Authorization failed';
        } elseif ($http_code == '500') {
            $this->errors[] = 'Body faulty';
        }

        $res = Tools::jsonDecode($res, true);
        if (isset($res['code'])) {
            $this->errors[] = $res['code'].': '.$res['detail'];
        }
        if (isset($res['statusCode'])) {
            $this->errors[] = $res['statusCode'].': '.$res['statusText'];
        }
        return $res;
    }

    public function getResponse($res)
    {
        if (isset($res->Status->statusCode)) {
            if ($res->Status->statusCode != '0') {
				if (isset($res->Status->statusText)) {
					$this->errors[] = $res->Status->statusText;
				}
				if (isset($res->Status->statusMessage)) {
					$this->errors[] = $res->Status->statusMessage;
				}
				if (isset($res->CreationState->LabelData->Status->statusCode)) {
					if ($res->CreationState->LabelData->Status->statusCode != '0' && isset($res->CreationState->LabelData->Status->statusMessage)) {
						if (is_array($res->CreationState->LabelData->Status->statusMessage)) {
							foreach ($res->CreationState->LabelData->Status->statusMessage as $message) {
								$this->warnings[] = $message;
							}
						} else {
							$this->warnings[] = $res->CreationState->LabelData->Status->statusMessage;
						}
					}
				}
                return false;
            } else {
                if (isset($res->CreationState->LabelData->Status->statusCode)) {
                    if ($res->CreationState->LabelData->Status->statusCode == '0') {
                        if (is_array($res->CreationState->LabelData->Status->statusMessage)) {
                            foreach ($res->CreationState->LabelData->Status->statusMessage as $message) {
                                $this->warnings[] = $message;
                            }
                        } else {
                            $this->warnings[] = $res->CreationState->LabelData->Status->statusMessage;
                        }
                    }
                    $ret = array('shipmentNumber' => isset($res->CreationState->LabelData->shipmentNumber)?$res->CreationState->LabelData->shipmentNumber:(isset($res->CreationState->shipmentNumber)?$res->CreationState->shipmentNumber:''), 'labelUrl' => $res->CreationState->LabelData->labelUrl);
                    if (isset($res->CreationState->LabelData->exportLabelUrl)) {
                        $ret['exportLabelUrl'] = $res->CreationState->LabelData->exportLabelUrl;
                    }
                    if (isset($res->CreationState->LabelData->codLabelUrl)) {
                        $ret['codLabelUrl'] = $res->CreationState->LabelData->codLabelUrl;
                    }
                    if (isset($res->CreationState->LabelData->returnLabelUrl)) {
                        $ret['returnLabelUrl'] = $res->CreationState->LabelData->returnLabelUrl;
                    }
                    return $ret;
                } elseif (isset($res->DeletionState)) {
                    return array('shipmentNumber' => $res->DeletionState->shipmentNumber);
                } elseif (isset($res->ManifestState)) {
                    return $res->ManifestState;
                }
            }
        } else {
            return false;
        }
        return $res;
    }

    public function callPFApi($function, $params, $id_shop = null)
    {
        $this->errors = array();
        // try {
        //     $mode = Configuration::get('DHLDP_DHL_MODE', null, null, $id_shop);

        //     return $res;
        // } catch ($e) {
        // }
        
        $msg = "\n-----------------------------------------";
        Flingex::logToFile('DHL', $msg, 'dhl_api');
        $this->errors[] = $this->module->getTranslationPFApiMessage($e->getMessage());

        return false;
    }

    public function getDefinedProducts($code = '', $to_country = '', $from_country = '', $api_version = '')
    {
        $products = array();

        return $products;
    }

    public function getConfiguredDHLProducts()
    {
        return array();
    }
}
