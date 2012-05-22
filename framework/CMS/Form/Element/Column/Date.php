<?php

class CMS_Form_Element_Column_Date extends CMS_Form_Element_TableColumn
{
    public function __construct($name)
    {
        parent::__construct($name);

        $this->width(90);
        $this->columnTemplate('cms/table/column/date');
    }

    public function dataToString($data)
    {
        $this->table->View->assign('data', null);
        if (isset($data->{$this->name})) {
            $date = $data->{$this->name};
            if ($date) {
                // $date = strToTime($date); // conflict with twig
                $this->table->View->assign('data', $date);
            }
        }
        $this->table->View->assign('element', $this);
        return $this->table->View->fetch($this->columnTemplate());
    }
}