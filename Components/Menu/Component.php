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
        /*$controller = 'Components\Gallery\Controller\Index';
        
        Route::root()->connect('Gallery.List', '/gallery',                 ['component' => __CLASS__, 'controller' => $controller, 'action' => 'default'])
                     ->connect('Gallery.Album',        '/{album}',         ['component' => __CLASS__, 'controller' => $controller, 'action' => 'album'])
                     ->connect('Gallery.Photo',                '/{photo}', ['component' => __CLASS__, 'controller' => $controller, 'action' => 'photo']);
        */
    }

    public function getMenuTypes()
    {
        return [
            'Link' => 'Components\Menu\Menu\Link'
        ];
    }
}