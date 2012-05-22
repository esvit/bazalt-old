<?php

class Configuration
{
    private static $_configuration = null;

    private static $_logger = null;

    public static function replaceConstants($str, $constants = null)
    {
        if (strpos($str, '%') === false) {
            return $str;
        }
        $newStr = '';
        $start = 1;
        while (strlen($str) != 0) {
            $start = strpos($str, '%');
            if ($start > 0) {
                $newStr .= substr($str, 0, $start);
            }
            $end = strpos($str, '%', $start + 1);
            if ($end !== false && $start < $end) {
                $const = substr($str, $start + 1, $end - $start - 1);
                $str = substr($str, $end + 1);
                // if element exists in array or defined constant
                $newStr .= (is_array($constants) && array_key_exists($const, $constants)) ? 
                                $constants[$const] : 
                                (defined($const) ? constant($const) : '%' . $const . '%');
            } else {
                $newStr .= substr($str, $start);
                break;
            }
        }
        return $newStr;
    }

    public static function load($fileName)
    {
        self::$_configuration = new Neon_Adapter();
        self::$_configuration->load($fileName);

        return self::$_configuration;
    }

    private static function _getLogger()
    {
        if (self::$_logger == null) {
            self::$_logger = new Logger(__CLASS__);
        }
        return self::$_logger;
    }

    public static function get($section)
    {
        if (self::$_configuration == null) {
            return null;
        }
        return self::$_configuration->get($section);
    }

    public static function init($section, $object)
    {
        if (!($object instanceof Config_IConfigurable)) {
            throw new Exception('Invalid interface Config_IConfigurable');
        }
        $config = self::get($section);
        if ($config) {
            $object->configure($config);
        }
    }
}