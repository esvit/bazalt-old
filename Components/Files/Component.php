<?php

namespace Components\Files;

use \Framework\CMS as CMS,
    \Framework\System\Routing\Route;

class Component extends CMS\Component
{
    const USERS_FOLDERS_OPTION = 'ComFileStorage.HaveUsersFolders';

    const ACL_HAVE_ACCESS = 1;
    const ACL_CAN_WRITE = 2;
    const ACL_CAN_REMOVE = 4;

    protected $mainMenu = null;

    public static function getName()
    {
        return 'Files';
    }

    public function getRoles()
    {
        return array(
            self::ACL_HAVE_ACCESS => __('User is granted to access to the file storage', self::getName()),
            self::ACL_CAN_WRITE => __('User is granted to modify files and folders on the system or users folders', self::getName()),
            self::ACL_CAN_REMOVE => __('User is granted to remove files and folders from the system or users folders', self::getName())
        );
    }

    public function initComponent(CMS\Application $application)
    {
        if ($application instanceof \App\Site\Application) {
            //$application->registerJsComponent('Component.Files', relativePath(__DIR__ . '/component.js'));
        } else {
            $application->registerJsComponent('Component.Files.Admin', relativePath(__DIR__ . '/admin.js'));
        }

        $controller = 'Components\Files\Controller\Index';

        $map = Route::root();

        $downloads = $map->connect('Files.Downloads', '/download',              ['component' => self::getName(), 'controller' => $controller, 'action' => 'viewFiles']);
        $downloads       ->connect('Files.File',               '/file{id:\d+}', ['component' => self::getName(), 'controller' => $controller, 'action' => 'downloadFile']);

        Route::root()->connect('Files.elFinder', '/elfinder/', ['component' => self::getName(), 'controller' => $controller, 'action' => 'elFinder'])
                     ->noIndex();
    }

    public function getMenuTypes()
    {
        return [
            'folder' => 'ComFileStorage_Menu_Folder'
        ];
    }
}