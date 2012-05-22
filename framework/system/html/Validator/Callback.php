<?php

class Html_Validator_Callback extends Html_Validator_Require
{
    protected $callback = null;

    protected $element = null;

    public function __construct(Html_FormElement $element, $config = array())
    {
        parent::__construct($element, $config);

        if (array_key_exists('callback', $config)) {
            $this->callback = $config['callback'];

            /*if (!is_callable($this->callback)) {
                throw new Exception('Invalid callback for validator');
            }*/
        }
    }

    public function validate(Html_FormElement $el, Html_Form $form)
    {
        $value = $el->value();
        $res = call_user_func($this->callback, $value);
        if ($res !== true){
            if ($res !== false) {
                $this->errorMessage = $res;
            }
            $el->addError($el->name() . __CLASS__, $this->errorMessage);
            $form->addError($el->name() . __CLASS__, $this->errorMessage);
        }
        return $res;
    }
}