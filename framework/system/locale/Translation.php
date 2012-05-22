<?php

class Locale_Translation extends Config_Adaptee
{
    protected static $instance = null;

    public static function &getInstance()
    {
        if (self::$instance == null) {
            $className = __CLASS__;
            self::$instance = new $className;
            Configuration::init('locale/translation', self::$instance);
        }
        return self::$instance;
    }

    public function configure($config)
    {
        parent::configure($config);

        $this->getAdapter()->initLocale(Locale::Singleton());
    }

    

    public static function &addEntry($string, $plural = null, $flags = array())
    {
        $tr = new Locale_Translation_Entry($string, $plural, $flags);
        return $tr;
    }

    public static function saveTrStringsToTemplate($file, $strings)
    {
        Locale_Translation::Singleton()->getAdapter()->saveTrStringsToTemplate($file, $strings);
    }

    public function addLocalization()
    {
    
    }

    public static function getTranslation($string, $domain = null)
    {
        $tr = Locale_Translation::Singleton()->getAdapter()->getTranslation($string, strToLower($domain));
        return ($tr === false) ? $string : $tr;
    }

    public function getPluralTranslation($string, $pluralString, $n = 0, $domain = null)
    {
        $tr = Locale_Translation::Singleton()->getAdapter()->getPluralTranslation($string, $pluralString, $n, strToLower($domain));
        return ($tr === false) ? $string : $tr;
    }

    public static function bindTextDomain($path, $domain)
    {
        Logger::getInstance()->info(sprintf('Bind text domain %s (%s)', $domain, $path));
        Locale_Translation::Singleton()->getAdapter()->bindTextDomain($path, strToLower($domain));
    }

    public static function getDictionary($name, $folder, $locale = null)
    {
        return Locale_Translation::Singleton()->getAdapter()->readDictionary($name, $folder, $locale);
    }
}