<?php

abstract class CMS_Service_Base
{
    protected $config;

    /**
     * Constructor
     *
     * @param array         $config    Service configuration
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function init($url)
    {
    }

    public function prepareUrl(&$url)
    {
    }
}