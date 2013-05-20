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

    public static function recompileLess($file, CMS\Model\Theme $theme)
    {
        using('Framework.Vendors.lessphp');
        $less = new \lessc();
        $less->addImportDir(SITE_DIR . '/themes/' . $theme->id . '/assets/less');
        $less->addImportDir(SITE_DIR . '/assets/components/bootstrap/less');
        $less->setVariables((array)$theme->settings);

        $content = $less->compileFile($file);
        file_put_contents($file . '.css', $content);
    }
}
