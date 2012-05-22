<?php

abstract class CMS_Model_Base_ServicesRefSites extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'cms_services_ref_sites';

    const MODEL_NAME = 'CMS_Model_ServicesRefSites';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('site_id', 'PU:int(10)');
        $this->hasColumn('service_id', 'PU:int(10)');
        $this->hasColumn('config', 'U:text');
    }

    public function initRelations()
    {
        $this->hasRelation('Service', new ORM_Relation_One2One('CMS_Model_Services', 'service_id', 'id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('ORM_Plugin_Serializable', 'config');
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