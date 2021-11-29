<?php
use Symfony\Component\Translation\TranslatorInterface;
class CustomerLoginFormatter extends CustomerLoginFormatterCore
{
    /*
    * module: wkmobilelogin
    * date: 2021-09-24 15:16:33
    * version: 4.0.2
    */
    private $translator;
    /*
    * module: wkmobilelogin
    * date: 2021-09-24 15:16:33
    * version: 4.0.2
    */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        parent::__construct($translator);
    }
    /*
    * module: wkmobilelogin
    * date: 2021-09-24 15:16:33
    * version: 4.0.2
    */
    public function getFormat()
    {
        if (Module::isEnabled('wkmobilelogin') && Configuration::get('WK_LOGIN_BY_PHONE')) {
            return array(
                'back' => (new FormField)
                    ->setName('back')
                    ->setType('hidden'),
                'email' => (new FormField)
                    ->setName('email')
                    ->setType('text')
                    ->setRequired(true)
                    ->setLabel(Module::getInstanceByName('wkmobilelogin')->loginEmailFieldName),
                'password' => (new FormField)
                    ->setName('password')
                    ->setType('password')
                    ->setRequired(true)
                    ->setLabel($this->translator->trans('Password', array(), 'Shop.Forms.Labels'))
                    ->addConstraint('isPasswd'),
            );
        } else {
            return parent::getFormat();
        }
    }
}
