<?php

namespace Framework\CMS\Model\Base;

abstract class Widget extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'cms_widgets';

    const MODEL_NAME = 'Framework\CMS\Model\Widget';

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
//        $this->hasColumn('title', 'varchar(50)');
//        $this->hasColumn('description', 'varchar(255)');
        $this->hasColumn('is_active', 'U:tinyint(1)|0');
    }

    public function initRelations()
    {
        $this->hasRelation('Instances', new \ORM_Relation_One2Many('Framework\CMS\Model\WidgetInstance', 'id', 'widget_id'));
        $this->hasRelation('Component', new \ORM_Relation_One2One('Framework\CMS\Model\Component', 'component_id', 'id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Framework\CMS\ORM\Localizable', ['title', 'description']);
    }
}