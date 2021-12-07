<?php
use Symfony\Component\Translation\TranslatorInterface;
class CustomerLoginForm extends CustomerLoginFormCore
{
    /*
    * module: wkmobilelogin
    * date: 2021-09-24 15:16:33
    * version: 4.0.2
    */
    private $context;
    /*
    * module: wkmobilelogin
    * date: 2021-09-24 15:16:33
    * version: 4.0.2
    */
    public function __construct(
        Smarty $smarty,
        Context $context,
        TranslatorInterface $translator,
        CustomerLoginFormatter $formatter,
        array $urls
    ) {
        $this->context = $context;
        $this->translator = $translator;
        parent::__construct($smarty, $context, $translator, $formatter, $urls);
    }
    /*
    * module: wkmobilelogin
    * date: 2021-09-24 15:16:33
    * version: 4.0.2
    */
    public function submit()
    {
        if ($this->validate()) {
            if (Module::isEnabled('wkmobilelogin') && Configuration::get('WK_LOGIN_BY_PHONE')) {
                $wkMobileLoginModule = Module::getInstanceByName('wkmobilelogin');
                $email = trim(Tools::getValue('email'));
                if (!(Validate::isEmail($email)
                    || (Validate::isPhoneNumber($email)
                    && (int) WkCustomerPhoneNumber::isOnlyNumber(preg_replace("/[^0-9]/", "", $email))))
                ) {
                    $this->errors[''][] = $wkMobileLoginModule->l('Please enter a valid Email or Mobile Number.');
                    return false;
                } elseif (Configuration::get('WK_PHONE_VERIFICATION_REQUIRED_LOGIN')) {
                    $phoneNumber = preg_replace("/[^0-9]/", "", $email);
                    if ((Validate::isPhoneNumber($email)
                        && (int) WkCustomerPhoneNumber::isOnlyNumber(preg_replace("/[^0-9]/", "", $email)))
                    ) {
                        if ($phoneDetails = WkCustomerPhoneNumber::getDetailsByPhoneNumber($phoneNumber)) {
                            $customer = new Customer();
                            $authentication = $customer->getByEmail(
                                $this->getValue('email'),
                                $this->getValue('password')
                            );
                            if (($authentication || (isset($authentication->active) && $authentication->active))
                                && $customer->id
                                && !$customer->is_guest
                                && !$phoneDetails['verified']
                            ) {
                                $this->errors[''][] = $wkMobileLoginModule->l(
                                    'Your mobile number is not verified, Please verify your mobile number.'
                                );
                                return false;
                            }
                        }
                    }
                }
            }
            Hook::exec('actionAuthenticationBefore');
            $customer = new Customer();
            $authentication = $customer->getByEmail(
                $this->getValue('email'),
                $this->getValue('password')
            );
            if (isset($authentication->active) && !$authentication->active) {
                $this->errors[''][] = $this->translator->trans(
                    'Your account isn\'t available at this time, please contact us',
                    array(),
                    'Shop.Notifications.Error'
                );
            } elseif (!$authentication || !$customer->id || $customer->is_guest) {
                $this->errors[''][] = $this->translator->trans(
                    'Authentication failed.',
                    array(),
                    'Shop.Notifications.Error'
                );
            } else {
                $this->context->updateCustomer($customer);
                Hook::exec('actionAuthentication', array('customer' => $this->context->customer));
                CartRule::autoRemoveFromCart($this->context);
                CartRule::autoAddToCart($this->context);
            }
        }
        return !$this->hasErrors();
    }
}
