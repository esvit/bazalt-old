<?php

namespace Framework\CMS\Model;

use Framework\CMS as CMS;

class Component extends CMS\Model\Base\Component
{
    use CMS\ORM\LocalizableTrait;

    public static function create($name, $title)
    {
        $com = self::getByName($name);
        if ($com) {
            throw new \Exception('Component "' . $name . '" already exsists');
        }
        $com = new Component();
        $com->name = $name;
        $com->title = $title;

        return $com;
    }

    public static function getActiveComponents()
    {
        $q = Component::select()
                          ->where('is_active = ?', 1);

        return $q->fetchAll();
    }

    public static function getComponentsForSite($siteId = null)
    {
        if ($siteId == null) {
            $siteId = CMS\Bazalt::getSiteId();
        }

        $q = Component::select()
                          ->where('is_active = ?', 1);

        if (ENABLE_MULTISITING) {
            $q->innerJoin('\Framework\CMS\Model\ComponentRefSite ref', array('component_id', 'id'))
              ->andWhere('ref.site_id = ?', intval($siteId));
        }
        return $q->fetchAll();
    }

    public function isEnable()
    {
        return $this->Sites->has(CMS\Bazalt::getSite());
    }

    /**
     * Видаляє компонент на сайті
     */
    public function disable($site = null)
    {
        if (!$site) {
            $site = CMS\Bazalt::getSite();
        }
        $this->Sites->remove($site);
    }

    /**
     * Додає компонент на сайт
     */
    public function enable($site = null)
    {
        if (!$site) {
            $site = CMS\Bazalt::getSite();
        }
        if ($this->dependencies) {
            $deps = explode(',', $this->dependencies);
            foreach ($deps as $dep) {
                $component = Component::getByName($dep);
                $component->enable();
            }
        }
        $this->Sites->add(CMS\Bazalt::getSite());
    }

    public function toArray()
    {
        $res = array(
            'id' => $this->id,
            'title' => $this->title,
            'is_active' => $this->is_active,
            'widgets' => array()
        );
        foreach ($this->Widgets->get() as $widget) {
            $res['widgets'][] = $widget->toArray();
        }
        return $res;
    }

    public static function getByName($name)
    {
        $q = Component::select()->where('name = ?', $name);

        return $q->fetch();
    }

    public static function getComponent($name)
    {
        return self::getByName($name);
    }
}