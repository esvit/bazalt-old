<?php

abstract class Assets_Filter_Abstract
{
    protected $assets = null;

    protected $config = null;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function attach(Assets_File $assets)
    {
        $this->assets = $assets;
    }

    abstract function prepareFiles(array $files);

    abstract function modifyFiles(array $files);
}