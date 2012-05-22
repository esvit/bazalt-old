<?php

abstract class CMS_Model_Base_CategoryLocale extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'cms_categories_locale';

    const MODEL_NAME = 'CMS_Model_CategoryLocale';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PU:int(10)');
        $this->hasColumn('lang_id', 'PU:int(10)');
        $this->hasColumn('title', 'varchar(255)');
        $this->hasColumn('alias', 'varchar(255)');
        $this->hasColumn('description', 'mediumtext');
        $this->hasColumn('completed', 'tinyint(4)|0');
    }

    public function initRelations()
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
