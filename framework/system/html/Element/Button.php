<?php

class Html_Element_Button extends Html_FormElement
{
    const DEFAULT_CSS_CLASS = 'bz-form-button';

    protected $content = null;

    public function initAttributes()
    {
        parent::initAttributes();

        $this->template('elements/button');

        $this->validAttribute('disabled', 'boolean'); // Блокирует доступ и изменение элемента. 
        $this->validAttribute('type', array('reset', 'submit', 'button'));
        $this->validAttribute('value');               // Значение кнопки, которое будет отправлено на сервер или прочитано с помощью скриптов.

        $this->addClass(self::DEFAULT_CSS_CLASS);
    }

    public function reset()
    {
        return $this->type('reset');
    }

    public function submit()
    {
        return $this->type('submit');
    }

    public function button()
    {
        return $this->type('button');
    }

    public function content($content = null)
    {
        if ($content != null) {
            $this->content = $content;
            return $this;
        }
        return $this->content;
    }
}