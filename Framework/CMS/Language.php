<?php

namespace Framework\CMS;

use Framework\System\Session\Session,
    Framework\CMS as CMS;

class Language
{
    /**
     * Назва змінною у сесії під іменем якої зберігається значення
     */
    const SESSION_LANGUAGE_VARNAME = 'CMS_Language_CurrentLanguage';

    /**
     * @var Language Поточна мова
     */
    protected static $currentLanguage = null;

    /**
     * @var Language[] Активні(опубліковані) мови
     */
    protected static $activeLanguages = null;

    /**
     * @var Language Мова оригіналу (за замовчуванням)
     */
    protected static $defaultLanguage = null;

    /**
     * @var string Префікс мови, що дописується до сторінки, наприклад, /en
     */
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
     *
     * @return Language[]
     */
    public static function getLanguages()
    {
        if (self::$activeLanguages == null) {
            $user = CMS\User::get();

            $languages = Model\Language::getSiteLanguages(!($user->hasRight(null, CMS\Bazalt::ACL_GODMODE) || $user->Sites->has(CMS\Bazalt::getSite())));
            foreach ($languages as $language) {
                self::$activeLanguages[$language->id] = $language;
            }
        }
        return self::$activeLanguages;
    }


    /**
     * Return language by alias
     *
     * <code>
     * CMS_Language::getLanguage('en');
     * </code>
     *
     * @param $alias Language alias
     * @return Language|null Language or null
     */
    public static function getLanguage($alias)
    {
        $languages = self::getLanguages();

        foreach ($languages as $language) {
            if ($language->id == $alias) {
                return $language;
            }
        }
        return null;
    }

    /**
     * Get current language
     *
     * @return Language
     */
    public static function getCurrentLanguage()
    {
        if (self::$currentLanguage == null) {
            $session = new Session('language');
            $lang_id = $session->{self::SESSION_LANGUAGE_VARNAME};
            if (!empty($langId)) {
                self::$currentLanguage = Language::getById((int)$langId);
            } else {
                self::$currentLanguage = self::getDefaultLanguage();
            }
        }
        return self::$currentLanguage;
    }

    /**
     * Get default language
     *
     * @return Language Return default language of site
     */
    public static function getDefaultLanguage()
    {
        return self::$defaultLanguage = (self::$defaultLanguage) ? self::$defaultLanguage : Model\Language::getDefaultLanguage();
    }

    /**
     * Set current language
     */
    public static function setCurrentLanguage($alias = null)
    {
        if ($alias == null) {
            $lang = Language::getDefaultLanguage();
        } else {
            $lang = Language::getLanguageByAlias($alias);
        }
        if ($lang == null) {
            throw new \Exception('Language not found "' . $alias . '"');
        }

        self::$currentLanguage = $lang;
        Locale_Config::setLocale($lang->id);
        if ($alias != null) {
            Session::Singleton()->{self::SESSION_LANGUAGE_VARNAME} = $lang->id;
        }

        Event::trigger(__CLASS__, 'OnChangeLanguage', array($lang));
        return $lang;
    }

}