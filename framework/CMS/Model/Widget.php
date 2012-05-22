<?php

class CMS_Model_Widget extends CMS_Model_Base_Widget
{
    public static function getActiveWidgets()
    {
        $q = self::getActiveCollection();

        return $q->fetchAll();
    }

    public static function getActiveCollection()
    {
        $q = CMS_Model_Widget::select()
                ->where('is_active = ?', 1)
                ->andWhere('(site_id = ? OR site_id IS NULL)', CMS_Bazalt::getSiteId());

        return new CMS_ORM_Collection($q);
    }

    public static function getPathByName($className)
    {
        $q = CMS_Model_Widget::select()
                ->where('LOWER(className) = ?', strToLower($className))
                ->limit(1);

        $widget = $q->fetch();
        if(!$widget) {
            return null;
        }
        return dirname(Core_Autoload::getFilename($widget->className));
    }

    public static function getInstancesForTemplate($template, $position)
    {
        $q = ORM::select('CMS_Model_WidgetInstance c', 'c.*, w.title AS `name`')
                        ->innerJoin('CMS_Model_Widget w', array('id', 'c.widget_id'))
                        ->where('c.template = ?', $template)
                        ->andWhere('c.position = ?', $position)
                        ->andWhere('c.site_id = ?', CMS_Bazalt::getSiteId())
                        ->orderBy('c.`order`');

        return $q->fetchAll();
    }

    public function getEmptyConfig()
    {
        $config = new CMS_Model_WidgetInstance();
        $config->widget_id = $this->id;
        $config->site_id = CMS_Bazalt::getSiteId();
        return $config;
    }
}
