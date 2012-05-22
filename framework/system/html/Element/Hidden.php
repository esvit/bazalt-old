<?php

class Html_Element_Hidden extends Html_Element_Text
{
    const DEFAULT_CSS_CLASS = 'bz-form-input-hidden';

    public function initAttributes()
    {
        parent::initAttributes();

        $this->template('elements/hidden');

        $this->removeClass(Html_Element_Text::DEFAULT_CSS_CLASS);
        $this->addClass(self::DEFAULT_CSS_CLASS);

        $this->type('hidden')
             ->autocomplete('off');
    }
}