<?php

abstract class Html_Element_Input extends Html_FormElement
{
    const DEFAULT_CSS_CLASS = 'bz-form-input';

    public function initAttributes()
    {
        parent::initAttributes();

        $this->template('elements/input');

        $this->validAttribute('disabled', 'boolean');   // Блокирует доступ и изменение элемента.
        $this->validAttribute('type', array(
                'text',
                'checkbox',
                'radio',
                'password',
                'hidden',
                'button',
                'submit',
                'reset',
                'file',
                'image',
                'number'
            )
        );       // Сообщает браузеру, к какому типу относится элемент формы.
        $this->validAttribute('value'); // Значение элемента.

        // html 5
        $this->validAttribute('autofocus', 'boolean');  // Specifies that an input element should get focus when the page loads
        $this->validAttribute('autocomplete');          // Specifies whether the input field should have autocomplete enabled
        $this->validAttribute('required', 'boolean');   // The input field's value is required in order to submit the form
        $this->validAttribute('placeholder');           // Specifies a short hint to help the user to fill out the input field

        $this->addClass(self::DEFAULT_CSS_CLASS);
    }

    /**
     * Деактивує елемент
     */
    public function disable()
    {
        $this->disabled(true);
    }

    public function enable()
    {
        $this->disabled(false);
    }

    public function isDisabled()
    {
        return (isset($this->attributes['disabled']) && ($this->attributes['disabled'] == 'disabled'));
    }
}