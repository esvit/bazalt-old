<?php

class CMS_Webservice_Component extends CMS_Webservice
{
    protected $view = null;

    protected $component = null;

    /**
     * Constructor
     *
     * @param $component Component
     */
    public function __construct(CMS_Component $component)
    {
        parent::__construct();

        $this->component = $component;
        $this->view = $component->View;
    }

    /**
     * Return url of webservice
     */
    public function __getServiceScriptName()
    {
        return CMS_Mapper::urlFor(
            CMS_Webservice::COMPONENT_ROUTE_NAME, 
            array(
                'cms_component' => get_class($this->component),
                'service' => get_class($this)
            )
        );
    }
}