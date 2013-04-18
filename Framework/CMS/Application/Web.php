<?php

namespace Framework\CMS\Application;

use Framework\CMS as CMS,
    Framework\Core\Event;

abstract class Web extends CMS\Application
{
    const DASHBOARD_GROUP = 'Dashboard';

    const GENERAL_GROUP = 'General';

    const DEVELOPER_GROUP = 'Developing';

    const SETTINGS_GROUP = 'Settings';

    /**
     * Admin mapper
     *
     * @see ADMIN_URL
     */
    protected static $mappers = array();

    protected $menu = null;

    protected $adminMapper = null;

    protected $settingsMenu = null;
    protected $settingsSubmenu = null;

    public function __construct($config = array())
    {
        parent::__construct($config);

        Event::register('CMS_Theme', 'OnThemeSet', array($this, 'onThemeSet'));

        if ($this->request) {
            $this->url = $this->request->url();
        }
    }

    /**
     * if domain does not exists
     */
    public function start()
    {
        try {
            return parent::start();
        } catch (CMS_Exception_DomainNotFound $ex) {
            $this->initView();
            $this->view->assign('domain', $ex->getDomain());
            $this->view->display('error.domain_notfound');
            CMS_Response::notFound();
        }
    }

    public function init()
    {
        parent::init();

        //$locale = Locale_Config::setUserLocale();

        $site = CMS\Bazalt::getSite();
        Metatags::set('SITE_TITLE', $site->title);
        $this->view->assignGlobal('site', $site);

        // global variable url in templates
        $this->view->assignGlobal('url', $this->url);

        // current domain
        $siteHost = 'http://' . $site->domain;
        $this->view->assignGlobal('site_host', $siteHost);

        // full url for current page
        $fullUrl = $siteHost . $this->url;
        $this->view->assignGlobal('full_url', $fullUrl);

        // languages
        $language = CMS_Language::getCurrentLanguage();
        $languages = CMS_Language::getLanguages();
        $this->view->assignGlobal('language', $language);
        $this->view->assignGlobal('languages', $languages);

        $currentLocale = Locale_Config::getLanguage();
        $allowLocale = false;
        foreach ($languages as $lang) {
            if ($lang->alias == $currentLocale) {
                $allowLocale = true;
                break;
            }
        }
        if (!$allowLocale) {
            Locale_Config::setLocale($language->alias);
        }

        $this->view->assignGlobal('profile_user', CMS_User::getUser());

        $this->initMenu();

        foreach ($this->components as $component) {
            $component->initComponent($this);
        }

        $this->addWebservice('CMS_Webservice_Language');
    }

    public function initMenu()
    {
        $this->menu = new CMS_Menu();

        $this->settingsMenu = new CMS_Menu_Item(__('Settings', __CLASS__), $settingUrl);
        $this->settingsSubmenu = $this->settingsMenu->addItem(__('Settings', __CLASS__), $settingUrl)
            ->addOption('icon-class', 'menu-image-settings');

        $this->view->assignByRef('adminMenu', $this->menu);
    }

    public function showPage(&$content = null)
    {
        if ($this->menu && $this->user->hasRight(null, CMS_Bazalt::ACL_CAN_CHANGE_SETTING)) {
            $this->menu->addSeparator();
            $this->menu->addMenuItem($this->settingsMenu);
        }
        parent::showPage($content);
    }

    /**
     * Повертає мапер для компонента
     */
    public function getMapper($name = null)
    {
        if (!$this->adminMapper) {
            $url = ($this->config['urlPrefix'] == '/admin/') ? '/' : '/admin/';
            $this->adminMapper = $this->applicationMapper->submapper($url, array(
                    'controller' => 'Admin', 'action' => 'Default')
            );
        }
        if ($name === null) {
            return parent::getMapper();
        }
        if (!array_key_exists($name, self::$mappers)) {

            $mapper = $this->adminMapper->submapper('/' . $name, array('component' => $name,
                    'controller' => 'Admin',
                    'action' => 'Default')
            );

            self::$mappers[$name] = $mapper;
        }
        return self::$mappers[$name];
    }
}