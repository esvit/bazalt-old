<?php

class CMS_Model_RoleRefComponent extends CMS_Model_Base_RoleRefComponent
{
    public static function create(CMS_Model_Role $role)
    {
        $acl = new CMS_Model_RoleRefComponent();
        $acl->role_id = $role->id;

        return $acl;
    }

    public static function getAcl($roleId, $component)
    {
        $q = ORM::select('CMS_Model_RoleRefComponent a')
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