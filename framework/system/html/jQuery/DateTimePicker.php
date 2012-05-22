<?php

class Html_jQuery_DateTimePicker extends Html_Element_Text
{
    protected $onSelect = '';
    
    /*
     * Map of jquery date formats
     */
    protected $jQueryFormats = array(
        'Y-m-d H:i' => 'yy-mm-dd',
        'Y-m-d h:i a' => 'yy-mm-dd'
    );
    
    private $_validAttributes = array(
        'ampm',
        'timeOnly',
        'showHour',
        'showMinute',
        'showSecond',
        'showTimezone',
        'showTime',
        'stepHour',
        'stepMinute',
        'stepSecond',
        'hour',
        'minute',
        'second',
        'timezone',
        'minDateTime',
        'maxDateTime',
        'hourGrid',
        'minuteGrid',
        'secondGrid',
        'alwaysSetTime',
        'separator',
        'altFieldTimeOnly',
        'showTimepicker'
    );
    
    public function __construct($name, $attributes = array())
    {
        foreach($this->_validAttributes as $validAttribute) {
            $this->validAttribute($validAttribute);
        }
        $this->validAttribute('format');
        if(!isset($attributes['format'])) {
            $attributes['format'] = 'Y-m-d H:i';
        }
        parent::__construct($name, $attributes);
    }
    
    public function onSelect($onSelect = null)
    {
        if ($onSelect !== null) {
            $this->onSelect = $onSelect;
        }
        return $this;
    }
    
    public function value($value = null)
    {
        if($value == null) {
            $elValue = parent::value();
            if (empty($elValue)) {
                return null;
            }
            return date($this->attributes['format'], strtotime($elValue));
        }
        if (!empty($value)) {
            parent::value(date($this->attributes['format'], strtotime($value)));
        }
    }
    
    public function validate()
    {
        $result = parent::validate();
        if(!$result) {
            return $result;
        }
        if($this->isRequireField()) {
            $result = !(strtotime($this->value) === false);
            if(!$result) {
                if(!isset($this->attributes['errorMessage'])) {
                    $this->attributes['errorMessage'] = sprintf(__('Field "%s". Invalid date'), $this->label());
                }
                $this->addError($this->name().'Invaliddate', $this->attributes['errorMessage']);
                $this->form->addError($this->name().'Invalidate', $this->attributes['errorMessage']);
            }
            return $result;
        }
        return true;
    }
    
    public function toString()
    {
        Scripts::addModule('jQuery UI DateTimePicker');
        
        $attributes = array();
        $attributes['dateFormat'] = isset($this->jQueryFormats[$this->attributes['format']]) ? 
                                           $this->jQueryFormats[$this->attributes['format']] :
                                           $this->attributes['format'];

        foreach($this->_validAttributes as $validAttribute) {
            if(isset($this->attributes[$validAttribute])) {
                $attributes[$validAttribute] = $this->attributes[$validAttribute];
            }
        }
        
        $attrs = json_encode($attributes);               
        if($this->onSelect) {
            $attrs = substr($attrs, 0, -1);
            $attrs .= ', onSelect: function(selectedDate) {' . $this->onSelect . '}';
            $attrs .= '}';
        }

        Html_jQuery_Form::addOnReady('$("#' . $this->id() . '").datetimepicker('.$attrs.');');
        return parent::toString() . '<div class="spacer"></div>';
    }
}
