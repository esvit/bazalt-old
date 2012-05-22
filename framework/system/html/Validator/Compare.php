<?php

class Html_Validator_Compare extends Html_Validator_Require
{
    protected $element = null;

    protected $compareElement = null;

    public function __construct(Html_FormElement $element, Html_FormElement $compareElement,  $config = array())
    {
        if (!array_key_exists('errorMessage', $config)) {
            $config['errorMessage'] =sprintf(__('"%s" should be equal to "%s"', ''), $element->label(), $compareElement->label());
        }
        parent::__construct($element, $config);
        $this->compareElement = $compareElement;
        if (!$compareElement) {
            throw new Exception('Invalid second argument');
        }
        //HtmlForm::addOnReady('qf.rules.nonempty(' . $this->element->javascriptValue(). ', {});');
    }

    public function validate(Html_FormElement $el, Html_Form $form)
    {
        $value = $el->value();
        $value2 = $this->compareElement->value();

        $res = ($value == $value2);
        if (!$res){
            $el->addError($el->name() . __CLASS__, $this->getErrorMessage());
            $form->addError($el->name() . __CLASS__, $this->getErrorMessage());
        }
        return $res;
    }
}
