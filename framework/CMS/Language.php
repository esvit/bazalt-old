<?php

class CMS_Language
{
    const SESSION_LANGUAGE_VARNAME = 'CMS_Language_CurrentLanguage';

    public $eventOnChangeLanguage = Event::EMPTY_EVENT;

    protected static $currentLanguage = null;

    protected static $activeLanguages = null;

    protected static $defaultLanguage = null;

    protected static $languagePrefix = '';

    /**
     * Get language prefix
     */
    public static function getLanguagePrefix()
    {
        return self::$languagePrefix;
    }

    /**
     * Set language prefix
     */
    public static function setLanguagePrefix($prefix)
    {
        self::$languagePrefix = $prefix;
    }

    /**
     * Get all active languages
     */
    public static function getLanguages()
    {
        if (self::$activeLanguages == null) {
            $user = CMS_User::getUser();
            if ($user->hasRight(null, CMS_Bazalt::ACL_GODMODE) || $user->Sites->has(CMS_Bazalt::getSite())) {
                $langs = CMS_Bazalt::getSite()->Languages->get();
            } else {
                $langs = CMS_Model_Language::getActiveLanguages();
            }
            foreach ($langs as $lang) {
                self::$activeLanguages[$lang->id] = $lang;
            }
        }
        return self::$activeLanguages;
    }

    public static function getLanguage($alias)
    {
        $langs = self::getLanguages();

        foreach ($langs as $lang) {
            if ($lang->alias == $alias) {
                return $lang;
            }
        }
        return null;
    }

    /**
     * Get current language
     */
    public static function getCurrentLanguage()
    {
        if (self::$currentLanguage == null) {
            $lang_id = Session::Singleton()->{self::SESSION_LANGUAGE_VARNAME};
            if (!empty($langId)) {
                self::$currentLanguage = CMS_Model_Language::getById((int)$langId);
            } else {
                self::$currentLanguage = self::getDefaultLanguage();
            }
        }
        return self::$currentLanguage;
    }

    /**
     * Get default language
     */
    public static function getDefaultLanguage()
    {
        return self::$defaultLanguage = (self::$defaultLanguage) ? self::$defaultLanguage : CMS_Model_Language::getDefaultLanguage();
    }

    /**
     * Set current language
     */
    public static function setCurrentLanguage($alias = null)
    {
        if ($alias == null) {
            $lang = CMS_Model_Language::getDefaultLanguage();
        } else {
            $lang = CMS_Model_Language::getLanguageByAlias($alias);
            if ($lang->default_lang && !CMS_Option::get(CMS_Bazalt::SAVE_USER_LANGUAGE_OPTION, false)) {
                throw new Exception('Language not found ' . $alias);
            }
        }
        if ($lang == null) {
            throw new Exception('Language not found ' . $alias);
        }

        self::$currentLanguage = $lang;
        Locale::setLocale($lang->alias);
        Session::Singleton()->{self::SESSION_LANGUAGE_VARNAME} = $lang->id;

        Event::trigger(__CLASS__, 'OnChangeLanguage', array($lang));
        return $lang;
    }

}