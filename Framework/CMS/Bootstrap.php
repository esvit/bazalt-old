<?php

namespace Framework\CMS;

using('Framework.Vendors.Neon');
using('Framework.System.Cache');

$whoops = new \Whoops\Run();
$handler = new \Whoops\Handler\PrettyPageHandler();
$handler->addDataTable('Killer App Details', array(
  "Thingamajig-id" => '123'
));

// Set the title of the error page:
$handler->setPageTitle("We're all going to be fired!");
$whoops->pushHandler($handler);
$jsonHandler = new  \Whoops\Handler\JsonResponseHandler();
$jsonHandler->onlyForAjaxRequests(true);
$whoops->pushHandler($jsonHandler);

$whoops->register();

use Framework\Core\Logger,
    Framework\Core\Event;

final class Bootstrap
{
    private static $_configuration = null;

    private static $_application = null;

    public static function current()
    {
        return self::$_application;
    }

    private static function _loadConfiguration()
    {
        //self::$_configuration = Config_Loader::load(SITE_DIR . '/bazalt.cfg');
    }
/*
    public static function OnSetUserLocale()
    {
        $languages = CMS_Model_Language::getActiveLanguages();
        foreach($languages as $language) {
            Locale_Config::allowLocale($language->alias);
        }
        if(count($languages) > 0) {
            Locale_Config::setLocale($languages[0]->alias);
        }
    }*/

    public static function start(Application $application)
    {
        //Event::register('Locale_Config', 'OnSetUserLocale', array('CMS_Bootstrap', 'onSetUserLocale'));

        //self::_loadConfiguration();
        //self::_initCache();

        self::$_application = $application;
        $application->start();
    }
}