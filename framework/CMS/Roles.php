<?php

class CMS_Roles
{
    protected $roles = null;

    protected $component = null;

    public function __construct($roles = array())
    {
        $this->roles = $roles;
    }

    public function component($component = null)
    {
        if ($component !== null) {
            $this->component = $component;
            return $this;
        }
        return $this->component;
    }

    public function roles($roles = null)
    {
        if ($roles !== null) {
            $this->roles = $roles;
            return $this;
        }
        return $this->roles;
    }

    public function getEditElement($name, $attributes = array())
    {
        $el = new CMS_Form_Element_AclSelector($name, $attributes);
        $el->roles($this->roles());
        return $el;
    }
}