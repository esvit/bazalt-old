<?php

namespace Framework\CMS\Model;

use Framework\CMS as CMS;

class Theme extends Base\Theme
{
    public static function getById($theme_id)
    {
        $q = Theme::select()
                  ->where('id = ?', $theme_id);

        return $q->fetch();
    }

    public function toArray()
    {
        $res = parent::toArray();
        if (empty($res['settings'])) {
            $res['settings'] = new \stdClass();
        }
        return $res;
    }
}