<?php

abstract class CMS_Application extends Object implements ISingleton, IEventable
{
    protected $view = null;

    protected $mapper;

    protected $applicationMapper;

    protected $url;

    protected $name;

    protected $user;

    protected $request;

    protected $config = array();

    protected $components = array();

    public $eventOnPageComplete = Event::EMPTY_EVENT;

    public static function current()
    {
        return CMS_Bootstrap::getCurrentApplication();
    }

    public function __construct($name, CMS_Request $request, $config = array())
    {
        if (!$request) {
            throw new InvalidArgumentException('Request cannot be null');
        }
        $this->name = $name;

        $this->config = $config;

        $this->request = $request;

        Event::register('CMS_Theme', 'OnThemeSet', array($this, 'onThemeSet'));

        $this->url = $this->request->url();
    }

    public function name()
    {
        return $this->name;
    }

    public function config()
    {
        return $this->config;
    }

    public function getMapper()
    {
        return $this->applicationMapper;
    }

    public function getRootMapper()
    {
        return $this->mapper;
    }

    // temp
    public function getRoute()
    {
        return $this->getMapper();
    }

    public function connectComponents()
    {
        $this->components = CMS_Bazalt::connectComponents();
        return $this->components;
    }

    public function initView()
    {
        $this->view = new CMS_View();
    }

    /**
     * Add js script
     */
    public function addScript($file)
    {
        Scripts::add($this->config['path'] . '/media/scripts/' . $file, __CLASS__ . $file);
    }

    /**
     * Add css styles
     */
    public function addStyle($file)
    {
        Assets_CSS::add($this->config['path'] . '/media/styles/' . $file);
    }

    public function initRoutes()
    {
        $profile = Logger::start(__CLASS__, __FUNCTION__);
        $components = CMS_Model_Component::getComponentsForSite();

        foreach ($components as $component) {
            $routesFile = SITE_DIR . '/components/' . strtolower($component->name) . '/routes.inc';
            if (file_exists($routesFile)) {
                require_once $routesFile;
            }
        }

        foreach($components as $component) {
            if (class_exists($component->name . 'Routes')) {
                call_user_func(array($component->name.'Routes', 'init'), $this->mapper);
            }
        }

        Logger::stop($profile);
    }

    public function hasLocale($folder, $domain)
    {
        if (empty($folder)) {
            throw new Exception('You must enter folder name for locale');
        }
        $localeFolder = $this->config['path'] . PATH_SEP . $folder;

        Locale_Translation::bindTextDomain($localeFolder, $domain);

        return $this;
    }

    public function init()
    {
        Locale::setUserLocale();

        CMS_Theme::getCurrentTheme();

        $this->view->assignGlobal('language', CMS_Language::getCurrentLanguage());
        $this->view->assignGlobal('languages', CMS_Language::getLanguages());

        $this->view->assignGlobal('url', $this->url);

        $site = CMS_Bazalt::getSite();
        Metatags::set('SITE_TITLE', $site->title);

        $this->view->assignGlobal('site', $site);

        // register widget namespace
        if (defined('WIDGETS_DIR') && WIDGETS_DIR) {
            Core_Autoload::registerNamespace('Widgets', WIDGETS_DIR);
        }

        foreach ($this->components as $component) {
            $component->initComponent($this);
        }
    }

    public function addWebservice($name)
    {
        $fileName = Core_Autoload::getFilename($name);

        $keyName = $fileName . (file_exists($fileName) ? filemtime($fileName) : '');
        $file = $keyName . '.js';

        if (!Assets_FileManager::exists($file)) {
            $comService = new $name();

            $content = $comService->getServiceJs();
            Assets_FileManager::save($file, $content);
        }

        CMS_Webservice::addWebservice(Assets_FileManager::filename($file));
    }

    public function onThemeSet($theme)
    {
        CMS_Locale::hasLocale($theme->getPath() . '/locale', $theme->Alias);

        $folders = Html_Form::getView()->getFolders();
        $folders['theme'] = $theme->getPath() . '/templates';
        Html_Form::getView()->setFolders($folders);

        // why?
        if (!$this->view) {
            $this->initView();
        }
        $this->view->assignGlobal('cms_theme', $theme);
    }
    
    public function showPage(&$content = null)
    {
        $profile = Logger::start(__CLASS__, __FUNCTION__);
        //Theme::assign('metatags', Metatags::Singleton()->__toString());

        $this->view->showPage('layout', $content);

        Logger::stop($profile);
    }

    public function showErrorPage(Exception $e, $message = null)
    {
        if (STAGE == PRODUCTION_STAGE) {
            $this->onPageNotFound();
            return;
        }
        if ($message != null) {
            echo $message . '<br />';
        }
        echo $e->getMessage() . '<br />';

        if (STAGE == DEVELOPMENT_STAGE) {
            echo '<pre>';
            print_r($e->getTraceAsString());
            echo '</pre>';
        }
    }

    public function onPageNotFound($url)
    {
        CMS_Response::pageNotFound();
        $this->view->display('error404');
    }
    
    public function onAccessDenied($url)
    {
        if (CMS_Request::isAjax()) {
            CMS_Response::accessDenied();
            CMS_Response::ouput('403 Forbidden');
            exit;
        }
        $this->view->assign('loginForm', new CMS_Form_Login());
        $this->view->display('error403');
    }

    public function preInit()
    {
        $this->initMapper();

        Event::trigger(__CLASS__, 'BeforeRouteInit', array($this->mapper));

        $this->preInitRoutes();

        $this->initRoutes();

        $this->postInitRoutes();

        Event::trigger(__CLASS__, 'AfterRouteInit', array($this->mapper));
    }

    public function initMapper()
    {
        $this->applicationMapper =
        $this->mapper = CMS_Mapper::init();

        if ($this->config['urlPrefix'] != '/') {
            $this->applicationMapper = $this->mapper->submapper($this->config['urlPrefix'])
                                                    ->name($this->name() . '.Home');
        }
    }

    protected function preInitRoutes()
    {
    }

    protected function postInitRoutes()
    {
    }


    protected function preDispatch($url)
    {
    }

    protected function dispatch($url)
    {
        $profile = Logger::start(__CLASS__, __FUNCTION__);

        // cache
        $this->preDispatch($url);

        //ob_start();

        try {
            $this->mapper->connect('/')->name('home');

            $this->mapper->dispatch($url);
        } catch(Routing_Exception_NoMatch $e) {
            $this->onPageNotFound($url);
        } catch(CMS_Exception_PageNotFound $e) {
            $this->onPageNotFound($url);
        } catch(CMS_Exception_AccessDenied $e) {
            $this->onAccessDenied($url);
        }

        $this->OnPageComplete();

        Logger::stop($profile);

        // document type
        return ob_get_clean();
    }

    public function start()
    {
        if (USE_DEFAULT_THEME) {
            $defaultTheme = CMS_Theme::getThemeInfo(CMS_Theme::DEFAULT_NAME);
            CMS_Locale::hasLocale($defaultTheme->getPath() . '/locale', $defaultTheme->Alias);

            $folders = Html_Form::getView()->getFolders();
            $folders['default_theme'] = $defaultTheme->getPath() . '/templates';
            Html_Form::getView()->setFolders($folders);
        }

        $this->connectComponents();

        $this->initView();

        $this->hasLocale('locale', get_class($this));

        $this->user = CMS_User::getUser();
        $this->view->assignGlobal('user', $this->user);

        $this->preInit();

        $this->init();

        $content = $this->dispatch($this->url);

        $this->showPage($content);
    }
}
