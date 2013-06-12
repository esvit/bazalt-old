<?php

namespace Framework\CMS\Model;
use Bazalt\ORM;

class RoleRefComponent extends Base\RoleRefComponent
{
    public static function create(Role $role)
    {
        $acl = new RoleRefComponent();
        $acl->role_id = $role->id;

        return $acl;
    }

    public static function getAcl($roleId, $component)
    {
        $q = ORM::select('Framework\CMS\Model\RoleRefComponent a')
                ->where('role_id = ?', $roleId)
                ->andWhere('component_id = ?', $component->id);

        return $q->fetch();
    }

    public static function getValue($roleId, $component)
    {
        $acl = self::getAcl($roleId, $component);

        return ($acl) ? $acl->value : 0;
    }
}