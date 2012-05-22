<?php

class CMS_Model_UserSetting extends CMS_Model_Base_UserSetting
{
    /**
     * create new settings object
     */
    public static function create(CMS_Model_User $user, $name)
    {
        $setting = new CMS_Model_UserSetting();
        $setting->user_id = $user->id;
        $setting->setting = $name;

        return $setting;
    }

    /**
     * Повертає об'єкт налаштування користувача
     */
    public static function getUserSetting(CMS_Model_User $user, $name)
    {
        $q = ORM::select('CMS_Model_UserSetting s')
                ->where('s.user_id = ?', $user->id)
                ->andWhere('s.setting = ?', $name);

        return $q->fetch();
    }

    /**
     * remove user setting
     */
    public static function removeUserSetting(CMS_Model_User $user, $name)
    {
        $q = ORM::delete('CMS_Model_UserSetting u')
                ->where('u.user_id = ?', $user->id)
                ->andWhere('u.setting = ?', $name);
        $q->exec();
    }

    public static function isSettingValUnique($name, $val)
    {
        $q = ORM::select('CMS_Model_UserSetting s', 'COUNT(*) as count')
                ->where('s.setting = ?', $name)
                ->andWhere('s.value = ?', $val);

        return (float)$q->fetch('stdClass')->count == 0;
    }
}
