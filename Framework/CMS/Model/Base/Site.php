<?php

namespace Framework\CMS\Model\Base;

/**
 * @property    int     id
 * @property    string  domain
 * @property    string  title
 * @property    int     theme_id
 * @property    int     language_id
 * @property    int     is_subdomain
 * @property    int     user_id
 * @property    int     is_active
 * @property    int     is_multilingual
 * @property    int     site_id
 * @property    int     is_redirect
 * @property    Language       DefaultLanguage
 * @property    Language[]     Languages
 */
abstract class Site extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'cms_sites';

    const MODEL_NAME = 'Framework\CMS\Model\Site';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('domain', 'varchar(255)|localhost');
        $this->hasColumn('path', 'varchar(255)|/');
        $this->hasColumn('title', 'N:varchar(255)');
        $this->hasColumn('theme_id', 'NU:int(11)');
        $this->hasColumn('language_id', 'NU:int(11)');
        $this->hasColumn('is_subdomain', 'U:tinyint(3)|0');
        $this->hasColumn('is_active', 'U:tinyint(3)|0');
        $this->hasColumn('is_multilingual', 'U:tinyint(3)|0');
        $this->hasColumn('user_id', 'NU:int(11)');
        $this->hasColumn('site_id', 'NU:int(11)');
        $this->hasColumn('is_redirect', 'U:tinyint(3)|0');
    }

    public function initRelations()
    {
        $this->hasRelation('Site', new \ORM_Relation_One2One(self::MODEL_NAME, 'site_id', 'id'));
        $this->hasRelation('Mirrors', new \ORM_Relation_One2Many(self::MODEL_NAME, 'id', 'site_id'));

        $this->hasRelation('Theme', new \ORM_Relation_One2One('Framework\CMS\Model\Theme', 'theme_id', 'id'));

        $this->hasRelation('Components', new \ORM_Relation_Many2Many('Framework\CMS\Model\Component', 'site_id', 'Framework\CMS\Model\ComponentRefSite', 'component_id'));
        $this->hasRelation('Options', new \ORM_Relation_One2Many('Framework\CMS\Model\Option', 'id', 'site_id'));
        $this->hasRelation('Users', new \ORM_Relation_Many2Many('Framework\CMS\Model\User', 'site_id', 'Framework\CMS\Model\SiteRefUser', 'user_id'));
        $this->hasRelation('Widgets', new \ORM_Relation_One2Many('Framework\CMS\Model\WidgetInstance', 'id', 'site_id'));

        $this->hasRelation('DefaultLanguage', new \ORM_Relation_One2One('Framework\CMS\Model\Language', 'language_id', 'id'));
        $this->hasRelation('Languages', new \ORM_Relation_Many2Many('Framework\CMS\Model\Language', 'site_id', 'Framework\CMS\Model\LanguageRefSite', 'language_id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Framework\System\ORM\Plugin\Timestampable', ['created' => 'created_at', 'updated' => 'updated_at']);
    }
}