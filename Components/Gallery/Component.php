<?php

namespace Components\Gallery;

use \Framework\CMS as CMS,
    \Framework\System\Routing\Route;

class Component extends CMS\Component implements CMS\Menu\HasItems
{
    const ACL_HAS_ACCESS = 1;

    public function getRoles()
    {
        return array(
            self::ACL_HAS_ACCESS => __('User can access to gallery', self::getName())
        );
    }

    public static function getName()
    {
        return 'Gallery';
    }

    public function initComponent(CMS\Application $application)
    {
        if ($application instanceof \App\Site\Application) {
            $application->registerJsComponent('Component.Gallery', relativePath(__DIR__ . '/component.js'));
        } else {
            $application->registerJsComponent('Component.Gallery.Admin', relativePath(__DIR__ . '/admin.js'));
        }

        $controller = 'Components\Gallery\Controller\Index';
        
        Route::root()->connect('Gallery.List', '/gallery',                 ['component' => self::getName(), 'controller' => $controller, 'action' => 'default'])
                     ->connect('Gallery.Album',        '/{album}',         ['component' => self::getName(), 'controller' => $controller, 'action' => 'album'])
                     ->connect('Gallery.Photo',                '/{photo}', ['component' => self::getName(), 'controller' => $controller, 'action' => 'photo']);
    }

    public function getMenuTypes()
    {
        return [
            'Components\Gallery\Menu\Album'
        ];
    }
}