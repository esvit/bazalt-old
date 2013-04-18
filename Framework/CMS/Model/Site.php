<?php

namespace Framework\CMS\Model;
use Framework\System\ORM\ORM;

/**
 * @property Site|null originalSite Оригінальний сайт, з якого був здійснений редірект
 */
class Site extends Base\Site
{
    public static function create()
    {
        $site = new Site();
        /*$user = Framework\CMS\User::getUser();
        if (!$user->isGuest()) {
            $site->user_id = $user->id;
        }*/
        return $site;
    }

    public static function getUserSites()
    {
        $q = Site::select();
        $user = CMS_User::getUser();

        if (!$user->hasRight(null, CMS_Bazalt::ACL_GODMODE)) {
            $q->innerJoin('CMS_Model_SiteRefUser ref', array('site_id', 'id'))
              ->where('ref.user_id = ?', CMS_User::getUser()->id);
        }
        return $q->fetchAll();
    }

    public static function getUserSite($id)
    {
        $q = CMS_Model_Site::select()->where('id = ?', intval($id));
        
        $user = User::getUser();
        if (!$user->hasRight(null, CMS_Bazalt::ACL_GODMODE)) {
            $q->innerJoin('CMS_Model_SiteRefUser ref', array('site_id', 'id'))
              ->andWhere('ref.user_id = ?', CMS_User::getUser()->id);
        }
        return $q->fetch();
    }

    public static function getCollection()
    {
        $q = ORM::select('CMS_Model_Site f')
                ->where('site_id IS NULL AND is_redirect = ?', 0);
        return new CMS_ORM_Collection($q);
    }

    public static function getSiteByDomain($domain, $onlyActive = true)
    {
         $q = ORM::select('Framework\CMS\Model\Site s')
                 ->where('s.domain = ?', $domain);

         if ($onlyActive) {
             $q->andWhere('s.is_active = ?', 1);
         }
         return $q->fetch();
    }

    public static function createEmptySite($domain)
    {
        $site = new Site();
        $site->title = $domain;
        $site->domain = $domain;
        $site->save();

        if (empty($site->id)) {
            throw new \Exception('Cant create site');
        }
        //CMS_Bazalt::Singleton()->OnSiteCreate($site);

        return $site;
    }

    public function getUrl()
    {
        return 'http://' . $this->domain . $this->path;
    }

    public static function getSitesWithPrivileges($user, $mask, $component = null)
    {
        $q = ORM::select('CMS_Model_Site s', 's.*');

        if (!$user->is_god) {
            $q->innerJoin('CMS_Model_RoleRefUser ref', array('site_id', 's.id'))
                ->innerJoin('CMS_Model_Role r', array('id', 'ref.role_id'))
                ->where('ref.user_id = ?', (int)$user->id)
                ->andWhere('r.system_acl & ?', (int)$mask)
                ->groupBy('s.id');
        }

        if ($component) {
            throw new \Exception('TODO :)');
        }
        return $q->fetchAll();
    }

    public function getMirrors()
    {
        return self::getSiteMirrors($this);
    }

    public function addLanguage(Language $language)
    {
        $this->Languages->add($language, array('is_active' => 1));
    }

    public static function getSiteMirrors($site)
    {
        $siteId = $site->id;
        if ($site->site_id) {
            $siteId = $site->site_id;
        }
        $mirrors = ORM::select('CMS_Model_Site s')
            ->where('s.site_id = ?', $siteId)
            ->fetchAll();
        return $mirrors;
    }
}