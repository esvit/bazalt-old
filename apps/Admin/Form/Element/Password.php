<?php

class Admin_Form_Element_Password extends Html_Element_Password
{
    public function toString()
    {
        $this->value('');
        $this->placeholder('[For change password click this]');

        Html_Form::addOnReady('$(".bz-form-input-password").focus(function() { $(this).removeAttr("placeholder"); })');

        return parent::toString();
    }
}