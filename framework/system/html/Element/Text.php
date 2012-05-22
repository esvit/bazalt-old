<?php

class Html_Element_Text extends Html_Element_Input
{
    const DEFAULT_CSS_CLASS = 'bz-form-input-text';

    public function initAttributes()
    {
        parent::initAttributes();

        $this->validAttribute('readonly', 'boolean');   // Устанавливает, что поле не может изменяться пользователем.
        $this->validAttribute('maxlength', 'int');      // Максимальное количество символов разрешенных в тексте.
        $this->validAttribute('size', 'int');           // Ширина текстового поля.
        $this->validAttribute('value', 'string');       // Значение элемента.

        $this->template('elements/text');

        $this->addClass(self::DEFAULT_CSS_CLASS);

        $this->type('text');
    }
}
