<?php

namespace App\Site;

use Framework\CMS as CMS;
use Framework\System\Session\Session;

define('THEMES_DIR', SITE_DIR . '/themes');

class Application extends CMS\Application
{
    public function init()
    {
        parent::init();

        $folders = $this->view->folders();

        $themePath = THEMES_DIR . PATH_SEP . CMS\Bazalt::getSite()->theme_id;
        $this->view->assign('themeUrl', relativePath($themePath));

        $this->view->assign('user', CMS\User::get());

        $folders []= __DIR__ . PATH_SEP . 'views';
        $folders []= $themePath . PATH_SEP . 'views';

        $this->view->folders($folders);

        CMS\Route::root()->param('controller', 'App\Site\Controller\Index');

        if (!CLI_MODE) {
            $content = $this->dispatch($this->url);

            $this->showPage($content);
        }
    }

    public function showPage(&$content = null)
    {
        parent::showPage($content);

        $user = CMS\User::get();
        $hasWidgetsRights = $user->hasRight(null, CMS\Bazalt::ACL_CAN_ADMIN_WIDGETS);
        $this->view->assign('canManageWidgets', $hasWidgetsRights);

        if ($hasWidgetsRights) {
            $widgets = CMS\Model\Widget::getActiveWidgets();
            $this->view->assign('widgets', $widgets);

            $this->view->assign('widgetsOn', isset($_COOKIE['cms-show-manage-widgets']) && ($_COOKIE['cms-show-manage-widgets'] == 'true'));
           //$this->view->display('admin/panel');
        }
    }
}