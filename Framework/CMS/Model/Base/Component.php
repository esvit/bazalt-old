<?php

namespace Framework\CMS\Model\Base;

abstract class Component extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'cms_components';

    const MODEL_NAME = 'Framework\CMS\Model\Component';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('id', 'PUA:int(10)');
        $this->hasColumn('name', 'varchar(255)');
        $this->hasColumn('dependencies', 'varchar(255)');
        $this->hasColumn('is_active', 'UN:tinyint(1)');
    }

    public function initRelations()
    {
        $this->hasRelation('Sites', new \Bazalt\ORM\Relation\Many2Many('Framework\CMS\Model\Site', 'component_id', 'Framework\CMS\Model\ComponentRefSite', 'site_id'));
        $this->hasRelation('Options', new \Bazalt\ORM\Relation\One2Many('Framework\CMS\Model\Option', 'id', 'component_id'));
        $this->hasRelation('Widgets', new \Bazalt\ORM\Relation\One2Many('Framework\CMS\Model\Widget', 'id', 'component_id'));
    }

    public function initPlugins()
    {
        $this->hasPlugin('Framework\CMS\ORM\Localizable', ['title', 'description']);
    }
}