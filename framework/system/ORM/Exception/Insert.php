<?php

class ORM_Exception_Insert extends Exception_Base
{
    protected $builder = null;

    public function __construct($message, ORM_Query_Insert $builder, $innerEx = null, $code = 0)
    {
        $this->builder = $builder;

        parent::__construct($message, $innerEx, $code);
    }

    public function getDetails()
    {
        return 'Builder: ' . get_class($this->builder);
    }
}