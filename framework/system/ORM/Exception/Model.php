<?php

class ORM_Exception_Model extends Exception_Base
{
    protected $model = null;

    public function __construct($message, $model, $innerEx = null, $code = 0)
    {
        $this->model = $model;

        parent::__construct($message, $innerEx, $code);
    }

    public function getDetails()
    {
        return 'Model: ' . get_class($this->model);
    }
}