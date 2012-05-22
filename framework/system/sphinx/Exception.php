<?php

class Sphinx_Exception extends Exception_Base
{
    protected $detailError = null;

    public function __construct($message, $innerEx = null, $code = 0)
    {
        if (preg_match('#(.*)\(errno=(\d+), msg=(.*)\)#', $message, $matches)) {
            $code = $matches[2];
            $message = $matches[3];

            $this->detailError = $matches[1];
        }

        parent::__construct($message, $innerEx, $code);
    }

    public function getDetails()
    {
        $details = '';
        if (!empty($this->detailError)) {
            $details = 'Parent message: ' . $this->detailError . "\n";
        }
        return $details;
    }
}