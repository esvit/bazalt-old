<?php

class Html_Element_Password extends Html_Element_Text
{
    const DEFAULT_CSS_CLASS = 'bz-form-input-password';

    public function initAttributes()
    {
        parent::initAttributes();

        //$this->template('elements/password');

        $this->removeClass(Html_Element_Text::DEFAULT_CSS_CLASS);
        $this->addClass(self::DEFAULT_CSS_CLASS);

        $this->type('password');
    }
}