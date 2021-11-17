<?php


class WebserviceSpecificManagementAddress implements WebserviceSpecificManagementInterface
{

    /** @var WebserviceOutputBuilder */

    protected $objOutput;

    protected $output;



    /** @var WebserviceRequest */

    protected $wsObject;



    public function setUrlSegment($segments)

    {

        $this->urlSegment = $segments;

        return $this;
    }



    public function getUrlSegment()

    {

        return $this->urlSegment;
    }

    public function getWsObject()

    {

        return $this->wsObject;
    }



    public function getObjectOutput()

    {

        return $this->objOutput;
    }



    /**

     * This must be return a string with specific values as WebserviceRequest expects.

     *

     * @return string

     */

    public function getContent()

    {

        return $this->objOutput->getObjectRender()->overrideContent($this->output);
    }



    public function setWsObject(WebserviceRequestCore $obj)

    {

        $this->wsObject = $obj;

        return $this;
    }



    /**

     * @param WebserviceOutputBuilderCore $obj

     * @return WebserviceSpecificManagementInterface

     */

    public function setObjectOutput(WebserviceOutputBuilderCore $obj)

    {

        $this->objOutput = $obj;

        return $this;
    }


    public function getCountryId($country_iso)
    {
        if (empty($country_iso)) return 0;
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
            SELECT  `id_country`
            FROM `' . _DB_PREFIX_ . 'country`
            WHERE `iso_code` = \'' . $country_iso . '\'
            LIMIT 1'
        );
        if (empty($result)) return 0;
        return $result[0]['id_country'];
    }

    public function getStateId($id_state, $id_country)
    {
        if (empty($id_state)) return 0;
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
            SELECT  `id_state`
            FROM `' . _DB_PREFIX_ . 'state`
            WHERE `id_state` = ' . $id_state . ' AND `id_country` = ' . $id_country . '
            LIMIT 1'
        );
        if (empty($result)) return 0;
        return $result[0]['id_state'];
    }

    public function getAddress($country_iso, $id_state, $id_customer, $email, $firstname, $lastname, $address, $city, $postcode, $phone)
    {
        $get_country = $this->getCountryId($country_iso);
        $get_state = $this->getStateId($id_state, $get_country);
        $address1 = $address;
        if ($get_state == 0) {
            $address1 .= $id_state;
        }
        if ($get_country == 0) {
            $address1 .= $country_iso;
        }
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
            SELECT  `id_address`
            FROM `' . _DB_PREFIX_ . 'address`
            WHERE `id_country` = ' . $get_country . ' AND 
            `id_state` = ' . $get_state . ' AND 
            `id_customer` = ' . $id_customer . ' AND `company` = \'' . $email . '\' AND
            `firstname` = \'' . $firstname . '\' AND `lastname` = \'' . $lastname . '\' AND
            `address1` = \'' . $address1 . '\' AND `city` = \'' . $city . '\' AND 
            `postcode` = \'' . $postcode . '\' AND `phone` = \'' . $phone . '\'
            '
        );
        if (empty($result)) {

            $new_address = Db::getInstance()->execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'address` (id_country, id_state, id_customer, company, firstname, lastname, address1, city, postcode, phone)
                VALUES (' . $get_country . ', ' . $get_state . ', ' . $id_customer . ', "' . $email . '", "' . $firstname . '", "' . $lastname . '", "' . $address1 . '", "' . $city . '", "' . $postcode . '", "' . $phone . '")'
            );
            if ($new_address == true) {
                return Db::getInstance()->Insert_ID();
            }
            return '1';
        };
        return $result[0]['id_address'];
    }


    public function manage()

    {

        $objects_address = array();
        $objects_address['empty'] = new Address();
        $country_iso = $this->wsObject->urlFragments['country_iso'] ?? null;
        $id_state = $this->wsObject->urlFragments['id_state'] ?? null;
        $id_customer = $this->wsObject->urlFragments['id_customer'] ?? null;
        $email = $this->wsObject->urlFragments['email'] ?? null;
        $firstname = $this->wsObject->urlFragments['firstname'] ?? null;
        $lastname = $this->wsObject->urlFragments['lastname'] ?? null;
        $address1 = $this->wsObject->urlFragments['address'] ?? null;
        $city = $this->wsObject->urlFragments['city'] ?? null;
        $postcode = $this->wsObject->urlFragments['postcode'] ?? null;
        $phone = $this->wsObject->urlFragments['phone'] ?? null;
        $address = $this->getAddress($country_iso, $id_state, $id_customer, $email, $firstname, $lastname, $address1, $city, $postcode, $phone);
        $objects_address[] = new Address($address);
        $this->_resourceConfiguration = $objects_address['empty']->getWebserviceParameters();

        $this->wsObject->setFieldsToDisplay();

        $this->output .= $this->objOutput->getContent($objects_address, null, $this->wsObject->fieldsToDisplay, $this->wsObject->depth, WebserviceOutputBuilder::VIEW_LIST, false);
    }
}
