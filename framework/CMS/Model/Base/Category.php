<?php

abstract class CMS_Model_Base_Category extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'cms_categories';

    const MODEL_NAME = 'CMS_Model_Category';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('category_id', 'U:int(10)');
        $this->hasColumn('title', 'varchar(255)');
        $this->hasColumn('description', 'mediumtext');
        $this->hasColumn('alias', 'varchar(255)');
        $this->hasColumn('is_hidden', 'U:tinyint(1)|0');
        $this->hasColumn('is_publish', 'U:tinyint(1)');
    }

    public function initRelations()
    {
        $this->hasRelation('Elements', new ORM_Relation_NestedSet('CMS_Model_Category', 'category_id'));
        $this->hasRelation('Root', new ORM_Relation_One2One('CMS_Model_CategoryRoot', 'category_id', 'id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('CMS_ORM_Localizable', array(
                'fields' => array('title', 'alias', 'description'),
                'type' => CMS_ORM_Localizable::ROWS_LOCALIZABLE
            ));
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