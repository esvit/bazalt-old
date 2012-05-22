<?php

abstract class CMS_Model_Base_WidgetTemplates extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'cms_widgets_templates';

    const MODEL_NAME = 'CMS_Model_WidgetTemplates';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('theme_id', 'U:int(10)');
        $this->hasColumn('widget_id', 'U:int(10)');
        $this->hasColumn('name', 'N:varchar(255)');
        $this->hasColumn('title', 'N:varchar(255)');
    }

    public function initRelations()
    {
        // $this->hasRelation('Widget', new ORM_Relation_One2One('CMS_Model_Widget', 'widget_id', 'id'));
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