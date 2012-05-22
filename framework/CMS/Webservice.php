<?php

using('Framework.Cms.Ajax');

/**
 * Webservice
 */
abstract class CMS_Webservice extends Object implements IEventable
{
    /**
     * Component webservice route name
     */
    const COMPONENT_ROUTE_NAME = 'CMS.Webservice.ComponentRoute';

    /**
     * Application webservice route name
     */
    const APPLICATION_ROUTE_NAME = 'CMS.Webservice.ApplicationRoute';

    /**
     * Webservice reflection class
     *
     * @var Type
     */
    protected $serviceType = null;

    /**
     * Return url of webservice
     */
    abstract function __getServiceScriptName();

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->serviceType = typeOf($this);

        Event::trigger(__CLASS__, 'OnServiceInit', array($this));
    }

    protected function getMethodsArgs()
    {
        return array();
    }

    /**
     * Add webservice route 
     *
     * @param CMS_Mapper $router Mapper
     *
     * @return void
     */
    public static function initWebserviceRoutes($router)
    {
        $webserviceEntryPoint = 'webservice';
        if (STAGE == PRODUCTION_STAGE) { // maybe this is security :)
        //    $webserviceEntryPoint = md5(substr(CMS_Bazalt::getSecretKey(), 0, 10)); // just for fun :)
        }
        $router->connect('/' . $webserviceEntryPoint . '/{service}', array('action' => 'applicationWebservice', 'controller' => 'CMS_Controller_Webservice'))
               ->name(self::APPLICATION_ROUTE_NAME);

        $router->connect('/' . $webserviceEntryPoint . '/{cms_component}/{service}', array('action' => 'componentWebservice', 'controller' => 'CMS_Controller_Webservice'))
               ->name(self::COMPONENT_ROUTE_NAME);
    }

    protected function getMethodArgs($methodName, $callType)
    {
        $methodsArgs = $this->getMethodsArgs();
        if (is_array($methodsArgs) && array_key_exists($methodName, $methodsArgs)) {
            return $methodsArgs[$methodName][$callType];
        }
        return array();
    }

    /**
     * Return js script filename
     */
    public static function getBaseJsFileName()
    {
        return dirname(__FILE__) . '/templates/cms/webservice/bazaltscriptservice.js';
    }

    public static function printBaseJs()
    {
        readfile(self::getBaseJsFileName());
    }

    /**
     * Check user rights for access to the method
     */
    protected function __getMethodAccess($method)
    {
        //$user = CMS_User::getUser();
        return true;//$user->hasRight(null, CMS_Bazalt::ACL_GODMODE);
    }

    /**
     * Check if method is callable
     */
    protected function isValidMethod($method)
    {
        $name = $method->getName();
        // if method private or user haven't rigths for access
        if (substr($name, 0, 2) == '__' || (substr($name, 0, 1) == '_' && !$this->__getMethodAccess($name))) {
            return false;
        }
        $class = $method->getDeclaringClass()->name;
        // disable methods of this and parent class
        return !($class == __CLASS__ || $class == 'Object');
    }

    /**
     * Get service methods
     */
    public function getServiceMethods()
    {
        $methods = array();
        $classMethods = $this->serviceType->getMethods();
        foreach ($classMethods as $method) {
            if (!$this->isValidMethod($method)) {
                continue;
            }
            $args = array();
            foreach ($method->getParameters() as $i => $parameter) {
                $args[$i] = $parameter->name;
            }
            $name = $method->name;

            $args = array_merge($args, $this->getMethodArgs($name, 'pre'));
            $methods[$name] = $args;
        }
        return $methods;
    }

    /**
     * Get service method
     *
     * @param string $name Method name
     *
     * @return ReflectionMethod or null
     */
    public function getServiceMethod($name)
    {
        try {
            $method = $this->serviceType->getMethod($name);
        } catch (ReflectionException $e) {
            return null;
        }

        if ($method != null && $this->isValidMethod($method)) {
            return $method;
        }
        return null;
    }

    public function executeService()
    {
        Metatags::Singleton()->noIndex()->noFollow();

        Event::trigger(__CLASS__, 'OnServiceExecute', array($this));
        if (isset($_REQUEST['method'])) {
            $methodName = $_REQUEST['method'];
            $args = array();
            if (array_key_exists('argCount', $_REQUEST) && is_numeric($_REQUEST['argCount'])) {
                $argCount = intval($_REQUEST['argCount']);
                for ($i = 0; $i < $argCount; $i++) {
                    $arg = $_REQUEST['arg' . $i];
                    if (($arg{0} == '{' && $arg{strlen($arg) - 1} == '}') ||
                        ($arg{0} == '[' && $arg{strlen($arg) - 1} == ']')) {
                        $argJson = json_decode($arg);

                        if ($argJson) {
                            $arg = $argJson;
                        }
                    }
                    if ($arg == 'null') {
                        $arg = null;
                    }
                    $args []= $arg;
                }
            }

            try {
                $method = $this->getServiceMethod($methodName);
            } catch (ReflectionException $ex) {
                $method = null;
            }

            if (!$method) {
                self::sendError(sprintf(__('The method "%s" was not found in webservice.', 'CMS'), $methodName));
            } else {
                $this->__preAction($result, $args, $method);
                $result = $method->invokeArgs($this, $args);
                $this->__postAction($result, $args, $method);
                self::sendData($result);
            }
        }
    }

    /**
     * Return error response
     */
    public static function sendError($error, $return = false)
    {
        $response = new stdClass();
        if (is_string($error)) {
            $response->message = $error;
        } else if ($error instanceof Exception) {
            $response->message = $error->getMessage();

            if (STAGE == DEVELOPMENT_STAGE) {
                // send stack trace in development mode
                $response->type .= get_class($error);
                $response->trace .= $error->getTraceAsString();
            }
        }

        return self::sendData($response, $return);
    }

    /**
     * Output response
     */
    public static function sendData($response, $return = false)
    {
        header('Content-type: application/json; charset=UTF-8');
        CMS_Browser::headerNoCache();
        MetaTags::Singleton()->noIndex()->noFollow();

        $json = json_encode($response);
        $result = '';
        if (isset($_GET['callback'])) {
            $result = $_GET['callback'] . '(' . $json .')';
        } else {
            $result = $json;
        }
        if ($return) {
            return $result;
        }
        echo $result;
        exit;
    }

    protected function __preAction(&$data, &$args, $method)
    {
    }

    protected function __postAction(&$data, $args, $method)
    {
        if ($data instanceof ORM_Record) {
            $data = $data->toArray();
            return;
        }
        if ($data instanceof ORM_Collection) {
            $coll = $data;
            $data = $coll->fetchPage();
            $arrayRes = array(
                'page' => $coll->page(),
                'pagesCount' => $coll->getPagesCount(),
                'totalCount' => $coll->count(),
                'data' => array()
            );

            if (is_array($data) && !array_key_exists('data', $data)) {
                foreach ($data as $key => $item) {
                    if ($item instanceof ORM_BaseRecord) {
                        $arrayRes['data'][$key] = $item->toArray();
                    } else {
                        $arrayRes['data'][$key] = $item;
                    }
                }
                $data = $arrayRes;
            }
            return;
        }
        if (is_array($data)) {
            $arrayRes = array();
            foreach ($data as $key => $item) {
                if ($item instanceof ORM_BaseRecord) {
                    $arrayRes[$key] = $item->toArray();
                } else {
                    $arrayRes[$key] = $item;
                }
            }
            $data = $arrayRes;
        }
    }

    public function getServiceInfo()
    {
        return array(
                    'name' => get_class($this),
                    'script' => $this->__getServiceScriptName(),
                    'methods' => $this->getServiceMethods()
                );
    }

    public function getServiceJs()
    {
        $service = $_GLOBAL['service'] = $this->getServiceInfo();

        ob_start();
        include 'templates/cms/webservice/servicejs.php';
        $serviceContent = ob_get_contents();
        ob_end_clean();
        return $serviceContent;
    }

    public function showServiceJs()
    {
        header('Content-type: application/javascript');
        Metatags::Singleton()->noIndex()->noFollow();
        echo $this->getServiceJs();
    }

    public function showServiceInfo()
    {
        Metatags::Singleton()->noIndex()->noFollow();
        $service = $_GLOBAL['service'] = $this->getServiceInfo();
        include 'templates/cms/webservice/servicehtml.php';
    }

    public static function addWebservice($serviceFile)
    {
        Assets_JS::add(self::getBaseJsFileName());
        Assets_JS::add($serviceFile);
    }
}