<?php

class Html_jQuery_TimeSlider extends Html_Element_Text
{

    /*
     * Map of jquery date formats
     */
    protected $jQueryFormats = array(
        'Y-m-d' => 'yy-mm-dd',
        'd-m-Y' => 'dd-mm-yy'
    );

    public function __construct($name, $attributes = array())
    {
        $this->validAttribute('format');
        $this->validAttribute('step');
        
        if(!isset($attributes['format'])) {
            $attributes['format'] = 'Y-m-d';
        }
        if(!isset($attributes['step'])) {
            $attributes['step'] = 15;
        }
        parent::__construct($name, $attributes);
    }
    
    // public function value($value = null)
    // {
        // if($value == null) {
    	    // if (empty($this->value)) {
                // return null;
    	    // }
            // return date($this->attributes['format'], strtotime($this->value));
        // }
        // if (!empty($value)) {
            // $this->value = date($this->attributes['format'], strtotime($value));
        // }
    // }
    public function value($value = false)
    {
        if ($value !== false) {
            $this->value = $value;
            // print_r($this->value);exit;
            return $this;
        }
        return $this->value;
    }

    public function toString()
    {
        Scripts::addModule('jQuery UI');
        $value = $this->value();
        
        $params = array();
        $params['range'] = true;
        $params['min'] = 0;
        $params['max'] = 1440;
        $params['step'] = $this->attributes['step'];
        if($value && is_array($value)) {
            $params['values'] = $value;
            foreach($params['values'] as &$val) {
                $vs = explode(':', $val);
                $val = (int)$vs[0]*60 + (int)$vs[1];
            }
        }
        
        // Hidden Fields
        $startValue = is_array($value) ? $value[0] : '';
        $endValue = is_array($value) ? $value[1] : '';
        $hidden = '';
        $hidden .= '<input type="hidden" id="'.$this->id().'-start" name="'.$this->name().'[]" value="'.$startValue.'"/>'. PHP_EOL;
        $hidden .= '<input type="hidden" id="'.$this->id().'-end" name="'.$this->name().'[]" value="'.$endValue.'"/>'. PHP_EOL;
        
        // Build the Slide functionality of the Slider via javascript, updating hidden fields. aswell as hidden fields
        $sliderUpdateFn = 'function(e, ui) {'.PHP_EOL;
        $sliderUpdateFn .= "    $('#".$this->id()."-res').text('"._('From')." '+getTime(ui.values[0])+' "._('to')." '+ getTime(ui.values[1]));";
        $sliderUpdateFn .= "    $('#".$this->id()."-start').val(getTime(ui.values[0]));";
        $sliderUpdateFn .= "    $('#".$this->id()."-end').val(getTime(ui.values[1]));";
        $sliderUpdateFn .= "}".PHP_EOL;
        
        $params = json_encode($params);
        $params = substr($params, 0, -1);
        $params .= ', slide: '.$sliderUpdateFn;
        $params .= '}';

        $js = "function getTime(value) {
                var hours = Math.floor(value / 60);
                var minutes = value - (hours * 60);
                if(hours == 24 && minutes == 0) {
                    hours = 23;
                    minutes = 59;
                }
                if(minutes.length == 1) minutes = '0' + minutes;
                if(minutes == 0) minutes = '00';
        
                return hours+':'+minutes;
            };".PHP_EOL;
        
        $js .= sprintf('$("#%s").slider(%s);', $this->id().'-slider', $params);
        Html_Form::addOnReady($js);

        $defVal = is_array($value) ? _('From').' '.$value[0].' '._('to').' '.$value[1] : '';
        
        $html = '<div class="bz-form-row clearfix">';
        $html .= $hidden;
        $html .= '  <label class="bz-form-label" for="'.$this->id().'">'.$this->label().'</label>';
        $html .= '  <div id="'.$this->id().'-res">'.$defVal.'</div>';
        $html .= '  <div id="'.$this->id().'-slider" >';//' . $this->getAttributesString() . '
        $html .= '      <div class="ui-slider-handle"></div>';
        $html .= '  </div>';
        $html .= '</div>';
        return $html;
    }
}