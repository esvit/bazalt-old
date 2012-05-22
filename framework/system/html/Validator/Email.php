<?php

class Html_Validator_Email extends Html_Validator_Require
{
    protected $element = null;

    public function __construct(Html_FormElement $element, $config = array())
    {
        if (!array_key_exists('errorMessage', $config)) {
            $config['errorMessage'] = 'Field "{label}" contains invalid e-mail';
        }
        parent::__construct($element, $config);
    }

    public function validate(Html_FormElement $el, Html_Form $form)
    {
        $value = trim($el->value());
        
        if(!$el->isRequireField() && empty($value)) {//not validate empty and NOT required fields
            return true;
        }
        $res = preg_match("/^([a-zA-Z0-9\,\-\+\.\_])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)+/", $value);
        if (!$res){
            $el->addError($el->name() . __CLASS__, $this->getErrorMessage());
            $form->addError($el->name() . __CLASS__, $this->getErrorMessage());
        }
        return $res;
    }
}