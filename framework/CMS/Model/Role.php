<?php

class CMS_Model_Role extends CMS_Model_Base_Role
{
    public static function create()
    {
        $role = new CMS_Model_Role();
        $role->site_id = CMS_Bazalt::getSiteId();
        $role->system_acl = 0;

        return $role;
    }

    public function getByName($name)
    {
        $q = ORM::select('CMS_Model_Role r')
                ->where('name = ?', $name);

        return $q->fetch();
    }

    public function setComponentAccess($component, $value = 0)
    {
        $acl = CMS_Model_RoleRefComponent::getAcl($this->id, $component);
        if (!$acl) {
            $acl = CMS_Model_RoleRefComponent::create($this);
            if ($component != null) {
                $acl->component_id = $component->id;
            }
        }
        $acl->value = intval($value);
        $acl->save();
    }

    public function getAccessForComponent($component)
    {
        return CMS_Model_RoleRefComponent::getValue($this->id, $component);
    }

    public function getCMSAccess()
    {
        return $this->system_acl;
    }

    public function setCMSAccess($value)
    {
        $this->system_acl = $value;
        $this->save();
    }

    public static function getGuestRoles()
    {
        $q = ORM::select('CMS_Model_Role r')
                ->where('is_guest = ?', 1)
                ->andWhere('(site_id IS NULL OR site_id = ?)', CMS_Bazalt::getSiteId());

        return $q->fetchAll();
    }

    public static function getSiteRoles($withSpecials = true, $withGuest = false)
    {
        $q = ORM::select('CMS_Model_Role r')
                ->orderBy('site_id ASC');

        if ($withSpecials) {
            $q->andWhere('(site_id IS NULL OR site_id = ?)', CMS_Bazalt::getSiteId());
        } else {
            $q->andWhere('(site_id = ?)', CMS_Bazalt::getSiteId());
        }

        if (!$withGuest) {
            $q->andWhere('is_guest = ?', 0);
        }

        return $q->fetchAll();
    }

    public static function getUserAcl(CMS_Model_User $user)
    {
        $q = ORM::select('CMS_Model_RoleRefComponent ref', 'ref.component_id, SUM(ref.value) AS acl')
                ->innerJoin('CMS_Model_RoleRefUser r', array('role_id', 'ref.role_id'))
                ->innerJoin('CMS_Model_User u', array('id', 'r.user_id'))
                ->where('u.id = ?', $user->id)
                ->groupBy('ref.component_id');

        $acls = $q->fetchAll('stdClass');

        return $acls;
    }

    public static function getBitmask($roles, $component)
    {
        $acls = array();

        $q = ORM::select('CMS_Model_RoleRefComponent a', 'a.value')
                ->andWhereIn('a.role_id', $roles)
                ->andWhere('a.component_id = ?', $component->id);

        $acls = $q->fetchAll();

        // merge roles
        $res = 0;
        foreach ($acls as $acl) {
            $res |= $acl->value;
        }
        return $res;
    }
}