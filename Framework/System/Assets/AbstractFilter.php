<?php

namespace Framework\System\Assets;

abstract class AbstractFilter
{
    protected $assets = null;

    protected $config = null;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function attach(array $assets)
    {
        $this->assets = $assets;
    }

    abstract function prepareFiles(array $files);

    abstract function modifyFiles(array $files);
}