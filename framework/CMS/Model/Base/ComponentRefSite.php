<?php

abstract class CMS_Model_Base_ComponentRefSite extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'cms_components_ref_sites';

    const MODEL_NAME = 'CMS_Model_ComponentRefSite';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('component_id', 'PU:int(10)');
        $this->hasColumn('site_id', 'PU:int(10)');
    }

    public function initRelations()
    {
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