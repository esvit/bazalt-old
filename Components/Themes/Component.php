<?php

namespace Components\Themes;

use \Framework\CMS as CMS;
use Framework\System\Routing\Route;

class Component extends CMS\Component
{
    public function initComponent(CMS\Application $application)
    {
        if ($application instanceof \App\Site\Application) {
            //$application->registerJsComponent('Component.Themes', relativePath(__DIR__ . '/component.js'));
        } else {
            $application->registerJsComponent('Component.Themes.Admin', relativePath(__DIR__ . '/admin.js'));
        }
    }
}
