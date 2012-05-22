<?php

abstract class CMS_Model_Base_ChangeLog extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'cms_changelog';

    const MODEL_NAME = 'CMS_Model_ChangeLog';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('component_id', 'UN:int(10)');
        $this->hasColumn('user_id', 'UN:int(10)');
        $this->hasColumn('action', 'varchar(255)');
        $this->hasColumn('params', 'text');
        $this->hasColumn('timestamp', 'timestamp');
    }

    public function initRelations()
    {
        $this->hasRelation('Component', new ORM_Relation_One2One('CMS_Model_Component', 'component_id',  'id'));
        $this->hasRelation('User', new ORM_Relation_One2One('CMS_Model_User', 'user_id', 'id'));
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