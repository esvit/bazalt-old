<?php

abstract class CMS_Model_Base_LanguageRefSite extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'cms_languages_ref_sites';

    const MODEL_NAME = 'CMS_Model_LanguageRefSite';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('language_id', 'PU:int(10)');
        $this->hasColumn('site_id', 'PU:int(10)');
        $this->hasColumn('is_default', 'U:tinyint(3)|0');
        $this->hasColumn('is_active', 'U:tinyint(3)|0');
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