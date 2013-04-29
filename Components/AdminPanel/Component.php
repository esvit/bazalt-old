<?php

namespace Components\AdminPanel;

use \Framework\CMS as CMS;
use Framework\System\Routing\Route;

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
    }
}
