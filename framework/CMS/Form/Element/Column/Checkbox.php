<?php

class CMS_Form_Element_Column_Checkbox extends CMS_Form_Element_TableColumn
{
    protected $checked = false;

    public function __construct($name)
    {
        parent::__construct($name);

        $this->width(10);

        $this->headerTemplate('cms/table/header/checkbox');
        $this->columnTemplate('cms/table/column/checkbox');

        $this->javascriptTemplate('cms/table/javascript/checkbox');
    }

    public function checked($checked = null)
    {
        if ($checked !== null) {
            $this->checked = $checked;
        }
        return $this->checked;
    }
}