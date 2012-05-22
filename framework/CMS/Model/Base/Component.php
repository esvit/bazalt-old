<?php

abstract class CMS_Model_Base_Component extends CMS_Model_Base_Record
{
    const TABLE_NAME = 'cms_components';

    const MODEL_NAME = 'CMS_Model_Component';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('name', 'varchar(255)');
        $this->hasColumn('title', 'varchar(255)');
        $this->hasColumn('is_active', 'UN:tinyint(1)');
        $this->hasColumn('have_hooks', 'UN:tinyint(1)');
    }

    public function initRelations()
    {
        $this->hasRelation('Sites', new ORM_Relation_Many2Many('CMS_Model_Site', 'component_id', 'CMS_Model_ComponentRefSite', 'site_id'));
        $this->hasRelation('Options', new ORM_Relation_One2Many('CMS_Model_Option', 'id', 'component_id'));
        $this->hasRelation('Services', new ORM_Relation_One2Many('CMS_Model_Service', 'id', 'component_id'));
        $this->hasRelation('Widgets', new ORM_Relation_One2Many('CMS_Model_Widget', 'id', 'component_id'));
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