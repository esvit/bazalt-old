<?php

class Html_Validator_NonEmpty extends Html_Validator_Require
{
    protected $element = null;

    public function __construct(Html_FormElement $element, $config = array())
    {
        if (!array_key_exists('errorMessage', $config)) {
            $config['errorMessage'] = 'Field "{label}" cannot be empty';
        }
        parent::__construct($element, $config);
    }

    public function validate(Html_FormElement $el, Html_Form $form)
    {
        $value = $el->value();
        if(is_array($value)) {
            $res = (count($value) > 0);
        } else {
            $res = !empty($value);
        }
        if (!$res){
            $el->addError($el->name() . __CLASS__, $this->getErrorMessage());
            $form->addError($el->name() . __CLASS__, $this->getErrorMessage());
        }
        return $res;
    }
}
