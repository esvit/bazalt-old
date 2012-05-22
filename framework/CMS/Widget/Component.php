<?php

class CMS_Widget_Component extends CMS_Widget
{
    protected $component;

    public function __construct(CMS_Model_WidgetInstance $config)
    {
        $this->widgetConfig = $config;
        $this->options = $config->config;

        $this->component = CMS_Bazalt::getComponent($config->Widget->Component->name);
        $this->view = $this->component->getView();
    }
}