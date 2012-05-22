<?php

using('Framework.Vendors.KCaptcha');

class Html_Custom_Captcha extends Html_Element_Input
{
    public function __construct($name, $attributes = array())
    {
        $this->validAttribute('minutesRange');
        $this->validAttribute('ampm');
        if(!isset($attributes['minutesRange'])) {
            $attributes['minutesRange'] = 15;
        }
        parent::__construct($name, $attributes);
    }

    private function _init()
    {
        if (!empty($this->placeholder)) {
            $this->addOption($this->placeholder, '');
        } else {
            $this->addOption(' - ', '');
        }
        
            for($i=0; $i<=23; $i++) {
                $h = sprintf('%02.0f', $i);
                if(isset($this->attributes['ampm']) && $this->attributes['ampm'] && $i > 12) {
                    $h = sprintf('%02.0f', ($i-12));
                }
                for($j=0; $j<60; $j=$j+$this->attributes['minutesRange']) {
                    $time = $h.':'.sprintf('%02.0f', $j);
                    if(isset($this->attributes['ampm']) && $this->attributes['ampm']) {
                        if($i > 12) {
                            $time .= ' pm';
                        } else {
                            $time .= ' am';
                        }
                    }
                    $this->addOption($time, sprintf('%02.0f', $i).':'.sprintf('%02.0f', $j));
                }
            }
    }
    public function placeholder($placeholder = null)
    {
        if ($placeholder != null) {
            $this->placeholder = htmlentities($placeholder, ENT_QUOTES, 'UTF-8');
            return $this;
        }
        return $this->placeholder;
    }

    public function validate()
    {
        $this->_init();
        return parent::validate();
    }
    
    public function toString()
    {
        if(!$this->form->isPostBack()) {
            $this->_init();
        }
        
        return parent::toString();
    }
}
