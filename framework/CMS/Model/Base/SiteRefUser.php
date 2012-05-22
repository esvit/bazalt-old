<?php

abstract class CMS_Model_Base_SiteRefUser extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'cms_sites_ref_users';

    const MODEL_NAME = 'CMS_Model_SiteRefUser';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('site_id', 'PU:int(10)');
        $this->hasColumn('user_id', 'PU:int(10)');
        $this->hasColumn('last_activity', 'N:datetime');
        $this->hasColumn('session_id', 'N:varchar(50)');
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