<?php

class Html_Datatable_Column_Hidden extends Html_Datatable_Column
{
    public function __construct($name, $title = null)
    {
        parent::__construct($name, $title);

        $this->visible = false;
        $this->searchable = false;
    }
}