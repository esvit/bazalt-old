<?php

class Html_Element_Radio extends Html_Element_Checkbox
{
    const DEFAULT_CSS_CLASS = 'bz-form-radio-input';

    public function initAttributes()
    {
        parent::initAttributes();

        $this->template('elements/radio');

        $this->removeClass(Html_Element_Checkbox::DEFAULT_CSS_CLASS);
        $this->addClass(self::DEFAULT_CSS_CLASS);

        $this->type('radio');
    }
}
