<?php

class CMS_Model_Option extends CMS_Model_Base_Option
{
    public static function set($name, $value, $componentId = null, $siteId = null)
    {
        $res = CMS_Model_Option::get($name, $siteId);
        if ($siteId == null) {
            $siteId = CMS_Bazalt::getSiteId();
        }

        if ($res == null || $res->site_id != $siteId) {
            $res = new CMS_Model_Option();
            $res->name = $name;
            $res->site_id = $siteId;
        }
        $res->value = $value;
        $res->component_id = $componentId;
        $res->save();

        return $res;
    }

    public static function get($name, $siteId = null)
    {
        $opt = false;
        if ($siteId == null) {
            $siteId = CMS_Bazalt::getSiteId();
        }

        $q = CMS_Model_Option::select()
                       ->where('name = ?', $name)
                       ->andWhere('site_id = ?', $siteId);
        $opt = $q->fetch();

        if (!$opt) {
            $q = CMS_Model_Option::select()->where('name = ?', $name)
                                           ->andWhere('site_id IS NULL');

            $opt = $q->fetch();
        }
        return $opt;
    }

    public static function getSiteOptions($siteId = null)
    {
        if ($siteId == null) {
            $siteId = CMS_Bazalt::getSiteId();
        }

        $q = CMS_Model_Option::select()
                       ->where('site_id IS NULL OR site_id = ?', $siteId)
                       ->orderBy('site_id');

        return $q->fetchAll();
    }
}