<?php

class Html_jQuery_DatePicker extends Html_Element_Text
{
    protected $onSelect = '';

    /*
     * Map of jquery date formats
     */
    protected $jQueryFormats = array(
        'Y-m-d' => 'yy-mm-dd',
        'd-m-Y' => 'dd-mm-yy'
    );

    public function initAttributes()
    {
        parent::initAttributes();

        $this->validAttribute('name',  'string', false);
        $this->validAttribute('placeholder', 'string', false);
        $this->validAttribute('inline','boolean', false);
        $this->validAttribute('format','string', false);

        $this->template('elements/jquery/datepicker');

        $this->format('Y-m-d');
        /*if(!isset($attributes['format'])) {
            $attributes['format'] = 'Y-m-d';
        }*/
        // print_r($this->form->id());
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
            return date($this->format(), strtotime($elValue));
        }
        if (!empty($value)) {
            parent::value(date($this->format(), strtotime($value)));
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
        Scripts::addModule('jQuery UI');

        $attributes = array();
        $attributes['dateFormat'] = isset($this->jQueryFormats[$this->format()]) ? 
                                           $this->jQueryFormats[$this->format()] :
                                           $this->format();
                                           
        if ($this->inline()) {
            $attributes['inline'] = 'true';
            $attributes['minDate'] = '1';
            $attributes['defaultDate'] = '1';
        }
               
        $attrs = json_encode($attributes);               
        if($this->onSelect) {
            $attrs = substr($attrs, 0, -1);
            $attrs .= ', onSelect: function(selectedDate) {' . $this->onSelect . '}';
            $attrs .= '}';
        }

        if ($this->inline()) {
            Html_Form::addOnReady('$("#' . $this->id() . 'DatePicker").datepicker('.$attrs.');');
            Html_Form::addOnReady('$("#' . $this->id() . 'DatePicker").click(function(){ $(this).find("input").val($(this).datepicker("getDate"));});');
            return '<div id="' . $this->id() . 'DatePicker"><input type="hidden" id="' . $this->id() . '" name="' . $this->name() . '" /></div><div class="spacer"></div>';
        }
        Html_Form::addOnReady('$("#' . $this->id() . ' input").datepicker(' . $attrs . ');');
        return parent::toString();
    }
}
