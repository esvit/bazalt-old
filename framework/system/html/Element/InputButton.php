<?php

class Html_Element_InputButton extends Html_Element_Input
{
    const DEFAULT_CSS_CLASS = 'bz-form-input-button';

    public function initAttributes()
    {
        parent::initAttributes();

        $this->validAttribute('value');                 // Значение элемента.

        $this->template('elements/inputbutton');

        $this->addClass(self::DEFAULT_CSS_CLASS);

        $this->type('button');
    }

    public function submit()
    {
        return $this->type('submit');
    }

    public function reset()
    {
        return $this->type('reset');
    }

    public function button()
    {
        return $this->type('button');
    }
}