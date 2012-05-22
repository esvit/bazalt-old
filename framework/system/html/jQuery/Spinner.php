<?php

class Html_jQuery_Spinner extends Html_Element_Text
{
    public function initAttributes()
    {
        parent::initAttributes();

        $this->validAttribute('min');
        $this->validAttribute('max');

        $this->validAttribute('value', 'int');
    }

    public function toString()
    {
        Scripts::addModule('ui-spinner');
        
        $params = array();
        if($this->attributes['min']) {
            $params['min'] = $this->attributes['min'];
        }
        if($this->attributes['max']) {
            $params['max'] = $this->attributes['max'];
        }

        Html_jQuery_Form::addOnReady('$("#' . $this->id() . '").spinner('.json_encode($params).');');
        return parent::toString();
    }
}
