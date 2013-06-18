<?php

namespace Components\AdminPanel;

use \Framework\CMS as CMS;
use Bazalt\Routing\Route;

class Component extends CMS\Component
{
    const ACL_HAS_ACCESS = 1;

    public static function getName()
    {
        return 'AdminPanel';
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
                $application->registerJsComponent('Component.AdminPanel', relativePath(__DIR__ . '/component.js'));
            }
        } else {
            $application->registerJsComponent('Component.AdminPanel.Admin', relativePath(__DIR__ . '/admin.js'));
        }
    }
}
