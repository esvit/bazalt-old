<?php

if (!defined('EMPTY_SITE')) {
    define('EMPTY_SITE', false); // dont check domain
}

class CMS_Bazalt extends Object implements ISingleton, IEventable
{
    protected static $instance = null;

    const ACL_CAN_LOGIN = 1;
    
    const ACL_CAN_CHANGE_SETTING = 2;

    const ACL_GODMODE = 128; // Developer :)

    const SITENAME_OPTION = 'CMS.SiteName';

    const SITEHOST_OPTION = 'CMS.SiteHost';

    const SECRETKEY_OPTION = 'CMS.Secretkey';

    const ALLOWSEARCHBOT_OPTION = 'CMS.AllowSearchBot';

    const MULTILANGUAGE_OPTION = 'CMS.Multilanguage';

    const SAVE_USER_LANGUAGE_OPTION = 'CMS.SaveUserLanguage';

    const ONLINEPERIOD_OPTION = 'CMS.OnlinePeriod';//in minutes

    public $eventOnSiteCreate = Event::EMPTY_EVENT;

    protected static $components = null;

    protected static $loadedComponents = array();

    protected static $site = null;

    public static function &getInstance()
    {
        if (self::$instance == null) {
            $className = __CLASS__;
            self::$instance = new $className;
            //Configuration::init($className, self::$instance);
        }
        return self::$instance;
    }

    public static function getSiteId()
    {
        return self::getSite()->id;
    }

    public static function setSite(CMS_Model_Site $site)
    {
        self::$site = $site;
        if (!CLI_MODE) {
            Session::Singleton()->cmsSiteId = $site->id;
        }
    }

    public static function getSite()
    {
        if (!self::$site) {
            self::detectSite();
        }
        return self::$site;
    }

    /**
     * Secret key for site (unique value)
     * Warning: DO NOT USE THIS VALUE FOR PASSWORD SALT!!!
     *
     * @return string Secret key (guid)
     */
    public static function getSecretKey()
    {
        return CMS_Option::get(self::SECRETKEY_OPTION);
    }

    public static function getSiteHost()
    {
        return CMS_Option::get(self::SITEHOST_OPTION);
    }

    public static function isMultilanguageSite()
    {
        return CMS_Option::get(self::MULTILANGUAGE_OPTION);
    }

    protected static function detectSite()
    {
        if (self::$site) {
            return;
        }
        if (!CLI_MODE && IDENTIFY_SITE_BY_SESSION && Session::Singleton()->cmsSiteId) {
            $site = CMS_Model_Site::getById(Session::Singleton()->cmsSiteId);
            if ($site) {
                self::setSite($site);
                return;
            }
        }
        $domain = Url::getDomain();
        if (CLI_MODE && empty($domain)) {
            $domain = 'cli_mode';
        } else if (substr(strToLower($domain), 0, 4) == 'www.') {
            $domain = substr($domain, 4);
        }

        if (!defined('ENABLE_MULTISITING') || !ENABLE_MULTISITING) {
            $site = CMS_Model_Site::getById(1);
            if (!$site) {
                $site = CMS_Model_Site::create();
                $site->id = 1;
                $site->domain = $domain;
            }
            $site->is_subdomain = false;
            $site->is_active = true;
            $site->save();
        } else {
            $site = CMS_Model_Site::getSiteByDomain($domain);
        }
        if (!CLI_MODE) {
            if ($site->is_redirect && $site->site_id) {
                Url::redirect('http://' . $site->Site->domain);
            }
        }
        if (!$site && !CLI_MODE) {
            throw new CMS_Exception_DomainNotFound($domain);
        }
        self::setSite($site);
    }

    /**
     * Ïîâåğòàº âåáñåğâ³ñ êîìïîíåíòà
     */
    public static function getComponentWebservice($component, $websevice)
    {
        return new $websevice($component);
    }

    /**
     * Ï³äêëş÷àº êîìïîíåíò
     */
    public static function getComponent($name)
    {
        $name = strToLower($name);
        if (!array_key_exists($name, self::$loadedComponents)) {
            return null;
        }
        return self::$loadedComponents[$name];
    }

    public static function getComponents()
    {
        return self::$loadedComponents;
    }

    public static function connectComponent($cmsComponent)
    {
        $className = $cmsComponent->name;
        if (!defined('COMPONENTS_DIR')) {
            throw new Exception('Constant "COMPONENTS_DIR" must be defined');
        }
        $baseDir = COMPONENTS_DIR . PATH_SEP . strToLower($className);
        if (!is_dir($baseDir)) {
            $baseDir = COMPONENTS_DIR . PATH_SEP . $className;
            if (!is_dir($baseDir)) {
                throw new Exception('Folder "' . $baseDir . '" for component not found');
            }
        }

        $path = $baseDir . PATH_SEP . $className . '.php';
        if (!file_exists($path)) {
            throw new Exception('Component file "' . $path . '" not found');
        }
        Core_Autoload::registerNamespace($className, $baseDir);
        require_once $path;

        if (!class_exists($className)) {
            throw new Exception('Component class "' . $className . '" not exists in file "' . $path . '"');
        }

        $component = new $className($cmsComponent, $baseDir);
        self::$loadedComponents[strToLower($className)] = $component;
        if (is_dir($baseDir . '/locale')) {
            $component->hasLocale('locale'); // add locale folder
        }
        return $component;
    }

    public static function initComponents($application)
    {
        foreach (self::$loadedComponents as $component) {
            $component->initComponent($application);
        }
        return self::$loadedComponents;
    }

    public static function connectComponents()
    {
        if (CLI_MODE) {
            $components = CMS_Model_Component::getActiveComponents();
        } else {
            $components = CMS_Model_Component::getComponentsForSite(self::getSiteId());
        }

        $loadedComponents = array();
        foreach ($components as $component) {
            # Ï³äêëş÷àºì êîìïîíåíò
            $com = CMS_Bazalt::connectComponent($component);
            $name = strToLower($component->name);
            $loadedComponents[$name] = $com;
        }
        return $loadedComponents;
    }

    public function Event_OnSiteCreate($site)
    {
        CMS_Option::set(self::SITENAME_OPTION, $site->title, null, $site->id);

        CMS_Option::set(self::SITEHOST_OPTION, $site->domain, null, $site->id);

        CMS_Option::set(self::SECRETKEY_OPTION, Datatype_Guid::newGuid()->toString(), null, $site->id);

        CMS_Option::set(CMS_Theme::THEME_OPTION, CMS_Theme::DEFAULT_NAME, null, $site->id);

        $user = CMS_User::getUser();
        if (!$user->isGuest()) {
            $site->Users->add($user);
        }
    }
}
