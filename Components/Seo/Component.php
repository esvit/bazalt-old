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
            CMS\MetaInfo::registerMetaGenerator('SEO', function(&$metainfo, $_meta) use ($application) {
                $_meta->assign('site_title', CMS\Bazalt::getSite()->title);

                $page = Model\Page::getByUrl($application->url());

                if ($page) {
                    $metainfo['title'] = $page->title;
                    $metainfo['keywords'] = $page->keywords;
                    $metainfo['description'] = $page->description;
                }

                if (!$page || ($page && (empty($page->title) || empty($page->keywords) || empty($page->description)))) {
                    $route = Model\Route::getByName($application->route()->name());

                    if ($route) {
                        $metainfo['title'] = $route->title;
                        $metainfo['keywords'] = $route->keywords;
                        $metainfo['description'] = $route->description;
                    }
                }
            });
        } else {
            $application->registerJsComponent('Component.Seo.Admin', relativePath(__DIR__ . '/admin.js'));
        }
    }
}
