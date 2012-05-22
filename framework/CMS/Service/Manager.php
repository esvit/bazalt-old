<?php

class CMS_Service_Manager extends DataType_Manager
{
    protected static $registredServices = array();

    public static function register($className, $config = array())
    {
        self::$registredServices[$className] = $config;
    }

    /**
     * Ініціалізація сервісів
     */
    public static function initServices(&$url)
    {
        foreach (self::$registredServices as $className => $config) {
            $service = new $className($config);
            $service->init($url);
            # Ініціалізуємо сервіси
            $service->prepareUrl($url);
        }
    }
}