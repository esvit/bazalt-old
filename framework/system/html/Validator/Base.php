<?php

abstract class Html_Validator_Base
{
    protected $element = null;

    protected $errorMessage = null;

    protected $config = array();

    public function __construct(Html_FormElement $element, $config = array())
    {
        $this->element = $element;
        $this->config = $config;

        if (array_key_exists('errorMessage', $config)) {
            $this->errorMessage = $config['errorMessage'];
        }
    }

    public function getErrorMessage()
    {
        $params = array(
            'label' => $this->element->label()
        );
        return DataType_String::replaceConstants($this->errorMessage, $params);
    }

    public function validate(Html_FormElement $el, Html_Form $form)
    {

    }
}