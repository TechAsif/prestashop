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
class AuthController extends AuthControllerCore
{
    /*
    * module: wkmobilelogin
    * date: 2021-09-24 15:16:33
    * version: 4.0.2
    */
    public function initContent()
    {
        if (Tools::isSubmit('submitCreate') && Module::isEnabled('wkmobilelogin')) {
            $assignTempEmail = false;
            $email = Tools::getValue('email', false);
            if (Configuration::get('WK_DISPLAY_PHONE_FIELD_ACC_FORM')
                && Configuration::get('WK_EMAIL_OPTIONAL')
            ) {
                if (!$email) {
                    $assignTempEmail = true;
                }
                if (!Configuration::get('WK_DISPLAY_OPTIONAL_EMAIL_FIELD_ACC_FORM')) {
                    $assignTempEmail = true;
                }
            }
            if ($assignTempEmail) {
                $firstName = Tools::getValue('firstname', false);
                $lastName = Tools::getValue('lastname', false);
                $phoneNumber = Tools::getValue('wkphonenumber', false);
                $phoneNumber = trim(preg_replace("/[^0-9]/", "", $phoneNumber));
                if (Validate::isName($firstName) && Validate::isName($lastName) && $phoneNumber) {
                    $email = $lastName.'_'.$firstName.'_';
                    $email .= mt_rand(100000, 999999).'_'.Tools::substr(
                        $phoneNumber,
                        (Tools::strlen($phoneNumber)/2)
                    );
                    $email .= Configuration::get('WK_TEMP_EMAIL_FORMAT');
                    $_POST['email'] = $email;
                }
            }
        }
        parent::initContent();
    }
}
