<?php

class Html_Element_Image extends Html_Element_Input
{
    const DEFAULT_CSS_CLASS = 'bz-form-input-image';

    public function initAttributes()
    {
        parent::initAttributes();

        // deprecated, and is not supported in HTML5
        //$this->validAttribute('align');       // Определяет выравнивание изображения. 
        $this->validAttribute('alt');           // Альтернативный текст для кнопки с изображением.
        $this->validAttribute('border', 'int'); // Толщина рамки вокруг изображения. 
        $this->validAttribute('value');         // Значение элемента.
        $this->validAttribute('src');           // Адрес графического файла для поля с изображением.

        $this->template('elements/image');

        $this->addClass(self::DEFAULT_CSS_CLASS);

        $this->type('image');
    }
}