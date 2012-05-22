<?php

class CMS_Model_WidgetInstance extends CMS_Model_Base_WidgetInstance
{
    public static function create()
    {
        $widget = new CMS_Model_WidgetInstance();
        $widget->site_id = CMS_Bazalt::getSiteId();

        return $widget;
    }

    public function getWidgetSettings()
    {
        $inst = $this->getWidgetInstance();
        if ($inst != null) {
            return $inst->getConfigPage();
        }
    }

    public function getWidgetInstance()
    {
        return CMS_Widget::getWidgetInstance($this);
    }
}