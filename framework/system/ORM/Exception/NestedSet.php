<?php

class ORM_Exception_NestedSet extends Exception_Base
{
    protected $errors = array();

    public function __construct($messages, $innerEx = null, $code = 0)
    {
        $this->errors = $messages;
        $message = implode("\n", $messages);
        parent::__construct($message, $innerEx, $code);
    }
}