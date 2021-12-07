<?php
use PrestaShop\PrestaShop\Adapter\ServiceLocator;
use PrestaShop\PrestaShop\Adapter\CoreException;
class Customer extends CustomerCore
{
    
    
    
    /*
    * module: wkmobilelogin
    * date: 2021-09-24 15:16:33
    * version: 4.0.2
    */
    public function getByEmail($email, $plaintextPassword = null, $ignoreGuest = true)
    {
        if (Module::isEnabled('wkmobilelogin') && Configuration::get('WK_LOGIN_BY_PHONE')) {
            $isPhone = false;
            if (!(Validate::isEmail($email) || Validate::isPhoneNumber($email))
                || ($plaintextPassword && !Validate::isPasswd($plaintextPassword))
            ) {
                die(Tools::displayError());
            }
            if (Validate::isEmail($email)) {
                $isPhone = false;
            } elseif (Validate::isPhoneNumber($email)) {
                $isPhone = true;
                $phoneNumber = preg_replace("/[^0-9]/", "", $email);
                if (!((int) $phoneNumber) || !WkCustomerPhoneNumber::isOnlyNumber($phoneNumber)) {
                    return false;
                }
            }
            $shopGroup = Shop::getGroupFromShop(Shop::getContextShopID(), false);
            $sql = new DbQuery();
            $sql->select('c.`passwd`');
            $sql->from('customer', 'c');
            if ($isPhone) {
                $sql->leftJoin('wk_customer_phone_number', 'cpn', 'c.`id_customer` = cpn.`id_customer`');
                $sql->where(' cpn.`phone` = \''.pSQL($phoneNumber).'\'');
            } else {
                $sql->where('c.`email` = \''.pSQL($email).'\'');
            }
            if (Shop::getContext() == Shop::CONTEXT_SHOP && $shopGroup['share_customer']) {
                $sql->where('c.`id_shop_group` = '.(int) Shop::getContextShopGroupID());
            } else {
                $sql->where('c.`id_shop` IN ('.implode(', ', Shop::getContextListShopID(Shop::SHARE_CUSTOMER)).')');
            }
            if ($ignoreGuest) {
                $sql->where('c.`is_guest` = 0');
            }
            $sql->where('c.`deleted` = 0');
            $passwordHash = Db::getInstance()->getValue($sql);
            try {
                $crypto = ServiceLocator::get('\\PrestaShop\\PrestaShop\\Core\\Crypto\\Hashing');
            } catch (CoreException $e) {
                return false;
            }
            $shouldCheckPassword = !is_null($plaintextPassword);
            if ($shouldCheckPassword && !$crypto->checkHash($plaintextPassword, $passwordHash)) {
                return false;
            }
            $sql = new DbQuery();
            $sql->select('c.*');
            $sql->from('customer', 'c');
            if ($isPhone) {
                $sql->leftJoin('wk_customer_phone_number', 'cpn', 'c.`id_customer` = cpn.`id_customer`');
                $sql->where('cpn.`phone` = \''.pSQL($phoneNumber).'\'');
            } else {
                $sql->where('c.`email` = \''.pSQL($email).'\'');
            }
            if (Shop::getContext() == Shop::CONTEXT_SHOP && $shopGroup['share_customer']) {
                $sql->where('c.`id_shop_group` = '.(int) Shop::getContextShopGroupID());
            } else {
                $sql->where('c.`id_shop` IN ('.implode(', ', Shop::getContextListShopID(Shop::SHARE_CUSTOMER)).')');
            }
            if ($ignoreGuest) {
                $sql->where('c.`is_guest` = 0');
            }
            $sql->where('c.`deleted` = 0');
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
            if (!$result) {
                return false;
            }
            $this->id = $result['id_customer'];
            foreach ($result as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
            if ($shouldCheckPassword && !$crypto->isFirstHash($plaintextPassword, $passwordHash)) {
                $this->passwd = $crypto->hash($plaintextPassword);
                $this->update();
            }
            return $this;
        } else {
            return parent::getByEmail($email, $plaintextPassword, $ignoreGuest);
        }
    }
}
