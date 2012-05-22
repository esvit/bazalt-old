<?php

class Html_Element_Checkbox extends Html_Element_Input
{
    const DEFAULT_CSS_CLASS = 'bz-form-checkbox-input';

    const DEFAULT_POST_VALUE = 'on';

    public function initAttributes()
    {
        parent::initAttributes();

        $this->template('elements/checkbox');

        $this->validAttribute('value', 'mixed');
        $this->validAttribute('postValue', 'mixed', false);
        $this->validAttribute('checked', 'boolean'); // Предварительно активированный переключатель или флажок. 

        $this->addClass(self::DEFAULT_CSS_CLASS);

        $this->type('checkbox');
        $this->checked(false);
        $this->postValue(self::DEFAULT_POST_VALUE);
    }

    public function checked($checked = null)
    {
        if ($checked !== null) {
            return parent::checked($checked);
        }
        if ($this->form->isPostBack()) {
            $values = $this->container->dataSource()->values();
            if (isset($values[$this->originalName])) {
                return $values[$this->originalName] == $this->postValue();
            }
        }
        return parent::checked();
    }

    public function postValue($value = null)
    {
        if ($value !== null) {
            $this->attributes['value'] = $value;
            return parent::postValue($value);
        }
        return parent::postValue();
    }

    public function value($checked = null, $value = self::DEFAULT_POST_VALUE)
    {
        if ($checked !== null) {
            if ($value) {
                $this->postValue($value);
            }
            $this->checked($checked);
            return parent::value($value);
        }
        if ($this->form->isPostBack()) {
            return $this->checked() ? $this->postValue() : null;
        }
        return parent::value();
    }
}