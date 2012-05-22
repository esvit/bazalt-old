<?php

abstract class CMS_Model_Base_Site extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'cms_sites';

    const MODEL_NAME = 'CMS_Model_Site';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('domain', 'N:varchar(255)');
        $this->hasColumn('title', 'N:varchar(255)');
        $this->hasColumn('is_subdomain', 'U:tinyint(3)|0');
        $this->hasColumn('is_active', 'U:tinyint(3)|0');
        $this->hasColumn('site_id', 'NU:int(11)');
        $this->hasColumn('is_redirect', 'U:tinyint(3)|0');
    }

    public function initRelations()
    {
        $this->hasRelation('Site', new ORM_Relation_One2One(self::MODEL_NAME, 'site_id', 'id'));
        $this->hasRelation('Mirrors', new ORM_Relation_One2Many(self::MODEL_NAME, 'id', 'site_id'));

        $this->hasRelation('Components', new ORM_Relation_Many2Many('CMS_Model_Component', 'site_id', 'CMS_Model_ComponentRefSite', 'component_id'));
        $this->hasRelation('Options', new ORM_Relation_One2Many('CMS_Model_Option', 'id', 'site_id'));
        $this->hasRelation('Users', new ORM_Relation_Many2Many('CMS_Model_User', 'site_id', 'CMS_Model_SiteRefUser', 'user_id'));
        $this->hasRelation('Services', new ORM_Relation_Many2Many('CMS_Model_Services', 'site_id', 'CMS_Model_ServicesRefSites', 'service_id'));
        $this->hasRelation('Widgets', new ORM_Relation_One2Many('CMS_Model_WidgetInstance', 'id', 'site_id'));
        $this->hasRelation('Languages', new ORM_Relation_Many2Many('CMS_Model_Language', 'site_id', 'CMS_Model_LanguageRefSite', 'language_id'));
    }

    public function initPlugins()
    {
    }

    public static function getById($id)
    {
        return parent::getRecordById($id, self::MODEL_NAME);
    }

    public static function getAll($limit = null)
    {
        return parent::getAllRecords($limit, self::MODEL_NAME);
    }

    public static function select($fields = null)
    {
        return ORM::select(self::MODEL_NAME, $fields);
    }

    public static function insert($fields = null)
    {
        return ORM::insert(self::MODEL_NAME, $fields);
    }
}