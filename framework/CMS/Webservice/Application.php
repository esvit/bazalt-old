<?php

class CMS_Webservice_Application extends CMS_Webservice
{
    protected $view = null;

    /**
     * Constructor
     *
     * @param $component Component
     */
    public function __construct()
    {
        parent::__construct();

        $this->view = CMS_Application::current()->View;
    }

    /**
     * Return url of webservice
     */
    public function __getServiceScriptName()
    {
        return CMS_Mapper::urlFor(
            CMS_Webservice::APPLICATION_ROUTE_NAME, 
            array(
                'service' => get_class($this)
            )
        );
    }
}