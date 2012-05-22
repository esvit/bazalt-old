<?php

class CMS_Model_Site extends CMS_Model_Base_Site
{
    public static function create()
    {
        $site = new CMS_Model_Site();
        if (!CLI_MODE) {
            $site->domain = $domain;
        }

        return $site;
    }

    public static function getUserSites()
    {
        $q = CMS_Model_Site::select();
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

    public static function getSiteByDomain($domain)
    {
        return CMS_Model_Site::select()
                             ->where('domain = ?', $domain)
                             ->fetch();
    }

    public static function createEmptySite($domain)
    {
        $site = new CMS_Model_Site();
        $site->title = $domain;
        $site->domain = $domain;
        $site->save();

        if (empty($site->id)) {
            throw new Exception('Cant create site');
        }
        CMS_Bazalt::Singleton()->OnSiteCreate($site);

        return $site;
    }

    public function getUrl()
    {
        $domain = $this->domain;

        $url = 'http://' . $domain;
        return $url;
    }
}