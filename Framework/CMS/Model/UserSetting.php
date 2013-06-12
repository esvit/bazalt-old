<?php

namespace Framework\CMS\Model;
use Bazalt\ORM,
    Framework\CMS as CMS;

class UserSetting extends Base\UserSetting
{
    /**
     * create new settings object
     */
    public static function create(User $user, $name)
    {
        $setting = new UserSetting();
        $setting->user_id = $user->id;
        $setting->setting = $name;

        return $setting;
    }

    /**
     * Повертає об'єкт налаштування користувача
     */
    public static function getUserSetting(User $user, $name)
    {
        $q = ORM::select('Framework\CMS\Model\UserSetting s')
                ->where('s.user_id = ?', $user->id)
                ->andWhere('s.setting = ?', $name);

        return $q->fetch();
    }

    /**
     * remove user setting
     */
    public static function removeUserSetting(User $user, $name)
    {
        $q = ORM::delete('Framework\CMS\Model\UserSetting u')
                ->where('u.user_id = ?', $user->id)
                ->andWhere('u.setting = ?', $name);
        $q->exec();
    }

    public static function isSettingValUnique($name, $val)
    {
        $q = ORM::select('Framework\CMS\Model\UserSetting s', 'COUNT(*) as count')
                ->where('s.setting = ?', $name)
                ->andWhere('s.value = ?', $val);

        return (float)$q->fetch('stdClass')->count == 0;
    }
}
