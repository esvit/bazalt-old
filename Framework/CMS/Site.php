<?php

namespace Framework\CMS;

use Framework\Core\Helper\Url;

if (!defined('ENABLE_MULTISITING')) {
    define('ENABLE_MULTISITING', false);
}

class Site
{
    public static function getDomainName()
    {
        $domain = strToLower(Url::getDomain());
        // remove port
        if (strpos($domain, ':') !== false) {
            $domain = substr($domain, 0, strpos($domain, ':'));
        }
        if (substr($domain, 0, 4) == 'www.') {
            $domain = substr($domain, 4);
        }
        return $domain;
    }


    /**
     * Detect current site from domain name and redirect as required
     *
     * @throws CMS_Exception_DomainNotFound
     */
    public function __construct($domain = null)
    {
        if ($domain == null) {
            $domain = self::getDomainName();
        }

        if (!defined('ENABLE_MULTISITING') || !ENABLE_MULTISITING) {
            $site = Model\Site::getById(1);
            if (!$site) {
                $site = Model\Site::create();
                $site->id = 1;
                $site->domain = $domain;
            }
            $site->is_subdomain = false;
            $site->is_active = true;
            $site->save();
        } else {
            $site = CMS_Model_Site::getSiteByDomain($domain);
            if (!$site) {
                $wildcard = '*' . substr($domain, strpos($domain, '.'));
                $site = CMS_Model_Site::getSiteByDomain($wildcard);
                if ($site) {
                    $site->subdomain = substr($domain, 0, strpos($domain, '.'));
                }
            }
        }
        if (!CLI_MODE) {
            if ($site->is_redirect && $site->site_id) {
                Url::redirect(Url::getProtocol() . $site->Site->domain);
            }
        }
        if (!$site && !CLI_MODE) {
            throw new CMS_Exception_DomainNotFound($domain);
        } else if (!$site && CLI_MODE) {
            $site = CMS_Model_Site::getById(1);
        }
        if ($site->site_id != null && $site->is_subdomain) {
            $originalSite = $site;
            $site = CMS_Model_Site::getById($site->site_id);
            if (!$site) {
                throw new CMS_Exception_DomainNotFound($domain);
            }
            $site->originalSite = $originalSite;
            Session::Singleton()->cookieDomain('.' . $site->domain);
        }
        return $site;
    }
}