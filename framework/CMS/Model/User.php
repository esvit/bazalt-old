<?php

class CMS_Model_User extends CMS_Model_Base_User implements CMS_Search_ISearchable
{
    protected $systemAcl = null;

    protected $componentsAcl = array();

    public static function create()
    {
        $user = new CMS_Model_User();

        return $user;
    }

    public function getRolesKey()
    {
        $acls = CMS_Model_Role::getUserAcl($this);

        $keyName = '';
        foreach ($acls as $role) {
            $keyName .= '(' . $role->acl . '-' . $role->component_id . ')';
        }
        return $keyName;
    }

    /**
     * Get user by id and session id
     */
    public static function getByIdAndSession($id, $sessionId = null)
    {
        $q = ORM::select('CMS_Model_User u')
                ->leftJoin('CMS_Model_SiteRefUser ref', array('user_id', 'u.id'))
                ->where('ref.user_id = ?', $id)
                ->andWhere('ref.site_id = ?', CMS_Bazalt::getSiteID());

        if ($sessionId != null) {
            $q->andWhere('ref.session_id = ?', $sessionId);
        }
        return $q->noCache()->fetch();
    }

    public function getAuthorizationToken()
    {
        $sid = Session::Singleton()->getSessionId();
        return md5($this->id . $sid . CMS_Bazalt::getSecretKey() . time());
    }

    /**
     * Get access bit mask for component or system
     */
    protected function getRoleBitmask($component = null)
    {
        if ($component == null) {
            if ($this->systemAcl === null) {
                $res = 0;
                foreach ($this->getRoles() as $role) {
                    $res |= $role->system_acl;
                }
                $this->systemAcl = $res;
            }
            return $this->systemAcl;
        } else if (!isset($this->componentsAcl[$component->id])) {
            $roles = array();
            foreach ($this->getRoles() as $role) {
                $roles []= $role->id;
            }

            // no roles - no rights
            if (count($roles) == 0) {
                return 0;
            }

            $acl = CMS_Model_Role::getBitmask($roles, $component);
            $this->componentsAcl[$component->id] = $acl;
        }
        return $this->componentsAcl[$component->id];
    }
    
    public function getRoles()
    {
        $splitRoles = CMS_Option::get(CMS_User::SPLIT_ROLES_OPTION, true);
        if ($splitRoles) {
            return $this->Roles->get();
        } else {
            $roles = CMS_Model_Role::getGuestRoles();
            $currentRole = CMS_User::getCurrentRole();
            if($currentRole) {
                $roles []= $currentRole;
            }
            return $roles;
        }
    }

    /**
     * Check if user has rights
     */
    public function hasRight($component = null, $roleValue)
    {
        if ($component != null && $this->hasRight(null, CMS_Bazalt::ACL_GODMODE)) {
            return true;
        }
        if ($component != null && !($component instanceof CMS_Model_Component)) {
            $component = CMS_Model_Component::getComponent(is_object($component) ? get_class($component) : $component);
        }
        return ($roleValue & $this->getRoleBitmask($component)) != 0;
    }

    /**
     * Повертає чи встановлює налаштування користувача
     */
    public function setting($name, $value = null, $default = null)
    {
        $setting = CMS_Model_UserSetting::getUserSetting($this, $name);
        if (!$setting && $value === null) {
            return $default;
        }
        if ($value !== null) {
            if (!$setting) {
                $setting = CMS_Model_UserSetting::create($this, $name);
            }
            $setting->value = $value;
            $setting->save();
        }
        return $setting->value;
    }

    public static function getUserWithSetting($settingName, $settingValue, $active = null)
    {
        $q = ORM::select('CMS_Model_User u')
                ->innerJoin('CMS_Model_UserSetting ref', array('user_id', 'u.id'))
                ->where('ref.setting = ?', $settingName)
                ->andWhere('ref.value = ?', $settingValue);

        if ($active !== null) {
            $q->andWhere('u.is_active = ?', $active ? 1 : 0);
        }
        return $q->fetchAll();
    }

    public function toArray()
    {
        $ret = $this->values;
        unset($ret['password']);
        $ret['roles'] = array();
        foreach ($this->Roles as $role) {
            $ret['roles'][] = $role->id;
        }
        return $ret;
    }

    public static function getUserByLogin($login, $onlyPublish = false)
    {
        $q = ORM::select('CMS_Model_User u')
                ->where('login = ?', $login);

        if ($onlyPublish) {
            $q->andWhere('is_active = ?', 1);
        }
        return $q->fetch();
    }

    public static function getUserByLoginPassword($login, $password)
    {
        $q = ORM::select('CMS_Model_User u')
                ->where('login = ?', $login)
                ->andWhere('password = ?', $password)
                ->andWhere('is_active = ?', 1);

        return $q->fetch();
    }
    
    public static function getUserByEmail($email, $onlyPublish = false)
    {
        $q = ORM::select('CMS_Model_User u')
                ->where('email = ?', $email);

        if ($onlyPublish) {
            $q->andWhere('is_active = ?', 1);
        }
        return $q->fetch();
    }

    public static function getByLoginAndEmail($login, $email, $onlyPublish = false)
    {
        $q = ORM::select('CMS_Model_User u')
                ->where('login = ?', $login)
                ->andWhere('email = ?', $email);

        if ($onlyPublish) {
            $q->andWhere('is_active = ?', 1);
        }
        return $q->fetch();
    }

    public static function getUsersCountFromDate($date = null)
    {
        $q = CMS_Model_User::select();
        if ($date != null) {
            $q->where('reg_date > FROM_UNIXTIME(?)', $date);
        }
        return $q->exec();
    }

    public function getActivationKey()
    {
        return md5($this->login . $this->password . CMS_Bazalt::getSecretKey());
    }

    public function getRemindKey()
    {
        return md5($this->login . $this->email . CMS_Bazalt::getSecretKey());
    }

    /**
     * remove user setting
     */
    public function removeSetting($name)
    {
        CMS_Model_UserSetting::removeUserSetting($this, $name);
    }

    public function setPhoto($filename)
    {
        $this->setting('photo', $filename);
    }

    public function getPhoto($size = null)
    {
        $photo = $this->setting('photo');

        if (!empty($photo) && $size != null) {
            return CMS_Image::getThumb($photo, $size);
        }
        return $photo;
    }

    public function setAvatar($filename)
    {
        $this->setting('avatar', $filename);
    }
    
    public function getAvatar($size = null)
    {
        $avatar = $this->setting('avatar');
        if (empty($avatar)) {
            $avatar = '/uploads/default_avatar.jpg';
            $this->setting('avatar', $avatar);
        }

        if (!empty($avatar) && $size != null) {
            return CMS_Image::getThumb($avatar, $size);
        }
        return $avatar;
    }

    public function getName()
    {
        $name = trim($this->secondname . ' ' . $this->firstname . ' ' . $this->patronymic);
        if (empty($name)) {
            $name = $this->login;
        }
        return $name;
    }
    
    public static function getOnlineUsers()
    {
        $p = (int)CMS_Option::get(CMS_Bazalt::ONLINEPERIOD_OPTION, 5);
        $q = CMS_Model_User::select()
            ->where('last_activity BETWEEN ? AND ?', array(
                date('Y-m-d H:i:s', strtotime('now -' . $p . ' minutes')),
                date('Y-m-d H:i:s', strtotime('now +' . $p . ' minutes'))
            ))
            ->noCache();
        return $q->fetchAll();
    }
    
    public function login()
    {
        if (!$this->isGuest()) {
            CMS_User::setUser($this);
        }
    }

    public function updateLastActivity($time = null)
    {
        if ($time == null) {
            $time = time();
        }
        $q = CMS_Model_SiteRefUser::select()
                ->where('user_id = ?', $this->id)
                ->andWhere('site_id = ?', CMS_Bazalt::getSiteID());

        $activity = $q->fetch();
        if (!$activity) {
            $activity = new CMS_Model_SiteRefUser();
            $activity->site_id = CMS_Bazalt::getSiteID();
            $activity->user_id = $this->id;
        }
        $activity->session_id = Session::Singleton()->getSessionId();
        $activity->last_activity = date('Y-m-d H:i:s', $time);
        $activity->save();
        
        ORM::update('CMS_Model_User')
           ->set('last_activity', date('Y-m-d H:i:s', $time))
           ->where('id = ?', $this->id)
           ->autoClearCache(false)
           ->exec(false);
    }

    public function isGuest()
    {
        return false;
    }

    public static function getSearchCollection()
    {
        $q = ORM::select('CMS_Model_User u')
                ->where('is_active = ?', 1);

        return new CMS_ORM_Collection($q);
    }

    public function toSearchIndex()
    {
        return parent::toSearchIndex();
    }

    public static function getSearchFields()
    {
        self::disableSearchField(__CLASS__, 'password');
        return parent::getModelSearchFields(__CLASS__);
    }

    public function getSearchType()
    {
        return 'user';
    }
}
