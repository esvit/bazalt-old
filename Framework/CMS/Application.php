<?php

namespace Framework\CMS;

use Framework\Core\Event,
    Framework\System\Routing\Route,
    Framework\CMS\Pagination\ApplicationTrait,
    Framework\Core\Logger;

abstract class Application
{
    use ApplicationTrait;

    /**
     * @var View
     */
    protected $view = null;

    protected $url;

    protected $user;

    protected $request;

    protected $route;

    protected $config = array();

    /**
     * @var CMS_Component[]
     */
    protected $components = array();

    protected $jsComponents = [];

    public $eventOnPageComplete = Event::EMPTY_EVENT;

    /**
     * @return Application
     */
    public static function current()
    {
        return Bootstrap::current();
    }

    public function registerJsComponent($name, $file)
    {
        $this->jsComponents[$name] = $file;
    }

    public function jsComponents()
    {
        return $this->jsComponents;
    }

    public function __construct($config = array())
    {
        $this->config = $config;
    }

    public function view()
    {
        return $this->view;
    }

    public function route()
    {
        return $this->route;
    }

    public function config()
    {
        return $this->config;
    }

    public function url($url = null)
    {
        if ($url !== null) {
            $this->url = $url;
            return $this;
        }
        return $this->url;
    }

    public function initView()
    {
        $this->view = View::root();
    }

    public function initRoutes()
    {
    }

    public function hasLocale($folder, $domain)
    {
        if (empty($folder)) {
            throw new \Exception('You must enter folder name for locale');
        }
        $localeFolder = $this->config['path'] . PATH_SEP . $folder;

        Locale_Translation::bindTextDomain($localeFolder, $domain);

        return $this;
    }

    public function init()
    {
        $this->url = Http\Request::url();
        self::parsePageFromUrl($this->url);
    }

    public function addWebservice($name)
    {
        $file = CMS_Webservice::getServiceFile($name);

        if (!Assets_FileManager::exists($file)) {
            $comService = new $name();

            $content = $comService->__getJavascript($name);
            Assets_FileManager::save($file, $content);
        }
        CMS_Webservice::addWebservice(Assets_FileManager::filename($file));
    }
    
    public function showPage(&$content = null)
    {
        //Theme::assign('metatags', Metatags::Singleton()->__toString());
        $this->view->showPage('layout', $content, $this->route);
    }

    public function showErrorPage(Exception $e, $message = null)
    {
        if (STAGE == PRODUCTION_STAGE) {
            $this->onPageNotFound($this->url);
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
        Response::pageNotFound();
        $this->view->display('error.404');
    }
    
    public function onAccessDenied($url)
    {
        if (CMS_Request::isAjax()) {
            CMS_Response::accessDenied();
            CMS_Response::output('403 Forbidden');
            exit;
        }
        $this->view->assign('loginForm', new CMS_Form_Login());
        $this->view->display('error403');
    }

    public function preInit()
    {
        $this->initRoutes();
    }

    protected function dispatch($url)
    {
        ob_start();
        try {
            if ($this->route = Route::root()->find($url)) {
                $this->route->dispatch();
            } else {
                throw new Exception\PageNotFound();
            }
        } catch(Exception\PageNotFound $e) {
            $this->onPageNotFound($url);
        } catch(Exception\AccessDenied $e) {
            $this->onAccessDenied($url);
        }
        // document type
        return ob_get_clean();
    }

    public function start()
    {
        $components = Bazalt::connectComponents();

        $this->initView();

        //$this->hasLocale('locale', get_class($this));

        $this->preInit();

        Bazalt::initComponents($this);

        $this->init($components);
    }

    public function getRoles()
    {
        return array();
    }

    /**
     * Detect current site from domain name and redirect as required
     *
     * @throws CMS_Exception_DomainNotFound
     */
    public function getSite()
    {
        $domain = Bazalt::getDomainName();

        if (!defined('ENABLE_MULTISITING') || !ENABLE_MULTISITING) {
            $site = CMS_Model_Site::getById(1);
            if (!$site) {
                $site = CMS_Model_Site::create();
                $site->id = 1;
                $site->domain = $domain;
            }
            $site->is_subdomain = false;
            $site->is_active = true;
            $site->save();
        } else {
            $site = CMS_Model_Site::getSiteByDomain($domain);
            if (!$site) {
                $wildcard = '*' . substr($domain, strpos($domain, '.'));
                $site = CMS_Model_Site::getSiteByDomain($wildcard);
                if ($site) {
                    $site->subdomain = substr($domain, 0, strpos($domain, '.'));
                }
            }
        }
        if (!CLI_MODE) {
            if ($site->is_redirect && $site->site_id) {
                Url::redirect(Url::getProtocol() . $site->Site->domain);
            }
        }
        if (!$site && !CLI_MODE) {
            throw new CMS_Exception_DomainNotFound($domain);
        } else if (!$site && CLI_MODE) {
            $site = CMS_Model_Site::getById(1);
        }
        if ($site->site_id != null && $site->is_subdomain) {
            $originalSite = $site;
            $site = CMS_Model_Site::getById($site->site_id);
            if (!$site) {
                throw new CMS_Exception_DomainNotFound($domain);
            }
            $site->originalSite = $originalSite;
            Session::Singleton()->cookieDomain('.' . $site->domain);
        }
        return $site;
    }
}
