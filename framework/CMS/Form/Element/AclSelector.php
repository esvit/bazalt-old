<?php

class CMS_Form_Element_AclSelector extends Html_Element_Fieldset
{
    const DEFAULT_CSS_CLASS = 'bz-form-cms-aclselector';

    public function initAttributes()
    {
        parent::initAttributes();

        $this->validAttribute('roles', 'array', false);
        $this->validAttribute('component', 'object', false);

        $this->template('elements/aclselector');

        $this->removeClass(Html_Element_Fieldset::DEFAULT_CSS_CLASS);
        $this->addClass(self::DEFAULT_CSS_CLASS);
    }

    public function initElements()
    {
        $acl = $this->getAcl();
        foreach ($this->roles() as $val => $role) {
            $checkbox = $this->addElement('checkbox', 'acl_' . $val)
                     ->addClass('role-checkbox')
                     ->label($role)
                     ->id('acl_' . $this->name() . '_' . $val)
                     ->postValue($val);

            if (!$this->form->isPostBack()) {
                $checkbox->checked((bool)($acl & $val));
            }
        }
    }

    public function dataBind(ORM_Record $object)
    {
        $this->initElements();

        parent::dataBind($obj);
    }

    public function getAcl()
    {
        if ($this->form->isPostBack()) {
            $acl = 0;
            foreach ($this->elements as $element) {
                if ($element->checked()) {
                    $acl |= (int)$element->postValue();
                }
            }
        } else {
            $cmsComponent = $this->component()->getCmsComponent();

            $acl = $this->form->DataBindedObject->getAccessForComponent($cmsComponent);
        }
        return $acl;
    }
}