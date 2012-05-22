<?php

/**
 * Клас мапер
 */
class Mapper extends Object implements IEventable
{
    /**
     * Папка де розташовані контролери
     */
    protected $folder = null;

    protected $prefix = 'Controller_';

    protected $baseUrl = '';

    protected static $urlPrefix = '';

    protected $rules = array();

    protected $defaults = array();

    protected $options = array();

    protected $submappers = array();

    protected $isSubmapper = false;

    protected $conditions = array();

    protected $submapperRoute = null;

    protected static $namedRoutes = array();

    public $eventOnFindRoute = Event::EMPTY_EVENT;

    protected static $rootMapper = null;

    protected static $dispatchedRoute = null;

    public static function getRoot()
    {
        return self::$rootMapper;
    }

    public static function getDispatchedRoute()
    {
        return self::$dispatchedRoute;
    }

    /**
     * Пов'язує ім'я роута з правилом
     */
    public static function addNamedRoute($name, Routing_Rule $route)
    {
        if (array_key_exists($name, self::$namedRoutes)) {
            throw new Exception('Route with name "' . $name . '" already exists');
        }

        self::$namedRoutes[$name] = $route;
    }

    /**
     * Пов'язує ім'я роута з правилом
     */
    public static function getRoute($name)
    {
        if (isset(self::$namedRoutes[$name])) {
            return self::$namedRoutes[$name];
        }
    }

    public static function setUrlPrefix($prefix)
    {
        self::$urlPrefix = $prefix;
    }

    public static function getUrlPrefix()
    {
        return self::$urlPrefix;
    }

    /**
     * Генерує посилання по імені роута
     */
    public static function urlFor($name, $params = array(), $withHostname = false)
    {
        return self::getRoot()->generateUrl($name, $params, $withHostname);
    }

    /**
     * Генерує посилання по імені роута
     */
    public static function patternFor($name, $withHostname = false)
    {
        return self::getRoot()->generateUrlPattern($name, $withHostname);
    }

    protected function generateUrl($name, $params = array(), $withHostname = false)
    {
        $query = array();
        $prms = array();
        foreach ($params as $key => $param) {
            if (substr($key, 0, 1) == '?') {
                $query[substr($key, 1)] = $param;
            } else {
                $prms['{' . $key . '}'] = $param;
            }
        }
        $pattern = self::patternFor($name, $withHostname);
        $url = strtr($pattern, $prms);
        if (count($query) > 0) {
            $url .= '?' . http_build_query($query);
        }
        return $url;
    }

    protected function getCacheKey($key)
    {
        return $key . $this->baseUrl;
    }

    protected function generateUrlPattern($name, $withHostname = false)
    {
        static $patterns;

        $cacheKey = $this->getCacheKey('generateUrlPattern' . $name);
        $cache = Cache::Singleton()->getCache($cacheKey);

        if ($cache) {
            return self::$urlPrefix . $cache;
        }

        if (!array_key_exists($name, self::$namedRoutes)) {
            throw new Exception('Route with name "' . $name . '" not found');
        }
        if (!is_array($patterns)) {
            $patterns = array();
        }

        $url = isset($patterns[$name]) ? $patterns[$name] : self::$namedRoutes[$name]->toUrlPattern();
        $patterns[$name] = $url;

        //Додає в кінень посилання шлеш, для СЕО
        if (SEO_LAST_SLASH_IN_URL && substr($url, -1) != '/') {
            if (substr($url, -4, 1) != '.') { // якщо не файл
                $url .= '/';
            }
        }
        Cache::Singleton()->setCache($cacheKey, $url);

        $prefix = self::$urlPrefix;

        // for url like /index_dev.php/etc/...
        if (array_key_exists('PATH_INFO', $_SERVER)) {
            $prefix = $_SERVER['SCRIPT_NAME'] . $prefix;
        }
        $prefix = rtrim($prefix, '/');
        $url = $prefix . $url;
        if (DataType_Url::needPortInDomain() || DataType_Url::isSecure() || $withHostname) {
            $url = DataType_Url::getHostname() . $url;
        }
        return $url;
    }

    public function postInit()
    {
    }

    public static function init($options = array())
    {
        if (self::$rootMapper != null) {
            throw new Exception('Root mapper alredy init');
        }
        $class = getCalledClass();
        $mapper = new $class(null, $options, false);

        self::$rootMapper = $mapper;
        return $mapper;
    }

    protected function __construct($baseUrl = null, $options = array())
    {
        parent::__construct();

        $this->options = $options;
        $this->baseUrl = $baseUrl;

        $this->defaults = (array_key_exists('defaults', $options)) ? $options['defaults'] : array();

        $this->folder = (array_key_exists('folder', $options)) ? $options['folder'] : '';
        $this->prefix = (array_key_exists('prefix', $options)) ? $options['prefix'] : $this->prefix;
        /*if (!is_dir($this->folder)) {
            throw new Exception('Invalid folder for controllers: ' . $this->folder);
        }*/
        $this->folder = realpath($this->folder);
        if (substr($this->folder, -1) != PATH_SEP) {
            $this->folder .= PATH_SEP;
        }
        $this->postInit();
    }

    public function submapper($subDir, $defaults = array())
    {
        if (is_array($subDir)) {
            $defaults = $subDir;
            $subDir = '';
        } else if (empty($subDir)) {
            throw new Exception('Invaid subpath for submapper');
        }
        $class = get_class($this);
        $options = $this->options;
        $options['defaults'] = array_merge($this->defaults, $defaults);
        $map = new $class(rtrim($this->baseUrl, '/') . '/' . ltrim($subDir, '/'), $options);
        //$this->submappers[$subDir] = $map;

        $map->submapperRoute = $this->connectSubmapper($map, $subDir, array_merge($this->defaults, $defaults));
        return $map;
    }

    public function name($value = null)
    {
        if (!$this->submapperRoute) {
            throw new Exception('Invalid submapper');
        }

        $this->submapperRoute->name($value);
        return $this;
    }

    public function connectSubmapper(Mapper $map, $subPattern, $defaults = array())
    {
        $this->getLogger()->info(sprintf('Connect submapper "%s" "%s"', $this->baseUrl, $subPattern));

        $this->submappers[$subPattern] = $map;
        $rule = new Routing_SubmapperRule($map, $subPattern, $defaults);

        return $this->connectRule($rule);
    }

    public function connectRule(Routing_Rule $rule)
    {
        if (count($this->conditions) > 0) {
            foreach ($this->conditions as $cond) {
                $rule->condition($cond['type'], $cond['options']);
            }
        }
        $this->rules []= $rule;

        return $rule;
    }

    public function connect($urlRule, $defaults = array())
    {
        $this->getLogger()->info(sprintf('Connect route "%s" "%s"', $this->baseUrl, $urlRule));
        return $this->connectRule(new Routing_Rule($this, $urlRule, $this->baseUrl, array_merge($this->defaults, $defaults)));
    }

    /**
     * Знаходить правило по посиланню
     */
    public function find($url)
    {
        $profile = Logger::start(__CLASS__, __FUNCTION__);
        $cacheKey = __FUNCTION__ . $url . $this->baseUrl;
        $cache = Cache::Singleton()->getCache($cacheKey);

        $urlParts = parse_url($url);
        $url = $urlParts['path'];

        $route = null;
        if ($cache) {
            $rule = self::getRoute($cache);
            $route = $rule->compare($url);
            if ($route) {
                $this->OnFindRoute($cache);
                Logger::stop($profile);
                return $route;
            }
        }

        foreach ($this->rules as $rule) {
            if ($route = $rule->compare($url)) {
                break;
            }
        }
        if ($route) {
            Cache::Singleton()->setCache($cacheKey, $rule->name(), Cache::TIME_DAY);
            $this->OnFindRoute($route);
            Logger::stop($profile);
        }
        return $route;
    }

    public function dispatch($url)
    {
        $route = $this->find($url);
        if (!$route) {
            throw new Routing_Exception_NoMatch('Route not found');
        }

        self::$dispatchedRoute = $route;
        $route->dispatch($this->folder);
    }

    public function condition($condType, $options = array())
    {
        $this->conditions []= array('type' => $condType, 'options' => $options);
        return $this;
    }
}