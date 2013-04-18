<?php

namespace Framework\CMS\Model;

use Framework\CMS as CMS;

class WidgetInstance extends Base\WidgetInstance
{
    public function getName()
    {
        return $this->Widget->title;
    }

    public static function create()
    {
        $widget = new WidgetInstance();
        $widget->site_id = CMS\Bazalt::getSiteId();

        return $widget;
    }

    public function getWidgetInstance()
    {
        return CMS\Widget::getWidgetInstance($this);
    }
}