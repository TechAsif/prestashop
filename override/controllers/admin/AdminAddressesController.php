<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * @property Address $object
 */
class AdminAddressesController extends AdminAddressesControllerCore
{
    public function __construct()
    {
        parent::__construct();
    }

    public function renderForm()
    {
        $this->fields_form = array(
            'legend' => array(
                'title' => $this->trans('Addresses', array(), 'Admin.Orderscustomers.Feature'),
                'icon' => 'icon-envelope-alt'
            ),
            'input' => array(
                array(
                    'type' => 'text_customer',
                    'label' => $this->trans('Customer', array(), 'Admin.Global'),
                    'name' => 'id_customer',
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Identification number', array(), 'Admin.Orderscustomers.Feature'),
                    'name' => 'dni',
                    'required' => false,
                    'col' => '4',
                    'hint' => $this->trans('The national ID card number of this person, or a unique tax identification number.', array(), 'Admin.Orderscustomers.Feature')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->trans('Address alias', array(), 'Admin.Orderscustomers.Feature'),
                    'name' => 'alias',
                    'required' => true,
                    'col' => '4',
                    'hint' => $this->trans('Invalid characters:', array(), 'Admin.Notifications.Info').' &lt;&gt;;=#{}'
                ), 
                array(
                    'type' => 'text',
                    'label' => $this->l('Union Porishad'),
                    'name' => 'up',
                    'required' => false,
                    'col' => '4',
                    'hint' => $this->trans('Just a custom field!')
                ), 
                array(
                    'type' => 'text',
                    'label' => $this->l('Thana'),
                    'name' => 'thana',
                    'required' => false,
                    'col' => '4',
                    'hint' => $this->trans('Just a custom field!')
                ), 
                array(
                    'type' => 'textarea',
                    'label' => $this->trans('Other', array(), 'Admin.Global'),
                    'name' => 'other',
                    'required' => false,
                    'cols' => 15,
                    'rows' => 3,
                    'hint' => $this->trans('Invalid characters:', array(), 'Admin.Notifications.Info').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_order'
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'address_type',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'back'
                )
            ),
            'submit' => array(
                'title' => $this->trans('Save', array(), 'Admin.Actions'),
            )
        );

        $this->fields_value['address_type'] = (int)Tools::getValue('address_type', 1);

        $id_customer = (int)Tools::getValue('id_customer');
        if (!$id_customer && Validate::isLoadedObject($this->object)) {
            $id_customer = $this->object->id_customer;
        }
        if ($id_customer) {
            $customer = new Customer((int)$id_customer);
            $token_customer = Tools::getAdminToken('AdminCustomers'.(int)(Tab::getIdFromClassName('AdminCustomers')).(int)$this->context->employee->id);
        }

        $this->tpl_form_vars = array(
            'customer' => isset($customer) ? $customer : null,
            'tokenCustomer' => isset($token_customer) ? $token_customer : null,
            'back_url' => urldecode(Tools::getValue('back'))
        );

        // Order address fields depending on country format
        $addresses_fields = $this->processAddressFormat();
        // we use  delivery address
        $addresses_fields = $addresses_fields['dlv_all_fields'];

        // get required field
        $required_fields = AddressFormat::getFieldsRequired();

        // Merge with field required
        $addresses_fields = array_unique(array_merge($addresses_fields, $required_fields));

        $temp_fields = array();

        foreach ($addresses_fields as $addr_field_item) {
            if ($addr_field_item == 'company') {
                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->trans('Company', array(), 'Admin.Global'),
                    'name' => 'company',
                    'required' => in_array('company', $required_fields),
                    'col' => '4',
                    'hint' => $this->trans('Invalid characters:', array(), 'Admin.Notifications.Info').' &lt;&gt;;=#{}'
                );
                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->trans('VAT number', array(), 'Admin.Orderscustomers.Feature'),
                    'col' => '2',
                    'name' => 'vat_number',
                    'required' => in_array('vat_number', $required_fields)
                );
            } elseif ($addr_field_item == 'lastname') {
                if (isset($customer) &&
                    !Tools::isSubmit('submit'.strtoupper($this->table)) &&
                    Validate::isLoadedObject($customer) &&
                    !Validate::isLoadedObject($this->object)) {
                    $default_value = $customer->lastname;
                } else {
                    $default_value = '';
                }

                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->trans('Last Name', array(), 'Admin.Global'),
                    'name' => 'lastname',
                    'required' => true,
                    'col' => '4',
                    'hint' => $this->trans('Invalid characters:', array(), 'Admin.Notifications.Info').' 0-9!&amp;lt;&amp;gt;,;?=+()@#"�{}_$%:',
                    'default_value' => $default_value,
                );
            } elseif ($addr_field_item == 'firstname') {
                if (isset($customer) &&
                    !Tools::isSubmit('submit'.strtoupper($this->table)) &&
                    Validate::isLoadedObject($customer) &&
                    !Validate::isLoadedObject($this->object)) {
                    $default_value = $customer->firstname;
                } else {
                    $default_value = '';
                }

                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->trans('First Name', array(), 'Admin.Global'),
                    'name' => 'firstname',
                    'required' => true,
                    'col' => '4',
                    'hint' => $this->trans('Invalid characters:', array(), 'Admin.Notifications.Info').' 0-9!&amp;lt;&amp;gt;,;?=+()@#"�{}_$%:',
                    'default_value' => $default_value,
                );
            } elseif ($addr_field_item == 'address1') {
                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->trans('Address', array(), 'Admin.Global'),
                    'name' => 'address1',
                    'col' => '6',
                    'required' => true,
                );
            } elseif ($addr_field_item == 'address2') {
                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->trans('Address', array(), 'Admin.Global').' (2)',
                    'name' => 'address2',
                    'col' => '6',
                    'required' => in_array('address2', $required_fields),
                );
            } elseif ($addr_field_item == 'postcode') {
                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->trans('Zip/postal code', array(), 'Admin.Global'),
                    'name' => 'postcode',
                    'col' => '2',
                    'required' => true,
                );
            } elseif ($addr_field_item == 'city') {
                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->trans('City', array(), 'Admin.Global'),
                    'name' => 'city',
                    'col' => '4',
                    'required' => true,
                );
            } elseif ($addr_field_item == 'country' || $addr_field_item == 'Country:name') {
                $temp_fields[] = array(
                    'type' => 'select',
                    'label' => $this->trans('Country', array(), 'Admin.Global'),
                    'name' => 'id_country',
                    'required' => in_array('Country:name', $required_fields) || in_array('country', $required_fields),
                    'col' => '4',
                    'default_value' => (int)$this->context->country->id,
                    'options' => array(
                        'query' => Country::getCountries($this->context->language->id),
                        'id' => 'id_country',
                        'name' => 'name'
                    )
                );
                $temp_fields[] = array(
                    'type' => 'select',
                    'label' => $this->trans('State', array(), 'Admin.Global'),
                    'name' => 'id_state',
                    'required' => false,
                    'col' => '4',
                    'options' => array(
                        'query' => array(),
                        'id' => 'id_state',
                        'name' => 'name'
                    )
                );
            } elseif ($addr_field_item == 'phone') {
                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->trans('Home phone', array(), 'Admin.Global'),
                    'name' => 'phone',
                    'required' => in_array('phone', $required_fields),
                    'col' => '4',
                );
            } elseif ($addr_field_item == 'phone_mobile') {
                $temp_fields[] = array(
                    'type' => 'text',
                    'label' => $this->trans('Mobile phone', array(), 'Admin.Global'),
                    'name' => 'phone_mobile',
                    'required' =>  in_array('phone_mobile', $required_fields),
                    'col' => '4',
                );
            }
        }

        // merge address format with the rest of the form
        array_splice($this->fields_form['input'], 3, 0, $temp_fields);

        // return parent::renderForm();
        return AdminController::renderForm();
    }

    public function processSave()
    {
        if (Tools::getValue('submitFormAjax')) {
            $this->redirect_after = false;
        }

        // Transform e-mail in id_customer for parent processing
        if (Validate::isEmail(Tools::getValue('email'))) {
            $customer = new Customer();
            $customer->getByEmail(Tools::getValue('email'), null, false);
            if (Validate::isLoadedObject($customer)) {
                $_POST['id_customer'] = $customer->id;
            } else {
                $this->errors[] = $this->trans('This email address is not registered.', array(), 'Admin.Orderscustomers.Notification');
            }
        } elseif ($id_customer = Tools::getValue('id_customer')) {
            $customer = new Customer((int)$id_customer);
            if (Validate::isLoadedObject($customer)) {
                $_POST['id_customer'] = $customer->id;
            } else {
                $this->errors[] = $this->trans('This customer ID is not recognized.', array(), 'Admin.Orderscustomers.Notification');
            }
        } else {
            $this->errors[] = $this->trans('This email address is not valid. Please use an address like bob@example.com.', array(), 'Admin.Orderscustomers.Notification');
        }
        if (Country::isNeedDniByCountryId(Tools::getValue('id_country')) && !Tools::getValue('dni')) {
            $this->errors[] = $this->trans('The identification number is incorrect or has already been used.', array(), 'Admin.Orderscustomers.Notification');
        }

        /* If the selected country does not contain states */
        $id_state = (int)Tools::getValue('id_state');
        $id_country = (int)Tools::getValue('id_country');
        $country = new Country((int)$id_country);
        if ($country && !(int)$country->contains_states && $id_state) {
            $this->errors[] = $this->trans('You have selected a state for a country that does not contain states.', array(), 'Admin.Orderscustomers.Notification');
        }

        /* If the selected country contains states, then a state have to be selected */
        if ((int)$country->contains_states && !$id_state) {
            $this->errors[] = $this->trans('An address located in a country containing states must have a state selected.', array(), 'Admin.Orderscustomers.Notification');
        }

        $postcode = Tools::getValue('postcode');
        /* Check zip code format */
        if ($country->zip_code_format && !$country->checkZipCode($postcode)) {
            $this->errors[] = $this->trans('Your Zip/postal code is incorrect.', array(), 'Admin.Notifications.Error').'<br />'.$this->trans('It must be entered as follows:', array(), 'Admin.Notifications.Error').' '.str_replace('C', $country->iso_code, str_replace('N', '0', str_replace('L', 'A', $country->zip_code_format)));
        } elseif (empty($postcode) && $country->need_zip_code) {
            $this->errors[] = $this->trans('A Zip/postal code is required.', array(), 'Admin.Notifications.Error');
        } elseif ($postcode && !Validate::isPostCode($postcode)) {
            $this->errors[] = $this->trans('The Zip/postal code is invalid.', array(), 'Admin.Notifications.Error');
        }

        /* If this address come from order's edition and is the same as the other one (invoice or delivery one)
        ** we delete its id_address to force the creation of a new one */
        if ((int)Tools::getValue('id_order')) {
            $this->_redirect = false;
            if (isset($_POST['address_type'])) {
                $_POST['id_address'] = '';
                $this->id_object = null;
            }
        }

        // Check the requires fields which are settings in the BO
        $address = new Address();
        $this->errors = array_merge($this->errors, $address->validateFieldsRequiredDatabase());

        $return = false;
        if (empty($this->errors)) {
            // $return = parent::processSave();
            $return = AdminController::processSave();
        } else {
            // if we have errors, we stay on the form instead of going back to the list
            $this->display = 'edit';
        }

        /* Reassignation of the order's new (invoice or delivery) address */
        $address_type = (int)Tools::getValue('address_type') == 2 ? 'invoice' : 'delivery';

        if ($this->action == 'save' && ($id_order = (int)Tools::getValue('id_order')) && !count($this->errors) && !empty($address_type)) {
            if (!Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'orders SET `id_address_'.bqSQL($address_type).'` = '.(int)$this->object->id.' WHERE `id_order` = '.(int)$id_order)) {
                $this->errors[] = $this->trans('An error occurred while linking this address to its order.', array(), 'Admin.Orderscustomers.Notification');
            } else {
                //update order shipping cost
                $order = new Order($id_order);
                $order->refreshShippingCost();
                Tools::redirectAdmin(urldecode(Tools::getValue('back')).'&conf=4');
            }
        }
        return $return;
    }

}
