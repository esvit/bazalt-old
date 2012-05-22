<?php

class Html_Element_Literal extends Html_FormElement
{
    public function __construct($name, $attributes = array())
    {
        parent::__construct($name, $attributes);

        $this->validAttribute('html', 'string', false);
    }

    public function toString()
    {
        return $this->html();
    }
}