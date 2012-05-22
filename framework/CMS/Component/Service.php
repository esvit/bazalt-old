<?php

abstract class CMS_Component_Service extends CMS_Service_Base
{
    protected $component;

    protected $view;

    /**
     * Constructor
     *
     * @param array         $config    Service configuration
     */
    public function __construct(array $config)
    {
        $this->component = $config['component'];
        if (!$this->component instanceof CMS_Component) {
            throw new Exception('Invalid service component');
        }
        unset($config['component']);
        $this->view = $this->component->View;

        parent::__construct($config);
    }
}