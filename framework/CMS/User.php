<?php

class CMS_User extends Object implements CMS_IUser
{
    const DENY_LOGINS_OPTION = 'CMS.BannedLogins';
    
    const SPLIT_ROLES_OPTION = 'CMS.SplitRoles';

    protected static $currentUser = null;

    public $eventOnUserLogin = Event::EMPTY_EVENT;

    public $eventOnUserLogout = Event::EMPTY_EVENT;

    /**
     * Add webservice route 
     *
     * @param CMS_Mapper $router Mapper
     *
     * @return void
     */
    public static function initWebserviceRoutes($router)
    {
        $router->connect('/login/', array('action' => 'login', 'controller' => 'CMS_Controller_User'))
               ->name('CMS.Login')
               ->noIndex()
               ->noFollow();

        $router->connect('/logout/', array('action' => 'logout', 'controller' => 'CMS_Controller_User'))
               ->name('CMS.Logout')
               ->noIndex()
               ->noFollow();

        $router->connect('/profile/', array('action' => 'profile', 'controller' => 'CMS_Controller_User'))
               ->name('CMS.Profile')
               ->noIndex()
               ->noFollow()
               ->authorizationRequest();

        $router->connect('/upload/', array('action' => 'upload', 'controller' => 'CMS_Controller_User'))
               ->name('CMS.Upload');
               
        $router->connect('/captcha/{element}', array('action' => 'captcha', 'controller' => 'CMS_Controller_User'))
               ->name('CMS.Captcha');

        if (!CMS_Option::get(CMS_User::SPLIT_ROLES_OPTION, true)) {
            $router->connect('/user/role/{roleId}', array('action' => 'setRole', 'controller' => 'CMS_Controller_User'))
                   ->name('CMS.UserRole');
        }
    }

    public static function getUserSessionLifetime()
    {
        // 30 days
        return 30 * 24 * 60 * 60;
    }

    public static function getUser()
    {
        if (!self::$currentUser && Session::Singleton()->cmsUser) {
            $user = CMS_Model_User::getByIdAndSession((int)Session::Singleton()->cmsUser, Session::Singleton()->getSessionId());

            if ($user && ($_COOKIE['authorization_token'] == self::getAuthorizationToken())) {
                self::$currentUser = $user;
            } else {
                self::logout();
            }
            if (self::$currentUser) {
                self::$currentUser->updateLastActivity();
            }
        }
        if (!self::$currentUser) {
            self::$currentUser = self::getGuest();
        }
        return self::$currentUser;
    }

    public static function getAuthorizationToken()
    {
        return Session::Singleton()->authorization_token;
    }

    public static function setUser(CMS_Model_User $user, $remember = true)
    {
        if (!$user->is_active) {
            return self::getGuest();
        }
        Session::Singleton()->regenerateSessionId();
        self::$currentUser = $user;

        $user->session_id = Session::Singleton()->getSessionId();
        $user->updateLastActivity();

        self::setGuestId($user->session_id);

        $token = $user->getAuthorizationToken();
        Session::Singleton()->cmsUser = $user->id;
        Session::Singleton()->authorization_token = $token;

        $cacheKey = $user->getRolesKey();
        StaticFiles::setCacheSalt($cacheKey);

        $lifetime = $remember ? (time() + self::getUserSessionLifetime()) : 0;

        $_COOKIE['authorization_token'] = $token;
        setcookie('authorization_token', $_COOKIE['authorization_token'], $lifetime, '/', DataType_Url::getCookieDomain(), false, true);

        return $user;
    }
    
    public static function getGuest()
    {
        if (isset($_COOKIE['GuestId'])) {
            $guestId = $_COOKIE['GuestId'];
        } else {
            $guestId = Session::Singleton()->getSessionId();
            self::setGuestId($guestId);
        }
        
        $guest = CMS_Model_Guest::getUser($guestId, Session::Singleton()->getSessionId());
        return $guest;
    }

    protected static function setGuestId($guestId)
    {
        $_COOKIE['GuestId'] = $guestId;
        setcookie('GuestId', $guestId, (time() + self::getUserSessionLifetime()), '/', DataType_Url::getCookieDomain(), false, false);
    }
    
    public static function isLogined()
    {
        return self::$currentUser != null && !(self::$currentUser instanceof CMS_Model_Guest);
    }
    
    public static function criptPassword($password)
    {
        return hash('sha512', $password);
    }    

    public static function login($login, $origPassword, $remeber = true)
    {
        $login = strToLower(trim($login));
        $password = self::criptPassword(trim($origPassword));

        $user = CMS_Model_User::getUserByLoginPassword($login, $password);
        if ($user) {
            $user->session_id = Session::Singleton()->getSessionId();
            Event::trigger('CMS_User', 'OnUserLogin', array($user));
            return self::setUser($user, $remeber);
        }
        return null;
    }

    public static function generateRandomPassword($length = 6, $symbols = 'abcdefghijklmnoprstuvxyzABCDEFGHIJKLMNOPRSTUVXYZ1234567890')
    {
        $symbolsArray = str_split($symbols, 1);
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $symbolsArray[rand(0, count($symbolsArray) - 1)];
        }
        return $password;
    }

    public static function isValidUserLogin($login)
    {
        $logins = explode(',', CMS_Option::get(self::DENY_LOGINS_OPTION,''));
        foreach ($logins as $k => $l) {
            $logins[$k] = strToLower(trim($l));
        }
        if (in_array(strToLower($login), $logins)) {
            return __('The use of this login is forbidden', 'CMS');
        }
        $loginLenth = strlen($login);
        if ($loginLenth < 4 || $loginLenth > 60 || !preg_match('/^([a-z])([a-z0-9._-]+)$/i', $login)) {
            return sprintf(__('Login must be between %d and %d characters. It must start with a letter and can contain only letters, numbers, and the following punctuation marks: full stop (.), dash (-), underscore (_)', 'CMS'), 4, 60);
        }
        $user = CMS_Model_User::getUserByLogin($login);
        if ($user) {
            return __('This login already in use', 'CMS');
        }
        return true;
    }

    public static function logout()
    {
        $_COOKIE['authorization_token'] = null;
        setcookie('authorization_token', '', time() - 3600, '/', DataType_Url::getCookieDomain(), false, false);

        Event::trigger('CMS_User', 'OnUserLogout', array(self::$currentUser));

        self::$currentUser = null;
        unset(Session::Singleton()->cmsUser);
        unset(Session::Singleton()->authorization_token);
        unset(Session::Singleton()->currentRoleId);
        Session::Singleton()->regenerateSessionId();
    }
    
    public static function getCurrentRole()
    {
        $user = self::getUser();
        if (isset(Session::Singleton()->currentRoleId)) {
            $curRole = CMS_Model_Role::getById((int)Session::Singleton()->currentRoleId);
            if(!$curRole) {
                return null;
            }
            $user = self::getUser();
            foreach ($user->Roles as $role) {
                if ($role->id == $curRole->id) {
                    return $role;
                }
            }
        }
        return current($user->Roles->get());
    }
    
    public static function setCurrentRole($roleId)
    {
        $curRole = CMS_Model_Role::getById((int)$roleId);
        if($curRole) {
            $user = self::getUser();
            foreach ($user->Roles as $role) {
                if ($role->id == $curRole->id) {
                    Session::Singleton()->currentRoleId = $role->id;
                    return true;
                }
            }
        }
        return false;
    }
}