<?php

define('THEMES_DIR', SITE_DIR . '/themes');

class Site_App extends CMS_Application
{
    protected $adminMenu = null;

    protected $adminQuicklinksMenu = null;

    public function start()
    {
        //try {
            return parent::start();
        //} catch (CMS_Exception_DomainNotFound $ex) {
        //}
    }
    
    public function initRoutes()
    {
        $res = parent::initRoutes();

        $this->route->connect('/test', array('controller' => 'Default'))
             ->name('Site.Test');

        return $res;
    }

    public function init()
    {
        parent::init();

        CMS_Service_Manager::register('Site_Service_Paging', array('application' => $this));
        if (defined('ENABLE_ADMINPANEL') && ENABLE_ADMINPANEL) {
            CMS_Service_Manager::register('Site_Service_AdminPanel', array('application' => $this));
        }

        // registed services from db
        $services = CMS_Model_Services::getSiteServices();
        foreach ($services as $service) {
            $config = $service->config;
            $config['component'] = CMS_Bazalt::getComponent($service->component_name);

            CMS_Service_Manager::register($service->className, $config);
        }
        // init all services
        CMS_Service_Manager::initServices($this->url);
        $this->view->assignGlobal('url', $this->url);

        $path = PUBLIC_DIR . '/sites/' . $_SERVER['HTTP_HOST'];
        if (!file_exists($path)) {
            $theme = CMS_Theme::getCurrentTheme();
            if ($theme && !symlink($theme->getPath(), $path)) {
                //echo 'cant create link';
            }
        }

        Assets_JS::addPackage('Pines Notify');

        $this->view->assignGlobal('SiteName', CMS_Option::get(CMS_Bazalt::SITENAME_OPTION));

        $siteHost = 'http://' . CMS_Option::get(CMS_Bazalt::SITEHOST_OPTION);

        $this->view->assignGlobal('site_host', $siteHost);
        $fullUrl = $siteHost . $this->url;
        $this->view->assignGlobal('full_url', $fullUrl);
    }

    public function getAdminMenu()
    {
        if (!$this->adminMenu) {
            $user = CMS_User::getUser();
            if (!$user) {
                return null;
            }

            $this->adminMenu = new CMS_Menu();

            $login = '';
            if ($user->getPhoto()) {
                $avatar = $user->getAvatar('AdminPanelAvatar'); 
                if (!empty($avatar)) { 
                    $login = '<img width="16" height="16" class="avatar avatar-16 photo" src="' . $avatar . '">';
                } else { 
                    $login = '<span class="ui-icon ui-icon-person"></span>';
                } 
            } 
            $login .= $user->login;
            
            $userMenu = $this->adminMenu->addItem($login, 'javascript:;');

            $userMenu->addItem(__('Profile', __CLASS__), CMS_Mapper::urlFor('CMS.Profile'));
            $userMenu->addItem(__('Administration panel', __CLASS__), '/admin/');
            $userMenu->addItem(__('Logout', __CLASS__), CMS_Mapper::urlFor('CMS.Logout'));

            $manageWidgets = '<span class="ui-icon ui-icon-gear"></span> Manage widgets';
            $manageMenu = $this->adminMenu->addItem($manageWidgets, 'javascript:;')
                            ->id('cms-show-manage-widgets')
                            ->addCss('bz-adminpanel-toggle');

            if ($_COOKIE['cms-show-manage-widgets'] == 'true') {
                $manageMenu->addCss('bz-adminpanel-active');
            }
        }
        return $this->adminMenu;
    }

    public function getAdminQuicklinksMenu()
    {
        if (!$this->adminQuicklinksMenu) {
            $this->adminQuicklinksMenu = new CMS_Menu();
        }
        return $this->adminQuicklinksMenu;
    }
}
