<?php

using('Framework.Vendors.Neon');
using('Framework.System.Cache');

final class CMS_Bootstrap
{
    private static $_logger = null;

    private static $_configuration = null;

    private static $_request = null;

    private static $_application = null;

    private static function _getLogger()
    {
        if (self::$_logger == null) {
            self::$_logger = new Logger(__CLASS__);
        }
        return self::$_logger;
    }

    public static function getCurrentApplication()
    {
        return self::$_application;
    }

    private static function _loadConfiguration()
    {
        self::$_configuration = Configuration::load(SITE_DIR . '/bazalt.cfg');

        $constants = self::$_configuration->get('constants');
        if (is_array($constants)) {
            foreach ($constants as $constant => $value) {
                $value = Configuration::replaceConstants($value);
                define($constant, $value);
                self::_getLogger()->log(sprintf('Define constant "%s" => "%s"', $constant, $value));
            }
        }
    }

    private static function _initCache()
    {
        if (!CACHE || !($cache = self::$_configuration->get('cache'))) {
            return;
        }

        $cache = Cache::Singleton()->initCache($cache->value, $cache->attributes);

        if (!CLI_MODE) {
        //    $cache->salt(DataType_Url::getDomain()); // cache salt, for memcache
        }
    }

    private static function _initApplication($name = null)
    {
        $application = null;
        $applications = self::$_configuration->get('applications');
        if (!empty($name) && isset($applications[$name])) {
            $application = $applications[$name];
        } else {
            $url = DataType_Url::getRequestUrl();
            foreach ($applications as $applicationName => $applicationConfig) {
                if (!isset($applicationConfig['urlPrefix'])) {
                    break;
                }
                $prefix = $applicationConfig['urlPrefix'];
                if (strpos(strToLower($url), strToLower($prefix)) === 0) {
                    $name = $applicationName;
                    $application = $applicationConfig;
                }
            }
        }
        if (!$application || empty($name)) {
            throw new Exception('Application not defined');
        }
        $application['path'] = Configuration::replaceConstants($application['path']);
        Core_Autoload::registerNamespace($name, $application['path']);

        $appClass = $name . '_App';
        if (!class_exists($appClass)) {
            throw new Exception('Class "' . $appClass . '" not found');
        }
        // init application
        self::$_application = new $appClass($name, self::$_request, $application);
        if (!(self::$_application instanceof CMS_Application)) {
            throw new Exception('Class "' . $appClass . '" must be instance of CMS_Application');
        }
        return self::$_application;
    }

    public static function start($name = null)
    {
        self::_loadConfiguration();
        self::_initCache();

        if (!CLI_MODE) {
            self::$_request = new CMS_Request();
        }

        self::_initApplication($name);

        self::$_application->start();
    }
}