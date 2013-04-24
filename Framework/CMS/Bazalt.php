<?php

namespace Framework\CMS;

define('CMS_DIR', dirname(__FILE__));

if (!defined('EMPTY_SITE')) {
    define('EMPTY_SITE', false); // dont check domain
}

use Framework\System\Session\Session,
    Framework\Core\Helper as Helper;


class Bazalt
{
    protected static $instance = null;

    const ACL_CAN_LOGIN = 1;

    const ACL_HAS_ADMIN_PANEL_ACCESS = 2;

    const ACL_CAN_CHANGE_SETTING = 4;

    const ACL_CAN_DELEGATE_ROLES = 8;

    const ACL_CAN_CREATE_ROLE = 16;

    const ACL_CAN_VIEW_HIDDEN_ROLE = 32;

    const ACL_CAN_ADMIN_ROLES = 64;

    const ACL_CAN_ADMIN_WIDGETS = 128;

    const ACL_GODMODE = 256; // Developer :)

    const SITENAME_OPTION = 'CMS.SiteName';

    const SECRETKEY_OPTION = 'CMS.Secretkey';

    const ALLOWSEARCHBOT_OPTION = 'CMS.AllowSearchBot';

    const MULTILANGUAGE_OPTION = 'CMS.Multilanguage';

    const SAVE_USER_LANGUAGE_OPTION = 'CMS.SaveUserLanguage';

    const ONLINEPERIOD_OPTION = 'CMS.OnlinePeriod'; //in minutes

    protected static $components = null;

    /**
     * @var Model\Component[]
     */
    protected static $loadedComponents = array();

    protected static $site = null;

    /**
     * Return site id
     *
     * @return int
     */
    public static function getSiteId()
    {
        return self::getSite()->id;
    }

    /**
     * Return site root dir
     *
     * @return string Site root dir
     */
    public static function getSiteDir()
    {
        return self::getSite()->path;
    }

    public static function setSite(Model\Site $site)
    {
        if (!$site) {
            throw new \InvalidArgumentException('Invalid site object');
        }
        if (!defined('SITES_DIR')) {
        //    define('SITES_DIR', PUBLIC_DIR . PATH_SEP . 'sites');
        }

        //$site->path = SITES_DIR . PATH_SEP . $site->domain;
        /*define('SITE_UPLOAD_DIR', $site->path . PATH_SEP . 'uploads');

        // create site folders
        if (!is_dir($site->path)) {
            if (!mkdir($site->path, 0777)) {
                throw new \Exception('Cannot create directory ' . $site->path);
            }

            // create uploads dir
            if (!mkdir(SITE_UPLOAD_DIR, 0777)) {
                throw new \Exception('Cannot create directory ' . SITE_UPLOAD_DIR);
            }

            // create logs dir
            if (!mkdir($site->path . PATH_SEP . 'logs', 0777)) {
                throw new \Exception('Cannot create directory ' . SITE_UPLOAD_DIR);
            }

            // create symbolic link on theme assets dir
            $themePath = $site->path . PATH_SEP . 'assets';
            $theme = $site->Theme;
            if ($theme) {
                $assetsDir = $theme->getPath() . PATH_SEP . 'assets';

                if (is_dir($assetsDir) && !symlink($assetsDir, $themePath)) {
                    throw new Exception('Cannot create directory ' . $themePath);
                }
            }
        }*/
        self::$site = $site;
    }

    /**
     * Return site object
     *
     * @return Model\Site Site object
     */
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
        return Option::get(self::SECRETKEY_OPTION);
    }

    /**
     * Return current site domain like example.com or test.example.com
     *
     * @return string Domain
     */
    public static function getSiteHost()
    {
        return Bazalt::getSite()->domain;
    }

    /**
     * Detect current site from domain name and redirect as required
     *
     * @throws Exception\DomainNotFound
     */
    protected static function detectSite()
    {
        if (self::$site) {
            return;
        }
        $domain = Site::getDomainName();
        
        if (!defined('ENABLE_MULTISITING') || !ENABLE_MULTISITING) {
            $site = Model\Site::getById(1);
            if (!$site) {
                $site = Model\Site::create();
                $site->id = 1;
                $site->domain = $domain;
            }
            $site->is_subdomain = false;
            $site->is_active = true;
            $site->save();
        } else {
            $site = Model\Site::getSiteByDomain($domain);
            if (!$site) {
                $wildcard = '*' . substr($domain, strpos($domain, '.'));
                $site = Model\Site::getSiteByDomain($wildcard);
                if ($site) {
                    $site->subdomain = substr($domain, 0, strpos($domain, '.'));
                }
            }
        }
        if (!CLI_MODE) {
            if ($site->is_redirect && $site->site_id) {
                Helper\Url::redirect(Helper\Url::getProtocol() . $site->Site->domain);
            }
        }
        if (!$site && !CLI_MODE) {
            throw new Exception\DomainNotFound($domain);
        } else if (!$site && CLI_MODE) {
            $site = Model\Site::getById(1);
        }
        if ($site->site_id != null && $site->is_subdomain) {
            $originalSite = $site;
            $site = Model\Site::getById($site->site_id);
            if (!$site) {
                throw new CMS_Exception_DomainNotFound($domain);
            }
            $site->originalSite = $originalSite;
            Session::Singleton()->cookieDomain('.' . $site->domain);
        }
        self::setSite($site);
    }

    /**
     * Підключає компонент
     *
     * @param string $name Component name
     * @return Component
     */
    public static function getComponent($name)
    {
        $name = 'Components\\' . $name . '\\Component';
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
        $base = 'Components\\' . $cmsComponent->name;
        $className = $base . '\\Component';

        $component = new $className($cmsComponent, SITE_DIR . PATH_SEP . str_replace('\\', PATH_SEP, $base));
        self::$loadedComponents[$className] = $component;
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
        $components = Model\Component::getComponentsForSite(self::getSiteId());

        $loadedComponents = array();
        foreach ($components as $component) {
            # Підключаєм компонент
            $com = Bazalt::connectComponent($component);
            $loadedComponents[$component->name] = $com;
        }
        return $loadedComponents;
    }

    /**
     * Отримати посилання на завантаження файлу
     */
    public static function uploadFilename($file, $type = 'temp', $persist = false)
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $fileKey = ($persist) ? md5($file) : str_replace('-', '', Helper\Guid::newGuid());
        $folder = SITE_DIR . '/uploads';

        $user = User::get();
        $siteId = Helper\Url::encodeId(Bazalt::getSiteId());
        $userId = $user->isGuest() ? 'guest' : Helper\Url::encodeId(Bazalt::getSiteId());

        $path  = rtrim($folder, PATH_SEP) . PATH_SEP . $siteId . PATH_SEP . $type . PATH_SEP . $userId . PATH_SEP;
        $path .= $fileKey{0} . $fileKey{1} . $fileKey{2} . PATH_SEP;

        if (!is_dir($path) && !mkdir($path, 0777, true)) {
            throw new Exception('Cant create folder "' . $path . '"');
        }
        return $path . substr($fileKey, 3) . '.' . $ext;
    }
}
