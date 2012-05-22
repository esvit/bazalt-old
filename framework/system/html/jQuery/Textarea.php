<?php

class Html_jQuery_Textarea extends Html_Element_Textarea
{
    protected $resizable = false;
    
    /**
     * Конструктор
     *
     * @param string $name       Ім'я елементу
     * @param array  $attributes Атрибути
     */
    public function __construct($name, $attributes = array())
    {
        $this->validAttribute('placeholder');
        parent::__construct($name, $attributes);
    }
    
    public function resizable($resizable = null)
    {
        if ($resizable != null) {
            $this->resizable = $resizable;
            return $this;
        }
        return $this->resizable;
    }

    public function placeholder($placeholder = null)
    {
        if ($placeholder != null) {
            $this->attributes['placeholder'] = htmlentities($placeholder, ENT_QUOTES, 'UTF-8');
            return $this;
        }
        return $this->attributes['placeholder'];
    }

    public function toString()
    {
        $str = parent::toString();

        if ($this->resizable) {
            Scripts::addModule('TextAreaResizer');
            Html_jQuery_Form::addOnReady('$("#' . $this->id() . '").TextAreaResizer();');
        }
        
        if (isset($this->attributes['placeholder'])) {
            Scripts::addModule('jQuery Placeholder Enhanced');
            Html_jQuery_Form::addOnReady('$("#' . $this->id() . '").trigger("blur.placeholder");');
        }

        return $str;
    }
}
