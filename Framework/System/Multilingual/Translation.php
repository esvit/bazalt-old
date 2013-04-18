<?php

namespace Framework\System\Multilingual;

class Translation
{
    protected static $instance = null;

    public static function &getInstance()
    {
        if (self::$instance == null) {
            $className = __CLASS__;
            self::$instance = new $className;
            Config_Loader::init('locale/translation', self::$instance);
        }
        return self::$instance;
    }

    public function configure($config)
    {
        if (empty($config)) {
            throw new Exception('Unknown adapter');
        }
        $this->adapterClass = $config->value;

        if (empty($this->adapterClass)) {
            throw new Exception('Unknown adapter');
        }
        $arr = $config->attributes;

        if (isset($arr['namespace'])) {
            $this->adapterNamespace = $arr['namespace'];
            unset($arr['namespace']);
        }

        $this->adapterOptions = $arr;

        $this->getAdapter()->initLocale(Locale_Config::getInstance());
    }

    

    public static function &addEntry($string, $plural = null, $flags = array())
    {
        $tr = new Locale_Translation_Entry($string, $plural, $flags);
        return $tr;
    }

    public static function saveTrStringsToTemplate($file, $strings)
    {
        Locale_Translation::getInstance()->getAdapter()->saveTrStringsToTemplate($file, $strings);
    }

    public function addLocalization()
    {
    
    }

    public static function getTranslation($string, $domain = null)
    {
        $tr = Locale_Translation::getInstance()->getAdapter()->getTranslation($string, strToLower($domain));
        return ($tr === false) ? $string : $tr;
    }

    public function getPluralTranslation($string, $pluralString, $n = 0, $domain = null)
    {
        $tr = Locale_Translation::getInstance()->getAdapter()->getPluralTranslation($string, $pluralString, $n, strToLower($domain));
        return ($tr === false) ? $string : $tr;
    }

    public static function bindTextDomain($path, $domain)
    {
        Logger::getInstance()->info(sprintf('Bind text domain %s (%s)', $domain, $path));
        Locale_Translation::getInstance()->getAdapter()->bindTextDomain($path, strToLower($domain));
    }

    public static function getDictionary($name, $folder, $locale = null)
    {
        return Locale_Translation::getInstance()->getAdapter()->readDictionary($name, $folder, $locale);
    }
}