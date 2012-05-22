<?php

class Site_Service_AdminPanel extends CMS_Application_Service
{
    public function prepareUrl(&$url)
    {
        $user = CMS_User::getUser();
        if ($user->hasRight(null, CMS_Bazalt::ACL_GODMODE)) {
            Assets_JS::addPackage('jQuery Form');
            Assets_JS::addPackage('jQuery Tmpl');
            Assets_JS::addPackage('jQuery Cookie');

            $app = CMS_Application::current();
            $app->addWebservice('Site_Webservice_Widget');

            Event::register('Hooks', 'body_end', array(__CLASS__, 'outputAdminPanel'));
        }
    }

    public function outputAdminPanel()
    {
        $app = CMS_Application::current();
        $menu = $app->getAdminMenu();

        $view = $app->View;

        $app->addScript('cms/admin_panel.js');
        $app->addStyle('cms/admin_panel.css');
        $app->addStyle('jquery-ui-1.8.16.custom.css');
        if (!$menu) {
            return;
        }

        $quickLinks = $app->getAdminQuicklinksMenu();

        $url = ($_SERVER['SCRIPT_NAME'] == '/index.php') ? '/index_dev.php' . Url::getRequestUrl() : $_SERVER['PATH_INFO'];
        $title = ($_SERVER['SCRIPT_NAME'] == '/index.php') ? 'DEV Mode' : 'PROD Mode';
        $quickLinks->addItem($title, $url);

        $view->assign('adminMenu', $menu);
        $view->assign('quickLinks', $quickLinks);
        $view->display('cms/admin_panel');
        $view->display('cms/widgets/base');
    }
}
