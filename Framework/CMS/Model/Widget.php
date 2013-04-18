<?php

namespace Framework\CMS\Model;

use Framework\System\ORM\ORM,
    Framework\CMS as CMS;

class Widget extends Base\Widget
{
    public static function getActiveWidgets()
    {
        $q = self::getActiveCollection();

        /**
         * @var $widgets CMS\Model_Widget[]
         */
        $widgets = $q->fetchAll();
        foreach ($widgets as $k => $widget) {
            $config = $widget->getEmptyWidget();
            if (!$config->isAvaliable()) {
                unset($widgets[$k]);
            }
        }
        return $widgets;
    }

    /**
     * @return CMS\ORM_Collection
     */
    public static function getActiveCollection()
    {
        $q = ORM::select('Framework\CMS\Model\Widget w', 'w.*')
                ->innerJoin('Framework\CMS\Model\Component c', array('id', 'w.component_id OR w.component_id IS NULL'))
                ->where('w.is_active = ?', 1)
                ->andWhere('c.is_active = ?', 1)
                ->andWhere('(w.site_id = ? OR w.site_id IS NULL)', CMS\Bazalt::getSiteId())
                ->groupBy('w.id');
        
        if (ENABLE_MULTISITING) {
            $q->innerJoin('Framework\CMS\Model\ComponentRefSite ref', array('component_id', 'c.id'))
              ->andWhere('ref.site_id = ?', CMS\Bazalt::getSiteId());
        }

        return new CMS\ORM\Collection($q);
    }

    public static function getByClassName($className)
    {
        $q = Widget::select()
                ->where('LOWER(className) = ?', strToLower($className))
                ->limit(1);

        return $q->fetch();
    }

    public static function getPathByName($className)
    {
        $widget = self::getByClassName($className);
        if(!$widget) {
            return null;
        }
        return dirname(Framework\Core\Autoload::getFilename($widget->className));
    }

    public static function getInstancesForTemplate($template, $position)
    {
        $q = ORM::select('Framework\CMS\Model\WidgetInstance c', 'c.*')
                ->innerJoin('Framework\CMS\Model\Widget w', array('id', 'c.widget_id'))
                ->where('c.template = ?', $template)
                ->andWhere('c.position = ?', $position)
                ->andWhere('c.site_id = ?', CMS\Bazalt::getSiteId())
                ->andWhere('w.is_active = 1')
                ->orderBy('c.`order`');

        if (!CMS\User::get()->hasRight(null, CMS\Bazalt::ACL_CAN_ADMIN_WIDGETS)) {
            $q->andWhere('c.publish = 1');
        }

        return $q->fetchAll();
    }

    public static function getWidgets()
    {
        $q = Widget::select();

        return $q->fetchAll();
    }

    public function getEmptyConfig()
    {
        $config = WidgetInstance::create();
        $config->widget_id = $this->id;

        return $config;
    }

    public function getEmptyWidget()
    {
        $config = $this->getEmptyConfig();
        $widget = $config->getWidgetInstance();

        return $widget;
    }
}
