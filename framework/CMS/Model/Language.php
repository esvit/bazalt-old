<?php

class CMS_Model_Language extends CMS_Model_Base_Language
{
    /**
     * Event OnAdd
     *
     * @var Event
     *   Event::trigger('CMS_Model_Language', 'OnAdd', array($a, 'en'));
     */
    public $eventOnAdd = Event::EMPTY_EVENT;
    
    /**
     * Event OnRemove
     *
     * @var Event
     */
    public $eventOnRemove = Event::EMPTY_EVENT;

    public function __construct()
    {
        parent::__construct();

        $this->site_id = CMS_Bazalt::getSiteId();
    }

    /**
     * Create language
     *
     * @param $title Language title
     * @param $alias Language alias
     * @param $ico   Language icon
     */
    public static function createLanguage($title, $alias, $ico)
    {
        $lang = new CMS_Model_Language();
        $lang->title = $title;
        $lang->alias = $alias;
        $lang->ico = $ico;
        $lang->site_id = CMS_Bazalt::getSiteId();

        $lang->save();
        //Event::trigger('CMS_Model_Language', 'OnAdd', array($lang, $alias));
        return $lang;
    }

    /**
     * Get site languages
     */
    public static function getSiteLanguages($onlyActive = false)
    {
        $q = CMS_Model_LanguageRefSite::select()
                ->innerJoin('CMS_Model_Language lang', array('id', 'language_id'))
                ->andWhere('site_id = ?', CMS_Bazalt::getSiteId())
                ->orderBy('is_default DESC');

        if ($onlyActive) {
            $q->andWhere('is_active = ?', 1);
        }
        return $q->fetchAll('CMS_Model_Language');
    }

    /**
     * Get active languages
     */
    public static function getActiveLanguages()
    {
        return self::getSiteLanguages(true);
    }

    public static function getLanguageByAlias($alias)
    {
        $q = CMS_Model_Language::select()
                ->innerJoin('CMS_Model_LanguageRefSite ref', array('language_id', 'id'))
                ->where('alias = ?', $alias)
                ->andWhere('ref.site_id = ?', CMS_Bazalt::getSiteId());

        return $q->fetch();
    }

    public static function getDefaultLanguage()
    {
        $langs = self::getActiveLanguages();
        if (count($langs) == 0) {
            throw new Exception('No languages');
        }
        return $langs[0];
    }

    public static function getSiteLanguage($langId, $onlyActive = false)
    {
        $q = CMS_Model_Language::select()
                ->innerJoin('CMS_Model_LanguageRefSite ref', array('language_id', 'id'))
                ->andWhere('ref.site_id = ?', CMS_Bazalt::getSiteId())
                ->andWhere('id = ?', $langId);

        if ($onlyActive) {
            $q->andWhere('ref.is_active = ?', 1);
        }
        return $q->fetch();
    }

    public function isDefault()
    {
        $language = self::getSiteLanguage($this->id);
        return $language->is_default;
    }

    public function isActive()
    {
        $language = self::getSiteLanguage($this->id);
        return $language->is_active;
    }

    public function activate()
    {
        ORM::update('CMS_Model_LanguageRefSite')
           ->set('is_active', '1')
           ->where('site_id = ?', CMS_Bazalt::getSiteId())
           ->andWhere('language_id = ?', $this->id)
           ->exec();
    }

    public function deactivate()
    {
        ORM::update('CMS_Model_LanguageRefSite')
           ->set('is_active', '0')
           ->where('site_id = ?', CMS_Bazalt::getSiteId())
           ->andWhere('is_default = ?', 0)
           ->andWhere('language_id = ?', $this->id)
           ->exec();
    }

    public static function setDefaultLanguageById($langId)
    {
        $language = self::getSiteLanguage($langId);
        if (!$language) {
            throw new Exception('Language not found');
        }
        $language->setDefaultLanguage();
    }

    public function setDefaultLanguage()
    {
        ORM::update('CMS_Model_LanguageRefSite')
           ->set('is_default', '0')
           ->where('site_id = ?', CMS_Bazalt::getSiteId())
           ->exec();

        ORM::update('CMS_Model_LanguageRefSite')
           ->set('is_default', '1')
           ->set('is_active', '1')
           ->where('site_id = ?', CMS_Bazalt::getSiteId())
           ->andWhere('language_id = ?', $this->id)
           ->exec();
    }

    public function removeLanguage($langId)
    {
        ORM::delete('CMS_Model_LanguageRefSite')
           ->where('site_id = ?', CMS_Bazalt::getSiteId())
           ->andWhere('language_id = ?', $langId)
           ->andWhere('is_default = ?', 0)
           ->exec();
    }

    public function addLanguage($langId)
    {
        $ref = new CMS_Model_LanguageRefSite();
        $ref->site_id = CMS_Bazalt::getSiteId();
        $ref->language_id = $langId;
        $ref->is_default = 0;
        $ref->is_active = 0;
        $ref->save();

        return $ref;
    }

    public static function getAvaliableLanguages()
    {
        $siteLanguages = self::getSiteLanguages();
        $langsIds = array();
        foreach ($siteLanguages as $lang) {
            $langsIds []= $lang->id;
        }

        $langs = array();
        $languages = self::getAll();
        foreach ($languages as $lang) {
            if (!in_array($lang->id, $langsIds)) {
                $langs []= $lang;
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
        $url = CMS_Application::current()->Url;

        $route = CMS_Mapper::getDispatchedRoute();
        if ($route && !$route->Rule->isLocalizable()) {
            return $url;
        }

        if ($this->isDefault()) {
            if (CMS_Option::get(CMS_Bazalt::SAVE_USER_LANGUAGE_OPTION, false)) {
                // if current language saved in cookie
                return '/' . $this->alias . $url;
            }
            return $url;
        }
        return '/' . $this->alias . $url;
    }
}