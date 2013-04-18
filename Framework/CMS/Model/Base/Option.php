<?php

namespace Framework\CMS\Model\Base;

abstract class Option extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'cms_options';

    const MODEL_NAME = 'Framework\CMS\Model\Option';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('site_id', 'UN:int(10)');
        $this->hasColumn('component_id', 'UN:int(10)');
        $this->hasColumn('name', 'varchar(255)');
        $this->hasColumn('value', 'text');
    }

    public function initRelations()
    {
        $this->hasRelation('Components', new \ORM_Relation_One2One('Framework\CMS\Model\Component', 'component_id',  'id'));
    }
}