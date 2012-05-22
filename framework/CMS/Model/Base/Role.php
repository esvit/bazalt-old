<?php

abstract class CMS_Model_Base_Role extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'cms_roles';

    const MODEL_NAME = 'CMS_Model_Role';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('site_id', 'UN:int(10)');
        $this->hasColumn('name', 'varchar(255)');
        $this->hasColumn('description', 'text');
        $this->hasColumn('is_guest', 'U:tinyint(1)|0');
        $this->hasColumn('system_acl', 'U:tinyint(1)|0');
    }

    public function initRelations()
    {
        $this->hasRelation('Users', new ORM_Relation_Many2Many('CMS_Model_User', 'user_id', 'CMS_Model_RoleRefUser', 'role_id'));
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