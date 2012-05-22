<?php

class Html_Element_Fieldset extends Html_Element_Group
{
    const DEFAULT_CSS_CLASS = 'bz-form-fieldset';

    public function initAttributes()
    {
        parent::initAttributes();

        $this->validAttribute('title');         // Добавляет всплывающую подсказку к группе формы.

        $this->template('elements/fieldset');

        $this->removeClass(Html_Element_Group::DEFAULT_CSS_CLASS);
        $this->addClass(self::DEFAULT_CSS_CLASS);
    }
}