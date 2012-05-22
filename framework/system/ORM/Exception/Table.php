<?php

class ORM_Exception_Table extends Exception_Base
{
    protected $tableName = null;

    public function __construct($message, $tableName, $innerEx = null, $code = 0)
    {
        $this->tableName = $tableName;

        parent::__construct($message, $innerEx, $code);
    }

    public function getDetails()
    {
        return 'Table name: ' . $this->tableName;
    }
}