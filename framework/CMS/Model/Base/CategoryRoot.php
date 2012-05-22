<?php

abstract class CMS_Model_Base_CategoryRoot extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'cms_categories_roots';

    const MODEL_NAME = 'CMS_Model_CategoryRoot';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('site_id', 'UN:int(10)');
        $this->hasColumn('root_id', 'U:int(10)');
        $this->hasColumn('component_id', 'UN:int(10)');
        $this->hasColumn('description', 'varchar(255)');
        $this->hasColumn('icon_number', 'U:int(10)');
    }

    public function initRelations()
    {
        $this->hasRelation('Category', new ORM_Relation_One2One('CMS_Model_Category', 'root_id', 'id'));
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