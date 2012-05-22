<?php

class CMS_Form_Element_CMSAclSelector extends CMS_Form_Element_AclSelector
{
    public function roles()
    {
        return array(
            CMS_Bazalt::ACL_CAN_LOGIN           => __('Can access to administration panel', 'CMS'),
            CMS_Bazalt::ACL_CAN_CHANGE_SETTING  => __('Change settings', 'CMS'),
            CMS_Bazalt::ACL_GODMODE             => __('God mode', 'CMS')
        );
    }

    public function afterFormPost()
    {
    
    }

    public function getAcl()
    {
        if ($this->form->isPostBack()) {
            return parent::getAcl();
        } else {
            return $this->form->DataBindedObject->system_acl;
        }
    }
}