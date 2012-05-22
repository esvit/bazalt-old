<?php

abstract class CMS_Model_Base_Option extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'cms_options';

    const MODEL_NAME = 'CMS_Model_Option';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('site_id', 'UN:int(10)');
        $this->hasColumn('component_id', 'UN:int(10)');
        $this->hasColumn('name', 'varchar(255)');
        $this->hasColumn('value', 'text');
    }

    public function initRelations()
    {
        $this->hasRelation('Components', new ORM_Relation_One2One('CMS_Model_Component', 'component_id',  'id'));
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