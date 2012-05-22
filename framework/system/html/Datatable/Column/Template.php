<?php

class Html_Datatable_Column_Template extends Html_Datatable_Column
{
    protected $template = null;

    public function template($template = null)
    {
        if ($template != null) {
            $this->template = $template;
        }
        return $this->template;
    }

    public function getData(ORM_Record $item)
    {
        if (empty($this->template)) {
            return parent::getData($item);
        }
        $params = $item->getFieldsValues();

        $html = $this->template;
        if (!empty($this->name)) {
            $params['data'] = $item->{$this->name};
        }
        return DataType_String::replaceConstants($html, $params);
    }
}