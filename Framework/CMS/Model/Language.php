<?php

namespace Framework\CMS\Model;

use Framework\CMS as CMS;
use Framework\System\ORM\ORM;

class Language extends Base\Language
{
    protected $is_default = null;

    /**
     * Event OnAdd
     *
     * @var Event
     *   Event::trigger('CMS_Model_Language', 'OnAdd', array($a, 'en'));
     */
    public $eventOnAdd = \Framework\Core\Event::EMPTY_EVENT;

    /**
     * Event OnRemove
     *
     * @var Event
     */
    public $eventOnRemove = \Framework\Core\Event::EMPTY_EVENT;

    /**
     * Create language
     *
     * @param string $title Language title
     * @param string $alias Language alias
     * @param string $ico   Language icon
     * @return Language
     */
    public static function create($title, $id, $ico)
    {
        $lang = new Language();
        $lang->title = $title;
        $lang->id = $id;
        $lang->ico = $ico;

        $lang->save();
        //Event::trigger('CMS_Model_Language', 'OnAdd', array($lang, $alias));
        return $lang;
    }

    /**
     * Get site languages
     *
     * @param bool $onlyActive If true return only active languages
     * @param Site $site
     * @throws \Exception
     * @return Language[]
     */
    public static function getSiteLanguages($onlyActive = false, Site $site = null)
    {
        if (!$site) {
            $site = CMS\Bazalt::getSite();
        }
        $defaultLanguage = $site->DefaultLanguage;
        if (!$defaultLanguage) {
            //throw new \Exception('Language not found');
            $defaultLanguage = Language::select()->where('id = ?', 'en')->fetch();
            if (!$defaultLanguage) {
                $defaultLanguage = new Language();
                $defaultLanguage->id = 'en';
                $defaultLanguage->title = 'English';
                $defaultLanguage->ico = 'gb';
                $defaultLanguage->save();

                $site->DefaultLanguage = $defaultLanguage;
            }
        }
        $defaultLanguage->is_default = true;

        $languages = [
            $defaultLanguage
        ];
        if ($site->is_multilingual) {
            $q = ORM::select('Framework\CMS\Model\Language l', 'l.*, ref.is_active')
                     ->innerJoin('Framework\CMS\Model\LanguageRefSite ref', array('language_id', 'id'))
                     ->andWhere('site_id = ?', $site->id);

            if ($onlyActive) {
                $q->andWhere('ref.is_active = ?', 1);
            }
            $langs = $q->fetchAll('Framework\CMS\Model\Language');
            foreach ($langs as $lang) {
                $languages []= $lang;
            }
        }
        return $languages;
    }

    /**
     * Get active languages
     *
     * @return Language[]
     */
    public static function getActiveLanguages()
    {
        return self::getSiteLanguages(true);
    }

    /**
     * Return language by alias
     *
     * @param $alias Language alias like 'en'
     * @return Language|null
     */
    public static function getLanguageByAlias($alias)
    {
        $languages = self::getSiteLanguages();

        foreach ($languages as $language) {
            if ($language->id == $alias) {
                return $language;
            }
        }
        return null;
    }

    /**
     * Return Default language for site
     *
     * @return Language
     * @throws \Exception
     */
    public static function getDefaultLanguage()
    {
        $languages = self::getActiveLanguages();
        if (count($languages) == 0) {
            throw new \Exception('No languages');
        }
        return $languages[0];
    }

    public static function getSiteLanguage($langId, $onlyActive = false)
    {
        $languages = self::getSiteLanguages($onlyActive);
        foreach ($languages as $language) {
            if ($language->id == $langId) {
                return $language;
            }
        }
        return null;
    }


    public function isDefault()
    {
        if ($this->is_default !== null) {
            return $this->is_default;
        }
        return $this->id == CMS\Bazalt::getSite()->language_id;
    }

    public function isActive()
    {
        $languages = self::getSiteLanguages();
        foreach ($languages as $lang) {
            if ($lang == $this->id) {
                return $lang->is_active;
            }
        }
        return false;
    }

    public function activate()
    {
        ORM::update('Framework\CMS\Model\LanguageRefSite')
            ->set('is_active', '1')
            ->where('site_id = ?', CMS\Bazalt::getSiteId())
            ->andWhere('language_id = ?', $this->id)
            ->exec();
    }

    public function deactivate()
    {
        ORM::update('Framework\CMS\Model\LanguageRefSite')
            ->set('is_active', '0')
            ->where('site_id = ?', CMS\Bazalt::getSiteId())
            ->andWhere('is_default = ?', 0)
            ->andWhere('language_id = ?', $this->id)
            ->exec();
    }

    public static function setDefaultLanguageById($langId)
    {
        $language = self::getSiteLanguage($langId);
        if (!$language) {
            throw new \Exception('Language not found');
        }
        $language->setDefaultLanguage();
    }

    public function setDefaultLanguage()
    {
        ORM::update('Framework\CMS\Model\Site')
            ->where('id = ?', CMS\Bazalt::getSiteId())
            ->set('language_id = ' . $this->id)
            ->exec();
    }

    public function removeLanguage($langId)
    {
        ORM::delete('CMS_Model_LanguageRefSite')
            ->where('site_id = ?', CMS\Bazalt::getSiteId())
            ->andWhere('language_id = ?', $langId)
            ->exec();
    }

    public function addLanguage($langId)
    {
        $ref = new LanguageRefSite();
        $ref->site_id = CMS\Bazalt::getSiteId();
        $ref->language_id = $langId;
        $ref->is_active = 0;
        $ref->save();

        return $ref;
    }

    public static function getAvaliableLanguages()
    {
        $siteLanguages = self::getSiteLanguages();
        $langsIds = array();
        foreach ($siteLanguages as $lang) {
            $langsIds [] = $lang->id;
        }

        $langs = array();
        $languages = self::getAll();
        foreach ($languages as $lang) {
            if (!in_array($lang->id, $langsIds)) {
                $langs [] = $lang;
            }
        }
        return $langs;
    }

    public function toArray()
    {
        $ret = parent::toArray();
        if (isset($this->is_default)) {
            $ret['is_default'] = $this->is_default == '1';
        }
        if (isset($this->is_active)) {
            $ret['is_active'] = $this->is_active == '1';
        }
        if (isset($this->site_id)) {
            $ret['site_id'] = $this->site_id;
        }
        return $ret;
    }

    public function getUrl()
    {
        $url = CMS\Application::current()->Url;

        $route = CMS_Mapper::getDispatchedRoute();
        if ($route && !$route->Rule->isLocalizable()) {
            return $url;
        }

        if ($this->isDefault()) {
            if (CMS_Option::get(CMS_Bazalt::SAVE_USER_LANGUAGE_OPTION, false)) {
                // if current language saved in cookie
                return '/' . $this->id . $url;
            }
            return $url;
        }
        return '/' . $this->id . $url;
    }
}