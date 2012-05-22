<?php

class CMS_Model_Guest extends CMS_Model_User
{
    protected $hasName = true;

    public static function getUser($id, $sessionId)
    {
        $user = new CMS_Model_Guest();
        $user->id = $user->setting('id');
        $user->login = 'guest_' . $id;
        $user->firstname = $user->setting('firstname');
        $user->password = $user->setting('password');

        if (empty($user->firstname)) {
            $user->firstname = __('Guest', 'CMS');
            $user->hasName = false;
        }
        if (empty($user->password)) {
            $user->password = CMS_User::generateRandomPassword();
            $user->setting('password', $user->password);
        }
        $user->password = CMS_User::criptPassword($user->password);
        $user->session_id = $sessionId;

        return $user;
    }

    public function getRolesKey()
    {
        return 'guest_' . parent::getRolesKey();
    }

    public function hasName()
    {
        return $this->hasName;
    }

    public function getRoles()
    {
        return CMS_Model_Role::getGuestRoles();
    }

    /**
     * Щоб випадково не зберегли гостя
     */
    public function save()
    {
        throw new Exception('Can\'t save guest account');
    }

    /**
     * Зберегти гостя як юзера в БД
     */
    public function saveAsUser()
    {
        unset($this->id);
        parent::save();

        $this->setting('id', $this->id);

        if (!$this->id) {
            return null;
        }
        $user = CMS_Model_User::getById($this->id);
        return $user;
    }

    /**
     * Повертає чи встановлює налаштування гостя
     */
    public function setting($name, $value = null, $default = null)
    {
        $settingName = 'guestSetting_' . $name;
        if ($value !== null) {
            Session::Singleton()->{$settingName} = $value;
        }
        if (isset(Session::Singleton()->{$settingName})) {
            return Session::Singleton()->{$settingName};
        }
        return $default;
    }
    
    public function isGuest()
    {
        return true;
    }
}
