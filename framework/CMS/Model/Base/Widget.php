<?php

abstract class CMS_Model_Base_Widget extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'cms_widgets';

    const MODEL_NAME = 'CMS_Model_Widget';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('component_id', 'U:int(10)');
        $this->hasColumn('className', 'varchar(50)');
        $this->hasColumn('default_template', 'N:varchar(255)');
        $this->hasColumn('title', 'varchar(50)');
        $this->hasColumn('description', 'varchar(255)');
        $this->hasColumn('is_active', 'U:tinyint(1)|0');
    }

    public function initRelations()
    {
        $this->hasRelation('Instances', new ORM_Relation_One2Many('CMS_Model_WidgetInstance', 'id', 'widget_id'));
        $this->hasRelation('Component', new ORM_Relation_One2One('CMS_Model_Component', 'component_id', 'id'));
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