<?php

abstract class CMS_Model_Base_UserSetting extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'cms_users_settings';

    const MODEL_NAME = 'CMS_Model_UserSetting';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('user_id', 'P:int(10)');
        $this->hasColumn('setting', 'P:varchar(255)');
        $this->hasColumn('value', 'text');
    }

    public function initRelations()
    {
        $this->hasRelation('User', new ORM_Relation_One2One('CMS_Model_User', 'user_id',  'id'));
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