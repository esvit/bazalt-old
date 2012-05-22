<?php

class CMS_Model_Theme extends CMS_Model_Base_Theme
{
    public static function getByAlias($alias)
    {
        $q = CMS_Model_Theme::select()
                ->where('alias = ?', $alias)
                ->limit(1);

        return $q->fetch();
    }
}
