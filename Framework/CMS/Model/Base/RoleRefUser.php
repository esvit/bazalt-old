<?php

namespace Framework\CMS\Model\Base;

abstract class RoleRefUser extends \Framework\CMS\ORM\Record
{
    const TABLE_NAME = 'cms_roles_ref_users';

    const MODEL_NAME = 'Framework\CMS\Model\RoleRefUser';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME, self::MODEL_NAME);
    }

    protected function initFields()
    {
        $this->hasColumn('user_id', 'PU:int(10)');
        $this->hasColumn('role_id', 'PU:int(10)');
        $this->hasColumn('site_id', 'PU:int(10)');
    }
}