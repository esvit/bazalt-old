<?php

using('Framework.System.Html');

class CMS_Form_Login extends Html_Form
{
    protected $loginField = null;

    protected $passwordField = null;

    protected $rememberMe = null;

    protected $backUrl = null;

    protected $user = null;

    public function __construct($name = 'form_login', $attributes = array())
    {
        if (!isset($attributes['action'])) {
            $attributes['action'] = CMS_Mapper::urlFor('CMS.Login');
        }
        parent::__construct($name, $attributes);

        $this->addElement('validationsummary', 'errors');

        $this->loginField = $this->addElement('text', 'login')
                                 ->label(__('Login:', 'CMS'))
                                 ->id('login')
                                 ->addRuleNonEmpty();

        $this->passwordField = $this->addElement('password', 'password')
                                    ->label(__('Password:', 'CMS'))
                                    ->id('password')
                                    ->addRuleNonEmpty();

        $this->rememberMe = $this->addElement('checkbox', 'remember')
                                 ->id('remember')
                                 ->label(__('Remember me', 'CMS'));

        $this->backUrl = $this->addElement('hidden', 'backUrl');

        $this->addElement('inputbutton', 'submit')
             ->value(__('Sign In', 'CMS'))
             ->submit();
    }

    public function backUrl($url = null)
    {
        if ($url != null) {
            $this->backUrl->value($url);
        }
        return $this->backUrl->value();
    }

    public function validate()
    {
        $login = $this->loginField->value();
        $password = $this->passwordField->value();

        if (!parent::validate()) {
            return false;
        }
        $this->user = CMS_User::login($login, $password, $this->rememberMe->value());
        if (!$this->user) {
            $this->addError($this->name(), __('Wrong username or password', 'CMS'));
            return false;
        }
        return true;
    }
}
