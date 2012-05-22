<?php

abstract class CMS_Application_Service extends CMS_Service_Base
{
    protected $application;

    protected $view;

    /**
     * Constructor
     *
     * @param array         $config    Service configuration
     */
    public function __construct(array $config)
    {
        $this->application = $config['application'];
        if (!$this->application instanceof CMS_Application) {
            throw new Exception('Invalid application in service "' . get_class($this) . '"');
        }
        unset($config['application']);
        $this->view = $this->application->View;

        parent::__construct($config);
    }
}