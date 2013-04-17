<?php

namespace Framework\CMS\Model;
use Framework\System\ORM\ORM,
    Framework\CMS as CMS;

class Role extends Base\Role
{
    public static function create()
    {
        $role = new Role();
        $role->site_id = CMS\Bazalt::getSiteId();
        $role->system_acl = 0;

        return $role;
    }

    public static function getUsersByRole($role)
    {
        $q = ORM::select('Framework\CMS\Model\User u')
                ->innerJoin('Framework\CMS\Model\RoleRefUser ref', array('user_id', 'u.id'))
                ->where('ref.role_id = ?', $role->id);

        return $q->fetchAll();
    }

    public function getByName($name)
    {
        $q = ORM::select('Role r')
                ->where('name = ?', $name);

        return $q->fetch();
    }

    public function setComponentAccess($component, $value = 0)
    {
        $acl = Framework\CMS\Model\RoleRefComponent::getAcl($this->id, $component);
        if (!$acl) {
            $acl = Framework\CMS\Model\RoleRefComponent::create($this);
            if ($component != null) {
                $acl->component_id = $component->id;
            }
        }
        $acl->value = intval($value);
        $acl->save();
    }

    public function getAccessForComponent($component)
    {
        return Framework\CMS\Model\RoleRefComponent::getValue($this->id, $component);
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
        $q = ORM::select('Framework\CMS\Model\Role r')
                ->where('is_guest = ?', 1)
                ->andWhere('(site_id IS NULL OR site_id = ?)', CMS\Bazalt::getSiteId());

        return $q->fetchAll();
    }

    public static function getUserRolesBySite($userId, $siteId)
    {
        $q = ORM::select('Framework\CMS\Model\Role ft')
            ->innerJoin('Framework\CMS\Model\RoleRefUser ref',array('role_id', 'ft.id'))
            ->where('ref.user_id = ?', $userId)
            ->andWhere('ref.site_id = ?', $siteId);
        return $q->fetchAll();
    }

    public static function getSiteRoles($withSpecials = true, $withGuest = false, $viewHidden = false, $siteId = null)
    {
        if (!isset($siteId)) {
            $siteId = CMS\Bazalt::getSiteId();
        }
        $q = ORM::select('Framework\CMS\Model\Role r')
                ->orderBy('site_id ASC');

        if ($withSpecials) {
            $q->andWhere('(site_id IS NULL OR site_id = ?)', $siteId);
        } else {
            $q->andWhere('(site_id = ?)', $siteId);
        }

        if (!$withGuest) {
            $q->andWhere('is_guest = ?', 0);
        }
        
        if (!$viewHidden){
            $q->andWhere('is_hidden = ?', 0);
        }
        return $q->fetchAll();
    }

    public static function getUserAcl(User $user)
    {
        $q = ORM::select('Framework\CMS\Model\RoleRefComponent ref', 'ref.component_id, SUM(ref.value) AS acl')
                ->innerJoin('Framework\CMS\Model\RoleRefUser r', array('role_id', 'ref.role_id'))
                ->innerJoin('Framework\CMS\Model\User u', array('id', 'r.user_id'))
                ->where('u.id = ?', $user->id)
                ->groupBy('ref.component_id');

        $acls = $q->fetchAll('stdClass');

        return $acls;
    }

    public static function getBitmask($roles, $component)
    {
        $acls = array();

        $q = ORM::select('Framework\CMS\Model\RoleRefComponent a', 'a.value')
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