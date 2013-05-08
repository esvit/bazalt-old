<?php

namespace Components\Seo;

use \Framework\CMS as CMS;
use Framework\System\Routing\Route,
    Framework\Core\Helper\Url;

class Component extends CMS\Component
{
    const ACL_HAS_ACCESS = 1;

    public static function getName()
    {
        return 'Seo';
    }

    public function getRoles()
    {
        return array(
            self::ACL_HAS_ACCESS => __('User can manage pages', __CLASS__)
        );
    }

    public function initComponent(CMS\Application $application)
    {
        if ($application instanceof \App\Site\Application) {
            if (!CMS\User::get()->isGuest()) {
                $application->registerJsComponent('Component.Seo', relativePath(__DIR__ . '/component.js'));
            }
        } else {
            $application->registerJsComponent('Component.Seo.Admin', relativePath(__DIR__ . '/admin.js'));
        }

        $url = Url::getRequestUrl();
        $page = Model\Page::getByUrl($url);

        if ($page) {
            CMS\MetaInfo::title($page->title);
            CMS\MetaInfo::keywords($page->keywords);
            CMS\MetaInfo::description($page->description);
        }
    }
}
