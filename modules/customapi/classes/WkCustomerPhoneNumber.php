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

class WkCustomerPhoneNumber extends ObjectModel
{
    public $id_customer;
    public $id_country;
    public $phone;
    public $temp_email;
    public $verified;
    public $otp;
    public $last_otp_gen;
    public $otp_gen_count;
    public $last_wrong_otp_attempt;
    public $wrong_otp_count;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'wk_customer_phone_number',
        'primary' => 'id_customer_phone',
        'fields' => array(
            'id_customer' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => false
            ),
            'id_country' => array(
                'type' => self::TYPE_INT,
                'required' => false,
                'validate' => 'isUnsignedId'
            ),
            'phone' => array(
                'type' => self::TYPE_STRING,
                'required' => false,
                'validate' => 'isPhoneNumber',
                'size' => 20
            ),
            'temp_email' => array(
                'type' => self::TYPE_STRING,
                'required' => false,
                'validate' => 'isEmail',
                'size' => 128
            ),
            'verified' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool'
            ),
            'otp' => array(
                'type' => self::TYPE_STRING,
                'copy_post' => false,
            ),
            'last_otp_gen' => array(
                'type' => self::TYPE_STRING,
                'copy_post' => false,
            ),
            'otp_gen_count' => array(
                'type' => self::TYPE_INT,
                'required' => false,
                'validate' => 'isUnsignedId'
            ),
            'last_wrong_otp_attempt' => array(
                'type' => self::TYPE_STRING,
                'copy_post' => false,
            ),
            'wrong_otp_count' => array(
                'type' => self::TYPE_INT,
                'required' => false,
                'validate' => 'isUnsignedId'
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate'
            ),
            'date_upd' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate'
            ),
        ),
    );

    public static function getIdByCustomerId($idCustomer)
    {
        return  Db::getInstance()->getValue(
            'SELECT `id_customer_phone` FROM `'._DB_PREFIX_.'wk_customer_phone_number`
            WHERE `id_customer` = '.(int) $idCustomer
        );
    }

    public static function getPhoneDetailsByIdCustomer($idCustomer)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_customer_phone_number` WHERE `id_customer` = '. (int) $idCustomer
        );
    }

    public static function getDetailsByPhoneNumber($phoneNumber, $idCountry = false)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `'._DB_PREFIX_.'wk_customer_phone_number`
            WHERE `phone` = '. pSQL($phoneNumber).($idCountry ? (' AND `id_country` = '. (int) $idCountry) : '')
        );
    }

    public static function checkMobileNumberExist($phoneNumber, $withIdCountry = false, $idCountry = null)
    {
        return Db::getInstance()->getValue(
            'SELECT `id_customer_phone`
            FROM `'._DB_PREFIX_.'wk_customer_phone_number`
            WHERE `phone` = '.pSQL($phoneNumber).(($withIdCountry && $idCountry) ?
            (' AND `id_country` = '.(int) $idCountry) : '')
        );
    }


    /**
     * Check for phone number validity
     *
     * @param string $number Phone number to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isOnlyNumber($phoneNumber)
    {
        return preg_match('/^\d+$/', $phoneNumber);
    }

    public static function generateOTP()
    {
        if ($digits = Configuration::get('WK_ML_OTP_LENGTH')) {
            return mt_rand(pow(10, $digits-1), pow(10, $digits)-1);
        }
        return false;
    }

    /**
     * Get OTP message to send
     *
     * @param string $otp    otp to send to user
     * @param int    $idLang language id in which message to be get
     *
     * @return string/bool
     */
    public static function getMessageForOTP($otp, $idLang)
    {
        if ($otpMsg = Configuration::get('WK_ML_OTP_TEXT', (int) $idLang)) {
            return str_replace('{otp}', $otp, $otpMsg);
        } elseif ($otpMsg = Configuration::get('WK_ML_OTP_TEXT', (int) Configuration::get('PS_LANG_DEFAULT'))) {
            return str_replace('{otp}', $otp, $otpMsg);
        }
        return false;
    }

    /**
     * Get Registration message to send
     *
     * @param int $idLang language id in which message to be get
     *
     * @return string/bool
     */
    public static function getRegistrationMessage($idLang)
    {
        $msg = trim(Configuration::get('WK_REGISTER_MESSAGE', (int) $idLang));
        if (empty($msg)) {
            $msg = trim(Configuration::get('WK_REGISTER_MESSAGE', (int) Configuration::get('PS_LANG_DEFAULT')));
        }

        if (empty($msg)) {
            return false;
        }
        return $msg;
    }

    public function getAllActiveCustomers()
    {
        return Db::getInstance()->executeS(
            'SELECT cu.`id_customer`, CONCAT(cu.`firstname`," ", cu.`lastname`) as `customer_name`
            FROM `'._DB_PREFIX_.'customer` cu
            WHERE `active` = 1'
        );
    }

    public static function getAllRegisteredCustomerID()
    {
        return Db::getInstance()->executeS(
            'SELECT `id_customer` FROM `'._DB_PREFIX_.'wk_customer_phone_number` WHERE `id_customer` <> 0'
        );
    }

    public static function checkPhoneNumberAlreadyTaken($phoneNumber, $idCustomer = false)
    {
        if ($idCustomer) {
            $phoneDetails = self::getPhoneDetailsByIdCustomer($idCustomer);
            if (empty($phoneDetails)) {
                $phoneDetails = self::getDetailsByPhoneNumber($phoneNumber);
                if (!empty($phoneDetails)) {
                    if ($phoneDetails['id_customer'] && ($idCustomer != $phoneDetails['id_customer'])) {
                        return true;
                    }
                }
            } else {
                if ($phoneNumber != $phoneDetails['phone']) {
                    $phoneDetails = self::getDetailsByPhoneNumber($phoneNumber);
                    if (!empty($phoneDetails)) {
                        if ($phoneDetails['id_customer'] && ($idCustomer != $phoneDetails['id_customer'])) {
                            return true;
                        }
                    }
                }
            }
        } else {
            $phoneDetails = self::getDetailsByPhoneNumber($phoneNumber);
            if (!empty($phoneDetails) && $phoneDetails['id_customer']) {
                return true;
            }
        }
        return false;
    }

    /**
     * To delete duplicate entries for same number assigned to the customer
     *
     * @param string $phoneNumber
     * @return void
     */
    public static function deleteEntriesWithSamePhoneNumberNotAssignedToCustomer($phoneNumber)
    {
        if ($phoneNumber) {
            $rows = Db::getInstance()->executeS(
                'SELECT `id_customer_phone`
                FROM `'._DB_PREFIX_.'wk_customer_phone_number`
                WHERE `id_customer` = 0 AND `phone` = '.pSQL($phoneNumber)
            );
            if (is_array($rows) && !empty($rows)) {
                foreach ($rows as $row) {
                    $customerPhoneNumber = new WkCustomerPhoneNumber($row['id_customer_phone']);
                    $customerPhoneNumber->delete();
                    unset($customerPhoneNumber);
                }
            }
        }
    }

    public static function deleteMobileDetailsOfDeletedCustomers()
    {
        $rows = Db::getInstance()->executeS(
            'SELECT `id_customer_phone`, `id_customer`
            FROM `'._DB_PREFIX_.'wk_customer_phone_number`
            WHERE `id_customer` <> 0'
        );
        $deletedAtleastOneRow = false;
        foreach ($rows as $row) {
            if (!Customer::customerIdExistsStatic($row['id_customer'])) {
                $customerPhoneNumber = new WkCustomerPhoneNumber($row['id_customer_phone']);
                $customerPhoneNumber->delete();
                $deletedAtleastOneRow = true;
            }
        }
        unset($customerPhoneNumber);
        return $deletedAtleastOneRow;
    }
}
