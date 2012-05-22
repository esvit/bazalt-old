<?php

using('Framework.Vendors.KCaptcha');

class CMS_Form_Element_Captcha extends Html_Element_Input
{
    public function initAttributes()
    {
        parent::initAttributes();

        $this->template('elements/captcha');
    }
    
    public function validate()
    {
        $elementName = md5($this->name());
        $res = isset(Session::Singleton()->{$elementName}) && trim(Session::Singleton()->{$elementName}) == trim($this->value());
        if(!$res) {
            $this->addError('value', __('Invalid captcha code', ''));
        }
        return $res;
    }
}
