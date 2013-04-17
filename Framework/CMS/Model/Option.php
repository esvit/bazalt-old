<?php

namespace Framework\CMS\Model;

use Framework\CMS as CMS;

class Option extends Base\Option
{
    public static function set($name, $value, $componentId = null, $siteId = null)
    {
        $res = Option::get($name, $siteId);
        if ($siteId == null) {
            $siteId = CMS\Bazalt::getSiteId();
        }

        if ($res == null || $res->site_id != $siteId) {
            $res = new Option();
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
        if ($siteId == null) {
            $siteId = CMS\Bazalt::getSiteId();
        }

        $q = Option::select()
                   ->where('name = ?', $name)
                   ->andWhere('site_id = ?', $siteId);

        $opt = $q->fetch();

        if (!$opt) {
            $q = Option::select()->where('name = ?', $name)
                                 ->andWhere('site_id IS NULL');

            $opt = $q->fetch();
        }
        return $opt;
    }

    public static function getSiteOptions($siteId = null)
    {
        if ($siteId == null) {
            $siteId = CMS\Bazalt::getSiteId();
        }

        $q = Option::select()
                   ->where('site_id IS NULL OR site_id = ?', $siteId)
                   ->orderBy('site_id');

        return $q->fetchAll();
    }
}