<?php

class Html_Element_Textarea extends Html_FormElement
{
    const DEFAULT_CSS_CLASS = 'bz-form-textarea';

    const DEFAULT_TEXTAREA_COLS = 40;

    const DEFAULT_TEXTAREA_ROWS = 6;

    public function initAttributes()
    {
        parent::initAttributes();

        $this->validAttribute('title');               // Добавляет всплывающую подсказку к группе формы.
        $this->validAttribute('cols',     'int');     // Ширина поля в символах. 
        $this->validAttribute('disabled', 'boolean'); // Блокирует доступ и изменение элемента.
        $this->validAttribute('name');                // Имя поля, предназначено для того, чтобы обработчик формы мог его идентифицировать.
        $this->validAttribute('readonly', 'boolean'); // Устанавливает, что поле не может изменяться пользователем.
        $this->validAttribute('rows',     'int');     // Высота поля в строках текста.

        $this->template('elements/textarea');

        if (!isset($attributes['cols'])) {
            $this->cols(self::DEFAULT_TEXTAREA_COLS);
        }
        if (!isset($attributes['rows'])) {
            $this->rows(self::DEFAULT_TEXTAREA_ROWS);
        }

        $this->addClass(self::DEFAULT_CSS_CLASS);
    }
}
