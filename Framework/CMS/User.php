<?php

namespace Framework\CMS;

use \Framework\System\Session\Session;

class User
{
    const DENY_LOGINS_OPTION = 'CMS.BannedLogins';
    
    const SPLIT_ROLES_OPTION = 'CMS.SplitRoles';

    /**
     * Опція: роль, яка надається користувачу після логіна
     */
    const LOGIN_USER_ROLE_OPTION = 'ComUsers.RoleAfterLogin';

    protected static $currentUser = null;

    protected static $userMapper = null;

    public $eventOnUserLogin = \Framework\Core\Event::EMPTY_EVENT;

    public $eventOnUserLogout = \Framework\Core\Event::EMPTY_EVENT;

    /**
     * @return CMS_Routing_SubmapperRule
     */
    public static function getSubmapper()
    {
        return self::$userMapper;
    }

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

       self::$userMapper = $router->submapper('/user/', array('action' => 'profile', 'controller' => 'CMS_Controller_User'));

        if (!Option::get(CMS_User::SPLIT_ROLES_OPTION, true)) {
            self::$userMapper->connect('/role/{roleId}', array('action' => 'setRole', 'controller' => 'CMS_Controller_User'))
                             ->name('CMS.UserRole');
        }
    }

    public static function getUserSessionLifetime()
    {
        // 30 days
        return 30 * 24 * 60 * 60;
    }

    /**
     * @return Model\Guest|Model\User
     */
    public static function get()
    {
        $session = new Session('cms');

        if (!self::$currentUser && $session->cmsUser) {
            $user = Model\User::getByIdAndSession((int)$session->cmsUser, $session->getSessionId());

            if ($user && ($_COOKIE['authorization_token'] == self::getAuthorizationToken())) {
                self::$currentUser = $user;
            } else {
                self::logout();
            }
            if (self::$currentUser) {
                self::$currentUser->updateLastActivity();
                $timezone = self::$currentUser->setting(Model\User::TIME_ZONE_SETTING, null);
                if($timezone) {
                    @date_default_timezone_set($timezone);
                }
            }
        }

        if (!self::$currentUser) {
            self::$currentUser = self::getGuest();
        }
        return self::$currentUser;
    }

    public static function getAuthorizationToken()
    {
        $session = new Session('cms');
        return $session->authorization_token;
    }

    public static function setUser(Model\User $user, $remember = true)
    {
        if (!$user->is_active) {
            return self::getGuest();
        }
        $session = new Session('cms');
        $session->regenerateSessionId();
        self::$currentUser = $user;

       /* if (!$user->hasRight(null, Bazalt::ACL_CAN_LOGIN)) {
            self::$currentUser = null;
            return null;
        }*/

        $user->session_id = $session->getSessionId();
        $user->updateLastActivity();

        self::setGuestId($user->session_id);

        $token = $user->getAuthorizationToken();
        $session->cmsUser = $user->id;
        $session->authorization_token = $token;

        $cacheKey = $user->getRolesKey();
        //StaticFiles::setCacheSalt($cacheKey);

        $lifetime = $remember ? (time() + self::getUserSessionLifetime()) : 0;

        $_COOKIE['authorization_token'] = $token;

        $site = Bazalt::getSite();
        if($site->originalSite) {//is catalog
            setcookie('authorization_token', $_COOKIE['authorization_token'], $lifetime, '/', $site->domain, false, true);
        } else {
            setcookie('authorization_token', $_COOKIE['authorization_token'], $lifetime, '/', null, false, true);
        }

        return $user;
    }
    
    public static function getGuest()
    {
        $session = new Session('cms');
        if (isset($_COOKIE['GuestId'])) {
            $guestId = $_COOKIE['GuestId'];
        } else {
            $guestId = $session->getSessionId();
            self::setGuestId($guestId);
        }
        
        $guest = Model\Guest::getUser($guestId, $session->getSessionId());
        return $guest;
    }

    protected static function setGuestId($guestId)
    {
        $_COOKIE['GuestId'] = $guestId;


        $site = Bazalt::getSite();
        if($site->originalSite) {//is catalog
            setcookie('GuestId', $guestId, (time() + self::getUserSessionLifetime()), '/', '.'.$site->domain, false, false);
        } else {
            setcookie('GuestId', $guestId, (time() + self::getUserSessionLifetime()), '/', null, false, false);
        }
    }
    
    public static function isLogined()
    {
        return self::$currentUser != null && !(self::$currentUser instanceof CMS_Model_Guest);
    }
    
    public static function criptPassword($password)
    {
        return hash('sha512', $password);
    }    

    public static function login($login, $origPassword, $remember = true)
    {
        $login = strToLower(trim($login));
        $password = self::criptPassword(trim($origPassword));

        $user = CMS_Model_User::getUserByLoginPassword($login, $password, true);
        if ($user) {
            $user->session_id = Session::Singleton()->getSessionId();
            $user->addAfterLoginRole();
            Event::trigger('CMS_User', 'OnUserLogin', array($user));
            return self::setUser($user, $remember);
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

    public static function addMessage($message, $type = 'success')
    {
        if(!isset(Session::Singleton()->userMessages)) {
            Session::Singleton()->userMessages = array();
        }
        $messages = Session::Singleton()->userMessages;
        $messages []= array(
            'message' => $message,
            'type' => 'alert-'.$type
        );
        Session::Singleton()->userMessages = $messages;
    }

    public static function getMessages()
    {
        if(isset(Session::Singleton()->userMessages)) {
            $res = Session::Singleton()->userMessages;
            Session::Singleton()->userMessages = array();
            return $res;
        }
        return array();
    }

    public static function isValidUserLogin($login)
    {
        $logins = explode(',', Option::get(self::DENY_LOGINS_OPTION,''));
        foreach ($logins as $k => $l) {
            $logins[$k] = strToLower(trim($l));
        }
        if (in_array(strToLower($login), $logins)) {
            return __('The use of this login is forbidden', 'CMS');
        }
        $loginLenth = strlen($login);
        $minLoginLength = (int)Option::get(ComUsers::MIN_LOGIN_LENGTH_OPTION, 4);
        $maxLoginLength = (int)Option::get(ComUsers::MAX_LOGIN_LENGTH_OPTION, 60);
        if ($loginLenth < $minLoginLength
            || $loginLenth > $maxLoginLength
            || !(preg_match('/^([a-z])([a-z0-9._-]+)$/i', $login) || filter_var($login, FILTER_VALIDATE_EMAIL))) {
            return sprintf(__('Login must be between %d and %d characters. It must start with a letter and can contain only letters, numbers, and the following punctuation marks: full stop (.), dash (-), underscore (_)', 'CMS'), $minLoginLength, $maxLoginLength);
        }
        $user = CMS_Model_User::getUserByLogin($login);
        if ($user) {
            return __('This login already in use', 'CMS');
        }
        return true;
    }
    
    public static function isValidUserPassword($pass)
    {
        $passLenth = strlen($pass);
        $minPassLength = (int)Option::get(ComUsers::MIN_PASS_LENGTH_OPTION, 6);
        if ($passLenth < $minPassLength) {
            return sprintf(__('Minimum password length should be %d characters', 'CMS'), $minPassLength);
        }
        return true;
    }

    public static function logout()
    {
        $_COOKIE['authorization_token'] = null;

        $site = Bazalt::getSite();
        if($site->originalSite) {//is catalog
            setcookie('authorization_token', '', time() - 3600, '/', '.'.$site->domain, false, false);
        } else {
            setcookie('authorization_token', '', time() - 3600, '/', null, false, false);
        }

        //Event::trigger('CMS_User', 'OnUserLogout', array(self::$currentUser));

        $session = new Session('cms');
        self::$currentUser = null;
        unset($session->cmsUser);
        unset($session->authorization_token);
        unset($session->currentRoleId);
        $session->destroy();
        $session->regenerateSessionId();
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
        $q = $user->Roles->getQuery();
        $q->andWhere('ref.site_id = ?', CMS_Bazalt::getSiteId());
        $q->limit(1);
        return $q->fetch();
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