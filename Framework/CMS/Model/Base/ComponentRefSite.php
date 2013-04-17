<?php

namespace Framework\CMS\Model\Base;

abstract class ComponentRefSite extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'cms_components_ref_sites';

    const MODEL_NAME = 'Framework\CMS\Model\ComponentRefSite';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('component_id', 'PU:int(10)');
        $this->hasColumn('site_id', 'PU:int(10)');
    }
}