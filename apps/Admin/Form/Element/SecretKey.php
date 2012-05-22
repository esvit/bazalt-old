<?php

class Admin_Form_Element_SecretKey extends Html_FormElement
{
    const DEFAULT_CSS_CLASS = 'bz-admin-settings-secret-key';

    public function __construct($name, $attributes = array())
    {
        parent::__construct($name, $attributes);

        $this->template('elements/admin/secretkey');

        $this->addClass(self::DEFAULT_CSS_CLASS);
    }
    public function toString()
    {
        Html_Form::addOnReady('$("#' . $this->id() . ' a").click(function() { Admin_Webservice_Main.generateSecretKey(function(res) { $("#' . $this->id() . ' .value").text(res); }); });');

        return parent::toString();
    }

    public function value($value = null)
    {
        if ($value != null) {
            return $this;
        }
        return CMS_Bazalt::getSecretKey();
    }
}
