<?php

class CMS_Model_Component extends CMS_Model_Base_Component
{
    public static function getActiveComponents()
    {
        $q = CMS_Model_Component::select()
                          ->where('is_active = ?', 1);

        return $q->fetchAll();
    }

    public static function getComponentsForSite($siteId = null)
    {
        if ($siteId == null) {
            $siteId = CMS_Bazalt::getSiteId();
        }

        $q = CMS_Model_Component::select()
                          ->where('is_active = ?', 1);

        if (ENABLE_MULTISITING) {
            $q->innerJoin('CMS_Model_ComponentRefSite ref', array('component_id', 'id'))
              ->andWhere('ref.site_id = ?', intval($siteId));
        }
        return $q->fetchAll();
    }

    public function isEnable()
    {
        return $this->Sites->has(CMS_Bazalt::getSite());
    }

    public function disable()
    {
        $this->is_active = 0;
        $this->save();
    }

    public function enable()
    {
        $this->is_active = 1;
        $this->save();
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

    public static function create($name, $title)
    {
        $com = self::getByName($name);
        if ($com) {
            throw new Exception('Component "' . $name . '" already exsists');
        }
        $com = new CMS_Model_Component();
        $com->name = $name;
        $com->title = $title;
        $com->save();
        return $com;
    }

    public static function getByName($name)
    {
        $q = CMS_Model_Component::select()
                ->where('name = ?', $name);

        return $q->fetch();
    }

    public static function getComponentsWithHooks()
    {
        $q = CMS_Model_Component::select()
                ->where('is_active = ?', 1)
                ->andWhere('have_hooks = ?', 1);

        return $q->fetchAll();
    }

    public static function getComponent($name)
    {
        return self::getByName($name);
    }
}