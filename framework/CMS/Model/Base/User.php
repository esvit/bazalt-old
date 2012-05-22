<?php

abstract class CMS_Model_Base_User extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'cms_users';

    const MODEL_NAME = 'CMS_Model_User';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('login', 'varchar(255)');
        $this->hasColumn('password', 'varchar(255)');
        $this->hasColumn('firstname', 'varchar(255)');
        $this->hasColumn('secondname', 'varchar(255)');
        $this->hasColumn('patronymic', 'varchar(255)');
        $this->hasColumn('email', 'N:varchar(60)');
        $this->hasColumn('gender', "ENUM('unknown','male','female')|'unknown'");
        $this->hasColumn('birth_date', 'N:date');
        $this->hasColumn('reg_date', 'N:timestamp|CURRENT_TIMESTAMP');
        $this->hasColumn('is_active', 'U:tinyint(1)');
        $this->hasColumn('last_activity', 'N:datetime');
    }

    public function initRelations()
    {
        $this->hasRelation('Roles', new ORM_Relation_Many2Many('CMS_Model_Role', 'user_id', 'CMS_Model_RoleRefUser', 'role_id'));
        $this->hasRelation('Settings', new ORM_Relation_One2Many('CMS_Model_UserSetting', 'id', 'user_id'));
        $this->hasRelation('Sites', new ORM_Relation_Many2Many('CMS_Model_Site', 'user_id', 'CMS_Model_SiteRefUser', 'site_id'));
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