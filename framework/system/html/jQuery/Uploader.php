<?php

class Html_jQuery_Uploader extends Html_FormElement
{
    const DEFAULT_CSS_CLASS = 'bz-form-jquery-uploader';

    public function __construct($name, $attributes = array())
    {
        parent::__construct($name, $attributes);

        $this->validAttribute('size', 'string');
        $this->validAttribute('allowed_extensions', 'array');
        $this->validAttribute('preview', 'bool');

        $this->template = 'elements/jquery/uploader';

        $this->addClass(self::DEFAULT_CSS_CLASS);
    }

    /*public function __construct($name, $attributes = array())
    {
        $this->validAttribute('size');
        $this->validAttribute('allowed_extensions');
        $this->validAttribute('preview');
        
        if(!isset($attributes['size'])) {
            $attributes['size'] = 'uploader_preview';
        }
        if(!isset($attributes['preview'])) {
            $attributes['preview'] = true;
        }
        if(!isset($attributes['allowed_extensions'])) {
            $attributes['allowed_extensions'] = array('jpg', 'jpeg', 'png', 'gif');
        }
        parent::__construct($name, $attributes);
    }*/
}