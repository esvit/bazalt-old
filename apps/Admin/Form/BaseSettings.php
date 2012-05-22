<?php

using('Framework.System.Forms');

abstract class Admin_Form_BaseSettings extends Html_Form
{
    protected $flash = null;

    public function __construct($name = null)
    {
        if (empty($name)) {
            $name = strToLower(get_class($this));
        }
        parent::__construct($name);

        $this->flash = $this->addElement('flasher');

        $this->addElement('validationsummary');

        $this->addSettingFormElements();

        $this->addFormButtons();

        if($this->isPostBack()) {
            $this->value($_POST);

            if ($this->validate()) {
                $this->saveSettings();
                $this->flash->text(__('Settings successfully been saved!', 'Admin_App'));
                Url::goBack();
            }
        } else {
            $this->setDefaultValue();
        }
    }

    abstract function setDefaultValue();

    abstract function addSettingFormElements();

    abstract function saveSettings();

    protected function addFormButtons()
    {
        $group = $this->addElement('group')
                      ->addClass('actions');

        $group->addElement('button', 'save')
              ->content(__('Save settings', 'Admin_App'))
              ->addClass('btn-primary btn-large');

        $group->addElement('button', 'cancel')
              ->content(__('Cancel', 'Admin_App'))
              ->reset();
    }
}
