<?php

abstract class Html_Filter_Base
{
    protected $element = null;

    protected $config = array();

    public function __construct($config = array())
    {
        $this->config = $config;
    }

    public function setElement($element)
    {
        $this->element = $element;
    }

    abstract function runFilter($element, $value);
}