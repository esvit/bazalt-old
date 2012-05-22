<?php

using('Framework.System.Html');

class Admin_Form_Login extends CMS_Form_Login
{
    public function __construct($name = null)
    {
        parent::__construct($name, array('action' => CMS_Mapper::urlFor('Admin.SignIn')));
    }

    public function validate()
    {
        if (!parent::validate()) {
            return false;
        }

        if ($this->user->hasRight(null, CMS_Bazalt::ACL_GODMODE)) {
            return true;
        }
        if (!$this->user->hasRight(null, CMS_Bazalt::ACL_CAN_LOGIN)) {
            $this->addError($this->name(), __('You have not permission for access', 'Admin_App'));
            return false;
        } else if (!$this->user->Sites->has(CMS_Bazalt::getSite())) {
            $this->addError($this->name(), __('This is not your site', 'Admin_App'));
            return false;
        }
        return true;
    }
}
