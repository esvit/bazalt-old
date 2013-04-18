<?php

namespace Framework\CMS\Model\Base;

abstract class RoleRefComponent extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'cms_roles_ref_components';

    const MODEL_NAME = 'Framework\CMS\Model\RoleRefComponent';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('role_id', 'PU:int(10)|0');
        $this->hasColumn('component_id', 'PU:int(10)|0');
        $this->hasColumn('value', 'U:int(10)|0');
    }
}