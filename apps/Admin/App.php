<?php

define('THEMES_DIR', dirname(__FILE__) . '/themes');

Html_Form::registerElement('settingsgroup',         'Admin_Form_Element_SettingsGroup');
Html_Form::registerElement('alert',                 'CMS_Form_Element_Alert');
//Html_Form::registerElement('password',              'Admin_Form_Element_Password');

class Admin_App extends CMS_Application
{
    const DASHBOARD_GROUP = 'Dashboard';

    const GENERAL_GROUP = 'General';

    const DEVELOPER_GROUP = 'Developing';

    const SETTINGS_GROUP = 'Settings';

    const ADMIN_LANGUAGE_OPTION = 'Admin.Language';

    protected $menu = array();

    protected $settingsMenu = null;
    protected $settingsSubmenu = null;

    /**
     * Admin mapper
     *
     * @see ADMIN_URL
     */
    protected static $mappers = array();

    public function initRoutes()
    {
        /* Sign In */
        $this->applicationMapper->connect('/login', array('action' => 'login'))
                     ->name('Admin.SignIn');

        $this->applicationMapper->SubmapperRoute
                                ->authorizationRequest(CMS_Mapper::urlFor('Admin.SignIn'))
                                ->allow(null, CMS_Bazalt::ACL_CAN_LOGIN);

        /* PHP Info */
        $r = $this->applicationMapper->connect('/phpinfo', array('action' => 'phpinfo'))
                     ->authorizationRequest(CMS_Mapper::urlFor('Admin.SignIn'))
                     ->allow(null, CMS_Bazalt::ACL_CAN_LOGIN)
                     ->name('Admin.PHPInfo');

        /* Settings */
        if ($this->user->hasRight(null, CMS_Bazalt::ACL_CAN_CHANGE_SETTING)) {
            $this->applicationMapper->connect('/settings', array('action' => 'settings'))
                         ->name('Admin.Settings')
                         ->authorizationRequest();

            $this->applicationMapper->connect('/mail-settings', array('action' => 'mailSettings'))
                         ->name('Admin.MailSettings')
                         ->authorizationRequest();

            $this->applicationMapper->connect('/components', array('action' => 'components'))
                         ->name('Admin.Components')
                         ->authorizationRequest();
        }

        $this->applicationMapper->connect('/about', array('action' => 'about'))
                     ->name('about')
                     ->authorizationRequest();

        parent::initRoutes();
    }

    /**
     * Перевіряє чи користувач залогінений
     */
    private function checkLogin()
    {
        $user = CMS_User::getUser();
        if (CMS_User::isLogined() && $user->hasRight(null, CMS_Bazalt::ACL_CAN_LOGIN)) {
            Session::Singleton()->last_activity = date('Y-m-d H:i:s');
        } else {
            $loginUrl = CMS_Mapper::urlFor('Admin.SignIn');
            if (substr(DataType_Url::getRequestUrl(), 0, strlen($loginUrl)) != $loginUrl) {
                Session::Singleton()->backUrl = Datatype_Url::getRequestUrl();
                Url::redirect($loginUrl);
            }
        }
    }

    public function onThemeSet($theme)
    {
        parent::onThemeSet($theme);

        $folders = Html_Form::getView()->getFolders();
        $folders['theme'] = $theme->getPath() . '/templates';
        Html_Form::getView()->setFolders($folders);

        CMS_Locale::hasLocale($theme->getPath() . '/locale', __CLASS__);
    }

    public function setTheme()
    {
        $theme = CMS_Model_Theme::create(THEMES_DIR . '/default');
        CMS_Theme::setCurrentTheme($theme);
    }

    public function init()
    {
        parent::init();

        if ($this->user->hasRight(null, CMS_Bazalt::ACL_GODMODE)) {
        }

        // set interface language
        Locale::setLocale(CMS_Option::get(self::ADMIN_LANGUAGE_OPTION, 'ru'));

        $this->checkLogin();

        if (CMS_User::isLogined()) {
            $this->initMenu();
            $profile = Logger::start(__CLASS__, __FUNCTION__);

            foreach ($this->components as $component) {
                $component->initBackend($this);
            }
            Logger::stop($profile);
        }
        
        CMS_ORM_Localizable::setCompleteFlag();
        if (Session::Singleton()->currentLocale) {
            $lang = CMS_Model_Language::getById(Session::Singleton()->currentLocale);
            CMS_ORM_Localizable::setLanguage($lang);
        }

        $this->view->assignGlobal('admin_menu_folded', (isset($_COOKIE['AdminMenu_Folded']) && ($_COOKIE['AdminMenu_Folded'] == 1)));
        $this->view->assignGlobal('cms_secretKey', CMS_Bazalt::getSecretKey());

        if ($this->user->hasRight(null, CMS_Bazalt::ACL_CAN_LOGIN)) {
            $this->addWebservice('Admin_Webservice_Main');
        }
    }

    public function initMenu()
    {
        $this->menu = new CMS_Menu();
        $this->menu->addItem(self::DASHBOARD_GROUP)
                   ->addItem(__('Dashboard', __CLASS__), CMS_Mapper::urlFor('Admin.Home'))
                   ->addOption('icon-class', 'menu-image-dashboard');

        if ($this->user->hasRight(null, CMS_Bazalt::ACL_CAN_CHANGE_SETTING)) {
            $settingUrl = CMS_Mapper::urlFor('Admin.Settings');
        } else {
            $settingUrl = CMS_Mapper::urlFor('Admin.Home');
        }
        $this->settingsMenu = new CMS_Menu_Item(__('Settings', __CLASS__), $settingUrl);
        $this->settingsSubmenu = $this->settingsMenu->addItem(__('Settings', __CLASS__), $settingUrl)
                                                    ->addOption('icon-class', 'menu-image-settings');

        if ($this->user->hasRight(null, CMS_Bazalt::ACL_CAN_CHANGE_SETTING)) {
            $this->settingsSubmenu->addItem(__('General settings', __CLASS__), $settingUrl);

            if ($this->user->hasRight(null, CMS_Bazalt::ACL_GODMODE)) {
                $this->settingsSubmenu->addItem(__('Outgoing email settings', __CLASS__), CMS_Mapper::urlFor('Admin.MailSettings'));
            }
        }

        $this->view->assignByRef('adminMenu', $this->menu);
    }

    public function showPage(&$content = null)
    {
        if ($this->menu && $this->user->hasRight(null, CMS_Bazalt::ACL_CAN_CHANGE_SETTING)) {
            $this->menu->addSeparator();
            $this->menu->addMenuItem($this->settingsMenu);
        }

        //if (!CMS_Request::isAjax()) {
            $page = Admin_TagsParser::prepare($content);
            $content = $page->output();
            parent::showPage($content);
        //}
    }

    public function onPageNotFound($url)
    {
        $this->view->display('error404');
    }

    /**
     * Повертає мапер для компонента
     */
    public function getMapper($name = null)
    {
        if ($name == null) {
            return parent::getRootMapper();
        }
        if (!array_key_exists($name, self::$mappers)) {

            $mapper = $this->applicationMapper->submapper('/' . $name, array('component' => $name, 
                                                                             'controller' => 'Admin', 
                                                                             'action' => 'Default')
            );

            self::$mappers[$name] = $mapper;
        }
        return self::$mappers[$name];
    }

    public function getMenu()
    {
        return $this->menu;
    }

    public function addGroup($groupName, $item = null)
    {
        $this->menu->addSeparator();
        if ($item == null) {
            return $this->menu->addItem($groupName);
        } else {
            return $this->menu->addMenuItem($item);
        }
    }

    public function getGroup($groupName)
    {
        if ($groupName == self::SETTINGS_GROUP) {
            return $this->settingsMenu;
        }
        if ($item = $this->menu->hasItem($groupName)) {
            return $item;
        }
        return $this->addGroup($groupName);
    }

    public function addDashboardBlock(Admin_Dashboard_Block $block)
    {
        Admin_Dashboard::getInstance()->addBlock($block);
    }

    protected function dispatch($url)
    {
        /*if (CMS_Request::isAjax()) {
            $url = $_GET['href'];
            $_SERVER['REQUEST_URI'] = $url;
            
            $content = parent::dispatch($url);

            using('Application.Libs.TagsParser');
            $page = TagsParser::prepare($content);
            $content = $page->output();

            $response = new stdClass;
            $response->title = Metatags::Singleton()->getTitle();
            $response->body = $content;
            echo json_encode($response);
            exit;
        }*/

        // document type
        return parent::dispatch($url);
    }
}
