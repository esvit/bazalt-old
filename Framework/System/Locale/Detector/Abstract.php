<?php

abstract class Locale_Detector_Abstract
{
    protected $options = array();

    public function __construct($options = array())
    {
        $this->options = $options;
    }

    abstract function detectLocale();
}