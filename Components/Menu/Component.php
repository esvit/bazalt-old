<?php

namespace Components\Menu;

use \Framework\CMS as CMS,
    \Framework\System\Routing\Route;

class Component extends CMS\Component implements CMS\Menu\HasItems
{
    public static function getName()
    {
        return 'Menu';
    }

    public function initComponent(CMS\Application $application)
    {
        if ($application instanceof \App\Site\Application) {
            $application->registerJsComponent('Component.Menu', relativePath(__DIR__ . '/component.js'));
        } else {
            $application->registerJsComponent('Component.Menu.Admin', relativePath(__DIR__ . '/admin.js'));
        }

        /*$controller = 'Components\Gallery\Controller\Index';
        
        Route::root()->connect('Gallery.List', '/gallery',                 ['component' => __CLASS__, 'controller' => $controller, 'action' => 'default'])
                     ->connect('Gallery.Album',        '/{album}',         ['component' => __CLASS__, 'controller' => $controller, 'action' => 'album'])
                     ->connect('Gallery.Photo',                '/{photo}', ['component' => __CLASS__, 'controller' => $controller, 'action' => 'photo']);
        */
    }

    public function getMenuTypes()
    {
        return [
            'Components\Menu\Menu\Link',
            'Components\Menu\Menu\MainPage'
        ];
    }
}