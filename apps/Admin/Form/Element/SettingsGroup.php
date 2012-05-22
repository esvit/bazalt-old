<?php

class Admin_Form_Element_SettingsGroup extends Html_Element_Group
{
    const DEFAULT_CSS_CLASS = 'bz-admin-settings-group';

    protected $title = null;

    public function __construct($name, $attributes = array())
    {
        parent::__construct($name, $attributes);

        $this->addClass(self::DEFAULT_CSS_CLASS);

        $this->template('elements/admin/settingsgroup');
    }

    public function title($title = null)
    {
        if ($title != null) {
            $this->title = $title;
            return $this;
        } 
        return $this->title;
    }
}